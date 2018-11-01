<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

if ((int)$_GET['kArtikel'] > 0 && (int)$_GET['kKundengruppe'] > 0 && (int)$_GET['kSteuerklasse'] > 0) {
    require_once __DIR__ . '/globalinclude.php';
    $session       = \Session\Session::getInstance();
    $conf          = Shop::Container()->getDB()->selectAll(
        'teinstellungen',
        'kEinstellungenSektion',
        CONF_PREISVERLAUF
    );
    $kArtikel      = (int)$_GET['kArtikel'];
    $kKundengruppe = (int)$_GET['kKundengruppe'];
    $kSteuerklasse = (int)$_GET['kSteuerklasse'];
    $nMonat        = Shop::getConfigValue(CONF_PREISVERLAUF, 'preisverlauf_anzahl_monate');

    if (count($conf) > 0) {
        $oPreisConfig           = new stdClass();
        $oPreisConfig->Waehrung = Session::Currency()->getName();
        $oPreisConfig->Netto    = Session::CustomerGroup()->isMerchant()
            ? 0
            : $_SESSION['Steuersatz'][$kSteuerklasse];
        $oPreisverlauf          = Shop::Container()->getDB()->query(
            'SELECT kPreisverlauf
                FROM tpreisverlauf
                WHERE kArtikel = ' . $kArtikel . '
                    AND kKundengruppe = ' . $kKundengruppe . '
                    AND DATE_SUB(NOW(), INTERVAL ' . $nMonat . ' MONTH) < dDate
                LIMIT 1',
            \DB\ReturnType::SINGLE_OBJECT
        );

        if (isset($oPreisverlauf->kPreisverlauf) && $oPreisverlauf->kPreisverlauf > 0) {
            $oPreisverlaufGraph                      = new PreisverlaufGraph(
                $kArtikel,
                $kKundengruppe,
                $nMonat,
                $conf,
                $oPreisConfig
            );
            $oPreisverlaufGraph->cSchriftverzeichnis = PFAD_ROOT . 'includes/fonts/';
            $oPreisverlaufGraph->zeichneGraphen();
        }
    }
}
