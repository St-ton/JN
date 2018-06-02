<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

if ((int)$_GET['kArtikel'] > 0 && (int)$_GET['kKundengruppe'] > 0 && (int)$_GET['kSteuerklasse'] > 0) {
    require_once __DIR__ . '/globalinclude.php';
    //session starten
    $session       = \Session\Session::getInstance();
    $Einstellungen = Shop::getSettings([CONF_PREISVERLAUF]);
    $oConfig_arr   = Shop::Container()->getDB()->selectAll('teinstellungen', 'kEinstellungenSektion', CONF_PREISVERLAUF);
    $kArtikel      = (int)$_GET['kArtikel'];
    $kKundengruppe = (int)$_GET['kKundengruppe'];
    $kSteuerklasse = (int)$_GET['kSteuerklasse'];
    $nMonat        = (int)$Einstellungen['preisverlauf']['preisverlauf_anzahl_monate'];

    if (count($oConfig_arr) > 0) {
        $oPreisConfig           = new stdClass();
        $oPreisConfig->Waehrung = Session::Currency()->getName();
        $oPreisConfig->Netto    = Session::CustomerGroup()->isMerchant()
            ? 0
            : $_SESSION['Steuersatz'][$kSteuerklasse];
        $oPreisverlauf          = Shop::Container()->getDB()->query(
            "SELECT kPreisverlauf
                FROM tpreisverlauf
                WHERE kArtikel = " . $kArtikel . "
                    AND kKundengruppe = " . $kKundengruppe . "
                    AND DATE_SUB(now(), INTERVAL " . $nMonat . " MONTH) < dDate
                LIMIT 1", 1
        );

        if (isset($oPreisverlauf->kPreisverlauf) && $oPreisverlauf->kPreisverlauf > 0) {
            $oPreisverlaufGraph                      = new PreisverlaufGraph(
                $kArtikel,
                $kKundengruppe,
                $nMonat,
                $oConfig_arr,
                $oPreisConfig
            );
            $oPreisverlaufGraph->cSchriftverzeichnis = PFAD_ROOT . 'includes/fonts/';
            $oPreisverlaufGraph->zeichneGraphen();
        }
    }
}
