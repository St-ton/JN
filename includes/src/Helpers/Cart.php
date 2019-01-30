<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Helpers;

use Artikel;
use Currency;
use DB\ReturnType;
use EigenschaftWert;
use Kampagne;
use Extensions\Konfigitem;
use Extensions\Konfigurator;
use Kunde;
use Kupon;
use Lieferadresse;
use Preise;
use Rechnungsadresse;
use Session\Frontend;
use Shop;
use stdClass;
use StringHandler;
use Extensions\Upload;
use Vergleichsliste;
use Warenkorb;
use WarenkorbPers;
use WarenkorbPos;
use Wunschliste;
use Alert;

/**
 * Class Cart
 * @package Helpers
 */
class Cart
{
    public const NET = 0;

    public const GROSS = 1;

    /**
     * @param int $decimals
     * @return stdClass
     */
    public function getTotal(int $decimals = 0): stdClass
    {
        $info            = new stdClass();
        $info->type      = Frontend::getCustomerGroup()->isMerchant() ? self::NET : self::GROSS;
        $info->currency  = null;
        $info->article   = [0, 0];
        $info->shipping  = [0, 0];
        $info->discount  = [0, 0];
        $info->surcharge = [0, 0];
        $info->total     = [0, 0];
        $info->items     = [];
        $info->currency  = $this->getCurrency();

        foreach (Frontend::getCart()->PositionenArr as $oPosition) {
            $amountItem = $oPosition->fPreisEinzelNetto;
            if (isset($oPosition->WarenkorbPosEigenschaftArr)
                && \is_array($oPosition->WarenkorbPosEigenschaftArr)
                && (!isset($oPosition->Artikel->kVaterArtikel) || (int)$oPosition->Artikel->kVaterArtikel === 0)
            ) {
                foreach ($oPosition->WarenkorbPosEigenschaftArr as $oWarenkorbPosEigenschaft) {
                    if ($oWarenkorbPosEigenschaft->fAufpreis != 0) {
                        $amountItem += $oWarenkorbPosEigenschaft->fAufpreis;
                    }
                }
            }
            $amount      = $amountItem * $info->currency->getConversionFactor();
            $amountGross = $amount * ((100 + Tax::getSalesTax($oPosition->kSteuerklasse)) / 100);

            switch ($oPosition->nPosTyp) {
                case \C_WARENKORBPOS_TYP_ARTIKEL:
                case \C_WARENKORBPOS_TYP_GRATISGESCHENK:
                    $item = (object)[
                        'name'     => '',
                        'quantity' => 1,
                        'amount'   => []
                    ];

                    if (\is_array($oPosition->cName)) {
                        $langIso    = $_SESSION['cISOSprache'];
                        $item->name = $oPosition->cName[$langIso];
                    } else {
                        $item->name = $oPosition->cName;
                    }

                    $item->name   = \html_entity_decode($item->name);
                    $item->amount = [
                        self::NET   => $amount,
                        self::GROSS => $amountGross
                    ];

                    if ((int)$oPosition->nAnzahl != $oPosition->nAnzahl) {
                        $item->amount[self::NET]   *= $oPosition->nAnzahl;
                        $item->amount[self::GROSS] *= $oPosition->nAnzahl;

                        $item->name = \sprintf(
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

                case \C_WARENKORBPOS_TYP_VERSANDPOS:
                case \C_WARENKORBPOS_TYP_VERSANDZUSCHLAG:
                case \C_WARENKORBPOS_TYP_VERPACKUNG:
                case \C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG:
                    $info->shipping[self::NET]   += $amount * $oPosition->nAnzahl;
                    $info->shipping[self::GROSS] += $amountGross * $oPosition->nAnzahl;
                    break;

                case \C_WARENKORBPOS_TYP_KUPON:
                case \C_WARENKORBPOS_TYP_GUTSCHEIN:
                case \C_WARENKORBPOS_TYP_NEUKUNDENKUPON:
                    $info->discount[self::NET]   += $amount * $oPosition->nAnzahl;
                    $info->discount[self::GROSS] += $amountGross * $oPosition->nAnzahl;
                    break;

                case \C_WARENKORBPOS_TYP_ZAHLUNGSART:
                    if ($amount >= 0) {
                        $info->surcharge[self::NET]   += $amount * $oPosition->nAnzahl;
                        $info->surcharge[self::GROSS] += $amountGross * $oPosition->nAnzahl;
                    } else {
                        $amount                      *= -1;
                        $info->discount[self::NET]   += $amount * $oPosition->nAnzahl;
                        $info->discount[self::GROSS] += $amountGross * $oPosition->nAnzahl;
                    }
                    break;

                case \C_WARENKORBPOS_TYP_TRUSTEDSHOPS:
                case \C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR:
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
                self::NET   => \number_format($prop[self::NET], $decimals, '.', ''),
                self::GROSS => \number_format($prop[self::GROSS], $decimals, '.', ''),
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
     * @return Lieferadresse|Rechnungsadresse
     */
    public function getShippingAddress()
    {
        return $_SESSION['Lieferadresse'];
    }

    /**
     * @return Rechnungsadresse
     */
    public function getBillingAddress(): ?Rechnungsadresse
    {
        return $_SESSION['Rechnungsadresse'];
    }

    /**
     * @return Kunde
     */
    public function getCustomer(): ?Kunde
    {
        return $_SESSION['Kunde'];
    }

    /**
     * @return Currency
     */
    public function getCurrency(): Currency
    {
        return Frontend::getCurrency();
    }

    /**
     * @return string
     */
    public function getCurrencyISO(): string
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
    public function getIdentifier(): int
    {
        return 0;
    }

    /**
     * @param WarenkorbPos $wkPos
     * @param object       $variation
     * @return void
     */
    public static function setVariationPicture(WarenkorbPos $wkPos, $variation): void
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
            'nNr'          => \count($wkPos->variationPicturesArr) + 1,
            'cAltAttribut' => \str_replace(['"', "'"], '', $wkPos->Artikel->cName . ' - ' . $variation->cName),
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
            if (isset($wkPos->variationPicturesArr) && \count($wkPos->variationPicturesArr) > 0) {
                Product::addVariationPictures($wkPos->Artikel, $wkPos->variationPicturesArr);
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
        $fAnzahl = 0;
        if (isset($_POST['anzahl'])) {
            $_POST['anzahl'] = \str_replace(',', '.', $_POST['anzahl']);
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
        $kArtikel = isset($_POST['a']) ? (int)$_POST['a'] : Request::verifyGPCDataInt('a');
        $conf     = Shop::getSettings([\CONF_GLOBAL, \CONF_VERGLEICHSLISTE]);
        \executeHook(\HOOK_TOOLS_GLOBAL_CHECKEWARENKORBEINGANG_ANFANG, [
            'kArtikel' => $kArtikel,
            'fAnzahl'  => $fAnzahl
        ]);
        if ($kArtikel > 0
            && (isset($_POST['Wunschliste']) || isset($_GET['Wunschliste']))
            && $conf['global']['global_wunschliste_anzeigen'] === 'Y'
        ) {
            return self::checkWishlist(
                $kArtikel,
                $fAnzahl,
                $conf['global']['global_wunschliste_weiterleitung'] === 'Y'
            );
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
                    Product::isParent($articleID),
                    isset($_POST['varimatrix'])
                );
            } else {
                \header('Location: ' . Shop::getURL() . '/?a=' . $articleID . '&r=' . \R_EMPTY_VARIBOX, true, 303);
                exit;
            }

            return true;
        }
        if (Product::isParent($articleID)) { // Varikombi
            $articleID  = Product::getArticleForParent($articleID);
            $attributes = Product::getSelectedPropertiesForVarCombiArticle($articleID);
        } else {
            $attributes = Product::getSelectedPropertiesForArticle($articleID);
        }
        $isConfigArticle = false;
        if (Konfigurator::checkLicense()) {
            if (!Konfigurator::validateKonfig($articleID)) {
                $isConfigArticle = false;
            } else {
                $groups          = Konfigurator::getKonfig($articleID);
                $isConfigArticle = \is_array($groups) && \count($groups) > 0;
            }
        }

        if (!$isConfigArticle) {
            return self::addProductIDToCart($articleID, $count, $attributes);
        }
        $valid             = true;
        $errors            = [];
        $itemErrors        = [];
        $configItems       = [];
        $configGroups      = (isset($_POST['item']) && \is_array($_POST['item']))
            ? $_POST['item']
            : [];
        $configGroupCounts = (isset($_POST['quantity']) && \is_array($_POST['quantity']))
            ? $_POST['quantity']
            : [];
        $configItemCounts  = (isset($_POST['item_quantity']) && \is_array($_POST['item_quantity']))
            ? $_POST['item_quantity']
            : false;
        $ignoreLimits      = isset($_POST['konfig_ignore_limits']);
        // Beim Bearbeiten die alten Positionen löschen
        if (isset($_POST['kEditKonfig'])) {
            $kEditKonfig = (int)$_POST['kEditKonfig'];
            self::deleteCartPosition($kEditKonfig);
        }

        foreach ($configGroups as $nKonfigitem_arr) {
            foreach ($nKonfigitem_arr as $kKonfigitem) {
                $kKonfigitem = (int)$kKonfigitem;
                // Falls ungültig, ignorieren
                if ($kKonfigitem <= 0) {
                    continue;
                }
                $oKonfigitem          = new Konfigitem($kKonfigitem);
                $oKonfigitem->fAnzahl = (float)($configGroupCounts[$oKonfigitem->getKonfiggruppe()]
                    ?? $oKonfigitem->getInitial());
                if ($configItemCounts && isset($configItemCounts[$oKonfigitem->getKonfigitem()])) {
                    $oKonfigitem->fAnzahl = (float)$configItemCounts[$oKonfigitem->getKonfigitem()];
                }
                // Todo: Mindestbestellanzahl / Abnahmeinterval beachten
                if ($oKonfigitem->fAnzahl < 1) {
                    $oKonfigitem->fAnzahl = 1;
                }
                $count                  = \max($count, 1);
                $oKonfigitem->fAnzahlWK = $oKonfigitem->fAnzahl;
                if (!$oKonfigitem->ignoreMultiplier()) {
                    $oKonfigitem->fAnzahlWK *= $count;
                }
                $configItems[] = $oKonfigitem;
                // Alle Artikel können in den WK gelegt werden?
                if ($oKonfigitem->getPosTyp() === \KONFIG_ITEM_TYP_ARTIKEL) {
                    // Varikombi
                    /** @var Artikel $oTmpArtikel */
                    $oKonfigitem->oEigenschaftwerte_arr = [];
                    $oTmpArtikel                        = $oKonfigitem->getArtikel();

                    if ($oTmpArtikel !== null
                        && $oTmpArtikel->kVaterArtikel > 0
                        && isset($oTmpArtikel->kEigenschaftKombi)
                        && $oTmpArtikel->kEigenschaftKombi > 0
                    ) {
                        $oKonfigitem->oEigenschaftwerte_arr =
                            Product::getVarCombiAttributeValues($oTmpArtikel->kArtikel, false);
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
                    if (\count($redirectParam) > 0) {
                        $valid           = false;
                        $productMessages = Product::getProductMessages(
                            $redirectParam,
                            true,
                            $oKonfigitem->getArtikel(),
                            $oKonfigitem->fAnzahlWK,
                            $oKonfigitem->getKonfigitem()
                        );

                        $itemErrors[$oKonfigitem->getKonfigitem()] = $productMessages[0];
                    }
                }
            }
        }
        // Komplette Konfiguration validieren
        if (!$ignoreLimits
            && (($errors = Konfigurator::validateBasket($articleID, $configItems)) !== true)
        ) {
            $valid = false;
        }
        // Alle Konfigurationsartikel können in den WK gelegt werden
        if ($valid) {
            // Eindeutige ID
            $cUnique = \uniqid('', true);
            // Hauptartikel in den WK legen
            self::addProductIDToCart($articleID, $count, $attributes, 0, $cUnique);
            // Konfigartikel in den WK legen
            foreach ($configItems as $oKonfigitem) {
                $oKonfigitem->isKonfigItem = true;
                switch ($oKonfigitem->getPosTyp()) {
                    case \KONFIG_ITEM_TYP_ARTIKEL:
                        Frontend::getCart()->fuegeEin(
                            $oKonfigitem->getArtikelKey(),
                            $oKonfigitem->fAnzahlWK,
                            $oKonfigitem->oEigenschaftwerte_arr,
                            \C_WARENKORBPOS_TYP_ARTIKEL,
                            $cUnique,
                            $oKonfigitem->getKonfigitem(),
                            false
                        );
                        break;

                    case \KONFIG_ITEM_TYP_SPEZIAL:
                        Frontend::getCart()->erstelleSpezialPos(
                            $oKonfigitem->getName(),
                            $oKonfigitem->fAnzahlWK,
                            $oKonfigitem->getPreis(),
                            $oKonfigitem->getSteuerklasse(),
                            \C_WARENKORBPOS_TYP_ARTIKEL,
                            false,
                            !Frontend::getCustomerGroup()->isMerchant(),
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
            Frontend::getCart()->redirectTo();
        } else {
            Shop::Container()->getAlertService()->addAlert(
                Alert::TYPE_ERROR,
                Shop::Lang()->get('configError', 'productDetails'),
                'configError'
            );
            Shop::Smarty()->assign('aKonfigerror_arr', $errors)
                ->assign('aKonfigitemerror_arr', $itemErrors);

        }

        $nKonfigitem_arr = [];
        foreach ($configGroups as $nTmpKonfigitem_arr) {
            $nKonfigitem_arr = \array_merge($nKonfigitem_arr, $nTmpKonfigitem_arr);
        }
        Shop::Smarty()->assign('fAnzahl', $count)
            ->assign('nKonfigitem_arr', $nKonfigitem_arr)
            ->assign('nKonfigitemAnzahl_arr', $configItemCounts)
            ->assign('nKonfiggruppeAnzahl_arr', $configGroupCounts);

        return $valid;
    }

    /**
     * @param int $kArtikel
     * @param int $maxItems
     * @return bool
     */
    private static function checkCompareList(int $kArtikel, int $maxItems): bool
    {
        $alertHelper = Shop::Container()->getAlertService();
        // Prüfen ob nicht schon die maximale Anzahl an Artikeln auf der Vergleichsliste ist
        if (isset($_SESSION['Vergleichsliste']->oArtikel_arr)
            && $maxItems <= \count($_SESSION['Vergleichsliste']->oArtikel_arr)
        ) {
            Shop::Container()->getAlertService()->addAlert(
                Alert::TYPE_ERROR,
                Shop::Lang()->get('compareMaxlimit', 'errorMessages'),
                'compareMaxlimit'
            );

            return false;
        }
        // Prüfe auf kArtikel
        $productExists = Shop::Container()->getDB()->select(
            'tartikel',
            'kArtikel',
            $kArtikel,
            null,
            null,
            null,
            null,
            false,
            'kArtikel, cName'
        );
        // Falls Artikel vorhanden
        if ($productExists !== null && $productExists->kArtikel > 0) {
            // Sichtbarkeit Prüfen
            $vis = Shop::Container()->getDB()->select(
                'tartikelsichtbarkeit',
                'kArtikel',
                $kArtikel,
                'kKundengruppe',
                Frontend::getCustomerGroup()->getID(),
                null,
                null,
                false,
                'kArtikel'
            );
            if ($vis === null || !isset($vis->kArtikel) || !$vis->kArtikel) {
                // Prüfe auf Vater Artikel
                $variations = [];
                if (Product::isParent($kArtikel)) {
                    $kArtikel   = Product::getArticleForParent($kArtikel);
                    $variations = Product::getSelectedPropertiesForVarCombiArticle($kArtikel, 1);
                }
                $compareList = new Vergleichsliste($kArtikel, $variations);
                // Falls es eine Vergleichsliste in der Session gibt
                if (isset($_SESSION['Vergleichsliste'])) {
                    // Falls Artikel vorhanden sind
                    if (\is_array($_SESSION['Vergleichsliste']->oArtikel_arr)
                        && \count($_SESSION['Vergleichsliste']->oArtikel_arr) > 0
                    ) {
                        $exists = false;
                        foreach ($_SESSION['Vergleichsliste']->oArtikel_arr as $oArtikel) {
                            if ($oArtikel->kArtikel === $compareList->oArtikel_arr[0]->kArtikel) {
                                $exists = true;
                                break;
                            }
                        }
                        // Wenn der Artikel der eingetragen werden soll, nicht schon in der Session ist
                        if (!$exists) {
                            foreach ($_SESSION['Vergleichsliste']->oArtikel_arr as $oArtikel) {
                                $compareList->oArtikel_arr[] = $oArtikel;
                            }
                            $_SESSION['Vergleichsliste'] = $compareList;
                            $alertHelper->addAlert(
                                Alert::TYPE_NOTE,
                                Shop::Lang()->get('comparelistProductadded', 'messages'),
                                'comparelistProductadded'
                            );
                        } else {
                            $alertHelper->addAlert(
                                Alert::TYPE_ERROR,
                                Shop::Lang()->get('comparelistProductexists', 'messages'),
                                'comparelistProductexists'
                            );
                        }
                    }
                } else {
                    // Vergleichsliste neu in der Session anlegen
                    $_SESSION['Vergleichsliste'] = $compareList;
                    $alertHelper->addAlert(
                        Alert::TYPE_NOTE,
                        Shop::Lang()->get('comparelistProductadded', 'messages'),
                        'comparelistProductadded'
                    );

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
    private static function checkWishlist(int $productID, $qty, $redirect): bool
    {
        $linkHelper = Shop::Container()->getLinkService();
        if (!isset($_POST['login']) && Frontend::getCustomer()->getID() < 1) {
            if ($qty <= 0) {
                $qty = 1;
            }
            \header('Location: ' . $linkHelper->getStaticRoute('jtl.php') .
                '?a=' . $productID .
                '&n=' . $qty .
                '&r=' . \R_LOGIN_WUNSCHLISTE, true, 302);
            exit;
        }

        if ($productID > 0 && Frontend::getCustomer()->getID() > 0) {
            $productExists = Shop::Container()->getDB()->select(
                'tartikel',
                'kArtikel',
                $productID,
                null,
                null,
                null,
                null,
                false,
                'kArtikel, cName'
            );
            if ($productExists !== null && $productExists->kArtikel > 0) {
                $vis = Shop::Container()->getDB()->select(
                    'tartikelsichtbarkeit',
                    'kArtikel',
                    $productID,
                    'kKundengruppe',
                    Frontend::getCustomerGroup()->getID(),
                    null,
                    null,
                    false,
                    'kArtikel'
                );
                if ($vis === null || !$vis->kArtikel) {
                    if (Product::isParent($productID)) {
                        // Falls die Wunschliste aus der Artikelübersicht ausgewählt wurde,
                        // muss zum Artikel weitergeleitet werden um Variationen zu wählen
                        if (Request::verifyGPCDataInt('overview') === 1) {
                            \header('Location: ' . Shop::getURL() . '/?a=' . $productID .
                                '&n=' . $qty .
                                '&r=' . \R_VARWAEHLEN, true, 303);
                            exit;
                        }

                        $productID  = Product::getArticleForParent($productID);
                        $attributes = $productID > 0
                            ? Product::getSelectedPropertiesForVarCombiArticle($productID)
                            : [];
                    } else {
                        $attributes = Product::getSelectedPropertiesForArticle($productID);
                    }
                    if ($productID > 0) {
                        if (empty($_SESSION['Wunschliste']->kWunschliste)) {
                            $_SESSION['Wunschliste'] = new Wunschliste();
                            $_SESSION['Wunschliste']->schreibeDB();
                        }
                        $qty             = \max(1, $qty);
                        $kWunschlistePos = $_SESSION['Wunschliste']->fuegeEin(
                            $productID,
                            $productExists->cName,
                            $attributes,
                            $qty
                        );
                        if (isset($_SESSION['Kampagnenbesucher'])) {
                            Kampagne::setCampaignAction(\KAMPAGNE_DEF_WUNSCHLISTE, $kWunschlistePos, $qty);
                        }

                        $obj           = new stdClass();
                        $obj->kArtikel = $productID;
                        \executeHook(\HOOK_TOOLS_GLOBAL_CHECKEWARENKORBEINGANG_WUNSCHLISTE, [
                            'kArtikel'         => &$productID,
                            'fAnzahl'          => &$qty,
                            'AktuellerArtikel' => &$obj
                        ]);

                        Shop::Container()->getAlertService()->addAlert(
                            Alert::TYPE_NOTE,
                            Shop::Lang()->get('wishlistProductadded', 'messages'),
                            'wishlistProductadded'
                        );
                        if ($redirect === true) {
                            \header('Location: ' . $linkHelper->getStaticRoute('wunschliste.php'), true, 302);
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
        $cart          = Frontend::getCart();
        $kArtikel      = (int)$product->kArtikel; // relevant für die Berechnung von Artikelsummen im Warenkorb
        $redirectParam = [];
        $conf          = Shop::getSettings([\CONF_GLOBAL]);
        if ($product->fAbnahmeintervall > 0) {
            $dVielfache = \function_exists('bcdiv')
                ? \round(
                    $product->fAbnahmeintervall
                    * \ceil(\bcdiv((string)$qty, $product->fAbnahmeintervall, $accuracy + 1)),
                    2
                )
                : \round($product->fAbnahmeintervall * \ceil($qty / $product->fAbnahmeintervall), $accuracy);
            if ($dVielfache != $qty) {
                $redirectParam[] = \R_ARTIKELABNAHMEINTERVALL;
            }
        }
        if ((int)$qty != $qty && $product->cTeilbar !== 'Y') {
            $qty = \max((int)$qty, 1);
        }
        if ($product->fMindestbestellmenge > $qty + $cart->gibAnzahlEinesArtikels($kArtikel)) {
            $redirectParam[] = \R_MINDESTMENGE;
        }
        if ($product->cLagerBeachten === 'Y'
            && $product->cLagerVariation !== 'Y'
            && $product->cLagerKleinerNull !== 'Y'
        ) {
            foreach ($product->getAllDependentProducts(true) as $dependent) {
                /** @var Artikel $product */
                $depProduct = $dependent->product;
                if ($depProduct->fPackeinheit
                    * ($qty * $dependent->stockFactor +
                        Frontend::getCart()->getDependentAmount(
                            $depProduct->kArtikel,
                            true
                        )
                    ) > $depProduct->fLagerbestand
                ) {
                    $redirectParam[] = \R_LAGER;
                    break;
                }
            }
        }
        if (!Frontend::getCustomerGroup()->mayViewPrices() || !Frontend::getCustomerGroup()->mayViewCategories()) {
            $redirectParam[] = \R_LOGIN;
        }
        // kein vorbestellbares Produkt, aber mit Erscheinungsdatum in Zukunft
        if ($product->nErscheinendesProdukt && $conf['global']['global_erscheinende_kaeuflich'] === 'N') {
            $redirectParam[] = \R_VORBESTELLUNG;
        }
        // Die maximale Bestellmenge des Artikels wurde überschritten
        if (isset($product->FunktionsAttribute[\FKT_ATTRIBUT_MAXBESTELLMENGE])
            && $product->FunktionsAttribute[\FKT_ATTRIBUT_MAXBESTELLMENGE] > 0
            && ($qty > $product->FunktionsAttribute[\FKT_ATTRIBUT_MAXBESTELLMENGE]
                || ($cart->gibAnzahlEinesArtikels($kArtikel) + $qty) >
                $product->FunktionsAttribute[\FKT_ATTRIBUT_MAXBESTELLMENGE])
        ) {
            $redirectParam[] = \R_MAXBESTELLMENGE;
        }
        // Der Artikel ist unverkäuflich
        if (isset($product->FunktionsAttribute[\FKT_ATTRIBUT_UNVERKAEUFLICH])
            && $product->FunktionsAttribute[\FKT_ATTRIBUT_UNVERKAEUFLICH] == 1
        ) {
            $redirectParam[] = \R_UNVERKAEUFLICH;
        }
        // Preis auf Anfrage
        // verhindert, dass Konfigitems mit Preis=0 aus der Artikelkonfiguration fallen
        // wenn 'Preis auf Anfrage' eingestellt ist
        if ($product->bHasKonfig === false
            && !empty($product->isKonfigItem)
            && $product->inWarenkorbLegbar === \INWKNICHTLEGBAR_PREISAUFANFRAGE
        ) {
            $product->inWarenkorbLegbar = 1;
        }
        if (($product->bHasKonfig === false && empty($product->isKonfigItem))
            && (!isset($product->Preise->fVKNetto) || $product->Preise->fVKNetto == 0)
            && $conf['global']['global_preis0'] === 'N'
        ) {
            $redirectParam[] = \R_AUFANFRAGE;
        }
        // fehlen zu einer Variation werte?
        foreach ($product->Variationen as $var) {
            if (\count($redirectParam) > 0) {
                break;
            }
            if ($var->cTyp === 'FREIFELD') {
                continue;
            }
            $bEigenschaftWertDa = false;
            foreach ($attributes as $oEigenschaftwerte) {
                $oEigenschaftwerte->kEigenschaft = (int)$oEigenschaftwerte->kEigenschaft;
                if ($var->cTyp === 'PFLICHT-FREIFELD' && $oEigenschaftwerte->kEigenschaft === $var->kEigenschaft) {
                    if (\mb_strlen($oEigenschaftwerte->cFreifeldWert) > 0) {
                        $bEigenschaftWertDa = true;
                    } else {
                        $redirectParam[] = \R_VARWAEHLEN;
                        break;
                    }
                } elseif ($var->cTyp !== 'PFLICHT-FREIFELD'
                    && $oEigenschaftwerte->kEigenschaft === $var->kEigenschaft
                ) {
                    $bEigenschaftWertDa = true;
                    //schau, ob auch genug davon auf Lager
                    $EigenschaftWert = new EigenschaftWert($oEigenschaftwerte->kEigenschaftWert);
                    //ist der Eigenschaftwert überhaupt gültig?
                    if ($EigenschaftWert->kEigenschaft !== $oEigenschaftwerte->kEigenschaft) {
                        $redirectParam[] = \R_VARWAEHLEN;
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
                            $redirectParam[] = \R_LAGERVAR;
                        }
                    }
                    break;
                }
            }
            if (!$bEigenschaftWertDa) {
                $redirectParam[] = \R_VARWAEHLEN;
                break;
            }
        }
        \executeHook(\HOOK_ADD_TO_CART_CHECK, [
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
        if (\is_array($amounts) && \count($amounts) > 0) {
            foreach (\array_keys($amounts) as $cKeys) {
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
        $factor = ($currency ?? Frontend::getCurrency())->getConversionFactor();

        return self::roundOptional($total * $factor) / $factor;
    }

    /**
     * @param float $total
     * @return float
     * @since 5.0.0
     */
    public static function roundOptional($total)
    {
        $conf = Shop::getSettings([\CONF_KAUFABWICKLUNG]);

        if (isset($conf['kaufabwicklung']['bestellabschluss_runden5'])
            && (int)$conf['kaufabwicklung']['bestellabschluss_runden5'] === 1
        ) {
            return \round($total * 20) / 20;
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
    public static function checkCartPartComponent($oArtikel, $fAnzahl): ?int
    {
        $oStueckliste = Product::isStuecklisteKomponente($oArtikel->kArtikel, true);
        if (!(\is_object($oArtikel) && $oArtikel->cLagerBeachten === 'Y'
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
        foreach (Frontend::getCart()->PositionenArr as $oPosition) {
            if ($oPosition->nPosTyp !== \C_WARENKORBPOS_TYP_ARTIKEL) {
                continue;
            }
            if ($isComponent
                && isset($oPosition->Artikel->kStueckliste)
                && $oPosition->Artikel->kStueckliste > 0
                && ($oPosition->nAnzahl * $oStueckliste->fAnzahl + $fAnzahl) > $oArtikel->fLagerbestand
            ) {
                return \R_LAGER;
            }
            if (!$isComponent && \count($components) > 0) {
                if (!empty($oPosition->Artikel->kStueckliste)) {
                    $oPositionKomponenten_arr = self::getPartComponent($oPosition->Artikel->kStueckliste, true);
                    foreach ($oPositionKomponenten_arr as $oKomponente) {
                        $desiredComponentQuantity = $fAnzahl * $components[$oKomponente->kArtikel]->fAnzahl;
                        $currentComponentStock    = $oPosition->Artikel->fLagerbestand * $oKomponente->fAnzahl;
                        if ($desiredComponentQuantity > $currentComponentStock) {
                            return \R_LAGER;
                        }
                    }
                } elseif (isset($components[$oPosition->kArtikel])
                    && (($oPosition->nAnzahl * $components[$oPosition->kArtikel]->fAnzahl) +
                        ($components[$oPosition->kArtikel]->fAnzahl * $fAnzahl)) > $oPosition->Artikel->fLagerbestand
                ) {
                    return \R_LAGER;
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
            $data = Shop::Container()->getDB()->selectAll('tstueckliste', 'kStueckliste', $kStueckliste);
            if (\count($data) > 0) {
                if ($bAssoc) {
                    $res = [];
                    foreach ($data as $item) {
                        $res[$item->kArtikel] = $item;
                    }

                    return $res;
                }

                return $data;
            }
        }

        return [];
    }

    /**
     * @param object $cartPosition
     * @param object $coupon
     * @return mixed
     * @former checkeKuponWKPos()
     * @since 5.0.0
     */
    public static function checkCouponCartPositions($cartPosition, $coupon)
    {
        $cartPosition->nPosTyp = (int)$cartPosition->nPosTyp;
        if ($cartPosition->nPosTyp !== \C_WARENKORBPOS_TYP_ARTIKEL) {
            return $cartPosition;
        }
        $categoryQRY = '';
        $customerQRY = '';
        $categoryIDs = [];
        if ($cartPosition->Artikel->kArtikel > 0 && $cartPosition->nPosTyp === \C_WARENKORBPOS_TYP_ARTIKEL) {
            $productID = (int)$cartPosition->Artikel->kArtikel;
            if (Product::isVariChild($productID)) {
                $productID = Product::getParent($productID);
            }
            $categories = Shop::Container()->getDB()->selectAll('tkategorieartikel', 'kArtikel', $productID);
            foreach ($categories as $category) {
                $category->kKategorie = (int)$category->kKategorie;
                if (!\in_array($category->kKategorie, $categoryIDs, true)) {
                    $categoryIDs[] = $category->kKategorie;
                }
            }
        }
        foreach ($categoryIDs as $id) {
            $categoryQRY .= " OR FIND_IN_SET('" . $id . "', REPLACE(cKategorien, ';', ',')) > 0";
        }
        if (Frontend::getCustomer()->isLoggedIn()) {
            $customerQRY = " OR FIND_IN_SET('" .
                Frontend::getCustomer()->getID() . "', REPLACE(cKunden, ';', ',')) > 0";
        }
        $couponsOK = Shop::Container()->getDB()->queryPrepared(
            "SELECT *
                FROM tkupon
                WHERE cAktiv = 'Y'
                    AND dGueltigAb <= NOW()
                    AND (dGueltigBis IS NULL OR dGueltigBis > NOW())
                    AND fMindestbestellwert <= :minAmount
                    AND (kKundengruppe = -1
                        OR kKundengruppe = 0
                        OR kKundengruppe = :cgid)
                    AND (nVerwendungen = 0
                        OR nVerwendungen > nVerwendungenBisher)
                    AND (cArtikel = '' OR FIND_IN_SET(:artNO, REPLACE(cArtikel, ';', ',')) > 0)
                    AND (cHersteller = '-1' OR FIND_IN_SET(:manuf, REPLACE(cHersteller, ';', ',')) > 0)
                    AND (cKategorien = '' OR cKategorien = '-1' " . $categoryQRY . ")
                    AND (cKunden = '' OR cKunden = '-1' " . $customerQRY . ')
                    AND kKupon = :couponID',
            [
                'minAmount' => Frontend::getCart()->gibGesamtsummeWaren(true, false),
                'cgID'      => Frontend::getCustomerGroup()->getID(),
                'artNO'     => \str_replace('%', '\%', $cartPosition->Artikel->cArtNr),
                'manuf'     => \str_replace('%', '\%', $cartPosition->Artikel->kHersteller),
                'couponID'  => (int)$coupon->kKupon
            ],
            ReturnType::SINGLE_OBJECT
        );
        if (isset($couponsOK->kKupon)
            && $couponsOK->kKupon > 0
            && $couponsOK->cWertTyp === 'prozent'
            && !Frontend::getCart()->posTypEnthalten(\C_WARENKORBPOS_TYP_KUPON)
        ) {
            $cartPosition->fPreisEinzelNetto -= ($cartPosition->fPreisEinzelNetto / 100) * $coupon->fWert;
            $cartPosition->fPreis            -= ($cartPosition->fPreis / 100) * $coupon->fWert;
            $cartPosition->cHinweis           = $coupon->cName .
                ' (' . \str_replace('.', ',', $coupon->fWert) .
                '% ' . Shop::Lang()->get('discount') . ')';

            if (\is_array($cartPosition->WarenkorbPosEigenschaftArr)) {
                foreach ($cartPosition->WarenkorbPosEigenschaftArr as $attribute) {
                    if (isset($attribute->fAufpreis) && (float)$attribute->fAufpreis > 0) {
                        $attribute->fAufpreis -= ((float)$attribute->fAufpreis / 100) * $coupon->fWert;
                    }
                }
            }
            foreach (Frontend::getCurrencies() as $currency) {
                $currencyName                                          = $currency->getName();
                $cartPosition->cGesamtpreisLocalized[0][$currencyName] = Preise::getLocalizedPriceString(
                    Tax::getGross(
                        $cartPosition->fPreis * $cartPosition->nAnzahl,
                        Tax::getSalesTax($cartPosition->kSteuerklasse)
                    ),
                    $currency
                );
                $cartPosition->cGesamtpreisLocalized[1][$currencyName] = Preise::getLocalizedPriceString(
                    $cartPosition->fPreis * $cartPosition->nAnzahl,
                    $currency
                );
                $cartPosition->cEinzelpreisLocalized[0][$currencyName] = Preise::getLocalizedPriceString(
                    Tax::getGross($cartPosition->fPreis, Tax::getSalesTax($cartPosition->kSteuerklasse)),
                    $currency
                );
                $cartPosition->cEinzelpreisLocalized[1][$currencyName] = Preise::getLocalizedPriceString(
                    $cartPosition->fPreis,
                    $currency
                );
            }
        }

        return $cartPosition;
    }

    /**
     * @param object $cartPosition
     * @param object $coupon
     * @return mixed
     * @former checkSetPercentCouponWKPos()
     * @since 5.0.0
     */
    public static function checkSetPercentCouponWKPos($cartPosition, $coupon)
    {
        $position              = new stdClass();
        $position->fPreis      = (float)0;
        $position->cName       = '';
        $cartPosition->nPosTyp = (int)$cartPosition->nPosTyp;
        if ($cartPosition->nPosTyp !== \C_WARENKORBPOS_TYP_ARTIKEL) {
            return $position;
        }
        $categoryQRY = '';
        $customerQRY = '';
        $categoryIDs = [];
        if ($cartPosition->Artikel->kArtikel > 0 && $cartPosition->nPosTyp === \C_WARENKORBPOS_TYP_ARTIKEL) {
            $productID = (int)$cartPosition->Artikel->kArtikel;
            if (Product::isVariChild($productID)) {
                $productID = Product::getParent($productID);
            }
            $categories = Shop::Container()->getDB()->selectAll(
                'tkategorieartikel',
                'kArtikel',
                $productID,
                'kKategorie'
            );
            foreach ($categories as $category) {
                $category->kKategorie = (int)$category->kKategorie;
                if (!\in_array($category->kKategorie, $categoryIDs, true)) {
                    $categoryIDs[] = $category->kKategorie;
                }
            }
        }
        foreach ($categoryIDs as $id) {
            $categoryQRY .= " OR FIND_IN_SET('" . $id . "', REPLACE(cKategorien, ';', ',')) > 0";
        }
        if (Frontend::getCustomer()->isLoggedIn()) {
            $customerQRY = " OR FIND_IN_SET('" .
                Frontend::getCustomer()->getID() . "', REPLACE(cKunden, ';', ',')) > 0";
        }
        $couponOK = Shop::Container()->getDB()->queryPrepared(
            "SELECT *
                FROM tkupon
                WHERE cAktiv = 'Y'
                    AND dGueltigAb <= NOW()
                    AND (dGueltigBis IS NULL OR dGueltigBis > NOW())
                    AND fMindestbestellwert <= :minAmount
                    AND (kKundengruppe = -1
                        OR kKundengruppe = 0
                        OR kKundengruppe = :cgid)
                    AND (nVerwendungen = 0 OR nVerwendungen > nVerwendungenBisher)
                    AND (cArtikel = '' OR FIND_IN_SET(:artNo, REPLACE(cArtikel, ';', ',')) > 0)
                    AND (cHersteller = '-1' OR FIND_IN_SET(:manuf, REPLACE(cHersteller, ';', ',')) > 0)
                    AND (cKategorien = '' OR cKategorien = '-1' " . $categoryQRY . ")
                    AND (cKunden = '' OR cKunden = '-1' " . $customerQRY . ')
                    AND kKupon = :couponID',
            [
                'minAmount' => Frontend::getCart()->gibGesamtsummeWaren(true, false),
                'cgID'      => Frontend::getCustomerGroup()->getID(),
                'artNo'     => \str_replace('%', '\%', $cartPosition->Artikel->cArtNr),
                'manuf'     => \str_replace('%', '\%', $cartPosition->Artikel->kHersteller),
                'couponID'  => $coupon->kKupon

            ],
            ReturnType::SINGLE_OBJECT
        );
        if (isset($couponOK->kKupon) && $couponOK->kKupon > 0 && $couponOK->cWertTyp === 'prozent') {
            $position->fPreis = $cartPosition->fPreis *
                Frontend::getCurrency()->getConversionFactor() *
                $cartPosition->nAnzahl *
                ((100 + Tax::getSalesTax($cartPosition->kSteuerklasse)) / 100);
            $position->cName  = $cartPosition->cName;
        }

        return $position;
    }

    /**
     * @param array $variBoxCounts
     * @param int   $kArtikel
     * @param bool  $bIstVater
     * @param bool  $bExtern
     * @former fuegeVariBoxInWK()
     * @since 5.0.0
     */
    public static function addVariboxToCart(
        array $variBoxCounts,
        int $kArtikel,
        bool $bIstVater,
        bool $bExtern = false
    ) {
        if (!\is_array($variBoxCounts) || \count($variBoxCounts) === 0) {
            return;
        }
        $kVaterArtikel = $kArtikel;
        $attributes    = [];
        unset($_SESSION['variBoxAnzahl_arr']);
        // Es ist min. eine Anzahl vorhanden
        foreach (\array_keys($variBoxCounts) as $cKeys) {
            if ((float)$variBoxCounts[$cKeys] <= 0) {
                continue;
            }
            // Switch zwischen 1 Vari und 2
            if ($cKeys[0] === '_') { // 1
                $cVariation0                         = \mb_substr($cKeys, 1);
                [$kEigenschaft0, $kEigenschaftWert0] = \explode(':', $cVariation0);
                // In die Session einbauen
                $oVariKombi                                 = new stdClass();
                $oVariKombi->fAnzahl                        = (float)$variBoxCounts[$cKeys];
                $oVariKombi->cVariation0                    = StringHandler::filterXSS($cVariation0);
                $oVariKombi->kEigenschaft0                  = (int)$kEigenschaft0;
                $oVariKombi->kEigenschaftWert0              = (int)$kEigenschaftWert0;
                $_SESSION['variBoxAnzahl_arr'][$cKeys]      = $oVariKombi;
                $_POST['eigenschaftwert_' . $kEigenschaft0] = $kEigenschaftWert0;
            } elseif ($bExtern) {
                $oVariKombi                       = new stdClass();
                $oVariKombi->fAnzahl              = (float)$variBoxCounts[$cKeys];
                $oVariKombi->kEigenschaft_arr     = [];
                $oVariKombi->kEigenschaftWert_arr = [];
                foreach (\explode('_', $cKeys) as $cComb) {
                    [$kEigenschaft, $kEigenschaftWert]         = \explode(':', $cComb);
                    $oVariKombi->kEigenschaft_arr[]            = (int)$kEigenschaft;
                    $oVariKombi->kEigenschaftWert_arr[]        = (int)$kEigenschaftWert;
                    $_POST['eigenschaftwert_' . $kEigenschaft] = (int)$kEigenschaftWert;
                }
                $_SESSION['variBoxAnzahl_arr'][$cKeys] = $oVariKombi;
            } else {
                [$cVariation0, $cVariation1]         = \explode('_', $cKeys);
                [$kEigenschaft0, $kEigenschaftWert0] = \explode(':', $cVariation0);
                [$kEigenschaft1, $kEigenschaftWert1] = \explode(':', $cVariation1);
                // In die Session einbauen
                $oVariKombi                                 = new stdClass();
                $oVariKombi->fAnzahl                        = (float)$variBoxCounts[$cKeys];
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
                $kArtikel                             = Product::getArticleForParent($kVaterArtikel);
                $attributes[$cKeys]->oEigenschaft_arr = Product::getSelectedPropertiesForVarCombiArticle(
                    $kArtikel
                );
                $attributes[$cKeys]->kArtikel         = $kArtikel;
            } else {
                $attributes[$cKeys]->oEigenschaft_arr = Product::getSelectedPropertiesForArticle($kArtikel);
                $attributes[$cKeys]->kArtikel         = $kArtikel;
            }
        }
        $redirectErrors = [];
        if (!\is_array($attributes) || \count($attributes) === 0) {
            return;
        }
        $defaultOptions = Artikel::getDefaultOptions();
        foreach ($attributes as $i => $oAlleEigenschaftPre) {
            // Prüfe ob er Artikel in den Warenkorb gelegt werden darf
            $redirects = self::addToCartCheck(
                (new Artikel())->fuelleArtikel($oAlleEigenschaftPre->kArtikel, $defaultOptions),
                (float)$variBoxCounts[$i],
                $oAlleEigenschaftPre->oEigenschaft_arr
            );

            $_SESSION['variBoxAnzahl_arr'][$i]->bError = false;
            if (\count($redirects) > 0) {
                foreach ($redirects as $nRedirect) {
                    $nRedirect = (int)$nRedirect;
                    if (!\in_array($nRedirect, $redirectErrors, true)) {
                        $redirectErrors[] = $nRedirect;
                    }
                }
                $_SESSION['variBoxAnzahl_arr'][$i]->bError = true;
            }
        }

        if (\count($redirectErrors) > 0) {
            $articleID = $bIstVater
                ? $kVaterArtikel
                : $kArtikel;
            \header('Location: ' . Shop::getURL() . '/?a=' . $articleID .
                '&r=' . \implode(',', $redirectErrors), true, 302);
            exit();
        }
        foreach ($attributes as $i => $oAlleEigenschaftPost) {
            if (!$_SESSION['variBoxAnzahl_arr'][$i]->bError) {
                //#8224, #7482 -> do not call setzePositionsPreise() in loop @ Wanrekob::fuegeEin()
                self::addProductIDToCart(
                    $oAlleEigenschaftPost->kArtikel,
                    (float)$variBoxCounts[$i],
                    $oAlleEigenschaftPost->oEigenschaft_arr,
                    0,
                    false,
                    0,
                    null,
                    false
                );
            }
        }
        Frontend::getCart()->setzePositionsPreise();
        unset($_SESSION['variBoxAnzahl_arr']);
        Frontend::getCart()->redirectTo();
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
            $anzahl = \max((int)$anzahl, 1);
        }
        $redirectParam = self::addToCartCheck($Artikel, $anzahl, $oEigenschaftwerte_arr);
        // verhindert, dass Konfigitems mit Preis=0 aus der Artikelkonfiguration fallen
        // wenn 'Preis auf Anfrage' eingestellt ist
        if (!empty($kKonfigitem) && isset($redirectParam[0]) && $redirectParam[0] === \R_AUFANFRAGE) {
            unset($redirectParam[0]);
        }

        if (\count($redirectParam) > 0) {
            if (isset($_SESSION['variBoxAnzahl_arr'])) {
                return false;
            }
            if ($nWeiterleitung === 0) {
                $con = (\mb_strpos($Artikel->cURLFull, '?') === false) ? '?' : '&';
                if ($Artikel->kEigenschaftKombi > 0) {
                    $url = empty($Artikel->cURLFull)
                        ? (Shop::getURL() . '/?a=' . $Artikel->kVaterArtikel .
                            '&a2=' . $Artikel->kArtikel . '&')
                        : ($Artikel->cURLFull . $con);
                    \header('Location: ' . $url . 'n=' . $anzahl . '&r=' . \implode(',', $redirectParam), true, 302);
                } else {
                    $url = empty($Artikel->cURLFull)
                        ? (Shop::getURL() . '/?a=' . $Artikel->kArtikel . '&')
                        : ($Artikel->cURLFull . $con);
                    \header('Location: ' . $url . 'n=' . $anzahl . '&r=' . \implode(',', $redirectParam), true, 302);
                }
                exit;
            }

            return false;
        }
        Frontend::getCart()
            ->fuegeEin(
                $kArtikel,
                $anzahl,
                $oEigenschaftwerte_arr,
                1,
                $cUnique,
                $kKonfigitem,
                false,
                $cResponsibility
            )
            ->loescheSpezialPos(\C_WARENKORBPOS_TYP_VERSANDPOS)
            ->loescheSpezialPos(\C_WARENKORBPOS_TYP_VERSANDZUSCHLAG)
            ->loescheSpezialPos(\C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG)
            ->loescheSpezialPos(\C_WARENKORBPOS_TYP_ZAHLUNGSART)
            ->loescheSpezialPos(\C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
            ->loescheSpezialPos(\C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR)
            ->loescheSpezialPos(\C_WARENKORBPOS_TYP_NEUKUNDENKUPON)
            ->loescheSpezialPos(\C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR)
            ->loescheSpezialPos(\C_WARENKORBPOS_TYP_TRUSTEDSHOPS);

        Kupon::resetNewCustomerCoupon(false);
        if ($setzePositionsPreise) {
            Frontend::getCart()->setzePositionsPreise();
        }
        unset(
            $_SESSION['VersandKupon'],
            $_SESSION['Versandart'],
            $_SESSION['Zahlungsart'],
            $_SESSION['TrustedShops']
        );
        // Wenn Kupon vorhanden und der cWertTyp prozentual ist, dann verwerfen und neu anlegen
        Kupon::reCheck();
        if (!isset($_POST['login']) && !isset($_REQUEST['basket2Pers'])) {
            WarenkorbPers::addToCheck($kArtikel, $anzahl, $oEigenschaftwerte_arr, $cUnique, $kKonfigitem);
        }
        Shop::Smarty()
            ->assign('cartNote', Shop::Lang()->get('basketAdded', 'messages'))
            ->assign('bWarenkorbHinzugefuegt', true)
            ->assign('bWarenkorbAnzahl', $anzahl);
        if (isset($_SESSION['Kampagnenbesucher'])) {
            Kampagne::setCampaignAction(\KAMPAGNE_DEF_WARENKORB, $kArtikel, $anzahl);
        }
        Frontend::getCart()->redirectTo((bool)$nWeiterleitung, $cUnique);

        return true;
    }

    /**
     * @param array $positions
     * @former loescheWarenkorbPositionen()
     * @since 5.0.0
     */
    public static function deleteCartPositions(array $positions): void
    {
        $cart    = Frontend::getCart();
        $uniques = [];
        foreach ($positions as $nPos) {
            if (!isset($cart->PositionenArr[$nPos])) {
                return;
            }
            if ($cart->PositionenArr[$nPos]->nPosTyp !== \C_WARENKORBPOS_TYP_ARTIKEL
                && $cart->PositionenArr[$nPos]->nPosTyp !== \C_WARENKORBPOS_TYP_GRATISGESCHENK
            ) {
                return;
            }
            $unique = $cart->PositionenArr[$nPos]->cUnique;
            if (!empty($unique) && $cart->PositionenArr[$nPos]->kKonfigitem > 0) {
                return;
            }
            \executeHook(\HOOK_WARENKORB_LOESCHE_POSITION, [
                'nPos'     => $nPos,
                'position' => &$cart->PositionenArr[$nPos]
            ]);

            Upload::deleteArtikelUploads($cart->PositionenArr[$nPos]->kArtikel);

            $uniques[] = $unique;

            unset($cart->PositionenArr[$nPos]);
        }
        $cart->PositionenArr = \array_merge($cart->PositionenArr);
        foreach ($uniques as $unique) {
            if (empty($unique)) {
                continue;
            }
            $positionCount = \count($cart->PositionenArr);
            for ($i = 0; $i < $positionCount; $i++) {
                if (isset($cart->PositionenArr[$i]->cUnique) && $cart->PositionenArr[$i]->cUnique === $unique) {
                    unset($cart->PositionenArr[$i]);
                    $cart->PositionenArr = \array_merge($cart->PositionenArr);
                    $i                   = -1;
                }
            }
        }
        self::deleteAllSpecialPositions();
        if (!$cart->posTypEnthalten(\C_WARENKORBPOS_TYP_ARTIKEL)) {
            unset($_SESSION['Kupon']);
            $_SESSION['Warenkorb'] = new Warenkorb();
        }
        require_once \PFAD_ROOT . \PFAD_INCLUDES . 'bestellvorgang_inc.php';
        \freeGiftStillValid();
        if (isset($_SESSION['Kunde']) && $_SESSION['Kunde']->kKunde > 0) {
            (new WarenkorbPers($_SESSION['Kunde']->kKunde))->entferneAlles()->bauePersVonSession();
        }
    }

    /**
     * @param int $nPos
     * @former loescheWarenkorbPosition()
     * @since 5.0.0
     */
    public static function deleteCartPosition(int $nPos): void
    {
        self::deleteCartPositions([$nPos]);
    }

    /**
     * @former uebernehmeWarenkorbAenderungen()
     * @since 5.0.0
     */
    public static function applyCartChanges(): void
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
        $cart = Frontend::getCart();
        if (isset($_POST['dropPos']) && $_POST['dropPos'] === 'assetToUse') {
            $_SESSION['Bestellung']->GuthabenNutzen   = false;
            $_SESSION['Bestellung']->fGuthabenGenutzt = 0;
            unset($_POST['dropPos']);
        }
        if (isset($_POST['dropPos'])) {
            $drop = (int)$_POST['dropPos'];
            $post = true;
        } elseif (isset($_GET['dropPos'])) {
            $drop = (int)$_GET['dropPos'];
        }
        if ($drop !== null) {
            self::deleteCartPosition($drop);
            \freeGiftStillValid();
            if ($post) {
                \header(
                    'Location: ' . Shop::Container()->getLinkService()->getStaticRoute(
                        'warenkorb.php',
                        true,
                        true
                    ),
                    true,
                    303
                );
                exit();
            }

            return;
        }
        //wurde WK aktualisiert?
        if (empty($_POST['anzahl'])) {
            return;
        }
        $bMindestensEinePosGeaendert = false;
        $kArtikelGratisgeschenk      = 0;
        $cartNotices                 = $_SESSION['Warenkorbhinweise'] ?? [];
        foreach ($cart->PositionenArr as $i => $position) {
            if ($position->nPosTyp === \C_WARENKORBPOS_TYP_ARTIKEL) {
                if ($position->kArtikel == 0) {
                    continue;
                }
                //stückzahlen verändert?
                if (isset($_POST['anzahl'][$i])) {
                    $Artikel = new Artikel();
                    $Artikel->fuelleArtikel(
                        $position->kArtikel,
                        Artikel::getDefaultOptions()
                    );

                    $_POST['anzahl'][$i] = \str_replace(',', '.', $_POST['anzahl'][$i]);

                    if ((int)$_POST['anzahl'][$i] != $_POST['anzahl'][$i] && $Artikel->cTeilbar !== 'Y') {
                        $_POST['anzahl'][$i] = \ceil($_POST['anzahl'][$i]);
                    }
                    $gueltig = true;
                    if ($Artikel->fAbnahmeintervall > 0) {
                        if (\function_exists('bcdiv')) {
                            $dVielfache = \round(
                                $Artikel->fAbnahmeintervall *
                                \ceil(\bcdiv($_POST['anzahl'][$i], $Artikel->fAbnahmeintervall, 3)),
                                2
                            );
                        } else {
                            $dVielfache = \round(
                                $Artikel->fAbnahmeintervall * \ceil($_POST['anzahl'][$i] / $Artikel->fAbnahmeintervall),
                                2
                            );
                        }

                        if ($dVielfache != $_POST['anzahl'][$i]) {
                            $gueltig       = false;
                            $cartNotices[] = Shop::Lang()->get('wkPurchaseintervall', 'messages');
                        }
                    }
                    if ((float)$_POST['anzahl'][$i] + $cart->gibAnzahlEinesArtikels(
                        $position->kArtikel,
                        $i
                    ) < $position->Artikel->fMindestbestellmenge) {
                        $gueltig       = false;
                        $cartNotices[] = \lang_mindestbestellmenge(
                            $position->Artikel,
                            (float)$_POST['anzahl'][$i]
                        );
                    }
                    if ($Artikel->cLagerBeachten === 'Y' && $Artikel->cLagerVariation !== 'Y'
                        && $Artikel->cLagerKleinerNull !== 'Y'
                    ) {
                        foreach ($Artikel->getAllDependentProducts(true) as $dependent) {
                            /** @var Artikel $product */
                            $product = $dependent->product;
                            if ($product->fPackeinheit * ((float)$_POST['anzahl'][$i] * $dependent->stockFactor
                                    + Frontend::getCart()->getDependentAmount(
                                        $product->kArtikel,
                                        true,
                                        [$i]
                                    )) > $product->fLagerbestand
                            ) {
                                $gueltig = false;
                                break;
                            }
                        }

                        if (!$gueltig) {
                            $msg = Shop::Lang()->get('quantityNotAvailable', 'messages');
                            if (!isset($cartNotices) || !in_array($msg, $cartNotices)) {
                                $cartNotices[] = $msg;
                            }
                            $_SESSION['Warenkorb']->PositionenArr[$i]->nAnzahl =
                                $_SESSION['Warenkorb']->getMaxAvailableAmount($i, (float)$_POST['anzahl'][$i]);
                        }
                    }
                    // maximale Bestellmenge des Artikels beachten
                    if (isset($Artikel->FunktionsAttribute[\FKT_ATTRIBUT_MAXBESTELLMENGE])
                        && $Artikel->FunktionsAttribute[\FKT_ATTRIBUT_MAXBESTELLMENGE] > 0
                        && $_POST['anzahl'][$i] > $Artikel->FunktionsAttribute[\FKT_ATTRIBUT_MAXBESTELLMENGE]
                    ) {
                        $gueltig       = false;
                        $cartNotices[] = Shop::Lang()->get('wkMaxorderlimit', 'messages');
                    }
                    if ($Artikel->cLagerBeachten === 'Y'
                        && $Artikel->cLagerVariation === 'Y'
                        && $Artikel->cLagerKleinerNull !== 'Y'
                        && \is_array($position->WarenkorbPosEigenschaftArr)
                    ) {
                        foreach ($position->WarenkorbPosEigenschaftArr as $eWert) {
                            $EigenschaftWert = new EigenschaftWert($eWert->kEigenschaftWert);
                            if ($EigenschaftWert->fPackeinheit * ((float)$_POST['anzahl'][$i] +
                                    $cart->gibAnzahlEinerVariation(
                                        $position->kArtikel,
                                        $eWert->kEigenschaftWert,
                                        $i
                                    )) > $EigenschaftWert->fLagerbestand
                            ) {
                                $cartNotices[] = Shop::Lang()->get(
                                    'quantityNotAvailableVar',
                                    'messages'
                                );
                                $gueltig       = false;
                                break;
                            }
                        }
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
                if (isset($position->Artikel->fVPEWert) && $position->Artikel->fVPEWert > 0) {
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
            } elseif ($position->nPosTyp === \C_WARENKORBPOS_TYP_GRATISGESCHENK) {
                $kArtikelGratisgeschenk = $position->kArtikel;
            }
        }
        $_SESSION['Warenkorbhinweise'] = $cartNotices;
        $kArtikelGratisgeschenk        = (int)$kArtikelGratisgeschenk;
        //positionen mit nAnzahl = 0 müssen gelöscht werden
        $cart->loescheNullPositionen();
        if (!$cart->posTypEnthalten(\C_WARENKORBPOS_TYP_ARTIKEL)) {
            $_SESSION['Warenkorb'] = new Warenkorb();
            $cart                  = $_SESSION['Warenkorb'];
        }
        if ($bMindestensEinePosGeaendert) {
            $oKuponTmp = null;
            //existiert ein proz. Kupon, der auf die neu eingefügte Pos greift?
            if (isset($_SESSION['Kupon'])
                && $_SESSION['Kupon']->cWertTyp === 'prozent'
                && $_SESSION['Kupon']->nGanzenWKRabattieren == 0
                && $cart->gibGesamtsummeWarenExt(
                    [\C_WARENKORBPOS_TYP_ARTIKEL],
                    true
                ) >= $_SESSION['Kupon']->fMindestbestellwert
            ) {
                $oKuponTmp = $_SESSION['Kupon'];
            }
            self::deleteAllSpecialPositions();
            if (isset($oKuponTmp->kKupon) && $oKuponTmp->kKupon > 0) {
                $_SESSION['Kupon'] = $oKuponTmp;
                foreach ($cart->PositionenArr as $i => $oWKPosition) {
                    $cart->PositionenArr[$i] = self::checkCouponCartPositions(
                        $oWKPosition,
                        $_SESSION['Kupon']
                    );
                }
            }
            \plausiNeukundenKupon();
        }
        $cart->setzePositionsPreise();
        // Gesamtsumme Warenkorb < Gratisgeschenk && Gratisgeschenk in den Pos?
        if ($kArtikelGratisgeschenk > 0) {
            // Prüfen, ob der Artikel wirklich ein Gratis Geschenk ist
            $oArtikelGeschenk = Shop::Container()->getDB()->query(
                'SELECT kArtikel
                    FROM tartikelattribut
                    WHERE kArtikel = ' . $kArtikelGratisgeschenk . "
                        AND cName = '" . \FKT_ATTRIBUT_GRATISGESCHENK . "'
                        AND CAST(cWert AS DECIMAL) <= " .
                $cart->gibGesamtsummeWarenExt([\C_WARENKORBPOS_TYP_ARTIKEL], true),
                ReturnType::SINGLE_OBJECT
            );

            if (empty($oArtikelGeschenk->kArtikel)) {
                $cart->loescheSpezialPos(\C_WARENKORBPOS_TYP_GRATISGESCHENK);
            }
        }
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
        if (!isset($_POST['schnellkauf']) || (int)$_POST['schnellkauf'] <= 0 || empty($_POST['ean'])) {
            return $msg;
        }
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
                Product::getSelectedPropertiesForArticle($product->kArtikel)
            )) {
                $msg = $product->cName . ' ' . Shop::Lang()->get('productAddedToCart');
            }
        }

        return $msg;
    }

    /**
     * @former loescheAlleSpezialPos()
     * @since 5.0.0
     */
    public static function deleteAllSpecialPositions(): void
    {
        Frontend::getCart()
                ->loescheSpezialPos(\C_WARENKORBPOS_TYP_ZAHLUNGSART)
                ->loescheSpezialPos(\C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
                ->loescheSpezialPos(\C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR)
                ->loescheSpezialPos(\C_WARENKORBPOS_TYP_VERSANDPOS)
                ->loescheSpezialPos(\C_WARENKORBPOS_TYP_VERSANDZUSCHLAG)
                ->loescheSpezialPos(\C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR)
                ->loescheSpezialPos(\C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG)
               ->loescheSpezialPos(\C_WARENKORBPOS_TYP_VERPACKUNG)
                ->loescheSpezialPos(\C_WARENKORBPOS_TYP_TRUSTEDSHOPS)
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

        \executeHook(\HOOK_WARENKORB_LOESCHE_ALLE_SPEZIAL_POS);

        Frontend::getCart()->setzePositionsPreise();
    }

    /**
     * @return stdClass
     * @former gibXSelling()
     * @since 5.0.0
     */
    public static function getXSelling(): stdClass
    {
        $xSelling      = new stdClass();
        $conf          = Shop::getSettings([\CONF_KAUFABWICKLUNG]);
        $cartPositions = Frontend::getCart()->PositionenArr;
        if ($conf['kaufabwicklung']['warenkorb_xselling_anzeigen'] !== 'Y'
            || !\is_array($cartPositions)
            || \count($cartPositions) === 0
        ) {
            return $xSelling;
        }
        $productIDs = \Functional\map(
            \Functional\filter($cartPositions, function ($p) {
                return isset($p->Artikel->kArtikel);
            }),
            function ($p) {
                return (int)$p->Artikel->kArtikel;
            }
        );
        if (\count($productIDs) > 0) {
            $productIDs = \implode(', ', $productIDs);
            $xsellData  = Shop::Container()->getDB()->query(
                'SELECT *
                    FROM txsellkauf
                    WHERE kArtikel IN (' . $productIDs . ')
                        AND kXSellArtikel NOT IN (' . $productIDs .')
                    GROUP BY kXSellArtikel
                    ORDER BY nAnzahl DESC
                    LIMIT ' . (int)$conf['kaufabwicklung']['warenkorb_xselling_anzahl'],
                ReturnType::ARRAY_OF_OBJECTS
            );
            if (\count($xsellData) > 0) {
                if (!isset($xSelling->Kauf)) {
                    $xSelling->Kauf = new stdClass();
                }
                $xSelling->Kauf->Artikel = [];
                $defaultOptions          = Artikel::getDefaultOptions();
                foreach ($xsellData as $oXsellkauf) {
                    $oArtikel = (new Artikel())->fuelleArtikel((int)$oXsellkauf->kXSellArtikel, $defaultOptions);
                    if ($oArtikel !== null && $oArtikel->kArtikel > 0 && $oArtikel->aufLagerSichtbarkeit()) {
                        $xSelling->Kauf->Artikel[] = $oArtikel;
                    }
                }
            }
        }

        return $xSelling;
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
                        AND tartikelattribut.cName = '" . \FKT_ATTRIBUT_GRATISGESCHENK . "'
                        AND CAST(tartikelattribut.cWert AS DECIMAL) <= " .
                Frontend::getCart()->gibGesamtsummeWarenExt([\C_WARENKORBPOS_TYP_ARTIKEL], true) .
                $cSQLSort . ' LIMIT 20',
                ReturnType::ARRAY_OF_OBJECTS
            );

            foreach ($giftsTmp as $gift) {
                $oArtikel = (new Artikel())->fuelleArtikel((int)$gift->kArtikel, Artikel::getDefaultOptions());
                if ($oArtikel !== null
                    && ($oArtikel->kEigenschaftKombi > 0
                        || !\is_array($oArtikel->Variationen)
                        || \count($oArtikel->Variationen) === 0)
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
        $cart         = Frontend::getCart();
        $cHinweis     = '';
        $cArtikelName = '';
        $bVorhanden   = false;
        $cISOSprache  = Shop::getLanguageCode();
        if (\is_array($cart->PositionenArr) && \count($cart->PositionenArr) > 0) {
            foreach ($cart->PositionenArr as $pos) {
                if ($pos->nPosTyp === \C_WARENKORBPOS_TYP_ARTIKEL
                    && isset($pos->Artikel)
                    && $pos->Artikel->cLagerBeachten === 'Y'
                    && $pos->Artikel->cLagerKleinerNull === 'Y'
                    && $conf['global']['global_lieferverzoegerung_anzeigen'] === 'Y'
                    && $pos->nAnzahl > $pos->Artikel->fLagerbestand
                ) {
                    $bVorhanden    = true;
                    $cName         = \is_array($pos->cName) ? $pos->cName[$cISOSprache] : $pos->cName;
                    $cArtikelName .= '<li>' . $cName . '</li>';
                }
            }
        }
        $cart->cEstimatedDelivery = $cart->getEstimatedDeliveryTime();

        if ($bVorhanden) {
            $cHinweis = \sprintf(Shop::Lang()->get('orderExpandInventory', 'basket'), '<ul>' . $cArtikelName . '</ul>');
        }

        return $cHinweis;
    }

    /**
     * Nachschauen ob beim Konfigartikel alle Pflichtkomponenten vorhanden sind, andernfalls löschen
     * @former validiereWarenkorbKonfig()
     * @since 5.0.0
     */
    public static function validateCartConfig(): void
    {
        Konfigurator::postcheckBasket($_SESSION['Warenkorb']);
    }
}
