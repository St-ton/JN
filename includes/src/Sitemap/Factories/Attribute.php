<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Sitemap\Factories;

use Tightenco\Collect\Support\Collection;
use function Functional\map;

/**
 * Class Attribute
 * @package Sitemap\Generators
 */
class Attribute extends AbstractGenerator
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
        $languageIDs  = map($languages, function ($e) {
            return $e->kSprache;
        });
        $collection   = new Collection();
        $imageBaseURL = \Shop::getImageBaseURL();
        $res          = $this->db->query(
            "SELECT tmerkmalsprache.cName, tmerkmalsprache.kMerkmal, tmerkmalwertsprache.cWert, 
                tseo.cSeo, tmerkmalwert.kMerkmalWert
                FROM tmerkmalsprache
                JOIN tmerkmal 
                    ON tmerkmal.kMerkmal = tmerkmalsprache.kMerkmal
                JOIN tmerkmalwert 
                    ON tmerkmalwert.kMerkmal = tmerkmalsprache.kMerkmal
                JOIN tmerkmalwertsprache 
                    ON tmerkmalwertsprache.kMerkmalWert = tmerkmalwert.kMerkmalWert
                    AND tmerkmalwertsprache.kSprache = tmerkmalsprache.kSprache
                JOIN tartikelmerkmal 
                    ON tartikelmerkmal.kMerkmalWert = tmerkmalwert.kMerkmalWert
                JOIN tseo 
                    ON tseo.cKey = 'kMerkmalWert'
                    AND tseo.kKey = tmerkmalwert.kMerkmalWert
                    AND tseo.kSprache = tmerkmalsprache.kSprache
                WHERE tmerkmal.nGlobal = 1
                    AND tmerkmalsprache.kSprache IN (" . \implode(',', $languageIDs) . ")
                GROUP BY tmerkmalwert.kMerkmalWert
                ORDER BY tmerkmal.kMerkmal, tmerkmal.cName",
            \DB\ReturnType::QUERYSINGLE
        );
        while (($tag = $res->fetch(\PDO::FETCH_OBJ)) !== false) {
            $item = new \Sitemap\Items\Attribute($this->config);
            $item->generateData($tag, $imageBaseURL);
            $collection->push($item);
        }

        return $collection;
    }
}
