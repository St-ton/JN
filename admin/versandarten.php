<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('ORDER_SHIPMENT_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'versandarten_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'toolsajax_inc.php';
/** @global JTLSmarty $smarty */
TaxHelper::setTaxRates();
$standardwaehrung   = Shop::Container()->getDB()->select('twaehrung', 'cStandard', 'Y');
$versandberechnung  = null;
$cHinweis           = '';
$cFehler            = '';
$step               = 'uebersicht';
$Versandart         = null;
$nSteuersatzKey_arr = array_keys($_SESSION['Steuersatz']);

$missingShippingClassCombis = getMissingShippingClassCombi();
$smarty->assign('missingShippingClassCombis', $missingShippingClassCombis);

if (isset($_POST['neu'], $_POST['kVersandberechnung']) &&
    (int)$_POST['neu'] === 1 &&
    (int)$_POST['kVersandberechnung'] > 0 &&
    FormHelper::validateToken()
) {
    $step = 'neue Versandart';
}
if (isset($_POST['kVersandberechnung']) && (int)$_POST['kVersandberechnung'] > 0 && FormHelper::validateToken()) {
    $versandberechnung = Shop::Container()->getDB()->select('tversandberechnung', 'kVersandberechnung', (int)$_POST['kVersandberechnung']);
}

//we need to flush the options caching group because of gibVersandkostenfreiAb(), baueVersandkostenfreiLaenderString() etc.
if (isset($_POST['del']) && (int)$_POST['del'] > 0 && FormHelper::validateToken() && Versandart::deleteInDB($_POST['del'])) {
    $cHinweis .= 'Versandart erfolgreich gelöscht!';
    Shop::Cache()->flushTags([CACHING_GROUP_OPTION, CACHING_GROUP_ARTICLE]);
}
if (isset($_POST['edit']) && (int)$_POST['edit'] > 0 && FormHelper::validateToken()) {
    $step                        = 'neue Versandart';
    $Versandart                  = Shop::Container()->getDB()->select('tversandart', 'kVersandart', (int)$_POST['edit']);
    $VersandartZahlungsarten     = Shop::Container()->getDB()->selectAll('tversandartzahlungsart', 'kVersandart', (int)$_POST['edit'], '*', 'kZahlungsart');
    $VersandartStaffeln          = Shop::Container()->getDB()->selectAll('tversandartstaffel', 'kVersandart', (int)$_POST['edit'], '*', 'fBis');
    $versandberechnung           = Shop::Container()->getDB()->select('tversandberechnung', 'kVersandberechnung', (int)$Versandart->kVersandberechnung);
    $Versandart->cVersandklassen = trim($Versandart->cVersandklassen);

    $smarty->assign('VersandartZahlungsarten', reorganizeObjectArray($VersandartZahlungsarten, 'kZahlungsart'))
           ->assign('VersandartStaffeln', $VersandartStaffeln)
           ->assign('Versandart', $Versandart)
           ->assign('gewaehlteLaender', explode(' ', $Versandart->cLaender));
}

if (isset($_POST['clone']) && (int)$_POST['clone'] > 0 && FormHelper::validateToken()) {
    $step = 'uebersicht';
    if (Versandart::cloneShipping($_POST['clone'])) {
        $cHinweis .= 'Versandart wurde erfolgreich dupliziert';
        Shop::Cache()->flushTags([CACHING_GROUP_OPTION]);
    } else {
        $cFehler .= 'Versandart konnte nicht dupliziert werden!';
    }
}

if (isset($_GET['cISO'], $_GET['zuschlag'], $_GET['kVersandart']) &&
    (int)$_GET['zuschlag'] === 1 && (int)$_GET['kVersandart'] > 0 && FormHelper::validateToken()) {
    $step = 'Zuschlagsliste';
}

if (isset($_GET['delzus']) && (int)$_GET['delzus'] > 0 && FormHelper::validateToken()) {
    $step = 'Zuschlagsliste';
    Shop::Container()->getDB()->queryPrepared(
        "DELETE tversandzuschlag, tversandzuschlagsprache
            FROM tversandzuschlag
            LEFT JOIN tversandzuschlagsprache
              ON tversandzuschlagsprache.kVersandzuschlag = tversandzuschlag.kVersandzuschlag
            WHERE tversandzuschlag.kVersandzuschlag = :kVersandzuschlag",
        ['kVersandzuschlag' => $_GET['delzus']],
        4
    );
    Shop::Container()->getDB()->delete('tversandzuschlagplz', 'kVersandzuschlag', (int)$_GET['delzus']);
    Shop::Cache()->flushTags([CACHING_GROUP_OPTION, CACHING_GROUP_ARTICLE]);
    $cHinweis .= 'Zuschlagsliste erfolgreich gelöscht!';
}
// Zuschlagliste editieren
if (RequestHelper::verifyGPCDataInt('editzus') > 0 && FormHelper::validateToken()) {
    $kVersandzuschlag = RequestHelper::verifyGPCDataInt('editzus');
    $cISO             = StringHandler::convertISO6392ISO(RequestHelper::verifyGPDataString('cISO'));

    if ($kVersandzuschlag > 0 && (strlen($cISO) > 0 && $cISO !== 'noISO')) {
        $step             = 'Zuschlagsliste';
        $oVersandzuschlag = Shop::Container()->getDB()->select('tversandzuschlag', 'kVersandzuschlag', $kVersandzuschlag);
        if (isset($oVersandzuschlag->kVersandzuschlag) && $oVersandzuschlag->kVersandzuschlag > 0) {
            $oVersandzuschlag->oVersandzuschlagSprache_arr = [];
            $oVersandzuschlagSprache_arr                   = Shop::Container()->getDB()->selectAll(
                'tversandzuschlagsprache',
                'kVersandzuschlag',
                (int)$oVersandzuschlag->kVersandzuschlag
            );
            if (is_array($oVersandzuschlagSprache_arr) && count($oVersandzuschlagSprache_arr) > 0) {
                foreach ($oVersandzuschlagSprache_arr as $oVersandzuschlagSprache) {
                    $oVersandzuschlag->oVersandzuschlagSprache_arr[$oVersandzuschlagSprache->cISOSprache] = $oVersandzuschlagSprache;
                }
            }
        }
        $smarty->assign('oVersandzuschlag', $oVersandzuschlag);
    }
}

if (isset($_GET['delplz']) && (int)$_GET['delplz'] > 0 && FormHelper::validateToken()) {
    $step = 'Zuschlagsliste';
    Shop::Container()->getDB()->delete('tversandzuschlagplz', 'kVersandzuschlagPlz', (int)$_GET['delplz']);
    Shop::Cache()->flushTags([CACHING_GROUP_OPTION, CACHING_GROUP_ARTICLE]);
    $cHinweis .= 'PLZ/PLZ-Bereich erfolgreich gelöscht.';
}

if (isset($_POST['neueZuschlagPLZ']) && (int)$_POST['neueZuschlagPLZ'] === 1 && FormHelper::validateToken()) {
    $step          = 'Zuschlagsliste';
    $oZipValidator = new ZipValidator($_POST['cISO']);
    $ZuschlagPLZ   = new stdClass();

    $ZuschlagPLZ->kVersandzuschlag = (int)$_POST['kVersandzuschlag'];
    if (!empty($_POST['cPLZ'])) {
        $ZuschlagPLZ->cPLZ = $oZipValidator->validateZip($_POST['cPLZ']);
    }
    if (!empty($_POST['cPLZAb']) && !empty($_POST['cPLZBis'])) {
        unset($ZuschlagPLZ->cPLZ);
        $ZuschlagPLZ->cPLZAb  = $oZipValidator->validateZip($_POST['cPLZAb']);
        $ZuschlagPLZ->cPLZBis = $oZipValidator->validateZip($_POST['cPLZBis']);
        if ($ZuschlagPLZ->cPLZAb > $ZuschlagPLZ->cPLZBis) {
            $ZuschlagPLZ->cPLZAb  = $oZipValidator->validateZip($_POST['cPLZBis']);
            $ZuschlagPLZ->cPLZBis = $oZipValidator->validateZip($_POST['cPLZAb']);
        }
    }

    $versandzuschlag = Shop::Container()->getDB()->select('tversandzuschlag', 'kVersandzuschlag', (int)$ZuschlagPLZ->kVersandzuschlag);

    if (!empty($ZuschlagPLZ->cPLZ) || !empty($ZuschlagPLZ->cPLZAb)) {
        //schaue, ob sich PLZ ueberschneiden
        if (!empty($ZuschlagPLZ->cPLZ)) {
            $plz_x = Shop::Container()->getDB()->queryPrepared(
                "SELECT tversandzuschlagplz.*
                    FROM tversandzuschlagplz, tversandzuschlag
                    WHERE (tversandzuschlagplz.cPLZ = :surchargeZip
                        OR :surchargeZip BETWEEN tversandzuschlagplz.cPLZAb AND tversandzuschlagplz.cPLZBis)
                        AND tversandzuschlagplz.kVersandzuschlag = tversandzuschlag.kVersandzuschlag
                        AND tversandzuschlag.cISO = :surchargeISO
                        AND tversandzuschlag.kVersandart = :surchargeShipmentMode",
                [
                    'surchargeZip'          => $ZuschlagPLZ->cPLZ,
                    'surchargeISO'          => $versandzuschlag->cISO,
                    'surchargeShipmentMode' => (int)$versandzuschlag->kVersandart
                ],
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
        } else {
            $plz_x = Shop::Container()->getDB()->queryPrepared(
                "SELECT tversandzuschlagplz.*
                    FROM tversandzuschlagplz, tversandzuschlag
                    WHERE (tversandzuschlagplz.cPLZ BETWEEN :surchargeZipFrom AND :surchargeZipTo
                        OR :surchargeZipTo >= tversandzuschlagplz.cPLZAb AND tversandzuschlagplz.cPLZBis >= :surchargeZipFrom)
                        AND tversandzuschlagplz.kVersandzuschlag = tversandzuschlag.kVersandzuschlag
                        AND tversandzuschlag.cISO = :surchargeISO
                        AND tversandzuschlag.kVersandart = :surchargeShipmentMode",
                [
                    'surchargeZipTo'        => $ZuschlagPLZ->cPLZBis,
                    'surchargeZipFrom'      => $ZuschlagPLZ->cPLZAb,
                    'surchargeISO'          => $versandzuschlag->cISO,
                    'surchargeShipmentMode' => (int)$versandzuschlag->kVersandart
                ],
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
        }
        // (string-)merge the possible resulting 'overlaps'
        // (multiple single ZIP or multiple ZIP-ranges)
        $szPLZ = $szPLZRange = $szOverlap = '';
        foreach ($plz_x as $oResult) {
            if (!empty($oResult->cPLZ) && (0 < strlen($szPLZ))) {
                $szPLZ .= ', ' . $oResult->cPLZ;
            } elseif (!empty($oResult->cPLZ) && (0 === strlen($szPLZ))) {
                $szPLZ = $oResult->cPLZ;
            }
            if (!empty($oResult->cPLZAb) && (0 < strlen($szPLZRange))) {
                $szPLZRange .= ', ' . $oResult->cPLZAb . '-' . $oResult->cPLZBis;
            } elseif (!empty($oResult->cPLZAb) && (0 === strlen($szPLZRange))) {
                $szPLZRange = $oResult->cPLZAb . '-' . $oResult->cPLZBis;
            }
        }
        if ((0 < strlen($szPLZ)) && (0 < strlen($szPLZRange))) {
            $szOverlap = $szPLZ . ' und ' . $szPLZRange;
        } else {
            $szOverlap = (0 < strlen($szPLZ)) ? $szPLZ : $szPLZRange;
        }
        // form an error-string, if there are any errors, or insert the input into the DB
        if (0 < strlen($szOverlap)) {
            $cFehler = '&nbsp;';
            if (!empty($ZuschlagPLZ->cPLZ)) {
                $cFehler .= "Die PLZ $ZuschlagPLZ->cPLZ";
            } else {
                $cFehler .= "Der PLZ-Bereich $ZuschlagPLZ->cPLZAb-$ZuschlagPLZ->cPLZBis";
            }
            $cFehler .= " überschneidet sich mit $szOverlap.<br>Bitte geben Sie eine andere PLZ / PLZ Bereich an.";
        } elseif (Shop::Container()->getDB()->insert('tversandzuschlagplz', $ZuschlagPLZ)) {
            $cHinweis .= "PLZ wurde erfolgreich hinzugefügt.";
        }
        Shop::Cache()->flushTags([CACHING_GROUP_OPTION]);
    } else {
        $szErrorString = $oZipValidator->getError();
        if ('' !== $szErrorString) {
            $cFehler .= $szErrorString;
        } else {
            $cFehler .= "Sie müssen eine PLZ oder einen PLZ-Bereich angeben!";
        }
    }
}

if (isset($_POST['neuerZuschlag']) && (int)$_POST['neuerZuschlag'] === 1 && FormHelper::validateToken()) {
    $step     = 'Zuschlagsliste';
    $Zuschlag = new stdClass();
    if (RequestHelper::verifyGPCDataInt('kVersandzuschlag') > 0) {
        $Zuschlag->kVersandzuschlag = RequestHelper::verifyGPCDataInt('kVersandzuschlag');
    }

    $Zuschlag->kVersandart = (int)$_POST['kVersandart'];
    $Zuschlag->cISO        = $_POST['cISO'];
    $Zuschlag->cName       = htmlspecialchars($_POST['cName'], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
    $Zuschlag->fZuschlag   = (float)str_replace(',', '.', $_POST['fZuschlag']);
    if ($Zuschlag->cName && $Zuschlag->fZuschlag != 0) {
        $kVersandzuschlag = 0;
        if (isset($Zuschlag->kVersandzuschlag) && $Zuschlag->kVersandzuschlag > 0) {
            Shop::Container()->getDB()->delete('tversandzuschlag', 'kVersandzuschlag', (int)$Zuschlag->kVersandzuschlag);
        }
        if (($kVersandzuschlag = Shop::Container()->getDB()->insert('tversandzuschlag', $Zuschlag)) > 0) {
            $cHinweis .= 'Zuschlagsliste wurde erfolgreich hinzugefügt.';
        }
        if (isset($Zuschlag->kVersandzuschlag) && $Zuschlag->kVersandzuschlag > 0) {
            $kVersandzuschlag = $Zuschlag->kVersandzuschlag;
        }
        $sprachen        = Sprache::getAllLanguages();
        $zuschlagSprache = new stdClass();

        $zuschlagSprache->kVersandzuschlag = $kVersandzuschlag;
        foreach ($sprachen as $sprache) {
            $zuschlagSprache->cISOSprache = $sprache->cISO;
            $zuschlagSprache->cName       = $Zuschlag->cName;
            if ($_POST['cName_' . $sprache->cISO]) {
                $zuschlagSprache->cName = $_POST['cName_' . $sprache->cISO];
            }

            Shop::Container()->getDB()->delete(
                'tversandzuschlagsprache',
                ['kVersandzuschlag', 'cISOSprache'],
                [(int)$kVersandzuschlag, $sprache->cISO]
            );
            Shop::Container()->getDB()->insert('tversandzuschlagsprache', $zuschlagSprache);
        }
        Shop::Cache()->flushTags([CACHING_GROUP_OPTION]);
    } else {
        if (!$Zuschlag->cName) {
            $cFehler .= "Bitte geben Sie der Zuschlagsliste einen Namen! ";
        }
        if (!$Zuschlag->fZuschlag) {
            $cFehler .= "Bitte geben Sie einen Preis für den Zuschlag ein! ";
        }
    }
}

if (isset($_POST['neueVersandart']) && (int)$_POST['neueVersandart'] > 0 && FormHelper::validateToken()) {
    $Versandart                           = new stdClass();
    $Versandart->cName                    = htmlspecialchars($_POST['cName'], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
    $Versandart->kVersandberechnung       = (int)$_POST['kVersandberechnung'];
    $Versandart->cAnzeigen                = $_POST['cAnzeigen'];
    $Versandart->cBild                    = $_POST['cBild'];
    $Versandart->nSort                    = (int)$_POST['nSort'];
    $Versandart->nMinLiefertage           = (int)$_POST['nMinLiefertage'];
    $Versandart->nMaxLiefertage           = (int)$_POST['nMaxLiefertage'];
    $Versandart->cNurAbhaengigeVersandart = $_POST['cNurAbhaengigeVersandart'];
    $Versandart->cSendConfirmationMail    = $_POST['cSendConfirmationMail'] ?? 'Y';
    $Versandart->cIgnoreShippingProposal  = $_POST['cIgnoreShippingProposal'] ?? 'N';
    $Versandart->eSteuer                  = $_POST['eSteuer'];
    $Versandart->fPreis                   = (float)str_replace(',', '.', $_POST['fPreis'] ?? 0);
    // Versandkostenfrei ab X
    $Versandart->fVersandkostenfreiAbX = (isset($_POST['versandkostenfreiAktiv']) && (int)$_POST['versandkostenfreiAktiv'] === 1)
        ? (float)$_POST['fVersandkostenfreiAbX']
        : 0;
    // Deckelung
    $Versandart->fDeckelung = (isset($_POST['versanddeckelungAktiv']) && (int)$_POST['versanddeckelungAktiv'] === 1)
        ? (float)$_POST['fDeckelung']
        : 0;

    $Versandart->cLaender = '';
    $Laender              = $_POST['land'];
    if (is_array($Laender)) {
        foreach ($Laender as $Land) {
            $Versandart->cLaender .= $Land . ' ';
        }
    }

    $VersandartZahlungsarten = [];
    if (isset($_POST['kZahlungsart']) && is_array($_POST['kZahlungsart'])) {
        foreach ($_POST['kZahlungsart'] as $kZahlungsart) {
            $versandartzahlungsart               = new stdClass();
            $versandartzahlungsart->kZahlungsart = $kZahlungsart;
            if ($_POST['fAufpreis_' . $kZahlungsart] != 0) {
                $versandartzahlungsart->fAufpreis    = (float)str_replace(',', '.', $_POST['fAufpreis_' . $kZahlungsart]);
                $versandartzahlungsart->cAufpreisTyp = $_POST['cAufpreisTyp_' . $kZahlungsart];
            }
            $VersandartZahlungsarten[] = $versandartzahlungsart;
        }
    }

    $VersandartStaffeln        = [];
    $fVersandartStaffelBis_arr = []; // Haelt alle fBis der Staffel
    $staffelDa                 = true;
    $bVersandkostenfreiGueltig = true;
    $fMaxVersandartStaffelBis  = 0;
    if ($versandberechnung->cModulId === 'vm_versandberechnung_gewicht_jtl'
        || $versandberechnung->cModulId === 'vm_versandberechnung_warenwert_jtl'
        || $versandberechnung->cModulId === 'vm_versandberechnung_artikelanzahl_jtl'
    ) {
        $staffelDa = false;
        if (count($_POST['bis']) > 0 && count($_POST['preis']) > 0) {
            $staffelDa = true;
        }
        //preisstaffel beachten
        if (!isset($_POST['bis'][0])
            || strlen($_POST['bis'][0]) === 0
            || !isset($_POST['preis'][0])
            || strlen($_POST['preis'][0]) === 0
        ) {
            $staffelDa = false;
        }
        if (is_array($_POST['bis']) && is_array($_POST['preis'])) {
            foreach ($_POST['bis'] as $i => $fBis) {
                if (isset($_POST['preis'][$i]) && strlen($fBis) > 0) {
                    unset($oVersandstaffel);
                    $oVersandstaffel         = new stdClass();
                    $oVersandstaffel->fBis   = (float)str_replace(',', '.', $fBis);
                    $oVersandstaffel->fPreis = (float)str_replace(',', '.', $_POST['preis'][$i]);

                    $VersandartStaffeln[]        = $oVersandstaffel;
                    $fVersandartStaffelBis_arr[] = $oVersandstaffel->fBis;
                }
            }
        }
        // Dummy Versandstaffel hinzufuegen, falls Versandart nach Warenwert und Versandkostenfrei ausgewaehlt wurde
        if ($versandberechnung->cModulId === 'vm_versandberechnung_warenwert_jtl' && $Versandart->fVersandkostenfreiAbX > 0) {
            $oVersandstaffel         = new stdClass();
            $oVersandstaffel->fBis   = 999999999;
            $oVersandstaffel->fPreis = 0.0;
            $VersandartStaffeln[]    = $oVersandstaffel;
        }
    }
    // Kundengruppe
    $Versandart->cKundengruppen = '';
    if (!$_POST['kKundengruppe']) {
        $_POST['kKundengruppe'] = [-1];
    }
    if (is_array($_POST['kKundengruppe'])) {
        if (in_array(-1, $_POST['kKundengruppe'])) {
            $Versandart->cKundengruppen = '-1';
        } else {
            $Versandart->cKundengruppen = ';' . implode(';', $_POST['kKundengruppe']) . ';';
        }
    }
    //Versandklassen
    $Versandart->cVersandklassen = ((!empty($_POST['kVersandklasse']) && $_POST['kVersandklasse'] !== '-1') ? ' ' . $_POST['kVersandklasse'] . ' ' : '-1');

    if (count($_POST['land']) >= 1 && count($_POST['kZahlungsart']) >= 1 &&
        $Versandart->cName && $staffelDa && $bVersandkostenfreiGueltig) {
        $kVersandart = 0;
        if ((int)$_POST['kVersandart'] === 0) {
            $kVersandart = Shop::Container()->getDB()->insert('tversandart', $Versandart);
            $cHinweis   .= "Die Versandart <strong>$Versandart->cName</strong> wurde erfolgreich hinzugefügt. ";
        } else {
            //updaten
            $kVersandart = (int)$_POST['kVersandart'];
            Shop::Container()->getDB()->update('tversandart', 'kVersandart', $kVersandart, $Versandart);
            Shop::Container()->getDB()->delete('tversandartzahlungsart', 'kVersandart', $kVersandart);
            Shop::Container()->getDB()->delete('tversandartstaffel', 'kVersandart', $kVersandart);
            $cHinweis .= "Die Versandart <strong>$Versandart->cName</strong> wurde erfolgreich geändert.";
        }
        if ($kVersandart > 0) {
            foreach ($VersandartZahlungsarten as $versandartzahlungsart) {
                $versandartzahlungsart->kVersandart = $kVersandart;
                Shop::Container()->getDB()->insert('tversandartzahlungsart', $versandartzahlungsart);
            }

            foreach ($VersandartStaffeln as $versandartstaffel) {
                $versandartstaffel->kVersandart = $kVersandart;
                Shop::Container()->getDB()->insert('tversandartstaffel', $versandartstaffel);
            }
            $sprachen       = Sprache::getAllLanguages();
            $versandSprache = new stdClass();

            $versandSprache->kVersandart = $kVersandart;
            foreach ($sprachen as $sprache) {
                $versandSprache->cISOSprache = $sprache->cISO;
                $versandSprache->cName       = $Versandart->cName;
                if ($_POST['cName_' . $sprache->cISO]) {
                    $versandSprache->cName = htmlspecialchars($_POST['cName_' . $sprache->cISO], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
                }
                $versandSprache->cLieferdauer = '';
                if ($_POST['cLieferdauer_' . $sprache->cISO]) {
                    $versandSprache->cLieferdauer = htmlspecialchars($_POST['cLieferdauer_' . $sprache->cISO], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
                }
                $versandSprache->cHinweistext = '';
                if ($_POST['cHinweistext_' . $sprache->cISO]) {
                    $versandSprache->cHinweistext = $_POST['cHinweistext_' . $sprache->cISO];
                }
                $versandSprache->cHinweistextShop = '';
                if ($_POST['cHinweistextShop_' . $sprache->cISO]) {
                    $versandSprache->cHinweistextShop = $_POST['cHinweistextShop_' . $sprache->cISO];
                }
                Shop::Container()->getDB()->delete('tversandartsprache', ['kVersandart', 'cISOSprache'], [$kVersandart, $sprache->cISO]);
                Shop::Container()->getDB()->insert('tversandartsprache', $versandSprache);
            }
            $step = 'uebersicht';
        }
        Shop::Cache()->flushTags([CACHING_GROUP_OPTION, CACHING_GROUP_ARTICLE]);
    } else {
        $step = 'neue Versandart';
        if (!$Versandart->cName) {
            $cFehler .= '<p>Bitte geben Sie dieser Versandart einen Namen!</p>';
        }
        if (count($_POST['land']) < 1) {
            $cFehler .= '<p>Bitte mindestens ein Versandland ankreuzen!</p>';
        }
        if (count($_POST['kZahlungsart']) < 1) {
            $cFehler .= '<p>Bitte mindestens eine akzeptierte Zahlungsart auswählen!</p>';
        }
        if (!$staffelDa) {
            $cFehler .= '<p>Bitte mindestens einen Staffelpreis angeben!</p>';
        }
        if (!$bVersandkostenfreiGueltig) {
            $cFehler .= '<p>Ihr Versandkostenfrei Wert darf maximal ' . $fMaxVersandartStaffelBis . ' sein!</p>';
        }
        if ((int)$_POST['kVersandart'] > 0) {
            $Versandart = Shop::Container()->getDB()->select('tversandart', 'kVersandart', (int)$_POST['kVersandart']);
        }
        $smarty->assign('cHinweis', $cHinweis)
               ->assign('cFehler', $cFehler)
               ->assign('VersandartZahlungsarten', reorganizeObjectArray($VersandartZahlungsarten, 'kZahlungsart'))
               ->assign('VersandartStaffeln', $VersandartStaffeln)
               ->assign('Versandart', $Versandart)
               ->assign('gewaehlteLaender', explode(' ', $Versandart->cLaender));
    }
}

if ($step === 'neue Versandart') {
    $versandlaender = Shop::Container()->getDB()->query(
        'SELECT *, cDeutsch AS cName FROM tland ORDER BY cDeutsch', 
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    if ($versandberechnung->cModulId === 'vm_versandberechnung_gewicht_jtl') {
        $smarty->assign('einheit', 'kg');
    }
    if ($versandberechnung->cModulId === 'vm_versandberechnung_warenwert_jtl') {
        $smarty->assign('einheit', $standardwaehrung->cName);
    }
    if ($versandberechnung->cModulId === 'vm_versandberechnung_artikelanzahl_jtl') {
        $smarty->assign('einheit', 'Stück');
    }
    $zahlungsarten      = Shop::Container()->getDB()->selectAll('tzahlungsart', 'nActive', 1, '*', 'cAnbieter, nSort, cName');
    $oVersandklasse_arr = Shop::Container()->getDB()->selectAll('tversandklasse', [], [], '*', 'kVersandklasse');
    $smarty->assign('versandKlassen', $oVersandklasse_arr);
    $kVersandartTMP = 0;
    if (isset($Versandart->kVersandart) && $Versandart->kVersandart > 0) {
        $kVersandartTMP = $Versandart->kVersandart;
    }

    $sprachen = Sprache::getAllLanguages();
    $smarty->assign('sprachen', $sprachen)
           ->assign('zahlungsarten', $zahlungsarten)
           ->assign('versandlaender', $versandlaender)
           ->assign('versandberechnung', $versandberechnung)
           ->assign('waehrung', $standardwaehrung->cName)
           ->assign('kundengruppen', Shop::Container()->getDB()->query(
               'SELECT kKundengruppe, cName FROM tkundengruppe ORDER BY kKundengruppe',
               \DB\ReturnType::ARRAY_OF_OBJECTS
           ))
           ->assign('oVersandartSpracheAssoc_arr', getShippingLanguage($kVersandartTMP, $sprachen))
           ->assign('gesetzteVersandklassen', isset($Versandart->cVersandklassen)
               ? gibGesetzteVersandklassen($Versandart->cVersandklassen)
               : null)
           ->assign('gesetzteKundengruppen', isset($Versandart->cKundengruppen)
               ? gibGesetzteKundengruppen($Versandart->cKundengruppen)
               : null);
}

if ($step === 'uebersicht') {
    $oKundengruppen_arr  = Shop::Container()->getDB()->query(
        'SELECT kKundengruppe, cName FROM tkundengruppe ORDER BY kKundengruppe',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    $versandberechnungen = Shop::Container()->getDB()->query(
        'SELECT * FROM tversandberechnung ORDER BY cName',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    $versandarten        = Shop::Container()->getDB()->query(
        'SELECT * FROM tversandart ORDER BY nSort, cName',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    $vCount              = count($versandarten);
    for ($i = 0; $i < $vCount; $i++) {
        $versandarten[$i]->versandartzahlungsarten = Shop::Container()->getDB()->query(
            "SELECT tversandartzahlungsart.*
                FROM tversandartzahlungsart
                JOIN tzahlungsart ON tzahlungsart.kZahlungsart = tversandartzahlungsart.kZahlungsart
                WHERE tversandartzahlungsart.kVersandart = " . (int)$versandarten[$i]->kVersandart . "
                ORDER BY tzahlungsart.cAnbieter, tzahlungsart.nSort, tzahlungsart.cName",
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );

        $count = count($versandarten[$i]->versandartzahlungsarten);
        for ($o = 0; $o < $count; $o++) {
            $versandarten[$i]->versandartzahlungsarten[$o]->zahlungsart = Shop::Container()->getDB()->select(
                'tzahlungsart',
                'kZahlungsart',
                (int)$versandarten[$i]->versandartzahlungsarten[$o]->kZahlungsart,
                'nActive',
                1
            );
            if ($versandarten[$i]->versandartzahlungsarten[$o]->cAufpreisTyp === 'prozent') {
                $versandarten[$i]->versandartzahlungsarten[$o]->cAufpreisTyp = '%';
            } else {
                $versandarten[$i]->versandartzahlungsarten[$o]->cAufpreisTyp = '';
            }
        }
        $versandarten[$i]->versandartstaffeln = Shop::Container()->getDB()->selectAll(
            'tversandartstaffel',
            'kVersandart',
            (int)$versandarten[$i]->kVersandart,
            '*',
            'fBis'
        );
        // Berechne Brutto
        $versandarten[$i]->fPreisBrutto               = berechneVersandpreisBrutto(
            $versandarten[$i]->fPreis,
            $_SESSION['Steuersatz'][$nSteuersatzKey_arr[0]]
        );
        $versandarten[$i]->fVersandkostenfreiAbXNetto = berechneVersandpreisNetto(
            $versandarten[$i]->fVersandkostenfreiAbX,
            $_SESSION['Steuersatz'][$nSteuersatzKey_arr[0]]
        );
        $versandarten[$i]->fDeckelungBrutto           = berechneVersandpreisBrutto(
            $versandarten[$i]->fDeckelung,
            $_SESSION['Steuersatz'][$nSteuersatzKey_arr[0]]
        );

        if (is_array($versandarten[$i]->versandartstaffeln) && count($versandarten[$i]->versandartstaffeln) > 0) {
            foreach ($versandarten[$i]->versandartstaffeln as $j => $oVersandartstaffeln) {
                $versandarten[$i]->versandartstaffeln[$j]->fPreisBrutto = berechneVersandpreisBrutto(
                    $oVersandartstaffeln->fPreis,
                    $_SESSION['Steuersatz'][$nSteuersatzKey_arr[0]]
                );
            }
        }

        $versandarten[$i]->versandberechnung = Shop::Container()->getDB()->select(
            'tversandberechnung',
            'kVersandberechnung',
            (int)$versandarten[$i]->kVersandberechnung
        );
        $versandarten[$i]->versandklassen    = gibGesetzteVersandklassenUebersicht($versandarten[$i]->cVersandklassen);
        if ($versandarten[$i]->versandberechnung->cModulId === 'vm_versandberechnung_gewicht_jtl') {
            $versandarten[$i]->einheit = 'kg';
        }
        if ($versandarten[$i]->versandberechnung->cModulId === 'vm_versandberechnung_warenwert_jtl') {
            $versandarten[$i]->einheit = $standardwaehrung->cName;
        }
        if ($versandarten[$i]->versandberechnung->cModulId === 'vm_versandberechnung_artikelanzahl_jtl') {
            $versandarten[$i]->einheit = 'Stück';
        }
        $versandarten[$i]->land_arr = explode(' ', $versandarten[$i]->cLaender);
        $count                      = count($versandarten[$i]->land_arr);
        for ($o = 0; $o < $count; $o++) {
            unset($zuschlag);
            $zuschlag = Shop::Container()->getDB()->select(
                'tversandzuschlag',
                'cISO',
                $versandarten[$i]->land_arr[$o],
                'kVersandart',
                (int)$versandarten[$i]->kVersandart
            );
            if (isset($zuschlag->kVersandart) && $zuschlag->kVersandart > 0) {
                $versandarten[$i]->zuschlag_arr[$versandarten[$i]->land_arr[$o]] = '(Zuschlag)';
            }
        }
        $versandarten[$i]->cKundengruppenName_arr  = [];
        $kKundengruppe_arr                         = explode(';', $versandarten[$i]->cKundengruppen);
        $versandarten[$i]->oVersandartSprachen_arr = Shop::Container()->getDB()->selectAll(
            'tversandartsprache',
            'kVersandart',
            (int)$versandarten[$i]->kVersandart,
            'cName',
            'cISOSprache'
        );

        if (is_array($kKundengruppe_arr)) {
            foreach ($kKundengruppe_arr as $kKundengruppe) {
                if ($kKundengruppe == '-1') {
                    $versandarten[$i]->cKundengruppenName_arr[] = 'Alle';
                } else {
                    foreach ($oKundengruppen_arr as $oKundengruppen) {
                        if ($oKundengruppen->kKundengruppe == $kKundengruppe) {
                            $versandarten[$i]->cKundengruppenName_arr[] = $oKundengruppen->cName;
                        }
                    }
                }
            }
        }
    }

    if (!empty($missingShippingClassCombis)) {
        $cFehler .= $smarty->assign('missingShippingClassCombis', $missingShippingClassCombis)
                           ->fetch('tpl_inc/versandarten_fehlende_kombis.tpl');
    }

    $smarty->assign('versandberechnungen', $versandberechnungen)
           ->assign('versandarten', $versandarten)
           ->assign('waehrung', $standardwaehrung->cName)
           ->assign('cHinweis', $cHinweis)
           ->assign('cFehler', $cFehler);
}

if ($step === 'Zuschlagsliste') {
    $cISO = isset($_GET['cISO']) ? Shop::Container()->getDB()->escape($_GET['cISO']) : null;
    if (isset($_POST['cISO'])) {
        $cISO = Shop::Container()->getDB()->escape($_POST['cISO']);
    }
    $kVersandart = isset($_GET['kVersandart']) ? (int)$_GET['kVersandart'] : 0;
    if (isset($_POST['kVersandart'])) {
        $kVersandart = (int)$_POST['kVersandart'];
    }
    $Versandart = Shop::Container()->getDB()->select('tversandart', 'kVersandart', $kVersandart);
    $Zuschlaege = Shop::Container()->getDB()->selectAll(
        'tversandzuschlag',
        ['kVersandart', 'cISO'],
        [(int)$Versandart->kVersandart , $cISO],
        '*',
        'fZuschlag'
    );
    $zCount     = count($Zuschlaege);
    for ($i = 0; $i < $zCount; $i++) {
        $Zuschlaege[$i]->zuschlagplz     = Shop::Container()->getDB()->selectAll(
            'tversandzuschlagplz',
            'kVersandzuschlag',
            $Zuschlaege[$i]->kVersandzuschlag
        );
        $Zuschlaege[$i]->angezeigterName = getZuschlagNames($Zuschlaege[$i]->kVersandzuschlag);
    }
    $smarty->assign('Versandart', $Versandart)
           ->assign('Zuschlaege', $Zuschlaege)
           ->assign('waehrung', $standardwaehrung->cName)
           ->assign('Land', Shop::Container()->getDB()->select('tland', 'cISO', $cISO))
           ->assign('cHinweis', $cHinweis)
           ->assign('cFehler', $cFehler)
           ->assign('sprachen', Sprache::getAllLanguages());
}

$smarty->assign('fSteuersatz', $_SESSION['Steuersatz'][$nSteuersatzKey_arr[0]])
       ->assign('oWaehrung', Shop::Container()->getDB()->select('twaehrung', 'cStandard', 'Y'))
       ->assign('step', $step)
       ->display('versandarten.tpl');
