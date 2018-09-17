<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Sitemap\Factories;

use function Functional\map;

/**
 * Class Manufacturer
 * @package Sitemap\Generators
 */
final class Manufacturer extends AbstractFactory
{
    /**
     * @inheritdoc
     */
    public function getCollection(array $languages, array $customerGroups): \Generator
    {
        if ($this->config['sitemap']['sitemap_hersteller_anzeigen'] !== 'Y') {
            yield null;
        }
        $languageIDs = map($languages, function ($e) {
            return (int)$e->kSprache;
        });
        $res         = $this->db->query(
            "SELECT thersteller.kHersteller, thersteller.cName, thersteller.cBildpfad AS image, 
            tseo.cSeo, tseo.kSprache AS langID
                FROM thersteller
                JOIN tseo 
                    ON tseo.cKey = 'kHersteller'
                    AND tseo.kKey = thersteller.kHersteller
                    AND tseo.kSprache IN (" . \implode(',', $languageIDs) . ")
                ORDER BY thersteller.kHersteller",
            \DB\ReturnType::QUERYSINGLE
        );
        while (($manufacturer = $res->fetch(\PDO::FETCH_OBJ)) !== false) {
            $item = new \Sitemap\Items\Manufacturer($this->config, $this->baseURL, $this->baseImageURL);
            $item->generateData($manufacturer, $languages);
            yield $item;
        }
    }
}
