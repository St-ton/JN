<?php // @deprecated since 5.0.0

use JTL\Catalog\Product\PreisverlaufGraph;
use JTL\Helpers\Request;
use JTL\Session\Frontend;
use JTL\Shop;

if (Request::getInt('kArtikel') > 0 && Request::getInt('kKundengruppe') > 0 && Request::getInt('kSteuerklasse') > 0) {
    require_once __DIR__ . '/globalinclude.php';
    $session               = Frontend::getInstance();
    $productID             = Request::getInt('kArtikel');
    $cgID                  = Request::getInt('kKundengruppe');
    $priceConfig           = new stdClass();
    $priceConfig->Waehrung = Frontend::getCurrency()->getName();
    $priceConfig->Netto    = Frontend::getCustomerGroup()->isMerchant()
        ? 0
        : $_SESSION['Steuersatz'][Request::getInt('kSteuerklasse')];
    $month                 = Shop::getSettingValue(CONF_PREISVERLAUF, 'preisverlauf_anzahl_monate');
    $history               = Shop::Container()->getDB()->getSingleObject(
        'SELECT kPreisverlauf
            FROM tpreisverlauf
            WHERE kArtikel = :pid
                AND kKundengruppe = :cgid
                AND DATE_SUB(NOW(), INTERVAL :mth MONTH) < dDate
            LIMIT 1',
        [
            'pid'  => $productID,
            'cgid' => $cgID,
            'mth'  => $month
        ]
    );

    if (isset($history->kPreisverlauf) && $history->kPreisverlauf > 0) {
        $graph                      = new PreisverlaufGraph(
            $productID,
            $cgID,
            $month,
            Shop::getConfig([CONF_PREISVERLAUF]),
            $priceConfig
        );
        $graph->cSchriftverzeichnis = PFAD_ROOT . PFAD_FONTS;
        $graph->zeichneGraphen();
    }
}
