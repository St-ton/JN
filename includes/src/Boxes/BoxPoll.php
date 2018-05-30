<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes;

use DB\ReturnType;

/**
 * Class BoxSurvey
 * @package Boxes
 */
final class BoxPoll extends AbstractBox
{
    /**
     * BoxPoll constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        parent::addMapping('oUmfrage_arr', 'Items');
        $this->setShow(false);
        $cSQL = '';
        if (($conf = $this->config['umfrage']['umfrage_box_anzahl']) > 0
        ) {
            $cSQL = ' LIMIT ' . (int)$conf;
        }
        $langID    = \Shop::getLanguageID();
        $cacheID   = 'bu_' . $langID . '_' . \Session::CustomerGroup()->getID() . md5($cSQL);
        $cacheTags = [CACHING_GROUP_BOX, CACHING_GROUP_CORE];
        $cached    = true;
        if (($polls = \Shop::Container()->getCache()->get($cacheID)) === false) {
            $cached = false;
            $polls  = \Shop::Container()->getDB()->queryPrepared(
                "SELECT tumfrage.kUmfrage, tumfrage.kSprache, tumfrage.kKupon, tumfrage.cKundengruppe, 
                tumfrage.cName, tumfrage.cBeschreibung, tumfrage.fGuthaben, tumfrage.nBonuspunkte, 
                tumfrage.nAktiv, tumfrage.dGueltigVon, tumfrage.dGueltigBis, tumfrage.dErstellt, tseo.cSeo,
                DATE_FORMAT(tumfrage.dGueltigVon, '%d.%m.%Y  %H:%i') AS dGueltigVon_de,
                DATE_FORMAT(tumfrage.dGueltigBis, '%d.%m.%Y  %H:%i') AS dGueltigBis_de, 
                count(tumfragefrage.kUmfrageFrage) AS nAnzahlFragen
                FROM tumfrage
                JOIN tumfragefrage 
                    ON tumfragefrage.kUmfrage = tumfrage.kUmfrage
                LEFT JOIN tseo 
                    ON tseo.cKey = 'kUmfrage'
                    AND tseo.kKey = tumfrage.kUmfrage
                    AND tseo.kSprache = :lid
                WHERE tumfrage.nAktiv = 1
                    AND tumfrage.kSprache = :lid
                    AND (cKundengruppe LIKE '%;-1;%' 
                        OR FIND_IN_SET(':cid', REPLACE(cKundengruppe, ';', ',')) > 0)
                    AND ((dGueltigVon <= now() 
                        AND dGueltigBis >= now()) || (dGueltigVon <= now() 
                        AND dGueltigBis = '0000-00-00 00:00:00'))
                GROUP BY tumfrage.kUmfrage
                ORDER BY tumfrage.dGueltigVon DESC" . $cSQL,
                ['lid' => $langID, 'cid' => \Session::CustomerGroup()->getID()],
                ReturnType::ARRAY_OF_OBJECTS
            );
            \Shop::Container()->getCache()->set($cacheID, $polls, $cacheTags);
        }
        foreach ($polls as $poll) {
            $poll->cURL     = baueURL($poll, URLART_UMFRAGE);
            $poll->cURLFull = baueURL($poll, URLART_UMFRAGE, 0, false, true);
        }
        $this->setItems($polls);
        executeHook(HOOK_BOXEN_INC_UMFRAGE, [
            'box'        => $this,
            'cache_tags' => &$cacheTags,
            'cached'     => $cached
        ]);
    }
}
