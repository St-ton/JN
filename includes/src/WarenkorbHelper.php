<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class WarenkorbHelper
 */
class WarenkorbHelper
{
    const NET   = 0;
    const GROSS = 1;

    /**
     * @param int $decimals
     * @return object
     */
    public function getTotal($decimals = 0)
    {
        $info            = new stdClass();
        $info->type      = Session::CustomerGroup()->isMerchant() ? self::NET : self::GROSS;
        $info->currency  = null;
        $info->article   = [0, 0];
        $info->shipping  = [0, 0];
        $info->discount  = [0, 0];
        $info->surcharge = [0, 0];
        $info->total     = [0, 0];
        $info->items     = [];
        $info->currency  = $this->getCurrency();

        foreach (Session::Cart()->PositionenArr as $oPosition) {
            $amountItem = $oPosition->fPreisEinzelNetto;
            if (isset($oPosition->WarenkorbPosEigenschaftArr)
                && is_array($oPosition->WarenkorbPosEigenschaftArr)
                && (!isset($oPosition->Artikel->kVaterArtikel) || (int)$oPosition->Artikel->kVaterArtikel === 0)
            ) {
                foreach ($oPosition->WarenkorbPosEigenschaftArr as $oWarenkorbPosEigenschaft) {
                    if ($oWarenkorbPosEigenschaft->fAufpreis != 0) {
                        $amountItem += $oWarenkorbPosEigenschaft->fAufpreis;
                    }
                }
            }
            $amount      = $amountItem * $info->currency->getConversionFactor();
            $amountGross = $amount * ((100 + gibUst($oPosition->kSteuerklasse)) / 100);

            switch ($oPosition->nPosTyp) {
                case C_WARENKORBPOS_TYP_ARTIKEL:
                case C_WARENKORBPOS_TYP_GRATISGESCHENK:
                    $item = (object)[
                        'name'     => '',
                        'quantity' => 1,
                        'amount'   => []
                    ];

                    if (is_array($oPosition->cName)) {
                        $langIso    = $_SESSION['cISOSprache'];
                        $item->name = $oPosition->cName[$langIso];
                    } else {
                        $item->name = $oPosition->cName;
                    }

                    $item->name   = html_entity_decode($item->name);
                    $item->amount = [
                        self::NET   => $amount,
                        self::GROSS => $amountGross
                    ];

                    if ((int)$oPosition->nAnzahl != $oPosition->nAnzahl) {
                        $item->amount[self::NET]   *= $oPosition->nAnzahl;
                        $item->amount[self::GROSS] *= $oPosition->nAnzahl;

                        $item->name = sprintf(
                            '%g %s %s',
                            (float)$oPosition->nAnzahl,
                            $oPosition->Artikel->cEinheit ?: 'x',
                            $item->name
                        );
                    } else {
                        $item->quantity = (int)$oPosition->nAnzahl;
                    }

                    $info->article[self::NET]   += $item->amount[self::NET] * $item->quantity;
                    $info->article[self::GROSS] += $item->amount[self::GROSS] * $item->quantity;

                    $info->items[] = $item;
                    break;

                case C_WARENKORBPOS_TYP_VERSANDPOS:
                case C_WARENKORBPOS_TYP_VERSANDZUSCHLAG:
                case C_WARENKORBPOS_TYP_VERPACKUNG:
                case C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG:
                    $info->shipping[self::NET]   += $amount * $oPosition->nAnzahl;
                    $info->shipping[self::GROSS] += $amountGross * $oPosition->nAnzahl;
                    break;

                case C_WARENKORBPOS_TYP_KUPON:
                case C_WARENKORBPOS_TYP_GUTSCHEIN:
                case C_WARENKORBPOS_TYP_NEUKUNDENKUPON:
                    $info->discount[self::NET]   += $amount * $oPosition->nAnzahl;
                    $info->discount[self::GROSS] += $amountGross * $oPosition->nAnzahl;
                    break;

                case C_WARENKORBPOS_TYP_ZAHLUNGSART:
                    if ($amount >= 0) {
                        $info->surcharge[self::NET]   += $amount * $oPosition->nAnzahl;
                        $info->surcharge[self::GROSS] += $amountGross * $oPosition->nAnzahl;
                    } else {
                        $amount                      *= -1;
                        $info->discount[self::NET]   += $amount * $oPosition->nAnzahl;
                        $info->discount[self::GROSS] += $amountGross * $oPosition->nAnzahl;
                    }
                    break;

                case C_WARENKORBPOS_TYP_TRUSTEDSHOPS:
                case C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR:
                    $info->surcharge[self::NET]   += $amount * $oPosition->nAnzahl;
                    $info->surcharge[self::GROSS] += $amountGross * $oPosition->nAnzahl;
                    break;
                default:
                    break;
            }
        }

        if (isset($_SESSION['Bestellung'], $_SESSION['Bestellung']->GuthabenNutzen)
            && $_SESSION['Bestellung']->GuthabenNutzen === 1
        ) {
            $amountGross = $_SESSION['Bestellung']->fGuthabenGenutzt * -1;
            $amount      = $amountGross;

            $info->discount[self::NET]   += $amount;
            $info->discount[self::GROSS] += $amountGross;
        }

        // positive discount
        $info->discount[self::NET]   *= -1;
        $info->discount[self::GROSS] *= -1;

        // total
        $info->total[self::NET]   = $info->article[self::NET] +
            $info->shipping[self::NET] -
            $info->discount[self::NET] +
            $info->surcharge[self::NET];
        $info->total[self::GROSS] = $info->article[self::GROSS] +
            $info->shipping[self::GROSS] -
            $info->discount[self::GROSS] +
            $info->surcharge[self::GROSS];

        $formatter = function ($prop) use ($decimals) {
            return [
                self::NET   => number_format($prop[self::NET], $decimals, '.', ''),
                self::GROSS => number_format($prop[self::GROSS], $decimals, '.', ''),
            ];
        };

        if ($decimals > 0) {
            $info->article   = $formatter($info->article);
            $info->shipping  = $formatter($info->shipping);
            $info->discount  = $formatter($info->discount);
            $info->surcharge = $formatter($info->surcharge);
            $info->total     = $formatter($info->total);

            foreach ($info->items as &$item) {
                $item->amount = $formatter($item->amount);
            }
        }

        return $info;
    }

    /**
     * @return Warenkorb
     */
    public function getObject()
    {
        return $_SESSION['Warenkorb'];
    }

    /**
     * @return Lieferadresse
     */
    public function getShippingAddress()
    {
        return $_SESSION['Lieferadresse'];
    }

    /**
     * @return Rechnungsadresse
     */
    public function getBillingAddress()
    {
        return $_SESSION['Rechnungsadresse'];
    }

    /**
     * @return Kunde
     */
    public function getCustomer()
    {
        return $_SESSION['Kunde'];
    }

    /**
     * @return Currency
     */
    public function getCurrency()
    {
        return Session::Currency();
    }

    /**
     * @return string
     */
    public function getCurrencyISO()
    {
        return $this->getCurrency()->getCode();
    }

    /**
     * @return null
     */
    public function getInvoiceID()
    {
        return null;
    }

    /**
     * @return int
     */
    public function getIdentifier()
    {
        return 0;
    }

    /**
     * @param WarenkorbPos $wkPos
     * @param object       $variation
     * @return void
     */
    public static function setVariationPicture(WarenkorbPos $wkPos, $variation)
    {
        if ($wkPos->variationPicturesArr === null) {
            $wkPos->variationPicturesArr = [];
        }
        $imageBaseURL          = Shop::getImageBaseURL();
        $oPicture              = (object)[
            'isVariation'  => true,
            'cPfadMini'    => $variation->cPfadMini,
            'cPfadKlein'   => $variation->cPfadKlein,
            'cPfadNormal'  => $variation->cPfadNormal,
            'cPfadGross'   => $variation->cPfadGross,
            'cURLMini'     => $imageBaseURL . $variation->cPfadMini,
            'cURLKlein'    => $imageBaseURL . $variation->cPfadKlein,
            'cURLNormal'   => $imageBaseURL . $variation->cPfadNormal,
            'cURLGross'    => $imageBaseURL . $variation->cPfadGross,
            'nNr'          => count($wkPos->variationPicturesArr) + 1,
            'cAltAttribut' => str_replace(['"', "'"], '', $wkPos->Artikel->cName . ' - ' . $variation->cName),
        ];
        $oPicture->galleryJSON = $wkPos->Artikel->getArtikelImageJSON($oPicture);

        $wkPos->variationPicturesArr[] = $oPicture;
    }

    /**
     * @param Warenkorb $warenkorb
     * @return int - since 4.07
     */
    public static function addVariationPictures(Warenkorb $warenkorb)
    {
        $count = 0;
        foreach ($warenkorb->PositionenArr as $wkPos) {
            if (isset($wkPos->variationPicturesArr) && count($wkPos->variationPicturesArr) > 0) {
                ArtikelHelper::addVariationPictures($wkPos->Artikel, $wkPos->variationPicturesArr);
                ++$count;
            }
        }

        return $count;
    }

    /**
     * @former checkeWarenkorbEingang()
     * @return bool
     */
    public static function checkAdditions()
    {
        // prg
        if (isset($_SESSION['bWarenkorbHinzugefuegt'], $_SESSION['bWarenkorbAnzahl'], $_SESSION['hinweis'])) {
            if (isset($_POST['a'])) {
                require_once PFAD_ROOT . PFAD_INCLUDES . 'artikel_inc.php';
            }
            Shop::Smarty()
                ->assign('bWarenkorbHinzugefuegt', $_SESSION['bWarenkorbHinzugefuegt'])
                ->assign('bWarenkorbAnzahl', $_SESSION['bWarenkorbAnzahl'])
                ->assign('hinweis', $_SESSION['hinweis'])
                ->assign('Xselling', isset($_POST['a']) ? gibArtikelXSelling($_POST['a']) : null);
            unset($_SESSION['hinweis'], $_SESSION['bWarenkorbAnzahl'], $_SESSION['bWarenkorbHinzugefuegt']);
        }
        $fAnzahl = 0;
        if (isset($_POST['anzahl'])) {
            $_POST['anzahl'] = str_replace(',', '.', $_POST['anzahl']);
        }
        if (isset($_POST['anzahl']) && (float)$_POST['anzahl'] > 0) {
            $fAnzahl = (float)$_POST['anzahl'];
        } elseif (isset($_GET['anzahl']) && (float)$_GET['anzahl'] > 0) {
            $fAnzahl = (float)$_GET['anzahl'];
        }
        if (isset($_POST['n']) && (float)$_POST['n'] > 0) {
            $fAnzahl = (float)$_POST['n'];
        } elseif (isset($_GET['n']) && (float)$_GET['n'] > 0) {
            $fAnzahl = (float)$_GET['n'];
        }
        $kArtikel = isset($_POST['a']) ? (int)$_POST['a'] : verifyGPCDataInteger('a');
        $conf     = Shop::getSettings([CONF_GLOBAL, CONF_VERGLEICHSLISTE]);
        executeHook(HOOK_TOOLS_GLOBAL_CHECKEWARENKORBEINGANG_ANFANG, [
            'kArtikel' => $kArtikel,
            'fAnzahl'  => $fAnzahl
        ]);
        if ($kArtikel > 0
            && (isset($_POST['Wunschliste']) || isset($_GET['Wunschliste']))
            && $conf['global']['global_wunschliste_anzeigen'] === 'Y'
        ) {
            return self::checkWishlist($kArtikel, $fAnzahl, $conf['global']['global_wunschliste_weiterleitung'] === 'Y');
        }
        if (isset($_POST['Vergleichsliste']) && $kArtikel > 0) {
            return self::checkCompareList($kArtikel, (int)$conf['vergleichsliste']['vergleichsliste_anzahl']);
        }
        if ($kArtikel > 0
            && isset($_POST['wke'])
            && (int)$_POST['wke'] === 1
            && !isset($_POST['Vergleichsliste'])
            && !isset($_POST['Wunschliste'])
        ) { //warenkorbeingang?
            return self::checkCart($kArtikel, $fAnzahl);
        }

        return false;
    }

    /**
     * @param int       $articleID
     * @param int|float $count
     * @return bool
     */
    private static function checkCart($articleID, $count)
    {
        // VariationsBox ist vorhanden => Prüfen ob Anzahl gesetzt wurde
        if (isset($_POST['variBox']) && (int)$_POST['variBox'] === 1) {
            if (self::checkVariboxAmount($_POST['variBoxAnzahl'])) {
                fuegeVariBoxInWK(
                    $_POST['variBoxAnzahl'],
                    $articleID,
                    ArtikelHelper::isParent($articleID),
                    isset($_POST['varimatrix'])
                );
            } else {
                header('Location: ' . Shop::getURL() . '/?a=' . $articleID . '&r=' . R_EMPTY_VARIBOX, true, 303);
                exit;
            }

            return true;
        }
        if (ArtikelHelper::isParent($articleID)) { // Varikombi
            $articleID  = ArtikelHelper::getArticleForParent($articleID);
            $attributes = ArtikelHelper::getSelectedPropertiesForVarCombiArticle($articleID);
        } else {
            $attributes = ArtikelHelper::getSelectedPropertiesForArticle($articleID);
        }
        $isConfigArticle = false;
        if (class_exists('Konfigurator')) {
            if (!Konfigurator::validateKonfig($articleID)) {
                $isConfigArticle = false;
            } else {
                $oGruppen_arr    = Konfigurator::getKonfig($articleID);
                $isConfigArticle = is_array($oGruppen_arr) && count($oGruppen_arr) > 0;
            }
        }

        if (!$isConfigArticle) {
            return fuegeEinInWarenkorb($articleID, $count, $attributes);
        }
        $bValid                  = true;
        $aError_arr              = [];
        $aItemError_arr          = [];
        $oKonfigitem_arr         = [];
        $nKonfiggruppe_arr       = (isset($_POST['item']) && is_array($_POST['item']))
            ? $_POST['item']
            : [];
        $nKonfiggruppeAnzahl_arr = (isset($_POST['quantity']) && is_array($_POST['quantity']))
            ? $_POST['quantity']
            : [];
        $nKonfigitemAnzahl_arr   = (isset($_POST['item_quantity']) && is_array($_POST['item_quantity']))
            ? $_POST['item_quantity']
            : false;
        $bIgnoreLimits           = isset($_POST['konfig_ignore_limits']);

        if (!function_exists('baueArtikelhinweise')) {
            require_once PFAD_ROOT . PFAD_INCLUDES . 'artikel_inc.php';
        }
        // Beim Bearbeiten die alten Positionen löschen
        if (isset($_POST['kEditKonfig'])) {
            $kEditKonfig = (int)$_POST['kEditKonfig'];

            if (!function_exists('loescheWarenkorbPosition')) {
                require_once PFAD_ROOT . PFAD_INCLUDES . 'warenkorb_inc.php';
            }

            loescheWarenkorbPosition($kEditKonfig);
        }

        foreach ($nKonfiggruppe_arr as $nKonfigitem_arr) {
            foreach ($nKonfigitem_arr as $kKonfigitem) {
                $kKonfigitem = (int)$kKonfigitem;
                // Falls ungültig, ignorieren
                if ($kKonfigitem <= 0) {
                    continue;
                }
                $oKonfigitem          = new Konfigitem($kKonfigitem);
                $oKonfigitem->fAnzahl = (float)($nKonfiggruppeAnzahl_arr[$oKonfigitem->getKonfiggruppe()]
                    ?? $oKonfigitem->getInitial());
                if ($nKonfigitemAnzahl_arr && isset($nKonfigitemAnzahl_arr[$oKonfigitem->getKonfigitem()])) {
                    $oKonfigitem->fAnzahl = (float)$nKonfigitemAnzahl_arr[$oKonfigitem->getKonfigitem()];
                }
                // Todo: Mindestbestellanzahl / Abnahmeinterval beachten
                if ($oKonfigitem->fAnzahl < 1) {
                    $oKonfigitem->fAnzahl = 1;
                }
                $count                  = max($count, 1);
                $oKonfigitem->fAnzahlWK = $oKonfigitem->fAnzahl;
                if (!$oKonfigitem->ignoreMultiplier()) {
                    $oKonfigitem->fAnzahlWK *= $count;
                }
                $oKonfigitem_arr[] = $oKonfigitem;
                // Alle Artikel können in den WK gelegt werden?
                if ($oKonfigitem->getPosTyp() === KONFIG_ITEM_TYP_ARTIKEL) {
                    // Varikombi
                    /** @var Artikel $oTmpArtikel */
                    $oKonfigitem->oEigenschaftwerte_arr = [];
                    $oTmpArtikel                        = $oKonfigitem->getArtikel();

                    if ($oTmpArtikel->kVaterArtikel > 0
                        && isset($oTmpArtikel->kEigenschaftKombi)
                        && $oTmpArtikel->kEigenschaftKombi > 0
                    ) {
                        $oKonfigitem->oEigenschaftwerte_arr =
                            gibVarKombiEigenschaftsWerte($oTmpArtikel->kArtikel, false);
                    }
                    if ($oTmpArtikel->cTeilbar !== 'Y' && (int)$count != $count) {
                        $count = (int)$count;
                    }
                    $oTmpArtikel->isKonfigItem = true;
                    $redirectParam             = self::addToCartCheck(
                        $oTmpArtikel,
                        $oKonfigitem->fAnzahlWK,
                        $oKonfigitem->oEigenschaftwerte_arr
                    );
                    if (count($redirectParam) > 0) {
                        $bValid            = false;
                        $aArticleError_arr = baueArtikelhinweise(
                            $redirectParam,
                            true,
                            $oKonfigitem->getArtikel(),
                            $oKonfigitem->fAnzahlWK,
                            $oKonfigitem->getKonfigitem()
                        );

                        $aItemError_arr[$oKonfigitem->getKonfigitem()] = $aArticleError_arr[0];
                    }
                }
            }
        }
        // Komplette Konfiguration validieren
        if (!$bIgnoreLimits
            && (($aError_arr = Konfigurator::validateBasket($articleID, $oKonfigitem_arr)) !== true)
        ) {
            $bValid = false;
        }
        // Alle Konfigurationsartikel können in den WK gelegt werden
        if ($bValid) {
            // Eindeutige ID
            $cUnique = uniqid('', true);
            // Hauptartikel in den WK legen
            fuegeEinInWarenkorb($articleID, $count, $attributes, 0, $cUnique);
            // Konfigartikel in den WK legen
            foreach ($oKonfigitem_arr as $oKonfigitem) {
                $oKonfigitem->isKonfigItem = true;
                switch ($oKonfigitem->getPosTyp()) {
                    case KONFIG_ITEM_TYP_ARTIKEL:
                        Session::Cart()->fuegeEin(
                            $oKonfigitem->getArtikelKey(),
                            $oKonfigitem->fAnzahlWK,
                            $oKonfigitem->oEigenschaftwerte_arr,
                            C_WARENKORBPOS_TYP_ARTIKEL,
                            $cUnique,
                            $oKonfigitem->getKonfigitem()
                        );
                        break;

                    case KONFIG_ITEM_TYP_SPEZIAL:
                        Session::Cart()->erstelleSpezialPos(
                            $oKonfigitem->getName(),
                            $oKonfigitem->fAnzahlWK,
                            $oKonfigitem->getPreis(),
                            $oKonfigitem->getSteuerklasse(),
                            C_WARENKORBPOS_TYP_ARTIKEL,
                            false,
                            !Session::CustomerGroup()->isMerchant(),
                            '',
                            $cUnique,
                            $oKonfigitem->getKonfigitem(),
                            $oKonfigitem->getArtikelKey()
                        );
                        break;
                }

                fuegeEinInWarenkorbPers(
                    $oKonfigitem->getArtikelKey(),
                    $oKonfigitem->fAnzahlWK,
                    $oKonfigitem->oEigenschaftwerte_arr ?? [],
                    $cUnique,
                    $oKonfigitem->getKonfigitem()
                );
            }
            // Warenkorb weiterleiten
            Session::Cart()->redirectTo();
        } else {
            // Gesammelte Fehler anzeigen
            Shop::Smarty()->assign('aKonfigerror_arr', $aError_arr)
                ->assign('aKonfigitemerror_arr', $aItemError_arr)
                ->assign('fehler', Shop::Lang()->get('configError', 'productDetails'));
        }

        $nKonfigitem_arr = [];
        foreach ($nKonfiggruppe_arr as $nTmpKonfigitem_arr) {
            $nKonfigitem_arr = array_merge($nKonfigitem_arr, $nTmpKonfigitem_arr);
        }
        Shop::Smarty()->assign('fAnzahl', $count)
            ->assign('nKonfigitem_arr', $nKonfigitem_arr)
            ->assign('nKonfigitemAnzahl_arr', $nKonfigitemAnzahl_arr)
            ->assign('nKonfiggruppeAnzahl_arr', $nKonfiggruppeAnzahl_arr);

        return $bValid;
    }

    /**
     * @param int       $kArtikel
     * @param int|float $qty
     * @return bool
     */
    private static function checkCompareList($kArtikel, $qty)
    {
        // Prüfen ob nicht schon die maximale Anzahl an Artikeln auf der Vergleichsliste ist
        if (!isset($_SESSION['Vergleichsliste']->oArtikel_arr)
            || $qty > count($_SESSION['Vergleichsliste']->oArtikel_arr)
        ) {
            // Prüfe auf kArtikel
            $productExists = Shop::Container()->getDB()->select(
                'tartikel',
                'kArtikel', $kArtikel,
                null, null,
                null, null,
                false,
                'kArtikel, cName'
            );
            // Falls Artikel vorhanden
            if ($productExists !== null && $productExists->kArtikel > 0) {
                // Sichtbarkeit Prüfen
                $vis = Shop::Container()->getDB()->select(
                    'tartikelsichtbarkeit',
                    'kArtikel', $kArtikel,
                    'kKundengruppe', Session::CustomerGroup()->getID(),
                    null, null,
                    false,
                    'kArtikel'
                );
                if ($vis === null || !isset($vis->kArtikel) || !$vis->kArtikel) {
                    // Prüfe auf Vater Artikel
                    $oVariationen_arr = 0;
                    if (ArtikelHelper::isParent($kArtikel)) {
                        $kArtikel         = ArtikelHelper::getArticleForParent($kArtikel);
                        $oVariationen_arr = ArtikelHelper::getSelectedPropertiesForVarCombiArticle($kArtikel, 1);
                    }
                    // Prüfe auf Vater Artikel
                    if (ArtikelHelper::isParent($kArtikel)) {
                        $kArtikel = ArtikelHelper::getArticleForParent($kArtikel);
                    }
                    $oVergleichsliste = new Vergleichsliste($kArtikel, $oVariationen_arr);
                    // Falls es eine Vergleichsliste in der Session gibt
                    if (isset($_SESSION['Vergleichsliste'])) {
                        // Falls Artikel vorhanden sind
                        if (is_array($_SESSION['Vergleichsliste']->oArtikel_arr)
                            && count($_SESSION['Vergleichsliste']->oArtikel_arr) > 0
                        ) {
                            $bSchonVorhanden = false;
                            foreach ($_SESSION['Vergleichsliste']->oArtikel_arr as $oArtikel) {
                                if ($oArtikel->kArtikel === $oVergleichsliste->oArtikel_arr[0]->kArtikel) {
                                    $bSchonVorhanden = true;
                                    break;
                                }
                            }
                            // Wenn der Artikel der eingetragen werden soll, nicht schon in der Session ist
                            if (!$bSchonVorhanden) {
                                foreach ($_SESSION['Vergleichsliste']->oArtikel_arr as $oArtikel) {
                                    $oVergleichsliste->oArtikel_arr[] = $oArtikel;
                                }
                                $_SESSION['Vergleichsliste'] = $oVergleichsliste;
                                Shop::Smarty()->assign(
                                    'hinweis',
                                    Shop::Lang()->get('comparelistProductadded', 'messages')
                                );
                            } else {
                                Shop::Smarty()->assign(
                                    'fehler',
                                    Shop::Lang()->get('comparelistProductexists', 'messages')
                                );
                            }
                        }
                    } else {
                        // Vergleichsliste neu in der Session anlegen
                        $_SESSION['Vergleichsliste'] = $oVergleichsliste;
                        Shop::Smarty()->assign('hinweis', Shop::Lang()->get('comparelistProductadded', 'messages'));
                        setzeLinks();
                    }
                }
            }

            return true;
        }
        Shop::Smarty()->assign('fehler', Shop::Lang()->get('compareMaxlimit', 'errorMessages'));

        return false;
    }

    /**
     * @param int       $articleID
     * @param float|int $qty
     * @param bool      $redirect
     * @return bool
     */
    private static function checkWishlist($articleID, $qty, $redirect)
    {
        $linkHelper = LinkHelper::getInstance();
        // Prüfe ob Kunde eingeloggt
        if (!isset($_SESSION['Kunde']->kKunde) && !isset($_POST['login'])) {
            //redirekt zum artikel, um variation/en zu wählen / MBM beachten
            if ($qty <= 0) {
                $qty = 1;
            }
            header('Location: ' . $linkHelper->getStaticRoute('jtl.php') .
                '?a=' . $articleID .
                '&n=' . $qty .
                '&r=' . R_LOGIN_WUNSCHLISTE, true, 302);
            exit;
        }

        if ($articleID > 0 && Session::Customer()->getID() > 0) {
            // Prüfe auf kArtikel
            $productExists = Shop::Container()->getDB()->select(
                'tartikel',
                'kArtikel', $articleID,
                null, null,
                null, null,
                false,
                'kArtikel, cName'
            );
            // Falls Artikel vorhanden
            if ($productExists !== null && $productExists->kArtikel > 0) {
                $attributes = [];
                // Sichtbarkeit Prüfen
                $vis = Shop::Container()->getDB()->select(
                    'tartikelsichtbarkeit',
                    'kArtikel', $articleID,
                    'kKundengruppe', Session::CustomerGroup()->getID(),
                    null, null,
                    false,
                    'kArtikel'
                );
                if ($vis === null || !$vis->kArtikel) {
                    // Prüfe auf Vater Artikel
                    if (ArtikelHelper::isParent($articleID)) {
                        // Falls die Wunschliste aus der Artikelübersicht ausgewählt wurde,
                        // muss zum Artikel weitergeleitet werden um Variationen zu wählen
                        if (verifyGPCDataInteger('overview') === 1) {
                            header('Location: ' . Shop::getURL() . '/?a=' . $articleID .
                                '&n=' . $qty .
                                '&r=' . R_VARWAEHLEN, true, 303);
                            exit;
                        }

                        $articleID  = ArtikelHelper::getArticleForParent($articleID);
                        $attributes = $articleID > 0
                            ? ArtikelHelper::getSelectedPropertiesForVarCombiArticle($articleID)
                            : [];
                    } else {
                        $attributes = ArtikelHelper::getSelectedPropertiesForArticle($articleID);
                    }
                    // Prüfe ob die Session ein Wunschlisten Objekt hat
                    if ($articleID > 0) {
                        if (empty($_SESSION['Wunschliste']->kWunschliste)) {
                            $_SESSION['Wunschliste'] = new Wunschliste();
                            $_SESSION['Wunschliste']->schreibeDB();
                        }
                        $qty             = max(1, $qty);
                        $kWunschlistePos = $_SESSION['Wunschliste']->fuegeEin(
                            $articleID,
                            $productExists->cName,
                            $attributes,
                            $qty
                        );
                        // Kampagne
                        if (isset($_SESSION['Kampagnenbesucher'])) {
                            setzeKampagnenVorgang(KAMPAGNE_DEF_WUNSCHLISTE, $kWunschlistePos, $qty);
                        }

                        $obj           = new stdClass();
                        $obj->kArtikel = $articleID;
                        executeHook(HOOK_TOOLS_GLOBAL_CHECKEWARENKORBEINGANG_WUNSCHLISTE, [
                            'kArtikel'         => &$articleID,
                            'fAnzahl'          => &$qty,
                            'AktuellerArtikel' => &$obj
                        ]);

                        Shop::Smarty()->assign('hinweis', Shop::Lang()->get('wishlistProductadded', 'messages'));
                        // Weiterleiten?
                        if ($redirect === true) {
                            header('Location: ' . $linkHelper->getStaticRoute('wunschliste.php'), true, 302);
                            exit;
                        }
                    }
                }
            }

            return true;
        }

        return false;
    }
    
    /**
     * @param Artikel|object $product
     * @param int            $qty
     * @param array          $attributes
     * @param int            $accuracy
     * @return array
     * @former pruefeFuegeEinInWarenkorb()
     */
    public static function addToCartCheck($product, $qty, $attributes, $accuracy = 2)
    {
        $cart          = Session::Cart();
        $kArtikel      = $product->kArtikel; // relevant für die Berechnung von Artikelsummen im Warenkorb
        $redirectParam = [];
        $conf          = Shop::getSettings([CONF_GLOBAL]);
        // Abnahmeintervall
        if ($product->fAbnahmeintervall > 0) {
            $dVielfache = function_exists('bcdiv')
                ? round($product->fAbnahmeintervall * ceil(bcdiv($qty, $product->fAbnahmeintervall, $accuracy + 1)), 2)
                : round($product->fAbnahmeintervall * ceil($qty / $product->fAbnahmeintervall), $accuracy);
            if ($dVielfache != $qty) {
                $redirectParam[] = R_ARTIKELABNAHMEINTERVALL;
            }
        }
        if ((int)$qty != $qty && $product->cTeilbar !== 'Y') {
            $qty = max((int)$qty, 1);
        }
        // mbm
        if ($product->fMindestbestellmenge > $qty + $cart->gibAnzahlEinesArtikels($kArtikel)) {
            $redirectParam[] = R_MINDESTMENGE;
        }
        // lager beachten
        if ($product->cLagerBeachten === 'Y'
            && $product->cLagerVariation !== 'Y'
            && $product->cLagerKleinerNull !== 'Y'
            && $product->fPackeinheit * ($qty + $cart->gibAnzahlEinesArtikels($kArtikel)) > $product->fLagerbestand
        ) {
            $redirectParam[] = R_LAGER;
        }
        // darf preise sehen und somit einkaufen?
        if (!Session::CustomerGroup()->mayViewPrices() || !Session::CustomerGroup()->mayViewCategories()) {
            $redirectParam[] = R_LOGIN;
        }
        // kein vorbestellbares Produkt, aber mit Erscheinungsdatum in Zukunft
        if ($product->nErscheinendesProdukt && $conf['global']['global_erscheinende_kaeuflich'] === 'N') {
            $redirectParam[] = R_VORBESTELLUNG;
        }
        // Die maximale Bestellmenge des Artikels wurde überschritten
        if (isset($product->FunktionsAttribute[FKT_ATTRIBUT_MAXBESTELLMENGE])
            && $product->FunktionsAttribute[FKT_ATTRIBUT_MAXBESTELLMENGE] > 0
            && ($qty > $product->FunktionsAttribute[FKT_ATTRIBUT_MAXBESTELLMENGE]
                || ($cart->gibAnzahlEinesArtikels($kArtikel) + $qty) >
                $product->FunktionsAttribute[FKT_ATTRIBUT_MAXBESTELLMENGE])
        ) {
            $redirectParam[] = R_MAXBESTELLMENGE;
        }
        // Der Artikel ist unverkäuflich
        if (isset($product->FunktionsAttribute[FKT_ATTRIBUT_UNVERKAEUFLICH])
            && $product->FunktionsAttribute[FKT_ATTRIBUT_UNVERKAEUFLICH] == 1
        ) {
            $redirectParam[] = R_UNVERKAEUFLICH;
        }
        // Preis auf Anfrage
        // verhindert, dass Konfigitems mit Preis=0 aus der Artikelkonfiguration fallen wenn 'Preis auf Anfrage' eingestellt ist
        if ($product->bHasKonfig === false
            && !empty($product->isKonfigItem)
            && $product->inWarenkorbLegbar === INWKNICHTLEGBAR_PREISAUFANFRAGE
        ) {
            $product->inWarenkorbLegbar = 1;
        }
        if (($product->bHasKonfig === false && empty($product->isKonfigItem))
            && (!isset($product->Preise->fVKNetto) || $product->Preise->fVKNetto == 0)
            && $conf['global']['global_preis0'] === 'N'
        ) {
            $redirectParam[] = R_AUFANFRAGE;
        }
        // Stücklistenkomponente oder Stückliste und ein Teil ist bereits im Warenkorb?
        $xReturn = pruefeWarenkorbStueckliste($product, $qty);
        if ($xReturn !== null) {
            $redirectParam[] = $xReturn;
        }
        // fehlen zu einer Variation werte?
        foreach ($product->Variationen as $var) {
            //min. 1 Problem?
            if (count($redirectParam) > 0) {
                break;
            }
            if ($var->cTyp === 'FREIFELD') {
                continue;
            }
            //schau, ob diese Eigenschaft auch gewählt wurde
            $bEigenschaftWertDa = false;
            foreach ($attributes as $oEigenschaftwerte) {
                $oEigenschaftwerte->kEigenschaft = (int)$oEigenschaftwerte->kEigenschaft;
                if ($var->cTyp === 'PFLICHT-FREIFELD' && $oEigenschaftwerte->kEigenschaft === $var->kEigenschaft) {
                    if (strlen($oEigenschaftwerte->cFreifeldWert) > 0) {
                        $bEigenschaftWertDa = true;
                    } else {
                        $redirectParam[] = R_VARWAEHLEN;
                        break;
                    }
                } elseif ($var->cTyp !== 'PFLICHT-FREIFELD' && $oEigenschaftwerte->kEigenschaft === $var->kEigenschaft) {
                    $bEigenschaftWertDa = true;
                    //schau, ob auch genug davon auf Lager
                    $EigenschaftWert = new EigenschaftWert($oEigenschaftwerte->kEigenschaftWert);
                    //ist der Eigenschaftwert überhaupt gültig?
                    if ($EigenschaftWert->kEigenschaft !== $oEigenschaftwerte->kEigenschaft) {
                        $redirectParam[] = R_VARWAEHLEN;
                        break;
                    }
                    //schaue, ob genug auf Lager von jeder var
                    if ($product->cLagerBeachten === 'Y'
                        && $product->cLagerVariation === 'Y'
                        && $product->cLagerKleinerNull !== 'Y'
                    ) {
                        if ($EigenschaftWert->fPackeinheit == 0) {
                            $EigenschaftWert->fPackeinheit = 1;
                        }
                        if ($EigenschaftWert->fPackeinheit *
                            ($qty +
                                $cart->gibAnzahlEinerVariation(
                                    $kArtikel,
                                    $EigenschaftWert->kEigenschaftWert
                                )
                            ) > $EigenschaftWert->fLagerbestand
                        ) {
                            $redirectParam[] = R_LAGERVAR;
                        }
                    }
                    break;
                }
            }
            if (!$bEigenschaftWertDa) {
                $redirectParam[] = R_VARWAEHLEN;
                break;
            }
        }
        executeHook('HOOK_ADD_TO_CART_CHECK', [
            'product'       => $product,
            'quantity'      => $qty,
            'attributes'    => $attributes,
            'accuracy'      => $accuracy,
            'redirectParam' => &$redirectParam
        ]);

        return $redirectParam;
    }

    /**
     * @param array $amounts
     * @return bool
     * @former pruefeVariBoxAnzahl
     */
    public static function checkVariboxAmount($amounts)
    {
        if (is_array($amounts) && count($amounts) > 0) {
            // Wurde die variBox überhaupt mit einer Anzahl gefüllt?
            foreach (array_keys($amounts) as $cKeys) {
                if ((float)$amounts[$cKeys] > 0) {
                    return true;
                }
            }
        }

        return false;
    }
}
