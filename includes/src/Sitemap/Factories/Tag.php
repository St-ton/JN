<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Sitemap\Factories;

use function Functional\map;
use Tightenco\Collect\Support\Collection;

/**
 * Class Tag
 * @package Sitemap\Generators
 */
class Tag extends AbstractGenerator
{
    /**
     * @inheritdoc
     */
    public function getCollection(array $languages, array $customerGroups): Collection
    {
        $collection = new Collection();
        if ($this->config['sitemap']['sitemap_tags_anzeigen'] !== 'Y') {
            return $collection;
        }
        $languageIDs = map($languages, function ($e) {
            return $e->kSprache;
        });
        $collection   = new Collection();
        $imageBaseURL = \Shop::getImageBaseURL();
        $res          = $this->db->query(
            "SELECT ttag.kTag, ttag.cName, tseo.cSeo
                FROM ttag               
                JOIN tseo 
                    ON tseo.cKey = 'kTag'
                    AND tseo.kKey = ttag.kTag
                WHERE ttag.kSprache IN (" . \implode(',', $languageIDs) . ")
                    AND ttag.nAktiv = 1
                ORDER BY ttag.kTag",
            \DB\ReturnType::QUERYSINGLE
        );
        while (($tag = $res->fetch(\PDO::FETCH_OBJ)) !== false) {
            $item = new \Sitemap\Items\Tag($this->config);
            $item->generateData($tag, $imageBaseURL);
            $collection->push($item);
        }

        return $collection;
    }
}
