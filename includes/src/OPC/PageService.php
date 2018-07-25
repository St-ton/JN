<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC;

/**
 * Class PageService
 * @package OPC
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
     * @param PageDB  $pageDB
     * @param Locker  $locker
     */
    public function __construct(Service $opc, PageDB $pageDB, Locker $locker)
    {
        $this->opc    = $opc;
        $this->pageDB = $pageDB;
        $this->locker = $locker;
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
            'createPageLivePreview',
            'deleteDraft',
        ];
    }

    /**
     * @param \AdminIO $io
     * @throws \Exception
     */
    public function registerAdminIOFunctions(\AdminIO $io)
    {
        $this->adminName = $io->getAccount()->account()->cLogin;

        foreach ($this->getPageIOFunctionNames() as $functionName) {
            $publicFunctionName = 'opc' . ucfirst($functionName);
            $io->register($publicFunctionName, [$this, $functionName], null, 'CONTENT_PAGE_VIEW');
        }
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
     * @return null|Page
     */
    public function getPublicPage(string $id)
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
        $editedPageKey = $this->opc->getEditedPageKey();

        if ($this->curPage === null) {
            if ($this->opc->isOPCInstalled() === false) {
                $this->curPage = new Page();
            } elseif ($isEditMode && $editedPageKey > 0) {
                $this->curPage = $this->getDraft($editedPageKey);
            } else {
                $curPageUrl                    = '/' . ltrim(\Shop::getRequestUri(), '/');
                $curPageParameters             = \Shop::getParameters();
                $curPageParameters['kSprache'] = \Shop::getLanguage();
                $curPageId                     = md5(serialize($curPageParameters));
                $this->curPage                 = $this->getPublicPage($curPageId) ?? new Page();
                $this->curPage->setId($curPageId);
                $this->curPage->setUrl($curPageUrl);
            }
        }

        return $this->curPage;
    }

    /**
     * @param string $id
     * @return Page[]
     */
    public function getDrafts(string $id): array
    {
        if ($this->opc->isOPCInstalled()) {
            return $this->pageDB->getDrafts($id);
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
    public function saveDraft(array $data)
    {
        $draft = $this->getDraft($data['key'])->deserialize($data);
        $this->pageDB->saveDraft($draft);
    }

    /**
     * @param array $data
     * @throws \Exception
     */
    public function publicateDraft(array $data)
    {
        $page = (new Page())->deserialize($data);
        $this->pageDB->saveDraftPublicationStatus($page);
    }

    /**
     * @param string $id
     * @return $this
     */
    public function deletePage(string $id)
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
    public function lockDraft($key)
    {
        $draft = $this->getDraft($key);

        return $this->locker->lock($this->adminName, $draft);
    }

    /**
     * @param int $key
     * @throws \Exception
     */
    public function unlockDraft(int $key)
    {
        $page = (new Page())->setKey($key);
        $this->locker->unlock($page);
    }

    /**
     * @param array $data
     * @return string[]
     */
    public function createPagePreview(array $data): array
    {
        $page = (new Page())->deserialize($data);

        return $page->getAreaList()->getPreviewHtml();
    }

    /**
     * @param array $data
     * @return string[]
     */
    public function createPageLivePreview(array $data): array
    {
        $page = (new Page())->deserialize($data);

        return $page->getAreaList()->getFinalHtml();
    }
}
