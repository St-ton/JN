<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\dbeS\Sync;

use JTL\DB\ReturnType;
use JTL\dbeS\Starter;
use JTL\Helpers\Seo;
use JTL\Sprache;
use stdClass;

/**
 * Class Attributes
 * @package JTL\dbeS\Sync
 */
final class Attributes extends AbstractSync
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
        if (isset($xml['del_merkmale']['kMerkmal']) && \is_array($xml['del_merkmale']['kMerkmal'])) {
            foreach ($xml['del_merkmale']['kMerkmal'] as $attributeID) {
                if ((int)$attributeID > 0) {
                    $this->delete((int)$attributeID);
                }
            }
        } elseif (isset($xml['del_merkmale']['kMerkmal']) && (int)$xml['del_merkmale']['kMerkmal'] > 0) {
            $this->delete((int)$xml['del_merkmale']['kMerkmal']);
        }
        // MerkmalWert
        // WIRD ZURZEIT NOCH NICHT GENUTZT WEGEN MOEGLICHER INKONSISTENZ
        if (isset($xml['del_merkmalwerte']['kMerkmalWert']) && \is_array($xml['del_merkmalwerte']['kMerkmalWert'])) {
            foreach ($xml['del_merkmalwerte']['kMerkmalWert'] as $attributeValueID) {
                if ((int)$attributeValueID > 0) {
                    $this->deleteAttributeValue((int)$attributeValueID);
                }
            }
        } elseif (isset($xml['del_merkmalwerte']['kMerkmalWert'])
            && (int)$xml['del_merkmalwerte']['kMerkmalWert'] > 0
        ) {
            $this->deleteAttributeValue((int)$xml['del_merkmalwerte']['kMerkmalWert']);
        }
        $this->cache->flushTags([\CACHING_GROUP_ATTRIBUTE]);
    }

    /**
     * @param array $xml
     */
    private function handleInserts(array $xml): void
    {
        $attributeValues = []; // Merkt sich alle MerkmalWerte die von der Wawi geschickt werden
        $defaultLanguage = Sprache::getDefaultLanguage();
        if (isset($xml['merkmale']['tmerkmal']) && \is_array($xml['merkmale']['tmerkmal'])) {
            $attributes = $this->mapper->mapArray($xml['merkmale'], 'tmerkmal', 'mMerkmal');
            $mmCount    = \count($attributes);
            for ($i = 0; $i < $mmCount; $i++) {
                if (!isset($attributeValues[$i]) || $attributeValues[$i] === null) {
                    $attributeValues[$i] = new stdClass();
                }
                if (isset($attributes[$i]->nMehrfachauswahl)) {
                    if ($attributes[$i]->nMehrfachauswahl > 1) {
                        $attributes[$i]->nMehrfachauswahl = 1;
                    }
                } else {
                    $attributes[$i]->nMehrfachauswahl = 0;
                }
                $oMerkmal                      = $this->merkeBildPfad($attributes[$i]->kMerkmal);
                $attributes[$i]->cBildpfad     = $oMerkmal->cBildpfad ?? '';
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
                        $this->deleteOnlyAttribute($xml['merkmale']['tmerkmal attr']['kMerkmal']);
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

                                    if ($localized[$j]->kSprache === (int)$defaultLanguage->kSprache) {
                                        $item->cNameSTD            = $localized[$j]->cWert;
                                        $item->cSeoSTD             = $localized[$j]->cSeo;
                                        $item->cMetaTitleSTD       = $localized[$j]->cMetaTitle;
                                        $item->cMetaKeywordsSTD    = $localized[$j]->cMetaKeywords;
                                        $item->cMetaDescriptionSTD = $localized[$j]->cMetaDescription;
                                        $item->cBeschreibungSTD    = $localized[$j]->cBeschreibung;
                                    }
                                }
                                $attrValues[$o]->cBildpfad = $oMerkmal->oMerkmalWert_arr[$attrValues[$o]->kMerkmalWert];
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

                                    if ($localized[$j]->kSprache === $defaultLanguage->kSprache) {
                                        $item->cNameSTD            = $localized[$j]->cWert;
                                        $item->cSeoSTD             = $localized[$j]->cSeo;
                                        $item->cMetaTitleSTD       = $localized[$j]->cMetaTitle;
                                        $item->cMetaKeywordsSTD    = $localized[$j]->cMetaKeywords;
                                        $item->cMetaDescriptionSTD = $localized[$j]->cMetaDescription;
                                        $item->cBeschreibungSTD    = $localized[$j]->cBeschreibung;
                                    }
                                }
                                //alten Bildpfad nehmen
                                $attrValues[$o]->cBildpfad = $oMerkmal->oMerkmalWert_arr[$attrValues[$o]->kMerkmalWert];
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
                        $this->deleteOnlyAttribute($xml['merkmale']['tmerkmal'][$i . ' attr']['kMerkmal']);
                    }

                    $this->updateXMLinDB(
                        $xml['merkmale']['tmerkmal'][$i],
                        'tmerkmalsprache',
                        'mMerkmalSprache',
                        'kMerkmal',
                        'kSprache'
                    );
                    $mmwCount = \count($attrValues);
                    if (\is_array($attrValues) && $mmwCount > 0) {
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

                                    if ($localized[$j]->kSprache === $defaultLanguage->kSprache) {
                                        $item->cNameSTD            = $localized[$j]->cWert;
                                        $item->cSeoSTD             = $localized[$j]->cSeo;
                                        $item->cMetaTitleSTD       = $localized[$j]->cMetaTitle;
                                        $item->cMetaKeywordsSTD    = $localized[$j]->cMetaKeywords;
                                        $item->cMetaDescriptionSTD = $localized[$j]->cMetaDescription;
                                        $item->cBeschreibungSTD    = $localized[$j]->cBeschreibung;
                                    }
                                }
                                //alten Bildpfad nehmen
                                $attrValues[$o]->cBildpfad = $oMerkmal->oMerkmalWert_arr[$attrValues[$o]->kMerkmalWert];
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

                                    if ($localized[$j]->kSprache === $defaultLanguage->kSprache) {
                                        $item->cNameSTD            = $localized[$j]->cWert;
                                        $item->cSeoSTD             = $localized[$j]->cSeo;
                                        $item->cMetaTitleSTD       = $localized[$j]->cMetaTitle;
                                        $item->cMetaKeywordsSTD    = $localized[$j]->cMetaKeywords;
                                        $item->cMetaDescriptionSTD = $localized[$j]->cMetaDescription;
                                        $item->cBeschreibungSTD    = $localized[$j]->cBeschreibung;
                                    }
                                }
                                // alten Bildpfad nehmen
                                $attrValues[$o]->cBildpfad = $oMerkmal->oMerkmalWert_arr[$attrValues[$o]->kMerkmalWert];
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
        }
        // Kommen nur MerkmalWerte?
        if (!isset($xml['merkmale']['tmerkmalwert']) || !\is_array($xml['merkmale']['tmerkmalwert'])) {
            return;
        }
        $attrValues = $this->mapper->mapArray($xml['merkmale'], 'tmerkmalwert', 'mMerkmalWert');
        $i          = 0;

        if (!isset($attributeValues[$i]) || $attributeValues[$i] === null) {
            $attributeValues[$i] = new stdClass();
        }
        $attributeValues[$i]->oMMW_arr = [];
        $mmwCount                      = \count($attrValues);
        for ($o = 0; $o < $mmwCount; $o++) {
            $this->deleteAttributeValue($attrValues[$o]->kMerkmalWert, true);
            $item               = new stdClass();
            $item->kMerkmalWert = $attrValues[$o]->kMerkmalWert;
            $item->kSprache_arr = [];

            if (\count($attrValues) < 2) {
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

                $localized[$j]->cSeo = Seo::getSeo($seo);
                $localized[$j]->cSeo = Seo::checkSeo($localized[$j]->cSeo);
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

                if (isset($localized[$j]->kSprache, $defaultLanguage->kSprache)
                    && $localized[$j]->kSprache === $defaultLanguage->kSprache
                ) {
                    $item->cNameSTD            = $localized[$j]->cWert;
                    $item->cSeoSTD             = $localized[$j]->cSeo;
                    $item->cMetaTitleSTD       = $localized[$j]->cMetaTitle;
                    $item->cMetaKeywordsSTD    = $localized[$j]->cMetaKeywords;
                    $item->cMetaDescriptionSTD = $localized[$j]->cMetaDescription;
                    $item->cBeschreibungSTD    = $localized[$j]->cBeschreibung;
                }
            }
            $image = $this->db->select('tmerkmalwertbild', 'kMerkmalWert', (int)$attrValues[$o]->kMerkmalWert);

            $attrValues[$o]->cBildpfad = $image->cBildpfad ?? '';
            $this->upsert('tmerkmalwert', [$attrValues[$o]], 'kMerkmalWert');
            $attributeValues[$i]->oMMW_arr[$o] = $item;
        }
        $this->fuelleFehlendeMMWInSeo($attributeValues);
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

                    $exists = false;
                    foreach ($attributeValue->kSprache_arr as $languageID) {
                        $languageID = (int)$languageID;
                        // Laufe alle gefüllten Sprachen durch
                        if ($languageID === $language->kSprache) {
                            $exists = true;
                            break;
                        }
                    }
                    if ($exists) {
                        continue;
                    }
                    // Sprache vom Shop wurde nicht von der Wawi mitgeschickt und muss somit in tseo nachgefüllt werden
                    $seo = Seo::checkSeo(Seo::getSeo($attributeValue->cNameSTD ?? ''));
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
                    if ($seo !== '' && $seo !== null) {
                        $oSeo           = new stdClass();
                        $oSeo->cSeo     = $seo;
                        $oSeo->cKey     = 'kMerkmalWert';
                        $oSeo->kKey     = (int)$attributeValue->kMerkmalWert;
                        $oSeo->kSprache = $language->kSprache;
                        $this->db->insert('tseo', $oSeo);
                        $attrLang                   = new stdClass();
                        $attrLang->kMerkmalWert     = $attributeValue->kMerkmalWert;
                        $attrLang->kSprache         = $language->kSprache;
                        $attrLang->cWert            = $attributeValue->cNameSTD ?? '';
                        $attrLang->cSeo             = $oSeo->cSeo ?? '';
                        $attrLang->cMetaTitle       = $attributeValue->cMetaTitleSTD ?? '';
                        $attrLang->cMetaKeywords    = $attributeValue->cMetaKeywordsSTD ?? '';
                        $attrLang->cMetaDescription = $attributeValue->cMetaDescriptionSTD ?? '';
                        $attrLang->cBeschreibung    = $attributeValue->cBeschreibungSTD ?? '';
                        $this->db->insert('tmerkmalwertsprache', $attrLang);
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
        if (!($id > 0)) {
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
        foreach ($this->db->selectAll('tmerkmalwert', 'kMerkmal', $id, 'kMerkmalWert') as $wert) {
            $this->db->delete('tmerkmalwertsprache', 'kMerkmalWert', (int)$wert->kMerkmalWert);
            $this->db->delete('tmerkmalwertbild', 'kMerkmalWert', (int)$wert->kMerkmalWert);
        }
        $this->db->delete('tmerkmalwert', 'kMerkmal', $id);
    }

    /**
     * @param int $id
     */
    private function deleteOnlyAttribute(int $id): void
    {
        if (!($id > 0)) {
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
    private function deleteAttributeValue(int $attributeValueID, $isInsert = false): void
    {
        if (!($attributeValueID > 0)) {
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
    private function merkeBildPfad(int $attributeID): stdClass
    {
        $attribute                   = new stdClass();
        $attribute->oMerkmalWert_arr = [];
        if ($attributeID > 0) {
            $oMerkmalTMP = $this->db->select('tmerkmal', 'kMerkmal', $attributeID);
            if (isset($oMerkmalTMP->kMerkmal) && $oMerkmalTMP->kMerkmal > 0) {
                $attribute->kMerkmal  = $oMerkmalTMP->kMerkmal;
                $attribute->cBildpfad = $oMerkmalTMP->cBildpfad;
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
