<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param string $nDatei
 * @param mixed  $data
 * @deprecated since 5.0.0
 */
function baueSitemap($nDatei, $data)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    Shop::Container()->getLogService()->debug(
        'Baue "' . PFAD_EXPORT . 'sitemap_' .
        $nDatei . '.xml", Datenlaenge ' . strlen($data)
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
 * @param string $nDatei
 * @param bool   $bGZ
 * @return string
 * @deprecated since 5.0.0
 */
function baueSitemapIndex($nDatei, $bGZ)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $shopURL = Shop::getURL();
    $conf    = Shop::getSettings([CONF_SITEMAP]);
    $cIndex  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $cIndex .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    for ($i = 0; $i <= $nDatei; ++$i) {
        if ($bGZ) {
            $cIndex .= '<sitemap><loc>' .
                StringHandler::htmlentities($shopURL . '/' . PFAD_EXPORT . 'sitemap_' . $i . '.xml.gz') .
                '</loc>' .
                ((!isset($conf['sitemap']['sitemap_insert_lastmod'])
                    || $conf['sitemap']['sitemap_insert_lastmod'] === 'Y')
                    ? ('<lastmod>' . StringHandler::htmlentities(date('Y-m-d')) . '</lastmod>') :
                    '') .
                '</sitemap>' . "\n";
        } else {
            $cIndex .= '<sitemap><loc>' . StringHandler::htmlentities($shopURL . '/' .
                    PFAD_EXPORT . 'sitemap_' . $i . '.xml') . '</loc>' .
                ((!isset($conf['sitemap']['sitemap_insert_lastmod'])
                    || $conf['sitemap']['sitemap_insert_lastmod'] === 'Y')
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
 * @deprecated since 5.0.0
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
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
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
 * @deprecated since 5.0.0
 */
function spracheEnthalten($cISO, $Sprachen)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
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
 * @deprecated since 5.0.0
 */
function isSitemapBlocked($cUrl)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
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
 * @deprecated since 5.0.0
 */
function generateSitemapXML()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
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
    $addChangeFreq = $conf['sitemap']['sitemap_insert_changefreq'] === 'Y';
    $addPriority   = $conf['sitemap']['sitemap_insert_priority'] === 'Y';
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
    $imageBaseURL   = Shop::getImageBaseURL();
    //Hauptseite
    $sitemap_data .= makeURL('', null, $addChangeFreq ? FREQ_ALWAYS : null, $addPriority ? PRIO_VERYHIGH : null);
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
    $res          = Shop::Container()->getDB()->queryPrepared(
        "SELECT tartikel.kArtikel, tartikel.cName, tseo.cSeo, tartikel.cArtNr" .
        $modification . "
            FROM tartikel
                LEFT JOIN tartikelsichtbarkeit 
                    ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = :kGrpID 
                LEFT JOIN tseo 
                    ON tseo.cKey = 'kArtikel'
                    AND tseo.kKey = tartikel.kArtikel
                    AND tseo.kSprache = :langID
            WHERE tartikelsichtbarkeit.kArtikel IS NULL" . $andWhere,
        [
            'kGrpID' => $defaultCustomerGroupID,
            'langID' => $defaultLangID
        ],
        \DB\ReturnType::QUERYSINGLE
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
        $cUrl = UrlHelper::buildURL($oArtikel, URLART_ARTIKEL);

        if (!isSitemapBlocked($cUrl)) {
            $sitemap_data .= makeURL(
                $cUrl,
                (($conf['sitemap']['sitemap_insert_lastmod'] === 'Y')
                    ? date_format(date_create($oArtikel->dLetzteAktualisierung), 'c')
                    : null),
                $addChangeFreq ? FREQ_DAILY : null,
                $addPriority ? PRIO_HIGH : null,
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
        $res = Shop::Container()->getDB()->queryPrepared(
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
            \DB\ReturnType::QUERYSINGLE
        );
        while (($oArtikel = $res->fetch(PDO::FETCH_OBJ)) !== false) {
            if ($nSitemap > $nSitemapLimit) {
                $nSitemap = 1;
                baueSitemap($nDatei, $sitemap_data);
                ++$nDatei;
                $nAnzahlURL_arr[$nDatei] = 0;
                $sitemap_data            = '';
            }
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
            $cUrl = UrlHelper::buildURL($oArtikel, URLART_ARTIKEL);
            if (!isSitemapBlocked($cUrl)) {
                $sitemap_data .= makeURL(
                    $cUrl,
                    date_format(date_create($oArtikel->dLetzteAktualisierung), 'c'),
                    $addChangeFreq ? FREQ_DAILY : null,
                    $addPriority ? PRIO_HIGH : null,
                    $cGoogleImage
                );
                ++$nSitemap;
                ++$nAnzahlURL_arr[$nDatei];
                ++$nStat_arr['artikelsprache'];
            }
        }
    }

    if ($conf['sitemap']['sitemap_seiten_anzeigen'] === 'Y') {
        // Links alle sprachen
        $res = Shop::Container()->getDB()->queryPrepared(
            "SELECT tlink.nLinkart, tlinksprache.kLink, tlinksprache.cISOSprache, tlink.bSSL
                FROM tlink
                JOIN tlinkgroupassociations
                    ON tlinkgroupassociations.linkID = tlink.kLink
                JOIN tlinkgruppe 
                    ON tlinkgroupassociations.linkGroupID = tlinkgruppe.kLinkgruppe
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
            \DB\ReturnType::QUERYSINGLE
        );
        while (($tlink = $res->fetch(PDO::FETCH_OBJ)) !== false) {
            if (spracheEnthalten($tlink->cISOSprache, $Sprachen)) {
                $oSeo = Shop::Container()->getDB()->queryPrepared(
                    "SELECT cSeo
                        FROM tseo
                        WHERE cKey = 'kLink'
                            AND kKey = :linkID
                            AND kSprache = :langID",
                    [
                        'linkID' => $tlink->kLink,
                        'langID' => $oSpracheAssoc_arr[$tlink->cISOSprache]
                    ],
                    \DB\ReturnType::SINGLE_OBJECT
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
                    $link                                      = UrlHelper::buildURL($tlink, URLART_SEITE);
                    if (strlen($tlink->cSeo) > 0) {
                        $link = $tlink->cSeo;
                    } elseif ($_SESSION['cISOSprache'] !== $tlink->cISOSprache) {
                        $link .= '&lang=' . $tlink->cISOSprache;
                    }
                    if (!isSitemapBlocked($link)) {
                        $sitemap_data .= makeURL(
                            $link,
                            null,
                            $addChangeFreq ? FREQ_MONTHLY : null,
                            $addPriority ? PRIO_LOW : null,
                            '',
                            (int)$tlink->bSSL === 2
                        );
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
        $res = Shop::Container()->getDB()->queryPrepared(
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
            \DB\ReturnType::QUERYSINGLE
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
                if ($categoryHelper->nichtLeer($tkategorie->kKategorie, $defaultCustomerGroupID) === true) {
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
            $res = Shop::Container()->getDB()->queryPrepared(
                "SELECT tkategorie.kKategorie, tkategorie.dLetzteAktualisierung, tseo.cSeo
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
                    ORDER BY tkategorie.kKategorie",
                [
                    'langID' => $SpracheTMP->kSprache,
                    'cGrpID' => $defaultCustomerGroupID
                ],
                \DB\ReturnType::QUERYSINGLE
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
                    if ($categoryHelper->nichtLeer($tkategorie->kKategorie, $defaultCustomerGroupID) === true) {
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
        $res = Shop::Container()->getDB()->queryPrepared(
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
            \DB\ReturnType::QUERYSINGLE
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
            $res = Shop::Container()->getDB()->queryPrepared(
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
                \DB\ReturnType::QUERYSINGLE
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
        $res = Shop::Container()->getDB()->queryPrepared(
            "SELECT thersteller.kHersteller, thersteller.cName, tseo.cSeo
                FROM thersteller
                JOIN tseo 
                    ON tseo.cKey = 'kHersteller'
                    AND tseo.kKey = thersteller.kHersteller
                    AND tseo.kSprache = :langID
                ORDER BY thersteller.kHersteller",
            ['langID' => $defaultLangID],
            \DB\ReturnType::QUERYSINGLE
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
        $res = Shop::Container()->getDB()->queryPrepared(
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
            \DB\ReturnType::QUERYSINGLE
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
            $res = Shop::Container()->getDB()->queryPrepared(
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
                \DB\ReturnType::QUERYSINGLE
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
        $res = Shop::Container()->getDB()->query(
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
            \DB\ReturnType::QUERYSINGLE
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
            $res = Shop::Container()->getDB()->queryPrepared(
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
                \DB\ReturnType::QUERYSINGLE
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
        $res = Shop::Container()->getDB()->query(
            "SELECT tnews.*, tseo.cSeo, tseo.kSprache
                FROM tnews
                JOIN tnewssprache t 
                    ON tnews.kNews = t.kNews
                JOIN tseo 
                    ON tseo.cKey = 'kNews'
                    AND tseo.kKey = tnews.kNews
                    AND tseo.kSprache = t.languageID
                WHERE tnews.nAktiv = 1
                    AND tnews.dGueltigVon <= NOW()
                    AND (tnews.cKundengruppe LIKE '%;-1;%'
                    OR FIND_IN_SET('" . \Session\Session::getCustomerGroup()->getID() .
                        "', REPLACE(tnews.cKundengruppe, ';',',')) > 0) 
                    ORDER BY tnews.dErstellt",
            \DB\ReturnType::QUERYSINGLE
        );
        while (($oNews = $res->fetch(PDO::FETCH_OBJ)) !== false) {
            $cURL = makeURL(
                UrlHelper::buildURL($oNews, URLART_NEWS),
                date_format(date_create($oNews->dGueltigVon), 'c'),
                $addChangeFreq ? FREQ_DAILY : null,
                $addPriority ? PRIO_HIGH : null
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
        $res = Shop::Container()->getDB()->query(
            "SELECT tnewskategorie.*, tseo.cSeo, tseo.kSprache
                 FROM tnewskategorie
                 JOIN tnewskategoriesprache t 
                    ON tnewskategorie.kNewsKategorie = t.kNewsKategorie
                 JOIN tseo 
                    ON tseo.cKey = 'kNewsKategorie'
                    AND tseo.kKey = tnewskategorie.kNewsKategorie
                    AND tseo.kSprache = t.languageID
                 WHERE tnewskategorie.nAktiv = 1",
            \DB\ReturnType::QUERYSINGLE
        );

        while (($oNewsKategorie = $res->fetch(PDO::FETCH_OBJ)) !== false) {
            $cURL = makeURL(
                UrlHelper::buildURL($oNewsKategorie, URLART_NEWSKATEGORIE),
                date_format(date_create($oNewsKategorie->dLetzteAktualisierung), 'c'),
                $addChangeFreq ? FREQ_DAILY : null,
                $addPriority ? PRIO_HIGH : null
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
            if (200 !== ($httpStatus = RequestHelper::http_get_status(
                'http://www.google.com/webmasters/tools/ping?sitemap=' . $encodedSitemapIndexURL
            ))) {
                Shop::Container()->getLogService()->notice('Sitemap ping to Google failed with status ' . $httpStatus);
            }
            if (200 !== ($httpStatus = RequestHelper::http_get_status(
                'http://www.bing.com/ping?sitemap=' . $encodedSitemapIndexURL
            ))) {
                Shop::Container()->getLogService()->notice('Sitemap ping to Bing failed with status ' . $httpStatus);
            }
        }
    }
}

/**
 * @param string $cGoogleImageEinstellung
 * @return string
 * @deprecated since 5.0.0
 */
function getXMLHeader($cGoogleImageEinstellung)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
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
 * @deprecated since 5.0.0
 */
function holeGoogleImage($artikel)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
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
 * @deprecated since 5.0.0
 */
function loescheSitemaps()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
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
 * @deprecated since 5.0.0
 */
function baueSitemapReport($nAnzahlURL_arr, $fTotalZeit)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
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
 * @deprecated since 5.0.0
 */
function baueExportURL(int $kKey, $cKey, $lastUpdate, $languages, $langID, $productsPerPage, $config = null)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $config   = $config ?? \Shopsetting::getInstance()->getAll();
    $cURL_arr = [];
    $params   = [];
    Shop::setLanguage($langID);
    $filterConfig = new \Filter\Config();
    $filterConfig->setLanguageID($langID);
    $filterConfig->setLanguages($languages);
    $filterConfig->setConfig($config);
    $filterConfig->setCustomerGroupID(\Session\Session::getCustomerGroup()->getID());
    $filterConfig->setBaseURL(Shop::getURL() . '/');
    $naviFilter = new \Filter\ProductFilter($filterConfig, Shop::Container()->getDB(), Shop::Container()->getCache());
    switch ($cKey) {
        case 'kKategorie':
            $params['kKategorie'] = $kKey;
            $naviFilter->initStates($params);
            break;

        case 'kHersteller':
            $params['kHersteller'] = $kKey;
            $naviFilter->initStates($params);
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
            break;

        case 'kMerkmalWert':
            $params['kMerkmalWert'] = $kKey;
            $naviFilter->initStates($params);
            break;

        case 'kTag':
            $params['kTag'] = $kKey;
            $naviFilter->initStates($params);
            break;

        case 'kSuchspecial':
            $params['kSuchspecial'] = $kKey;
            $naviFilter->initStates($params);
            break;

        default:
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
            $config['sitemap']['sitemap_insert_changefreq'] === 'Y' ? FREQ_WEEKLY : null,
            $config ['sitemap']['sitemap_insert_priority'] === 'Y' ? PRIO_NORMAL : null
        );
    }

    return $cURL_arr;
}

/**
 * @param array $Sprachen
 * @return array
 * @deprecated since 5.0.0
 */
function gibAlleSprachenAssoc($Sprachen)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $oSpracheAssoc_arr = [];
    foreach ($Sprachen as $oSprache) {
        $oSpracheAssoc_arr[$oSprache->cISO] = (int)$oSprache->kSprache;
    }

    return $oSpracheAssoc_arr;
}
