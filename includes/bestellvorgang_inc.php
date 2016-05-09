<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param string $userLogin
 * @param string $passLogin
 * @return int
 */
function fuehreLoginAus($userLogin, $passLogin)
{
    global $hinweis, $Kunde;

    if (strlen($userLogin) > 0 && strlen($passLogin) > 0) {
        $csrfTest = validateToken();
        if ($csrfTest === false) {
            $hinweis = Shop::Lang()->get('csrfValidationFailed', 'global');
            Jtllog::writeLog('CSRF-Warnung fuer Login: ' . $_POST['login'], JTLLOG_LEVEL_ERROR);

            return 0;
        }
        $Kunde = new Kunde();
        $Kunde->holLoginKunde($userLogin, $passLogin);
        if (isset($Kunde->kKunde) && $Kunde->kKunde > 0) {
            $_customer         = new stdClass();
            $_customer->kKunde = (int)$Kunde->kKunde;
            Shop::DB()->update('tbesucher', 'cIP', gibIP(), $_customer);
            unset($_SESSION['Zahlungsart']);
            unset($_SESSION['Versandart']);
            $_SESSION['Warenkorb']->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR)
                                  ->loescheSpezialPos(C_WARENKORBPOS_TYP_NEUKUNDENKUPON)
                                  ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR)
                                  ->loescheSpezialPos(C_WARENKORBPOS_TYP_KUPON);
            unset($_SESSION['Lieferadresse']);
            unset($_SESSION['ks']);
            unset($_SESSION['VersandKupon']);
            unset($_SESSION['oVersandfreiKupon']);
            unset($_SESSION['NeukundenKupon']);
            unset($_SESSION['Kupon']);
            $Kunde->angezeigtesLand = ISO2land($Kunde->cLand);
            $session                = Session::getInstance();
            $session->setCustomer($Kunde);
            // Prüfe ob Artikel im Warenkorb vorhanden sind, welche für den aktuellen Kunden nicht mehr sichtbar sein dürfen
            require_once PFAD_ROOT . PFAD_INCLUDES . 'jtl_inc.php';
            pruefeWarenkorbArtikelSichtbarkeit($_SESSION['Kunde']->kKundengruppe);
            $conf = Shop::getSettings(array(CONF_GLOBAL, CONF_KAUFABWICKLUNG));
            if ($conf['global']['warenkorbpers_nutzen'] === 'Y' && $conf['kaufabwicklung']['warenkorb_warenkorb2pers_merge'] === 'Y') {
                setzeWarenkorbPersInWarenkorb($_SESSION['Kunde']->kKunde);
            } elseif ($conf['global']['warenkorbpers_nutzen'] === 'Y' && $conf['kaufabwicklung']['warenkorb_warenkorb2pers_merge'] === 'P') {
                $oWarenkorbPers = new WarenkorbPers($Kunde->kKunde);
                if (count($oWarenkorbPers->oWarenkorbPersPos_arr) > 0) {
                    Shop::Smarty()->assign('nWarenkorb2PersMerge', 1);
                }
            }

            return 1;
        }
    }
    $hinweis = Shop::Lang()->get('incorrectLogin', 'global');

    return 0;
}

/**
 *
 */
function pruefeBestellungMoeglich()
{
    header('Location: warenkorb.php?fillOut=' . $_SESSION['Warenkorb']->istBestellungMoeglich(), true, 303);
    exit;
}

/**
 * @param int  $Versandart
 * @param int  $aFormValues
 * @param bool $bMsg
 * @return bool
 */
function pruefeVersandartWahl($Versandart, $aFormValues = 0, $bMsg = true)
{
    global $hinweis, $step;

    $nReturnValue = versandartKorrekt(intval($Versandart), $aFormValues);
    executeHook(HOOK_BESTELLVORGANG_PAGE_STEPVERSAND_PLAUSI);

    if ($nReturnValue) {
        $step = 'Zahlung';

        return true;
    }
    if ($bMsg) {
        $hinweis = Shop::Lang()->get('fillShipping', 'checkout');
    }
    $step = 'Versand';

    return false;
}

/**
 * @param array $cPost_arr
 * @return int
 */
function pruefeUnregistriertBestellen($cPost_arr)
{
    global $step, $Kunde;

    unset($_SESSION['Lieferadresse']);
    unset($_SESSION['Versandart']);
    unset($_SESSION['Zahlungsart']);
    $_SESSION['Warenkorb']->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
                          ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART);
    $step = 'unregistriert bestellen';
    unset($_SESSION['Kunde']);
    $fehlendeAngaben     = checkKundenFormular(0);
    $Kunde               = getKundendaten($cPost_arr, 0);
    $cKundenattribut_arr = getKundenattribute($cPost_arr);
    $kKundengruppe       = Kundengruppe::getCurrent();
    // CheckBox Plausi
    require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.CheckBox.php';
    $oCheckBox       = new CheckBox();
    $fehlendeAngaben = array_merge($fehlendeAngaben, $oCheckBox->validateCheckBox(CHECKBOX_ORT_REGISTRIERUNG, $kKundengruppe, $cPost_arr, true));
    $nReturnValue    = angabenKorrekt($fehlendeAngaben);

    executeHook(HOOK_BESTELLVORGANG_INC_UNREGISTRIERTBESTELLEN_PLAUSI, array(
            'nReturnValue'    => &$nReturnValue,
            'fehlendeAngaben' => &$fehlendeAngaben,
            'Kunde'           => &$Kunde,
            'cPost_arr'       => &$cPost_arr
        )
    );

    if ($nReturnValue) {
        // CheckBox Spezialfunktion ausführen
        $oCheckBox->triggerSpecialFunction(CHECKBOX_ORT_REGISTRIERUNG, $kKundengruppe, true, $cPost_arr, array('oKunde' => $Kunde));
        $oCheckBox->checkLogging(CHECKBOX_ORT_REGISTRIERUNG, $kKundengruppe, $cPost_arr, true);
        //selbstdef. Kundenattr in session setzen
        $Kunde->cKundenattribut_arr = $cKundenattribut_arr;
        $Kunde->nRegistriert        = 0;
        setzeInSession('Kunde', $Kunde);
        if ((isset($_SESSION['Warenkorb']->kWarenkorb)) && $_SESSION['Warenkorb']->gibAnzahlArtikelExt(array(C_WARENKORBPOS_TYP_ARTIKEL)) > 0) {
            if (isset($_SESSION['Lieferadresse']) && $_SESSION['Bestellung']->kLieferadresse == 0) {
                setzeLieferadresseAusRechnungsadresse();
            }
            setzeSteuersaetze();
            $_SESSION['Warenkorb']->gibGesamtsummeWarenLocalized();
        }

        executeHook(HOOK_BESTELLVORGANG_INC_UNREGISTRIERTBESTELLEN);

        return 1;
    }
    Shop::Smarty()->assign('cKundenattribut_arr', $cKundenattribut_arr)
        ->assign('fehlendeAngaben', $fehlendeAngaben)
        ->assign('cPost_var', StringHandler::filterXSS($cPost_arr));

    return 0;
}

/**
 * @param array $cPost_arr
 * @return string
 */
function pruefeLieferdaten($cPost_arr)
{
    global $step, $Lieferadresse;

    $step = 'Lieferadresse';
    unset($_SESSION['Lieferadresse']);
    if (!isset($_SESSION['Bestellung'])) {
        $_SESSION['Bestellung'] = new stdClass();
    }
    $_SESSION['Bestellung']->kLieferadresse = (isset($cPost_arr['kLieferadresse'])) ?
        (int)$cPost_arr['kLieferadresse'] :
        -1;
    $_SESSION['Warenkorb']->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS);
    unset($_SESSION['Versandart']);
    //neue lieferadresse
    if (!isset($cPost_arr['kLieferadresse']) || intval($cPost_arr['kLieferadresse']) === -1) {
        $fehlendeAngaben = checkLieferFormular();
        $Lieferadresse   = getLieferdaten($cPost_arr);
        $nReturnValue    = angabenKorrekt($fehlendeAngaben);

        executeHook(HOOK_BESTELLVORGANG_PAGE_STEPLIEFERADRESSE_NEUELIEFERADRESSE_PLAUSI,
            array(
                'nReturnValue'    => &$nReturnValue,
                'fehlendeAngaben' => &$fehlendeAngaben
            )
        );
        if ($nReturnValue) {
            // Anrede mappen
            if ($Lieferadresse->cAnrede === 'm') {
                $Lieferadresse->cAnredeLocalized = Shop::Lang()->get('salutationM', 'global');
            } elseif ($Lieferadresse->cAnrede === 'w') {
                $Lieferadresse->cAnredeLocalized = Shop::Lang()->get('salutationW', 'global');
            }
            setzeInSession('Lieferadresse', $Lieferadresse);
            executeHook(HOOK_BESTELLVORGANG_PAGE_STEPLIEFERADRESSE_NEUELIEFERADRESSE);
            pruefeVersandkostenfreiKuponVorgemerkt();
        } else {
            Shop::Smarty()->assign('fehlendeAngaben', $fehlendeAngaben);
        }
    } elseif (intval($cPost_arr['kLieferadresse']) > 0) { //vorhandene lieferadresse
        $LA = Shop::DB()->query("
            SELECT kLieferadresse
                FROM tlieferadresse
                WHERE kKunde = " . (int)$_SESSION['Kunde']->kKunde . "
                    AND kLieferadresse = " . (int)$cPost_arr['kLieferadresse'], 1
        );
        if ($LA->kLieferadresse > 0) {
            $oLieferadresse = new Lieferadresse($LA->kLieferadresse);
            setzeInSession('Lieferadresse', $oLieferadresse);

            executeHook(HOOK_BESTELLVORGANG_PAGE_STEPLIEFERADRESSE_VORHANDENELIEFERADRESSE);
        }
    } elseif (intval($cPost_arr['kLieferadresse']) === 0) { //lieferadresse gleich rechnungsadresse
        setzeLieferadresseAusRechnungsadresse();

        executeHook(HOOK_BESTELLVORGANG_PAGE_STEPLIEFERADRESSE_RECHNUNGLIEFERADRESSE);
    }
    setzeSteuersaetze();
    //lieferland hat sich geändert und versandart schon gewählt?
    if (isset($_SESSION['Lieferadresse']) && isset($_SESSION['Versandart']) && $_SESSION['Lieferadresse'] && $_SESSION['Versandart']) {
        $delVersand = false;
        if (!stristr($_SESSION['Versandart']->cLaender, $_SESSION['Lieferadresse']->cLand)) {
            $delVersand = true;
        }
        //ist die plz im zuschlagsbereich?
        $plz   = Shop::DB()->escape($_SESSION['Lieferadresse']->cPLZ);
        $plz_x = Shop::DB()->query(
            "SELECT kVersandzuschlagPlz
                FROM tversandzuschlagplz, tversandzuschlag
                WHERE tversandzuschlag.kVersandart = " . (int)$_SESSION['Versandart']->kVersandart . "
                AND tversandzuschlag.kVersandzuschlag = tversandzuschlagplz.kVersandzuschlag
                AND ((tversandzuschlagplz.cPLZAb <= '" . $plz . "'
                AND tversandzuschlagplz.cPLZBis >= '" . $plz . "')
                OR tversandzuschlagplz.cPLZ = '" . $plz . "')", 1
        );
        if (!empty($plz_x->kVersandzuschlagPlz)) {
            $delVersand = true;
        }
        if ($delVersand) {
            $_SESSION['Warenkorb']->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
                                  ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG)
                                  ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR)
                                  ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
                                  ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
                                  ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR);
            unset($_SESSION['Versandart']);
            unset($_SESSION['Zahlungsart']);
        } else {
            $_SESSION['Warenkorb']->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG);
        }
    }
    plausiGuthaben($cPost_arr);

    return $step;
}

/**
 * @param array $cPost_arr
 */
function plausiGuthaben($cPost_arr)
{
    //guthaben
    if ((isset($cPost_arr['guthabenVerrechnen']) && intval($cPost_arr['guthabenVerrechnen']) === 1) ||
        (isset($_SESSION['Bestellung']->GuthabenNutzen) && intval($_SESSION['Bestellung']->GuthabenNutzen) === 1)
    ) {
        $_SESSION['Bestellung']->GuthabenNutzen   = 1;
        $_SESSION['Bestellung']->fGuthabenGenutzt = min($_SESSION['Kunde']->fGuthaben, $_SESSION['Warenkorb']->gibGesamtsummeWaren(true, false));
        executeHook(HOOK_BESTELLVORGANG_PAGE_STEPBESTAETIGUNG_GUTHABENVERRECHNEN);
    }
}

/**
 *
 */
function pruefeVersandkostenStep()
{
    global $step;

    if (isset($_SESSION['Kunde']) && isset($_SESSION['Lieferadresse'])) {
        //artikelabhängige versandkosten
        $_SESSION['Warenkorb']->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG);
        $arrArtikelabhaengigeVersandkosten = VersandartHelper::gibArtikelabhaengigeVersandkostenImWK($_SESSION['Lieferadresse']->cLand, $_SESSION['Warenkorb']->PositionenArr);
        foreach ($arrArtikelabhaengigeVersandkosten as $oVersandPos) {
            $_SESSION['Warenkorb']->erstelleSpezialPos(
                $oVersandPos->cName,
                1,
                $oVersandPos->fKosten,
                $_SESSION['Warenkorb']->gibVersandkostenSteuerklasse($_SESSION['Lieferadresse']->cLand),
                C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG,
                false
            );
        }
        $step = 'Versand';
    }
}

/**
 *
 */
function pruefeZahlungStep()
{
    global $step;
    if (isset($_SESSION['Kunde']) && isset($_SESSION['Lieferadresse']) && isset($_SESSION['Versandart'])) {
        $step = 'Zahlung';
    }
}

/**
 *
 */
function pruefeBestaetigungStep()
{
    global $step;
    if (isset($_SESSION['Kunde']) && isset($_SESSION['Lieferadresse']) && isset($_SESSION['Versandart']) && isset($_SESSION['Zahlungsart'])) {
        $step = 'Bestaetigung';
    }
    if (isset($_SESSION['Zahlungsart'])) {
        if (isset($_SESSION['Zahlungsart']->cZusatzschrittTemplate) && strlen($_SESSION['Zahlungsart']->cZusatzschrittTemplate) > 0) {
            $paymentMethod = PaymentMethod::create($_SESSION['Zahlungsart']->cModulId);
            if (is_object($paymentMethod)) {
                if (!$paymentMethod->validateAdditional()) {
                    $step = 'Zahlung';
                }
            }
        }
    }
}

/**
 * @param array $cGet_arr
 */
function pruefeRechnungsadresseStep($cGet_arr)
{
    global $step, $Kunde;
    //sondersteps Rechnungsadresse ändern
    if (isset($cGet_arr['editRechnungsadresse']) && $cGet_arr['editRechnungsadresse'] == 1 && $_SESSION['Kunde']) {
        resetNeuKundenKupon();
        if ($_SESSION['Kunde']->kKunde > 0) {
            //weiterleitung zur Rechnungsänderung eines bestehenden Kunden
            header('Location: registrieren.php?checkout=1&editRechnungsadresse=1', true, 303);
            exit;
        } else {
            $Kunde = $_SESSION['Kunde'];
            $step  = 'unregistriert bestellen';
        }
    }
}

/**
 * @param array $cGet_arr
 */
function pruefeLieferadresseStep($cGet_arr)
{
    global $step, $Lieferadresse;
    //sondersteps Lieferadresse ändern
    if (isset($cGet_arr['editLieferadresse']) && $cGet_arr['editLieferadresse'] == 1 && $_SESSION['Lieferadresse']) {
        resetNeuKundenKupon();
        unset($_SESSION['Zahlungsart']);
        unset($_SESSION['TrustedShops']);
        unset($_SESSION['Versandart']);
        $Lieferadresse = $_SESSION['Lieferadresse'];
        $step          = 'Lieferadresse';
    }
}

/**
 * Prüft ob im WK ein Versandfrei Kupon eingegeben wurde und falls ja, wird dieser nach Eingabe der Lieferadresse gesetzt (falls Kriterien erfüllt)
 *
 * @return array
 */
function pruefeVersandkostenfreiKuponVorgemerkt()
{
    if ((isset($_SESSION['Kupon']) && $_SESSION['Kupon']->cKuponTyp === 'versandkupon') ||
        (isset($_SESSION['oVersandfreiKupon']) && $_SESSION['oVersandfreiKupon']->cKuponTyp === 'versandkupon')
    ) {
        $_SESSION['Warenkorb']->loescheSpezialPos(C_WARENKORBPOS_TYP_KUPON);
        unset($_SESSION['Kupon']);
    }
    $Kuponfehler = array();
    if (isset($_SESSION['oVersandfreiKupon']->kKupon) && $_SESSION['oVersandfreiKupon']->kKupon > 0) { // Wurde im WK ein Versandfreikupon eingegeben?
        $Kuponfehler = checkeKupon($_SESSION['oVersandfreiKupon']);
        if (angabenKorrekt($Kuponfehler)) {
            kuponAnnehmen($_SESSION['oVersandfreiKupon']);
            Shop::Smarty()->assign('KuponMoeglich', kuponMoeglich());
        }
    }

    return $Kuponfehler;
}

/**
 * @param array $cGet_arr
 */
function pruefeVersandartStep($cGet_arr)
{
    global $step;
    //sondersteps Versandart ändern
    if (isset($cGet_arr['editVersandart']) && $cGet_arr['editVersandart'] == 1 && isset($_SESSION['Versandart'])) {
        resetNeuKundenKupon();
        $_SESSION['Warenkorb']->loescheSpezialPos(C_WARENKORBPOS_TYP_VERPACKUNG)
                              ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
                              ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG)
                              ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR)
                              ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
                              ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
                              ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR);
        unset($_SESSION['Zahlungsart']);
        unset($_SESSION['TrustedShops']);
        unset($_SESSION['Versandart']);
        $step = 'Versand';
    }
}

/**
 * @param array $cGet_arr
 */
function pruefeZahlungsartStep($cGet_arr)
{
    global $step, $hinweis;
    //sondersteps Zahlungsart ändern
    if (isset($cGet_arr['editZahlungsart']) && $cGet_arr['editZahlungsart'] == 1 && isset($_SESSION['Zahlungsart'])) {
        resetNeuKundenKupon();
        $_SESSION['Warenkorb']->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
                              ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
                              ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR)
                              ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR);
        unset($_SESSION['Zahlungsart']);
        $step = 'Zahlung';
    }
    // Hinweis?
    if (isset($cGet_arr['nHinweis']) && intval($cGet_arr['nHinweis']) > 0) {
        $hinweis = mappeBestellvorgangZahlungshinweis(intval($cGet_arr['nHinweis']));
    }
}

/**
 * @param array $cPost_arr
 * @return int
 */
function pruefeZahlungsartwahlStep($cPost_arr)
{
    global $zahlungsangaben, $hinweis, $step;

    if (isset($cPost_arr['zahlungsartwahl']) && intval($cPost_arr['zahlungsartwahl']) === 1) {
        $zahlungsangaben = zahlungsartKorrekt(intval($cPost_arr['Zahlungsart']));
        $conf            = Shop::getSettings(array(CONF_TRUSTEDSHOPS));
        executeHook(HOOK_BESTELLVORGANG_PAGE_STEPZAHLUNG_PLAUSI);
        // Trusted Shops
        if ($conf['trustedshops']['trustedshops_nutzen'] === 'Y' && isset($cPost_arr['bTS']) && intval($cPost_arr['bTS']) === 1 &&
            $zahlungsangaben > 0 && $_SESSION['Zahlungsart']->nWaehrendBestellung == 0
        ) {
            $_SESSION['TrustedShops']->cKaeuferschutzProdukt = StringHandler::htmlentities(StringHandler::filterXSS($cPost_arr['cKaeuferschutzProdukt']));

            $fNetto        = $_SESSION['TrustedShops']->oKaeuferschutzProduktIDAssoc_arr[StringHandler::htmlentities(StringHandler::filterXSS($cPost_arr['cKaeuferschutzProdukt']))];
            $cLandISO      = (isset($_SESSION['Lieferadresse']->cLand)) ? $_SESSION['Lieferadresse']->cLand : '';
            $kSteuerklasse = $_SESSION['Warenkorb']->gibVersandkostenSteuerklasse($cLandISO);
            $fPreis        = ($_SESSION['Kundengruppe']->nNettoPreise) ? $fNetto : ($fNetto * ((100 + doubleval($_SESSION['Steuersatz'][$kSteuerklasse])) / 100));
            $cName['ger']  = Shop::Lang()->get('trustedshopsName', 'global');
            $cName['eng']  = Shop::Lang()->get('trustedshopsName', 'global');
            $_SESSION['Warenkorb']->erstelleSpezialPos(
                $cName,
                1,
                $fPreis,
                $kSteuerklasse,
                C_WARENKORBPOS_TYP_TRUSTEDSHOPS,
                true,
                (bool) !$_SESSION['Kundengruppe']->nNettoPreise
            );
        }

        switch ($zahlungsangaben) {
            case 0:
                $hinweis = Shop::Lang()->get('fillPayment', 'checkout');
                $step    = 'Zahlung';

                return 0;
                break;
            case 1:
                $step = 'ZahlungZusatzschritt';

                return 1;
                break;
            case 2:
                $step = 'Bestaetigung';

                return 2;
                break;
        }
    }
}

/**
 *
 */
function pruefeGuthabenNutzen()
{
    if (isset($_SESSION['Bestellung']->GuthabenNutzen) && $_SESSION['Bestellung']->GuthabenNutzen) {
        $_SESSION['Bestellung']->fGuthabenGenutzt   = min($_SESSION['Kunde']->fGuthaben, $_SESSION['Warenkorb']->gibGesamtsummeWaren(true, false));
        $_SESSION['Bestellung']->GutscheinLocalized = gibPreisStringLocalized($_SESSION['Bestellung']->fGuthabenGenutzt);
    }

    executeHook(HOOK_BESTELLVORGANG_PAGE_STEPBESTAETIGUNG_GUTHABEN_PLAUSI);
}

/**
 *
 */
function gibStepAccountwahl()
{
    global $hinweis;
    // Einstellung global_kundenkonto_aktiv ist auf 'A' und Kunde wurde nach der Registrierung zurück zur Accountwahl geleitet
    if (isset($_REQUEST['reg']) && intval($_REQUEST['reg']) === 1) {
        $hinweis = Shop::Lang()->get('accountCreated') . '<br />' . Shop::Lang()->get('loginNotActivated');
    }
    Shop::Smarty()->assign('untertitel', lang_warenkorb_bestellungEnthaeltXArtikel($_SESSION['Warenkorb']));

    executeHook(HOOK_BESTELLVORGANG_PAGE_STEPACCOUNTWAHL);
}

/**
 *
 */
function gibStepUnregistriertBestellen()
{
    global $Kunde;
    $conf      = Shop::getSettings(array(CONF_KUNDEN));
    $herkunfte = Shop::DB()->query("SELECT * FROM tkundenherkunft ORDER BY nSort", 2);
    if (isset($Kunde->dGeburtstag)) {
        if (preg_match('/^\d{4}\-\d{2}\-(\d{2})$/', $Kunde->dGeburtstag)) {
            list($jahr, $monat, $tag) = explode('-', $Kunde->dGeburtstag);
            $Kunde->dGeburtstag       = $tag . '.' . $monat . '.' . $jahr;
        }
    }
    Shop::Smarty()->assign('untertitel', Shop::Lang()->get('fillUnregForm', 'checkout'))
        ->assign('herkunfte', $herkunfte)
        ->assign('Kunde', (isset($Kunde) ? $Kunde : null))
        ->assign('laender', gibBelieferbareLaender($_SESSION['Kundengruppe']->kKundengruppe))
        ->assign('oKundenfeld_arr', gibSelbstdefKundenfelder())
        ->assign('nAnzeigeOrt', CHECKBOX_ORT_REGISTRIERUNG)
        ->assign('code_registrieren', generiereCaptchaCode($conf['kunden']['registrieren_captcha']));
    if (isset($Kunde->cKundenattribut_arr) && is_array($Kunde->cKundenattribut_arr)) {
        Shop::Smarty()->assign('cKundenattribut_arr', $Kunde->cKundenattribut_arr);
    }

    executeHook(HOOK_BESTELLVORGANG_PAGE_STEPUNREGISTRIERTBESTELLEN);
}

/**
 * fix für /jtl-shop/issues#219
 */
function validateCouponInCheckout()
{
    if (isset($_SESSION['Kupon'])) {
        $checkCouponResult = checkeKupon($_SESSION['Kupon']);
        if (count($checkCouponResult) !== 0) {
            $_SESSION['Warenkorb']->loescheSpezialPos(C_WARENKORBPOS_TYP_KUPON);
            $_SESSION['checkCouponResult'] = $checkCouponResult;
            unset($_SESSION['Kupon']);
            header('Location: ' . Shop::getURL() . '/warenkorb.php');
            exit(0);
        }
    }
}
/**
 * @return mixed
 */
function gibStepLieferadresse()
{
    global $Lieferadresse;

    $kKundengruppe = $_SESSION['Kundengruppe']->kKundengruppe;

    if ($_SESSION['Kunde']->kKunde > 0) {
        $Lieferadressen        = array();
        $oLieferadresseTMP_arr = Shop::DB()->query(
            "SELECT DISTINCT(kLieferadresse)
                FROM tlieferadresse
                WHERE kKunde = " . (int)$_SESSION['Kunde']->kKunde, 2
        );
        if (is_array($oLieferadresseTMP_arr) && count($oLieferadresseTMP_arr) > 0) {
            foreach ($oLieferadresseTMP_arr as $oLieferadresseTMP) {
                if ($oLieferadresseTMP->kLieferadresse > 0) {
                    $oLieferadresse   = new Lieferadresse($oLieferadresseTMP->kLieferadresse);
                    $Lieferadressen[] = $oLieferadresse;
                }
            }
        }
        Shop::Smarty()->assign('Lieferadressen', $Lieferadressen);
        $kKundengruppe = $_SESSION['Kunde']->kKundengruppe;
    }
    Shop::Smarty()->assign('laender', gibBelieferbareLaender($kKundengruppe))
        ->assign('Kunde', $_SESSION['Kunde'])
        ->assign('kLieferadresse', ((isset($_SESSION['Bestellung']->kLieferadresse)) ? $_SESSION['Bestellung']->kLieferadresse : null));
    if (isset($_SESSION['Bestellung']->kLieferadresse) && $_SESSION['Bestellung']->kLieferadresse == -1) {
        Shop::Smarty()->assign('Lieferadresse', $Lieferadresse);
    }
    executeHook(HOOK_BESTELLVORGANG_PAGE_STEPLIEFERADRESSE);

    return $Lieferadresse;
}

/**
 *
 */
function gibStepZahlung()
{
    global $step;
    $conf          = Shop::getSettings(array(CONF_TRUSTEDSHOPS));
    $oTrustedShops = new stdClass();
    if ($conf['trustedshops']['trustedshops_nutzen'] === 'Y' && (!isset($_SESSION['ajaxcheckout']) || $_SESSION['ajaxcheckout']->nEnabled < 5)) {
        unset($_SESSION['TrustedShops']);
        $_SESSION['Warenkorb']->loescheSpezialPos(C_WARENKORBPOS_TYP_TRUSTEDSHOPS);
        $oTrustedShops = gibTrustedShops();
        if ($oTrustedShops->nAktiv == 1 && $oTrustedShops->eType === TS_BUYERPROT_EXCELLENCE) {
            if (!isset($_SESSION['TrustedShops'])) {
                $_SESSION['TrustedShops'] = new stdClass();
            }
            $_SESSION['TrustedShops']->oKaeuferschutzProduktIDAssoc_arr = gibKaeuferschutzProdukteAssocID($oTrustedShops->oKaeuferschutzProdukte->item);
            Shop::Smarty()->assign('oTrustedShops', $oTrustedShops)
                ->assign('PFAD_GFX_TRUSTEDSHOPS', PFAD_GFX_TRUSTEDSHOPS);
        }
        Shop::Smarty()->assign('URL_SHOP', Shop::getURL());
    }

    $oZahlungsart_arr = gibZahlungsarten($_SESSION['Versandart']->kVersandart, $_SESSION['Kundengruppe']->kKundengruppe);
    if (is_array($oZahlungsart_arr) && count($oZahlungsart_arr) === 1 && !isset($_GET['editZahlungsart']) && (!isset($_SESSION['TrustedShopsZahlung']) || !$_SESSION['TrustedShopsZahlung'])) {
        // Prüfe Zahlungsart
        $nZahglungsartStatus = zahlungsartKorrekt($oZahlungsart_arr[0]->kZahlungsart);
        if ($nZahglungsartStatus == 2) {
            // Prüfen ab es ein Trusted Shops Zertifikat gibt
            if ($conf['trustedshops']['trustedshops_nutzen'] === 'Y') {
                require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.TrustedShops.php';
                $oTrustedShops = new TrustedShops(-1, StringHandler::convertISO2ISO639($_SESSION['cISOSprache']));
            }
            if (isset($oTrustedShops->tsId) && strlen($oTrustedShops->tsId) > 0 && $oTrustedShops->eType === TS_BUYERPROT_EXCELLENCE) {
                $_SESSION['TrustedShopsZahlung'] = true;
                gibStepZahlung();
            }
        }
    } elseif (!is_array($oZahlungsart_arr) || count($oZahlungsart_arr) === 0) {
        if (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
            Jtllog::writeLog(
                'Es konnte keine Zahlungsart für folgende Daten gefunden werden: Versandart: ' . $_SESSION['Versandart']->kVersandart .
                ', Kundengruppe: ' . $_SESSION['Kundengruppe']->kKundengruppe, JTLLOG_LEVEL_ERROR
            );
        }
    }
    Shop::Smarty()->assign('Zahlungsarten', $oZahlungsart_arr)
        ->assign('Kunde', $_SESSION['Kunde'])
        ->assign('Lieferadresse', $_SESSION['Lieferadresse']);

    executeHook(HOOK_BESTELLVORGANG_PAGE_STEPZAHLUNG);
}

/**
 * @param array $cPost_arr
 */
function gibStepZahlungZusatzschritt($cPost_arr)
{
    $Zahlungsart = gibZahlungsart(intval($cPost_arr['Zahlungsart']));
    // Wenn Zahlungsart = Lastschrift ist => versuche Kundenkontodaten zu holen
    $oKundenKontodaten = gibKundenKontodaten($_SESSION['Kunde']->kKunde);
    if (isset($oKundenKontodaten->kKunde) && $oKundenKontodaten->kKunde > 0) {
        Shop::Smarty()->assign('oKundenKontodaten', $oKundenKontodaten);
    }
    if (!isset($cPost_arr['zahlungsartzusatzschritt']) || !$cPost_arr['zahlungsartzusatzschritt']) {
        Shop::Smarty()->assign('ZahlungsInfo', (isset($_SESSION['Zahlungsart']->ZahlungsInfo) ? $_SESSION['Zahlungsart']->ZahlungsInfo : null));
    } else {
        Shop::Smarty()->assign('fehlendeAngaben', checkAdditionalPayment($Zahlungsart))
            ->assign('ZahlungsInfo', gibPostZahlungsInfo());
    }
    Shop::Smarty()->assign('Zahlungsart', $Zahlungsart)
        ->assign('Kunde', $_SESSION['Kunde'])
        ->assign('Lieferadresse', $_SESSION['Lieferadresse']);

    executeHook(HOOK_BESTELLVORGANG_PAGE_STEPZAHLUNGZUSATZSCHRITT);
}

/**
 * @param array $cGet_arr
 * @return string
 */
function gibStepBestaetigung($cGet_arr)
{
    global $hinweis;
    //check currenct shipping method again to avoid using invalid methods when using one click method (#9566)
    if (isset($_SESSION['Versandart']->kVersandart) && !versandartKorrekt($_SESSION['Versandart']->kVersandart)) {
        header('Location: bestellvorgang.php?editVersandart=1', true, 303);
    }
    // Bei Standardzahlungsarten mit Zahlungsinformationen prüfen ob Daten vorhanden sind
    if (isset($_SESSION['Zahlungsart']) && in_array($_SESSION['Zahlungsart']->cModulId, array('za_lastschrift_jtl', 'za_kreditkarte_jtl'))) {
        if (!is_object($_SESSION['Zahlungsart']->ZahlungsInfo)) {
            header('Location: bestellvorgang.php?editZahlungsart=1', true, 303);
        }
    }
    if (isset($cGet_arr['fillOut']) && $cGet_arr['fillOut'] > 0) {
        if ($cGet_arr['fillOut'] == 5) {
            $hinweis = Shop::Lang()->get('acceptAgb', 'checkout');
        }
    } else {
        unset($_SESSION['cPlausi_arr']);
        unset($_SESSION['cPost_arr']);
    }
    //falls zahlungsart extern und Einstellung, dass Bestellung für Kaufabwicklung notwendig, füllte tzahlungsession
    Shop::Smarty()->assign('Kunde', $_SESSION['Kunde'])
        ->assign('Lieferadresse', $_SESSION['Lieferadresse'])
        ->assign('KuponMoeglich', kuponMoeglich())
        ->assign('GuthabenMoeglich', guthabenMoeglich())
        ->assign('nAnzeigeOrt', CHECKBOX_ORT_BESTELLABSCHLUSS)
        ->assign('cPost_arr', ((isset($_SESSION['cPost_arr'])) ? StringHandler::filterXSS($_SESSION['cPost_arr']) : array()));
    if ($_SESSION['Kunde']->kKunde > 0) {
        Shop::Smarty()->assign('GuthabenLocalized', $_SESSION['Kunde']->gibGuthabenLocalized());
    }
    if (isset($_SESSION['Versandart']->angezeigterHinweistext[$_SESSION['cISOSprache']]) && strlen($_SESSION['Versandart']->angezeigterHinweistext[$_SESSION['cISOSprache']]) > 0) {
        if (isset($_SESSION['Warenkorb']->PositionenArr) && count($_SESSION['Warenkorb']->PositionenArr) > 0) {
            foreach ($_SESSION['Warenkorb']->PositionenArr as $i => $oPosition) {
                if ($oPosition->nPosTyp == C_WARENKORBPOS_TYP_VERSANDPOS) {
                    $_SESSION['Warenkorb']->PositionenArr[$i]->cHinweis = $_SESSION['Versandart']->angezeigterHinweistext[$_SESSION['cISOSprache']];
                }
            }
        }
    }

    executeHook(HOOK_BESTELLVORGANG_PAGE_STEPBESTAETIGUNG);

    return $hinweis;
}

/**
 *
 */
function gibStepVersand()
{
    global $step;
    unset($_SESSION['TrustedShopsZahlung']);
    pruefeVersandkostenfreiKuponVorgemerkt();
    $lieferland = (isset($_SESSION['Lieferadresse']->cLand)) ? $_SESSION['Lieferadresse']->cLand : null;
    if (!$lieferland) {
        $lieferland = $_SESSION['Kunde']->cLand;
    }
    $plz = (isset($_SESSION['Lieferadresse']->cPLZ)) ? $_SESSION['Lieferadresse']->cPLZ : null;
    if (!$plz) {
        $plz = $_SESSION['Kunde']->cPLZ;
    }
    $kKundengruppe = (isset($_SESSION['Kunde']->kKundengruppe)) ? $_SESSION['Kunde']->kKundengruppe : null;
    if (!$kKundengruppe) {
        $kKundengruppe = $_SESSION['Kundengruppe']->kKundengruppe;
    }
    $oVersandart_arr = VersandartHelper::getPossibleShippingMethods($lieferland, $plz, VersandartHelper::getShippingClasses($_SESSION['Warenkorb']), $kKundengruppe);
    $oVerpackung_arr = gibMoeglicheVerpackungen($_SESSION['Kundengruppe']->kKundengruppe);
    if ((is_array($oVersandart_arr) && count($oVersandart_arr) > 0) ||
        (is_array($oVersandart_arr) && count($oVersandart_arr) === 1 && is_array($oVerpackung_arr) && count($oVerpackung_arr) > 0)) {
        Shop::Smarty()->assign('Versandarten', $oVersandart_arr)
            ->assign('Verpackungsarten', $oVerpackung_arr);
    } elseif (is_array($oVersandart_arr) && count($oVersandart_arr) === 1 && (is_array($oVerpackung_arr) && count($oVerpackung_arr) === 0)) {
        pruefeVersandartWahl($oVersandart_arr[0]->kVersandart);
    } elseif (!is_array($oVersandart_arr) || count($oVersandart_arr) === 0) {
        if (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
            Jtllog::writeLog(
                'Es konnte keine Versandart für folgende Daten gefunden werden: Lieferland: ' . $lieferland . ', PLZ: ' . $plz .
                ', Versandklasse: ' . VersandartHelper::getShippingClasses($_SESSION['Warenkorb']) . ', Kundengruppe: ' . $kKundengruppe,
                JTLLOG_LEVEL_ERROR
            );
        }
    }
    Shop::Smarty()->assign('Kunde', $_SESSION['Kunde'])
        ->assign('Lieferadresse', $_SESSION['Lieferadresse']);

    executeHook(HOOK_BESTELLVORGANG_PAGE_STEPVERSAND);
}

/**
 * @param array $cPost_arr
 * @return array|int
 */
function plausiKupon($cPost_arr)
{
    $nKuponfehler_arr = array();
    //kupons
    if (isset($cPost_arr['Kuponcode']) && (!isset($_SESSION['Kupon']->cKuponTyp) || $_SESSION['Kupon']->cKuponTyp !== 'standard') &&
        !$_SESSION['Warenkorb']->posTypEnthalten(C_WARENKORBPOS_TYP_KUPON) && (isset($_SESSION['Bestellung']->lieferadresseGleich) || $_SESSION['Lieferadresse'])
    ) {
        $Kupon = Shop::DB()->select('tkupon', 'cCode', $cPost_arr['Kuponcode']);
        if (isset($Kupon->kKupon) && $Kupon->kKupon > 0) {
            $nKuponfehler_arr = checkeKupon($Kupon);
            if (angabenKorrekt($nKuponfehler_arr)) {
                kuponAnnehmen($Kupon);
                if ($Kupon->cKuponTyp === 'versandkupon') { // Versandfrei Kupon
                    $_SESSION['oVersandfreiKupon'] = $Kupon;
                }
            } else {
                Shop::Smarty()->assign('cKuponfehler_arr', $nKuponfehler_arr);
            }
        } else {
            $nKuponfehler_arr['ungueltig'] = 11;
        }
    }
    plausiNeukundenKupon();
    if (count($nKuponfehler_arr) > 0) {
        return $nKuponfehler_arr;
    }

    return 0;
}

/**
 *
 */
function plausiNeukundenKupon()
{
    if (isset($_SESSION['NeukundenKuponAngenommen']) && $_SESSION['NeukundenKuponAngenommen'] === true) {
        return;
    }
    if (!isset($_SESSION['Kupon']->cKuponTyp) || $_SESSION['Kupon']->cKuponTyp !== 'standard') {
        // Registrierte Kunden
        if ($_SESSION['Kunde']->kKunde > 0) {
            $oBestellung = Shop::DB()->query(
                "SELECT tbestellung.kBestellung
                    FROM tkunde
                    JOIN tbestellung ON tbestellung.kKunde = tkunde.kKunde
                    WHERE tkunde.cMail = '" . StringHandler::filterXSS($_SESSION['Kunde']->cMail) . "'
                        OR tkunde.kKunde = " . (int)$_SESSION['Kunde']->kKunde . "
                    LIMIT 1", 1
            );
            if ($oBestellung === false || !empty($oBestellung->kBestellung)) {
                $NeukundenKupons = Shop::DB()->query("SELECT * FROM tkupon WHERE cKuponTyp = 'neukundenkupon' AND cAktiv = 'Y' ORDER BY fWert DESC", 2);
                foreach ($NeukundenKupons as $NeukundenKupon) {
                    if (angabenKorrekt(checkeKupon($NeukundenKupon))) {
                        kuponAnnehmen($NeukundenKupon);
                        $hash = Kuponneukunde::Hash(
                            null,
                            trim($_SESSION['Kunde']->cNachname),
                            trim($_SESSION['Kunde']->cStrasse),
                            null,
                            trim($_SESSION['Kunde']->cPLZ),
                            trim($_SESSION['Kunde']->cOrt),
                            trim($_SESSION['Kunde']->cLand)
                        );
                        $Options = array(
                            'Kupon'     => $NeukundenKupon->kKupon,
                            'Email'     => $_SESSION['Kunde']->cMail,
                            'DatenHash' => $hash,
                            'Erstellt'  => 'now()'
                        );
                        $Kuponneukunde = new Kuponneukunde();
                        $Kuponneukunde->setOptions($Options);
                        $Kuponneukunde->Save();

                        break;
                    }
                }
            }
        } else {
            $conf = Shop::getSettings(array(CONF_KAUFABWICKLUNG));
            if ($conf['kaufabwicklung']['bestellvorgang_unregneukundenkupon_zulassen'] === 'N') {
                return;
            }
            $hash = Kuponneukunde::Hash(
                null,
                trim($_SESSION['Kunde']->cNachname),
                trim($_SESSION['Kunde']->cStrasse),
                null,
                trim($_SESSION['Kunde']->cPLZ),
                trim($_SESSION['Kunde']->cOrt),
                trim($_SESSION['Kunde']->cLand)
            );
            $neukunde = Kuponneukunde::load($_SESSION['Kunde']->cMail, $hash);
            if ($neukunde !== null) {
                return;
            }
            $NeukundenKupons = Shop::DB()->query(
                "SELECT *
                     FROM tkupon
                     WHERE cKuponTyp = 'neukundenkupon'
                         AND cAktiv = 'Y'", 2
            );
            foreach ($NeukundenKupons as $NeukundenKupon) {
                if (angabenKorrekt(checkeKupon($NeukundenKupon))) {
                    kuponAnnehmen($NeukundenKupon);

                    $Options = array(
                        'Kupon'     => $NeukundenKupon->kKupon,
                        'Email'     => $_SESSION['Kunde']->cMail,
                        'DatenHash' => $hash,
                        'Erstellt'  => 'now()'
                    );
                    $Kuponneukunde = new Kuponneukunde();
                    $Kuponneukunde->setOptions($Options);
                    $Kuponneukunde->Save();
                    break;
                }
            }
        }
    }
}

/**
 * @param Zahlungsart $paymentMethod
 * @return array
 */
function checkAdditionalPayment($paymentMethod)
{
    $conf   = Shop::getSettings(array(CONF_ZAHLUNGSARTEN));
    $errors = array();
    switch ($paymentMethod->cModulId) {
        case 'za_kreditkarte_jtl':
            if (!isset($_POST['kreditkartennr']) || !$_POST['kreditkartennr']) {
                $errors['kreditkartennr'] = 1;
            }
            if (!isset($_POST['gueltigkeit']) || !$_POST['gueltigkeit']) {
                $errors['gueltigkeit'] = 1;
            }
            if (!isset($_POST['cvv']) || !$_POST['cvv']) {
                $errors['cvv'] = 1;
            }
            if (!isset($_POST['kartentyp']) || !$_POST['kartentyp']) {
                $errors['kartentyp'] = 1;
            }
            if (!isset($_POST['inhaber']) || !$_POST['inhaber']) {
                $errors['inhaber'] = 1;
            }
            break;

        case 'za_lastschrift_jtl':
            if (!isset($_POST['bankname']) || !$_POST['bankname'] || trim($_POST['bankname']) === '') {
                $errors['bankname'] = 1;
            }
            if ($conf['zahlungsarten']['zahlungsart_lastschrift_kontoinhaber_abfrage'] === 'Y' && (!isset($_POST['inhaber']) ||
                    !$_POST['inhaber'] || trim($_POST['inhaber']) === '')
            ) {
                $errors['inhaber'] = 1;
            }
            if (((isset($_POST['blz']) && $_POST['blz'] && $conf['zahlungsarten']['zahlungsart_lastschrift_kontonummer_abfrage'] !== 'N') ||
                    $conf['zahlungsarten']['zahlungsart_lastschrift_kontonummer_abfrage'] === 'Y')
                && (!isset($_POST['kontonr']) || !$_POST['kontonr'] || isset($_POST['kontonr']) && trim($_POST['kontonr']) == '')
            ) {
                $errors['kontonr'] = 1;
            }
            if (((isset($_POST['kontonr']) && $_POST['kontonr'] && $conf['zahlungsarten']['zahlungsart_lastschrift_blz_abfrage'] !== 'N') ||
                    $conf['zahlungsarten']['zahlungsart_lastschrift_blz_abfrage'] === 'Y')
                && (!isset($_POST['blz']) || !$_POST['blz'] || (isset($_POST['blz']) && trim($_POST['blz']) === ''))
            ) {
                $errors['blz'] = 1;
            }
            if ($conf['zahlungsarten']['zahlungsart_lastschrift_bic_abfrage'] === 'Y' && (!isset($_POST['bic']) || !$_POST['bic'])) {
                $errors['bic'] = 1;
            }
            if (((isset($_POST['bic']) && $conf['zahlungsarten']['zahlungsart_lastschrift_iban_abfrage'] !== 'N') ||
                    $conf['zahlungsarten']['zahlungsart_lastschrift_iban_abfrage'] === 'Y') && !$_POST['iban']
            ) {
                $errors['iban'] = 1;
            }
            if (((isset($_POST['bic']) && $conf['zahlungsarten']['zahlungsart_lastschrift_iban_abfrage'] !== 'N') ||
                    $conf['zahlungsarten']['zahlungsart_lastschrift_iban_abfrage'] === 'Y') && !plausiIban($_POST['iban'])
            ) {
                $errors['iban'] = 2;
            }
            if (!isset($_POST['kontonr']) && !isset($_POST['blz']) && !isset($_POST['iban']) && !isset($_POST['bic'])) {
                $errors['kontonr'] = 2;
                $errors['blz']     = 2;
                $errors['bic']     = 2;
                $errors['iban']    = 2;
            }
            break;
    }

    return $errors;
}

/**
 * @param string $iban
 * @return bool|mixed
 */
function plausiIban($iban)
{
    if ($iban === '') {
        return false;
    }
    $iban  = str_replace(' ', '', $iban);
    $iban1 = substr($iban, 4)
        . strval(ord($iban{0}) - 55)
        . strval(ord($iban{1}) - 55)
        . substr($iban, 2, 2);

    for ($i = 0; $i < strlen($iban1); $i++) {
        if (ord($iban1{$i}) > 64 && ord($iban1{$i}) < 91) {
            $iban1 = substr($iban1, 0, $i) . strval(ord($iban1{$i}) - 55) . substr($iban1, $i + 1);
        }
    }

    $rest = 0;
    for ($pos = 0; $pos < strlen($iban1); $pos += 7) {
        $part = strval($rest) . substr($iban1, $pos, 7);
        $rest = intval($part) % 97;
    }

    $pz = sprintf("%02d", 98 - $rest);

    if (substr($iban, 2, 2) == '00') {
        return substr_replace($iban, $pz, 2, 2);
    }

    return ($rest == 1) ? true : false;
}

/**
 * @return stdClass
 */
function gibPostZahlungsInfo()
{
    $oZahlungsInfo = new stdClass();

    $oZahlungsInfo->cKartenNr    = isset($_POST['kreditkartennr']) ? StringHandler::htmlentities(stripslashes($_POST['kreditkartennr']), ENT_QUOTES) : null;
    $oZahlungsInfo->cGueltigkeit = isset($_POST['gueltigkeit']) ? StringHandler::htmlentities(stripslashes($_POST['gueltigkeit']), ENT_QUOTES) : null;
    $oZahlungsInfo->cCVV         = isset($_POST['cvv']) ? StringHandler::htmlentities(stripslashes($_POST['cvv']), ENT_QUOTES) : null;
    $oZahlungsInfo->cKartenTyp   = isset($_POST['kartentyp']) ? StringHandler::htmlentities(stripslashes($_POST['kartentyp']), ENT_QUOTES) : null;
    $oZahlungsInfo->cBankName    = isset($_POST['bankname']) ? StringHandler::htmlentities(stripslashes(trim($_POST['bankname'])), ENT_QUOTES) : null;
    $oZahlungsInfo->cKontoNr     = isset($_POST['kontonr']) ? StringHandler::htmlentities(stripslashes(trim($_POST['kontonr'])), ENT_QUOTES) : null;
    $oZahlungsInfo->cBLZ         = isset($_POST['blz']) ? StringHandler::htmlentities(stripslashes(trim($_POST['blz'])), ENT_QUOTES) : null;
    $oZahlungsInfo->cIBAN        = isset($_POST['iban']) ? StringHandler::htmlentities(stripslashes(trim($_POST['iban'])), ENT_QUOTES) : null;
    $oZahlungsInfo->cBIC         = isset($_POST['bic']) ? StringHandler::htmlentities(stripslashes(trim($_POST['bic'])), ENT_QUOTES) : null;
    $oZahlungsInfo->cInhaber     = isset($_POST['inhaber']) ? StringHandler::htmlentities(stripslashes(trim($_POST['inhaber'])), ENT_QUOTES) : null;

    return $oZahlungsInfo;
}

/**
 * @param int $kZahlungsart
 * @return int
 */
function zahlungsartKorrekt($kZahlungsart)
{
    $kZahlungsart = (int)$kZahlungsart;
    unset($_SESSION['Zahlungsart']);
    $_SESSION['Warenkorb']->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
                          ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
                          ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR)
                          ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR);
    if ($kZahlungsart > 0 && isset($_SESSION['Versandart']->kVersandart) && intval($_SESSION['Versandart']->kVersandart) > 0) {
        $Zahlungsart = Shop::DB()->query(
            "SELECT tversandartzahlungsart.*, tzahlungsart.*
                FROM tversandartzahlungsart, tzahlungsart
                WHERE tversandartzahlungsart.kVersandart = " . (int)$_SESSION['Versandart']->kVersandart . "
                    AND tversandartzahlungsart.kZahlungsart = tzahlungsart.kZahlungsart
                    AND tversandartzahlungsart.kZahlungsart = " . $kZahlungsart, 1
        );
        if (isset($Zahlungsart->cModulId) && strlen($Zahlungsart->cModulId) > 0) {
            $einstellungen = Shop::DB()->query("
                SELECT *
                    FROM teinstellungen
                    WHERE kEinstellungenSektion = " . CONF_ZAHLUNGSARTEN . "
                        AND cModulId = '" . $Zahlungsart->cModulId . "'", 2
            );
            foreach ($einstellungen as $einstellung) {
                $Zahlungsart->einstellungen[$einstellung->cName] = $einstellung->cWert;
            }
        }
        //Einstellungen beachten
        if (!zahlungsartGueltig($Zahlungsart)) {
            return 0;
        }
        // Hinweistext
        $oObj                      = Shop::DB()->select('tzahlungsartsprache', 'kZahlungsart', (int)$Zahlungsart->kZahlungsart, 'cISOSprache', $_SESSION['cISOSprache'], null, null, false, 'cHinweisText');
        $Zahlungsart->cHinweisText = '';
        if (isset($oObj->cHinweisText)) {
            $Zahlungsart->cHinweisText = $oObj->cHinweisText;
        }
        if (isset($_SESSION['VersandKupon']->cZusatzgebuehren) && $_SESSION['VersandKupon']->cZusatzgebuehren === 'Y' && $Zahlungsart->fAufpreis > 0) {
            if ($Zahlungsart->cName === 'Nachnahme') {
                $Zahlungsart->fAufpreis = 0;
            }
        }

        if ($Zahlungsart->fAufpreis != 0) {
            //lokalisieren
            $Zahlungsart->cPreisLocalized = gibPreisStringLocalized($Zahlungsart->fAufpreis);
            $Aufpreis                     = $Zahlungsart->fAufpreis;
            if ($Zahlungsart->cAufpreisTyp === 'prozent') {
                $Zahlungsart->cPreisLocalized = gibPreisStringLocalized(($_SESSION['Warenkorb']->gibGesamtsummeWarenExt(array('1'), true) * $Zahlungsart->fAufpreis) / 100.0);
                $Aufpreis                     = ($_SESSION['Warenkorb']->gibGesamtsummeWarenExt(array('1'), true) * $Zahlungsart->fAufpreis) / 100.0;
            }
            //posname lokalisiert ablegen
            if (!isset($Spezialpos)) {
                $Spezialpos = new stdClass();
            }
            $Spezialpos->cGebuehrname = array();
            foreach ($_SESSION['Sprachen'] as $Sprache) {
                if ($Zahlungsart->kZahlungsart > 0) {
                    $name_spr = Shop::DB()->select('tzahlungsartsprache', 'kZahlungsart', intval($Zahlungsart->kZahlungsart), 'cISOSprache', $Sprache->cISO, null, null, false, 'cGebuehrname');
                    if (isset($name_spr->cGebuehrname)) {
                        $Spezialpos->cGebuehrname[$Sprache->cISO] = $name_spr->cGebuehrname;
                    }
                    if ($Zahlungsart->cAufpreisTyp === 'prozent') {
                        if ($Zahlungsart->fAufpreis > 0) {
                            $Spezialpos->cGebuehrname[$Sprache->cISO] .= ' +';
                        }
                        $Spezialpos->cGebuehrname[$Sprache->cISO] .= $Zahlungsart->fAufpreis . '%';
                    }
                }
            }
            if ($Zahlungsart->cModulId === 'za_nachnahme_jtl') {
                $_SESSION['Warenkorb']->erstelleSpezialPos(
                    $Spezialpos->cGebuehrname,
                    1,
                    $Aufpreis,
                    $_SESSION['Warenkorb']->gibVersandkostenSteuerklasse($_SESSION['Lieferadresse']->cLand),
                    C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR,
                    true,
                    true,
                    $Zahlungsart->cHinweisText
                );
            } else {
                $_SESSION['Warenkorb']->erstelleSpezialPos(
                    $Spezialpos->cGebuehrname,
                    1,
                    $Aufpreis,
                    $_SESSION['Warenkorb']->gibVersandkostenSteuerklasse($_SESSION['Lieferadresse']->cLand),
                    C_WARENKORBPOS_TYP_ZAHLUNGSART,
                    true,
                    true,
                    $Zahlungsart->cHinweisText
                );
            }
        }
        //posname lokalisiert ablegen
        if (!isset($Spezialpos)) {
            $Spezialpos = new stdClass();
        }
        $Spezialpos->cName = array();
        foreach ($_SESSION['Sprachen'] as $Sprache) {
            if ($Zahlungsart->kZahlungsart > 0) {
                $name_spr = Shop::DB()->select('tzahlungsartsprache', 'kZahlungsart', (int)$Zahlungsart->kZahlungsart, 'cISOSprache', $Sprache->cISO, null, null, false, 'cName');
                if (isset($name_spr->cName)) {
                    $Spezialpos->cName[$Sprache->cISO] = $name_spr->cName;
                }
            }
        }
        $Zahlungsart->angezeigterName = $Spezialpos->cName;
        $_SESSION['Zahlungsart']      = $Zahlungsart;
        if ($Zahlungsart->cZusatzschrittTemplate) {
            $ZahlungsInfo    = new stdClass();
            $zusatzangabenDa = false;
            switch ($Zahlungsart->cModulId) {
                case 'za_kreditkarte_jtl':
                    if (isset($_POST['kreditkartennr']) &&
                        $_POST['kreditkartennr'] &&
                        $_POST['gueltigkeit'] &&
                        $_POST['cvv'] &&
                        $_POST['kartentyp'] &&
                        $_POST['inhaber']
                    ) {
                        $ZahlungsInfo->cKartenNr    = StringHandler::htmlentities(stripslashes($_POST['kreditkartennr']), ENT_QUOTES);
                        $ZahlungsInfo->cGueltigkeit = StringHandler::htmlentities(stripslashes($_POST['gueltigkeit']), ENT_QUOTES);
                        $ZahlungsInfo->cCVV         = StringHandler::htmlentities(stripslashes($_POST['cvv']), ENT_QUOTES);
                        $ZahlungsInfo->cKartenTyp   = StringHandler::htmlentities(stripslashes($_POST['kartentyp']), ENT_QUOTES);
                        $ZahlungsInfo->cInhaber     = StringHandler::htmlentities(stripslashes($_POST['inhaber']), ENT_QUOTES);
                        $zusatzangabenDa            = true;
                    }
                    break;
                case 'za_lastschrift_jtl':
                    $fehlendeAngaben = checkAdditionalPayment($Zahlungsart);

                    if (count($fehlendeAngaben) === 0) {
                        $ZahlungsInfo->cBankName = StringHandler::htmlentities(stripslashes($_POST['bankname']), ENT_QUOTES);
                        $ZahlungsInfo->cKontoNr  = StringHandler::htmlentities(stripslashes($_POST['kontonr']), ENT_QUOTES);
                        $ZahlungsInfo->cBLZ      = StringHandler::htmlentities(stripslashes($_POST['blz']), ENT_QUOTES);
                        $ZahlungsInfo->cIBAN     = StringHandler::htmlentities(stripslashes($_POST['iban']), ENT_QUOTES);
                        $ZahlungsInfo->cBIC      = StringHandler::htmlentities(stripslashes($_POST['bic']), ENT_QUOTES);
                        $ZahlungsInfo->cInhaber  = StringHandler::htmlentities(stripslashes($_POST['inhaber']), ENT_QUOTES);
                        $zusatzangabenDa         = true;
                    }
                    break;
                case 'za_billpay_jtl':
                case 'za_billpay_invoice_jtl':
                case 'za_billpay_direct_debit_jtl':
                case 'za_billpay_rate_payment_jtl':
                case 'za_billpay_paylater_jtl':
                // workaround, fallback wawi <= v1.072
                if ($Zahlungsart->cModulId === 'za_billpay_jtl') {
                    $Zahlungsart->cModulId = 'za_billpay_invoice_jtl';
                }
                    $paymentMethod = PaymentMethod::create($Zahlungsart->cModulId);
                    if ($paymentMethod->handleAdditional($_POST)) {
                        $zusatzangabenDa = true;
                    }
                    break;
                default:
                    // Plugin-Zusatzschritt
                    $zusatzangabenDa = true;
                    $paymentMethod   = PaymentMethod::create($Zahlungsart->cModulId);
                    if ($paymentMethod) {
                        if (!$paymentMethod->handleAdditional($_POST)) {
                            $zusatzangabenDa = false;
                        }
                    }
                    break;
            }
            if (!$zusatzangabenDa) {
                return 1;
            }
            $Zahlungsart->ZahlungsInfo = $ZahlungsInfo;
        }
        // billpay
        if (substr($Zahlungsart->cModulId, 0, 10) === 'za_billpay') {
            if (isset($paymentMethod) && $paymentMethod) {
                return $paymentMethod->preauthRequest() ? 2 : 1;
            }
        }

        return 2;
    }

    return 0;
}

/**
 * @param string $cModulId
 * @return bool|Plugin
 */
function gibPluginZahlungsart($cModulId)
{
    $kPlugin = gibkPluginAuscModulId($cModulId);
    if ($kPlugin > 0) {
        $oPlugin = new Plugin($kPlugin);
        if ($oPlugin->kPlugin > 0) {
            return $oPlugin;
        }
    }

    return false;
}

/**
 * @param int $kZahlungsart
 * @return mixed
 */
function gibZahlungsart($kZahlungsart)
{
    $kZahlungsart = (int)$kZahlungsart;
    $Zahlungsart  = Shop::DB()->select('tzahlungsart', 'kZahlungsart', $kZahlungsart);
    foreach ($_SESSION['Sprachen'] as $Sprache) {
        $name_spr                                     = Shop::DB()->select('tzahlungsartsprache', 'kZahlungsart', $kZahlungsart, 'cISOSprache', $Sprache->cISO, null, null, false, 'cName');
        $Zahlungsart->angezeigterName[$Sprache->cISO] = (isset($name_spr->cName)) ? $name_spr->cName : null;
    }
    $einstellungen = Shop::DB()->query("
        SELECT *
            FROM teinstellungen
            WHERE kEinstellungenSektion = " . CONF_ZAHLUNGSARTEN . "
                AND cModulId = '" . $Zahlungsart->cModulId . "'", 2
    );
    foreach ($einstellungen as $einstellung) {
        $Zahlungsart->einstellungen[$einstellung->cName] = $einstellung->cWert;
    }
    $oPlugin = gibPluginZahlungsart($Zahlungsart->cModulId);
    if ($oPlugin) {
        $Zahlungsart->cZusatzschrittTemplate = $oPlugin->oPluginZahlungsmethodeAssoc_arr[$Zahlungsart->cModulId]->cZusatzschrittTemplate;
    }

    return $Zahlungsart;
}

/**
 * @param int $kKunde
 * @return bool
 */
function gibKundenKontodaten($kKunde)
{
    if ($kKunde > 0) {
        $oKundenKontodaten = Shop::DB()->select('tkundenkontodaten', 'kKunde', (int)$kKunde);

        if (isset($oKundenKontodaten->kKunde) && $oKundenKontodaten->kKunde > 0) {
            if (strlen($oKundenKontodaten->cBLZ) > 0) {
                $oKundenKontodaten->cBLZ = intval(entschluesselXTEA($oKundenKontodaten->cBLZ));
            }
            if (strlen($oKundenKontodaten->cInhaber) > 0) {
                $oKundenKontodaten->cInhaber = trim(entschluesselXTEA($oKundenKontodaten->cInhaber));
            }
            if (strlen($oKundenKontodaten->cBankName) > 0) {
                $oKundenKontodaten->cBankName = trim(entschluesselXTEA($oKundenKontodaten->cBankName));
            }
            if (strlen($oKundenKontodaten->nKonto) > 0) {
                $oKundenKontodaten->nKonto = trim(entschluesselXTEA($oKundenKontodaten->nKonto));
            }
            if (strlen($oKundenKontodaten->cIBAN) > 0) {
                $oKundenKontodaten->cIBAN = trim(entschluesselXTEA($oKundenKontodaten->cIBAN));
            }
            if (strlen($oKundenKontodaten->cBIC) > 0) {
                $oKundenKontodaten->cBIC = trim(entschluesselXTEA($oKundenKontodaten->cBIC));
            }

            return $oKundenKontodaten;
        }
    }

    return false;
}

/**
 * @param int $kVersandart
 * @param int $kKundengruppe
 * @return array
 */
function gibZahlungsarten($kVersandart, $kKundengruppe)
{
    $kVersandart   = (int)$kVersandart;
    $kKundengruppe = (int)$kKundengruppe;
    $fSteuersatz   = 0.0;
    $Zahlungsarten = array();
    if ($kVersandart > 0) {
        $Zahlungsarten = Shop::DB()->query(
            "SELECT tversandartzahlungsart.*, tzahlungsart.*
                FROM tversandartzahlungsart, tzahlungsart
                WHERE tversandartzahlungsart.kVersandart = {$kVersandart}
                    AND tversandartzahlungsart.kZahlungsart=tzahlungsart.kZahlungsart
                    AND (tzahlungsart.cKundengruppen IS NULL OR tzahlungsart.cKundengruppen=''
                    OR tzahlungsart.cKundengruppen LIKE '%;{$kKundengruppe};%')
                    AND tzahlungsart.nActive = 1
                    AND tzahlungsart.nNutzbar = 1
                ORDER BY tzahlungsart.nSort", 2
        );
    }

    $gueltigeZahlungsarten = array();
    $zaCount               = count($Zahlungsarten);
    for ($i = 0; $i < $zaCount; $i++) {
        if (!$Zahlungsarten[$i]->kZahlungsart) {
            continue;
        }
        //posname lokalisiert ablegen
        $Zahlungsarten[$i]->angezeigterName = array();
        $Zahlungsarten[$i]->cGebuehrname    = array();
        foreach ($_SESSION['Sprachen'] as $Sprache) {
            $name_spr = Shop::DB()->select('tzahlungsartsprache', 'kZahlungsart', (int)$Zahlungsarten[$i]->kZahlungsart, 'cISOSprache', $Sprache->cISO, null, null, false, 'cName, cGebuehrname, cHinweisText');
            if (isset($name_spr->cName)) {
                $Zahlungsarten[$i]->angezeigterName[$Sprache->cISO] = $name_spr->cName;
                $Zahlungsarten[$i]->cGebuehrname[$Sprache->cISO]    = $name_spr->cGebuehrname;
                $Zahlungsarten[$i]->cHinweisText[$Sprache->cISO]    = $name_spr->cHinweisText;
            }
        }
        $einstellungen = Shop::DB()->query(
            "SELECT *
                FROM teinstellungen
                WHERE kEinstellungenSektion = " . CONF_ZAHLUNGSARTEN . "
                AND cModulId = '" . $Zahlungsarten[$i]->cModulId . "'", 2
        );
        foreach ($einstellungen as $einstellung) {
            $Zahlungsarten[$i]->einstellungen[$einstellung->cName] = $einstellung->cWert;
        }
        //Einstellungen beachten
        if (!zahlungsartGueltig($Zahlungsarten[$i])) {
            continue;
        }
        $Zahlungsarten[$i]->Specials = gibSpecials($Zahlungsarten[$i]);
        //evtl. Versandkupon anwenden / Nur Nachname fällt weg
        if (isset($_SESSION['VersandKupon']->cZusatzgebuehren) && $_SESSION['VersandKupon']->cZusatzgebuehren === 'Y' && $Zahlungsarten[$i]->fAufpreis > 0) {
            if ($Zahlungsarten[$i]->cName === 'Nachnahme') {
                $Zahlungsarten[$i]->fAufpreis = 0;
            }
        }
        //lokalisieren
        if ($Zahlungsarten[$i]->cAufpreisTyp === 'festpreis') {
            $Zahlungsarten[$i]->fAufpreis = $Zahlungsarten[$i]->fAufpreis * ((100 + $fSteuersatz) / 100);
        }
        $Zahlungsarten[$i]->cPreisLocalized = gibPreisStringLocalized($Zahlungsarten[$i]->fAufpreis);
        if ($Zahlungsarten[$i]->cAufpreisTyp === 'prozent') {
            $Zahlungsarten[$i]->cPreisLocalized = '';
            if ($Zahlungsarten[$i]->fAufpreis < 0) {
                $Zahlungsarten[$i]->cPreisLocalized = ' ';
            } else {
                $Zahlungsarten[$i]->cPreisLocalized = '+ ';
            }
            $Zahlungsarten[$i]->cPreisLocalized .= $Zahlungsarten[$i]->fAufpreis . '%';
        }
        if ($Zahlungsarten[$i]->fAufpreis == 0) {
            $Zahlungsarten[$i]->cPreisLocalized = '';
        }
        $gueltigeZahlungsarten[] = $Zahlungsarten[$i];
    }

    return $gueltigeZahlungsarten;
}

/**
 * @param Zahlungsart $Zahlungsart
 * @return bool
 */
function zahlungsartGueltig($Zahlungsart)
{
    if (!isset($Zahlungsart->cModulId)) {
        return false;
    }
    // Interne Zahlungsartpruefung ob wichtige Parameter gesetzt sind
    require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php';
    $kPlugin = gibkPluginAuscModulId($Zahlungsart->cModulId);
    if ($kPlugin > 0) {
        if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
            Jtllog::writeLog(utf8_decode('Die Zahlungsart "' . $Zahlungsart->cName . '" ist ein Plugin und wird auf Gültigkeit geprüft!'),
                JTLLOG_LEVEL_DEBUG, false, 'cModulId', $Zahlungsart->cModulId);
        }

        $oPlugin = new Plugin($kPlugin);
        if ($oPlugin->kPlugin > 0) {
            // Plugin muss aktiv sein
            if ($oPlugin->nStatus != 2) {
                return false;
            }
            require_once PFAD_ROOT . PFAD_PLUGIN . $oPlugin->cVerzeichnis . '/' . PFAD_PLUGIN_VERSION . $oPlugin->nVersion . '/' .
                PFAD_PLUGIN_PAYMENTMETHOD . $oPlugin->oPluginZahlungsKlasseAssoc_arr[$Zahlungsart->cModulId]->cClassPfad;
            $className              = $oPlugin->oPluginZahlungsKlasseAssoc_arr[$Zahlungsart->cModulId]->cClassName;
            $oZahlungsart           = new $className($Zahlungsart->cModulId);
            $oZahlungsart->cModulId = $Zahlungsart->cModulId;

            if ($oZahlungsart && $oZahlungsart->isSelectable() === false) {
                return false;
            }
            if ($oZahlungsart && !$oZahlungsart->isValidIntern()) {
                Jtllog::writeLog(utf8_decode('Die Zahlungsartprüfung (' . $Zahlungsart->cModulId . ') wurde nicht erfolgreich validiert (isValidIntern).'),
                    JTLLOG_LEVEL_DEBUG,
                    false,
                    'cModulId',
                    $Zahlungsart->cModulId
                );

                return false;
            }
            // Lizenzprüfung
            if (!pluginLizenzpruefung($oPlugin, array('cModulId' => $Zahlungsart->cModulId))) {
                return false;
            }
        }
    } else {
        $oPaymentMethod = new PaymentMethod($Zahlungsart->cModulId);
        $oZahlungsart   = $oPaymentMethod->create($Zahlungsart->cModulId);

        if ($oZahlungsart && $oZahlungsart->isSelectable() === false) {
            return false;
        }
        if ($oZahlungsart && !$oZahlungsart->isValidIntern()) {
            Jtllog::writeLog('Die Zahlungsartprüfung (' . $Zahlungsart->cModulId . ') wurde nicht erfolgreich validiert (isValidIntern).',
                JTLLOG_LEVEL_DEBUG,
                false,
                'cModulId',
                $Zahlungsart->cModulId
            );

            return false;
        }
    }

    switch ($Zahlungsart->cModulId) {
        case 'za_ueberweisung_jtl':
            if (!pruefeZahlungsartMinBestellungen(@$Zahlungsart->einstellungen['zahlungsart_ueberweisung_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert(@$Zahlungsart->einstellungen['zahlungsart_ueberweisung_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert(@$Zahlungsart->einstellungen['zahlungsart_ueberweisung_max'])) {
                return false;
            }
            break;
        case 'za_nachnahme_jtl':
            if (!pruefeZahlungsartMinBestellungen(@$Zahlungsart->einstellungen['zahlungsart_nachnahme_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert(@$Zahlungsart->einstellungen['zahlungsart_nachnahme_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert(@$Zahlungsart->einstellungen['zahlungsart_nachnahme_max'])) {
                return false;
            }
            break;
        case 'za_kreditkarte_jtl':
            if (!pruefeZahlungsartMinBestellungen(@$Zahlungsart->einstellungen['zahlungsart_kreditkarte_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert(@$Zahlungsart->einstellungen['zahlungsart_kreditkarte_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert(@$Zahlungsart->einstellungen['zahlungsart_kreditkarte_max'])) {
                return false;
            }
            break;
        case 'za_rechnung_jtl':
            if (!pruefeZahlungsartMinBestellungen(@$Zahlungsart->einstellungen['zahlungsart_rechnung_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert(@$Zahlungsart->einstellungen['zahlungsart_rechnung_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert(@$Zahlungsart->einstellungen['zahlungsart_rechnung_max'])) {
                return false;
            }
            break;
        case 'za_lastschrift_jtl':
            if (!pruefeZahlungsartMinBestellungen(@$Zahlungsart->einstellungen['zahlungsart_lastschrift_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert(@$Zahlungsart->einstellungen['zahlungsart_lastschrift_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert(@$Zahlungsart->einstellungen['zahlungsart_lastschrift_max'])) {
                return false;
            }
            break;
        case 'za_barzahlung_jtl':
            if (!pruefeZahlungsartMinBestellungen(@$Zahlungsart->einstellungen['zahlungsart_barzahlung_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert(@$Zahlungsart->einstellungen['zahlungsart_barzahlung_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert(@$Zahlungsart->einstellungen['zahlungsart_barzahlung_max'])) {
                return false;
            }
            break;
        case 'za_paypal_jtl':
            if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_paypal_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_paypal_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_paypal_max'])) {
                return false;
            }
            break;
        case 'za_moneybookers_jtl':
            if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_moneybookers_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_moneybookers_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_moneybookers_max'])) {
                return false;
            }
            break;
        case 'za_worldpay_jtl':
            if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_worldpay_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_worldpay_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_worldpay_max'])) {
                return false;
            }
            break;
        case 'za_ipayment_jtl':
            if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_ipayment_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_ipayment_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_ipayment_max'])) {
                return false;
            }
            break;
        case 'za_sofortueberweisung_jtl':
            if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_sofortueberweisung_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_sofortueberweisung_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_sofortueberweisung_max'])) {
                return false;
            }
            break;
        case 'za_clickpay_cc_jtl':
            if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_clickpay_cc_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_clickpay_cc_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_clickpay_cc_max'])) {
                return false;
            }
            break;
        case 'za_clickpay_dd_jtl':
            if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_clickpay_dd_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_clickpay_dd_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_clickpay_dd_max'])) {
                return false;
            }
            break;
        case 'za_eos_cc_jtl':
            if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_eos_cc_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_eos_cc_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_eos_cc_max'])) {
                return false;
            }
            break;
        case 'za_eos_dd_jtl':
            if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_eos_dd_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_eos_dd_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_eos_dd_max'])) {
                return false;
            }
            break;
        case 'za_eos_direkt_jtl':
            if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_eos_direkt_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_eos_direkt_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_eos_direkt_max'])) {
                return false;
            }
            break;
        case 'za_eos_ewallet_jtl':
            if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_eos_ewallet_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_eos_ewallet_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_eos_ewallet_max'])) {
                return false;
            }
            break;
        case 'za_heidelpay_jtl':
            if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_heidelpay_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_heidelpay_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_heidelpay_max'])) {
                return false;
            }
            break;
        case 'za_postfinance_jtl':
            if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_postfinance_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_postfinance_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_postfinance_max'])) {
                return false;
            }
            break;
        case 'za_safetypay':
            if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_safetypay_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_safetypay_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_safetypay_max'])) {
                return false;
            }
            break;
        case 'za_ut_cc_jtl':
            if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_ut_cc_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_ut_cc_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_ut_cc_max'])) {
                return false;
            }
            break;
        case 'za_ut_dd_jtl':
            if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_ut_dd_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_ut_dd_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_ut_dd_max'])) {
                return false;
            }
            break;
        case 'za_ut_ebank_jtl':
            if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_ut_ebank_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_ut_ebank_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_ut_ebank_max'])) {
                return false;
            }
            break;
        case 'za_ut_gi_jtl':
            if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_ut_gi_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_ut_gi_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_ut_gi_max'])) {
                return false;
            }
            break;
        case 'za_ut_prepaid_jtl':
            if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_ut_prepaid_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_ut_prepaid_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_ut_prepaid_max'])) {
                return false;
            }
            break;
        case 'za_ut_stand_jtl':
            if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_ut_stand_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_ut_stand_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_ut_stand_max'])) {
                return false;
            }
            break;
        case 'za_wirecard_jtl':
            if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_wirecard_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_wirecard_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_wirecard_max'])) {
                return false;
            }
            break;
        case 'za_mbqc_acc_jtl':
            if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_mbqc_acc_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_mbqc_acc_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_mbqc_acc_max'])) {
                return false;
            }
            break;
        case 'za_mbqc_csi_jtl':
            if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_mbqc_csi_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_mbqc_csi_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_mbqc_csi_max'])) {
                return false;
            }
            break;
        case 'za_mbqc_did_jtl':
            if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_mbqc_did_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_mbqc_did_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_mbqc_did_max'])) {
                return false;
            }
            break;
        case 'za_mbqc_dnk_jtl':
            if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_mbqc_dnk_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_mbqc_dnk_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_mbqc_dnk_max'])) {
                return false;
            }
            break;
        case 'za_mbqc_ebt_jtl':
            if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_mbqc_ebt_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_mbqc_ebt_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_mbqc_ebt_max'])) {
                return false;
            }
            break;
        case 'za_mbqc_ent_jtl':
            if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_mbqc_ent_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_mbqc_ent_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_mbqc_ent_max'])) {
                return false;
            }
            break;
        case 'za_mbqc_gcb_jtl':
            if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_mbqc_gcb_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_mbqc_gcb_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_mbqc_gcb_max'])) {
                return false;
            }
            break;
        case 'za_mbqc_git_jtl':
            if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_mbqc_git_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_mbqc_git_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_mbqc_git_max'])) {
                return false;
            }
            break;
        case 'za_mbqc_obt_jtl':
            if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_mbqc_obt_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_mbqc_obt_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_mbqc_obt_max'])) {
                return false;
            }
            break;
        case 'za_mbqc_idl_jtl':
            if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_mbqc_idl_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_mbqc_idl_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_mbqc_idl_max'])) {
                return false;
            }
            break;
        case 'za_mbqc_lsr_jtl':
            if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_mbqc_lsr_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_mbqc_lsr_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_mbqc_lsr_max'])) {
                return false;
            }
            break;
        case 'za_mbqc_mae_jtl':
            if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_mbqc_mae_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_mbqc_mae_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_mbqc_mae_max'])) {
                return false;
            }
            break;
        case 'za_mbqc_msc_jtl':
            if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_mbqc_msc_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_mbqc_msc_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_mbqc_msc_max'])) {
                return false;
            }
            break;
        case 'za_mbqc_npy_jtl':
            if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_mbqc_npy_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_mbqc_npy_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_mbqc_npy_max'])) {
                return false;
            }
            break;
        case 'za_mbqc_pli_jtl':
            if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_mbqc_pli_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_mbqc_pli_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_mbqc_pli_max'])) {
                return false;
            }
            break;
        case 'za_mbqc_psp_jtl':
            if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_mbqc_psp_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_mbqc_psp_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_mbqc_psp_max'])) {
                return false;
            }
            break;
        case 'za_mbqc_pwy_jtl':
            if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_mbqc_pwy_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_mbqc_pwy_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_mbqc_pwy_max'])) {
                return false;
            }
            break;
        case 'za_mbqc_sft_jtl':
            if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_mbqc_sft_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_mbqc_sft_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_mbqc_sft_max'])) {
                return false;
            }
            break;
        case 'za_mbqc_slo_jtl':
            if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_mbqc_slo_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_mbqc_slo_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_mbqc_slo_max'])) {
                return false;
            }
            break;
        case 'za_mbqc_so2_jtl':
            if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_mbqc_so2_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_mbqc_so2_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_mbqc_so2_max'])) {
                return false;
            }
            break;
        case 'za_mbqc_vsa_jtl':
            if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_mbqc_vsa_min_bestellungen'])) {
                return false;
            }

            if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_mbqc_vsa_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_mbqc_vsa_max'])) {
                return false;
            }
            break;
        case 'za_mbqc_wlt_jtl':
            if (!pruefeZahlungsartMinBestellungen($Zahlungsart->einstellungen['zahlungsart_mbqc_wlt_min_bestellungen'])) {
                return false;
            }
            if (!pruefeZahlungsartMinBestellwert($Zahlungsart->einstellungen['zahlungsart_mbqc_wlt_min'])) {
                return false;
            }

            if (!pruefeZahlungsartMaxBestellwert($Zahlungsart->einstellungen['zahlungsart_mbqc_wlt_max'])) {
                return false;
            }
            break;
        case 'za_billpay_jtl':
        case 'za_billpay_invoice_jtl':
        case 'za_billpay_direct_debit_jtl':
        case 'za_billpay_rate_payment_jtl':
        case 'za_billpay_paylater_jtl':
        // workaround, fallback wawi <= v1.072
        if ($Zahlungsart->cModulId === 'za_billpay_jtl') {
            $Zahlungsart->cModulId = 'za_billpay_invoice_jtl';
        }
            require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php';
            $paymentMethod = PaymentMethod::create($Zahlungsart->cModulId);

            return $paymentMethod->isValid($_SESSION['Kunde'], $_SESSION['Warenkorb']);
            break;

        default:
            // Zahlungsart als Plugin
            $kPlugin = gibkPluginAuscModulId($Zahlungsart->cModulId);
            if ($kPlugin > 0) {
                $oPlugin = new Plugin($kPlugin);
                if ($oPlugin->kPlugin > 0) {
                    require_once PFAD_ROOT . PFAD_PLUGIN . $oPlugin->cVerzeichnis . '/' . PFAD_PLUGIN_VERSION . $oPlugin->nVersion . '/' . PFAD_PLUGIN_PAYMENTMETHOD .
                        $oPlugin->oPluginZahlungsKlasseAssoc_arr[$Zahlungsart->cModulId]->cClassPfad;
                    $className               = $oPlugin->oPluginZahlungsKlasseAssoc_arr[$Zahlungsart->cModulId]->cClassName;
                    $paymentMethod           = new $className($Zahlungsart->cModulId);
                    $paymentMethod->cModulId = $Zahlungsart->cModulId;

                    Jtllog::writeLog('Zahlungsart Gueltig Plugin: kPlugin ' . $oPlugin->kPlugin . ' - ModulID: ' . $Zahlungsart->cModulId, JTLLOG_LEVEL_DEBUG, false);

                    if (!pruefeZahlungsartMinBestellungen($oPlugin->oPluginEinstellungAssoc_arr[$Zahlungsart->cModulId . '_min_bestellungen'])) {
                        return false;
                    }
                    if (!pruefeZahlungsartMinBestellwert($oPlugin->oPluginEinstellungAssoc_arr[$Zahlungsart->cModulId . '_min'])) {
                        return false;
                    }
                    if (!pruefeZahlungsartMaxBestellwert($oPlugin->oPluginEinstellungAssoc_arr[$Zahlungsart->cModulId . '_max'])) {
                        return false;
                    }

                    return $paymentMethod->isValid($_SESSION['Kunde'], $_SESSION['Warenkorb']);
                }
            }
            break;
    }

    return true;
}

/**
 * @param int $nMinBestellungen
 * @return bool
 */
function pruefeZahlungsartMinBestellungen($nMinBestellungen)
{
    if ($nMinBestellungen > 0) {
        if ($_SESSION['Kunde']->kKunde > 0) {
            $anzahl_obj = Shop::DB()->query(
                "SELECT count(*) AS anz
					FROM tbestellung
					WHERE kKunde = " . (int)$_SESSION['Kunde']->kKunde . "
                        AND (cStatus = '" . BESTELLUNG_STATUS_BEZAHLT . "'
                        OR cStatus = '" . BESTELLUNG_STATUS_VERSANDT . "')", 1
            );
            if ($anzahl_obj->anz < $nMinBestellungen) {
                Jtllog::writeLog('pruefeZahlungsartMinBestellungen Bestellanzahl zu niedrig: Anzahl ' . $anzahl_obj->anz . ' < ' . $nMinBestellungen, JTLLOG_LEVEL_DEBUG, false);

                return false;
            }
        } else {
            Jtllog::writeLog('pruefeZahlungsartMinBestellungen erhielt keinen kKunden', JTLLOG_LEVEL_DEBUG, false);

            return false;
        }
    }

    return true;
}

/**
 * @param float $fMinBestellwert
 * @return bool
 */
function pruefeZahlungsartMinBestellwert($fMinBestellwert)
{
    if ($fMinBestellwert > 0 && $_SESSION['Warenkorb']->gibGesamtsummeWaren(1) <= $fMinBestellwert) {
        if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
            Jtllog::writeLog('pruefeZahlungsartMinBestellwert Bestellwert zu niedrig: Wert ' . $_SESSION['Warenkorb']->gibGesamtsummeWaren(true) . ' < ' . $fMinBestellwert,
                JTLLOG_LEVEL_DEBUG, false);
        }

        return false;
    }

    return true;
}

/**
 * @param float $fMaxBestellwert
 * @return bool
 */
function pruefeZahlungsartMaxBestellwert($fMaxBestellwert)
{
    if ($fMaxBestellwert > 0 && $_SESSION['Warenkorb']->gibGesamtsummeWaren(1) >= $fMaxBestellwert) {
        if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
            Jtllog::writeLog('pruefeZahlungsartMaxBestellwert Bestellwert zu hoch: Wert ' . $_SESSION['Warenkorb']->gibGesamtsummeWaren(true) . ' > ' . $fMaxBestellwert,
                JTLLOG_LEVEL_DEBUG, false);
        }

        return false;
    }

    return true;
}

/**
 * @param int $kVersandart
 * @param int $aFormValues
 * @return bool
 */
function versandartKorrekt($kVersandart, $aFormValues = 0)
{
    $kVersandart = intval($kVersandart);
    //Verpackung beachten
    $kVerpackung_arr = (isset($_POST['kVerpackung']) && is_array($_POST['kVerpackung']) && count($_POST['kVerpackung']) > 0) ?
        $_POST['kVerpackung'] :
        $aFormValues['kVerpackung'];
    $fSummeWarenkorb        = $_SESSION['Warenkorb']->gibGesamtsummeWarenExt(array(C_WARENKORBPOS_TYP_ARTIKEL), true);
    $_SESSION['Verpackung'] = array();
    if (is_array($kVerpackung_arr) && count($kVerpackung_arr) > 0) {
        unset($_SESSION['Verpackungen']);
        $_SESSION['Warenkorb']->loescheSpezialPos(C_WARENKORBPOS_TYP_VERPACKUNG);
        foreach ($kVerpackung_arr as $i => $kVerpackung) {
            $kVerpackung = intval($kVerpackung);
            $oVerpackung = Shop::DB()->query(
                "SELECT *
                    FROM tverpackung
                    WHERE kVerpackung = " . (int)$kVerpackung . "
                        AND (tverpackung.cKundengruppe = '-1'
                            OR tverpackung.cKundengruppe LIKE '%;" . (int)$_SESSION['Kundengruppe']->kKundengruppe . ";%')
                        AND " . $fSummeWarenkorb . " >= tverpackung.fMindestbestellwert
                        AND nAktiv = 1", 1
            );

            if ($oVerpackung->kVerpackung > 0) {
                $cName_arr              = array();
                $oVerpackungSprache_arr = Shop::DB()->query(
                    "SELECT *
                        FROM tverpackungsprache
                        WHERE kVerpackung = " . (int)$oVerpackung->kVerpackung, 2
                );
                if (count($oVerpackungSprache_arr) > 0) {
                    foreach ($oVerpackungSprache_arr as $oVerpackungSprache) {
                        $cName_arr[$oVerpackungSprache->cISOSprache] = $oVerpackungSprache->cName;
                    }
                }
                $fBrutto = $oVerpackung->fBrutto;
                if ($fSummeWarenkorb >= $oVerpackung->fKostenfrei && $oVerpackung->fBrutto > 0 && $oVerpackung->fKostenfrei != 0) {
                    $fBrutto = 0;
                }
                if ($oVerpackung->kSteuerklasse == -1) {
                    $oVerpackung->kSteuerklasse = $_SESSION['Warenkorb']->gibVersandkostenSteuerklasse($_SESSION['Lieferadresse']->cLand);
                }
                $_SESSION['Verpackung'][] = $oVerpackung;
                $_SESSION['Warenkorb']->erstelleSpezialPos($cName_arr, 1, $fBrutto, $oVerpackung->kSteuerklasse, C_WARENKORBPOS_TYP_VERPACKUNG, false);
                unset($oVerpackung);
            } else {
                return false;
            }
        }
    }
    unset($_SESSION['Versandart']);
    if ($kVersandart > 0) {
        $lieferland = (isset($_SESSION['Lieferadresse']->cLand)) ? $_SESSION['Lieferadresse']->cLand : null;
        if (!$lieferland) {
            $lieferland = $_SESSION['Kunde']->cLand;
        }
        $plz = (isset($_SESSION['Lieferadresse']->cPLZ)) ? $_SESSION['Lieferadresse']->cPLZ : null;
        if (!$plz) {
            $plz = $_SESSION['Kunde']->cPLZ;
        }
        $versandklassen           = VersandartHelper::getShippingClasses($_SESSION['Warenkorb']);
        $cNurAbhaengigeVersandart = 'N';
        if (VersandartHelper::normalerArtikelversand($lieferland) == false) {
            $cNurAbhaengigeVersandart = 'Y';
        }
        $cISO       = $lieferland;
        $versandart = Shop::DB()->query(
            "SELECT *
                FROM tversandart
                WHERE cLaender LIKE \"%" . $cISO . "%\"
                    AND cNurAbhaengigeVersandart = '" . $cNurAbhaengigeVersandart . "'
                    AND (cVersandklassen='-1' OR (cVersandklassen LIKE \"% " . $versandklassen . " %\" OR cVersandklassen LIKE \"% " . $versandklassen . "\"))
                    AND kVersandart = " . $kVersandart, 1
        );

        if (isset($versandart->kVersandart) && $versandart->kVersandart > 0) {
            $versandart->Zuschlag  = gibVersandZuschlag($versandart, $cISO, $plz);
            $versandart->fEndpreis = berechneVersandpreis($versandart, $cISO, null);
            if ($versandart->fEndpreis == -1) {
                return false;
            }
            //posname lokalisiert ablegen
            if (!isset($Spezialpos)) {
                $Spezialpos = new stdClass();
            }
            $Spezialpos->cName = array();
            foreach ($_SESSION['Sprachen'] as $Sprache) {
                $name_spr = Shop::DB()->select('tversandartsprache', 'kVersandart', (int)$versandart->kVersandart, 'cISOSprache', $Sprache->cISO, null, null, false, 'cName, cHinweisText');
                if (isset($name_spr->cName)) {
                    $Spezialpos->cName[$Sprache->cISO]                  = $name_spr->cName;
                    $versandart->angezeigterName[$Sprache->cISO]        = $name_spr->cName;
                    $versandart->angezeigterHinweistext[$Sprache->cISO] = $name_spr->cHinweisText;
                }
            }
            //auskommentiert - $versandart->eSteuer soll eigentlich NUR die Versandkostenfrei-Grenze beeinflussen
            //$bSteuerPos = $versandart->eSteuer === 'netto' ? false : true;
            // Ticket #1298 Inselzuschläge müssen bei Versandkostenfrei berücksichtigt werden
            $fVersandpreis = $versandart->fEndpreis;
            if (isset($versandart->Zuschlag->fZuschlag)) {
                $fVersandpreis = $versandart->fEndpreis - $versandart->Zuschlag->fZuschlag;
            }
            if ($versandart->fEndpreis == 0 && isset($versandart->Zuschlag->fZuschlag) && $versandart->Zuschlag->fZuschlag > 0) {
                $fVersandpreis = $versandart->fEndpreis;
            }
            $_SESSION['Warenkorb']->erstelleSpezialPos(
                $Spezialpos->cName,
                1,
                $fVersandpreis,
                $_SESSION['Warenkorb']->gibVersandkostenSteuerklasse($cISO),
                C_WARENKORBPOS_TYP_VERSANDPOS,
                true,
                true//$bSteuerPos
            );
            pruefeVersandkostenfreiKuponVorgemerkt();
            //Zuschlag?
            $_SESSION['Warenkorb']->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG);
            if (isset($versandart->Zuschlag->fZuschlag) && $versandart->Zuschlag->fZuschlag != 0) {
                //posname lokalisiert ablegen
                $Spezialpos->cName = array();
                foreach ($_SESSION['Sprachen'] as $Sprache) {
                    $name_spr                          = Shop::DB()->select('tversandzuschlagsprache', 'kVersandzuschlag', (int)$versandart->Zuschlag->kVersandzuschlag, 'cISOSprache', $Sprache->cISO, null, null, false, 'cName');
                    $Spezialpos->cName[$Sprache->cISO] = $name_spr->cName;
                }
                $_SESSION['Warenkorb']->erstelleSpezialPos(
                    $Spezialpos->cName,
                    1,
                    $versandart->Zuschlag->fZuschlag,
                    $_SESSION['Warenkorb']->gibVersandkostenSteuerklasse($cISO), C_WARENKORBPOS_TYP_VERSANDZUSCHLAG,
                    true,
                    true//$bSteuerPos
                );
            }
            $_SESSION['Versandart'] = $versandart;

            return true;
        }
    }

    return false;
}

/**
 * @param array $fehlendeAngaben
 * @return int
 */
function angabenKorrekt($fehlendeAngaben)
{
    foreach ($fehlendeAngaben as $angabe) {
        if ($angabe > 0) {
            return 0;
        }
    }

    return 1;
}

/**
 * @param int $kundenaccount
 * @param int $checkpass
 * @return array
 */
function checkKundenFormular($kundenaccount, $checkpass = 1)
{
    $_POST['email']      = (isset($_POST['email'])) ? trim($_POST['email']) : null;
    $_POST['vorname']    = (isset($_POST['vorname'])) ? trim($_POST['vorname']) : null;
    $_POST['nachname']   = (isset($_POST['nachname'])) ? trim($_POST['nachname']) : null;
    $_POST['strasse']    = (isset($_POST['strasse'])) ? trim($_POST['strasse']) : null;
    $_POST['hausnummer'] = (isset($_POST['hausnummer'])) ? trim($_POST['hausnummer']) : null;
    $_POST['firma']      = (isset($_POST['firma'])) ? trim($_POST['firma']) : null;
    $ret                 = array();
    $conf                = Shop::getSettings(array(CONF_KUNDEN, CONF_KUNDENFELD, CONF_GLOBAL));
    if ($conf['kunden']['kundenregistrierung_abfragen_anrede'] === 'Y' && (!isset($_POST['anrede']) || !$_POST['anrede'])) {
        $ret['anrede'] = 1;
    }
    if ($conf['kunden']['kundenregistrierung_pflicht_vorname'] === 'Y' && (!isset($_POST['vorname']) || !$_POST['vorname'])) {
        $ret['vorname'] = 1;
    }
    if (!isset($_POST['nachname']) || !$_POST['nachname']) {
        $ret['nachname'] = 1;
    }
    if (!isset($_POST['strasse']) || !$_POST['strasse']) {
        $ret['strasse'] = 1;
    }
    if (!isset($_POST['hausnummer']) || !$_POST['hausnummer']) {
        $ret['hausnummer'] = 1;
    }
    if ($conf['kunden']['kundenregistrierung_abfragen_firma'] === 'Y' && (!isset($_POST['firma']) || !$_POST['firma'])) {
        $ret['firma'] = 1;
    }
    if ($conf['kunden']['kundenregistrierung_abfragen_firmazusatz'] === 'Y' && (!isset($_POST['firmazusatz']) || !$_POST['firmazusatz'])) {
        $ret['firmazusatz'] = 1;
    }
    if (!isset($_POST['plz']) || !$_POST['plz']) {
        $ret['plz'] = 1;
    }
    if (!isset($_POST['ort']) || !$_POST['ort']) {
        $ret['ort'] = 1;
    }
    if (!isset($_POST['land']) || !$_POST['land']) {
        $ret['land'] = 1;
    }
    if (!isset($_POST['email']) || !$_POST['email']) {
        $ret['email'] = 1;
    }
    if (!valid_email($_POST['email'])) {
        $ret['email'] = 2;
    } elseif (pruefeEmailblacklist($_POST['email'])) {
        $ret['email'] = 3;
    }
    if (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) {
        if ($_POST['email'] !== $_SESSION['Kunde']->cMail && !isEmailAvailable($_POST['email'])) {
            $ret['email'] = 5;
        }
    } elseif (!isEmailAvailable($_POST['email'])) {
        $ret['email'] = 5;
    }
    if ($conf['kunden']['kundenregistrierung_abgleichen_plz'] === 'Y' && $_POST['plz'] && $_POST['ort'] && $_POST['land'] && empty($_SESSION['check_plzort'])) {
        if (!valid_plzort($_POST['plz'], $_POST['ort'], $_POST['land'])) {
            $ret['plz']               = 2;
            $ret['ort']               = 2;
            $_SESSION['check_plzort'] = 1;
        }
    } else {
        unset($_SESSION['check_plzort']);
    }
    if ($conf['kunden']['kundenregistrierung_abfragen_titel'] === 'Y' && !$_POST['titel']) {
        $ret['titel'] = 1;
    }
    if ($conf['kunden']['kundenregistrierung_abfragen_adresszusatz'] === 'Y' && !$_POST['adresszusatz']) {
        $ret['adresszusatz'] = 1;
    }
    if ($conf['kunden']['kundenregistrierung_abfragen_mobil'] === 'Y') {
        if (checkeTel($_POST['mobil']) > 0) {
            $ret['mobil'] = checkeTel($_POST['mobil']);
        }
    }
    if ($conf['kunden']['kundenregistrierung_abfragen_fax'] === 'Y') {
        if (checkeTel($_POST['fax']) > 0) {
            $ret['fax'] = checkeTel($_POST['fax']);
        }
    }
    $deliveryCountry = ($conf['kunden']['kundenregistrierung_abfragen_ustid'] !== 'N') ?
        Shop::DB()->select('tland', 'cISO', $_POST['land']) :
        null;

    if ($conf['kunden']['kundenregistrierung_abfragen_ustid'] === 'Y' && isset($deliveryCountry->nEU) && $deliveryCountry->nEU === '0') {
        //skip
    } elseif ($conf['kunden']['kundenregistrierung_abfragen_ustid'] === 'Y' && (empty($_POST['ustid']))) {
        $ret['ustid'] = 1;
    } elseif ($conf['kunden']['kundenregistrierung_abfragen_ustid'] !== 'N') {
        if ((!isset($_SESSION['Kunde']->cUSTID) && $_POST['ustid'] !== '') || (isset($_SESSION['Kunde']->cUSTID) && $_SESSION['Kunde']->cUSTID != $_POST['ustid'])) {
            $oUstID = new UstID(
                $conf['kunden']['shop_ustid'],
                StringHandler::filterXSS($_POST['ustid']),
                StringHandler::filterXSS($_POST['firma']),
                StringHandler::filterXSS($_POST['ort']),
                StringHandler::filterXSS($_POST['plz']),
                StringHandler::filterXSS($_POST['strasse']),
                'Nein',
                ((isset($_POST['hausnummer'])) ? (StringHandler::filterXSS($_POST['hausnummer'])) : '')
            );
            $bBZStPruefung = false;
            //Admin-Einstellung BZST pruefen und checken ob Auslaendische USt-ID angegeben (deutsche USt-IDs koennen nicht geprueft werden)
            $ustLaendercode = strtolower(substr($_POST['ustid'], 0, 2));
            if ($conf['kunden']['shop_ustid_bzstpruefung'] === 'Y' && $ustLaendercode !== 'de') {
                $bBZStPruefung = true;
            }
            $cUstPruefung = $oUstID->bearbeiteAnfrage($bBZStPruefung);

            if ($cUstPruefung === -1) { // UstID ist durch Stringprüfung ungültig
                $oReturn          = $oUstID->pruefeUstIDString($_POST['ustid']);
                $ret['ustid']     = 2;
                $ret['ustid_err'] = $oReturn->cError;
            } elseif ($cUstPruefung === 999) {
                // BZSt ist nicht erreichbar aber Stringprüfung war erfolgreich
                $ret['ustid'] = 4;
            } elseif ($cUstPruefung !== 200 && $cUstPruefung !== 1) { // UstID ist durch BZSt ungültig
                $ret['ustid'] = 5;
            }
        }
    }
    if ($conf['kunden']['kundenregistrierung_abfragen_geburtstag'] === 'Y') {
        if (checkeDatum(StringHandler::filterXSS($_POST['geburtstag'])) > 0) {
            $ret['geburtstag'] = checkeDatum(StringHandler::filterXSS($_POST['geburtstag']));
        }
    }
    if ($conf['kunden']['kundenregistrierung_abfragen_www'] === 'Y' && !$_POST['www']) {
        $ret['www'] = 1;
    }
    if ($conf['kunden']['kundenregistrierung_abfragen_tel'] === 'Y') {
        if (checkeTel($_POST['tel']) > 0) {
            $ret['tel'] = checkeTel($_POST['tel']);
        }
    }
    if ($conf['kunden']['kundenregistrierung_abfragen_bundesland'] === 'Y' && !$_POST['bundesland']) {
        $ret['bundesland'] = 1;
    }
    if ($kundenaccount == 1) {
        if ($checkpass) {
            if ($_POST['pass'] != $_POST['pass2']) {
                $ret['pass_ungleich'] = 1;
            }
            if (strlen($_POST['pass']) < $conf['kunden']['kundenregistrierung_passwortlaenge']) {
                $ret['pass_zu_kurz'] = 1;
            }
        }
        //existiert diese email bereits?
        $obj = Shop::DB()->query("SELECT kKunde FROM tkunde WHERE cMail = '" . Shop::DB()->escape($_POST['email']) . "' AND cPasswort != ''", 1);
        if (isset($obj->kKunde) && $obj->kKunde > 0) {
            $ret['email_vorhanden'] = 1;
        }
        if (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) {
            //emailadresse anders und existiert dennoch?
            $mail = Shop::DB()->query("SELECT cMail FROM tkunde WHERE kKunde = " . intval($_SESSION['Kunde']->kKunde), 1);
            if ($_POST['email'] == $mail->cMail) {
                unset($ret['email_vorhanden']);
            }
        }
    }

    // Selbstdef. Kundenfelder
    if (isset($conf['kundenfeld']['kundenfeld_anzeigen']) && $conf['kundenfeld']['kundenfeld_anzeigen'] === 'Y') {
        $oKundenfeld_arr = Shop::DB()->query(
            "SELECT kKundenfeld, cName, cTyp, nPflicht, nEditierbar
                FROM tkundenfeld
                WHERE kSprache = " . (int)Shop::$kSprache, 2
        );
        if (is_array($oKundenfeld_arr) && count($oKundenfeld_arr) > 0) {
            foreach ($oKundenfeld_arr as $oKundenfeld) {
                // Kundendaten ändern?
                if (intval($_POST['editRechnungsadresse']) === 1) {
                    if (!$_POST['custom_' . $oKundenfeld->kKundenfeld] && $oKundenfeld->nPflicht == 1 && $oKundenfeld->nEditierbar == 1) {
                        $ret['custom'][$oKundenfeld->kKundenfeld] = 1;
                    } else {
                        if ($_POST['custom_' . $oKundenfeld->kKundenfeld]) {
                            // Datum
                            // 1 = leer
                            // 2 = falsches Format
                            // 3 = falsches Datum
                            // 0 = o.k.
                            if ($oKundenfeld->cTyp === 'datum') {
                                $_dat   = StringHandler::filterXSS($_POST['custom_' . $oKundenfeld->kKundenfeld]);
                                $_datTs = strtotime($_dat);
                                $_dat   = ($_datTs !== false) ? date('d.m.Y', $_datTs) : false;
                                $check  = checkeDatum($_dat);
                                if ($check !== 0) {
                                    $ret['custom'][$oKundenfeld->kKundenfeld] = $check;
                                }
                            } elseif ($oKundenfeld->cTyp === 'zahl') {
                                // Zahl, 4 = keine Zahl
                                if ($_POST['custom_' . $oKundenfeld->kKundenfeld] != doubleval($_POST['custom_' . $oKundenfeld->kKundenfeld])) {
                                    $ret['custom'][$oKundenfeld->kKundenfeld] = 4;
                                }
                            }
                        }
                    }
                } else { // Neuer Kunde
                    if (!$_POST['custom_' . $oKundenfeld->kKundenfeld] && $oKundenfeld->nPflicht == 1) {
                        $ret['custom'][$oKundenfeld->kKundenfeld] = 1;
                    } else {
                        if ($_POST['custom_' . $oKundenfeld->kKundenfeld]) {
                            // Datum
                            // 1 = leer
                            // 2 = falsches Format
                            // 3 = falsches Datum
                            // 0 = o.k.
                            if ($oKundenfeld->cTyp === 'datum') {
                                $_dat   = StringHandler::filterXSS($_POST['custom_' . $oKundenfeld->kKundenfeld]);
                                $_datTs = strtotime($_dat);
                                $_dat   = ($_datTs !== false) ? date('d.m.Y', $_datTs) : false;
                                $check  = checkeDatum($_dat);
                                if ($check !== 0) {
                                    $ret['custom'][$oKundenfeld->kKundenfeld] = $check;
                                }
                            } elseif ($oKundenfeld->cTyp === 'zahl') {
                                // Zahl, 4 = keine Zahl
                                if ($_POST['custom_' . $oKundenfeld->kKundenfeld] != doubleval($_POST['custom_' . $oKundenfeld->kKundenfeld])) {
                                    $ret['custom'][$oKundenfeld->kKundenfeld] = 4;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    if (isset($conf['kunden']['kundenregistrierung_pruefen_ort']) && $conf['kunden']['kundenregistrierung_pruefen_ort'] === 'Y') {
        if (preg_match('#[0-9]+#', $_POST['ort'])) {
            $ret['ort'] = 3;
        }
    }
    if (isset($conf['kunden']['kundenregistrierung_pruefen_name']) && $conf['kunden']['kundenregistrierung_pruefen_name'] === 'Y') {
        if (preg_match('#[0-9]+#', $_POST['nachname'])) {
            $ret['nachname'] = 2;
        }
    }

    if (isset($conf['kunden']['kundenregistrierung_pruefen_zeit']) && $conf['kunden']['kundenregistrierung_pruefen_zeit'] === 'Y' &&
        isset($_POST['editRechnungsadresse']) && $_POST['editRechnungsadresse'] != 1
    ) {
        $dRegZeit = (!isset($_SESSION['dRegZeit'])) ? 0 : $_SESSION['dRegZeit'];
        if (!($dRegZeit + 5 < time())) {
            $ret['formular_zeit'] = 1;
        }
    }
    if (isset($conf['kunden']['kundenregistrierung_pruefen_email']) && $conf['kunden']['kundenregistrierung_pruefen_email'] === 'Y') {
        if (isset($_POST['email']) && strlen($_POST['email']) > 0) {
            if (!checkdnsrr(substr($_POST['email'], strpos($_POST['email'], '@') + 1))) {
                $ret['email'] = 4;
            }
        }
    }

    if ((!isset($_SESSION['bAnti_spam_already_checked']) || $_SESSION['bAnti_spam_already_checked'] !== true) && isset($conf['kunden']['registrieren_captcha']) && $conf['kunden']['registrieren_captcha'] !== 'N' && $conf['global']['anti_spam_method'] !== 'N' && empty($_SESSION['Kunde']->kKunde)) {
        // reCAPTCHA
        if (isset($_POST['g-recaptcha-response'])) {
            $ret['captcha'] = !validateReCaptcha($_POST['g-recaptcha-response']);
        } else {
            if (empty($_POST['captcha'])) {
                $ret['captcha'] = 1;
            }
            if (empty($_POST['md5']) || ($_POST['md5'] !== md5(PFAD_ROOT . strtoupper($_POST['captcha'])))) {
                $ret['captcha'] = 2;
            }
            if ($conf['kunden']['registrieren_captcha'] == 5) { //Prüfen ob der Token und der Name korrekt sind
                $ret['captcha'] = 2;
                if (validToken()) {
                    unset($ret['captcha']);
                }
            }
        }
    }

    return $ret;
}

/**
 * @return array
 */
function checkLieferFormular()
{
    $ret  = array();
    $conf = Shop::getSettings(array(CONF_KUNDEN));
    if (!isset($_POST['nachname']) || !$_POST['nachname']) {
        $ret['nachname'] = 1;
    }
    if (!isset($_POST['strasse']) || !$_POST['strasse']) {
        $ret['strasse'] = 1;
    }
    if (!isset($_POST['hausnummer']) || !$_POST['hausnummer']) {
        $ret['hausnummer'] = 1;
    }
    if (!isset($_POST['plz']) || !$_POST['plz']) {
        $ret['plz'] = 1;
    }
    if (!isset($_POST['ort']) || !$_POST['ort']) {
        $ret['ort'] = 1;
    }
    if (!isset($_POST['land']) || !$_POST['land']) {
        $ret['land'] = 1;
    }
    if ($conf['kunden']['lieferadresse_abfragen_titel'] === 'Y' && !$_POST['titel']) {
        $ret['titel'] = 1;
    }
    if ($conf['kunden']['lieferadresse_abfragen_adresszusatz'] === 'Y' && !$_POST['adresszusatz']) {
        $ret['adresszusatz'] = 1;
    }
    if ($conf['kunden']['lieferadresse_abfragen_bundesland'] === 'Y' && !$_POST['bundesland']) {
        $ret['bundesland'] = 1;
    }
    if ($conf['kunden']['lieferadresse_abfragen_email'] === 'Y') {
        if (!$_POST['email']) {
            $ret['email'] = 1;
        } elseif (!valid_email($_POST['email'])) {
            $ret['email'] = 2;
        }
    }
    if ($conf['kunden']['lieferadresse_abfragen_tel'] === 'Y') {
        if (checkeTel(StringHandler::filterXSS($_POST['tel'])) > 0) {
            $ret['tel'] = checkeTel(StringHandler::filterXSS($_POST['tel']));
        }
    }
    if ($conf['kunden']['lieferadresse_abfragen_mobil'] === 'Y') {
        if (checkeTel(StringHandler::filterXSS($_POST['mobil'])) > 0) {
            $ret['mobil'] = checkeTel(StringHandler::filterXSS($_POST['mobil']));
        }
    }
    if ($conf['kunden']['lieferadresse_abfragen_fax'] === 'Y') {
        if (checkeTel(StringHandler::filterXSS($_POST['fax'])) > 0) {
            $ret['fax'] = checkeTel(StringHandler::filterXSS($_POST['fax']));
        }
    }

    return $ret;
}

/**
 * @param object|Kupon $Kupon
 * @return array
 */
function checkeKupon($Kupon)
{
    $ret = array();
    if ($Kupon->cAktiv !== 'Y') {
        $ret['ungueltig'] = 1;
    }
    if ($Kupon->dGueltigBis !== '0000-00-00 00:00:00' && date_create($Kupon->dGueltigBis) < date_create()) {
        $ret['ungueltig'] = 2;
    }
    if (date_create($Kupon->dGueltigAb) > date_create()) {
        $ret['ungueltig'] = 3;
    }
    if ($Kupon->fMindestbestellwert > $_SESSION['Warenkorb']->gibGesamtsummeWarenExt(array(C_WARENKORBPOS_TYP_ARTIKEL), true)) {
        $ret['ungueltig'] = 4;
    }
    if ($Kupon->kKundengruppe > 0 && $Kupon->kKundengruppe != $_SESSION['Kundengruppe']->kKundengruppe) {
        $ret['ungueltig'] = 5;
    }
    if ($Kupon->nVerwendungen > 0 && $Kupon->nVerwendungen <= $Kupon->nVerwendungenBisher) {
        $ret['ungueltig'] = 6;
    }
    if ($Kupon->cArtikel && !warenkorbKuponFaehigArtikel($Kupon, $_SESSION['Warenkorb']->PositionenArr)) {
        $ret['ungueltig'] = 7;
    }
    if ($Kupon->cKategorien && $Kupon->cKategorien != -1 && !warenkorbKuponFaehigKategorien($Kupon, $_SESSION['Warenkorb']->PositionenArr)) {
        $ret['ungueltig'] = 8;
    }
    if (($Kupon->cKunden != -1 && isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0 && strpos($Kupon->cKunden, $_SESSION['Kunde']->kKunde . ';') === false && $Kupon->cKuponTyp !== 'neukundenkupon') ||
        ($Kupon->cKunden != -1 && $Kupon->cKuponTyp !== 'neukundenkupon' && !isset($_SESSION['Kunde']->kKunde))) {
        $ret['ungueltig'] = 9;
    }
    if ($Kupon->cKuponTyp === 'versandkupon' && isset($_SESSION['Lieferadresse']) && strpos($Kupon->cLieferlaender, $_SESSION['Lieferadresse']->cLand) === false) {
        $ret['ungueltig'] = 10;
    }
    // Neukundenkupon
    if ($Kupon->cKuponTyp === 'neukundenkupon') {
        $Hash = Kuponneukunde::Hash(
            null,
            trim($_SESSION['Kunde']->cNachname),
            trim($_SESSION['Kunde']->cStrasse),
            null,
            trim($_SESSION['Kunde']->cPLZ),
            trim($_SESSION['Kunde']->cOrt),
            trim($_SESSION['Kunde']->cLand)
        );
        $Kuponneukunde = Kuponneukunde::Load($_SESSION['Kunde']->cMail, $Hash);
        if ($Kuponneukunde !== null) {
            $ret['ungueltig'] = 11;
        }
    }
    $alreadyUsedSQL = '';
    if (!empty($_SESSION['Kunde']->kKunde) && !empty($_SESSION['Kunde']->cMail)) {
        $alreadyUsedSQL = "SELECT SUM(nVerwendungen) AS nVerwendungen FROM tkuponkunde WHERE (kKunde = " . intval($_SESSION['Kunde']->kKunde) .
            " OR cMail = '" . Shop::DB()->escape($_SESSION['Kunde']->cMail) . "') AND kKupon = " . intval($Kupon->kKupon);
    } elseif (!empty($_SESSION['Kunde']->cMail)) {
        $alreadyUsedSQL = "SELECT SUM(nVerwendungen) AS nVerwendungen FROM tkuponkunde WHERE (cMail = '" . Shop::DB()->escape($_SESSION['Kunde']->cMail) .
            "') AND kKupon = " . (int)$Kupon->kKupon;
    } elseif (!empty($_SESSION['Kunde']->kKunde)) {
        $alreadyUsedSQL = "SELECT SUM(nVerwendungen) AS nVerwendungen FROM tkuponkunde WHERE (kKunde = " . intval($_SESSION['Kunde']->kKunde) .
            ") AND kKupon = " . (int)$Kupon->kKupon;
    }
    if ($alreadyUsedSQL !== '') {
        //hat der kunde schon die max. Verwendungsanzahl erreicht?
        $anz = Shop::DB()->query($alreadyUsedSQL, 1);
        if (isset($Kupon->nVerwendungenProKunde) && isset($anz->nVerwendungen) && $anz->nVerwendungen >= $Kupon->nVerwendungenProKunde && $Kupon->nVerwendungenProKunde > 0) {
            $ret['ungueltig'] = 6;
        }
    }

    return $ret;
}

/**
 * @param Kupon|object $Kupon
 */
function kuponAnnehmen($Kupon)
{
    if ($Kupon->cWertTyp === 'festpreis') {
        $maxPreisKupon = $Kupon->fWert;
        if ($Kupon->fWert > $_SESSION['Warenkorb']->gibGesamtsummeWarenExt(array(C_WARENKORBPOS_TYP_ARTIKEL), true)) {
            $maxPreisKupon = $_SESSION['Warenkorb']->gibGesamtsummeWarenExt(array(C_WARENKORBPOS_TYP_ARTIKEL), true);
        }
    } elseif ($Kupon->cWertTyp === 'prozent') {
        // Alle Positionen prüfen ob der Kupon greift und falls ja, dann Position rabattieren
        if ($Kupon->nGanzenWKRabattieren == 0) {
            if (is_array($_SESSION['Warenkorb']->PositionenArr) && count($_SESSION['Warenkorb']->PositionenArr) > 0) {
                foreach ($_SESSION['Warenkorb']->PositionenArr as $i => $oWKPosition) {
                    $_SESSION['Warenkorb']->PositionenArr[$i] = checkeKuponWKPos($oWKPosition, $Kupon);
                }
            }
        } else { //Rabatt ermitteln für den ganzen WK
            $maxPreisKupon = ($_SESSION['Warenkorb']->gibGesamtsummeWarenExt(array(C_WARENKORBPOS_TYP_ARTIKEL), true) / 100.0) * $Kupon->fWert;
        }
    }

    //posname lokalisiert ablegen
    if (!isset($Spezialpos)) {
        $Spezialpos = new stdClass();
    }
    $Spezialpos->cName = array();
    foreach ($_SESSION['Sprachen'] as $Sprache) {
        $name_spr                          = Shop::DB()->select('tkuponsprache', 'kKupon', (int)$Kupon->kKupon, 'cISOSprache', $Sprache->cISO, null, null, false, 'cName');
        $Spezialpos->cName[$Sprache->cISO] = $name_spr->cName;
        if ($Kupon->cWertTyp === 'prozent') {
            $Spezialpos->cName[$Sprache->cISO] .= ' ' . $Kupon->fWert . '%';
        }
    }

    if ($Kupon->cKuponTyp === 'standard') {
        $postyp = C_WARENKORBPOS_TYP_KUPON;
        $_SESSION['Warenkorb']->loescheSpezialPos(C_WARENKORBPOS_TYP_KUPON);
        $_SESSION['Kupon'] = $Kupon;
        if (Jtllog::doLog(JTLLOG_LEVEL_NOTICE)) {
            Jtllog::writeLog('Der Standardkupon' . print_r($Kupon, true) . ' wurde genutzt.', JTLLOG_LEVEL_NOTICE, false, 'kKupon', $Kupon->kKupon);
        }
    } elseif ($Kupon->cKuponTyp == 'neukundenkupon') {
        $postyp = C_WARENKORBPOS_TYP_NEUKUNDENKUPON;
        $_SESSION['Warenkorb']->loescheSpezialPos(C_WARENKORBPOS_TYP_NEUKUNDENKUPON);
        $_SESSION['NeukundenKupon']           = $Kupon;
        $_SESSION['NeukundenKuponAngenommen'] = true;
        if (Jtllog::doLog(JTLLOG_LEVEL_NOTICE)) {
            Jtllog::writeLog('Der Neukundenkupon' . print_r($Kupon, true) . ' wurde genutzt.', JTLLOG_LEVEL_NOTICE, false, 'kKupon', $Kupon->kKupon);
        }
    } elseif ($Kupon->cKuponTyp === 'versandkupon') {
        $postyp = C_WARENKORBPOS_TYP_KUPON;
        $_SESSION['Warenkorb']->loescheSpezialPos(C_WARENKORBPOS_TYP_KUPON);
        // Darf nicht gelöscht werden sondern den Preis nur auf 0 setzen!
        //$_SESSION['Warenkorb']->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS);
        $_SESSION['Warenkorb']->setzeVersandfreiKupon();
        $_SESSION['VersandKupon']          = $Kupon;
        $maxPreisKupon                     = 0;
        $Spezialpos->cName[$Sprache->cISO] = $Kupon->cName;
        $_SESSION['Warenkorb']->erstelleSpezialPos($Spezialpos->cName, 1, $maxPreisKupon * -1, $Kupon->kSteuerklasse, $postyp);
        if (Jtllog::doLog(JTLLOG_LEVEL_NOTICE)) {
            Jtllog::writeLog('Der Versandkupon ' . print_r($Kupon, true) . ' wurde genutzt.', JTLLOG_LEVEL_NOTICE, false, 'kKupon', $Kupon->kKupon);
        }
    }
    if ($Kupon->cWertTyp === 'prozent' && $Kupon->nGanzenWKRabattieren != 0) { // %-Kupon + Ganzen WK rabattieren
        $_SESSION['Warenkorb']->erstelleSpezialPos($Spezialpos->cName, 1, $maxPreisKupon * -1, $Kupon->kSteuerklasse, $postyp);
    } elseif ($Kupon->cWertTyp === 'prozent' && $Kupon->nGanzenWKRabattieren == 0 && $Kupon->cKuponTyp !== 'neukundenkupon') { // %-Kupon + nicht Ganzen WK rabattieren
        $_SESSION['Warenkorb']->setzePositionsPreise();
    } elseif ($Kupon->cWertTyp === 'festpreis') {
        $_SESSION['Warenkorb']->erstelleSpezialPos($Spezialpos->cName, 1, $maxPreisKupon * -1, $Kupon->kSteuerklasse, $postyp);
    }
}

/**
 * @param Kupon|object $Kupon
 * @param array $PositionenArr
 * @return bool
 */
function warenkorbKuponFaehigArtikel($Kupon, $PositionenArr)
{
    if (is_array($PositionenArr)) {
        foreach ($PositionenArr as $Pos) {
            if ($Pos->nPosTyp == C_WARENKORBPOS_TYP_ARTIKEL && preg_match('/;' . preg_quote($Pos->Artikel->cArtNr, '/') . ';/i', $Kupon->cArtikel)) {
                return true;
            }
        }
    }

    return false;
}

/**
 * @param Kupon|object $Kupon
 * @param array $PositionenArr
 * @return bool
 */
function warenkorbKuponFaehigKategorien($Kupon, $PositionenArr)
{
    $Kats = array();
    if (is_array($PositionenArr)) {
        foreach ($PositionenArr as $Pos) {
            $kArtikel = $Pos->Artikel->kArtikel;
            // Kind?
            if (ArtikelHelper::isVariChild($kArtikel)) {
                $kArtikel = ArtikelHelper::getParent($kArtikel);
            }
            $Kats_arr = Shop::DB()->query("SELECT kKategorie FROM tkategorieartikel WHERE kArtikel = " . (int)$kArtikel, 2);
            if (is_array($Kats_arr)) {
                foreach ($Kats_arr as $Kat) {
                    if (!in_array($Kat->kKategorie, $Kats)) {
                        $Kats[] = $Kat->kKategorie;
                    }
                }
            }
        }
    }
    foreach ($Kats as $Kat) {
        if (stristr($Kupon->cKategorien, $Kat . ';') !== false) {
            return true;
        }
    }

    return false;
}

/**
 * @param array $post
 * @param int   $kundenaccount
 * @param int   $htmlentities
 * @return Kunde
 */
function getKundendaten($post, $kundenaccount, $htmlentities = 1)
{
    //erstelle neuen Kunden
    $kKunde = (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) ?
        (int)$_SESSION['Kunde']->kKunde :
        0;
    $Kunde = new Kunde($kKunde);
    if ($htmlentities) {
        $Kunde->cAnrede       = (isset($post['anrede'])) ? StringHandler::htmlentities(StringHandler::filterXSS($post['anrede'])) : $Kunde->cAnrede;
        $Kunde->cVorname      = (isset($post['vorname'])) ? StringHandler::htmlentities(StringHandler::filterXSS($post['vorname'])) : $Kunde->cVorname;
        $Kunde->cNachname     = (isset($post['nachname'])) ? StringHandler::htmlentities(StringHandler::filterXSS($post['nachname'])) : $Kunde->cNachname;
        $Kunde->cStrasse      = (isset($post['strasse'])) ? StringHandler::htmlentities(StringHandler::filterXSS($post['strasse'])) : $Kunde->cStrasse;
        $Kunde->cHausnummer   = (isset($post['hausnummer'])) ? StringHandler::htmlentities(StringHandler::filterXSS($post['hausnummer'])) : $Kunde->cHausnummer;
        $Kunde->cPLZ          = (isset($post['plz'])) ? StringHandler::htmlentities(StringHandler::filterXSS($post['plz'])) : $Kunde->cPLZ;
        $Kunde->cOrt          = (isset($post['ort'])) ? StringHandler::htmlentities(StringHandler::filterXSS($post['ort'])) : $Kunde->cOrt;
        $Kunde->cLand         = (isset($post['land'])) ? StringHandler::htmlentities(StringHandler::filterXSS($post['land'])) : $Kunde->cLand;
        $Kunde->cMail         = (isset($post['email'])) ? StringHandler::htmlentities(StringHandler::filterXSS($post['email'])) : $Kunde->cMail;
        $Kunde->cTel          = (isset($post['tel'])) ? StringHandler::htmlentities(StringHandler::filterXSS($post['tel'])) : $Kunde->cTel;
        $Kunde->cFax          = (isset($post['fax'])) ? StringHandler::htmlentities(StringHandler::filterXSS($post['fax'])) : $Kunde->cFax;
        $Kunde->cFirma        = (isset($post['firma'])) ? StringHandler::htmlentities(StringHandler::filterXSS($post['firma'])) : $Kunde->cFirma;
        $Kunde->cZusatz       = (isset($post['firmazusatz'])) ? StringHandler::htmlentities(StringHandler::filterXSS($post['firmazusatz'])) : $Kunde->cZusatz;
        $Kunde->cBundesland   = (isset($post['bundesland'])) ? StringHandler::htmlentities(StringHandler::filterXSS($post['bundesland'])) : $Kunde->cBundesland;
        $Kunde->cTitel        = (isset($post['titel'])) ? StringHandler::htmlentities(StringHandler::filterXSS($post['titel'])) : $Kunde->cTitel;
        $Kunde->cAdressZusatz = (isset($post['adresszusatz'])) ? StringHandler::htmlentities(StringHandler::filterXSS($post['adresszusatz'])) : $Kunde->cAdressZusatz;
        $Kunde->cMobil        = (isset($post['mobil'])) ? StringHandler::htmlentities(StringHandler::filterXSS($post['mobil'])) : $Kunde->cMobil;
        $Kunde->cWWW          = (isset($post['www'])) ? StringHandler::htmlentities(StringHandler::filterXSS($post['www'])) : $Kunde->cWWW;
        $Kunde->cUSTID        = (isset($post['ustid'])) ? StringHandler::htmlentities(StringHandler::filterXSS($post['ustid'])) : $Kunde->cUSTID;
        $Kunde->dGeburtstag   = (isset($post['geburtstag'])) ? StringHandler::htmlentities(StringHandler::filterXSS($post['geburtstag'])) : $Kunde->dGeburtstag;
        $Kunde->cHerkunft     = (isset($post['kundenherkunft'])) ? StringHandler::htmlentities(StringHandler::filterXSS($post['kundenherkunft'])) : $Kunde->cHerkunft;
        if ($kundenaccount != 0) {
            $Kunde->cPasswort = (isset($post['pass'])) ? StringHandler::htmlentities(StringHandler::filterXSS($post['pass'])) : $Kunde->cPasswort;
        }
    } else {
        $Kunde->cAnrede       = (isset($post['anrede'])) ? StringHandler::filterXSS($post['anrede']) : $Kunde->cAnrede;
        $Kunde->cVorname      = (isset($post['vorname'])) ? StringHandler::filterXSS($post['vorname']) : $Kunde->cVorname;
        $Kunde->cNachname     = (isset($post['nachname'])) ? StringHandler::filterXSS($post['nachname']) : $Kunde->cNachname;
        $Kunde->cStrasse      = (isset($post['strasse'])) ? StringHandler::filterXSS($post['strasse']) : $Kunde->cStrasse;
        $Kunde->cHausnummer   = (isset($post['hausnummer'])) ? StringHandler::filterXSS($post['hausnummer']) : $Kunde->cHausnummer;
        $Kunde->cPLZ          = (isset($post['plz'])) ? StringHandler::filterXSS($post['plz']) : $Kunde->cPLZ;
        $Kunde->cOrt          = (isset($post['ort'])) ? StringHandler::filterXSS($post['ort']) : $Kunde->cOrt;
        $Kunde->cLand         = (isset($post['land'])) ? StringHandler::filterXSS($post['land']) : $Kunde->cLand;
        $Kunde->cMail         = (isset($post['email'])) ? StringHandler::filterXSS($post['email']) : $Kunde->cMail;
        $Kunde->cTel          = (isset($post['tel'])) ? StringHandler::filterXSS($post['tel']) : $Kunde->cTel;
        $Kunde->cFax          = (isset($post['fax'])) ? StringHandler::filterXSS($post['fax']) : $Kunde->cFax;
        $Kunde->cFirma        = (isset($post['firma'])) ? StringHandler::filterXSS($post['firma']) : $Kunde->cFirma;
        $Kunde->cZusatz       = (isset($post['firmazusatz'])) ? StringHandler::filterXSS($post['firmazusatz']) : $Kunde->cZusatz;
        $Kunde->cBundesland   = (isset($post['bundesland'])) ? StringHandler::filterXSS($post['bundesland']) : $Kunde->cBundesland;
        $Kunde->cTitel        = (isset($post['titel'])) ? StringHandler::filterXSS($post['titel']) : $Kunde->cTitel;
        $Kunde->cAdressZusatz = (isset($post['adresszusatz'])) ? StringHandler::filterXSS($post['adresszusatz']) : $Kunde->cAdressZusatz;
        $Kunde->cMobil        = (isset($post['mobil'])) ? StringHandler::filterXSS($post['mobil']) : $Kunde->cMobil;
        $Kunde->cWWW          = (isset($post['www'])) ? StringHandler::filterXSS($post['www']) : $Kunde->cWWW;
        $Kunde->cUSTID        = (isset($post['ustid'])) ? StringHandler::filterXSS($post['ustid']) : $Kunde->cUSTID;
        $Kunde->dGeburtstag   = (isset($post['geburtstag'])) ? StringHandler::filterXSS($post['geburtstag']) : $Kunde->dGeburtstag;
        $Kunde->cHerkunft     = (isset($post['kundenherkunft'])) ? StringHandler::filterXSS($post['kundenherkunft']) :  $Kunde->cHerkunft;
        if ($kundenaccount != 0) {
            $Kunde->cPasswort = (isset($post['pass'])) ? StringHandler::filterXSS($post['pass']) : $Kunde->cPasswort;
        }
    }
    if (preg_match('/^\d{2}\.\d{2}\.(\d{4})$/', $Kunde->dGeburtstag)) {
        $Kunde->dGeburtstag = convertDate2German($Kunde->dGeburtstag);
    }
    $Kunde->angezeigtesLand = ISO2land($Kunde->cLand);
    if (strlen($Kunde->cBundesland)) {
        $oISO = Staat::getRegionByIso($Kunde->cBundesland, $Kunde->cLand);
        if (is_object($oISO)) {
            $Kunde->cBundesland = $oISO->cName;
        }
    }

    return $Kunde;
}

/**
 * @param array $cPost_arr
 * @return array
 */
function getKundenattribute($cPost_arr)
{
    $cKundenattribut_arr = array();
    $cSQL                = '';
    if (intval($cPost_arr['editRechnungsadresse']) === 1) {
        $cSQL = " AND nEditierbar=1";
    }
    $oKundenfeld_arr = Shop::DB()->query(
        "SELECT kKundenfeld, cName, cWawi
            FROM tkundenfeld
            WHERE kSprache = " . (int)Shop::$kSprache . $cSQL, 2
    );
    if (is_array($oKundenfeld_arr) && count($oKundenfeld_arr) > 0) {
        foreach ($oKundenfeld_arr as $oKundenfeldTMP) {
            $oKundenfeld              = new stdClass();
            $oKundenfeld->kKundenfeld = $oKundenfeldTMP->kKundenfeld;
            $oKundenfeld->cName       = $oKundenfeldTMP->cName;
            $oKundenfeld->cWawi       = $oKundenfeldTMP->cWawi;
            $oKundenfeld->cWert       = (isset($cPost_arr['custom_' . $oKundenfeldTMP->kKundenfeld])) ?
                StringHandler::filterXSS($cPost_arr['custom_' . $oKundenfeldTMP->kKundenfeld]) :
                null;
            $cKundenattribut_arr[$oKundenfeldTMP->kKundenfeld] = $oKundenfeld;
        }
    }

    return $cKundenattribut_arr;
}

/**
 * @return mixed
 */
function getKundenattributeNichtEditierbar()
{
    $oKundenfeld_arr = Shop::DB()->query(
        "SELECT kKundenfeld
            FROM tkundenfeld
            WHERE kSprache = " . (int)Shop::$kSprache . "
            AND nEditierbar = 0", 2
    );

    return $oKundenfeld_arr;
}

/**
 * @param array $post
 * @return Lieferadresse
 */
function getLieferdaten($post)
{
    //erstelle neue Lieferadresse
    $Lieferadresse                  = new Lieferadresse();
    $Lieferadresse->cAnrede         = StringHandler::filterXSS($post['anrede']);
    $Lieferadresse->cVorname        = StringHandler::filterXSS($post['vorname']);
    $Lieferadresse->cNachname       = StringHandler::filterXSS($post['nachname']);
    $Lieferadresse->cStrasse        = StringHandler::filterXSS($post['strasse']);
    $Lieferadresse->cHausnummer     = StringHandler::filterXSS($post['hausnummer']);
    $Lieferadresse->cPLZ            = StringHandler::filterXSS($post['plz']);
    $Lieferadresse->cOrt            = StringHandler::filterXSS($post['ort']);
    $Lieferadresse->cLand           = StringHandler::filterXSS($post['land']);
    $Lieferadresse->cMail           = isset($post['email']) ? StringHandler::filterXSS($post['email']) : '';
    $Lieferadresse->cTel            = isset($post['tel']) ? StringHandler::filterXSS($post['tel']) : null;
    $Lieferadresse->cFax            = isset($post['fax']) ? StringHandler::filterXSS($post['fax']) : null;
    $Lieferadresse->cFirma          = isset($post['firma']) ? StringHandler::filterXSS($post['firma']) : null;
    $Lieferadresse->cZusatz         = isset($post['firmazusatz']) ? StringHandler::filterXSS($post['firmazusatz']) : null;
    $Lieferadresse->cTitel          = isset($post['titel']) ? StringHandler::filterXSS($post['titel']) : null;
    $Lieferadresse->cAdressZusatz   = isset($post['adresszusatz']) ? StringHandler::filterXSS($post['adresszusatz']) : null;
    $Lieferadresse->cMobil          = isset($post['mobil']) ? StringHandler::filterXSS($post['mobil']) : null;
    $Lieferadresse->cBundesland     = isset($post['bundesland']) ? StringHandler::filterXSS($post['bundesland']) : null;
    $Lieferadresse->angezeigtesLand = ISO2land($Lieferadresse->cLand);

    if (strlen($Lieferadresse->cBundesland)) {
        $oISO = Staat::getRegionByIso($Lieferadresse->cBundesland, $Lieferadresse->cLand);
        if (is_object($oISO)) {
            $Lieferadresse->cBundesland = $oISO->cName;
        }
    }

    return $Lieferadresse;
}

/**
 * @param string $name
 * @param mixed $obj
 */
function setzeInSession($name, $obj)
{
    //an die Session anhängen
    unset($_SESSION[$name]);
    $_SESSION[$name] = $obj;
}

/**
 * @param array $PositionenArr
 * @return string
 */
function getArtikelQry($PositionenArr)
{
    $ret = '';
    if (is_array($PositionenArr) && count($PositionenArr) > 0) {
        foreach ($PositionenArr as $Pos) {
            if (isset($Pos->Artikel->cArtNr) && strlen($Pos->Artikel->cArtNr) > 0) {
                $ret .= " OR cArtikel like '%;" . str_replace("%", "\%", Shop::DB()->escape($Pos->Artikel->cArtNr)) . ";%'";
            }
        }
    }

    return $ret;
}

function guthabenMoeglich()
{
    return ($_SESSION['Kunde']->fGuthaben > 0 && (empty($_SESSION['Bestellung']->GuthabenNutzen) || !$_SESSION['Bestellung']->GuthabenNutzen))
        && substr($_SESSION['Zahlungsart']->cModulId, 0, 10) !== 'za_billpay';
}

/**
 * @return int
 */
function kuponMoeglich()
{
    $moeglich      = 0;
    $Artikel_qry   = '';
    $Kats          = array();
    $Kategorie_qry = '';
    $Kunden_qry    = '';

    if ((!empty($_SESSION['Kupon']->kKupon)) || (!empty($_SESSION['VersandKupon']->kKupon)) ||
        (isset($_SESSION['Zahlungsart']->cModulId) && substr($_SESSION['Zahlungsart']->cModulId, 0, 10) === 'za_billpay')
    ) {
        return 0;
    }
    if (is_array($_SESSION['Warenkorb']->PositionenArr) && count($_SESSION['Warenkorb']->PositionenArr) > 0) {
        foreach ($_SESSION['Warenkorb']->PositionenArr as $Pos) {
            if (isset($Pos->Artikel->cArtNr) && strlen($Pos->Artikel->cArtNr) > 0) {
                $Artikel_qry .= " OR cArtikel LIKE '%;" . str_replace('%', '\%', Shop::DB()->escape($Pos->Artikel->cArtNr)) . ";%'";
            }
            if ($Pos->nPosTyp == C_WARENKORBPOS_TYP_ARTIKEL) {
                if (isset($Pos->Artikel->kArtikel) && $Pos->Artikel->kArtikel > 0) {
                    $kArtikel = $Pos->Artikel->kArtikel;
                    // Kind?
                    if (ArtikelHelper::isVariChild($kArtikel)) {
                        $kArtikel = ArtikelHelper::getParent($kArtikel);
                    }
                    $Kats_arr = Shop::DB()->query("SELECT kKategorie FROM tkategorieartikel WHERE kArtikel = " . (int)$kArtikel, 2);
                    if (is_array($Kats_arr) && count($Kats_arr) > 0) {
                        foreach ($Kats_arr as $Kat) {
                            if (!in_array($Kat->kKategorie, $Kats)) {
                                $Kats[] = $Kat->kKategorie;
                            }
                        }
                    }
                }
            }
        }
        foreach ($Kats as $Kat) {
            $Kategorie_qry .= " OR cKategorien like '%;" . $Kat . ";%'";
        }
    }

    if (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) {
        $Kunden_qry = " OR cKunden LIKE '%;" . $_SESSION['Kunde']->kKunde . ";%'";
    }
    $kupons_mgl = Shop::DB()->query(
        "SELECT * FROM tkupon
            WHERE cAktiv = 'Y'
                AND dGueltigAb <= now()
                AND (dGueltigBis > now() OR dGueltigBis = '0000-00-00 00:00:00')
                AND fMindestbestellwert <= " . $_SESSION['Warenkorb']->gibGesamtsummeWaren(true, false) . "
                AND (cKuponTyp='versandkupon' OR cKuponTyp = 'standard')
                AND (kKundengruppe = -1 OR kKundengruppe = 0 OR kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe . ")
                AND (nVerwendungen = 0 OR nVerwendungen > nVerwendungenBisher)
                AND (cArtikel = '' " . $Artikel_qry . ")
                AND (cKategorien = '' OR cKategorien = '-1' " . $Kategorie_qry . ")
                AND (cKunden = '' OR cKunden = '-1' " . $Kunden_qry . ")", 1
    );
    if (!empty($kupons_mgl->kKupon)) {
        $moeglich = 1;
    }

    return $moeglich;
}

/**
 * @param string $plz
 * @param string $ort
 * @param string $land
 * @return bool
 */
function valid_plzort($plz, $ort, $land)
{
    $plz  = StringHandler::filterXSS($plz);
    $ort  = StringHandler::filterXSS($ort);
    $land = StringHandler::filterXSS($land);
    // Länder die wir mit Ihren Postleitzahlen in der Datenbank haben
    $cSupportedCountry_arr = array('DE', 'AT', 'CH');
    if (in_array(strtoupper($land), $cSupportedCountry_arr)) {
        $obj = Shop::DB()->query(
            "SELECT kPLZ
				FROM tplz
				WHERE cPLZ = '" . Shop::DB()->escape($plz) . "'
				AND cOrt LIKE '" . Shop::DB()->escape($ort) . "'
				AND cLandISO = '" . Shop::DB()->escape($land) . "'", 1
        );
        if (isset($obj->kPLZ) && $obj->kPLZ > 0) {
            return true;
        }
        $obj = Shop::DB()->query(
            "SELECT kPLZ
				FROM tplz
				WHERE cPLZ = '" . Shop::DB()->escape($plz) . "'
				AND cOrt LIKE '" . Shop::DB()->escape(umlauteUmschreibenA2AE($ort)) . "'
				AND cLandISO = '" . Shop::DB()->escape($land) . "'", 1
        );
        if (isset($obj->kPLZ) && $obj->kPLZ > 0) {
            return true;
        }
        $obj = Shop::DB()->query(
            "SELECT kPLZ
				FROM tplz
				WHERE cPLZ = '" . Shop::DB()->escape($plz) . "'
				AND cOrt LIKE '" . Shop::DB()->escape(umlauteUmschreibenAE2A($ort)) . "'
				AND cLandISO = '" . Shop::DB()->escape($land) . "'", 1
        );
        if (isset($obj->kPLZ) && $obj->kPLZ > 0) {
            return true;
        }

        return false;
    }

    //wenn land nicht de/at/ch dann true zurueckgeben
    return true;
}

/**
 * @param string $str
 * @return string
 */
function umlauteUmschreibenA2AE($str)
{
    $src = array('ä', 'ö', 'ü', 'ß', 'Ä', 'Ö', 'Ü', utf8_decode('ä'), utf8_decode('ö'), utf8_decode('ü'), utf8_decode('ß'), utf8_decode('Ä'), utf8_decode('Ö'), utf8_decode('Ü'));
    $rpl = array('ae', 'oe', 'ue', 'ss', 'Ae', 'Oe', 'Ue', 'ae', 'oe', 'ue', 'ss', 'Ae', 'Oe', 'Ue');

    return str_replace($src, $rpl, $str);
}

/**
 * @param string $str
 * @return string
 */
function umlauteUmschreibenAE2A($str)
{
    $rpl = array('ä', 'ö', 'ü', 'ß', 'Ä', 'Ö', 'Ü', utf8_decode('ä'), utf8_decode('ö'), utf8_decode('ü'), utf8_decode('ß'), utf8_decode('Ä'), utf8_decode('Ö'), utf8_decode('Ü'));
    $src = array('ae', 'oe', 'ue', 'ss', 'Ae', 'Oe', 'Ue', 'ae', 'oe', 'ue', 'ss', 'Ae', 'Oe', 'Ue');

    return str_replace($src, $rpl, $str);
}

/**
 * @param string $step
 * @return mixed
 */
function gibBestellschritt($step)
{
    $schritt[1] = 3;
    $schritt[2] = 3;
    $schritt[3] = 3;
    $schritt[4] = 3;
    $schritt[5] = 3;
    switch ($step) {
        case 'unregistriert bestellen' :
            $schritt[1] = 1;
            $schritt[2] = 3;
            $schritt[3] = 3;
            $schritt[4] = 3;
            $schritt[5] = 3;
            break;

        case 'Lieferadresse' :
            $schritt[1] = 2;
            $schritt[2] = 1;
            $schritt[3] = 3;
            $schritt[4] = 3;
            $schritt[5] = 3;
            break;

        case 'Versand' :
            $schritt[1] = 2;
            $schritt[2] = 2;
            $schritt[3] = 1;
            $schritt[4] = 3;
            $schritt[5] = 3;
            break;

        case 'Zahlung' :
            $schritt[1] = 2;
            $schritt[2] = 2;
            $schritt[3] = 2;
            $schritt[4] = 1;
            $schritt[5] = 3;
            break;

        case 'ZahlungZusatzschritt' :
            $schritt[1] = 2;
            $schritt[2] = 2;
            $schritt[3] = 2;
            $schritt[4] = 1;
            $schritt[5] = 3;
            break;

        case 'Bestaetigung' :
            $schritt[1] = 2;
            $schritt[2] = 2;
            $schritt[3] = 2;
            $schritt[4] = 2;
            $schritt[5] = 1;
            break;

        default :
            break;

    }

    return $schritt;
}

/**
 * @return Lieferadresse
 */
function setzeLieferadresseAusRechnungsadresse()
{
    $Lieferadresse                  = new Lieferadresse();
    $Lieferadresse->kKunde          = $_SESSION['Kunde']->kKunde;
    $Lieferadresse->cAnrede         = $_SESSION['Kunde']->cAnrede;
    $Lieferadresse->cVorname        = $_SESSION['Kunde']->cVorname;
    $Lieferadresse->cNachname       = $_SESSION['Kunde']->cNachname;
    $Lieferadresse->cStrasse        = $_SESSION['Kunde']->cStrasse;
    $Lieferadresse->cHausnummer     = $_SESSION['Kunde']->cHausnummer;
    $Lieferadresse->cPLZ            = $_SESSION['Kunde']->cPLZ;
    $Lieferadresse->cOrt            = $_SESSION['Kunde']->cOrt;
    $Lieferadresse->cLand           = $_SESSION['Kunde']->cLand;
    $Lieferadresse->cMail           = $_SESSION['Kunde']->cMail;
    $Lieferadresse->cTel            = $_SESSION['Kunde']->cTel;
    $Lieferadresse->cFax            = $_SESSION['Kunde']->cFax;
    $Lieferadresse->cFirma          = $_SESSION['Kunde']->cFirma;
    $Lieferadresse->cZusatz         = $_SESSION['Kunde']->cZusatz;
    $Lieferadresse->cTitel          = $_SESSION['Kunde']->cTitel;
    $Lieferadresse->cAdressZusatz   = $_SESSION['Kunde']->cAdressZusatz;
    $Lieferadresse->cMobil          = $_SESSION['Kunde']->cMobil;
    $Lieferadresse->cBundesland     = $_SESSION['Kunde']->cBundesland;
    $Lieferadresse->angezeigtesLand = ISO2land($Lieferadresse->cLand);
    setzeInSession('Lieferadresse', $Lieferadresse);

    return $Lieferadresse;
}

/**
 * @return mixed
 */
function gibSelbstdefKundenfelder()
{
    // selbstdef. Kundenfelder
    $oKundenfeld_arr = Shop::DB()->query(
        "SELECT *
            FROM tkundenfeld
            WHERE kSprache = " . (int)Shop::$kSprache . "
            ORDER BY nSort DESC", 2
    );

    if (is_array($oKundenfeld_arr) && count($oKundenfeld_arr) > 0) {
        // tkundenfeldwert nachschauen ob dort Werte für tkundenfeld enthalten sind
        foreach ($oKundenfeld_arr as $i => $oKundenfeld) {
            if ($oKundenfeld->cTyp === 'auswahl') {
                $oKundenfeldWert_arr = Shop::DB()->query(
                    "SELECT *
                        FROM tkundenfeldwert
                        WHERE kKundenfeld = " . (int)$oKundenfeld->kKundenfeld, 2
                );
                $oKundenfeld_arr[$i]->oKundenfeldWert_arr = $oKundenfeldWert_arr;
            }
        }
    }

    return $oKundenfeld_arr;
}

/**
 * @return int
 */
function pruefeAjaxEinKlick()
{
    // Ist der Kunde eingeloggt?
    if (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) {
        // Prüfe ob Kunde schon bestellt hat, falls ja --> Lieferdaten laden
        $oLetzteBestellung = Shop::DB()->query(
            "SELECT tbestellung.kBestellung, tbestellung.kLieferadresse, tbestellung.kZahlungsart, tbestellung.kVersandart
                FROM tbestellung
                JOIN tzahlungsart ON tzahlungsart.kZahlungsart = tbestellung.kZahlungsart
                    AND (tzahlungsart.cKundengruppen IS NULL OR tzahlungsart.cKundengruppen = ''
                    OR tzahlungsart.cKundengruppen LIKE '%{$_SESSION['Kunde']->kKundengruppe};%')
                JOIN tversandart ON tversandart.kVersandart = tbestellung.kVersandart
                    AND tversandart.cKundengruppen = '-1' OR tversandart.cKundengruppen LIKE '%;{$_SESSION['Kunde']->kKundengruppe};%'
                    AND tbestellung.kVersandart = tversandart.kVersandart
                JOIN tversandartzahlungsart ON tversandartzahlungsart.kVersandart = tversandart.kVersandart
                    AND tversandartzahlungsart.kZahlungsart = tzahlungsart.kZahlungsart
                WHERE tbestellung.kKunde = {$_SESSION['Kunde']->kKunde}
                ORDER BY tbestellung.dErstellt
                DESC LIMIT 1", 1
        );

        if (isset($oLetzteBestellung->kBestellung) && $oLetzteBestellung->kBestellung > 0) {
            // Hat der Kunde eine Lieferadresse angegeben?
            if ($oLetzteBestellung->kLieferadresse > 0) {
                $oLieferdaten = Shop::DB()->query(
                    "SELECT kLieferadresse
                        FROM tlieferadresse
                        WHERE kKunde = " . (int)$_SESSION['Kunde']->kKunde . "
                            AND kLieferadresse = " . (int)$oLetzteBestellung->kLieferadresse, 1
                );

                if ($oLieferdaten->kLieferadresse > 0) {
                    $oLieferdaten = new Lieferadresse($oLieferdaten->kLieferadresse);
                    setzeInSession('Lieferadresse', $oLieferdaten);
                    if (!isset($_SESSION['Bestellung'])) {
                        $_SESSION['Bestellung'] = new stdClass();
                    }
                    $_SESSION['Bestellung']->kLieferadresse = $oLetzteBestellung->kLieferadresse;
                    Shop::Smarty()->assign('Lieferadresse', $oLieferdaten);
                }
            } else {
                Shop::Smarty()->assign('Lieferadresse', setzeLieferadresseAusRechnungsadresse());
            }
            pruefeVersandkostenfreiKuponVorgemerkt();
            setzeSteuersaetze();

            // Prüfe Versandart, falls korrekt --> laden
            if ($oLetzteBestellung->kVersandart > 0) {
                if (isset($_SESSION['Versandart'])) {
                    $bVersandart = true;
                } else {
                    $bVersandart = pruefeVersandartWahl($oLetzteBestellung->kVersandart, 0, false);
                }
                if ($bVersandart) {
                    if ($oLetzteBestellung->kZahlungsart > 0) {
                        if (isset($_SESSION['Zahlungsart'])) {
                            return 5;
                        }
                        // Prüfe Zahlungsart
                        $nZahglungsartStatus = zahlungsartKorrekt($oLetzteBestellung->kZahlungsart);
                        if ($nZahglungsartStatus == 2) {
                            // Prüfen ab es ein Trusted Shops Zertifikat gibt
                            $oTrustedShops = new TrustedShops(-1, StringHandler::convertISO2ISO639($_SESSION['cISOSprache']));
                            if (strlen($oTrustedShops->tsId) > 0) {
                                return 4;
                            } else {
                                gibStepZahlung();

                                return 5;
                            }
                        } else {
                            unset($_SESSION['Zahlungsart']);

                            return 4;
                        }
                    } else {
                        unset($_SESSION['Zahlungsart']);

                        return 4;
                    }
                } else {
                    return 3;
                }
            } else {
                return 3;
            }
        }

        return 2;
    }

    return 0;
}

/**
 *
 */
function ladeAjaxEinKlick()
{
    global $aFormValues;

    gibKunde();
    gibFormularDaten();
    gibStepLieferadresse();
    gibStepVersand();
    gibStepZahlung();
    gibStepBestaetigung($aFormValues);

    Shop::Smarty()->assign('L_CHECKOUT_ACCEPT_AGB', Shop::Lang()->get('acceptAgb', 'checkout'))
        ->assign('AGB', gibAGBWRB(Shop::$kSprache, $_SESSION['Kundengruppe']->kKundengruppe))
        ->assign('WarensummeLocalized', $_SESSION['Warenkorb']->gibGesamtsummeWarenLocalized())
        ->assign('Warensumme', $_SESSION['Warenkorb']->gibGesamtsummeWaren());
}

/**
 * @param string $cUserLogin
 * @param string $cUserPass
 * @return int
 */
function plausiAccountwahlLogin($cUserLogin, $cUserPass)
{
    global $Kunde;
    if (strlen($cUserLogin) > 0 && strlen($cUserPass) > 0) {
        $Kunde = new Kunde();
        $Kunde->holLoginKunde($cUserLogin, $cUserPass);
        if ($Kunde->kKunde > 0) {
            return 10;
        }

        return 2;
    }

    return 1;
}

/**
 * @param Kunde $oKunde
 * @return bool
 */
function setzeSesssionAccountwahlLogin($oKunde)
{
    if (isset($oKunde->kKunde) && $oKunde->kKunde > 0) {
        //in tbesucher kKunde setzen
        if (isset($_SESSION['oBesucher']->kBesucher) && $_SESSION['oBesucher']->kBesucher > 0) {
            $_upd              = new stdClass();
            $_upd->kKunde      = (int)$oKunde->kKunde;
            Shop::DB()->update('tbesucher', 'kBesucher', (int)$_SESSION['oBesucher']->kBesucher, $_upd);
        }
        $_SESSION['Warenkorb']->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR)
                              ->loescheSpezialPos(C_WARENKORBPOS_TYP_NEUKUNDENKUPON)
                              ->loescheSpezialPos(C_WARENKORBPOS_TYP_KUPON)
                              ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR);
        unset($_SESSION['Zahlungsart']);
        unset($_SESSION['Versandart']);
        unset($_SESSION['Lieferadresse']);
        unset($_SESSION['ks']);
        unset($_SESSION['VersandKupon']);
        unset($_SESSION['oVersandfreiKupon']);
        unset($_SESSION['NeukundenKupon']);
        unset($_SESSION['Kupon']);
        $oKunde->angezeigtesLand = ISO2land($oKunde->cLand);
        $session                 = Session::getInstance();
        $session->setCustomer($oKunde);

        return true;
    }

    return false;
}

/**
 *
 */
function setzeSmartyAccountwahl()
{
    Shop::Smarty()->assign('untertitel', lang_warenkorb_bestellungEnthaeltXArtikel($_SESSION['Warenkorb']));
}

/**
 * @param string $cFehler
 */
function setzeFehlerSmartyAccountwahl($cFehler)
{
    Shop::Smarty()->assign('hinweis', $cFehler);
}

/**
 * @param array $cPost_arr
 * @param int   $nUnreg
 * @return array
 */
function plausiRechnungsadresse($cPost_arr, $nUnreg = 0)
{
    if ($nUnreg) {
        $cFehlendeEingaben_arr = checkKundenFormular(0);
    } else {
        $cFehlendeEingaben_arr = checkKundenFormular(0, 1);
    }
    if (angabenKorrekt($cFehlendeEingaben_arr)) {
        return $cFehlendeEingaben_arr;
    }

    return $cFehlendeEingaben_arr;
}

/**
 * @param array $cPost_arr
 * @param array $cFehlendeEingaben_arr
 * @return bool
 */
function setzeSessionRechnungsadresse($cPost_arr, $cFehlendeEingaben_arr)
{
    $oKunde              = getKundendaten($cPost_arr, 0);
    $cKundenattribut_arr = getKundenattribute($cPost_arr);
    if (count($cFehlendeEingaben_arr) === 0) {
        //selbstdef. Kundenattr in session setzen
        $oKunde->cKundenattribut_arr = $cKundenattribut_arr;
        $oKunde->nRegistriert        = 0;
        setzeInSession('Kunde', $oKunde);

        if ((isset($_SESSION['Warenkorb']->kWarenkorb)) && $_SESSION['Warenkorb']->gibAnzahlArtikelExt(array(C_WARENKORBPOS_TYP_ARTIKEL)) > 0) {
            if ($_SESSION['Bestellung']->kLieferadresse == 0 && $_SESSION['Lieferadresse']) {
                setzeLieferadresseAusRechnungsadresse();
            }
            setzeSteuersaetze();
            $_SESSION['Warenkorb']->gibGesamtsummeWarenLocalized();
        }

        return true;
    }

    return false;
}

/**
 * @param int $nUnreg
 * @param int $nCheckout
 */
function setzeSmartyRechnungsadresse($nUnreg, $nCheckout = 0)
{
    global $step;
    $conf      = Shop::getSettings(array(CONF_KUNDEN));
    $herkunfte = Shop::DB()->query("SELECT * FROM tkundenherkunft ORDER BY nSort", 2);
    if ($nUnreg) {
        Shop::Smarty()->assign('step', 'formular');
    } else {
        $_POST['editRechnungsadresse'] = 1;
        Shop::Smarty()->assign('editRechnungsadresse', 1)
            ->assign('step', 'rechnungsdaten');
        $step = 'rechnungsdaten';
    }
    Shop::Smarty()->assign('untertitel', Shop::Lang()->get('fillUnregForm', 'checkout'))
        ->assign('herkunfte', $herkunfte)
        ->assign('Kunde', $_SESSION['Kunde'])
        ->assign('laender', gibBelieferbareLaender($_SESSION['Kundengruppe']->kKundengruppe))
        ->assign('oKundenfeld_arr', gibSelbstdefKundenfelder());
    if (is_array($_SESSION['Kunde']->cKundenattribut_arr)) {
        Shop::Smarty()->assign('cKundenattribut_arr', $_SESSION['Kunde']->cKundenattribut_arr);
    } else {
        $_SESSION['Kunde']->cKundenattribut_arr = getKundenattribute($_POST);
        Shop::Smarty()->assign('cKundenattribut_arr', $_SESSION['Kunde']->cKundenattribut_arr);
    }
    if (preg_match('/^\d{4}\-\d{2}\-(\d{2})$/', $_SESSION['Kunde'])) {
        list($jahr, $monat, $tag)       = explode('-', $_SESSION['Kunde']);
        $_SESSION['Kunde']->dGeburtstag = $tag . '.' . $monat . '.' . $jahr;
    }
    Shop::Smarty()->assign('warning_passwortlaenge', lang_passwortlaenge($conf['kunden']['kundenregistrierung_passwortlaenge']));
    if (intval($nCheckout) === 1) {
        Shop::Smarty()->assign('checkout', 1);
    }
}

/**
 * @param array $cFehlendeEingaben_arr
 * @param int   $nUnreg
 * @param array $cPost_arr
 */
function setzeFehlerSmartyRechnungsadresse($cFehlendeEingaben_arr, $nUnreg = 0, $cPost_arr)
{
    $conf = Shop::getSettings(array(CONF_KUNDEN));
    Shop::Smarty()->assign('fehlendeAngaben', $cFehlendeEingaben_arr);
    $herkunfte = Shop::DB()->query(
        "SELECT *
            FROM tkundenherkunft
            ORDER BY nSort", 2
    );
    $oKunde_tmp = getKundendaten($cPost_arr, 0);
    if (preg_match('/^\d{4}\-\d{2}\-(\d{2})$/', $oKunde_tmp->dGeburtstag)) {
        list($jahr, $monat, $tag) = explode('-', $oKunde_tmp->dGeburtstag);
        $oKunde_tmp->dGeburtstag  = $tag . '.' . $monat . '.' . $jahr;
    }
    Shop::Smarty()->assign('untertitel', Shop::Lang()->get('fillUnregForm', 'checkout'))
        ->assign('herkunfte', $herkunfte)
        ->assign('Kunde', $oKunde_tmp)
        ->assign('laender', gibBelieferbareLaender($_SESSION['Kundengruppe']->kKundengruppe))
        ->assign('oKundenfeld_arr', gibSelbstdefKundenfelder())
        ->assign('warning_passwortlaenge', lang_passwortlaenge($conf['kunden']['kundenregistrierung_passwortlaenge']));
    if (is_array($_SESSION['Kunde']->cKundenattribut_arr)) {
        Shop::Smarty()->assign('cKundenattribut_arr', $_SESSION['Kunde']->cKundenattribut_arr);
    }
    if ($nUnreg) {
        Shop::Smarty()->assign('step', 'formular');
    } else {
        Shop::Smarty()->assign('editRechnungsadresse', 1);
    }
}

/**
 * @param array $cPost_arr
 * @return array
 */
function plausiLieferadresse($cPost_arr)
{
    $cFehlendeEingaben_arr                  = array();
    $_SESSION['Bestellung']->kLieferadresse = intval($cPost_arr['kLieferadresse']);
    //neue lieferadresse
    if (intval($cPost_arr['kLieferadresse']) === -1) {
        $cFehlendeAngaben_arr = checkLieferFormular();
        if (angabenKorrekt($cFehlendeAngaben_arr)) {
            return $cFehlendeEingaben_arr;
        } else {
            return $cFehlendeAngaben_arr;
        }
    } elseif (intval($cPost_arr['kLieferadresse']) > 0) {
        //vorhandene lieferadresse
        $oLieferadresse = Shop::DB()->query(
            "SELECT kLieferadresse
				FROM tlieferadresse
				WHERE kKunde = " . intval($_SESSION['Kunde']->kKunde) . "
				AND kLieferadresse = " . intval($cPost_arr['kLieferadresse']), 1
        );
        if ($oLieferadresse->kLieferadresse > 0) {
            $oLieferadresse = new Lieferadresse($oLieferadresse->kLieferadresse);
            setzeInSession('Lieferadresse', $oLieferadresse);
        }
    } //lieferadresse gleich rechnungsadresse
    elseif (intval($cPost_arr['kLieferadresse']) === 0) {
        setzeLieferadresseAusRechnungsadresse();
    }
    setzeSteuersaetze();
    //lieferland hat sich geändert und versandart schon gewählt?
    if ($_SESSION['Lieferadresse'] && $_SESSION['Versandart']) {
        $delVersand = false;
        if (!stristr($_SESSION['Versandart']->cLaender, $_SESSION['Lieferadresse']->cLand)) {
            $delVersand = true;
        }
        //ist die plz im zuschlagsbereich?
        $plz   = Shop::DB()->escape($_SESSION['Lieferadresse']->cPLZ);
        $plz_x = Shop::DB()->query(
            "SELECT kVersandzuschlagPlz
                FROM tversandzuschlagplz, tversandzuschlag
                WHERE tversandzuschlag.kVersandart = " . (int)$_SESSION['Versandart']->kVersandart . " AND
                    tversandzuschlag.kVersandzuschlag = tversandzuschlagplz.kVersandzuschlag AND
                    ((tversandzuschlagplz.cPLZAb <= '" . $plz . "'
                    AND tversandzuschlagplz.cPLZBis >= '" . $plz . "')
                    OR tversandzuschlagplz.cPLZ = '" . $plz . "')", 1
        );
        if (isset($plz_x->kVersandzuschlagPlz) && $plz_x->kVersandzuschlagPlz) {
            $delVersand = true;
        }
        if ($delVersand) {
            $_SESSION['Warenkorb']->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
                                  ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG)
                                  ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR)
                                  ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
                                  ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
                                  ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR);
            unset($_SESSION['Versandart']);
            unset($_SESSION['Zahlungsart']);
        }
        if (!$delVersand) {
            $_SESSION['Warenkorb']->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG);
        }
    }

    return $cFehlendeEingaben_arr;
}

/**
 * @param array $cPost_arr
 */
function setzeSessionLieferadresse($cPost_arr)
{
    $kLieferadresse = (isset($cPost_arr['kLieferadresse'])) ?
        (int)$cPost_arr['kLieferadresse'] :
        -1;
    $_SESSION['Bestellung']->kLieferadresse = $kLieferadresse;
    //neue lieferadresse
    if ($kLieferadresse === -1) {
        $Lieferadresse = getLieferdaten($cPost_arr);
        setzeInSession('Lieferadresse', $Lieferadresse);
    } elseif ($kLieferadresse > 0) {
        //vorhandene lieferadresse
        $LA = Shop::DB()->query(
            "SELECT kLieferadresse
				FROM tlieferadresse
				WHERE kKunde = " . (int)$_SESSION['Kunde']->kKunde . "
				AND kLieferadresse = " . (int)$cPost_arr['kLieferadresse'], 1
        );
        if ($LA->kLieferadresse > 0) {
            $oLieferadresse = new Lieferadresse($LA->kLieferadresse);
            setzeInSession('Lieferadresse', $oLieferadresse);
        }
    } elseif ($kLieferadresse === 0) { //lieferadresse gleich rechnungsadresse
        setzeLieferadresseAusRechnungsadresse();
    }
    setzeSteuersaetze();
    //guthaben
    if (intval($cPost_arr['guthabenVerrechnen']) === 1) {
        $_SESSION['Bestellung']->GuthabenNutzen   = 1;
        $_SESSION['Bestellung']->fGuthabenGenutzt = min($_SESSION['Kunde']->fGuthaben, $_SESSION['Warenkorb']->gibGesamtsummeWaren(true, false));
    } else {
        unset($_SESSION['Bestellung']->GuthabenNutzen);
        unset($_SESSION['Bestellung']->fGuthabenGenutzt);
    }
}

/**
 *
 */
function setzeSmartyLieferadresse()
{
    $kKundengruppe = (int)$_SESSION['Kundengruppe']->kKundengruppe;
    if ($_SESSION['Kunde']->kKunde > 0) {
        $Lieferadressen      = array();
        $oLieferdatenTMP_arr = Shop::DB()->query(
            "SELECT kLieferadresse
                FROM tlieferadresse
                WHERE kKunde = " . (int)$_SESSION['Kunde']->kKunde, 2
        );
        if (is_array($oLieferdatenTMP_arr) && count($oLieferdatenTMP_arr) > 0) {
            foreach ($oLieferdatenTMP_arr as $oLieferdatenTMP) {
                if ($oLieferdatenTMP->kLieferadresse > 0) {
                    $Lieferadressen[] = new Lieferadresse($oLieferdatenTMP->kLieferadresse);
                }
            }
        }
        $kKundengruppe = $_SESSION['Kunde']->kKundengruppe;
        Shop::Smarty()->assign('Lieferadressen', $Lieferadressen)
            ->assign('GuthabenLocalized', $_SESSION['Kunde']->gibGuthabenLocalized());
    }
    Shop::Smarty()->assign('laender', gibBelieferbareLaender($kKundengruppe))
        ->assign('Kunde', $_SESSION['Kunde'])
        ->assign('KuponMoeglich', kuponMoeglich())
        ->assign('kLieferadresse', $_SESSION['Bestellung']->kLieferadresse);
    if ($_SESSION['Bestellung']->kLieferadresse == -1) {
        Shop::Smarty()->assign('Lieferadresse', null);
    }
}

/**
 * @param array $cFehlendeEingaben_arr
 * @param array $cPost_arr
 */
function setzeFehlerSmartyLieferadresse($cFehlendeEingaben_arr, $cPost_arr)
{
    $kKundengruppe = (int)$_SESSION['Kundengruppe']->kKundengruppe;
    if ($_SESSION['Kunde']->kKunde > 0) {
        $Lieferadressen      = array();
        $oLieferdatenTMP_arr = Shop::DB()->query(
            "SELECT kLieferadresse
                FROM tlieferadresse
                WHERE kKunde = " . (int)$_SESSION['Kunde']->kKunde, 2
        );
        if (is_array($oLieferdatenTMP_arr) && count($oLieferdatenTMP_arr) > 0) {
            foreach ($oLieferdatenTMP_arr as $oLieferdatenTMP) {
                if ($oLieferdatenTMP->kLieferadresse > 0) {
                    $Lieferadressen[] = new Lieferadresse($oLieferdatenTMP->kLieferadresse);
                }
            }
        }

        $kKundengruppe = $_SESSION['Kunde']->kKundengruppe;
        Shop::Smarty()->assign('Lieferadressen', $Lieferadressen)
            ->assign('GuthabenLocalized', $_SESSION['Kunde']->gibGuthabenLocalized());
    }
    Shop::Smarty()->assign('laender', gibBelieferbareLaender($kKundengruppe))
        ->assign('Kunde', $_SESSION['Kunde'])
        ->assign('KuponMoeglich', kuponMoeglich())
        ->assign('kLieferadresse', $_SESSION['Bestellung']->kLieferadresse)
        ->assign('kLieferadresse', $cPost_arr['kLieferadresse'])
        ->assign('fehlendeAngaben', $cFehlendeEingaben_arr);
    if ($_SESSION['Bestellung']->kLieferadresse == -1) {
        Shop::Smarty()->assign('Lieferadresse', mappeLieferadresseKontaktdaten($cPost_arr));
    }
}

/**
 * @param array $Lieferadresse_arr
 * @return stdClass
 */
function mappeLieferadresseKontaktdaten($Lieferadresse_arr)
{
    $oLieferadresseFormular                = new stdClass();
    $oLieferadresseFormular->cAnrede       = $Lieferadresse_arr['anrede'];
    $oLieferadresseFormular->cTitel        = $Lieferadresse_arr['titel'];
    $oLieferadresseFormular->cVorname      = $Lieferadresse_arr['vorname'];
    $oLieferadresseFormular->cNachname     = $Lieferadresse_arr['nachname'];
    $oLieferadresseFormular->cFirma        = $Lieferadresse_arr['firma'];
    $oLieferadresseFormular->cZusatz       = $Lieferadresse_arr['firmazusatz'];
    $oLieferadresseFormular->cStrasse      = $Lieferadresse_arr['strasse'];
    $oLieferadresseFormular->cHausnummer   = $Lieferadresse_arr['hausnummer'];
    $oLieferadresseFormular->cAdressZusatz = $Lieferadresse_arr['adresszusatz'];
    $oLieferadresseFormular->cPLZ          = $Lieferadresse_arr['plz'];
    $oLieferadresseFormular->cOrt          = $Lieferadresse_arr['ort'];
    $oLieferadresseFormular->cBundesland   = $Lieferadresse_arr['bundesland'];
    $oLieferadresseFormular->cLand         = $Lieferadresse_arr['land'];
    $oLieferadresseFormular->cMail         = $Lieferadresse_arr['email'];
    $oLieferadresseFormular->cTel          = $Lieferadresse_arr['tel'];
    $oLieferadresseFormular->cMobil        = $Lieferadresse_arr['mobil'];
    $oLieferadresseFormular->cFax          = $Lieferadresse_arr['fax'];

    return $oLieferadresseFormular;
}

/**
 *
 */
function setzeSmartyVersandart()
{
    gibStepVersand();
}

/**
 *
 */
function setzeFehlerSmartyVersandart()
{
    Shop::Smarty()->assign('hinweis', Shop::Lang()->get('fillShipping', 'checkout'));
}

/**
 * @param Zahlungsart $oZahlungsart
 * @param array       $cPost_arr
 * @return array
 */
function plausiZahlungsartZusatz($oZahlungsart, $cPost_arr)
{
    $conf            = Shop::getSettings(array(CONF_TRUSTEDSHOPS));
    $zahlungsangaben = zahlungsartKorrekt(intval($oZahlungsart->kZahlungsart));
    // Trusted Shops
    if ($conf['trustedshops']['trustedshops_nutzen'] === 'Y' && intval($cPost_arr['bTS']) === 1 && $zahlungsangaben > 0 && $_SESSION['Zahlungsart']->nWaehrendBestellung == 0) {
        $fNetto        = $_SESSION['TrustedShops']->oKaeuferschutzProduktIDAssoc_arr[StringHandler::htmlentities(StringHandler::filterXSS($cPost_arr['cKaeuferschutzProdukt']))];
        $kSteuerklasse = $_SESSION['Warenkorb']->gibVersandkostenSteuerklasse();
        $fPreis        = $fNetto;
        if (!$_SESSION['Kundengruppe']->nNettoPreise) {
            $fPreis = $fNetto * ((100 + doubleval($_SESSION['Steuersatz'][$kSteuerklasse])) / 100);
        }
        $cName['ger']                                    = Shop::Lang()->get('trustedshopsName', 'global');
        $cName['eng']                                    = Shop::Lang()->get('trustedshopsName', 'global');
        $_SESSION['TrustedShops']->cKaeuferschutzProdukt = StringHandler::htmlentities(StringHandler::filterXSS($cPost_arr['cKaeuferschutzProdukt']));
        $_SESSION['Warenkorb']->erstelleSpezialPos($cName, 1, $fPreis, $kSteuerklasse, C_WARENKORBPOS_TYP_TRUSTEDSHOPS, true, true);
    }

    return checkAdditionalPayment($oZahlungsart);
}

/**
 * @param array     $cPost_arr
 * @param int|array $cFehlendeEingaben_arr
 */
function setzeSmartyZahlungsartZusatz($cPost_arr, $cFehlendeEingaben_arr = 0)
{
    $Zahlungsart = gibZahlungsart(intval($cPost_arr['Zahlungsart']));
    // Wenn Zahlungsart = Lastschrift ist => versuche Kundenkontodaten zu holen
    $oKundenKontodaten = gibKundenKontodaten($_SESSION['Kunde']->kKunde);
    if (!empty($oKundenKontodaten->kKunde)) {
        Shop::Smarty()->assign('oKundenKontodaten', $oKundenKontodaten);
    }
    if (empty($cPost_arr['zahlungsartzusatzschritt'])) {
        Shop::Smarty()->assign('ZahlungsInfo', $_SESSION['Zahlungsart']->ZahlungsInfo);
    } else {
        Shop::Smarty()->assign('fehlendeAngaben', $cFehlendeEingaben_arr)
            ->assign('ZahlungsInfo', gibPostZahlungsInfo());
    }
    Shop::Smarty()->assign('Zahlungsart', $Zahlungsart)
        ->assign('Kunde', $_SESSION['Kunde'])
        ->assign('Lieferadresse', $_SESSION['Lieferadresse']);
}

/**
 *
 */
function setzeFehlerSmartyZahlungsart()
{
    gibStepZahlung();
    Shop::Smarty()->assign('hinweis', Shop::Lang()->get('fillPayment', 'checkout'));
}

/**
 *
 */
function setzeSmartyBestaetigung()
{
    Shop::Smarty()->assign('Kunde', $_SESSION['Kunde'])
        ->assign('Lieferadresse', $_SESSION['Lieferadresse'])
        ->assign('L_CHECKOUT_ACCEPT_AGB', Shop::Lang()->get('acceptAgb', 'checkout'))
        ->assign('AGB', gibAGBWRB(Shop::$kSprache, $_SESSION['Kundengruppe']->kKundengruppe))
        ->assign('WarensummeLocalized', $_SESSION['Warenkorb']->gibGesamtsummeWarenLocalized())
        ->assign('Warensumme', $_SESSION['Warenkorb']->gibGesamtsummeWaren());
    // SafetyPay Work Around
    if ($_SESSION['Zahlungsart']->cModulId === 'za_safetypay') {
        require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'safetypay/safetypay.php';
        $conf = Shop::getSettings(array(CONF_ZAHLUNGSARTEN));
        Shop::Smarty()->assign('safetypay_form', gib_safetypay_form($_SESSION['Kunde'], $_SESSION['Warenkorb'], $conf['zahlungsarten']));
    }
}

/**
 * Globale Funktionen
 */
function globaleAssigns()
{
    global $step, $hinweis, $Einstellungen, $AktuelleSeite;
    //specific assigns
    Shop::Smarty()->assign('Navigation', createNavigation($AktuelleSeite))
        ->assign('AGB', gibAGBWRB(Shop::$kSprache, $_SESSION['Kundengruppe']->kKundengruppe))
        ->assign('Ueberschrift', Shop::Lang()->get('orderStep0Title', 'checkout'))
        ->assign('UeberschriftKlein', Shop::Lang()->get('orderStep0Title2', 'checkout'))
        ->assign('Einstellungen', $Einstellungen)
        ->assign('hinweis', $hinweis)
        ->assign('step', $step)
        ->assign('WarensummeLocalized', $_SESSION['Warenkorb']->gibGesamtsummeWarenLocalized())
        ->assign('Warensumme', $_SESSION['Warenkorb']->gibGesamtsummeWaren())
        ->assign('Steuerpositionen', $_SESSION['Warenkorb']->gibSteuerpositionen())
        ->assign('bestellschritt', gibBestellschritt($step))
        ->assign('sess', $_SESSION);
}

/**
 * @param int $nStep
 */
function loescheSession($nStep)
{
    switch ($nStep) {
        case 0;
            unset($_SESSION['Kunde']);
            unset($_SESSION['Lieferadresse']);
            unset($_SESSION['Versandart']);
            unset($_SESSION['oVersandfreiKupon']);
            unset($_SESSION['Zahlungsart']);
            unset($_SESSION['TrustedShops']);
            $_SESSION['Warenkorb']->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
                                  ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG)
                                  ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG)
                                  ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
                                  ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
                                  ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR)
                                  ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR)
                                  ->loescheSpezialPos(C_WARENKORBPOS_TYP_TRUSTEDSHOPS);
            break;

        case 1 :
            unset($_SESSION['Lieferadresse']);
            unset($_SESSION['Versandart']);
            unset($_SESSION['oVersandfreiKupon']);
            unset($_SESSION['Zahlungsart']);
            unset($_SESSION['TrustedShops']);
            $_SESSION['Warenkorb']->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
                                  ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG)
                                  ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG)
                                  ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
                                  ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
                                  ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR)
                                  ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR)
                                  ->loescheSpezialPos(C_WARENKORBPOS_TYP_TRUSTEDSHOPS);
            break;

        case 2 :
            unset($_SESSION['Lieferadresse']);
            unset($_SESSION['Versandart']);
            unset($_SESSION['oVersandfreiKupon']);
            $_SESSION['Warenkorb']->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
                                  ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG)
                                  ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG)
                                  ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
                                  ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
                                  ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR)
                                  ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR)
                                  ->loescheSpezialPos(C_WARENKORBPOS_TYP_TRUSTEDSHOPS);
            unset($_SESSION['TrustedShops']);
            unset($_SESSION['Zahlungsart']);
            break;

        case 3 :
            unset($_SESSION['Versandart']);
            unset($_SESSION['oVersandfreiKupon']);
            unset($_SESSION['Zahlungsart']);
            unset($_SESSION['TrustedShops']);
            $_SESSION['Warenkorb']->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
                                  ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG)
                                  ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG)
                                  ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
                                  ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
                                  ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR)
                                  ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR)
                                  ->loescheSpezialPos(C_WARENKORBPOS_TYP_TRUSTEDSHOPS);
            break;

        case 4 :
            unset($_SESSION['Zahlungsart']);
            unset($_SESSION['TrustedShops']);
            $_SESSION['Warenkorb']->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
                                  ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
                                  ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR)
                                  ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR)
                                  ->loescheSpezialPos(C_WARENKORBPOS_TYP_TRUSTEDSHOPS);
            break;

        default:
            break;
    }
}

/**
 * @param int $nHinweisCode
 * @return string
 */
function mappeBestellvorgangZahlungshinweis($nHinweisCode)
{
    $cHinweis = '';
    if (intval($nHinweisCode) > 0) {
        switch ($nHinweisCode) {
            // 1-30 EOS
            case 1 : // EOS_BACKURL_CODE
                $cHinweis = Shop::Lang()->get('eosErrorBack', 'checkout');
                break;

            case 3 : // EOS_FAILURL_CODE
                $cHinweis = Shop::Lang()->get('eosErrorFailure', 'checkout');
                break;

            case 4 : // EOS_ERRORURL_CODE
                $cHinweis = Shop::Lang()->get('eosErrorError', 'checkout');
                break;

        }
    }

    executeHook(HOOK_BESTELLVORGANG_INC_MAPPEBESTELLVORGANGZAHLUNGSHINWEIS, array('cHinweis' => &$cHinweis, 'nHinweisCode' => $nHinweisCode));

    return $cHinweis;
}

/**
 * @param string $email
 * @return bool
 */
function isEmailAvailable($email)
{
    $email = StringHandler::filterXSS(Shop::DB()->escape($email));
    if (strlen($email) > 0) {
        $obj = Shop::DB()->query("SELECT count(*) AS count FROM tkunde WHERE cMail = '{$email}' AND cPasswort != ''", 1);

        return ($obj->count == 0);
    }

    return false;
}

/**
 * @param string $datum
 * @return string
 */
function convertDate2German($datum)
{
    if (is_string($datum)) {
        list($tag, $monat, $jahr) = explode('.', $datum);
        if ($tag && $monat && $jahr) {
            return $jahr . '-' . $monat . '-' . $tag;
        }
    }

    return $datum;
}

/**
 * @param Zahlungsart $Zahlungsart
 * @return null
 */
function gibSpecials($Zahlungsart)
{
    $specials = null;
    switch ($Zahlungsart->cModulId) {
        case 'externesModul':
            break;
    }

    return $specials;
}

/**
 * @param Zahlungsart $Zahlungsart
 * @return int|mixed
 * @deprecated since 4.0
 */
function gibIloxxAufpreis($Zahlungsart)
{
    $fWarenwert = $_SESSION['Warenkorb']->gibGesamtsummeWaren(true);
    $fKosten    = 0;
    for ($i = 8; $i >= 1; $i--) {
        list($fSumme, $fTmpKosten) = explode(';', $Zahlungsart->einstellungen['zahlungsart_iloxx_staffel' . $i]);
        $fTmpKosten                = str_replace(',', '.', $fTmpKosten);
        if ($fSumme >= $fWarenwert) {
            $fKosten = $fTmpKosten;
        }
    }

    return $fKosten;
}

/**
 * @param array $cPost_arr
 * @return int
 * @deprecated since 4.0
 */
function plausiZahlungsart($cPost_arr)
{
    return pruefeZahlungsartwahlStep($cPost_arr);
}

/**
 * @param int   $kVersandart
 * @param array $cPost_arr
 * @return bool
 * @deprecated since 4.0
 */
function plausiVersandart($kVersandart, $cPost_arr)
{
    if (versandartKorrekt(intval($kVersandart), $cPost_arr)) {
        return true;
    }

    return false;
}

/**
 * @deprecated since 4.0
 */
function setzeSmartyZahlungsart()
{
    gibStepZahlung();
}

/**
 * @param Zahlungsart $Zahlungsart
 * @return array
 * @deprecated since 4.0
 */
function gibFehlendeAngabenZahlungsart($Zahlungsart)
{
    return checkAdditionalPayment($Zahlungsart);
}

/**
 * @param Zahlungsart $Zahlungsart
 * @deprecated since 4.0
 */
function setzeSessionZahlungsart($Zahlungsart)
{
}
