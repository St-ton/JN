<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\dbeS\Sync;

use JTL\DB\ReturnType;
use JTL\dbeS\Starter;
use JTL\Helpers\Seo;
use JTL\Language\LanguageHelper;
use stdClass;

/**
 * Class Characteristics
 * @package JTL\dbeS\Sync
 */
final class Characteristics extends AbstractSync
{
    /**
     * @param Starter $starter
     * @return mixed|null
     */
    public function handle(Starter $starter)
    {
        foreach ($starter->getXML() as $i => $item) {
            [$file, $xml] = [\key($item), \reset($item)];
            if (\strpos($file, 'del_merkmal.xml') !== false) {
                $this->handleDeletes($xml);
            } elseif (\strpos($file, 'merkmal.xml') !== false) {
                $this->handleInserts($xml);
            }
        }

        return null;
    }

    /**
     * @param array $xml
     */
    private function handleDeletes(array $xml): void
    {
        // Merkmal
        $attributes      = $xml['del_merkmale']['kMerkmal'] ?? [];
        $attributeValues = $xml['del_merkmalwerte']['kMerkmalWert'] ?? [];
        if (!\is_array($attributes)) {
            $attributes = [$attributes];
        }
        foreach (\array_filter($attributes, '\is_numeric') as $attributeID) {
            $this->delete((int)$attributeID);
        }
        // MerkmalWert - WIRD ZURZEIT NOCH NICHT GENUTZT WEGEN MOEGLICHER INKONSISTENZ
        if (!\is_array($attributeValues)) {
            $attributeValues = [$attributeValues];
        }
        foreach (\array_filter($attributeValues, '\is_numeric') as $attributeValueID) {
            $this->deleteCharacteristicValue((int)$attributeValueID);
        }
        $this->cache->flushTags([\CACHING_GROUP_ATTRIBUTE]);
    }

    /**
     * @param array $xml
     */
    private function handleInserts(array $xml): void
    {
        $defaultLangID = LanguageHelper::getDefaultLanguage()->kSprache ?? -1;
        $charValues    = $this->insertCharacteristics($xml, $defaultLangID);
        $this->insertCharacteristicValues($xml, $charValues, $defaultLangID);
    }

    /**
     * @param array $xml
     * @param int   $defaultLangID
     * @return array
     */
    private function insertCharacteristics(array $xml, int $defaultLangID): array
    {
        $attributeValues = []; // Merkt sich alle MerkmalWerte die von der Wawi geschickt werden
        if (!isset($xml['merkmale']['tmerkmal']) || !\is_array($xml['merkmale']['tmerkmal'])) {
            return $attributeValues;
        }
        $attributes = $this->mapper->mapArray($xml['merkmale'], 'tmerkmal', 'mMerkmal');
        $mmCount    = \count($attributes);
        for ($i = 0; $i < $mmCount; $i++) {
            $attributeValues[$i] = new stdClass();
            if (isset($attributes[$i]->nMehrfachauswahl)) {
                if ($attributes[$i]->nMehrfachauswahl > 1) {
                    $attributes[$i]->nMehrfachauswahl = 1;
                }
            } else {
                $attributes[$i]->nMehrfachauswahl = 0;
            }
            $attribute                     = $this->saveImagePath($attributes[$i]->kMerkmal);
            $attributes[$i]->cBildpfad     = $attribute->cBildpfad ?? '';
            $attributeValues[$i]->oMMW_arr = [];

            if ($mmCount < 2) {
                $attrValues = $this->mapper->mapArray(
                    $xml['merkmale']['tmerkmal'],
                    'tmerkmalwert',
                    'mMerkmalWert'
                );
                if (\count($attrValues) > 0) {
                    $this->delete($xml['merkmale']['tmerkmal attr']['kMerkmal'], 0);
                } else {
                    $this->deleteCharacteristicOnly($xml['merkmale']['tmerkmal attr']['kMerkmal']);
                }
                $this->updateXMLinDB(
                    $xml['merkmale']['tmerkmal'],
                    'tmerkmalsprache',
                    'mMerkmalSprache',
                    'kMerkmal',
                    'kSprache'
                );
                if (\count($attrValues) > 0) {
                    $mmwCountO = \count($attrValues);
                    for ($o = 0; $o < $mmwCountO; $o++) {
                        $item               = $attributeValues[$i]->oMMW_arr[$o];
                        $item->kMerkmalWert = $attrValues[$o]->kMerkmalWert;
                        $item->kSprache_arr = [];

                        if (\count($attrValues) < 2) {
                            $localized = $this->mapper->mapArray(
                                $xml['merkmale']['tmerkmal']['tmerkmalwert'],
                                'tmerkmalwertsprache',
                                'mMerkmalWertSprache'
                            );
                            $mmwsCount = \count($localized);
                            for ($j = 0; $j < $mmwsCount; ++$j) {
                                $localized[$j]->kSprache = (int)$localized[$j]->kSprache;
                                $this->db->delete(
                                    'tseo',
                                    ['kKey', 'cKey', 'kSprache'],
                                    [
                                        (int)$localized[$j]->kMerkmalWert,
                                        'kMerkmalWert',
                                        (int)$localized[$j]->kSprache
                                    ]
                                );
                                if (\trim($localized[$j]->cSeo)) {
                                    $seo = Seo::getFlatSeoPath($localized[$j]->cSeo);
                                } else {
                                    $seo = Seo::getFlatSeoPath($localized[$j]->cWert);
                                }
                                $localized[$j]->cSeo = Seo::getSeo($seo);
                                $localized[$j]->cSeo = Seo::checkSeo($localized[$j]->cSeo);
                                $this->upsert(
                                    'tmerkmalwertsprache',
                                    [$localized[$j]],
                                    'kMerkmalWert',
                                    'kSprache'
                                );
                                $ins           = new stdClass();
                                $ins->cSeo     = $localized[$j]->cSeo;
                                $ins->cKey     = 'kMerkmalWert';
                                $ins->kKey     = $localized[$j]->kMerkmalWert;
                                $ins->kSprache = $localized[$j]->kSprache;
                                $this->db->insert('tseo', $ins);

                                if (!\in_array($localized[$j]->kSprache, $item->kSprache_arr, true)) {
                                    $item->kSprache_arr[] = $localized[$j]->kSprache;
                                }

                                if ($localized[$j]->kSprache === $defaultLangID) {
                                    $item->cNameSTD            = $localized[$j]->cWert;
                                    $item->cSeoSTD             = $localized[$j]->cSeo;
                                    $item->cMetaTitleSTD       = $localized[$j]->cMetaTitle;
                                    $item->cMetaKeywordsSTD    = $localized[$j]->cMetaKeywords;
                                    $item->cMetaDescriptionSTD = $localized[$j]->cMetaDescription;
                                    $item->cBeschreibungSTD    = $localized[$j]->cBeschreibung;
                                }
                            }
                            $attrValues[$o]->cBildpfad = $attribute->oMerkmalWert_arr[$attrValues[$o]->kMerkmalWert];
                            $this->upsert('tmerkmalwert', [$attrValues[$o]], 'kMerkmalWert');
                        } else {
                            $localized  = $this->mapper->mapArray(
                                $xml['merkmale']['tmerkmal']['tmerkmalwert'][$o],
                                'tmerkmalwertsprache',
                                'mMerkmalWertSprache'
                            );
                            $mmwsaCount = \count($localized);
                            for ($j = 0; $j < $mmwsaCount; $j++) {
                                $localized[$j]->kSprache = (int)$localized[$j]->kSprache;
                                $this->db->delete(
                                    'tseo',
                                    ['kKey', 'cKey', 'kSprache'],
                                    [
                                        (int)$localized[$j]->kMerkmalWert,
                                        'kMerkmalWert',
                                        (int)$localized[$j]->kSprache
                                    ]
                                );
                                if (\trim($localized[$j]->cSeo)) {
                                    $seo = Seo::getFlatSeoPath($localized[$j]->cSeo);
                                } else {
                                    $seo = Seo::getFlatSeoPath($localized[$j]->cWert);
                                }
                                $localized[$j]->cSeo = Seo::getSeo($seo);
                                $localized[$j]->cSeo = Seo::checkSeo($localized[$j]->cSeo);
                                $this->upsert(
                                    'tmerkmalwertsprache',
                                    [$localized[$j]],
                                    'kMerkmalWert',
                                    'kSprache'
                                );
                                $ins           = new stdClass();
                                $ins->cSeo     = $localized[$j]->cSeo;
                                $ins->cKey     = 'kMerkmalWert';
                                $ins->kKey     = (int)$localized[$j]->kMerkmalWert;
                                $ins->kSprache = (int)$localized[$j]->kSprache;
                                $this->db->insert('tseo', $ins);

                                if (!\in_array($localized[$j]->kSprache, $item->kSprache_arr, true)) {
                                    $item->kSprache_arr[] = $localized[$j]->kSprache;
                                }

                                if ($localized[$j]->kSprache === $defaultLangID) {
                                    $item->cNameSTD            = $localized[$j]->cWert;
                                    $item->cSeoSTD             = $localized[$j]->cSeo;
                                    $item->cMetaTitleSTD       = $localized[$j]->cMetaTitle;
                                    $item->cMetaKeywordsSTD    = $localized[$j]->cMetaKeywords;
                                    $item->cMetaDescriptionSTD = $localized[$j]->cMetaDescription;
                                    $item->cBeschreibungSTD    = $localized[$j]->cBeschreibung;
                                }
                            }
                            //alten Bildpfad nehmen
                            $attrValues[$o]->cBildpfad = $attribute->oMerkmalWert_arr[$attrValues[$o]->kMerkmalWert];
                            $this->upsert('tmerkmalwert', [$attrValues[$o]], 'kMerkmalWert');
                        }
                        $attributeValues[$i]->oMMW_arr[$o] = $item;
                    }
                }
            } else {
                $attrValues = $this->mapper->mapArray(
                    $xml['merkmale']['tmerkmal'][$i],
                    'tmerkmalwert',
                    'mMerkmalWert'
                );
                if (\count($attrValues) > 0) {
                    $this->delete($xml['merkmale']['tmerkmal'][$i . ' attr']['kMerkmal'], 0);
                } else {
                    $this->deleteCharacteristicOnly($xml['merkmale']['tmerkmal'][$i . ' attr']['kMerkmal']);
                }

                $this->updateXMLinDB(
                    $xml['merkmale']['tmerkmal'][$i],
                    'tmerkmalsprache',
                    'mMerkmalSprache',
                    'kMerkmal',
                    'kSprache'
                );
                $mmwCount = \is_array($attrValues) ? \count($attrValues) : 0;
                if ($mmwCount > 0) {
                    for ($o = 0; $o < $mmwCount; $o++) {
                        $item               = $attributeValues[$i]->oMMW_arr[$o];
                        $item->kMerkmalWert = (int)$attrValues[$o]->kMerkmalWert;
                        $item->kSprache_arr = [];

                        if (\count($attrValues) < 2) {
                            $localized = $this->mapper->mapArray(
                                $xml['merkmale']['tmerkmal'][$i]['tmerkmalwert'],
                                'tmerkmalwertsprache',
                                'mMerkmalWertSprache'
                            );
                            $cnt       = \count($localized);
                            for ($j = 0; $j < $cnt; $j++) {
                                $localized[$j]->kSprache = (int)$localized[$j]->kSprache;
                                $this->db->delete(
                                    'tseo',
                                    ['kKey', 'cKey', 'kSprache'],
                                    [
                                        (int)$localized[$j]->kMerkmalWert,
                                        'kMerkmalWert',
                                        (int)$localized[$j]->kSprache
                                    ]
                                );
                                $seo = \trim($localized[$j]->cSeo)
                                    ? Seo::getFlatSeoPath($localized[$j]->cSeo)
                                    : Seo::getFlatSeoPath($localized[$j]->cWert);

                                $localized[$j]->cSeo = Seo::getSeo($seo);
                                $localized[$j]->cSeo = Seo::checkSeo($localized[$j]->cSeo);
                                $this->upsert(
                                    'tmerkmalwertsprache',
                                    [$localized[$j]],
                                    'kMerkmalWert',
                                    'kSprache'
                                );
                                $ins           = new stdClass();
                                $ins->cSeo     = $localized[$j]->cSeo;
                                $ins->cKey     = 'kMerkmalWert';
                                $ins->kKey     = (int)$localized[$j]->kMerkmalWert;
                                $ins->kSprache = (int)$localized[$j]->kSprache;
                                $this->db->insert('tseo', $ins);

                                if (!\in_array($localized[$j]->kSprache, $item->kSprache_arr, true)) {
                                    $item->kSprache_arr[] = $localized[$j]->kSprache;
                                }

                                if ($localized[$j]->kSprache === $defaultLangID) {
                                    $item->cNameSTD            = $localized[$j]->cWert;
                                    $item->cSeoSTD             = $localized[$j]->cSeo;
                                    $item->cMetaTitleSTD       = $localized[$j]->cMetaTitle;
                                    $item->cMetaKeywordsSTD    = $localized[$j]->cMetaKeywords;
                                    $item->cMetaDescriptionSTD = $localized[$j]->cMetaDescription;
                                    $item->cBeschreibungSTD    = $localized[$j]->cBeschreibung;
                                }
                            }
                            //alten Bildpfad nehmen
                            $attrValues[$o]->cBildpfad = $attribute->oMerkmalWert_arr[$attrValues[$o]->kMerkmalWert];
                            $this->upsert('tmerkmalwert', [$attrValues[$o]], 'kMerkmalWert');
                        } else {
                            $localized  = $this->mapper->mapArray(
                                $xml['merkmale']['tmerkmal'][$i]['tmerkmalwert'][$o],
                                'tmerkmalwertsprache',
                                'mMerkmalWertSprache'
                            );
                            $mmwsaCount = \count($localized);
                            for ($j = 0; $j < $mmwsaCount; ++$j) {
                                $localized[$j]->kSprache = (int)$localized[$j]->kSprache;
                                $this->db->delete(
                                    'tseo',
                                    ['kKey', 'cKey', 'kSprache'],
                                    [
                                        (int)$localized[$j]->kMerkmalWert,
                                        'kMerkmalWert',
                                        (int)$localized[$j]->kSprache
                                    ]
                                );
                                if (\trim($localized[$j]->cSeo)) {
                                    $seo = Seo::getFlatSeoPath($localized[$j]->cSeo);
                                } else {
                                    $seo = Seo::getFlatSeoPath($localized[$j]->cWert);
                                }

                                $localized[$j]->cSeo = Seo::getSeo($seo);
                                $localized[$j]->cSeo = Seo::checkSeo($localized[$j]->cSeo);
                                $this->upsert(
                                    'tmerkmalwertsprache',
                                    [$localized[$j]],
                                    'kMerkmalWert',
                                    'kSprache'
                                );
                                $ins           = new stdClass();
                                $ins->cSeo     = $localized[$j]->cSeo;
                                $ins->cKey     = 'kMerkmalWert';
                                $ins->kKey     = $localized[$j]->kMerkmalWert;
                                $ins->kSprache = $localized[$j]->kSprache;
                                $this->db->insert('tseo', $ins);

                                if (!\in_array($localized[$j]->kSprache, $item->kSprache_arr, true)) {
                                    $item->kSprache_arr[] = $localized[$j]->kSprache;
                                }

                                if ($localized[$j]->kSprache === $defaultLangID) {
                                    $item->cNameSTD            = $localized[$j]->cWert;
                                    $item->cSeoSTD             = $localized[$j]->cSeo;
                                    $item->cMetaTitleSTD       = $localized[$j]->cMetaTitle;
                                    $item->cMetaKeywordsSTD    = $localized[$j]->cMetaKeywords;
                                    $item->cMetaDescriptionSTD = $localized[$j]->cMetaDescription;
                                    $item->cBeschreibungSTD    = $localized[$j]->cBeschreibung;
                                }
                            }
                            // alten Bildpfad nehmen
                            $attrValues[$o]->cBildpfad = $attribute->oMerkmalWert_arr[$attrValues[$o]->kMerkmalWert];
                            $this->upsert('tmerkmalwert', [$attrValues[$o]], 'kMerkmalWert');
                        }
                        $attributeValues[$i]->oMMW_arr[$o] = $item;
                    }
                }
            }
        }
        $this->upsert('tmerkmal', $attributes, 'kMerkmal');
        $this->fuelleFehlendeMMWInSeo($attributeValues);
        $this->cache->flushTags([\CACHING_GROUP_ATTRIBUTE]);

        return $attributeValues;
    }

    /**
     * @param array $xml
     * @param array $charValues
     * @param int   $defaultLangID
     * @return array
     */
    private function insertCharacteristicValues(array $xml, array $charValues, int $defaultLangID): array
    {
        // Kommen nur MerkmalWerte?
        if (!isset($xml['merkmale']['tmerkmalwert']) || !\is_array($xml['merkmale']['tmerkmalwert'])) {
            return [];
        }
        $mapped = $this->mapper->mapArray($xml['merkmale'], 'tmerkmalwert', 'mMerkmalWert');
        $i      = 0;

        if (!isset($charValues[$i])) {
            $charValues[$i] = new stdClass();
        }
        $charValues[$i]->oMMW_arr = [];
        $valueCount               = \count($mapped);
        for ($o = 0; $o < $valueCount; $o++) {
            $this->deleteCharacteristicValue($mapped[$o]->kMerkmalWert, true);
            $item               = new stdClass();
            $item->kMerkmalWert = $mapped[$o]->kMerkmalWert;
            $item->kSprache_arr = [];

            if (\count($mapped) < 2) {
                $localized = $this->mapper->mapArray(
                    $xml['merkmale']['tmerkmalwert'],
                    'tmerkmalwertsprache',
                    'mMerkmalWertSprache'
                );
            } else {
                $localized = $this->mapper->mapArray(
                    $xml['merkmale']['tmerkmalwert'][$o],
                    'tmerkmalwertsprache',
                    'mMerkmalWertSprache'
                );
            }
            $mmwsaCount = \count($localized);
            for ($j = 0; $j < $mmwsaCount; $j++) {
                $localized[$j]->kSprache     = (int)$localized[$j]->kSprache;
                $localized[$j]->kMerkmalWert = (int)$localized[$j]->kMerkmalWert;
                $this->db->delete(
                    'tseo',
                    ['kKey', 'cKey', 'kSprache'],
                    [
                        $localized[$j]->kMerkmalWert,
                        'kMerkmalWert',
                        $localized[$j]->kSprache
                    ]
                );
                $seo = \trim($localized[$j]->cSeo)
                    ? Seo::getFlatSeoPath($localized[$j]->cSeo)
                    : Seo::getFlatSeoPath($localized[$j]->cWert);

                $localized[$j]->cSeo = Seo::checkSeo(Seo::getSeo($seo));
                $this->upsert('tmerkmalwertsprache', [$localized[$j]], 'kMerkmalWert', 'kSprache');
                $ins           = new stdClass();
                $ins->cSeo     = $localized[$j]->cSeo;
                $ins->cKey     = 'kMerkmalWert';
                $ins->kKey     = $localized[$j]->kMerkmalWert;
                $ins->kSprache = $localized[$j]->kSprache;
                $this->db->insert('tseo', $ins);

                if (!\in_array($localized[$j]->kSprache, $item->kSprache_arr, true)) {
                    $item->kSprache_arr[] = $localized[$j]->kSprache;
                }

                if (isset($localized[$j]->kSprache, $defaultLangID)
                    && $localized[$j]->kSprache === $defaultLangID
                ) {
                    $item->cNameSTD            = $localized[$j]->cWert;
                    $item->cSeoSTD             = $localized[$j]->cSeo;
                    $item->cMetaTitleSTD       = $localized[$j]->cMetaTitle;
                    $item->cMetaKeywordsSTD    = $localized[$j]->cMetaKeywords;
                    $item->cMetaDescriptionSTD = $localized[$j]->cMetaDescription;
                    $item->cBeschreibungSTD    = $localized[$j]->cBeschreibung;
                }
            }
            $image = $this->db->select('tmerkmalwertbild', 'kMerkmalWert', (int)$mapped[$o]->kMerkmalWert);

            $mapped[$o]->cBildpfad = $image->cBildpfad ?? '';
            $this->upsert('tmerkmalwert', [$mapped[$o]], 'kMerkmalWert');
            $charValues[$i]->oMMW_arr[$o] = $item;
        }
        $this->fuelleFehlendeMMWInSeo($charValues);

        return $charValues;
    }

    /**
     * Geht $oMMW_arr durch welches vorher mit den mitgeschickten Merkmalwerten gefüllt wurde
     * und füllt die Seo Tabelle in den Sprachen, die nicht von der Wawi mitgeschickt wurden
     *
     * @param array $attributes
     */
    private function fuelleFehlendeMMWInSeo($attributes): void
    {
        if (!\is_array($attributes)) {
            return;
        }
        $languages = $this->db->query(
            'SELECT kSprache FROM tsprache ORDER BY kSprache',
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($attributes as $attribute) {
            foreach ($attribute->oMMW_arr as $attributeValue) {
                $attributeValue->kMerkmalWert = (int)$attributeValue->kMerkmalWert;
                foreach ($languages as $language) {
                    $language->kSprache = (int)$language->kSprache;
                    foreach ($attributeValue->kSprache_arr as $languageID) {
                        $languageID = (int)$languageID;
                        // Laufe alle gefüllten Sprachen durch
                        if ($languageID === $language->kSprache) {
                            continue 2;
                        }
                    }
                    // Sprache vom Shop wurde nicht von der Wawi mitgeschickt und muss somit in tseo nachgefüllt werden
                    $slug = Seo::checkSeo(Seo::getSeo($attributeValue->cNameSTD ?? ''));
                    $this->db->query(
                        "DELETE tmerkmalwertsprache, tseo FROM tmerkmalwertsprache
                        LEFT JOIN tseo
                            ON tseo.cKey = 'kMerkmalWert'
                                AND tseo.kKey = " . $attributeValue->kMerkmalWert . '
                                AND tseo.kSprache = ' . (int)$language->kSprache . '
                        WHERE tmerkmalwertsprache.kMerkmalWert = ' . $attributeValue->kMerkmalWert . '
                            AND tmerkmalwertsprache.kSprache = ' . $language->kSprache,
                        ReturnType::DEFAULT
                    );
                    //@todo: 1062: Duplicate entry '' for key 'PRIMARY'
                    if ($slug !== '' && $slug !== null) {
                        $seo           = new stdClass();
                        $seo->cSeo     = $slug;
                        $seo->cKey     = 'kMerkmalWert';
                        $seo->kKey     = (int)$attributeValue->kMerkmalWert;
                        $seo->kSprache = $language->kSprache;
                        $this->db->insert('tseo', $seo);
                        $localized                   = new stdClass();
                        $localized->kMerkmalWert     = $attributeValue->kMerkmalWert;
                        $localized->kSprache         = $language->kSprache;
                        $localized->cWert            = $attributeValue->cNameSTD ?? '';
                        $localized->cSeo             = $seo->cSeo ?? '';
                        $localized->cMetaTitle       = $attributeValue->cMetaTitleSTD ?? '';
                        $localized->cMetaKeywords    = $attributeValue->cMetaKeywordsSTD ?? '';
                        $localized->cMetaDescription = $attributeValue->cMetaDescriptionSTD ?? '';
                        $localized->cBeschreibung    = $attributeValue->cBeschreibungSTD ?? '';
                        $this->db->insert('tmerkmalwertsprache', $localized);
                    }
                }
            }
        }
    }

    /**
     * @param int $id
     * @param int $update
     */
    private function delete(int $id, $update = 1): void
    {
        if ($id < 1) {
            return;
        }
        $this->db->query(
            "DELETE tseo
            FROM tseo
            INNER JOIN tmerkmalwert
                ON tmerkmalwert.kMerkmalWert = tseo.kKey
            INNER JOIN tmerkmal
                ON tmerkmal.kMerkmal = tmerkmalwert.kMerkmal
            WHERE tseo.cKey = 'kMerkmalWert'
                AND tmerkmal.kMerkmal = " . $id,
            ReturnType::DEFAULT
        );

        if ($update) {
            $this->db->delete('tartikelmerkmal', 'kMerkmal', $id);
        }
        $this->db->delete('tmerkmal', 'kMerkmal', $id);
        $this->db->delete('tmerkmalsprache', 'kMerkmal', $id);
        foreach ($this->db->selectAll('tmerkmalwert', 'kMerkmal', $id, 'kMerkmalWert') as $value) {
            $this->db->delete('tmerkmalwertsprache', 'kMerkmalWert', (int)$value->kMerkmalWert);
            $this->db->delete('tmerkmalwertbild', 'kMerkmalWert', (int)$value->kMerkmalWert);
        }
        $this->db->delete('tmerkmalwert', 'kMerkmal', $id);
    }

    /**
     * @param int $id
     */
    private function deleteCharacteristicOnly(int $id): void
    {
        if ($id < 1) {
            return;
        }
        $this->db->query(
            "DELETE tseo
            FROM tseo
            INNER JOIN tmerkmalwert
                ON tmerkmalwert.kMerkmalWert = tseo.kKey
            INNER JOIN tmerkmal
                ON tmerkmal.kMerkmal = tmerkmalwert.kMerkmal
            WHERE tseo.cKey = 'kMerkmalWert'
                AND tmerkmal.kMerkmal = " . $id,
            ReturnType::DEFAULT
        );

        $this->db->delete('tmerkmal', 'kMerkmal', $id);
        $this->db->delete('tmerkmalsprache', 'kMerkmal', $id);
    }

    /**
     * @param int  $attributeValueID
     * @param bool $isInsert
     */
    private function deleteCharacteristicValue(int $attributeValueID, $isInsert = false): void
    {
        if ($attributeValueID < 1) {
            return;
        }
        $this->db->delete('tseo', ['cKey', 'kKey'], ['kMerkmalWert', $attributeValueID]);
        // Hat das Merkmal vor dem Loeschen noch mehr als einen Wert?
        // Wenn nein => nach dem Loeschen auch das Merkmal loeschen
        $count = $this->db->query(
            'SELECT COUNT(*) AS nAnzahl, kMerkmal
            FROM tmerkmalwert
            WHERE kMerkmal = (
                SELECT kMerkmal
                    FROM tmerkmalwert
                    WHERE kMerkmalWert = ' . $attributeValueID . ')',
            ReturnType::SINGLE_OBJECT
        );

        $this->db->query(
            'DELETE tmerkmalwert, tmerkmalwertsprache
            FROM tmerkmalwert
            JOIN tmerkmalwertsprache
                ON tmerkmalwertsprache.kMerkmalWert = tmerkmalwert.kMerkmalWert
            WHERE tmerkmalwert.kMerkmalWert = ' . $attributeValueID,
            ReturnType::DEFAULT
        );
        // Das Merkmal hat keine MerkmalWerte mehr => auch loeschen
        if (!$isInsert && (int)$count->nAnzahl === 1) {
            $this->delete($count->kMerkmal);
        }
    }

    /**
     * @param int $attributeID
     * @return stdClass
     */
    private function saveImagePath(int $attributeID): stdClass
    {
        $attribute                   = new stdClass();
        $attribute->oMerkmalWert_arr = [];
        if ($attributeID > 0) {
            $tmp = $this->db->select('tmerkmal', 'kMerkmal', $attributeID);
            if (isset($tmp->kMerkmal) && $tmp->kMerkmal > 0) {
                $attribute->kMerkmal  = $tmp->kMerkmal;
                $attribute->cBildpfad = $tmp->cBildpfad;
            }
            $attributeValues = $this->db->selectAll(
                'tmerkmalwert',
                'kMerkmal',
                $attributeID,
                'kMerkmalWert, cBildpfad'
            );
            foreach ($attributeValues as $oMerkmalWert) {
                $attribute->oMerkmalWert_arr[$oMerkmalWert->kMerkmalWert] = $oMerkmalWert->cBildpfad;
            }
        }

        return $attribute;
    }
}
