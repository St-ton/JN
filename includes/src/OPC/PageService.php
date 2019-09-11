<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\OPC;

use JTL\Backend\AdminIO;
use JTL\Helpers\Request;
use JTL\IO\IOResponse;
use JTL\Shop;

/**
 * Class PageService
 * @package JTL\OPC
 */
class PageService
{
    /**
     * @var string
     */
    protected $adminName = '';

    /**
     * @var null|Service
     */
    protected $opc;

    /**
     * @var null|PageDB
     */
    protected $pageDB;

    /**
     * @var null|Locker
     */
    protected $locker;

    /**
     * @var null|Page
     */
    protected $curPage;

    /**
     * PageService constructor.
     * @param Service $opc
     * @param PageDB $pageDB
     * @param Locker $locker
     * @throws \SmartyException
     */
    public function __construct(Service $opc, PageDB $pageDB, Locker $locker)
    {
        $this->opc    = $opc;
        $this->pageDB = $pageDB;
        $this->locker = $locker;

        Shop::Smarty()->registerPlugin('function', 'opcMountPoint', [$this, 'renderMountPoint']);
    }

    /**
     * @return array list of the OPC service methods to be exposed for AJAX requests
     */
    public function getPageIOFunctionNames(): array
    {
        return [
            'getPageIOFunctionNames',
            'getRevisionList',
            'getDraft',
            'lockDraft',
            'unlockDraft',
            'getDraftPreview',
            'getDraftFinal',
            'getRevisionPreview',
            'publicateDraft',
            'saveDraft',
            'createPagePreview',
            'deleteDraft',
            'changeDraftName',
            'getDraftStatusHtml',
        ];
    }

    /**
     * @param AdminIO $io
     * @throws \Exception
     */
    public function registerAdminIOFunctions(AdminIO $io): void
    {
        $adminAccount = $io->getAccount();

        if ($adminAccount === null) {
            throw new \Exception('Admin account was not set on AdminIO.');
        }

        $this->adminName = $adminAccount->account()->cLogin;

        foreach ($this->getPageIOFunctionNames() as $functionName) {
            $publicFunctionName = 'opc' . \ucfirst($functionName);
            $io->register($publicFunctionName, [$this, $functionName], null, 'CONTENT_PAGE_VIEW');
        }
    }

    /**
     * @param $params
     * @param $smarty
     * @return string
     * @throws \Exception
     */
    public function renderMountPoint($params)
    {
        $id     = $params['id'];
        $title  = $params['title'] ?? $id;
        $output = '';

        if ($this->opc->isEditMode()) {
            $output = '<div class="opc-area opc-rootarea" data-area-id="' . $id . '" data-title="' . $title
                . '"></div>';
        } elseif ($this->getCurPage()->getAreaList()->hasArea($id)) {
            $output = $this->getCurPage()->getAreaList()->getArea($id)->getFinalHtml();
        }

        Shop::fire('shop.OPC.PageService.renderMountPoint', [
            'output' => &$output,
            'id' => $id,
            'title' => $title,
        ]);

        return $output;
    }

    /**
     * @param string $id
     * @return Page
     */
    public function createDraft($id): Page
    {
        return (new Page())->setId($id);
    }

    /**
     * @param int $key
     * @return Page
     * @throws \Exception
     */
    public function getDraft(int $key): Page
    {
        return $this->pageDB->getDraft($key);
    }

    /**
     * @param int $revId
     * @return Page
     * @throws \Exception
     */
    public function getRevision(int $revId): Page
    {
        return $this->pageDB->getRevision($revId);
    }

    /**
     * @param int $key
     * @return array
     */
    public function getRevisionList(int $key): array
    {
        return $this->pageDB->getRevisionList($key);
    }

    /**
     * @param string $id
     * @return Page|null
     * @throws \Exception
     */
    public function getPublicPage(string $id): ?Page
    {
        return $this->pageDB->getPublicPage($id);
    }

    /**
     * @return Page
     * @throws \Exception
     */
    public function getCurPage(): Page
    {
        $isEditMode    = $this->opc->isEditMode();
        $isPreviewMode = $this->opc->isPreviewMode();
        $editedPageKey = $this->opc->getEditedPageKey();

        if ($this->curPage === null) {
            if ($this->opc->isOPCInstalled() === false) {
                $this->curPage = new Page();
            } elseif ($isEditMode && $editedPageKey > 0) {
                $this->curPage = $this->getDraft($editedPageKey);
            } elseif ($isPreviewMode) {
                $pageData      = $this->getPreviewPageData();
                $this->curPage = $this->createPageFromData($pageData);
            } else {
                $curPageUrl    = $this->getCurPageUri();
                $curPageId     = $this->createCurrentPageId();
                $this->curPage = $this->getPublicPage($curPageId) ?? new Page();
                $this->curPage->setId($curPageId);
                $this->curPage->setUrl($curPageUrl);
            }
        }

        return $this->curPage;
    }

    /**
     * @param int $langId
     * @return string
     */
    public function getCurPageUri(int $langId = 0)
    {
        if ($langId > 0) {
            $languages = $_SESSION['Sprachen'];
            foreach ($languages as $language) {
                if ($language->id === $langId) {
                    $uri = $language->url;
                    break;
                }
            }
        } else {
            $uri = $_SERVER['HTTP_X_REWRITE_URL'] ?? $_SERVER['REQUEST_URI'];
        }

        $shopURLdata = \parse_url(Shop::getURL());
        $baseURLdata = \parse_url($uri);

        if (empty($shopURLdata['path'])) {
            $shopURLdata['path'] = '/';
        }

        if (!isset($baseURLdata['path'])) {
            return '/';
        }

        $result = \mb_substr($baseURLdata['path'], \mb_strlen($shopURLdata['path']));

        if (isset($baseURLdata['query'])) {
            $result .= '?' . $baseURLdata['query'];
        }

        $result = '/' . \ltrim($result, '/');

        return $result;
    }

    /**
     * @param string $id
     * @return array
     */
    public function getOtherLanguageDrafts(string $id): array
    {
        return $this->pageDB->getOtherLanguageDraftRows($id);
    }

    /**
     * @param int $langId
     * @return string
     */
    public function createCurrentPageId(int $langId = 0): string
    {
        $res              = '';
        $params           = (object)Shop::getParameters();
        $params->kSprache = Shop::getLanguage();

        if ($params->kKategorie > 0) {
            $res .= 'category:' . $params->kKategorie;
        } elseif ($params->kHersteller > 0) {
            $res .= 'manufacturer:' . $params->kHersteller;
        } elseif ($params->kArtikel > 0) {
            $res .= 'product:' . $params->kArtikel;
        } elseif ($params->kLink > 0) {
            $res .= 'link:' . $params->kLink;
        } elseif ($params->kMerkmalWert > 0) {
            $res .= 'attrib:' . $params->kMerkmalWert;
        } elseif ($params->kSuchspecial > 0) {
            $res .= 'special:' . $params->kSuchspecial;
        } elseif ($params->kNews > 0) {
            $res .= 'news:' . $params->kNews;
        } elseif ($params->kNewsKategorie > 0) {
            $res .= 'newscat:' . $params->kNewsKategorie;
        } elseif ($params->kUmfrage > 0) {
            $res .= 'poll:' . $params->kUmfrage;
        } elseif (\mb_strlen($params->cSuche) > 0) {
            $res .= 'search:' . \base64_encode($params->cSuche);
        } else {
            $res .= 'other:' . \md5(\serialize($params));
        }

        if (\is_array($params->MerkmalFilter) && \count($params->MerkmalFilter) > 0) {
            $res .= ';attribs:' . \implode(',', $params->MerkmalFilter);
        }
        if (\mb_strlen($params->cPreisspannenFilter) > 0) {
            $res .= ';range:' . $params->cPreisspannenFilter;
        }

        $res .= ';lang:' . ($langId > 0 ? $langId : $params->kSprache);

        return $res;
    }

    /**
     * @param string $id
     * @return Page[]
     * @throws \Exception
     */
    public function getDrafts(string $id): array
    {
        if ($this->opc->isOPCInstalled()) {
            $drafts         = $this->pageDB->getDrafts($id);
            $publicDraft    = $this->getPublicPage($id);
            $publicDraftKey = $publicDraft === null ? 0 : $publicDraft->getKey();
            \usort($drafts, function ($a, $b) use ($publicDraftKey) {
                /**
                 * @var Page $a
                 * @var Page $b
                 */
                return $a->getStatus($publicDraftKey) - $b->getStatus($publicDraftKey);
            });
            return $drafts;
        }

        return [];
    }

    /**
     * @param int $key
     * @return string[]
     * @throws \Exception
     */
    public function getDraftPreview(int $key): array
    {
        return $this->getDraft($key)->getAreaList()->getPreviewHtml();
    }

    /**
     * @param int $key
     * @return array
     * @throws \Exception
     */
    public function getDraftFinal(int $key): array
    {
        return $this->getDraft($key)->getAreaList()->getFinalHtml();
    }

    /**
     * @param int $revId
     * @return string[]
     * @throws \Exception
     */
    public function getRevisionPreview(int $revId): array
    {
        return $this->getRevision($revId)->getAreaList()->getPreviewHtml();
    }

    /**
     * @param array $data
     * @throws \Exception
     */
    public function saveDraft(array $data): void
    {
        $draft = $this->getDraft($data['key'])->deserialize($data);
        $this->pageDB->saveDraft($draft);
    }

    /**
     * @param array $data
     * @throws \Exception
     */
    public function publicateDraft(array $data): void
    {
        $page = (new Page())->deserialize($data);
        $this->pageDB->saveDraftPublicationStatus($page);
    }

    /**
     * @param string $id
     * @return $this
     */
    public function deletePage(string $id): self
    {
        $this->pageDB->deletePage($id);

        return $this;
    }

    /**
     * @param int $key
     * @return $this
     */
    public function deleteDraft(int $key): self
    {
        $this->pageDB->deleteDraft($key);

        return $this;
    }

    /**
     * @param int $key
     * @return bool true if the draft could be locked, false if it is still locked by some other user
     * @throws \Exception
     */
    public function lockDraft($key): bool
    {
        $draft = $this->getDraft($key);

        return $this->locker->lock($this->adminName, $draft);
    }

    /**
     * @param int $key
     * @throws \Exception
     */
    public function unlockDraft(int $key): void
    {
        $page = (new Page())->setKey($key);
        $this->locker->unlock($page);
    }

    /**
     * @param array $data
     * @return Page
     * @throws \Exception
     */
    public function createPageFromData(array $data): Page
    {
        return (new Page())->deserialize($data);
    }

    /**
     * @param array $data
     * @return string[]
     * @throws \Exception
     */
    public function createPagePreview(array $data): array
    {
        return $this->createPageFromData($data)->getAreaList()->getPreviewHtml();
    }

    /**
     * @return array
     */
    public function getPreviewPageData()
    {
        return \json_decode(Request::verifyGPDataString('pageData'), true);
    }

    /**
     * @param int $draftKey
     * @param string $draftName
     * @throws \Exception
     */
    public function changeDraftName(int $draftKey, string $draftName)
    {
        $this->pageDB->saveDraftName($draftKey, $draftName);
    }

    /**
     * @param int $draftKey
     * @return IOResponse
     * @throws \SmartyException
     */
    public function getDraftStatusHtml(int $draftKey): IOResponse
    {
        $draft    = $this->getDraft($draftKey);
        $smarty   = Shop::Smarty();
        $response = new IOResponse();

        $draftStatusHtml = $smarty
            ->assign('page', $draft)
            ->fetch(\PFAD_ROOT . \PFAD_ADMIN . 'opc/tpl/draftstatus.tpl');

        $response->assign('opcDraftStatus', 'innerHTML', $draftStatusHtml);

        return $response;
    }
}
