<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\Tax;
use Helpers\ShippingMethod;

/**
 * @param array $post
 * @return array|int
 */
function kundeSpeichern(array $post)
{
    global $Kunde,
           $step,
           $edit,
           $knd,
           $cKundenattribut_arr;

    unset($_SESSION['Lieferadresse'], $_SESSION['Versandart'], $_SESSION['Zahlungsart']);
    $db   = Shop::Container()->getDB();
    $conf = Shop::getSettings([CONF_GLOBAL, CONF_KUNDENWERBENKUNDEN]);
    $cart = \Session\Frontend::getCart();
    $cart->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
         ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART);

    $edit = (int)$post['editRechnungsadresse'];
    $step = 'formular';
    Shop::Smarty()->assign('cPost_arr', StringHandler::filterXSS($post));
    $fehlendeAngaben     = (!$edit)
        ? checkKundenFormular(1)
        : checkKundenFormular(1, 0);
    $knd                 = getKundendaten($post, 1, 0);
    $cKundenattribut_arr = getKundenattribute($post);
    $kKundengruppe       = \Session\Frontend::getCustomerGroup()->getID();
    $oCheckBox           = new CheckBox();
    $fehlendeAngaben     = array_merge(
        $fehlendeAngaben,
        $oCheckBox->validateCheckBox(CHECKBOX_ORT_REGISTRIERUNG, $kKundengruppe, $post, true)
    );

    if (isset($post['shipping_address'])) {
        if ((int)$post['shipping_address'] === 0) {
            $post['kLieferadresse'] = 0;
            $post['lieferdaten']    = 1;
            pruefeLieferdaten($post);
        } elseif (isset($post['kLieferadresse']) && (int)$post['kLieferadresse'] > 0) {
            pruefeLieferdaten($post);
        } elseif (isset($post['register']['shipping_address'])) {
            pruefeLieferdaten($post['register']['shipping_address'], $fehlendeAngaben);
        }
    } elseif (isset($post['lieferdaten']) && (int)$post['lieferdaten'] === 1) {
        // compatibility with older template
        pruefeLieferdaten($post, $fehlendeAngaben);
    }
    $nReturnValue = angabenKorrekt($fehlendeAngaben);

    executeHook(HOOK_REGISTRIEREN_PAGE_REGISTRIEREN_PLAUSI, [
        'nReturnValue'    => &$nReturnValue,
        'fehlendeAngaben' => &$fehlendeAngaben
    ]);

    if ($nReturnValue) {
        // CheckBox Spezialfunktion ausführen
        $oCheckBox->triggerSpecialFunction(
            CHECKBOX_ORT_REGISTRIERUNG,
            $kKundengruppe,
            true,
            $post,
            ['oKunde' => $knd]
        )->checkLogging(CHECKBOX_ORT_REGISTRIERUNG, $kKundengruppe, $post, true);

        if ($edit && $_SESSION['Kunde']->kKunde > 0) {
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
                        if ($i === 0) {
                            $cSQL .= 'kKundenfeld != ' . (int)$oKundenfeldNichtEditierbar->kKundenfeld;
                        } else {
                            $cSQL .= ' AND kKundenfeld != ' . (int)$oKundenfeldNichtEditierbar->kKundenfeld;
                        }
                    }
                    $cSQL .= ')';
                }

                $db->query(
                    'DELETE FROM tkundenattribut WHERE kKunde = ' . (int)$_SESSION['Kunde']->kKunde . $cSQL,
                    \DB\ReturnType::AFFECTED_ROWS
                );
                foreach (array_keys($cKundenattribut_arr) as $kKundenfeld) {
                    $oKundenattribut              = new stdClass();
                    $oKundenattribut->kKunde      = (int)$_SESSION['Kunde']->kKunde;
                    $oKundenattribut->kKundenfeld = $cKundenattribut_arr[$kKundenfeld]->kKundenfeld;
                    $oKundenattribut->cName       = $cKundenattribut_arr[$kKundenfeld]->cWawi;
                    $oKundenattribut->cWert       = $cKundenattribut_arr[$kKundenfeld]->cWert;

                    $db->insert('tkundenattribut', $oKundenattribut);
                }
            }

            $_SESSION['Kunde']                      = new Kunde($_SESSION['Kunde']->kKunde);
            $_SESSION['Kunde']->cKundenattribut_arr = $cKundenattribut_arr;
        } else {
            // Guthaben des Neukunden aufstocken insofern er geworben wurde
            $oNeukunde     = $db->select(
                'tkundenwerbenkunden',
                'cEmail',
                $knd->cMail,
                'nRegistriert',
                0
            );
            $kKundengruppe = \Session\Frontend::getCustomerGroup()->getID();
            if (isset($oNeukunde->kKundenWerbenKunden, $conf['kundenwerbenkunden']['kwk_kundengruppen'])
                && $oNeukunde->kKundenWerbenKunden > 0
                && (int)$conf['kundenwerbenkunden']['kwk_kundengruppen'] > 0
            ) {
                $kKundengruppe = (int)$conf['kundenwerbenkunden']['kwk_kundengruppen'];
            }

            $knd->kKundengruppe     = $kKundengruppe;
            $knd->kSprache          = Shop::getLanguage();
            $knd->cAbgeholt         = 'N';
            $knd->cSperre           = 'N';
            $knd->cAktiv            = $conf['global']['global_kundenkonto_aktiv'] === 'A'
                ? 'N'
                : 'Y';
            $cPasswortKlartext      = $knd->cPasswort;
            $knd->cPasswort         = Shop::Container()->getPasswordService()->hash($cPasswortKlartext);
            $knd->dErstellt         = 'NOW()';
            $knd->nRegistriert      = 1;
            $knd->angezeigtesLand   = Sprache::getCountryCodeByCountryName($knd->cLand);
            $cLand                  = $knd->cLand;
            $knd->cPasswortKlartext = $cPasswortKlartext;
            $obj                    = new stdClass();
            $obj->tkunde            = $knd;
            sendeMail(MAILTEMPLATE_NEUKUNDENREGISTRIERUNG, $obj);

            $knd->cLand = $cLand;
            unset($knd->cPasswortKlartext, $knd->Anrede);

            $knd->kKunde = $knd->insertInDB();
            // Kampagne
            if (isset($_SESSION['Kampagnenbesucher'])) {
                Kampagne::setCampaignAction(KAMPAGNE_DEF_ANMELDUNG, $knd->kKunde, 1.0); // Anmeldung
            }
            // Insert Kundenattribute
            foreach (array_keys($cKundenattribut_arr) as $kKundenfeld) {
                $oKundenattribut              = new stdClass();
                $oKundenattribut->kKunde      = $knd->kKunde;
                $oKundenattribut->kKundenfeld = $cKundenattribut_arr[$kKundenfeld]->kKundenfeld;
                $oKundenattribut->cName       = $cKundenattribut_arr[$kKundenfeld]->cWawi;
                $oKundenattribut->cWert       = $cKundenattribut_arr[$kKundenfeld]->cWert;

                $db->insert('tkundenattribut', $oKundenattribut);
            }
            if ($conf['global']['global_kundenkonto_aktiv'] !== 'A') {
                $_SESSION['Kunde']                      = new Kunde($knd->kKunde);
                $_SESSION['Kunde']->cKundenattribut_arr = $cKundenattribut_arr;
            } else {
                $step = 'formular eingegangen';
            }
            // Guthaben des Neukunden aufstocken insofern er geworben wurde
            if (isset($oNeukunde->kKundenWerbenKunden) && $oNeukunde->kKundenWerbenKunden > 0) {
                $db->queryPrepared(
                    'UPDATE tkunde
                        SET fGuthaben = fGuthaben + :amount
                        WHERE kKunde = :cid',
                    [
                        'cid'    => (int)$knd->kKunde,
                        'amount' => (float)$conf['kundenwerbenkunden']['kwk_neukundenguthaben']
                    ],
                    \DB\ReturnType::AFFECTED_ROWS
                );
                $db->update('tkundenwerbenkunden', 'cEmail', $knd->cMail, (object)['nRegistriert' => 1]);
            }
        }
        if (isset($cart->kWarenkorb) && $cart->gibAnzahlArtikelExt([C_WARENKORBPOS_TYP_ARTIKEL]) > 0) {
            Tax::setTaxRates();
            $cart->gibGesamtsummeWarenLocalized();
        }
        if ((int)$post['checkout'] === 1) {
            //weiterleitung zum chekout
            header('Location: ' . Shop::Container()->getLinkService()
                                      ->getStaticRoute('bestellvorgang.php', true) . '?reg=1', true, 303);
            exit;
        }
        if (isset($post['ajaxcheckout_return']) && (int)$post['ajaxcheckout_return'] === 1) {
            return 1;
        }
        if ($conf['global']['global_kundenkonto_aktiv'] !== 'A') {
            //weiterleitung zu mein Konto
            header('Location: ' . Shop::Container()->getLinkService()
                                      ->getStaticRoute('jtl.php', true) . '?reg=1', true, 303);
            exit;
        }
    } else {
        if ((int)$post['checkout'] === 1) {
            //weiterleitung zum chekout
            $_SESSION['checkout.register']        = 1;
            $_SESSION['checkout.fehlendeAngaben'] = $fehlendeAngaben;
            $_SESSION['checkout.cPost_arr']       = $post;

            //keep shipping address on error
            if (isset($post['register']['shipping_address'])) {
                $_SESSION['Lieferadresse'] = getLieferdaten($post['register']['shipping_address']);
            }

            header('Location: ' . Shop::Container()->getLinkService()
                                      ->getStaticRoute('bestellvorgang.php', true) . '?reg=1', true, 303);
            exit;
        }
        Shop::Smarty()->assign('fehlendeAngaben', $fehlendeAngaben);
        $Kunde = $knd;

        return $fehlendeAngaben;
    }

    return [];
}

/**
 * @param int $nCheckout
 */
function gibFormularDaten(int $nCheckout = 0)
{
    global $cKundenattribut_arr, $Kunde;

    if ($cKundenattribut_arr === null || count($cKundenattribut_arr) === 0) {
        $cKundenattribut_arr = $_SESSION['Kunde']->cKundenattribut_arr ?? [];
    }

    $herkunfte = Shop::Container()->getDB()->query(
        'SELECT * 
            FROM tkundenherkunft 
            ORDER BY nSort',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );

    Shop::Smarty()->assign('herkunfte', $herkunfte)
        ->assign('Kunde', $Kunde)
        ->assign('cKundenattribut_arr', $cKundenattribut_arr)
        ->assign(
            'laender',
            ShippingMethod::getPossibleShippingCountries(\Session\Frontend::getCustomerGroup()->getID(), false, true)
        )
        ->assign(
            'warning_passwortlaenge',
            lang_passwortlaenge(Shop::getSettingValue(CONF_KUNDEN, 'kundenregistrierung_passwortlaenge'))
        )
        ->assign('oKundenfeld_arr', gibSelbstdefKundenfelder());

    if ($nCheckout === 1) {
        Shop::Smarty()->assign('checkout', 1)
            ->assign('bestellschritt', [1 => 1, 2 => 3, 3 => 3, 4 => 3, 5 => 3]); // Rechnungsadresse ändern
    }
}

/**
 *
 */
function gibKunde()
{
    global $Kunde, $titel;

    $Kunde = $_SESSION['Kunde'];
    $titel = Shop::Lang()->get('editData', 'login');
}

/**
 * @param string $vCardFile
 */
function gibKundeFromVCard($vCardFile)
{
    if (is_file($vCardFile)) {
        global $Kunde;

        try {
            $vCard = new VCard(file_get_contents($vCardFile), ['handling' => VCard::OPT_ERR_RAISE]);
            $Kunde = $vCard->selectVCard(0)->asKunde();
            Shop::Smarty()->assign('Kunde', $Kunde);
        } catch (Exception $e) {
            Shop::Container()->getAlertService()->addAlert(
                Alert::TYPE_ERROR,
                Shop::Lang()->get('uploadError'),
                'uploadError'
            );
        }
    }
}
