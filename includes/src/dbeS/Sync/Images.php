<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\dbeS\Sync;

use JTL\DB\ReturnType;
use JTL\dbeS\Starter;
use JTL\Shop;
use stdClass;

/**
 * Class Images
 * @package JTL\dbeS\Sync
 */
final class Images extends AbstractSync
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var array
     */
    private $brandingConfig;

    /**
     * @var string
     */
    private $unzipPath;

    /**
     * @param Starter $starter
     * @return mixed|null
     */
    public function handle(Starter $starter)
    {
        $this->brandingConfig = $this->getBrandingConfig();
        $this->config         = $this->getConfig();
        $sql                  = $this->getSeoSQL();
        $this->db->query('START TRANSACTION', ReturnType::DEFAULT);
        foreach ($starter->getXML() as $i => $item) {
            [$file, $xml] = [\key($item), \reset($item)];
            switch (\pathinfo($file)['basename']) {
                case 'bilder_ka.xml':
                case 'bilder_a.xml':
                case 'bilder_k.xml':
                case 'bilder_v.xml':
                case 'bilder_m.xml':
                case 'bilder_mw.xml':
                case 'bilder_h.xml':
                    $this->handleInserts($xml, $starter->getUnzipPath(), $sql);
                    break;

                case 'del_bilder_ka.xml':
                case 'del_bilder_a.xml':
                case 'del_bilder_k.xml':
                case 'del_bilder_v.xml':
                case 'del_bilder_m.xml':
                case 'del_bilder_mw.xml':
                case 'del_bilder_h.xml':
                    $this->handleDeletes($xml);
                    break;
            }
        }
        $this->db->query('COMMIT', ReturnType::DEFAULT);

        return null;
    }

    /**
     * @return string
     */
    private function getSeoSQL(): string
    {
        $lang   = $this->db->select('tsprache', 'cShopStandard', 'Y');
        $langID = (int)($lang->kSprache ?? 0);
        $sql    = '';
        if (!$langID) {
            $langID = $_SESSION['kSprache'] ?? 0;
        }
        if ($langID > 0) {
            $sql = ' AND tseo.kSprache = ' . $lang->kSprache;
        }

        return $sql;
    }

    /**
     * @return array
     */
    private function getBrandingConfig(): array
    {
        $branding = [];
        $data     = $this->db->query(
            'SELECT * FROM tbranding',
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($data as $item) {
            $branding[$item->cBildKategorie] = $item;
        }
        foreach ($branding as $config) {
            $config->oBrandingEinstellung = $this->db->select(
                'tbrandingeinstellung',
                'kBranding',
                (int)$config->kBranding
            );
        }

        return $branding;
    }

    /**
     * @return array
     */
    private function getConfig(): array
    {
        $config = Shop::getSettings([\CONF_BILDER]);

        if (!$config['bilder']['bilder_kategorien_breite']) {
            $config['bilder']['bilder_kategorien_breite'] = 100;
        }
        if (!$config['bilder']['bilder_kategorien_hoehe']) {
            $config['bilder']['bilder_kategorien_hoehe'] = 100;
        }
        if (!$config['bilder']['bilder_variationen_gross_breite']) {
            $config['bilder']['bilder_variationen_gross_breite'] = 800;
        }
        if (!$config['bilder']['bilder_variationen_gross_hoehe']) {
            $config['bilder']['bilder_variationen_gross_hoehe'] = 800;
        }
        if (!$config['bilder']['bilder_variationen_breite']) {
            $config['bilder']['bilder_variationen_breite'] = 210;
        }
        if (!$config['bilder']['bilder_variationen_hoehe']) {
            $config['bilder']['bilder_variationen_hoehe'] = 210;
        }
        if (!$config['bilder']['bilder_variationen_mini_breite']) {
            $config['bilder']['bilder_variationen_mini_breite'] = 30;
        }
        if (!$config['bilder']['bilder_variationen_mini_hoehe']) {
            $config['bilder']['bilder_variationen_mini_hoehe'] = 30;
        }
        if (!$config['bilder']['bilder_artikel_gross_breite']) {
            $config['bilder']['bilder_artikel_gross_breite'] = 800;
        }
        if (!$config['bilder']['bilder_artikel_gross_hoehe']) {
            $config['bilder']['bilder_artikel_gross_hoehe'] = 800;
        }
        if (!$config['bilder']['bilder_artikel_normal_breite']) {
            $config['bilder']['bilder_artikel_normal_breite'] = 210;
        }
        if (!$config['bilder']['bilder_artikel_normal_hoehe']) {
            $config['bilder']['bilder_artikel_normal_hoehe'] = 210;
        }
        if (!$config['bilder']['bilder_artikel_klein_breite']) {
            $config['bilder']['bilder_artikel_klein_breite'] = 80;
        }
        if (!$config['bilder']['bilder_artikel_klein_hoehe']) {
            $config['bilder']['bilder_artikel_klein_hoehe'] = 80;
        }
        if (!$config['bilder']['bilder_artikel_mini_breite']) {
            $config['bilder']['bilder_artikel_mini_breite'] = 30;
        }
        if (!$config['bilder']['bilder_artikel_mini_hoehe']) {
            $config['bilder']['bilder_artikel_mini_hoehe'] = 30;
        }
        if (!$config['bilder']['bilder_hersteller_normal_breite']) {
            $config['bilder']['bilder_hersteller_normal_breite'] = 100;
        }
        if (!$config['bilder']['bilder_hersteller_normal_hoehe']) {
            $config['bilder']['bilder_hersteller_normal_hoehe'] = 100;
        }
        if (!$config['bilder']['bilder_hersteller_klein_breite']) {
            $config['bilder']['bilder_hersteller_klein_breite'] = 40;
        }
        if (!$config['bilder']['bilder_hersteller_klein_hoehe']) {
            $config['bilder']['bilder_hersteller_klein_hoehe'] = 40;
        }
        if (!$config['bilder']['bilder_merkmal_normal_breite']) {
            $config['bilder']['bilder_merkmal_normal_breite'] = 100;
        }
        if (!$config['bilder']['bilder_merkmal_normal_hoehe']) {
            $config['bilder']['bilder_merkmal_normal_hoehe'] = 100;
        }
        if (!$config['bilder']['bilder_merkmal_klein_breite']) {
            $config['bilder']['bilder_merkmal_klein_breite'] = 20;
        }
        if (!$config['bilder']['bilder_merkmal_klein_hoehe']) {
            $config['bilder']['bilder_merkmal_klein_hoehe'] = 20;
        }
        if (!$config['bilder']['bilder_merkmalwert_normal_breite']) {
            $config['bilder']['bilder_merkmalwert_normal_breite'] = 100;
        }
        if (!$config['bilder']['bilder_merkmalwert_normal_hoehe']) {
            $config['bilder']['bilder_merkmalwert_normal_hoehe'] = 100;
        }
        if (!$config['bilder']['bilder_merkmalwert_klein_breite']) {
            $config['bilder']['bilder_merkmalwert_klein_breite'] = 20;
        }
        if (!$config['bilder']['bilder_merkmalwert_klein_hoehe']) {
            $config['bilder']['bilder_merkmalwert_klein_hoehe'] = 20;
        }
        if (!$config['bilder']['bilder_konfiggruppe_klein_breite']) {
            $config['bilder']['bilder_konfiggruppe_klein_breite'] = 130;
        }
        if (!$config['bilder']['bilder_konfiggruppe_klein_hoehe']) {
            $config['bilder']['bilder_konfiggruppe_klein_hoehe'] = 130;
        }
        if (!$config['bilder']['bilder_jpg_quali']) {
            $config['bilder']['bilder_jpg_quali'] = 80;
        }
        if (!$config['bilder']['bilder_dateiformat']) {
            $config['bilder']['bilder_dateiformat'] = 'PNG';
        }
        if (!$config['bilder']['bilder_hintergrundfarbe']) {
            $config['bilder']['bilder_hintergrundfarbe'] = '#ffffff';
        }
        if (!$config['bilder']['bilder_skalieren']) {
            $config['bilder']['bilder_skalieren'] = 'N';
        }

        return $config;
    }

    /**
     * @param array  $xml
     * @param string $unzipPath
     * @param string $sql
     */
    private function handleInserts($xml, string $unzipPath, string $sql): void
    {
        $productImages      = $this->mapper->mapArray($xml['bilder'], 'tartikelpict', 'mArtikelPict');
        $categoryImages     = $this->mapper->mapArray($xml['bilder'], 'tkategoriepict', 'mKategoriePict');
        $propertyImages     = $this->mapper->mapArray($xml['bilder'], 'teigenschaftwertpict', 'mEigenschaftWertPict');
        $manufacturerImages = $this->mapper->mapArray($xml['bilder'], 'therstellerbild', 'mEigenschaftWertPict');
        $attributeImages    = $this->mapper->mapArray($xml['bilder'], 'tMerkmalbild', 'mEigenschaftWertPict');
        $attrValImages      = $this->mapper->mapArray($xml['bilder'], 'tmerkmalwertbild', 'mEigenschaftWertPict');
        $configImages       = $this->mapper->mapArray($xml['bilder'], 'tkonfiggruppebild', 'mKonfiggruppePict');

        \executeHook(\HOOK_BILDER_XML_BEARBEITE, [
            'Pfad'             => $unzipPath,
            'Artikel'          => &$productImages,
            'Kategorie'        => &$categoryImages,
            'Eigenschaftswert' => &$propertyImages,
            'Hersteller'       => &$manufacturerImages,
            'Merkmalwert'      => &$attrValImages,
            'Merkmal'          => &$attributeImages,
            'Konfiggruppe'     => &$configImages
        ]);
        $this->unzipPath = $unzipPath;

        $this->handleProductImages($productImages, $sql);
        $this->handleCategoryImages($categoryImages, $sql);
        $this->handlePropertyImages($propertyImages, $sql);
        $this->handleManufacturerImages($manufacturerImages);
        $this->handleAttributeImages($attributeImages);
        $this->handleAttributeValueImages($attrValImages);
        $this->handleConfigImages($configImages);

        \executeHook(\HOOK_BILDER_XML_BEARBEITE_ENDE, [
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
     * @param array $configImages
     */
    private function handleConfigImages(array $configImages): void
    {
        foreach ($configImages as $configImage) {
            $item                = new stdClass();
            $item->cBildPfad     = $configImage->cPfad;
            $item->kKonfiggruppe = $configImage->kKonfiggruppe;
            if (empty($item->cBildPfad)) {
                continue;
            }
            $imgFilename = $item->cBildPfad;
            $format      = $this->getExtension($this->unzipPath . $imgFilename);
            if (!$format) {
                $this->logger->error(
                    'Bildformat des Konfiggruppenbildes konnte nicht ermittelt werden. Datei ' .
                    $imgFilename . ' keine Bilddatei?'
                );
                continue;
            }
            $item->cBildPfad = $item->kKonfiggruppe . '.' . $format;
            $item->cBildPfad = $this->getNewFilename($item->cBildPfad);

            $branding                               = new stdClass();
            $branding->oBrandingEinstellung         = new stdClass();
            $branding->oBrandingEinstellung->nAktiv = 0;

            if ($this->createThumbnail(
                $branding,
                $this->unzipPath . $imgFilename,
                \PFAD_KONFIGURATOR_KLEIN . $item->cBildPfad,
                $this->config['bilder']['bilder_konfiggruppe_klein_breite'],
                $this->config['bilder']['bilder_konfiggruppe_klein_hoehe'],
                $this->config['bilder']['bilder_jpg_quali'],
                1,
                $this->config['bilder']['container_verwenden']
            )) {
                $this->db->update(
                    'tkonfiggruppe',
                    'kKonfiggruppe',
                    (int)$item->kKonfiggruppe,
                    (object)['cBildPfad' => $item->cBildPfad]
                );
            }
            \unlink($this->unzipPath . $imgFilename);
        }
    }

    /**
     * @param array $attrValImages
     */
    private function handleAttributeValueImages(array $attrValImages): void
    {
        foreach ($attrValImages as $attrValImage) {
            $attrValImage->kMerkmalWert = (int)$attrValImage->kMerkmalWert;
            if (empty($attrValImage->cPfad) || $attrValImage->kMerkmalWert <= 0) {
                continue;
            }
            $imgFilename = $attrValImage->cPfad;
            $format      = $this->getExtension($this->unzipPath . $imgFilename);
            if (!$format) {
                $this->logger->error(
                    'Bildformat des Merkmalwertbildes konnte nicht ermittelt werden. Datei ' .
                    $imgFilename . ' keine Bilddatei?'
                );
                continue;
            }
            $attrValImage->cPfad .= '.' . $format;
            $attrValImage->cPfad  = $this->getNewFilename($attrValImage->cPfad);
            $this->createThumbnail(
                $this->brandingConfig['Merkmalwerte'],
                $this->unzipPath . $imgFilename,
                \PFAD_MERKMALWERTBILDER_NORMAL . $attrValImage->cPfad,
                $this->config['bilder']['bilder_merkmalwert_normal_breite'],
                $this->config['bilder']['bilder_merkmalwert_normal_hoehe'],
                $this->config['bilder']['bilder_jpg_quali'],
                1,
                $this->config['bilder']['container_verwenden']
            );
            if ($this->createBrandedThumbnail(
                \PFAD_ROOT . \PFAD_MERKMALWERTBILDER_NORMAL . $attrValImage->cPfad,
                \PFAD_MERKMALWERTBILDER_KLEIN . $attrValImage->cPfad,
                $this->config['bilder']['bilder_merkmalwert_klein_breite'],
                $this->config['bilder']['bilder_merkmalwert_klein_hoehe'],
                $this->config['bilder']['bilder_jpg_quali'],
                $this->config['bilder']['container_verwenden']
            )) {
                $this->db->update(
                    'tmerkmalwert',
                    'kMerkmalWert',
                    (int)$attrValImage->kMerkmalWert,
                    (object)['cBildpfad' => $attrValImage->cPfad]
                );
                $oMerkmalwertbild               = new stdClass();
                $oMerkmalwertbild->kMerkmalWert = (int)$attrValImage->kMerkmalWert;
                $oMerkmalwertbild->cBildpfad    = $attrValImage->cPfad;

                $this->upsert('tmerkmalwertbild', [$oMerkmalwertbild], 'kMerkmalWert');
            }
            \unlink($this->unzipPath . $imgFilename);
        }
    }

    /**
     * @param array $attributeImages
     */
    private function handleAttributeImages(array $attributeImages): void
    {
        foreach ($attributeImages as $attributeImage) {
            $attributeImage->kMerkmal = (int)$attributeImage->kMerkmal;
            if (empty($attributeImage->cPfad) || $attributeImage->kMerkmal <= 0) {
                continue;
            }
            $imgFilename = $attributeImage->cPfad;
            $format      = $this->getExtension($this->unzipPath . $imgFilename);
            if (!$format) {
                $this->logger->error(
                    'Bildformat des Merkmalbildes konnte nicht ermittelt werden. Datei ' .
                    $imgFilename . ' keine Bilddatei?'
                );
                continue;
            }
            $attributeImage->cPfad .= '.' . $format;
            $attributeImage->cPfad  = $this->getNewFilename($attributeImage->cPfad);
            $this->createThumbnail(
                $this->brandingConfig['Merkmale'],
                $this->unzipPath . $imgFilename,
                \PFAD_MERKMALBILDER_NORMAL . $attributeImage->cPfad,
                $this->config['bilder']['bilder_merkmal_normal_breite'],
                $this->config['bilder']['bilder_merkmal_normal_hoehe'],
                $this->config['bilder']['bilder_jpg_quali'],
                1,
                $this->config['bilder']['container_verwenden']
            );
            if ($this->createBrandedThumbnail(
                \PFAD_ROOT . \PFAD_MERKMALBILDER_NORMAL . $attributeImage->cPfad,
                \PFAD_MERKMALBILDER_KLEIN . $attributeImage->cPfad,
                $this->config['bilder']['bilder_merkmal_klein_breite'],
                $this->config['bilder']['bilder_merkmal_klein_hoehe'],
                $this->config['bilder']['bilder_jpg_quali'],
                $this->config['bilder']['container_verwenden']
            )) {
                $this->db->update(
                    'tmerkmal',
                    'kMerkmal',
                    (int)$attributeImage->kMerkmal,
                    (object)['cBildpfad' => $attributeImage->cPfad]
                );
            }
            \unlink($this->unzipPath . $imgFilename);
        }
    }
    /**
     * @param array $manufacturerImages
     */
    private function handleManufacturerImages(array $manufacturerImages): void
    {
        foreach ($manufacturerImages as $manufacturerImage) {
            $manufacturerImage->kHersteller = (int)$manufacturerImage->kHersteller;
            if (empty($manufacturerImage->cPfad) || $manufacturerImage->kHersteller <= 0) {
                continue;
            }
            $imgFilename = $manufacturerImage->cPfad;
            $format      = $this->getExtension($this->unzipPath . $imgFilename);
            if (!$format) {
                $this->logger->error(
                    'Bildformat des Herstellerbildes konnte nicht ermittelt werden. Datei ' .
                    $imgFilename . ' keine Bilddatei?'
                );
                continue;
            }
            $manufacturer = $this->db->query(
                'SELECT cSeo
                FROM thersteller
                WHERE kHersteller = ' . (int)$manufacturerImage->kHersteller,
                ReturnType::SINGLE_OBJECT
            );
            if (!empty($manufacturer->cSeo)) {
                $manufacturerImage->cPfad = \str_replace('/', '_', $manufacturer->cSeo . '.' . $format);
            } elseif (\stripos(\strrev($manufacturerImage->cPfad), \strrev($format)) !== 0) {
                $manufacturerImage->cPfad .= '.' . $format;
            }
            $manufacturerImage->cPfad = $this->getNewFilename($manufacturerImage->cPfad);
            $this->createThumbnail(
                $this->brandingConfig['Hersteller'],
                $this->unzipPath . $imgFilename,
                \PFAD_HERSTELLERBILDER_NORMAL . $manufacturerImage->cPfad,
                $this->config['bilder']['bilder_hersteller_normal_breite'],
                $this->config['bilder']['bilder_hersteller_normal_hoehe'],
                $this->config['bilder']['bilder_jpg_quali'],
                1,
                $this->config['bilder']['container_verwenden']
            );
            if ($this->createBrandedThumbnail(
                \PFAD_ROOT . \PFAD_HERSTELLERBILDER_NORMAL . $manufacturerImage->cPfad,
                \PFAD_HERSTELLERBILDER_KLEIN . $manufacturerImage->cPfad,
                $this->config['bilder']['bilder_hersteller_klein_breite'],
                $this->config['bilder']['bilder_hersteller_klein_hoehe'],
                $this->config['bilder']['bilder_jpg_quali'],
                $this->config['bilder']['container_verwenden']
            )) {
                $this->db->update(
                    'thersteller',
                    'kHersteller',
                    (int)$manufacturerImage->kHersteller,
                    (object)['cBildpfad' => $manufacturerImage->cPfad]
                );
            }
            $cacheTags = [];
            foreach ($this->db->selectAll(
                'tartikel',
                'kHersteller',
                (int)$manufacturerImage->kHersteller,
                'kArtikel'
            ) as $product) {
                $cacheTags[] = \CACHING_GROUP_ARTICLE . '_' . $product->kArtikel;
            }
            $this->cache->flushTags($cacheTags);
            \unlink($this->unzipPath . $imgFilename);
        }
    }

    /**
     * @param array  $propertyImages
     * @param string $sql
     */
    private function handlePropertyImages(array $propertyImages, string $sql): void
    {
        foreach ($propertyImages as $propertyImage) {
            if (empty($propertyImage->cPfad)) {
                continue;
            }
            $imgFilename = $propertyImage->cPfad;
            $format      = $this->getExtension($this->unzipPath . $imgFilename);
            if (!$format) {
                $this->logger->error(
                    'Bildformat des Eigenschaftwertbildes konnte nicht ermittelt werden. Datei ' .
                    $imgFilename . ' keine Bilddatei?'
                );
                continue;
            }
            $propertyImage->cPfad = $this->getAttributeImageName($propertyImage, $format, $sql);
            $propertyImage->cPfad = $this->getNewFilename($propertyImage->cPfad);
            $this->createThumbnail(
                $this->brandingConfig['Variationen'],
                $this->unzipPath . $imgFilename,
                \PFAD_VARIATIONSBILDER_GROSS . $propertyImage->cPfad,
                $this->config['bilder']['bilder_variationen_gross_breite'],
                $this->config['bilder']['bilder_variationen_gross_hoehe'],
                $this->config['bilder']['bilder_jpg_quali'],
                1,
                $this->config['bilder']['container_verwenden']
            );
            $this->createBrandedThumbnail(
                \PFAD_ROOT . \PFAD_VARIATIONSBILDER_GROSS . $propertyImage->cPfad,
                \PFAD_VARIATIONSBILDER_NORMAL . $propertyImage->cPfad,
                $this->config['bilder']['bilder_variationen_breite'],
                $this->config['bilder']['bilder_variationen_hoehe'],
                $this->config['bilder']['bilder_jpg_quali'],
                $this->config['bilder']['container_verwenden']
            );
            if ($this->createBrandedThumbnail(
                \PFAD_ROOT . \PFAD_VARIATIONSBILDER_GROSS . $propertyImage->cPfad,
                \PFAD_VARIATIONSBILDER_MINI . $propertyImage->cPfad,
                $this->config['bilder']['bilder_variationen_mini_breite'],
                $this->config['bilder']['bilder_variationen_mini_hoehe'],
                $this->config['bilder']['bilder_jpg_quali'],
                $this->config['bilder']['container_verwenden']
            )) {
                $this->upsert('teigenschaftwertpict', [$propertyImage], 'kEigenschaftWert');
            }
            \unlink($this->unzipPath . $imgFilename);
        }
    }

    /**
     * @param array  $categoryImages
     * @param string $sql
     */
    private function handleCategoryImages(array $categoryImages, string $sql): void
    {
        foreach ($categoryImages as $categoryImage) {
            if (empty($categoryImage->cPfad)) {
                continue;
            }
            $imgFilename = $categoryImage->cPfad;
            $format      = $this->getExtension($this->unzipPath . $imgFilename);
            if (!$format) {
                $this->logger->error(
                    'Bildformat des Kategoriebildes konnte nicht ermittelt werden. Datei ' .
                    $imgFilename . ' keine Bilddatei?'
                );
                continue;
            }

            $categoryImage->cPfad = $this->getCategoryImageName($categoryImage, $format, $sql);
            $categoryImage->cPfad = $this->getNewFilename($categoryImage->cPfad);
            if ($this->createThumbnail(
                $this->brandingConfig['Kategorie'],
                $this->unzipPath . $imgFilename,
                \PFAD_KATEGORIEBILDER . $categoryImage->cPfad,
                $this->config['bilder']['bilder_kategorien_breite'],
                $this->config['bilder']['bilder_kategorien_hoehe'],
                $this->config['bilder']['bilder_jpg_quali'],
                1,
                $this->config['bilder']['container_verwenden']
            )) {
                $this->upsert('tkategoriepict', [$categoryImage], 'kKategorie');
            }
            \unlink($this->unzipPath . $imgFilename);
        }
    }

    /**
     * @param array  $productImages
     * @param string $sql
     */
    private function handleProductImages(array $productImages, string $sql): void
    {
        foreach ($productImages as $image) {
            if (\strlen($image->cPfad) <= 0) {
                continue;
            }
            $image->nNr  = (int)$image->nNr;
            $imgFilename = $image->cPfad;
            $format      = $this->getExtension($this->unzipPath . $imgFilename);
            if (!$format) {
                $this->logger->error(
                    'Bildformat des Artikelbildes konnte nicht ermittelt werden. Datei ' .
                    $imgFilename . ' keine Bilddatei?'
                );
                continue;
            }
            // first delete by kArtikelPict
            $this->deleteArtikelPict($image->kArtikelPict, 0);
            // then delete by kArtikel + nNr since Wawi > .99923 has changed all kArtikelPict keys
            if (isset($image->nNr) && $image->nNr > 0) {
                $this->deleteArtikelPict($image->kArtikel, $image->nNr);
            }
            if ($image->kMainArtikelBild > 0) {
                $main = $this->db->select(
                    'tartikelpict',
                    'kArtikelPict',
                    (int)$image->kMainArtikelBild
                );
                if (!empty($main->cPfad)) {
                    $image->cPfad = $this->getNewFilename($main->cPfad);
                    $this->upsert('tartikelpict', [$image], 'kArtikel', 'kArtikelpict');
                } else {
                    $this->createProductImage($image, $format, $this->unzipPath, $imgFilename, $sql);
                }
            } else {
                $productImage = $this->db->select(
                    'tartikelpict',
                    'kArtikelPict',
                    (int)$image->kArtikelPict
                );
                // update all references, if img is used by other products
                if (!empty($productImage->cPfad)) {
                    $this->db->update(
                        'tartikelpict',
                        'kMainArtikelBild',
                        (int)$productImage->kArtikelPict,
                        (object)['cPfad' => $productImage->cPfad]
                    );
                }
                $this->createProductImage($image, $format, $this->unzipPath, $imgFilename, $sql);
            }
        }
        if (\count($productImages) > 0) {
            $handle = \opendir($this->unzipPath);
            while (($file = \readdir($handle)) !== false) {
                if ($file === '.' || $file === '..' || $file === 'bilder_a.xml') {
                    continue;
                }
                if (\file_exists($this->unzipPath . $file) && !\unlink($this->unzipPath . $file)) {
                    $this->logger->error('Artikelbild konnte nicht geloescht werden: ' . $file);
                }
            }
            \closedir($handle);
        }
    }

    /**
     * @param stdClass $img
     * @param string    $format
     * @param string    $unzipPath
     * @param string    $imgFilename
     * @param string    $sql
     */
    private function createProductImage($img, $format, $unzipPath, $imgFilename, $sql): void
    {
        $img->cPfad = $this->getProductImageName(
            $img,
            $this->config['bilder']['container_verwenden'] === 'Y' ? 'png' : $format,
            $sql
        );
        $img->cPfad = $this->getNewFilename($img->cPfad);
        $this->createThumbnail(
            $this->brandingConfig['Artikel'],
            $unzipPath . $imgFilename,
            \PFAD_PRODUKTBILDER_GROSS . $img->cPfad,
            $this->config['bilder']['bilder_artikel_gross_breite'],
            $this->config['bilder']['bilder_artikel_gross_hoehe'],
            $this->config['bilder']['bilder_jpg_quali'],
            1,
            $this->config['bilder']['container_verwenden']
        );
        $this->createBrandedThumbnail(
            \PFAD_ROOT . \PFAD_PRODUKTBILDER_GROSS . $img->cPfad,
            \PFAD_PRODUKTBILDER_NORMAL . $img->cPfad,
            $this->config['bilder']['bilder_artikel_normal_breite'],
            $this->config['bilder']['bilder_artikel_normal_hoehe'],
            $this->config['bilder']['bilder_jpg_quali'],
            $this->config['bilder']['container_verwenden']
        );
        $this->createBrandedThumbnail(
            \PFAD_ROOT . \PFAD_PRODUKTBILDER_GROSS . $img->cPfad,
            \PFAD_PRODUKTBILDER_KLEIN . $img->cPfad,
            $this->config['bilder']['bilder_artikel_klein_breite'],
            $this->config['bilder']['bilder_artikel_klein_hoehe'],
            $this->config['bilder']['bilder_jpg_quali'],
            $this->config['bilder']['container_verwenden']
        );
        if ($this->createBrandedThumbnail(
            $unzipPath . $imgFilename,
            \PFAD_PRODUKTBILDER_MINI . $img->cPfad,
            $this->config['bilder']['bilder_artikel_mini_breite'],
            $this->config['bilder']['bilder_artikel_mini_hoehe'],
            $this->config['bilder']['bilder_jpg_quali'],
            $this->config['bilder']['container_verwenden']
        )) {
            $this->upsert('tartikelpict', [$img], 'kArtikel', 'kArtikelPict');
        }
    }

    /**
     * @param object $image
     * @param string $format
     * @param string $sql
     * @return string
     */
    private function getAttributeImageName($image, $format, $sql): string
    {
        if (!$image->kEigenschaftWert || !$this->config['bilder']['bilder_variation_namen']) {
            return (\stripos(\strrev($image->cPfad), \strrev($format)) === 0)
                ? $image->cPfad
                : $image->cPfad . '.' . $format;
        }
        $attributeValue = $this->db->query(
            'SELECT kEigenschaftWert, cArtNr, cName, kEigenschaft
                FROM teigenschaftwert
                WHERE kEigenschaftWert = ' . (int)$image->kEigenschaftWert,
            ReturnType::SINGLE_OBJECT
        );
        if ($attributeValue === false) {
            $this->logger->warning(
                'Eigenschaftswertbild fuer nicht existierenden Eigenschaftswert ' . (int)$image->kEigenschaftWert
            );
            return $image->cPfad;
        }
        $imageName = $attributeValue->kEigenschaftWert;
        if ($attributeValue->cName) {
            switch ($this->config['bilder']['bilder_variation_namen']) {
                case 1:
                    if (!empty($attributeValue->cArtNr)) {
                        $imageName = 'var' . $this->convertUmlauts($attributeValue->cArtNr);
                    }
                    break;

                case 2:
                    $product = $this->db->query(
                        "SELECT tartikel.cArtNr, tartikel.cBarcode, tartikel.cName, tseo.cSeo
                            FROM teigenschaftwert, teigenschaft, tartikel
                            LEFT JOIN tseo
                                ON tseo.cKey = 'kArtikel'
                                AND tseo.kKey = tartikel.kArtikel
                                " . $sql . '
                            WHERE teigenschaftwert.kEigenschaft = teigenschaft.kEigenschaft
                                AND teigenschaft.kArtikel = tartikel.kArtikel
                                AND teigenschaftwert.kEigenschaftWert = ' . (int)$image->kEigenschaftWert,
                        ReturnType::SINGLE_OBJECT
                    );
                    if (!empty($product->cArtNr) && !empty($attributeValue->cArtNr)) {
                        $imageName = $this->convertUmlauts($product->cArtNr) .
                            '_' .
                            $this->convertUmlauts($attributeValue->cArtNr);
                    }
                    break;

                case 3:
                    $product = $this->db->query(
                        "SELECT tartikel.cArtNr, tartikel.cBarcode, tartikel.cName, tseo.cSeo
                            FROM teigenschaftwert, teigenschaft, tartikel
                            LEFT JOIN tseo
                                ON tseo.cKey = 'kArtikel'
                                AND tseo.kKey = tartikel.kArtikel
                                " . $sql . '
                            WHERE teigenschaftwert.kEigenschaft = teigenschaft.kEigenschaft
                                AND teigenschaft.kArtikel = tartikel.kArtikel
                                AND teigenschaftwert.kEigenschaftWert = ' . $image->kEigenschaftWert,
                        ReturnType::SINGLE_OBJECT
                    );

                    $attribute = $this->db->query(
                        'SELECT cName FROM teigenschaft WHERE kEigenschaft = ' . $attributeValue->kEigenschaft,
                        ReturnType::SINGLE_OBJECT
                    );
                    if ((!empty($product->cSeo) || !empty($product->cName))
                        && !empty($attribute->cName)
                        && !empty($attributeValue->cName)
                    ) {
                        if ($product->cSeo) {
                            $imageName = $product->cSeo . '_' .
                                $this->convertUmlauts($attribute->cName) . '_' .
                                $this->convertUmlauts($attributeValue->cName);
                        } else {
                            $imageName = $this->convertUmlauts($product->cName) . '_' .
                                $this->convertUmlauts($attribute->cName) . '_' .
                                $this->convertUmlauts($attributeValue->cName);
                        }
                    }
                    break;
            }
        }

        return $this->removeSpecialChars($imageName) . '.' . $format;
    }

    /**
     * @param object $image
     * @param string $format
     * @param string $sql
     * @return string
     */
    private function getCategoryImageName($image, $format, $sql): string
    {
        if (!$image->kKategorie || !$this->config['bilder']['bilder_kategorie_namen']) {
            return (\stripos(\strrev($image->cPfad), \strrev($format)) === 0)
                ? $image->cPfad
                : $image->cPfad . '.' . $format;
        }
        $attr = $this->db->select(
            'tkategorieattribut',
            'kKategorie',
            (int)$image->kKategorie,
            'cName',
            \KAT_ATTRIBUT_BILDNAME,
            null,
            null,
            false,
            'cWert'
        );
        if (!empty($attr->cWert)) {
            return $attr->cWert . '.' . $format;
        }
        $category  = $this->db->query(
            "SELECT tseo.cSeo, tkategorie.cName
            FROM tkategorie
            LEFT JOIN tseo
                ON tseo.cKey = 'kKategorie'
                AND tseo.kKey = tkategorie.kKategorie
                " . $sql . '
            WHERE tkategorie.kKategorie = ' . (int)$image->kKategorie,
            ReturnType::SINGLE_OBJECT
        );
        $imageName = $image->cPfad;
        if ($category->cName) {
            switch ($this->config['bilder']['bilder_kategorie_namen']) {
                case 1:
                    if ($category->cSeo) {
                        $imageName = $category->cSeo;
                    } else {
                        $imageName = $this->convertUmlauts($category->cName);
                    }
                    $imageName = $this->removeSpecialChars($imageName) . '.' . $format;
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
     * @param string $sql
     * @return string
     */
    private function getProductImageName($image, $format, $sql): string
    {
        if ($image->kArtikel) {
            $attr = $this->db->select(
                'tkategorieattribut',
                'kArtikel',
                (int)$image->kArtikel,
                'cName',
                \FKT_ATTRIBUT_BILDNAME,
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

        if (!$image->kArtikel || !$this->config['bilder']['bilder_artikel_namen']) {
            return $image->cPfad . '.' . $format;
        }
        $product   = $this->db->query(
            "SELECT tartikel.cArtNr, tseo.cSeo, tartikel.cName, tartikel.cBarcode
            FROM tartikel
            LEFT JOIN tseo
                ON tseo.cKey = 'kArtikel'
                AND tseo.kKey = tartikel.kArtikel
                " . $sql . '
            WHERE tartikel.kArtikel = ' . (int)$image->kArtikel,
            ReturnType::SINGLE_OBJECT
        );
        $imageName = $image->cPfad;
        if ($product->cName) {
            switch ($this->config['bilder']['bilder_artikel_namen']) {
                case 1:
                    if ($product->cArtNr) {
                        $imageName = $this->convertUmlauts($product->cArtNr);
                    }
                    break;

                case 2:
                    if ($product->cSeo) {
                        $imageName = $product->cSeo;
                    } else {
                        $imageName = $this->convertUmlauts($product->cName);
                    }
                    break;

                case 3:
                    if ($product->cArtNr) {
                        $imageName = $this->convertUmlauts($product->cArtNr) . '_';
                    }
                    if ($product->cSeo) {
                        $imageName .= $product->cSeo;
                    } else {
                        $imageName .= $this->convertUmlauts($product->cName);
                    }
                    break;

                case 4:
                    if ($product->cBarcode) {
                        $imageName = $this->convertUmlauts($product->cBarcode);
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
        if ($imageName !== $image->cPfad && (int)$this->config['bilder']['bilder_artikel_namen'] !== 5) {
            $imageName = $this->removeSpecialChars($imageName) . '.' . $format;
        } else {
            $imageName .= '.' . $format;
        }

        return $imageName;
    }

    /**
     * @param string $str
     * @return mixed
     */
    private function convertUmlauts(string $str): string
    {
        $src = ['ä', 'ö', 'ü', 'ß', 'Ä', 'Ö', 'Ü'];
        $rpl = ['ae', 'oe', 'ue', 'ss', 'AE', 'OE', 'UE'];

        return \str_replace($src, $rpl, $str);
    }

    /**
     * @param string $str
     * @return string
     */
    private function removeSpecialChars(string $str): string
    {
        $str = \str_replace(['/', ' '], '-', $str);

        return \preg_replace('/[^a-zA-Z0-9\.\-_]/', '', $str);
    }

    /**
     * @param string $imgFilename
     * @param string $targetImage
     * @param int    $targetWidth
     * @param int    $targetheight
     * @param int    $quality
     * @param string $container
     * @return int
     */
    private function createBrandedThumbnail(
        $imgFilename,
        $targetImage,
        $targetWidth,
        $targetheight,
        int $quality = 80,
        $container = 'N'
    ): int {
        $enlarge          = $this->config['bilder']['bilder_skalieren'] === 'Y';
        $ret              = 0;
        $format           = $this->config['bilder']['bilder_dateiformat'];//$this->getExtension($imgFilename);
        [$width, $height] = \getimagesize($imgFilename);
        if ($width > 0 && $height > 0) {
            if (!$enlarge && $width < $targetWidth && $height < $targetheight) {
                if ($container === 'Y') {
                    $im = $this->imageloadContainer($imgFilename, $width, $height, $targetWidth, $targetheight);
                } else {
                    $im = $this->imageloadAlpha($imgFilename, $width, $height);
                }
                $this->saveImage($im, $format, \PFAD_ROOT . $targetImage, $quality);
                @\chmod(\PFAD_ROOT . $targetImage, 0644);

                return 1;
            }
            $ratio     = $width / $height;
            $newWidth  = $targetWidth;
            $newHeight = \round($newWidth / $ratio);
            if ($newHeight > $targetheight) {
                $newHeight = $targetheight;
                $newWidth  = \round($newHeight * $ratio);
            }
            if ($container === 'Y') {
                $im = $this->imageloadContainer($imgFilename, $newWidth, $newHeight, $targetWidth, $targetheight);
            } else {
                $im = $this->imageloadAlpha($imgFilename, $newWidth, $newHeight);
            }
            if ($this->saveImage($im, $format, \PFAD_ROOT . $targetImage, $quality)) {
                $ret = 1;
                @\chmod(\PFAD_ROOT . $targetImage, 0644);
            } else {
                $this->logger->error('Fehler beim Speichern des Bildes: ' . $targetImage);
            }
        } else {
            $this->logger->error('Fehler beim Speichern des Bildes: ' . $imgFilename);
        }

        return $ret;
    }

    /**
     * @param object       $branding
     * @param string       $imgFilename
     * @param string       $target
     * @param int          $targetWidth
     * @param int          $targetHeight
     * @param int          $quality
     * @param int|resource $brand
     * @param string       $container
     * @return int
     */
    private function createThumbnail(
        $branding,
        $imgFilename,
        $target,
        $targetWidth,
        $targetHeight,
        $quality = 80,
        $brand = 0,
        $container = 'N'
    ): int {
        $enlarge = $this->config['bilder']['bilder_skalieren'] === 'Y';
        $ret     = 0;
        $format  = $this->config['bilder']['bilder_dateiformat'];//$this->getExtension($imgFilename);
        $im      = $this->imageloadAlpha($imgFilename);
        if (!$im) {
            $this->logger->error('Bild konnte nicht erstellt werden. Datei kein Bild?: ' . $imgFilename);
            return $ret;
        }
        [$width, $height] = \getimagesize($imgFilename);
        if (!$enlarge && $width < $targetWidth && $height < $targetHeight) {
            // Bild nicht neu berechnen, nur verschieben
            $im = $container === 'Y'
                ? $this->imageloadContainer($imgFilename, $width, $height, $targetWidth, $targetHeight)
                : $this->imageloadAlpha($imgFilename, $width, $height);
            $this->saveImage($this->brandImage($im, $brand, $branding), $format, \PFAD_ROOT . $target, $quality);
            @\chmod(\PFAD_ROOT . $target, 0644);

            return 1;
        }
        $ratio     = $width / $height;
        $newWidth  = $targetWidth;
        $newHeight = \round($newWidth / $ratio);
        if ($newHeight > $targetHeight) {
            $newHeight = $targetHeight;
            $newWidth  = \round($newHeight * $ratio);
        }
        $image = $container === 'Y'
            ? $this->imageloadContainer($imgFilename, $newWidth, $newHeight, $targetWidth, $targetHeight)
            : $this->imageloadAlpha($imgFilename, $newWidth, $newHeight);
        if ($this->saveImage($this->brandImage($image, $brand, $branding), $format, \PFAD_ROOT . $target, $quality)) {
            $ret = 1;
            @\chmod(\PFAD_ROOT . $target, 0644);
        } else {
            $this->logger->error('Fehler beim Speichern des Bildes: ' . $target);
        }

        return $ret;
    }

    /**
     * @param array $xml
     */
    private function handleDeletes($xml): void
    {
        \executeHook(\HOOK_BILDER_XML_BEARBEITEDELETES, [
            'Artikel'          => $xml['del_bilder']['tArtikelPict'] ?? [],
            'Kategorie'        => $xml['del_bilder']['kKategoriePict'] ?? [],
            'KategoriePK'      => $xml['del_bilder']['kKategorie'] ?? [],
            'Eigenschaftswert' => $xml['del_bilder']['kEigenschaftWertPict'] ?? [],
            'Hersteller'       => $xml['del_bilder']['kHersteller'] ?? [],
            'Merkmal'          => $xml['del_bilder']['kMerkmal'] ?? [],
            'Merkmalwert'      => $xml['del_bilder']['kMerkmalWert'] ?? [],
        ]);
        // Artikelbilder löschen Wawi > .99923
        if (isset($xml['del_bilder']['tArtikelPict'])) {
            if (\count($xml['del_bilder']['tArtikelPict']) > 1) {
                for ($i = 0; $i < (\count($xml['del_bilder']['tArtikelPict']) / 2); $i++) {
                    $index        = $i . ' attr';
                    $productImage = (object)$xml['del_bilder']['tArtikelPict'][$index];
                    $this->deleteArtikelPict($productImage->kArtikel, $productImage->nNr);
                }
            } else {
                $productImage = (object)$xml['del_bilder']['tArtikelPict attr'];
                $this->deleteArtikelPict($productImage->kArtikel, $productImage->nNr);
            }
        }
        // Kategoriebilder löschen Wawi > .99923
        $source = $xml['del_bilder']['kKategorie'] ?? [];
        if (\is_numeric($source)) {
            $source = [$source];
        }
        foreach (\array_filter(\array_map('\intval', $source)) as $id) {
            $this->deleteCategoryImage(null, $id);
        }
        // Variationsbilder löschen Wawi > .99923
        $source = $xml['del_bilder']['kEigenschaftWert'] ?? [];
        if (\is_numeric($source)) {
            $source = [$source];
        }
        foreach (\array_filter(\array_map('\intval', $source)) as $id) {
            $this->deleteAttributeValueImage(null, $id);
        }
        // Herstellerbilder löschen
        $source = $xml['del_bilder']['kHersteller'] ?? [];
        if (\is_numeric($source)) {
            $source = [$source];
        }
        $cacheTags = [];
        foreach (\array_filter(\array_map('\intval', $source)) as $manufacturerID) {
            $this->db->update(
                'thersteller',
                'kHersteller',
                (int)$manufacturerID,
                (object)['cBildpfad' => '']
            );
            foreach ($this->db->selectAll(
                'tartikel',
                'kHersteller',
                (int)$manufacturerID,
                'kArtikel'
            ) as $product) {
                $cacheTags[] = $product->kArtikel;
            }
        }
        if (\count($cacheTags) > 0) {
            \array_walk($cacheTags, function (&$i) {
                $i = \CACHING_GROUP_ARTICLE . '_' . $i;
            });
            $this->cache->flushTags($cacheTags);
        }
        // Merkmalbilder löschen
        $source = $xml['del_bilder']['kMerkmal'] ?? [];
        if (\is_numeric($source)) {
            $source = [$source];
        }
        foreach (\array_filter(\array_map('\intval', $source)) as $attrID) {
            $this->db->update(
                'tmerkmal',
                'kMerkmal',
                (int)$attrID,
                (object)['cBildpfad' => '']
            );
        }
        // Merkmalwertbilder löschen
        $source = $xml['del_bilder']['kMerkmalWert'] ?? [];
        if (\is_numeric($source)) {
            $source = [$source];
        }
        foreach (\array_filter(\array_map('\intval', $source)) as $attrValID) {
            $this->db->update(
                'tmerkmalwert',
                'kMerkmalWert',
                (int)$attrValID,
                (object)['cBildpfad' => '']
            );
            $this->db->delete('tmerkmalwertbild', 'kMerkmalWert', (int)$attrValID);
        }
    }

    /**
     * @param int      $productImageID
     * @param int|null $no
     */
    private function deleteArtikelPict(int $productImageID, int $no = null): void
    {
        if ($productImageID <= 0) {
            return;
        }
        if ($no !== null && $no > 0) {
            $image          = $this->db->select('tartikelpict', 'kArtikel', $productImageID, 'nNr', $no);
            $productImageID = $image->kArtikelPict ?? 0;
        }
        $this->deleteProductImage(null, 0, $productImageID);
    }

    /**
     * @param int|null $categoryImageID
     * @param int|null $categoryID
     */
    private function deleteCategoryImage(int $categoryImageID = null, int $categoryID = null): void
    {
        if ($categoryImageID !== null && $categoryImageID > 0) {
            $this->db->delete('tkategoriepict', 'kKategoriePict', $categoryImageID);
        } elseif ($categoryID !== null && $categoryID > 0) {
            $this->db->delete('tkategoriepict', 'kKategorie', $categoryID);
        }
    }

    /**
     * @param int|null $imageID
     * @param int|null $attrValID
     */
    private function deleteAttributeValueImage(int $imageID = null, int $attrValID = null): void
    {
        if ($attrValID !== null && $attrValID > 0) {
            $this->db->delete('teigenschaftwertpict', 'kEigenschaftWert', $attrValID);
        }
        if ($imageID !== null && $imageID > 0) {
            $this->db->delete('teigenschaftwertpict', 'kEigenschaftwertPict', $imageID);
        }
    }

    /**
     * @param resource $im
     * @param resource $brand
     * @param object   $oBranding
     * @return mixed
     */
    private function brandImage($im, $brand, $oBranding)
    {
        if (!$brand
            || (isset($oBranding->oBrandingEinstellung->nAktiv) && (int)$oBranding->oBrandingEinstellung->nAktiv === 0)
            || !isset($oBranding->oBrandingEinstellung->cBrandingBild)
        ) {
            return $im;
        }
        $brandingImage = \PFAD_ROOT . \PFAD_BRANDINGBILDER . $oBranding->oBrandingEinstellung->cBrandingBild;
        if (!\file_exists($brandingImage)) {
            return $im;
        }
        $position     = $oBranding->oBrandingEinstellung->cPosition;
        $transparency = $oBranding->oBrandingEinstellung->dTransparenz;
        $brandingSize = $oBranding->oBrandingEinstellung->dGroesse;
        $randabstand  = $oBranding->oBrandingEinstellung->dRandabstand / 100;
        $branding     = $this->imageloadAlpha($brandingImage, 0, 0, true);
        if (!$im || !$branding) {
            return $im;
        }
        $imageWidth        = \imagesx($im);
        $imageHeight       = \imagesy($im);
        $brandingWidth     = \imagesx($branding);
        $brandingHeight    = \imagesy($branding);
        $brandingNewWidth  = $brandingWidth;
        $brandingNewHeight = $brandingHeight;
        $image_branding    = $branding;
        if ($brandingSize > 0) { // branding auf diese Breite skalieren
            $brandingNewWidth  = \round(($imageWidth * $brandingSize) / 100.0);
            $brandingNewHeight = \round(($brandingNewWidth / $brandingWidth) * $brandingHeight);

            $image_branding = $this->imageloadAlpha($brandingImage, $brandingNewWidth, $brandingNewHeight, true);
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
        $brandingPosX = \round($brandingPosX);
        $brandingPosY = \round($brandingPosY);
        // bild mit branding composen
        \imagealphablending($im, true);
        \imagesavealpha($im, true);
        $this->imagecopymergeAlpha(
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

    /**
     * @param resource $destImg
     * @param resource $srcImg
     * @param int      $destX
     * @param int      $destY
     * @param int      $srcX
     * @param int      $srxY
     * @param int      $srcW
     * @param int      $srcH
     * @param int      $pct
     * @return bool
     */
    private function imagecopymergeAlpha($destImg, $srcImg, $destX, $destY, $srcX, $srxY, $srcW, $srcH, $pct): bool
    {
        if ($pct === null) {
            return false;
        }
        $pct /= 100;
        // Get image width and height
        $w = \imagesx($srcImg);
        $h = \imagesy($srcImg);
        // Turn alpha blending off
        \imagealphablending($srcImg, false);
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
                $colorxy = \imagecolorat($srcImg, $x, $y);
                $alpha   = ($colorxy >> 24) & 0xFF;
                // calculate new alpha
                if ($minalpha !== 127) {
                    $alpha = 127 + 127 * $pct * ($alpha - 127) / (127 - $minalpha);
                } else {
                    $alpha += 127 * $pct;
                }
                // get the color index with new alpha
                $alphacolorxy = \imagecolorallocatealpha(
                    $srcImg,
                    ($colorxy >> 16) & 0xFF,
                    ($colorxy >> 8) & 0xFF,
                    $colorxy & 0xFF,
                    $alpha
                );
                // set pixel with the new color + opacity
                if (!\imagesetpixel($srcImg, $x, $y, $alphacolorxy)) {
                    return false;
                }
            }
        }
        // The image copy
        \imagecopy($destImg, $srcImg, $destX, $destY, $srcX, $srxY, $srcW, $srcH);

        return true;
    }

    /**
     * @param string $imgFilename
     * @return bool|string
     */
    private function getExtension(string $imgFilename)
    {
        if (!\file_exists($imgFilename)) {
            return false;
        }
        $size = \getimagesize($imgFilename);
        switch ($size[2]) {
            case \IMAGETYPE_JPEG:
                $ext = 'jpg';
                break;
            case \IMAGETYPE_PNG:
                $ext = \function_exists('imagecreatefrompng') ? 'png' : false;
                break;
            case \IMAGETYPE_GIF:
                $ext = \function_exists('imagecreatefromgif') ? 'gif' : false;
                break;
            case \IMAGETYPE_BMP:
                $ext = \function_exists('imagecreatefromwbmp') ? 'bmp' : false;
                break;
            default:
                $ext = false;
                break;
        }

        return $ext;
    }

    /**
     * @param string $img
     * @param int    $width
     * @param int    $height
     * @param int    $containerWidth
     * @param int    $containerHeight
     * @return resource
     */
    private function imageloadContainer($img, int $width, int $height, $containerWidth, $containerHeight)
    {
        $imgInfo = \getimagesize($img);
        switch ($imgInfo[2]) {
            case 1:
                $im = \imagecreatefromgif($img);
                break;
            case 2:
                $im = \imagecreatefromjpeg($img);
                break;
            case 3:
                $im = \imagecreatefrompng($img);
                break;
            default:
                $im = \imagecreatefromjpeg($img);
                break;
        }

        if ($width === 0 && $height === 0) {
            [$width, $height] = $imgInfo;
        }
        $width  = \round($width);
        $height = \round($height);
        $newImg = \imagecreatetruecolor($containerWidth, $containerHeight);
        // hintergrundfarbe
        $format = \strtolower($this->config['bilder']['bilder_dateiformat']);
        if ($format === 'jpg') {
            $rgb   = $this->html2rgb($this->config['bilder']['bilder_hintergrundfarbe']);
            $color = \imagecolorallocate($newImg, $rgb[0], $rgb[1], $rgb[2]);
            \imagealphablending($newImg, true);
        } else {
            $color = \imagecolorallocatealpha($newImg, 255, 255, 255, 127);
            \imagealphablending($newImg, false);
        }
        \imagesavealpha($newImg, true);
        \imagefilledrectangle($newImg, 0, 0, $containerWidth, $containerHeight, $color);

        $posX = ($containerWidth / 2) - ($width / 2);
        $posY = ($containerHeight / 2) - ($height / 2);

        \imagecopyresampled($newImg, $im, $posX, $posY, 0, 0, $width, $height, $imgInfo[0], $imgInfo[1]);

        return $newImg;
    }

    /**
     * @param string $img
     * @param int    $width
     * @param int    $height
     * @param bool   $branding
     * @return resource
     */
    private function imageloadAlpha($img, int $width = 0, int $height = 0, bool $branding = false)
    {
        $imgInfo = \getimagesize($img);
        switch ($imgInfo[2]) {
            case 1:
                $im = \imagecreatefromgif($img);
                break;

            case 2:
                $im = \imagecreatefromjpeg($img);
                break;

            case 3:
                $im = \imagecreatefrompng($img);
                break;

            default:
                $im = \imagecreatefromjpeg($img);
                break;
        }

        if ($width === 0 && $height === 0) {
            [$width, $height] = $imgInfo;
        }

        $width  = \round($width);
        $height = \round($height);
        $newImg = \imagecreatetruecolor($width, $height);

        if (!$newImg) {
            return $im;
        }

        // hintergrundfarbe
        $format = \strtolower($this->config['bilder']['bilder_dateiformat']);
        if ($format === 'jpg') {
            $rgb   = $this->html2rgb($this->config['bilder']['bilder_hintergrundfarbe']);
            $color = \imagecolorallocate($newImg, $rgb[0], $rgb[1], $rgb[2]);
            if ($branding) {
                \imagealphablending($newImg, false);
            } else {
                \imagealphablending($newImg, true);
            }
        } else {
            $color = \imagecolorallocatealpha($newImg, 255, 255, 255, 127);
            \imagealphablending($newImg, false);
        }

        \imagesavealpha($newImg, true);
        \imagefilledrectangle($newImg, 0, 0, $width, $height, $color);
        \imagecopyresampled($newImg, $im, 0, 0, 0, 0, $width, $height, $imgInfo[0], $imgInfo[1]);

        return $newImg;
    }

    /**
     * @param string $path
     * @return string
     */
    private function getNewFilename(string $path): string
    {
        $format = \strtolower($this->config['bilder']['bilder_dateiformat']);
        $path   = \substr($path, 0, -3);

        return $path . $format;
    }

    /**
     * @param resource $im
     * @param string   $format
     * @param string   $path
     * @param int      $quality
     * @return bool
     */
    private function saveImage($im, $format, $path, int $quality = 80): bool
    {
        if (!$im) {
            return false;
        }

        $path = $this->getNewFilename($path);

        switch (\strtolower($format)) {
            case 'jpg':
                $res = \function_exists('imagejpeg') ? \imagejpeg($im, $path, $quality) : false;
                break;
            case 'png':
                $res = \function_exists('imagepng') ? \imagepng($im, $path) : false;
                break;
            case 'gif':
                $res = \function_exists('imagegif') ? \imagegif($im, $path) : false;
                break;
            case 'bmp':
                $res = \function_exists('imagewbmp') ? \imagewbmp($im, $path) : false;
                break;
            default:
                $res = false;
                break;
        }

        return $res;
    }

    /**
     * @param string $color
     * @return array|bool
     */
    private function html2rgb(string $color)
    {
        if (\strpos($color, '#') === 0) {
            $color = \substr($color, 1);
        }

        if (\strlen($color) === 6) {
            [$r, $g, $b] = [
                $color[0] . $color[1],
                $color[2] . $color[3],
                $color[4] . $color[5]
            ];
        } elseif (\strlen($color) === 3) {
            [$r, $g, $b] = [
                $color[0] . $color[0],
                $color[1] . $color[1],
                $color[2] . $color[2]
            ];
        } else {
            return false;
        }

        return [\hexdec($r), \hexdec($g), \hexdec($b)];
    }
}
