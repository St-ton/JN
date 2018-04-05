<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class LinkHelper
 */
class LinkHelper
{
    /**
     * @var LinkHelper
     */
    private static $_instance;

    /**
     * the language ID which was used to generate $this->linkGroups
     * used for invalidation on lang switch
     *
     * @var int
     */
    private static $_langID = 0;

    /**
     * @var string|null
     */
    public $cacheID;

    /**
     * @var stdClass|null
     */
    public $linkGroups;

    /**
     * LinkHelper constructor.
     */
    public function __construct()
    {
        self::$_langID    = isset($_SESSION['kSprache']) ? (int)$_SESSION['kSprache'] : 0;
        $this->generateCacheID();
        $this->linkGroups = $this->getLinkGroups();
        self::$_instance  = $this;
    }

    /**
     * @return string
     */
    private function generateCacheID()
    {
        $this->cacheID    = 'lnkgrps' .
            Shop::Cache()->getBaseID(false, false, true, true, true, false) .
            (isset($_SESSION['Kunde']->kKunde) ? 'k' : '');

        return $this->cacheID;

    }

    /**
     * singleton
     *
     * @return LinkHelper
     */
    public static function getInstance()
    {
        return self::$_instance ?? new self();
    }

    /**
     * @return mixed|null
     */
    public function getLinkGroups()
    {
        if (isset($_SESSION['kSprache']) && (int)$_SESSION['kSprache'] !== self::$_langID) { // we had a lang switch event
            // update last used lang id
            self::$_langID = (int)$_SESSION['kSprache'];
            // create new cache ID with new lang ID
            $this->generateCacheID();
        } elseif ($this->linkGroups !== null) {
            // if we got matching language IDs, try to use class property
            return $this->linkGroups;
        }
        // try to load linkgroups from object cache
        if (($this->linkGroups = Shop::Cache()->get($this->cacheID)) === false) {
            return $this->buildLinkGroups(true);
        }

        return $this->linkGroups;
    }

    /**
     * save link groups to cache
     *
     * @param stdClass $linkGroups
     * @return mixed
     */
    public function setLinkGroups($linkGroups)
    {
        return Shop::Cache()->set($this->cacheID, $linkGroups, [CACHING_GROUP_CORE]);
    }

    /**
     * @param int $kParentLink
     * @param int $kLink
     * @return bool
     */
    public function isChildActive($kParentLink, $kLink)
    {
        $kLink       = (int)$kLink;
        $kParentLink = (int)$kParentLink;
        if ($kParentLink > 0) {
            $filtered = array_filter((array)$this->linkGroups, function ($l) {
                return isset($l->Links) && is_array($l->Links);
            });
            foreach ($filtered as $linkGroup) {
                foreach ($linkGroup->Links as $oLink) {
                    if ($oLink->kLink === $kLink && $oLink->kVaterLink === $kParentLink) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param int $kLink
     * @return int|null
     */
    public function getRootLink($kLink)
    {
        $kLink = (int)$kLink;
        if ($kLink > 0 && $this->linkGroups !== null) {
            $filtered = array_filter((array)$this->linkGroups, function ($l) {
                return isset($l->Links) && is_array($l->Links);
            });
            foreach ($filtered as $linkGroup) {
                foreach ($linkGroup->Links as $oLink) {
                    if ($oLink->kLink === $kLink) {
                        $kParentLink = (int)$oLink->kVaterLink;

                        return $kParentLink > 0
                            ? $this->getRootLink($kParentLink)
                            : $kLink;
                    }
                }
            }
        }

        return null;
    }

    /**
     * @param int $kParentLink
     * @return null|Link
     */
    public function getParent($kParentLink)
    {
        $kParentLink = (int)$kParentLink;
        if ($kParentLink > 0) {
            $filtered = array_filter((array)$this->linkGroups, function ($l) {
                return isset($l->Links) && is_array($l->Links);
            });
            foreach ($filtered as $linkGroup) {
                foreach ($linkGroup->Links as $oLink) {
                    if ($oLink->kLink === $kParentLink) {
                        return $oLink;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Gets an array of Link-IDs as a parent-chain
     *
     * @param int $kLink
     * @return array
     */
    public function getParentsArray($kLink)
    {
        $kLink  = (int)$kLink;
        $result = [];
        $oLink  = $this->getParent($kLink);

        while ($oLink !== null && $oLink->kLink > 0) {
            array_unshift($result, $oLink->kLink);
            $oLink = $this->getParent($oLink->kVaterLink);
        }

        return $result;
    }

    /**
     * @param int  $kParentLink
     * @param bool $bAssoc
     * @return array
     */
    public function getMyLevel($kParentLink, $bAssoc = false)
    {
        $kParentLink = (int)$kParentLink;
        $oLink_arr   = [];
        if ($kParentLink > 0) {
            $filtered = array_filter((array)$this->linkGroups, function ($l) {
                return isset($l->Links) && is_array($l->Links);
            });
            foreach ($filtered as $linkGroup) {
                foreach ($linkGroup->Links as $oLink) {
                    if ($oLink->kVaterLink === $kParentLink) {
                        if ($bAssoc) {
                            $oLink_arr[$oLink->kLink] = $oLink;
                        } else {
                            $oLink_arr[] = $oLink;
                        }
                    }
                }
            }
        }

        return $oLink_arr;
    }

    /**
     * @param object     $oLink
     * @param array|null $oLinkLvl_arr
     * @return mixed|null
     */
    public function getPrevious($oLink, $oLinkLvl_arr = null)
    {
        return $this->getPaging($oLink, $oLinkLvl_arr, 1);
    }

    /**
     * @param object     $oLink
     * @param array|null $oLinkLvl_arr
     * @return mixed|null
     */
    public function getNext($oLink, $oLinkLvl_arr = null)
    {
        return $this->getPaging($oLink, $oLinkLvl_arr, 2);
    }

    /**
     * @param object     $oLink
     * @param null|array $oLinkLvl_arr
     * @param int        $nEvent
     * @return mixed|null
     */
    protected function getPaging($oLink, $oLinkLvl_arr = null, $nEvent)
    {
        if (is_object($oLink) && isset($oLink->kVaterLink, $oLink->kLink)) {
            if ($oLinkLvl_arr === null) {
                $oLinkLvl_arr = $this->getMyLevel($oLink->kVaterLink);
            }
            foreach ($oLinkLvl_arr as $i => $lvl) {
                if ($lvl->kLink !== $oLink->kLink) {
                    continue;
                }
                switch ($nEvent) {
                    case 1: // Previous
                        if (isset($lvl[$i - 1])) {
                            return $lvl[$i - 1];
                        }
                        break;

                    case 2: // Next
                        if (isset($lvl[$i + 1])) {
                            return $lvl[$i + 1];
                        }
                        break;
                    default:
                        break;
                }
            }
        }

        return null;
    }

    /**
     * @param int $kLink
     * @return mixed
     */
    public function getLinkObject($kLink)
    {
        $kLink      = (int)$kLink;
        $cacheID    = 'linkobject';
        $linkObject = Shop::Cache()->get($cacheID);
        if ($linkObject === false) {
            $linkObject = [];
        }
        if (!isset($linkObject[$kLink])) {
            $linkObject[$kLink] = Shop::Container()->getDB()->select('tlink', 'kLink', $kLink);
            Shop::Cache()->set($cacheID, $linkObject, [CACHING_GROUP_CORE]);
        }

        return $linkObject[$kLink];
    }

    /**
     * @param bool $force
     * @return mixed|null|stdClass
     */
    public function buildLinkGroups($force = false)
    {
        if ($force === true) {
            $this->generateCacheID();
        }
        $linkGroups = $this->linkGroups;
        if ($linkGroups === null || !is_object($linkGroups) || $force === true) {
            $session = [];
            // fixes for admin backend
            $customerGroupID = isset($_SESSION['Kundengruppe'])
                ? Session::CustomerGroup()->getID()
                : Kundengruppe::getDefaultGroupID();
            $Linkgruppen     = Shop::Container()->getDB()->query("SELECT * FROM tlinkgruppe", 2);
            $linkGroups      = new stdClass();
            $shopURL         = Shop::getURL() . '/';
            $shopURLSSL      = Shop::getURL(true) . '/';
            foreach ($Linkgruppen as $Linkgruppe) {
                if (trim($Linkgruppe->cTemplatename) === '') {
                    continue;
                }
                $linkGroups->{$Linkgruppe->cTemplatename}              = new stdClass();
                $linkGroups->{$Linkgruppe->cTemplatename}->cName       = $Linkgruppe->cName;
                $linkGroups->{$Linkgruppe->cTemplatename}->kLinkgruppe = (int)$Linkgruppe->kLinkgruppe;

                $Linkgruppesprachen = Shop::Container()->getDB()->selectAll(
                    'tlinkgruppesprache',
                    'kLinkgruppe',
                    (int)$Linkgruppe->kLinkgruppe
                );
                foreach ($Linkgruppesprachen as $Linkgruppesprache) {
                    $linkGroups->{$Linkgruppe->cTemplatename}->cLocalizedName[$Linkgruppesprache->cISOSprache] =
                        $Linkgruppesprache->cName;
                }

                $loginSichtbarkeit = (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0)
                    ? ''
                    : " AND tlink.cSichtbarNachLogin = 'N' ";
                $linkData = Shop::Container()->getDB()->query(
                    "SELECT tlink.*, tplugin.nStatus AS nPluginStatus
                        FROM tlink
                        LEFT JOIN tplugin
                            ON tplugin.kPlugin = tlink.kPlugin
                        WHERE tlink.bIsActive = 1 
                            AND tlink.kLinkgruppe = " . (int)$Linkgruppe->kLinkgruppe . $loginSichtbarkeit . "
                            AND (tlink.cKundengruppen IS NULL
                            OR tlink.cKundengruppen = 'NULL'
                            OR FIND_IN_SET('{$customerGroupID}', REPLACE(tlink.cKundengruppen, ';', ',')) > 0)
                        ORDER BY tlink.nSort, tlink.cName", 2
                );
                $links = [];
                foreach ($linkData as $item) {
                    $link = new Link(null, $item);
                    // Deaktivierte Plugins, nicht als Link anzeigen
                    if ($link->kPlugin > 0 && (int)$item->nPluginStatus !== 2) {
                        continue;
                    }
                    $linkLanguages = Shop::Container()->getDB()->query(
                        "SELECT tlinksprache.cISOSprache, tlinksprache.cName, tlinksprache.cTitle, tseo.cSeo
                            FROM tlinksprache
                            JOIN tsprache
                                ON tsprache.cISO = tlinksprache.cISOSprache
                            LEFT JOIN tseo
                                ON tseo.cKey = 'kLink'
                                AND tseo.kKey = tlinksprache.kLink
                                AND tseo.kSprache = tsprache.kSprache
                            WHERE tlinksprache.kLink = " . $link->kLink . "
                            GROUP BY tlinksprache.cISOSprache", 2
                    );
                    if ($linkLanguages === false) {
                        $linkLanguages = [];
                    }
                    foreach ($linkLanguages as $Linksprache) {
                        $link->cLocalizedName[$Linksprache->cISOSprache]  = $Linksprache->cName;
                        $link->cLocalizedTitle[$Linksprache->cISOSprache] = $Linksprache->cTitle;
                        $link->cLocalizedSeo[$Linksprache->cISOSprache]   = $Linksprache->cSeo;
                    }
                    if ($link->nLinkart === LINKTYP_EXTERNE_URL) {
                        $link->URL      = $link->cURL;
                        $link->cURLFull = $link->cURL;
                    } else {
                        $link->URL      = baueURL($link, URLART_SEITE);
                        $link->cURLFull = $shopURL . $link->URL;
                        if ($link->bSSL === 2) {
                            // if link has forced ssl, modify cURLFull accordingly
                            $link->cURLFull = str_replace('http://', 'https://', $link->cURLFull);
                        }
                    }
                    $links[] = $link;
                }
                $links                                           = array_merge($links);
                $linkGroups->{$Linkgruppe->cTemplatename}->Links = $links;
            }
            // startseite
            $start_arr = Shop::Container()->getDB()->query(
                "SELECT tseo.cSeo, tlinksprache.cISOSprache, tlink.kLink
                    FROM tlinksprache
                    JOIN tlink
                        ON tlink.kLink = tlinksprache.kLink
                    JOIN tsprache
                        ON tsprache.cISO = tlinksprache.cISOSprache
                    LEFT JOIN tseo
                        ON tseo.cKey = 'kLink'
                        AND tseo.kKey = tlink.kLink
                        AND tseo.kSprache = tsprache.kSprache
                    WHERE tlink.kLink = tlinksprache.kLink
                        AND tlink.nLinkart = " . LINKTYP_STARTSEITE . "
                    GROUP BY tlinksprache.cISOSprache
                    ORDER BY tlink.kLink", 2
            );
            $session['Link_Startseite'] = [];

            $oSprache = gibStandardsprache();
            foreach ($start_arr as $start) {
                $session['Link_Startseite'][$start->cISOSprache] = '?s=' . (int)$start->kLink;
                if ($start->cSeo && strlen($start->cSeo) > 1) {
                    $session['Link_Startseite'][$start->cISOSprache] = $start->cSeo;
                    if ($start->cISOSprache === $oSprache->cISO) {
                        $session['Link_Startseite'][$start->cISOSprache] = $shopURL;
                    }
                }
            }
            // versand
            $cKundengruppenSQL = '';
            if (Session::CustomerGroup()->getID() > 0) {
                $cKundengruppenSQL = " AND (FIND_IN_SET('" . Session::CustomerGroup()->getID()
                    . "', REPLACE(tlink.cKundengruppen, ';', ',')) > 0
                    OR tlink.cKundengruppen IS NULL OR tlink.cKundengruppen = 'NULL' OR tlink.cKundengruppen = '')";
            }
            $versand_arr = Shop::Container()->getDB()->query(
                "SELECT tseo.cSeo, tlinksprache.cISOSprache, tlink.kLink
                    FROM tlinksprache
                    JOIN tlink
                        ON tlink.kLink = tlinksprache.kLink
                    JOIN tsprache
                        ON tsprache.cISO = tlinksprache.cISOSprache
                    LEFT JOIN tseo
                        ON tseo.cKey = 'kLink'
                        AND tseo.kKey = tlink.kLink
                        AND tseo.kSprache = tsprache.kSprache
                    WHERE tlink.kLink = tlinksprache.kLink
                        AND tlink.nLinkart = " . LINKTYP_VERSAND . $cKundengruppenSQL . "
                    GROUP BY tlinksprache.cISOSprache
                    ORDER BY tlink.kLink", 2
            );
            $session['Link_Versandseite'] = [];

            foreach ($versand_arr as $versand) {
                $session['Link_Versandseite'][$versand->cISOSprache] = '?s=' . (int)$versand->kLink;
                if ($versand->cSeo && strlen($versand->cSeo) > 1) {
                    $session['Link_Versandseite'][$versand->cISOSprache] = $versand->cSeo;
                }
            }
            // AGB
            $agb_arr = Shop::Container()->getDB()->query(
                "SELECT tseo.cSeo, tlinksprache.cISOSprache, tlink.kLink
                    FROM tlinksprache
                    JOIN tlink
                        ON tlink.kLink = tlinksprache.kLink
                    JOIN tsprache
                        ON tsprache.cISO = tlinksprache.cISOSprache
                    LEFT JOIN tseo
                        ON tseo.cKey = 'kLink'
                        AND tseo.kKey = tlink.kLink
                        AND tseo.kSprache = tsprache.kSprache
                    WHERE tlink.kLink = tlinksprache.kLink
                        AND tlink.nLinkart = " . LINKTYP_AGB . "
                    GROUP BY tlinksprache.cISOSprache
                    ORDER BY tlink.kLink", 2
            );

            $session['Link_AGB'] = [];

            foreach ($agb_arr as $agb) {
                $session['Link_AGB'][$agb->cISOSprache] = '?s=' . (int)$agb->kLink;
                if ($agb->cSeo && strlen($agb->cSeo) > 1) {
                    $session['Link_AGB'][$agb->cISOSprache] = $agb->cSeo;
                }
            }
            // Link_Datenschutz
            $agb_arr = Shop::Container()->getDB()->query(
                "SELECT tseo.cSeo, tlinksprache.cISOSprache, tlink.kLink
                    FROM tlinksprache
                    JOIN tlink
                        ON tlink.kLink = tlinksprache.kLink
                    JOIN tsprache
                        ON tsprache.cISO = tlinksprache.cISOSprache
                    LEFT JOIN tseo
                        ON tseo.cKey = 'kLink'
                        AND tseo.kKey = tlink.kLink
                        AND tseo.kSprache = tsprache.kSprache
                    WHERE tlink.kLink = tlinksprache.kLink
                        AND tlink.nLinkart = " . LINKTYP_DATENSCHUTZ . "
                    GROUP BY tlinksprache.cISOSprache
                    ORDER BY tlink.kLink", 2
            );

            $session['Link_Datenschutz'] = [];

            foreach ($agb_arr as $agb) {
                $session['Link_Datenschutz'][$agb->cISOSprache] = '?s=' . (int)$agb->kLink;
                if ($agb->cSeo && strlen($agb->cSeo) > 0) {
                    $session['Link_Datenschutz'][$agb->cISOSprache] = $agb->cSeo;
                }
            }
            $_SESSION['Link_Datenschutz']  = $session['Link_Datenschutz'];
            $_SESSION['Link_AGB']          = $session['Link_AGB'];
            $_SESSION['Link_Versandseite'] = $session['Link_Versandseite'];
            $linkGroups->Link_Datenschutz  = $session['Link_Datenschutz'];
            $linkGroups->Link_AGB          = $session['Link_AGB'];
            $linkGroups->Link_Versandseite = $session['Link_Versandseite'];

            $staticRoutes_arr = Shop::Container()->getDB()->query(
                "SELECT tspezialseite.kSpezialseite, tspezialseite.cName AS baseName, tspezialseite.cDateiname, 
                        tspezialseite.nLinkart, tlink.kLink, tlinksprache.cName AS seoName, tlink.cKundengruppen, 
                        tseo.cSeo, tsprache.cISO, tsprache.kSprache, tlink.kVaterLink, tspezialseite.kPlugin, 
                        tlink.kLinkgruppe, tlink.cName, tlink.cNoFollow, tlink.cSichtbarNachLogin, tlink.cDruckButton, 
                        tlink.nSort, tlink.bIsActive, tlink.bIsFluid, tlink.bSSL 
                    FROM tspezialseite
                        LEFT JOIN tlink 
                            ON tlink.nLinkart = tspezialseite.nLinkart
                        LEFT JOIN tlinksprache 
                            ON tlink.kLink = tlinksprache.kLink
                        LEFT JOIN tsprache 
                            ON tsprache.cISO = tlinksprache.cISOSprache
                        LEFT JOIN tseo 
                            ON tseo.cKey = 'kLink' 
                                AND tseo.kKey = tlink.kLink 
                                AND tseo.kSprache = tsprache.kSprache
                    WHERE cDateiname IS NOT NULL 
                        AND cDateiname != ''",
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
            $linkGroups->staticRoutes = [];
            foreach ($staticRoutes_arr as $sr) {
                if (empty($sr->cSeo)) {
                    continue;
                }
                $customerGroups  = (strpos($sr->cKundengruppen, ';') === false)
                    ? [$sr->cKundengruppen]
                    : explode(';', $sr->cKundengruppen);

                foreach ($customerGroups as $idx => &$customerGroup) {
                    if ($customerGroup === null || $customerGroup === 'NULL') {
                        $customerGroup = 0;
                    } elseif (empty($customerGroup)) {
                        unset($customerGroups[$idx]);
                    } else {
                        $customerGroup = (int)$customerGroup;
                    }
                }
                unset($customerGroup);
                $link = new Link(null, $sr);
                $link->setURLFull($shopURL . $link->cSeo)
                     ->setURLFullSSL($shopURLSSL . $link->cSeo);
                $link->customerGroups = $customerGroups;
                $currentIndex         = $sr->cDateiname;
                if (!isset($linkGroups->staticRoutes[$sr->cDateiname])) {
                    $linkGroups->staticRoutes[$currentIndex] = [];
                }
                if (!empty($link->cISO)) {
                    if (!isset($linkGroups->staticRoutes[$currentIndex][$link->cISO])) {
                        $linkGroups->staticRoutes[$currentIndex][$link->cISO] = [];
                    }
                    foreach ($customerGroups as $cg) {
                        $linkGroups->staticRoutes[$currentIndex][$link->cISO][$cg] = $link;
                    }
                } else {
                    foreach ($customerGroups as $cg) {
                        $linkGroups->staticRoutes[$currentIndex][$cg] = $link;
                    }
                }
            }
            $this->linkGroups = $linkGroups;
            executeHook(HOOK_BUILD_LINK_GROUPS, [
                'linkGroups' => &$linkGroups,
                'cached'     => false,
                'forced'     => $force
            ]);
            $this->setLinkGroups($linkGroups);

            return $this->linkGroups;
        }
        executeHook(HOOK_BUILD_LINK_GROUPS, [
            'linkGroups' => &$this->linkGroups,
            'cached'     => true,
            'forced'     => false
        ]);

        return $this->linkGroups;
    }

    /**
     * @former gibSpezialSeiten()
     * @return array|mixed
     */
    public function getSpecialPages()
    {
        $cISO    = Shop::getLanguage(true);
        $cacheID = 'special_pages_b_' . $cISO;
        if (($oSpeziallinks = Shop::Cache()->get($cacheID)) !== false) {
            return $oSpeziallinks;
        }
        $oSpeziallinks            = [];
        $_SESSION['Speziallinks'] = [];
        $oLink_arr                = Shop::Container()->getDB()->query(
            "SELECT kLink, nLinkart, cName 
                FROM tlink 
                WHERE nLinkart >= 5 
                ORDER BY nLinkart", 2
        );
        foreach ($oLink_arr as &$oLink) {
            $oObj           = new stdClass();
            $oObj->kLink    = (int)$oLink->kLink;
            $oObj->nLinkart = (int)$oLink->nLinkart;
            $oObj->cName    = $oLink->cName;
            $oLink          = $this->findCMSLinkInSession($oLink->kLink);
            $oObj->cURL     = $oLink->cURLFull ?? '';
            if (isset($oLink->cLocalizedName) && array_key_exists($cISO, $oLink->cLocalizedName)) {
                $oLink->cName = $oLink->cLocalizedName[$cISO];
            }
            $oSpeziallinks[$oObj->nLinkart] = $oObj;
        }
        unset($oLink);
        Shop::Cache()->set($cacheID, $oSpeziallinks, [CACHING_GROUP_CORE]);

        return $oSpeziallinks;
    }

    /**
     * @param int $kLink
     * @param int $kPlugin
     * @return stdClass
     */
    public function findCMSLinkInSession($kLink, $kPlugin = 0)
    {
        $kLink      = (int)$kLink;
        $kPlugin    = (int)$kPlugin;
        $linkGroups = $this->getLinkGroups();
        if ($linkGroups !== null) {
            // this can happen when there is a $_SESSION active and object cache is beeing flushed
            // since setzeLinks() is only executed in class.core.Session
            $linkGroups = setzeLinks();
        }
        if (($kLink > 0 || $kPlugin > 0) && $linkGroups !== null && is_object($linkGroups)) {
            foreach (array_keys(get_object_vars($linkGroups)) as $cMember) {
                if (isset($linkGroups->$cMember->Links)
                    && is_array($linkGroups->$cMember->Links)
                    && count($linkGroups->$cMember->Links) > 0
                ) {
                    foreach ($linkGroups->$cMember->Links as $oLink) {
                        if ($kLink > 0 && isset($oLink->kLink) && $oLink->kLink === $kLink) {
                            return $oLink;
                        }
                        if ($kPlugin > 0 && isset($oLink->kPlugin) && $oLink->kPlugin === $kPlugin) {
                            return $oLink;
                        }
                    }
                }
            }
        }

        return new stdClass();
    }

    /**
     * @return bool
     */
    public function checkNoIndex()
    {
        $productFilter = Shop::getProductFilter();
        $bNoIndex      = false;
        switch (basename($_SERVER['SCRIPT_NAME'])) {
            case 'wartung.php':
            case 'navi.php':
            case 'bestellabschluss.php':
            case 'bestellvorgang.php':
            case 'jtl.php':
            case 'pass.php':
            case 'registrieren.php':
            case 'warenkorb.php':
            case 'wunschliste.php':
                $bNoIndex = true;
                break;
            default:
                break;
        }
        if ($productFilter !== null && $productFilter->hasSearch()) {
            $bNoIndex = true;
        }
        if (!$bNoIndex) {
            $conf     = Shop::getSettings([CONF_GLOBAL]);
            $bNoIndex = $productFilter !== null
                && $productFilter->hasAttributeValue()
                && $productFilter->getAttributeValue()->getValue() > 0
                && isset($conf['global']['global_merkmalwert_url_indexierung'])
                && $conf['global']['global_merkmalwert_url_indexierung'] === 'N';
        }

        return $bNoIndex;
    }

    /**
     * gets (cached) linkgroup created by setzeLinks() and updates the current activate state
     * used in letzterInclude.php
     *
     * @former aktiviereLinks()
     * @param int $pageType
     * @return array
     */
    public function activate($pageType)
    {
        $linkGroups = $this->getLinkGroups();
        if ($linkGroups === null) {
            // this can happen when there is a $_SESSION active and object cache is beeing flushed
            // since setzeLinks() is only executed in class.core.Session
            $linkGroups = setzeLinks();
        }
        foreach ($linkGroups as $_name => $linkgruppe) {
            if (!isset($linkgruppe->Links) || !is_array($linkgruppe->Links)) {
                continue;
            }
            $linkgruppe->kVaterLinkAktiv = 0;

            $cnt = count($linkgruppe->Links);
            foreach ($linkgruppe->Links as $link) {
                $link->aktiv = 0;
                switch ($pageType) {
                    case PAGE_STARTSEITE:
                        if ($link->nLinkart === LINKTYP_STARTSEITE) {
                            $link->aktiv = 1;
                        }
                        break;
                    case PAGE_ARTIKEL:
                    case PAGE_ARTIKELLISTE:
                    case PAGE_BESTELLVORGANG:
                        break;
                    case PAGE_EIGENE:
                        // Hoechste Ebene
                        $kVaterLink = $link->kVaterLink;
                        if ($kVaterLink === 0 && $this->isChildActive($kVaterLink, Shop::$kLink)) {
                            $link->aktiv = 1;
                        }
                        if ($link->kLink === Shop::$kLink) {
                            $link->aktiv = 1;
                            $kVaterLink  = $this->getRootLink($link->kLink);
                            for ($j = 0; $j < $cnt; $j++) {
                                if ($linkgruppe->Links[$j]->kLink === $kVaterLink) {
                                    $linkgruppe->Links[$j]->aktiv = 1;
                                }
                            }
                        }
                        break;
                    case PAGE_WARENKORB:
                        if ($link->nLinkart === LINKTYP_WARENKORB) {
                            $link->aktiv = 1;
                        }
                        break;
                    case PAGE_LOGIN:
                    case PAGE_MEINKONTO:
                        if ($link->nLinkart === LINKTYP_LOGIN) {
                            $link->aktiv = 1;
                        }
                        break;
                    case PAGE_REGISTRIERUNG:
                        if ($link->nLinkart === LINKTYP_REGISTRIEREN) {
                            $link->aktiv = 1;
                        }
                        break;
                    case PAGE_PASSWORTVERGESSEN:
                        if ($link->nLinkart === LINKTYP_PASSWORD_VERGESSEN) {
                            $link->aktiv = 1;
                        }
                        break;
                    case PAGE_KONTAKT:
                        if ($link->nLinkart === LINKTYP_KONTAKT) {
                            $link->aktiv = 1;
                        }
                        break;
                    case PAGE_NEWSLETTER:
                        if ($link->nLinkart === LINKTYP_NEWSLETTER) {
                            $link->aktiv = 1;
                        }
                        break;
                    case PAGE_UMFRAGE:
                        if ($link->nLinkart === LINKTYP_UMFRAGE) {
                            $link->aktiv = 1;
                        }
                        break;
                    case PAGE_NEWS:
                        if ($link->nLinkart === LINKTYP_NEWS) {
                            $link->aktiv = 1;
                        }
                        break;
                    default:
                        break;
                }
            }
        }
        $this->linkGroups = $linkGroups;

        return $linkGroups;
    }


    /**
     * @param int $kLink
     * @return Link|stdClass
     */
    public function getPageLink($kLink)
    {
        $shopLangID = Shop::getLanguage();
        $kLink      = (int)$kLink;
        $cacheID    = 'page_' . $kLink . '_' . (Session::Customer()->getID() > 0
                ? 'vis'
                : 'nvis');
        if (($links = Shop::Cache()->get($cacheID)) !== false && is_array($links)) {
            foreach ($links as $link) {
                if ($link->kSprache === $shopLangID) {
                    return $link;
                }
            }
        }
        $urls  = [];
        $links = [];
        $link  = null;
        if ($kLink > 0) {
            $shopLangISO       = Shop::getLanguage(true);
            $loginSichtbarkeit = (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0)
                ? ''
                : " AND tlink.cSichtbarNachLogin = 'N' ";
            // get links for ALL languages
            $linkData = Shop::Container()->getDB()->queryPrepared(
                "SELECT tlink.*, tseo.cSeo, tseo.kSprache, tsprache.cISO
                    FROM tlink
                    LEFT JOIN tseo
                        ON tseo.cKey = 'kLink'
                        AND tseo.kKey = :linkID
                    LEFT JOIN tsprache
                        ON tsprache.kSprache = tseo.kSprache
                    WHERE tlink.bIsActive = 1 
                        AND tlink.kLink = :linkID" . $loginSichtbarkeit . "
                        AND (tlink.cKundengruppen IS NULL
                        OR tlink.cKundengruppen = 'NULL'
                        OR FIND_IN_SET(:cGroupID, REPLACE(tlink.cKundengruppen, ';', ',')) > 0)",
                [
                    'linkID'   => $kLink,
                    'cGroupID' => Session::CustomerGroup()->getID()
                ],
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
            // collect language URLs
            foreach ($linkData as $item) {
                $linkInstance = new Link(null, $item);
                if (($linkInstance->kSprache === 0 || $linkInstance->kSprache === null)
                    && $linkInstance->cISO === null
                ) {
                    // there may be no entries in tseo if there is only one active language
                    $linkInstance->kSprache = $shopLangID;
                    $linkInstance->cISO     = $shopLangISO;
                }
                $linkInstance->nHTTPRedirectCode = 0;
                $linkInstance->bHideContent      = false;
                $urls[$linkInstance->cISO] = empty($linkInstance->cSeo)
                    ? '?s=' . $item->kLink . '&amp;lang=' . $item->cISO
                    : $linkInstance->cSeo;
                if ($linkInstance->kSprache === $shopLangID) {
                    $link = $linkInstance;
                }
                $linkInstance->cLocalizedSeo = [];
                $linkInstance->cLocalizedSeo[$linkInstance->cISO] = $linkInstance->cSeo;
                $links[] = $linkInstance;
            }
            // append language URLs to all links
            foreach ($links as $item) {
                $item->languageURLs = $urls;
            }
            Shop::Cache()->set($cacheID, $links, [CACHING_GROUP_CORE]);
        }
        if (!isset($link->kLink)) {
            $item = Shop::Container()->getDB()->select('tlink', 'nLinkart', LINKTYP_STARTSEITE);
            $link = new Link(null, $item);
            if ($link->kLink !== $kLink) {
                $link->nHTTPRedirectCode = 301;
            } else {
                $link->nHTTPRedirectCode = 0;
                $link->bHideContent      = true;
            }
        }

        return $link;
    }

    /**
     * @param int $kLink
     * @return mixed|stdClass
     */
    public function getPageLinkLanguage($kLink)
    {
        $kLink = (int)$kLink;
        if ((int)$_SESSION['kSprache'] === 0) {
            $oSprache                = gibStandardsprache();
            $_SESSION['kSprache']    = (int)$oSprache->kSprache;
            $_SESSION['cISOSprache'] = $oSprache->cISO;
            Shop::Lang()->autoload();
        }
        $cacheID = 'page_lang_' . $kLink . '_' . $_SESSION['kSprache'];
        if (($oLinkSprache = Shop::Cache()->get($cacheID)) !== false) {
            executeHook(HOOK_GET_PAGE_LINK_LANGUAGE, [
                'cacheTags'    => [],
                'oLinkSprache' => &$oLinkSprache,
                'cached'       => true
            ]);

            return $oLinkSprache;
        }

        if ($kLink > 0
            && isset($_SESSION['kSprache'], $_SESSION['cISOSprache'])
            && $_SESSION['kSprache'] > 0
            && strlen($_SESSION['cISOSprache']) > 0
        ) {
            $oLinkSprache = Shop::Container()->getDB()->executeQueryPrepared(
                "SELECT tlinksprache.kLink, tlinksprache.cISOSprache, tlinksprache.cName, tlinksprache.cTitle, 
                        tlinksprache.cContent, tlinksprache.cMetaTitle, tlinksprache.cMetaKeywords, 
                        tlinksprache.cMetaDescription , tseo.cSeo
                    FROM tlinksprache
                    LEFT JOIN tseo
                        ON tseo.cKey = 'kLink'
                        AND tseo.kKey = tlinksprache.kLink
                        AND tseo.kSprache = :lang
                    WHERE tlinksprache.kLink = :klink
                        AND tlinksprache.cISOSprache = :iso
                    GROUP BY tlinksprache.kLink",
                [
                    'lang'  => Shop::getLanguageID(),
                    'klink' => $kLink,
                    'iso'   => Shop::getLanguageCode()
                ],
                \DB\ReturnType::SINGLE_OBJECT
            );
            if (isset($oLinkSprache->kLink)) {
                $oLinkSprache->kLink = (int)$oLinkSprache->kLink;
            }
            if (isset($oLinkSprache->cContent) && strlen($oLinkSprache->cContent) > 0) {
                $oLinkSprache->cContent = parseNewsText($oLinkSprache->cContent);
            }
        }
        $cacheTags = [CACHING_GROUP_CORE];
        executeHook(HOOK_GET_PAGE_LINK_LANGUAGE, [
            'cacheTags'    => &$cacheTags,
            'oLinkSprache' => &$oLinkSprache,
            'cached'       => false
        ]);
        Shop::Cache()->set($cacheID, $oLinkSprache, $cacheTags);

        return $oLinkSprache;
    }

    /**
     * @former gibLinkKeySpecialSeite()
     * @param int $nLinkart
     * @return int|bool
     */
    public function getSpecialPageLinkKey($nLinkart)
    {
        $nLinkart = (int)$nLinkart;
        if ($nLinkart > 0) {
            $allLinks = $this->getSpecialPages();
            $oLink    = isset($allLinks[$nLinkart]->kLink)
                ? $allLinks[$nLinkart]
                : Shop::Container()->getDB()->select('tlink', 'nLinkart', (int)$nLinkart, null, null, null, null, false, 'kLink');

            return (isset($oLink->kLink) && $oLink->kLink > 0) ? (int)$oLink->kLink : false;
        }

        return false;
    }

    /**
     * @param int    $nLinkArt
     * @param string $cISOSprache
     * @return stdClass
     */
    public function buildSpecialPageMeta($nLinkArt, $cISOSprache = '')
    {
        if ($cISOSprache === '') {
            $shopISO = Shop::getLanguageCode();
            if ($shopISO !== null && strlen($shopISO) > 0) {
                $cISOSprache = $shopISO;
            } else {
                $oSprache    = gibStandardsprache();
                $cISOSprache = $oSprache->cISO;
            }
        }
        $oMeta            = new stdClass();
        $oMeta->cTitle    = '';
        $oMeta->cDesc     = '';
        $oMeta->cKeywords = '';

        if ($nLinkArt > 0 && strlen($cISOSprache) > 0) {
            $oLink = Shop::Container()->getDB()->executeQueryPrepared(
                "SELECT tlinksprache.*
                    FROM tlinksprache
                    JOIN tlink
                        ON tlink.nLinkart = :type
                    WHERE tlinksprache.kLink = tlink.kLink
                        AND tlinksprache.cISOSprache = :iso",
                ['type' => (int)$nLinkArt, 'iso' => $cISOSprache],
                \DB\ReturnType::SINGLE_OBJECT
            );
            if (isset($oLink->kLink) && $oLink->kLink > 0) {
                $oMeta->cTitle    = $oLink->cMetaTitle;
                $oMeta->cDesc     = $oLink->cMetaDescription;
                $oMeta->cKeywords = $oLink->cMetaKeywords;
            }
        }

        return $oMeta;
    }

    /**
     * @param string      $id
     * @param bool        $full
     * @param bool        $secure
     * @param string|null $langISO
     * @return string
     */
    public function getStaticRoute($id = 'kontakt.php', $full = true, $secure = false, $langISO = null)
    {
        if (!isset($this->linkGroups->staticRoutes[$id])) {
            return $full && strpos($id, 'http') !== 0
                ? Shop::getURL($secure) . '/' . $id
                : $id;
        }
        $index = $this->linkGroups->staticRoutes[$id];
        if (is_array($index)) {
            $language        = $langISO ?? Shop::getLanguageCode();
            $localized       = $index[$language] ?? null;
            $customerGroupID = isset($_SESSION['Kundengruppe'])
                ? Session::CustomerGroup()->getID()
                : 0;
            $base            = $full === true
                ? (Shop::getURL($secure) . '/')
                : '';
            if (!is_array($localized)) {
                return $base . $id;
            }
            $attr     = $full === true
                ? ($secure === true
                    ? 'cURLFullSSL'
                    : 'cURLFull')
                : 'cSeo';
            $res      = !empty($localized[$customerGroupID]->$attr)
                ? $localized[$customerGroupID]->$attr
                : null;
            $fallback = $res === null && isset($localized[0]->$attr)
                ? $localized[0]->$attr
                : null;

            return $res ?? ($fallback ?? $base . $id);
        }

        return $index;
    }
}
