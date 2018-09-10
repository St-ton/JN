<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Sitemap\Factories;

use function Functional\first;
use function Functional\map;
use Tightenco\Collect\Support\Collection;

/**
 * Class NewsItem
 * @package Sitemap\Generators
 */
class NewsItem extends AbstractGenerator
{
    /**
     * @inheritdoc
     */
    public function getCollection(array $languages, array $customerGroups): Collection
    {
        $collection = new Collection();
        if ($this->config['sitemap']['sitemap_news_anzeigen'] !== 'Y') {
            return $collection;
        }
        // @todo:
        $languageIDs = map($languages, function ($e) {
            return $e->kSprache;
        });
        $collection   = new Collection();
        $imageBaseURL = \Shop::getImageBaseURL();
        $res = $this->db->query(
            "SELECT tnews.dGueltigVon, tseo.cSeo
                FROM tnews
                JOIN tseo 
                    ON tseo.cKey = 'kNews'
                    AND tseo.kKey = tnews.kNews
                    AND tseo.kSprache = tnews.kSprache
                WHERE tnews.nAktiv = 1
                    AND tnews.dGueltigVon <= NOW()
                    AND (tnews.cKundengruppe LIKE '%;-1;%'
                    OR FIND_IN_SET('" . first($customerGroups) . "', REPLACE(tnews.cKundengruppe, ';',',')) > 0) 
                    ORDER BY tnews.dErstellt",
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
