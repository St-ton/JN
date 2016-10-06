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
     * @var LinkHelper|null
     */
    private static $_instance = null;

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
    public $cacheID = null;

    /**
     * @var stdClass|null
     */
    public $linkGroups = null;

    /**
     *
     */
    public function __construct()
    {
        $this->cacheID    = 'linkgroups' . Shop::Cache()->getBaseID(false, false, true, true, true, false);
        self::$_langID    = (isset($_SESSION['kSprache'])) ? (int)$_SESSION['kSprache'] : 0;
        $this->linkGroups = $this->getLinkGroups();
        self::$_instance  = $this;
    }

    /**
     * singleton
     *
     * @return LinkHelper|null
     */
    public static function getInstance()
    {
        return (self::$_instance === null) ? new self() : self::$_instance;
    }

    /**
     * @return mixed|null
     */
    public function getLinkGroups()
    {
        if (isset($_SESSION['kSprache']) && (int)$_SESSION['kSprache'] !== self::$_langID) { //we had a lang switch event
            //update last used lang id
            self::$_langID = (int)$_SESSION['kSprache'];
            //create new cache ID with new lang ID
            $this->cacheID = 'linkgroups' . Shop::Cache()->getBaseID(false, false, true, true, true, false);
        } elseif ($this->linkGroups !== null) { //if we got matching language IDs, try to use class property
            return $this->linkGroups;
        }
        //try to load linkgroups from object cache
        if (($this->linkGroups = Shop::Cache()->get($this->cacheID)) === false) {
            return $this->buildLinkGroups(true);
        }

        return $this->linkGroups;
    }

    /**
     * save link groups to cache
     *
     * @param object $linkGroups
     * @return mixed
     */
    public function setLinkGroups($linkGroups)
    {
        return Shop::Cache()->set($this->cacheID, $linkGroups, array(CACHING_GROUP_CORE));
    }

    /**
     * @param int $kParentLink
     * @param int $kLink
     * @return bool
     */
    public function isChildActive($kParentLink, $kLink)
    {
        $kParentLink = (int) $kParentLink;
        if ($kParentLink > 0) {
            $cMember_arr = array_keys(get_object_vars($this->linkGroups));
            foreach ($cMember_arr as $cLinkGruppe) {
                if (is_array($this->linkGroups->$cLinkGruppe->Links)) {
                    foreach ($this->linkGroups->$cLinkGruppe->Links as $oLink) {
                        if ($oLink->kLink == $kLink && $oLink->kVaterLink == $kParentLink) {
                            return true;
                        }
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
        $kLink = (int) $kLink;
        if ($kLink > 0 && $this->linkGroups !== null) {
            $cMember_arr = array_keys(get_object_vars($this->linkGroups));
            foreach ($cMember_arr as $cLinkGruppe) {
                if (is_array($this->linkGroups->$cLinkGruppe->Links)) {
                    foreach ($this->linkGroups->$cLinkGruppe->Links as $oLink) {
                        if ($oLink->kLink == $kLink) {
                            $kParentLink = (int) $oLink->kVaterLink;
                            if ($kParentLink > 0) {
                                return $this->getRootLink($kParentLink);
                            }

                            return $kLink;
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * @param int $kParentLink
     * @return null|object
     */
    public function getParent($kParentLink)
    {
        $kParentLink = (int)$kParentLink;
        if ($kParentLink > 0) {
            $cMember_arr = array_keys(get_object_vars($this->linkGroups));
            foreach ($cMember_arr as $cLinkGruppe) {
                if (isset($this->linkGroups->$cLinkGruppe->Links) && is_array($this->linkGroups->$cLinkGruppe->Links)) {
                    foreach ($this->linkGroups->$cLinkGruppe->Links as $oLink) {
                        if ($oLink->kLink == $kParentLink) {
                            return $oLink;
                        }
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
        $result = array();
        $oLink  = $this->getParent($kLink);

        while (isset($oLink) && $oLink->kLink != 0) {
            array_unshift($result, $oLink->kLink);
            $oLink  = $this->getParent($oLink->kVaterLink);
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
            $cMember_arr = array_keys(get_object_vars($this->linkGroups));
            foreach ($cMember_arr as $cLinkGruppe) {
                if (is_array($this->linkGroups->$cLinkGruppe->Links)) {
                    foreach ($this->linkGroups->$cLinkGruppe->Links as $oLink) {
                        if ($oLink->kVaterLink == $kParentLink) {
                            if ($bAssoc) {
                                $oLink_arr[$oLink->kLink] = $oLink;
                            } else {
                                $oLink_arr[] = $oLink;
                            }
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
        if (is_object($oLink) && isset($oLink->kVaterLink) && isset($oLink->kLink)) {
            if ($oLinkLvl_arr === null) {
                $oLinkLvl_arr = $this->getMyLevel($oLink->kVaterLink);
            }
            $linkCount = count($oLinkLvl_arr);
            if (is_array($oLinkLvl_arr) && $linkCount > 0) {
                for ($i = 0; $i < $linkCount; $i++) {
                    if ($oLinkLvl_arr[$i]->kLink == $oLink->kLink) {
                        switch ($nEvent) {
                            case 1: // Previous
                                if (isset($oLinkLvl_arr[($i - 1)])) {
                                    return $oLinkLvl_arr[($i - 1)];
                                }
                                break;

                            case 2: // Next
                                if (isset($oLinkLvl_arr[($i + 1)])) {
                                    return $oLinkLvl_arr[($i + 1)];
                                }
                                break;
                            default:
                                break;
                        }
                    }
                }
            }
        }

        return;
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
            $linkObject = array();
        }
        if (!isset($linkObject[$kLink])) {
            $linkObject[$kLink] = Shop::DB()->select('tlink', 'kLink', $kLink);
            Shop::Cache()->set($cacheID, $linkObject, array(CACHING_GROUP_CORE));
        }

        return $linkObject[$kLink];
    }

    /**
     * @param bool $force
     * @return mixed|null|stdClass
     */
    public function buildLinkGroups($force = false)
    {
        $linkGroups = $this->linkGroups;
        if ($linkGroups === null || !is_object($linkGroups) || $force === true) {
            $session = array();
            //fixes for admin backend
            $customerGroupID = (isset($_SESSION['Kundengruppe']->kKundengruppe)) ?
                $_SESSION['Kundengruppe']->kKundengruppe :
                Kundengruppe::getDefaultGroupID();
            $Linkgruppen = Shop::DB()->query("SELECT * FROM tlinkgruppe", 2);
            $linkGroups  = new stdClass();
            $shopURL     = Shop::getURL();
            $shopURLSSL  = Shop::getURL(true);
            foreach ($Linkgruppen as $Linkgruppe) {
                if (strlen(trim($Linkgruppe->cTemplatename)) === 0) {
                    continue;
                }
                $linkGroups->{$Linkgruppe->cTemplatename}              = new stdClass();
                $linkGroups->{$Linkgruppe->cTemplatename}->cName       = $Linkgruppe->cName;
                $linkGroups->{$Linkgruppe->cTemplatename}->kLinkgruppe = $Linkgruppe->kLinkgruppe;

                $Linkgruppesprachen = Shop::DB()->selectAll('tlinkgruppesprache', 'kLinkgruppe', (int)$Linkgruppe->kLinkgruppe);
                foreach ($Linkgruppesprachen as $Linkgruppesprache) {
                    $linkGroups->{$Linkgruppe->cTemplatename}->cLocalizedName[$Linkgruppesprache->cISOSprache] = $Linkgruppesprache->cName;
                }

                $active = " AND tlink.bIsActive = 1 ";
                $loginSichtbarkeit = (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) ?
                    '' :
                    " AND tlink.cSichtbarNachLogin = 'N' ";
                $Links = Shop::DB()->query(
                    "SELECT tlink.*, tplugin.nStatus AS nPluginStatus
                        FROM tlink
                        LEFT JOIN tplugin
                            ON tplugin.kPlugin = tlink.kPlugin
                        WHERE tlink.kLinkgruppe = " . $Linkgruppe->kLinkgruppe . " " . $loginSichtbarkeit . " " . $active . "
                            AND (tlink.cKundengruppen IS NULL
                            OR tlink.cKundengruppen = 'NULL'
                            OR tlink.cKundengruppen LIKE '" . $customerGroupID . ";%'
                            OR tlink.cKundengruppen LIKE '%;" . $customerGroupID . ";%')
                        ORDER BY tlink.nSort, tlink.cName", 2
                );
                $linkCount = count($Links);
                for ($i = 0; $i < $linkCount; $i++) {
                    // Deaktivierte Plugins, nicht als Link anzeigen
                    if ($Links[$i]->kPlugin > 0 && $Links[$i]->nPluginStatus != 2) {
                        unset($Links[$i]);
                        continue;
                    }
                    $linkLanguages = Shop::DB()->query(
                        "SELECT tlinksprache.kLink, tlinksprache.cISOSprache, tlinksprache.cName, tlinksprache.cTitle, tseo.cSeo
                            FROM tlinksprache
                            JOIN tsprache
                                ON tsprache.cISO = tlinksprache.cISOSprache
                            LEFT JOIN tseo
                                ON tseo.cKey = 'kLink'
                                AND tseo.kKey = tlinksprache.kLink
                                AND tseo.kSprache = tsprache.kSprache
                            WHERE tlinksprache.kLink = " . (int)$Links[$i]->kLink . "
                            GROUP BY tlinksprache.cISOSprache", 2
                    );
                    if ($linkLanguages === false) {
                        $linkLanguages = array();
                    }
                    foreach ($linkLanguages as $Linksprache) {
                        $Links[$i]->cLocalizedName[$Linksprache->cISOSprache]  = $Linksprache->cName;
                        $Links[$i]->cLocalizedTitle[$Linksprache->cISOSprache] = $Linksprache->cTitle;
                        $Links[$i]->cLocalizedSeo[$Linksprache->cISOSprache]   = $Linksprache->cSeo;
                    }
                    $Links[$i]->URL      = baueURL($Links[$i], URLART_SEITE);
                    $Links[$i]->cURLFull = $shopURL . '/' . $Links[$i]->URL;

                    if (isset($Links[$i]->bSSL) && (int)$Links[$i]->bSSL === 2) {
                        //if link has forced ssl, modify cURLFull accordingly
                        $Links[$i]->cURLFull = str_replace('http://', 'https://', $Links[$i]->cURLFull);
                    }
                    //reset if external link
                    if ($Links[$i]->nLinkart == 2) {
                        $Links[$i]->URL      = $Links[$i]->cURL;
                        $Links[$i]->cURLFull = $Links[$i]->cURL;
                    }
                }
                $Links                                           = array_merge($Links);
                $linkGroups->{$Linkgruppe->cTemplatename}->Links = $Links;
            }
            $sid    = '';
            $cDatei = 'navi.php';
            //startseite
            $start_arr = Shop::DB()->query(
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
            $session['Link_Startseite'] = array();

            if (is_array($start_arr) && count($start_arr) > 0) {
                foreach ($start_arr as $start) {
                    $session['Link_Startseite'][$start->cISOSprache] = $cDatei . '?s=' . $start->kLink . $sid;
                    if ($start->cSeo && strlen($start->cSeo) > 1) {
                        $session['Link_Startseite'][$start->cISOSprache] = $start->cSeo;
                        $oSprache                                        = gibStandardsprache(true);
                        if ($start->cISOSprache == $oSprache->cISO) {
                            $session['Link_Startseite'][$start->cISOSprache] = Shop::getURL();
                        }
                    }
                }
            }
            //versand
            $cKundengruppenSQL = '';
            if (isset($_SESSION['Kundengruppe']->kKundengruppe) && $_SESSION['Kundengruppe']->kKundengruppe > 0) {
                $cKundengruppenSQL = " AND (tlink.cKundengruppen LIKE '" . (int) $_SESSION['Kundengruppe']->kKundengruppe . ";%'
                    OR tlink.cKundengruppen LIKE '%;" . (int) $_SESSION['Kundengruppe']->kKundengruppe . ";%'
                    OR tlink.cKundengruppen IS NULL OR tlink.cKundengruppen = 'NULL' OR tlink.cKundengruppen = '')";
            }
            $versand_arr = Shop::DB()->query(
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
            $session['Link_Versandseite'] = array();

            if (is_array($versand_arr) && count($versand_arr) > 0) {
                foreach ($versand_arr as $versand) {
                    $session['Link_Versandseite'][$versand->cISOSprache] = $cDatei . '?s=' . $versand->kLink . $sid;
                    if ($versand->cSeo && strlen($versand->cSeo) > 1) {
                        $session['Link_Versandseite'][$versand->cISOSprache] = $versand->cSeo;
                    }
                }
            }
            //AGB
            $agb_arr = Shop::DB()->query(
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

            $session['Link_AGB'] = array();
            if (is_array($agb_arr) && count($agb_arr) > 0) {
                foreach ($agb_arr as $agb) {
                    $session['Link_AGB'][$agb->cISOSprache] = $cDatei . '?s=' . $agb->kLink . $sid;
                    if ($agb->cSeo && strlen($agb->cSeo) > 1) {
                        $session['Link_AGB'][$agb->cISOSprache] = $agb->cSeo;
                    }
                }
            }
            //Link_Datenschutz
            $agb_arr = Shop::DB()->query(
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

            $session['Link_Datenschutz'] = array();
            if (is_array($agb_arr) && count($agb_arr) > 0) {
                foreach ($agb_arr as $agb) {
                    $session['Link_Datenschutz'][$agb->cISOSprache] = $cDatei . '?s=' . $agb->kLink . $sid;
                    if ($agb->cSeo && strlen($agb->cSeo) > 0) {
                        $session['Link_Datenschutz'][$agb->cISOSprache] = $agb->cSeo;
                    }
                }
            }
            $_SESSION['Link_Datenschutz']  = $session['Link_Datenschutz'];
            $_SESSION['Link_AGB']          = $session['Link_AGB'];
            $_SESSION['Link_Versandseite'] = $session['Link_Versandseite'];
            $linkGroups->Link_Datenschutz  = $session['Link_Datenschutz'];
            $linkGroups->Link_AGB          = $session['Link_AGB'];
            $linkGroups->Link_Versandseite = $session['Link_Versandseite'];

            $staticRoutes_arr = Shop::DB()->query(
                "SELECT tspezialseite.kSpezialseite, tspezialseite.cName AS baseName, tspezialseite.cDateiname, tspezialseite.nLinkart, 
                        tlink.kLink, tlinksprache.cName AS seoName, tseo.cSeo, tsprache.cISO, tsprache.kSprache
                    FROM tspezialseite
                        LEFT JOIN tlink ON tlink.nLinkart = tspezialseite.nLinkart
                        LEFT JOIN tlinksprache ON tlink.kLink = tlinksprache.kLink
                        LEFT JOIN tsprache ON tsprache.cISO = tlinksprache.cISOSprache
                        LEFT JOIN tseo ON tseo.cKey = 'kLink' AND tseo.kKey = tlink.kLink AND tseo.kSprache = tsprache.kSprache
                    WHERE cDateiname IS NOT NULL AND cDateiname != ''", 2);

            $linkGroups->staticRoutes = array();
            foreach ($staticRoutes_arr as $link) {
                if (empty($link->cSeo)) {
                    continue;
                }
                $link->cURLFull    = $shopURL . '/' . $link->cSeo;
                $link->cURLFullSSL = $shopURLSSL . '/' . $link->cSeo;
                $currentIndex      = $link->cDateiname;
                if (!isset($linkGroups->staticRoutes[$link->cDateiname])) {
                    $linkGroups->staticRoutes[$currentIndex] = array();
                }
                unset($link->cDateiname);
                if (!empty($link->cISO)) {
                    $linkGroups->staticRoutes[$currentIndex][$link->cISO] = $link;
                } else {
                    $linkGroups->staticRoutes[$currentIndex][] = $link;
                }
            }

            $this->linkGroups = $linkGroups;
            executeHook(HOOK_BUILD_LINK_GROUPS, array(
                    'linkGroups' => &$linkGroups,
                    'cached'     => false,
                    'forced'     => $force
                )
            );
            $this->setLinkGroups($linkGroups);

            return $this->linkGroups;
        }
        executeHook(HOOK_BUILD_LINK_GROUPS, array(
                'linkGroups' => &$this->linkGroups,
                'cached'     => true,
                'forced'     => false
            )
        );

        return $this->linkGroups;
    }

    /**
     * @former gibSpezialSeiten()
     * @return array|mixed
     */
    public function getSpecialPages()
    {
        $cISO    = Shop::$cISO;
        $cacheID = 'special_pages_b_' . $cISO;
        if (($oSpeziallinks = Shop::Cache()->get($cacheID)) !== false) {
            return $oSpeziallinks;
        }
        $oSpeziallinks            = array();
        $_SESSION['Speziallinks'] = array();
        $oLink_arr                = Shop::DB()->query("SELECT kLink, nLinkart, cName FROM tlink WHERE nLinkart >= 5 ORDER BY nLinkart", 2);
        foreach ($oLink_arr as &$oLink) {
            $oObj           = new stdClass();
            $oObj->kLink    = (int)$oLink->kLink;
            $oObj->nLinkart = (int)$oLink->nLinkart;
            $oObj->cName    = $oLink->cName;
            $oLink          = $this->findCMSLinkInSession($oLink->kLink);
            $oObj->cURL     = (isset($oLink->cURLFull)) ?
                $oLink->cURLFull :
                '';
            if (isset($oLink->cLocalizedName) && array_key_exists($cISO, $oLink->cLocalizedName)) {
                $oLink->cName = $oLink->cLocalizedName[$cISO];
            }
            $oSpeziallinks[$oObj->nLinkart] = $oObj;
        }
        Shop::Cache()->set($cacheID, $oSpeziallinks, array(CACHING_GROUP_CORE));

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
        if (!isset($linkGroups)) {
            //this can happen when there is a $_SESSION active and object cache is beeing flushed
            //since setzeLinks() is only executed in class.core.Session
            setzeLinks();
        }
        if (($kLink > 0 || $kPlugin > 0) && isset($linkGroups) && is_object($linkGroups)) {
            $cMember_arr = array_keys(get_object_vars($linkGroups));
            if (is_array($cMember_arr) && count($cMember_arr) > 0) {
                foreach ($cMember_arr as $cMember) {
                    if (isset($linkGroups->$cMember->Links) && is_array($linkGroups->$cMember->Links) && count($linkGroups->$cMember->Links) > 0) {
                        foreach ($linkGroups->$cMember->Links as $oLink) {
                            if ($kLink > 0 && isset($oLink->kLink) && $oLink->kLink == $kLink) {
                                return $oLink;
                            }
                            if ($kPlugin > 0 && isset($oLink->kPlugin) && $oLink->kPlugin == $kPlugin) {
                                return $oLink;
                            }
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
        global $NaviFilter;

        $bNoIndex = false;
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
        if (isset($NaviFilter->Suche->cSuche) && !is_null($NaviFilter->Suche->cSuche) && strlen($NaviFilter->Suche->cSuche) > 0) {
            $bNoIndex = true;
        }
        if (!$bNoIndex) {
            $shopsetting = Shopsetting::getInstance();
            $bNoIndex    = isset($NaviFilter->MerkmalWert->kMerkmalWert) && $NaviFilter->MerkmalWert->kMerkmalWert > 0
                && isset($shopsetting['global']['global_merkmalwert_url_indexierung']) &&
                $shopsetting['global']['global_merkmalwert_url_indexierung'] === 'N';
        }

        return $bNoIndex;
    }

    /**
     * gets (cached) linkgroup created by setzeLinks() and returns currently active link
     * used in letzterInclude.php
     *
     * @former aktiviereLinks()
     * @param int $pageType
     * @return mixed|null|stdClass
     */
    public function activate($pageType)
    {
        $linkGroups = $this->getLinkGroups();

        if (!isset($linkGroups)) {
            //this can happen when there is a $_SESSION active and object cache is beeing flushed
            //since setzeLinks() is only executed in class.core.Session
            $linkGroups = setzeLinks();
        }
        if (isset($linkGroups) && is_object($linkGroups)) {
            $arr         = get_object_vars($linkGroups);
            $linkgruppen = array_keys($arr);
            foreach ($linkgruppen as $linkgruppe) {
                if (isset($linkGroups->$linkgruppe->Links) && is_array($linkGroups->$linkgruppe->Links)) {
                    $linkGroups->$linkgruppe->kVaterLinkAktiv = 0;
                    $cnt                                      = count($linkGroups->$linkgruppe->Links);
                    for ($i = 0; $i < $cnt; $i++) {
                        $linkGroups->$linkgruppe->Links[$i]->aktiv = 0;
                        switch ($pageType) {
                            case PAGE_STARTSEITE:
                                if ($linkGroups->$linkgruppe->Links[$i]->nLinkart == LINKTYP_STARTSEITE) {
                                    $linkGroups->$linkgruppe->Links[$i]->aktiv = 1;
                                }
                                break;
                            case PAGE_ARTIKEL:
                                break;
                            case PAGE_ARTIKELLISTE:
                                break;
                            case PAGE_EIGENE:
                                // Hoechste Ebene
                                $kVaterLink = $linkGroups->$linkgruppe->Links[$i]->kVaterLink;
                                if ($kVaterLink == 0 && $this->isChildActive($kVaterLink, Shop::$kLink)) {
                                    $linkGroups->$linkgruppe->Links[$i]->aktiv = 1;
                                }
                                if ($linkGroups->$linkgruppe->Links[$i]->kLink == Shop::$kLink) {
                                    $linkGroups->$linkgruppe->Links[$i]->aktiv = 1;
                                    $kVaterLink                                = $this->getRootLink($linkGroups->$linkgruppe->Links[$i]->kLink);
                                    for ($j = 0; $j < $cnt; $j++) {
                                        if ($linkGroups->$linkgruppe->Links[$j]->kLink == $kVaterLink) {
                                            $linkGroups->$linkgruppe->Links[$j]->aktiv = 1;
                                        }
                                    }
                                }
                                break;
                            case PAGE_WARENKORB:
                                if ($linkGroups->$linkgruppe->Links[$i]->nLinkart == LINKTYP_WARENKORB) {
                                    $linkGroups->$linkgruppe->Links[$i]->aktiv = 1;
                                }
                                break;
                            case PAGE_LOGIN:
                            case PAGE_MEINKONTO:
                                if ($linkGroups->$linkgruppe->Links[$i]->nLinkart == LINKTYP_LOGIN) {
                                    $linkGroups->$linkgruppe->Links[$i]->aktiv = 1;
                                }
                                break;
                            case PAGE_REGISTRIERUNG:
                                if ($linkGroups->$linkgruppe->Links[$i]->nLinkart == LINKTYP_REGISTRIEREN) {
                                    $linkGroups->$linkgruppe->Links[$i]->aktiv = 1;
                                }
                                break;
                            case PAGE_PASSWORTVERGESSEN:
                                if ($linkGroups->$linkgruppe->Links[$i]->nLinkart == LINKTYP_PASSWORD_VERGESSEN) {
                                    $linkGroups->$linkgruppe->Links[$i]->aktiv = 1;
                                }
                                break;
                            case PAGE_BESTELLVORGANG:
                                break;
                            case PAGE_KONTAKT:
                                if ($linkGroups->$linkgruppe->Links[$i]->nLinkart == LINKTYP_KONTAKT) {
                                    $linkGroups->$linkgruppe->Links[$i]->aktiv = 1;
                                }
                                break;
                            case PAGE_NEWSLETTER:
                                if ($linkGroups->$linkgruppe->Links[$i]->nLinkart == LINKTYP_NEWSLETTER) {
                                    $linkGroups->$linkgruppe->Links[$i]->aktiv = 1;
                                }
                                break;
                            case PAGE_UMFRAGE:
                                if ($linkGroups->$linkgruppe->Links[$i]->nLinkart == LINKTYP_UMFRAGE) {
                                    $linkGroups->$linkgruppe->Links[$i]->aktiv = 1;
                                }
                                break;
                            case PAGE_NEWS:
                                if ($linkGroups->$linkgruppe->Links[$i]->nLinkart == LINKTYP_NEWS) {
                                    $linkGroups->$linkgruppe->Links[$i]->aktiv = 1;
                                }
                                break;
                            default:
                                break;
                        }
                    }
                }
            }
            //write back linkgroups
        }

        return $linkGroups;
    }

    /**
     * @param int $kLink
     * @return mixed|stdClass
     */
    public function getPageLink($kLink)
    {
        $kLink   = (int)$kLink;
        $cacheID = 'page_' . $kLink . '_' . Shop::$kSprache . ((isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) ? 'vis' : 'nvis');
        if (($Link = Shop::Cache()->get($cacheID)) !== false) {
            return $Link;
        }
        $Link = new stdClass();
        if ($kLink > 0) {
            //hole Link
            $active = " AND tlink.bIsActive = 1 ";
            $loginSichtbarkeit = " AND tlink.cSichtbarNachLogin = 'N' ";
            if (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) {
                $loginSichtbarkeit = '';
            }
            $Link = Shop::DB()->query("
                SELECT tlink.*, tseo.cSeo
                    FROM tlink
                    LEFT JOIN tseo
                        ON tseo.cKey = 'kLink'
                        AND tseo.kKey = " . $kLink . "
                        AND tseo.kSprache = " . (int)Shop::$kSprache . "
                    WHERE tlink.kLink = " . $kLink . "
                        " . $active . "
                        " . $loginSichtbarkeit . "
                        AND (tlink.cKundengruppen IS NULL
                        OR tlink.cKundengruppen = 'NULL'
                        OR tlink.cKundengruppen LIKE '" . (int)$_SESSION['Kundengruppe']->kKundengruppe . ";%'
                        OR tlink.cKundengruppen LIKE '%;" . (int)$_SESSION['Kundengruppe']->kKundengruppe . ";%')", 1
            );
            if ($Link === null || $Link === false) {
                $Link = new stdClass();
            }
        }
        $Link->nHTTPRedirectCode = 0;
        $Link->bHideContent      = false;
        if (!isset($Link->kLink)) {
            $Link = Shop::DB()->select('tlink', 'nLinkart', LINKTYP_STARTSEITE);
            if ($Link->kLink != $kLink) {
                $Link->nHTTPRedirectCode = 301;
            } else {
                $Link->bHideContent = true;
            }
        } elseif (isset($Link->cSeo) && strlen($Link->cSeo) > 0) {
            $Link->cLocalizedSeo[Shop::$cISO] = $Link->cSeo;
        }
        Shop::Cache()->set($cacheID, $Link, array(CACHING_GROUP_CATEGORY, CACHING_GROUP_CORE));

        return $Link;
    }

    /**
     * @param int $kLink
     * @return mixed|stdClass
     */
    public function getPageLinkLanguage($kLink)
    {
        $kLink = (int)$kLink;
        // Workaround
        if ((int)$_SESSION['kSprache'] === 0) {
            $oSprache                = gibStandardsprache(true);
            $_SESSION['kSprache']    = $oSprache->kSprache;
            $_SESSION['cISOSprache'] = $oSprache->cISO;
            Shop::Lang()->autoload();
        }
        $cacheID = 'page_lang_' . $kLink . '_' . $_SESSION['kSprache'];
        if (($oLinkSprache = Shop::Cache()->get($cacheID)) !== false) {
            executeHook(HOOK_GET_PAGE_LINK_LANGUAGE, array(
                'cacheTags'    => array(),
                'oLinkSprache' => &$oLinkSprache,
                'cached'       => true

            ));

            return $oLinkSprache;
        }

        if ($kLink > 0 && isset($_SESSION['kSprache']) && $_SESSION['kSprache'] > 0 && isset($_SESSION['cISOSprache']) && strlen($_SESSION['cISOSprache']) > 0) {
            $oLinkSprache = Shop::DB()->query(
                "SELECT tlinksprache.kLink, tlinksprache.cISOSprache, tlinksprache.cName, tlinksprache.cTitle, tlinksprache.cContent,
                        tlinksprache.cMetaTitle, tlinksprache.cMetaKeywords, tlinksprache.cMetaDescription , tseo.cSeo
                    FROM tlinksprache
                    LEFT JOIN tseo
                        ON tseo.cKey = 'kLink'
                        AND tseo.kKey = tlinksprache.kLink
                        AND tseo.kSprache = " . (int)$_SESSION['kSprache'] . "
                    WHERE tlinksprache.kLink = " . $kLink . "
                        AND tlinksprache.cISOSprache = '" . $_SESSION['cISOSprache'] . "'
                    GROUP BY tlinksprache.kLink", 1
            );
            if (isset($oLinkSprache->cContent) && strlen($oLinkSprache->cContent) > 0) {
                $oLinkSprache->cContent = parseNewsText($oLinkSprache->cContent);
            }
        }
        $cacheTags = array(CACHING_GROUP_CORE);
        executeHook(HOOK_GET_PAGE_LINK_LANGUAGE, array(
            'cacheTags'    => &$cacheTags,
            'oLinkSprache' => &$oLinkSprache,
            'cached'       => false

        ));
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
            $oLink    = (isset($allLinks[$nLinkart]->kLink)) ?
                $allLinks[$nLinkart] :
                Shop::DB()->select('tlink', 'nLinkart', (int)$nLinkart, null, null, null, null, false, 'kLink');

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
        if (strlen($cISOSprache) === 0) {
            if (isset(Shop::$cISO) && strlen(Shop::$cISO) > 0) {
                $cISOSprache = Shop::$cISO;
            } else {
                $oSprache    = gibStandardsprache(true);
                $cISOSprache = $oSprache->cISO;
            }
        }
        $oMeta            = new stdClass();
        $oMeta->cTitle    = '';
        $oMeta->cDesc     = '';
        $oMeta->cKeywords = '';

        if ($nLinkArt > 0 && strlen($cISOSprache) > 0) {
            $oLink = Shop::DB()->query(
                "SELECT tlinksprache.*
                    FROM tlinksprache
                    JOIN tlink
                        ON tlink.nLinkart = " . (int)$nLinkArt . "
                    WHERE tlinksprache.kLink = tlink.kLink
                        AND tlinksprache.cISOSprache = '" . StringHandler::filterXSS($cISOSprache) . "'", 1
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
        if (isset($this->linkGroups->staticRoutes[$id])) {
            $index = $this->linkGroups->staticRoutes[$id];
            if (is_array($index)) {
                $language  = ($langISO !== null) ? $langISO : $_SESSION['cISOSprache'];
                $localized = (isset($index[$language])) ?
                    $index[$language] :
                    null;
                if ($full === true) {
                    if ($secure === true) {
                        return (!empty($localized->cURLFullSSL)) ? $localized->cURLFullSSL : (Shop::getURL(true) . '/' . $id);
                    }

                    return (!empty($localized->cURLFull)) ? $localized->cURLFull : (Shop::getURL() . '/' . $id);
                }

                return (!empty($localized->cSeo)) ? $localized->cSeo : $id;
            }

            return $index;
        }
        if ($full && strpos($id, 'http') !== 0) {
            return Shop::getURL($secure) . '/' . $id;
        }

        return $id;
    }
}
