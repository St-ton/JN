<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Versandart.php';

$oAccount->permission('ORDER_SHIPMENT_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'versandarten_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'toolsajax_inc.php';
/** @global JTLSmarty $smarty */
setzeSteuersaetze();
$standardwaehrung   = Shop::DB()->select('twaehrung', 'cStandard', 'Y');
$versandberechnung  = null;
$cHinweis           = '';
$cFehler            = '';
$step               = 'uebersicht';
$Versandart         = null;
$nSteuersatzKey_arr = array_keys($_SESSION['Steuersatz']);
$postData           = StringHandler::filterXSS($_POST);
if (isset($postData['neu'], $postData['kVersandberechnung']) &&
    (int)$postData['neu'] === 1 &&
    (int)$postData['kVersandberechnung'] > 0 &&
    validateToken()
) {
    $step = 'neue Versandart';
}
if (isset($postData['kVersandberechnung']) && (int)$postData['kVersandberechnung'] > 0 && validateToken()) {
    $versandberechnung = Shop::DB()->select('tversandberechnung', 'kVersandberechnung', (int)$postData['kVersandberechnung']);
}

//we need to flush the options caching group because of gibVersandkostenfreiAb(), baueVersandkostenfreiLaenderString() etc.
if (isset($postData['del']) && (int)$postData['del'] > 0 && validateToken() && Versandart::deleteInDB($postData['del'])) {
    $cHinweis .= 'Versandart erfolgreich gel&ouml;scht!';
    Shop::Cache()->flushTags([CACHING_GROUP_OPTION, CACHING_GROUP_ARTICLE]);
}
if (isset($postData['edit']) && (int)$postData['edit'] > 0 && validateToken()) {
    $step                        = 'neue Versandart';
    $Versandart                  = Shop::DB()->select('tversandart', 'kVersandart', (int)$postData['edit']);
    $VersandartZahlungsarten     = Shop::DB()->selectAll('tversandartzahlungsart', 'kVersandart', (int)$postData['edit'], '*', 'kZahlungsart');
    $VersandartStaffeln          = Shop::DB()->selectAll('tversandartstaffel', 'kVersandart', (int)$postData['edit'], '*', 'fBis');
    $versandberechnung           = Shop::DB()->select('tversandberechnung', 'kVersandberechnung', (int)$Versandart->kVersandberechnung);
    $Versandart->cVersandklassen = trim($Versandart->cVersandklassen);

    $smarty->assign('VersandartZahlungsarten', reorganizeObjectArray($VersandartZahlungsarten, 'kZahlungsart'))
           ->assign('VersandartStaffeln', $VersandartStaffeln)
           ->assign('Versandart', $Versandart)
           ->assign('gewaehlteLaender', explode(' ', $Versandart->cLaender));
}

if (isset($postData['clone']) && (int)$postData['clone'] > 0 && validateToken()) {
    $step = 'uebersicht';
    if (Versandart::cloneShipping($postData['clone'])) {
        $cHinweis .= 'Versandart wurde erfolgreich dupliziert';
        Shop::Cache()->flushTags([CACHING_GROUP_OPTION]);
    } else {
        $cFehler .= 'Versandart konnte nicht dupliziert werden!';
    }
}

if (isset($_GET['cISO'], $_GET['zuschlag'], $_GET['kVersandart']) &&
    (int)$_GET['zuschlag'] === 1 && (int)$_GET['kVersandart'] > 0 && validateToken()) {
    $step = 'Zuschlagsliste';
}

if (isset($_GET['delzus']) && (int)$_GET['delzus'] > 0 && validateToken()) {
    $step = 'Zuschlagsliste';
    Shop::DB()->queryPrepared(
        "DELETE tversandzuschlag, tversandzuschlagsprache
            FROM tversandzuschlag
            LEFT JOIN tversandzuschlagsprache 
              ON tversandzuschlagsprache.kVersandzuschlag = tversandzuschlag.kVersandzuschlag
            WHERE tversandzuschlag.kVersandzuschlag = :kVersandzuschlag",
        ['kVersandzuschlag' => $_GET['delzus']],
        4
    );
    Shop::DB()->delete('tversandzuschlagplz', 'kVersandzuschlag', (int)$_GET['delzus']);
    Shop::Cache()->flushTags([CACHING_GROUP_OPTION, CACHING_GROUP_ARTICLE]);
    $cHinweis .= 'Zuschlagsliste erfolgreich gel&ouml;scht!';
}
// Zuschlagliste editieren
if (verifyGPCDataInteger('editzus') > 0 && validateToken()) {
    $kVersandzuschlag = verifyGPCDataInteger('editzus');
    $cISO             = StringHandler::convertISO6392ISO(verifyGPDataString('cISO'));

    if ($kVersandzuschlag > 0 && (strlen($cISO) > 0 && $cISO !== 'noISO')) {
        $step             = 'Zuschlagsliste';
        $oVersandzuschlag = Shop::DB()->select('tversandzuschlag', 'kVersandzuschlag', $kVersandzuschlag);
        if (isset($oVersandzuschlag->kVersandzuschlag) && $oVersandzuschlag->kVersandzuschlag > 0) {
            $oVersandzuschlag->oVersandzuschlagSprache_arr = [];
            $oVersandzuschlagSprache_arr                   = Shop::DB()->selectAll(
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

if (isset($_GET['delplz']) && (int)$_GET['delplz'] > 0 && validateToken()) {
    $step = 'Zuschlagsliste';
    Shop::DB()->delete('tversandzuschlagplz', 'kVersandzuschlagPlz', (int)$_GET['delplz']);
    Shop::Cache()->flushTags([CACHING_GROUP_OPTION, CACHING_GROUP_ARTICLE]);
    $cHinweis .= 'PLZ/PLZ-Bereich erfolgreich gel&ouml;scht.';
}

if (isset($postData['neueZuschlagPLZ']) && (int)$postData['neueZuschlagPLZ'] === 1 && validateToken()) {
    $step        = 'Zuschlagsliste';
    $ZuschlagPLZ = new stdClass();
    $ZuschlagPLZ->kVersandzuschlag = (int)$postData['kVersandzuschlag'];
    $ZuschlagPLZ->cPLZ             = $postData['cPLZ'];
    if ($postData['cPLZAb'] && $postData['cPLZBis']) {
        unset($ZuschlagPLZ->cPLZ);
        $ZuschlagPLZ->cPLZAb  = $postData['cPLZAb'];
        $ZuschlagPLZ->cPLZBis = $postData['cPLZBis'];
        if ($ZuschlagPLZ->cPLZAb > $ZuschlagPLZ->cPLZBis) {
            $ZuschlagPLZ->cPLZAb  = $postData['cPLZBis'];
            $ZuschlagPLZ->cPLZBis = $postData['cPLZAb'];
        }
    }

    $versandzuschlag = Shop::DB()->select('tversandzuschlag', 'kVersandzuschlag', (int)$ZuschlagPLZ->kVersandzuschlag);

    if ($ZuschlagPLZ->cPLZ || $ZuschlagPLZ->cPLZAb) {
        //schaue, ob sich PLZ ueberscheiden
        if ($ZuschlagPLZ->cPLZ) {
            $plz_x = Shop::DB()->queryPrepared(
                'SELECT tversandzuschlagplz.*
                    FROM tversandzuschlagplz, tversandzuschlag
                    WHERE (tversandzuschlagplz.cPLZ = :plz
                        OR (tversandzuschlagplz.cPLZAb <= :plz
                        AND tversandzuschlagplz.cPLZBis >= :plz))
                        AND tversandzuschlagplz.kVersandzuschlag != :kVersandzuschlag
                        AND tversandzuschlagplz.kVersandzuschlag = tversandzuschlag.kVersandzuschlag
                        AND tversandzuschlag.cISO = :iso
                        AND tversandzuschlag.kVersandart = :kVersandart',
                [
                    'plz'              => $ZuschlagPLZ->cPLZ,
                    'kVersandzuschlag' => $ZuschlagPLZ->kVersandzuschlag,
                    'iso'              => $versandzuschlag->cISO,
                    'kVersandart'      => $versandzuschlag->kVersandart,
                ],
                1
            );
        } else {
            $plz_x = Shop::DB()->queryPrepared(
                'SELECT tversandzuschlagplz.*
                    FROM tversandzuschlagplz, tversandzuschlag
                    WHERE ((tversandzuschlagplz.cPLZ <= :plzBis
                        AND tversandzuschlagplz.cPLZ >= :plzAb)
                        OR (tversandzuschlagplz.cPLZAb >= :plzAb
                        AND tversandzuschlagplz.cPLZAb <= :plzBis)
                        OR (tversandzuschlagplz.cPLZBis >= :plzAb
                        AND tversandzuschlagplz.cPLZBis <= :plzBis))
                        AND tversandzuschlagplz.kVersandzuschlag != :kVersandzuschlag
                        AND tversandzuschlagplz.kVersandzuschlag = tversandzuschlag.kVersandzuschlag
                        AND tversandzuschlag.cISO = :iso
                        AND tversandzuschlag.kVersandart = :kVersandart',
                [
                    'plzAb'            => $ZuschlagPLZ->cPLZAb,
                    'plzBis'           => $ZuschlagPLZ->cPLZBis,
                    'kVersandzuschlag' => $ZuschlagPLZ->kVersandzuschlag,
                    'iso'              => $versandzuschlag->cISO,
                    'kVersandart'      => $versandzuschlag->kVersandart,
                ],
                1
            );
        }
        if ((isset($plz_x->cPLZ) && $plz_x->cPLZ) || (isset($plz_x->cPLZAb) && $plz_x->cPLZAb)) {
            $cFehler .= "<p>Die PLZ $ZuschlagPLZ->cPLZ bzw der PLZ Bereich $ZuschlagPLZ->cPLZAb - $ZuschlagPLZ->cPLZBis &uuml;berschneidet sich mit PLZ $plz_x->cPLZ bzw.
               PLZ-Bereichen $plz_x->cPLZAb - $plz_x->cPLZBis einer anderen Zuschlagsliste! Bitte geben Sie eine andere PLZ / PLZ Bereich an.</p>";
        } elseif (Shop::DB()->insert('tversandzuschlagplz', $ZuschlagPLZ)) {
            $cHinweis .= "PLZ wurde erfolgreich hinzugef&uuml;gt.";
        }
        Shop::Cache()->flushTags([CACHING_GROUP_OPTION]);
    } else {
        $cFehler .= "Sie m&uuml;ssen eine PLZ oder einen PLZ-Bereich angeben!";
    }
}

if (isset($postData['neuerZuschlag']) && (int)$postData['neuerZuschlag'] === 1 && validateToken()) {
    $step     = 'Zuschlagsliste';
    $Zuschlag = new stdClass();
    if (verifyGPCDataInteger('kVersandzuschlag') > 0) {
        $Zuschlag->kVersandzuschlag = verifyGPCDataInteger('kVersandzuschlag');
    }

    $Zuschlag->kVersandart = (int)$postData['kVersandart'];
    $Zuschlag->cISO        = $postData['cISO'];
    $Zuschlag->cName       = htmlspecialchars($postData['cName'], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
    $Zuschlag->fZuschlag   = (float)str_replace(',', '.', $postData['fZuschlag']);
    if ($Zuschlag->cName && $Zuschlag->fZuschlag != 0) {
        $kVersandzuschlag = 0;
        if (isset($Zuschlag->kVersandzuschlag) && $Zuschlag->kVersandzuschlag > 0) {
            Shop::DB()->delete('tversandzuschlag', 'kVersandzuschlag', (int)$Zuschlag->kVersandzuschlag);
        }
        if (($kVersandzuschlag = Shop::DB()->insert('tversandzuschlag', $Zuschlag)) > 0) {
            $cHinweis .= 'Zuschlagsliste wurde erfolgreich hinzugef&uuml;gt.';
        }
        if (isset($Zuschlag->kVersandzuschlag) && $Zuschlag->kVersandzuschlag > 0) {
            $kVersandzuschlag = $Zuschlag->kVersandzuschlag;
        }
        $sprachen        = gibAlleSprachen();
        $zuschlagSprache = new stdClass();
        $zuschlagSprache->kVersandzuschlag = $kVersandzuschlag;
        foreach ($sprachen as $sprache) {
            $zuschlagSprache->cISOSprache = $sprache->cISO;
            $zuschlagSprache->cName       = $Zuschlag->cName;
            if ($postData['cName_' . $sprache->cISO]) {
                $zuschlagSprache->cName = $postData['cName_' . $sprache->cISO];
            }

            Shop::DB()->delete(
                'tversandzuschlagsprache',
                ['kVersandzuschlag', 'cISOSprache'],
                [(int)$kVersandzuschlag, $sprache->cISO]
            );
            Shop::DB()->insert('tversandzuschlagsprache', $zuschlagSprache);
        }
        Shop::Cache()->flushTags([CACHING_GROUP_OPTION]);
    } else {
        if (!$Zuschlag->cName) {
            $cFehler .= "Bitte geben Sie der Zuschlagsliste einen Namen! ";
        }
        if (!$Zuschlag->fZuschlag) {
            $cFehler .= "Bitte geben Sie einen Preis f&uuml;r den Zuschlag ein! ";
        }
    }
}

if (isset($postData['neueVersandart']) && (int)$postData['neueVersandart'] > 0 && validateToken()) {
    $Versandart = new stdClass();
    $Versandart->cName                    = htmlspecialchars($postData['cName'], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
    $Versandart->kVersandberechnung       = (int)$postData['kVersandberechnung'];
    $Versandart->cAnzeigen                = $postData['cAnzeigen'];
    $Versandart->cBild                    = $postData['cBild'];
    $Versandart->nSort                    = (int)$postData['nSort'];
    $Versandart->nMinLiefertage           = (int)$postData['nMinLiefertage'];
    $Versandart->nMaxLiefertage           = (int)$postData['nMaxLiefertage'];
    $Versandart->cNurAbhaengigeVersandart = $postData['cNurAbhaengigeVersandart'];
    $Versandart->cSendConfirmationMail    = isset($postData['cSendConfirmationMail'])
        ? $postData['cSendConfirmationMail']
        : 'Y';
    $Versandart->cIgnoreShippingProposal  = isset($postData['cIgnoreShippingProposal'])
        ? $postData['cIgnoreShippingProposal']
        : 'N';
    $Versandart->eSteuer                  = $postData['eSteuer'];
    $Versandart->fPreis                   = (float)str_replace(',', '.', isset($postData['fPreis'])
        ? $postData['fPreis']
        : 0);
    // Versandkostenfrei ab X
    $Versandart->fVersandkostenfreiAbX = (isset($postData['versandkostenfreiAktiv']) && (int)$postData['versandkostenfreiAktiv'] === 1)
        ? (float)$postData['fVersandkostenfreiAbX']
        : 0;
    // Deckelung
    $Versandart->fDeckelung = (isset($postData['versanddeckelungAktiv']) && (int)$postData['versanddeckelungAktiv'] === 1)
        ? (float)$postData['fDeckelung']
        : 0;
    $Versandart->cLaender = '';
    $Laender              = $postData['land'];
    if (is_array($Laender)) {
        foreach ($Laender as $Land) {
            $Versandart->cLaender .= $Land . ' ';
        }
    }

    $VersandartZahlungsarten = [];
    if (isset($postData['kZahlungsart']) && is_array($postData['kZahlungsart'])) {
        foreach ($postData['kZahlungsart'] as $kZahlungsart) {
            $versandartzahlungsart               = new stdClass();
            $versandartzahlungsart->kZahlungsart = $kZahlungsart;
            if ($postData['fAufpreis_' . $kZahlungsart] != 0) {
                $versandartzahlungsart->fAufpreis    = (float)str_replace(',', '.', $postData['fAufpreis_' . $kZahlungsart]);
                $versandartzahlungsart->cAufpreisTyp = $postData['cAufpreisTyp_' . $kZahlungsart];
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
        if (count($postData['bis']) > 0 && count($postData['preis']) > 0) {
            $staffelDa = true;
        }
        //preisstaffel beachten
        if (!isset($postData['bis'][0]) || strlen($postData['bis'][0]) === 0 ||
            !isset($postData['preis'][0]) || strlen($postData['preis'][0]) === 0) {
            $staffelDa = false;
        }
        if (is_array($postData['bis']) && is_array($postData['preis'])) {
            foreach ($postData['bis'] as $i => $fBis) {
                if (isset($postData['preis'][$i]) && strlen($fBis) > 0) {
                    unset($oVersandstaffel);
                    $oVersandstaffel         = new stdClass();
                    $oVersandstaffel->fBis   = (float)str_replace(',', '.', $fBis);
                    $oVersandstaffel->fPreis = (float)str_replace(',', '.', $postData['preis'][$i]);

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
    if (!$postData['kKundengruppe']) {
        $postData['kKundengruppe'] = [-1];
    }
    if (is_array($postData['kKundengruppe'])) {
        if (in_array(-1, $postData['kKundengruppe'])) {
            $Versandart->cKundengruppen = '-1';
        } else {
            $Versandart->cKundengruppen = ';' . implode(';', $postData['kKundengruppe']) . ';';
        }
    }
    //Versandklassen
    $Versandart->cVersandklassen = ((!empty($postData['kVersandklasse']) && $postData['kVersandklasse'] !== '-1') ? ' ' . $postData['kVersandklasse'] . ' ' : '-1');

    if (count($postData['land']) >= 1 && count($postData['kZahlungsart']) >= 1 &&
        $Versandart->cName && $staffelDa && $bVersandkostenfreiGueltig) {
        $kVersandart = 0;
        if ((int)$postData['kVersandart'] === 0) {
            $kVersandart = Shop::DB()->insert('tversandart', $Versandart);
            $cHinweis .= "Die Versandart <strong>$Versandart->cName</strong> wurde erfolgreich hinzugef&uuml;gt. ";
        } else {
            //updaten
            $kVersandart = (int)$postData['kVersandart'];
            Shop::DB()->update('tversandart', 'kVersandart', $kVersandart, $Versandart);
            Shop::DB()->delete('tversandartzahlungsart', 'kVersandart', $kVersandart);
            Shop::DB()->delete('tversandartstaffel', 'kVersandart', $kVersandart);
            $cHinweis .= "Die Versandart <strong>$Versandart->cName</strong> wurde erfolgreich ge&auml;ndert.";
        }
        if ($kVersandart > 0) {
            foreach ($VersandartZahlungsarten as $versandartzahlungsart) {
                $versandartzahlungsart->kVersandart = $kVersandart;
                Shop::DB()->insert('tversandartzahlungsart', $versandartzahlungsart);
            }

            foreach ($VersandartStaffeln as $versandartstaffel) {
                $versandartstaffel->kVersandart = $kVersandart;
                Shop::DB()->insert('tversandartstaffel', $versandartstaffel);
            }
            $sprachen       = gibAlleSprachen();
            $versandSprache = new stdClass();
            $versandSprache->kVersandart = $kVersandart;
            foreach ($sprachen as $sprache) {
                $versandSprache->cISOSprache = $sprache->cISO;
                $versandSprache->cName       = $Versandart->cName;
                if ($postData['cName_' . $sprache->cISO]) {
                    $versandSprache->cName = htmlspecialchars($postData['cName_' . $sprache->cISO], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
                }
                $versandSprache->cLieferdauer = '';
                if ($postData['cLieferdauer_' . $sprache->cISO]) {
                    $versandSprache->cLieferdauer = htmlspecialchars($postData['cLieferdauer_' . $sprache->cISO], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
                }
                $versandSprache->cHinweistext = '';
                if ($postData['cHinweistext_' . $sprache->cISO]) {
                    $versandSprache->cHinweistext = $postData['cHinweistext_' . $sprache->cISO];
                }
                $versandSprache->cHinweistextShop = '';
                if ($postData['cHinweistextShop_' . $sprache->cISO]) {
                    $versandSprache->cHinweistextShop = $postData['cHinweistextShop_' . $sprache->cISO];
                }
                Shop::DB()->delete('tversandartsprache', ['kVersandart', 'cISOSprache'], [$kVersandart, $sprache->cISO]);
                Shop::DB()->insert('tversandartsprache', $versandSprache);
            }
            $step = 'uebersicht';
        }
        Shop::Cache()->flushTags([CACHING_GROUP_OPTION, CACHING_GROUP_ARTICLE]);
    } else {
        $step = 'neue Versandart';
        if (!$Versandart->cName) {
            $cFehler .= '<p>Bitte geben Sie dieser Versandart einen Namen!</p>';
        }
        if (count($postData['land']) < 1) {
            $cFehler .= '<p>Bitte mindestens ein Versandland ankreuzen!</p>';
        }
        if (count($postData['kZahlungsart']) < 1) {
            $cFehler .= '<p>Bitte mindestens eine akzeptierte Zahlungsart ausw&auml;hlen!</p>';
        }
        if (!$staffelDa) {
            $cFehler .= '<p>Bitte mindestens einen Staffelpreis angeben!</p>';
        }
        if (!$bVersandkostenfreiGueltig) {
            $cFehler .= '<p>Ihr Versandkostenfrei Wert darf maximal ' . $fMaxVersandartStaffelBis . ' sein!</p>';
        }
        if ((int)$postData['kVersandart'] > 0) {
            $Versandart = Shop::DB()->select('tversandart', 'kVersandart', (int)$postData['kVersandart']);
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
    $versandlaender = Shop::DB()->query("SELECT *, cDeutsch AS cName FROM tland ORDER BY cDeutsch", 2);
    if ($versandberechnung->cModulId === 'vm_versandberechnung_gewicht_jtl') {
        $smarty->assign('einheit', 'kg');
    }
    if ($versandberechnung->cModulId === 'vm_versandberechnung_warenwert_jtl') {
        $smarty->assign('einheit', $standardwaehrung->cName);
    }
    if ($versandberechnung->cModulId === 'vm_versandberechnung_artikelanzahl_jtl') {
        $smarty->assign('einheit', 'St&uuml;ck');
    }
    $zahlungsarten      = Shop::DB()->selectAll('tzahlungsart', 'nActive', 1, '*', 'cAnbieter, nSort, cName');
    $oVersandklasse_arr = Shop::DB()->selectAll('tversandklasse', [], [], '*', 'kVersandklasse');
    $smarty->assign('versandKlassen', $oVersandklasse_arr);
    $kVersandartTMP = 0;
    if (isset($Versandart->kVersandart) && $Versandart->kVersandart > 0) {
        $kVersandartTMP = $Versandart->kVersandart;
    }

    $sprachen = gibAlleSprachen();
    $smarty->assign('sprachen', $sprachen)
           ->assign('zahlungsarten', $zahlungsarten)
           ->assign('versandlaender', $versandlaender)
           ->assign('versandberechnung', $versandberechnung)
           ->assign('waehrung', $standardwaehrung->cName)
           ->assign('kundengruppen', Shop::DB()->query("SELECT kKundengruppe, cName FROM tkundengruppe ORDER BY kKundengruppe", 2))
           ->assign('oVersandartSpracheAssoc_arr', getShippingLanguage($kVersandartTMP, $sprachen))
           ->assign('gesetzteVersandklassen', isset($Versandart->cVersandklassen)
               ? gibGesetzteVersandklassen($Versandart->cVersandklassen)
               : null)
           ->assign('gesetzteKundengruppen', isset($Versandart->cKundengruppen)
               ? gibGesetzteKundengruppen($Versandart->cKundengruppen)
               : null);
}

if ($step === 'uebersicht') {
    $oKundengruppen_arr  = Shop::DB()->query("SELECT kKundengruppe, cName FROM tkundengruppe ORDER BY kKundengruppe", 2);
    $versandberechnungen = Shop::DB()->query("SELECT * FROM tversandberechnung ORDER BY cName", 2);
    $versandarten        = Shop::DB()->query("SELECT * FROM tversandart ORDER BY nSort, cName", 2);
    $vCount              = count($versandarten);
    for ($i = 0; $i < $vCount; $i++) {
        $versandarten[$i]->versandartzahlungsarten = Shop::DB()->query(
            "SELECT tversandartzahlungsart.*
                FROM tversandartzahlungsart
                JOIN tzahlungsart ON tzahlungsart.kZahlungsart = tversandartzahlungsart.kZahlungsart
                WHERE tversandartzahlungsart.kVersandart = " . (int)$versandarten[$i]->kVersandart . "
                ORDER BY tzahlungsart.cAnbieter, tzahlungsart.nSort, tzahlungsart.cName", 2
        );
        $count = count($versandarten[$i]->versandartzahlungsarten);
        for ($o = 0; $o < $count; $o++) {
            $versandarten[$i]->versandartzahlungsarten[$o]->zahlungsart = Shop::DB()->select(
                'tzahlungsart',
                'kZahlungsart',
                (int)$versandarten[$i]->versandartzahlungsarten[$o]->kZahlungsart ,
                'nActive',
                1
            );
            if ($versandarten[$i]->versandartzahlungsarten[$o]->cAufpreisTyp === 'prozent') {
                $versandarten[$i]->versandartzahlungsarten[$o]->cAufpreisTyp = '%';
            } else {
                $versandarten[$i]->versandartzahlungsarten[$o]->cAufpreisTyp = '';
            }
        }
        $versandarten[$i]->versandartstaffeln = Shop::DB()->selectAll(
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

        $versandarten[$i]->versandberechnung = Shop::DB()->select(
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
            $versandarten[$i]->einheit = 'St&uuml;ck';
        }
        $versandarten[$i]->land_arr = explode(' ', $versandarten[$i]->cLaender);
        $count = count($versandarten[$i]->land_arr);
        for ($o = 0; $o < $count; $o++) {
            unset($zuschlag);
            $zuschlag = Shop::DB()->select(
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
        $versandarten[$i]->oVersandartSprachen_arr = Shop::DB()->selectAll(
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

    $smarty->assign('versandberechnungen', $versandberechnungen)
           ->assign('versandarten', $versandarten)
           ->assign('waehrung', $standardwaehrung->cName)
           ->assign('cHinweis', $cHinweis)
           ->assign('cFehler', $cFehler);
}

if ($step === 'Zuschlagsliste') {
    $cISO = isset($_GET['cISO']) ? Shop::DB()->escape($_GET['cISO']) : null;
    if (isset($postData['cISO'])) {
        $cISO = Shop::DB()->escape($postData['cISO']);
    }
    $kVersandart = isset($_GET['kVersandart']) ? (int)$_GET['kVersandart'] : 0;
    if (isset($postData['kVersandart'])) {
        $kVersandart = (int)$postData['kVersandart'];
    }
    $Versandart = Shop::DB()->select('tversandart', 'kVersandart', $kVersandart);
    $Zuschlaege = Shop::DB()->selectAll(
        'tversandzuschlag',
        ['kVersandart', 'cISO'],
        [(int)$Versandart->kVersandart , $cISO],
        '*',
        'fZuschlag'
    );
    $zCount     = count($Zuschlaege);
    for ($i = 0; $i < $zCount; $i++) {
        $Zuschlaege[$i]->zuschlagplz     = Shop::DB()->selectAll(
            'tversandzuschlagplz',
            'kVersandzuschlag',
            $Zuschlaege[$i]->kVersandzuschlag
        );
        $Zuschlaege[$i]->angezeigterName = getZuschlagNames($Zuschlaege[$i]->kVersandzuschlag);
    }
    $smarty->assign('Versandart', $Versandart)
           ->assign('Zuschlaege', $Zuschlaege)
           ->assign('waehrung', $standardwaehrung->cName)
           ->assign('Land', Shop::DB()->select('tland', 'cISO', $cISO))
           ->assign('cHinweis', $cHinweis)
           ->assign('cFehler', $cFehler)
           ->assign('sprachen', gibAlleSprachen());
}

$smarty->assign('fSteuersatz', $_SESSION['Steuersatz'][$nSteuersatzKey_arr[0]])
       ->assign('oWaehrung', Shop::DB()->select('twaehrung', 'cStandard', 'Y'))
       ->assign('step', $step)
       ->display('versandarten.tpl');
