<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes;

use DB\ReturnType;

/**
 * Class BoxTagCloud
 * @package Boxes
 */
final class BoxTagCloud extends AbstractBox
{
    /**
     * BoxCart constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        parent::addMapping('Tagbegriffe', 'Items');
        $limit     = (int)$config['boxen']['boxen_tagging_count'];
        $limitSQL  = ($limit > 0) ? ' LIMIT ' . $limit : '';
        $cacheTags = [CACHING_GROUP_BOX, CACHING_GROUP_ARTICLE];
        $cached    = true;
        $langID    = \Shop::getLanguageID();
        $cacheID   = 'bx_tgcld_' . $langID . '_' . $limit;
        if (($tagCloud = \Shop::Container()->getCache()->get($cacheID)) === false) {
            $tagCloud = [];
            $cached   = false;
            $tags     = \Shop::Container()->getDB()->queryPrepared(
                "SELECT ttag.kTag,ttag.cName, tseo.cSeo,sum(ttagartikel.nAnzahlTagging) AS Anzahl 
                    FROM ttag
                    JOIN ttagartikel 
                        ON ttagartikel.kTag = ttag.kTag
                    LEFT JOIN tseo 
                        ON tseo.cKey = 'kTag'
                        AND tseo.kKey = ttag.kTag
                        AND tseo.kSprache = :lid
                    WHERE ttag.nAktiv = 1 
                        AND ttag.kSprache = :lid 
                    GROUP BY ttag.kTag 
                    ORDER BY Anzahl DESC" . $limitSQL,
                ['lid' => $langID],
                ReturnType::ARRAY_OF_OBJECTS
            );
            if (($count = count($tags)) > 0) {
                // Priorität berechnen
                $prio_step = ($tags[0]->Anzahl - $tags[$count - 1]->Anzahl) / 9;
                foreach ($tags as $tagwolke) {
                    if ($tagwolke->kTag > 0) {
                        $tagwolke->Klasse   = ($prio_step < 1) ?
                            rand(1, 10) :
                            (round(($tagwolke->Anzahl - $tags[$count - 1]->Anzahl) / $prio_step) + 1);
                        $tagwolke->cURL     = baueURL($tagwolke, URLART_TAG);
                        $tagwolke->cURLFull = baueURL($tagwolke, URLART_TAG, 0, false, true);
                        $tagCloud[]         = $tagwolke;
                    }
                }
            }
            \Shop::Container()->getCache()->set($cacheID, $tagCloud, $cacheTags);
        }

        if (count($tagCloud) > 0) {
            $this->setShow(true);
            shuffle($tagCloud);
            $this->setItems($tagCloud);
            $this->setJSON(\Boxen::gibJSONString($tagCloud));
            executeHook(HOOK_BOXEN_INC_TAGWOLKE, [
                'box'        => &$this,
                'cache_tags' => &$cacheTags,
                'cached'     => $cached
            ]);
        } else {
            $this->setShow(false);
        }
    }
}
