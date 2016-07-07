<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';

$oAccount->permission('ORDER_COUPON_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'kupons_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'toolsajax_inc.php';

$cHinweis     = '';
$cFehler      = '';
$step         = 'uebersicht';
$action       = '';
$cFehler_arr  = array();
$oSprache_arr = gibAlleSprachen();

// Aktion ausgeloest?
if (validateToken()) {
    if (isset($_POST['kKuponBearbeiten'])) {
        // vorhandenen Kupon bearbeiten
        $action = 'bearbeiten';
        $step   = 'bearbeiten';
    } elseif (isset($_POST['action'])) {
        if ($_POST['action'] === 'speichern') {
            if (isset($_POST['kKupon'])) {
                // vorhandenen Kupon speichern
                $action = 'updaten';
            } else {
                // neuen Kupon speichern
                $action = 'speichern';
            }
        } elseif ($_POST['action'] === 'loeschen') {
            // vorhandene Kupons loeschen
            $action = 'loeschen';
        } elseif ($_POST['action'] === 'erstellen') {
            // neuen Kupon bearbeiten
            $action = 'erstellen';
            $step   = 'bearbeiten';
        }
    }
}

if ($action == 'updaten' || $action == 'speichern') {
    // vorhandenen oder neuen Kupon speichern
    $oKupon                        = new Kupon($action == 'updaten' ? (int)$_POST['kKupon'] : 0);
    $oKupon->cKuponTyp             = $_POST['cKuponTyp'];
    $oKupon->cName                 = $_POST['cName'];
    $oKupon->fWert                 = isset($_POST['fWert']) ? (float)str_replace(',', '.', $_POST['fWert']) : null;
    $oKupon->cWertTyp              = isset($_POST['cWertTyp']) ? $_POST['cWertTyp'] : null;
    $oKupon->cZusatzgebuehren      = isset($_POST['cZusatzgebuehren']) ? $_POST['cZusatzgebuehren'] : 'N';
    $oKupon->nGanzenWKRabattieren  = isset($_POST['nGanzenWKRabattieren']) ? (int)$_POST['nGanzenWKRabattieren'] : 0;
    $oKupon->kSteuerklasse         = isset($_POST['kSteuerklasse']) ? (int)$_POST['kSteuerklasse'] : null;
    $oKupon->fMindestbestellwert   = (float)str_replace(',', '.', $_POST['fMindestbestellwert']);
    $oKupon->cCode                 = isset($_POST['cCode']) ? $_POST['cCode'] : null;
    $oKupon->cLieferlaender        = isset($_POST['cLieferlaender']) ? strtoupper($_POST['cLieferlaender']) : null;
    $oKupon->nVerwendungen         = isset($_POST['nVerwendungen']) ? (int)$_POST['nVerwendungen'] : 0;
    $oKupon->nVerwendungenProKunde = isset($_POST['nVerwendungenProKunde']) ? (int)$_POST['nVerwendungenProKunde'] : 0;
    $oKupon->cArtikel              = trim($_POST['cArtikel']);
    $oKupon->kKundengruppe         = (int)$_POST['kKundengruppe'];
    $oKupon->dGueltigAb            = convertDate(isset($_POST['dGueltigAb']) ? $_POST['dGueltigAb'] : null);
    $oKupon->dGueltigBis           = convertDate(isset($_POST['dGueltigBis']) ? $_POST['dGueltigBis'] : null);
    $oKupon->cAktiv                = isset($_POST['cAktiv']) && $_POST['cAktiv'] == 'Y' ? 'Y' : 'N';
    $oKupon->cKategorien           = '-1';
    $oKupon->cKunden               = '-1';

    if (isset($_POST['kKategorien']) && is_array($_POST['kKategorien']) && count($_POST['kKategorien']) > 0 && !in_array('-1', $_POST['kKategorien'])) {
        $oKupon->cKategorien = StringHandler::createSSK($_POST['kKategorien']);
    }
    if (isset($_POST['kKunden']) && is_array($_POST['kKunden']) && count($_POST['kKunden']) > 0 && !in_array('-1', $_POST['kKunden'])) {
        $oKupon->cKunden = StringHandler::createSSK($_POST['kKunden']);
    }

    // Validierung
    if ($oKupon->cName == '') {
        $cFehler_arr[] = 'Es wurde kein Kuponname angegeben. Bitte geben Sie einen Namen an!';
    }
    if (($oKupon->cKuponTyp === 'standard' || $oKupon->cKuponTyp === 'neukundenkupon') && $oKupon->fWert < 0) {
        $cFehler_arr[] = 'Bitte geben Sie einen nicht-negativen Kuponwert an!';
    }
    if ($oKupon->fMindestbestellwert < 0) {
        $cFehler_arr[] = 'Bitte geben Sie einen nicht-negativen Mindestbestellwert an!';
    }
    if ($oKupon->cKuponTyp == 'versandkupon' && $oKupon->cLieferlaender == '') {
        $cFehler_arr[] = 'Bitte geben Sie die L&auml;nderk&uuml;rzel (ISO-Codes) unter "Lieferl&auml;nder" an, f&uuml;r die dieser Versandkupon gelten soll!';
    }
    $queryRes = Shop::DB()->query("
        SELECT kKupon
            FROM tkupon
            WHERE cCode = '" . $oKupon->cCode . "'" . ($action == 'updaten' ? " AND kKupon != " . $oKupon->kKupon : ''),
        1);
    if (is_object($queryRes)) {
        $cFehler_arr[] = 'Der angegeben Kuponcode wird bereits von einem anderen Kupon verwendet. Bitte w&auml;hlen Sie einen anderen Code!';
    }

    if (count($cFehler_arr) > 0) {
        $cFehler = 'Bitte &uuml;berpr&uuml;fen Sie folgende Eingaben:<ul>';
        foreach ($cFehler_arr as $fehler) {
            $cFehler .= '<li>' . $fehler . '</li>';
        }
        $cFehler .= '</ul>';
        $step = 'bearbeiten';
    } else {
        if ($action == 'speichern') {
            // neuen Kupon speichern
            $oKupon->nVerwendungenBisher = 0;
            $oKupon->dErstellt           = 'now()';
            $oKupon->kKupon              = $oKupon->save();
            $cHinweis                    = 'Der Kupon wurde erfolgreich erstellt.';
        } elseif ($action == 'updaten') {
            // vorhandenen Kupon speichern
            $oKupon->update();
            $cHinweis = 'Der Kupon wurde erfolgreich aktualisiert.';
        }

        // Kupon-Sprachen speichern
        if ($action == 'updaten') {
            Shop::DB()->delete('tkuponsprache', 'kKupon', $oKupon->kKupon);
        }
        foreach ($oSprache_arr as $oSprache) {
            if (isset($_POST['cName_' . $oSprache->cISO]) && $_POST['cName_' . $oSprache->cISO] !== '') {
                $cKuponSpracheName = $_POST['cName_' . $oSprache->cISO];
            } else {
                $cKuponSpracheName = $oKupon->cName;
            }
            $kuponSprache              = new stdClass();
            $kuponSprache->kKupon      = $oKupon->kKupon;
            $kuponSprache->cISOSprache = $oSprache->cISO;
            $kuponSprache->cName       = $cKuponSpracheName;
            Shop::DB()->insert('tkuponsprache', $kuponSprache);
        }
    }
} elseif ($action == 'bearbeiten') {
    // vorhandenen Kupon bearbeiten
    $kKupon = (int)$_POST['kKuponBearbeiten'];
    $oKupon = new Kupon($kKupon);
} elseif ($action == 'loeschen') {
    // vorhandene Kupons loeschen
    if (isset($_POST['kKupon_arr']) && is_array($_POST['kKupon_arr']) && count($_POST['kKupon_arr']) > 0) {
        if (loescheKupons($_POST['kKupon_arr'])) {
            $cHinweis = 'Ihre markierten Kupons wurden erfolgreich gel&ouml;scht.';
        } else {
            $cFehler = 'Fehler: Ein oder mehrere Kupons konnten nicht gel&ouml;scht werden.';
        }
    } else {
        $cFehler = 'Fehler: Bitte markieren Sie mindestens einen Kupon.';
    }
} elseif ($action == 'erstellen') {
    // neuen Kupon bearbeiten
    $cKuponTyp                     = $_POST['cKuponTyp'];
    $oKupon                        = new Kupon();
    $oKupon->cKuponTyp             = $cKuponTyp;
    $oKupon->cName                 = 'neuerkupon';
    $oKupon->fWert                 = 0.0;
    $oKupon->cWertTyp              = 'festpreis';
    $oKupon->cZusatzgebuehren      = 'N';
    $oKupon->nGanzenWKRabattieren  = 1;
    $oKupon->kSteuerklasse         = 1;
    $oKupon->fMindestbestellwert   = 0.0;
    $oKupon->cCode                 = Kupon::generateCode();
    $oKupon->cLieferlaender        = '';
    $oKupon->nVerwendungen         = 1;
    $oKupon->nVerwendungenProKunde = 1;
    $oKupon->cArtikel              = '';
    $oKupon->kKundengruppe         = -1;
    $oKupon->dGueltigAb            = date_create()->format('Y-m-d H:i');
    $oKupon->dGueltigBis           = $oKupon->dGueltigAb;
    $oKupon->cAktiv                = 'Y';
    $oKupon->cKategorien           = '-1';
    $oKupon->cKunden               = '-1';
}

if ($step == 'uebersicht') {
    // Seite: Uebersicht
    $oKupon_arr = getCoupons();
    $smarty->assign('oKupon_arr', $oKupon_arr);
} elseif ($step == 'bearbeiten') {
    // Seite: Bearbeiten
    $oSteuerklasse_arr = Shop::DB()->query("SELECT kSteuerklasse, cName FROM tsteuerklasse", 2);
    $oKundengruppe_arr = Shop::DB()->query("SELECT kKundengruppe, cName FROM tkundengruppe", 2);
    $oKategorie_arr    = getCategories($oKupon->cKategorien);
    $oKunde_arr        = getCustomers($oKupon->cKunden);
    $oKuponName_arr    = getCouponNames($oKupon->kKupon);
    $smarty->assign('oSteuerklasse_arr', $oSteuerklasse_arr)
        ->assign('oKundengruppe_arr', $oKundengruppe_arr)
        ->assign('oKategorie_arr', $oKategorie_arr)
        ->assign('oKunde_arr', $oKunde_arr)
        ->assign('oSprache_arr', $oSprache_arr)
        ->assign('oKuponName_arr', $oKuponName_arr)
        ->assign('oKupon', $oKupon);
}

$smarty->assign('step', $step)
    ->assign('action', $action)
    ->assign('cHinweis', $cHinweis)
    ->assign('cFehler', $cFehler)
    ->display('kupons.tpl');
