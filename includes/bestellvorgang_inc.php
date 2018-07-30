<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 *
 */
function pruefeBestellungMoeglich()
{
    header('Location: ' . Shop::Container()->getLinkService()->getStaticRoute('warenkorb.php', true) .
        '?fillOut=' . Session::Cart()->istBestellungMoeglich(), true, 303);
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

    $nReturnValue = versandartKorrekt($Versandart, $aFormValues);
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
    unset($_SESSION['Lieferadresse'], $_SESSION['Versandart'], $_SESSION['Zahlungsart']);
    $cart = Session::Cart();
    $cart->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
         ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART);
    $step = 'edit_customer_address';
    unset($_SESSION['Kunde']);
    $fehlendeAngaben     = checkKundenFormular(0);
    $Kunde               = getKundendaten($cPost_arr, 0);
    $cKundenattribut_arr = getKundenattribute($cPost_arr);
    $kKundengruppe       = Session::CustomerGroup()->getID();
    // CheckBox Plausi
    $oCheckBox       = new CheckBox();
    $fehlendeAngaben = array_merge($fehlendeAngaben, $oCheckBox->validateCheckBox(
        CHECKBOX_ORT_REGISTRIERUNG,
        $kKundengruppe,
        $cPost_arr,
        true
    ));
    $nReturnValue    = angabenKorrekt($fehlendeAngaben);

    executeHook(HOOK_BESTELLVORGANG_INC_UNREGISTRIERTBESTELLEN_PLAUSI, [
        'nReturnValue'    => &$nReturnValue,
        'fehlendeAngaben' => &$fehlendeAngaben,
        'Kunde'           => &$Kunde,
        'cPost_arr'       => &$cPost_arr
    ]);

    if ($nReturnValue) {
        // CheckBox Spezialfunktion ausführen
        $oCheckBox->triggerSpecialFunction(
            CHECKBOX_ORT_REGISTRIERUNG,
            $kKundengruppe,
            true,
            $cPost_arr,
            ['oKunde' => $Kunde]
        )->checkLogging(CHECKBOX_ORT_REGISTRIERUNG, $kKundengruppe, $cPost_arr, true);
        //selbstdef. Kundenattr in session setzen
        $Kunde->cKundenattribut_arr = $cKundenattribut_arr;
        $Kunde->nRegistriert        = 0;
        $_SESSION['Kunde']          = $Kunde;
        if (isset($_SESSION['Warenkorb']->kWarenkorb)
            && $cart->gibAnzahlArtikelExt([C_WARENKORBPOS_TYP_ARTIKEL]) > 0
        ) {
            if (isset($_SESSION['Lieferadresse']) && $_SESSION['Bestellung']->kLieferadresse == 0) {
                setzeLieferadresseAusRechnungsadresse();
            }
            TaxHelper::setTaxRates();
            $cart->gibGesamtsummeWarenLocalized();
        }
        if (isset($cPost_arr['shipping_address'])) {
            if ((int)$cPost_arr['shipping_address'] === 0) {
                $cPost_arr['kLieferadresse'] = 0;
                $cPost_arr['lieferdaten']    = 1;
                pruefeLieferdaten($cPost_arr);
            } elseif (isset($cPost_arr['kLieferadresse']) && (int)$cPost_arr['kLieferadresse'] > 0) {
                pruefeLieferdaten($cPost_arr);
            } elseif (isset($cPost_arr['register']['shipping_address'])) {
                pruefeLieferdaten($cPost_arr['register']['shipping_address'], $fehlendeAngaben);
            }
        } elseif (isset($cPost_arr['lieferdaten']) && (int)$cPost_arr['lieferdaten'] === 1) {
            // compatibility with older template
            pruefeLieferdaten($cPost_arr, $fehlendeAngaben);
        }

        executeHook(HOOK_BESTELLVORGANG_INC_UNREGISTRIERTBESTELLEN);

        return 1;
    }
    setzeFehlendeAngaben($fehlendeAngaben);
    Shop::Smarty()->assign('cKundenattribut_arr', $cKundenattribut_arr)
        ->assign('cPost_var', StringHandler::filterXSS($cPost_arr));

    return 0;
}

/**
 * @param array $cPost_arr
 * @param array|null $fehlendeAngaben
 * @return string
 */
function pruefeLieferdaten($cPost_arr, &$fehlendeAngaben = null)
{
    global $step, $Lieferadresse;
    $step = 'Lieferadresse';
    unset($_SESSION['Lieferadresse']);
    if (!isset($_SESSION['Bestellung'])) {
        $_SESSION['Bestellung'] = new stdClass();
    }
    $_SESSION['Bestellung']->kLieferadresse = isset($cPost_arr['kLieferadresse'])
        ? (int)$cPost_arr['kLieferadresse']
        : -1;
    Session::Cart()->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS);
    unset($_SESSION['Versandart']);
    //neue lieferadresse
    if (!isset($cPost_arr['kLieferadresse']) || (int)$cPost_arr['kLieferadresse'] === -1) {
        $fehlendeAngaben = checkLieferFormular($cPost_arr);
        $Lieferadresse   = getLieferdaten($cPost_arr);
        $nReturnValue    = angabenKorrekt($fehlendeAngaben);

        executeHook(HOOK_BESTELLVORGANG_PAGE_STEPLIEFERADRESSE_NEUELIEFERADRESSE_PLAUSI, [
            'nReturnValue'    => &$nReturnValue,
            'fehlendeAngaben' => &$fehlendeAngaben
        ]);
        if ($nReturnValue) {
            // Anrede mappen
            if ($Lieferadresse->cAnrede === 'm') {
                $Lieferadresse->cAnredeLocalized = Shop::Lang()->get('salutationM');
            } elseif ($Lieferadresse->cAnrede === 'w') {
                $Lieferadresse->cAnredeLocalized = Shop::Lang()->get('salutationW');
            }
            $_SESSION['Lieferadresse'] = $Lieferadresse;
            executeHook(HOOK_BESTELLVORGANG_PAGE_STEPLIEFERADRESSE_NEUELIEFERADRESSE);
            pruefeVersandkostenfreiKuponVorgemerkt();
        } else {
            $_SESSION['Lieferadresse'] = $Lieferadresse;
            setzeFehlendeAngaben($fehlendeAngaben, 'shipping_address');
        }
    } elseif ((int)$cPost_arr['kLieferadresse'] > 0) {
        //vorhandene lieferadresse
        $LA = Shop::Container()->getDB()->query(
            "SELECT kLieferadresse
                FROM tlieferadresse
                WHERE kKunde = " . Session::Customer()->getID() . "
                    AND kLieferadresse = " . (int)$cPost_arr['kLieferadresse'],
            \DB\ReturnType::SINGLE_OBJECT
        );
        if ($LA->kLieferadresse > 0) {
            $oLieferadresse            = new Lieferadresse($LA->kLieferadresse);
            $_SESSION['Lieferadresse'] = $oLieferadresse;

            executeHook(HOOK_BESTELLVORGANG_PAGE_STEPLIEFERADRESSE_VORHANDENELIEFERADRESSE);
        }
    } elseif ((int)$cPost_arr['kLieferadresse'] === 0 && isset($_SESSION['Kunde'])) {
        //lieferadresse gleich rechnungsadresse
        setzeLieferadresseAusRechnungsadresse();

        executeHook(HOOK_BESTELLVORGANG_PAGE_STEPLIEFERADRESSE_RECHNUNGLIEFERADRESSE);
    }
    TaxHelper::setTaxRates();
    //lieferland hat sich geändert und versandart schon gewählt?
    if (isset($_SESSION['Lieferadresse'], $_SESSION['Versandart']) &&
        $_SESSION['Lieferadresse'] &&
        $_SESSION['Versandart']
    ) {
        $delVersand = stripos($_SESSION['Versandart']->cLaender, $_SESSION['Lieferadresse']->cLand) === false;
        //ist die plz im zuschlagsbereich?
        $plz_x = Shop::Container()->getDB()->executeQueryPrepared(
            "SELECT kVersandzuschlagPlz
                FROM tversandzuschlagplz, tversandzuschlag
                WHERE tversandzuschlag.kVersandart = :id
                    AND tversandzuschlag.kVersandzuschlag = tversandzuschlagplz.kVersandzuschlag
                    AND ((tversandzuschlagplz.cPLZAb <= :plz
                    AND tversandzuschlagplz.cPLZBis >= :plz)
                    OR tversandzuschlagplz.cPLZ = :plz)",
            ['plz' => $_SESSION['Lieferadresse']->cPLZ, 'id' => (int)$_SESSION['Versandart']->kVersandart],
            \DB\ReturnType::SINGLE_OBJECT
        );
        if (!empty($plz_x->kVersandzuschlagPlz)) {
            $delVersand = true;
        }
        if ($delVersand) {
            Session::Cart()->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
                   ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG)
                   ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR)
                   ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
                   ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
                   ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR);
            unset($_SESSION['Versandart'], $_SESSION['Zahlungsart']);
        } else {
            Session::Cart()->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG);
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
    if ((isset($_SESSION['Bestellung']->GuthabenNutzen) && (int)$_SESSION['Bestellung']->GuthabenNutzen === 1)
        || (isset($cPost_arr['guthabenVerrechnen']) && (int)$cPost_arr['guthabenVerrechnen'] === 1)
    ) {
        $_SESSION['Bestellung']->GuthabenNutzen   = 1;
        $_SESSION['Bestellung']->fGuthabenGenutzt = min(
            $_SESSION['Kunde']->fGuthaben,
            Session::Cart()->gibGesamtsummeWaren(true, false)
        );
        executeHook(HOOK_BESTELLVORGANG_PAGE_STEPBESTAETIGUNG_GUTHABENVERRECHNEN);
    }
}

/**
 *
 */
function pruefeVersandkostenStep()
{
    global $step;
    if (isset($_SESSION['Kunde'], $_SESSION['Lieferadresse'])) {
        $cart = Session::Cart();
        //artikelabhängige versandkosten
        $cart->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG);
        $arrArtikelabhaengigeVersandkosten = VersandartHelper::gibArtikelabhaengigeVersandkostenImWK(
            $_SESSION['Lieferadresse']->cLand,
            $cart->PositionenArr
        );
        foreach ($arrArtikelabhaengigeVersandkosten as $oVersandPos) {
            $cart->erstelleSpezialPos(
                $oVersandPos->cName,
                1,
                $oVersandPos->fKosten,
                $cart->gibVersandkostenSteuerklasse($_SESSION['Lieferadresse']->cLand),
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
    if (isset($_SESSION['Kunde'], $_SESSION['Lieferadresse'], $_SESSION['Versandart'])) {
        $step = 'Zahlung';
    }
}

/**
 *
 */
function pruefeBestaetigungStep()
{
    global $step;
    if (isset($_SESSION['Kunde'], $_SESSION['Lieferadresse'], $_SESSION['Versandart'], $_SESSION['Zahlungsart'])) {
        $step = 'Bestaetigung';
    }
    if (isset($_SESSION['Zahlungsart'], $_SESSION['Zahlungsart']->cZusatzschrittTemplate) &&
        strlen($_SESSION['Zahlungsart']->cZusatzschrittTemplate) > 0
    ) {
        $paymentMethod = PaymentMethod::create($_SESSION['Zahlungsart']->cModulId);
        if (is_object($paymentMethod) && !$paymentMethod->validateAdditional()) {
            $step = 'Zahlung';
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
        Kupon::resetNewCustomerCoupon();
        if (!isset($cGet_arr['editLieferadresse'])) {
            // Shipping address and customer address are now on same site - check shipping address also
            pruefeLieferadresseStep(['editLieferadresse' => $cGet_arr['editRechnungsadresse']]);
        }
        $Kunde = $_SESSION['Kunde'];
        $step  = 'edit_customer_address';
    }

    if (isset($_SESSION['checkout.register']) && (int)$_SESSION['checkout.register'] === 1) {
        if (isset($_SESSION['checkout.fehlendeAngaben'])) {
            setzeFehlendeAngaben($_SESSION['checkout.fehlendeAngaben']);
            unset($_SESSION['checkout.fehlendeAngaben']);
            $step = 'accountwahl';
        }
        if (isset($_SESSION['checkout.cPost_arr'])) {
            $Kunde                      = getKundendaten($_SESSION['checkout.cPost_arr'], 0, 0);
            $Kunde->cKundenattribut_arr = getKundenattribute($_SESSION['checkout.cPost_arr']);
            Shop::Smarty()->assign('Kunde', $Kunde)
                ->assign('cPost_var', $_SESSION['checkout.cPost_arr']);

            if (isset($_SESSION['Lieferadresse']) && (int)$_SESSION['checkout.cPost_arr']['shipping_address'] !== 0) {
                Shop::Smarty()->assign('Lieferadresse', $_SESSION['Lieferadresse']);
            }

            $_POST = array_merge($_POST, $_SESSION['checkout.cPost_arr']);
            unset($_SESSION['checkout.cPost_arr']);
        }
        unset($_SESSION['checkout.register']);
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
        Kupon::resetNewCustomerCoupon();
        unset($_SESSION['Zahlungsart'], $_SESSION['TrustedShops'], $_SESSION['Versandart']);
        $Lieferadresse = $_SESSION['Lieferadresse'];
        $step          = 'Lieferadresse';
    }
    if (pruefeFehlendeAngaben('shipping_address')) {
        $Lieferadresse = $_SESSION['Lieferadresse'];
        $step          = 'Lieferadresse';
    }
}

/**
 * Prüft ob im WK ein Versandfrei Kupon eingegeben wurde und falls ja,
 * wird dieser nach Eingabe der Lieferadresse gesetzt (falls Kriterien erfüllt)
 *
 * @return array
 */
function pruefeVersandkostenfreiKuponVorgemerkt()
{
    if ((isset($_SESSION['Kupon']) && $_SESSION['Kupon']->cKuponTyp === 'versandkupon')
        || (isset($_SESSION['oVersandfreiKupon']) && $_SESSION['oVersandfreiKupon']->cKuponTyp === 'versandkupon')
    ) {
        Session::Cart()->loescheSpezialPos(C_WARENKORBPOS_TYP_KUPON);
        unset($_SESSION['Kupon']);
    }
    $Kuponfehler = [];
    if (isset($_SESSION['oVersandfreiKupon']->kKupon) && $_SESSION['oVersandfreiKupon']->kKupon > 0) {
        // Wurde im WK ein Versandfreikupon eingegeben?
        $Kuponfehler = Kupon::checkCoupon($_SESSION['oVersandfreiKupon']);
        if (angabenKorrekt($Kuponfehler)) {
            Kupon::acceptCoupon($_SESSION['oVersandfreiKupon']);
            Shop::Smarty()->assign('KuponMoeglich', Kupon::couponsAvailable());
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
    if (isset($cGet_arr['editVersandart'], $_SESSION['Versandart']) && $cGet_arr['editVersandart'] == 1) {
        Kupon::resetNewCustomerCoupon();
        Session::Cart()->loescheSpezialPos(C_WARENKORBPOS_TYP_VERPACKUNG)
               ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
               ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG)
               ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR)
               ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
               ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
               ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR);
        unset($_SESSION['Zahlungsart'], $_SESSION['TrustedShops'], $_SESSION['Versandart']);

        $step = 'Versand';
        pruefeZahlungsartStep(['editZahlungsart' => 1]);
    }
}

/**
 * @param array $cGet_arr
 */
function pruefeZahlungsartStep($cGet_arr)
{
    global $step, $hinweis;
    //sondersteps Zahlungsart ändern
    if (isset($_SESSION['Zahlungsart'], $cGet_arr['editZahlungsart']) && $cGet_arr['editZahlungsart'] == 1) {
        Kupon::resetNewCustomerCoupon();
        Session::Cart()->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
               ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
               ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR)
               ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR);
        unset($_SESSION['Zahlungsart']);
        $step = 'Zahlung';
        pruefeVersandartStep(['editVersandart' => 1]);
    }
    // Hinweis?
    if (isset($cGet_arr['nHinweis']) && (int)$cGet_arr['nHinweis'] > 0) {
        $hinweis = mappeBestellvorgangZahlungshinweis((int)$cGet_arr['nHinweis']);
    }
}

/**
 * @param array $cPost_arr
 * @return int
 */
function pruefeZahlungsartwahlStep($cPost_arr)
{
    global $zahlungsangaben, $hinweis, $step;
    if (isset($cPost_arr['zahlungsartwahl']) && (int)$cPost_arr['zahlungsartwahl'] === 1) {
        $zahlungsangaben = zahlungsartKorrekt($cPost_arr['Zahlungsart']);
        $conf            = Shop::getSettings([CONF_TRUSTEDSHOPS]);
        executeHook(HOOK_BESTELLVORGANG_PAGE_STEPZAHLUNG_PLAUSI);
        // Trusted Shops
        if ($zahlungsangaben > 0
            && $_SESSION['Zahlungsart']->nWaehrendBestellung == 0
            && isset($cPost_arr['bTS'])
            && (int)$cPost_arr['bTS'] === 1
            && $conf['trustedshops']['trustedshops_nutzen'] === 'Y'
        ) {
            $_SESSION['TrustedShops']->cKaeuferschutzProdukt =
                StringHandler::htmlentities(StringHandler::filterXSS($cPost_arr['cKaeuferschutzProdukt']));

            $fNetto        = $_SESSION['TrustedShops']->oKaeuferschutzProduktIDAssoc_arr[StringHandler::htmlentities(
                StringHandler::filterXSS($cPost_arr['cKaeuferschutzProdukt'])
            )];
            $cLandISO      = $_SESSION['Lieferadresse']->cLand ?? '';
            $kSteuerklasse = Session::Cart()->gibVersandkostenSteuerklasse($cLandISO);
            $fPreis        = Session::CustomerGroup()->isMerchant()
                ? $fNetto
                : ($fNetto * ((100 + (float)$_SESSION['Steuersatz'][$kSteuerklasse]) / 100));
            $cName['ger']  = Shop::Lang()->get('trustedshopsName');
            $cName['eng']  = Shop::Lang()->get('trustedshopsName');
            Session::Cart()->erstelleSpezialPos(
                $cName,
                1,
                $fPreis,
                $kSteuerklasse,
                C_WARENKORBPOS_TYP_TRUSTEDSHOPS,
                true,
                !Session::CustomerGroup()->isMerchant()
            );
        }

        switch ($zahlungsangaben) {
            case 0:
                $hinweis = Shop::Lang()->get('fillPayment', 'checkout');
                $step    = 'Zahlung';

                return 0;
            case 1:
                $step = 'ZahlungZusatzschritt';

                return 1;
            case 2:
                $step = 'Bestaetigung';

                return 2;
        }
    }

    return null;
}

/**
 *
 */
function pruefeGuthabenNutzen()
{
    if (isset($_SESSION['Bestellung']->GuthabenNutzen) && $_SESSION['Bestellung']->GuthabenNutzen) {
        $_SESSION['Bestellung']->fGuthabenGenutzt   = min(
            $_SESSION['Kunde']->fGuthaben,
            Session::Cart()->gibGesamtsummeWaren(true, false)
        );
        $_SESSION['Bestellung']->GutscheinLocalized = Preise::getLocalizedPriceString($_SESSION['Bestellung']->fGuthabenGenutzt);
    }

    executeHook(HOOK_BESTELLVORGANG_PAGE_STEPBESTAETIGUNG_GUTHABEN_PLAUSI);
}

/**
 * @param string $context
 * @return bool
 */
function pruefeFehlendeAngaben($context)
{
    $fehlendeAngaben = Shop::Smarty()->getTemplateVars('fehlendeAngaben');

    return (is_array($fehlendeAngaben[$context]) && count($fehlendeAngaben[$context]));
}

/**
 *
 */
function gibStepAccountwahl()
{
    global $hinweis;
    // Einstellung global_kundenkonto_aktiv ist auf 'A' und Kunde wurde nach der Registrierung zurück zur Accountwahl geleitet
    if (isset($_REQUEST['reg']) && (int)$_REQUEST['reg'] === 1) {
        $hinweis = Shop::Lang()->get('accountCreated') . '<br />' . Shop::Lang()->get('loginNotActivated');
    }
    Shop::Smarty()->assign('untertitel', lang_warenkorb_bestellungEnthaeltXArtikel(Session::Cart()));

    executeHook(HOOK_BESTELLVORGANG_PAGE_STEPACCOUNTWAHL);
}

/**
 *
 */
function gibStepUnregistriertBestellen()
{
    global $Kunde;
    $herkunfte = Shop::Container()->getDB()->query(
        "SELECT * 
            FROM tkundenherkunft 
            ORDER BY nSort",
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    if (isset($Kunde->dGeburtstag) && preg_match('/^\d{4}\-\d{2}\-(\d{2})$/', $Kunde->dGeburtstag)) {
        list($jahr, $monat, $tag) = explode('-', $Kunde->dGeburtstag);
        $Kunde->dGeburtstag       = $tag . '.' . $monat . '.' . $jahr;
    }
    Shop::Smarty()->assign('untertitel', Shop::Lang()->get('fillUnregForm', 'checkout'))
        ->assign('herkunfte', $herkunfte)
        ->assign('Kunde', $Kunde ?? null)
        ->assign('laender', VersandartHelper::getPossibleShippingCountries(Session::CustomerGroup()->getID()))
        ->assign('oKundenfeld_arr', gibSelbstdefKundenfelder())
        ->assign('nAnzeigeOrt', CHECKBOX_ORT_REGISTRIERUNG)
        ->assign('code_registrieren', false);
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
        $checkCouponResult = Kupon::checkCoupon($_SESSION['Kupon']);
        if (count($checkCouponResult) !== 0) {
            Session::Cart()->loescheSpezialPos(C_WARENKORBPOS_TYP_KUPON);
            $_SESSION['checkCouponResult'] = $checkCouponResult;
            unset($_SESSION['Kupon']);
            header('Location: ' . Shop::Container()->getLinkService()->getStaticRoute('warenkorb.php', true));
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

    $kKundengruppe = Session::CustomerGroup()->getID();

    if ($_SESSION['Kunde']->kKunde > 0) {
        $Lieferadressen        = [];
        $oLieferadresseTMP_arr = Shop::Container()->getDB()->query(
            "SELECT DISTINCT(kLieferadresse)
                FROM tlieferadresse
                WHERE kKunde = " . Session::Customer()->getID(),
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($oLieferadresseTMP_arr as $oLieferadresseTMP) {
            if ($oLieferadresseTMP->kLieferadresse > 0) {
                $Lieferadressen[] = new Lieferadresse($oLieferadresseTMP->kLieferadresse);
            }
        }
        Shop::Smarty()->assign('Lieferadressen', $Lieferadressen);
        $kKundengruppe = $_SESSION['Kunde']->kKundengruppe;
    }
    Shop::Smarty()->assign('laender', VersandartHelper::getPossibleShippingCountries($kKundengruppe))
        ->assign('Kunde', $_SESSION['Kunde'])
        ->assign('kLieferadresse', $_SESSION['Bestellung']->kLieferadresse ?? null);
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
    global $step, $Einstellungen;
    $cart          = Session::Cart();
    $conf          = Shop::getSettings([CONF_TRUSTEDSHOPS]);
    $oTrustedShops = new stdClass();
    if ($conf['trustedshops']['trustedshops_nutzen'] === 'Y'
        && (!isset($_SESSION['ajaxcheckout']) || $_SESSION['ajaxcheckout']->nEnabled < 5)
    ) {
        unset($_SESSION['TrustedShops']);
        $cart->loescheSpezialPos(C_WARENKORBPOS_TYP_TRUSTEDSHOPS);
        $oTrustedShops = TrustedShops::getTrustedShops();
        if (isset($oTrustedShops->nAktiv)
            && $oTrustedShops->nAktiv == 1
            && $oTrustedShops->eType === TS_BUYERPROT_EXCELLENCE
        ) {
            if (!isset($_SESSION['TrustedShops'])) {
                $_SESSION['TrustedShops'] = new stdClass();
            }
            $_SESSION['TrustedShops']->oKaeuferschutzProduktIDAssoc_arr =
                TrustedShops::gibKaeuferschutzProdukteAssocID($oTrustedShops->oKaeuferschutzProdukte->item);
            Shop::Smarty()->assign('oTrustedShops', $oTrustedShops)
                ->assign('PFAD_GFX_TRUSTEDSHOPS', PFAD_GFX_TRUSTEDSHOPS);
        }
        Shop::Smarty()->assign('URL_SHOP', Shop::getURL());
    }

    $lieferland = $_SESSION['Lieferadresse']->cLand ?? null;
    if (!$lieferland) {
        $lieferland = $_SESSION['Kunde']->cLand;
    }
    $plz = $_SESSION['Lieferadresse']->cPLZ ?? null;
    if (!$plz) {
        $plz = $_SESSION['Kunde']->cPLZ;
    }
    $kKundengruppe = $_SESSION['Kunde']->kKundengruppe ?? null;
    if (!$kKundengruppe) {
        $kKundengruppe = Session::CustomerGroup()->getID();
    }
    $oVersandart_arr = VersandartHelper::getPossibleShippingMethods(
        $lieferland,
        $plz,
        VersandartHelper::getShippingClasses(Session::Cart()),
        $kKundengruppe
    );
    $oVerpackung_arr = VersandartHelper::getPossiblePackagings(Session::CustomerGroup()->getID());

    if (!empty($oVerpackung_arr) && $cart->posTypEnthalten(C_WARENKORBPOS_TYP_VERPACKUNG)) {
        foreach ($cart->PositionenArr as $oPos) {
            if ($oPos->nPosTyp === C_WARENKORBPOS_TYP_VERPACKUNG) {
                foreach ($oVerpackung_arr as $oPack) {
                    if ($oPack->cName === $oPos->cName[$oPack->cISOSprache]) {
                        $oPack->bWarenkorbAktiv = true;
                    }
                }
            }
        }
    }

    if (is_array($oVersandart_arr) && count($oVersandart_arr) > 0) {
        $aktiveVersandart = gibAktiveVersandart($oVersandart_arr);
        $oZahlungsart_arr = gibZahlungsarten($aktiveVersandart, Session::CustomerGroup()->getID());
        if (is_array($oZahlungsart_arr)
            && count($oZahlungsart_arr) === 1
            && !isset($_GET['editZahlungsart'])
            && empty($_SESSION['TrustedShopsZahlung'])
            && isset($_POST['zahlungsartwahl'])
            && (int)$_POST['zahlungsartwahl'] === 1
        ) {
            // Prüfe Zahlungsart
            $nZahglungsartStatus = zahlungsartKorrekt($oZahlungsart_arr[0]->kZahlungsart);
            if ($nZahglungsartStatus === 2) {
                // Prüfen ab es ein Trusted Shops Zertifikat gibt
                if ($conf['trustedshops']['trustedshops_nutzen'] === 'Y') {
                    $oTrustedShops = new TrustedShops(-1, StringHandler::convertISO2ISO639($_SESSION['cISOSprache']));
                }
                if (isset($oTrustedShops->tsId)
                    && $oTrustedShops->eType === TS_BUYERPROT_EXCELLENCE
                    && strlen($oTrustedShops->tsId) > 0
                ) {
                    $_SESSION['TrustedShopsZahlung'] = true;
                    gibStepZahlung();
                }
            }
        } elseif (!is_array($oZahlungsart_arr) || count($oZahlungsart_arr) === 0) {
            Shop::Container()->getLogService()->error(
                'Es konnte keine Zahlungsart für folgende Daten gefunden werden: Versandart: ' .
                $_SESSION['Versandart']->kVersandart . ', Kundengruppe: ' . Session::CustomerGroup()->getID()
            );
        }

        $aktiveVerpackung  = gibAktiveVerpackung($oVerpackung_arr);
        $aktiveZahlungsart = gibAktiveZahlungsart($oZahlungsart_arr);
        if (!isset($_SESSION['Versandart']) && !empty($aktiveVersandart)) {
            // dieser Workaround verhindert die Anzeige der Standardzahlungsarten wenn ein Zahlungsplugin aktiv ist
            $_SESSION['Versandart'] = (object)[
                'kVersandart' => $aktiveVersandart,
            ];
        }

        Shop::Smarty()->assign('Zahlungsarten', $oZahlungsart_arr)
            ->assign('Einstellungen', $Einstellungen)
            ->assign('Versandarten', $oVersandart_arr)
            ->assign('Verpackungsarten', $oVerpackung_arr)
            ->assign('AktiveVersandart', $aktiveVersandart)
            ->assign('AktiveZahlungsart', $aktiveZahlungsart)
            ->assign('AktiveVerpackung', $aktiveVerpackung)
            ->assign('Kunde', $_SESSION['Kunde'])
            ->assign('Lieferadresse', $_SESSION['Lieferadresse']);

        executeHook(HOOK_BESTELLVORGANG_PAGE_STEPZAHLUNG);

        /**
         * This is for compatibility in 3-step checkout and will prevent form in form tags trough payment plugins
         * @see /templates/Evo/checkout/step4_payment_options.tpl
         * ToDo: Replace with more convenient solution in later versions (after 4.06)
         */
        $step4_payment_content = Shop::Smarty()->fetch('checkout/step4_payment_options.tpl');
        if (preg_match('/<form([^>]*)>/', $step4_payment_content, $hits)) {
            $step4_payment_content = str_replace($hits[0], '<div' . $hits[1] . '>', $step4_payment_content);
            $step4_payment_content = str_replace('</form>', '</div>', $step4_payment_content);
        }
        Shop::Smarty()->assign('step4_payment_content', $step4_payment_content);
    }
}

/**
 * @param array $cPost_arr
 */
function gibStepZahlungZusatzschritt($cPost_arr)
{
    $Zahlungsart = gibZahlungsart((int)$cPost_arr['Zahlungsart']);
    // Wenn Zahlungsart = Lastschrift ist => versuche Kundenkontodaten zu holen
    $oKundenKontodaten = gibKundenKontodaten($_SESSION['Kunde']->kKunde);
    if (isset($oKundenKontodaten->kKunde) && $oKundenKontodaten->kKunde > 0) {
        Shop::Smarty()->assign('oKundenKontodaten', $oKundenKontodaten);
    }
    if (!isset($cPost_arr['zahlungsartzusatzschritt']) || !$cPost_arr['zahlungsartzusatzschritt']) {
        Shop::Smarty()->assign('ZahlungsInfo', $_SESSION['Zahlungsart']->ZahlungsInfo ?? null);
    } else {
        setzeFehlendeAngaben(checkAdditionalPayment($Zahlungsart));
        Shop::Smarty()->assign('ZahlungsInfo', gibPostZahlungsInfo());
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
    $linkHelper = Shop::Container()->getLinkService();
    //check currenct shipping method again to avoid using invalid methods when using one click method (#9566)
    if (isset($_SESSION['Versandart']->kVersandart) && !versandartKorrekt($_SESSION['Versandart']->kVersandart)) {
        header('Location: ' . $linkHelper->getStaticRoute('bestellvorgang.php') . '?editVersandart=1', true, 303);
    }
    // Bei Standardzahlungsarten mit Zahlungsinformationen prüfen ob Daten vorhanden sind
    if (isset($_SESSION['Zahlungsart'])
        && in_array($_SESSION['Zahlungsart']->cModulId, ['za_lastschrift_jtl', 'za_kreditkarte_jtl'], true)
        && (empty($_SESSION['Zahlungsart']->ZahlungsInfo) || !is_object($_SESSION['Zahlungsart']->ZahlungsInfo))
    ) {
        header('Location: ' . $linkHelper->getStaticRoute('bestellvorgang.php') . '?editZahlungsart=1', true, 303);
    }
    if (isset($cGet_arr['fillOut']) && $cGet_arr['fillOut'] > 0) {
        if ((int)$cGet_arr['fillOut'] === 5) {
            $hinweis = Shop::Lang()->get('acceptAgb', 'checkout');
        }
    } else {
        unset($_SESSION['cPlausi_arr'], $_SESSION['cPost_arr']);
    }
    if (!empty($_SESSION['Kunde']->cKundenattribut_arr)) {
        krsort($_SESSION['Kunde']->cKundenattribut_arr);
    }
    //falls zahlungsart extern und Einstellung, dass Bestellung für Kaufabwicklung notwendig, füllte tzahlungsession
    Shop::Smarty()->assign('Kunde', $_SESSION['Kunde'])
        ->assign('customerAttribute_arr', $_SESSION['Kunde']->cKundenattribut_arr)
        ->assign('Lieferadresse', $_SESSION['Lieferadresse'])
        ->assign('KuponMoeglich', Kupon::couponsAvailable())
        ->assign('currentCoupon', Shop::Lang()->get('currentCoupon', 'checkout'))
        ->assign('currentCouponName', !empty($_SESSION['Kupon']->translationList)
            ? $_SESSION['Kupon']->translationList
            : null)
        ->assign('currentShippingCouponName', !empty($_SESSION['oVersandfreiKupon']->translationList)
            ? $_SESSION['oVersandfreiKupon']->translationList
            : null)
        ->assign('GuthabenMoeglich', guthabenMoeglich())
        ->assign('nAnzeigeOrt', CHECKBOX_ORT_BESTELLABSCHLUSS)
        ->assign('cPost_arr', (isset($_SESSION['cPost_arr']) ? StringHandler::filterXSS($_SESSION['cPost_arr']) : []));
    if ($_SESSION['Kunde']->kKunde > 0) {
        Shop::Smarty()->assign('GuthabenLocalized', Session::Customer()->gibGuthabenLocalized());
    }
    $cart = Session::Cart();
    if (isset($cart->PositionenArr)
        && !empty($_SESSION['Versandart']->angezeigterHinweistext[$_SESSION['cISOSprache']])
        && count($cart->PositionenArr) > 0
    ) {
        foreach ($cart->PositionenArr as $oPosition) {
            if ($oPosition->nPosTyp == C_WARENKORBPOS_TYP_VERSANDPOS) {
                $oPosition->cHinweis = $_SESSION['Versandart']->angezeigterHinweistext[$_SESSION['cISOSprache']];
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
    $cart       = Session::Cart();
    $lieferland = $_SESSION['Lieferadresse']->cLand ?? null;
    if (!$lieferland) {
        $lieferland = $_SESSION['Kunde']->cLand;
    }
    $plz = $_SESSION['Lieferadresse']->cPLZ ?? null;
    if (!$plz) {
        $plz = $_SESSION['Kunde']->cPLZ;
    }
    $kKundengruppe = $_SESSION['Kunde']->kKundengruppe ?? null;
    if (!$kKundengruppe) {
        $kKundengruppe = Session::CustomerGroup()->getID();
    }
    $oVersandart_arr  = VersandartHelper::getPossibleShippingMethods(
        $lieferland,
        $plz,
        VersandartHelper::getShippingClasses($cart),
        $kKundengruppe
    );
    $oZahlungsart_arr = [];
    foreach ($oVersandart_arr as $oVersandart) {
        $oTmp_arr = gibZahlungsarten($oVersandart->kVersandart, Session::CustomerGroup()->getID());
        foreach ($oTmp_arr as $oTmp) {
            $oZahlungsart_arr[$oTmp->kZahlungsart] = $oTmp;
        }
    }
    $oVerpackung_arr = VersandartHelper::getPossiblePackagings(Session::CustomerGroup()->getID());
    if ($cart->posTypEnthalten(C_WARENKORBPOS_TYP_VERPACKUNG) && !empty($oVerpackung_arr)) {
        foreach ($cart->PositionenArr as $oPos) {
            if ($oPos->nPosTyp === C_WARENKORBPOS_TYP_VERPACKUNG) {
                foreach ($oVerpackung_arr as $oPack) {
                    if ($oPack->cName === $oPos->cName[$oPack->cISOSprache]) {
                        $oPack->bWarenkorbAktiv = true;
                    }
                }
            }
        }
    }
    if ((is_array($oVersandart_arr) && count($oVersandart_arr) > 0)
        || (is_array($oVersandart_arr) && count($oVersandart_arr) === 1
            && is_array($oVerpackung_arr) && count($oVerpackung_arr) > 0)
    ) {
        Shop::Smarty()->assign('Versandarten', $oVersandart_arr)
            ->assign('Verpackungsarten', $oVerpackung_arr);
    } elseif (is_array($oVersandart_arr) && count($oVersandart_arr) === 1 &&
        (is_array($oVerpackung_arr) && count($oVerpackung_arr) === 0)
    ) {
        pruefeVersandartWahl($oVersandart_arr[0]->kVersandart);
    } elseif (!is_array($oVersandart_arr) || count($oVersandart_arr) === 0) {
        Shop::Container()->getLogService()->error(
            'Es konnte keine Versandart für folgende Daten gefunden werden: Lieferland: ' . $lieferland .
            ', PLZ: ' . $plz . ', Versandklasse: ' . VersandartHelper::getShippingClasses(Session::Cart()) .
            ', Kundengruppe: ' . $kKundengruppe
        );
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
    $nKuponfehler_arr = [];
    if (isset($cPost_arr['Kuponcode'])
        && (isset($_SESSION['Bestellung']->lieferadresseGleich) || $_SESSION['Lieferadresse'])
    ) {
        $Kupon = new Kupon();
        $Kupon = $Kupon->getByCode($_POST['Kuponcode']);
        if ($Kupon !== false && $Kupon->kKupon > 0) {
            $nKuponfehler_arr = Kupon::checkCoupon($Kupon);
            if (angabenKorrekt($nKuponfehler_arr)) {
                Kupon::acceptCoupon($Kupon);
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

    return (count($nKuponfehler_arr) > 0)
        ? $nKuponfehler_arr
        : 0;
}

/**
 *
 */
function plausiNeukundenKupon()
{
    if (isset($_SESSION['NeukundenKuponAngenommen']) && $_SESSION['NeukundenKuponAngenommen'] === true) {
        return;
    }
    if ((!isset($_SESSION['Kupon']->cKuponTyp) || $_SESSION['Kupon']->cKuponTyp !== 'standard')
        && !empty($_SESSION['Kunde']->cMail)
    ) {
        $query  = "SELECT tbestellung.kBestellung
            FROM tkunde
            JOIN tbestellung
                ON tbestellung.kKunde = tkunde.kKunde
            WHERE tkunde.cMail = :mail";
        $values = ['mail' => $_SESSION['Kunde']->cMail];
        $conf   = Shop::getSettings([CONF_KAUFABWICKLUNG]);
        if (empty($_SESSION['Kunde']->kKunde)
            && $conf['kaufabwicklung']['bestellvorgang_unregneukundenkupon_zulassen'] === 'N'
        ) {
            //unregistrierte Neukunden, keine Kupons für Gastbestellungen zugelassen
            return;
        }
        if (!empty($_SESSION['Kunde']->kKunde)) {
            // registrierte Kunden und Neukunden mit Kundenkonto
            $query           .= " OR tkunde.kKunde = :kkunde";
            $values['kkunde'] = $_SESSION['Kunde']->kKunde;
        }
        $query      .= " LIMIT 1";
        $oBestellung = Shop::Container()->getDB()->executeQueryPrepared($query, $values, \DB\ReturnType::SINGLE_OBJECT);

        if (empty($oBestellung)) {
            $NeukundenKupons = (new Kupon())->getNewCustomerCoupon();
            if (!empty($NeukundenKupons)) {
                $verwendet = Shop::Container()->getDB()->select('tkuponneukunde', 'cEmail', $_SESSION['Kunde']->cMail);
                $verwendet = !empty($verwendet) ? $verwendet->cVerwendet : null;
                foreach ($NeukundenKupons as $NeukundenKupon) {
                    // teste ob Kunde mit cMail den Neukundenkupon schon verwendet hat...
                    $oDbKuponKunde = Shop::Container()->getDB()->select(
                        'tkuponkunde',
                        ['kKupon', 'cMail'],
                        [$NeukundenKupon->kKupon, $_SESSION['Kunde']->cMail]
                    );
                    if (is_object($oDbKuponKunde)) {
                        // ...falls ja, versuche nächsten Neukundenkupon
                        continue;
                    }
                    if ((empty($verwendet) || $verwendet === 'N') && angabenKorrekt(Kupon::checkCoupon($NeukundenKupon))) {
                        Kupon::acceptCoupon($NeukundenKupon);
                        if (empty($verwendet)) {
                            $hash    = Kuponneukunde::Hash(
                                null,
                                trim($_SESSION['Kunde']->cNachname),
                                trim($_SESSION['Kunde']->cStrasse),
                                null,
                                trim($_SESSION['Kunde']->cPLZ),
                                trim($_SESSION['Kunde']->cOrt),
                                trim($_SESSION['Kunde']->cLand)
                            );
                            $Options = [
                                'Kupon' => $NeukundenKupon->kKupon,
                                'Email' => $_SESSION['Kunde']->cMail,
                                'DatenHash' => $hash,
                                'Erstellt' => 'now()',
                                'Verwendet' => 'N'
                            ];

                            $Kuponneukunde = new Kuponneukunde();
                            $Kuponneukunde->setOptions($Options);
                            $Kuponneukunde->Save();
                        }
                        break;
                    }
                }
            }
        }
    }
}

/**
 * @param Zahlungsart|object $paymentMethod
 * @return array
 */
function checkAdditionalPayment($paymentMethod)
{
    $conf   = Shop::getSettings([CONF_ZAHLUNGSARTEN]);
    $errors = [];
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
            if (empty($_POST['bankname']) || trim($_POST['bankname']) === '') {
                $errors['bankname'] = 1;
            }
            if ($conf['zahlungsarten']['zahlungsart_lastschrift_kontoinhaber_abfrage'] === 'Y' &&
                (empty($_POST['inhaber']) ||
                    trim($_POST['inhaber']) === '')
            ) {
                $errors['inhaber'] = 1;
            }
            if (((!empty($_POST['blz']) &&
                        $conf['zahlungsarten']['zahlungsart_lastschrift_kontonummer_abfrage'] !== 'N') ||
                    $conf['zahlungsarten']['zahlungsart_lastschrift_kontonummer_abfrage'] === 'Y')
                && (empty($_POST['kontonr']) || trim($_POST['kontonr']) === '')
            ) {
                $errors['kontonr'] = 1;
            }
            if (((!empty($_POST['kontonr']) &&
                        $conf['zahlungsarten']['zahlungsart_lastschrift_blz_abfrage'] !== 'N') ||
                    $conf['zahlungsarten']['zahlungsart_lastschrift_blz_abfrage'] === 'Y')
                && (empty($_POST['blz']) || trim($_POST['blz']) === '')
            ) {
                $errors['blz'] = 1;
            }
            if ($conf['zahlungsarten']['zahlungsart_lastschrift_bic_abfrage'] === 'Y' && empty($_POST['bic'])) {
                $errors['bic'] = 1;
            }
            if (!empty($_POST['bic'])
                && ($conf['zahlungsarten']['zahlungsart_lastschrift_iban_abfrage'] !== 'N'
                    || $conf['zahlungsarten']['zahlungsart_lastschrift_iban_abfrage'] === 'Y')
            ) {
                if (empty($_POST['iban'])) {
                    $errors['iban'] = 1;
                } elseif (!plausiIban($_POST['iban'])) {
                    $errors['iban'] = 2;
                }
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
    if ($iban === '' || strlen($iban) < 6) {
        return false;
    }
    $iban  = str_replace(' ', '', $iban);
    $iban1 = substr($iban, 4)
        . (string)(ord($iban{0}) - 55)
        . (string)(ord($iban{1}) - 55)
        . substr($iban, 2, 2);
    $len   = strlen($iban1);
    for ($i = 0; $i < $len; $i++) {
        if (ord($iban1{$i}) > 64 && ord($iban1{$i}) < 91) {
            $iban1 = substr($iban1, 0, $i) . (string)(ord($iban1{$i}) - 55) . substr($iban1, $i + 1);
        }
    }

    $rest = 0;
    $len  = strlen($iban1);
    for ($pos = 0; $pos < $len; $pos += 7) {
        $part = (string)$rest . substr($iban1, $pos, 7);
        $rest = (int)$part % 97;
    }

    $pz = sprintf("%02d", 98 - $rest);

    if (substr($iban, 2, 2) == '00') {
        return substr_replace($iban, $pz, 2, 2);
    }

    return $rest == 1;
}

/**
 * @return stdClass
 */
function gibPostZahlungsInfo()
{
    $oZahlungsInfo = new stdClass();

    $oZahlungsInfo->cKartenNr    = isset($_POST['kreditkartennr'])
        ? StringHandler::htmlentities(stripslashes($_POST['kreditkartennr']), ENT_QUOTES)
        : null;
    $oZahlungsInfo->cGueltigkeit = isset($_POST['gueltigkeit'])
        ? StringHandler::htmlentities(stripslashes($_POST['gueltigkeit']), ENT_QUOTES)
        : null;
    $oZahlungsInfo->cCVV         = isset($_POST['cvv'])
        ? StringHandler::htmlentities(stripslashes($_POST['cvv']), ENT_QUOTES) : null;
    $oZahlungsInfo->cKartenTyp   = isset($_POST['kartentyp'])
        ? StringHandler::htmlentities(stripslashes($_POST['kartentyp']), ENT_QUOTES)
        : null;
    $oZahlungsInfo->cBankName    = isset($_POST['bankname'])
        ? StringHandler::htmlentities(stripslashes(trim($_POST['bankname'])), ENT_QUOTES)
        : null;
    $oZahlungsInfo->cKontoNr     = isset($_POST['kontonr'])
        ? StringHandler::htmlentities(stripslashes(trim($_POST['kontonr'])), ENT_QUOTES)
        : null;
    $oZahlungsInfo->cBLZ         = isset($_POST['blz'])
        ? StringHandler::htmlentities(stripslashes(trim($_POST['blz'])), ENT_QUOTES)
        : null;
    $oZahlungsInfo->cIBAN        = isset($_POST['iban'])
        ? StringHandler::htmlentities(stripslashes(trim($_POST['iban'])), ENT_QUOTES)
        : null;
    $oZahlungsInfo->cBIC         = isset($_POST['bic'])
        ? StringHandler::htmlentities(stripslashes(trim($_POST['bic'])), ENT_QUOTES)
        : null;
    $oZahlungsInfo->cInhaber     = isset($_POST['inhaber'])
        ? StringHandler::htmlentities(stripslashes(trim($_POST['inhaber'])), ENT_QUOTES)
        : null;

    return $oZahlungsInfo;
}

/**
 * @param int $kZahlungsart
 * @return int
 */
function zahlungsartKorrekt($kZahlungsart)
{
    $kZahlungsart = (int)$kZahlungsart;
    $cart         = Session::Cart();
    unset($_SESSION['Zahlungsart']);
    $cart->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
         ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
         ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR)
         ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR);
    if ($kZahlungsart > 0
        && isset($_SESSION['Versandart']->kVersandart)
        && (int)$_SESSION['Versandart']->kVersandart > 0
    ) {
        $Zahlungsart = Shop::Container()->getDB()->query(
            "SELECT tversandartzahlungsart.*, tzahlungsart.*
                FROM tversandartzahlungsart, tzahlungsart
                WHERE tversandartzahlungsart.kVersandart = " . (int)$_SESSION['Versandart']->kVersandart . "
                    AND tversandartzahlungsart.kZahlungsart = tzahlungsart.kZahlungsart
                    AND tversandartzahlungsart.kZahlungsart = " . $kZahlungsart,
            \DB\ReturnType::SINGLE_OBJECT
        );
        if (isset($Zahlungsart->cModulId) && strlen($Zahlungsart->cModulId) > 0) {
            $einstellungen = Shop::Container()->getDB()->selectAll(
                'teinstellungen',
                ['kEinstellungenSektion', 'cModulId'],
                [CONF_ZAHLUNGSARTEN, $Zahlungsart->cModulId]
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
        $oObj                      = Shop::Container()->getDB()->select(
            'tzahlungsartsprache',
            'kZahlungsart',
            (int)$Zahlungsart->kZahlungsart,
            'cISOSprache',
            $_SESSION['cISOSprache'],
            null,
            null,
            false,
            'cHinweisTextShop'
        );
        $Zahlungsart->cHinweisText = '';
        if (isset($oObj->cHinweisTextShop)) {
            $Zahlungsart->cHinweisText = $oObj->cHinweisTextShop;
        }
        if (isset($_SESSION['VersandKupon']->cZusatzgebuehren)
            && $_SESSION['VersandKupon']->cZusatzgebuehren === 'Y'
            && $Zahlungsart->fAufpreis > 0
            && $Zahlungsart->cName === 'Nachnahme'
        ) {
            $Zahlungsart->fAufpreis = 0;
        }
        /** @var array('Warenkorb' => Warenkorb) $_SESSION */
        getPaymentSurchageDiscount($Zahlungsart);

        //posname lokalisiert ablegen
        $Spezialpos        = new stdClass();
        $Spezialpos->cName = [];
        foreach ($_SESSION['Sprachen'] as $Sprache) {
            if ($Zahlungsart->kZahlungsart > 0) {
                $name_spr = Shop::Container()->getDB()->select(
                    'tzahlungsartsprache',
                    'kZahlungsart',
                    (int)$Zahlungsart->kZahlungsart,
                    'cISOSprache',
                    $Sprache->cISO,
                    null,
                    null,
                    false,
                    'cName'
                );
                if (isset($name_spr->cName)) {
                    $Spezialpos->cName[$Sprache->cISO] = $name_spr->cName;
                }
            }
        }
        $Zahlungsart->angezeigterName  = $Spezialpos->cName;
        $_SESSION['Zahlungsart']       = $Zahlungsart;
        $_SESSION['AktiveZahlungsart'] = $Zahlungsart->kZahlungsart;
        if ($Zahlungsart->cZusatzschrittTemplate) {
            $ZahlungsInfo    = new stdClass();
            $zusatzangabenDa = false;
            switch ($Zahlungsart->cModulId) {
                case 'za_kreditkarte_jtl':
                    if (isset($_POST['kreditkartennr'])
                        && $_POST['kreditkartennr']
                        && $_POST['gueltigkeit']
                        && $_POST['cvv']
                        && $_POST['kartentyp']
                        && $_POST['inhaber']
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
                    if ($paymentMethod && !$paymentMethod->handleAdditional($_POST)) {
                        $zusatzangabenDa = false;
                    }
                    break;
            }
            if (!$zusatzangabenDa) {
                return 1;
            }
            $Zahlungsart->ZahlungsInfo = $ZahlungsInfo;
        }
        // billpay
        if (isset($paymentMethod) && strpos($Zahlungsart->cModulId, 'za_billpay') === 0 && $paymentMethod) {
            /** @var Billpay $paymentMethod */
            return $paymentMethod->preauthRequest() ? 2 : 1;
        }

        return 2;
    }

    return 0;
}

/**
 * @param $Zahlungsart
 */
function getPaymentSurchageDiscount($Zahlungsart)
{
    if ($Zahlungsart->fAufpreis != 0) {
        $cart = Session\Session::Cart();
        $cart->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
             ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR);
        //lokalisieren
        $Zahlungsart->cPreisLocalized = Preise::getLocalizedPriceString($Zahlungsart->fAufpreis);
        $Aufpreis = $Zahlungsart->fAufpreis;
        if ($Zahlungsart->cAufpreisTyp === 'prozent') {
            $fGuthaben = $_SESSION['Bestellung']->fGuthabenGenutzt ?? 0;
            $Aufpreis  = (($cart->gibGesamtsummeWarenExt(
                            [
                                C_WARENKORBPOS_TYP_ARTIKEL,
                                C_WARENKORBPOS_TYP_VERSANDPOS,
                                C_WARENKORBPOS_TYP_KUPON,
                                C_WARENKORBPOS_TYP_GUTSCHEIN,
                                C_WARENKORBPOS_TYP_VERSANDZUSCHLAG,
                                C_WARENKORBPOS_TYP_NEUKUNDENKUPON,
                                C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG,
                                C_WARENKORBPOS_TYP_VERPACKUNG,
                                C_WARENKORBPOS_TYP_TRUSTEDSHOPS
                            ],
                            true
                        ) - $fGuthaben) * $Zahlungsart->fAufpreis) / 100.0;

            $Zahlungsart->cPreisLocalized = Preise::getLocalizedPriceString($Aufpreis);
        }
        //posname lokalisiert ablegen
        $Spezialpos               = new stdClass();
        $Spezialpos->cGebuehrname = [];
        foreach ($_SESSION['Sprachen'] as $Sprache) {
            if ($Zahlungsart->kZahlungsart > 0) {
                $name_spr = Shop::Container()->getDB()->select(
                    'tzahlungsartsprache',
                    'kZahlungsart',
                    (int)$Zahlungsart->kZahlungsart,
                    'cISOSprache', $Sprache->cISO,
                    null,
                    null,
                    false,
                    'cGebuehrname'
                );
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
            $cart->erstelleSpezialPos(
                $Spezialpos->cGebuehrname,
                1,
                $Aufpreis,
                $cart->gibVersandkostenSteuerklasse($_SESSION['Lieferadresse']->cLand),
                C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR,
                true,
                true,
                $Zahlungsart->cHinweisText
            );
        } else {
            $cart->erstelleSpezialPos(
                $Spezialpos->cGebuehrname,
                1,
                $Aufpreis,
                $cart->gibVersandkostenSteuerklasse($_SESSION['Lieferadresse']->cLand),
                C_WARENKORBPOS_TYP_ZAHLUNGSART,
                true,
                true,
                $Zahlungsart->cHinweisText
            );
        }
    }
}

/**
 * @param string $cModulId
 * @return bool|Plugin
 */
function gibPluginZahlungsart($cModulId)
{
    $kPlugin = Plugin::getIDByModuleID($cModulId);
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
    $Zahlungsart  = Shop::Container()->getDB()->select('tzahlungsart', 'kZahlungsart', $kZahlungsart);
    foreach ($_SESSION['Sprachen'] as $Sprache) {
        $name_spr                                     = Shop::Container()->getDB()->select(
            'tzahlungsartsprache',
            'kZahlungsart',
            $kZahlungsart,
            'cISOSprache',
            $Sprache->cISO,
            null,
            null,
            false,
            'cName'
        );
        $Zahlungsart->angezeigterName[$Sprache->cISO] = $name_spr->cName ?? null;
    }
    $einstellungen = Shop::Container()->getDB()->query(
        "SELECT *
            FROM teinstellungen
            WHERE kEinstellungenSektion = " . CONF_ZAHLUNGSARTEN . "
                AND cModulId = '" . $Zahlungsart->cModulId . "'",
        \DB\ReturnType::ARRAY_OF_OBJECTS
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
 * @return object|bool
 */
function gibKundenKontodaten($kKunde)
{
    if ($kKunde > 0) {
        $oKundenKontodaten = Shop::Container()->getDB()->select('tkundenkontodaten', 'kKunde', (int)$kKunde);

        if (isset($oKundenKontodaten->kKunde) && $oKundenKontodaten->kKunde > 0) {
            $cryptoService = Shop::Container()->getCryptoService();
            if (strlen($oKundenKontodaten->cBLZ) > 0) {
                $oKundenKontodaten->cBLZ = (int)$cryptoService->decryptXTEA($oKundenKontodaten->cBLZ);
            }
            if (strlen($oKundenKontodaten->cInhaber) > 0) {
                $oKundenKontodaten->cInhaber = trim($cryptoService->decryptXTEA($oKundenKontodaten->cInhaber));
            }
            if (strlen($oKundenKontodaten->cBankName) > 0) {
                $oKundenKontodaten->cBankName = trim($cryptoService->decryptXTEA($oKundenKontodaten->cBankName));
            }
            if (strlen($oKundenKontodaten->nKonto) > 0) {
                $oKundenKontodaten->nKonto = trim($cryptoService->decryptXTEA($oKundenKontodaten->nKonto));
            }
            if (strlen($oKundenKontodaten->cIBAN) > 0) {
                $oKundenKontodaten->cIBAN = trim($cryptoService->decryptXTEA($oKundenKontodaten->cIBAN));
            }
            if (strlen($oKundenKontodaten->cBIC) > 0) {
                $oKundenKontodaten->cBIC = trim($cryptoService->decryptXTEA($oKundenKontodaten->cBIC));
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
    $Zahlungsarten = [];
    if ($kVersandart > 0) {
        $Zahlungsarten = Shop::Container()->getDB()->query(
            "SELECT tversandartzahlungsart.*, tzahlungsart.*
                FROM tversandartzahlungsart, tzahlungsart
                WHERE tversandartzahlungsart.kVersandart = {$kVersandart}
                    AND tversandartzahlungsart.kZahlungsart=tzahlungsart.kZahlungsart
                    AND (tzahlungsart.cKundengruppen IS NULL OR tzahlungsart.cKundengruppen=''
                    OR FIND_IN_SET({$kKundengruppe}, REPLACE(tzahlungsart.cKundengruppen, ';', ',')) > 0)
                    AND tzahlungsart.nActive = 1
                    AND tzahlungsart.nNutzbar = 1
                ORDER BY tzahlungsart.nSort",
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
    }
    $gueltigeZahlungsarten = [];
    $zaCount               = count($Zahlungsarten);
    for ($i = 0; $i < $zaCount; ++$i) {
        if (!$Zahlungsarten[$i]->kZahlungsart) {
            continue;
        }
        //posname lokalisiert ablegen
        $Zahlungsarten[$i]->angezeigterName = [];
        $Zahlungsarten[$i]->cGebuehrname    = [];
        foreach ($_SESSION['Sprachen'] as $Sprache) {
            $name_spr = Shop::Container()->getDB()->select(
                'tzahlungsartsprache',
                'kZahlungsart',
                (int)$Zahlungsarten[$i]->kZahlungsart,
                'cISOSprache',
                $Sprache->cISO,
                null,
                null,
                false,
                'cName, cGebuehrname, cHinweisTextShop'
            );
            if (isset($name_spr->cName)) {
                $Zahlungsarten[$i]->angezeigterName[$Sprache->cISO] = $name_spr->cName;
                $Zahlungsarten[$i]->cGebuehrname[$Sprache->cISO]    = $name_spr->cGebuehrname;
                $Zahlungsarten[$i]->cHinweisText[$Sprache->cISO]    = $name_spr->cHinweisTextShop;
            }
        }
        $einstellungen = Shop::Container()->getDB()->selectAll(
            'teinstellungen',
            ['kEinstellungenSektion', 'cModulId'],
            [CONF_ZAHLUNGSARTEN, $Zahlungsarten[$i]->cModulId]
        );
        foreach ($einstellungen as $einstellung) {
            $Zahlungsarten[$i]->einstellungen[$einstellung->cName] = $einstellung->cWert;
        }
        //Einstellungen beachten
        if (!zahlungsartGueltig($Zahlungsarten[$i])) {
            continue;
        }
        $Zahlungsarten[$i]->Specials = null;
        //evtl. Versandkupon anwenden / Nur Nachname fällt weg
        if (isset($_SESSION['VersandKupon']->cZusatzgebuehren)
            && $_SESSION['VersandKupon']->cZusatzgebuehren === 'Y'
            && $Zahlungsarten[$i]->fAufpreis > 0
            && $Zahlungsarten[$i]->cName === 'Nachnahme'
        ) {
            $Zahlungsarten[$i]->fAufpreis = 0;
        }
        //lokalisieren
        if ($Zahlungsarten[$i]->cAufpreisTyp === 'festpreis') {
            $Zahlungsarten[$i]->fAufpreis *= ((100 + $fSteuersatz) / 100);
        }
        $Zahlungsarten[$i]->cPreisLocalized = Preise::getLocalizedPriceString($Zahlungsarten[$i]->fAufpreis);
        if ($Zahlungsarten[$i]->cAufpreisTyp === 'prozent') {
            $Zahlungsarten[$i]->cPreisLocalized  = ($Zahlungsarten[$i]->fAufpreis < 0) ? ' ' : '+ ';
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
 * @param object[] $oVersandarten_arr
 * @return int
 */
function gibAktiveVersandart($oVersandarten_arr)
{
    if (isset($_SESSION['Versandart'])) {
        $_SESSION['AktiveVersandart'] = $_SESSION['Versandart']->kVersandart;
    } elseif (!empty($_SESSION['AktiveVersandart']) && is_array($oVersandarten_arr) && count($oVersandarten_arr) > 0) {
        $active = (int)$_SESSION['AktiveVersandart'];
        if (array_reduce($oVersandarten_arr, function ($carry, $item) use ($active) {
            return (int)$item->kVersandart === $active ? (int)$item->kVersandart : $carry;
        }, 0) !== (int)$_SESSION['AktiveVersandart']) {
            $_SESSION['AktiveVersandart'] = $oVersandarten_arr[0]->kVersandart;
        }
    } else {
        $_SESSION['AktiveVersandart'] = $oVersandarten_arr[0]->kVersandart;
    }

    return $_SESSION['AktiveVersandart'];
}

/**
 * @param object[] $oZahlungsarten_arr
 * @return int
 */
function gibAktiveZahlungsart($oZahlungsarten_arr)
{
    if (isset($_SESSION['Zahlungsart'])) {
        $_SESSION['AktiveZahlungsart'] = $_SESSION['Zahlungsart']->kZahlungsart;
    } elseif (!empty($_SESSION['AktiveZahlungsart']) && is_array($oZahlungsarten_arr) && count($oZahlungsarten_arr) > 0) {
        $active = (int)$_SESSION['AktiveZahlungsart'];
        if (array_reduce($oZahlungsarten_arr, function ($carry, $item) use ($active) {
            return (int)$item->kZahlungsart === $active ? (int)$item->kZahlungsart : $carry;
        }, 0) !== (int)$_SESSION['AktiveZahlungsart']) {
            $_SESSION['AktiveZahlungsart'] = $oZahlungsarten_arr[0]->kZahlungsart;
        }
    } else {
        $_SESSION['AktiveZahlungsart'] = $oZahlungsarten_arr[0]->kZahlungsart;
    }

    return $_SESSION['AktiveZahlungsart'];
}

/**
 * @param object[] $oVerpackung_arr
 * @return array
 */
function gibAktiveVerpackung($oVerpackung_arr)
{
    if (isset($_SESSION['Verpackung']) && count($_SESSION['Verpackung']) > 0) {
        $_SESSION['AktiveVerpackung'] = [];
        foreach ($_SESSION['Verpackung'] as $verpackung) {
            $_SESSION['AktiveVerpackung'][$verpackung->kVerpackung] = 1;
        }
    } elseif (!empty($_SESSION['AktiveVerpackung']) && is_array($oVerpackung_arr) && count($oVerpackung_arr) > 0) {
        foreach (array_keys($_SESSION['AktiveVerpackung']) as $active) {
            if (array_reduce($oVerpackung_arr, function ($carry, $item) use ($active) {
                $kVerpackung = (int)$item->kVerpackung;
                return $kVerpackung === $active ? $kVerpackung : $carry;
            }, 0) === 0) {
                unset($_SESSION['AktiveVerpackung'][$active]);
            }
        }
    } else {
        $_SESSION['AktiveVerpackung'] = [];
    }

    return $_SESSION['AktiveVerpackung'];
}

/**
 * @param Zahlungsart|object $Zahlungsart
 * @return bool
 */
function zahlungsartGueltig($Zahlungsart)
{
    if (!isset($Zahlungsart->cModulId)) {
        return false;
    }
    // Interne Zahlungsartpruefung ob wichtige Parameter gesetzt sind
    require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php';
    $kPlugin = Plugin::getIDByModuleID($Zahlungsart->cModulId);
    if ($kPlugin > 0) {
        $oPlugin = new Plugin($kPlugin);
        if ($oPlugin->kPlugin > 0) {
            // Plugin muss aktiv sein
            if ($oPlugin->nStatus !== Plugin::PLUGIN_ACTIVATED) {
                return false;
            }
            require_once PFAD_ROOT . PFAD_PLUGIN . $oPlugin->cVerzeichnis . '/' . PFAD_PLUGIN_VERSION . $oPlugin->nVersion . '/' .
                PFAD_PLUGIN_PAYMENTMETHOD . $oPlugin->oPluginZahlungsKlasseAssoc_arr[$Zahlungsart->cModulId]->cClassPfad;
            $className              = $oPlugin->oPluginZahlungsKlasseAssoc_arr[$Zahlungsart->cModulId]->cClassName;
            $oZahlungsart           = new $className($Zahlungsart->cModulId);
            $oZahlungsart->cModulId = $Zahlungsart->cModulId;
            /** @var PaymentMethod $oZahlungsart */
            if ($oZahlungsart && $oZahlungsart->isSelectable() === false) {
                return false;
            }
            if ($oZahlungsart && !$oZahlungsart->isValidIntern()) {
                Shop::Container()->getLogService()->withName('cModulId')->debug(
                    'Die Zahlungsartprüfung (' . $Zahlungsart->cModulId .
                    ') wurde nicht erfolgreich validiert (isValidIntern).',
                    [$Zahlungsart->cModulId]
                );

                return false;
            }
            // Lizenzprüfung
            if (!Plugin::licenseCheck($oPlugin, ['cModulId' => $Zahlungsart->cModulId])) {
                return false;
            }

            return $oZahlungsart->isValid($_SESSION['Kunde'], Session::Cart());
        }
    } else {
        $oPaymentMethod = new PaymentMethod($Zahlungsart->cModulId);
        $oZahlungsart   = $oPaymentMethod::create($Zahlungsart->cModulId);

        if ($oZahlungsart && $oZahlungsart->isSelectable() === false) {
            return false;
        }
        if ($oZahlungsart && !$oZahlungsart->isValidIntern()) {
            Shop::Container()->getLogService()->withName('cModulId')->debug(
                'Die Zahlungsartprüfung (' .
                    $Zahlungsart->cModulId . ') wurde nicht erfolgreich validiert (isValidIntern).',
                [$Zahlungsart->cModulId]
            );

            return false;
        }

        return ZahlungsartHelper::shippingMethodWithValidPaymentMethod($Zahlungsart);
    }

    return false;
}

/**
 * @param int $nMinBestellungen
 * @return bool
 */
function pruefeZahlungsartMinBestellungen($nMinBestellungen)
{
    if ($nMinBestellungen > 0) {
        if ($_SESSION['Kunde']->kKunde > 0) {
            $anzahl_obj = Shop::Container()->getDB()->query(
                "SELECT count(*) AS anz
                    FROM tbestellung
                    WHERE kKunde = " . (int)$_SESSION['Kunde']->kKunde . "
                        AND (cStatus = '" . BESTELLUNG_STATUS_BEZAHLT . "'
                        OR cStatus = '" . BESTELLUNG_STATUS_VERSANDT . "')",
                \DB\ReturnType::SINGLE_OBJECT
            );
            if ($anzahl_obj->anz < $nMinBestellungen) {
                Shop::Container()->getLogService()->debug(
                    'pruefeZahlungsartMinBestellungen Bestellanzahl zu niedrig: Anzahl ' .
                    $anzahl_obj->anz . ' < ' . $nMinBestellungen
                );

                return false;
            }
        } else {
            Shop::Container()->getLogService()->debug('pruefeZahlungsartMinBestellungen erhielt keinen kKunden');

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
    if ($fMinBestellwert > 0
        && Session::Cart()->gibGesamtsummeWarenOhne([C_WARENKORBPOS_TYP_VERSANDPOS], true) < $fMinBestellwert
    ) {
        Shop::Container()->getLogService()->debug(
            'pruefeZahlungsartMinBestellwert Bestellwert zu niedrig: Wert ' .
            Session::Cart()->gibGesamtsummeWaren(true) . ' < ' . $fMinBestellwert
        );

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
    if ($fMaxBestellwert > 0
        && Session::Cart()->gibGesamtsummeWarenOhne([C_WARENKORBPOS_TYP_VERSANDPOS], true) >= $fMaxBestellwert
    ) {
        Shop::Container()->getLogService()->debug(
            'pruefeZahlungsartMaxBestellwert Bestellwert zu hoch: Wert ' .
            Session::Cart()->gibGesamtsummeWaren(true) . ' > ' . $fMaxBestellwert
        );

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
    $cart        = Session::Cart();
    $kVersandart = (int)$kVersandart;
    //Verpackung beachten
    $kVerpackung_arr        = (isset($_POST['kVerpackung']) && is_array($_POST['kVerpackung']) && count($_POST['kVerpackung']) > 0)
        ? $_POST['kVerpackung']
        : $aFormValues['kVerpackung'];
    $fSummeWarenkorb        = $cart->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], true);
    $_SESSION['Verpackung'] = [];
    if (is_array($kVerpackung_arr) && count($kVerpackung_arr) > 0) {
        $cart->loescheSpezialPos(C_WARENKORBPOS_TYP_VERPACKUNG);
        foreach ($kVerpackung_arr as $i => $kVerpackung) {
            $kVerpackung = (int)$kVerpackung;
            $oVerpackung = Shop::Container()->getDB()->query(
                "SELECT *
                    FROM tverpackung
                    WHERE kVerpackung = " . $kVerpackung . "
                        AND (tverpackung.cKundengruppe = '-1'
                            OR FIND_IN_SET('" . Session::CustomerGroup()->getID()
                                . "', REPLACE(tverpackung.cKundengruppe, ';', ',')) > 0)
                        AND " . $fSummeWarenkorb . " >= tverpackung.fMindestbestellwert
                        AND nAktiv = 1",
                \DB\ReturnType::SINGLE_OBJECT
            );

            $oVerpackung->kVerpackung = (int)$oVerpackung->kVerpackung;
            if ($oVerpackung->kVerpackung > 0) {
                $cName_arr              = [];
                $oVerpackungSprache_arr = Shop::Container()->getDB()->selectAll('tverpackungsprache', 'kVerpackung', (int)$oVerpackung->kVerpackung);
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
                    $oVerpackung->kSteuerklasse = $cart->gibVersandkostenSteuerklasse($_SESSION['Lieferadresse']->cLand);
                }
                $_SESSION['Verpackung'][] = $oVerpackung;

                $_SESSION['AktiveVerpackung'][$oVerpackung->kVerpackung] = 1;
                $cart->erstelleSpezialPos($cName_arr, 1, $fBrutto, $oVerpackung->kSteuerklasse, C_WARENKORBPOS_TYP_VERPACKUNG, false);
                unset($oVerpackung);
            } else {
                return false;
            }
        }
    }
    unset($_SESSION['Versandart']);
    if ($kVersandart > 0) {
        $lieferland = $_SESSION['Lieferadresse']->cLand ?? null;
        if (!$lieferland) {
            $lieferland = $_SESSION['Kunde']->cLand;
        }
        $plz = $_SESSION['Lieferadresse']->cPLZ ?? null;
        if (!$plz) {
            $plz = $_SESSION['Kunde']->cPLZ;
        }
        $versandklassen           = VersandartHelper::getShippingClasses(Session::Cart());
        $cNurAbhaengigeVersandart = 'N';
        if (VersandartHelper::normalerArtikelversand($lieferland) == false) {
            $cNurAbhaengigeVersandart = 'Y';
        }
        $cISO       = $lieferland;
        $versandart = Shop::Container()->getDB()->query(
            "SELECT *
                FROM tversandart
                WHERE cLaender LIKE '%" . $cISO . "%'
                    AND cNurAbhaengigeVersandart = '" . $cNurAbhaengigeVersandart . "'
                    AND (
                            cVersandklassen = '-1' OR
                            cVersandklassen RLIKE '^([0-9 -]* )?" . $versandklassen . " '
                        )
                    AND kVersandart = " . $kVersandart,
            \DB\ReturnType::SINGLE_OBJECT
        );

        if (isset($versandart->kVersandart) && $versandart->kVersandart > 0) {
            $versandart->Zuschlag  = VersandartHelper::getAdditionalFees($versandart, $cISO, $plz);
            $versandart->fEndpreis = VersandartHelper::calculateShippingFees($versandart, $cISO, null);
            if ($versandart->fEndpreis == -1) {
                return false;
            }
            //posname lokalisiert ablegen
            if (!isset($Spezialpos)) {
                $Spezialpos = new stdClass();
            }
            $Spezialpos->cName = [];
            foreach ($_SESSION['Sprachen'] as $Sprache) {
                $name_spr = Shop::Container()->getDB()->select(
                    'tversandartsprache',
                    'kVersandart',
                    (int)$versandart->kVersandart,
                    'cISOSprache',
                    $Sprache->cISO,
                    null,
                    null,
                    false,
                    'cName, cHinweisTextShop'
                );
                if (isset($name_spr->cName)) {
                    $Spezialpos->cName[$Sprache->cISO]                  = $name_spr->cName;
                    $versandart->angezeigterName[$Sprache->cISO]        = $name_spr->cName;
                    $versandart->angezeigterHinweistext[$Sprache->cISO] = $name_spr->cHinweisTextShop;
                }
            }
            $bSteuerPos = $versandart->eSteuer !== 'netto';
            // Ticket #1298 Inselzuschläge müssen bei Versandkostenfrei berücksichtigt werden
            $fVersandpreis = $versandart->fEndpreis;
            if (isset($versandart->Zuschlag->fZuschlag)) {
                $fVersandpreis = $versandart->fEndpreis - $versandart->Zuschlag->fZuschlag;
            }
            if ($versandart->fEndpreis == 0 &&
                isset($versandart->Zuschlag->fZuschlag) &&
                $versandart->Zuschlag->fZuschlag > 0
            ) {
                $fVersandpreis = $versandart->fEndpreis;
            }
            $cart->erstelleSpezialPos(
                $Spezialpos->cName,
                1,
                $fVersandpreis,
                $cart->gibVersandkostenSteuerklasse($cISO),
                C_WARENKORBPOS_TYP_VERSANDPOS,
                true,
                $bSteuerPos
            );
            pruefeVersandkostenfreiKuponVorgemerkt();
            //Zuschlag?
            $cart->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG);
            if (isset($versandart->Zuschlag->fZuschlag) && $versandart->Zuschlag->fZuschlag != 0) {
                //posname lokalisiert ablegen
                $Spezialpos->cName = [];
                foreach ($_SESSION['Sprachen'] as $Sprache) {
                    $name_spr                          = Shop::Container()->getDB()->select(
                        'tversandzuschlagsprache',
                        'kVersandzuschlag',
                        (int)$versandart->Zuschlag->kVersandzuschlag,
                        'cISOSprache', $Sprache->cISO,
                        null,
                        null,
                        false,
                        'cName'
                    );
                    $Spezialpos->cName[$Sprache->cISO] = $name_spr->cName;
                }
                $cart->erstelleSpezialPos(
                    $Spezialpos->cName,
                    1,
                    $versandart->Zuschlag->fZuschlag,
                    $cart->gibVersandkostenSteuerklasse($cISO), C_WARENKORBPOS_TYP_VERSANDZUSCHLAG,
                    true,
                    $bSteuerPos
                );
            }
            $_SESSION['Versandart']       = $versandart;
            $_SESSION['AktiveVersandart'] = $versandart->kVersandart;

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
 * @param array $data
 * @param int   $kundenaccount
 * @param int   $checkpass
 * @return array
 */
function checkKundenFormularArray($data, $kundenaccount, $checkpass = 1)
{
    $ret  = [];
    $conf = Shop::getSettings([CONF_KUNDEN, CONF_KUNDENFELD, CONF_GLOBAL]);

    foreach (['nachname', 'strasse', 'hausnummer', 'plz', 'ort', 'land', 'email'] as $dataKey) {
        $data[$dataKey] = isset($data[$dataKey]) ? trim($data[$dataKey]) : null;

        if (!isset($data[$dataKey]) || !$data[$dataKey]) {
            $ret[$dataKey] = 1;
        }
    }

    foreach ([
             'kundenregistrierung_abfragen_anrede' => 'anrede',
             'kundenregistrierung_pflicht_vorname' => 'vorname',
             'kundenregistrierung_abfragen_firma' => 'firma',
             'kundenregistrierung_abfragen_firmazusatz' => 'firmazusatz',
             ] as $confKey => $dataKey) {
        if ($conf['kunden'][$confKey] === 'Y') {
            $data[$dataKey] = isset($data[$dataKey]) ? trim($data[$dataKey]) : null;

            if (!$data[$dataKey]) {
                $ret[$dataKey] = 1;
            }
        }
    }

    if (isset($ret['email']) && $ret['email'] === 1) {
        // email is empty
    } elseif (StringHandler::filterEmailAddress($data['email']) === false) {
        $ret['email'] = 2;
    } elseif (SimpleMail::checkBlacklist($data['email'])) {
        $ret['email'] = 3;
    }
    if (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) {
        if (isset($ret['email'])
            && $ret['email'] !== 1
            && $data['email'] !== $_SESSION['Kunde']->cMail
            && !isEmailAvailable($data['email'])
        ) {
            $ret['email'] = 5;
        }
    } elseif (isset($ret['email']) && $ret['email'] !== 1 && !isEmailAvailable($data['email'])) {
        $ret['email'] = 5;
    }
    if (empty($_SESSION['check_plzort'])
        && $data['plz']
        && $data['ort']
        && $data['land']
        && $conf['kunden']['kundenregistrierung_abgleichen_plz'] === 'Y'
    ) {
        if (!valid_plzort($data['plz'], $data['ort'], $data['land'])) {
            $ret['plz']               = 2;
            $ret['ort']               = 2;
            $_SESSION['check_plzort'] = 1;
        }
    } else {
        unset($_SESSION['check_plzort']);
    }
    if (empty($data['titel']) && $conf['kunden']['kundenregistrierung_abfragen_titel'] === 'Y') {
        $ret['titel'] = 1;
    }
    if (empty($data['adresszusatz']) && $conf['kunden']['kundenregistrierung_abfragen_adresszusatz'] === 'Y') {
        $ret['adresszusatz'] = 1;
    }
    if ($conf['kunden']['kundenregistrierung_abfragen_mobil'] === 'Y' && StringHandler::checkPhoneNumber($data['mobil']) > 0) {
        $ret['mobil'] = StringHandler::checkPhoneNumber($data['mobil']);
    }
    if ($conf['kunden']['kundenregistrierung_abfragen_fax'] === 'Y' && StringHandler::checkPhoneNumber($data['fax']) > 0) {
        $ret['fax'] = StringHandler::checkPhoneNumber($data['fax']);
    }
    $deliveryCountry = ($conf['kunden']['kundenregistrierung_abfragen_ustid'] !== 'N')
        ? Shop::Container()->getDB()->select('tland', 'cISO', $data['land'])
        : null;

    if (isset($deliveryCountry->nEU)
        && $deliveryCountry->nEU === '0'
        && $conf['kunden']['kundenregistrierung_abfragen_ustid'] === 'Y'
    ) {
        //skip
    } elseif (empty($data['ustid']) && $conf['kunden']['kundenregistrierung_abfragen_ustid'] === 'Y') {
        $ret['ustid'] = 1;
    } elseif (isset($data['ustid'])
        && $data['ustid'] !== ''
        && $conf['kunden']['kundenregistrierung_abfragen_ustid'] !== 'N'
    ) {
        if (!isset($_SESSION['Kunde']->cUSTID)
            || (isset($_SESSION['Kunde']->cUSTID) && $_SESSION['Kunde']->cUSTID !== $data['ustid'])
        ) {
            $bAnalizeCheck = false; // flag to signalize further analization
            $vViesResult   = null;
            if ('Y' === $conf['kunden']['shop_ustid_bzstpruefung']) { // backend-setting: "Einstellungen -> Formulareinstellungen ->"
                $oVies         = new UstIDvies();
                $vViesResult   = $oVies->doCheckID(trim($data['ustid']));
                $bAnalizeCheck = true; // flag to signalize further analization
            }
            if (true === $bAnalizeCheck && true === $vViesResult['success']) {
                // "all was fine"
                $ret['ustid'] = 0;
            } elseif(isset($vViesResult)) {
                switch ($vViesResult['errortype']) {
                    case 'vies' :
                        // vies-error: the ID is invalid according to the VIES-system
                        $ret['ustid'] = $vViesResult['errorcode']; // (old value 5)
                        break;
                    case 'parse' :
                        // parse-error: the ID-string is misspelled in any way
                        if (1 === $vViesResult['errorcode']) {
                            $ret['ustid'] = 1; // parse-error: no id was given
                        } elseif (1 < $vViesResult['errorcode']) {
                            $ret['ustid'] = 2; // parse-error: with the position of error in given ID-string
                            switch ($vViesResult['errorcode']) {
                                case 120:
                                    // build a string with error-code and error-information
                                    $ret['ustid_err'] = $vViesResult['errorcode'].
                                        ','.
                                        substr($data['ustid'], 0, $vViesResult['errorinfo']).
                                        '<span style="color:red;">'.
                                        substr($data['ustid'], $vViesResult['errorinfo']).
                                        '</span>';
                                    break;
                                case 130 :
                                    $ret['ustid_err'] = $vViesResult['errorcode'].
                                        ','.
                                        $vViesResult['errorinfo'];
                                    break;
                                default:
                                    $ret['ustid_err'] = $vViesResult['errorcode'];
                                    break;
                            }
                        }
                        break;
                    case 'time' :
                        // according to the backend-setting: "Einstellungen -> (Formular)einstellungen -> UstID-Nummer"-check active
                        if ('Y' === $conf['kunden']['shop_ustid_force_remote_check']) {
                            $ret['ustid'] = 4; // parsing ok, but the remote-service is in a "down-slot" and unreachable
                            $ret['ustid_err'] = $vViesResult['errorcode'].
                                ','.
                                $vViesResult['errorinfo'];
                        }
                        break;
                }

            }
        }

    }
    if ($conf['kunden']['kundenregistrierung_abfragen_geburtstag'] === 'Y'
        && StringHandler::checkDate(StringHandler::filterXSS($data['geburtstag'])) > 0
    ) {
        $ret['geburtstag'] = StringHandler::checkDate(StringHandler::filterXSS($data['geburtstag']));
    }
    if ($conf['kunden']['kundenregistrierung_abfragen_www'] === 'Y' && empty($data['www'])) {
        $ret['www'] = 1;
    }
    if ($conf['kunden']['kundenregistrierung_abfragen_tel'] === 'Y' && StringHandler::checkPhoneNumber($data['tel']) > 0) {
        $ret['tel'] = StringHandler::checkPhoneNumber($data['tel']);
    }
    if ($conf['kunden']['kundenregistrierung_abfragen_bundesland'] === 'Y' && empty($data['bundesland'])) {
        $ret['bundesland'] = 1;
    }
    if ($kundenaccount == 1) {
        if ($checkpass) {
            if ($data['pass'] !== $data['pass2']) {
                $ret['pass_ungleich'] = 1;
            }
            if (strlen($data['pass']) < $conf['kunden']['kundenregistrierung_passwortlaenge']) {
                $ret['pass_zu_kurz'] = 1;
            }
        }
        //existiert diese email bereits?
        $obj = Shop::Container()->getDB()->selectAll('tkunde', 'cMail', Shop::Container()->getDB()->escape($data['email']));
        foreach ($obj as $customer) {
            if (!empty($customer->cPasswort) && !empty($customer->kKunde)) {
                $ret['email_vorhanden'] = 1;
                break;
            }
        }
        if (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) {
            //emailadresse anders und existiert dennoch?
            $mail = Shop::Container()->getDB()->select('tkunde', 'kKunde', (int)$_SESSION['Kunde']->kKunde);
            if (isset($mail->cMail) && $data['email'] === $mail->cMail) {
                unset($ret['email_vorhanden']);
            }
        }
    }
    // Selbstdef. Kundenfelder
    if (isset($conf['kundenfeld']['kundenfeld_anzeigen']) && $conf['kundenfeld']['kundenfeld_anzeigen'] === 'Y') {
        $oKundenfeld_arr = Shop::Container()->getDB()->selectAll(
            'tkundenfeld',
            'kSprache',
            Shop::getLanguage(),
            'kKundenfeld, cName, cTyp, nPflicht, nEditierbar'
        );
        foreach ($oKundenfeld_arr as $oKundenfeld) {
            // Kundendaten ändern?
            if ((int)$data['editRechnungsadresse'] === 1) {
                if (!isset($data['custom_' . $oKundenfeld->kKundenfeld])
                    && $oKundenfeld->nPflicht == 1
                    && $oKundenfeld->nEditierbar == 1
                ) {
                    $ret['custom'][$oKundenfeld->kKundenfeld] = 1;
                } elseif (isset($data['custom_' . $oKundenfeld->kKundenfeld])
                    && $data['custom_' . $oKundenfeld->kKundenfeld]
                ) {
                    // Datum
                    // 1 = leer
                    // 2 = falsches Format
                    // 3 = falsches Datum
                    // 0 = o.k.
                    if ($oKundenfeld->cTyp === 'datum') {
                        $_dat   = StringHandler::filterXSS($data['custom_' . $oKundenfeld->kKundenfeld]);
                        $_datTs = strtotime($_dat);
                        $_dat   = ($_datTs !== false) ? date('d.m.Y', $_datTs) : false;
                        $check  = StringHandler::checkDate($_dat);
                        if ($check !== 0) {
                            $ret['custom'][$oKundenfeld->kKundenfeld] = $check;
                        }
                    } elseif ($oKundenfeld->cTyp === 'zahl') {
                        // Zahl, 4 = keine Zahl
                        if ($data['custom_' . $oKundenfeld->kKundenfeld] != (float)$data['custom_' . $oKundenfeld->kKundenfeld]) {
                            $ret['custom'][$oKundenfeld->kKundenfeld] = 4;
                        }
                    }
                }
            } elseif (empty($data['custom_' . $oKundenfeld->kKundenfeld]) && $oKundenfeld->nPflicht == 1) {
                $ret['custom'][$oKundenfeld->kKundenfeld] = 1;
            } elseif ($data['custom_' . $oKundenfeld->kKundenfeld]) {
                // Datum
                // 1 = leer
                // 2 = falsches Format
                // 3 = falsches Datum
                // 0 = o.k.
                if ($oKundenfeld->cTyp === 'datum') {
                    $_dat   = StringHandler::filterXSS($data['custom_' . $oKundenfeld->kKundenfeld]);
                    $_datTs = strtotime($_dat);
                    $_dat   = ($_datTs !== false) ? date('d.m.Y', $_datTs) : false;
                    $check  = StringHandler::checkDate($_dat);
                    if ($check !== 0) {
                        $ret['custom'][$oKundenfeld->kKundenfeld] = $check;
                    }
                } elseif ($oKundenfeld->cTyp === 'zahl') {
                    // Zahl, 4 = keine Zahl
                    if ($data['custom_' . $oKundenfeld->kKundenfeld] != (float)$data['custom_' . $oKundenfeld->kKundenfeld]) {
                        $ret['custom'][$oKundenfeld->kKundenfeld] = 4;
                    }
                }
            }
        }
    }
    if (isset($conf['kunden']['kundenregistrierung_pruefen_ort'])
        && $conf['kunden']['kundenregistrierung_pruefen_ort'] === 'Y'
        && preg_match('#[0-9]+#', $data['ort'])
    ) {
        $ret['ort'] = 3;
    }
    if (isset($conf['kunden']['kundenregistrierung_pruefen_name'])
        && $conf['kunden']['kundenregistrierung_pruefen_name'] === 'Y'
        && preg_match('#[0-9]+#', $data['nachname'])
    ) {
        $ret['nachname'] = 2;
    }

    if (isset($conf['kunden']['kundenregistrierung_pruefen_zeit'], $data['editRechnungsadresse'])
        && $data['editRechnungsadresse'] != 1
        && $conf['kunden']['kundenregistrierung_pruefen_zeit'] === 'Y'
    ) {
        $dRegZeit = $_SESSION['dRegZeit'] ?? 0;
        if (!($dRegZeit + 5 < time())) {
            $ret['formular_zeit'] = 1;
        }
    }
    if (isset($conf['kunden']['kundenregistrierung_pruefen_email'], $data['email'])
        && $conf['kunden']['kundenregistrierung_pruefen_email'] === 'Y'
        && strlen($data['email']) > 0
        && !checkdnsrr(substr($data['email'], strpos($data['email'], '@') + 1))
    ) {
        $ret['email'] = 4;
    }

    if (isset($conf['kunden']['registrieren_captcha'])
        && $conf['kunden']['registrieren_captcha'] !== 'N'
        && !FormHelper::validateCaptcha($data)
    ) {
        $ret['captcha'] = 2;
    }

    return $ret;
}

/**
 * @param int $kundenaccount
 * @param int $checkpass
 * @return array
 */
function checkKundenFormular($kundenaccount, $checkpass = 1)
{
    $data = $_POST; // create a copy

    return checkKundenFormularArray($data, $kundenaccount, $checkpass);
}

/**
 * @param array $data
 * @return array
 */
function checkLieferFormularArray($data)
{
    $ret  = [];
    $conf = Shop::getSettings([CONF_KUNDEN]);

    foreach (['nachname', 'strasse', 'hausnummer', 'plz', 'ort', 'land'] as $dataKey) {
        $data[$dataKey] = isset($data[$dataKey]) ? trim($data[$dataKey]) : null;

        if (!isset($data[$dataKey]) || !$data[$dataKey]) {
            $ret[$dataKey] = 1;
        }
    }

    foreach ([
             'lieferadresse_abfragen_titel' => 'titel',
             'lieferadresse_abfragen_adresszusatz' => 'adresszusatz',
             'lieferadresse_abfragen_bundesland' => 'bundesland',
             ] as $confKey => $dataKey) {
        if ($conf['kunden'][$confKey] === 'Y') {
            $data[$dataKey] = isset($data[$dataKey]) ? trim($data[$dataKey]) : null;

            if (!$data[$dataKey]) {
                $ret[$dataKey] = 1;
            }
        }
    }

    if ($conf['kunden']['lieferadresse_abfragen_email'] !== 'N') {
        $data['email'] = trim($data['email']);

        if (empty($data['email'])) {
            if ($conf['kunden']['lieferadresse_abfragen_email'] === 'Y') {
                $ret['email'] = 1;
            }
        } elseif (StringHandler::filterEmailAddress($data['email']) === false) {
            $ret['email'] = 2;
        }
    }

    foreach (['tel', 'mobil', 'fax'] as $telType) {
        if ($conf['kunden']["lieferadresse_abfragen_$telType"] !== 'N') {
            $result = StringHandler::checkPhoneNumber(StringHandler::filterXSS($data[$telType]));
            if ($result === 1 && $conf['kunden']["lieferadresse_abfragen_$telType"] === 'Y') {
                $ret[$telType] = 1;
            } elseif ($result > 1) {
                $ret[$telType] = $result;
            }
        }
    }

    return $ret;
}

/**
 * @param array $cPost_arr
 * @return array
 */
function checkLieferFormular($cPost_arr = null)
{
    return checkLieferFormularArray($cPost_arr ?? $_POST);
}

/**
 * @param object|Kupon $Kupon
 * @return array
 * @deprecated since 5.0.0
 */
function checkeKupon($Kupon)
{
    return Kupon::checkCoupon($Kupon);
}

/**
 * @param Kupon|object $Kupon
 * @deprecated since 5.0.0
 */
function kuponAnnehmen($Kupon)
{
    Kupon::acceptCoupon($Kupon);
}

/**
 * liefert Gesamtsumme der Artikel im Warenkorb, welche dem Kupon zugeordnet werden können
 *
 * @param Kupon|object $Kupon
 * @param array $PositionenArr
 * @return bool
 */
function gibGesamtsummeKuponartikelImWarenkorb($Kupon, $PositionenArr)
{
    $gesamtsumme = 0;
    if (is_array($PositionenArr)) {
        foreach ($PositionenArr as $Position) {
            if ((empty($Kupon->cArtikel) || warenkorbKuponFaehigArtikel($Kupon, [$Position]))
                && (empty($Kupon->cHersteller) || $Kupon->cHersteller === '-1' || warenkorbKuponFaehigHersteller($Kupon,
                        [$Position]))
                && (empty($Kupon->cKategorien) || $Kupon->cKategorien === '-1' || warenkorbKuponFaehigKategorien($Kupon,
                        [$Position]))) {
                $gesamtsumme += $Position->fPreis *
                    $Position->nAnzahl *
                    ((100 + TaxHelper::getSalesTax($Position->kSteuerklasse)) / 100);
            }
        }
    }

    return round($gesamtsumme, 2);
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
            if ($Pos->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL
                && preg_match('/;' . preg_quote($Pos->Artikel->cArtNr, '/') . ';/i', $Kupon->cArtikel)
            ) {
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
function warenkorbKuponFaehigHersteller($Kupon, array $PositionenArr)
{
    foreach ($PositionenArr as $Pos) {
        if ($Pos->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL
            && preg_match('/;' . preg_quote($Pos->Artikel->kHersteller, '/') . ';/i', $Kupon->cHersteller)
        ) {
            return true;
        }
    }

    return false;
}

/**
 * @param Kupon|object $Kupon
 * @param array $PositionenArr
 * @return bool
 */
function warenkorbKuponFaehigKategorien($Kupon, array $PositionenArr)
{
    $Kats = [];
    foreach ($PositionenArr as $Pos) {
        if (empty($Pos->Artikel)) {
            continue;
        }
        $kArtikel = $Pos->Artikel->kArtikel;
        // Kind?
        if (ArtikelHelper::isVariChild($kArtikel)) {
            $kArtikel = ArtikelHelper::getParent($kArtikel);
        }
        $Kats_arr = Shop::Container()->getDB()->selectAll('tkategorieartikel', 'kArtikel', (int)$kArtikel, 'kKategorie');
        if (is_array($Kats_arr)) {
            foreach ($Kats_arr as $Kat) {
                $Kat->kKategorie = (int)$Kat->kKategorie;
                if (!in_array($Kat->kKategorie, $Kats, true)) {
                    $Kats[] = $Kat->kKategorie;
                }
            }
        }
    }
    foreach ($Kats as $Kat) {
        if (preg_match('/;' . preg_quote($Kat, '/') . ';/i', $Kupon->cKategorien)) {
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
    $mapping = [
        'anrede'            => 'cAnrede',
        'vorname'           => 'cVorname',
        'nachname'          => 'cNachname',
        'strasse'           => 'cStrasse',
        'hausnummer'        => 'cHausnummer',
        'plz'               => 'cPLZ',
        'ort'               => 'cOrt',
        'land'              => 'cLand',
        'email'             => 'cMail',
        'tel'               => 'cTel',
        'fax'               => 'cFax',
        'firma'             => 'cFirma',
        'firmazusatz'       => 'cZusatz',
        'bundesland'        => 'cBundesland',
        'titel'             => 'cTitel',
        'adresszusatz'      => 'cAdressZusatz',
        'mobil'             => 'cMobil',
        'www'               => 'cWWW',
        'ustid'             => 'cUSTID',
        'geburtstag'        => 'dGeburtstag',
        'kundenherkunft'    => 'cHerkunft',
    ];

    if ($kundenaccount !== 0) {
        $mapping['pass'] = 'cPasswort';
    }

    //erstelle neuen Kunden
    $kKunde   = isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0 ? (int)$_SESSION['Kunde']->kKunde : 0;
    $customer = new Kunde($kKunde);

    foreach ($mapping as $external => $internal) {
        if (isset($post[$external])) {
            $val = StringHandler::filterXSS($post[$external]);
            if ($htmlentities) {
                $val = StringHandler::htmlentities($val);
            }
            $customer->$internal = $val;
        }
    }

    if (preg_match('/^\d{2}\.\d{2}\.(\d{4})$/', $customer->dGeburtstag)) {
        $customer->dGeburtstag = DateTime::createFromFormat('d.m.Y', $customer->dGeburtstag)->format('Y-m-d');
    }
    $customer->angezeigtesLand = Sprache::getCountryCodeByCountryName($customer->cLand);
    if (!empty($customer->cBundesland)) {
        $oISO = Staat::getRegionByIso($customer->cBundesland, $customer->cLand);
        if (is_object($oISO)) {
            $customer->cBundesland = $oISO->cName;
        }
    }

    return $customer;
}

/**
 * @param array $cPost_arr
 * @return array
 */
function getKundenattribute($cPost_arr)
{
    $cKundenattribut_arr = [];
    $oKundenfeld_arr     = Shop::Container()->getDB()->selectAll(
        'tkundenfeld',
        'kSprache',
        Shop::getLanguage(),
        'kKundenfeld, cName, cWawi'
    );
    if (is_array($oKundenfeld_arr) && count($oKundenfeld_arr) > 0) {
        foreach ($oKundenfeld_arr as $oKundenfeldTMP) {
            $oKundenfeld              = new stdClass();
            $oKundenfeld->kKundenfeld = $oKundenfeldTMP->kKundenfeld;
            $oKundenfeld->cName       = $oKundenfeldTMP->cName;
            $oKundenfeld->cWawi       = $oKundenfeldTMP->cWawi;
            $oKundenfeld->cWert       = isset($cPost_arr['custom_' . $oKundenfeldTMP->kKundenfeld])
                ? StringHandler::filterXSS($cPost_arr['custom_' . $oKundenfeldTMP->kKundenfeld])
                : null;

            $cKundenattribut_arr[$oKundenfeldTMP->kKundenfeld] = $oKundenfeld;
        }
    }

    return $cKundenattribut_arr;
}

/**
 * @return array
 */
function getKundenattributeNichtEditierbar()
{
    return Shop::Container()->getDB()->selectAll('tkundenfeld', ['kSprache', 'nEditierbar'], [Shop::getLanguage(), 0], 'kKundenfeld');
}

/**
 * @return array - non editable customer fields
 */
function getNonEditableCustomerFields()
{
    $cKundenAttribute_arr = [];
    $oKundenattribute_arr = Shop::Container()->getDB()->query(
        "SELECT ka.kKundenfeld
             FROM tkundenattribut AS ka
             LEFT JOIN tkundenfeld AS kf
                ON ka.kKundenfeld = kf.kKundenfeld
             WHERE kKunde = " . Session::Customer()->getID() . "
             AND kf.nEditierbar = 0",
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($oKundenattribute_arr as $oKundenattribute) {
        $oKundenfeldAttribut                                  = new stdClass();
        $oKundenfeldAttribut->kKundenfeld                     = $oKundenattribute->kKundenfeld;
        $cKundenAttribute_arr[$oKundenattribute->kKundenfeld] = $oKundenfeldAttribut;
    }

    return $cKundenAttribute_arr;
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
    $Lieferadresse->cMail           = isset($post['email'])
        ? StringHandler::filterXSS($post['email'])
        : '';
    $Lieferadresse->cTel            = isset($post['tel'])
        ? StringHandler::filterXSS($post['tel'])
        : null;
    $Lieferadresse->cFax            = isset($post['fax'])
        ? StringHandler::filterXSS($post['fax'])
        : null;
    $Lieferadresse->cFirma          = isset($post['firma'])
        ? StringHandler::filterXSS($post['firma'])
        : null;
    $Lieferadresse->cZusatz         = isset($post['firmazusatz'])
        ? StringHandler::filterXSS($post['firmazusatz'])
        : null;
    $Lieferadresse->cTitel          = isset($post['titel'])
        ? StringHandler::filterXSS($post['titel'])
        : null;
    $Lieferadresse->cAdressZusatz   = isset($post['adresszusatz'])
        ? StringHandler::filterXSS($post['adresszusatz'])
        : null;
    $Lieferadresse->cMobil          = isset($post['mobil'])
        ? StringHandler::filterXSS($post['mobil'])
        : null;
    $Lieferadresse->cBundesland     = isset($post['bundesland'])
        ? StringHandler::filterXSS($post['bundesland'])
        : null;
    $Lieferadresse->angezeigtesLand = Sprache::getCountryCodeByCountryName($Lieferadresse->cLand);

    if (!empty($Lieferadresse->cBundesland)) {
        $oISO = Staat::getRegionByIso($Lieferadresse->cBundesland, $Lieferadresse->cLand);
        if (is_object($oISO)) {
            $Lieferadresse->cBundesland = $oISO->cName;
        }
    }

    return $Lieferadresse;
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
                $ret .= " OR FIND_IN_SET('" .
                    str_replace('%', '\%', Shop::Container()->getDB()->escape($Pos->Artikel->cArtNr))
                    . "', REPLACE(cArtikel, ';', ',')) > 0";
            }
        }
    }

    return $ret;
}

/**
 * @return bool
 */
function guthabenMoeglich()
{
    return ($_SESSION['Kunde']->fGuthaben > 0
            && (empty($_SESSION['Bestellung']->GuthabenNutzen) || !$_SESSION['Bestellung']->GuthabenNutzen))
        && strpos($_SESSION['Zahlungsart']->cModulId, 'za_billpay') !== 0;
}

/**
 * @return int
 * @deprecated since 5.0.0
 */
function kuponMoeglich()
{
    return Kupon::couponsAvailable();
}


/**
 * @return bool
 */
function freeGiftStillValid()
{
    $cart  = Session::Cart();
    $valid = true;
    foreach ($cart->PositionenArr as $oPosition) {
        if ($oPosition->nPosTyp === C_WARENKORBPOS_TYP_GRATISGESCHENK) {
            // Prüfen ob der Artikel wirklich ein Gratisgeschenk ist und ob die Mindestsumme erreicht wird
            $oArtikelGeschenk = Shop::Container()->getDB()->query(
                "SELECT kArtikel
                    FROM tartikelattribut
                    WHERE kArtikel = " . (int)$oPosition->kArtikel . "
                       AND cName = '" . FKT_ATTRIBUT_GRATISGESCHENK . "'
                       AND CAST(cWert AS DECIMAL) <= " .
                            $cart->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], true),
                \DB\ReturnType::SINGLE_OBJECT
            );

            if (empty($oArtikelGeschenk->kArtikel)) {
                $cart->loescheSpezialPos(C_WARENKORBPOS_TYP_GRATISGESCHENK);
                $valid = false;
            }
            break;
        }
    }

    return $valid;
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
    $cSupportedCountry_arr = ['DE', 'AT', 'CH'];
    if (in_array(strtoupper($land), $cSupportedCountry_arr, true)) {
        $obj = Shop::Container()->getDB()->executeQueryPrepared(
            "SELECT kPLZ
                FROM tplz
                WHERE cPLZ = :plz
                AND cOrt LIKE :ort
                AND cLandISO = :land",
            [
                'plz'  => $plz,
                'ort'  => '%' . $ort . '%',
                'land' => $land
            ],
            1
        );
        if (isset($obj->kPLZ) && $obj->kPLZ > 0) {
            return true;
        }
        $obj = Shop::Container()->getDB()->executeQueryPrepared(
            "SELECT kPLZ
                FROM tplz
                WHERE cPLZ = :plz
                AND cOrt LIKE :ort
                AND cLandISO = :land",
            [
                'plz'  => $plz,
                'ort'  => umlauteUmschreibenA2AE($ort),
                'land' => $land
            ],
            1
        );
        if (isset($obj->kPLZ) && $obj->kPLZ > 0) {
            return true;
        }
        $obj = Shop::Container()->getDB()->executeQueryPrepared(
            "SELECT kPLZ
                FROM tplz
                WHERE cPLZ = :plz
                AND cOrt LIKE :ort
                AND cLandISO = :land",
            [
                'plz'  => $plz,
                'ort'  => umlauteUmschreibenAE2A($ort),
                'land' => $land
            ],
            1
        );
        return (isset($obj->kPLZ) && $obj->kPLZ > 0);
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
    $src = ['ä', 'ö', 'ü', 'ß', 'Ä', 'Ö', 'Ü'];
    $rpl = ['ae', 'oe', 'ue', 'ss', 'Ae', 'Oe', 'Ue', 'ae', 'oe', 'ue', 'ss', 'Ae', 'Oe', 'Ue'];

    return str_replace($src, $rpl, $str);
}

/**
 * @param string $str
 * @return string
 */
function umlauteUmschreibenAE2A($str)
{
    $rpl = ['ä', 'ö', 'ü', 'ß', 'Ä', 'Ö', 'Ü'];
    $src = ['ae', 'oe', 'ue', 'ss', 'Ae', 'Oe', 'Ue'];

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
        case 'accountwahl':
        case 'edit_customer_address':
            $schritt[1] = 1;
            $schritt[2] = 3;
            $schritt[3] = 3;
            $schritt[4] = 3;
            $schritt[5] = 3;
            break;

        case 'Lieferadresse':
            $schritt[1] = 2;
            $schritt[2] = 1;
            $schritt[3] = 3;
            $schritt[4] = 3;
            $schritt[5] = 3;
            break;

        case 'Versand':
            $schritt[1] = 2;
            $schritt[2] = 2;
            $schritt[3] = 1;
            $schritt[4] = 3;
            $schritt[5] = 3;
            break;

        case 'Zahlung':
            $schritt[1] = 2;
            $schritt[2] = 2;
            $schritt[3] = 2;
            $schritt[4] = 1;
            $schritt[5] = 3;
            break;

        case 'ZahlungZusatzschritt':
            $schritt[1] = 2;
            $schritt[2] = 2;
            $schritt[3] = 2;
            $schritt[4] = 1;
            $schritt[5] = 3;
            break;

        case 'Bestaetigung':
            $schritt[1] = 2;
            $schritt[2] = 2;
            $schritt[3] = 2;
            $schritt[4] = 2;
            $schritt[5] = 1;
            break;

        default:
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
    $Lieferadresse->angezeigtesLand = Sprache::getCountryCodeByCountryName($Lieferadresse->cLand);
    $_SESSION['Lieferadresse']      = $Lieferadresse;

    return $Lieferadresse;
}

/**
 * @return mixed
 */
function gibSelbstdefKundenfelder()
{
    $oKundenfeld_arr = Shop::Container()->getDB()->query(
        "SELECT *
            FROM tkundenfeld
            WHERE kSprache = " . Shop::getLanguageID(). "
            ORDER BY nSort ASC",
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    // tkundenfeldwert nachschauen ob dort Werte für tkundenfeld enthalten sind
    foreach ($oKundenfeld_arr as $oKundenfeld) {
        if ($oKundenfeld->cTyp === 'auswahl') {
            $oKundenfeld->oKundenfeldWert_arr = Shop::Container()->getDB()->selectAll(
                'tkundenfeldwert',
                'kKundenfeld',
                (int)$oKundenfeld->kKundenfeld,
                '*',
                '`kKundenfeld`, `nSort`, `kKundenfeldWert` ASC'
            );
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
    if (($customerID = Session::Customer()->getID()) > 0) {
        $customerGroupID = Session::CustomerGroup()->getID();
        // Prüfe ob Kunde schon bestellt hat, falls ja --> Lieferdaten laden
        $oLetzteBestellung = Shop::Container()->getDB()->query(
            "SELECT tbestellung.kBestellung, tbestellung.kLieferadresse, tbestellung.kZahlungsart, tbestellung.kVersandart
                FROM tbestellung
                JOIN tzahlungsart
                    ON tzahlungsart.kZahlungsart = tbestellung.kZahlungsart
                    AND (tzahlungsart.cKundengruppen IS NULL
                        OR tzahlungsart.cKundengruppen = ''
                        OR FIND_IN_SET('{$customerGroupID}', REPLACE(tzahlungsart.cKundengruppen, ';', ',')) > 0)
                JOIN tversandart
                    ON tversandart.kVersandart = tbestellung.kVersandart
                    AND (tversandart.cKundengruppen = '-1'
                        OR FIND_IN_SET('{$customerGroupID}', REPLACE(tversandart.cKundengruppen, ';', ',')) > 0)
                JOIN tversandartzahlungsart
                    ON tversandartzahlungsart.kVersandart = tversandart.kVersandart
                    AND tversandartzahlungsart.kZahlungsart = tzahlungsart.kZahlungsart
                WHERE tbestellung.kKunde = {$customerID}
                ORDER BY tbestellung.dErstellt
                DESC LIMIT 1",
            \DB\ReturnType::SINGLE_OBJECT
        );

        if (isset($oLetzteBestellung->kBestellung) && $oLetzteBestellung->kBestellung > 0) {
            // Hat der Kunde eine Lieferadresse angegeben?
            if ($oLetzteBestellung->kLieferadresse > 0) {
                $oLieferdaten = Shop::Container()->getDB()->query(
                    "SELECT kLieferadresse
                        FROM tlieferadresse
                        WHERE kKunde = " . $customerID . "
                            AND kLieferadresse = " . (int)$oLetzteBestellung->kLieferadresse,
                    \DB\ReturnType::SINGLE_OBJECT
                );

                if ($oLieferdaten->kLieferadresse > 0) {
                    $oLieferdaten              = new Lieferadresse($oLieferdaten->kLieferadresse);
                    $_SESSION['Lieferadresse'] = $oLieferdaten;
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
            TaxHelper::setTaxRates();

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
                        if ($nZahglungsartStatus === 2) {
                            // Prüfen ab es ein Trusted Shops Zertifikat gibt
                            $oTrustedShops = new TrustedShops(-1, StringHandler::convertISO2ISO639($_SESSION['cISOSprache']));
                            if (strlen($oTrustedShops->tsId) > 0) {
                                return 4;
                            }
                            gibStepZahlung();

                            return 5;
                        }
                        unset($_SESSION['Zahlungsart']);

                        return 4;
                    }
                    unset($_SESSION['Zahlungsart']);

                    return 4;
                }

                return 3;
            }

            return 3;
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
        ->assign('AGB', Shop::Container()->getLinkService()->getAGBWRB(
            Shop::getLanguage(),
            Session::CustomerGroup()->getID()
        ))
        ->assign('WarensummeLocalized', Session::Cart()->gibGesamtsummeWarenLocalized())
        ->assign('Warensumme', Session::Cart()->gibGesamtsummeWaren());
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
            $_upd         = new stdClass();
            $_upd->kKunde = (int)$oKunde->kKunde;
            Shop::Container()->getDB()->update('tbesucher', 'kBesucher', (int)$_SESSION['oBesucher']->kBesucher, $_upd);
        }
        Session::Cart()->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR)
                       ->loescheSpezialPos(C_WARENKORBPOS_TYP_NEUKUNDENKUPON)
                       ->loescheSpezialPos(C_WARENKORBPOS_TYP_KUPON)
                       ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR);
        unset(
            $_SESSION['Zahlungsart'],
            $_SESSION['Versandart'],
            $_SESSION['Lieferadresse'],
            $_SESSION['ks'],
            $_SESSION['VersandKupon'],
            $_SESSION['oVersandfreiKupon'],
            $_SESSION['NeukundenKupon'],
            $_SESSION['Kupon']
        );
        $oKunde->angezeigtesLand = Sprache::getCountryCodeByCountryName($oKunde->cLand);
        $session                 = \Session\Session::getInstance();
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
    Shop::Smarty()->assign('untertitel', lang_warenkorb_bestellungEnthaeltXArtikel(Session::Cart()));
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
        $_SESSION['Kunde']           = $oKunde;
        if (isset($_SESSION['Warenkorb']->kWarenkorb)
            && Session::Cart()->gibAnzahlArtikelExt([C_WARENKORBPOS_TYP_ARTIKEL]) > 0
        ) {
            if ($_SESSION['Bestellung']->kLieferadresse == 0 && $_SESSION['Lieferadresse']) {
                setzeLieferadresseAusRechnungsadresse();
            }
            TaxHelper::setTaxRates();
            Session::Cart()->gibGesamtsummeWarenLocalized();
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
    $conf      = Shop::getSettings([CONF_KUNDEN]);
    $herkunfte = Shop::Container()->getDB()->query(
        "SELECT * 
            FROM tkundenherkunft 
            ORDER BY nSort",
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
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
        ->assign('laender', VersandartHelper::getPossibleShippingCountries(Session::CustomerGroup()->getID()))
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
    if ((int)$nCheckout === 1) {
        Shop::Smarty()->assign('checkout', 1);
    }
}

/**
 * @param array $cFehlendeEingaben_arr
 * @param int   $nUnreg
 * @param array $cPost_arr
 */
function setzeFehlerSmartyRechnungsadresse($cFehlendeEingaben_arr, $nUnreg = 0, $cPost_arr = null)
{
    $conf = Shop::getSettings([CONF_KUNDEN]);
    setzeFehlendeAngaben($cFehlendeEingaben_arr);
    $herkunfte  = Shop::Container()->getDB()->query(
        "SELECT *
            FROM tkundenherkunft
            ORDER BY nSort",
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    $oKunde_tmp = getKundendaten($cPost_arr, 0);
    if (preg_match('/^\d{4}\-\d{2}\-(\d{2})$/', $oKunde_tmp->dGeburtstag)) {
        list($jahr, $monat, $tag) = explode('-', $oKunde_tmp->dGeburtstag);
        $oKunde_tmp->dGeburtstag  = $tag . '.' . $monat . '.' . $jahr;
    }
    Shop::Smarty()->assign('untertitel', Shop::Lang()->get('fillUnregForm', 'checkout'))
        ->assign('herkunfte', $herkunfte)
        ->assign('Kunde', $oKunde_tmp)
        ->assign('laender', VersandartHelper::getPossibleShippingCountries(Session::CustomerGroup()->getID()))
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
    $cFehlendeEingaben_arr                  = [];
    $_SESSION['Bestellung']->kLieferadresse = (int)$cPost_arr['kLieferadresse'];
    //neue lieferadresse
    if ((int)$cPost_arr['kLieferadresse'] === -1) {
        $cFehlendeAngaben_arr = checkLieferFormular($cPost_arr);
        if (angabenKorrekt($cFehlendeAngaben_arr)) {
            return $cFehlendeEingaben_arr;
        }

        return $cFehlendeAngaben_arr;
    }
    if ((int)$cPost_arr['kLieferadresse'] > 0) {
        //vorhandene lieferadresse
        $oLieferadresse = Shop::Container()->getDB()->select(
            'tlieferadresse',
            'kKunde',
            (int)$_SESSION['Kunde']->kKunde,
            'kLieferadresse',
            (int)$cPost_arr['kLieferadresse']
        );
        if (isset($oLieferadresse->kLieferadresse) && $oLieferadresse->kLieferadresse > 0) {
            $oLieferadresse            = new Lieferadresse($oLieferadresse->kLieferadresse);
            $_SESSION['Lieferadresse'] = $oLieferadresse;
        }
    } elseif ((int)$cPost_arr['kLieferadresse'] === 0) {
        //lieferadresse gleich rechnungsadresse
        setzeLieferadresseAusRechnungsadresse();
    }
    TaxHelper::setTaxRates();
    //lieferland hat sich geändert und versandart schon gewählt?
    if ($_SESSION['Lieferadresse'] && $_SESSION['Versandart']) {
        $delVersand = (stripos($_SESSION['Versandart']->cLaender, $_SESSION['Lieferadresse']->cLand) === false);
        //ist die plz im zuschlagsbereich?
        $plz_x = Shop::Container()->getDB()->executeQueryPrepared(
            "SELECT kVersandzuschlagPlz
                FROM tversandzuschlagplz, tversandzuschlag
                WHERE tversandzuschlag.kVersandart = :id
                    AND tversandzuschlag.kVersandzuschlag = tversandzuschlagplz.kVersandzuschlag
                    AND ((tversandzuschlagplz.cPLZAb <= :plz
                        AND tversandzuschlagplz.cPLZBis >= :plz)
                        OR tversandzuschlagplz.cPLZ = :plz)",
            [
                'id'  => (int)$_SESSION['Versandart']->kVersandart,
                'plz' => $_SESSION['Lieferadresse']->cPLZ
            ],
            1
        );
        if (isset($plz_x->kVersandzuschlagPlz) && $plz_x->kVersandzuschlagPlz) {
            $delVersand = true;
        }
        if ($delVersand) {
            Session::Cart()->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
                           ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG)
                           ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR)
                           ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
                           ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
                           ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR);
            unset($_SESSION['Versandart'], $_SESSION['Zahlungsart']);
        }
        if (!$delVersand) {
            Session::Cart()->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG);
        }
    }

    return $cFehlendeEingaben_arr;
}

/**
 * @param array $cPost_arr
 */
function setzeSessionLieferadresse($cPost_arr)
{
    $kLieferadresse = isset($cPost_arr['kLieferadresse']) ? (int)$cPost_arr['kLieferadresse'] : -1;

    $_SESSION['Bestellung']->kLieferadresse = $kLieferadresse;
    //neue lieferadresse
    if ($kLieferadresse === -1) {
        $_SESSION['Lieferadresse'] = getLieferdaten($cPost_arr);
    } elseif ($kLieferadresse > 0) {
        //vorhandene lieferadresse
        $LA = Shop::Container()->getDB()->query(
            "SELECT kLieferadresse
                FROM tlieferadresse
                WHERE kKunde = " . Session::Customer()->getID() . "
                AND kLieferadresse = " . (int)$cPost_arr['kLieferadresse'],
            \DB\ReturnType::SINGLE_OBJECT
        );
        if ($LA->kLieferadresse > 0) {
            $_SESSION['Lieferadresse'] = new Lieferadresse($LA->kLieferadresse);
        }
    } elseif ($kLieferadresse === 0) { //lieferadresse gleich rechnungsadresse
        setzeLieferadresseAusRechnungsadresse();
    }
    TaxHelper::setTaxRates();
    //guthaben
    if ((int)$cPost_arr['guthabenVerrechnen'] === 1) {
        $_SESSION['Bestellung']->GuthabenNutzen   = 1;
        $_SESSION['Bestellung']->fGuthabenGenutzt = min(
            $_SESSION['Kunde']->fGuthaben,
            Session::Cart()->gibGesamtsummeWaren(true, false)
        );
    } else {
        unset($_SESSION['Bestellung']->GuthabenNutzen, $_SESSION['Bestellung']->fGuthabenGenutzt);
    }
}

/**
 *
 */
function setzeSmartyLieferadresse()
{
    $kKundengruppe = Session::CustomerGroup()->getID();
    if (Session::Customer()->getID() > 0) {
        $Lieferadressen      = [];
        $oLieferdatenTMP_arr = Shop::Container()->getDB()->selectAll(
            'tlieferadresse',
            'kKunde',
            Session::Customer()->getID(),
            'kLieferadresse'
        );
        foreach ($oLieferdatenTMP_arr as $oLieferdatenTMP) {
            if ($oLieferdatenTMP->kLieferadresse > 0) {
                $Lieferadressen[] = new Lieferadresse($oLieferdatenTMP->kLieferadresse);
            }
        }
        $kKundengruppe = Session::Customer()->kKundengruppe;
        Shop::Smarty()->assign('Lieferadressen', $Lieferadressen)
            ->assign('GuthabenLocalized', Session::Customer()->gibGuthabenLocalized());
    }
    Shop::Smarty()->assign('laender', VersandartHelper::getPossibleShippingCountries($kKundengruppe))
        ->assign('Kunde', $_SESSION['Kunde'])
        ->assign('KuponMoeglich', Kupon::couponsAvailable())
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
    /** @var array('Kunde' => Kunde) $_SESSION */
    $kKundengruppe = Session::CustomerGroup()->getID();
    if (Session::Customer()->getID() > 0) {
        $Lieferadressen      = [];
        $oLieferdatenTMP_arr = Shop::Container()->getDB()->selectAll(
            'tlieferadresse',
            'kKunde',
            (int)$_SESSION['Kunde']->kKunde,
            'kLieferadresse'
        );
        foreach ($oLieferdatenTMP_arr as $oLieferdatenTMP) {
            if ($oLieferdatenTMP->kLieferadresse > 0) {
                $Lieferadressen[] = new Lieferadresse($oLieferdatenTMP->kLieferadresse);
            }
        }
        $kKundengruppe = $_SESSION['Kunde']->kKundengruppe;
        Shop::Smarty()->assign('Lieferadressen', $Lieferadressen)
            ->assign('GuthabenLocalized', Session::Customer()->gibGuthabenLocalized());
    }
    setzeFehlendeAngaben($cFehlendeEingaben_arr, 'shipping_address');
    Shop::Smarty()->assign('laender', VersandartHelper::getPossibleShippingCountries($kKundengruppe))
        ->assign('Kunde', $_SESSION['Kunde'])
        ->assign('KuponMoeglich', Kupon::couponsAvailable())
        ->assign('kLieferadresse', $_SESSION['Bestellung']->kLieferadresse)
        ->assign('kLieferadresse', $cPost_arr['kLieferadresse']);
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
    $conf            = Shop::getSettings([CONF_TRUSTEDSHOPS]);
    $zahlungsangaben = zahlungsartKorrekt((int)$oZahlungsart->kZahlungsart);
    // Trusted Shops
    if ((int)$cPost_arr['bTS'] === 1
        && $zahlungsangaben > 0
        && $_SESSION['Zahlungsart']->nWaehrendBestellung == 0
        && $conf['trustedshops']['trustedshops_nutzen'] === 'Y'
    ) {
        $fNetto        = $_SESSION['TrustedShops']->oKaeuferschutzProduktIDAssoc_arr[StringHandler::htmlentities(
            StringHandler::filterXSS($cPost_arr['cKaeuferschutzProdukt'])
        )];
        $kSteuerklasse = Session::Cart()->gibVersandkostenSteuerklasse();
        $fPreis        = $fNetto;
        if (!Session::CustomerGroup()->isMerchant()) {
            $fPreis = $fNetto * ((100 + (float)$_SESSION['Steuersatz'][$kSteuerklasse]) / 100);
        }
        $cName['ger']                                    = Shop::Lang()->get('trustedshopsName');
        $cName['eng']                                    = Shop::Lang()->get('trustedshopsName');
        $_SESSION['TrustedShops']->cKaeuferschutzProdukt = StringHandler::htmlentities(
            StringHandler::filterXSS($cPost_arr['cKaeuferschutzProdukt'])
        );
        Session::Cart()->erstelleSpezialPos(
            $cName,
            1,
            $fPreis,
            $kSteuerklasse,
            C_WARENKORBPOS_TYP_TRUSTEDSHOPS
        );
    }

    return checkAdditionalPayment($oZahlungsart);
}

/**
 * @param array     $cPost_arr
 * @param int|array $cFehlendeEingaben_arr
 */
function setzeSmartyZahlungsartZusatz($cPost_arr, $cFehlendeEingaben_arr = 0)
{
    $Zahlungsart = gibZahlungsart($cPost_arr['Zahlungsart']);
    // Wenn Zahlungsart = Lastschrift ist => versuche Kundenkontodaten zu holen
    $oKundenKontodaten = gibKundenKontodaten($_SESSION['Kunde']->kKunde);
    if (!empty($oKundenKontodaten->kKunde)) {
        Shop::Smarty()->assign('oKundenKontodaten', $oKundenKontodaten);
    }
    if (empty($cPost_arr['zahlungsartzusatzschritt'])) {
        Shop::Smarty()->assign('ZahlungsInfo', $_SESSION['Zahlungsart']->ZahlungsInfo);
    } else {
        setzeFehlendeAngaben($cFehlendeEingaben_arr);
        Shop::Smarty()->assign('ZahlungsInfo', gibPostZahlungsInfo());
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
        ->assign('AGB', Shop::Container()->getLinkService()->getAGBWRB(
            Shop::getLanguage(),
            Session::CustomerGroup()->getID()
        ))
        ->assign('WarensummeLocalized', Session::Cart()->gibGesamtsummeWarenLocalized())
        ->assign('Warensumme', Session::Cart()->gibGesamtsummeWaren());
    // SafetyPay Work Around
    if ($_SESSION['Zahlungsart']->cModulId === 'za_safetypay') {
        require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'safetypay/safetypay.php';
        $conf = Shop::getSettings([CONF_ZAHLUNGSARTEN]);
        Shop::Smarty()->assign('safetypay_form', gib_safetypay_form($_SESSION['Kunde'], Session::Cart(), $conf['zahlungsarten']));
    }
}

/**
 * @param array $fehlendeAngabe
 * @param null $context
 */
function setzeFehlendeAngaben($fehlendeAngabe, $context = null)
{
    $fehlendeAngaben = Shop::Smarty()->getTemplateVars('fehlendeAngaben');
    if (!is_array($fehlendeAngaben)) {
        $fehlendeAngaben = [];
    }
    if (empty($context)) {
        $fehlendeAngaben = array_merge($fehlendeAngaben, $fehlendeAngabe);
    } else {
        $fehlendeAngaben[$context] = isset($fehlendeAngaben[$context]) ? array_merge($fehlendeAngaben[$context], $fehlendeAngabe) : $fehlendeAngabe;
    }

    Shop::Smarty()->assign('fehlendeAngaben', $fehlendeAngaben);
}

/**
 * Globale Funktionen
 */
function globaleAssigns()
{
    global $step, $hinweis, $Einstellungen;
    Shop::Smarty()->assign('AGB', Shop::Container()->getLinkService()->getAGBWRB(
            Shop::getLanguage(),
            Session::CustomerGroup()->getID()
        ))
        ->assign('Ueberschrift', Shop::Lang()->get('orderStep0Title', 'checkout'))
        ->assign('UeberschriftKlein', Shop::Lang()->get('orderStep0Title2', 'checkout'))
        ->assign('Einstellungen', $Einstellungen)
        ->assign('hinweis', $hinweis)
        ->assign('step', $step)
        ->assign('WarensummeLocalized', Session::Cart()->gibGesamtsummeWarenLocalized())
        ->assign('Warensumme', Session::Cart()->gibGesamtsummeWaren())
        ->assign('Steuerpositionen', Session::Cart()->gibSteuerpositionen())
        ->assign('bestellschritt', gibBestellschritt($step))
        ->assign('sess', $_SESSION);
}

/**
 * @param int $nStep
 */
function loescheSession($nStep)
{
    switch ($nStep) {
        case 0:
            unset(
                $_SESSION['Kunde'],
                $_SESSION['Lieferadresse'],
                $_SESSION['Versandart'],
                $_SESSION['oVersandfreiKupon'],
                $_SESSION['Zahlungsart'],
                $_SESSION['TrustedShops']
            );
            Session::Cart()->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
                           ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG)
                           ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG)
                           ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
                           ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
                           ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR)
                           ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR)
                           ->loescheSpezialPos(C_WARENKORBPOS_TYP_TRUSTEDSHOPS);
            break;

        case 1:
            unset(
                $_SESSION['Lieferadresse'],
                $_SESSION['Versandart'],
                $_SESSION['oVersandfreiKupon'],
                $_SESSION['Zahlungsart'],
                $_SESSION['TrustedShops']
            );
            Session::Cart()->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
                           ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG)
                           ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG)
                           ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
                           ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
                           ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR)
                           ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR)
                           ->loescheSpezialPos(C_WARENKORBPOS_TYP_TRUSTEDSHOPS);
            break;

        case 2:
            unset($_SESSION['Lieferadresse'], $_SESSION['Versandart'], $_SESSION['oVersandfreiKupon']);
            Session::Cart()->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
                           ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG)
                           ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG)
                           ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
                           ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
                           ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR)
                           ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR)
                           ->loescheSpezialPos(C_WARENKORBPOS_TYP_TRUSTEDSHOPS);
            unset($_SESSION['TrustedShops'], $_SESSION['Zahlungsart']);
            break;

        case 3:
            unset(
                $_SESSION['Versandart'],
                $_SESSION['oVersandfreiKupon'],
                $_SESSION['Zahlungsart'],
                $_SESSION['TrustedShops']
            );
            Session::Cart()->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
                           ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG)
                           ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG)
                           ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
                           ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
                           ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR)
                           ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR)
                           ->loescheSpezialPos(C_WARENKORBPOS_TYP_TRUSTEDSHOPS);
            break;

        case 4:
            unset($_SESSION['Zahlungsart'], $_SESSION['TrustedShops']);
            Session::Cart()->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
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
 * @todo: check if this is only used by the old EOS payment method
 */
function mappeBestellvorgangZahlungshinweis($nHinweisCode)
{
    $cHinweis = '';
    if ((int)$nHinweisCode > 0) {
        switch ($nHinweisCode) {
            // 1-30 EOS
            case 1: // EOS_BACKURL_CODE
                $cHinweis = Shop::Lang()->get('eosErrorBack', 'checkout');
                break;

            case 3: // EOS_FAILURL_CODE
                $cHinweis = Shop::Lang()->get('eosErrorFailure', 'checkout');
                break;

            case 4: // EOS_ERRORURL_CODE
                $cHinweis = Shop::Lang()->get('eosErrorError', 'checkout');
                break;
        }
    }

    executeHook(HOOK_BESTELLVORGANG_INC_MAPPEBESTELLVORGANGZAHLUNGSHINWEIS, [
        'cHinweis'     => &$cHinweis,
        'nHinweisCode' => $nHinweisCode
    ]);

    return $cHinweis;
}

/**
 * @param string $email
 * @return bool
 */
function isEmailAvailable($email)
{
    return strlen($email) > 0
        && (Shop::Container()->getDB()->select('tkunde', 'cMail', $email, 'nRegistriert', 1) === null);
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
 * @return int|mixed
 * @deprecated since 4.0
 */
function gibIloxxAufpreis($Zahlungsart)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $fWarenwert = Session::Cart()->gibGesamtsummeWaren(true);
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
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
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
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return versandartKorrekt($kVersandart, $cPost_arr);
}

/**
 * @deprecated since 4.0
 */
function setzeSmartyZahlungsart()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    gibStepZahlung();
}

/**
 * @param Zahlungsart $Zahlungsart
 * @return array
 * @deprecated since 4.0
 */
function gibFehlendeAngabenZahlungsart($Zahlungsart)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return checkAdditionalPayment($Zahlungsart);
}

/**
 * @param Zahlungsart $Zahlungsart
 * @deprecated since 4.0
 */
function setzeSessionZahlungsart($Zahlungsart)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
}

/**
 * @param Zahlungsart $Zahlungsart
 * @return null
 * @deprecated since 4.05
 */
function gibSpecials($Zahlungsart)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return null;
}

/**
 * @param array $cPost_arr
 * @param int   $nUnreg
 * @return array
 * @deprecated since 4.05
 */
function plausiRechnungsadresse($cPost_arr, $nUnreg = 0)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return $nUnreg ? checkKundenFormular(0) : checkKundenFormular(0, 1);
}

/**
 * @param string $name
 * @param mixed $obj
 * @deprecated since 4.06
 */
function setzeInSession($name, $obj)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    //an die Session anhängen
    unset($_SESSION[$name]);
    $_SESSION[$name] = $obj;
}
