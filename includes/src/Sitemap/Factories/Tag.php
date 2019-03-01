<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Sitemap\Factories;

use Generator;
use JTL\DB\ReturnType;
use JTL\Sitemap\Items\Tag as Item;
use PDO;
use function Functional\map;

/**
 * Class Tag
 * @package JTL\Sitemap\Factories
 */
final class Tag extends AbstractFactory
{
    /**
     * @inheritdoc
     */
    public function getCollection(array $languages, array $customerGroups): Generator
    {
        $languageIDs = map($languages, function ($e) {
            return (int)$e->kSprache;
        });
        $res         = $this->db->query(
            "SELECT ttag.kTag, ttag.cName, tseo.cSeo, ttag.kSprache AS langID
                FROM ttag               
                JOIN tseo 
                    ON tseo.cKey = 'kTag'
                    AND tseo.kKey = ttag.kTag
                WHERE ttag.kSprache IN (" . \implode(',', $languageIDs) . ')
                    AND ttag.nAktiv = 1
                ORDER BY ttag.kTag',
            ReturnType::QUERYSINGLE
        );
        while (($tag = $res->fetch(PDO::FETCH_OBJ)) !== false) {
            $tag->langID = (int)$tag->langID;
            $tag->kTag   = (int)$tag->kTag;
            $item        = new Item($this->config, $this->baseURL, $this->baseImageURL);
            $item->generateData($tag, $languages);
            yield $item;
        }
    }
}
