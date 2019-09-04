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
        $config   = Shop::getSettings([\CONF_BILDER]);
        $defaults = [
            'bilder_kategorien_breite'         => 100,
            'bilder_kategorien_hoehe'          => 100,
            'bilder_variationen_gross_breite'  => 800,
            'bilder_variationen_gross_hoehe'   => 800,
            'bilder_variationen_breite'        => 210,
            'bilder_variationen_hoehe'         => 210,
            'bilder_variationen_mini_breite'   => 30,
            'bilder_variationen_mini_hoehe'    => 30,
            'bilder_artikel_gross_breite'      => 800,
            'bilder_artikel_gross_hoehe'       => 800,
            'bilder_artikel_normal_breite'     => 210,
            'bilder_artikel_normal_hoehe'      => 210,
            'bilder_artikel_klein_breite'      => 80,
            'bilder_artikel_klein_hoehe'       => 80,
            'bilder_artikel_mini_breite'       => 30,
            'bilder_artikel_mini_hoehe'        => 30,
            'bilder_hersteller_normal_breite'  => 100,
            'bilder_hersteller_normal_hoehe'   => 100,
            'bilder_hersteller_klein_breite'   => 40,
            'bilder_hersteller_klein_hoehe'    => 40,
            'bilder_merkmal_normal_breite'     => 100,
            'bilder_merkmal_normal_hoehe'      => 100,
            'bilder_merkmal_klein_breite'      => 20,
            'bilder_merkmal_klein_hoehe'       => 20,
            'bilder_merkmalwert_normal_breite' => 100,
            'bilder_merkmalwert_normal_hoehe'  => 100,
            'bilder_merkmalwert_klein_breite'  => 20,
            'bilder_merkmalwert_klein_hoehe'   => 20,
            'bilder_konfiggruppe_klein_breite' => 130,
            'bilder_konfiggruppe_klein_hoehe'  => 130,
            'bilder_jpg_quali'                 => 80,
            'bilder_dateiformat'               => 'PNG',
            'bilder_hintergrundfarbe'          => '#ffffff',
            'bilder_skalieren'                 => 'N',
        ];
        foreach ($defaults as $option => $value) {
            if (empty($config['bilder'][$option])) {
                $config['bilder'][$option] = $value;
            }
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
        if (!\is_array($xml['bilder'])) {
            return;
        }
        $productImages      = $this->mapper->mapArray($xml['bilder'], 'tartikelpict', 'mArtikelPict');
        $categoryImages     = $this->mapper->mapArray($xml['bilder'], 'tkategoriepict', 'mKategoriePict');
        $propertyImages     = $this->mapper->mapArray($xml['bilder'], 'teigenschaftwertpict', 'mEigenschaftWertPict');
        $manufacturerImages = $this->mapper->mapArray($xml['bilder'], 'therstellerbild', 'mEigenschaftWertPict');
        $charImages         = $this->mapper->mapArray($xml['bilder'], 'tMerkmalbild', 'mEigenschaftWertPict');
        $charValImages      = $this->mapper->mapArray($xml['bilder'], 'tmerkmalwertbild', 'mEigenschaftWertPict');
        $configGroupImages  = $this->mapper->mapArray($xml['bilder'], 'tkonfiggruppebild', 'mKonfiggruppePict');

        \executeHook(\HOOK_BILDER_XML_BEARBEITE, [
            'Pfad'             => $unzipPath,
            'Artikel'          => &$productImages,
            'Kategorie'        => &$categoryImages,
            'Eigenschaftswert' => &$propertyImages,
            'Hersteller'       => &$manufacturerImages,
            'Merkmalwert'      => &$charValImages,
            'Merkmal'          => &$charImages,
            'Konfiggruppe'     => &$configGroupImages
        ]);
        $this->unzipPath = $unzipPath;

        $this->handleProductImages($productImages, $sql);
        $this->handleCategoryImages($categoryImages, $sql);
        $this->handlePropertyImages($propertyImages, $sql);
        $this->handleManufacturerImages($manufacturerImages);
        $this->handleCharacteristicImages($charImages);
        $this->handleCharacteristicValueImages($charValImages);
        $this->handleConfigGroupImages($configGroupImages);
        if (\count($charImages) > 0 || \count($charValImages) > 0) {
            $this->cache->flushTags([\CACHING_GROUP_ATTRIBUTE, \CACHING_GROUP_FILTER_CHARACTERISTIC]);
        }

        \executeHook(\HOOK_BILDER_XML_BEARBEITE_ENDE, [
            'Artikel'          => &$productImages,
            'Kategorie'        => &$categoryImages,
            'Eigenschaftswert' => &$propertyImages,
            'Hersteller'       => &$manufacturerImages,
            'Merkmalwert'      => &$charValImages,
            'Merkmal'          => &$charImages,
            'Konfiggruppe'     => &$configGroupImages
        ]);
    }

    /**
     * @param array $images
     */
    private function handleConfigGroupImages(array $images): void
    {
        foreach ($images as $image) {
            $item                = new stdClass();
            $item->cBildPfad     = $image->cPfad;
            $item->kKonfiggruppe = $image->kKonfiggruppe;
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
     * @param array $images
     */
    private function handleCharacteristicValueImages(array $images): void
    {
        foreach ($images as $image) {
            $image->kMerkmalWert = (int)$image->kMerkmalWert;
            if (empty($image->cPfad) || $image->kMerkmalWert <= 0) {
                continue;
            }
            $original = $this->unzipPath . $image->cPfad;
            $format   = $this->getExtension($original);
            if (!$format) {
                $this->logger->error(
                    'Bildformat des Merkmalwertbildes konnte nicht ermittelt werden. Datei ' .
                    $original . ' keine Bilddatei?'
                );
                continue;
            }
            $image->cPfad .= '.' . $format;
            $image->cPfad  = $this->getNewFilename($image->cPfad);
            $this->createThumbnail(
                $this->brandingConfig['Merkmalwerte'],
                $original,
                \PFAD_MERKMALWERTBILDER_NORMAL . $image->cPfad,
                $this->config['bilder']['bilder_merkmalwert_normal_breite'],
                $this->config['bilder']['bilder_merkmalwert_normal_hoehe'],
                $this->config['bilder']['bilder_jpg_quali'],
                1,
                $this->config['bilder']['container_verwenden']
            );
            if ($this->createBrandedThumbnail(
                $original,
                \PFAD_MERKMALWERTBILDER_KLEIN . $image->cPfad,
                $this->config['bilder']['bilder_merkmalwert_klein_breite'],
                $this->config['bilder']['bilder_merkmalwert_klein_hoehe'],
                $this->config['bilder']['bilder_jpg_quali'],
                $this->config['bilder']['container_verwenden']
            )) {
                $this->db->update(
                    'tmerkmalwert',
                    'kMerkmalWert',
                    (int)$image->kMerkmalWert,
                    (object)['cBildpfad' => $image->cPfad]
                );
                $charValImage               = new stdClass();
                $charValImage->kMerkmalWert = (int)$image->kMerkmalWert;
                $charValImage->cBildpfad    = $image->cPfad;

                $this->upsert('tmerkmalwertbild', [$charValImage], 'kMerkmalWert');
            }
            \unlink($original);
        }
    }

    /**
     * @param array $images
     */
    private function handleCharacteristicImages(array $images): void
    {
        foreach ($images as $image) {
            $image->kMerkmal = (int)$image->kMerkmal;
            if (empty($image->cPfad) || $image->kMerkmal <= 0) {
                continue;
            }
            $original = $this->unzipPath . $image->cPfad;
            $format   = $this->getExtension($original);
            if (!$format) {
                $this->logger->error(
                    'Bildformat des Merkmalbildes konnte nicht ermittelt werden. Datei ' .
                    $original . ' keine Bilddatei?'
                );
                continue;
            }
            $image->cPfad .= '.' . $format;
            $image->cPfad  = $this->getNewFilename($image->cPfad);
            $this->createThumbnail(
                $this->brandingConfig['Merkmale'],
                $original,
                \PFAD_MERKMALBILDER_NORMAL . $image->cPfad,
                $this->config['bilder']['bilder_merkmal_normal_breite'],
                $this->config['bilder']['bilder_merkmal_normal_hoehe'],
                $this->config['bilder']['bilder_jpg_quali'],
                1,
                $this->config['bilder']['container_verwenden']
            );
            if ($this->createBrandedThumbnail(
                $original,
                \PFAD_MERKMALBILDER_KLEIN . $image->cPfad,
                $this->config['bilder']['bilder_merkmal_klein_breite'],
                $this->config['bilder']['bilder_merkmal_klein_hoehe'],
                $this->config['bilder']['bilder_jpg_quali'],
                $this->config['bilder']['container_verwenden']
            )) {
                $this->db->update(
                    'tmerkmal',
                    'kMerkmal',
                    (int)$image->kMerkmal,
                    (object)['cBildpfad' => $image->cPfad]
                );
            }
            \unlink($original);
        }
    }

    /**
     * @param array $images
     */
    private function handleManufacturerImages(array $images): void
    {
        foreach ($images as $image) {
            $image->kHersteller = (int)$image->kHersteller;
            if (empty($image->cPfad) || $image->kHersteller <= 0) {
                continue;
            }
            $original = $this->unzipPath . $image->cPfad;
            $format   = $this->getExtension($original);
            if (!$format) {
                $this->logger->error(
                    'Bildformat des Herstellerbildes konnte nicht ermittelt werden. Datei ' .
                    $original . ' keine Bilddatei?'
                );
                continue;
            }
            $manufacturer = $this->db->query(
                'SELECT cSeo
                FROM thersteller
                WHERE kHersteller = ' . (int)$image->kHersteller,
                ReturnType::SINGLE_OBJECT
            );
            if (!empty($manufacturer->cSeo)) {
                $image->cPfad = \str_replace('/', '_', $manufacturer->cSeo . '.' . $format);
            } elseif (\stripos(\strrev($image->cPfad), \strrev($format)) !== 0) {
                $image->cPfad .= '.' . $format;
            }
            $image->cPfad = $this->getNewFilename($image->cPfad);
            $this->createThumbnail(
                $this->brandingConfig['Hersteller'],
                $original,
                \PFAD_HERSTELLERBILDER_NORMAL . $image->cPfad,
                $this->config['bilder']['bilder_hersteller_normal_breite'],
                $this->config['bilder']['bilder_hersteller_normal_hoehe'],
                $this->config['bilder']['bilder_jpg_quali'],
                1,
                $this->config['bilder']['container_verwenden']
            );
            if ($this->createBrandedThumbnail(
                $original,
                \PFAD_HERSTELLERBILDER_KLEIN . $image->cPfad,
                $this->config['bilder']['bilder_hersteller_klein_breite'],
                $this->config['bilder']['bilder_hersteller_klein_hoehe'],
                $this->config['bilder']['bilder_jpg_quali'],
                $this->config['bilder']['container_verwenden']
            )) {
                $this->db->update(
                    'thersteller',
                    'kHersteller',
                    (int)$image->kHersteller,
                    (object)['cBildpfad' => $image->cPfad]
                );
            }
            $cacheTags = [];
            foreach ($this->db->selectAll(
                'tartikel',
                'kHersteller',
                (int)$image->kHersteller,
                'kArtikel'
            ) as $product) {
                $cacheTags[] = \CACHING_GROUP_ARTICLE . '_' . $product->kArtikel;
            }
            $this->cache->flushTags($cacheTags);
            \unlink($original);
        }
    }

    /**
     * @param array  $images
     * @param string $sql
     */
    private function handlePropertyImages(array $images, string $sql): void
    {
        foreach ($images as $image) {
            if (empty($image->cPfad)) {
                continue;
            }
            $original = $this->unzipPath . $image->cPfad;
            $format   = $this->getExtension($original);
            if (!$format) {
                $this->logger->error(
                    'Bildformat des Eigenschaftwertbildes konnte nicht ermittelt werden. Datei ' .
                    $original . ' keine Bilddatei?'
                );
                continue;
            }
            $image->cPfad = $this->getPropertiesImageName($image, $format, $sql);
            $image->cPfad = $this->getNewFilename($image->cPfad);
            $this->createThumbnail(
                $this->brandingConfig['Variationen'],
                $original,
                \PFAD_VARIATIONSBILDER_GROSS . $image->cPfad,
                $this->config['bilder']['bilder_variationen_gross_breite'],
                $this->config['bilder']['bilder_variationen_gross_hoehe'],
                $this->config['bilder']['bilder_jpg_quali'],
                1,
                $this->config['bilder']['container_verwenden']
            );
            $this->createBrandedThumbnail(
                $original,
                \PFAD_VARIATIONSBILDER_NORMAL . $image->cPfad,
                $this->config['bilder']['bilder_variationen_breite'],
                $this->config['bilder']['bilder_variationen_hoehe'],
                $this->config['bilder']['bilder_jpg_quali'],
                $this->config['bilder']['container_verwenden']
            );
            if ($this->createBrandedThumbnail(
                $original,
                \PFAD_VARIATIONSBILDER_MINI . $image->cPfad,
                $this->config['bilder']['bilder_variationen_mini_breite'],
                $this->config['bilder']['bilder_variationen_mini_hoehe'],
                $this->config['bilder']['bilder_jpg_quali'],
                $this->config['bilder']['container_verwenden']
            )) {
                $this->upsert('teigenschaftwertpict', [$image], 'kEigenschaftWert');
            }
            \unlink($original);
        }
    }

    /**
     * @param array  $images
     * @param string $sql
     */
    private function handleCategoryImages(array $images, string $sql): void
    {
        foreach ($images as $image) {
            if (empty($image->cPfad)) {
                continue;
            }
            $original = $this->unzipPath . $image->cPfad;
            $format   = $this->getExtension($original);
            if (!$format) {
                $this->logger->error(
                    'Bildformat des Kategoriebildes konnte nicht ermittelt werden. Datei ' .
                    $original . ' keine Bilddatei?'
                );
                continue;
            }

            $image->cPfad = $this->getCategoryImageName($image, $format, $sql);
            $image->cPfad = $this->getNewFilename($image->cPfad);
            if ($this->createThumbnail(
                $this->brandingConfig['Kategorie'],
                $original,
                \PFAD_KATEGORIEBILDER . $image->cPfad,
                $this->config['bilder']['bilder_kategorien_breite'],
                $this->config['bilder']['bilder_kategorien_hoehe'],
                $this->config['bilder']['bilder_jpg_quali'],
                1,
                $this->config['bilder']['container_verwenden']
            )) {
                $this->upsert('tkategoriepict', [$image], 'kKategorie');
            }
            \unlink($original);
        }
    }

    /**
     * @param stdClass[] $images
     * @param string     $sql
     */
    private function handleProductImages(array $images, string $sql): void
    {
        if (\count($images) === 0) {
            return;
        }
        foreach ($images as $image) {
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
            $this->deleteProductImage((int)$image->kArtikelPict);
            // then delete by kArtikel + nNr since Wawi > .99923 has changed all kArtikelPict keys
            if ($image->nNr > 0) {
                $this->deleteProductImageByProductID((int)$image->kArtikel, $image->nNr);
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
                    $this->createProductImage($image, $format, $imgFilename, $sql);
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
                $this->createProductImage($image, $format, $imgFilename, $sql);
            }
        }
        $this->deleteTempProductImages();
    }

    /**
     *
     */
    private function deleteTempProductImages(): void
    {
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

    /**
     * @param stdClass $image
     * @param string   $format
     * @param string   $fileName
     * @param string   $sql
     */
    private function createProductImage($image, string $format, string $fileName, string $sql): void
    {
        $config       = $this->config['bilder'];
        $image->cPfad = $this->getProductImageName(
            $image,
            $config['container_verwenden'] === 'Y' ? 'png' : $format,
            $sql
        );
        $image->cPfad = $this->getNewFilename($image->cPfad);
        $original     = $this->unzipPath . $fileName;
        $this->createThumbnail(
            $this->brandingConfig['Artikel'],
            $original,
            \PFAD_PRODUKTBILDER_GROSS . $image->cPfad,
            $config['bilder_artikel_gross_breite'],
            $config['bilder_artikel_gross_hoehe'],
            $config['bilder_jpg_quali'],
            1,
            $config['container_verwenden']
        );
        $this->createBrandedThumbnail(
            $original,
            \PFAD_PRODUKTBILDER_NORMAL . $image->cPfad,
            $config['bilder_artikel_normal_breite'],
            $config['bilder_artikel_normal_hoehe'],
            $config['bilder_jpg_quali'],
            $config['container_verwenden']
        );
        $this->createBrandedThumbnail(
            $original,
            \PFAD_PRODUKTBILDER_KLEIN . $image->cPfad,
            $config['bilder_artikel_klein_breite'],
            $config['bilder_artikel_klein_hoehe'],
            $config['bilder_jpg_quali'],
            $config['container_verwenden']
        );
        if ($this->createBrandedThumbnail(
            $original,
            \PFAD_PRODUKTBILDER_MINI . $image->cPfad,
            $config['bilder_artikel_mini_breite'],
            $config['bilder_artikel_mini_hoehe'],
            $config['bilder_jpg_quali'],
            $config['container_verwenden']
        )) {
            $this->upsert('tartikelpict', [$image], 'kArtikel', 'kArtikelPict');
        }
    }

    /**
     * @param object $image
     * @param string $format
     * @param string $sql
     * @return string
     */
    private function getPropertiesImageName($image, $format, $sql): string
    {
        if (!$image->kEigenschaftWert || !$this->config['bilder']['bilder_variation_namen']) {
            return (\stripos(\strrev($image->cPfad), \strrev($format)) === 0)
                ? $image->cPfad
                : $image->cPfad . '.' . $format;
        }
        $propValue = $this->db->query(
            'SELECT kEigenschaftWert, cArtNr, cName, kEigenschaft
                FROM teigenschaftwert
                WHERE kEigenschaftWert = ' . (int)$image->kEigenschaftWert,
            ReturnType::SINGLE_OBJECT
        );
        if ($propValue === false) {
            $this->logger->warning(
                'Eigenschaftswertbild fuer nicht existierenden Eigenschaftswert ' . (int)$image->kEigenschaftWert
            );
            return $image->cPfad;
        }
        $imageName = $propValue->kEigenschaftWert;
        if ($propValue->cName) {
            switch ($this->config['bilder']['bilder_variation_namen']) {
                case 1:
                    if (!empty($propValue->cArtNr)) {
                        $imageName = 'var' . $this->convertUmlauts($propValue->cArtNr);
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
                    if (!empty($product->cArtNr) && !empty($propValue->cArtNr)) {
                        $imageName = $this->convertUmlauts($product->cArtNr) .
                            '_' .
                            $this->convertUmlauts($propValue->cArtNr);
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
                        'SELECT cName FROM teigenschaft WHERE kEigenschaft = ' . $propValue->kEigenschaft,
                        ReturnType::SINGLE_OBJECT
                    );
                    if ((!empty($product->cSeo) || !empty($product->cName))
                        && !empty($attribute->cName)
                        && !empty($propValue->cName)
                    ) {
                        if ($product->cSeo) {
                            $imageName = $product->cSeo . '_' .
                                $this->convertUmlauts($attribute->cName) . '_' .
                                $this->convertUmlauts($propValue->cName);
                        } else {
                            $imageName = $this->convertUmlauts($product->cName) . '_' .
                                $this->convertUmlauts($attribute->cName) . '_' .
                                $this->convertUmlauts($propValue->cName);
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
        if (!empty($image->kArtikel)) {
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
        if (empty($product->cName)) {
            return $image->cPfad . '.' . $format;
        }
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
                    $this->deleteProductImageByProductID($productImage->kArtikel, $productImage->nNr);
                }
            } else {
                $productImage = (object)$xml['del_bilder']['tArtikelPict attr'];
                $this->deleteProductImageByProductID($productImage->kArtikel, $productImage->nNr);
            }
        }
        // Kategoriebilder löschen Wawi > .99923
        $this->deleteCategoryImages($xml);
        // Variationsbilder löschen Wawi > .99923
        $this->deleteVariationImages($xml);
        // Herstellerbilder löschen
        $this->deleteManufacturerImages($xml);
        // Merkmalbilder löschen
        $this->deleteCharacteristicImages($xml);
        // Merkmalwertbilder löschen
        $this->deleteCharacteristicValueImages($xml);
    }

    /**
     * @param array $xml
     */
    private function deleteVariationImages(array $xml): void
    {
        $source = $xml['del_bilder']['kEigenschaftWert'] ?? [];
        if (\is_numeric($source)) {
            $source = [$source];
        }
        foreach (\array_filter(\array_map('\intval', $source)) as $id) {
            $this->db->delete('teigenschaftwertpict', 'kEigenschaftWert', $id);
        }
    }

    /**
     * @param array $xml
     */
    private function deleteCategoryImages(array $xml): void
    {
        $source = $xml['del_bilder']['kKategorie'] ?? [];
        if (\is_numeric($source)) {
            $source = [$source];
        }
        foreach (\array_filter(\array_map('\intval', $source)) as $id) {
            $this->db->delete('tkategoriepict', 'kKategorie', $id);
        }
    }

    /**
     * @param array $xml
     */
    private function deleteManufacturerImages(array $xml): void
    {
        $cacheTags = [];
        $source    = $xml['del_bilder']['kHersteller'] ?? [];
        if (\is_numeric($source)) {
            $source = [$source];
        }
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
                $cacheTags[] = \CACHING_GROUP_ARTICLE . '_' . (int)$product->kArtikel;
            }
        }
        $this->cache->flushTags($cacheTags);
    }

    /**
     * @param array $xml
     */
    private function deleteCharacteristicImages(array $xml): void
    {
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
    }

    /**
     * @param array $xml
     */
    private function deleteCharacteristicValueImages(array $xml): void
    {
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
     * @param int $productID
     * @param int $no
     */
    private function deleteProductImageByProductID(int $productID, int $no): void
    {
        if ($productID < 1 || $no < 1) {
            return;
        }
        $image = $this->db->select('tartikelpict', 'kArtikel', $productID, 'nNr', $no);
        $this->deleteProductImage((int)($image->kArtikelPict ?? 0));
    }

    /**
     * @param int $imageID
     */
    private function deleteProductImage(int $imageID): void
    {
        if ($imageID < 1) {
            return;
        }
        $image     = $this->db->select('tartikelpict', 'kArtikelPict', $imageID);
        $productID = isset($image->kArtikel) ? (int)$image->kArtikel : 0;
        // Das Bild ist eine Verknüpfung
        if (isset($image->kMainArtikelBild) && $image->kMainArtikelBild > 0 && $productID > 0) {
            // Existiert der Artikel vom Mainbild noch?
            $main = $this->db->query(
                'SELECT kArtikel
                FROM tartikel
                WHERE kArtikel = (
                    SELECT kArtikel
                        FROM tartikelpict
                        WHERE kArtikelPict = ' . (int)$image->kMainArtikelBild . ')',
                ReturnType::SINGLE_OBJECT
            );
            // Main Artikel existiert nicht mehr
            if (!isset($main->kArtikel) || (int)$main->kArtikel === 0) {
                // Existiert noch eine andere aktive Verknüpfung auf das Mainbild?
                $productImages = $this->db->query(
                    'SELECT kArtikelPict
                    FROM tartikelpict
                    WHERE kMainArtikelBild = ' . (int)$image->kMainArtikelBild . '
                        AND kArtikel != ' . $productID,
                    ReturnType::ARRAY_OF_OBJECTS
                );
                // Lösche das MainArtikelBild
                if (\count($productImages) === 0) {
                    $this->deleteImageFiles($image->cPfad);
                    $this->db->delete('tartikelpict', 'kArtikelPict', (int)$image->kMainArtikelBild);
                }
            }
            // Bildverknüpfung aus DB löschen
            $this->db->delete('tartikelpict', 'kArtikelPict', (int)$image->kArtikelPict);
        } elseif (isset($image->kMainArtikelBild) && (int)$image->kMainArtikelBild === 0) {
            // Das Bild ist ein Hauptbild
            // Gibt es Artikel die auf Bilder des zu löschenden Artikel verknüpfen?
            $childProducts = $this->db->queryPrepared(
                'SELECT kArtikelPict
                FROM tartikelpict
                WHERE kMainArtikelBild = :img',
                ['img' => (int)$image->kArtikelPict],
                ReturnType::ARRAY_OF_OBJECTS
            );
            if (\count($childProducts) === 0) {
                $data = $this->db->queryPrepared(
                    'SELECT COUNT(*) AS nCount
                    FROM tartikelpict
                    WHERE cPfad = :pth',
                    ['pth' => $image->cPfad],
                    ReturnType::SINGLE_OBJECT
                );
                if (isset($data->nCount) && $data->nCount < 2) {
                    $this->deleteImageFiles($image->cPfad);
                }
            } else {
                // Reorder linked images because master imagelink will be deleted
                $next = $childProducts[0]->kArtikelPict;
                // this will be the next masterimage
                $this->db->update(
                    'tartikelpict',
                    'kArtikelPict',
                    (int)$next,
                    (object)['kMainArtikelBild' => 0]
                );
                // now link other images to the new masterimage
                $this->db->update(
                    'tartikelpict',
                    'kMainArtikelBild',
                    (int)$image->kArtikelPict,
                    (object)['kMainArtikelBild' => (int)$next]
                );
            }
            $this->db->delete('tartikelpict', 'kArtikelPict', (int)$image->kArtikelPict);
        }
        $this->cache->flushTags([\CACHING_GROUP_ARTICLE . '_' . $productID]);
    }

    /**
     * @param string $path
     */
    private function deleteImageFiles(string $path): void
    {
        $files = [
            \PFAD_ROOT . \PFAD_PRODUKTBILDER_MINI . $path,
            \PFAD_ROOT . \PFAD_PRODUKTBILDER_KLEIN . $path,
            \PFAD_ROOT . \PFAD_PRODUKTBILDER_NORMAL . $path,
            \PFAD_ROOT . \PFAD_PRODUKTBILDER_GROSS . $path,
            \PFAD_ROOT . \PFAD_MEDIA_IMAGE_STORAGE . $path
        ];

        foreach (\array_filter($files, '\file_exists') as $file) {
            @\unlink($file);
        }
    }

    /**
     * @param resource $im
     * @param resource $brand
     * @param object   $brandData
     * @return mixed
     */
    private function brandImage($im, $brand, $brandData)
    {
        if (!$brand
            || (isset($brandData->oBrandingEinstellung->nAktiv) && (int)$brandData->oBrandingEinstellung->nAktiv === 0)
            || !isset($brandData->oBrandingEinstellung->cBrandingBild)
        ) {
            return $im;
        }
        $brandingImage = \PFAD_ROOT . \PFAD_BRANDINGBILDER . $brandData->oBrandingEinstellung->cBrandingBild;
        if (!\file_exists($brandingImage)) {
            return $im;
        }
        $position     = $brandData->oBrandingEinstellung->cPosition;
        $transparency = $brandData->oBrandingEinstellung->dTransparenz;
        $brandingSize = $brandData->oBrandingEinstellung->dGroesse;
        $randabstand  = $brandData->oBrandingEinstellung->dRandabstand / 100;
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
            case 3:
                $im = \imagecreatefrompng($img);
                break;
            case 2:
            default:
                $im = \imagecreatefromjpeg($img);
                break;
        }

        if ($width === 0 && $height === 0) {
            [$width, $height] = $imgInfo;
        }
        $width  = (int)\round($width);
        $height = (int)\round($height);
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
            case 3:
                $im = \imagecreatefrompng($img);
                break;
            case 2:
            default:
                $im = \imagecreatefromjpeg($img);
                break;
        }

        if ($width === 0 && $height === 0) {
            [$width, $height] = $imgInfo;
        }

        $width  = (int)\round($width);
        $height = (int)\round($height);
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
