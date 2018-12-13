<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Link;

use Cache\JTLCacheInterface;
use DB\DbInterface;
use DB\ReturnType;
use Session\Session;
use Tightenco\Collect\Support\Collection;
use function Functional\group;

/**
 * Class LinkGroupList
 * @package Link
 */
final class LinkGroupList implements LinkGroupListInterface
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var JTLCacheInterface
     */
    private $cache;

    /**
     * @var LinkGroupCollection
     */
    private $linkGroups;

    /**
     * @var LinkGroupCollection
     */
    private $visibleLinkGroups;

    /**
     * LinkGroupList constructor.
     * @param DbInterface       $db
     * @param JTLCacheInterface $cache
     */
    public function __construct(DbInterface $db, JTLCacheInterface $cache)
    {
        $this->db                = $db;
        $this->cache             = $cache;
        $this->linkGroups        = new LinkGroupCollection();
        $this->visibleLinkGroups = new LinkGroupCollection();
    }

    /**
     * @param string $name
     * @return LinkGroupInterface|null
     */
    public function __get($name)
    {
        \trigger_error(__CLASS__ . ': getter should be used to get ' . $name, \E_USER_DEPRECATED);

        return $this->getLinkgroupByTemplate($name);
    }

    /**
     * @param mixed $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        \trigger_error(__CLASS__ . ': setting data like this not supported anymore. ', \E_USER_DEPRECATED);
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
        if ($this->linkGroups->count() > 0) {
            return $this;
        }
        if (($this->linkGroups = $this->cache->get('linkgroups')) === false) {
            $this->linkGroups = new LinkGroupCollection();
            foreach ($this->loadDefaultGroups() as $group) {
                $this->linkGroups->push($group);
            }
            $this->linkGroups->push($this->loadSpecialPages());
            $this->linkGroups->push($this->loadStaticRoutes());
            $this->linkGroups->push($this->loadUnassignedGroups());

            $this->cache->set('linkgroups', $this->linkGroups, [\CACHING_GROUP_CORE]);
        }
        $this->applyVisibilityFilter(Session::getCustomerGroup()->getID(), \Session::getCustomer()->getID());

        return $this;
    }

    /**
     * @return LinkGroupInterface
     */
    private function loadUnassignedGroups(): LinkGroupInterface
    {
        $unassigned = $this->db->query(
            "SELECT tlink.*,tlinksprache.cISOSprache, 
                tlink.cName AS displayName, 
                tlinksprache.cName AS localizedName, 
                tlinksprache.cTitle AS localizedTitle, 
                tsprache.kSprache, 
                tlinksprache.cContent AS content,
                tlinksprache.cMetaDescription AS metaDescription,
                tlinksprache.cMetaKeywords AS metaKeywords,
                tlinksprache.cMetaTitle AS metaTitle,
                tseo.kSprache AS languageID,
                tseo.cSeo AS localizedUrl,
                '' AS cDateiname,
                '' AS linkGroups,
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
                    WHERE tlink.kLink NOT IN (SELECT linkID FROM tlinkgroupassociations)
                    GROUP BY tlink.kLink, tsprache.kSprache",
            ReturnType::ARRAY_OF_OBJECTS
        );

        $grouped = group($unassigned, function ($e) {
            return $e->kLink;
        });
        $lg      = new LinkGroup($this->db);
        $lg->setID(-1);
        $lg->setNames(['unassigned']);
        $lg->setTemplate('unassigned');
        $links = new Collection();
        foreach ($grouped as $linkID => $linkData) {
            $link = new Link($this->db);
            $link->map($linkData);
            if ($link->getLinkType() === \LINKTYP_DATENSCHUTZ) {
                $this->linkGroups->Link_Datenschutz = [];
                foreach ($link->getURLs() as $langID => $url) {
                    $this->linkGroups->Link_Datenschutz[$link->getLanguageCode($langID)] = $url;
                }
            } elseif ($link->getLinkType() === \LINKTYP_AGB) {
                $this->linkGroups->Link_AGB = [];
                foreach ($link->getURLs() as $langID => $url) {
                    $this->linkGroups->Link_AGB[$link->getLanguageCode($langID)] = $url;
                }
            } elseif ($link->getLinkType() === \LINKTYP_VERSAND) {
                $this->linkGroups->Link_Versandseite = [];
                foreach ($link->getURLs() as $langID => $url) {
                    $this->linkGroups->Link_Versandseite[$link->getLanguageCode($langID)] = $url;
                }
            }
            $links->push($link);
        }
        $lg->setLinks($links);

        return $lg;
    }

    /**
     * @return LinkGroupInterface[]
     */
    private function loadDefaultGroups(): array
    {
        $groups         = [];
        $groupLanguages = $this->db->query(
            'SELECT tlinkgruppesprache.*, tlinkgruppe.cTemplatename AS template, tsprache.kSprache 
                FROM tlinkgruppe
                JOIN tlinkgruppesprache
                    ON tlinkgruppe.kLinkgruppe = tlinkgruppesprache.kLinkgruppe
                JOIN tsprache 
                    ON tsprache.cISO = tlinkgruppesprache.cISOSprache
                WHERE tlinkgruppe.kLinkgruppe > 0 AND tlinkgruppesprache.kLinkgruppe > 0',
            ReturnType::ARRAY_OF_OBJECTS
        );
        $grouped        = group($groupLanguages, function ($e) {
            return $e->kLinkgruppe;
        });
        foreach ($grouped as $linkGroupID => $localizedLinkgroup) {
            $lg = new LinkGroup($this->db);
            $lg->setID($linkGroupID);
            $lg->setIsSpecial(false);
            $groups[] = $lg->map($localizedLinkgroup);
        }

        return $groups;
    }

    /**
     * @return LinkGroupInterface
     */
    private function loadSpecialPages(): LinkGroupInterface
    {
        $specialPages = $this->db->query(
            "SELECT tlink.*,tlinksprache.cISOSprache, 
                tlink.cName AS displayName, 
                tlinksprache.cName AS localizedName, 
                tlinksprache.cTitle AS localizedTitle, 
                tsprache.kSprache, 
                tlinksprache.cContent AS content,
                tlinksprache.cMetaDescription AS metaDescription,
                tlinksprache.cMetaKeywords AS metaKeywords,
                tlinksprache.cMetaTitle AS metaTitle,
                tseo.kSprache AS languageID,
                tseo.cSeo AS localizedUrl,
                tspezialseite.cDateiname,
                GROUP_CONCAT(tlinkgroupassociations.linkGroupID) AS linkGroups,
                2 AS pluginState
                    FROM tlinksprache
                    JOIN tlink
                        ON tlink.kLink = tlinksprache.kLink
                    JOIN tsprache
                        ON tsprache.cISO = tlinksprache.cISOSprache
                    JOIN tlinkgroupassociations
					    ON tlinkgroupassociations.linkID = tlinksprache.kLink
                    LEFT JOIN tseo
                        ON tseo.cKey = 'kLink'
                        AND tseo.kKey = tlink.kLink
                        AND tseo.kSprache = tsprache.kSprache
                    LEFT JOIN tspezialseite
						ON tspezialseite.nLinkart = tlink.nLinkart
                    WHERE tlink.kLink = tlinksprache.kLink
                        AND tlink.nLinkart >= 5
                    GROUP BY tlink.kLink, tseo.kSprache",
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
            if ($link->getLinkType() === \LINKTYP_DATENSCHUTZ) {
                $this->linkGroups->Link_Datenschutz = [];
                foreach ($link->getURLs() as $langID => $url) {
                    $this->linkGroups->Link_Datenschutz[$link->getLanguageCode($langID)] = $url;
                }
            } elseif ($link->getLinkType() === \LINKTYP_AGB) {
                $this->linkGroups->Link_AGB = [];
                foreach ($link->getURLs() as $langID => $url) {
                    $this->linkGroups->Link_AGB[$link->getLanguageCode($langID)] = $url;
                }
            } elseif ($link->getLinkType() === \LINKTYP_VERSAND) {
                $this->linkGroups->Link_Versandseite = [];
                foreach ($link->getURLs() as $langID => $url) {
                    $this->linkGroups->Link_Versandseite[$link->getLanguageCode($langID)] = $url;
                }
            }
            $links->push($link);
        }
        $lg->setLinks($links);

        return $lg;
    }

    /**
     * @return LinkGroupInterface
     */
    private function loadStaticRoutes(): LinkGroupInterface
    {
        $staticRoutes = $this->db->query(
            "SELECT tspezialseite.kSpezialseite, tspezialseite.cName AS baseName, tspezialseite.cDateiname, 
                tspezialseite.nLinkart, tlink.kLink, 
                tlink.cName AS displayName,
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
                tlink.cName, tlink.cNoFollow, tlink.cSichtbarNachLogin, tlink.cDruckButton, 
                tlink.nSort, tlink.bIsActive, tlink.bIsFluid, tlink.bSSL,
                GROUP_CONCAT(tlinkgroupassociations.linkGroupID) AS linkGroups,
                2 AS pluginState
            FROM tspezialseite
                LEFT JOIN tlink 
                    ON tlink.nLinkart = tspezialseite.nLinkart
                LEFT JOIN tlinksprache 
                    ON tlink.kLink = tlinksprache.kLink
                JOIN tsprache 
                    ON tsprache.cISO = tlinksprache.cISOSprache
                JOIN tlinkgroupassociations
                    ON tlinkgroupassociations.linkID = tlinksprache.kLink
                LEFT JOIN tseo 
                    ON tseo.cKey = 'kLink' 
                    AND tseo.kKey = tlink.kLink 
                    AND tseo.kSprache = tsprache.kSprache
                WHERE cDateiname IS NOT NULL 
                    AND cDateiname != ''
                GROUP BY tlink.kLink, tsprache.kSprache",
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

        return $lg;
    }

    /**
     * @inheritdoc
     */
    public function getLinkGroups(): LinkGroupCollection
    {
        return $this->linkGroups;
    }

    /**
     * @inheritdoc
     */
    public function setLinkGroups(Collection $linkGroups): void
    {
        $this->linkGroups = $linkGroups;
    }

    /**
     * @inheritdoc
     */
    public function getVisibleLinkGroups(): LinkGroupCollection
    {
        return $this->visibleLinkGroups;
    }

    /**
     * @inheritdoc
     */
    public function setVisibleLinkGroups(LinkGroupCollection $linkGroups): void
    {
        $this->visibleLinkGroups = $linkGroups;
    }

    /**
     * @inheritdoc
     */
    public function applyVisibilityFilter(int $customerGroupID, int $customerID): LinkGroupListInterface
    {
        foreach ($this->linkGroups as $linkGroup) {
            /** @var LinkGroupInterface $linkGroup */
            $linkGroup->getLinks()->map(function (LinkInterface $l) use ($customerID, $customerGroupID) {
                $l->checkVisibility($customerGroupID, $customerID);

                return $l;
            });
            $filtered = clone $linkGroup;
            $filtered->filterLinks(function (LinkInterface $l) {
                return $l->isVisible();
            });
            $this->visibleLinkGroups->push($filtered);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLinkgroupByTemplate(string $name, $filtered = true): ?LinkGroupInterface
    {
        $source = $filtered ? $this->visibleLinkGroups : $this->linkGroups;

        return $source->getLinkgroupByTemplate($name);
    }

    /**
     * @inheritdoc
     */
    public function getLinkgroupByID(int $id, $filtered = true): ?LinkGroupInterface
    {
        $source = $filtered ? $this->visibleLinkGroups : $this->linkGroups;

        return $source->getLinkgroupByID($id);
    }
}
