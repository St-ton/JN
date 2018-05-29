<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC;

/**
 * Class DB
 * @package OPC
 */
class DB
{
    /**
     * @var null|\DB\DbInterface
     */
    protected $shopDB = null;

    /**
     * DB constructor.
     */
    public function __construct(\DB\DbInterface $shopDB)
    {
        $this->shopDB = $shopDB;
    }

    /**
     * @param bool $withInactive
     * @return int[]
     */
    public function getAllBlueprintIds($withInactive = false)
    {
        $blueprintsDB = $this->shopDB->selectAll(
            'topcblueprint',
            $withInactive ? [] : 'bActive',
            $withInactive ? [] : 1,
            'kBlueprint'
        );

        $blueprintIds = [];

        foreach ($blueprintsDB as $blueprintDB) {
            $blueprintIds[] = $blueprintDB->kBlueprint;
        }

        return $blueprintIds;
    }

    /**
     * @param Blueprint $blueprint
     * @return bool
     */
    public function blueprintExists(Blueprint $blueprint)
    {
        return is_object($this->shopDB->select('topcblueprint', 'kBlueprint', $blueprint->getId()));
    }

    /**
     * @return $this
     */
    public function deleteBlueprint(Blueprint $blueprint)
    {
        $this->shopDB->delete('topcblueprint', 'kBlueprint', $blueprint->getId());

        return $this;
    }

    /**
     * @param Blueprint $blueprint
     * @throws \Exception
     */
    public function loadBlueprint(Blueprint $blueprint)
    {
        $blueprintDB = $this->shopDB->select('topcblueprint', 'kBlueprint', $blueprint->getId());

        if (!is_object($blueprintDB)) {
            throw new \Exception("The OPC blueprint with the id '{$blueprint->getId()}' could not be found.");
        }

        $content = json_decode($blueprintDB->cJson, true);

        $blueprint
            ->setId($blueprintDB->kBlueprint)
            ->setName($blueprintDB->cName)
            ->deserialize(['name' => $blueprintDB->cName, 'content' => $content]);
    }

    /**
     * @param Blueprint $blueprint
     * @return $this
     * @throws \Exception
     */
    public function saveBlueprint(Blueprint $blueprint)
    {
        if ($blueprint->getName() === '') {
            throw new \Exception('The OPC blueprint data to be saved is incomplete or invalid.');
        }

        $blueprintDB = (object)[
            'kBlueprint' => $blueprint->getId(),
            'cName'      => $blueprint->getName(),
            'cJson'      => json_encode($blueprint->getInstance()),
        ];

        if ($this->blueprintExists($blueprint)) {
            $res = $this->shopDB->update('topcblueprint', 'kBlueprint', $blueprint->getId(), $blueprintDB);

            if ($res === -1) {
                throw new \Exception('The OPC blueprint could not be updated in the DB.');
            }
        } else {
            $key = $this->shopDB->insert('topcblueprint', $blueprintDB);

            if ($key === 0) {
                throw new \Exception('The OPC blueprint could not be inserted into the DB.');
            }

            $blueprint->setId($key);
        }

        return $this;
    }

    /**
     * @return PortletGroup[]
     * @throws \Exception
     */
    public function getPortletGroups($withInactive = false)
    {
        $groupNames = $this->shopDB->query("SELECT DISTINCT(cGroup) FROM topcportlet ORDER BY cGroup ASC", 2);
        $groups     = [];

        foreach ($groupNames as $groupName) {
            $groups[] = $this->getPortletGroup($groupName->cGroup, $withInactive);
        }

        return $groups;
    }

    /**
     * @param string $groupName
     * @return PortletGroup
     * @throws \Exception
     */
    public function getPortletGroup($groupName, $withInactive = false)
    {
        $portletsDB = $this->shopDB->selectAll(
            'topcportlet',
            $withInactive ? 'cGroup' : ['cGroup', 'bActive'],
            $withInactive ? $groupName : [$groupName, 1],
            'cClass',
            'cTitle'
        );

        $portletGroup = new PortletGroup($groupName);

        foreach ($portletsDB as $portletDB) {
            $portlet = $this->getPortlet($portletDB->cClass);
            $portletGroup->addPortlet($portlet);
        }

        return $portletGroup;
    }

    /**
     * @return Portlet[]
     * @throws \Exception
     */
    public function getAllPortlets()
    {
        $portlets   = [];
        $portletsDB = $this->shopDB->selectAll('topcportlet', [], [], 'cClass', 'cTitle');

        foreach ($portletsDB as $portletDB) {
            $portlets[] = $this->getPortlet($portletDB->cClass);
        }

        return $portlets;
    }

    /**
     * @return int
     */
    public function getPortletCount()
    {
        return (int)$this->shopDB->query("SELECT count(kPortlet) AS count FROM topcportlet", 1)->count;
    }

    /**
     * @param string $class
     * @return Portlet
     * @throws \Exception
     */
    public function getPortlet($class)
    {
        if ($class === '') {
            throw new \Exception("The OPC portlet class name '$class' is invalid.");
        }

        $portletDB = $this->shopDB->select('topcportlet', 'cClass', $class);

        if (!is_object($portletDB)) {
            throw new \Exception("The OPC portlet with class name '$class' could not be found.");
        }

        if ((int)$portletDB->bActive !== 1) {
            throw new \Exception("The OPC portlet with class name '$class' is inactive.");
        }

        if ($portletDB->kPlugin > 0) {
            $plugin  = new \Plugin($portletDB->kPlugin);
            $include = PFAD_ROOT . PFAD_PLUGIN . $plugin->cVerzeichnis . '/' . PFAD_PLUGIN_VERSION
                . $plugin->getCurrentVersion() . '/' . PFAD_PLUGIN_ADMINMENU . PFAD_PLUGIN_PORTLETS
                . $portletDB->cClass . '/' . $portletDB->cClass . '.php';
            require_once $include;
        }

        /** @var Portlet $portlet */
        $fullClass = "\\OPC\\Portlets\\$class";
        $portlet   = new $fullClass();

        return $portlet
            ->setId($portletDB->kPortlet)
            ->setPluginId($portletDB->kPlugin)
            ->setTitle($portletDB->cTitle)
            ->setClass($portletDB->cClass)
            ->setGroup($portletDB->cGroup)
            ->setActive((int)$portletDB->bActive === 1);
    }

    /**
     * @param string $id
     * @return array
     */
    public function getPageDrafts($id)
    {
        return $this->shopDB->selectAll('topcpage', 'cPageId', $id, 'kPage, dPublishFrom, dPublishTo, cName');
    }

    /**
     * @param string $id
     * @return int
     */
    public function getPageDraftCount($id)
    {
        return $this->shopDB->queryPrepared(
            "SELECT count(kPage) AS count FROM topcpage WHERE cPageId = :id",
            ['id' => $id],
            1
        )->count;
    }

    /**
     * @return string[]
     */
    public function getAllPageIds()
    {
        $pageIds = [];
        $pagesDB = $this->shopDB->selectAll('topcpage', [], [], 'cPageId', 'cPageUrl');

        foreach ($pagesDB as $pageDB) {
            $pageIds[] = $pageDB->cPageId;
        }

        return $pageIds;
    }

    /**
     * @return int
     */
    public function getPageCount()
    {
        return (int)$this->shopDB->query("SELECT count(kPage) AS count FROM topcpage", 1)->count;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function pageIdExists($id)
    {
        return is_object($this->shopDB->select('topcpage', 'cPageId', $id));
    }

    /**
     * @param Page $page
     * @return bool
     */
    public function pageExists(Page $page)
    {
        return is_object($this->shopDB->select('topcpage', 'cPageId', $page->getId()));
    }

    /**
     * @param string $pageId
     * @return bool
     */
    public function hasPublicPage($pageId)
    {
        return is_object($this->shopDB->queryPrepared(
            "SELECT kPage FROM topcpage
                    WHERE
                        cPageId = :pageId
                        AND dPublishFrom IS NOT NULL
                        AND dPublishFrom <= NOW()
                        AND (dPublishTo > NOW() OR dPublishTo IS NULL)
                    ORDER BY dPublishFrom DESC",
            ['pageId' => $pageId],
            \DB\ReturnType::SINGLE_OBJECT
        ));
    }

    /**
     * @param string $id
     * @return $this
     */
    public function deletePage($id)
    {
        $this->shopDB->delete('topcpage', 'cPageId', $id);

        return $this;
    }

    /**
     * @param int $key
     * @return $this
     */
    public function deletePageDraft($key)
    {
        $this->shopDB->delete('topcpage', 'kPage', $key);

        return $this;
    }

    /**
     * @param Page $page
     * @return $this
     * @throws \Exception
     */
    public function loadPage(Page $page)
    {
        if ($page->getRevId() > 0) {
            $revision = new \Revision();
            $revision = $revision->getRevision($page->getRevId());
            $pageDB   = json_decode($revision->content);
        } elseif ($page->getKey() > 0) {
            $pageDB = $this->shopDB->select('topcpage', 'kPage', $page->getKey());
        } elseif (strlen($page->getId()) === 32) {
            $pageDB = $this->shopDB->query(
                "SELECT * FROM topcpage
                    WHERE dPublishFrom IS NOT NULL
                        AND dPublishFrom <= NOW()
                        AND (dPublishTo > NOW() OR dPublishTo IS NULL)
                    ORDER BY dPublishFrom DESC",
                1
            );
        } else {
            throw new \Exception("The OPC page has no sufficient data to be loaded from the database.");
        }

        if (!is_object($pageDB)) {
            throw new \Exception("The OPC page could not be found in the database.");
        }

        $page
            ->setKey($pageDB->kPage)
            ->setId($pageDB->cPageId)
            ->setPublishFrom($pageDB->dPublishFrom)
            ->setPublishTo($pageDB->dPublishTo)
            ->setName($pageDB->cName)
            ->setUrl($pageDB->cPageUrl)
            ->setLastModified($pageDB->dLastModified)
            ->setLockedBy($pageDB->cLockedBy)
            ->setLockedAt($pageDB->dLockedAt)
            ->setReplace($pageDB->bReplace);

        $areaData = json_decode($pageDB->cAreasJson, true);

        if ($areaData !== null) {
            $page->getAreaList()->deserialize($areaData);
        }

        return $this;
    }

    /**
     * @param Page $page
     * @return $this
     * @throws \Exception
     */
    public function savePage(Page $page)
    {
        if ($page->getUrl() === '' || $page->getLastModified() === '' || $page->getLockedAt() === ''
            || strlen($page->getId()) !== 32
        ) {
            throw new \Exception('The OPC page data to be saved is incomplete or invalid.');
        }

        $page->setLastModified(date('Y-m-d H:i:s'));

        $pageDB = (object)[
            'cPageId'       => $page->getId(),
            'dPublishFrom'  => $page->getPublishFrom() ?? '_DBNULL_',
            'dPublishTo'    => $page->getPublishTo() ?? '_DBNULL_',
            'cName'         => $page->getName(),
            'cPageUrl'      => $page->getUrl(),
            'cAreasJson'    => json_encode($page->getAreaList()),
            'dLastModified' => $page->getLastModified(),
            'cLockedBy'     => $page->getLockedBy(),
            'dLockedAt'     => $page->getLockedAt(),
            'bReplace'      => (int)$page->isReplace(),
        ];

        if ($page->getKey() > 0) {
            $dbPage       = $this->shopDB->select('topcpage', 'cPageId', $page->getId());
            $oldAreasJson = $dbPage->cAreasJson;
            $newAreasJson = $pageDB->cAreasJson;

            if ($oldAreasJson !== $newAreasJson) {
                $revision = new \Revision();
                $revision->addRevision('opcpage', $dbPage->kPage);
            }

            if ($this->shopDB->update('topcpage', 'kPage', $page->getKey(), $pageDB) === -1) {
                throw new \Exception('The OPC page could not be updated in the DB.');
            }
        } else {
            $key = $this->shopDB->insert('topcpage', $pageDB);

            if ($key === 0) {
                throw new \Exception('The OPC page could not be inserted into the DB.');
            }

            $page->setKey($key);
        }

        return $this;
    }

    /**
     * @param Page $page
     * @return $this
     * @throws \Exception
     */
    public function savePageLockStatus(Page $page)
    {
        $pageDB = (object)[
            'cPageId'   => $page->getId(),
            'cLockedBy' => $page->getLockedBy(),
            'dLockedAt' => $page->getLockedAt(),
        ];

        if ($this->pageExists($page)) {
            if ($this->shopDB->update('topcpage', 'cPageId', $page->getId(), $pageDB) === -1) {
                throw new \Exception('The OPC page could not be updated in the DB.');
            }
        } else {
            $key = $this->shopDB->insert('topcpage', $pageDB);

            if ($key === 0) {
                throw new \Exception('The OPC page could not be inserted into the DB.');
            }

            $page->setKey($key);
        }

        return $this;
    }

    /**
     * @param Page $page
     * @return array
     */
    public function getPageRevisions(Page $page)
    {
        $revision = new \Revision();

        return $revision->getRevisions('opcpage', $page->getKey());
    }
}
