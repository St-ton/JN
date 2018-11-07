<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_WIDGETS . 'class.WidgetBase.php';

/**
 * Class WidgetProductDemand
 */
class WidgetProductDemand extends WidgetBase
{
    /**
     * @var array
     */
    public $oBots_arr;

    /**
     *
     */
    public function init()
    {
    }

    /**
     * @param int $nYear
     * @param int $nMonth
     * @param int $nLimit
     * @return array
     */
    public function getBotsOfMonth($nYear, $nMonth, int $nLimit = 10)
    {
        return Shop::Container()->getDB()->query(
            "SELECT *, COUNT(tbesucherbot.kBesucherBot) AS nAnzahl
                FROM tbesucherarchiv
                LEFT JOIN tbesucherbot
                    ON tbesucherarchiv.kBesucherBot = tbesucherbot.kBesucherBot
                WHERE tbesucherarchiv.kBesucherBot > 0
                    AND YEAR(tbesucherarchiv.dZeit) = '" . (int)$nYear . "'
                    AND MONTH(tbesucherarchiv.dZeit) = '" . (int)$nMonth . "'
                GROUP BY tbesucherbot.kBesucherBot 
                LIMIT 0," . $nLimit,
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->oSmarty->fetch('tpl_inc/widgets/productdemand.tpl');
    }
}
