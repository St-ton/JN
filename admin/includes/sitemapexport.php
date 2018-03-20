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
    Jtllog::writeLog('Baue "' . PFAD_EXPORT . 'sitemap_' . $nDatei . '.xml", Datenlaenge "' .
        strlen($data) . '"', JTLLOG_LEVEL_DEBUG
    );
    $conf = Shop::getSettings([CONF_SITEMAP]);
    if (!empty($data)) {
        if (function_exists('gzopen')) {
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
    $cIndex .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
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
    Jtllog::writeLog('Sitemap wird erstellt', JTLLOG_LEVEL_NOTICE);
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
    $Sprachen                = gibAlleSprachen();
    $oSpracheAssoc_arr       = gibAlleSprachenAssoc($Sprachen);
    $defaultLang             = gibStandardsprache(true);
    $defaultLangID           = (int)$defaultLang->kSprache;
    $_SESSION['kSprache']    = $defaultLangID;
    $_SESSION['cISOSprache'] = $defaultLang->cISO;
    setzeSteuersaetze();
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
    $nArtikelProSeite = ((int)$conf['artikeluebersicht']['artikeluebersicht_artikelproseite'] > 0)
        ? (int)$conf['artikeluebersicht']['artikeluebersicht_artikelproseite']
        : 20;
    if ($conf['artikeluebersicht']['artikeluebersicht_erw_darstellung'] === 'Y') {
        $nStdDarstellung = (int)$conf['artikeluebersicht']['artikeluebersicht_erw_darstellung_stdansicht'] > 0
            ? (int)$conf['artikeluebersicht']['artikeluebersicht_erw_darstellung_stdansicht']
            : ERWDARSTELLUNG_ANSICHT_LISTE;
        if ($nStdDarstellung > 0) {
            switch ($nStdDarstellung) {
                case ERWDARSTELLUNG_ANSICHT_LISTE:
                    $nArtikelProSeite = (int)$conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'];
                    break;
                case ERWDARSTELLUNG_ANSICHT_GALERIE:
                    $nArtikelProSeite = (int)$conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung2'];
                    break;
                case ERWDARSTELLUNG_ANSICHT_MOSAIK:
                    $nArtikelProSeite = (int)$conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung3'];
                    break;
            }
        }
    }
    $nDatei         = 0;
    $nSitemap       = 1;
    $nAnzahlURL_arr = [];
    $nSitemapLimit  = 25000;
    $sitemap_data   = '';
    $shopURL        = Shop::getURL();
    $imageBaseURL   = Shop::getImageBaseURL();
    //Hauptseite
    $sitemap_data .= makeURL('', null, FREQ_ALWAYS, PRIO_VERYHIGH);
    //Alte Sitemaps löschen
    loescheSitemaps();
    $andWhere = '';
    // Kindartikel?
    if ($conf['sitemap']['sitemap_varkombi_children_export'] !== 'Y') {
        $andWhere .= ' AND tartikel.kVaterArtikel = 0';
    }
    // Artikelanzeigefilter
    if ((int)$conf['global']['artikel_artikelanzeigefilter'] === EINSTELLUNGEN_ARTIKELANZEIGEFILTER_LAGER) {
        // 'Nur Artikel mit Lagerbestand>0 anzeigen'
        $andWhere .= " AND (tartikel.cLagerBeachten = 'N' OR tartikel.fLagerbestand > 0)";
    } elseif ((int)$conf['global']['artikel_artikelanzeigefilter'] === EINSTELLUNGEN_ARTIKELANZEIGEFILTER_LAGERNULL) {
        // 'Nur Artikel mit Lagerbestand>0 oder deren Lagerbestand<0 werden darf'
        $andWhere .= " AND (tartikel.cLagerBeachten = 'N' 
                            OR tartikel.cLagerKleinerNull = 'Y' 
                            OR tartikel.fLagerbestand > 0)";
    }
    //Artikel STD Sprache
    $modification = $conf['sitemap']['sitemap_insert_lastmod'] === 'Y'
        ? ', tartikel.dLetzteAktualisierung'
        : '';
    $strSQL = "SELECT tartikel.kArtikel, tartikel.cName, tseo.cSeo, tartikel.cArtNr" .
            $modification . "
            FROM tartikel
                LEFT JOIN tartikelsichtbarkeit 
                    ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = :kGrpID 
                LEFT JOIN tseo 
                    ON tseo.cKey = 'kArtikel'
                    AND tseo.kKey = tartikel.kArtikel
                    AND tseo.kSprache = :langID
            WHERE tartikelsichtbarkeit.kArtikel IS NULL" . $andWhere;
    $res = Shop::DB()->queryPrepared(
        $strSQL, 
        [
            'kGrpID' => $defaultCustomerGroupID,
            'langID' => $defaultLangID
        ], 
        NiceDB::RET_QUERYSINGLE
    );
    while (($oArtikel = $res->fetch(PDO::FETCH_OBJ)) !== false) {
        if ($nSitemap > $nSitemapLimit) {
            $nSitemap = 1;
            baueSitemap($nDatei, $sitemap_data);
            ++$nDatei;
            $nAnzahlURL_arr[$nDatei] = 0;
            $sitemap_data            = '';
        }
        // GoogleImages einbinden?
        $cGoogleImage = '';
        if ($conf['sitemap']['sitemap_googleimage_anzeigen'] === 'Y'
            && ($number = MediaImage::getPrimaryNumber(Image::TYPE_PRODUCT, $oArtikel->kArtikel)) !== null
        ) {
            $cGoogleImage = MediaImage::getThumb(
                Image::TYPE_PRODUCT,
                $oArtikel->kArtikel,
                $oArtikel,
                Image::SIZE_LG,
                $number
            );
            if (strlen($cGoogleImage) > 0) {
                $cGoogleImage = $imageBaseURL . $cGoogleImage;
            }
        }
        $cUrl = baueURL($oArtikel, URLART_ARTIKEL);

        if (!isSitemapBlocked($cUrl)) {
            $sitemap_data .= makeURL(
                $cUrl,
                (($conf['sitemap']['sitemap_insert_lastmod'] === 'Y')
                    ? date_format(date_create($oArtikel->dLetzteAktualisierung), 'c')
                    : null),
                FREQ_DAILY,
                PRIO_HIGH,
                $cGoogleImage
            );
            ++$nSitemap;
            if (!isset($nAnzahlURL_arr[$nDatei])) {
                $nAnzahlURL_arr[$nDatei] = 0;
            }
            ++$nAnzahlURL_arr[$nDatei];
            ++$nStat_arr['artikelbild'];
        }
    }
    // Artikel sonstige Sprachen
    foreach ($Sprachen as $SpracheTMP) {
        if ($SpracheTMP->kSprache === $defaultLangID) {
            continue;
        }
        $res = Shop::DB()->queryPrepared(
            "SELECT tartikel.kArtikel, tartikel.dLetzteAktualisierung, tseo.cSeo
               FROM tartikelsprache, tartikel
               JOIN tseo 
                  ON tseo.cKey = 'kArtikel'
                  AND tseo.kKey = tartikel.kArtikel
                  AND tseo.kSprache = :langID
               LEFT JOIN tartikelsichtbarkeit 
                  ON tartikel.kArtikel=tartikelsichtbarkeit.kArtikel
                  AND tartikelsichtbarkeit.kKundengruppe = :kGrpID
               WHERE tartikelsichtbarkeit.kArtikel IS NULL
                  AND tartikel.kArtikel = tartikelsprache.kArtikel
                  AND tartikel.kVaterArtikel = 0 
                  AND tartikelsprache.kSprache = :langID
               ORDER BY tartikel.kArtikel",
            [
                'kGrpID' => $defaultCustomerGroupID,
                'langID' => $SpracheTMP->kSprache
            ],
            NiceDB::RET_QUERYSINGLE
        );
        while (($oArtikel = $res->fetch(PDO::FETCH_OBJ)) !== false) {
            if ($nSitemap > $nSitemapLimit) {
                $nSitemap = 1;
                baueSitemap($nDatei, $sitemap_data);
                ++$nDatei;
                $nAnzahlURL_arr[$nDatei] = 0;
                $sitemap_data            = '';
            }
            $cUrl = baueURL($oArtikel, URLART_ARTIKEL);
            if (!isSitemapBlocked($cUrl)) {
                $sitemap_data .= makeURL(
                    $cUrl,
                    date_format(date_create($oArtikel->dLetzteAktualisierung), 'c'),
                    FREQ_DAILY,
                    PRIO_HIGH
                );
                ++$nSitemap;
                ++$nAnzahlURL_arr[$nDatei];
                ++$nStat_arr['artikelsprache'];
            }
        }
    }

    if ($conf['sitemap']['sitemap_seiten_anzeigen'] === 'Y') {
        // Links alle sprachen
        $res = Shop::DB()->queryPrepared(
            "SELECT tlink.nLinkart, tlinksprache.kLink, tlinksprache.cISOSprache, tlink.bSSL
                     FROM tlink
                     JOIN tlinkgruppe 
                        ON tlink.kLinkgruppe = tlinkgruppe.kLinkgruppe
                     JOIN tlinksprache 
                        ON tlinksprache.kLink = tlink.kLink
                     WHERE tlink.cSichtbarNachLogin = 'N'
                        AND tlink.cNoFollow = 'N'
                        AND tlinkgruppe.cName != 'hidden'
                        AND tlinkgruppe.cTemplatename != 'hidden'
                        AND (tlink.cKundengruppen IS NULL
                          OR tlink.cKundengruppen = 'NULL'
                          OR FIND_IN_SET(:cGrpID, REPLACE(tlink.cKundengruppen, ';', ',')) > 0)
                     ORDER BY tlinksprache.kLink",
            ['cGrpID' => $defaultCustomerGroupID],
            NiceDB::RET_QUERYSINGLE
        );
        while (($tlink = $res->fetch(PDO::FETCH_OBJ)) !== false) {
            if (spracheEnthalten($tlink->cISOSprache, $Sprachen)) {
                $oSeo = Shop::DB()->queryPrepared(
                    "SELECT cSeo
                        FROM tseo
                        WHERE cKey = 'kLink'
                            AND kKey = :linkID
                            AND kSprache = :langID",
                    [
                        'linkID' => $tlink->kLink,
                        'langID' => $oSpracheAssoc_arr[$tlink->cISOSprache]
                    ],
                    NiceDB::RET_SINGLE_OBJECT
                );
                if (isset($oSeo->cSeo) && strlen($oSeo->cSeo) > 0) {
                    $tlink->cSeo = $oSeo->cSeo;
                }

                if (isset($tlink->cSeo) && strlen($tlink->cSeo) > 0) {
                    if ($nSitemap > $nSitemapLimit) {
                        $nSitemap = 1;
                        baueSitemap($nDatei, $sitemap_data);
                        ++$nDatei;
                        $nAnzahlURL_arr[$nDatei] = 0;
                        $sitemap_data            = '';
                    }

                    $tlink->cLocalizedSeo[$tlink->cISOSprache] = $tlink->cSeo ?? null;
                    $link                                      = baueURL($tlink, URLART_SEITE);
                    if (strlen($tlink->cSeo) > 0) {
                        $link = $tlink->cSeo;
                    } elseif ($_SESSION['cISOSprache'] !== $tlink->cISOSprache) {
                        $link .= '&lang=' . $tlink->cISOSprache;
                    }
                    if (!isSitemapBlocked($link)) {
                        $sitemap_data .= makeURL($link, null, FREQ_MONTHLY, PRIO_LOW, '', (int)$tlink->bSSL === 2);
                        ++$nSitemap;
                        ++$nAnzahlURL_arr[$nDatei];
                        ++$nStat_arr['link'];
                    }
                }
            }
        }
    }
    if ($conf['sitemap']['sitemap_kategorien_anzeigen'] === 'Y') {
        $categoryHelper = new KategorieListe();
        // Kategorien STD Sprache
        $res = Shop::DB()->queryPrepared(
            "SELECT tkategorie.kKategorie, tseo.cSeo, tkategorie.dLetzteAktualisierung
                 FROM tkategorie
                 JOIN tseo 
                    ON tseo.cKey = 'kKategorie'
                    AND tseo.kKey = tkategorie.kKategorie
                    AND tseo.kSprache = :langID
                 LEFT JOIN tkategoriesichtbarkeit 
                    ON tkategorie.kKategorie = tkategoriesichtbarkeit.kKategorie
                    AND tkategoriesichtbarkeit.kKundengruppe = :cGrpID
                 WHERE tkategoriesichtbarkeit.kKategorie IS NULL
                 ORDER BY tkategorie.kKategorie",
            [
                'langID' => $defaultLangID,
                'cGrpID' => $defaultCustomerGroupID
            ],
            NiceDB::RET_QUERYSINGLE
        );
        while (($tkategorie = $res->fetch(PDO::FETCH_OBJ)) !== false) {
            $cURL_arr = baueExportURL(
                $tkategorie->kKategorie,
                'kKategorie',
                date_format(date_create($tkategorie->dLetzteAktualisierung), 'c'),
                $Sprachen,
                $defaultLangID,
                $nArtikelProSeite,
                $conf
            );
            foreach ($cURL_arr as $cURL) {
                if ($categoryHelper->nichtLeer(
                    $tkategorie->kKategorie,
                    $defaultCustomerGroupID
                    ) === true
                ) {
                    if ($nSitemap > $nSitemapLimit) {
                        $nSitemap = 1;
                        baueSitemap($nDatei, $sitemap_data);
                        ++$nDatei;
                        $nAnzahlURL_arr[$nDatei] = 0;
                        $sitemap_data            = '';
                    }
                    if (!isSitemapBlocked($cURL)) {
                        $sitemap_data .= $cURL;
                        ++$nSitemap;
                        ++$nAnzahlURL_arr[$nDatei];
                        ++$nStat_arr['kategorie'];
                    }
                }
            }
        }
        // Kategorien sonstige Sprachen
        foreach ($Sprachen as $SpracheTMP) {
            $strSQL = "SELECT tkategorie.kKategorie, tkategorie.dLetzteAktualisierung, tseo.cSeo
                      FROM tkategoriesprache, tkategorie
                      JOIN tseo 
                          ON tseo.cKey = 'kKategorie'
                          AND tseo.kKey = tkategorie.kKategorie
                          AND tseo.kSprache = :langID
                      LEFT JOIN tkategoriesichtbarkeit 
                          ON tkategorie.kKategorie = tkategoriesichtbarkeit.kKategorie
                          AND tkategoriesichtbarkeit.kKundengruppe = :cGrpID 
                      WHERE tkategoriesichtbarkeit.kKategorie IS NULL
                          AND tkategorie.kKategorie = tkategoriesprache.kKategorie
                          AND tkategoriesprache.kSprache = :langID
                      ORDER BY tkategorie.kKategorie";
            $res = Shop::DB()->queryPrepared(
                $strSQL,
                [
                    'langID' => $SpracheTMP->kSprache,
                    'cGrpID' => $defaultCustomerGroupID
                ],
                NiceDB::RET_QUERYSINGLE
            );
            while (($tkategorie = $res->fetch(PDO::FETCH_OBJ)) !== false) {
                $cURL_arr = baueExportURL(
                    $tkategorie->kKategorie,
                    'kKategorie',
                    date_format(date_create($tkategorie->dLetzteAktualisierung), 'c'),
                    $Sprachen,
                    $SpracheTMP->kSprache,
                    $nArtikelProSeite,
                    $conf
                );
                foreach ($cURL_arr as $cURL) { // X viele Seiten durchlaufen
                    if ($categoryHelper->nichtLeer(
                        $tkategorie->kKategorie,
                        $defaultCustomerGroupID
                        ) === true
                    ) {
                        if ($nSitemap > $nSitemapLimit) {
                            $nSitemap = 1;
                            baueSitemap($nDatei, $sitemap_data);
                            ++$nDatei;
                            $nAnzahlURL_arr[$nDatei] = 0;
                            $sitemap_data            = '';
                        }
                        if (!isSitemapBlocked($cURL)) {
                            $sitemap_data .= $cURL;
                            ++$nSitemap;
                            ++$nAnzahlURL_arr[$nDatei];
                            ++$nStat_arr['kategoriesprache'];
                        }
                    }
                }
            }
        }
    }
    if ($conf['sitemap']['sitemap_tags_anzeigen'] === 'Y') {
        // Tags
        $res = Shop::DB()->queryPrepared(
            "SELECT ttag.kTag, ttag.cName, tseo.cSeo
               FROM ttag               
               JOIN tseo 
                  ON tseo.cKey = 'kTag'
                  AND tseo.kKey = ttag.kTag
                  AND tseo.kSprache = :langID
               WHERE ttag.kSprache = :langID
                  AND ttag.nAktiv = 1
               ORDER BY ttag.kTag",
            ['langID' => $defaultLangID],
            NiceDB::RET_QUERYSINGLE
        );
        while (($oTag = $res->fetch(PDO::FETCH_OBJ)) !== false) {
            $cURL_arr = baueExportURL(
                $oTag->kTag,
                'kTag',
                null,
                $Sprachen,
                $defaultLangID,
                $nArtikelProSeite,
                $conf
            );
            foreach ($cURL_arr as $cURL) {
                if ($nSitemap > $nSitemapLimit) {
                    $nSitemap = 1;
                    baueSitemap($nDatei, $sitemap_data);
                    ++$nDatei;
                    $nAnzahlURL_arr[$nDatei] = 0;
                    $sitemap_data            = '';
                }
                if (!isSitemapBlocked($cURL)) {
                    $sitemap_data .= $cURL;
                    ++$nSitemap;
                    ++$nAnzahlURL_arr[$nDatei];
                    ++$nStat_arr['tag'];
                }
            }
        }
        // Tags sonstige Sprachen
        foreach ($Sprachen as $SpracheTMP) {
            if ($SpracheTMP->kSprache === $defaultLangID) {
                continue;
            }
            $res = Shop::DB()->queryPrepared(
                  "SELECT ttag.kTag, ttag.cName, tseo.cSeo
                      FROM ttag
                      JOIN tseo 
                          ON tseo.cKey = 'kTag'
                          AND tseo.kKey = ttag.kTag
                          AND tseo.kSprache = :langID
                      WHERE ttag.kSprache = :langID
                          AND ttag.nAktiv = 1
                      ORDER BY ttag.kTag",
                ['langID' => $SpracheTMP->kSprache],
                NiceDB::RET_QUERYSINGLE
            );
            while (($oTag = $res->fetch(PDO::FETCH_OBJ)) !== false) {
                $cURL_arr = baueExportURL(
                    $oTag->kTag,
                    'kTag',
                    null,
                    $Sprachen,
                    $SpracheTMP->kSprache,
                    $nArtikelProSeite,
                    $conf
                );
                foreach ($cURL_arr as $cURL) {
                    // X viele Seiten durchlaufen
                    if ($nSitemap > $nSitemapLimit) {
                        $nSitemap = 1;
                        baueSitemap($nDatei, $sitemap_data);
                        ++$nDatei;
                        $nAnzahlURL_arr[$nDatei] = 0;
                        $sitemap_data            = '';
                    }
                    if (!isSitemapBlocked($cURL)) {
                        $sitemap_data .= $cURL;
                        ++$nSitemap;
                        ++$nAnzahlURL_arr[$nDatei];
                        ++$nStat_arr['tagsprache'];
                    }
                }
            }
        }
    }
    if ($conf['sitemap']['sitemap_hersteller_anzeigen'] === 'Y') {
        // Hersteller
        $res = Shop::DB()->queryPrepared(
            "SELECT thersteller.kHersteller, thersteller.cName, tseo.cSeo
                 FROM thersteller
                 JOIN tseo 
                    ON tseo.cKey = 'kHersteller'
                    AND tseo.kKey = thersteller.kHersteller
                    AND tseo.kSprache = :langID
                 ORDER BY thersteller.kHersteller",
            ['langID' => $defaultLangID],
            NiceDB::RET_QUERYSINGLE
        );
        while (($oHersteller = $res->fetch(PDO::FETCH_OBJ)) !== false) {
            $cURL_arr = baueExportURL(
                $oHersteller->kHersteller,
                'kHersteller',
                null,
                $Sprachen,
                $defaultLangID,
                $nArtikelProSeite,
                $conf
            );
            foreach ($cURL_arr as $cURL) {
                if ($nSitemap > $nSitemapLimit) {
                    $nSitemap = 1;
                    baueSitemap($nDatei, $sitemap_data);
                    ++$nDatei;
                    $nAnzahlURL_arr[$nDatei] = 0;
                    $sitemap_data            = '';
                }
                if (!isSitemapBlocked($cURL)) {
                    $sitemap_data .= $cURL;
                    ++$nSitemap;
                    ++$nAnzahlURL_arr[$nDatei];
                    ++$nStat_arr['hersteller'];
                }
            }
        }
    }
    if ($conf['sitemap']['sitemap_livesuche_anzeigen'] === 'Y') {
        // Livesuche STD Sprache
        $res = Shop::DB()->queryPrepared(
            "SELECT tsuchanfrage.kSuchanfrage, tseo.cSeo, tsuchanfrage.dZuletztGesucht
                 FROM tsuchanfrage
                 JOIN tseo 
                    ON tseo.cKey = 'kSuchanfrage'
                    AND tseo.kKey = tsuchanfrage.kSuchanfrage
                    AND tseo.kSprache = :langID
                 WHERE tsuchanfrage.kSprache = :langID
                    AND tsuchanfrage.nAktiv = 1
                 ORDER BY tsuchanfrage.kSuchanfrage",
            ['langID' => $defaultLangID],
            NiceDB::RET_QUERYSINGLE
        );
        while (($oSuchanfrage = $res->fetch(PDO::FETCH_OBJ)) !== false) {
            $cURL_arr = baueExportURL(
                $oSuchanfrage->kSuchanfrage,
                'kSuchanfrage',
                null,
                $Sprachen,
                $defaultLangID,
                $nArtikelProSeite,
                $conf
            );
            foreach ($cURL_arr as $cURL) {
                if ($nSitemap > $nSitemapLimit) {
                    $nSitemap = 1;
                    baueSitemap($nDatei, $sitemap_data);
                    ++$nDatei;
                    $nAnzahlURL_arr[$nDatei] = 0;
                    $sitemap_data            = '';
                }
                if (!isSitemapBlocked($cURL)) {
                    $sitemap_data .= $cURL;
                    ++$nSitemap;
                    ++$nAnzahlURL_arr[$nDatei];
                    ++$nStat_arr['livesuche'];
                }
            }
        }
        // Livesuche sonstige Sprachen
        foreach ($Sprachen as $SpracheTMP) {
            if ($SpracheTMP->kSprache === $defaultLangID) {
                continue;
            }
            $res = Shop::DB()->queryPrepared(
                "SELECT tsuchanfrage.kSuchanfrage, tseo.cSeo, tsuchanfrage.dZuletztGesucht
                     FROM tsuchanfrage
                     JOIN tseo 
                        ON tseo.cKey = 'kSuchanfrage'
                        AND tseo.kKey = tsuchanfrage.kSuchanfrage
                        AND tseo.kSprache = :langID
                     WHERE tsuchanfrage.kSprache = :langID
                        AND tsuchanfrage.nAktiv = 1
                     ORDER BY tsuchanfrage.kSuchanfrage",
                ['langID' => $SpracheTMP->kSprache],
                NiceDB::RET_QUERYSINGLE
            );
            while (($oSuchanfrage = $res->fetch(PDO::FETCH_OBJ)) !== false) {
                $cURL_arr = baueExportURL(
                    $oSuchanfrage->kSuchanfrage,
                    'kSuchanfrage',
                    null,
                    $Sprachen,
                    $SpracheTMP->kSprache,
                    $nArtikelProSeite,
                    $conf
                );
                foreach ($cURL_arr as $cURL) { // X viele Seiten durchlaufen
                    if ($nSitemap > $nSitemapLimit) {
                        $nSitemap = 1;
                        baueSitemap($nDatei, $sitemap_data);
                        ++$nDatei;
                        $nAnzahlURL_arr[$nDatei] = 0;
                        $sitemap_data            = '';
                    }
                    if (!isSitemapBlocked($cURL)) {
                        $sitemap_data .= $cURL;
                        ++$nSitemap;
                        ++$nAnzahlURL_arr[$nDatei];
                        ++$nStat_arr['livesuchesprache'];
                    }
                }
            }
        }
    }
    if ($conf['sitemap']['sitemap_globalemerkmale_anzeigen'] === 'Y') {
        // Merkmale STD Sprache
        $res = Shop::DB()->query(
            "SELECT tmerkmal.cName, tmerkmal.kMerkmal, tmerkmalwertsprache.cWert, 
                tseo.cSeo, tmerkmalwert.kMerkmalWert
                FROM tmerkmal
                JOIN tmerkmalwert 
                    ON tmerkmalwert.kMerkmal = tmerkmal.kMerkmal
                JOIN tmerkmalwertsprache 
                    ON tmerkmalwertsprache.kMerkmalWert = tmerkmalwert.kMerkmalWert
                JOIN tartikelmerkmal 
                    ON tartikelmerkmal.kMerkmalWert = tmerkmalwert.kMerkmalWert
                JOIN tseo 
                    ON tseo.cKey = 'kMerkmalWert'
                    AND tseo.kKey = tmerkmalwert.kMerkmalWert
                WHERE tmerkmal.nGlobal = 1
                GROUP BY tmerkmalwert.kMerkmalWert
                ORDER BY tmerkmal.kMerkmal, tmerkmal.cName",
            NiceDB::RET_QUERYSINGLE
        );
        while (($oMerkmalWert = $res->fetch(PDO::FETCH_OBJ)) !== false) {
            $cURL_arr = baueExportURL(
                $oMerkmalWert->kMerkmalWert,
                'kMerkmalWert',
                null,
                $Sprachen,
                $defaultLangID,
                $nArtikelProSeite,
                $conf
            );
            foreach ($cURL_arr as $cURL) {
                if ($nSitemap > $nSitemapLimit) {
                    $nSitemap = 1;
                    baueSitemap($nDatei, $sitemap_data);
                    ++$nDatei;
                    $nAnzahlURL_arr[$nDatei] = 0;
                    $sitemap_data            = '';
                }
                if (!isSitemapBlocked($cURL)) {
                    $sitemap_data .= $cURL;
                    ++$nSitemap;
                    ++$nAnzahlURL_arr[$nDatei];
                    ++$nStat_arr['merkmal'];
                }
            }
        }
        // Merkmale sonstige Sprachen
        foreach ($Sprachen as $SpracheTMP) {
            if ($SpracheTMP->kSprache === $defaultLangID) {
                continue;
            }
            $res = Shop::DB()->queryPrepared(
                    "SELECT tmerkmalsprache.cName, tmerkmalsprache.kMerkmal, tmerkmalwertsprache.cWert, 
                        tseo.cSeo, tmerkmalwert.kMerkmalWert
                        FROM tmerkmalsprache
                        JOIN tmerkmal 
                            ON tmerkmal.kMerkmal = tmerkmalsprache.kMerkmal
                        JOIN tmerkmalwert 
                            ON tmerkmalwert.kMerkmal = tmerkmalsprache.kMerkmal
                        JOIN tmerkmalwertsprache 
                            ON tmerkmalwertsprache.kMerkmalWert = tmerkmalwert.kMerkmalWert
                            AND tmerkmalwertsprache.kSprache = tmerkmalsprache.kSprache
                        JOIN tartikelmerkmal 
                            ON tartikelmerkmal.kMerkmalWert = tmerkmalwert.kMerkmalWert
                        JOIN tseo 
                            ON tseo.cKey = 'kMerkmalWert'
                            AND tseo.kKey = tmerkmalwert.kMerkmalWert
                            AND tseo.kSprache = tmerkmalsprache.kSprache
                        WHERE tmerkmal.nGlobal = 1
                            AND tmerkmalsprache.kSprache = :langID
                        GROUP BY tmerkmalwert.kMerkmalWert
                        ORDER BY tmerkmal.kMerkmal, tmerkmal.cName",
                ['langID' => $SpracheTMP->kSprache],
                NiceDB::RET_QUERYSINGLE
            );
            while (($oMerkmalWert = $res->fetch(PDO::FETCH_OBJ)) !== false) {
                $cURL_arr = baueExportURL(
                    $oMerkmalWert->kMerkmalWert,
                    'kMerkmalWert',
                    null,
                    $Sprachen,
                    $SpracheTMP->kSprache,
                    $nArtikelProSeite,
                    $conf
                );
                foreach ($cURL_arr as $cURL) {
                    if ($nSitemap > $nSitemapLimit) {
                        $nSitemap = 1;
                        baueSitemap($nDatei, $sitemap_data);
                        ++$nDatei;
                        $nAnzahlURL_arr[$nDatei] = 0;
                        $sitemap_data            = '';
                    }
                    if (!isSitemapBlocked($cURL)) {
                        $sitemap_data .= $cURL;
                        ++$nSitemap;
                        ++$nAnzahlURL_arr[$nDatei];
                        ++$nStat_arr['merkmalsprache'];
                    }
                }
            }
        }
    }
    if ($conf['sitemap']['sitemap_news_anzeigen'] === 'Y') {
        $res = Shop::DB()->query(
            "SELECT tnews.*, tseo.cSeo
                FROM tnews
                JOIN tseo 
                    ON tseo.cKey = 'kNews'
                    AND tseo.kKey = tnews.kNews
                    AND tseo.kSprache = tnews.kSprache
                WHERE tnews.nAktiv = 1
                    AND tnews.dGueltigVon <= now()
                    AND (tnews.cKundengruppe LIKE '%;-1;%'
                    OR FIND_IN_SET('" . Session::CustomerGroup()->getID() . "', REPLACE(tnews.cKundengruppe, ';',',')) > 0) 
                    ORDER BY tnews.dErstellt",
            NiceDB::RET_QUERYSINGLE
        );
        while (($oNews = $res->fetch(PDO::FETCH_OBJ)) !== false) {
            $cURL = makeURL(
                baueURL($oNews, URLART_NEWS),
                date_format(date_create($oNews->dGueltigVon), 'c'),
                FREQ_DAILY,
                PRIO_HIGH
            );
            if ($nSitemap > $nSitemapLimit) {
                $nSitemap = 1;
                baueSitemap($nDatei, $sitemap_data);
                ++$nDatei;
                $nAnzahlURL_arr[$nDatei] = 0;
                $sitemap_data            = '';
            }
            if (!isSitemapBlocked($cURL)) {
                $sitemap_data .= $cURL;
                ++$nSitemap;
                ++$nAnzahlURL_arr[$nDatei];
                ++$nStat_arr['news'];
            }
        }
    }
    if ($conf['sitemap']['sitemap_newskategorien_anzeigen'] === 'Y') {
        $res = Shop::DB()->query(
            "SELECT tnewskategorie.*, tseo.cSeo
                 FROM tnewskategorie
                 JOIN tseo 
                    ON tseo.cKey = 'kNewsKategorie'
                    AND tseo.kKey = tnewskategorie.kNewsKategorie
                    AND tseo.kSprache = tnewskategorie.kSprache
                 WHERE tnewskategorie.nAktiv = 1",
            NiceDB::RET_QUERYSINGLE
        );

        while (($oNewsKategorie = $res->fetch(PDO::FETCH_OBJ)) !== false) {
            $cURL = makeURL(
                baueURL($oNewsKategorie, URLART_NEWSKATEGORIE),
                date_format(date_create($oNewsKategorie->dLetzteAktualisierung), 'c'),
                FREQ_DAILY,
                PRIO_HIGH
            );
            if ($nSitemap > $nSitemapLimit) {
                $nSitemap = 1;
                baueSitemap($nDatei, $sitemap_data);
                ++$nDatei;
                $nAnzahlURL_arr[$nDatei] = 0;
                $sitemap_data            = '';
            }
            if (!isSitemapBlocked($cURL)) {
                $sitemap_data .= $cURL;
                ++$nSitemap;
                ++$nAnzahlURL_arr[$nDatei];
                ++$nStat_arr['newskategorie'];
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
            if (200 !== ($httpStatus = http_get_status('http://www.google.com/webmasters/tools/ping?sitemap=' . $encodedSitemapIndexURL))) {
                Jtllog::writeLog('Sitemap ping to Google failed with status ' . $httpStatus, JTLLOG_LEVEL_NOTICE);
            }
            if (200 !== ($httpStatus = http_get_status('http://www.bing.com/ping?sitemap=' . $encodedSitemapIndexURL))) {
                Jtllog::writeLog('Sitemap ping to Bing failed with status ' . $httpStatus, JTLLOG_LEVEL_NOTICE);
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
        $oBild  = Shop::DB()->queryPrepared(
            "SELECT tartikelpict.cPfad
                FROM tartikelpict
                JOIN tartikel 
                    ON tartikel.cArtNr = :artNr
                WHERE tartikelpict.kArtikel = tartikel.kArtikel
                GROUP BY tartikelpict.cPfad
                ORDER BY tartikelpict.nNr
                LIMIT 1",
            ['artNr' => $cArtNr],
            NiceDB::RET_SINGLE_OBJECT
        );
    }

    if (empty($oBild->cPfad)) {
        $oBild = Shop::DB()->queryPrepared(
            'SELECT cPfad 
                FROM tartikelpict 
                WHERE kArtikel = :articleID 
                GROUP BY cPfad 
                ORDER BY nNr 
                LIMIT 1',
            ['articleID' => (int)$oArtikel->kArtikel],
            NiceDB::RET_SINGLE_OBJECT
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
        $oSitemapReport->dErstellt          = 'now()';

        $kSitemapReport = Shop::DB()->insert('tsitemapreport', $oSitemapReport);
        $bGZ            = function_exists('gzopen');
        Jtllog::writeLog('Sitemaps Report: ' . var_export($nAnzahlURL_arr, true), JTLLOG_LEVEL_DEBUG);
        foreach ($nAnzahlURL_arr as $i => $nAnzahlURL) {
            if ($nAnzahlURL > 0) {
                $oSitemapReportFile                 = new stdClass();
                $oSitemapReportFile->kSitemapReport = $kSitemapReport;
                $oSitemapReportFile->cDatei         = $bGZ
                    ? ('sitemap_' . $i . '.xml.gz')
                    : ('sitemap_' . $i . '.xml');
                $oSitemapReportFile->nAnzahlURL = $nAnzahlURL;
                $file                           = PFAD_ROOT . PFAD_EXPORT . $oSitemapReportFile->cDatei;
                $oSitemapReportFile->fGroesse   = is_file($file)
                    ? number_format(filesize(PFAD_ROOT . PFAD_EXPORT . $oSitemapReportFile->cDatei) / 1024, 2)
                    : 0;
                Shop::DB()->insert('tsitemapreportfile', $oSitemapReportFile);
            }
        }
    }
}

/**
 * @param int        $kKey
 * @param string     $cKey
 * @param string     $dLetzteAktualisierung
 * @param array      $oSprach_arr
 * @param int        $kSprache
 * @param int        $nArtikelProSeite
 * @param array|null $config
 * @return array
 */
function baueExportURL($kKey, $cKey, $dLetzteAktualisierung, $oSprach_arr, $kSprache, $nArtikelProSeite, $config = null)
{
    $cURL_arr       = [];
    $params         = [];
    $kKey           = (int)$kKey;

    Shop::setLanguage($kSprache);
    $naviFilter = new ProductFilter($oSprach_arr, $kSprache, $config);
    switch ($cKey) {
        case 'kKategorie':
            $params['kKategorie'] = $kKey;
            $naviFilter->initStates($params);
            $filterSeo = $naviFilter->getCategory()->getSeo($kSprache);
            break;

        case 'kHersteller':
            $params['kHersteller'] = $kKey;
            $naviFilter->initStates($params);
            $filterSeo = $naviFilter->getManufacturer()->getSeo($kSprache);
            break;

        case 'kSuchanfrage':
            $params['kSuchanfrage'] = $kKey;
            $naviFilter->initStates($params);
            if ($kKey > 0) {
                $oSuchanfrage = Shop::DB()->queryPrepared(
                    "SELECT cSuche
                        FROM tsuchanfrage
                        WHERE kSuchanfrage = :ks
                        ORDER BY kSuchanfrage",
                    ['ks' => $kKey],
                    NiceDB::RET_SINGLE_OBJECT
                );
                if (!empty($oSuchanfrage->cSuche)) {
                    $naviFilter->getSearchQuery()->setID($kKey)->setName($oSuchanfrage->cSuche);
                }
            }
            $filterSeo = $naviFilter->getSearchQuery()->getSeo($kSprache);
            break;

        case 'kMerkmalWert':
            $params['kMerkmalWert'] = $kKey;
            $naviFilter->initStates($params);
            $filterSeo = $naviFilter->getAttributeValue()->getSeo($kSprache);
            break;

        case 'kTag':
            $params['kTag'] = $kKey;
            $naviFilter->initStates($params);
            $filterSeo = $naviFilter->getTag()->getSeo($kSprache);
            break;

        case 'kSuchspecial':
            $params['kSuchspecial'] = $kKey;
            $naviFilter->initStates($params);
            $filterSeo = $naviFilter->getSearchSpecial()->getSeo($kSprache);
            break;

        default :
            return $cURL_arr;
    }
    $oSuchergebnisse = $naviFilter->getProducts(true, null, false, (int)$nArtikelProSeite);
    $shopURL         = Shop::getURL();
    $shopURLSSL      = Shop::getURL(true);
    $search          = [$shopURL . '/', $shopURLSSL . '/'];
    $replace         = ['', ''];
    if (($cKey === 'kKategorie' && $kKey > 0) || $oSuchergebnisse->getProductCount() > 0) {
        $cURL_arr[] = makeURL(
            str_replace($search, $replace, $naviFilter->getFilterURL()->getURL()),
            $dLetzteAktualisierung,
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
