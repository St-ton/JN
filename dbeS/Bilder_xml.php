<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use dbeS\TableMapper as Mapper;

require_once __DIR__ . '/syncinclude.php';
$return       = 3;
$conf         = null;
$brandingConf = null;
$zipFile      = '';
if (auth()) {
    $zipFile      = checkFile();
    $conf         = Shop::getSettings([CONF_BILDER]);
    $brandingConf = holeBilderEinstellungen();

    if (!$conf['bilder']['bilder_kategorien_breite']) {
        $conf['bilder']['bilder_kategorien_breite'] = 100;
    }
    if (!$conf['bilder']['bilder_kategorien_hoehe']) {
        $conf['bilder']['bilder_kategorien_hoehe'] = 100;
    }
    if (!$conf['bilder']['bilder_variationen_gross_breite']) {
        $conf['bilder']['bilder_variationen_gross_breite'] = 800;
    }
    if (!$conf['bilder']['bilder_variationen_gross_hoehe']) {
        $conf['bilder']['bilder_variationen_gross_hoehe'] = 800;
    }
    if (!$conf['bilder']['bilder_variationen_breite']) {
        $conf['bilder']['bilder_variationen_breite'] = 210;
    }
    if (!$conf['bilder']['bilder_variationen_hoehe']) {
        $conf['bilder']['bilder_variationen_hoehe'] = 210;
    }
    if (!$conf['bilder']['bilder_variationen_mini_breite']) {
        $conf['bilder']['bilder_variationen_mini_breite'] = 30;
    }
    if (!$conf['bilder']['bilder_variationen_mini_hoehe']) {
        $conf['bilder']['bilder_variationen_mini_hoehe'] = 30;
    }
    if (!$conf['bilder']['bilder_artikel_gross_breite']) {
        $conf['bilder']['bilder_artikel_gross_breite'] = 800;
    }
    if (!$conf['bilder']['bilder_artikel_gross_hoehe']) {
        $conf['bilder']['bilder_artikel_gross_hoehe'] = 800;
    }
    if (!$conf['bilder']['bilder_artikel_normal_breite']) {
        $conf['bilder']['bilder_artikel_normal_breite'] = 210;
    }
    if (!$conf['bilder']['bilder_artikel_normal_hoehe']) {
        $conf['bilder']['bilder_artikel_normal_hoehe'] = 210;
    }
    if (!$conf['bilder']['bilder_artikel_klein_breite']) {
        $conf['bilder']['bilder_artikel_klein_breite'] = 80;
    }
    if (!$conf['bilder']['bilder_artikel_klein_hoehe']) {
        $conf['bilder']['bilder_artikel_klein_hoehe'] = 80;
    }
    if (!$conf['bilder']['bilder_artikel_mini_breite']) {
        $conf['bilder']['bilder_artikel_mini_breite'] = 30;
    }
    if (!$conf['bilder']['bilder_artikel_mini_hoehe']) {
        $conf['bilder']['bilder_artikel_mini_hoehe'] = 30;
    }
    if (!$conf['bilder']['bilder_hersteller_normal_breite']) {
        $conf['bilder']['bilder_hersteller_normal_breite'] = 100;
    }
    if (!$conf['bilder']['bilder_hersteller_normal_hoehe']) {
        $conf['bilder']['bilder_hersteller_normal_hoehe'] = 100;
    }
    if (!$conf['bilder']['bilder_hersteller_klein_breite']) {
        $conf['bilder']['bilder_hersteller_klein_breite'] = 40;
    }
    if (!$conf['bilder']['bilder_hersteller_klein_hoehe']) {
        $conf['bilder']['bilder_hersteller_klein_hoehe'] = 40;
    }
    if (!$conf['bilder']['bilder_merkmal_normal_breite']) {
        $conf['bilder']['bilder_merkmal_normal_breite'] = 100;
    }
    if (!$conf['bilder']['bilder_merkmal_normal_hoehe']) {
        $conf['bilder']['bilder_merkmal_normal_hoehe'] = 100;
    }
    if (!$conf['bilder']['bilder_merkmal_klein_breite']) {
        $conf['bilder']['bilder_merkmal_klein_breite'] = 20;
    }
    if (!$conf['bilder']['bilder_merkmal_klein_hoehe']) {
        $conf['bilder']['bilder_merkmal_klein_hoehe'] = 20;
    }
    if (!$conf['bilder']['bilder_merkmalwert_normal_breite']) {
        $conf['bilder']['bilder_merkmalwert_normal_breite'] = 100;
    }
    if (!$conf['bilder']['bilder_merkmalwert_normal_hoehe']) {
        $conf['bilder']['bilder_merkmalwert_normal_hoehe'] = 100;
    }
    if (!$conf['bilder']['bilder_merkmalwert_klein_breite']) {
        $conf['bilder']['bilder_merkmalwert_klein_breite'] = 20;
    }
    if (!$conf['bilder']['bilder_merkmalwert_klein_hoehe']) {
        $conf['bilder']['bilder_merkmalwert_klein_hoehe'] = 20;
    }
    if (!$conf['bilder']['bilder_konfiggruppe_klein_breite']) {
        $conf['bilder']['bilder_konfiggruppe_klein_breite'] = 130;
    }
    if (!$conf['bilder']['bilder_konfiggruppe_klein_hoehe']) {
        $conf['bilder']['bilder_konfiggruppe_klein_hoehe'] = 130;
    }
    if (!$conf['bilder']['bilder_jpg_quali']) {
        $conf['bilder']['bilder_jpg_quali'] = 80;
    }
    if (!$conf['bilder']['bilder_dateiformat']) {
        $conf['bilder']['bilder_dateiformat'] = 'PNG';
    }
    if (!$conf['bilder']['bilder_hintergrundfarbe']) {
        $conf['bilder']['bilder_hintergrundfarbe'] = '#ffffff';
    }
    if (!$conf['bilder']['bilder_skalieren']) {
        $conf['bilder']['bilder_skalieren'] = 'N';
    }
    $lang = Shop::Container()->getDB()->select('tsprache', 'cShopStandard', 'Y');
    $cSQL = '';
    if (!$lang->kSprache) {
        $lang->kSprache = $_SESSION['kSprache'];
        $cSQL           = 'AND tseo.kSprache = ' . $lang->kSprache;
    }
    if ($lang->kSprache > 0) {
        $cSQL = ' AND tseo.kSprache = ' . $lang->kSprache;
    }
    $return    = 2;
    $zipFile   = $_FILES['data']['tmp_name'];
    $unzipPath = PFAD_ROOT .
        PFAD_DBES .
        PFAD_SYNC_TMP .
        basename($_FILES['data']['tmp_name']) . '_' .
        date('dhis') . '/';
    $db        = Shop::Container()->getDB();
    if (($syncFiles = unzipSyncFiles($zipFile, $unzipPath, __FILE__)) === false) {
        Shop::Container()->getLogService()->error('Error: Cannot extract zip file ' . $zipFile . ' to ' . $unzipPath);
        removeTemporaryFiles($zipFile);
    } else {
        $return = 0;
        $db->query('START TRANSACTION', \DB\ReturnType::DEFAULT);
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
                    bearbeite($xml, $unzipPath, $brandingConf);
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
        $db->query('COMMIT', \DB\ReturnType::DEFAULT);
        removeTemporaryFiles(substr($unzipPath, 0, -1), true);
    }
}

echo $return;

/**
 * @param array  $xml
 * @param string $unzipPath
 * @param array  $brandingConf
 * @throws \Exceptions\CircularReferenceException
 * @throws \Exceptions\ServiceNotFoundException
 */
function bearbeite($xml, string $unzipPath, array $brandingConf)
{
    $productImages      = mapArray($xml['bilder'], 'tartikelpict', Mapper::getMapping('mArtikelPict'));
    $categoryImages     = mapArray($xml['bilder'], 'tkategoriepict', Mapper::getMapping('mKategoriePict'));
    $propertyImages     = mapArray($xml['bilder'], 'teigenschaftwertpict', Mapper::getMapping('mEigenschaftWertPict'));
    $manufacturerImages = mapArray($xml['bilder'], 'therstellerbild', Mapper::getMapping('mEigenschaftWertPict'));
    $attrValImages      = mapArray($xml['bilder'], 'tmerkmalwertbild', Mapper::getMapping('mEigenschaftWertPict'));
    $attributeImages    = mapArray($xml['bilder'], 'tMerkmalbild', Mapper::getMapping('mEigenschaftWertPict'));
    $configImages       = mapArray($xml['bilder'], 'tkonfiggruppebild', Mapper::getMapping('mKonfiggruppePict'));

    $db   = Shop::Container()->getDB();
    $conf = Shop::getSettings([CONF_BILDER]);

    executeHook(HOOK_BILDER_XML_BEARBEITE, [
        'Pfad'             => $unzipPath,
        'Artikel'          => &$productImages,
        'Kategorie'        => &$categoryImages,
        'Eigenschaftswert' => &$propertyImages,
        'Hersteller'       => &$manufacturerImages,
        'Merkmalwert'      => &$attrValImages,
        'Merkmal'          => &$attributeImages,
        'Konfiggruppe'     => &$configImages
    ]);
    foreach ($productImages as $image) {
        if (strlen($image->cPfad) <= 0) {
            continue;
        }
        $image->nNr  = (int)$image->nNr;
        $imgFilename = $image->cPfad;
        $format      = gibBildformat($unzipPath . $imgFilename);
        if (!$format) {
            Shop::Container()->getLogService()->error(
                'Bildformat des Artikelbildes konnte nicht ermittelt werden. Datei ' .
                $imgFilename . ' keine Bilddatei?'
            );
            continue;
        }
        // first delete by kArtikelPict
        loescheArtikelPict($image->kArtikelPict, 0);
        // then delete by kArtikel + nNr since Wawi > .99923 has changed all kArtikelPict keys
        if (isset($image->nNr) && $image->nNr > 0) {
            loescheArtikelPict($image->kArtikel, $image->nNr);
        }
        if ($image->kMainArtikelBild > 0) {
            $main = $db->select(
                'tartikelpict',
                'kArtikelPict',
                (int)$image->kMainArtikelBild
            );
            if (isset($main->cPfad) && strlen($main->cPfad) > 0) {
                $image->cPfad = neuerDateiname($main->cPfad);
                DBUpdateInsert('tartikelpict', [$image], 'kArtikel', 'kArtikelpict');
            } else {
                erstelleArtikelBild($image, $format, $unzipPath, $imgFilename, $brandingConf);
            }
        } else {
            $productImage = $db->select(
                'tartikelpict',
                'kArtikelPict',
                (int)$image->kArtikelPict
            );
            // update all references, if img is used by other products
            if (isset($productImage->cPfad) && strlen($productImage->cPfad) > 0) {
                $db->update(
                    'tartikelpict',
                    'kMainArtikelBild',
                    (int)$productImage->kArtikelPict,
                    (object)['cPfad' => $productImage->cPfad]
                );
            }
            erstelleArtikelBild($image, $format, $unzipPath, $imgFilename, $brandingConf);
        }
    }
    if (count($productImages) > 0) {
        $handle = @opendir($unzipPath);
        while (false !== ($file = readdir($handle))) {
            if ($file !== '.' && $file !== '..' && $file !== 'bilder_a.xml' && file_exists($unzipPath . $file)) {
                if (!unlink($unzipPath . $file)) {
                    Shop::Container()->getLogService()->error('Artikelbild konnte nicht geloescht werden: ' . $file);
                }
            }
        }
        @closedir($handle);
    }
    foreach ($categoryImages as $categoryImage) {
        if (strlen($categoryImage->cPfad) > 0) {
            $imgFilename = $categoryImage->cPfad;
            $format      = gibBildformat($unzipPath . $imgFilename);
            if (!$format) {
                Shop::Container()->getLogService()->error(
                    'Bildformat des Kategoriebildes konnte nicht ermittelt werden. Datei ' .
                    $imgFilename . ' keine Bilddatei?'
                );
                continue;
            }

            $categoryImage->cPfad = gibKategoriebildname($categoryImage, $format);
            $categoryImage->cPfad = neuerDateiname($categoryImage->cPfad);
            if (erstelleThumbnail(
                $brandingConf['Kategorie'],
                $unzipPath . $imgFilename,
                PFAD_KATEGORIEBILDER . $categoryImage->cPfad,
                $conf['bilder']['bilder_kategorien_breite'],
                $conf['bilder']['bilder_kategorien_hoehe'],
                $conf['bilder']['bilder_jpg_quali'],
                1,
                $conf['bilder']['container_verwenden']
            )) {
                DBUpdateInsert('tkategoriepict', [$categoryImage], 'kKategorie');
            }
            unlink($unzipPath . $imgFilename);
        }
    }
    foreach ($propertyImages as $propertyImage) {
        if (strlen($propertyImage->cPfad) > 0) {
            $imgFilename = $propertyImage->cPfad;
            $format      = gibBildformat($unzipPath . $imgFilename);
            if (!$format) {
                Shop::Container()->getLogService()->error(
                    'Bildformat des Eigenschaftwertbildes konnte nicht ermittelt werden. Datei ' .
                    $imgFilename . ' keine Bilddatei?'
                );
                continue;
            }
            $propertyImage->cPfad = gibEigenschaftwertbildname($propertyImage, $format);
            $propertyImage->cPfad = neuerDateiname($propertyImage->cPfad);
            erstelleThumbnail(
                $brandingConf['Variationen'],
                $unzipPath . $imgFilename,
                PFAD_VARIATIONSBILDER_GROSS . $propertyImage->cPfad,
                $conf['bilder']['bilder_variationen_gross_breite'],
                $conf['bilder']['bilder_variationen_gross_hoehe'],
                $conf['bilder']['bilder_jpg_quali'],
                1,
                $conf['bilder']['container_verwenden']
            );
            erstelleThumbnailBranded(
                PFAD_ROOT . PFAD_VARIATIONSBILDER_GROSS . $propertyImage->cPfad,
                PFAD_VARIATIONSBILDER_NORMAL . $propertyImage->cPfad,
                $conf['bilder']['bilder_variationen_breite'],
                $conf['bilder']['bilder_variationen_hoehe'],
                $conf['bilder']['bilder_jpg_quali'],
                $conf['bilder']['container_verwenden']
            );
            if (erstelleThumbnailBranded(
                PFAD_ROOT . PFAD_VARIATIONSBILDER_GROSS . $propertyImage->cPfad,
                PFAD_VARIATIONSBILDER_MINI . $propertyImage->cPfad,
                $conf['bilder']['bilder_variationen_mini_breite'],
                $conf['bilder']['bilder_variationen_mini_hoehe'],
                $conf['bilder']['bilder_jpg_quali'],
                $conf['bilder']['container_verwenden']
            )) {
                DBUpdateInsert('teigenschaftwertpict', [$propertyImage], 'kEigenschaftWert');
            }
            unlink($unzipPath . $imgFilename);
        }
    }
    foreach ($manufacturerImages as $manufacturerImage) {
        $manufacturerImage->kHersteller = (int)$manufacturerImage->kHersteller;
        if (strlen($manufacturerImage->cPfad) > 0 && $manufacturerImage->kHersteller > 0) {
            $imgFilename = $manufacturerImage->cPfad;
            $format      = gibBildformat($unzipPath . $imgFilename);
            if (!$format) {
                Shop::Container()->getLogService()->error(
                    'Bildformat des Herstellerbildes konnte nicht ermittelt werden. Datei ' .
                    $imgFilename . ' keine Bilddatei?'
                );
                continue;
            }
            $manufacturer = $db->query(
                'SELECT cSeo
                    FROM thersteller
                    WHERE kHersteller = ' . (int)$manufacturerImage->kHersteller,
                \DB\ReturnType::SINGLE_OBJECT
            );
            if (isset($manufacturer->cSeo) && strlen($manufacturer->cSeo) > 0) {
                $manufacturerImage->cPfad = str_replace('/', '_', $manufacturer->cSeo . '.' . $format);
            } elseif (stripos(strrev($manufacturerImage->cPfad), strrev($format)) !== 0) {
                $manufacturerImage->cPfad .= '.' . $format;
            }
            $manufacturerImage->cPfad = neuerDateiname($manufacturerImage->cPfad);
            erstelleThumbnail(
                $brandingConf['Hersteller'],
                $unzipPath . $imgFilename,
                PFAD_HERSTELLERBILDER_NORMAL . $manufacturerImage->cPfad,
                $conf['bilder']['bilder_hersteller_normal_breite'],
                $conf['bilder']['bilder_hersteller_normal_hoehe'],
                $conf['bilder']['bilder_jpg_quali'],
                1,
                $conf['bilder']['container_verwenden']
            );
            if (erstelleThumbnailBranded(
                PFAD_ROOT . PFAD_HERSTELLERBILDER_NORMAL . $manufacturerImage->cPfad,
                PFAD_HERSTELLERBILDER_KLEIN . $manufacturerImage->cPfad,
                $conf['bilder']['bilder_hersteller_klein_breite'],
                $conf['bilder']['bilder_hersteller_klein_hoehe'],
                $conf['bilder']['bilder_jpg_quali'],
                $conf['bilder']['container_verwenden']
            )) {
                $db->update(
                    'thersteller',
                    'kHersteller',
                    (int)$manufacturerImage->kHersteller,
                    (object)['cBildpfad' => $manufacturerImage->cPfad]
                );
            }
            $cacheTags = [];
            foreach ($db->selectAll(
                'tartikel',
                'kHersteller',
                (int)$manufacturerImage->kHersteller,
                'kArtikel'
            ) as $article) {
                $cacheTags[] = CACHING_GROUP_ARTICLE . '_' . $article->kArtikel;
            }
            Shop::Container()->getCache()->flushTags($cacheTags);
            unlink($unzipPath . $imgFilename);
        }
    }
    foreach ($attributeImages as $attributeImage) {
        $attributeImage->kMerkmal = (int)$attributeImage->kMerkmal;
        if (strlen($attributeImage->cPfad) > 0 && $attributeImage->kMerkmal > 0) {
            $imgFilename = $attributeImage->cPfad;
            $format  = gibBildformat($unzipPath . $imgFilename);
            if (!$format) {
                Shop::Container()->getLogService()->error(
                    'Bildformat des Merkmalbildes konnte nicht ermittelt werden. Datei ' .
                    $imgFilename . ' keine Bilddatei?'
                );
                continue;
            }
            $attributeImage->cPfad .= '.' . $format;
            $attributeImage->cPfad  = neuerDateiname($attributeImage->cPfad);
            erstelleThumbnail(
                $brandingConf['Merkmale'],
                $unzipPath . $imgFilename,
                PFAD_MERKMALBILDER_NORMAL . $attributeImage->cPfad,
                $conf['bilder']['bilder_merkmal_normal_breite'],
                $conf['bilder']['bilder_merkmal_normal_hoehe'],
                $conf['bilder']['bilder_jpg_quali'],
                1,
                $conf['bilder']['container_verwenden']
            );
            if (erstelleThumbnailBranded(
                PFAD_ROOT . PFAD_MERKMALBILDER_NORMAL . $attributeImage->cPfad,
                PFAD_MERKMALBILDER_KLEIN . $attributeImage->cPfad,
                $conf['bilder']['bilder_merkmal_klein_breite'],
                $conf['bilder']['bilder_merkmal_klein_hoehe'],
                $conf['bilder']['bilder_jpg_quali'],
                $conf['bilder']['container_verwenden']
            )) {
                $db->update(
                    'tmerkmal',
                    'kMerkmal',
                    (int)$attributeImage->kMerkmal,
                    (object)['cBildpfad' => $attributeImage->cPfad]
                );
            }
            unlink($unzipPath . $imgFilename);
        }
    }
    foreach ($attrValImages as $attrValImage) {
        $attrValImage->kMerkmalWert = (int)$attrValImage->kMerkmalWert;
        if (strlen($attrValImage->cPfad) > 0 && $attrValImage->kMerkmalWert > 0) {
            $imgFilename = $attrValImage->cPfad;
            $format      = gibBildformat($unzipPath . $imgFilename);
            if (!$format) {
                Shop::Container()->getLogService()->error(
                    'Bildformat des Merkmalwertbildes konnte nicht ermittelt werden. Datei ' .
                    $imgFilename . ' keine Bilddatei?'
                );
                continue;
            }
            $attrValImage->cPfad .= '.' . $format;
            $attrValImage->cPfad  = neuerDateiname($attrValImage->cPfad);
            erstelleThumbnail(
                $brandingConf['Merkmalwerte'],
                $unzipPath . $imgFilename,
                PFAD_MERKMALWERTBILDER_NORMAL . $attrValImage->cPfad,
                $conf['bilder']['bilder_merkmalwert_normal_breite'],
                $conf['bilder']['bilder_merkmalwert_normal_hoehe'],
                $conf['bilder']['bilder_jpg_quali'],
                1,
                $conf['bilder']['container_verwenden']
            );
            if (erstelleThumbnailBranded(
                PFAD_ROOT . PFAD_MERKMALWERTBILDER_NORMAL . $attrValImage->cPfad,
                PFAD_MERKMALWERTBILDER_KLEIN . $attrValImage->cPfad,
                $conf['bilder']['bilder_merkmalwert_klein_breite'],
                $conf['bilder']['bilder_merkmalwert_klein_hoehe'],
                $conf['bilder']['bilder_jpg_quali'],
                $conf['bilder']['container_verwenden']
            )) {
                $db->update(
                    'tmerkmalwert',
                    'kMerkmalWert',
                    (int)$attrValImage->kMerkmalWert,
                    (object)['cBildpfad' => $attrValImage->cPfad]
                );
                $oMerkmalwertbild               = new stdClass();
                $oMerkmalwertbild->kMerkmalWert = (int)$attrValImage->kMerkmalWert;
                $oMerkmalwertbild->cBildpfad    = $attrValImage->cPfad;

                DBUpdateInsert('tmerkmalwertbild', [$oMerkmalwertbild], 'kMerkmalWert');
            }
            unlink($unzipPath . $imgFilename);
        }
    }
    foreach ($configImages as $configImage) {
        $item                = new stdClass();
        $item->cBildPfad     = $configImage->cPfad;
        $item->kKonfiggruppe = $configImage->kKonfiggruppe;

        if (strlen($item->cBildPfad) > 0) {
            $imgFilename = $item->cBildPfad;
            $format  = gibBildformat($unzipPath . $imgFilename);
            if (!$format) {
                Shop::Container()->getLogService()->error(
                    'Bildformat des Konfiggruppenbildes konnte nicht ermittelt werden. Datei ' .
                    $imgFilename . ' keine Bilddatei?'
                );
                continue;
            }
            $item->cBildPfad = $item->kKonfiggruppe . '.' . $format;
            $item->cBildPfad = neuerDateiname($item->cBildPfad);

            $branding                               = new stdClass();
            $branding->oBrandingEinstellung         = new stdClass();
            $branding->oBrandingEinstellung->nAktiv = 0;

            if (erstelleThumbnail(
                $branding,
                $unzipPath . $imgFilename,
                PFAD_KONFIGURATOR_KLEIN . $item->cBildPfad,
                $conf['bilder']['bilder_konfiggruppe_klein_breite'],
                $conf['bilder']['bilder_konfiggruppe_klein_hoehe'],
                $conf['bilder']['bilder_jpg_quali'],
                1,
                $conf['bilder']['container_verwenden']
            )) {
                $db->update(
                    'tkonfiggruppe',
                    'kKonfiggruppe',
                    (int)$item->kKonfiggruppe,
                    (object)['cBildPfad' => $item->cBildPfad]
                );
            }
            unlink($unzipPath . $imgFilename);
        }
    }

    executeHook(HOOK_BILDER_XML_BEARBEITE_ENDE, [
        'Artikel'          => &$productImages,
        'Kategorie'        => &$categoryImages,
        'Eigenschaftswert' => &$propertyImages,
        'Hersteller'       => &$manufacturerImages,
        'Merkmalwert'      => &$attrValImages,
        'Merkmal'          => &$attributeImages,
        'Konfiggruppe'     => &$configImages
    ]);
}

/**
 * @param stdClass $img
 * @param string   $format
 * @param string   $unzipPath
 * @param string   $imgFilename
 * @param array    $brandingConf
 * @throws \Exceptions\CircularReferenceException
 * @throws \Exceptions\ServiceNotFoundException
 */
function erstelleArtikelBild($img, $format, $unzipPath, $imgFilename, $brandingConf)
{
    $conf       = Shop::getSettings([CONF_BILDER]);
    $img->cPfad = gibArtikelbildname($img, $conf['bilder']['container_verwenden'] === 'Y' ? 'png' : $format);
    $img->cPfad = neuerDateiname($img->cPfad);
    erstelleThumbnail(
        $brandingConf['Artikel'],
        $unzipPath . $imgFilename,
        PFAD_PRODUKTBILDER_GROSS . $img->cPfad,
        $conf['bilder']['bilder_artikel_gross_breite'],
        $conf['bilder']['bilder_artikel_gross_hoehe'],
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
 * @param object $image
 * @param string $format
 * @return mixed|string
 */
function gibEigenschaftwertbildname($image, $format)
{
    global $cSQL;

    $conf = Shop::getSettings([CONF_BILDER]);

    if (!$image->kEigenschaftWert || !$conf['bilder']['bilder_variation_namen']) {
        return (stripos(strrev($image->cPfad), strrev($format)) === 0)
            ? $image->cPfad
            : $image->cPfad . '.' . $format;
    }
    $attributeValue = Shop::Container()->getDB()->query(
        'SELECT kEigenschaftWert, cArtNr, cName, kEigenschaft
            FROM teigenschaftwert
            WHERE kEigenschaftWert = ' . (int)$image->kEigenschaftWert,
        \DB\ReturnType::SINGLE_OBJECT
    );
    $imageName       = $attributeValue->kEigenschaftWert;
    if ($attributeValue->cName) {
        switch ($conf['bilder']['bilder_variation_namen']) {
            case 1:
                if (!empty($attributeValue->cArtNr)) {
                    $imageName = 'var' . gibAusgeschriebeneUmlaute($attributeValue->cArtNr);
                }
                break;

            case 2:
                $product = Shop::Container()->getDB()->query(
                    "SELECT tartikel.cArtNr, tartikel.cBarcode, tartikel.cName, tseo.cSeo
                        FROM teigenschaftwert, teigenschaft, tartikel
                        LEFT JOIN tseo
                            ON tseo.cKey = 'kArtikel'
                            AND tseo.kKey = tartikel.kArtikel
                            " . $cSQL . '
                        WHERE teigenschaftwert.kEigenschaft = teigenschaft.kEigenschaft
                            AND teigenschaft.kArtikel = tartikel.kArtikel
                            AND teigenschaftwert.kEigenschaftWert = ' . (int)$image->kEigenschaftWert,
                    \DB\ReturnType::SINGLE_OBJECT
                );
                if (!empty($product->cArtNr) && !empty($attributeValue->cArtNr)) {
                    $imageName = gibAusgeschriebeneUmlaute($product->cArtNr) .
                        '_' .
                        gibAusgeschriebeneUmlaute($attributeValue->cArtNr);
                }
                break;

            case 3:
                $product = Shop::Container()->getDB()->query(
                    "SELECT tartikel.cArtNr, tartikel.cBarcode, tartikel.cName, tseo.cSeo
                        FROM teigenschaftwert, teigenschaft, tartikel
                        LEFT JOIN tseo
                            ON tseo.cKey = 'kArtikel'
                            AND tseo.kKey = tartikel.kArtikel
                            " . $cSQL . '
                        WHERE teigenschaftwert.kEigenschaft = teigenschaft.kEigenschaft
                            AND teigenschaft.kArtikel = tartikel.kArtikel
                            AND teigenschaftwert.kEigenschaftWert = ' . $image->kEigenschaftWert,
                    \DB\ReturnType::SINGLE_OBJECT
                );

                $attribute = Shop::Container()->getDB()->query(
                    'SELECT cName FROM teigenschaft WHERE kEigenschaft = ' . $attributeValue->kEigenschaft,
                    \DB\ReturnType::SINGLE_OBJECT
                );
                if ((!empty($product->cSeo) || !empty($product->cName))
                    && !empty($attribute->cName)
                    && !empty($attributeValue->cName)
                ) {
                    if ($product->cSeo) {
                        $imageName = $product->cSeo . '_' .
                            gibAusgeschriebeneUmlaute($attribute->cName) . '_' .
                            gibAusgeschriebeneUmlaute($attributeValue->cName);
                    } else {
                        $imageName = gibAusgeschriebeneUmlaute($product->cName) . '_' .
                            gibAusgeschriebeneUmlaute($attribute->cName) . '_' .
                            gibAusgeschriebeneUmlaute($attributeValue->cName);
                    }
                }
                break;
        }
    }

    return streicheSonderzeichen($imageName) . '.' . $format;
}

/**
 * @param object $image
 * @param string $format
 * @return mixed|string
 */
function gibKategoriebildname($image, $format)
{
    global $cSQL;

    $conf = Shop::getSettings([CONF_BILDER]);

    if (!$image->kKategorie || !$conf['bilder']['bilder_kategorie_namen']) {
        return (stripos(strrev($image->cPfad), strrev($format)) === 0)
            ? $image->cPfad
            : $image->cPfad . '.' . $format;
    }
    $attr = Shop::Container()->getDB()->select(
        'tkategorieattribut',
        'kKategorie',
        (int)$image->kKategorie,
        'cName',
        KAT_ATTRIBUT_BILDNAME,
        null,
        null,
        false,
        'cWert'
    );
    if (!empty($attr->cWert)) {
        return $attr->cWert . '.' . $format;
    }
    $category = Shop::Container()->getDB()->query(
        "SELECT tseo.cSeo, tkategorie.cName
            FROM tkategorie
            LEFT JOIN tseo
                ON tseo.cKey = 'kKategorie'
                AND tseo.kKey = tkategorie.kKategorie
                " . $cSQL . '
            WHERE tkategorie.kKategorie = ' . (int)$image->kKategorie,
        \DB\ReturnType::SINGLE_OBJECT
    );
    $imageName  = $image->cPfad;
    if ($category->cName) {
        switch ($conf['bilder']['bilder_kategorie_namen']) {
            case 1:
                if ($category->cSeo) {
                    $imageName = $category->cSeo;
                } else {
                    $imageName = gibAusgeschriebeneUmlaute($category->cName);
                }
                $imageName = streicheSonderzeichen($imageName) . '.' . $format;
                break;

            default:
                return $image->cPfad . '.' . $format;
                break;
        }
    }

    return $imageName;
}

/**
 * @param object $image
 * @param string $format
 * @return mixed|string
 */
function gibArtikelbildname($image, $format)
{
    global $cSQL;

    $conf = Shop::getSettings([CONF_BILDER]);

    if ($image->kArtikel) {
        $attr = Shop::Container()->getDB()->select(
            'tkategorieattribut',
            'kArtikel',
            (int)$image->kArtikel,
            'cName',
            FKT_ATTRIBUT_BILDNAME,
            null,
            null,
            false,
            'cWert'
        );
        if (isset($attr->cWert)) {
            if ($image->nNr > 1) {
                $attr->cWert .= '_' . $image->nNr;
            }

            return $attr->cWert . '.' . $format;
        }
    }

    if (!$image->kArtikel || !$conf['bilder']['bilder_artikel_namen']) {
        return $image->cPfad . '.' . $format;
    }
    $product  = Shop::Container()->getDB()->query(
        "SELECT tartikel.cArtNr, tseo.cSeo, tartikel.cName, tartikel.cBarcode
            FROM tartikel
            LEFT JOIN tseo
                ON tseo.cKey = 'kArtikel'
                AND tseo.kKey = tartikel.kArtikel
                " . $cSQL . '
            WHERE tartikel.kArtikel = ' . (int)$image->kArtikel,
        \DB\ReturnType::SINGLE_OBJECT
    );
    $imageName = $image->cPfad;
    if ($product->cName) {
        switch ($conf['bilder']['bilder_artikel_namen']) {
            case 1:
                if ($product->cArtNr) {
                    $imageName = gibAusgeschriebeneUmlaute($product->cArtNr);
                }
                break;

            case 2:
                if ($product->cSeo) {
                    $imageName = $product->cSeo;
                } else {
                    $imageName = gibAusgeschriebeneUmlaute($product->cName);
                }
                break;

            case 3:
                if ($product->cArtNr) {
                    $imageName = gibAusgeschriebeneUmlaute($product->cArtNr) . '_';
                }
                if ($product->cSeo) {
                    $imageName .= $product->cSeo;
                } else {
                    $imageName .= gibAusgeschriebeneUmlaute($product->cName);
                }
                break;

            case 4:
                if ($product->cBarcode) {
                    $imageName = gibAusgeschriebeneUmlaute($product->cBarcode);
                }
                break;
            default:
                return $image->cPfad . '.' . $format;
                break;
        }
    } else {
        return $image->cPfad . '.' . $format;
    }

    if ($image->nNr > 1 && $imageName !== $image->cPfad) {
        $imageName .= '_b' . $image->nNr;
    }
    if ($imageName !== $image->cPfad && (int)$conf['bilder']['bilder_artikel_namen'] !== 5) {
        $imageName = streicheSonderzeichen($imageName) . '.' . $format;
    } else {
        $imageName .= '.' . $format;
    }

    return $imageName;
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
 * @param string $targetImage
 * @param int    $targetWidth
 * @param int    $targetheight
 * @param int    $quality
 * @param string $container
 * @return int
 * @throws \Exceptions\CircularReferenceException
 * @throws \Exceptions\ServiceNotFoundException
 */
function erstelleThumbnailBranded(
    $imgFilename,
    $targetImage,
    $targetWidth,
    $targetheight,
    int $quality = 80,
    $container = 'N'
) {
    $conf         = Shop::getSettings([CONF_BILDER]);
    $vergroessern = 0;
    if ($conf['bilder']['bilder_skalieren'] === 'Y') {
        $vergroessern = 1;
    }
    $ret              = 0;
    $format           = $conf['bilder']['bilder_dateiformat'];//gibBildformat($imgFilename);
    [$width, $height] = getimagesize($imgFilename);
    if ($width > 0 && $height > 0) {
        if (!$vergroessern && $width < $targetWidth && $height < $targetheight) {
            if ($container === 'Y') {
                $im = imageload_container($imgFilename, $width, $height, $targetWidth, $targetheight);
            } else {
                $im = imageload_alpha($imgFilename, $width, $height);
            }
            speichereBild($im, $format, PFAD_ROOT . $targetImage, $quality);
            @chmod(PFAD_ROOT . $targetImage, 0644);

            return 1;
        }
        $ratio     = $width / $height;
        $newWidth  = $targetWidth;
        $newHeight = round($newWidth / $ratio);
        if ($newHeight > $targetheight) {
            $newHeight = $targetheight;
            $newWidth  = round($newHeight * $ratio);
        }
        if ($container === 'Y') {
            $im = imageload_container($imgFilename, $newWidth, $newHeight, $targetWidth, $targetheight);
        } else {
            $im = imageload_alpha($imgFilename, $newWidth, $newHeight);
        }
        if (speichereBild($im, $format, PFAD_ROOT . $targetImage, $quality)) {
            $ret = 1;
            @chmod(PFAD_ROOT . $targetImage, 0644);
        } else {
            Shop::Container()->getLogService()->error('Fehler beim Speichern des Bildes: ' . $targetImage);
        }
    } else {
        Shop::Container()->getLogService()->error('Fehler beim Speichern des Bildes: ' . $imgFilename);
    }

    return $ret;
}

/**
 * @param object       $branding
 * @param string       $imgFilename
 * @param string       $zielbild
 * @param int          $targetWidth
 * @param int          $targetHeight
 * @param int          $quality
 * @param int|resource $brand
 * @param string       $container
 * @return int
 * @throws \Exceptions\CircularReferenceException
 * @throws \Exceptions\ServiceNotFoundException
 */
function erstelleThumbnail(
    $branding,
    $imgFilename,
    $zielbild,
    $targetWidth,
    $targetHeight,
    $quality = 80,
    $brand = 0,
    $container = 'N'
) {
    $conf    = Shop::getSettings([CONF_BILDER]);
    $enlarge = 0;
    if ($conf['bilder']['bilder_skalieren'] === 'Y') {
        $enlarge = 1;
    }
    $ret    = 0;
    $format = $conf['bilder']['bilder_dateiformat'];//gibBildformat($imgFilename);
    $im     = imageload_alpha($imgFilename);
    if ($im) {
        // bild skalieren
        [$width, $height] = getimagesize($imgFilename);
        if (!$enlarge && $width < $targetWidth && $height < $targetHeight) {
            //Bild nicht neu berechnen, nur verschieben
            if ($container === 'Y') {
                $im = imageload_container($imgFilename, $width, $height, $targetWidth, $targetHeight);
            } else {
                $im = imageload_alpha($imgFilename, $width, $height);
            }
            speichereBild(brandImage($im, $brand, $branding), $format, PFAD_ROOT . $zielbild, $quality);
            @chmod(PFAD_ROOT . $zielbild, 0644);

            return 1;
        }
        $ratio     = $width / $height;
        $newWidth  = $targetWidth;
        $newHeight = round($newWidth / $ratio);
        if ($newHeight > $targetHeight) {
            $newHeight = $targetHeight;
            $newWidth  = round($newHeight * $ratio);
        }
        if ($container === 'Y') {
            $image_p = imageload_container($imgFilename, $newWidth, $newHeight, $targetWidth, $targetHeight);
        } else {
            $image_p = imageload_alpha($imgFilename, $newWidth, $newHeight);
        }
        if (speichereBild(brandImage($image_p, $brand, $branding), $format, PFAD_ROOT . $zielbild, $quality)) {
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
    // Artikelbilder löschen Wawi > .99923
    if (isset($xml['del_bilder']['tArtikelPict'])) {
        if (count($xml['del_bilder']['tArtikelPict']) > 1) {
            for ($i = 0; $i < (count($xml['del_bilder']['tArtikelPict']) / 2); $i++) {
                $index        = $i . ' attr';
                $productImage = (object)$xml['del_bilder']['tArtikelPict'][$index];
                loescheArtikelPict($productImage->kArtikel, $productImage->nNr);
            }
        } else {
            $productImage = (object)$xml['del_bilder']['tArtikelPict attr'];
            loescheArtikelPict($productImage->kArtikel, $productImage->nNr);
        }
    }
    // Kategoriebilder löschen Wawi <= .99923
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
    // Kategoriebilder löschen Wawi > .99923
    if (isset($xml['del_bilder']['kKategorie'])) {
        foreach ((array)$xml['del_bilder']['kKategorie'] as $kKategorie) {
            if ((int)$kKategorie > 0) {
                loescheKategoriePict(null, $kKategorie);
            }
        }
    }
    // Variationsbilder löschen Wawi <= .99923
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
    // Variationsbilder löschen Wawi > .99923
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
    // Herstellerbilder löschen
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
                    ) as $product) {
                        $cacheTags[] = $product->kArtikel;
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
            ) as $product) {
                $cacheTags[] = $product->kArtikel;
            }
        }
        if (count($cacheTags) > 0) {
            array_walk($cacheTags, function (&$i) {
                $i = CACHING_GROUP_ARTICLE . '_' . $i;
            });
            Shop::Container()->getCache()->flushTags($cacheTags);
        }
    }
    // Merkmalbilder löschen
    if (isset($xml['del_bilder']['kMerkmal'])) {
        if (is_array($xml['del_bilder']['kMerkmal'])) {
            foreach ($xml['del_bilder']['kMerkmal'] as $attrID) {
                if ((int)$attrID > 0) {
                    $db->update(
                        'tmerkmal',
                        'kMerkmal',
                        (int)$attrID,
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
    // Merkmalwertbilder löschen
    if (isset($xml['del_bilder']['kMerkmalWert'])) {
        if (is_array($xml['del_bilder']['kMerkmalWert'])) {
            foreach ($xml['del_bilder']['kMerkmalWert'] as $attrValID) {
                if ((int)$attrValID > 0) {
                    $db->update(
                        'tmerkmalwert',
                        'kMerkmalWert',
                        (int)$attrValID,
                        (object)['cBildpfad' => '']
                    );
                    $db->delete('tmerkmalwertbild', 'kMerkmalWert', (int)$attrValID);
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
 * @param int      $productImageID
 * @param int|null $no
 */
function loescheArtikelPict(int $productImageID, int $no = null)
{
    if ($productImageID <= 0) {
        return;
    }
    $image = null;
    if ($no !== null && $no > 0) {
        $image          = Shop::Container()->getDB()->select('tartikelpict', 'kArtikel', $productImageID, 'nNr', $no);
        $productImageID = $image->kArtikelPict ?? 0;
    }
    deleteArticleImage(null, 0, $productImageID);
}

/**
 * @param int|null $categoryImageID
 * @param int|null $categoryID
 */
function loescheKategoriePict(int $categoryImageID = null, int $categoryID = null)
{
    if ($categoryImageID !== null && $categoryImageID > 0) {
        Shop::Container()->getDB()->delete('tkategoriepict', 'kKategoriePict', $categoryImageID);
    } elseif ($categoryID !== null && $categoryID > 0) {
        Shop::Container()->getDB()->delete('tkategoriepict', 'kKategorie', $categoryID);
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
    // file_exists will return true even if cBrandingBild is not set - check before to avoid warning
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
        $imageWidth        = imagesx($im);
        $imageHeight       = imagesy($im);
        $brandingWidth     = imagesx($branding);
        $brandingHeight    = imagesy($branding);
        $brandingNewWidth  = $brandingWidth;
        $brandingNewHeight = $brandingHeight;
        $image_branding    = $branding;
        // branding auf diese Breite skalieren
        if ($brandingSize > 0) {
            $brandingNewWidth = round(($imageWidth * $brandingSize) / 100.0);
            $brandingNewHeight  = round(($brandingNewWidth / $brandingWidth) * $brandingHeight);

            $image_branding = imageload_alpha($brandingImage, $brandingNewWidth, $brandingNewHeight, true);
        }
        // position bestimmen
        $brandingPosX = 0;
        $brandingPosY = 0;
        switch ($position) {
            case 'oben':
                $brandingPosX = $imageWidth / 2 - $brandingNewWidth / 2;
                $brandingPosY = $imageHeight * $randabstand;
                break;

            case 'oben-rechts':
                $brandingPosX = $imageWidth - $brandingNewWidth - $imageWidth * $randabstand;
                $brandingPosY = $imageHeight * $randabstand;
                break;

            case 'rechts':
                $brandingPosX = $imageWidth - $brandingNewWidth - $imageWidth * $randabstand;
                $brandingPosY = $imageHeight / 2 - $brandingNewHeight / 2;
                break;

            case 'unten-rechts':
                $brandingPosX = $imageWidth - $brandingNewWidth - $imageWidth * $randabstand;
                $brandingPosY = $imageHeight - $brandingNewHeight - $imageHeight * $randabstand;
                break;

            case 'unten':
                $brandingPosX = $imageWidth / 2 - $brandingNewWidth / 2;
                $brandingPosY = $imageHeight - $brandingNewHeight - $imageHeight * $randabstand;
                break;

            case 'unten-links':
                $brandingPosX = $imageWidth * $randabstand;
                $brandingPosY = $imageHeight - $brandingNewHeight - $imageHeight * $randabstand;
                break;

            case 'links':
                $brandingPosX = $imageWidth * $randabstand;
                $brandingPosY = $imageHeight / 2 - $brandingNewHeight / 2;
                break;

            case 'oben-links':
                $brandingPosX = $imageWidth * $randabstand;
                $brandingPosY = $imageHeight * $randabstand;
                break;

            case 'zentriert':
                $brandingPosX = $imageWidth / 2 - $brandingNewWidth / 2;
                $brandingPosY = $imageHeight / 2 - $brandingNewHeight / 2;
                break;
        }
        $brandingPosX = round($brandingPosX);
        $brandingPosY = round($brandingPosY);
        // bild mit branding composen
        imagealphablending($im, true);
        imagesavealpha($im, true);
        imagecopymerge_alpha(
            $im,
            $image_branding,
            $brandingPosX,
            $brandingPosY,
            0,
            0,
            $brandingNewWidth,
            $brandingNewHeight,
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
 * @param int    $width
 * @param int    $height
 * @param int    $containerWidth
 * @param int    $containerHeight
 * @return resource
 */
function imageload_container($img, int $width, int $height, $containerWidth, $containerHeight)
{
    $conf    = Shop::getSettings([CONF_BILDER]);
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

    if ($width === 0 && $height === 0) {
        $height = $imgInfo[1];
        $width  = $imgInfo[0];
    }
    $width  = round($width);
    $height = round($height);
    $newImg  = imagecreatetruecolor($containerWidth, $containerHeight);
    // hintergrundfarbe
    $format = strtolower($conf['bilder']['bilder_dateiformat']);
    if ($format === 'jpg') {
        $rgb   = html2rgb($conf['bilder']['bilder_hintergrundfarbe']);
        $color = imagecolorallocate($newImg, $rgb[0], $rgb[1], $rgb[2]);
        imagealphablending($newImg, true);
    } else {
        $color = imagecolorallocatealpha($newImg, 255, 255, 255, 127);
        imagealphablending($newImg, false);
    }
    imagesavealpha($newImg, true);
    imagefilledrectangle($newImg, 0, 0, $containerWidth, $containerHeight, $color);

    $nPosX = ($containerWidth / 2) - ($width / 2);
    $nPosY = ($containerHeight / 2) - ($height / 2);

    imagecopyresampled($newImg, $im, $nPosX, $nPosY, 0, 0, $width, $height, $imgInfo[0], $imgInfo[1]);

    return $newImg;
}

/**
 * @param string $img
 * @param int    $width
 * @param int    $height
 * @param bool   $branding
 * @return resource
 */
function imageload_alpha($img, int $width = 0, int $height = 0, bool $branding = false)
{
    $conf    = Shop::getSettings([CONF_BILDER]);
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

    if ($width === 0 && $height === 0) {
        $height = $imgInfo[1];
        $width  = $imgInfo[0];
    }

    $width  = round($width);
    $height = round($height);
    $newImg  = imagecreatetruecolor($width, $height);

    if (!$newImg) {
        return $im;
    }

    // hintergrundfarbe
    $format = strtolower($conf['bilder']['bilder_dateiformat']);
    if ($format === 'jpg') {
        $rgb   = html2rgb($conf['bilder']['bilder_hintergrundfarbe']);
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
    imagefilledrectangle($newImg, 0, 0, $width, $height, $color);
    imagecopyresampled($newImg, $im, 0, 0, 0, 0, $width, $height, $imgInfo[0], $imgInfo[1]);

    return $newImg;
}

/**
 * @param string $path
 * @return string
 */
function neuerDateiname(string $path): string
{
    $conf   = Shop::getSettings([CONF_BILDER]);
    $format = strtolower($conf['bilder']['bilder_dateiformat']);
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
