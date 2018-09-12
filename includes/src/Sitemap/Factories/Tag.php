<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Sitemap\Factories;

use function Functional\map;

/**
 * Class Tag
 * @package Sitemap\Generators
 */
final class Tag extends AbstractFactory
{
    /**
     * @inheritdoc
     */
    public function getCollection(array $languages, array $customerGroups): \Generator
    {
        if ($this->config['sitemap']['sitemap_tags_anzeigen'] !== 'Y') {
            yield null;
        }
        $languageIDs = map($languages, function ($e) {
            return $e->kSprache;
        });
        $res         = $this->db->query(
            "SELECT ttag.kTag, ttag.cName, tseo.cSeo, ttag.kSprache AS langID, tsprache.cISO AS langCode
                FROM ttag               
                JOIN tseo 
                    ON tseo.cKey = 'kTag'
                    AND tseo.kKey = ttag.kTag
                JOIN tsprache
                    ON tsprache.kSprache = ttag.kSprache
                WHERE ttag.kSprache IN (" . \implode(',', $languageIDs) . ")
                    AND ttag.nAktiv = 1
                ORDER BY ttag.kTag",
            \DB\ReturnType::QUERYSINGLE
        );
        while (($tag = $res->fetch(\PDO::FETCH_OBJ)) !== false) {
            $item = new \Sitemap\Items\Tag($this->config, $this->baseURL, $this->baseImageURL);
            $item->generateData($tag);
            yield $item;
        }
    }
}
