<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Sitemap\Factories;

use DB\ReturnType;
use function Functional\first;
use function Functional\map;

/**
 * Class Category
 * @package Sitemap\Factories
 */
final class Category extends AbstractFactory
{
    /**
     * @param array $languages
     * @param array $customerGroups
     * @return \Generator
     */
    public function getCollection(array $languages, array $customerGroups): \Generator
    {
        $languageIDs    = map($languages, function ($e) {
            return (int)$e->kSprache;
        });
        $customerGroup  = first($customerGroups);
        $categoryHelper = new \KategorieListe();
        $res            = $this->db->queryPrepared(
            "SELECT tkategorie.kKategorie, tkategorie.dLetzteAktualisierung AS dlm, 
                tseo.cSeo, tkategoriepict.cPfad AS image, tseo.kSprache AS langID
                FROM tkategorie
                JOIN tseo 
                    ON tseo.cKey = 'kKategorie'
                    AND tseo.kKey = tkategorie.kKategorie
                    AND tseo.kSprache IN (" . \implode(', ', $languageIDs) . ')
                LEFT JOIN tkategoriesichtbarkeit 
                    ON tkategorie.kKategorie = tkategoriesichtbarkeit.kKategorie
                    AND tkategoriesichtbarkeit.kKundengruppe = :cGrpID
                LEFT JOIN tkategoriepict
                    ON tkategoriepict.kKategorie = tkategorie.kKategorie
                WHERE tkategoriesichtbarkeit.kKategorie IS NULL
                ORDER BY tkategorie.kKategorie',
            [
                'cGrpID' => $customerGroup
            ],
            ReturnType::QUERYSINGLE
        );
        while (($category = $res->fetch(\PDO::FETCH_OBJ)) !== false) {
            $category->kKategorie = (int)$category->kKategorie;
            $category->langID     = (int)$category->langID;
            if ($categoryHelper->nichtLeer($category->kKategorie, $customerGroup) === true) {
                $item = new \Sitemap\Items\Category($this->config, $this->baseURL, $this->baseImageURL);
                $item->generateData($category, $languages);
                yield $item;
            }
        }
    }
}
