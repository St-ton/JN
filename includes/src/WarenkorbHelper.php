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
    const NET = 0;
    const GROSS = 1;

    /**
     * @param int $decimals
     * @return stdClass
     */
    public function getTotal(int $decimals = 0): stdClass
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
            $amountGross = $amount * ((100 + TaxHelper::getSalesTax($oPosition->kSteuerklasse)) / 100);

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
     * @return int - since 5.0.0
     */
    public static function addVariationPictures(Warenkorb $warenkorb): int
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
    public static function checkAdditions(): bool
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
                ->assign('Xselling', isset($_POST['a']) ? ArtikelHelper::getXSelling($_POST['a']) : null);
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
        $kArtikel = isset($_POST['a']) ? (int)$_POST['a'] : RequestHelper::verifyGPCDataInt('a');
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
            if (self::checkVariboxAmount($_POST['variBoxAnzahl'] ?? [])) {
                self::addVariboxToCart(
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
            return self::addProductIDToCart($articleID, $count, $attributes);
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
        // Beim Bearbeiten die alten Positionen löschen
        if (isset($_POST['kEditKonfig'])) {
            $kEditKonfig = (int)$_POST['kEditKonfig'];
            self::deleteCartPosition($kEditKonfig);
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
                            ArtikelHelper::getVarCombiAttributeValues($oTmpArtikel->kArtikel, false);
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
                        $aArticleError_arr = ArtikelHelper::getProductMessages(
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
            self::addProductIDToCart($articleID, $count, $attributes, 0, $cUnique);
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

                WarenkorbPers::addToCheck(
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
     * @param int $kArtikel
     * @param int $maxItems
     * @return bool
     */
    private static function checkCompareList(int $kArtikel, int $maxItems): bool
    {
        // Prüfen ob nicht schon die maximale Anzahl an Artikeln auf der Vergleichsliste ist
        if (isset($_SESSION['Vergleichsliste']->oArtikel_arr) && $maxItems <= count($_SESSION['Vergleichsliste']->oArtikel_arr)) {
            Shop::Smarty()->assign('fehler', Shop::Lang()->get('compareMaxlimit', 'errorMessages'));

            return false;
        }
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
                $oVariationen_arr = [];
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
                }
            }
        }

        return true;
    }

    /**
     * @param int       $productID
     * @param float|int $qty
     * @param bool      $redirect
     * @return bool
     */
    private static function checkWishlist(int $productID, $qty, $redirect)
    {
        $linkHelper = LinkHelper::getInstance();
        // Prüfe ob Kunde eingeloggt
        if (!isset($_SESSION['Kunde']->kKunde) && !isset($_POST['login'])) {
            //redirekt zum artikel, um variation/en zu wählen / MBM beachten
            if ($qty <= 0) {
                $qty = 1;
            }
            header('Location: ' . $linkHelper->getStaticRoute('jtl.php') .
                '?a=' . $productID .
                '&n=' . $qty .
                '&r=' . R_LOGIN_WUNSCHLISTE, true, 302);
            exit;
        }

        if ($productID > 0 && Session::Customer()->getID() > 0) {
            // Prüfe auf kArtikel
            $productExists = Shop::Container()->getDB()->select(
                'tartikel',
                'kArtikel', $productID,
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
                    'kArtikel', $productID,
                    'kKundengruppe', Session::CustomerGroup()->getID(),
                    null, null,
                    false,
                    'kArtikel'
                );
                if ($vis === null || !$vis->kArtikel) {
                    // Prüfe auf Vater Artikel
                    if (ArtikelHelper::isParent($productID)) {
                        // Falls die Wunschliste aus der Artikelübersicht ausgewählt wurde,
                        // muss zum Artikel weitergeleitet werden um Variationen zu wählen
                        if (RequestHelper::verifyGPCDataInt('overview') === 1) {
                            header('Location: ' . Shop::getURL() . '/?a=' . $productID .
                                '&n=' . $qty .
                                '&r=' . R_VARWAEHLEN, true, 303);
                            exit;
                        }

                        $productID  = ArtikelHelper::getArticleForParent($productID);
                        $attributes = $productID > 0
                            ? ArtikelHelper::getSelectedPropertiesForVarCombiArticle($productID)
                            : [];
                    } else {
                        $attributes = ArtikelHelper::getSelectedPropertiesForArticle($productID);
                    }
                    // Prüfe ob die Session ein Wunschlisten Objekt hat
                    if ($productID > 0) {
                        if (empty($_SESSION['Wunschliste']->kWunschliste)) {
                            $_SESSION['Wunschliste'] = new Wunschliste();
                            $_SESSION['Wunschliste']->schreibeDB();
                        }
                        $qty             = max(1, $qty);
                        $kWunschlistePos = $_SESSION['Wunschliste']->fuegeEin(
                            $productID,
                            $productExists->cName,
                            $attributes,
                            $qty
                        );
                        // Kampagne
                        if (isset($_SESSION['Kampagnenbesucher'])) {
                            Kampagne::setCampaignAction(KAMPAGNE_DEF_WUNSCHLISTE, $kWunschlistePos, $qty);
                        }

                        $obj           = new stdClass();
                        $obj->kArtikel = $productID;
                        executeHook(HOOK_TOOLS_GLOBAL_CHECKEWARENKORBEINGANG_WUNSCHLISTE, [
                            'kArtikel'         => &$productID,
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
    public static function addToCartCheck($product, $qty, $attributes, int $accuracy = 2): array
    {
        $cart          = Session::Cart();
        $kArtikel      = (int)$product->kArtikel; // relevant für die Berechnung von Artikelsummen im Warenkorb
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
        $xReturn = self::checkCartPartComponent($product, $qty);
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
        executeHook(HOOK_ADD_TO_CART_CHECK, [
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
    public static function checkVariboxAmount(array $amounts): bool
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

    /**
     * @param float    $total
     * @param Currency $currency
     * @return float
     * @since 5.0.0
     */
    public static function roundOptionalCurrency($total, Currency $currency = null)
    {
        $factor = ($currency ?? Session::Currency())->getConversionFactor();

        return self::roundOptional($total * $factor) / $factor;
    }

    /**
     * @param float $total
     * @return float
     * @since 5.0.0
     */
    public static function roundOptional($total)
    {
        $conf = Shop::getSettings([CONF_KAUFABWICKLUNG]);

        if (isset($conf['kaufabwicklung']['bestellabschluss_runden5'])
            && (int)$conf['kaufabwicklung']['bestellabschluss_runden5'] === 1
        ) {
            return round($total * 20) / 20;
        }

        return $total;
    }

    /**
     * @param Artikel $oArtikel
     * @param float   $fAnzahl
     * @return int|null
     * @former pruefeWarenkorbStueckliste()
     * @since 5.0.0
     */
    public static function checkCartPartComponent($oArtikel, $fAnzahl)
    {
        $oStueckliste = ArtikelHelper::isStuecklisteKomponente($oArtikel->kArtikel, true);
        if (!(is_object($oArtikel) && $oArtikel->cLagerBeachten === 'Y'
            && $oArtikel->cLagerKleinerNull !== 'Y'
            && ($oArtikel->kStueckliste > 0 || $oStueckliste))
        ) {
            return null;
        }
        $isComponent = false;
        $components  = null;
        if (isset($oStueckliste->kStueckliste)) {
            $isComponent = true;
        } else {
            $components = self::getPartComponent($oArtikel->kStueckliste, true);
        }
        foreach (Session::Cart()->PositionenArr as $oPosition) {
            if ($oPosition->nPosTyp !== C_WARENKORBPOS_TYP_ARTIKEL) {
                continue;
            }
            // Komponente soll hinzugefügt werden aber die Stückliste ist bereits im Warenkorb
            // => Prüfen ob der Lagebestand nicht unterschritten wird
            if ($isComponent
                && isset($oPosition->Artikel->kStueckliste)
                && $oPosition->Artikel->kStueckliste > 0
                && ($oPosition->nAnzahl * $oStueckliste->fAnzahl + $fAnzahl) > $oArtikel->fLagerbestand
            ) {
                return R_LAGER;
            }
            if (!$isComponent && count($components) > 0) {
                //Test auf Stücklistenkomponenten in der aktuellen Position
                if (!empty($oPosition->Artikel->kStueckliste)) {
                    $oPositionKomponenten_arr = self::getPartComponent($oPosition->Artikel->kStueckliste, true);
                    foreach ($oPositionKomponenten_arr as $oKomponente) {
                        $desiredComponentQuantity = $fAnzahl * $components[$oKomponente->kArtikel]->fAnzahl;
                        $currentComponentStock    = $oPosition->Artikel->fLagerbestand * $oKomponente->fAnzahl;
                        if ($desiredComponentQuantity > $currentComponentStock) {
                            return R_LAGER;
                        }
                    }
                } elseif (isset($components[$oPosition->kArtikel])
                    && (($oPosition->nAnzahl * $components[$oPosition->kArtikel]->fAnzahl) +
                        ($components[$oPosition->kArtikel]->fAnzahl * $fAnzahl)) > $oPosition->Artikel->fLagerbestand
                ) {
                    return R_LAGER;
                }
            }
        }

        return null;
    }

    /**
     * @param int  $kStueckliste
     * @param bool $bAssoc
     * @return array
     * @former gibStuecklistenKomponente()
     * @since 5.0.0
     */
    public static function getPartComponent(int $kStueckliste, bool $bAssoc = false): array
    {
        if ($kStueckliste > 0) {
            $oObj_arr = Shop::Container()->getDB()->selectAll('tstueckliste', 'kStueckliste', $kStueckliste);
            if (count($oObj_arr) > 0) {
                if ($bAssoc) {
                    $oArtikelAssoc_arr = [];
                    foreach ($oObj_arr as $oObj) {
                        $oArtikelAssoc_arr[$oObj->kArtikel] = $oObj;
                    }

                    return $oArtikelAssoc_arr;
                }

                return $oObj_arr;
            }
        }

        return [];
    }

    /**
     * @param object $oWKPosition
     * @param object $Kupon
     * @return mixed
     * @former checkeKuponWKPos()
     * @since 5.0.0
     */
    public static function checkCouponCartPositions($oWKPosition, $Kupon)
    {
        $oWKPosition->nPosTyp = (int)$oWKPosition->nPosTyp;
        if ($oWKPosition->nPosTyp !== C_WARENKORBPOS_TYP_ARTIKEL) {
            return $oWKPosition;
        }
        $Artikel_qry    = " OR FIND_IN_SET('" .
            str_replace('%', '\%', Shop::Container()->getDB()->escape($oWKPosition->Artikel->cArtNr))
            . "', REPLACE(cArtikel, ';', ',')) > 0";
        $Hersteller_qry = " OR FIND_IN_SET('" .
            str_replace('%', '\%', Shop::Container()->getDB()->escape($oWKPosition->Artikel->kHersteller))
            . "', REPLACE(cHersteller, ';', ',')) > 0";
        $Kategorie_qry  = '';
        $Kunden_qry     = '';
        $kKategorie_arr = [];

        if ($oWKPosition->Artikel->kArtikel > 0 && $oWKPosition->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL) {
            $kArtikel = (int)$oWKPosition->Artikel->kArtikel;
            // Kind?
            if (ArtikelHelper::isVariChild($kArtikel)) {
                $kArtikel = ArtikelHelper::getParent($kArtikel);
            }
            $oKategorie_arr = Shop::Container()->getDB()->selectAll('tkategorieartikel', 'kArtikel', $kArtikel);
            foreach ($oKategorie_arr as $oKategorie) {
                $oKategorie->kKategorie = (int)$oKategorie->kKategorie;
                if (!in_array($oKategorie->kKategorie, $kKategorie_arr, true)) {
                    $kKategorie_arr[] = $oKategorie->kKategorie;
                }
            }
        }
        foreach ($kKategorie_arr as $kKategorie) {
            $Kategorie_qry .= " OR FIND_IN_SET('" . $kKategorie . "', REPLACE(cKategorien, ';', ',')) > 0";
        }
        if (Session::Customer()->isLoggedIn()) {
            $Kunden_qry = " OR FIND_IN_SET('" . Session::Customer()->getID() . "', REPLACE(cKunden, ';', ',')) > 0";
        }
        $kupons_mgl = Shop::Container()->getDB()->query(
            "SELECT *
                FROM tkupon
                WHERE cAktiv = 'Y'
                    AND dGueltigAb <= now()
                    AND (dGueltigBis > now() OR dGueltigBis = '0000-00-00 00:00:00')
                    AND fMindestbestellwert <= " . Session::Cart()->gibGesamtsummeWaren(true, false) . "
                    AND (kKundengruppe = -1
                        OR kKundengruppe = 0
                        OR kKundengruppe = " . Session::CustomerGroup()->getID() . ")
                    AND (nVerwendungen = 0
                        OR nVerwendungen > nVerwendungenBisher)
                    AND (cArtikel = '' {$Artikel_qry})
                    AND (cHersteller = '-1' {$Hersteller_qry})
                    AND (cKategorien = '' OR cKategorien = '-1' {$Kategorie_qry})
                    AND (cKunden = '' OR cKunden = '-1' {$Kunden_qry})
                    AND kKupon = " . (int)$Kupon->kKupon,
            \DB\ReturnType::SINGLE_OBJECT
        );
        if (isset($kupons_mgl->kKupon)
            && $kupons_mgl->kKupon > 0
            && $kupons_mgl->cWertTyp === 'prozent'
            && !Session::Cart()->posTypEnthalten(C_WARENKORBPOS_TYP_KUPON)
        ) {
            $oWKPosition->fPreisEinzelNetto -= ($oWKPosition->fPreisEinzelNetto / 100) * $Kupon->fWert;
            $oWKPosition->fPreis            -= ($oWKPosition->fPreis / 100) * $Kupon->fWert;
            $oWKPosition->cHinweis          = $Kupon->cName .
                ' (' . str_replace('.', ',', $Kupon->fWert) .
                '% ' . Shop::Lang()->get('discount') . ')';

            if (is_array($oWKPosition->WarenkorbPosEigenschaftArr)) {
                foreach ($oWKPosition->WarenkorbPosEigenschaftArr as $attribute) {
                    if (isset($attribute->fAufpreis) && (float)$attribute->fAufpreis > 0) {
                        $attribute->fAufpreis -= ((float)$attribute->fAufpreis / 100) * $Kupon->fWert;
                    }
                }
            }
            foreach (Session::Currencies() as $currency) {
                $currencyName                                         = $currency->getName();
                $oWKPosition->cGesamtpreisLocalized[0][$currencyName] = Preise::getLocalizedPriceString(
                    TaxHelper::getGross($oWKPosition->fPreis * $oWKPosition->nAnzahl,
                        TaxHelper::getSalesTax($oWKPosition->kSteuerklasse)),
                    $currency
                );
                $oWKPosition->cGesamtpreisLocalized[1][$currencyName] = Preise::getLocalizedPriceString(
                    $oWKPosition->fPreis * $oWKPosition->nAnzahl,
                    $currency
                );
                $oWKPosition->cEinzelpreisLocalized[0][$currencyName] = Preise::getLocalizedPriceString(
                    TaxHelper::getGross($oWKPosition->fPreis, TaxHelper::getSalesTax($oWKPosition->kSteuerklasse)),
                    $currency
                );
                $oWKPosition->cEinzelpreisLocalized[1][$currencyName] = Preise::getLocalizedPriceString(
                    $oWKPosition->fPreis,
                    $currency
                );
            }
        }

        return $oWKPosition;
    }

    /**
     * @param object $oWKPosition
     * @param object $Kupon
     * @return mixed
     * @former checkSetPercentCouponWKPos()
     * @since 5.0.0
     */
    public static function checkSetPercentCouponWKPos($oWKPosition, $Kupon)
    {
        $wkPos                = new stdClass();
        $wkPos->fPreis        = (float)0;
        $wkPos->cName         = '';
        $oWKPosition->nPosTyp = (int)$oWKPosition->nPosTyp;
        if ($oWKPosition->nPosTyp !== C_WARENKORBPOS_TYP_ARTIKEL) {
            return $wkPos;
        }
        $Artikel_qry    = " OR FIND_IN_SET('" .
            str_replace('%', '\%', Shop::Container()->getDB()->escape($oWKPosition->Artikel->cArtNr))
            . "', REPLACE(cArtikel, ';', ',')) > 0";
        $Hersteller_qry = " OR FIND_IN_SET('" .
            str_replace('%', '\%', Shop::Container()->getDB()->escape($oWKPosition->Artikel->kHersteller))
            . "', REPLACE(cHersteller, ';', ',')) > 0";
        $Kategorie_qry  = '';
        $Kunden_qry     = '';
        $kKategorie_arr = [];

        if ($oWKPosition->Artikel->kArtikel > 0 && $oWKPosition->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL) {
            $kArtikel = (int)$oWKPosition->Artikel->kArtikel;
            // Kind?
            if (ArtikelHelper::isVariChild($kArtikel)) {
                $kArtikel = ArtikelHelper::getParent($kArtikel);
            }
            $categories = Shop::Container()->getDB()->selectAll('tkategorieartikel', 'kArtikel', $kArtikel,
                'kKategorie');
            foreach ($categories as $category) {
                $category->kKategorie = (int)$category->kKategorie;
                if (!in_array($category->kKategorie, $kKategorie_arr, true)) {
                    $kKategorie_arr[] = $category->kKategorie;
                }
            }
        }
        foreach ($kKategorie_arr as $kKategorie) {
            $Kategorie_qry .= " OR FIND_IN_SET('" . $kKategorie . "', REPLACE(cKategorien, ';', ',')) > 0";
        }
        if (Session::Customer()->isLoggedIn()) {
            $Kunden_qry = " OR FIND_IN_SET('" . Session::Customer()->getID() . "', REPLACE(cKunden, ';', ',')) > 0";
        }
        $kupons_mgl = Shop::Container()->getDB()->query(
            "SELECT *
                FROM tkupon
                WHERE cAktiv = 'Y'
                    AND dGueltigAb <= now()
                    AND (dGueltigBis > now() OR dGueltigBis = '0000-00-00 00:00:00')
                    AND fMindestbestellwert <= " . Session::Cart()->gibGesamtsummeWaren(true, false) . "
                    AND (kKundengruppe = -1
                        OR kKundengruppe = 0
                        OR kKundengruppe = " . Session::CustomerGroup()->getID() . ")
                    AND (nVerwendungen = 0 OR nVerwendungen > nVerwendungenBisher)
                    AND (cArtikel = '' {$Artikel_qry})
                    AND (cHersteller = '-1' {$Hersteller_qry})
                    AND (cKategorien = '' OR cKategorien = '-1' {$Kategorie_qry})
                    AND (cKunden = '' OR cKunden = '-1' {$Kunden_qry})
                    AND kKupon = " . (int)$Kupon->kKupon,
            \DB\ReturnType::SINGLE_OBJECT
        );
        if (isset($kupons_mgl->kKupon) && $kupons_mgl->kKupon > 0 && $kupons_mgl->cWertTyp === 'prozent') {
            $wkPos->fPreis = $oWKPosition->fPreis *
                Session::Currency()->getConversionFactor() *
                $oWKPosition->nAnzahl *
                ((100 + TaxHelper::getSalesTax($oWKPosition->kSteuerklasse)) / 100);
            $wkPos->cName  = $oWKPosition->cName;
        }

        return $wkPos;
    }

    /**
     * @param array $variBoxAnzahl_arr
     * @param int   $kArtikel
     * @param bool  $bIstVater
     * @param bool  $bExtern
     * @former fuegeVariBoxInWK()
     * @since 5.0.0
     */
    public static function addVariboxToCart(
        array $variBoxAnzahl_arr,
        int $kArtikel,
        bool $bIstVater,
        bool $bExtern = false
    ) {
        if (!is_array($variBoxAnzahl_arr) || count($variBoxAnzahl_arr) === 0) {
            return;
        }
        $cKeys_arr     = array_keys($variBoxAnzahl_arr);
        $kVaterArtikel = $kArtikel;
        $attributes    = [];
        unset($_SESSION['variBoxAnzahl_arr']);
        // Es ist min. eine Anzahl vorhanden
        foreach ($cKeys_arr as $cKeys) {
            if ((float)$variBoxAnzahl_arr[$cKeys] <= 0) {
                continue;
            }
            // Switch zwischen 1 Vari und 2
            if ($cKeys[0] === '_') { // 1
                $cVariation0 = substr($cKeys, 1);
                list($kEigenschaft0, $kEigenschaftWert0) = explode(':', $cVariation0);
                // In die Session einbauen
                $oVariKombi                                 = new stdClass();
                $oVariKombi->fAnzahl                        = (float)$variBoxAnzahl_arr[$cKeys];
                $oVariKombi->cVariation0                    = StringHandler::filterXSS($cVariation0);
                $oVariKombi->kEigenschaft0                  = (int)$kEigenschaft0;
                $oVariKombi->kEigenschaftWert0              = (int)$kEigenschaftWert0;
                $_SESSION['variBoxAnzahl_arr'][$cKeys]      = $oVariKombi;
                $_POST['eigenschaftwert_' . $kEigenschaft0] = $kEigenschaftWert0;
            } elseif ($bExtern) {
                $cComb_arr                        = explode('_', $cKeys);
                $oVariKombi                       = new stdClass();
                $oVariKombi->fAnzahl              = (float)$variBoxAnzahl_arr[$cKeys];
                $oVariKombi->kEigenschaft_arr     = [];
                $oVariKombi->kEigenschaftWert_arr = [];
                foreach ($cComb_arr as $cComb) {
                    list($kEigenschaft, $kEigenschaftWert) = explode(':', $cComb);
                    $oVariKombi->kEigenschaft_arr[]            = (int)$kEigenschaft;
                    $oVariKombi->kEigenschaftWert_arr[]        = (int)$kEigenschaftWert;
                    $_POST['eigenschaftwert_' . $kEigenschaft] = (int)$kEigenschaftWert;
                }
                $_SESSION['variBoxAnzahl_arr'][$cKeys] = $oVariKombi;
            } else {
                list($cVariation0, $cVariation1) = explode('_', $cKeys);
                list($kEigenschaft0, $kEigenschaftWert0) = explode(':', $cVariation0);
                list($kEigenschaft1, $kEigenschaftWert1) = explode(':', $cVariation1);
                // In die Session einbauen
                $oVariKombi                                 = new stdClass();
                $oVariKombi->fAnzahl                        = (float)$variBoxAnzahl_arr[$cKeys];
                $oVariKombi->cVariation0                    = StringHandler::filterXSS($cVariation0);
                $oVariKombi->cVariation1                    = StringHandler::filterXSS($cVariation1);
                $oVariKombi->kEigenschaft0                  = (int)$kEigenschaft0;
                $oVariKombi->kEigenschaftWert0              = (int)$kEigenschaftWert0;
                $oVariKombi->kEigenschaft1                  = (int)$kEigenschaft1;
                $oVariKombi->kEigenschaftWert1              = (int)$kEigenschaftWert1;
                $_SESSION['variBoxAnzahl_arr'][$cKeys]      = $oVariKombi;
                $_POST['eigenschaftwert_' . $kEigenschaft0] = $kEigenschaftWert0;
                $_POST['eigenschaftwert_' . $kEigenschaft1] = $kEigenschaftWert1;
            }
            $attributes[$cKeys]                   = new stdClass();
            $attributes[$cKeys]->oEigenschaft_arr = [];
            $attributes[$cKeys]->kArtikel         = 0;

            if ($bIstVater) {
                $kArtikel                             = ArtikelHelper::getArticleForParent($kVaterArtikel);
                $attributes[$cKeys]->oEigenschaft_arr = ArtikelHelper::getSelectedPropertiesForVarCombiArticle($kArtikel);
                $attributes[$cKeys]->kArtikel         = $kArtikel;
            } else {
                $attributes[$cKeys]->oEigenschaft_arr = ArtikelHelper::getSelectedPropertiesForArticle($kArtikel);
                $attributes[$cKeys]->kArtikel         = $kArtikel;
            }
        }
        $nRedirectErr_arr = [];
        if (!is_array($attributes) || count($attributes) === 0) {
            return;
        }
        $defaultOptions = Artikel::getDefaultOptions();
        foreach ($attributes as $i => $oAlleEigenschaftPre) {
            // Prüfe ob er Artikel in den Warenkorb gelegt werden darf
            $nRedirect_arr = self::addToCartCheck(
                (new Artikel())->fuelleArtikel($oAlleEigenschaftPre->kArtikel, $defaultOptions),
                (float)$variBoxAnzahl_arr[$i],
                $oAlleEigenschaftPre->oEigenschaft_arr
            );

            $_SESSION['variBoxAnzahl_arr'][$i]->bError = false;
            if (count($nRedirect_arr) > 0) {
                foreach ($nRedirect_arr as $nRedirect) {
                    $nRedirect = (int)$nRedirect;
                    if (!in_array($nRedirect, $nRedirectErr_arr, true)) {
                        $nRedirectErr_arr[] = $nRedirect;
                    }
                }
                $_SESSION['variBoxAnzahl_arr'][$i]->bError = true;
            }
        }

        if (count($nRedirectErr_arr) > 0) {
            //redirekt zum artikel, um variation/en zu wählen / MBM beachten
            $articleID = $bIstVater
                ? $kVaterArtikel
                : $kArtikel;
            header('Location: ' . Shop::getURL() . '/?a=' . $articleID .
                '&r=' . implode(',', $nRedirectErr_arr), true, 302);
            exit();
        }
        foreach ($attributes as $i => $oAlleEigenschaftPost) {
            if (!$_SESSION['variBoxAnzahl_arr'][$i]->bError) {
                //#8224, #7482 -> do not call setzePositionsPreise() in loop @ Wanrekob::fuegeEin()
                self::addProductIDToCart(
                    $oAlleEigenschaftPost->kArtikel,
                    (float)$variBoxAnzahl_arr[$i],
                    $oAlleEigenschaftPost->oEigenschaft_arr,
                    0,
                    false,
                    0,
                    null,
                    false
                );
            }
        }
        Session::Cart()->setzePositionsPreise();
        unset($_SESSION['variBoxAnzahl_arr']);
        Session::Cart()->redirectTo();
    }

    /**
     * @param int           $kArtikel
     * @param int           $anzahl
     * @param array         $oEigenschaftwerte_arr
     * @param int           $nWeiterleitung
     * @param string        $cUnique
     * @param int           $kKonfigitem
     * @param stdClass|null $oArtikelOptionen
     * @param bool          $setzePositionsPreise
     * @param string        $cResponsibility
     * @return bool
     * @former fuegeEinInWarenkorb()
     * @since 5.0.0
     */
    public static function addProductIDToCart(
        int $kArtikel,
        $anzahl,
        array $oEigenschaftwerte_arr = [],
        $nWeiterleitung = 0,
        $cUnique = '',
        int $kKonfigitem = 0,
        $oArtikelOptionen = null,
        bool $setzePositionsPreise = true,
        string $cResponsibility = 'core'
    ): bool {
        if (!($anzahl > 0 && ($kArtikel > 0 || $kArtikel === 0 && !empty($kKonfigitem) && !empty($cUnique)))) {
            return false;
        }
        $Artikel          = new Artikel();
        $oArtikelOptionen = $oArtikelOptionen ?? Artikel::getDefaultOptions();
        $Artikel->fuelleArtikel($kArtikel, $oArtikelOptionen);
        if ((int)$anzahl != $anzahl && $Artikel->cTeilbar !== 'Y') {
            $anzahl = max((int)$anzahl, 1);
        }
        $redirectParam = self::addToCartCheck($Artikel, $anzahl, $oEigenschaftwerte_arr);
        // verhindert, dass Konfigitems mit Preis=0 aus der Artikelkonfiguration fallen wenn 'Preis auf Anfrage' eingestellt ist
        if (!empty($kKonfigitem) && isset($redirectParam[0]) && $redirectParam[0] === R_AUFANFRAGE) {
            unset($redirectParam[0]);
        }

        if (count($redirectParam) > 0) {
            if (isset($_SESSION['variBoxAnzahl_arr'])) {
                return false;
            }
            if ($nWeiterleitung === 0) {
                $con = (strpos($Artikel->cURLFull, '?') === false) ? '?' : '&';
                if ($Artikel->kEigenschaftKombi > 0) {
                    $url = empty($Artikel->cURLFull)
                        ? (Shop::getURL() . '/?a=' . $Artikel->kVaterArtikel .
                            '&a2=' . $Artikel->kArtikel . '&')
                        : ($Artikel->cURLFull . $con);
                    header('Location: ' . $url . 'n=' . $anzahl . '&r=' . implode(',', $redirectParam), true, 302);
                } else {
                    $url = empty($Artikel->cURLFull)
                        ? (Shop::getURL() . '/?a=' . $Artikel->kArtikel . '&')
                        : ($Artikel->cURLFull . $con);
                    header('Location: ' . $url . 'n=' . $anzahl . '&r=' . implode(',', $redirectParam), true, 302);
                }
                exit;
            }

            return false;
        }
        Session::Cart()
               ->fuegeEin($kArtikel, $anzahl, $oEigenschaftwerte_arr, 1, $cUnique, $kKonfigitem, $setzePositionsPreise,
                   $cResponsibility)
               ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
               ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG)
               ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG)
               ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
               ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
               ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR)
               ->loescheSpezialPos(C_WARENKORBPOS_TYP_NEUKUNDENKUPON)
               ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR)
               ->loescheSpezialPos(C_WARENKORBPOS_TYP_TRUSTEDSHOPS);

        Kupon::resetNewCustomerCoupon();
        unset(
            $_SESSION['VersandKupon'],
            $_SESSION['Versandart'],
            $_SESSION['Zahlungsart'],
            $_SESSION['TrustedShops']
        );
        // Wenn Kupon vorhanden und der cWertTyp prozentual ist, dann verwerfen und neuanlegen
        Kupon::reCheck();
        // Persistenter Warenkorb
        if (!isset($_POST['login']) && !isset($_REQUEST['basket2Pers'])) {
            WarenkorbPers::addToCheck($kArtikel, $anzahl, $oEigenschaftwerte_arr, $cUnique, $kKonfigitem);
        }
        // Hinweis
        Shop::Smarty()
            ->assign('hinweis', Shop::Lang()->get('basketAdded', 'messages'))
            ->assign('bWarenkorbHinzugefuegt', true)
            ->assign('bWarenkorbAnzahl', $anzahl);
        // Kampagne
        if (isset($_SESSION['Kampagnenbesucher'])) {
            Kampagne::setCampaignAction(KAMPAGNE_DEF_WARENKORB, $kArtikel, $anzahl);
        }
        // Warenkorb weiterleiten
        Session::Cart()->redirectTo((bool)$nWeiterleitung, $cUnique);

        return true;
    }

    /**
     * @param array $positions
     * @former loescheWarenkorbPositionen()
     * @since 5.0.0
     */
    public static function deleteCartPositions(array $positions)
    {
        $cart        = Session::Cart();
        $cUnique_arr = [];
        foreach ($positions as $nPos) {
            //Kupons bearbeiten
            if (!isset($cart->PositionenArr[$nPos])) {
                return;
            }
            if ($cart->PositionenArr[$nPos]->nPosTyp !== C_WARENKORBPOS_TYP_ARTIKEL
                && $cart->PositionenArr[$nPos]->nPosTyp !== C_WARENKORBPOS_TYP_GRATISGESCHENK
            ) {
                return;
            }
            $cUnique = $cart->PositionenArr[$nPos]->cUnique;
            // Kindartikel?
            if (!empty($cUnique) && $cart->PositionenArr[$nPos]->kKonfigitem > 0) {
                return;
            }
            executeHook(HOOK_WARENKORB_LOESCHE_POSITION, [
                'nPos'     => $nPos,
                'position' => &$cart->PositionenArr[$nPos]
            ]);

            if (class_exists('Upload')) {
                Upload::deleteArtikelUploads($cart->PositionenArr[$nPos]->kArtikel);
            }

            $cUnique_arr[] = $cUnique;

            unset($cart->PositionenArr[$nPos]);
        }
        $cart->PositionenArr = array_merge($cart->PositionenArr);
        foreach ($cUnique_arr as $cUnique) {
            // Kindartikel löschen
            if (empty($cUnique)) {
                continue;
            }
            $positionCount = count($cart->PositionenArr);
            for ($i = 0; $i < $positionCount; $i++) {
                if (isset($cart->PositionenArr[$i]->cUnique)
                    && $cart->PositionenArr[$i]->cUnique == $cUnique
                ) {
                    unset($cart->PositionenArr[$i]);
                    $cart->PositionenArr = array_merge($cart->PositionenArr);
                    $i                   = -1;
                }
            }
        }
        self::deleteAllSpecialPositions();
        if (!$cart->posTypEnthalten(C_WARENKORBPOS_TYP_ARTIKEL)) {
            unset($_SESSION['Kupon']);
            $_SESSION['Warenkorb'] = new Warenkorb();
        }
        require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellvorgang_inc.php';
        freeGiftStillValid();
        // Lösche Position aus dem WarenkorbPersPos
        if (isset($_SESSION['Kunde']) && $_SESSION['Kunde']->kKunde > 0) {
            (new WarenkorbPers($_SESSION['Kunde']->kKunde))->entferneAlles()->bauePersVonSession();
        }
    }

    /**
     * @param int $nPos
     * @former loescheWarenkorbPosition()
     * @since 5.0.0
     */
    public static function deleteCartPosition(int $nPos)
    {
        self::deleteCartPositions([$nPos]);
    }

    /**
     * @former uebernehmeWarenkorbAenderungen()
     * @since 5.0.0
     */
    public static function applyCartChanges()
    {
        /** @var array('Warenkorb' => Warenkorb) $_SESSION */
        unset($_SESSION['cPlausi_arr'], $_SESSION['cPost_arr']);
        // Gratis Geschenk wurde hinzugefuegt
        if (isset($_POST['gratishinzufuegen'])) {
            return;
        }
        // wurden Positionen gelöscht?
        $drop = null;
        $post = false;
        $cart = Session::Cart();
        if (isset($_POST['dropPos'])) {
            $drop = (int)$_POST['dropPos'];
            $post = true;
        } elseif (isset($_GET['dropPos'])) {
            $drop = (int)$_GET['dropPos'];
        }
        if ($drop !== null) {
            self::deleteCartPosition($drop);
            freeGiftStillValid();
            if ($post) {
                //prg
                header('Location: ' . Shop::Container()->getLinkService()
                                          ->getStaticRoute('warenkorb.php', true, true), true, 303);
            }

            return;
        }
        //wurde WK aktualisiert?
        if (empty($_POST['anzahl'])) {
            return;
        }
        $anzahlPositionen            = count(Session::Cart()->PositionenArr);
        $bMindestensEinePosGeaendert = false;
        //variationen wurden gesetzt oder anzahl der positionen verändert?
        $kArtikelGratisgeschenk = 0;
        foreach ($cart->PositionenArr as $i => $position) {
            if ($position->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL) {
                if ($position->kArtikel == 0) {
                    continue;
                }
                //stückzahlen verändert?
                if (isset($_POST['anzahl'][$i])) {
                    $Artikel = (new Artikel())->fuelleArtikel(
                        $position->kArtikel,
                        Artikel::getDefaultOptions()
                    );

                    $_POST['anzahl'][$i] = str_replace(',', '.', $_POST['anzahl'][$i]);

                    if ((int)$_POST['anzahl'][$i] != $_POST['anzahl'][$i] && $Artikel->cTeilbar !== 'Y') {
                        $_POST['anzahl'][$i] = min((int)$_POST['anzahl'][$i], 1);
                    }
                    $gueltig = true;
                    // Abnahmeintervall
                    if ($Artikel->fAbnahmeintervall > 0) {
                        if (function_exists('bcdiv')) {
                            $dVielfache = round(
                                $Artikel->fAbnahmeintervall * ceil(bcdiv($_POST['anzahl'][$i],
                                    $Artikel->fAbnahmeintervall, 3)),
                                2
                            );
                        } else {
                            $dVielfache = round(
                                $Artikel->fAbnahmeintervall * ceil($_POST['anzahl'][$i] / $Artikel->fAbnahmeintervall),
                                2
                            );
                        }

                        if ($dVielfache != $_POST['anzahl'][$i]) {
                            $gueltig                         = false;
                            $_SESSION['Warenkorbhinweise'][] = Shop::Lang()->get('wkPurchaseintervall', 'messages');
                        }
                    }
                    if ((float)$_POST['anzahl'][$i] + $cart->gibAnzahlEinesArtikels(
                            $position->kArtikel,
                            $i
                        ) < $position->Artikel->fMindestbestellmenge) {
                        //mindestbestellmenge nicht erreicht
                        $gueltig                         = false;
                        $_SESSION['Warenkorbhinweise'][] = lang_mindestbestellmenge(
                            $position->Artikel,
                            (float)$_POST['anzahl'][$i]
                        );
                    }
                    //hole akt. lagerbestand vom artikel
                    if ($Artikel->cLagerBeachten === 'Y' && $Artikel->cLagerVariation !== 'Y'
                        && $Artikel->cLagerKleinerNull !== 'Y'
                        && $Artikel->fPackeinheit * ((float)$_POST['anzahl'][$i] + $cart->gibAnzahlEinesArtikels(
                                $position->kArtikel,
                                $i
                            )) > $Artikel->fLagerbestand
                    ) {
                        $gueltig                         = false;
                        $_SESSION['Warenkorbhinweise'][] = Shop::Lang()->get('quantityNotAvailable', 'messages');
                    }
                    // maximale Bestellmenge des Artikels beachten
                    if (isset($Artikel->FunktionsAttribute[FKT_ATTRIBUT_MAXBESTELLMENGE])
                        && $Artikel->FunktionsAttribute[FKT_ATTRIBUT_MAXBESTELLMENGE] > 0
                        && $_POST['anzahl'][$i] > $Artikel->FunktionsAttribute[FKT_ATTRIBUT_MAXBESTELLMENGE]
                    ) {
                        $gueltig                         = false;
                        $_SESSION['Warenkorbhinweise'][] = Shop::Lang()->get('wkMaxorderlimit', 'messages');
                    }
                    //schaue, ob genug auf Lager von jeder var
                    if ($Artikel->cLagerBeachten === 'Y'
                        && $Artikel->cLagerVariation === 'Y'
                        && $Artikel->cLagerKleinerNull !== 'Y'
                        && is_array($position->WarenkorbPosEigenschaftArr)
                    ) {
                        foreach ($position->WarenkorbPosEigenschaftArr as $eWert) {
                            $EigenschaftWert = new EigenschaftWert($eWert->kEigenschaftWert);
                            if ($EigenschaftWert->fPackeinheit * ((float)$_POST['anzahl'][$i] + $cart->gibAnzahlEinerVariation(
                                        $position->kArtikel,
                                        $eWert->kEigenschaftWert,
                                        $i
                                    )) > $EigenschaftWert->fLagerbestand) {
                                $_SESSION['Warenkorbhinweise'][] = Shop::Lang()->get('quantityNotAvailableVar',
                                    'messages');
                                $gueltig                         = false;
                                break;
                            }
                        }
                    }
                    // Stücklistenkomponente oder Stückliste und ein Teil ist bereits im Warenkorb?
                    $xReturn = WarenkorbHelper::checkCartPartComponent($Artikel, $_POST['anzahl'][$i]);
                    if ($xReturn !== null) {
                        $gueltig                         = false;
                        $_SESSION['Warenkorbhinweise'][] = Shop::Lang()->get('quantityNotAvailableVar', 'messages');
                    }

                    if ($gueltig) {
                        $position->nAnzahl = (float)$_POST['anzahl'][$i];
                        $position->fPreis  = $Artikel->gibPreis(
                            $position->nAnzahl,
                            $position->WarenkorbPosEigenschaftArr
                        );
                        $position->setzeGesamtpreisLocalized();
                        $position->fGesamtgewicht = $position->gibGesamtgewicht();

                        $bMindestensEinePosGeaendert = true;
                    }
                }
                // Grundpreise bei Staffelpreisen
                if (isset($position->Artikel->fVPEWert)
                    && $position->Artikel->fVPEWert > 0
                ) {
                    $nLast = 0;
                    for ($j = 1; $j <= 5; $j++) {
                        $cStaffel = 'nAnzahl' . $j;
                        if (isset($position->Artikel->Preise->$cStaffel)
                            && $position->Artikel->Preise->$cStaffel > 0
                            && $position->Artikel->Preise->$cStaffel <= $position->nAnzahl
                        ) {
                            $nLast = $j;
                        }
                    }
                    if ($nLast > 0) {
                        $cStaffel = 'fPreis' . $nLast;
                        $position->Artikel->baueVPE($position->Artikel->Preise->$cStaffel);
                    } else {
                        $position->Artikel->baueVPE();
                    }
                }
            } elseif ($position->nPosTyp === C_WARENKORBPOS_TYP_GRATISGESCHENK) {
                $kArtikelGratisgeschenk = $position->kArtikel;
            }
        }
        $kArtikelGratisgeschenk = (int)$kArtikelGratisgeschenk;
        //positionen mit nAnzahl = 0 müssen gelöscht werden
        $cart->loescheNullPositionen();
        if (!$cart->posTypEnthalten(C_WARENKORBPOS_TYP_ARTIKEL)) {
            $_SESSION['Warenkorb'] = new Warenkorb();
            $cart                  = $_SESSION['Warenkorb'];
        }
        if ($bMindestensEinePosGeaendert) {
            $oKuponTmp = null;
            //existiert ein proz. Kupon, der auf die neu eingefügte Pos greift?
            if (isset($_SESSION['Kupon'])
                && $_SESSION['Kupon']->cWertTyp === 'prozent'
                && $_SESSION['Kupon']->nGanzenWKRabattieren == 0
                && $cart->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL],
                    true) >= $_SESSION['Kupon']->fMindestbestellwert
            ) {
                $oKuponTmp = $_SESSION['Kupon'];
            }
            self::deleteAllSpecialPositions();
            if (isset($oKuponTmp->kKupon) && $oKuponTmp->kKupon > 0) {
                $_SESSION['Kupon'] = $oKuponTmp;
                foreach ($cart->PositionenArr as $i => $oWKPosition) {
                    $cart->PositionenArr[$i] = WarenkorbHelper::checkCouponCartPositions($oWKPosition,
                        $_SESSION['Kupon']);
                }
            }
            plausiNeukundenKupon();
        }
        $cart->setzePositionsPreise();
        // Gesamtsumme Warenkorb < Gratisgeschenk && Gratisgeschenk in den Pos?
        if ($kArtikelGratisgeschenk > 0) {
            // Prüfen, ob der Artikel wirklich ein Gratis Geschenk ist
            $oArtikelGeschenk = Shop::Container()->getDB()->query(
                "SELECT kArtikel
                FROM tartikelattribut
                WHERE kArtikel = " . $kArtikelGratisgeschenk . "
                    AND cName = '" . FKT_ATTRIBUT_GRATISGESCHENK . "'
                    AND CAST(cWert AS DECIMAL) <= " .
                $cart->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], true),
                \DB\ReturnType::SINGLE_OBJECT
            );

            if (empty($oArtikelGeschenk->kArtikel)) {
                $cart->loescheSpezialPos(C_WARENKORBPOS_TYP_GRATISGESCHENK);
            }
        }
        // Lösche Position aus dem WarenkorbPersPos
        if (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) {
            $oWarenkorbPers = new WarenkorbPers($_SESSION['Kunde']->kKunde);
            $oWarenkorbPers->entferneAlles()
                           ->bauePersVonSession();
        }
    }

    /**
     * @return string
     * @former checkeSchnellkauf()
     * @since 5.0.0
     */
    public static function checkQuickBuy(): string
    {
        $msg = '';
        if (isset($_POST['schnellkauf']) && (int)$_POST['schnellkauf'] > 0 && !empty($_POST['ean'])) {
            $msg = Shop::Lang()->get('eanNotExist') . ' ' .
                StringHandler::htmlentities(StringHandler::filterXSS($_POST['ean']));
            //gibts artikel mit dieser artnr?
            $product = Shop::Container()->getDB()->select(
                'tartikel',
                'cArtNr',
                StringHandler::htmlentities(StringHandler::filterXSS($_POST['ean']))
            );
            if (empty($product->kArtikel)) {
                $product = Shop::Container()->getDB()->select(
                    'tartikel',
                    'cBarcode',
                    StringHandler::htmlentities(StringHandler::filterXSS($_POST['ean']))
                );
            }
            if (isset($product->kArtikel) && $product->kArtikel > 0) {
                $oArtikel = (new Artikel())->fuelleArtikel($product->kArtikel, Artikel::getDefaultOptions());
                if ($oArtikel !== null && $oArtikel->kArtikel > 0 && self::addProductIDToCart(
                        $product->kArtikel,
                        1,
                        ArtikelHelper::getSelectedPropertiesForArticle($product->kArtikel)
                    )) {
                    $msg = $product->cName . ' ' . Shop::Lang()->get('productAddedToCart');
                }
            }
        }

        return $msg;
    }

    /**
     * @former loescheAlleSpezialPos()
     * @since 5.0.0
     */
    public static function deleteAllSpecialPositions()
    {
        Session::Cart()
               ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
               ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
               ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR)
               ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
               ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG)
               ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR)
               ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG)
               ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERPACKUNG)
               ->loescheSpezialPos(C_WARENKORBPOS_TYP_TRUSTEDSHOPS)
               ->checkIfCouponIsStillValid();
        unset(
            $_SESSION['Versandart'],
            $_SESSION['VersandKupon'],
            $_SESSION['oVersandfreiKupon'],
            $_SESSION['Verpackung'],
            $_SESSION['TrustedShops'],
            $_SESSION['Zahlungsart']
        );
        Kupon::resetNewCustomerCoupon();
        Kupon::reCheck();

        executeHook(HOOK_WARENKORB_LOESCHE_ALLE_SPEZIAL_POS);

        Session::Cart()->setzePositionsPreise();
    }

    /**
     * @return stdClass
     * @former gibXSelling()
     * @since 5.0.0
     */
    public static function getXSelling(): stdClass
    {
        $oXselling     = new stdClass();
        $conf          = Shop::getSettings([CONF_KAUFABWICKLUNG]);
        $cartPositions = Session::Cart()->PositionenArr;
        if ($conf['kaufabwicklung']['warenkorb_xselling_anzeigen'] !== 'Y'
            || !is_array($cartPositions)
            || count($cartPositions) === 0
        ) {
            return $oXselling;
        }
        $productIDs = \Functional\map(
            \Functional\filter($cartPositions, function ($p) {
                return isset($p->Artikel->kArtikel);
            }),
            function ($p) {
                return (int)$p->Artikel->kArtikel;
            });

        if (count($productIDs) > 0) {
            $cArtikel_str   = implode(', ', $productIDs);
            $oXsellkauf_arr = Shop::Container()->getDB()->query(
                "SELECT *
                    FROM txsellkauf
                    WHERE kArtikel IN ({$cArtikel_str})
                        AND kXSellArtikel NOT IN ({$cArtikel_str})
                    GROUP BY kXSellArtikel
                    ORDER BY nAnzahl DESC
                    LIMIT " . (int)$conf['kaufabwicklung']['warenkorb_xselling_anzahl'],
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );

            if (is_array($oXsellkauf_arr) && count($oXsellkauf_arr) > 0) {
                if (!isset($oXselling->Kauf)) {
                    $oXselling->Kauf = new stdClass();
                }
                $oXselling->Kauf->Artikel = [];
                $defaultOptions           = Artikel::getDefaultOptions();
                foreach ($oXsellkauf_arr as $oXsellkauf) {
                    $oArtikel = (new Artikel())->fuelleArtikel($oXsellkauf->kXSellArtikel, $defaultOptions);
                    if ($oArtikel !== null && $oArtikel->kArtikel > 0 && $oArtikel->aufLagerSichtbarkeit()) {
                        $oXselling->Kauf->Artikel[] = $oArtikel;
                    }
                }
            }
        }

        return $oXselling;
    }

    /**
     * @param array $conf
     * @return array
     * @former gibGratisGeschenke()
     * @since 5.0.0
     */
    public static function getFreeGifts(array $conf): array
    {
        $gifts = [];
        if ($conf['sonstiges']['sonstiges_gratisgeschenk_nutzen'] === 'Y') {
            $cSQLSort = ' ORDER BY CAST(tartikelattribut.cWert AS DECIMAL) DESC';
            if ($conf['sonstiges']['sonstiges_gratisgeschenk_sortierung'] === 'N') {
                $cSQLSort = ' ORDER BY tartikel.cName';
            } elseif ($conf['sonstiges']['sonstiges_gratisgeschenk_sortierung'] === 'L') {
                $cSQLSort = ' ORDER BY tartikel.fLagerbestand DESC';
            }

            $giftsTmp = Shop::Container()->getDB()->query(
                "SELECT tartikel.kArtikel, tartikelattribut.cWert
                    FROM tartikel
                    JOIN tartikelattribut 
                        ON tartikelattribut.kArtikel = tartikel.kArtikel
                    WHERE (tartikel.fLagerbestand > 0 || 
                          (tartikel.fLagerbestand <= 0 && 
                          (tartikel.cLagerBeachten = 'N' || tartikel.cLagerKleinerNull = 'Y')))
                        AND tartikelattribut.cName = '" . FKT_ATTRIBUT_GRATISGESCHENK . "'
                        AND CAST(tartikelattribut.cWert AS DECIMAL) <= " .
                Session::Cart()->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], true) .
                $cSQLSort . " LIMIT 20",
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );

            foreach ($giftsTmp as $gift) {
                $oArtikel = (new Artikel())->fuelleArtikel($gift->kArtikel, Artikel::getDefaultOptions());
                if ($oArtikel !== null
                    && ($oArtikel->kEigenschaftKombi > 0
                        || !is_array($oArtikel->Variationen)
                        || count($oArtikel->Variationen) === 0)
                ) {
                    $oArtikel->cBestellwert = Preise::getLocalizedPriceString((float)$gift->cWert);
                    $gifts[]                = $oArtikel;
                }
            }
        }

        return $gifts;
    }

    /**
     * Schaut nach ob eine Bestellmenge > Lagersbestand ist und falls dies erlaubt ist, gibt es einen Hinweis
     *
     * @param array $conf
     * @return string
     * @former pruefeBestellMengeUndLagerbestand()
     * @since 5.0.0
     */
    public static function checkOrderAmountAndStock(array $conf = []): string
    {
        $cart         = Session::Cart();
        $cHinweis     = '';
        $cArtikelName = '';
        $bVorhanden   = false;
        $cISOSprache  = Shop::getLanguageCode();
        if (is_array($cart->PositionenArr) && count($cart->PositionenArr) > 0) {
            foreach ($cart->PositionenArr as $i => $oPosition) {
                if ($oPosition->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL
                    && isset($oPosition->Artikel)
                    && $oPosition->Artikel->cLagerBeachten === 'Y'
                    && $oPosition->Artikel->cLagerKleinerNull === 'Y'
                    && $conf['global']['global_lieferverzoegerung_anzeigen'] === 'Y'
                    && $oPosition->nAnzahl > $oPosition->Artikel->fLagerbestand
                ) {
                    $bVorhanden   = true;
                    $cName        = is_array($oPosition->cName) ? $oPosition->cName[$cISOSprache] : $oPosition->cName;
                    $cArtikelName .= '<li>' . $cName . '</li>';
                }
            }
        }
        $cart->cEstimatedDelivery = $cart->getEstimatedDeliveryTime();

        if ($bVorhanden) {
            $cHinweis = sprintf(Shop::Lang()->get('orderExpandInventory', 'basket'), '<ul>' . $cArtikelName . '</ul>');
        }

        return $cHinweis;
    }

    /**
     * Nachschauen ob beim Konfigartikel alle Pflichtkomponenten vorhanden sind, andernfalls löschen
     * @former validiereWarenkorbKonfig()
     * @since 5.0.0
     */
    public static function validateCartConfig()
    {
        if (class_exists('Konfigurator')) {
            Konfigurator::postcheckBasket($_SESSION['Warenkorb']);
        }
    }
}
