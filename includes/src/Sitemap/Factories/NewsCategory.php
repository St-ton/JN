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
 * Class NewsCategory
 * @package Sitemap\Generators
 */
class NewsCategory extends AbstractGenerator
{
    /**
     * @inheritdoc
     */
    public function getCollection(array $languages, array $customerGroups): Collection
    {
        $collection = new Collection();
        if ($this->config['sitemap']['sitemap_newskategorien_anzeigen'] !== 'Y') {
            return $collection;
        }
        $languageIDs = map($languages, function ($e) {
            return $e->kSprache;
        });
        $collection   = new Collection();
        $imageBaseURL = \Shop::getImageBaseURL();
        $res = $this->db->query(
            "SELECT tnewskategorie.dLetzteAktualisierung, tseo.cSeo
                 FROM tnewskategorie
                 JOIN tseo 
                    ON tseo.cKey = 'kNewsKategorie'
                    AND tseo.kKey = tnewskategorie.kNewsKategorie
                    AND tseo.kSprache = tnewskategorie.kSprache
                 WHERE tnewskategorie.nAktiv = 1
                    AND tseo.kSprache IN (" . \implode(',', $languageIDs) . ")",
            \DB\ReturnType::QUERYSINGLE
        );
        while (($tag = $res->fetch(\PDO::FETCH_OBJ)) !== false) {
            $item = new \Sitemap\Items\NewsCategory($this->config);
            $item->generateData($tag, $imageBaseURL);
            $collection->push($item);
        }

        return $collection;
    }
}
