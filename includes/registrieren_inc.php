<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param array $cPost_arr
 * @return array
 */
function kundeSpeichern($cPost_arr)
{
    global $smarty, $Kunde, $GlobaleEinstellungen, $Einstellungen, $step, $editRechnungsadresse, $knd, $cKundenattribut_arr;

    unset($_SESSION['Lieferadresse']);
    unset($_SESSION['Versandart']);
    unset($_SESSION['Zahlungsart']);
    $_SESSION['Warenkorb']->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
                          ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART);

    $editRechnungsadresse = (int)$cPost_arr['editRechnungsadresse'];
    $step                 = 'formular';
    $smarty->assign('cPost_arr', StringHandler::filterXSS($cPost_arr));
    if (!$editRechnungsadresse) {
        $fehlendeAngaben = checkKundenFormular(1);
    } else {
        $fehlendeAngaben = checkKundenFormular(1, 0);
    }
    $knd                 = getKundendaten($cPost_arr, 1, 0);
    $cKundenattribut_arr = getKundenattribute($cPost_arr);
    $kKundengruppe       = Kundengruppe::getCurrent();
    // CheckBox Plausi
    require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.CheckBox.php';
    $oCheckBox       = new CheckBox();
    $fehlendeAngaben = array_merge($fehlendeAngaben, $oCheckBox->validateCheckBox(CHECKBOX_ORT_REGISTRIERUNG, $kKundengruppe, $cPost_arr, true));
    $nReturnValue    = angabenKorrekt($fehlendeAngaben);

    executeHook(HOOK_REGISTRIEREN_PAGE_REGISTRIEREN_PLAUSI, array('nReturnValue' => &$nReturnValue, 'fehlendeAngaben' => &$fehlendeAngaben));

    if ($nReturnValue) {
        // CheckBox Spezialfunktion ausführen
        $oCheckBox->triggerSpecialFunction(CHECKBOX_ORT_REGISTRIERUNG, $kKundengruppe, true, $cPost_arr, array('oKunde' => $knd));
        $oCheckBox->checkLogging(CHECKBOX_ORT_REGISTRIERUNG, $kKundengruppe, $cPost_arr, true);

        if ($editRechnungsadresse && $_SESSION['Kunde']->kKunde > 0) {
            $knd->cAbgeholt = 'N';
            unset($knd->cPasswort);
            $knd->updateInDB();
            // Kundendatenhistory
            Kundendatenhistory::saveHistory($_SESSION['Kunde'], $knd, Kundendatenhistory::QUELLE_BESTELLUNG);

            $_SESSION['Kunde'] = $knd;
            // Update Kundenattribute
            if (is_array($cKundenattribut_arr) && count($cKundenattribut_arr) > 0) {
                $oKundenfeldNichtEditierbar_arr = getKundenattributeNichtEditierbar();
                $cSQL                           = '';
                if (is_array($oKundenfeldNichtEditierbar_arr) && count($oKundenfeldNichtEditierbar_arr) > 0) {
                    $cSQL .= ' AND (';
                    foreach ($oKundenfeldNichtEditierbar_arr as $i => $oKundenfeldNichtEditierbar) {
                        if ($i == 0) {
                            $cSQL .= 'kKundenfeld != ' . $oKundenfeldNichtEditierbar->kKundenfeld;
                        } else {
                            $cSQL .= ' AND kKundenfeld != ' . $oKundenfeldNichtEditierbar->kKundenfeld;
                        }
                    }
                    $cSQL .= ')';
                }

                Shop::DB()->query("DELETE FROM tkundenattribut WHERE kKunde = " . (int)$_SESSION['Kunde']->kKunde . $cSQL, 3);
                $nKundenattributKey_arr = array_keys($cKundenattribut_arr);
                foreach ($nKundenattributKey_arr as $kKundenfeld) {
                    $oKundenattribut              = new stdClass();
                    $oKundenattribut->kKunde      = (int)$_SESSION['Kunde']->kKunde;
                    $oKundenattribut->kKundenfeld = $cKundenattribut_arr[$kKundenfeld]->kKundenfeld;
                    $oKundenattribut->cName       = $cKundenattribut_arr[$kKundenfeld]->cWawi;
                    $oKundenattribut->cWert       = $cKundenattribut_arr[$kKundenfeld]->cWert;

                    Shop::DB()->insert('tkundenattribut', $oKundenattribut);
                }
            }

            $_SESSION['Kunde']                      = new Kunde($_SESSION['Kunde']->kKunde);
            $_SESSION['Kunde']->cKundenattribut_arr = $cKundenattribut_arr;
        } else {
            // Guthaben des Neukunden aufstocken insofern er geworben wurde
            $oNeukunde = Shop::DB()->query(
                "SELECT kKundenWerbenKunden
                    FROM tkundenwerbenkunden
                    WHERE cEmail = '" . $knd->cMail . "'
                        AND nRegistriert = 0", 1
            );
            $kKundengruppe = $_SESSION['Kundengruppe']->kKundengruppe;
            if (isset($oNeukunde->kKundenWerbenKunden) && $oNeukunde->kKundenWerbenKunden > 0 &&
                isset($Einstellungen['kundenwerbenkunden']['kwk_kundengruppen']) &&
                intval($Einstellungen['kundenwerbenkunden']['kwk_kundengruppen']) > 0
            ) {
                $kKundengruppe = (int) $Einstellungen['kundenwerbenkunden']['kwk_kundengruppen'];
            }

            $knd->kKundengruppe = $kKundengruppe;
            $knd->kSprache      = $_SESSION['kSprache'];
            $knd->cAbgeholt     = 'N';
            $knd->cAktiv        = 'Y';
            $knd->cSperre       = 'N';
            //konto sofort aktiv?
            if ($GlobaleEinstellungen['global']['global_kundenkonto_aktiv'] === 'A') {
                $knd->cAktiv = 'N';
            }
            $customer             = new Kunde();
            $cPasswortKlartext    = $knd->cPasswort;
            $knd->cPasswort       = $customer->generatePasswordHash($cPasswortKlartext);//cryptPasswort($cPasswortKlartext);
            $knd->dErstellt       = 'now()';
            $knd->nRegistriert    = 1;
            $knd->angezeigtesLand = ISO2land($knd->cLand);
            // Work Around Mail zerhaut cLand
            $cLand = $knd->cLand;
            //mail
            $knd->cPasswortKlartext = $cPasswortKlartext;
            $obj                    = new stdClass();
            $obj->tkunde            = $knd;
            sendeMail(MAILTEMPLATE_NEUKUNDENREGISTRIERUNG, $obj);

            $knd->cLand = $cLand;
            unset($knd->cPasswortKlartext);
            unset($knd->Anrede);

            $knd->kKunde = $knd->insertInDB();
            // Kampagne
            if (isset($_SESSION['Kampagnenbesucher'])) {
                setzeKampagnenVorgang(KAMPAGNE_DEF_ANMELDUNG, $knd->kKunde, 1.0); // Anmeldung
            }
            // Insert Kundenattribute
            if (is_array($cKundenattribut_arr) && count($cKundenattribut_arr) > 0) {
                $nKundenattributKey_arr = array_keys($cKundenattribut_arr);

                foreach ($nKundenattributKey_arr as $kKundenfeld) {
                    $oKundenattribut              = new stdClass();
                    $oKundenattribut->kKunde      = $knd->kKunde;
                    $oKundenattribut->kKundenfeld = $cKundenattribut_arr[$kKundenfeld]->kKundenfeld;
                    $oKundenattribut->cName       = $cKundenattribut_arr[$kKundenfeld]->cWawi;
                    $oKundenattribut->cWert       = $cKundenattribut_arr[$kKundenfeld]->cWert;

                    Shop::DB()->insert('tkundenattribut', $oKundenattribut);
                }
            }
            if ($Einstellungen['global']['global_kundenkonto_aktiv'] !== 'A') {
                $_SESSION['Kunde']                      = new Kunde($knd->kKunde);
                $_SESSION['Kunde']->cKundenattribut_arr = $cKundenattribut_arr;
            } else {
                $step = 'formular eingegangen';
            }
            // Guthaben des Neukunden aufstocken insofern er geworben wurde
            if (isset($oNeukunde->kKundenWerbenKunden) && $oNeukunde->kKundenWerbenKunden > 0) {
                Shop::DB()->query(
                    "UPDATE tkunde
                        SET fGuthaben = fGuthaben+" . doubleval($Einstellungen['kundenwerbenkunden']['kwk_neukundenguthaben']) . "
                        WHERE kKunde = " . (int)$knd->kKunde, 3
                );
                $_upd               = new stdClass();
                $_upd->nRegistriert = 1;
                Shop::DB()->update('tkundenwerbenkunden', 'cEmail', $knd->cMail, $_upd);
            }
        }
        if ((isset($_SESSION['Warenkorb']->kWarenkorb)) && $_SESSION['Warenkorb']->gibAnzahlArtikelExt(array(C_WARENKORBPOS_TYP_ARTIKEL)) > 0) {
            setzeSteuersaetze();
            $_SESSION['Warenkorb']->gibGesamtsummeWarenLocalized();
        }
        if ($cPost_arr['checkout'] == 1) {
            //weiterleitung zum chekout
            header('Location: bestellvorgang.php?reg=1', true, 303);
            exit;
        } elseif (isset($cPost_arr['ajaxcheckout_return']) && intval($cPost_arr['ajaxcheckout_return']) === 1) {
            return 1;
        } elseif ($GlobaleEinstellungen['global']['global_kundenkonto_aktiv'] !== 'A') {
            //weiterleitung zu mein Konto
            header('Location: jtl.php?reg=1', true, 303);
            exit;
        }
    } else {
        $smarty->assign('fehlendeAngaben', $fehlendeAngaben);
        $Kunde = $knd;

        return $fehlendeAngaben;
    }
}

/**
 * @param int $nCheckout
 */
function gibFormularDaten($nCheckout = 0)
{
    global $smarty, $cKundenattribut_arr, $Kunde, $Einstellungen;

    if (count($cKundenattribut_arr) === 0) {
        $cKundenattribut_arr = (isset($_SESSION['Kunde']->cKundenattribut_arr)) ? $_SESSION['Kunde']->cKundenattribut_arr : array();
    }

    if (isset($Kunde->dGeburtstag) && preg_match('/^\d{4}\-\d{2}\-(\d{2})$/', $Kunde->dGeburtstag)) {
        list($jahr, $monat, $tag) = explode('-', $Kunde->dGeburtstag);
        $Kunde->dGeburtstag       = $tag . '.' . $monat . '.' . $jahr;
    }
    $herkunfte = Shop::DB()->query("SELECT * FROM tkundenherkunft ORDER BY nSort", 2);

    $smarty->assign('herkunfte', $herkunfte)
           ->assign('Kunde', $Kunde)
           ->assign('cKundenattribut_arr', $cKundenattribut_arr)
           ->assign('laender', gibBelieferbareLaender($_SESSION['Kundengruppe']->kKundengruppe))
           ->assign('warning_passwortlaenge', lang_passwortlaenge($Einstellungen['kunden']['kundenregistrierung_passwortlaenge']))
           ->assign('oKundenfeld_arr', gibSelbstdefKundenfelder());

    if (intval($nCheckout) === 1) {
        $smarty->assign('checkout', 1)
               ->assign('bestellschritt', array(1 => 1, 2 => 3, 3 => 3, 4 => 3, 5 => 3)); // Rechnungsadresse ändern
    }
}

/**
 *
 */
function gibKunde()
{
    global $Kunde, $titel;

    $Kunde = $_SESSION['Kunde'];

    if (preg_match('/^\d{4}\-\d{2}\-(\d{2})$/', $Kunde->dGeburtstag)) {
        list($jahr, $monat, $tag) = explode('-', $Kunde->dGeburtstag);
        $Kunde->dGeburtstag       = $tag . '.' . $monat . '.' . $jahr;
    }
    $titel = Shop::Lang()->get('editData', 'login');
}
