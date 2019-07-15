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
        $charValues = []; // Merkt sich alle MerkmalWerte die von der Wawi geschickt werden
        if (!isset($xml['merkmale']['tmerkmal']) || !\is_array($xml['merkmale']['tmerkmal'])) {
            return $charValues;
        }
        $attributes = $this->mapper->mapArray($xml['merkmale'], 'tmerkmal', 'mMerkmal');
        $mmCount    = \count($attributes);
        for ($i = 0; $i < $mmCount; $i++) {
            $charValues[$i] = new stdClass();
            if (isset($attributes[$i]->nMehrfachauswahl)) {
                if ($attributes[$i]->nMehrfachauswahl > 1) {
                    $attributes[$i]->nMehrfachauswahl = 1;
                }
            } else {
                $attributes[$i]->nMehrfachauswahl = 0;
            }
            $attribute                 = $this->saveImagePath($attributes[$i]->kMerkmal);
            $attributes[$i]->cBildpfad = $attribute->cBildpfad ?? '';
            $charValues[$i]->oMMW_arr  = [];

            if ($mmCount < 2) {
                $charData      = $xml['merkmale']['tmerkmal'];
                $charAttribute = $xml['merkmale']['tmerkmal attr'];
            } else {
                $charData      = $xml['merkmale']['tmerkmal'][$i];
                $charAttribute = $xml['merkmale']['tmerkmal'][$i . ' attr'];
            }

            $values = $this->mapper->mapArray(
                $charData,
                'tmerkmalwert',
                'mMerkmalWert'
            );
            if (\count($values) > 0) {
                $this->delete($charAttribute['kMerkmal'], 0);
            } else {
                $this->deleteCharacteristicOnly($charAttribute['kMerkmal']);
            }
            $this->upsertXML(
                $charData,
                'tmerkmalsprache',
                'mMerkmalSprache',
                'kMerkmal',
                'kSprache'
            );
            if (\count($values) > 0) {
                $mmwCountO = \count($values);
                for ($o = 0; $o < $mmwCountO; $o++) {
                    $item               = $charValues[$i]->oMMW_arr[$o];
                    $item->kMerkmalWert = $values[$o]->kMerkmalWert;
                    $item->kSprache_arr = [];

                    $source    = \count($values) < 2
                        ? $charData['tmerkmalwert']
                        : $charData['tmerkmalwert'][$o];
                    $localized = $this->mapper->mapArray($source, 'tmerkmalwertsprache', 'mMerkmalWertSprache');
                    foreach ($localized as $loc) {
                        $loc->kSprache     = (int)$loc->kSprache;
                        $loc->kMerkmalWert = (int)$loc->kMerkmalWert;
                        $this->db->delete(
                            'tseo',
                            ['kKey', 'cKey', 'kSprache'],
                            [
                                $loc->kMerkmalWert,
                                'kMerkmalWert',
                                $loc->kSprache
                            ]
                        );
                        $seo       = \trim($loc->cSeo)
                            ? Seo::getFlatSeoPath($loc->cSeo)
                            : Seo::getFlatSeoPath($loc->cWert);
                        $loc->cSeo = Seo::checkSeo(Seo::getSeo($seo));
                        $this->upsert(
                            'tmerkmalwertsprache',
                            [$loc],
                            'kMerkmalWert',
                            'kSprache'
                        );
                        $ins           = new stdClass();
                        $ins->cSeo     = $loc->cSeo;
                        $ins->cKey     = 'kMerkmalWert';
                        $ins->kKey     = $loc->kMerkmalWert;
                        $ins->kSprache = $loc->kSprache;
                        $this->db->insert('tseo', $ins);

                        if (!\in_array($loc->kSprache, $item->kSprache_arr, true)) {
                            $item->kSprache_arr[] = $loc->kSprache;
                        }

                        if ($loc->kSprache === $defaultLangID) {
                            $item->cNameSTD            = $loc->cWert;
                            $item->cSeoSTD             = $loc->cSeo;
                            $item->cMetaTitleSTD       = $loc->cMetaTitle;
                            $item->cMetaKeywordsSTD    = $loc->cMetaKeywords;
                            $item->cMetaDescriptionSTD = $loc->cMetaDescription;
                            $item->cBeschreibungSTD    = $loc->cBeschreibung;
                        }
                    }
                    $values[$o]->cBildpfad = $attribute->oMerkmalWert_arr[$values[$o]->kMerkmalWert];
                    $this->upsert('tmerkmalwert', [$values[$o]], 'kMerkmalWert');
                    $charValues[$i]->oMMW_arr[$o] = $item;
                }
            }
        }
        $this->upsert('tmerkmal', $attributes, 'kMerkmal');
        $this->addMissingCharacteristicValueSeo($charValues);
        $this->cache->flushTags([\CACHING_GROUP_ATTRIBUTE]);

        return $charValues;
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
                $source = $xml['merkmale']['tmerkmalwert'];
            } else {
                $source = $xml['merkmale']['tmerkmalwert'][$o];
            }
            $localized = $this->mapper->mapArray(
                $source,
                'tmerkmalwertsprache',
                'mMerkmalWertSprache'
            );
            foreach ($localized as $loc) {
                $loc->kSprache     = (int)$loc->kSprache;
                $loc->kMerkmalWert = (int)$loc->kMerkmalWert;
                $this->db->delete(
                    'tseo',
                    ['kKey', 'cKey', 'kSprache'],
                    [
                        $loc->kMerkmalWert,
                        'kMerkmalWert',
                        $loc->kSprache
                    ]
                );
                $seo = \trim($loc->cSeo)
                    ? Seo::getFlatSeoPath($loc->cSeo)
                    : Seo::getFlatSeoPath($loc->cWert);

                $loc->cSeo = Seo::checkSeo(Seo::getSeo($seo));
                $this->upsert('tmerkmalwertsprache', [$loc], 'kMerkmalWert', 'kSprache');
                $ins           = new stdClass();
                $ins->cSeo     = $loc->cSeo;
                $ins->cKey     = 'kMerkmalWert';
                $ins->kKey     = $loc->kMerkmalWert;
                $ins->kSprache = $loc->kSprache;
                $this->db->insert('tseo', $ins);

                if (!\in_array($loc->kSprache, $item->kSprache_arr, true)) {
                    $item->kSprache_arr[] = $loc->kSprache;
                }

                if (isset($loc->kSprache, $defaultLangID) && $loc->kSprache === $defaultLangID) {
                    $item->cNameSTD            = $loc->cWert;
                    $item->cSeoSTD             = $loc->cSeo;
                    $item->cMetaTitleSTD       = $loc->cMetaTitle;
                    $item->cMetaKeywordsSTD    = $loc->cMetaKeywords;
                    $item->cMetaDescriptionSTD = $loc->cMetaDescription;
                    $item->cBeschreibungSTD    = $loc->cBeschreibung;
                }
            }
            $image = $this->db->select('tmerkmalwertbild', 'kMerkmalWert', (int)$mapped[$o]->kMerkmalWert);

            $mapped[$o]->cBildpfad = $image->cBildpfad ?? '';
            $this->upsert('tmerkmalwert', [$mapped[$o]], 'kMerkmalWert');
            $charValues[$i]->oMMW_arr[$o] = $item;
        }
        $this->addMissingCharacteristicValueSeo($charValues);

        return $charValues;
    }

    /**
     * Geht $oMMW_arr durch welches vorher mit den mitgeschickten Merkmalwerten gefüllt wurde
     * und füllt die Seo Tabelle in den Sprachen, die nicht von der Wawi mitgeschickt wurden
     *
     * @param array $characteristics
     */
    private function addMissingCharacteristicValueSeo(array $characteristics): void
    {
        $languages = $this->db->query(
            'SELECT kSprache FROM tsprache ORDER BY kSprache',
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($characteristics as $attribute) {
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
