<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Link;

use DB\DbInterface;
use DB\ReturnType;
use function Functional\group;
use Tightenco\Collect\Support\Collection;

/**
 * Class LinkGroupList
 * @package Filter
 */
class LinkGroupList implements LinkGroupListInterface
{
    /**
     * @var LinkGroupCollection
     */
    protected $linkgroups;

    /**
     * @var DbInterface
     */
    protected $db;

    /**
     * @var Collection
     */
    protected $visibleGroups;

    /**
     * LinkGroupList constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db            = $db;
        $this->linkgroups    = new Collection();
        $this->visibleGroups = new Collection();
    }

    /**
     * @param string $name
     * @return LinkGroupInterface|null
     */
    public function __get($name)
    {
        trigger_error(__CLASS__ . ': getter should be used to get ' . $name, E_USER_DEPRECATED);

        return $this->getLinkgroupByTemplate($name);
    }

    /**
     * @param mixed $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        trigger_error(__CLASS__ . ': setting data like this not supported anymore. ', E_USER_DEPRECATED);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->__get($name) !== null;
    }

    /**
     * @inheritdoc
     */
    public function loadAll(): LinkGroupListInterface
    {
        if ($this->linkgroups->count() > 0) {
            return $this;
        }
        $cache = \Shop::Container()->getCache();
        if (true || ($this->linkgroups = $cache->get('linkgroups')) === false) {
            $this->linkgroups = new LinkGroupCollection();
            $groupLanguages   = $this->db->query(
                'SELECT tlinkgruppesprache.*, tlinkgruppe.cTemplatename AS template, tsprache.kSprache 
                    FROM tlinkgruppe
                    JOIN tlinkgruppesprache
                        ON tlinkgruppe.kLinkgruppe = tlinkgruppesprache.kLinkgruppe
                    JOIN tsprache 
                        ON tsprache.cISO = tlinkgruppesprache.cISOSprache
                    WHERE tlinkgruppe.kLinkgruppe > 0',
                ReturnType::ARRAY_OF_OBJECTS
            );
            $grouped          = group($groupLanguages, function ($e) {
                return $e->kLinkgruppe;
            });
            foreach ($grouped as $linkGroupID => $localizedLinkgroup) {
                $lg = new LinkGroup($this->db);
                $lg->setID($linkGroupID);
                $this->linkgroups->push($lg->map($localizedLinkgroup));
            }
            $staticRoutes = $this->db->query(
                "SELECT tspezialseite.kSpezialseite, tspezialseite.cName AS baseName, tspezialseite.cDateiname, 
                        tspezialseite.nLinkart, tlink.kLink, 
                        tlinksprache.cName AS localizedName,
                        tlinksprache.cTitle AS localizedTitle,
                        tlinksprache.cContent AS content,
                        tlinksprache.cMetaDescription AS metaDescription,
                        tlinksprache.cMetaKeywords AS metaKeywords,
                        tlinksprache.cMetaTitle AS metaTitle,
                        tlink.cKundengruppen, 
                        tseo.cSeo AS localizedUrl, 
                        tsprache.cISO AS cISOSprache, tsprache.kSprache AS languageID, 
                        tlink.kVaterLink, tspezialseite.kPlugin, 
                        tlink.kLinkgruppe, tlink.cName, tlink.cNoFollow, tlink.cSichtbarNachLogin, tlink.cDruckButton, 
                        tlink.nSort, tlink.bIsActive, tlink.bIsFluid, tlink.bSSL,
                        2 AS pluginState
                    FROM tspezialseite
                        LEFT JOIN tlink 
                            ON tlink.nLinkart = tspezialseite.nLinkart
                        LEFT JOIN tlinksprache 
                            ON tlink.kLink = tlinksprache.kLink
                        JOIN tsprache 
                            ON tsprache.cISO = tlinksprache.cISOSprache
                        LEFT JOIN tseo 
                            ON tseo.cKey = 'kLink' 
                            AND tseo.kKey = tlink.kLink 
                            AND tseo.kSprache = tsprache.kSprache
                    WHERE cDateiname IS NOT NULL 
                        AND cDateiname != ''",
                ReturnType::ARRAY_OF_OBJECTS
            );
            $grouped      = group($staticRoutes, function ($e) {
                return $e->kLink;
            });
            $lg           = new LinkGroup($this->db);
            $lg->setID(999);
            $lg->setNames(['staticroutes']);
            $lg->setTemplate('staticroutes');
            $links = new Collection();
            foreach ($grouped as $linkID => $linkData) {
                $link = new Link($this->db);
                $link->map($linkData);
                $links->push($link);
            }
            $lg->setLinks($links);
            $this->linkgroups->push($lg);

            $specialPages = $this->db->query(
                "SELECT tlink.*,tlinksprache.cISOSprache, 
                tlinksprache.cName AS localizedName, 
                tlinksprache.cTitle AS localizedTitle, 
                tlinksprache.kSprache, 
                tlinksprache.cContent AS content,
                tlinksprache.cMetaDescription AS metaDescription,
                tlinksprache.cMetaKeywords AS metaKeywords,
                tlinksprache.cMetaTitle AS metaTitle,
                tseo.kSprache AS languageID,
                tseo.cSeo AS localizedUrl,
                tspezialseite.cDateiname,
                2 AS pluginState
                    FROM tlinksprache
                    JOIN tlink
                        ON tlink.kLink = tlinksprache.kLink
                    JOIN tsprache
                        ON tsprache.cISO = tlinksprache.cISOSprache
                    LEFT JOIN tseo
                        ON tseo.cKey = 'kLink'
                        AND tseo.kKey = tlink.kLink
                        AND tseo.kSprache = tsprache.kSprache
                    LEFT JOIN tspezialseite
						ON tspezialseite.nLinkart = tlink.nLinkart
                    WHERE tlink.kLink = tlinksprache.kLink
                        AND tlink.nLinkart >= 5",
                ReturnType::ARRAY_OF_OBJECTS
            );

            $grouped = group($specialPages, function ($e) {
                return $e->kLink;
            });
            $lg      = new LinkGroup($this->db);
            $lg->setID(998);
            $lg->setNames(['specialpages']);
            $lg->setTemplate('specialpages');
            $links = new Collection();
            foreach ($grouped as $linkID => $linkData) {
                $link = new Link($this->db);
                $link->map($linkData);
                $links->push($link);
            }
            $lg->setLinks($links);
            $this->linkgroups->push($lg);

            $cache->set('linkgroups', $this->linkgroups, [CACHING_GROUP_CORE]);
        }
        $this->applyVisibilityFilter();

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLinkgroups(): LinkGroupCollection
    {
        return $this->linkgroups;
    }

    /**
     * @inheritdoc
     */
    public function setLinkgroups(Collection $linkgroups)
    {
        $this->linkgroups = $linkgroups;
    }

    /**
     * @inheritdoc
     */
    public function getVisibleLinkgroups(): Collection
    {
        return $this->visibleGroups;
    }

    /**
     * @inheritdoc
     */
    public function setVisiblyLinkgroups(Collection $linkgroups)
    {
        $this->visibleGroups = $linkgroups;
    }

    /**
     * @inheritdoc
     */
    public function applyVisibilityFilter(): LinkGroupListInterface
    {
        $customerGroupID = \Session::CustomerGroup()->getID();
        $customerID      = \Session::Customer()->getID();
        //@todo: remove clone hack!
        $this->visibleGroups = clone $this->linkgroups;
        foreach ($this->linkgroups as $i => $linkgroup) {
            /** @var LinkGroupInterface $linkgroup */
            $this->visibleGroups[$i] = clone $linkgroup;
        }
        foreach ($this->visibleGroups as $linkgroup) {
            /** @var LinkGroupInterface $linkgroup */
            $linkgroup->setLinks($linkgroup->getLinks()
                                           ->filter(function (LinkInterface $l) use ($customerID, $customerGroupID) {
                                               return $l->isVisible($customerGroupID, $customerID);
                                           }));
        }

        return $this;
    }


    /**
     * @former aktiviereLinks()
     * @param int $pageType
     * @return LinkGroupCollection
     */
    public function activate(int $pageType): LinkGroupCollection
    {
        $helper = LinkHelper::getInstance();
        foreach ($this->linkgroups as $linkgroup) {
            /** @var LinkGroupInterface $linkgroup */
            $links = $linkgroup->getLinks();
            $cnt   = $links->count();
            foreach ($links as $link) {
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
                        if ($parent === 0 && $helper->isChildActive($linkID, \Shop::$kLink)) {
                            $link->setIsActive(true);
                        }
                        if ($linkID === \Shop::$kLink) {
                            $link->setIsActive(true);
                            $parent   = $helper->getRootLink($linkID);
                            $filtered = $linkgroup->getLinks()->filter(function (LinkInterface $l) use ($parent) {
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

        return $this->linkgroups;
    }

    /**
     * @inheritdoc
     */
    public function getLinkgroupByTemplate(string $name, $filtered = true)
    {
        $source = $filtered ? $this->visibleGroups : $this->linkgroups;

        return $source->filter(function (LinkGroupInterface $e) use ($name) {
            return $e->getTemplate() === $name;
        })->first();
    }

    /**
     * @inheritdoc
     */
    public function getLinkgroupByID(int $id, $filtered = true)
    {
        $source = $filtered ? $this->visibleGroups : $this->linkgroups;

        return $source->filter(function (LinkGroupInterface $e) use ($id) {
            return $e->getID() === $id;
        })->first();
    }
}
