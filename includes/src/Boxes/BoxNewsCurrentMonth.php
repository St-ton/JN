<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes;

use DB\ReturnType;

/**
 * Class BoxNewsCurrentMonth
 * @package Boxes
 */
final class BoxNewsCurrentMonth extends AbstractBox
{
    /**
     * BoxWishlist constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        parent::addMapping('oNewsMonatsUebersicht_arr', 'Items');
        $langID       = \Shop::getLanguageID();
        $cSQL         = (int)$config['news']['news_anzahl_box'] > 0
            ? ' LIMIT ' . (int)$config['news']['news_anzahl_box']
            : '';
        $newsOverview = \Shop::Container()->getDB()->queryPrepared(
            "SELECT tseo.cSeo, tnewsmonatsuebersicht.cName, tnewsmonatsuebersicht.kNewsMonatsUebersicht, 
                month(tnews.dGueltigVon) AS nMonat, year( tnews.dGueltigVon ) AS nJahr, count(*) AS nAnzahl
                FROM tnews
                JOIN tnewsmonatsuebersicht 
                    ON tnewsmonatsuebersicht.nMonat = month(tnews.dGueltigVon)
                    AND tnewsmonatsuebersicht.nJahr = year(tnews.dGueltigVon)
                    AND tnewsmonatsuebersicht.kSprache = :lid
                LEFT JOIN tseo 
                    ON cKey = 'kNewsMonatsUebersicht'
                    AND kKey = tnewsmonatsuebersicht.kNewsMonatsUebersicht
                    AND tseo.kSprache = :lid
                WHERE tnews.dGueltigVon < now()
                    AND tnews.nAktiv = 1
                    AND tnews.kSprache = :lid
                GROUP BY year(tnews.dGueltigVon) , month(tnews.dGueltigVon)
                ORDER BY tnews.dGueltigVon DESC" . $cSQL,
            ['lid' => $langID],
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($newsOverview as $item) {
            $item->cURL     = baueURL($item, URLART_NEWSMONAT);
            $item->cURLFull = baueURL(
                $item,
                URLART_NEWSMONAT,
                0,
                false,
                true
            );
        }
        $this->setShow(count($newsOverview) > 0);
        $this->setItems($newsOverview);

        executeHook(HOOK_BOXEN_INC_NEWS);
    }
}
