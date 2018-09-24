<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Sitemap\Factories;

use function Functional\map;

/**
 * Class LiveSearch
 * @package Sitemap\Generators
 */
final class LiveSearch extends AbstractFactory
{
    /**
     * @inheritdoc
     */
    public function getCollection(array $languages, array $customerGroups): \Generator
    {
        if ($this->config['sitemap']['sitemap_livesuche_anzeigen'] !== 'Y') {
            yield null;
        }
        $languageIDs = map($languages, function ($e) {
            return (int)$e->kSprache;
        });
        $res         = $this->db->query(
            "SELECT tsuchanfrage.kSuchanfrage, tseo.cSeo, tsuchanfrage.dZuletztGesucht AS dlm,
            tseo.kSprache AS langID
                FROM tsuchanfrage
                JOIN tseo 
                    ON tseo.cKey = 'kSuchanfrage'
                    AND tseo.kKey = tsuchanfrage.kSuchanfrage
                WHERE tsuchanfrage.nAktiv = 1
                    AND tsuchanfrage.kSprache IN (" . \implode(',', $languageIDs) . ")
                ORDER BY tsuchanfrage.kSuchanfrage",
            \DB\ReturnType::QUERYSINGLE
        );
        while (($ls = $res->fetch(\PDO::FETCH_OBJ)) !== false) {
            $ls->kSuchanfrage = (int)$ls->kSuchanfrage;
            $ls->langID       = (int)$ls->langID;
            $item             = new \Sitemap\Items\LiveSearch($this->config, $this->baseURL, $this->baseImageURL);
            $item->generateData($ls, $languages);
            yield $item;
        }
    }
}
