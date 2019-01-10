<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/syncinclude.php';
$return        = 3;
$Einstellungen = null;
$oBranding_arr = null;
$zipFile       = '';
if (auth()) {
    $zipFile       = checkFile();
    $Einstellungen = Shop::getSettings([CONF_BILDER]);
    // Branding Einstellungen pro Bildkategorie
    $oBranding_arr = holeBilderEinstellungen();

    if (!$Einstellungen['bilder']['bilder_kategorien_breite']) {
        $Einstellungen['bilder']['bilder_kategorien_breite'] = 100;
    }
    if (!$Einstellungen['bilder']['bilder_kategorien_hoehe']) {
        $Einstellungen['bilder']['bilder_kategorien_hoehe'] = 100;
    }
    if (!$Einstellungen['bilder']['bilder_variationen_gross_breite']) {
        $Einstellungen['bilder']['bilder_variationen_gross_breite'] = 800;
    }
    if (!$Einstellungen['bilder']['bilder_variationen_gross_hoehe']) {
        $Einstellungen['bilder']['bilder_variationen_gross_hoehe'] = 800;
    }
    if (!$Einstellungen['bilder']['bilder_variationen_breite']) {
        $Einstellungen['bilder']['bilder_variationen_breite'] = 210;
    }
    if (!$Einstellungen['bilder']['bilder_variationen_hoehe']) {
        $Einstellungen['bilder']['bilder_variationen_hoehe'] = 210;
    }
    if (!$Einstellungen['bilder']['bilder_variationen_mini_breite']) {
        $Einstellungen['bilder']['bilder_variationen_mini_breite'] = 30;
    }
    if (!$Einstellungen['bilder']['bilder_variationen_mini_hoehe']) {
        $Einstellungen['bilder']['bilder_variationen_mini_hoehe'] = 30;
    }
    if (!$Einstellungen['bilder']['bilder_artikel_gross_breite']) {
        $Einstellungen['bilder']['bilder_artikel_gross_breite'] = 800;
    }
    if (!$Einstellungen['bilder']['bilder_artikel_gross_hoehe']) {
        $Einstellungen['bilder']['bilder_artikel_gross_hoehe'] = 800;
    }
    if (!$Einstellungen['bilder']['bilder_artikel_normal_breite']) {
        $Einstellungen['bilder']['bilder_artikel_normal_breite'] = 210;
    }
    if (!$Einstellungen['bilder']['bilder_artikel_normal_hoehe']) {
        $Einstellungen['bilder']['bilder_artikel_normal_hoehe'] = 210;
    }
    if (!$Einstellungen['bilder']['bilder_artikel_klein_breite']) {
        $Einstellungen['bilder']['bilder_artikel_klein_breite'] = 80;
    }
    if (!$Einstellungen['bilder']['bilder_artikel_klein_hoehe']) {
        $Einstellungen['bilder']['bilder_artikel_klein_hoehe'] = 80;
    }
    if (!$Einstellungen['bilder']['bilder_artikel_mini_breite']) {
        $Einstellungen['bilder']['bilder_artikel_mini_breite'] = 30;
    }
    if (!$Einstellungen['bilder']['bilder_artikel_mini_hoehe']) {
        $Einstellungen['bilder']['bilder_artikel_mini_hoehe'] = 30;
    }
    if (!$Einstellungen['bilder']['bilder_hersteller_normal_breite']) {
        $Einstellungen['bilder']['bilder_hersteller_normal_breite'] = 100;
    }
    if (!$Einstellungen['bilder']['bilder_hersteller_normal_hoehe']) {
        $Einstellungen['bilder']['bilder_hersteller_normal_hoehe'] = 100;
    }
    if (!$Einstellungen['bilder']['bilder_hersteller_klein_breite']) {
        $Einstellungen['bilder']['bilder_hersteller_klein_breite'] = 40;
    }
    if (!$Einstellungen['bilder']['bilder_hersteller_klein_hoehe']) {
        $Einstellungen['bilder']['bilder_hersteller_klein_hoehe'] = 40;
    }
    if (!$Einstellungen['bilder']['bilder_merkmal_normal_breite']) {
        $Einstellungen['bilder']['bilder_merkmal_normal_breite'] = 100;
    }
    if (!$Einstellungen['bilder']['bilder_merkmal_normal_hoehe']) {
        $Einstellungen['bilder']['bilder_merkmal_normal_hoehe'] = 100;
    }
    if (!$Einstellungen['bilder']['bilder_merkmal_klein_breite']) {
        $Einstellungen['bilder']['bilder_merkmal_klein_breite'] = 20;
    }
    if (!$Einstellungen['bilder']['bilder_merkmal_klein_hoehe']) {
        $Einstellungen['bilder']['bilder_merkmal_klein_hoehe'] = 20;
    }
    if (!$Einstellungen['bilder']['bilder_merkmalwert_normal_breite']) {
        $Einstellungen['bilder']['bilder_merkmalwert_normal_breite'] = 100;
    }
    if (!$Einstellungen['bilder']['bilder_merkmalwert_normal_hoehe']) {
        $Einstellungen['bilder']['bilder_merkmalwert_normal_hoehe'] = 100;
    }
    if (!$Einstellungen['bilder']['bilder_merkmalwert_klein_breite']) {
        $Einstellungen['bilder']['bilder_merkmalwert_klein_breite'] = 20;
    }
    if (!$Einstellungen['bilder']['bilder_merkmalwert_klein_hoehe']) {
        $Einstellungen['bilder']['bilder_merkmalwert_klein_hoehe'] = 20;
    }
    if (!$Einstellungen['bilder']['bilder_konfiggruppe_klein_breite']) {
        $Einstellungen['bilder']['bilder_konfiggruppe_klein_breite'] = 130;
    }
    if (!$Einstellungen['bilder']['bilder_konfiggruppe_klein_hoehe']) {
        $Einstellungen['bilder']['bilder_konfiggruppe_klein_hoehe'] = 130;
    }
    if (!$Einstellungen['bilder']['bilder_jpg_quali']) {
        $Einstellungen['bilder']['bilder_jpg_quali'] = 80;
    }
    if (!$Einstellungen['bilder']['bilder_dateiformat']) {
        $Einstellungen['bilder']['bilder_dateiformat'] = 'PNG';
    }
    if (!$Einstellungen['bilder']['bilder_hintergrundfarbe']) {
        $Einstellungen['bilder']['bilder_hintergrundfarbe'] = '#ffffff';
    }
    if (!$Einstellungen['bilder']['bilder_skalieren']) {
        $Einstellungen['bilder']['bilder_skalieren'] = 'N';
    }
    // tseo Sprache
    $oSprache = Shop::Container()->getDB()->select('tsprache', 'cShopStandard', 'Y');
    $cSQL     = '';
    if (!$oSprache->kSprache) {
        $oSprache->kSprache = $_SESSION['kSprache'];
        $cSQL               = 'AND tseo.kSprache = ' . $oSprache->kSprache;
    }
    if ($oSprache->kSprache > 0) {
        $cSQL = ' AND tseo.kSprache = ' . $oSprache->kSprache;
    }
    $return    = 2;
    $zipFile   = $_FILES['data']['tmp_name'];
    $unzipPath = PFAD_ROOT .
        PFAD_DBES .
        PFAD_SYNC_TMP .
        basename($_FILES['data']['tmp_name']) . '_' .
        date('dhis') . '/';
    if (($syncFiles = unzipSyncFiles($zipFile, $unzipPath, __FILE__)) === false) {
        Shop::Container()->getLogService()->error('Error: Cannot extract zip file ' . $zipFile . ' to ' . $unzipPath);
        removeTemporaryFiles($zipFile);
    } else {
        $return = 0;
        foreach ($syncFiles as $xmlFile) {
            switch (pathinfo($xmlFile)['basename']) {
                case 'bilder_ka.xml':
                case 'bilder_a.xml':
                case 'bilder_k.xml':
                case 'bilder_v.xml':
                case 'bilder_m.xml':
                case 'bilder_mw.xml':
                case 'bilder_h.xml':
                    $d   = file_get_contents($xmlFile);
                    $xml = XML_unserialize($d);
                    bearbeite($xml, $unzipPath);
                    removeTemporaryFiles($xmlFile);
                    break;

                case 'del_bilder_ka.xml':
                case 'del_bilder_a.xml':
                case 'del_bilder_k.xml':
                case 'del_bilder_v.xml':
                case 'del_bilder_m.xml':
                case 'del_bilder_mw.xml':
                case 'del_bilder_h.xml':
                    $d   = file_get_contents($xmlFile);
                    $xml = XML_unserialize($d);
                    bearbeiteDeletes($xml);
                    removeTemporaryFiles($xmlFile);
                    break;
            }
        }
        removeTemporaryFiles(substr($unzipPath, 0, -1), true);
    }
}

echo $return;

/**
 * @param array  $xml
 * @param string $unzipPath
 */
function bearbeite($xml, string $unzipPath)
{
    $img_arr                 = mapArray($xml['bilder'], 'tartikelpict', $GLOBALS['mArtikelPict']);
    $kategoriebild_arr       = mapArray($xml['bilder'], 'tkategoriepict', $GLOBALS['mKategoriePict']);
    $eigenschaftwertbild_arr = mapArray($xml['bilder'], 'teigenschaftwertpict', $GLOBALS['mEigenschaftWertPict']);
    $herstellerbild_arr      = mapArray($xml['bilder'], 'therstellerbild', $GLOBALS['mEigenschaftWertPict']);
    $merkmalwertbild_arr     = mapArray($xml['bilder'], 'tmerkmalwertbild', $GLOBALS['mEigenschaftWertPict']);
    $merkmalbild_arr         = mapArray($xml['bilder'], 'tMerkmalbild', $GLOBALS['mEigenschaftWertPict']);
    $konfigartikelbild_arr   = mapArray($xml['bilder'], 'tkonfiggruppebild', $GLOBALS['mKonfiggruppePict']);

    $db = Shop::Container()->getDB();

    executeHook(HOOK_BILDER_XML_BEARBEITE, [
        'Pfad'             => $unzipPath,
        'Artikel'          => &$img_arr,
        'Kategorie'        => &$kategoriebild_arr,
        'Eigenschaftswert' => &$eigenschaftwertbild_arr,
        'Hersteller'       => &$herstellerbild_arr,
        'Merkmalwert'      => &$merkmalwertbild_arr,
        'Merkmal'          => &$merkmalbild_arr,
        'Konfiggruppe'     => &$konfigartikelbild_arr
    ]);
    //Artikelbilder
    foreach ($img_arr as $img) {
        if (strlen($img->cPfad) > 0) {
            $img->nNr    = (int)$img->nNr;
            $imgFilename = $img->cPfad;
            $Bildformat  = gibBildformat($unzipPath . $imgFilename);
            if (!$Bildformat) {
                Shop::Container()->getLogService()->error(
                    'Bildformat des Artikelbildes konnte nicht ermittelt werden. Datei ' .
                    $imgFilename . ' keine Bilddatei?'
                );
                continue;
            }

            //first delete by kArtikelPict
            loescheArtikelPict($img->kArtikelPict, 0);

            // then delete by kArtikel + nNr since Wawi > .99923 has changed all kArtikelPict keys
            if (isset($img->nNr) && $img->nNr > 0) {
                loescheArtikelPict($img->kArtikel, $img->nNr);
            }

            if ($img->kMainArtikelBild > 0) {
                $oMainArtikelBild = $db->select(
                    'tartikelpict',
                    'kArtikelPict',
                    (int)$img->kMainArtikelBild
                )
                ;
                if (isset($oMainArtikelBild->cPfad) && strlen($oMainArtikelBild->cPfad) > 0) {
                    $img->cPfad = neuerDateiname($oMainArtikelBild->cPfad);
                    DBUpdateInsert('tartikelpict', [$img], 'kArtikel', 'kArtikelpict');
                } else {
                    erstelleArtikelBild($img, $Bildformat, $unzipPath, $imgFilename);
                }
            } else {
                $oArtikelBild = $db->select(
                    'tartikelpict',
                    'kArtikelPict',
                    (int)$img->kArtikelPict
                );
                // update all references, if img is used by other products
                if (isset($oArtikelBild->cPfad) && strlen($oArtikelBild->cPfad) > 0) {
                    $db->update(
                        'tartikelpict',
                        'kMainArtikelBild',
                        (int)$oArtikelBild->kArtikelPict,
                        (object)['cPfad' => $oArtikelBild->cPfad]
                    );
                }
                erstelleArtikelBild($img, $Bildformat, $unzipPath, $imgFilename);
            }
        }
    }
    if (count($img_arr) > 0) {
        $dir_handle = @opendir($unzipPath);
        while (false !== ($file = readdir($dir_handle))) {
            if ($file !== '.' && $file !== '..' && $file !== 'bilder_a.xml' && file_exists($unzipPath . $file)) {
                if (!unlink($unzipPath . $file)) {
                    Shop::Container()->getLogService()->error('Artikelbild konnte nicht geloescht werden: ' . $file);
                }
            }
        }
        @closedir($dir_handle);
    }
    foreach ($kategoriebild_arr as $Kategoriebild) {
        if (strlen($Kategoriebild->cPfad) > 0) {
            $imgFilename = $Kategoriebild->cPfad;
            $Bildformat  = gibBildformat($unzipPath . $imgFilename);
            if (!$Bildformat) {
                Shop::Container()->getLogService()->error(
                    'Bildformat des Kategoriebildes konnte nicht ermittelt werden. Datei ' .
                    $imgFilename . ' keine Bilddatei?'
                );
                continue;
            }

            $Bildname = gibKategoriebildname($Kategoriebild, $Bildformat);

            $Kategoriebild->cPfad = $Bildname;
            $Kategoriebild->cPfad = neuerDateiname($Kategoriebild->cPfad);
            if (erstelleThumbnail(
                $GLOBALS['oBranding_arr']['Kategorie'],
                $unzipPath . $imgFilename,
                PFAD_KATEGORIEBILDER . $Kategoriebild->cPfad,
                $GLOBALS['Einstellungen']['bilder']['bilder_kategorien_breite'],
                $GLOBALS['Einstellungen']['bilder']['bilder_kategorien_hoehe'],
                1,
                $GLOBALS['Einstellungen']['bilder']['bilder_jpg_quali'],
                1,
                $GLOBALS['Einstellungen']['bilder']['container_verwenden']
            )) {
                DBUpdateInsert('tkategoriepict', [$Kategoriebild], 'kKategorie');
            }
            unlink($unzipPath . $imgFilename);
        }
    }
    foreach ($eigenschaftwertbild_arr as $Eigenschaftwertbild) {
        if (strlen($Eigenschaftwertbild->cPfad) > 0) {
            $imgFilename = $Eigenschaftwertbild->cPfad;
            $Bildformat  = gibBildformat($unzipPath . $imgFilename);
            if (!$Bildformat) {
                Shop::Container()->getLogService()->error(
                    'Bildformat des Eigenschaftwertbildes konnte nicht ermittelt werden. Datei ' .
                    $imgFilename . ' keine Bilddatei?'
                );
                continue;
            }
            $Bildname                   = gibEigenschaftwertbildname($Eigenschaftwertbild, $Bildformat);
            $Eigenschaftwertbild->cPfad = $Bildname;
            $Eigenschaftwertbild->cPfad = neuerDateiname($Eigenschaftwertbild->cPfad);
            erstelleThumbnail(
                $GLOBALS['oBranding_arr']['Variationen'],
                $unzipPath . $imgFilename,
                PFAD_VARIATIONSBILDER_GROSS . $Eigenschaftwertbild->cPfad,
                $GLOBALS['Einstellungen']['bilder']['bilder_variationen_gross_breite'],
                $GLOBALS['Einstellungen']['bilder']['bilder_variationen_gross_hoehe'],
                0,
                $GLOBALS['Einstellungen']['bilder']['bilder_jpg_quali'],
                1,
                $GLOBALS['Einstellungen']['bilder']['container_verwenden']
            );
            erstelleThumbnailBranded(
                PFAD_ROOT . PFAD_VARIATIONSBILDER_GROSS . $Eigenschaftwertbild->cPfad,
                PFAD_VARIATIONSBILDER_NORMAL . $Eigenschaftwertbild->cPfad,
                $GLOBALS['Einstellungen']['bilder']['bilder_variationen_breite'],
                $GLOBALS['Einstellungen']['bilder']['bilder_variationen_hoehe'],
                $GLOBALS['Einstellungen']['bilder']['bilder_jpg_quali'],
                $GLOBALS['Einstellungen']['bilder']['container_verwenden']
            );
            if (erstelleThumbnailBranded(
                PFAD_ROOT . PFAD_VARIATIONSBILDER_GROSS . $Eigenschaftwertbild->cPfad,
                PFAD_VARIATIONSBILDER_MINI . $Eigenschaftwertbild->cPfad,
                $GLOBALS['Einstellungen']['bilder']['bilder_variationen_mini_breite'],
                $GLOBALS['Einstellungen']['bilder']['bilder_variationen_mini_hoehe'],
                $GLOBALS['Einstellungen']['bilder']['bilder_jpg_quali'],
                $GLOBALS['Einstellungen']['bilder']['container_verwenden']
            )) {
                DBUpdateInsert('teigenschaftwertpict', [$Eigenschaftwertbild], 'kEigenschaftWert');
            }
            unlink($unzipPath . $imgFilename);
        }
    }
    foreach ($herstellerbild_arr as $Herstellerbild) {
        $Herstellerbild->kHersteller = (int)$Herstellerbild->kHersteller;
        if (strlen($Herstellerbild->cPfad) > 0 && $Herstellerbild->kHersteller > 0) {
            $imgFilename = $Herstellerbild->cPfad;
            $Bildformat  = gibBildformat($unzipPath . $imgFilename);
            if (!$Bildformat) {
                Shop::Container()->getLogService()->error(
                    'Bildformat des Herstellerbildes konnte nicht ermittelt werden. Datei ' .
                    $imgFilename . ' keine Bilddatei?'
                );
                continue;
            }
            $Hersteller = $db->query(
                'SELECT cSeo
                    FROM thersteller
                    WHERE kHersteller = ' . (int)$Herstellerbild->kHersteller,
                \DB\ReturnType::SINGLE_OBJECT
            );
            if (isset($Hersteller->cSeo) && strlen($Hersteller->cSeo) > 0) {
                $Herstellerbild->cPfad = str_replace('/', '_', $Hersteller->cSeo . '.' . $Bildformat);
            } elseif (stripos(strrev($Herstellerbild->cPfad), strrev($Bildformat)) !== 0) {
                $Herstellerbild->cPfad .= '.' . $Bildformat;
            }
            $Herstellerbild->cPfad = neuerDateiname($Herstellerbild->cPfad);
            erstelleThumbnail(
                $GLOBALS['oBranding_arr']['Hersteller'],
                $unzipPath . $imgFilename,
                PFAD_HERSTELLERBILDER_NORMAL . $Herstellerbild->cPfad,
                $GLOBALS['Einstellungen']['bilder']['bilder_hersteller_normal_breite'],
                $GLOBALS['Einstellungen']['bilder']['bilder_hersteller_normal_hoehe'],
                0,
                $GLOBALS['Einstellungen']['bilder']['bilder_jpg_quali'],
                1,
                $GLOBALS['Einstellungen']['bilder']['container_verwenden']
            );
            if (erstelleThumbnailBranded(
                PFAD_ROOT . PFAD_HERSTELLERBILDER_NORMAL . $Herstellerbild->cPfad,
                PFAD_HERSTELLERBILDER_KLEIN . $Herstellerbild->cPfad,
                $GLOBALS['Einstellungen']['bilder']['bilder_hersteller_klein_breite'],
                $GLOBALS['Einstellungen']['bilder']['bilder_hersteller_klein_hoehe'],
                $GLOBALS['Einstellungen']['bilder']['bilder_jpg_quali'],
                $GLOBALS['Einstellungen']['bilder']['container_verwenden']
            )) {
                //thersteller updaten
                $db->update(
                    'thersteller',
                    'kHersteller',
                    (int)$Herstellerbild->kHersteller,
                    (object)['cBildpfad' => $Herstellerbild->cPfad]
                );
            }
            $cacheTags = [];
            foreach ($db->selectAll(
                'tartikel',
                'kHersteller',
                (int)$Herstellerbild->kHersteller,
                'kArtikel'
            ) as $article) {
                $cacheTags[] = CACHING_GROUP_ARTICLE . '_' . $article->kArtikel;
            }
            Shop::Container()->getCache()->flushTags($cacheTags);
            unlink($unzipPath . $imgFilename);
        }
    }
    foreach ($merkmalbild_arr as $Merkmalbild) {
        $Merkmalbild->kMerkmal = (int)$Merkmalbild->kMerkmal;
        if (strlen($Merkmalbild->cPfad) > 0 && $Merkmalbild->kMerkmal > 0) {
            $imgFilename = $Merkmalbild->cPfad;
            $Bildformat  = gibBildformat($unzipPath . $imgFilename);
            if (!$Bildformat) {
                Shop::Container()->getLogService()->error(
                    'Bildformat des Merkmalbildes konnte nicht ermittelt werden. Datei ' .
                    $imgFilename . ' keine Bilddatei?'
                );
                continue;
            }
            $Merkmalbild->cPfad .= '.' . $Bildformat;
            $Merkmalbild->cPfad  = neuerDateiname($Merkmalbild->cPfad);
            erstelleThumbnail(
                $GLOBALS['oBranding_arr']['Merkmale'],
                $unzipPath . $imgFilename,
                PFAD_MERKMALBILDER_NORMAL . $Merkmalbild->cPfad,
                $GLOBALS['Einstellungen']['bilder']['bilder_merkmal_normal_breite'],
                $GLOBALS['Einstellungen']['bilder']['bilder_merkmal_normal_hoehe'],
                0,
                $GLOBALS['Einstellungen']['bilder']['bilder_jpg_quali'],
                1,
                $GLOBALS['Einstellungen']['bilder']['container_verwenden']
            );
            if (erstelleThumbnailBranded(
                PFAD_ROOT . PFAD_MERKMALBILDER_NORMAL . $Merkmalbild->cPfad,
                PFAD_MERKMALBILDER_KLEIN . $Merkmalbild->cPfad,
                $GLOBALS['Einstellungen']['bilder']['bilder_merkmal_klein_breite'],
                $GLOBALS['Einstellungen']['bilder']['bilder_merkmal_klein_hoehe'],
                $GLOBALS['Einstellungen']['bilder']['bilder_jpg_quali'],
                $GLOBALS['Einstellungen']['bilder']['container_verwenden']
            )) {
                $db->update(
                    'tmerkmal',
                    'kMerkmal',
                    (int)$Merkmalbild->kMerkmal,
                    (object)['cBildpfad' => $Merkmalbild->cPfad]
                );
            }
            unlink($unzipPath . $imgFilename);
        }
    }
    foreach ($merkmalwertbild_arr as $Merkmalwertbild) {
        $Merkmalwertbild->kMerkmalWert = (int)$Merkmalwertbild->kMerkmalWert;
        if (strlen($Merkmalwertbild->cPfad) > 0 && $Merkmalwertbild->kMerkmalWert > 0) {
            $imgFilename = $Merkmalwertbild->cPfad;
            $Bildformat  = gibBildformat($unzipPath . $imgFilename);
            if (!$Bildformat) {
                Shop::Container()->getLogService()->error(
                    'Bildformat des Merkmalwertbildes konnte nicht ermittelt werden. Datei ' .
                    $imgFilename . ' keine Bilddatei?'
                );
                continue;
            }
            $Merkmalwertbild->cPfad .= '.' . $Bildformat;
            $Merkmalwertbild->cPfad  = neuerDateiname($Merkmalwertbild->cPfad);
            erstelleThumbnail(
                $GLOBALS['oBranding_arr']['Merkmalwerte'],
                $unzipPath . $imgFilename,
                PFAD_MERKMALWERTBILDER_NORMAL . $Merkmalwertbild->cPfad,
                $GLOBALS['Einstellungen']['bilder']['bilder_merkmalwert_normal_breite'],
                $GLOBALS['Einstellungen']['bilder']['bilder_merkmalwert_normal_hoehe'],
                0,
                $GLOBALS['Einstellungen']['bilder']['bilder_jpg_quali'],
                1,
                $GLOBALS['Einstellungen']['bilder']['container_verwenden']
            );
            if (erstelleThumbnailBranded(
                PFAD_ROOT . PFAD_MERKMALWERTBILDER_NORMAL . $Merkmalwertbild->cPfad,
                PFAD_MERKMALWERTBILDER_KLEIN . $Merkmalwertbild->cPfad,
                $GLOBALS['Einstellungen']['bilder']['bilder_merkmalwert_klein_breite'],
                $GLOBALS['Einstellungen']['bilder']['bilder_merkmalwert_klein_hoehe'],
                $GLOBALS['Einstellungen']['bilder']['bilder_jpg_quali'],
                $GLOBALS['Einstellungen']['bilder']['container_verwenden']
            )) {
                $db->update(
                    'tmerkmalwert',
                    'kMerkmalWert',
                    (int)$Merkmalwertbild->kMerkmalWert,
                    (object)['cBildpfad' => $Merkmalwertbild->cPfad]
                );
                $oMerkmalwertbild               = new stdClass();
                $oMerkmalwertbild->kMerkmalWert = (int)$Merkmalwertbild->kMerkmalWert;
                $oMerkmalwertbild->cBildpfad    = $Merkmalwertbild->cPfad;

                DBUpdateInsert('tmerkmalwertbild', [$oMerkmalwertbild], 'kMerkmalWert');
            }
            unlink($unzipPath . $imgFilename);
        }
    }
    foreach ($konfigartikelbild_arr as $Konfigbild) {
        $oKonfig                = new stdClass();
        $oKonfig->cBildPfad     = $Konfigbild->cPfad;
        $oKonfig->kKonfiggruppe = $Konfigbild->kKonfiggruppe;

        if (strlen($oKonfig->cBildPfad) > 0) {
            $imgFilename = $oKonfig->cBildPfad;
            $Bildformat  = gibBildformat($unzipPath . $imgFilename);
            if (!$Bildformat) {
                Shop::Container()->getLogService()->error(
                    'Bildformat des Konfiggruppenbildes konnte nicht ermittelt werden. Datei ' .
                    $imgFilename . ' keine Bilddatei?'
                );
                continue;
            }
            $Bildname = $oKonfig->kKonfiggruppe . '.' . $Bildformat;

            $oKonfig->cBildPfad = $Bildname;
            $oKonfig->cBildPfad = neuerDateiname($oKonfig->cBildPfad);

            $oBranding                               = new stdClass();
            $oBranding->oBrandingEinstellung         = new stdClass();
            $oBranding->oBrandingEinstellung->nAktiv = 0;

            if (erstelleThumbnail(
                $oBranding,
                $unzipPath . $imgFilename,
                PFAD_KONFIGURATOR_KLEIN . $oKonfig->cBildPfad,
                $GLOBALS['Einstellungen']['bilder']['bilder_konfiggruppe_klein_breite'],
                $GLOBALS['Einstellungen']['bilder']['bilder_konfiggruppe_klein_hoehe'],
                1,
                $GLOBALS['Einstellungen']['bilder']['bilder_jpg_quali'],
                1,
                $GLOBALS['Einstellungen']['bilder']['container_verwenden']
            )) {
                $db->update(
                    'tkonfiggruppe',
                    'kKonfiggruppe',
                    (int)$oKonfig->kKonfiggruppe,
                    (object)['cBildPfad' => $oKonfig->cBildPfad]
                );
            }
            unlink($unzipPath . $imgFilename);
        }
    }

    executeHook(HOOK_BILDER_XML_BEARBEITE_ENDE, [
        'Artikel'          => &$img_arr,
        'Kategorie'        => &$kategoriebild_arr,
        'Eigenschaftswert' => &$eigenschaftwertbild_arr,
        'Hersteller'       => &$herstellerbild_arr,
        'Merkmalwert'      => &$merkmalwertbild_arr,
        'Merkmal'          => &$merkmalbild_arr,
        'Konfiggruppe'     => &$konfigartikelbild_arr
    ]);
}

/**
 * @param stdClass $img
 * @param string   $Bildformat
 * @param string   $unzipPath
 * @param string   $imgFilename
 */
function erstelleArtikelBild($img, $Bildformat, $unzipPath, $imgFilename)
{
    $conf       = Shop::getSettings([CONF_BILDER]);
    $Bildname   = gibArtikelbildname($img, $conf['bilder']['container_verwenden'] === 'Y' ? 'png' : $Bildformat);
    $img->cPfad = $Bildname;
    $img->cPfad = neuerDateiname($img->cPfad);
    erstelleThumbnail(
        $GLOBALS['oBranding_arr']['Artikel'],
        $unzipPath . $imgFilename,
        PFAD_PRODUKTBILDER_GROSS . $img->cPfad,
        $conf['bilder']['bilder_artikel_gross_breite'],
        $conf['bilder']['bilder_artikel_gross_hoehe'],
        1,
        $conf['bilder']['bilder_jpg_quali'],
        1,
        $conf['bilder']['container_verwenden']
    );
    erstelleThumbnailBranded(
        PFAD_ROOT . PFAD_PRODUKTBILDER_GROSS . $img->cPfad,
        PFAD_PRODUKTBILDER_NORMAL . $img->cPfad,
        $conf['bilder']['bilder_artikel_normal_breite'],
        $conf['bilder']['bilder_artikel_normal_hoehe'],
        $conf['bilder']['bilder_jpg_quali'],
        $conf['bilder']['container_verwenden']
    );
    erstelleThumbnailBranded(
        PFAD_ROOT . PFAD_PRODUKTBILDER_GROSS . $img->cPfad,
        PFAD_PRODUKTBILDER_KLEIN . $img->cPfad,
        $conf['bilder']['bilder_artikel_klein_breite'],
        $conf['bilder']['bilder_artikel_klein_hoehe'],
        $conf['bilder']['bilder_jpg_quali'],
        $conf['bilder']['container_verwenden']
    );
    if (erstelleThumbnailBranded(
        $unzipPath . $imgFilename,
        PFAD_PRODUKTBILDER_MINI . $img->cPfad,
        $conf['bilder']['bilder_artikel_mini_breite'],
        $conf['bilder']['bilder_artikel_mini_hoehe'],
        $conf['bilder']['bilder_jpg_quali'],
        $conf['bilder']['container_verwenden']
    )) {
        DBUpdateInsert('tartikelpict', [$img], 'kArtikel', 'kArtikelPict');
    }
}

/**
 * @param object $Eigenschaftwertbild
 * @param string $Bildformat
 * @return mixed|string
 */
function gibEigenschaftwertbildname($Eigenschaftwertbild, $Bildformat)
{
    global $cSQL;

    if (!$Eigenschaftwertbild->kEigenschaftWert || !$GLOBALS['Einstellungen']['bilder']['bilder_variation_namen']) {
        return (stripos(strrev($Eigenschaftwertbild->cPfad), strrev($Bildformat)) === 0)
            ? $Eigenschaftwertbild->cPfad
            : $Eigenschaftwertbild->cPfad . '.' . $Bildformat;
    }
    $Eigenschaftwert = Shop::Container()->getDB()->query(
        'SELECT kEigenschaftWert, cArtNr, cName, kEigenschaft
            FROM teigenschaftwert
            WHERE kEigenschaftWert = ' . (int)$Eigenschaftwertbild->kEigenschaftWert,
        \DB\ReturnType::SINGLE_OBJECT
    );
    $Bildname        = $Eigenschaftwert->kEigenschaftWert;
    if ($Eigenschaftwert->cName) {
        switch ($GLOBALS['Einstellungen']['bilder']['bilder_variation_namen']) {
            case 1:
                if (!empty($Eigenschaftwert->cArtNr)) {
                    $Bildname = 'var' . gibAusgeschriebeneUmlaute($Eigenschaftwert->cArtNr);
                }
                break;

            case 2:
                $Artikel = Shop::Container()->getDB()->query(
                    "SELECT tartikel.cArtNr, tartikel.cBarcode, tartikel.cName, tseo.cSeo
                        FROM teigenschaftwert, teigenschaft, tartikel
                        LEFT JOIN tseo
                            ON tseo.cKey = 'kArtikel'
                            AND tseo.kKey = tartikel.kArtikel
                            " . $cSQL . '
                        WHERE teigenschaftwert.kEigenschaft = teigenschaft.kEigenschaft
                            AND teigenschaft.kArtikel = tartikel.kArtikel
                            AND teigenschaftwert.kEigenschaftWert = ' . (int)$Eigenschaftwertbild->kEigenschaftWert,
                    \DB\ReturnType::SINGLE_OBJECT
                );
                if (!empty($Artikel->cArtNr) && !empty($Eigenschaftwert->cArtNr)) {
                    $Bildname = gibAusgeschriebeneUmlaute($Artikel->cArtNr) .
                        '_' .
                        gibAusgeschriebeneUmlaute($Eigenschaftwert->cArtNr);
                }
                break;

            case 3:
                $Artikel = Shop::Container()->getDB()->query(
                    "SELECT tartikel.cArtNr, tartikel.cBarcode, tartikel.cName, tseo.cSeo
                        FROM teigenschaftwert, teigenschaft, tartikel
                        LEFT JOIN tseo
                            ON tseo.cKey = 'kArtikel'
                            AND tseo.kKey = tartikel.kArtikel
                            " . $cSQL . '
                        WHERE teigenschaftwert.kEigenschaft = teigenschaft.kEigenschaft
                            AND teigenschaft.kArtikel = tartikel.kArtikel
                            AND teigenschaftwert.kEigenschaftWert = ' . $Eigenschaftwertbild->kEigenschaftWert,
                    \DB\ReturnType::SINGLE_OBJECT
                );

                $Eigenschaft = Shop::Container()->getDB()->query(
                    'SELECT cName FROM teigenschaft WHERE kEigenschaft = ' . $Eigenschaftwert->kEigenschaft,
                    \DB\ReturnType::SINGLE_OBJECT
                );
                if ((!empty($Artikel->cSeo) || !empty($Artikel->cName))
                    && !empty($Eigenschaft->cName)
                    && !empty($Eigenschaftwert->cName)
                ) {
                    if ($Artikel->cSeo) {
                        $Bildname = $Artikel->cSeo . '_' .
                            gibAusgeschriebeneUmlaute($Eigenschaft->cName) . '_' .
                            gibAusgeschriebeneUmlaute($Eigenschaftwert->cName);
                    } else {
                        $Bildname = gibAusgeschriebeneUmlaute($Artikel->cName) . '_' .
                            gibAusgeschriebeneUmlaute($Eigenschaft->cName) . '_' .
                            gibAusgeschriebeneUmlaute($Eigenschaftwert->cName);
                    }
                }
                break;
        }
    }
    $Bildname = streicheSonderzeichen($Bildname) . '.' . $Bildformat;

    return $Bildname;
}

/**
 * @param object $Kategoriebild
 * @param string $Bildformat
 * @return mixed|string
 */
function gibKategoriebildname($Kategoriebild, $Bildformat)
{
    global $cSQL;

    if (!$Kategoriebild->kKategorie || !$GLOBALS['Einstellungen']['bilder']['bilder_kategorie_namen']) {
        return (stripos(strrev($Kategoriebild->cPfad), strrev($Bildformat)) === 0)
            ? $Kategoriebild->cPfad
            : $Kategoriebild->cPfad . '.' . $Bildformat;
    }
    $attr = Shop::Container()->getDB()->select(
        'tkategorieattribut',
        'kKategorie',
        (int)$Kategoriebild->kKategorie,
        'cName',
        KAT_ATTRIBUT_BILDNAME,
        null,
        null,
        false,
        'cWert'
    );
    if (!empty($attr->cWert)) {
        return $attr->cWert . '.' . $Bildformat;
    }
    $Kategorie = Shop::Container()->getDB()->query(
        "SELECT tseo.cSeo, tkategorie.cName
            FROM tkategorie
            LEFT JOIN tseo
                ON tseo.cKey = 'kKategorie'
                AND tseo.kKey = tkategorie.kKategorie
                " . $cSQL . '
            WHERE tkategorie.kKategorie = ' . (int)$Kategoriebild->kKategorie,
        \DB\ReturnType::SINGLE_OBJECT
    );
    $Bildname  = $Kategoriebild->cPfad;
    if ($Kategorie->cName) {
        switch ($GLOBALS['Einstellungen']['bilder']['bilder_kategorie_namen']) {
            case 1:
                if ($Kategorie->cSeo) {
                    $Bildname = $Kategorie->cSeo;
                } else {
                    $Bildname = gibAusgeschriebeneUmlaute($Kategorie->cName);
                }
                $Bildname = streicheSonderzeichen($Bildname) . '.' . $Bildformat;
                break;

            default:
                return $Kategoriebild->cPfad . '.' . $Bildformat;
                break;
        }
    }

    return $Bildname;
}

/**
 * @param object $img
 * @param string $Bildformat
 * @return mixed|string
 */
function gibArtikelbildname($img, $Bildformat)
{
    global $cSQL;

    if ($img->kArtikel) {
        $attr = Shop::Container()->getDB()->select(
            'tkategorieattribut',
            'kArtikel',
            (int)$img->kArtikel,
            'cName',
            FKT_ATTRIBUT_BILDNAME,
            null,
            null,
            false,
            'cWert'
        );
        if (isset($attr->cWert)) {
            if ($img->nNr > 1) {
                $attr->cWert .= '_' . $img->nNr;
            }

            return $attr->cWert . '.' . $Bildformat;
        }
    }

    if (!$img->kArtikel || !$GLOBALS['Einstellungen']['bilder']['bilder_artikel_namen']) {
        return $img->cPfad . '.' . $Bildformat;
    }
    $Artikel  = Shop::Container()->getDB()->query(
        "SELECT tartikel.cArtNr, tseo.cSeo, tartikel.cName, tartikel.cBarcode
            FROM tartikel
            LEFT JOIN tseo
                ON tseo.cKey = 'kArtikel'
                AND tseo.kKey = tartikel.kArtikel
                " . $cSQL . '
            WHERE tartikel.kArtikel = ' . (int)$img->kArtikel,
        \DB\ReturnType::SINGLE_OBJECT
    );
    $Bildname = $img->cPfad;
    if ($Artikel->cName) {
        switch ($GLOBALS['Einstellungen']['bilder']['bilder_artikel_namen']) {
            case 1:
                if ($Artikel->cArtNr) {
                    $Bildname = gibAusgeschriebeneUmlaute($Artikel->cArtNr);
                }
                break;

            case 2:
                if ($Artikel->cSeo) {
                    $Bildname = $Artikel->cSeo;
                } else {
                    $Bildname = gibAusgeschriebeneUmlaute($Artikel->cName);
                }
                break;

            case 3:
                if ($Artikel->cArtNr) {
                    $Bildname = gibAusgeschriebeneUmlaute($Artikel->cArtNr) . '_';
                }
                if ($Artikel->cSeo) {
                    $Bildname .= $Artikel->cSeo;
                } else {
                    $Bildname .= gibAusgeschriebeneUmlaute($Artikel->cName);
                }
                break;

            case 4:
                if ($Artikel->cBarcode) {
                    $Bildname = gibAusgeschriebeneUmlaute($Artikel->cBarcode);
                }
                break;
            default:
                return $img->cPfad . '.' . $Bildformat;
                break;
        }
    } else {
        return $img->cPfad . '.' . $Bildformat;
    }

    if ($img->nNr > 1 && $Bildname !== $img->cPfad) {
        $Bildname .= '_b' . $img->nNr;
    }
    if ($Bildname !== $img->cPfad && (int)$GLOBALS['Einstellungen']['bilder']['bilder_artikel_namen'] !== 5) {
        $Bildname = streicheSonderzeichen($Bildname) . '.' . $Bildformat;
    } else {
        $Bildname .= '.' . $Bildformat;
    }

    return $Bildname;
}

/**
 * @param string $str
 * @return mixed
 */
function gibAusgeschriebeneUmlaute($str)
{
    $src = ['ä', 'ö', 'ü', 'ß', 'Ä', 'Ö', 'Ü'];
    $rpl = ['ae', 'oe', 'ue', 'ss', 'AE', 'OE', 'UE'];

    return str_replace($src, $rpl, $str);
}

/**
 * @param string $str
 * @return mixed
 */
function streicheSonderzeichen($str)
{
    $str = str_replace(['/', ' '], '-', $str);

    return preg_replace('/[^a-zA-Z0-9\.\-_]/', '', $str);
}

/**
 * @param string $imgFilename
 * @param string $zielbild
 * @param int    $breite
 * @param int    $hoehe
 * @param int    $quality
 * @param string $container
 * @return int
 */
function erstelleThumbnailBranded($imgFilename, $zielbild, $breite, $hoehe, int $quality = 80, $container = 'N')
{
    $vergroessern = 0;
    if ($GLOBALS['Einstellungen']['bilder']['bilder_skalieren'] === 'Y') {
        $vergroessern = 1;
    }
    $ret                         = 0;
    $Bildformat                  = $GLOBALS['Einstellungen']['bilder']['bilder_dateiformat'];//gibBildformat($imgFilename);
    list($width, $height, $type) = getimagesize($imgFilename);
    if ($width > 0 && $height > 0) {
        if (!$vergroessern && $width < $breite && $height < $hoehe) {
            if ($container === 'Y') {
                $im = imageload_container($imgFilename, $width, $height, $breite, $hoehe);
            } else {
                $im = imageload_alpha($imgFilename, $width, $height);
            }
            speichereBild($im, $Bildformat, PFAD_ROOT . $zielbild, $quality);
            @chmod(PFAD_ROOT . $zielbild, 0644);

            return 1;
        }
        $ratio      = $width / $height;
        $new_width  = $breite;
        $new_height = round($new_width / $ratio);
        if ($new_height > $hoehe) {
            $new_height = $hoehe;
            $new_width  = round($new_height * $ratio);
        }
        if ($container === 'Y') {
            $im = imageload_container($imgFilename, $new_width, $new_height, $breite, $hoehe);
        } else {
            $im = imageload_alpha($imgFilename, $new_width, $new_height);
        }
        if (speichereBild($im, $Bildformat, PFAD_ROOT . $zielbild, $quality)) {
            $ret = 1;
            @chmod(PFAD_ROOT . $zielbild, 0644);
        } else {
            Shop::Container()->getLogService()->error('Fehler beim Speichern des Bildes: ' . $zielbild);
        }
    } else {
        Shop::Container()->getLogService()->error('Fehler beim Speichern des Bildes: ' . $imgFilename);
    }

    return $ret;
}

/**
 * @param object       $oBranding
 * @param string       $imgFilename
 * @param string       $zielbild
 * @param int          $breite
 * @param int          $hoehe
 * @param int          $vergroessern
 * @param int          $quality
 * @param int|resource $brand
 * @param string       $container
 * @return int
 */
function erstelleThumbnail(
    $oBranding,
    $imgFilename,
    $zielbild,
    $breite,
    $hoehe,
    $vergroessern = 0,
    $quality = 80,
    $brand = 0,
    $container = 'N'
) {
    $vergroessern = 0;
    if ($GLOBALS['Einstellungen']['bilder']['bilder_skalieren'] === 'Y') {
        $vergroessern = 1;
    }
    $ret        = 0;
    $Bildformat = $GLOBALS['Einstellungen']['bilder']['bilder_dateiformat'];//gibBildformat($imgFilename);
    $im         = imageload_alpha($imgFilename);
    if ($im) {
        //bild skalieren
        list($width, $height, $type) = getimagesize($imgFilename);
        if (!$vergroessern && $width < $breite && $height < $hoehe) {
            //Bild nicht neu berechnen, nur verschieben
            if ($container === 'Y') {
                $im = imageload_container($imgFilename, $width, $height, $breite, $hoehe);
            } else {
                $im = imageload_alpha($imgFilename, $width, $height);
            }
            speichereBild(brandImage($im, $brand, $oBranding), $Bildformat, PFAD_ROOT . $zielbild, $quality);
            @chmod(PFAD_ROOT . $zielbild, 0644);

            return 1;
        }
        $ratio      = $width / $height;
        $new_width  = $breite;
        $new_height = round($new_width / $ratio);
        if ($new_height > $hoehe) {
            $new_height = $hoehe;
            $new_width  = round($new_height * $ratio);
        }
        if ($container === 'Y') {
            $image_p = imageload_container($imgFilename, $new_width, $new_height, $breite, $hoehe);
        } else {
            $image_p = imageload_alpha($imgFilename, $new_width, $new_height);
        }
        if (speichereBild(brandImage($image_p, $brand, $oBranding), $Bildformat, PFAD_ROOT . $zielbild, $quality)) {
            $ret = 1;
            @chmod(PFAD_ROOT . $zielbild, 0644);
        } else {
            Shop::Container()->getLogService()->error('Fehler beim Speichern des Bildes: ' . $zielbild);
        }
    } else {
        Shop::Container()->getLogService()->error(
            'Bild konnte nicht erstellt werden. Datei kein Bild?: ' . $imgFilename
        );
    }

    return $ret;
}

/**
 * @param array $xml
 */
function bearbeiteDeletes($xml)
{
    executeHook(HOOK_BILDER_XML_BEARBEITEDELETES, [
        'Artikel'          => $xml['del_bilder']['kArtikelPict'] ?? [],
        'Kategorie'        => $xml['del_bilder']['kKategoriePict'] ?? [],
        'KategoriePK'      => $xml['del_bilder']['kKategorie'] ?? [],
        'Eigenschaftswert' => $xml['del_bilder']['kEigenschaftWertPict'] ?? [],
        'Hersteller'       => $xml['del_bilder']['kHersteller'] ?? [],
        'Merkmal'          => $xml['del_bilder']['kMerkmal'] ?? [],
        'Merkmalwert'      => $xml['del_bilder']['kMerkmalWert'] ?? [],
    ]);
    $db = Shop::Container()->getDB();
    // Artikelbilder löschen Wawi <= .99923
    if (isset($xml['del_bilder']['kArtikelPict'])) {
        if (is_array($xml['del_bilder']['kArtikelPict'])) {
            foreach ($xml['del_bilder']['kArtikelPict'] as $kArtikelPict) {
                if ((int)$kArtikelPict > 0) {
                    loescheArtikelPict((int)$kArtikelPict);
                }
            }
        } elseif ((int)$xml['del_bilder']['kArtikelPict'] > 0) {
            loescheArtikelPict((int)$xml['del_bilder']['kArtikelPict']);
        }
    }
    //Artikelbilder löschen Wawi > .99923
    if (isset($xml['del_bilder']['tArtikelPict'])) {
        if (count($xml['del_bilder']['tArtikelPict']) > 1) {
            for ($i = 0; $i < (count($xml['del_bilder']['tArtikelPict']) / 2); $i++) {
                $index        = $i . ' attr';
                $oArtikelPict = (object)$xml['del_bilder']['tArtikelPict'][$index];
                loescheArtikelPict($oArtikelPict->kArtikel, $oArtikelPict->nNr);
            }
        } else {
            $oArtikelPict = (object)$xml['del_bilder']['tArtikelPict attr'];
            loescheArtikelPict($oArtikelPict->kArtikel, $oArtikelPict->nNr);
        }
    }
    //Kategoriebilder löschen Wawi <= .99923
    if (isset($xml['del_bilder']['kKategoriePict'])) {
        if (is_array($xml['del_bilder']['kKategoriePict'])) {
            foreach ($xml['del_bilder']['kKategoriePict'] as $kKategoriePict) {
                if ((int)$kKategoriePict > 0) {
                    loescheKategoriePict($kKategoriePict);
                }
            }
        } elseif ((int)$xml['del_bilder']['kKategoriePict'] > 0) {
            loescheKategoriePict((int)$xml['del_bilder']['kKategoriePict']);
        }
    }
    //Kategoriebilder löschen Wawi > .99923
    if (isset($xml['del_bilder']['kKategorie'])) {
        foreach ((array)$xml['del_bilder']['kKategorie'] as $kKategorie) {
            if ((int)$kKategorie > 0) {
                loescheKategoriePict(null, $kKategorie);
            }
        }
    }
    //Variationsbilder löschen Wawi <= .99923
    if (isset($xml['del_bilder']['kEigenschaftWertPict'])) {
        if (is_array($xml['del_bilder']['kEigenschaftWertPict'])) {
            foreach ($xml['del_bilder']['kEigenschaftWertPict'] as $kEigenschaftWertPict) {
                if ((int)$kEigenschaftWertPict > 0) {
                    loescheEigenschaftwertPict($kEigenschaftWertPict);
                }
            }
        } elseif ((int)$xml['del_bilder']['kEigenschaftWertPict'] > 0) {
            loescheEigenschaftwertPict($xml['del_bilder']['kEigenschaftWertPict']);
        }
    }
    //Variationsbilder löschen Wawi > .99923
    if (isset($xml['del_bilder']['kEigenschaftWert'])) {
        if (is_array($xml['del_bilder']['kEigenschaftWert'])) {
            foreach ($xml['del_bilder']['kEigenschaftWert'] as $kEigenschaftWert) {
                if ((int)$kEigenschaftWert > 0) {
                    loescheEigenschaftwertPict(null, $kEigenschaftWert);
                }
            }
        } elseif ((int)$xml['del_bilder']['kEigenschaftWert'] > 0) {
            loescheEigenschaftwertPict(null, $xml['del_bilder']['kEigenschaftWert']);
        }
    }
    //Herstellerbilder löschen
    if (isset($xml['del_bilder']['kHersteller'])) {
        $cacheTags = [];
        if (is_array($xml['del_bilder']['kHersteller'])) {
            foreach ($xml['del_bilder']['kHersteller'] as $kHersteller) {
                if ((int)$kHersteller > 0) {
                    $db->update(
                        'thersteller',
                        'kHersteller',
                        (int)$kHersteller,
                        (object)['cBildpfad' => '']
                    );
                    foreach ($db->selectAll(
                        'tartikel',
                        'kHersteller',
                        (int)$kHersteller,
                        'kArtikel'
                    ) as $article) {
                        $cacheTags[] = $article->kArtikel;
                    }
                }
            }
        } elseif ((int)$xml['del_bilder']['kHersteller'] > 0) {
            $db->update(
                'thersteller',
                'kHersteller',
                (int)$xml['del_bilder']['kHersteller'],
                (object)['cBildpfad' => '']
            );
            foreach ($db->selectAll(
                'tartikel',
                'kHersteller',
                (int)$xml['del_bilder']['kHersteller'],
                'kArtikel'
            ) as $article) {
                $cacheTags[] = $article->kArtikel;
            }
        }
        if (count($cacheTags) > 0) {
            array_walk($cacheTags, function (&$i) {
                $i = CACHING_GROUP_ARTICLE . '_' . $i;
            });
            Shop::Container()->getCache()->flushTags($cacheTags);
        }
    }
    //Merkmalbilder löschen
    if (isset($xml['del_bilder']['kMerkmal'])) {
        if (is_array($xml['del_bilder']['kMerkmal'])) {
            foreach ($xml['del_bilder']['kMerkmal'] as $kMerkmal) {
                if ((int)$kMerkmal > 0) {
                    $db->update(
                        'tmerkmal',
                        'kMerkmal',
                        (int)$kMerkmal,
                        (object)['cBildpfad' => '']
                    );
                }
            }
        } elseif ((int)$xml['del_bilder']['kMerkmal'] > 0) {
            $db->update(
                'tmerkmal',
                'kMerkmal',
                (int)$xml['del_bilder']['kMerkmal'],
                (object)['cBildpfad' => '']
            );
        }
    }
    //Merkmalwertbilder löschen
    if (isset($xml['del_bilder']['kMerkmalWert'])) {
        if (is_array($xml['del_bilder']['kMerkmalWert'])) {
            foreach ($xml['del_bilder']['kMerkmalWert'] as $kMerkmalWert) {
                if ((int)$kMerkmalWert > 0) {
                    $db->update(
                        'tmerkmalwert',
                        'kMerkmalWert',
                        (int)$kMerkmalWert,
                        (object)['cBildpfad' => '']
                    );
                    $db->delete('tmerkmalwertbild', 'kMerkmalWert', (int)$kMerkmalWert);
                }
            }
        } elseif ((int)$xml['del_bilder']['kMerkmalWert'] > 0) {
            $db->update(
                'tmerkmalwert',
                'kMerkmalWert',
                (int)$xml['del_bilder']['kMerkmalWert'],
                (object)['cBildpfad' => '']
            );
            $db->delete(
                'tmerkmalwertbild',
                'kMerkmalWert',
                (int)$xml['del_bilder']['kMerkmalWert']
            );
        }
    }
}

/**
 * @param int      $kArtikelPict
 * @param int|null $nNr
 */
function loescheArtikelPict(int $kArtikelPict, int $nNr = null)
{
    if ($kArtikelPict > 0) {
        $oArtikelPict = null;
        if ($nNr !== null && $nNr > 0) {
            $oArtikelPict = Shop::Container()->getDB()->select('tartikelpict', 'kArtikel', $kArtikelPict, 'nNr', $nNr);
            $kArtikelPict = $oArtikelPict->kArtikelPict ?? 0;
        }
        deleteArticleImage(null, 0, $kArtikelPict);
    }
}

/**
 * @param int|null $kKategoriePict
 * @param int|null $kKategorie
 */
function loescheKategoriePict(int $kKategoriePict = null, int $kKategorie = null)
{
    if ($kKategoriePict !== null && $kKategoriePict > 0) {
        Shop::Container()->getDB()->delete('tkategoriepict', 'kKategoriePict', $kKategoriePict);
    } elseif ($kKategorie !== null && $kKategorie > 0) {
        Shop::Container()->getDB()->delete('tkategoriepict', 'kKategorie', $kKategorie);
    }
}

/**
 * @param int|null $kEigenschaftwertPict
 * @param int|null $kEigenschaftwert
 */
function loescheEigenschaftwertPict(int $kEigenschaftwertPict = null, int $kEigenschaftwert = null)
{
    if ($kEigenschaftwert !== null && $kEigenschaftwert > 0) {
        Shop::Container()->getDB()->delete('teigenschaftwertpict', 'kEigenschaftWert', $kEigenschaftwert);
    }

    if ($kEigenschaftwertPict !== null && $kEigenschaftwertPict > 0) {
        Shop::Container()->getDB()->delete('teigenschaftwertpict', 'kEigenschaftwertPict', $kEigenschaftwertPict);
    }
}

/**
 * @param resource $im
 * @param resource $brand
 * @param object   $oBranding
 * @return mixed
 */
function brandImage($im, $brand, $oBranding)
{
    if (!$brand
        || (isset($oBranding->oBrandingEinstellung->nAktiv) && (int)$oBranding->oBrandingEinstellung->nAktiv === 0)
    ) {
        return $im;
    }
    //file_exists will return true even if cBrandingBild is not set - check before to avoid warning
    if (!isset($oBranding->oBrandingEinstellung->cBrandingBild)) {
        return $im;
    }
    $brandingImage = PFAD_ROOT . PFAD_BRANDINGBILDER . $oBranding->oBrandingEinstellung->cBrandingBild;
    if (!file_exists($brandingImage)) {
        return $im;
    }

    $position     = $oBranding->oBrandingEinstellung->cPosition;
    $transparency = $oBranding->oBrandingEinstellung->dTransparenz;
    $brandingSize = $oBranding->oBrandingEinstellung->dGroesse;
    $randabstand  = $oBranding->oBrandingEinstellung->dRandabstand / 100;
    $branding     = imageload_alpha($brandingImage, 0, 0, true);

    if ($im && $branding) {
        $bildBreite         = imagesx($im);
        $bildHoehe          = imagesy($im);
        $brandingBreite     = imagesx($branding);
        $brandingHoehe      = imagesy($branding);
        $brandingNeueBreite = $brandingBreite;
        $brandingNeueHoehe  = $brandingHoehe;
        $image_branding     = $branding;
        //branding auf diese Breite skalieren
        if ($brandingSize > 0) {
            $brandingNeueBreite = round(($bildBreite * $brandingSize) / 100.0);
            $brandingNeueHoehe  = round(($brandingNeueBreite / $brandingBreite) * $brandingHoehe);

            $image_branding = imageload_alpha($brandingImage, $brandingNeueBreite, $brandingNeueHoehe, true);
        }
        //position bestimmen
        $brandingPosX = 0;
        $brandingPosY = 0;
        switch ($position) {
            case 'oben':
                $brandingPosX = $bildBreite / 2 - $brandingNeueBreite / 2;
                $brandingPosY = $bildHoehe * $randabstand;
                break;

            case 'oben-rechts':
                $brandingPosX = $bildBreite - $brandingNeueBreite - $bildBreite * $randabstand;
                $brandingPosY = $bildHoehe * $randabstand;
                break;

            case 'rechts':
                $brandingPosX = $bildBreite - $brandingNeueBreite - $bildBreite * $randabstand;
                $brandingPosY = $bildHoehe / 2 - $brandingNeueHoehe / 2;
                break;

            case 'unten-rechts':
                $brandingPosX = $bildBreite - $brandingNeueBreite - $bildBreite * $randabstand;
                $brandingPosY = $bildHoehe - $brandingNeueHoehe - $bildHoehe * $randabstand;
                break;

            case 'unten':
                $brandingPosX = $bildBreite / 2 - $brandingNeueBreite / 2;
                $brandingPosY = $bildHoehe - $brandingNeueHoehe - $bildHoehe * $randabstand;
                break;

            case 'unten-links':
                $brandingPosX = $bildBreite * $randabstand;
                $brandingPosY = $bildHoehe - $brandingNeueHoehe - $bildHoehe * $randabstand;
                break;

            case 'links':
                $brandingPosX = $bildBreite * $randabstand;
                $brandingPosY = $bildHoehe / 2 - $brandingNeueHoehe / 2;
                break;

            case 'oben-links':
                $brandingPosX = $bildBreite * $randabstand;
                $brandingPosY = $bildHoehe * $randabstand;
                break;

            case 'zentriert':
                $brandingPosX = $bildBreite / 2 - $brandingNeueBreite / 2;
                $brandingPosY = $bildHoehe / 2 - $brandingNeueHoehe / 2;
                break;
        }
        $brandingPosX = round($brandingPosX);
        $brandingPosY = round($brandingPosY);
        //bild mit branding composen
        imagealphablending($im, true);
        imagesavealpha($im, true);
        imagecopymerge_alpha(
            $im,
            $image_branding,
            $brandingPosX,
            $brandingPosY,
            0,
            0,
            $brandingNeueBreite,
            $brandingNeueHoehe,
            100 - $transparency
        );

        return $im;
    }

    return $im;
}

/**
 * @param resource $dst_im
 * @param resource $src_im
 * @param int      $dst_x
 * @param int      $dst_y
 * @param int      $src_x
 * @param int      $src_y
 * @param int      $src_w
 * @param int      $src_h
 * @param int      $pct
 * @return bool
 */
function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct)
{
    if ($pct === null) {
        return false;
    }
    $pct /= 100;
    // Get image width and height
    $w = imagesx($src_im);
    $h = imagesy($src_im);
    // Turn alpha blending off
    imagealphablending($src_im, false);
    // Find the most opaque pixel in the image (the one with the smallest alpha value)
    /*
    $minalpha = 127;
    for( $x = 0; $x < $w; $x++ )
    for( $y = 0; $y < $h; $y++ ){
        $alpha = ( imagecolorat( $src_im, $x, $y ) >> 24 ) & 0xFF;
        if( $alpha < $minalpha ){
            $minalpha = $alpha;
        }
    }
    */

    $minalpha = 0;
    // loop through image pixels and modify alpha for each
    for ($x = 0; $x < $w; $x++) {
        for ($y = 0; $y < $h; $y++) {
            // get current alpha value (represents the TANSPARENCY!)
            $colorxy = imagecolorat($src_im, $x, $y);
            $alpha   = ($colorxy >> 24) & 0xFF;
            // calculate new alpha
            if ($minalpha !== 127) {
                $alpha = 127 + 127 * $pct * ($alpha - 127) / (127 - $minalpha);
            } else {
                $alpha += 127 * $pct;
            }
            // get the color index with new alpha
            $alphacolorxy = imagecolorallocatealpha(
                $src_im,
                ($colorxy >> 16) & 0xFF,
                ($colorxy >> 8) & 0xFF,
                $colorxy & 0xFF,
                $alpha
            );
            // set pixel with the new color + opacity
            if (!imagesetpixel($src_im, $x, $y, $alphacolorxy)) {
                return false;
            }
        }
    }
    // The image copy
    imagecopy($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);

    return true;
}

/**
 * @param resource $dst_im
 * @param resource $src_im
 * @param int      $dst_x
 * @param int      $dst_y
 * @param int      $src_x
 * @param int      $src_y
 * @param int      $src_w
 * @param int      $src_h
 * @param int      $pct
 */
function imagecopymerge_alpha_fast($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct)
{
    $opacity = $pct;
    $cut     = imagecreatetruecolor($src_w, $src_h);

    imagecopy($cut, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h);
    imagecopy($cut, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h);
    imagecopymerge($dst_im, $cut, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $opacity);
}

/**
 * @param string $imgFilename
 * @return bool|string
 */
function gibBildformat(string $imgFilename)
{
    if (!file_exists($imgFilename)) {
        return false;
    }
    $size = getimagesize($imgFilename);
    $type = $size[2];
    switch ($type) {
        case IMAGETYPE_JPEG:
            return 'jpg';
            break;

        case IMAGETYPE_PNG:
            if (function_exists('imagecreatefrompng')) {
                return 'png';
            }
            break;

        case IMAGETYPE_GIF:
            if (function_exists('imagecreatefromgif')) {
                return 'gif';
            }
            break;

        case IMAGETYPE_BMP:
            if (function_exists('imagecreatefromwbmp')) {
                return 'bmp';
            }
            break;

        default:
            break;
    }

    return false;
}

/**
 * @param string $img
 * @param int    $nWidth
 * @param int    $nHeight
 * @param int    $nContainerWidth
 * @param int    $nContainerHeight
 * @return resource
 */
function imageload_container($img, int $nWidth, int $nHeight, $nContainerWidth, $nContainerHeight)
{
    $imgInfo = getimagesize($img);
    switch ($imgInfo[2]) {
        case 1:
            $im = imagecreatefromgif($img);
            break;
        case 2:
            $im = imagecreatefromjpeg($img);
            break;
        case 3:
            $im = imagecreatefrompng($img);
            break;
        default:
            $im = imagecreatefromjpeg($img);
            break;
    }

    if ($nWidth === 0 && $nHeight === 0) {
        $nHeight = $imgInfo[1];
        $nWidth  = $imgInfo[0];
    }
    $nWidth  = round($nWidth);
    $nHeight = round($nHeight);
    $newImg  = imagecreatetruecolor($nContainerWidth, $nContainerHeight);
    // hintergrundfarbe
    $format = strtolower($GLOBALS['Einstellungen']['bilder']['bilder_dateiformat']);
    if ($format === 'jpg') {
        $rgb   = html2rgb($GLOBALS['Einstellungen']['bilder']['bilder_hintergrundfarbe']);
        $color = imagecolorallocate($newImg, $rgb[0], $rgb[1], $rgb[2]);
        imagealphablending($newImg, true);
    } else {
        $color = imagecolorallocatealpha($newImg, 255, 255, 255, 127);
        imagealphablending($newImg, false);
    }
    imagesavealpha($newImg, true);
    imagefilledrectangle($newImg, 0, 0, $nContainerWidth, $nContainerHeight, $color);

    $nPosX = ($nContainerWidth / 2) - ($nWidth / 2);
    $nPosY = ($nContainerHeight / 2) - ($nHeight / 2);

    imagecopyresampled($newImg, $im, $nPosX, $nPosY, 0, 0, $nWidth, $nHeight, $imgInfo[0], $imgInfo[1]);

    return $newImg;
}

/**
 * @param string $img
 * @param int    $nWidth
 * @param int    $nHeight
 * @param bool   $branding
 * @return resource
 */
function imageload_alpha($img, int $nWidth = 0, int $nHeight = 0, bool $branding = false)
{
    $imgInfo = getimagesize($img);
    switch ($imgInfo[2]) {
        case 1:
            $im = imagecreatefromgif($img);
            break;

        case 2:
            $im = imagecreatefromjpeg($img);
            break;

        case 3:
            $im = imagecreatefrompng($img);
            break;
    }

    if ($nWidth === 0 && $nHeight === 0) {
        $nHeight = $imgInfo[1];
        $nWidth  = $imgInfo[0];
    }

    $nWidth  = round($nWidth);
    $nHeight = round($nHeight);
    $newImg  = imagecreatetruecolor($nWidth, $nHeight);

    if (!$newImg) {
        return $img;
    }

    // hintergrundfarbe
    $format = strtolower($GLOBALS['Einstellungen']['bilder']['bilder_dateiformat']);
    if ($format === 'jpg') {
        $rgb   = html2rgb($GLOBALS['Einstellungen']['bilder']['bilder_hintergrundfarbe']);
        $color = imagecolorallocate($newImg, $rgb[0], $rgb[1], $rgb[2]);
        if ($branding) {
            imagealphablending($newImg, false);
        } else {
            imagealphablending($newImg, true);
        }
    } else {
        $color = imagecolorallocatealpha($newImg, 255, 255, 255, 127);
        imagealphablending($newImg, false);
    }

    imagesavealpha($newImg, true);
    imagefilledrectangle($newImg, 0, 0, $nWidth, $nHeight, $color);
    imagecopyresampled($newImg, $im, 0, 0, 0, 0, $nWidth, $nHeight, $imgInfo[0], $imgInfo[1]);

    return $newImg;
}

/**
 * @param string $path
 * @return string
 */
function neuerDateiname(string $path): string
{
    $format = strtolower($GLOBALS['Einstellungen']['bilder']['bilder_dateiformat']);
    $path   = substr($path, 0, -3);
    $path  .= $format;

    return $path;
}

/**
 * @param resource $im
 * @param string   $format
 * @param string   $path
 * @param int      $quality
 * @return bool
 */
function speichereBild($im, $format, $path, int $quality = 80)
{
    if (!$format || !$im) {
        return false;
    }

    $path = neuerDateiname($path);

    switch (strtolower($format)) {
        case 'jpg':
            return function_exists('imagejpeg') ? imagejpeg($im, $path, $quality) : false;
        case 'png':
            return function_exists('imagepng') ? imagepng($im, $path) : false;
        case 'gif':
            return function_exists('imagegif') ? imagegif($im, $path) : false;
        case 'bmp':
            return function_exists('imagewbmp') ? imagewbmp($im, $path) : false;
    }

    return false;
}

/**
 * @return array
 */
function holeBilderEinstellungen(): array
{
    $db           = Shop::Container()->getDB();
    $branding     = [];
    $brandingData = $db->query(
        'SELECT * FROM tbranding',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($brandingData as $oBrandingTMP) {
        $branding[$oBrandingTMP->cBildKategorie] = $oBrandingTMP;
    }
    foreach ($branding as $i => $oBranding) {
        $branding[$i]->oBrandingEinstellung = $db->select(
            'tbrandingeinstellung',
            'kBranding',
            (int)$oBranding->kBranding
        );
    }

    return $branding;
}
