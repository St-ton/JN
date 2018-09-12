<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Sitemap\Factories;

use function Functional\first;
use function Functional\map;

/**
 * Class Category
 * @package Sitemap\Generators
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
        $languageIDs   = map($languages, function ($e) {
            return $e->kSprache;
        });
        $customerGroup = first($customerGroups);
        if ($this->config['sitemap']['sitemap_kategorien_anzeigen'] !== 'Y') {
            yield null;
        }
        $categoryHelper = new \KategorieListe();
        $res            = $this->db->queryPrepared(
            "SELECT tkategorie.kKategorie, tkategorie.dLetzteAktualisierung AS dlm, 
                tseo.cSeo, tkategoriepict.cPfad AS image, tseo.kSprache AS langID, tsprache.cISO AS langCode
                FROM tkategorie
                JOIN tseo 
                    ON tseo.cKey = 'kKategorie'
                    AND tseo.kKey = tkategorie.kKategorie
                    AND tseo.kSprache IN (" . \implode(', ', $languageIDs) . ")
                LEFT JOIN tkategoriesichtbarkeit 
                    ON tkategorie.kKategorie = tkategoriesichtbarkeit.kKategorie
                    AND tkategoriesichtbarkeit.kKundengruppe = :cGrpID
                LEFT JOIN tkategoriepict
                    ON tkategoriepict.kKategorie = tkategorie.kKategorie
                LEFT JOIN tsprache
                    ON tsprache.kSprache = tseo.kSprache
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
                yield $item;
            }
        }
    }
}
