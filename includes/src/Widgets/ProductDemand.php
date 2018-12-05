<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Widgets;

/**
 * Class WidgetProductDemand
 */
class ProductDemand extends WidgetBase
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
     * @param int $year
     * @param int $month
     * @param int $nLimit
     * @return array
     */
    public function getBotsOfMonth(int $year, int $month, int $nLimit = 10): array
    {
        return $this->oDB->query(
            "SELECT *, COUNT(tbesucherbot.kBesucherBot) AS nAnzahl
                FROM tbesucherarchiv
                LEFT JOIN tbesucherbot
                    ON tbesucherarchiv.kBesucherBot = tbesucherbot.kBesucherBot
                WHERE tbesucherarchiv.kBesucherBot > 0
                    AND YEAR(tbesucherarchiv.dZeit) = '" . $year . "'
                    AND MONTH(tbesucherarchiv.dZeit) = '" . $month . "'
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
