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
use function Functional\flatten;

/**
 * Class Manufacturers
 * @package JTL\dbeS\Sync
 */
final class Manufacturers extends AbstractSync
{
    /**
     * @param Starter $starter
     * @return mixed|null
     */
    public function handle(Starter $starter)
    {
        foreach ($starter->getXML() as $i => $item) {
            [$file, $xml] = [\key($item), \reset($item)];
            if (\strpos($file, 'del_hersteller.xml') !== false) {
                $this->handleDeletes($xml);
            } elseif (\strpos($file, 'hersteller.xml') !== false) {
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
        $cacheTags = [];
        if (isset($xml['del_hersteller']['kHersteller']) && (int)$xml['del_hersteller']['kHersteller'] > 0) {
            $xml['del_hersteller']['kHersteller'] = [$xml['del_hersteller']['kHersteller']];
        }
        if (!isset($xml['del_hersteller']['kHersteller']) || !\is_array($xml['del_hersteller']['kHersteller'])) {
            return;
        }
        foreach ($xml['del_hersteller']['kHersteller'] as $manufacturerID) {
            $manufacturerID = (int)$manufacturerID;
            if ($manufacturerID > 0) {
                $affectedProducts = $this->db->selectAll(
                    'tartikel',
                    'kHersteller',
                    $manufacturerID,
                    'kArtikel'
                );
                $this->db->delete('tseo', ['kKey', 'cKey'], [$manufacturerID, 'kHersteller']);
                $this->db->delete('thersteller', 'kHersteller', $manufacturerID);
                $this->db->delete('therstellersprache', 'kHersteller', $manufacturerID);

                \executeHook(\HOOK_HERSTELLER_XML_BEARBEITEDELETES, ['kHersteller' => $manufacturerID]);
                $cacheTags[] = \CACHING_GROUP_MANUFACTURER . '_' . $manufacturerID;
                foreach ($affectedProducts as $product) {
                    $cacheTags[] = \CACHING_GROUP_ARTICLE . '_' . $product->kArtikel;
                }
            }
        }
        $this->cache->flushTags(flatten($cacheTags));
    }

    /**
     * @param array $xml
     */
    private function handleInserts(array $xml): void
    {
        if (!isset($xml['hersteller']['thersteller']) || !\is_array($xml['hersteller']['thersteller'])) {
            return;
        }
        $manufacturers = $this->mapper->mapArray($xml['hersteller'], 'thersteller', 'mHersteller');
        $languages     = Sprache::getAllLanguages();
        $mfCount       = \count($manufacturers);
        $cacheTags     = [];
        for ($i = 0; $i < $mfCount; $i++) {
            $id               = (int)$manufacturers[$i]->kHersteller;
            $affectedProducts = $this->db->selectAll('tartikel', 'kHersteller', $id, 'kArtikel');
            $this->db->delete('tseo', ['kKey', 'cKey'], [$id, 'kHersteller']);
            if (!\trim($manufacturers[$i]->cSeo)) {
                $manufacturers[$i]->cSeo = Seo::getFlatSeoPath($manufacturers[$i]->cName);
            }
            // alten Bildpfad merken
            $manufacturerImage            = $this->db->query(
                'SELECT cBildPfad 
                FROM thersteller 
                WHERE kHersteller = ' . $id,
                ReturnType::SINGLE_OBJECT
            );
            $manufacturers[$i]->cBildPfad = $manufacturerImage->cBildPfad ?? '';
            $manufacturers[$i]->cSeo      = Seo::getSeo($manufacturers[$i]->cSeo);
            $manufacturers[$i]->cSeo      = Seo::checkSeo($manufacturers[$i]->cSeo);
            $this->upsert('thersteller', [$manufacturers[$i]], 'kHersteller');

            $cXMLSprache = [];
            if (isset($xml['hersteller']['thersteller'][$i])) {
                $cXMLSprache = $xml['hersteller']['thersteller'][$i];
            } elseif (isset($xml['hersteller']['thersteller']['therstellersprache'])) {
                $cXMLSprache = $xml['hersteller']['thersteller'];
            }
            $mfSeo = $this->mapper->mapArray($cXMLSprache, 'therstellersprache', 'mHerstellerSpracheSeo');
            foreach ($languages as $language) {
                $baseSeo = $manufacturers[$i]->cSeo;
                foreach ($mfSeo as $mf) {
                    if (isset($mf->kSprache) && (int)$mf->kSprache === (int)$language->kSprache && !empty($mf->cSeo)) {
                        $baseSeo = Seo::getSeo($mf->cSeo);
                        break;
                    }
                }
                $oSeo           = new stdClass();
                $oSeo->cSeo     = Seo::checkSeo($baseSeo);
                $oSeo->cKey     = 'kHersteller';
                $oSeo->kKey     = $id;
                $oSeo->kSprache = (int)$language->kSprache;
                $this->db->insert('tseo', $oSeo);
            }
            $this->db->delete('therstellersprache', 'kHersteller', $id);

            $this->updateXMLinDB(
                $cXMLSprache,
                'therstellersprache',
                'mHerstellerSprache',
                'kHersteller',
                'kSprache'
            );

            \executeHook(\HOOK_HERSTELLER_XML_BEARBEITEINSERT, ['oHersteller' => $manufacturers[$i]]);
            $cacheTags[] = \CACHING_GROUP_MANUFACTURER . '_' . $id;
            if (\is_array($affectedProducts)) {
                $articleCacheTags = [];
                foreach ($affectedProducts as $article) {
                    $articleCacheTags[] = \CACHING_GROUP_ARTICLE . '_' . $article->kArtikel;
                }
                $this->cache->flushTags($articleCacheTags);
            }
        }
        $this->cache->flushTags($cacheTags);
    }
}
