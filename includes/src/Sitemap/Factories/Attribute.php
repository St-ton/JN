<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Sitemap\Factories;

use function Functional\map;

/**
 * Class Attribute
 * @package Sitemap\Generators
 */
final class Attribute extends AbstractFactory
{
    /**
     * @inheritdoc
     */
    public function getCollection(array $languages, array $customerGroups): \Generator
    {
        if ($this->config['sitemap']['sitemap_tags_anzeigen'] !== 'Y') {
            yield null;
        }
        $languageIDs = map($languages, function ($e) {
            return (int)$e->kSprache;
        });
        $res         = $this->db->query(
            "SELECT tmerkmalsprache.cName, tmerkmalsprache.kMerkmal, tmerkmalwertsprache.cWert, 
                tseo.cSeo, tmerkmalwert.kMerkmalWert, tmerkmalwert.cBildpfad AS image,
                tmerkmalsprache.kSprache AS langID
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
                WHERE tmerkmalsprache.kSprache IN (" . \implode(',', $languageIDs) . ")
                GROUP BY tmerkmalwert.kMerkmalWert
                ORDER BY tmerkmal.kMerkmal, tmerkmal.cName",
            \DB\ReturnType::QUERYSINGLE
        );
        while (($attribute = $res->fetch(\PDO::FETCH_OBJ)) !== false) {
            $attribute->kMerkmal     = (int)$attribute->kMerkmal;
            $attribute->kMerkmalWert = (int)$attribute->kMerkmalWert;
            $attribute->langID       = (int)$attribute->langID;
            $item                    = new \Sitemap\Items\Attribute($this->config, $this->baseURL, $this->baseImageURL);
            $item->generateData($attribute, $languages);
            yield $item;
        }
    }
}
