<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes\Items;


use DB\ReturnType;

/**
 * Class SearchCloud
 * @package Boxes
 */
final class SearchCloud extends AbstractBox
{
    /**
     * Cart constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        parent::addMapping('Suchbegriffe', 'Items');
        parent::addMapping('SuchbegriffeJSON', 'JSON');
        $this->setShow(false);
        $langID    = \Shop::getLanguageID();
        $limit     = (int)$config['boxen']['boxen_livesuche_count'];
        $cacheID   = 'bx_stgs_' . $langID . '_' . $limit;
        $cacheTags = [\CACHING_GROUP_BOX, \CACHING_GROUP_ARTICLE];
        $cached    = true;
        if (($searchCloudEntries = \Shop::Container()->getCache()->get($cacheID)) === false) {
            $cached             = false;
            $searchCloudEntries = \Shop::Container()->getDB()->queryPrepared(
                "SELECT tsuchanfrage.kSuchanfrage, tsuchanfrage.kSprache, tsuchanfrage.cSuche, 
                    tsuchanfrage.nAktiv, tsuchanfrage.nAnzahlTreffer, tsuchanfrage.nAnzahlGesuche, 
                    tsuchanfrage.dZuletztGesucht, tseo.cSeo
                    FROM tsuchanfrage
                    LEFT JOIN tseo 
                        ON tseo.cKey = 'kSuchanfrage'
                        AND tseo.kKey = tsuchanfrage.kSuchanfrage
                        AND tseo.kSprache = :lid
                    WHERE tsuchanfrage.kSprache = :lid
                        AND tsuchanfrage.nAktiv = 1
                        AND tsuchanfrage.kSuchanfrage > 0
                    GROUP BY tsuchanfrage.kSuchanfrage
                    ORDER BY tsuchanfrage.nAnzahlGesuche DESC
                    LIMIT :lmt",
                ['lid' => $langID, 'lmt' => $limit],
                ReturnType::ARRAY_OF_OBJECTS
            );
            \Shop::Container()->getCache()->set($cacheID, $searchCloudEntries, $cacheTags);
        }
        if (($count = \count($searchCloudEntries)) > 0) {
            $prio_step = ($searchCloudEntries[0]->nAnzahlGesuche - $searchCloudEntries[$count - 1]->nAnzahlGesuche) / 9;
            foreach ($searchCloudEntries as $cloudEntry) {
                $cloudEntry->Klasse   = ($prio_step < 1) ?
                    \rand(1, 10) :
                    (\round(($cloudEntry->nAnzahlGesuche - $searchCloudEntries[$count - 1]->nAnzahlGesuche) / $prio_step) + 1);
                $cloudEntry->cURL     = \UrlHelper::buildURL($cloudEntry, \URLART_LIVESUCHE);
                $cloudEntry->cURLFull = \UrlHelper::buildURL($cloudEntry, \URLART_LIVESUCHE, true);
            }
            $this->setShow(true);
            \shuffle($searchCloudEntries);
            $this->setItems($searchCloudEntries);
            $this->setJSON(AbstractBox::getJSONString($searchCloudEntries));
            \executeHook(\HOOK_BOXEN_INC_SUCHWOLKE, [
                'box'        => &$this,
                'cache_tags' => &$cacheTags,
                'cached'     => $cached
            ]);
        }
    }
}
