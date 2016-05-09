<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
if (intval($_GET['kArtikel']) > 0 && intval($_GET['kKundengruppe']) > 0 && intval($_GET['kSteuerklasse']) > 0) {
    require_once dirname(__FILE__) . '/globalinclude.php';
    require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.PreisverlaufGraph.php';
    //session starten
    $session       = Session::getInstance();
    $Einstellungen = Shop::getSettings(array(CONF_PREISVERLAUF));
    $oConfig_arr   = Shop::DB()->query("SELECT * FROM teinstellungen WHERE kEinstellungenSektion = " . CONF_PREISVERLAUF, 2);
    $kArtikel      = intval($_GET['kArtikel']);
    $kKundengruppe = intval($_GET['kKundengruppe']);
    $kSteuerklasse = intval($_GET['kSteuerklasse']);
    $nMonat        = intval($Einstellungen['preisverlauf']['preisverlauf_anzahl_monate']);

    if (count($oConfig_arr) > 0) {
        if (!isset($oPreisConfig)) {
            $oPreisConfig = new stdClass();
        }
        $oPreisConfig->Waehrung = $_SESSION['Waehrung']->cName;
        if ($_SESSION['Kundengruppe']->nNettoPreise == 1) {
            $oPreisConfig->Netto = 0;
        } else {
            $oPreisConfig->Netto = $_SESSION['Steuersatz'][$kSteuerklasse];
        }
        $oPreisverlauf = Shop::DB()->query(
            "SELECT kPreisverlauf
                FROM tpreisverlauf
                WHERE kArtikel = " . $kArtikel . "
                    AND kKundengruppe = " . $kKundengruppe . "
                    AND DATE_SUB(now(), INTERVAL " . $nMonat . " MONTH) < dDate
                LIMIT 1", 1
        );

        if (isset($oPreisverlauf->kPreisverlauf) && $oPreisverlauf->kPreisverlauf > 0) {
            $oPreisverlaufGraph                      = new PreisverlaufGraph($kArtikel, $kKundengruppe, $nMonat, $oConfig_arr, $oPreisConfig);
            $oPreisverlaufGraph->cSchriftverzeichnis = PFAD_ROOT . 'includes/fonts/';
            $oPreisverlaufGraph->zeichneGraphen();
        }
    }
}
