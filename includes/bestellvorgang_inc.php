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
        '?fillOut=' . \Session\Session::getCart()->istBestellungMoeglich(), true, 303);
    exit;
}

/**
 * @param int  $Versandart
 * @param int  $aFormValues
 * @param bool $bMsg
 * @return bool
 */
function pruefeVersandartWahl($Versandart, $aFormValues = 0, $bMsg = true): bool
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
function pruefeUnregistriertBestellen($cPost_arr): int
{
    global $step, $Kunde, $Lieferadresse;
    unset($_SESSION['Lieferadresse'], $_SESSION['Versandart'], $_SESSION['Zahlungsart']);
    $cart = \Session\Session::getCart();
    $cart->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
         ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART);
    $fehlendeAngaben     = checkKundenFormular(0);
    $Kunde               = getKundendaten($cPost_arr, 0);
    $cKundenattribut_arr = getKundenattribute($cPost_arr);
    $kKundengruppe       = \Session\Session::getCustomerGroup()->getID();
    // CheckBox Plausi
    $oCheckBox       = new CheckBox();
    $fehlendeAngaben = array_merge($fehlendeAngaben, $oCheckBox->validateCheckBox(
        CHECKBOX_ORT_REGISTRIERUNG,
        $kKundengruppe,
        $cPost_arr,
        true
    ));

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
    $nReturnValue = angabenKorrekt($fehlendeAngaben);

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
        executeHook(HOOK_BESTELLVORGANG_INC_UNREGISTRIERTBESTELLEN);

        return 1;
    }
    //keep shipping address on error
    if (isset($cPost_arr['register']['shipping_address'])) {
        $_SESSION['Bestellung']                 = $_SESSION['Bestellung'] ?? new stdClass();
        $_SESSION['Bestellung']->kLieferadresse = isset($cPost_arr['kLieferadresse'])
            ? (int)$cPost_arr['kLieferadresse']
            : -1;
        $Lieferadresse                          = getLieferdaten($cPost_arr['register']['shipping_address']);
        $_SESSION['Lieferadresse']              = $Lieferadresse;
    }

    setzeFehlendeAngaben($fehlendeAngaben);
    Shop::Smarty()->assign('cKundenattribut_arr', $cKundenattribut_arr)
        ->assign('cPost_var', StringHandler::filterXSS($cPost_arr));

    return 0;
}

/**
 * @param array $cPost_arr
 * @param array|null $fehlendeAngaben
 */
function pruefeLieferdaten($cPost_arr, &$fehlendeAngaben = null): void
{
    global $Lieferadresse;
    unset($_SESSION['Lieferadresse']);
    if (!isset($_SESSION['Bestellung'])) {
        $_SESSION['Bestellung'] = new stdClass();
    }
    $_SESSION['Bestellung']->kLieferadresse = isset($cPost_arr['kLieferadresse'])
        ? (int)$cPost_arr['kLieferadresse']
        : -1;
    \Session\Session::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS);
    unset($_SESSION['Versandart']);
    //neue lieferadresse
    if (!isset($cPost_arr['kLieferadresse']) || (int)$cPost_arr['kLieferadresse'] === -1) {
        $fehlendeAngaben           = \array_merge($fehlendeAngaben, checkLieferFormular($cPost_arr));
        $Lieferadresse             = getLieferdaten($cPost_arr);
        $nReturnValue              = angabenKorrekt($fehlendeAngaben);
        $_SESSION['Lieferadresse'] = $Lieferadresse;
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
            executeHook(HOOK_BESTELLVORGANG_PAGE_STEPLIEFERADRESSE_NEUELIEFERADRESSE);
            pruefeVersandkostenfreiKuponVorgemerkt();
        }
    } elseif ((int)$cPost_arr['kLieferadresse'] > 0) {
        //vorhandene lieferadresse
        $LA = Shop::Container()->getDB()->query(
            'SELECT kLieferadresse
                FROM tlieferadresse
                WHERE kKunde = ' . \Session\Session::getCustomer()->getID() . '
                    AND kLieferadresse = ' . (int)$cPost_arr['kLieferadresse'],
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
    if (isset($_SESSION['Lieferadresse'], $_SESSION['Versandart'])
        && $_SESSION['Lieferadresse']
        && $_SESSION['Versandart']
    ) {
        $delVersand = stripos($_SESSION['Versandart']->cLaender, $_SESSION['Lieferadresse']->cLand) === false;
        //ist die plz im zuschlagsbereich?
        $plz_x = Shop::Container()->getDB()->executeQueryPrepared(
            'SELECT kVersandzuschlagPlz
                FROM tversandzuschlagplz, tversandzuschlag
                WHERE tversandzuschlag.kVersandart = :id
                    AND tversandzuschlag.kVersandzuschlag = tversandzuschlagplz.kVersandzuschlag
                    AND ((tversandzuschlagplz.cPLZAb <= :plz
                    AND tversandzuschlagplz.cPLZBis >= :plz)
                    OR tversandzuschlagplz.cPLZ = :plz)',
            ['plz' => $_SESSION['Lieferadresse']->cPLZ, 'id' => (int)$_SESSION['Versandart']->kVersandart],
            \DB\ReturnType::SINGLE_OBJECT
        );
        if (!empty($plz_x->kVersandzuschlagPlz)) {
            $delVersand = true;
        }
        if ($delVersand) {
            \Session\Session::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
                   ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG)
                   ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR)
                   ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
                   ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
                   ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR);
            unset($_SESSION['Versandart'], $_SESSION['Zahlungsart']);
        } else {
            \Session\Session::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG);
        }
    }
    plausiGuthaben($cPost_arr);
}

/**
 * @param array $cPost_arr
 */
function plausiGuthaben($cPost_arr): void
{
    if ((isset($_SESSION['Bestellung']->GuthabenNutzen) && (int)$_SESSION['Bestellung']->GuthabenNutzen === 1)
        || (isset($cPost_arr['guthabenVerrechnen']) && (int)$cPost_arr['guthabenVerrechnen'] === 1)
    ) {
        $_SESSION['Bestellung']->GuthabenNutzen   = 1;
        $_SESSION['Bestellung']->fGuthabenGenutzt = min(
            $_SESSION['Kunde']->fGuthaben,
            \Session\Session::getCart()->gibGesamtsummeWaren(true, false)
        );
        executeHook(HOOK_BESTELLVORGANG_PAGE_STEPBESTAETIGUNG_GUTHABENVERRECHNEN);
    }
}

/**
 *
 */
function pruefeVersandkostenStep(): void
{
    global $step;
    if (isset($_SESSION['Kunde'], $_SESSION['Lieferadresse'])) {
        $cart = \Session\Session::getCart();
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
function pruefeZahlungStep(): void
{
    global $step;
    if (isset($_SESSION['Kunde'], $_SESSION['Lieferadresse'], $_SESSION['Versandart'])) {
        $step = 'Zahlung';
    }
}

/**
 *
 */
function pruefeBestaetigungStep(): void
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
function pruefeRechnungsadresseStep($cGet_arr): void
{
    global $step, $Kunde;
    //sondersteps Rechnungsadresse ändern
    if (!empty(\Session\Session::getCustomer()->cOrt)
        && isset($cGet_arr['editRechnungsadresse'])
        && (int)$cGet_arr['editRechnungsadresse'] === 1
    ) {
        Kupon::resetNewCustomerCoupon();
        $Kunde = \Session\Session::getCustomer();
        $step  = 'edit_customer_address';
    }

    if (!empty(\Session\Session::getCustomer()->cOrt)
        && count(VersandartHelper::getPossibleShippingCountries(
            \Session\Session::getCustomerGroup()->getID(),
            false,
            false,
            [\Session\Session::getCustomer()->cLand]
        )) === 0
    ) {
        Shop::Smarty()->assign('forceDeliveryAddress', 1);

        if (!isset($_SESSION['Lieferadresse'])
            || count(VersandartHelper::getPossibleShippingCountries(
                \Session\Session::getCustomerGroup()->getID(),
                false,
                false,
                [$_SESSION['Lieferadresse']->cLand]
            )) === 0
        ) {
            $Kunde = \Session\Session::getCustomer();
            $step  = 'edit_customer_address';
        }
    }

    if (isset($_SESSION['checkout.register']) && (int)$_SESSION['checkout.register'] === 1) {
        if (isset($_SESSION['checkout.fehlendeAngaben'])) {
            setzeFehlendeAngaben($_SESSION['checkout.fehlendeAngaben']);
            unset($_SESSION['checkout.fehlendeAngaben']);
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
    if (pruefeFehlendeAngaben()) {
        $step = isset($_SESSION['Kunde']) ? 'edit_customer_address' : 'accountwahl';
    }
}

/**
 * @param array $cGet_arr
 */
function pruefeLieferadresseStep($cGet_arr): void
{
    global $step, $Lieferadresse;
    //sondersteps Lieferadresse ändern
    if (!empty($_SESSION['Lieferadresse'])) {
        $Lieferadresse = $_SESSION['Lieferadresse'];
        if (isset($cGet_arr['editLieferadresse']) && (int)$cGet_arr['editLieferadresse'] === 1) {
            Kupon::resetNewCustomerCoupon();
            unset($_SESSION['Zahlungsart'], $_SESSION['TrustedShops'], $_SESSION['Versandart']);
            $step = 'Lieferadresse';
        }
    }
    if (pruefeFehlendeAngaben('shippingAddress')) {
        $step = isset($_SESSION['Kunde']) ? 'Lieferadresse' : 'accountwahl';
    }
}

/**
 * Prüft ob im WK ein Versandfrei Kupon eingegeben wurde und falls ja,
 * wird dieser nach Eingabe der Lieferadresse gesetzt (falls Kriterien erfüllt)
 *
 * @return array
 */
function pruefeVersandkostenfreiKuponVorgemerkt(): array
{
    if ((isset($_SESSION['Kupon']) && $_SESSION['Kupon']->cKuponTyp === 'versandkupon')
        || (isset($_SESSION['oVersandfreiKupon']) && $_SESSION['oVersandfreiKupon']->cKuponTyp === 'versandkupon')
    ) {
        \Session\Session::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_KUPON);
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
function pruefeVersandartStep($cGet_arr): void
{
    global $step;
    //sondersteps Versandart ändern
    if (isset($cGet_arr['editVersandart'], $_SESSION['Versandart']) && (int)$cGet_arr['editVersandart'] === 1) {
        Kupon::resetNewCustomerCoupon();
        \Session\Session::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_VERPACKUNG)
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
function pruefeZahlungsartStep($cGet_arr): void
{
    global $step, $hinweis;
    //sondersteps Zahlungsart ändern
    if (isset($_SESSION['Zahlungsart'], $cGet_arr['editZahlungsart']) && (int)$cGet_arr['editZahlungsart'] === 1) {
        Kupon::resetNewCustomerCoupon();
        \Session\Session::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
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
 * @return int|null
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
            $kSteuerklasse = \Session\Session::getCart()->gibVersandkostenSteuerklasse($cLandISO);
            $fPreis        = \Session\Session::getCustomerGroup()->isMerchant()
                ? $fNetto
                : ($fNetto * ((100 + (float)$_SESSION['Steuersatz'][$kSteuerklasse]) / 100));
            $cName['ger']  = Shop::Lang()->get('trustedshopsName');
            $cName['eng']  = Shop::Lang()->get('trustedshopsName');
            \Session\Session::getCart()->erstelleSpezialPos(
                $cName,
                1,
                $fPreis,
                $kSteuerklasse,
                C_WARENKORBPOS_TYP_TRUSTEDSHOPS,
                true,
                !\Session\Session::getCustomerGroup()->isMerchant()
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
function pruefeGuthabenNutzen(): void
{
    if (isset($_SESSION['Bestellung']->GuthabenNutzen) && $_SESSION['Bestellung']->GuthabenNutzen) {
        $_SESSION['Bestellung']->fGuthabenGenutzt   = min(
            $_SESSION['Kunde']->fGuthaben,
            \Session\Session::getCart()->gibGesamtsummeWaren(true, false)
        );
        $_SESSION['Bestellung']->GutscheinLocalized = Preise::getLocalizedPriceString(
            $_SESSION['Bestellung']->fGuthabenGenutzt
        );
    }

    executeHook(HOOK_BESTELLVORGANG_PAGE_STEPBESTAETIGUNG_GUTHABEN_PLAUSI);
}

/**
 * @param string $context
 * @return bool
 */
function pruefeFehlendeAngaben($context = null): bool
{
    $fehlendeAngaben = Shop::Smarty()->getTemplateVars('fehlendeAngaben');
    if (!$context) {
        return !empty($fehlendeAngaben);
    }

    return (isset($fehlendeAngaben[$context])
        && is_array($fehlendeAngaben[$context])
        && count($fehlendeAngaben[$context]));
}

/**
 *
 */
function gibStepAccountwahl(): void
{
    global $hinweis;
    // Einstellung global_kundenkonto_aktiv ist auf 'A'
    // und Kunde wurde nach der Registrierung zurück zur Accountwahl geleitet
    if (isset($_REQUEST['reg']) && (int)$_REQUEST['reg'] === 1) {
        $hinweis = Shop::Lang()->get('accountCreated') . '<br />' . Shop::Lang()->get('loginNotActivated');
    }
    Shop::Smarty()->assign('untertitel', lang_warenkorb_bestellungEnthaeltXArtikel(\Session\Session::getCart()));

    executeHook(HOOK_BESTELLVORGANG_PAGE_STEPACCOUNTWAHL);
}

/**
 *
 */
function gibStepUnregistriertBestellen(): void
{
    global $Kunde;
    $herkunfte = Shop::Container()->getDB()->query(
        'SELECT *
            FROM tkundenherkunft
            ORDER BY nSort',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );

    Shop::Smarty()->assign('untertitel', Shop::Lang()->get('fillUnregForm', 'checkout'))
        ->assign('herkunfte', $herkunfte)
        ->assign('Kunde', $Kunde ?? null)
        ->assign('laender', VersandartHelper::getPossibleShippingCountries(\Session\Session::getCustomerGroup()->getID(), false, true))
        ->assign('LieferLaender', VersandartHelper::getPossibleShippingCountries(\Session\Session::getCustomerGroup()->getID()))
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
            \Session\Session::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_KUPON);
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

    $kKundengruppe = \Session\Session::getCustomerGroup()->getID();

    if (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) {
        $Lieferadressen        = [];
        $oLieferadresseTMP_arr = Shop::Container()->getDB()->query(
            'SELECT DISTINCT(kLieferadresse)
                FROM tlieferadresse
                WHERE kKunde = ' . \Session\Session::getCustomer()->getID(),
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
    Shop::Smarty()->assign('laender', VersandartHelper::getPossibleShippingCountries($kKundengruppe, false, true))
        ->assign('LieferLaender', VersandartHelper::getPossibleShippingCountries($kKundengruppe))
        ->assign('Kunde', $_SESSION['Kunde'] ?? null)
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
    $cart          = \Session\Session::getCart();
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
        $kKundengruppe = \Session\Session::getCustomerGroup()->getID();
    }
    $oVersandart_arr = VersandartHelper::getPossibleShippingMethods(
        $lieferland,
        $plz,
        VersandartHelper::getShippingClasses(\Session\Session::getCart()),
        $kKundengruppe
    );
    $packagings = VersandartHelper::getPossiblePackagings(\Session\Session::getCustomerGroup()->getID());

    if (!empty($packagings) && $cart->posTypEnthalten(C_WARENKORBPOS_TYP_VERPACKUNG)) {
        foreach ($cart->PositionenArr as $oPos) {
            if ($oPos->nPosTyp === C_WARENKORBPOS_TYP_VERPACKUNG) {
                foreach ($packagings as $oPack) {
                    if ($oPack->cName === $oPos->cName[$oPack->cISOSprache]) {
                        $oPack->bWarenkorbAktiv = true;
                    }
                }
            }
        }
    }

    if (is_array($oVersandart_arr) && count($oVersandart_arr) > 0) {
        $aktiveVersandart = gibAktiveVersandart($oVersandart_arr);
        $oZahlungsart_arr = gibZahlungsarten($aktiveVersandart, \Session\Session::getCustomerGroup()->getID());
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
                $_SESSION['Versandart']->kVersandart . ', Kundengruppe: ' . \Session\Session::getCustomerGroup()->getID()
            );
        }

        $aktiveVerpackung  = gibAktiveVerpackung($packagings);
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
            ->assign('Verpackungsarten', $packagings)
            ->assign('AktiveVersandart', $aktiveVersandart)
            ->assign('AktiveZahlungsart', $aktiveZahlungsart)
            ->assign('AktiveVerpackung', $aktiveVerpackung)
            ->assign('Kunde', $_SESSION['Kunde'])
            ->assign('Lieferadresse', $_SESSION['Lieferadresse'])
            ->assign('OrderAmount', \Session\Session::getCart()->gibGesamtsummeWaren(true))
            ->assign('ShopCreditAmount', $_SESSION['Kunde']->fGuthaben);

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
function gibStepZahlungZusatzschritt($cPost_arr): void
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
        unset($_SESSION['checkout.fehlendeAngaben']);
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
        Shop::Smarty()->assign('GuthabenLocalized', \Session\Session::getCustomer()->gibGuthabenLocalized());
    }
    $cart = \Session\Session::getCart();
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
function gibStepVersand(): void
{
    global $step;
    unset($_SESSION['TrustedShopsZahlung']);
    pruefeVersandkostenfreiKuponVorgemerkt();
    $cart       = \Session\Session::getCart();
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
        $kKundengruppe = \Session\Session::getCustomerGroup()->getID();
    }
    $oVersandart_arr  = VersandartHelper::getPossibleShippingMethods(
        $lieferland,
        $plz,
        VersandartHelper::getShippingClasses($cart),
        $kKundengruppe
    );
    $oZahlungsart_arr = [];
    foreach ($oVersandart_arr as $oVersandart) {
        $oTmp_arr = gibZahlungsarten($oVersandart->kVersandart, \Session\Session::getCustomerGroup()->getID());
        foreach ($oTmp_arr as $oTmp) {
            $oZahlungsart_arr[$oTmp->kZahlungsart] = $oTmp;
        }
    }
    $packagings = VersandartHelper::getPossiblePackagings(\Session\Session::getCustomerGroup()->getID());
    if ($cart->posTypEnthalten(C_WARENKORBPOS_TYP_VERPACKUNG) && !empty($packagings)) {
        foreach ($cart->PositionenArr as $oPos) {
            if ($oPos->nPosTyp === C_WARENKORBPOS_TYP_VERPACKUNG) {
                foreach ($packagings as $oPack) {
                    if ($oPack->cName === $oPos->cName[$oPack->cISOSprache]) {
                        $oPack->bWarenkorbAktiv = true;
                    }
                }
            }
        }
    }
    if ((is_array($oVersandart_arr) && count($oVersandart_arr) > 0)
        || (is_array($oVersandart_arr) && count($oVersandart_arr) === 1
            && is_array($packagings) && count($packagings) > 0)
    ) {
        Shop::Smarty()->assign('Versandarten', $oVersandart_arr)
            ->assign('Verpackungsarten', $packagings);
    } elseif (is_array($oVersandart_arr) && count($oVersandart_arr) === 1 &&
        (is_array($packagings) && count($packagings) === 0)
    ) {
        pruefeVersandartWahl($oVersandart_arr[0]->kVersandart);
    } elseif (!is_array($oVersandart_arr) || count($oVersandart_arr) === 0) {
        Shop::Container()->getLogService()->error(
            'Es konnte keine Versandart für folgende Daten gefunden werden: Lieferland: ' . $lieferland .
            ', PLZ: ' . $plz . ', Versandklasse: ' . VersandartHelper::getShippingClasses(\Session\Session::getCart()) .
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
        $conf   = Shop::getSettings([CONF_KAUFABWICKLUNG]);
        if (empty($_SESSION['Kunde']->kKunde)
            && $conf['kaufabwicklung']['bestellvorgang_unregneukundenkupon_zulassen'] === 'N'
        ) {
            //unregistrierte Neukunden, keine Kupons für Gastbestellungen zugelassen
            return;
        }
        //not for already registered customers with order(s)
        if (!empty($_SESSION['Kunde']->kKunde)) {
            $oBestellung  = Shop::Container()->getDB()->executeQueryPrepared('
              SELECT tbestellung.kBestellung
                FROM tkunde
                JOIN tbestellung
                    ON tbestellung.kKunde = tkunde.kKunde
                WHERE tkunde.kKunde = :customerID
                LIMIT 1',
                ['customerID' => $_SESSION['Kunde']->kKunde],
                \DB\ReturnType::SINGLE_OBJECT
            );
            if (!empty($oBestellung)) {
                return;
            }
        }

        $NeukundenKupons = (new Kupon())->getNewCustomerCoupon();
        if (!empty($NeukundenKupons) && !Kupon::newCustomerCouponUsed($_SESSION['Kunde']->cMail)) {
            foreach ($NeukundenKupons as $NeukundenKupon) {
                if (angabenKorrekt(Kupon::checkCoupon($NeukundenKupon))) {
                    Kupon::acceptCoupon($NeukundenKupon);
                    break;
                }
            }
        }
    }
}

/**
 * @param Zahlungsart|object $paymentMethod
 * @return array
 */
function checkAdditionalPayment($paymentMethod): array
{
    foreach (['iban', 'bic'] as $dataKey) {
        if (!empty($_POST[$dataKey])) {
            $_POST[$dataKey] = strtoupper($_POST[$dataKey]);
        }
    }

    $conf   = Shop::getSettings([CONF_ZAHLUNGSARTEN]);
    $post   = StringHandler::filterXSS($_POST);
    $errors = [];
    switch ($paymentMethod->cModulId) {
        case 'za_kreditkarte_jtl':
            if (empty($post['kreditkartennr'])) {
                $errors['kreditkartennr'] = 1;
            }
            if (empty($post['gueltigkeit'])) {
                $errors['gueltigkeit'] = 1;
            }
            if (empty($post['cvv'])) {
                $errors['cvv'] = 1;
            }
            if (empty($post['kartentyp'])) {
                $errors['kartentyp'] = 1;
            }
            if (empty($post['inhaber'])) {
                $errors['inhaber'] = 1;
            }
            break;

        case 'za_lastschrift_jtl':
            if (empty($post['bankname'])
                && $conf['zahlungsarten']['zahlungsart_lastschrift_kreditinstitut_abfrage'] === 'Y'
            ) {
                $errors['bankname'] = 1;
            }
            if (empty($post['inhaber'])
                && $conf['zahlungsarten']['zahlungsart_lastschrift_kontoinhaber_abfrage'] === 'Y'
            ) {
                $errors['inhaber'] = 1;
            }
            if (empty($post['bic'])) {
                if ($conf['zahlungsarten']['zahlungsart_lastschrift_bic_abfrage'] === 'Y') {
                    $errors['bic'] = 1;
                }
            } elseif (!checkBIC($post['bic'])) {
                $errors['bic'] = 2;
            }
            if (empty($post['iban'])) {
                $errors['iban'] = 1;
            } elseif (!plausiIban($post['iban'])) {
                $errors['iban'] = 2;
            }
            break;
    }

    return $errors;
}

/**
 * @param string $bic
 * @return bool
 */
function checkBIC($bic): bool
{
    return preg_match('/^[A-Z]{6}[A-Z\d]{2}([A-Z\d]{3})?$/i', $bic) === 1;
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
function gibPostZahlungsInfo(): stdClass
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
function zahlungsartKorrekt(int $kZahlungsart): int
{
    $cart = \Session\Session::getCart();
    unset($_SESSION['Zahlungsart']);
    $cart->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
         ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
         ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR)
         ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR);
    if ($kZahlungsart > 0
        && isset($_SESSION['Versandart']->kVersandart)
        && (int)$_SESSION['Versandart']->kVersandart > 0
    ) {
        $Zahlungsart = Shop::Container()->getDB()->executeQueryPrepared(
            'SELECT tversandartzahlungsart.*, tzahlungsart.*
                FROM tversandartzahlungsart, tzahlungsart
                WHERE tversandartzahlungsart.kVersandart = :session_kversandart
                    AND tversandartzahlungsart.kZahlungsart = tzahlungsart.kZahlungsart
                    AND tversandartzahlungsart.kZahlungsart = :kzahlungsart',
            [
                'session_kversandart' => (int)$_SESSION['Versandart']->kVersandart,
                'kzahlungsart'        => $kZahlungsart
            ],
            \DB\ReturnType::SINGLE_OBJECT
        );
        if (!$Zahlungsart) {
			$Zahlungsart = Shop::Container()->getDB()->select('tzahlungsart', 'kZahlungsart', $kZahlungsart);
			// only the null-payment-method is allowed to go ahead in this case
			if ('za_null_jtl' !== $Zahlungsart->cModulId) {

				return 0;
			}
		}
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
                case 'za_null_jtl' :
                    // the null-paymentMethod did not has any additional-steps
                    break;
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
                        $ZahlungsInfo->cBankName = StringHandler::htmlentities(stripslashes($_POST['bankname'] ?? ''), ENT_QUOTES);
                        $ZahlungsInfo->cKontoNr  = StringHandler::htmlentities(stripslashes($_POST['kontonr'] ?? ''), ENT_QUOTES);
                        $ZahlungsInfo->cBLZ      = StringHandler::htmlentities(stripslashes($_POST['blz'] ?? ''), ENT_QUOTES);
                        $ZahlungsInfo->cIBAN     = StringHandler::htmlentities(stripslashes($_POST['iban']), ENT_QUOTES);
                        $ZahlungsInfo->cBIC      = StringHandler::htmlentities(stripslashes($_POST['bic'] ?? ''), ENT_QUOTES);
                        $ZahlungsInfo->cInhaber  = StringHandler::htmlentities(stripslashes($_POST['inhaber'] ?? ''), ENT_QUOTES);
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
    if ($Zahlungsart->fAufpreis == 0) {
        return;
    }
    $cart = \Session\Session::getCart();
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
                'cISOSprache',
                $Sprache->cISO,
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

/**
 * @param string $cModulId
 * @return bool|\Plugin\Plugin
 */
function gibPluginZahlungsart($cModulId)
{
    $kPlugin = \Plugin\Plugin::getIDByModuleID($cModulId);
    if ($kPlugin > 0) {
        $oPlugin = new \Plugin\Plugin($kPlugin);
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
function gibZahlungsart(int $kZahlungsart)
{
    $method = Shop::Container()->getDB()->select('tzahlungsart', 'kZahlungsart', $kZahlungsart);
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
        $method->angezeigterName[$Sprache->cISO] = $name_spr->cName ?? null;
    }
    $confData = Shop::Container()->getDB()->queryPrepared(
        'SELECT *
            FROM teinstellungen
            WHERE kEinstellungenSektion = :sec
                AND cModulId = :mod',
        ['mod' => $method->cModulId, 'sec' => CONF_ZAHLUNGSARTEN],
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($confData as $conf) {
        $method->einstellungen[$conf->cName] = $conf->cWert;
    }
    $plugin = gibPluginZahlungsart($method->cModulId);
    if ($plugin) {
        $method->cZusatzschrittTemplate =
            $plugin->oPluginZahlungsmethodeAssoc_arr[$method->cModulId]->cZusatzschrittTemplate;
    }

    return $method;
}

/**
 * @param null|int $kKunde
 * @return object|bool
 */
function gibKundenKontodaten(?int $kKunde)
{
    if (empty($kKunde)) {
        return false;
    }
    $accountData = Shop::Container()->getDB()->select('tkundenkontodaten', 'kKunde', $kKunde);

    if (isset($accountData->kKunde) && $accountData->kKunde > 0) {
        $cryptoService = Shop::Container()->getCryptoService();
        if (strlen($accountData->cBLZ) > 0) {
            $accountData->cBLZ = (int)$cryptoService->decryptXTEA($accountData->cBLZ);
        }
        if (strlen($accountData->cInhaber) > 0) {
            $accountData->cInhaber = trim($cryptoService->decryptXTEA($accountData->cInhaber));
        }
        if (strlen($accountData->cBankName) > 0) {
            $accountData->cBankName = trim($cryptoService->decryptXTEA($accountData->cBankName));
        }
        if (strlen($accountData->nKonto) > 0) {
            $accountData->nKonto = trim($cryptoService->decryptXTEA($accountData->nKonto));
        }
        if (strlen($accountData->cIBAN) > 0) {
            $accountData->cIBAN = trim($cryptoService->decryptXTEA($accountData->cIBAN));
        }
        if (strlen($accountData->cBIC) > 0) {
            $accountData->cBIC = trim($cryptoService->decryptXTEA($accountData->cBIC));
        }

        return $accountData;
    }

    return false;
}

/**
 * @param int $kVersandart
 * @param int $kKundengruppe
 * @return array
 */
function gibZahlungsarten(int $kVersandart, int $kKundengruppe)
{
    $taxRate = 0.0;
    $methods = [];
    if ($kVersandart > 0) {
        $methods = Shop::Container()->getDB()->query(
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
    $valid = [];
    foreach ($methods as $method) {
        if (!$method->kZahlungsart) {
            continue;
        }
        $method->kVersandartZahlungsart = (int)$method->kVersandartZahlungsart;
        $method->kVersandart            = (int)$method->kVersandart;
        $method->kZahlungsart           = (int)$method->kZahlungsart;
        $method->nSort                  = (int)$method->nSort;
        //posname lokalisiert ablegen
        $method->angezeigterName = [];
        $method->cGebuehrname    = [];
        foreach ($_SESSION['Sprachen'] as $Sprache) {
            $name_spr = Shop::Container()->getDB()->select(
                'tzahlungsartsprache',
                'kZahlungsart',
                (int)$method->kZahlungsart,
                'cISOSprache',
                $Sprache->cISO,
                null,
                null,
                false,
                'cName, cGebuehrname, cHinweisTextShop'
            );
            if (isset($name_spr->cName)) {
                $method->angezeigterName[$Sprache->cISO] = $name_spr->cName;
                $method->cGebuehrname[$Sprache->cISO]    = $name_spr->cGebuehrname;
                $method->cHinweisText[$Sprache->cISO]    = $name_spr->cHinweisTextShop;
            }
        }
        $confData = Shop::Container()->getDB()->selectAll(
            'teinstellungen',
            ['kEinstellungenSektion', 'cModulId'],
            [CONF_ZAHLUNGSARTEN, $method->cModulId]
        );
        foreach ($confData as $config) {
            $method->einstellungen[$config->cName] = $config->cWert;
        }
        if (!zahlungsartGueltig($method)) {
            continue;
        }
        $method->Specials = null;
        //evtl. Versandkupon anwenden / Nur Nachname fällt weg
        if (isset($_SESSION['VersandKupon']->cZusatzgebuehren)
            && $_SESSION['VersandKupon']->cZusatzgebuehren === 'Y'
            && $method->fAufpreis > 0
            && $method->cName === 'Nachnahme'
        ) {
            $method->fAufpreis = 0;
        }
        //lokalisieren
        if ($method->cAufpreisTyp === 'festpreis') {
            $method->fAufpreis *= ((100 + $taxRate) / 100);
        }
        $method->cPreisLocalized = Preise::getLocalizedPriceString($method->fAufpreis);
        if ($method->cAufpreisTyp === 'prozent') {
            $method->cPreisLocalized = ($method->fAufpreis < 0) ? ' ' : '+ ';
            $method->cPreisLocalized .= $method->fAufpreis . '%';
        }
        if ($method->fAufpreis == 0) {
            $method->cPreisLocalized = '';
        }
        if (!empty($method->angezeigterName)) {
            $valid[] = $method;
        }
    }

    return $valid;
}

/**
 * @param object[] $shippingMethods
 * @return int
 */
function gibAktiveVersandart($shippingMethods)
{
    if (isset($_SESSION['Versandart'])) {
        $_SESSION['AktiveVersandart'] = $_SESSION['Versandart']->kVersandart;
    } elseif (!empty($_SESSION['AktiveVersandart']) && is_array($shippingMethods) && count($shippingMethods) > 0) {
        $active = (int)$_SESSION['AktiveVersandart'];
        if (array_reduce($shippingMethods, function ($carry, $item) use ($active) {
            return (int)$item->kVersandart === $active ? (int)$item->kVersandart : $carry;
        }, 0) !== (int)$_SESSION['AktiveVersandart']) {
            $_SESSION['AktiveVersandart'] = $shippingMethods[0]->kVersandart;
        }
    } else {
        $_SESSION['AktiveVersandart'] = $shippingMethods[0]->kVersandart ?? 0;
    }

    return $_SESSION['AktiveVersandart'];
}

/**
 * @param object[] $shippingMethods
 * @return int
 */
function gibAktiveZahlungsart($shippingMethods)
{
    if (isset($_SESSION['Zahlungsart'])) {
        $_SESSION['AktiveZahlungsart'] = $_SESSION['Zahlungsart']->kZahlungsart;
    } elseif (!empty($_SESSION['AktiveZahlungsart']) && is_array($shippingMethods) && count($shippingMethods) > 0) {
        $active = (int)$_SESSION['AktiveZahlungsart'];
        if (array_reduce($shippingMethods, function ($carry, $item) use ($active) {
            return (int)$item->kZahlungsart === $active ? (int)$item->kZahlungsart : $carry;
        }, 0) !== (int)$_SESSION['AktiveZahlungsart']) {
            $_SESSION['AktiveZahlungsart'] = $shippingMethods[0]->kZahlungsart;
        }
    } else {
        $_SESSION['AktiveZahlungsart'] = $shippingMethods[0]->kZahlungsart;
    }

    return $_SESSION['AktiveZahlungsart'];
}

/**
 * @param object[] $packagings
 * @return array
 */
function gibAktiveVerpackung($packagings): array
{
    if (isset($_SESSION['Verpackung']) && count($_SESSION['Verpackung']) > 0) {
        $_SESSION['AktiveVerpackung'] = [];
        foreach ($_SESSION['Verpackung'] as $verpackung) {
            $_SESSION['AktiveVerpackung'][$verpackung->kVerpackung] = 1;
        }
    } elseif (!empty($_SESSION['AktiveVerpackung']) && is_array($packagings) && count($packagings) > 0) {
        foreach (array_keys($_SESSION['AktiveVerpackung']) as $active) {
            if (array_reduce($packagings, function ($carry, $item) use ($active) {
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
 * @param Zahlungsart|stdClass $paymentMethod
 * @return bool
 */
function zahlungsartGueltig($paymentMethod): bool
{
    if (!isset($paymentMethod->cModulId)) {
        return false;
    }
    $kPlugin = \Plugin\Plugin::getIDByModuleID($paymentMethod->cModulId);
    if ($kPlugin > 0) {
        $oPlugin = new \Plugin\Plugin($kPlugin);
        if ($oPlugin->kPlugin > 0) {
            // Plugin muss aktiv sein
            if ($oPlugin->nStatus !== \Plugin\Plugin::PLUGIN_ACTIVATED) {
                return false;
            }
            require_once PFAD_ROOT . PFAD_PLUGIN . $oPlugin->cVerzeichnis . '/' .
                PFAD_PLUGIN_VERSION . $oPlugin->nVersion . '/' .
                PFAD_PLUGIN_PAYMENTMETHOD .
                $oPlugin->oPluginZahlungsKlasseAssoc_arr[$paymentMethod->cModulId]->cClassPfad;
            $className              = $oPlugin->oPluginZahlungsKlasseAssoc_arr[$paymentMethod->cModulId]->cClassName;
            $oZahlungsart           = new $className($paymentMethod->cModulId);
            $oZahlungsart->cModulId = $paymentMethod->cModulId;
            /** @var PaymentMethod $oZahlungsart */
            if ($oZahlungsart && $oZahlungsart->isSelectable() === false) {
                return false;
            }
            if ($oZahlungsart && !$oZahlungsart->isValidIntern()) {
                Shop::Container()->getLogService()->withName('cModulId')->debug(
                    'Die Zahlungsartprüfung (' . $paymentMethod->cModulId .
                    ') wurde nicht erfolgreich validiert (isValidIntern).',
                    [$paymentMethod->cModulId]
                );

                return false;
            }
            if (!\Plugin\Plugin::licenseCheck($oPlugin, ['cModulId' => $paymentMethod->cModulId])) {
                return false;
            }

            return $oZahlungsart->isValid($_SESSION['Kunde'], \Session\Session::getCart());
        }
    } else {
        $oPaymentMethod = new PaymentMethod($paymentMethod->cModulId);
        $oZahlungsart   = $oPaymentMethod::create($paymentMethod->cModulId);

        if ($oZahlungsart && $oZahlungsart->isSelectable() === false) {
            return false;
        }
        if ($oZahlungsart && !$oZahlungsart->isValidIntern()) {
            Shop::Container()->getLogService()->withName('cModulId')->debug(
                'Die Zahlungsartprüfung (' .
                    $paymentMethod->cModulId . ') wurde nicht erfolgreich validiert (isValidIntern).',
                [$paymentMethod->cModulId]
            );

            return false;
        }

        return ZahlungsartHelper::shippingMethodWithValidPaymentMethod($paymentMethod);
    }

    return false;
}

/**
 * @param int $nMinBestellungen
 * @return bool
 */
function pruefeZahlungsartMinBestellungen($nMinBestellungen): bool
{
    if ($nMinBestellungen <= 0) {
        return true;
    }
    if ($_SESSION['Kunde']->kKunde > 0) {
        $count = Shop::Container()->getDB()->query(
            'SELECT COUNT(*) AS anz
                FROM tbestellung
                WHERE kKunde = ' . (int)$_SESSION['Kunde']->kKunde . '
                    AND (cStatus = ' . BESTELLUNG_STATUS_BEZAHLT . '
                    OR cStatus = ' . BESTELLUNG_STATUS_VERSANDT . ')',
            \DB\ReturnType::SINGLE_OBJECT
        );
        if ($count->anz < $nMinBestellungen) {
            Shop::Container()->getLogService()->debug(
                'pruefeZahlungsartMinBestellungen Bestellanzahl zu niedrig: Anzahl ' .
                $count->anz . ' < ' . $nMinBestellungen
            );

            return false;
        }
    } else {
        Shop::Container()->getLogService()->debug('pruefeZahlungsartMinBestellungen erhielt keinen kKunden');

        return false;
    }

    return true;
}

/**
 * @param float $fMinBestellwert
 * @return bool
 */
function pruefeZahlungsartMinBestellwert($fMinBestellwert): bool
{
    if ($fMinBestellwert > 0
        && \Session\Session::getCart()->gibGesamtsummeWarenOhne([C_WARENKORBPOS_TYP_VERSANDPOS], true) <
        $fMinBestellwert
    ) {
        Shop::Container()->getLogService()->debug(
            'pruefeZahlungsartMinBestellwert Bestellwert zu niedrig: Wert ' .
            \Session\Session::getCart()->gibGesamtsummeWaren(true) . ' < ' . $fMinBestellwert
        );

        return false;
    }

    return true;
}

/**
 * @param float $fMaxBestellwert
 * @return bool
 */
function pruefeZahlungsartMaxBestellwert($fMaxBestellwert): bool
{
    if ($fMaxBestellwert > 0
        && \Session\Session::getCart()->gibGesamtsummeWarenOhne([C_WARENKORBPOS_TYP_VERSANDPOS], true)
        >= $fMaxBestellwert
    ) {
        Shop::Container()->getLogService()->debug(
            'pruefeZahlungsartMaxBestellwert Bestellwert zu hoch: Wert ' .
            \Session\Session::getCart()->gibGesamtsummeWaren(true) . ' > ' . $fMaxBestellwert
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
function versandartKorrekt(int $kVersandart, $aFormValues = 0)
{
    $cart                   = \Session\Session::getCart();
    $kVerpackung_arr        = (isset($_POST['kVerpackung'])
        && is_array($_POST['kVerpackung'])
        && count($_POST['kVerpackung']) > 0)
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
                            OR FIND_IN_SET('" . \Session\Session::getCustomerGroup()->getID()
                                . "', REPLACE(tverpackung.cKundengruppe, ';', ',')) > 0)
                        AND " . $fSummeWarenkorb . " >= tverpackung.fMindestbestellwert
                        AND nAktiv = 1",
                \DB\ReturnType::SINGLE_OBJECT
            );
            $oVerpackung->kVerpackung = (int)$oVerpackung->kVerpackung;
            if (empty($oVerpackung->kVerpackung)) {
                return false;
            }
            $cName_arr              = [];
            $oVerpackungSprache_arr = Shop::Container()->getDB()->selectAll(
                'tverpackungsprache',
                'kVerpackung',
                (int)$oVerpackung->kVerpackung
            );
            if (count($oVerpackungSprache_arr) > 0) {
                foreach ($oVerpackungSprache_arr as $oVerpackungSprache) {
                    $cName_arr[$oVerpackungSprache->cISOSprache] = $oVerpackungSprache->cName;
                }
            }
            $fBrutto = $oVerpackung->fBrutto;
            if ($fSummeWarenkorb >= $oVerpackung->fKostenfrei
                && $oVerpackung->fBrutto > 0
                && $oVerpackung->fKostenfrei != 0
            ) {
                $fBrutto = 0;
            }
            if ($oVerpackung->kSteuerklasse == -1) {
                $oVerpackung->kSteuerklasse = $cart->gibVersandkostenSteuerklasse($_SESSION['Lieferadresse']->cLand);
            }
            $_SESSION['Verpackung'][] = $oVerpackung;

            $_SESSION['AktiveVerpackung'][$oVerpackung->kVerpackung] = 1;
            $cart->erstelleSpezialPos(
                $cName_arr,
                1,
                $fBrutto,
                $oVerpackung->kSteuerklasse,
                C_WARENKORBPOS_TYP_VERPACKUNG,
                false
            );
            unset($oVerpackung);
        }
    }
    unset($_SESSION['Versandart']);
    if ($kVersandart <= 0) {
        return false;
    }
    $lieferland = $_SESSION['Lieferadresse']->cLand ?? null;
    if (!$lieferland) {
        $lieferland = $_SESSION['Kunde']->cLand;
    }
    $plz = $_SESSION['Lieferadresse']->cPLZ ?? null;
    if (!$plz) {
        $plz = $_SESSION['Kunde']->cPLZ;
    }
    $versandklassen           = VersandartHelper::getShippingClasses(\Session\Session::getCart());
    $cNurAbhaengigeVersandart = 'N';
    if (VersandartHelper::normalerArtikelversand($lieferland) === false) {
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

    if (!isset($versandart->kVersandart) || $versandart->kVersandart <= 0) {
        return false;
    }
    $versandart->Zuschlag  = VersandartHelper::getAdditionalFees($versandart, $cISO, $plz);
    $versandart->fEndpreis = VersandartHelper::calculateShippingFees($versandart, $cISO, null);
    if ($versandart->fEndpreis == -1) {
        return false;
    }
    $Spezialpos = new stdClass();
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
    if ($versandart->fEndpreis == 0
        && isset($versandart->Zuschlag->fZuschlag)
        && $versandart->Zuschlag->fZuschlag > 0
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
                'cISOSprache',
                $Sprache->cISO,
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
            $cart->gibVersandkostenSteuerklasse($cISO),
            C_WARENKORBPOS_TYP_VERSANDZUSCHLAG,
            true,
            $bSteuerPos
        );
    }
    $_SESSION['Versandart']       = $versandart;
    $_SESSION['AktiveVersandart'] = $versandart->kVersandart;

    return true;
}

/**
 * @param array $fehlendeAngaben
 * @return int
 */
function angabenKorrekt(array $fehlendeAngaben): int
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
function checkKundenFormularArray($data, int $kundenaccount, $checkpass = 1)
{
    $ret  = [];
    $conf = Shop::getSettings([CONF_KUNDEN, CONF_KUNDENFELD, CONF_GLOBAL]);

    foreach (['nachname', 'strasse', 'hausnummer', 'plz', 'ort', 'land', 'email'] as $dataKey) {
        $data[$dataKey] = isset($data[$dataKey]) ? trim($data[$dataKey]) : null;

        if (!$data[$dataKey]) {
            $ret[$dataKey] = 1;
        }
    }

    foreach ([
             'kundenregistrierung_abfragen_anrede' => 'anrede',
             'kundenregistrierung_pflicht_vorname' => 'vorname',
             'kundenregistrierung_abfragen_firma' => 'firma',
             'kundenregistrierung_abfragen_firmazusatz' => 'firmazusatz',
             'kundenregistrierung_abfragen_titel' => 'titel',
             'kundenregistrierung_abfragen_adresszusatz' => 'adresszusatz',
             'kundenregistrierung_abfragen_www' => 'www',
             'kundenregistrierung_abfragen_bundesland' => 'bundesland'
             ] as $confKey => $dataKey) {
        if ($conf['kunden'][$confKey] === 'Y') {
            $data[$dataKey] = isset($data[$dataKey]) ? trim($data[$dataKey]) : null;

            if (!$data[$dataKey]) {
                $ret[$dataKey] = 1;
            }
        }
    }

    if (!empty($data['www']) && !StringHandler::filterURL($data['www'])) {
        $ret['www'] = 2;
    }

    if (isset($ret['email']) && $ret['email'] === 1) {
        // email is empty
    } elseif (StringHandler::filterEmailAddress($data['email']) === false) {
        $ret['email'] = 2;
    } elseif (SimpleMail::checkBlacklist($data['email'])) {
        $ret['email'] = 3;
    } elseif (isset($conf['kunden']['kundenregistrierung_pruefen_email'])
        && $conf['kunden']['kundenregistrierung_pruefen_email'] === 'Y'
        && !checkdnsrr(substr($data['email'], strpos($data['email'], '@') + 1))
    ) {
        $ret['email'] = 4;
    }

    if (empty($_SESSION['check_plzort'])
        && empty($_SESSION['check_liefer_plzort'])
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

    foreach ([
             'kundenregistrierung_abfragen_tel' => 'tel',
             'kundenregistrierung_abfragen_mobil' => 'mobil',
             'kundenregistrierung_abfragen_fax' => 'fax'
             ] as $confKey => $dataKey) {
        if (isset($data[$dataKey])
            && ($errCode = StringHandler::checkPhoneNumber($data[$dataKey], $conf['kunden'][$confKey] === 'Y')) > 0
        ) {
            $ret[$dataKey] = $errCode;
        }
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
            if ($conf['kunden']['shop_ustid_bzstpruefung'] === 'Y') {
                // backend-setting: "Einstellungen -> Formulareinstellungen ->"
                $oVies         = new UstIDvies();
                $vViesResult   = $oVies->doCheckID(trim($data['ustid']));
                $bAnalizeCheck = true; // flag to signalize further analization
            }
            if ($bAnalizeCheck === true && $vViesResult['success'] === true) {
                // "all was fine"
                $ret['ustid'] = 0;
            } elseif (isset($vViesResult)) {
                switch ($vViesResult['errortype']) {
                    case 'vies':
                        // vies-error: the ID is invalid according to the VIES-system
                        $ret['ustid'] = $vViesResult['errorcode']; // (old value 5)
                        break;
                    case 'parse':
                        // parse-error: the ID-string is misspelled in any way
                        if ($vViesResult['errorcode'] === 1) {
                            $ret['ustid'] = 1; // parse-error: no id was given
                        } elseif ($vViesResult['errorcode'] > 1) {
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
                                case 130:
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
                    case 'time':
                        // according to the backend-setting:
                        // "Einstellungen -> (Formular)einstellungen -> UstID-Nummer"-check active
                        if ($conf['kunden']['shop_ustid_force_remote_check'] === 'Y') {
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
    if (isset($data['geburtstag'])
        && ($errCode = StringHandler::checkDate(
            $data['geburtstag'],
            $conf['kunden']['kundenregistrierung_abfragen_geburtstag'] === 'Y'
        )) > 0
    ) {
        $ret['geburtstag'] = $errCode;
    }
    if ($kundenaccount === 1) {
        if ($checkpass) {
            if ($data['pass'] !== $data['pass2']) {
                $ret['pass_ungleich'] = 1;
            }
            if (strlen($data['pass']) < $conf['kunden']['kundenregistrierung_passwortlaenge']) {
                $ret['pass_zu_kurz'] = 1;
            }
        }
        //existiert diese email bereits?
        if (!isset($ret['email']) && !isEmailAvailable($data['email'], $_SESSION['Kunde']->kKunde ?? 0)) {
            if (!(isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0)) {
                $ret['email_vorhanden'] = 1;
            }
            $ret['email'] = 5;
        }
    }
    // Selbstdef. Kundenfelder
    if (isset($conf['kundenfeld']['kundenfeld_anzeigen']) && $conf['kundenfeld']['kundenfeld_anzeigen'] === 'Y') {
        $customerFields = Shop::Container()->getDB()->selectAll(
            'tkundenfeld',
            'kSprache',
            Shop::getLanguage(),
            'kKundenfeld, cName, cTyp, nPflicht, nEditierbar'
        );
        foreach ($customerFields as $customerField) {
            // Kundendaten ändern?
            if ((int)$data['editRechnungsadresse'] === 1) {
                if (!isset($data['custom_' . $customerField->kKundenfeld])
                    && $customerField->nPflicht == 1
                    && $customerField->nEditierbar == 1
                ) {
                    $ret['custom'][$customerField->kKundenfeld] = 1;
                } elseif (!empty($data['custom_' . $customerField->kKundenfeld])) {
                    // Datum
                    // 1 = leer
                    // 2 = falsches Format
                    // 3 = falsches Datum
                    // 0 = o.k.
                    if ($customerField->cTyp === 'datum') {
                        $_dat   = $data['custom_' . $customerField->kKundenfeld];
                        $_datTs = strtotime($_dat);
                        $_dat   = ($_datTs !== false) ? date('d.m.Y', $_datTs) : false;
                        $check  = StringHandler::checkDate($_dat);
                        if ($check !== 0) {
                            $ret['custom'][$customerField->kKundenfeld] = $check;
                        }
                    } elseif ($customerField->cTyp === 'zahl') {
                        // Zahl, 4 = keine Zahl
                        if ($data['custom_' . $customerField->kKundenfeld] !=
                            (float)$data['custom_' . $customerField->kKundenfeld]
                        ) {
                            $ret['custom'][$customerField->kKundenfeld] = 4;
                        }
                    }
                }
            } elseif (empty($data['custom_' . $customerField->kKundenfeld]) && $customerField->nPflicht == 1) {
                $ret['custom'][$customerField->kKundenfeld] = 1;
            } elseif (!empty($data['custom_' . $customerField->kKundenfeld])) {
                // Datum
                // 1 = leer
                // 2 = falsches Format
                // 3 = falsches Datum
                // 0 = o.k.
                if ($customerField->cTyp === 'datum') {
                    $_dat   = $data['custom_' . $customerField->kKundenfeld];
                    $_datTs = strtotime($_dat);
                    $_dat   = ($_datTs !== false) ? date('d.m.Y', $_datTs) : false;
                    $check  = StringHandler::checkDate($_dat);
                    if ($check !== 0) {
                        $ret['custom'][$customerField->kKundenfeld] = $check;
                    }
                } elseif ($customerField->cTyp === 'zahl') {
                    // Zahl, 4 = keine Zahl
                    if ($data['custom_' . $customerField->kKundenfeld] !=
                        (float)$data['custom_' . $customerField->kKundenfeld]
                    ) {
                        $ret['custom'][$customerField->kKundenfeld] = 4;
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
        && (int)$data['editRechnungsadresse'] !== 1
        && $conf['kunden']['kundenregistrierung_pruefen_zeit'] === 'Y'
    ) {
        $dRegZeit = $_SESSION['dRegZeit'] ?? 0;
        if (!($dRegZeit + 5 < time())) {
            $ret['formular_zeit'] = 1;
        }
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
function checkKundenFormular(int $kundenaccount, $checkpass = 1): array
{
    $data = StringHandler::filterXSS($_POST); // create a copy

    return checkKundenFormularArray($data, $kundenaccount, $checkpass);
}

/**
 * @param array $data
 * @return array
 */
function checkLieferFormularArray($data): array
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
            $result = StringHandler::checkPhoneNumber($data[$telType]);
            if ($result === 1 && $conf['kunden']["lieferadresse_abfragen_$telType"] === 'Y') {
                $ret[$telType] = 1;
            } elseif ($result > 1) {
                $ret[$telType] = $result;
            }
        }
    }

    if (empty($_SESSION['check_liefer_plzort'])
        && $conf['kunden']['kundenregistrierung_abgleichen_plz'] === 'Y'
    ) {
        if (!valid_plzort($data['plz'], $data['ort'], $data['land'])) {
            $ret['plz']                      = 2;
            $ret['ort']                      = 2;
            $_SESSION['check_liefer_plzort'] = 1;
        }
    } else {
        unset($_SESSION['check_liefer_plzort']);
    }

    return !empty($ret) ? ['shippingAddress' => $ret] : $ret;
}

/**
 * @param array $cPost_arr
 * @return array
 */
function checkLieferFormular($cPost_arr = null): array
{
    return checkLieferFormularArray($cPost_arr ?? $_POST);
}

/**
 * @param object|Kupon $Kupon
 * @return array
 * @deprecated since 5.0.0
 */
function checkeKupon($Kupon): array
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
 * @param array $cartPositions
 * @return float
 */
function gibGesamtsummeKuponartikelImWarenkorb($Kupon, array $cartPositions)
{
    $gesamtsumme = 0;
    foreach ($cartPositions as $Position) {
        if ((empty($Kupon->cArtikel) || warenkorbKuponFaehigArtikel($Kupon, [$Position]))
            && (empty($Kupon->cHersteller)
                || $Kupon->cHersteller === '-1'
                || warenkorbKuponFaehigHersteller($Kupon, [$Position]))
            && (empty($Kupon->cKategorien)
                || $Kupon->cKategorien === '-1'
                || warenkorbKuponFaehigKategorien($Kupon, [$Position]))
        ) {
            $gesamtsumme += $Position->fPreis *
                $Position->nAnzahl *
                ((100 + TaxHelper::getSalesTax($Position->kSteuerklasse)) / 100);
        }
    }

    return round($gesamtsumme, 2);
}

/**
 * @param Kupon|object $Kupon
 * @param array $cartPositions
 * @return bool
 */
function warenkorbKuponFaehigArtikel($Kupon, array $cartPositions): bool
{
    foreach ($cartPositions as $Pos) {
        if ($Pos->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL
            && preg_match('/;' . preg_quote($Pos->Artikel->cArtNr, '/') . ';/i', $Kupon->cArtikel)
        ) {
            return true;
        }
    }

    return false;
}

/**
 * @param Kupon|object $Kupon
 * @param array $cartPositions
 * @return bool
 */
function warenkorbKuponFaehigHersteller($Kupon, array $cartPositions): bool
{
    foreach ($cartPositions as $Pos) {
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
 * @param array $cartPositions
 * @return bool
 */
function warenkorbKuponFaehigKategorien($Kupon, array $cartPositions): bool
{
    $categories = [];
    foreach ($cartPositions as $Pos) {
        if (empty($Pos->Artikel)) {
            continue;
        }
        $kArtikel = $Pos->Artikel->kArtikel;
        // Kind?
        if (ArtikelHelper::isVariChild($kArtikel)) {
            $kArtikel = ArtikelHelper::getParent($kArtikel);
        }
        $catData = Shop::Container()->getDB()->selectAll('tkategorieartikel', 'kArtikel', $kArtikel, 'kKategorie');
        foreach ($catData as $category) {
            $category->kKategorie = (int)$category->kKategorie;
            if (!in_array($category->kKategorie, $categories, true)) {
                $categories[] = $category->kKategorie;
            }
        }
    }
    foreach ($categories as $category) {
        if (preg_match('/;' . preg_quote($category, '/') . ';/i', $Kupon->cKategorien)) {
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
    $kKunde   = isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0
        ? (int)$_SESSION['Kunde']->kKunde
        : 0;
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

    $customer->dGeburtstag           = DateHelper::convertDateToMysqlStandard($customer->dGeburtstag ?? '');
    $customer->dGeburtstag_formatted = $customer->dGeburtstag === '_DBNULL_'
        ? ''
        : DateTime::createFromFormat('Y-m-d', $customer->dGeburtstag)->format('d.m.Y');
    $customer->angezeigtesLand       = Sprache::getCountryCodeByCountryName($customer->cLand);
    if (!empty($customer->cBundesland)) {
        $oISO = Staat::getRegionByIso($customer->cBundesland, $customer->cLand);
        if (is_object($oISO)) {
            $customer->cBundesland = $oISO->cName;
        }
    }

    return $customer;
}

/**
 * @param array $post
 * @return array
 */
function getKundenattribute($post): array
{
    $customerAttributes = [];
    $fieldData          = Shop::Container()->getDB()->selectAll(
        'tkundenfeld',
        'kSprache',
        Shop::getLanguage(),
        'kKundenfeld, cName, cWawi'
    );
    foreach ($fieldData as $field) {
        $oKundenfeld              = new stdClass();
        $oKundenfeld->kKundenfeld = $field->kKundenfeld;
        $oKundenfeld->cName       = $field->cName;
        $oKundenfeld->cWawi       = $field->cWawi;
        $oKundenfeld->cWert       = isset($post['custom_' . $field->kKundenfeld])
            ? StringHandler::filterXSS($post['custom_' . $field->kKundenfeld])
            : null;

        $customerAttributes[$field->kKundenfeld] = $oKundenfeld;
    }

    return $customerAttributes;
}

/**
 * @return array
 */
function getKundenattributeNichtEditierbar(): array
{
    return Shop::Container()->getDB()->selectAll(
        'tkundenfeld',
        ['kSprache', 'nEditierbar'],
        [Shop::getLanguage(), 0],
        'kKundenfeld'
    );
}

/**
 * @return array - non editable customer fields
 */
function getNonEditableCustomerFields(): array
{
    $res                = [];
    $customerAttributes = Shop::Container()->getDB()->query(
        'SELECT ka.kKundenfeld
             FROM tkundenattribut AS ka
             LEFT JOIN tkundenfeld AS kf
                ON ka.kKundenfeld = kf.kKundenfeld
             WHERE kKunde = ' . \Session\Session::getCustomer()->getID() . '
             AND kf.nEditierbar = 0',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($customerAttributes as $attribute) {
        $oKundenfeldAttribut              = new stdClass();
        $oKundenfeldAttribut->kKundenfeld = $attribute->kKundenfeld;
        $res[$attribute->kKundenfeld]     = $oKundenfeldAttribut;
    }

    return $res;
}

/**
 * @param array $post
 * @return Lieferadresse
 */
function getLieferdaten($post)
{
    $post = StringHandler::filterXSS($post);
    //erstelle neue Lieferadresse
    $shippingAddress                  = new Lieferadresse();
    $shippingAddress->cAnrede         = $post['anrede'] ?? null;
    $shippingAddress->cVorname        = $post['vorname'];
    $shippingAddress->cNachname       = $post['nachname'];
    $shippingAddress->cStrasse        = $post['strasse'];
    $shippingAddress->cHausnummer     = $post['hausnummer'];
    $shippingAddress->cPLZ            = $post['plz'];
    $shippingAddress->cOrt            = $post['ort'];
    $shippingAddress->cLand           = $post['land'];
    $shippingAddress->cMail           = $post['email'] ?? '';
    $shippingAddress->cTel            = $post['tel'] ?? null;
    $shippingAddress->cFax            = $post['fax'] ?? null;
    $shippingAddress->cFirma          = $post['firma'] ?? null;
    $shippingAddress->cZusatz         = $post['firmazusatz'] ?? null;
    $shippingAddress->cTitel          = $post['titel'] ?? null;
    $shippingAddress->cAdressZusatz   = $post['adresszusatz'] ?? null;
    $shippingAddress->cMobil          = $post['mobil'] ?? null;
    $shippingAddress->cBundesland     = $post['bundesland'] ?? null;
    $shippingAddress->angezeigtesLand = Sprache::getCountryCodeByCountryName($shippingAddress->cLand);

    if (!empty($shippingAddress->cBundesland)) {
        $oISO = Staat::getRegionByIso($shippingAddress->cBundesland, $shippingAddress->cLand);
        if (is_object($oISO)) {
            $shippingAddress->cBundesland = $oISO->cName;
        }
    }

    return $shippingAddress;
}

/**
 * @param array $cartPositions
 * @return string
 */
function getArtikelQry(array $cartPositions): string
{
    $ret = '';
    foreach ($cartPositions as $Pos) {
        if (isset($Pos->Artikel->cArtNr) && strlen($Pos->Artikel->cArtNr) > 0) {
            $ret .= " OR FIND_IN_SET('" .
                str_replace('%', '\%', Shop::Container()->getDB()->escape($Pos->Artikel->cArtNr))
                . "', REPLACE(cArtikel, ';', ',')) > 0";
        }
    }

    return $ret;
}

/**
 * @return bool
 */
function guthabenMoeglich(): bool
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
function freeGiftStillValid(): bool
{
    $cart  = \Session\Session::getCart();
    $valid = true;
    foreach ($cart->PositionenArr as $oPosition) {
        if ($oPosition->nPosTyp !== C_WARENKORBPOS_TYP_GRATISGESCHENK) {
            continue;
        }
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

    return $valid;
}

/**
 * @param string $plz
 * @param string $ort
 * @param string $land
 * @return bool
 */
function valid_plzort(string $plz, string $ort, string $land): bool
{
    // Länder die wir mit Ihren Postleitzahlen in der Datenbank haben
    $cSupportedCountry_arr = ['DE', 'AT', 'CH'];
    if (!in_array(strtoupper($land), $cSupportedCountry_arr, true)) {
        return true;
    }
    $obj = Shop::Container()->getDB()->executeQueryPrepared(
        'SELECT kPLZ
        FROM tplz
        WHERE cPLZ = :plz
            AND INSTR(cOrt COLLATE utf8_german2_ci, :ort)
            AND cLandISO = :land',
        [
            'plz'  => $plz,
            'ort'  => $ort,
            'land' => $land
        ],
        \DB\ReturnType::SINGLE_OBJECT
    );

    return isset($obj->kPLZ) && $obj->kPLZ > 0;
}

/**
 * @param string $step
 * @return array
 */
function gibBestellschritt(string $step)
{
    $res    = [];
    $res[1] = 3;
    $res[2] = 3;
    $res[3] = 3;
    $res[4] = 3;
    $res[5] = 3;
    switch ($step) {
        case 'accountwahl':
        case 'edit_customer_address':
            $res[1] = 1;
            $res[2] = 3;
            $res[3] = 3;
            $res[4] = 3;
            $res[5] = 3;
            break;

        case 'Lieferadresse':
            $res[1] = 2;
            $res[2] = 1;
            $res[3] = 3;
            $res[4] = 3;
            $res[5] = 3;
            break;

        case 'Versand':
            $res[1] = 2;
            $res[2] = 2;
            $res[3] = 1;
            $res[4] = 3;
            $res[5] = 3;
            break;

        case 'Zahlung':
            $res[1] = 2;
            $res[2] = 2;
            $res[3] = 2;
            $res[4] = 1;
            $res[5] = 3;
            break;

        case 'ZahlungZusatzschritt':
            $res[1] = 2;
            $res[2] = 2;
            $res[3] = 2;
            $res[4] = 1;
            $res[5] = 3;
            break;

        case 'Bestaetigung':
            $res[1] = 2;
            $res[2] = 2;
            $res[3] = 2;
            $res[4] = 2;
            $res[5] = 1;
            break;

        default:
            break;
    }

    return $res;
}

/**
 * @return Lieferadresse
 */
function setzeLieferadresseAusRechnungsadresse(): Lieferadresse
{
    $shippingAddress                  = new Lieferadresse();
    $shippingAddress->kKunde          = $_SESSION['Kunde']->kKunde;
    $shippingAddress->cAnrede         = $_SESSION['Kunde']->cAnrede;
    $shippingAddress->cVorname        = $_SESSION['Kunde']->cVorname;
    $shippingAddress->cNachname       = $_SESSION['Kunde']->cNachname;
    $shippingAddress->cStrasse        = $_SESSION['Kunde']->cStrasse;
    $shippingAddress->cHausnummer     = $_SESSION['Kunde']->cHausnummer;
    $shippingAddress->cPLZ            = $_SESSION['Kunde']->cPLZ;
    $shippingAddress->cOrt            = $_SESSION['Kunde']->cOrt;
    $shippingAddress->cLand           = $_SESSION['Kunde']->cLand;
    $shippingAddress->cMail           = $_SESSION['Kunde']->cMail;
    $shippingAddress->cTel            = $_SESSION['Kunde']->cTel;
    $shippingAddress->cFax            = $_SESSION['Kunde']->cFax;
    $shippingAddress->cFirma          = $_SESSION['Kunde']->cFirma;
    $shippingAddress->cZusatz         = $_SESSION['Kunde']->cZusatz;
    $shippingAddress->cTitel          = $_SESSION['Kunde']->cTitel;
    $shippingAddress->cAdressZusatz   = $_SESSION['Kunde']->cAdressZusatz;
    $shippingAddress->cMobil          = $_SESSION['Kunde']->cMobil;
    $shippingAddress->cBundesland     = $_SESSION['Kunde']->cBundesland;
    $shippingAddress->angezeigtesLand = Sprache::getCountryCodeByCountryName($shippingAddress->cLand);
    $_SESSION['Lieferadresse']      = $shippingAddress;

    return $shippingAddress;
}

/**
 * @return array
 */
function gibSelbstdefKundenfelder(): array
{
    $customerFields = Shop::Container()->getDB()->query(
        'SELECT *
            FROM tkundenfeld
            WHERE kSprache = ' . Shop::getLanguageID(). '
            ORDER BY nSort ASC',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($customerFields as $customerField) {
        if ($customerField->cTyp !== 'auswahl') {
            continue;
        }
        $customerField->oKundenfeldWert_arr = Shop::Container()->getDB()->selectAll(
            'tkundenfeldwert',
            'kKundenfeld',
            (int)$customerField->kKundenfeld,
            '*',
            '`kKundenfeld`, `nSort`, `kKundenfeldWert` ASC'
        );
    }

    return $customerFields;
}

/**
 * @return int
 */
function pruefeAjaxEinKlick(): int
{
    // Ist der Kunde eingeloggt?
    if (($customerID = \Session\Session::getCustomer()->getID()) <= 0) {
        return 0;
    }
    $customerGroupID = \Session\Session::getCustomerGroup()->getID();
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

    if (!isset($oLetzteBestellung->kBestellung) || $oLetzteBestellung->kBestellung <= 0) {
        return 2;
    }
    // Hat der Kunde eine Lieferadresse angegeben?
    if ($oLetzteBestellung->kLieferadresse > 0) {
        $oLieferdaten = Shop::Container()->getDB()->query(
            'SELECT kLieferadresse
                FROM tlieferadresse
                WHERE kKunde = ' . $customerID . '
                    AND kLieferadresse = ' . (int)$oLetzteBestellung->kLieferadresse,
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
    if (empty($oLetzteBestellung->kVersandart)) {
        return 3;
    }
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

/**
 *
 */
function ladeAjaxEinKlick(): void
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
            \Session\Session::getCustomerGroup()->getID()
        ))
        ->assign('WarensummeLocalized', \Session\Session::getCart()->gibGesamtsummeWarenLocalized())
        ->assign('Warensumme', \Session\Session::getCart()->gibGesamtsummeWaren());
}

/**
 * @param string $cUserLogin
 * @param string $cUserPass
 * @return int
 */
function plausiAccountwahlLogin($cUserLogin, $cUserPass): int
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
function setzeSesssionAccountwahlLogin($oKunde): bool
{
    if (empty($oKunde->kKunde)) {
        return false;
    }
    //in tbesucher kKunde setzen
    if (isset($_SESSION['oBesucher']->kBesucher) && $_SESSION['oBesucher']->kBesucher > 0) {
        $_upd         = new stdClass();
        $_upd->kKunde = (int)$oKunde->kKunde;
        Shop::Container()->getDB()->update('tbesucher', 'kBesucher', (int)$_SESSION['oBesucher']->kBesucher, $_upd);
    }
    \Session\Session::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR)
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

/**
 *
 */
function setzeSmartyAccountwahl()
{
    Shop::Smarty()->assign('untertitel', lang_warenkorb_bestellungEnthaeltXArtikel(\Session\Session::getCart()));
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
function setzeSessionRechnungsadresse(array $cPost_arr, $cFehlendeEingaben_arr)
{
    $oKunde              = getKundendaten($cPost_arr, 0);
    $cKundenattribut_arr = getKundenattribute($cPost_arr);
    if (count($cFehlendeEingaben_arr) > 0) {
        return false;
    }
    $oKunde->cKundenattribut_arr = $cKundenattribut_arr;
    $oKunde->nRegistriert        = 0;
    $_SESSION['Kunde']           = $oKunde;
    if (isset($_SESSION['Warenkorb']->kWarenkorb)
        && \Session\Session::getCart()->gibAnzahlArtikelExt([C_WARENKORBPOS_TYP_ARTIKEL]) > 0
    ) {
        if ($_SESSION['Bestellung']->kLieferadresse == 0 && $_SESSION['Lieferadresse']) {
            setzeLieferadresseAusRechnungsadresse();
        }
        TaxHelper::setTaxRates();
        \Session\Session::getCart()->gibGesamtsummeWarenLocalized();
    }

    return true;
}

/**
 * @param int $nUnreg
 * @param int $nCheckout
 */
function setzeSmartyRechnungsadresse($nUnreg, $nCheckout = 0): void
{
    global $step;
    $conf      = Shop::getSettings([CONF_KUNDEN]);
    $herkunfte = Shop::Container()->getDB()->query(
        'SELECT *
            FROM tkundenherkunft
            ORDER BY nSort',
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
        ->assign('laender', VersandartHelper::getPossibleShippingCountries(\Session\Session::getCustomerGroup()->getID(), false, true))
        ->assign('oKundenfeld_arr', gibSelbstdefKundenfelder());
    if (is_array($_SESSION['Kunde']->cKundenattribut_arr)) {
        Shop::Smarty()->assign('cKundenattribut_arr', $_SESSION['Kunde']->cKundenattribut_arr);
    } else {
        $_SESSION['Kunde']->cKundenattribut_arr = getKundenattribute($_POST);
        Shop::Smarty()->assign('cKundenattribut_arr', $_SESSION['Kunde']->cKundenattribut_arr);
    }
    Shop::Smarty()->assign(
        'warning_passwortlaenge',
        lang_passwortlaenge($conf['kunden']['kundenregistrierung_passwortlaenge'])
    );
    if ((int)$nCheckout === 1) {
        Shop::Smarty()->assign('checkout', 1);
    }
}

/**
 * @param array $cFehlendeEingaben_arr
 * @param int   $nUnreg
 * @param array $cPost_arr
 */
function setzeFehlerSmartyRechnungsadresse($cFehlendeEingaben_arr, $nUnreg = 0, $cPost_arr = null): void
{
    $conf = Shop::getSettings([CONF_KUNDEN]);
    setzeFehlendeAngaben($cFehlendeEingaben_arr);
    $herkunfte  = Shop::Container()->getDB()->query(
        'SELECT *
            FROM tkundenherkunft
            ORDER BY nSort',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    $oKunde_tmp = getKundendaten($cPost_arr, 0);

    Shop::Smarty()->assign('untertitel', Shop::Lang()->get('fillUnregForm', 'checkout'))
        ->assign('herkunfte', $herkunfte)
        ->assign('Kunde', $oKunde_tmp)
        ->assign('laender', VersandartHelper::getPossibleShippingCountries(\Session\Session::getCustomerGroup()->getID(), false, true))
        ->assign('LieferLaender', VersandartHelper::getPossibleShippingCountries(\Session\Session::getCustomerGroup()->getID()))
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
function plausiLieferadresse(array $cPost_arr): array
{
    $missingData = [];

    $_SESSION['Bestellung']->kLieferadresse = (int)$cPost_arr['kLieferadresse'];
    //neue lieferadresse
    if ((int)$cPost_arr['kLieferadresse'] === -1) {
        $cFehlendeAngaben_arr = checkLieferFormular($cPost_arr);
        if (angabenKorrekt($cFehlendeAngaben_arr)) {
            return $missingData;
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
            'SELECT kVersandzuschlagPlz
                FROM tversandzuschlagplz, tversandzuschlag
                WHERE tversandzuschlag.kVersandart = :id
                    AND tversandzuschlag.kVersandzuschlag = tversandzuschlagplz.kVersandzuschlag
                    AND ((tversandzuschlagplz.cPLZAb <= :plz
                        AND tversandzuschlagplz.cPLZBis >= :plz)
                        OR tversandzuschlagplz.cPLZ = :plz)',
            [
                'id'  => (int)$_SESSION['Versandart']->kVersandart,
                'plz' => $_SESSION['Lieferadresse']->cPLZ
            ],
            \DB\ReturnType::SINGLE_OBJECT
        );
        if (isset($plz_x->kVersandzuschlagPlz) && $plz_x->kVersandzuschlagPlz) {
            $delVersand = true;
        }
        if ($delVersand) {
            \Session\Session::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
                   ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG)
                   ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR)
                   ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
                   ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
                   ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR);
            unset($_SESSION['Versandart'], $_SESSION['Zahlungsart']);
        }
        if (!$delVersand) {
            \Session\Session::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG);
        }
    }

    return $missingData;
}

/**
 * @param array $cPost_arr
 */
function setzeSessionLieferadresse(array $cPost_arr): void
{
    $kLieferadresse = isset($cPost_arr['kLieferadresse']) ? (int)$cPost_arr['kLieferadresse'] : -1;

    $_SESSION['Bestellung']->kLieferadresse = $kLieferadresse;
    //neue lieferadresse
    if ($kLieferadresse === -1) {
        $_SESSION['Lieferadresse'] = getLieferdaten($cPost_arr);
    } elseif ($kLieferadresse > 0) {
        //vorhandene lieferadresse
        $LA = Shop::Container()->getDB()->query(
            'SELECT kLieferadresse
                FROM tlieferadresse
                WHERE kKunde = ' . \Session\Session::getCustomer()->getID() . '
                AND kLieferadresse = ' . (int)$cPost_arr['kLieferadresse'],
            \DB\ReturnType::SINGLE_OBJECT
        );
        if ($LA->kLieferadresse > 0) {
            $_SESSION['Lieferadresse'] = new Lieferadresse($LA->kLieferadresse);
        }
    } elseif ($kLieferadresse === 0) { //lieferadresse gleich rechnungsadresse
        setzeLieferadresseAusRechnungsadresse();
    }
    TaxHelper::setTaxRates();
    if ((int)$cPost_arr['guthabenVerrechnen'] === 1) {
        $_SESSION['Bestellung']->GuthabenNutzen   = 1;
        $_SESSION['Bestellung']->fGuthabenGenutzt = min(
            $_SESSION['Kunde']->fGuthaben,
            \Session\Session::getCart()->gibGesamtsummeWaren(true, false)
        );
    } else {
        unset($_SESSION['Bestellung']->GuthabenNutzen, $_SESSION['Bestellung']->fGuthabenGenutzt);
    }
}

/**
 *
 */
function setzeSmartyLieferadresse(): void
{
    $kKundengruppe = \Session\Session::getCustomerGroup()->getID();
    if (\Session\Session::getCustomer()->getID() > 0) {
        $Lieferadressen      = [];
        $oLieferdatenTMP_arr = Shop::Container()->getDB()->selectAll(
            'tlieferadresse',
            'kKunde',
            \Session\Session::getCustomer()->getID(),
            'kLieferadresse'
        );
        foreach ($oLieferdatenTMP_arr as $oLieferdatenTMP) {
            if ($oLieferdatenTMP->kLieferadresse > 0) {
                $Lieferadressen[] = new Lieferadresse($oLieferdatenTMP->kLieferadresse);
            }
        }
        $kKundengruppe = \Session\Session::getCustomer()->kKundengruppe;
        Shop::Smarty()->assign('Lieferadressen', $Lieferadressen)
            ->assign('GuthabenLocalized', \Session\Session::getCustomer()->gibGuthabenLocalized());
    }
    Shop::Smarty()->assign('LieferLaender', VersandartHelper::getPossibleShippingCountries($kKundengruppe))
        ->assign('Kunde', $_SESSION['Kunde'])
        ->assign('KuponMoeglich', Kupon::couponsAvailable())
        ->assign('kLieferadresse', $_SESSION['Bestellung']->kLieferadresse);
    if ($_SESSION['Bestellung']->kLieferadresse == -1) {
        Shop::Smarty()->assign('Lieferadresse', null);
    }
}

/**
 * @param array $missingData
 * @param array $cPost_arr
 */
function setzeFehlerSmartyLieferadresse($missingData, array $cPost_arr): void
{
    /** @var array('Kunde' => Kunde) $_SESSION */
    $kKundengruppe = \Session\Session::getCustomerGroup()->getID();
    if (\Session\Session::getCustomer()->getID() > 0) {
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
            ->assign('GuthabenLocalized', \Session\Session::getCustomer()->gibGuthabenLocalized());
    }
    setzeFehlendeAngaben($missingData, 'shipping_address');
    Shop::Smarty()->assign('laender', VersandartHelper::getPossibleShippingCountries($kKundengruppe), false, true)
        ->assign('LieferLaender', VersandartHelper::getPossibleShippingCountries($kKundengruppe))
        ->assign('Kunde', $_SESSION['Kunde'])
        ->assign('KuponMoeglich', Kupon::couponsAvailable())
        ->assign('kLieferadresse', $_SESSION['Bestellung']->kLieferadresse)
        ->assign('kLieferadresse', $cPost_arr['kLieferadresse']);
    if ($_SESSION['Bestellung']->kLieferadresse == -1) {
        Shop::Smarty()->assign('Lieferadresse', mappeLieferadresseKontaktdaten($cPost_arr));
    }
}

/**
 * @param array $shippingAddress
 * @return stdClass
 */
function mappeLieferadresseKontaktdaten(array $shippingAddress): stdClass
{
    $form                = new stdClass();
    $form->cAnrede       = $shippingAddress['anrede'];
    $form->cTitel        = $shippingAddress['titel'];
    $form->cVorname      = $shippingAddress['vorname'];
    $form->cNachname     = $shippingAddress['nachname'];
    $form->cFirma        = $shippingAddress['firma'];
    $form->cZusatz       = $shippingAddress['firmazusatz'];
    $form->cStrasse      = $shippingAddress['strasse'];
    $form->cHausnummer   = $shippingAddress['hausnummer'];
    $form->cAdressZusatz = $shippingAddress['adresszusatz'];
    $form->cPLZ          = $shippingAddress['plz'];
    $form->cOrt          = $shippingAddress['ort'];
    $form->cBundesland   = $shippingAddress['bundesland'];
    $form->cLand         = $shippingAddress['land'];
    $form->cMail         = $shippingAddress['email'];
    $form->cTel          = $shippingAddress['tel'];
    $form->cMobil        = $shippingAddress['mobil'];
    $form->cFax          = $shippingAddress['fax'];

    return $form;
}

/**
 *
 */
function setzeSmartyVersandart(): void
{
    gibStepVersand();
}

/**
 *
 */
function setzeFehlerSmartyVersandart(): void
{
    Shop::Smarty()->assign('hinweis', Shop::Lang()->get('fillShipping', 'checkout'));
}

/**
 * @param Zahlungsart $oZahlungsart
 * @param array       $cPost_arr
 * @return array
 */
function plausiZahlungsartZusatz($oZahlungsart, array $cPost_arr)
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
        $kSteuerklasse = \Session\Session::getCart()->gibVersandkostenSteuerklasse();
        $fPreis        = $fNetto;
        if (!\Session\Session::getCustomerGroup()->isMerchant()) {
            $fPreis = $fNetto * ((100 + (float)$_SESSION['Steuersatz'][$kSteuerklasse]) / 100);
        }
        $cName['ger']                                    = Shop::Lang()->get('trustedshopsName');
        $cName['eng']                                    = Shop::Lang()->get('trustedshopsName');
        $_SESSION['TrustedShops']->cKaeuferschutzProdukt = StringHandler::htmlentities(
            StringHandler::filterXSS($cPost_arr['cKaeuferschutzProdukt'])
        );
        \Session\Session::getCart()->erstelleSpezialPos(
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
function setzeSmartyZahlungsartZusatz($cPost_arr, $cFehlendeEingaben_arr = 0): void
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
            \Session\Session::getCustomerGroup()->getID()
        ))
        ->assign('WarensummeLocalized', \Session\Session::getCart()->gibGesamtsummeWarenLocalized())
        ->assign('Warensumme', \Session\Session::getCart()->gibGesamtsummeWaren());
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
        $fehlendeAngaben[$context] = isset($fehlendeAngaben[$context])
            ? array_merge($fehlendeAngaben[$context], $fehlendeAngabe)
            : $fehlendeAngabe;
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
            \Session\Session::getCustomerGroup()->getID()
        ))
        ->assign('Ueberschrift', Shop::Lang()->get('orderStep0Title', 'checkout'))
        ->assign('UeberschriftKlein', Shop::Lang()->get('orderStep0Title2', 'checkout'))
        ->assign('Einstellungen', $Einstellungen)
        ->assign('hinweis', $hinweis)
        ->assign('step', $step)
        ->assign('WarensummeLocalized', \Session\Session::getCart()->gibGesamtsummeWarenLocalized())
        ->assign('Warensumme', \Session\Session::getCart()->gibGesamtsummeWaren())
        ->assign('Steuerpositionen', \Session\Session::getCart()->gibSteuerpositionen())
        ->assign('bestellschritt', gibBestellschritt($step))
        ->assign('sess', $_SESSION);
}

/**
 * @param int $nStep
 */
function loescheSession(int $nStep)
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
            \Session\Session::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
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
            \Session\Session::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
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
            \Session\Session::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
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
            \Session\Session::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
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
            \Session\Session::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
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
function mappeBestellvorgangZahlungshinweis(int $nHinweisCode)
{
    $cHinweis = '';
    if ($nHinweisCode > 0) {
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
            default:
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
 * @param int $customerID
 * @return bool
 */
function isEmailAvailable(string $email, int $customerID = 0): bool
{
    return Shop::Container()->getDB()->queryPrepared(
        'SELECT *
            FROM tkunde
            WHERE cmail = :email
              AND nRegistriert = 1
            AND kKunde != :customerID',
        ['email' => $email, 'customerID' => $customerID],
        \DB\ReturnType::SINGLE_OBJECT
    ) === false;
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

/**
 * @param string $str
 * @return string
 * @deprecated since 5.0.0
 */
function umlauteUmschreibenA2AE(string $str): string
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $src = ['ä', 'ö', 'ü', 'ß', 'Ä', 'Ö', 'Ü'];
    $rpl = ['ae', 'oe', 'ue', 'ss', 'Ae', 'Oe', 'Ue'];

    return str_replace($src, $rpl, $str);
}

/**
 * @param string $str
 * @return string
 * @deprecated since 5.0.0
 */
function umlauteUmschreibenAE2A(string $str): string
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $rpl = ['ä', 'ö', 'ü', 'ß', 'Ä', 'Ö', 'Ü'];
    $src = ['ae', 'oe', 'ue', 'ss', 'Ae', 'Oe', 'Ue'];

    return str_replace($src, $rpl, $str);
}
