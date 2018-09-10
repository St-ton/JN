<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Sitemap\Factories;

use function Functional\map;
use Tightenco\Collect\Support\Collection;

/**
 * Class LiveSearch
 * @package Sitemap\Generators
 */
class LiveSearch extends AbstractGenerator
{
    /**
     * @inheritdoc
     */
    public function getCollection(array $languages, array $customerGroups): Collection
    {
        $collection = new Collection();
        if ($this->config['sitemap']['sitemap_livesuche_anzeigen'] !== 'Y') {
            return $collection;
        }
        $languageIDs = map($languages, function ($e) {
            return $e->kSprache;
        });
        $collection   = new Collection();
        $imageBaseURL = \Shop::getImageBaseURL();
        $res = $this->db->query(
            "SELECT tsuchanfrage.kSuchanfrage, tseo.cSeo, tsuchanfrage.dZuletztGesucht
                FROM tsuchanfrage
                JOIN tseo 
                    ON tseo.cKey = 'kSuchanfrage'
                    AND tseo.kKey = tsuchanfrage.kSuchanfrage
                WHERE tsuchanfrage.nAktiv = 1
                    AND tsuchanfrage.kSprache IN (" . \implode(',', $languageIDs) . ")
                ORDER BY tsuchanfrage.kSuchanfrage",
            \DB\ReturnType::QUERYSINGLE
        );
        while (($liveSearch = $res->fetch(\PDO::FETCH_OBJ)) !== false) {
            $item = new \Sitemap\Items\LiveSearch($this->config);
            $item->generateData($liveSearch, $imageBaseURL);
            $collection->push($item);
        }

        return $collection;
    }
}
