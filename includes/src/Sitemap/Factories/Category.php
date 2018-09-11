<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Sitemap\Factories;

use Tightenco\Collect\Support\Collection;
use function Functional\first;
use function Functional\map;

/**
 * Class Category
 * @package Sitemap\Generators
 */
class Category extends AbstractGenerator
{
    /**
     * @inheritdoc
     */
    public function getCollection(array $languages, array $customerGroups): Collection
    {
        $languageIDs   = map($languages, function ($e) {
            return $e->kSprache;
        });
        $collection    = new Collection();
        $customerGroup = first($customerGroups);
        if ($this->config['sitemap']['sitemap_kategorien_anzeigen'] !== 'Y') {
            return $collection;
        }
        $categoryHelper = new \KategorieListe();
        $res            = $this->db->queryPrepared(
            "SELECT tkategorie.kKategorie, tseo.cSeo, tkategorie.dLetzteAktualisierung
                FROM tkategorie
                JOIN tseo 
                    ON tseo.cKey = 'kKategorie'
                    AND tseo.kKey = tkategorie.kKategorie
                    AND tseo.kSprache IN (" . \implode(', ', $languageIDs) . ")
                LEFT JOIN tkategoriesichtbarkeit 
                    ON tkategorie.kKategorie = tkategoriesichtbarkeit.kKategorie
                    AND tkategoriesichtbarkeit.kKundengruppe = :cGrpID
                WHERE tkategoriesichtbarkeit.kKategorie IS NULL
                ORDER BY tkategorie.kKategorie",
            [
                'cGrpID' => $customerGroup
            ],
            \DB\ReturnType::QUERYSINGLE
        );
        while (($category = $res->fetch(\PDO::FETCH_OBJ)) !== false) {
            if ($categoryHelper->nichtLeer($category->kKategorie, $customerGroup) === true) {
                $item = new \Sitemap\Items\Category($this->config, $this->baseURL, $this->baseImageURL);
                $item->generateData($category);
                $collection->push($item);
            }
        }

        return $collection;
    }
}
