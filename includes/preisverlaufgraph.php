<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

if ((int)$_GET['kArtikel'] > 0 && (int)$_GET['kKundengruppe'] > 0 && (int)$_GET['kSteuerklasse'] > 0) {
    require_once __DIR__ . '/globalinclude.php';
    $session               = \Session\Frontend::getInstance();
    $productID             = (int)$_GET['kArtikel'];
    $cgID                  = (int)$_GET['kKundengruppe'];
    $priceConfig           = new stdClass();
    $priceConfig->Waehrung = \Session\Frontend::getCurrency()->getName();
    $priceConfig->Netto    = \Session\Frontend::getCustomerGroup()->isMerchant()
        ? 0
        : $_SESSION['Steuersatz'][(int)$_GET['kSteuerklasse']];
    $history               = Shop::Container()->getDB()->queryPrepared(
        'SELECT kPreisverlauf
            FROM tpreisverlauf
            WHERE kArtikel = :pid
                AND kKundengruppe = :cgid
                AND DATE_SUB(NOW(), INTERVAL :mth MONTH) < dDate
            LIMIT 1',
        [
            'pid'  => $productID,
            'cgid' => $cgID,
            'mth'  => Shop::getSettingValue(CONF_PREISVERLAUF, 'preisverlauf_anzahl_monate')
        ],
        \DB\ReturnType::SINGLE_OBJECT
    );

    if (isset($history->kPreisverlauf) && $history->kPreisverlauf > 0) {
        $graph                      = new PreisverlaufGraph(
            $productID,
            $cgID,
            $nMonat,
            $conf,
            $priceConfig
        );
        $graph->cSchriftverzeichnis = PFAD_ROOT . PFAD_FONTS;
        $graph->zeichneGraphen();
    }
}
