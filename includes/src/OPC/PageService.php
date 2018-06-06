<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC;

class PageService
{
    /**
     * @var string
     */
    protected $adminName = '';

    /**
     * @var null|Service
     */
    protected $opc = null;

    /**
     * @var null|PageDB
     */
    protected $pageDB = null;

    /**
     * @var null|Locker
     */
    protected $locker = null;

    /**
     * @var null|Page
     */
    protected $curPage = null;

    /**
     * PageService constructor.
     * @param PageDB $pageDB
     * @param Locker $locker
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
    public function getPageIOFunctionNames()
    {
        return [
            'getPageIOFunctionNames',
            'getRevisionList',
            'getDraft',
            'lockDraft',
            'unlockDraft',
            'getDraftPreview',
            'getRevisionPreview',
            'publicateDraft',
            'saveDraft',
            'createPagePreview',
        ];
    }

    /**
     * @param \AdminIO $io
     * @throws \Exception
     */
    public function registerAdminIOFunctions($io)
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
    public function createDraft($id)
    {
        return (new Page())->setId($id);
    }

    /**
     * @param int $key
     * @return Page
     * @throws \Exception
     */
    public function getDraft(int $key)
    {
        return $this->pageDB->getDraft($key);
    }

    /**
     * @param int $revId
     * @return Page
     * @throws \Exception
     */
    public function getRevision(int $revId)
    {
        return $this->pageDB->getRevision($revId);
    }

    /**
     * @param int $key
     * @return array
     */
    public function getRevisionList(int $key)
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
    public function getCurPage()
    {
        $isEditMode    = $this->opc->isEditMode();
        $editedPageKey = $this->opc->getEditedPageKey();

        if ($this->curPage === null) {
            if ($isEditMode && $editedPageKey > 0) {
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
    public function getDrafts(string $id)
    {
        return $this->pageDB->getDrafts($id);
    }

    /**
     * @param int $key
     * @return string[]
     * @throws \Exception
     */
    public function getDraftPreview(int $key)
    {
        return $this->getDraft($key)->getAreaList()->getPreviewHtml();
    }

    /**
     * @param int $revId
     * @return string[]
     * @throws \Exception
     */
    public function getRevisionPreview(int $revId)
    {
        return $this->getRevision($revId)->getAreaList()->getPreviewHtml();
    }

    /**
     * @param array $data
     * @throws \Exception
     */
    public function saveDraft($data)
    {
        $draft = $this->getDraft($data['key'])->deserialize($data);
        $this->pageDB->saveDraft($draft);
    }

    /**
     * @param $data
     * @throws \Exception
     */
    public function publicateDraft($data)
    {
        $page = (new Page())->deserialize($data);
        $this->pageDB->saveDraftPublicationStatus($page);
    }

    /**
     * @param string $id
     * @return $this
     */
    public function deletePage($id)
    {
        $this->pageDB->deletePage($id);

        return $this;
    }

    /**
     * @param int $key
     * @return $this
     */
    public function deleteDraft($key)
    {
        $this->pageDB->deleteDraft($key);

        return $this;
    }

    /**
     * @param int $key
     * @throws \Exception
     */
    public function lockDraft($key)
    {
        $draft = $this->getDraft($key);
        $this->locker->lock($this->adminName, $draft);
    }

    /**
     * @param $key
     * @throws \Exception
     */
    public function unlockDraft($key)
    {
        $page = (new Page())->setKey($key);
        $this->locker->unlock($page);
    }

    /**
     * @param array $data
     * @return string[]
     */
    public function createPagePreview($data)
    {
        $page = (new Page())->deserialize($data);
        return $page->getAreaList()->getPreviewHtml();
    }
}
