<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';

$oAccount->permission('ORDER_COUPON_VIEW', true, true);

$standardwaehrung = Shop::DB()->query("SELECT * FROM twaehrung WHERE cStandard='Y'", 1);

require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'kupons_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'blaetternavi.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'toolsajax_inc.php';

$hinweis           = '';
$cHinweis          = '';
$cFehler           = '';
$step              = 'uebersicht';
$Kupon             = null;
$nAnzahlProSeite   = 15;
$oBlaetterNaviConf = baueBlaetterNaviGetterSetter(2, $nAnzahlProSeite);

/**
 * @param int $stellen
 * @return string
 */
function generateCode($stellen = 7)
{
    return strtoupper(substr(time() / 1000 + rand(123, 9999999), 0, $stellen));
}

// In- oder Aktive Kupons loeschen
if ((verifyGPCDataInteger('del_aktive_kupons') === 1 || verifyGPCDataInteger('del_inaktive_kupons') === 1) && validateToken()) {
    if (isset($_POST['kKupon']) && is_array($_POST['kKupon']) && count($_POST['kKupon']) > 0) {
        if (loescheKupons($_POST['kKupon'])) {
            $cHinweis = 'Ihre markierten Kupons wurden erfolgreich gel&ouml;scht.';
        } else {
            $cFehler = 'Fehler: Die markierten Kupons konnten nicht oder nur zum Teil gel&ouml;scht werden.';
        }
    } else {
        $cFehler = 'Fehler: Bitte markieren Sie mindestens einen Kupon.';
    }
}

if (isset($_POST['neu']) && intval($_POST['neu']) === 1 && validateToken()) {
    $step = 'neuer Kupon';
    if (!isset($Kupon)) {
        $Kupon = new stdClass();
    }
    $Kupon->cKuponTyp = $_POST['cKuponTyp'];
}

if (isset($_GET['kKupon']) && intval($_GET['kKupon']) > 0 && validateToken()) {
    $step  = 'neuer Kupon';
    $Kupon = Shop::DB()->query(
        "SELECT *, date_format(dGueltigAb,'%d.%m.%Y %H:%i') AS GueltigAb, date_format(dGueltigBis,'%d.%m.%Y %H:%i') AS GueltigBis
            FROM tkupon
            WHERE kKupon = " . (int)$_GET['kKupon'], 1
    );
}

if (isset($_POST['neuerKupon']) && intval($_POST['neuerKupon']) === 1 && validateToken()) {
    if (!isset($Kupon)) {
        $Kupon = new stdClass();
    }
    $gueltigAb                    = (isset($_POST['dGueltigAb'])) ? ($_POST['dGueltigAb']) : null;
    $gueltigBis                   = (isset($_POST['dGueltigBis'])) ? ($_POST['dGueltigBis']) : null;
    $Kupon->cName                 = (isset($_POST['cName'])) ? $_POST['cName'] : null;
    $Kupon->fWert                 = (isset($_POST['fWert'])) ? str_replace(',', '.', $_POST['fWert']) : '';
    $Kupon->dGueltigAb            = convertDate($gueltigAb);
    $Kupon->dGueltigBis           = convertDate($gueltigBis);
    $Kupon->kKundengruppe         = (isset($_POST['kKundengruppe'])) ? $_POST['kKundengruppe'] : null;
    $Kupon->cWertTyp              = (isset($_POST['cWertTyp'])) ? $_POST['cWertTyp'] : null;
    $Kupon->fMindestbestellwert   = str_replace(',', '.', $_POST['fMindestbestellwert']);
    $Kupon->cCode                 = (isset($_POST['cCode'])) ? $_POST['cCode'] : null;
    $Kupon->nVerwendungen         = (isset($_POST['nVerwendungen'])) ? (int)$_POST['nVerwendungen'] : 0;
    $Kupon->nVerwendungenProKunde = (isset($_POST['nVerwendungenProKunde'])) ? (int)$_POST['nVerwendungenProKunde'] : 0;
    $Kupon->kSteuerklasse         = (isset($_POST['kSteuerklasse'])) ? (int)$_POST['kSteuerklasse'] : null;
    $Kupon->cArtikel              = trim($_POST['cArtikel']);
    if (strlen($Kupon->cArtikel) > 0) {
        if (substr($Kupon->cArtikel, 0, 1) !== ';') {
            $Kupon->cArtikel = ';' . $Kupon->cArtikel;
        }
        if (substr($Kupon->cArtikel, strlen($Kupon->cArtikel) - 1, 1) !== ';') {
            $Kupon->cArtikel = $Kupon->cArtikel . ';';
        }
    }
    $Kupon->cLieferlaender   = (isset($_POST['cLieferlaender'])) ? strtoupper($_POST['cLieferlaender']) : null;
    $Kupon->cZusatzgebuehren = 'N';
    if (isset($_POST['cZusatzgebuehren']) && $_POST['cZusatzgebuehren'] === 'Y') {
        $Kupon->cZusatzgebuehren = 'Y';
    }
    $Kupon->cAktiv = 'N';
    if (isset($_POST['cAktiv']) && $_POST['cAktiv'] === 'Y') {
        $Kupon->cAktiv = 'Y';
    }
    $Kupon->dErstellt   = 'now()';
    $Kupon->cKuponTyp   = $_POST['cKuponTyp'];
    $Kupon->cKategorien = null;
    $Kupon->cKunden     = null;
    if (isset($_POST['kKategorien']) && is_array($_POST['kKategorien'])) {
        $Kupon->cKategorien = StringHandler::createSSK($_POST['kKategorien']);
    }
    if (isset($_POST['kKunden']) && is_array($_POST['kKunden'])) {
        $Kupon->cKunden = StringHandler::createSSK($_POST['kKunden']);
    }

    if (isset($_POST['kKategorien']) && is_array($_POST['kKategorien']) && in_array(0, $_POST['kKategorien'])) {
        $Kupon->cKategorien = '-1';
    }
    if (isset($_POST['kKunden']) && is_array($_POST['kKunden']) && in_array(0, $_POST['kKunden'])) {
        $Kupon->cKunden = '-1';
    }
    // Ganzen WK rabattieren
    $Kupon->nGanzenWKRabattieren = (isset($_POST['nGanzenWKRabattieren'])) ? (int)$_POST['nGanzenWKRabattieren'] : 0;
    $smarty->assign('gueltigAb', $gueltigAb)
           ->assign('gueltigBis', $gueltigBis);
    if ($Kupon->cKuponTyp !== 'neukundenkupon') {
        if (!$Kupon->cCode) {
            $Kupon->cCode = generateCode();
        }
        //code muss unique sein
        if (intval($_POST['kKupon']) > 0) {
            $code_obj = Shop::DB()->query("SELECT kKupon FROM tkupon WHERE cCode = '" . $Kupon->cCode . "' AND kKupon != " . (int)$_POST['kKupon'], 1);
        } else {
            $code_obj = Shop::DB()->query("SELECT kKupon FROM tkupon WHERE cCode = '" . $Kupon->cCode . "'", 1);
        }
        if (is_object($code_obj) && $code_obj->kKupon > 0) {
            $Kupon->cCode = -1;
        }
    }
    if ($Kupon->cName && (($Kupon->fWert && ($Kupon->cKuponTyp === 'standard' || $Kupon->cKuponTyp === 'neukundenkupon')) ||
            ($Kupon->cKuponTyp === 'versandkupon' && $Kupon->cLieferlaender)) &&
        $Kupon->dGueltigAb && $Kupon->dGueltigBis && $Kupon->cCode != -1 && strlen($Kupon->cCode) < 21
    ) {
        $kKupon = 0;
        if (intval($_POST['kKupon']) > 0) {
            $kKupon = (int)$_POST['kKupon'];
            if (Shop::DB()->update('tkupon', 'kKupon', (int)$_POST['kKupon'], $Kupon) >= 0) {
                $hinweis .= 'Kupon ' . $Kupon->cName . ' erfolgreich ge&auml;ndert.';
            }
        } elseif ($kKupon = Shop::DB()->insert('tkupon', $Kupon)) {
            $hinweis .= 'Kupon ' . $Kupon->cName . ' erfolgreich erstellt.';
        }
        $sprachen = gibAlleSprachen();
        if (!isset($kuponSprache)) {
            $kuponSprache = new stdClass();
        }
        $kuponSprache->kKupon = $kKupon;
        foreach ($sprachen as $sprache) {
            $kuponSprache->cISOSprache = $sprache->cISO;
            $kuponSprache->cName       = $Kupon->cName;
            if ($_POST['cName_' . $sprache->cISO]) {
                $kuponSprache->cName = $_POST['cName_' . $sprache->cISO];
            }

            Shop::DB()->delete('tkuponsprache', array('kKupon', 'cISOSprache'), array($kKupon, $sprache->cISO));
            Shop::DB()->insert('tkuponsprache', $kuponSprache);
        }
        //emails versenden
        $kKunden_arr = array();
        if ((isset($_POST['informieren']) && $_POST['informieren'] === 'Y') && ($Kupon->cKuponTyp === 'standard' || $Kupon->cKuponTyp === 'versandkupon') && $Kupon->cAktiv === 'Y') {
            if ($Kupon->cKunden == '-1') {
                if (intval($_POST['kKundengruppe']) === 0 || intval($_POST['kKundengruppe']) == -1) {
                    $kKunden_arr = Shop::DB()->query("SELECT * FROM tkunde WHERE cPasswort != ''", 2);
                } else {
                    $kKunden_arr = Shop::DB()->query("SELECT * FROM tkunde WHERE cPasswort != '' AND kKundengruppe = " . (int)$_POST['kKundengruppe'], 2);
                }
            } else {
                $where = "(";
                foreach ($_POST['kKunden'] as $i => $kKunde) {
                    if ($i > 0) {
                        $where .= ' OR ';
                    }
                    $where .= 'kKunde = ' . $kKunde;
                }
                $where .= ")";
                $kKunden_arr = Shop::DB()->query("SELECT * FROM tkunde WHERE cPasswort != '' AND " . $where, 2);
            }
        }
        //evtl artiklnr?
        $Artikel_arr = array();
        if ($Kupon->cArtikel !== '') {
            $Artikel_arr = Shop::DB()->query("
                SELECT kArtikel
                    FROM tartikel
                    WHERE cArtNr IN (" . str_replace(';', ',', trim($Kupon->cArtikel, ';')) . ")", 2);
        }

        if (is_array($kKunden_arr) && count($kKunden_arr) > 0) {
            $Kupon = Shop::DB()->query(
                "SELECT *, date_format(dGueltigAb,'%d.%m.%Y %H:%i') AS GueltigAb, date_format(dGueltigBis,'%d.%m.%Y %H:%i') AS GueltigBis
                    FROM tkupon
                    WHERE kKupon = " . $kKupon, 1
            );
            $Kupon->cLocalizedWert = ($Kupon->cWertTyp === 'festpreis') ?
                gibPreisStringLocalized($Kupon->fWert, $standardwaehrung, 0) :
                $Kupon->fWert . ' %';
            $Kupon->cLocalizedMBW = gibPreisStringLocalized($Kupon->fMindestbestellwert, $standardwaehrung, 0);

            foreach ($kKunden_arr as $Kunde) {
                $oKunde = new Kunde($Kunde->kKunde);
                //evtl kat?
                $Kategorie_arr = array();
                if ($Kupon->cKategorien != '-1') {
                    $pcs = explode(';', $Kupon->cKategorien);
                    foreach ($pcs as $i => $kKategorie) {
                        if ($kKategorie > 0) {
                            $kat = new Kategorie($kKategorie, $oKunde->kSprache, $oKunde->kKundengruppe);
                            if ($kat->kKategorie > 0) {
                                $kat->cURL       = Shop::getURL() . '/' . $kat->cURL;
                                $Kategorie_arr[] = $kat;
                            }
                        }
                    }
                }

                $Sprache = Shop::Lang()->getIsoFromLangID($oKunde->kSprache);
                if (!$Sprache) {
                    $Sprache = Shop::DB()->query("SELECT cISO FROM tsprache WHERE cShopStandard = 'Y'", 1);
                }

                $kuponSprache           = Shop::DB()->select('tkuponsprache', 'kKupon', $kKupon, 'cISOSprache', $Sprache->cISO);
                $Kupon->AngezeigterName = $kuponSprache->cName;
                if (is_array($Artikel_arr)) {
                    $Artikel_filled_arr = array();
                    foreach ($Artikel_arr as $kArtikel_obj) {
                        $art                                 = new Artikel();
                        $oArtikelOptionen->nMerkmale         = 0;
                        $oArtikelOptionen->nAttribute        = 0;
                        $oArtikelOptionen->nArtikelAttribute = 0;
                        $art->fuelleArtikel($kArtikel_obj->kArtikel, $oArtikelOptionen, $oKunde->kKundengruppe, $oKunde->kSprache, true);
                        if ($art->kArtikel > 0) {
                            $Artikel_filled_arr[] = $art;
                        }
                    }
                }
                $Kupon->Artikel    = $Artikel_filled_arr;
                $Kupon->Kategorien = $Kategorie_arr;
                $obj               = new stdClass();
                $obj->tkupon       = $Kupon;
                $obj->tkunde       = $oKunde;
                sendeMail(MAILTEMPLATE_KUPON, $obj);
            }
        }

        $step = 'uebersicht';
    } else {
        $step = 'neuer Kupon';
        if (strlen($Kupon->cCode) > 20) {
            $hinweis .= 'Bitte Code-L&auml;nge auf maximal 20 Zeichen beschr&auml;nken!<br />';
        }
        if (!$Kupon->cName) {
            $hinweis .= 'Bitte Kuponnamen eintragen!<br />';
        }
        if (!$Kupon->fWert && ($Kupon->cKuponTyp === 'standard' || $Kupon->cKuponTyp === 'neukundenkupon')) {
            $hinweis .= 'Bitte Wert eintragen!<br />';
        }
        if (!$Kupon->dGueltigAb) {
            $hinweis .= 'Bitte Beginn der G&uuml;ltigkeit im Format (tt.mm.yyyy ss:mm), z.B. 21.08.2009 13:30 eintragen!<br />';
        }
        if (!$Kupon->dGueltigBis) {
            $hinweis .= 'Bitte Ende der G&uuml;ltigkeit im Format (tt.mm.yyyy ss:mm), z.B. 21.08.2009 13:30 eintragen!<br />';
        }
        if ($Kupon->cCode == -1) {
            $hinweis .= 'Dieser Kuponcode wird bereits von einem anderen Kupon verwendet. Bitte w&auml;hlen Sie einen anderen Code!<br />';
        }
        if ($Kupon->cKuponTyp === 'versandkupon' && !$Kupon->cLieferlaender) {
            $hinweis .= 'Bitte tragen Sie die L&auml;nderk&uuml;rzel (ISO-Codes) unter "Lieferl&auml;nder" ein, f&uuml;r die dieser Versandkupon gelten soll!<br />';
        }
    }
}

if ($step === 'neuer Kupon') {
    $kundengruppen          = Shop::DB()->query("SELECT * FROM tkundengruppe ORDER BY cName", 2);
    $kategoriebaum          = getCategories((isset($Kupon->cKategorien)) ? $Kupon->cKategorien : null);
    $kategoriebaum_selected = false;
    if (!empty($kategoriebaum)) {
        foreach ($kategoriebaum as $kat) {
            if ($kat->selected) {
                $kategoriebaum_selected = true;
                break;
            }
        }
    }
    $smarty->assign('kundengruppen', $kundengruppen)
           ->assign('Kupon', (isset($Kupon)) ? $Kupon : null)
           ->assign('kategoriebaum', $kategoriebaum)
           ->assign('kategoriebaum_selected', $kategoriebaum_selected);

    $kunden          = Shop::DB()->query("SELECT kKunde FROM tkunde WHERE cPasswort != '' LIMIT 1000", 2);
    $kunden_selected = false;
    $aktKunden       = array();
    if (isset($Kupon->cKunden) && $Kupon->cKunden) {
        $aktKunden = explode(';', $Kupon->cKunden);
    }
    $nCount = count($kunden);
    for ($i = 0; $i < $nCount; $i++) {
        unset($oKunde);
        $oKunde = new Kunde($kunden[$i]->kKunde);

        $kunden[$i]->kKunde      = $oKunde->kKunde;
        $kunden[$i]->cVorname    = $oKunde->cVorname;
        $kunden[$i]->cNachname   = $oKunde->cNachname;
        $kunden[$i]->cFirma      = $oKunde->cFirma;
        $kunden[$i]->cStrasse    = $oKunde->cStrasse;
        $kunden[$i]->cHausnummer = $oKunde->cHausnummer;

        $kunden[$i]->selected = 0;
        if (in_array($kunden[$i]->kKunde, $aktKunden)) {
            $kunden[$i]->selected = 1;
            $kunden_selected      = true;
        }
    }

    // Kunden sortieren da der Nachname im 3er verschluesselt ist
    objectSort($kunden, 'cNachname', true);

    $steuerklassen = Shop::DB()->query("SELECT * FROM tsteuerklasse ORDER BY cStandard DESC", 2);

    $smarty->assign('kunden', $kunden)
           ->assign('kunden_selected', $kunden_selected)
           ->assign('steuerklassen', $steuerklassen)
           ->assign('sprachen', gibAlleSprachen())
           ->assign('Kuponname', getCouponNames((isset($Kupon->kKupon)) ? $Kupon->kKupon : null));
}

if ($step === 'uebersicht') {
    // Baue Blaetternavigation
    $oBlaetterNaviAktiv   = baueBlaetterNavi($oBlaetterNaviConf->nAktuelleSeite1, getCouponCount(), $nAnzahlProSeite);
    $oBlaetterNaviInaktiv = baueBlaetterNavi($oBlaetterNaviConf->nAktuelleSeite2, getCouponCount(false), $nAnzahlProSeite);

    $smarty->assign('oBlaetterNaviAktiv', $oBlaetterNaviAktiv)
           ->assign('oBlaetterNaviInaktiv', $oBlaetterNaviInaktiv)
           ->assign('kupons_aktiv', getCoupons(true, $oBlaetterNaviConf->cSQL1))
           ->assign('kupons_inaktiv', getCoupons(false, $oBlaetterNaviConf->cSQL2));
}

$smarty->assign('step', $step)
       ->assign('hinweis', $hinweis)
       ->assign('cHinweis', $cHinweis)
       ->assign('cFehler', $cFehler)
       ->assign('waehrung', $standardwaehrung->cName)
       ->display('kupons.tpl');
