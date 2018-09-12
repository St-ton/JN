<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Sitemap\Factories;

use function Functional\map;

/**
 * Class NewsCategory
 * @package Sitemap\Generators
 */
final class NewsCategory extends AbstractFactory
{
    /**
     * @inheritdoc
     */
    public function getCollection(array $languages, array $customerGroups): \Generator
    {
        if ($this->config['sitemap']['sitemap_newskategorien_anzeigen'] !== 'Y') {
            yield null;
        }
        $languageIDs = map($languages, function ($e) {
            return $e->kSprache;
        });
        $res         = $this->db->query(
            "SELECT tnewskategorie.dLetzteAktualisierung AS dlm, tnewskategorie.cPreviewImage AS image, tseo.cSeo,
            tsprache.kSprache AS langID, tsprache.cISO AS langCode
                FROM tnewskategorie
                JOIN tseo 
                    ON tseo.cKey = 'kNewsKategorie'
                    AND tseo.kKey = tnewskategorie.kNewsKategorie
                    AND tseo.kSprache = tnewskategorie.kSprache
                JOIN tsprache
                    ON tsprache.kSprache = tseo.kSprache
                WHERE tnewskategorie.nAktiv = 1
                    AND tseo.kSprache IN (" . \implode(',', $languageIDs) . ")",
            \DB\ReturnType::QUERYSINGLE
        );
        while (($tag = $res->fetch(\PDO::FETCH_OBJ)) !== false) {
            $item = new \Sitemap\Items\NewsCategory($this->config, $this->baseURL, $this->baseImageURL);
            $item->generateData($tag);
            yield $item;
        }
    }
}
