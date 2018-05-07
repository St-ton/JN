<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Link;


use function Functional\first;
use function Functional\first_index_of;
use function Functional\select;

/**
 * Class LinkHelper
 * @package Link
 */
class LinkHelper
{
    /**
     * @var LinkHelper
     */
    private static $_instance;

    /**
     * @var LinkGroupListInterface
     */
    public $linkGroups;

    /**
     * @var LinkGroupListInterface
     */
    protected $linkGroupList;

    /**
     * LinkHelper constructor.
     */
    public function __construct()
    {
        self::$_instance = $this;
        $this->getLinkGroups();
    }

    /**
     * singleton
     *
     * @return LinkHelper
     */
    public static function getInstance(): self
    {
        return self::$_instance ?? new self();
    }

    /**
     * @return mixed|null
     */
    public function getLinkGroups()
    {
        $this->linkGroupList = new LinkGroupList(\Shop::Container()->getDB());
        $this->linkGroups    = $this->linkGroupList->loadAll()->getLinkgroups();

        return $this->linkGroups;
    }

    /**
     * @param int $id
     * @return LinkInterface|null
     */
    public function getLinkByID(int $id)
    {
        foreach ($this->linkGroups as $linkGroup) {
            /** @var LinkGroupInterface $linkGroup */
            $first = first($linkGroup->getLinks(), function (LinkInterface $link) use ($id) {
                return $link->getID() === $id;
            });
            if ($first !== null) {
                return $first;
            }
        }

        return null;
    }

    /**
     * @param int $id
     * @return LinkInterface|null
     */
    public function getParentForID(int $id)
    {
        foreach ($this->linkGroups as $linkGroup) {
            /** @var LinkGroupInterface $linkGroup */
            $first = first($linkGroup->getLinks(), function (LinkInterface $link) use ($id) {
                return $link->getID() === $id;
            });
            if ($first !== null) {
                return $this->getLinkByID($first->getParent());
            }
        }

        return null;
    }

    /**
     * @param int $id
     * @return int[]
     */
    public function getParentIDs(int $id): array
    {
        $result = [];
        $oLink  = $this->getParentForID($id);

        while ($oLink !== null && $oLink->getID() > 0) {
            array_unshift($result, $oLink->getID());
            $oLink = $this->getParentForID($oLink->getParent());
        }

        return $result;
    }

    /**
     * @param int $id
     * @return int|null
     */
    public function getRootID(int $id)
    {
        $res = null;
        while (($parent = $this->getParentForID($id)) !== null && $parent->getID() !== $id) {
            $id  = $parent->getID();
            $res = $parent;
        }
        if ($res === null) {
            $res = $this->getLinkByID($id);
        }

        return $res !== null ? $res->getID() : null;
    }

    /**
     * @param int $parentLinkID
     * @param int $linkID
     * @return bool
     */
    public function isDirectChild(int $parentLinkID, int $linkID): bool
    {
        if ($parentLinkID > 0) {
            foreach ($this->linkGroups as $linkGroup) {
                /** @var LinkGroupInterface $linkGroup */
                foreach ($linkGroup->getLinks() as $link) {
                    /** @var Link $link */
                    if ($link->getID() === $linkID && $link->getParent() === $parentLinkID) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param int $id
     * @return LinkInterface|null
     */
    public function getLinkObjectByID(int $id)
    {
        $link = new Link(\Shop::Container()->getDB());

        return $link->load($id);
    }

    /**
     * @former gibLinkKeySpecialSeite()
     * @param int $nLinkart
     * @return LinkInterface|null
     */
    public function getSpecialPage($nLinkart)
    {
        $lg = $this->getLinkGroupByName('specialpages');

        return $lg !== null
            ? first($lg->getLinks(), function (LinkInterface $l) use ($nLinkart) {
                return $l->getLinkType() === $nLinkart;
            })
            : null;
    }

    /**
     * @former gibLinkKeySpecialSeite()
     * @param int $nLinkart
     * @return int|bool
     */
    public function getSpecialPageID($nLinkart)
    {
        $link = $this->getSpecialPage($nLinkart);

        return $link === null ? false : $link->getID();
    }

    /**
     * for compatability only
     *
     * @former gibLinkKeySpecialSeite()
     * @param int $nLinkart
     * @return int|bool
     */
    public function getSpecialPageLinkKey($nLinkart)
    {
        return $this->getSpecialPageID($nLinkart);
    }

    /**
     * @param string $name
     * @return LinkGroupInterface|null
     */
    public function getLinkGroupByName(string $name)
    {
        return $this->linkGroupList->getLinkgroupByTemplate($name);
    }

    /**
     * @param string      $id
     * @param bool        $full
     * @param bool        $secure
     * @param string|null $langISO
     * @return string
     */
    public function getStaticRoute($id = 'kontakt.php', $full = true, $secure = false, $langISO = null): string
    {
        $idx = null;
        $lg  = $this->getLinkGroupByName('staticroutes');
        if ($lg !== null) {
            $filterd = $lg->getLinks()->first(function (LinkInterface $link) use ($id) {
                return $link->getFileName() === $id;
            });
            if ($filterd !== null) {
                if ($langISO !== null) {
                    $codes = $filterd->getLanguageCodes();
                    $idx   = first_index_of($codes, $langISO);
                }

                if ($idx !== false) {
                    return $filterd->getURL($idx);
                }
            }
        }

        return $full && strpos($id, 'http') !== 0
            ? \Shop::getURL($secure) . '/' . $id
            : $id;
    }

    public function getPageLinkLanguage(int $id)
    {
        return $this->getLinkByID($id);
    }

    /**
     * for compatability only
     *
     * @param int $id
     * @return LinkInterface|null
     */
    public function getLinkObject(int $id)
    {
        return $this->getLinkByID($id);
    }

    /**
     * for compatability only
     *
     * @param int $id
     * @param int $pluginID
     * @return LinkInterface|null
     */
    public function findCMSLinkInSession(int $id, int $pluginID = 0)
    {
        $link = $this->getLinkByID($id);

        return $pluginID === 0 || ($link !== null && $link->getPluginID() === $pluginID)
            ? $link
            : null;
    }

    /**
     * for compatability only
     *
     * @param int $parentLinkID
     * @param int $linkID
     * @return bool
     */
    public function isChildActive(int $parentLinkID, int $linkID): bool
    {
        return $this->isDirectChild($parentLinkID, $linkID);
    }

    /**
     * for compatability only
     *
     * @param int $id
     * @return int|null
     */
    public function getRootLink(int $id)
    {
        return $this->getRootID($id);
    }

    /**
     * for compatability only
     *
     * @param int $id
     * @return int[]
     */
    public function getParentsArray(int $id): array
    {
        return $this->getParentIDs($id);
    }

    /**
     * for compatability only
     * careful: does not do what it says it does.
     *
     * @param int $id
     * @return LinkInterface|null
     */
    public function getParent(int $id)
    {
        return $this->getLinkByID($id);
    }

    /**
     * @param int $pageType
     * @return LinkGroupCollection
     */
    public function activate(int $pageType): LinkGroupCollection
    {
        return $this->linkGroupList->activate($pageType);
    }
}
