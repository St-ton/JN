<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL;


use DB\DbInterface;
use function Functional\first;
use function Functional\first_index_of;
use function Functional\select;
use Link\LinkGroupCollection;
use Link\LinkGroupInterface;
use Link\LinkGroupList;
use Link\LinkGroupListInterface;
use Link\LinkInterface;
use Link\Link;
use Tightenco\Collect\Support\Collection;

/**
 * Class LinkService
 * @package Link
 */
final class LinkService implements LinkServiceInterface
{
    /**
     * @var LinkService
     */
    private static $_instance;

    /**
     * @var LinkGroupListInterface
     */
    public $linkGroups;

    /**
     * @var LinkGroupListInterface
     */
    private $linkGroupList;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * LinkService constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db            = $db;
        self::$_instance     = $this;
        $this->linkGroupList = new LinkGroupList($this->db);
        $this->initLinkGroups();
    }

    /**
     * @inheritdoc
     */
    public static function getInstance(): LinkServiceInterface
    {
        return self::$_instance ?? new self(\Shop::Container()->getDB());
    }

    /**
     * @inheritdoc
     */
    public function getLinkGroups(): LinkGroupCollection
    {
        return $this->linkGroupList->getVisibleLinkgroups();
    }

    /**
     * @inheritdoc
     */
    public function getVisibleLinkGroups(): LinkGroupCollection
    {
        return $this->getLinkGroups();
    }

    /**
     * @inheritdoc
     */
    public function getAllLinkGroups(): LinkGroupCollection
    {
        return $this->linkGroups;
    }

    /**
     * @inheritdoc
     */
    public function initLinkGroups(): LinkGroupCollection
    {
        $this->linkGroups = $this->linkGroupList->loadAll()->getLinkGroups();

        return $this->linkGroupList->getVisibleLinkgroups();
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
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
     * @inheritdoc
     */
    public function getParentIDs(int $id): array
    {
        $result = [];
        $link   = $this->getParentForID($id);

        while ($link !== null && $link->getID() > 0) {
            array_unshift($result, $link->getID());
            $link = $this->getLinkByID($link->getParent());
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getParentLinks(int $id): Collection
    {
        $result = new Collection();
        $link   = $this->getParentForID($id);

        while ($link !== null && $link->getID() > 0) {
            $result->push($link);
            $link = $this->getLinkByID($link->getParent());
        }

        return $result;
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function isDirectChild(int $parentLinkID, int $linkID): bool
    {
        if ($parentLinkID > 0) {
            foreach ($this->linkGroups as $linkGroup) {
                /** @var LinkGroupInterface $linkGroup */
                foreach ($linkGroup->getLinks() as $link) {
                    /** @var LinkInterface $link */
                    if ($link->getID() === $linkID && $link->getParent() === $parentLinkID) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function getLinkObjectByID(int $id): LinkInterface
    {
        $link = new Link($this->db);

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
            ? $lg->getLinks()->first(function (LinkInterface $l) use ($nLinkart) {
                return $l->getLinkType() === $nLinkart;
            })
            : null;
    }

    /**
     * @inheritdoc
     */
    public function getSpecialPageID($nLinkart)
    {
        $link = $this->getSpecialPage($nLinkart);

        return $link === null ? false : $link->getID();
    }

    /**
     * @inheritdoc
     */
    public function getSpecialPageLinkKey($nLinkart)
    {
        return $this->getSpecialPageID($nLinkart);
    }

    /**
     * @inheritdoc
     */
    public function getLinkGroupByName(string $name)
    {
        return $this->linkGroupList->getLinkgroupByTemplate($name);
    }

    /**
     * @inheritdoc
     */
    public function getLinkGroupByID(int $id)
    {
        return $this->linkGroupList->getLinkgroupByID($id);
    }

    /**
     * @inheritdoc
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

    /**
     * @inheritdoc
     */
    public function getSpecialPages(): Collection
    {
        $lg = $this->getLinkGroupByName('specialpages'); //@todo: use const
        if ($lg !== null) {
            return $lg->getLinks()->groupBy(function (LinkInterface $link) {
                return $link->getLinkType();
            })->map(function (Collection $group) {
                return $group->first();
            });
        }

        return new Collection();
    }

    /**
     * @inheritdoc
     */
    public function getPageLinkLanguage(int $id)
    {
        return $this->getLinkByID($id);
    }

    /**
     * @inheritdoc
     */
    public function getPageLink(int $id)
    {
        return $this->getLinkByID($id);
    }

    /**
     * @inheritdoc
     */
    public function getLinkObject(int $id)
    {
        return $this->getLinkByID($id);
    }

    /**
     * @inheritdoc
     */
    public function findCMSLinkInSession(int $id, int $pluginID = 0)
    {
        $link = $this->getLinkByID($id);

        return $pluginID === 0 || ($link !== null && $link->getPluginID() === $pluginID)
            ? $link
            : null;
    }

    /**
     * @inheritdoc
     */
    public function isChildActive(int $parentLinkID, int $linkID): bool
    {
        return $this->isDirectChild($parentLinkID, $linkID);
    }

    /**
     * @inheritdoc
     */
    public function getRootLink(int $id)
    {
        return $this->getRootID($id);
    }

    /**
     * @inheritdoc
     */
    public function getParentsArray(int $id): array
    {
        return $this->getParentIDs($id);
    }

    /**
     * @inheritdoc
     */
    public function getParent(int $id)
    {
        return $this->getLinkByID($id);
    }

    /**
     * @inheritdoc
     */
    public function buildSpecialPageMeta(int $type, string $cISOSprache = null): \stdClass
    {
        $first = null;
        foreach ($this->linkGroups as $linkGroup) {
            /** @var LinkGroupInterface $linkGroup */
            $first = $linkGroup->getLinks()->first(function (LinkInterface $link) use ($type) {
                return $link->getLinkType() === $type;
            });
        }
        if ($cISOSprache !== null) {
            $shopISO = \Shop::getLanguageCode();
            if ($shopISO !== null && strlen($shopISO) > 0) {
                $cISOSprache = $shopISO;
            } else {
                $oSprache    = gibStandardsprache();
                $cISOSprache = $oSprache->cISO;
            }
        }
        $oMeta            = new \stdClass();
        $oMeta->cTitle    = '';
        $oMeta->cDesc     = '';
        $oMeta->cKeywords = '';

        if ($first !== null) {
            /** @var LinkInterface $first */
            $oMeta->cTitle    = $first->getMetaTitle();
            $oMeta->cDesc     = $first->getMetaDescription();
            $oMeta->cKeywords = $first->getMetaKeyword();
        }

        return $oMeta;
    }

    /**
     * @inheritdoc
     */
    public function checkNoIndex(): bool
    {
        return \Shop::getProductFilter()->getMetaData()->checkNoIndex();
    }

    /**
     * @inheritdoc
     */
    public function activate(int $pageType): LinkGroupCollection
    {
        foreach ($this->linkGroups as $linkGroup) {
            /** @var LinkGroupInterface $linkGroup */
            foreach ($linkGroup->getLinks() as $link) {
                /** @var LinkInterface $link */
                $link->setIsActive(false);
                $linkType = $link->getLinkType();
                $linkID   = $link->getID();
                switch ($pageType) {
                    case PAGE_STARTSEITE:
                        if ($linkType === LINKTYP_STARTSEITE) {
                            $link->setIsActive(true);
                        }
                        break;
                    case PAGE_ARTIKEL:
                    case PAGE_ARTIKELLISTE:
                    case PAGE_BESTELLVORGANG:
                        break;
                    case PAGE_EIGENE:
                        $parent = $link->getParent();
                        if ($parent === 0 && $this->isChildActive($linkID, \Shop::$kLink)) {
                            $link->setIsActive(true);
                        }
                        if ($linkID === \Shop::$kLink) {
                            $link->setIsActive(true);
                            $parent   = $this->getRootLink($linkID);
                            $filtered = $linkGroup->getLinks()->filter(function (LinkInterface $l) use ($parent) {
                                return $l->getID() === $parent;
                            })->map(function (LinkInterface $l) {
                                $l->setIsActive(true);

                                return $l;
                            });
                        }
                        break;
                    case PAGE_WARENKORB:
                        if ($linkType === LINKTYP_WARENKORB) {
                            $link->setIsActive(true);
                        }
                        break;
                    case PAGE_LOGIN:
                    case PAGE_MEINKONTO:
                        if ($linkType === LINKTYP_LOGIN) {
                            $link->setIsActive(true);
                        }
                        break;
                    case PAGE_REGISTRIERUNG:
                        if ($linkType === LINKTYP_REGISTRIEREN) {
                            $link->setIsActive(true);
                        }
                        break;
                    case PAGE_PASSWORTVERGESSEN:
                        if ($linkType === LINKTYP_PASSWORD_VERGESSEN) {
                            $link->setIsActive(true);
                        }
                        break;
                    case PAGE_KONTAKT:
                        if ($linkType === LINKTYP_KONTAKT) {
                            $link->setIsActive(true);
                        }
                        break;
                    case PAGE_NEWSLETTER:
                        if ($linkType === LINKTYP_NEWSLETTER) {
                            $link->setIsActive(true);
                        }
                        break;
                    case PAGE_UMFRAGE:
                        if ($linkType === LINKTYP_UMFRAGE) {
                            $link->setIsActive(true);
                        }
                        break;
                    case PAGE_NEWS:
                        if ($linkType === LINKTYP_NEWS) {
                            $link->setIsActive(true);
                        }
                        break;
                    default:
                        break;
                }
            }
        }

        return $this->linkGroups;
    }
}
