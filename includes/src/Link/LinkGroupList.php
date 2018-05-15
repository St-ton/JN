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
final class LinkGroupList implements LinkGroupListInterface
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var LinkGroupCollection
     */
    private $linkGroups;

    /**
     * @var LinkGroupCollection
     */
    private $visibleGroups;

    /**
     * LinkGroupList constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db            = $db;
        $this->linkGroups    = new LinkGroupCollection();
        $this->visibleGroups = new LinkGroupCollection();
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
        if ($this->linkGroups->count() > 0) {
            return $this;
        }
        $cache = \Shop::Container()->getCache();
        if (($this->linkGroups = $cache->get('linkgroups')) === false) {
            $this->linkGroups = new LinkGroupCollection();
            foreach ($this->loadStandardGroups() as $group) {
                $this->linkGroups->push($group);
            }
            $this->linkGroups->push($this->loadSpecialPages());
            $this->linkGroups->push($this->loadStaticRoutes());

            $cache->set('linkgroups', $this->linkGroups, [CACHING_GROUP_CORE]);
        }
        $this->applyVisibilityFilter();

        return $this;
    }

    /**
     * @return LinkGroupInterface[]
     */
    private function loadStandardGroups(): array
    {
        $groups         = [];
        $groupLanguages = $this->db->query(
            'SELECT tlinkgruppesprache.*, tlinkgruppe.cTemplatename AS template, tsprache.kSprache 
                FROM tlinkgruppe
                JOIN tlinkgruppesprache
                    ON tlinkgruppe.kLinkgruppe = tlinkgruppesprache.kLinkgruppe
                JOIN tsprache 
                    ON tsprache.cISO = tlinkgruppesprache.cISOSprache
                WHERE tlinkgruppe.kLinkgruppe > 0',
            ReturnType::ARRAY_OF_OBJECTS
        );
        $grouped        = group($groupLanguages, function ($e) {
            return $e->kLinkgruppe;
        });
        foreach ($grouped as $linkGroupID => $localizedLinkgroup) {
            $lg = new LinkGroup($this->db);
            $lg->setID($linkGroupID);
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
            if ($link->getLinkType() === LINKTYP_DATENSCHUTZ) {
                $this->linkGroups->Link_Datenschutz = [];
                foreach ($link->getURLs() as $langID => $url) {
                    $this->linkGroups->Link_Datenschutz[$link->getLanguageCode($langID)] = $url;
                }
            } elseif ($link->getLinkType() === LINKTYP_AGB) {
                $this->linkGroups->Link_AGB = [];
                foreach ($link->getURLs() as $langID => $url) {
                    $this->linkGroups->Link_AGB[$link->getLanguageCode($langID)] = $url;
                }
            } elseif ($link->getLinkType() === LINKTYP_VERSAND) {
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
    public function setLinkGroups(Collection $linkGroups)
    {
        $this->linkGroups = $linkGroups;
    }

    /**
     * @inheritdoc
     */
    public function getVisibleLinkGroups(): Collection
    {
        return $this->visibleGroups;
    }

    /**
     * @inheritdoc
     */
    public function setVisibleLinkGroups(Collection $linkGroups)
    {
        $this->visibleGroups = $linkGroups;
    }

    /**
     * @inheritdoc
     */
    public function applyVisibilityFilter(): LinkGroupListInterface
    {
        $customerGroupID = \Session::CustomerGroup()->getID();
        $customerID      = \Session::Customer()->getID();
        //@todo: remove clone hack!
        $this->visibleGroups = clone $this->linkGroups;
        foreach ($this->linkGroups as $i => $linkGroup) {
            /** @var LinkGroupInterface $linkGroup */
            $this->visibleGroups[$i] = clone $linkGroup;
        }
        foreach ($this->visibleGroups as $linkGroup) {
            /** @var LinkGroupInterface $linkGroup */
            $linkGroup->setLinks($linkGroup->getLinks()
                                           ->filter(function (LinkInterface $l) use ($customerID, $customerGroupID) {
                                               return $l->isVisible($customerGroupID, $customerID);
                                           }));
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLinkgroupByTemplate(string $name, $filtered = true)
    {
        $source = $filtered ? $this->visibleGroups : $this->linkGroups;

        return $source->getLinkgroupByTemplate($name);
    }

    /**
     * @inheritdoc
     */
    public function getLinkgroupByID(int $id, $filtered = true)
    {
        $source = $filtered ? $this->visibleGroups : $this->linkGroups;

        return $source->getLinkgroupByID($id);
    }
}
