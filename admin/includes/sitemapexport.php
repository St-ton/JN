<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param string $nDatei
 * @param mixed  $data
 */
function baueSitemap($nDatei, $data)
{
    Shop::Container()->getLogService()->debug('Baue "' . PFAD_EXPORT . 'sitemap_' .
        $nDatei . '.xml", Datenlaenge ' . strlen($data)
    );
    $conf = Shop::getSettings([CONF_SITEMAP]);
    if (!empty($data)) {
        if (false && function_exists('gzopen')) {
            // Sitemap-Dateien anlegen
            $gz = gzopen(PFAD_ROOT . PFAD_EXPORT . 'sitemap_' . $nDatei . '.xml.gz', 'w9');
            fwrite($gz, getXMLHeader($conf['sitemap']['sitemap_googleimage_anzeigen']) . "\n");
            fwrite($gz, $data);
            fwrite($gz, '</urlset>');
            gzclose($gz);
        } else {
            // Sitemap-Dateien anlegen
            $file = fopen(PFAD_ROOT . PFAD_EXPORT . 'sitemap_' . $nDatei . '.xml', 'w+');
            fwrite($file, getXMLHeader($conf['sitemap']['sitemap_googleimage_anzeigen']) . "\n");
            fwrite($file, $data);
            fwrite($file, '</urlset>');
            fclose($file);
        }
    }
    $data = null;
}

/**
 * @deprecated since 4.06
 * @param bool $ssl
 * @return string
 */
function getSitemapBaseURL($ssl = false)
{
    return Shop::getURL($ssl);
}

/**
 * @param string $nDatei
 * @param bool   $bGZ
 * @return string
 */
function baueSitemapIndex($nDatei, $bGZ)
{
    $shopURL = Shop::getURL();
    $conf    = Shop::getSettings([CONF_SITEMAP]);
    $cIndex  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $cIndex  .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    for ($i = 0; $i <= $nDatei; ++$i) {
        if ($bGZ) {
            $cIndex .= '<sitemap><loc>' .
                StringHandler::htmlentities($shopURL . '/' . PFAD_EXPORT . 'sitemap_' . $i . '.xml.gz') .
                '</loc>' .
                ((!isset($conf['sitemap']['sitemap_insert_lastmod']) || $conf['sitemap']['sitemap_insert_lastmod'] === 'Y')
                    ? ('<lastmod>' . StringHandler::htmlentities(date('Y-m-d')) . '</lastmod>') :
                    '') .
                '</sitemap>' . "\n";
        } else {
            $cIndex .= '<sitemap><loc>' . StringHandler::htmlentities($shopURL . '/' .
                    PFAD_EXPORT . 'sitemap_' . $i . '.xml') . '</loc>' .
                ((!isset($conf['sitemap']['sitemap_insert_lastmod']) || $conf['sitemap']['sitemap_insert_lastmod'] === 'Y')
                    ? ('<lastmod>' . StringHandler::htmlentities(date('Y-m-d')) . '</lastmod>')
                    : '') .
                '</sitemap>' . "\n";
        }
    }
    $cIndex .= '</sitemapindex>';

    return $cIndex;
}

/**
 * @param string      $strLoc
 * @param null|string $strLastMod
 * @param null|string $strChangeFreq
 * @param null|string $strPriority
 * @param string      $cGoogleImageURL
 * @param bool        $ssl
 *
 * @return string
 */
function makeURL(
    $strLoc,
    $strLastMod = null,
    $strChangeFreq = null,
    $strPriority = null,
    $cGoogleImageURL = '',
    $ssl = false
) {
    $strRet = "  <url>\n" .
        '     <loc>' . StringHandler::htmlentities(Shop::getURL($ssl)) . '/' .
        StringHandler::htmlentities($strLoc) . "</loc>\n";
    if (strlen($cGoogleImageURL) > 0) {
        $strRet .=
            "     <image:image>\n" .
            '        <image:loc>' . StringHandler::htmlentities($cGoogleImageURL) . "</image:loc>\n" .
            "     </image:image>\n";
    }
    if ($strLastMod) {
        $strRet .= '     <lastmod>' . StringHandler::htmlentities($strLastMod) . "</lastmod>\n";
    }
    if ($strChangeFreq) {
        $strRet .= '     <changefreq>' . StringHandler::htmlentities($strChangeFreq) . "</changefreq>\n";
    }
    if ($strPriority) {
        $strRet .= '     <priority>' . StringHandler::htmlentities($strPriority) . "</priority>\n";
    }
    $strRet .= "  </url>\n";

    return $strRet;
}

/**
 * @param string $cISO
 * @param array  $Sprachen
 * @return bool
 */
function spracheEnthalten($cISO, $Sprachen)
{
    if ($_SESSION['cISOSprache'] === $cISO) {
        return true;
    }
    if (is_array($Sprachen)) {
        foreach ($Sprachen as $SpracheTMP) {
            if ($SpracheTMP->cISO === $cISO) {
                return true;
            }
        }
    }

    return false;
}

/**
 * @param string $cUrl
 * @return bool
 */
function isSitemapBlocked($cUrl)
{
    $cExclude_arr = [
        'navi.php',
        'suche.php',
        'jtl.php',
        'pass.php',
        'registrieren.php',
        'warenkorb.php',
    ];

    foreach ($cExclude_arr as $cExclude) {
        if (strpos($cUrl, $cExclude) !== false) {
            return true;
        }
    }

    return false;
}

/**
 *
 */
function generateSitemapXML()
{
    Shop::Container()->getLogService()->debug('Sitemap wird erstellt');
    $nStartzeit = microtime(true);
    $conf       = Shop::getSettings([
        CONF_ARTIKELUEBERSICHT,
        CONF_SITEMAP,
        CONF_GLOBAL,
        CONF_NAVIGATIONSFILTER,
        CONF_BOXEN
    ]);
    require_once PFAD_ROOT . PFAD_INCLUDES . 'filter_inc.php';
    if (!isset($conf['sitemap']['sitemap_insert_lastmod'])) {
        $conf['sitemap']['sitemap_insert_lastmod'] = 'N';
    }
    if (!isset($conf['sitemap']['sitemap_insert_changefreq'])) {
        $conf['sitemap']['sitemap_insert_changefreq'] = 'N';
    }
    if (!isset($conf['sitemap']['sitemap_insert_priority'])) {
        $conf['sitemap']['sitemap_insert_priority'] = 'N';
    }
    if (!isset($conf['sitemap']['sitemap_google_ping'])) {
        $conf['sitemap']['sitemap_google_ping'] = 'N';
    }
    if ($conf['sitemap']['sitemap_insert_changefreq'] === 'Y') {
        define('FREQ_ALWAYS', 'always');
        define('FREQ_HOURLY', 'hourly');
        define('FREQ_DAILY', 'daily');
        define('FREQ_WEEKLY', 'weekly');
        define('FREQ_MONTHLY', 'monthly');
        define('FREQ_YEARLY', 'yearly');
        define('FREQ_NEVER', 'never');
    } else {
        define('FREQ_ALWAYS', null);
        define('FREQ_HOURLY', null);
        define('FREQ_DAILY', null);
        define('FREQ_WEEKLY', null);
        define('FREQ_MONTHLY', null);
        define('FREQ_YEARLY', null);
        define('FREQ_NEVER', null);
    }
    // priorities
    if ($conf['sitemap']['sitemap_insert_priority'] === 'Y') {
        define('PRIO_VERYHIGH', '1.0');
        define('PRIO_HIGH', '0.7');
        define('PRIO_NORMAL', '0.5');
        define('PRIO_LOW', '0.3');
        define('PRIO_VERYLOW', '0.0');
    } else {
        define('PRIO_VERYHIGH', null);
        define('PRIO_HIGH', null);
        define('PRIO_NORMAL', null);
        define('PRIO_LOW', null);
        define('PRIO_VERYLOW', null);
    }
    // W3C Datetime formats:
    //  YYYY-MM-DD (eg 1997-07-16)
    //  YYYY-MM-DDThh:mmTZD (eg 1997-07-16T19:20+01:00)
    $defaultCustomerGroupID  = Kundengruppe::getDefaultGroupID();
    $Sprachen                = Sprache::getAllLanguages();
    $oSpracheAssoc_arr       = gibAlleSprachenAssoc($Sprachen);
    $defaultLang             = Sprache::getDefaultLanguage(true);
    $defaultLangID           = (int)$defaultLang->kSprache;
    $_SESSION['kSprache']    = $defaultLangID;
    $_SESSION['cISOSprache'] = $defaultLang->cISO;
    TaxHelper::setTaxRates();
    if (!isset($_SESSION['Kundengruppe'])) {
        $_SESSION['Kundengruppe'] = new Kundengruppe();
    }
    $_SESSION['Kundengruppe']->setID($defaultCustomerGroupID);
    // Stat Array
    $nStat_arr = [
        'artikel'          => 0,
        'artikelbild'      => 0,
        'artikelsprache'   => 0,
        'link'             => 0,
        'kategorie'        => 0,
        'kategoriesprache' => 0,
        'tag'              => 0,
        'tagsprache'       => 0,
        'hersteller'       => 0,
        'livesuche'        => 0,
        'livesuchesprache' => 0,
        'merkmal'          => 0,
        'merkmalsprache'   => 0,
        'news'             => 0,
        'newskategorie'    => 0,
    ];
    // Artikelübersicht - max. Artikel pro Seite
    
    $nDatei         = 0;
    $nSitemap       = 1;
    $nAnzahlURL_arr = [0 => 0];
    $nSitemapLimit  = 25000;
    $sitemap_data   = '';
    $db             = Shop::Container()->getDB();
    $imageBaseURL   = Shop::getImageBaseURL();
    $baseURL = Shop::getURL() . '/';
    //Hauptseite
    $sitemap_data .= makeURL('', null, FREQ_ALWAYS, PRIO_VERYHIGH);
    
    
    $urlGenerator = new \Sitemap\URLGenerator($db, Shop::Container()->getCache(), $conf, $baseURL);
    Shop::dbg($urlGenerator->getExportURL(1, 'kKategorie', $Sprachen, 1));
    die('xxx');
    //Alte Sitemaps löschen
    loescheSitemaps();
    $renderer = new \Sitemap\Renderes\DefaultRenderer($baseURL);

    $generators   = [];
    $generators[] = new \Sitemap\Factories\Product($db, $conf);
    $generators[] = new \Sitemap\Factories\Category($db, $conf);
    $generators[] = new \Sitemap\Factories\Tag($db, $conf);
    $generators[] = new \Sitemap\Factories\LiveSearch($db, $conf);
    $generators[] = new \Sitemap\Factories\Attribute($db, $conf);
    $generators[] = new \Sitemap\Factories\NewsItem($db, $conf);
    $generators[] = new \Sitemap\Factories\NewsCategory($db, $conf);

    foreach ($generators as $generator) {
        $collection = $generator->getCollection($Sprachen, [$defaultCustomerGroupID]);
        foreach ($collection as $item) {
            if ($nSitemap > $nSitemapLimit) {
                $nSitemap = 1;
                baueSitemap($nDatei, $sitemap_data);
                ++$nDatei;
                $nAnzahlURL_arr[$nDatei] = 0;
                $sitemap_data            = '';
            }
            /** @var \Sitemap\Items\ItemInterface $item */
            $cUrl = $item->getLocation();
            if (!isSitemapBlocked($cUrl)) {
                $sitemap_data .= $renderer->renderItem($item);
                ++$nSitemap;
                ++$nAnzahlURL_arr[$nDatei];
            }
        }
    }
    baueSitemap($nDatei, $sitemap_data);
    // XML ablegen + ausgabe an user
    $datei = PFAD_ROOT . PFAD_EXPORT . 'sitemap_index.xml';
    if (is_writable($datei) || !is_file($datei)) {
        $bGZ = function_exists('gzopen');
        // Sitemap Index Datei anlegen
        $file = fopen(PFAD_ROOT . PFAD_EXPORT . 'sitemap_index.xml', 'w+');
        fwrite($file, baueSitemapIndex($nDatei, $bGZ));
        fclose($file);
        $nEndzeit   = microtime(true);
        $fTotalZeit = $nEndzeit - $nStartzeit;
        executeHook(HOOK_SITEMAP_EXPORT_GENERATED, ['nAnzahlURL_arr' => $nAnzahlURL_arr, 'fTotalZeit' => $fTotalZeit]);
        // Sitemap Report
        baueSitemapReport($nAnzahlURL_arr, $fTotalZeit);
        // ping sitemap to Google and Bing
        if ($conf['sitemap']['sitemap_google_ping'] === 'Y') {
            $encodedSitemapIndexURL = urlencode(Shop::getURL() . '/sitemap_index.xml');
            if (200 !== ($httpStatus = RequestHelper::http_get_status('http://www.google.com/webmasters/tools/ping?sitemap=' . $encodedSitemapIndexURL))) {
                Shop::Container()->getLogService()->notice('Sitemap ping to Google failed with status ' . $httpStatus);
            }
            if (200 !== ($httpStatus = RequestHelper::http_get_status('http://www.bing.com/ping?sitemap=' . $encodedSitemapIndexURL))) {
                Shop::Container()->getLogService()->notice('Sitemap ping to Bing failed with status ' . $httpStatus);
            }
        }
    }
}

/**
 * @param string $cGoogleImageEinstellung
 * @return string
 */
function getXMLHeader($cGoogleImageEinstellung)
{
    $cHead = '<?xml version="1.0" encoding="UTF-8"?>
            <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';

    if ($cGoogleImageEinstellung === 'Y') {
        $cHead .= ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"';
    }

    $cHead .= ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
            http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';

    return $cHead;
}

/**
 * @param stdClass $artikel
 * @return string|null
 */
function holeGoogleImage($artikel)
{
    $oArtikel           = new Artikel();
    $oArtikel->kArtikel = $artikel->kArtikel;
    $oArtikel->holArtikelAttribute();
    // Prüfe ob Funktionsattribut "artikelbildlink" ART_ATTRIBUT_BILDLINK gesetzt ist
    // Falls ja, lade die Bilder des anderen Artikels
    $oBild = new stdClass();
    if (isset($oArtikel->FunktionsAttribute[ART_ATTRIBUT_BILDLINK])
        && strlen($oArtikel->FunktionsAttribute[ART_ATTRIBUT_BILDLINK]) > 0
    ) {
        $cArtNr = StringHandler::filterXSS($oArtikel->FunktionsAttribute[ART_ATTRIBUT_BILDLINK]);
        $oBild  = Shop::Container()->getDB()->queryPrepared(
            'SELECT tartikelpict.cPfad
                FROM tartikelpict
                JOIN tartikel 
                    ON tartikel.cArtNr = :artNr
                WHERE tartikelpict.kArtikel = tartikel.kArtikel
                GROUP BY tartikelpict.cPfad
                ORDER BY tartikelpict.nNr
                LIMIT 1',
            ['artNr' => $cArtNr],
            \DB\ReturnType::SINGLE_OBJECT
        );
    }

    if (empty($oBild->cPfad)) {
        $oBild = Shop::Container()->getDB()->queryPrepared(
            'SELECT cPfad 
                FROM tartikelpict 
                WHERE kArtikel = :articleID 
                GROUP BY cPfad 
                ORDER BY nNr 
                LIMIT 1',
            ['articleID' => (int)$oArtikel->kArtikel],
            \DB\ReturnType::SINGLE_OBJECT
        );
    }

    return $oBild->cPfad ?? null;
}

/**
 * @return bool
 */
function loescheSitemaps()
{
    if (is_dir(PFAD_ROOT . PFAD_EXPORT) && $dh = opendir(PFAD_ROOT . PFAD_EXPORT)) {
        while (($file = readdir($dh)) !== false) {
            if ($file === 'sitemap_index.xml' || strpos($file, 'sitemap_') !== false) {
                unlink(PFAD_ROOT . PFAD_EXPORT . $file);
            }
        }

        closedir($dh);

        return true;
    }

    return false;
}

/**
 * @param array $nAnzahlURL_arr
 * @param float $fTotalZeit
 */
function baueSitemapReport($nAnzahlURL_arr, $fTotalZeit)
{
    if ($fTotalZeit > 0 && is_array($nAnzahlURL_arr) && count($nAnzahlURL_arr) > 0) {
        $nTotalURL = 0;
        foreach ($nAnzahlURL_arr as $nAnzahlURL) {
            $nTotalURL += $nAnzahlURL;
        }
        $oSitemapReport                     = new stdClass();
        $oSitemapReport->nTotalURL          = $nTotalURL;
        $oSitemapReport->fVerarbeitungszeit = number_format($fTotalZeit, 2);
        $oSitemapReport->dErstellt          = 'NOW()';

        $kSitemapReport = Shop::Container()->getDB()->insert('tsitemapreport', $oSitemapReport);
        $bGZ            = function_exists('gzopen');
        Shop::Container()->getLogService()->debug('Sitemaps Report: ' . var_export($nAnzahlURL_arr, true));
        foreach ($nAnzahlURL_arr as $i => $nAnzahlURL) {
            if ($nAnzahlURL > 0) {
                $oSitemapReportFile                 = new stdClass();
                $oSitemapReportFile->kSitemapReport = $kSitemapReport;
                $oSitemapReportFile->cDatei         = $bGZ
                    ? ('sitemap_' . $i . '.xml.gz')
                    : ('sitemap_' . $i . '.xml');
                $oSitemapReportFile->nAnzahlURL     = $nAnzahlURL;
                $file                               = PFAD_ROOT . PFAD_EXPORT . $oSitemapReportFile->cDatei;
                $oSitemapReportFile->fGroesse       = is_file($file)
                    ? number_format(filesize(PFAD_ROOT . PFAD_EXPORT . $oSitemapReportFile->cDatei) / 1024, 2)
                    : 0;
                Shop::Container()->getDB()->insert('tsitemapreportfile', $oSitemapReportFile);
            }
        }
    }
}

/**
 * @param int        $kKey
 * @param string     $cKey
 * @param string     $lastUpdate
 * @param array      $languages
 * @param int        $langID
 * @param int        $productsPerPage
 * @param array|null $config
 * @return array
 */
function baueExportURL(int $kKey, $cKey, $lastUpdate, $languages, $langID, $productsPerPage, $config = null)
{
    $cURL_arr = [];
    $params   = [];
    Shop::setLanguage($langID);
    $filterConfig = new \Filter\Config();
    $filterConfig->setLanguageID($langID);
    $filterConfig->setLanguages($languages);
    $filterConfig->setConfig($config);
    $filterConfig->setCustomerGroupID(\Session\Session::CustomerGroup()->getID());
    $filterConfig->setBaseURL(Shop::getURL() . '/');
    $naviFilter = new \Filter\ProductFilter($filterConfig, Shop::Container()->getDB(), Shop::Container()->getCache());
    switch ($cKey) {
        case 'kKategorie':
            $params['kKategorie'] = $kKey;
            $naviFilter->initStates($params);
            $filterSeo = $naviFilter->getCategory()->getSeo($langID);
            break;

        case 'kHersteller':
            $params['kHersteller'] = $kKey;
            $naviFilter->initStates($params);
            $filterSeo = $naviFilter->getManufacturer()->getSeo($langID);
            break;

        case 'kSuchanfrage':
            $params['kSuchanfrage'] = $kKey;
            $naviFilter->initStates($params);
            if ($kKey > 0) {
                $oSuchanfrage = Shop::Container()->getDB()->queryPrepared(
                    'SELECT cSuche
                        FROM tsuchanfrage
                        WHERE kSuchanfrage = :ks
                        ORDER BY kSuchanfrage',
                    ['ks' => $kKey],
                    \DB\ReturnType::SINGLE_OBJECT
                );
                if (!empty($oSuchanfrage->cSuche)) {
                    $naviFilter->getSearchQuery()->setID($kKey)->setName($oSuchanfrage->cSuche);
                }
            }
            $filterSeo = $naviFilter->getSearchQuery()->getSeo($langID);
            break;

        case 'kMerkmalWert':
            $params['kMerkmalWert'] = $kKey;
            $naviFilter->initStates($params);
            $filterSeo = $naviFilter->getAttributeValue()->getSeo($langID);
            break;

        case 'kTag':
            $params['kTag'] = $kKey;
            $naviFilter->initStates($params);
            $filterSeo = $naviFilter->getTag()->getSeo($langID);
            break;

        case 'kSuchspecial':
            $params['kSuchspecial'] = $kKey;
            $naviFilter->initStates($params);
            $filterSeo = $naviFilter->getSearchSpecial()->getSeo($langID);
            break;

        default :
            return $cURL_arr;
    }
    $oSuchergebnisse = $naviFilter->generateSearchResults(null, false, (int)$productsPerPage);
    $shopURL         = Shop::getURL();
    $shopURLSSL      = Shop::getURL(true);
    $search          = [$shopURL . '/', $shopURLSSL . '/'];
    $replace         = ['', ''];
    if (($cKey === 'kKategorie' && $kKey > 0) || $oSuchergebnisse->getProductCount() > 0) {
        $cURL_arr[] = makeURL(
            str_replace($search, $replace, $naviFilter->getFilterURL()->getURL()),
            $lastUpdate,
            FREQ_WEEKLY,
            PRIO_NORMAL
        );
    }

    return $cURL_arr;
}

/**
 * @param array $Sprachen
 * @return array
 */
function gibAlleSprachenAssoc($Sprachen)
{
    $oSpracheAssoc_arr = [];
    foreach ($Sprachen as $oSprache) {
        $oSpracheAssoc_arr[$oSprache->cISO] = (int)$oSprache->kSprache;
    }

    return $oSpracheAssoc_arr;
}
