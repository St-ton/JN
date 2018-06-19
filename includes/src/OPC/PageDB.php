<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC;

/**
 * Class PageDB
 * @package OPC
 */
class PageDB
{
    /**
     * @var null|\DB\DbInterface
     */
    protected $shopDB = null;

    /**
     * PageDB constructor.
     */
    public function __construct(\DB\DbInterface $shopDB)
    {
        $this->shopDB = $shopDB;
    }

    /**
     * @return int
     */
    public function getPageCount()
    {
        return $this->shopDB->query("SELECT count(DISTINCT cPageId) AS count FROM topcpage", 1)->count;
    }

    /**
     * @return array
     */
    public function getPages()
    {
        return $this->shopDB->query(
            "SELECT cPageId, cPageUrl FROM topcpage GROUP BY cPageId, cPageUrl",
            2
        );
    }

    /**
     * @param string $id
     * @return array
     */
    public function getDraftRows($id)
    {
        return $this->shopDB->selectAll('topcpage', 'cPageId', $id);
    }

    /**
     * @param string $id
     * @return int
     */
    public function getDraftCount($id)
    {
        return $this->shopDB->queryPrepared(
            "SELECT count(kPage) AS count FROM topcpage WHERE cPageId = :id",
            ['id' => $id],
            1
        )->count;
    }

    /**
     * @param int $key
     * @return object
     * @throws \Exception
     */
    public function getDraftRow(int $key)
    {
        $draftRow = $this->shopDB->select('topcpage', 'kPage', $key);

        if (!is_object($draftRow)) {
            throw new \Exception("The OPC page draft could not be found in the database.");
        }

        return $draftRow;
    }

    /**
     * @param int $revId
     * @return object
     * @throws \Exception
     */
    public function getRevisionRow(int $revId)
    {
        $revision    = new \Revision();
        $revisionRow = $revision->getRevision($revId);

        if (!is_object($revisionRow)) {
            throw new \Exception("The OPC page revision could not be found in the database.");
        }

        return json_decode($revisionRow->content);
    }

    /**
     * @param string $id
     * @return array|int|null|object
     */
    public function getPublicPageRow(string $id)
    {
        $publicRow = $this->shopDB->queryPrepared(
            "SELECT * FROM topcpage
                    WHERE cPageId = :pageId
                        AND dPublishFrom IS NOT NULL
                        AND dPublishFrom <= NOW()
                        AND (dPublishTo > NOW() OR dPublishTo IS NULL)
                    ORDER BY dPublishFrom DESC",
            ['pageId' => $id], 1
        );

        if (!is_object($publicRow)) {
            return null;
        }

        return $publicRow;
    }

    /**
     * @param string $id
     * @return Page[]
     */
    public function getDrafts(string $id)
    {
        $drafts = [];

        foreach ($this->getDraftRows($id) as $draftRow) {
            $drafts[] = $this->getPageFromRow($draftRow);
        }

        return $drafts;
    }

    /**
     * @param int $key
     * @return Page
     * @throws \Exception
     */
    public function getDraft(int $key) : Page
    {
        $draftRow = $this->getDraftRow($key);

        return $this->getPageFromRow($draftRow);
    }

    /**
     * @param int $revId
     * @return Page
     * @throws \Exception
     */
    public function getRevision(int $revId) : Page
    {
        $revisionRow = $this->getRevisionRow($revId);

        return $this->getPageFromRow($revisionRow);
    }

    /**
     * @param int $key
     * @return array
     */
    public function getRevisionList(int $key)
    {
        $revision = new \Revision();

        return $revision->getRevisions('opcpage', $key);
    }

    /**
     * @param string $id
     * @return null|Page
     */
    public function getPublicPage(string $id)
    {
        $publicRow = $this->getPublicPageRow($id);

        if (!is_object($publicRow)) {
            return null;
        }

        return $this->getPageFromRow($publicRow);
    }

    /**
     * @param Page $page
     * @return $this
     * @throws \Exception
     */
    public function saveDraft(Page $page)
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
            'dLastModified' => $page->getLastModified() ?? '_DBNULL_',
            'cLockedBy'     => $page->getLockedBy(),
            'dLockedAt'     => $page->getLockedAt() ?? '_DBNULL_',
            'bReplace'      => (int)$page->isReplace(),
        ];

        if ($page->getKey() > 0) {
            $dbPage       = $this->shopDB->select('topcpage', 'kPage', $page->getKey());
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
     * @param Page $page existing page draft
     * @return $this
     * @throws \Exception
     */
    public function saveDraftLockStatus(Page $page)
    {
        $pageDB = (object)[
            'cLockedBy' => $page->getLockedBy(),
            'dLockedAt' => $page->getLockedAt() ?? '_DBNULL_',
        ];

        if ($this->shopDB->update('topcpage', 'kPage', $page->getKey(), $pageDB) === -1) {
            throw new \Exception('The OPC page could not be updated in the DB.');
        }

        return $this;
    }

    /**
     * @param Page $page existing page draft
     * @return $this
     * @throws \Exception
     */
    public function saveDraftPublicationStatus(Page $page)
    {
        $pageDB = (object)[
            'dPublishFrom' => $page->getPublishFrom() ?? '_DBNULL_',
            'dPublishTo'   => $page->getPublishTo() ?? '_DBNULL_',
            'cName'        => $page->getName(),
        ];

        if ($this->shopDB->update('topcpage', 'kPage', $page->getKey(), $pageDB) === -1) {
            throw new \Exception('The OPC page publication status could not be updated in the DB.');
        }

        return $this;
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
    public function deleteDraft($key)
    {
        $this->shopDB->delete('topcpage', 'kPage', $key);

        return $this;
    }

    /**
     * @param object $row
     * @return Page
     */
    protected function getPageFromRow($row) : Page
    {
        $page = (new Page())
            ->setKey($row->kPage)
            ->setId($row->cPageId)
            ->setPublishFrom($row->dPublishFrom)
            ->setPublishTo($row->dPublishTo)
            ->setName($row->cName)
            ->setUrl($row->cPageUrl)
            ->setLastModified($row->dLastModified)
            ->setLockedBy($row->cLockedBy)
            ->setLockedAt($row->dLockedAt)
            ->setReplace($row->bReplace);

        $areaData = json_decode($row->cAreasJson, true);

        if ($areaData !== null) {
            $page->getAreaList()->deserialize($areaData);
        }

        return $page;
    }
}
