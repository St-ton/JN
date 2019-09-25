<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\dbeS\Sync;

use JTL\DB\ReturnType;
use JTL\dbeS\Starter;
use JTL\Media\Image;
use JTL\Media\Image\Manufacturer;
use JTL\Media\Image\Category;
use JTL\Media\Image\Characteristic;
use JTL\Media\Image\CharacteristicValue;
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
                    $this->handleInserts($xml, $starter->getUnzipPath());
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
     */
    private function handleInserts($xml, string $unzipPath): void
    {
        if (!\is_array($xml['bilder'])) {
            return;
        }
        $categoryImages     = $this->mapper->mapArray($xml['bilder'], 'tkategoriepict', 'mKategoriePict');
        $propertyImages     = $this->mapper->mapArray($xml['bilder'], 'teigenschaftwertpict', 'mEigenschaftWertPict');
        $manufacturerImages = $this->mapper->mapArray($xml['bilder'], 'therstellerbild', 'mEigenschaftWertPict');
        $charImages         = $this->mapper->mapArray($xml['bilder'], 'tMerkmalbild', 'mEigenschaftWertPict');
        $charValImages      = $this->mapper->mapArray($xml['bilder'], 'tmerkmalwertbild', 'mEigenschaftWertPict');
        $configGroupImages  = $this->mapper->mapArray($xml['bilder'], 'tkonfiggruppebild', 'mKonfiggruppePict');

        \executeHook(\HOOK_BILDER_XML_BEARBEITE, [
            'Pfad'             => $unzipPath,
            'Kategorie'        => &$categoryImages,
            'Eigenschaftswert' => &$propertyImages,
            'Hersteller'       => &$manufacturerImages,
            'Merkmalwert'      => &$charValImages,
            'Merkmal'          => &$charImages,
            'Konfiggruppe'     => &$configGroupImages
        ]);
        $this->unzipPath = $unzipPath;

        $this->handleCategoryImages($categoryImages);
        $this->handlePropertyImages($propertyImages);
        $this->handleManufacturerImages($manufacturerImages);
        $this->handleCharacteristicImages($charImages);
        $this->handleCharacteristicValueImages($charValImages);
        $this->handleConfigGroupImages($configGroupImages);
        if (\count($charImages) > 0 || \count($charValImages) > 0) {
            $this->cache->flushTags([\CACHING_GROUP_ATTRIBUTE, \CACHING_GROUP_FILTER_CHARACTERISTIC]);
        }

        \executeHook(\HOOK_BILDER_XML_BEARBEITE_ENDE, [
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
            $original  = $this->unzipPath . $item->cBildPfad;
            $extension = $this->getExtension($original);
            if (!$extension) {
                $this->logger->error(
                    'Bildformat des Konfiggruppenbildes konnte nicht ermittelt werden. Datei ' .
                    $original . ' keine Bilddatei?'
                );
                continue;
            }
            $item->cBildPfad = $item->kKonfiggruppe . '.' . $extension;
            $item->cBildPfad = $this->getNewFilename($item->cBildPfad);

            $branding                               = new stdClass();
            $branding->oBrandingEinstellung         = new stdClass();
            $branding->oBrandingEinstellung->nAktiv = 0;
            \copy($original, \PFAD_ROOT . \STORAGE_CONFIGGROUPS . $image->cPfad);
            if ($this->createThumbnail(
                $branding,
                $original,
                \PFAD_KONFIGURATOR_KLEIN . $item->cBildPfad,
                $this->config['bilder']['bilder_konfiggruppe_klein_breite'],
                $this->config['bilder']['bilder_konfiggruppe_klein_hoehe'],
                $this->config['bilder']['bilder_jpg_quali'],
                true,
                $this->config['bilder']['container_verwenden']
            )) {
                $this->db->update(
                    'tkonfiggruppe',
                    'kKonfiggruppe',
                    (int)$item->kKonfiggruppe,
                    (object)['cBildPfad' => $item->cBildPfad]
                );
            }
            \unlink($original);
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
            $original  = $this->unzipPath . $image->cPfad;
            $extension = $this->getExtension($original);
            if (!$extension) {
                $this->logger->error(
                    'Bildformat des Merkmalwertbildes konnte nicht ermittelt werden. Datei ' .
                    $original . ' keine Bilddatei?'
                );
                continue;
            }
            $image->cPfad .= '.' . $extension;
            $image->cPfad  = $this->getNewFilename($image->cPfad);
            \copy($original, \PFAD_ROOT . \STORAGE_CHARACTERISTIC_VALUES . $image->cPfad);
            // @todo: why createThumbnail with branding config and createBrandedThumbnail??????????????????????
            // ??????????????????????
            // ??????????????????????
            // ??????????????????????
            // ??????????????????????
            $this->createThumbnail(
                $this->brandingConfig[Image::TYPE_CHARACTERISTIC_VALUE],
                $original,
                \PFAD_MERKMALWERTBILDER_NORMAL . $image->cPfad,
                $this->config['bilder']['bilder_merkmalwert_normal_breite'],
                $this->config['bilder']['bilder_merkmalwert_normal_hoehe'],
                $this->config['bilder']['bilder_jpg_quali'],
                true,
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
            $original  = $this->unzipPath . $image->cPfad;
            $extension = $this->getExtension($original);
            if (!$extension) {
                $this->logger->error(
                    'Bildformat des Merkmalbildes konnte nicht ermittelt werden. Datei ' .
                    $original . ' keine Bilddatei?'
                );
                continue;
            }
            $image->cPfad .= '.' . $extension;
            $image->cPfad  = $this->getNewFilename($image->cPfad);
            \copy($original, \PFAD_ROOT . \STORAGE_CHARACTERISTICS . $image->cPfad);
            $this->createThumbnail(
                $this->brandingConfig[Image::TYPE_CHARACTERISTIC],
                $original,
                \PFAD_MERKMALBILDER_NORMAL . $image->cPfad,
                $this->config['bilder']['bilder_merkmal_normal_breite'],
                $this->config['bilder']['bilder_merkmal_normal_hoehe'],
                $this->config['bilder']['bilder_jpg_quali'],
                true,
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
            $original  = $this->unzipPath . $image->cPfad;
            $extension = $this->getExtension($original);
            if (!$extension) {
                $this->logger->error(
                    'Bildformat des Herstellerbildes konnte nicht ermittelt werden. Datei ' .
                    $original . ' keine Bilddatei?'
                );
                continue;
            }
            $manufacturer = $this->db->queryPrepared(
                'SELECT cSeo
                    FROM thersteller
                    WHERE kHersteller = :mid',
                ['mid' => $image->kHersteller],
                ReturnType::SINGLE_OBJECT
            );
            if (!empty($manufacturer->cSeo)) {
                $image->cPfad = \str_replace('/', '_', $manufacturer->cSeo . '.' . $extension);
            } elseif (\stripos(\strrev($image->cPfad), \strrev($extension)) !== 0) {
                $image->cPfad .= '.' . $extension;
            }
            $image->cPfad = $this->getNewFilename($image->cPfad);
            \copy($original, \PFAD_ROOT . \STORAGE_MANUFACTURERS . $image->cPfad);
            $this->createThumbnail(
                $this->brandingConfig[Image::TYPE_MANUFACTURER],
                $original,
                \PFAD_HERSTELLERBILDER_NORMAL . $image->cPfad,
                $this->config['bilder']['bilder_hersteller_normal_breite'],
                $this->config['bilder']['bilder_hersteller_normal_hoehe'],
                $this->config['bilder']['bilder_jpg_quali'],
                true,
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
                    $image->kHersteller,
                    (object)['cBildpfad' => $image->cPfad]
                );
            }
            $cacheTags = [];
            foreach ($this->db->selectAll(
                'tartikel',
                'kHersteller',
                $image->kHersteller,
                'kArtikel'
            ) as $product) {
                $cacheTags[] = \CACHING_GROUP_ARTICLE . '_' . $product->kArtikel;
            }
            $this->cache->flushTags($cacheTags);
            \unlink($original);
        }
    }

    /**
     * @param array $images
     */
    private function handlePropertyImages(array $images): void
    {
        foreach ($images as $image) {
            if (empty($image->cPfad)) {
                continue;
            }
            $original  = $this->unzipPath . $image->cPfad;
            $extension = $this->getExtension($original);
            if (!$extension) {
                $this->logger->error(
                    'Bildformat des Eigenschaftwertbildes konnte nicht ermittelt werden. Datei ' .
                    $original . ' keine Bilddatei?'
                );
                continue;
            }
            $image->cPfad = $this->getPropertiesImageName($image, $extension);
            $image->cPfad = $this->getNewFilename($image->cPfad);
            \copy($original, \PFAD_ROOT . \STORAGE_VARIATIONS . $image->cPfad);
            $this->createThumbnail(
                $this->brandingConfig[Image::TYPE_VARIATION],
                $original,
                \PFAD_VARIATIONSBILDER_GROSS . $image->cPfad,
                $this->config['bilder']['bilder_variationen_gross_breite'],
                $this->config['bilder']['bilder_variationen_gross_hoehe'],
                $this->config['bilder']['bilder_jpg_quali'],
                true,
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
     * @param array $images
     */
    private function handleCategoryImages(array $images): void
    {
        foreach ($images as $image) {
            if (empty($image->cPfad)) {
                continue;
            }
            $original  = $this->unzipPath . $image->cPfad;
            $extension = $this->getExtension($original);
            if (!$extension) {
                $this->logger->error(
                    'Bildformat des Kategoriebildes konnte nicht ermittelt werden. Datei ' .
                    $original . ' keine Bilddatei?'
                );
                continue;
            }
            $image->cPfad = $this->getCategoryImageName($image, $extension);
            $image->cPfad = $this->getNewFilename($image->cPfad);
            \copy($original, \PFAD_ROOT . \STORAGE_CATEGORIES . $image->cPfad);
            if ($this->createThumbnail(
                $this->brandingConfig[Image::TYPE_CATEGORY],
                $original,
                \PFAD_KATEGORIEBILDER . $image->cPfad,
                $this->config['bilder']['bilder_kategorien_breite'],
                $this->config['bilder']['bilder_kategorien_hoehe'],
                $this->config['bilder']['bilder_jpg_quali'],
                true,
                $this->config['bilder']['container_verwenden']
            )) {
                $this->upsert('tkategoriepict', [$image], 'kKategorie');
            }
            \unlink($original);
        }
    }

    /**
     * @param object $image
     * @param string $extension
     * @return string
     */
    private function getPropertiesImageName($image, string $extension): string
    {
        if (!$image->kEigenschaftWert || !$this->config['bilder']['bilder_variation_namen']) {
            return (\stripos(\strrev($image->cPfad), \strrev($extension)) === 0)
                ? $image->cPfad
                : $image->cPfad . '.' . $extension;
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
                    $product = $this->db->queryPrepared(
                        "SELECT tartikel.cArtNr, tartikel.cBarcode, tartikel.cName, tseo.cSeo
                            FROM teigenschaftwert, teigenschaft, tartikel
                            JOIN tseo
                                ON tseo.cKey = 'kArtikel'
                                AND tseo.kKey = tartikel.kArtikel
                            JOIN tsprache
                                ON tsprache.kSprache = tseo.kSprache
                            WHERE teigenschaftwert.kEigenschaft = teigenschaft.kEigenschaft
                                AND tsprache.cShopStandard = 'Y'
                                AND teigenschaft.kArtikel = tartikel.kArtikel
                                AND teigenschaftwert.kEigenschaftWert = :cid",
                        ['cid' => (int)$image->kEigenschaftWert],
                        ReturnType::SINGLE_OBJECT
                    );
                    if (!empty($product->cArtNr) && !empty($propValue->cArtNr)) {
                        $imageName = $this->convertUmlauts($product->cArtNr) .
                            '_' .
                            $this->convertUmlauts($propValue->cArtNr);
                    }
                    break;

                case 3:
                    $product = $this->db->queryPrepared(
                        "SELECT tartikel.cArtNr, tartikel.cBarcode, tartikel.cName, tseo.cSeo
                            FROM teigenschaftwert, teigenschaft, tartikel
                            JOIN tseo
                                ON tseo.cKey = 'kArtikel'
                                AND tseo.kKey = tartikel.kArtikel
                            JOIN tsprache
                                ON tsprache.kSprache = tseo.kSprache
                            WHERE teigenschaftwert.kEigenschaft = teigenschaft.kEigenschaft
                                AND tsprache.cShopStandard = 'Y'
                                AND teigenschaft.kArtikel = tartikel.kArtikel
                                AND teigenschaftwert.kEigenschaftWert = :cid",
                        ['cid' => $image->kEigenschaftWert],
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

        return $this->removeSpecialChars($imageName) . '.' . $extension;
    }

    /**
     * @param object $image
     * @param string $ext
     * @return string
     */
    private function getCategoryImageName($image, string $ext): string
    {
        if (!$image->kKategorie || !$this->config['bilder']['bilder_kategorie_namen']) {
            return (\stripos(\strrev($image->cPfad), \strrev($ext)) === 0)
                ? $image->cPfad
                : $image->cPfad . '.' . $ext;
        }
        $attr = $this->db->select(
            'tkategorieattribut',
            'kKategorie',
            (int)$image->kKategorie,
            'cName',
            \KAT_ATTRIBUT_BILDNAME);
        if (!empty($attr->cWert)) {
            return $attr->cWert . '.' . $ext;
        }
        $category  = $this->db->queryPrepared(
            "SELECT tseo.cSeo, tkategorie.cName
            FROM tkategorie
            JOIN JOIN tseo
                ON tseo.cKey = 'kKategorie'
                AND tseo.kKey = tkategorie.kKategorie
            JOIN tsprache
                ON tsprache.kSprache = tseo.kSprache
            WHERE tkategorie.kKategorie = :cid
                AND tsprache.cShopStandard = 'Y'",
            ['cid' => (int)$image->kKategorie],
            ReturnType::SINGLE_OBJECT
        );
        $imageName = $image->cPfad;
        if ($category->cName) {
            switch ($this->config['bilder']['bilder_kategorie_namen']) {
                case 1:
                    $imageName = $this->removeSpecialChars($category->cSeo ?: $this->convertUmlauts($category->cName))
                        . '.' . $ext;
                    break;
                case 0:
                default:
                    return $image->cPfad . '.' . $ext;
            }
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
        string $imgFilename,
        string $targetImage,
        int $targetWidth,
        int $targetheight,
        int $quality = 80,
        string $container = 'N'
    ): int {
        $enlarge          = $this->config['bilder']['bilder_skalieren'] === 'Y';
        $ret              = 0;
        $extension        = $this->getNewExtension($targetImage);
        [$width, $height] = \getimagesize($imgFilename);
        if ($width > 0 && $height > 0) {
            if (!$enlarge && $width < $targetWidth && $height < $targetheight) {
                if ($container === 'Y') {
                    $im = $this->imageloadContainer($imgFilename, $width, $height, $targetWidth, $targetheight);
                } else {
                    $im = $this->imageloadAlpha($imgFilename, $width, $height);
                }
                $this->saveImage($im, $extension, \PFAD_ROOT . $targetImage, $quality);
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
            if ($this->saveImage($im, $extension, \PFAD_ROOT . $targetImage, $quality)) {
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
     * @param bool         $brand
     * @param string       $container
     * @return int
     */
    private function createThumbnail(
        $branding,
        string $imgFilename,
        string $target,
        int $targetWidth,
        int $targetHeight,
        int $quality = 80,
        bool $brand = false,
        $container = 'N'
    ): int {
        $enlarge   = $this->config['bilder']['bilder_skalieren'] === 'Y';
        $ret       = 0;
        $extension = $this->getNewExtension($target);
        $im        = $this->imageloadAlpha($imgFilename);
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
            $this->saveImage($this->brandImage($im, $brand, $branding), $extension, \PFAD_ROOT . $target, $quality);
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
        if ($this->saveImage($this->brandImage($image, $brand, $branding), $extension, \PFAD_ROOT . $target, $quality)) {
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
            'Kategorie'        => $xml['del_bilder']['kKategoriePict'] ?? [],
            'KategoriePK'      => $xml['del_bilder']['kKategorie'] ?? [],
            'Eigenschaftswert' => $xml['del_bilder']['kEigenschaftWertPict'] ?? [],
            'Hersteller'       => $xml['del_bilder']['kHersteller'] ?? [],
            'Merkmal'          => $xml['del_bilder']['kMerkmal'] ?? [],
            'Merkmalwert'      => $xml['del_bilder']['kMerkmalWert'] ?? [],
        ]);
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
        $ids = \array_filter(\array_map('\intval', $source));
        foreach ($ids as $id) {
            $this->db->delete('tkategoriepict', 'kKategorie', $id);
        }
        Category::clearCache($ids);
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
        $ids = \array_filter(\array_map('\intval', $source));
        foreach ($ids as $manufacturerID) {
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
        Manufacturer::clearCache($ids);
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
        $ids = \array_filter(\array_map('\intval', $source));
        foreach ($ids as $attrID) {
            $this->db->update(
                'tmerkmal',
                'kMerkmal',
                (int)$attrID,
                (object)['cBildpfad' => '']
            );
        }
        Characteristic::clearCache($ids);
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
        $ids = \array_filter(\array_map('\intval', $source));
        foreach ($ids as $attrValID) {
            $this->db->update(
                'tmerkmalwert',
                'kMerkmalWert',
                (int)$attrValID,
                (object)['cBildpfad' => '']
            );
            $this->db->delete('tmerkmalwertbild', 'kMerkmalWert', (int)$attrValID);
        }
        CharacteristicValue::clearCache($ids);
    }

    /**
     * @param resource $im
     * @param bool     $brand
     * @param object   $brandData
     * @return mixed
     */
    private function brandImage($im, bool $brand, $brandData)
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
     * @param string $filename
     * @return string|null
     */
    private function getExtension(string $filename): ?string
    {
        if (!\file_exists($filename)) {
            return null;
        }
        $size = \getimagesize($filename);
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
                $ext = null;
                break;
        }

        return $ext;
    }

    /**
     * @param string|null $sourcePath
     * @return string
     */
    private function getNewExtension(string $sourcePath = null): string
    {
        $config = \mb_convert_case($this->config['bilder']['bilder_dateiformat'], \MB_CASE_LOWER);

        return $config === 'auto'
            ? \pathinfo($sourcePath)['extension'] ?? 'jpg'
            : $config;
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
        if ($this->getNewExtension($img) === 'jpg') {
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
        if ($this->getNewExtension($img) === 'jpg') {
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
        return \substr($path, 0, -3) . $this->getNewExtension($path);
    }

    /**
     * @param resource $im
     * @param string   $format
     * @param string   $path
     * @param int      $quality
     * @return bool
     */
    private function saveImage($im, string $format, string $path, int $quality = 80): bool
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
