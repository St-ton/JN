<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC;

class Service
{
    /**
     * @var string
     */
    protected $adminName = '';

    /**
     * @var null|Page
     */
    protected $curPage = null;

    /**
     * @var null|DB
     */
    protected $db = null;

    /**
     * @var null|Locker
     */
    protected $locker = null;

    /**
     * Service constructor.
     */
    public function __construct()
    {
        $this->db     = new DB();
        $this->locker = new Locker();
    }

    /**
     * @return array list of the OPC service methods to be exposed for AJAX requests
     */
    public function getIOFunctionNames()
    {
        return [
            'getIOFunctionNames',
            'createPagePreview',
            'loadPagePreview',
            'savePage',
            'lockPage',
            'unlockPage',
//            'getPage',
            'getPagePreview',
            'getPageById',
            'getPageRevisions',
            'getPortletInstance',
            'getBlueprint',
            'getBlueprintList',
            'saveBlueprint',
            'deleteBlueprint',
        ];
    }

    /**
     * @param $name
     * @return $this
     */
    public function setAdminName($name)
    {
        $this->adminName = $name;

        return $this;
    }

    /**
     * @param \AdminIO $io
     * @throws \Exception
     */
    public function registerIOFunctions($io)
    {
        foreach ($this->getIOFunctionNames() as $functionName) {
            $publicFunctionName = 'opc' . ucfirst($functionName);
            $io->register($publicFunctionName, [$this, $functionName], null, 'CONTENT_PAGE_VIEW');
        }
    }

    /**
     * @param array $data
     * @return string[]
     */
    public function createPagePreview($data)
    {
        $page = new Page();
        $page->deserialize($data);

        return $page->getAreaList()->getPreviewHtml();
    }

    /**
     * @param int $id
     * @param int $revId
     * @return string[]
     */
    public function loadPagePreview($id, $revId = 0)
    {
        return $this->db->getPage($id, $revId)->getAreaList()->getPreviewHtml();
    }

    /**
     * @param array $data
     * @throws \Exception
     */
    public function savePage($data)
    {
        $page = new Page();
        $page->deserialize($data);
        $this->db->savePage($page);
    }

    /**
     * @param $id
     * @return bool
     * @throws \Exception
     */
    public function lockPage($id)
    {
        $page = new Page();
        $page->setId($id);

        return $this->locker->lock($this->adminName, $page, $this->db);
    }

    /**
     * @param string $id
     * @throws \Exception
     */
    public function unlockPage($id)
    {
        $page = new Page();
        $page->setId($id);
        $this->locker->unlock($page, $this->db);
    }

    /**
     * @param array $data
     * @return Page
     * @throws \Exception
     */
    public function getPage($data)
    {
        $page = new Page();
        $page->deserialize($data);
        $page->getDB()->loadIfExists();

        return $page;
    }

    /**
     * @param array $data
     * @return string[]
     */
    public function getPagePreview($data)
    {
        $page = $this->getPage($data);

        return $page->getAreaList()->getPreviewHtml();
    }

    /**
     * @param string $id
     * @return Page
     * @throws \Exception
     */
    public function getPageById($id)
    {
        return $this->getPage(['id' => $id]);
    }

    /**
     * @return Page
     * @throws \Exception
     */
    public function getCurrentPage()
    {
        if ($this->curPage === null) {
            $this->curPage = $this->getPageById($this->getCurrentPageId());
        }

        return $this->curPage;
    }

    /**
     * @return string
     */
    public function getCurrentPageId()
    {
        $curPageParameters             = \Shop::getParameters();
        $curPageParameters['kSprache'] = \Shop::getLanguage();

        return md5(serialize($curPageParameters));
    }

    /**
     * @param string $id
     * @return array
     * @throws \Exception
     */
    public function getPageRevisions($id)
    {
        return $this->getPageById($id)->getRevisions();
    }

    /**
     * @param array $data
     * @return Area
     */
    public function getArea($data)
    {
        return new Area($data);
    }

    /**
     * @param int $id
     * @return Portlet
     * @throws \Exception
     */
    public function getPortlet($id)
    {
        return Portlet::fromId($id);
    }

    /**
     * @param array $data
     * @return PortletInstance
     * @throws \Exception
     */
    public function getPortletInstance($data)
    {
        return new PortletInstance($data);
    }

    /**
     * @param int $id
     * @return PortletInstance
     * @throws \Exception
     */
    public function getBlueprint($id)
    {
        return (new Blueprint($id))
            ->getInstance()
            ->setPreviewHtmlEnabled(true);
    }

    /**
     * @return array
     */
    public function getBlueprintList()
    {
        return \Shop::DB()->selectAll('topcblueprint', [], []);
    }

    /**
     * @param string $name
     * @param array $data
     * @return Blueprint
     * @throws \Exception
     */
    public function saveBlueprint($name, $data)
    {
        return (new Blueprint())
            ->setName($name)
            ->setData($data)
            ->save();
    }

    /**
     * @param int $id
     * @throws \Exception
     */
    public function deleteBlueprint($id)
    {
        (new Blueprint($id))
            ->delete();
    }

    /**
     * @param string $groupName
     * @return PortletGroup
     * @throws \Exception
     */
    public function getPortletGroup($groupName = '')
    {
        return new PortletGroup($groupName);
    }

    /**
     * @return PortletGroup[]
     * @throws \Exception
     */
    public function getPortletGroups()
    {
        $groupNames = \Shop::DB()->query("SELECT DISTINCT(cGroup) FROM topcportlet ORDER BY cGroup ASC", 2);
        $groups     = [];

        foreach ($groupNames as $groupName) {
            $cName          = $groupName->cGroup;
            $groups[$cName] = new PortletGroup($cName);
        }

        return $groups;
    }

    /**
     * @return bool
     */
    public function isEditMode()
    {
        return verifyGPDataString('opcEditMode') === 'yes';
    }

    public function getAllPortlets()
    {
        return (new PortletGroup())->getPortlets();
    }
}
