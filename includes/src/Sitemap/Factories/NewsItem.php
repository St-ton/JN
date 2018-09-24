<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Sitemap\Factories;

use function Functional\first;
use function Functional\map;

/**
 * Class NewsItem
 * @package Sitemap\Generators
 */
final class NewsItem extends AbstractFactory
{
    /**
     * @inheritdoc
     */
    public function getCollection(array $languages, array $customerGroups): \Generator
    {
        if ($this->config['sitemap']['sitemap_news_anzeigen'] !== 'Y') {
            yield null;
        }
        $languageIDs = map($languages, function ($e) {
            return (int)$e->kSprache;
        });
        $res         = $this->db->query(
            "SELECT tnews.dGueltigVon AS dlm, tnews.kNews, tnews.cPreviewImage AS image, tseo.cSeo, 
            tseo.kSprache AS langID
                FROM tnews
                JOIN tseo 
                    ON tseo.cKey = 'kNews'
                    AND tseo.kKey = tnews.kNews
                    AND tseo.kSprache = tnews.kSprache
                WHERE tnews.nAktiv = 1
                    AND tnews.dGueltigVon <= NOW()
                    AND tnews.kSprache IN (" . \implode(',', $languageIDs) . ")
                    AND (tnews.cKundengruppe LIKE '%;-1;%'
                    OR FIND_IN_SET('" . first($customerGroups) . "', REPLACE(tnews.cKundengruppe, ';',',')) > 0) 
                    ORDER BY tnews.dErstellt",
            \DB\ReturnType::QUERYSINGLE
        );
        while (($ni = $res->fetch(\PDO::FETCH_OBJ)) !== false) {
            $ni->langID = (int)$ni->langID;
            $ni->kNews  = (int)$ni->kNews;
            $item       = new \Sitemap\Items\NewsItem($this->config, $this->baseURL, $this->baseImageURL);
            $item->generateData($ni, $languages);
            yield $item;
        }
    }
}
