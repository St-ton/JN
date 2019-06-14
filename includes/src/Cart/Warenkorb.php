<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Cart;

use function Functional\select;
use function Functional\some;
use JTL\Alert\Alert;
use JTL\Catalog\Product\Artikel;
use JTL\DB\ReturnType;
use JTL\Checkout\Eigenschaft;
use JTL\Catalog\Product\EigenschaftWert;
use JTL\Extensions\Konfigitem;
use JTL\Extensions\Konfigitemsprache;
use JTL\Extensions\Download;
use JTL\Helpers\Cart;
use JTL\Helpers\Product;
use JTL\Helpers\Request;
use JTL\Helpers\ShippingMethod;
use JTL\Helpers\Tax;
use JTL\Checkout\Kupon;
use JTL\Catalog\Product\Preise;
use JTL\Services\JTL\AlertService;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Checkout\Versandart;
use stdClass;

/**
 * Class Warenkorb
 * @package JTL\Cart
 */
class Warenkorb
{
    /**
     * @var int
     */
    public $kWarenkorb;

    /**
     * @var int
     */
    public $kKunde;

    /**
     * @var int
     */
    public $kLieferadresse;

    /**
     * @var int
     */
    public $kZahlungsInfo = 0;

    /**
     * @var WarenkorbPos[]
     */
    public $PositionenArr = [];

    /**
     * @var string
     */
    public $cEstimatedDelivery = '';

    /**
     * @var string
     */
    public $cChecksumme = '';

    /**
     * @var object
     */
    public $Waehrung;

    /**
     * @var Versandart
     */
    public $oFavourableShipping;

    /**
     * @var array
     */
    public static $updatedPositions = [];

    /**
     * @var array
     */
    public static $deletedPositions = [];

    /**
     * @var array
     */
    private $config;

    /**
     *
     */
    public function __wakeup()
    {
        $this->config = $this->config ?? Shop::getSettings([\CONF_GLOBAL, \CONF_KAUFABWICKLUNG]);
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        return select(\array_keys(\get_object_vars($this)), function ($e) {
            return $e !== 'config';
        });
    }

    /**
     * Warenkorb constructor.
     * @param int $kWarenkorb
     */
    public function __construct(int $kWarenkorb = 0)
    {
        $this->config = Shop::getSettings([\CONF_GLOBAL, \CONF_KAUFABWICKLUNG]);
        if ($kWarenkorb > 0) {
            $this->loadFromDB($kWarenkorb);
        }
    }

    /**
     * @since 4.06.10
     * @param bool       $onlyStockRelevant
     * @param null|int[] $excludePos
     * @return float[]
     */
    public function getAllDependentAmount(bool $onlyStockRelevant = false, $excludePos = null): array
    {
        $depAmount = [];

        foreach ($this->PositionenArr as $key => $cartItem) {
            if (\is_array($excludePos) && \in_array($key, $excludePos)) {
                continue;
            }

            if (!empty($cartItem->Artikel)
                && (!$onlyStockRelevant
                    || ($cartItem->Artikel->cLagerBeachten === 'Y' && $cartItem->Artikel->cLagerKleinerNull !== 'Y'))
            ) {
                $depProducts = $cartItem->Artikel->getAllDependentProducts($onlyStockRelevant);

                foreach ($depProducts as $productID => $item) {
                    if (isset($depAmount[$productID])) {
                        $depAmount[$productID] += ($cartItem->nAnzahl * $item->stockFactor);
                    } else {
                        $depAmount[$productID] = $cartItem->nAnzahl * $item->stockFactor;
                    }
                }
            }
        }

        return $depAmount;
    }

    /**
     * @since 4.06.10
     * @param int        $productID
     * @param bool       $onlyStockRelevant
     * @param null|int[] $excludePos
     * @return float
     */
    public function getDependentAmount(int $productID, bool $onlyStockRelevant = false, $excludePos = null): float
    {
        static $depAmount = null;

        if ($excludePos !== null) {
            $tmpAmount = $this->getAllDependentAmount($onlyStockRelevant, $excludePos);

            return $tmpAmount[$productID] ?? 0;
        }

        if (!isset($depAmount, $depAmount[$productID])) {
            $depAmount = $this->getAllDependentAmount($onlyStockRelevant);
        }

        return $depAmount[$productID] ?? 0;
    }

    /**
     * @param int   $item
     * @param float $amount
     * @return float
     */
    public function getMaxAvailableAmount(int $item, float $amount): float
    {
        foreach ($this->PositionenArr[$item]->Artikel->getAllDependentProducts(true) as $dependent) {
            $depProduct = $dependent->product;
            $depAmount  = $this->getDependentAmount($depProduct->kArtikel, true, [$item]);
            $newAmount  = \floor(
                ($depProduct->fLagerbestand - $depAmount) / $depProduct->fPackeinheit / $dependent->stockFactor
            );

            if ($newAmount < $amount) {
                $amount = $newAmount;
            }
        }

        return $amount;
    }

    /**
     * Entfernt Positionen, die in der Wawi zwischenzeitlich deaktiviert/geloescht wurden
     *
     * @return $this
     */
    public function loescheDeaktiviertePositionen(): self
    {
        foreach ($this->PositionenArr as $i => $item) {
            $item->nPosTyp = (int)$item->nPosTyp;
            $delete        = false;
            if (!empty($item->Artikel)) {
                if (isset(
                    $item->Artikel->fLagerbestand,
                    $item->Artikel->cLagerBeachten,
                    $item->Artikel->cLagerKleinerNull,
                    $item->Artikel->cLagerVariation
                )
                    && $item->Artikel->fLagerbestand <= 0
                    && $item->Artikel->cLagerBeachten === 'Y'
                    && $item->Artikel->cLagerKleinerNull !== 'Y'
                    && $item->Artikel->cLagerVariation !== 'Y'
                ) {
                    $delete = true;
                } elseif (empty($item->kKonfigitem)
                    && $item->fPreisEinzelNetto == 0
                    && !$item->Artikel->bHasKonfig
                    && $item->nPosTyp !== \C_WARENKORBPOS_TYP_GRATISGESCHENK
                    && isset($item->fPreisEinzelNetto, $this->config['global']['global_preis0'])
                    && $this->config['global']['global_preis0'] === 'N'
                ) {
                    $delete = true;
                } elseif (!empty($item->Artikel->FunktionsAttribute[\FKT_ATTRIBUT_UNVERKAEUFLICH])) {
                    $delete = true;
                } else {
                    $delete = (Shop::Container()->getDB()->select(
                        'tartikel',
                        'kArtikel',
                        $item->kArtikel
                    ) === null);
                }

                \executeHook(\HOOK_WARENKORB_CLASS_LOESCHEDEAKTIVIERTEPOS, [
                    'oPosition' => $item,
                    'delete'    => &$delete
                ]);
            }
            if ($delete) {
                self::addDeletedPosition($item);
                unset($this->PositionenArr[$i]);
            }
        }
        $this->PositionenArr = \array_merge($this->PositionenArr);

        return $this;
    }

    /**
     * @param object $item
     */
    public static function addUpdatedPosition($item): void
    {
        self::$updatedPositions[] = $item;
    }

    /**
     * @param object $item
     */
    public static function addDeletedPosition($item): void
    {
        self::$deletedPositions[] = $item;
    }

    /**
     * fuegt eine neue Position hinzu
     * @param int         $productID
     * @param int         $qty   Anzahl des Artikel fuer die neue Position
     * @param array       $attributeValues
     * @param int         $type
     * @param string|bool $unique
     * @param int         $kKonfigitem
     * @param bool        $setzePositionsPreise
     * @param string      $responsibility
     * @return $this
     */
    public function fuegeEin(
        int $productID,
        $qty,
        array $attributeValues,
        int $type = \C_WARENKORBPOS_TYP_ARTIKEL,
        $unique = false,
        int $kKonfigitem = 0,
        bool $setzePositionsPreise = true,
        string $responsibility = 'core'
    ): self {
        $iso = Shop::getLanguageCode();
        //toDo schaue, ob diese Pos nicht markiert werden muesste, wenn anzahl>lager gekauft wird
        //schaue, ob es nicht schon Positionen mit diesem Artikel gibt
        foreach ($this->PositionenArr as $i => $item) {
            if (!(isset($item->Artikel->kArtikel)
                && $item->Artikel->kArtikel == $productID
                && $item->nPosTyp == $type
                && !$item->cUnique)
            ) {
                continue;
            }
            $isNew = false;
            // hat diese Position schon einen EigenschaftWert ausgewaehlt
            // und ist das dieselbe Eigenschaft wie ausgewaehlt?
            if (!$unique) {
                foreach ($item->WarenkorbPosEigenschaftArr as $wke) {
                    foreach ($attributeValues as $aValue) {
                        // gleiche Eigenschaft suchen
                        if ($aValue->kEigenschaft != $wke->kEigenschaft) {
                            continue;
                        }
                        // ist es ein Freifeld mit unterschiedlichem Inhalt
                        // oder eine Eigenschaft mit unterschiedlichem Wert?
                        if (($wke->kEigenschaftWert > 0
                                && $wke->kEigenschaftWert != $aValue->kEigenschaftWert)
                            || (($wke->cTyp === 'FREIFELD' || $wke->cTyp === 'PFLICHT-FREIFELD')
                                && $wke->cEigenschaftWertName[$iso] != $aValue->cFreifeldWert)
                        ) {
                            $isNew = true;
                            break;
                        }
                    }
                }
                if (!$isNew && !$unique) {
                    //erhoehe Anzahl dieser Position
                    $item->nZeitLetzteAenderung = \time();
                    $item->nAnzahl             += $qty;
                    if ($setzePositionsPreise === true) {
                        $this->setzePositionsPreise();
                    }
                    \executeHook(\HOOK_WARENKORB_CLASS_FUEGEEIN, [
                        'kArtikel'      => $productID,
                        'oPosition_arr' => &$this->PositionenArr,
                        'nAnzahl'       => &$qty,
                        'exists'        => true
                    ]);

                    return $this;
                }
            }
        }
        $options               = Artikel::getDefaultOptions();
        $options->nVariationen = 1;
        if ($kKonfigitem > 0) {
            $options->nKeineSichtbarkeitBeachten = 1;
        }
        $cartItem          = new WarenkorbPos();
        $cartItem->Artikel = new Artikel();
        $cartItem->Artikel->fuelleArtikel($productID, $options);
        $cartItem->nAnzahl           = $qty;
        $cartItem->kArtikel          = $cartItem->Artikel->kArtikel;
        $cartItem->kVersandklasse    = $cartItem->Artikel->kVersandklasse;
        $cartItem->kSteuerklasse     = $cartItem->Artikel->kSteuerklasse;
        $cartItem->fPreisEinzelNetto = $cartItem->Artikel->gibPreis($cartItem->nAnzahl, []);
        $cartItem->fPreis            = $cartItem->Artikel->gibPreis($qty, []);
        $cartItem->cArtNr            = $cartItem->Artikel->cArtNr;
        $cartItem->nPosTyp           = $type;
        $cartItem->cEinheit          = $cartItem->Artikel->cEinheit;
        $cartItem->cUnique           = $unique;
        $cartItem->cResponsibility   = $responsibility;
        $cartItem->kKonfigitem       = $kKonfigitem;
        $cartItem->setzeGesamtpreisLocalized();
        $cartItem->cName         = [];
        $cartItem->cLieferstatus = [];

        $db            = Shop::Container()->getDB();
        $deliveryState = $cartItem->Artikel->cLieferstatus;
        foreach (Frontend::getLanguages() as $lang) {
            $cartItem->cName[$lang->cISO]         = $cartItem->Artikel->cName;
            $cartItem->cLieferstatus[$lang->cISO] = $deliveryState;
            if ($lang->cStandard === 'Y') {
                $localized = $db->select(
                    'tartikel',
                    'kArtikel',
                    (int)$cartItem->kArtikel,
                    null,
                    null,
                    null,
                    null,
                    false,
                    'cName'
                );
            } else {
                $localized = $db->select(
                    'tartikelsprache',
                    'kArtikel',
                    (int)$cartItem->kArtikel,
                    'kSprache',
                    (int)$lang->kSprache,
                    null,
                    null,
                    false,
                    'cName'
                );
            }
            //Wenn fuer die gewaehlte Sprache kein Name vorhanden ist dann StdSprache nehmen
            $cartItem->cName[$lang->cISO] = (isset($localized->cName) && \mb_strlen(\trim($localized->cName)) > 0)
                ? $localized->cName
                : $cartItem->Artikel->cName;
            $lieferstatus_spr             = $db->select(
                'tlieferstatus',
                'kLieferstatus',
                (int)($cartItem->Artikel->kLieferstatus ?? 0),
                'kSprache',
                (int)$lang->kSprache
            );
            if (!empty($lieferstatus_spr->cName)) {
                $cartItem->cLieferstatus[$lang->cISO] = $lieferstatus_spr->cName;
            }
        }
        // Grundpreise bei Staffelpreisen
        if (isset($cartItem->Artikel->fVPEWert) && $cartItem->Artikel->fVPEWert > 0) {
            $nLast = 0;
            for ($j = 1; $j <= 5; $j++) {
                $cStaffel = 'nAnzahl' . $j;
                if (isset($cartItem->Artikel->Preise->$cStaffel)
                    && $cartItem->Artikel->Preise->$cStaffel > 0
                    && $cartItem->Artikel->Preise->$cStaffel <= $cartItem->nAnzahl
                ) {
                    $nLast = $j;
                }
            }
            if ($nLast > 0) {
                $cStaffel = 'fPreis' . $nLast;
                $cartItem->Artikel->baueVPE($cartItem->Artikel->Preise->$cStaffel);
            } else {
                $cartItem->Artikel->baueVPE();
            }
        }
        $this->setzeKonfig($cartItem, false);
        if (\is_array($cartItem->Artikel->Variationen) && \count($cartItem->Artikel->Variationen) > 0) {
            //foreach ($ewerte as $eWert)
            foreach ($cartItem->Artikel->Variationen as $variation) {
                $variation->kEigenschaft = (int)$variation->kEigenschaft;
                foreach ($attributeValues as $aValue) {
                    $aValue->kEigenschaft = (int)$aValue->kEigenschaft;
                    //gleiche Eigenschaft suchen
                    if ($aValue->kEigenschaft !== $variation->kEigenschaft) {
                        continue;
                    }
                    if ($variation->cTyp === 'FREIFELD' || $variation->cTyp === 'PFLICHT-FREIFELD') {
                        $cartItem->setzeVariationsWert($variation->kEigenschaft, 0, $aValue->cFreifeldWert);
                    } elseif ($aValue->kEigenschaftWert > 0) {
                        $value = new EigenschaftWert($aValue->kEigenschaftWert);
                        $attr  = new Eigenschaft($value->kEigenschaft);
                        // Varkombi Kind?
                        if ($cartItem->Artikel->kVaterArtikel > 0) {
                            if ($attr->kArtikel == $cartItem->Artikel->kVaterArtikel) {
                                $cartItem->setzeVariationsWert(
                                    $value->kEigenschaft,
                                    $value->kEigenschaftWert
                                );
                            }
                        } elseif ($attr->kArtikel == $cartItem->kArtikel) {
                            // Variationswert hat eigene Artikelnummer
                            // und der Artikel hat nur eine Dimension als Variation?
                            if (isset($value->cArtNr)
                                && \count($cartItem->Artikel->Variationen) === 1
                                && \mb_strlen($value->cArtNr) > 0
                            ) {
                                $cartItem->cArtNr          = $value->cArtNr;
                                $cartItem->Artikel->cArtNr = $value->cArtNr;
                            }

                            $cartItem->setzeVariationsWert(
                                $value->kEigenschaft,
                                $value->kEigenschaftWert
                            );
                            // aktuellen Eigenschaftswert mit Bild ermitteln
                            // und Variationsbild an der Position speichern
                            $kEigenschaftWert = $value->kEigenschaftWert;
                            $oVariationWert   = \current(
                                \array_filter(
                                    $variation->Werte,
                                    function ($item) use ($kEigenschaftWert) {
                                        return $item->kEigenschaftWert === $kEigenschaftWert
                                            && !empty($item->cPfadNormal);
                                    }
                                )
                            );

                            if ($oVariationWert !== false) {
                                Cart::setVariationPicture($cartItem, $oVariationWert);
                            }
                        }
                    }
                }
            }
        }

        $cartItem->fGesamtgewicht       = $cartItem->gibGesamtgewicht();
        $cartItem->nZeitLetzteAenderung = \time();

        switch ($cartItem->nPosTyp) {
            case \C_WARENKORBPOS_TYP_GRATISGESCHENK:
                $cartItem->fPreisEinzelNetto = 0;
                $cartItem->fPreis            = 0;
                $cartItem->setzeGesamtpreisLocalized();
                break;

            case \C_WARENKORBPOS_TYP_VERSANDPOS:
                if (isset($_SESSION['Versandart']->angezeigterHinweistext[Shop::getLanguageCode()])
                    && \mb_strlen($_SESSION['Versandart']->angezeigterHinweistext[Shop::getLanguageCode()]) > 0
                ) {
                    $cartItem->cHinweis = $_SESSION['Versandart']->angezeigterHinweistext[Shop::getLanguageCode()];
                }
                break;

            case \C_WARENKORBPOS_TYP_ZAHLUNGSART:
                if (isset($_SESSION['Zahlungsart']->cHinweisText)) {
                    $cartItem->cHinweis = $_SESSION['Zahlungsart']->cHinweisText;
                }
                break;
        }
        unset($cartItem->Artikel->oKonfig_arr); //#7482
        $this->PositionenArr[] = $cartItem;
        if ($setzePositionsPreise === true) {
            $this->setzePositionsPreise();
        }
        $this->updateCouponValue();
        $this->sortShippingPosition();

        \executeHook(\HOOK_WARENKORB_CLASS_FUEGEEIN, [
            'kArtikel'      => $productID,
            'oPosition_arr' => &$this->PositionenArr,
            'nAnzahl'       => &$qty,
            'exists'        => false
        ]);

        return $this;
    }

    /**
     * @return $this
     */
    public function sortShippingPosition(): self
    {
        if (!\is_array($this->PositionenArr) || \count($this->PositionenArr) <= 1) {
            return $this;
        }
        $shippingItem = null;
        $i            = 0;
        foreach ($this->PositionenArr as $item) {
            $item->nPosTyp = (int)$item->nPosTyp;
            if ($item->nPosTyp === \C_WARENKORBPOS_TYP_VERSANDPOS) {
                $shippingItem = $item;
                break;
            }
            $i++;
        }

        if ($shippingItem !== null) {
            unset($this->PositionenArr[$i]);
            $this->PositionenArr   = \array_merge($this->PositionenArr);
            $this->PositionenArr[] = $shippingItem;
        }

        return $this;
    }

    /**
     * @return int
     */
    public function gibLetzteWarenkorbPostionindex(): int
    {
        return \is_array($this->PositionenArr) ? (\count($this->PositionenArr) - 1) : 0;
    }

    /**
     * @param int $type
     * @return bool
     * @deprecated since 5.0.0
     */
    public function enthaltenSpezialPos(int $type): bool
    {
        return $this->posTypEnthalten($type);
    }

    /**
     * @param int $type
     * @return $this
     */
    public function loescheSpezialPos(int $type): self
    {
        if (\count($this->PositionenArr) === 0) {
            return $this;
        }
        foreach ($this->PositionenArr as $i => $item) {
            if (isset($item->nPosTyp) && (int)$item->nPosTyp === $type) {
                unset($this->PositionenArr[$i]);
            }
        }
        $this->PositionenArr = \array_merge($this->PositionenArr);
        if (!empty($_POST['Kuponcode']) && $type === \C_WARENKORBPOS_TYP_KUPON) {
            if (!empty($_SESSION['Kupon'])) {
                unset($_SESSION['Kupon']);
            } elseif (!empty($_SESSION['oVersandfreiKupon'])) {
                unset($_SESSION['oVersandfreiKupon']);
                if (!empty($_SESSION['VersandKupon'])) {
                    unset($_SESSION['VersandKupon']);
                }
            }
        }

        return $this;
    }

    /**
     * erstellt eine Spezialposition im Warenkorb
     *
     * @param string|array $name          Positionsname
     * @param string       $qty        Positionsanzahl
     * @param string       $price         Positionspreis
     * @param string       $taxClassID Positionsmwst
     * @param int          $typ           Positionstyp
     * @param bool         $delSamePosType
     * @param bool         $grossPrice
     * @param string       $message
     * @param string|bool  $unique
     * @param int          $kKonfigitem
     * @param int          $productID
     * @return $this
     */
    public function erstelleSpezialPos(
        $name,
        $qty,
        $price,
        $taxClassID,
        int $typ,
        bool $delSamePosType = true,
        bool $grossPrice = true,
        string $message = '',
        $unique = false,
        int $kKonfigitem = 0,
        int $productID = 0
    ): self {
        if ($delSamePosType) {
            $this->loescheSpezialPos($typ);
        }
        $cartItem                = new WarenkorbPos();
        $cartItem->nAnzahl       = $qty;
        $cartItem->nAnzahlEinzel = $qty;
        $cartItem->kArtikel      = 0;
        $cartItem->kSteuerklasse = $taxClassID;
        $cartItem->fPreis        = $price;
        $cartItem->cUnique       = $unique;
        $cartItem->kKonfigitem   = $kKonfigitem;
        $cartItem->kArtikel      = $productID;
        //fixes #4967
        if (\is_object($_SESSION['Kundengruppe']) && Frontend::getCustomerGroup()->isMerchant()) {
            if ($grossPrice) {
                $cartItem->fPreis = $price / (100 + Tax::getSalesTax($taxClassID)) * 100.0;
            }
            //round net price
            $cartItem->fPreis = \round($cartItem->fPreis, 2);
        } elseif ($grossPrice) {
            //calculate net price based on rounded gross price
            $cartItem->fPreis = \round($price, 2) / (100 + Tax::getSalesTax($taxClassID)) * 100.0;
        } else {
            //calculate rounded gross price then calculate net price again.
            $cartItem->fPreis = \round($price * (100 + Tax::getSalesTax($taxClassID)) / 100, 2) /
                (100 + Tax::getSalesTax($taxClassID)) * 100.0;
        }

        $cartItem->fPreisEinzelNetto = $cartItem->fPreis;
        if ($typ === \C_WARENKORBPOS_TYP_KUPON && isset($name->cName)) {
            $cartItem->cName = \is_array($name->cName)
                ? $name->cName
                : [Shop::getLanguageCode() => $name->cName];
            if (isset($name->cArticleNameAffix, $name->discountForArticle)) {
                $cartItem->cArticleNameAffix  = $name->cArticleNameAffix;
                $cartItem->discountForArticle = $name->discountForArticle;
            }
        } else {
            $cartItem->cName = \is_array($name)
                ? $name
                : [Shop::getLanguageCode() => $name];
        }
        $cartItem->nPosTyp  = $typ;
        $cartItem->cHinweis = $message;
        $nOffset            = \array_push($this->PositionenArr, $cartItem);
        $cartItem           = $this->PositionenArr[$nOffset - 1];
        foreach (Frontend::getCurrencies() as $currency) {
            $currencyName = $currency->getName();
            // Standardartikel
            $cartItem->cGesamtpreisLocalized[0][$currencyName] = Preise::getLocalizedPriceString(
                Tax::getGross(
                    $cartItem->fPreis * $cartItem->nAnzahl,
                    Tax::getSalesTax($cartItem->kSteuerklasse)
                ),
                $currency
            );
            $cartItem->cGesamtpreisLocalized[1][$currencyName] = Preise::getLocalizedPriceString(
                $cartItem->fPreis * $cartItem->nAnzahl,
                $currency
            );
            $cartItem->cEinzelpreisLocalized[0][$currencyName] = Preise::getLocalizedPriceString(
                Tax::getGross($cartItem->fPreis, Tax::getSalesTax($cartItem->kSteuerklasse)),
                $currency
            );
            $cartItem->cEinzelpreisLocalized[1][$currencyName] = Preise::getLocalizedPriceString(
                $cartItem->fPreis,
                $currency
            );

            // Konfigurationsartikel: mapto: 9a87wdgad
            if ((int)$cartItem->kKonfigitem > 0
                && \is_string($cartItem->cUnique)
                && !empty($cartItem->cUnique)
            ) {
                $net       = 0;
                $gross     = 0;
                $parentIdx = null;

                foreach ($this->PositionenArr as $idx => $item) {
                    if ($cartItem->cUnique === $item->cUnique) {
                        $net   += $item->fPreis * $item->nAnzahl;
                        $gross += Tax::getGross(
                            $item->fPreis * $item->nAnzahl,
                            Tax::getSalesTax($item->kSteuerklasse)
                        );

                        if ((int)$item->kKonfigitem === 0
                            && \is_string($item->cUnique)
                            && !empty($item->cUnique)
                        ) {
                            $parentIdx = $idx;
                        }
                    }
                }

                if ($parentIdx !== null) {
                    $parent = $this->PositionenArr[$parentIdx];
                    if (\is_object($parent)) {
                        $cartItem->nAnzahlEinzel                         = $cartItem->nAnzahl / $parent->nAnzahl;
                        $parent->cKonfigpreisLocalized[0][$currencyName] = Preise::getLocalizedPriceString(
                            $gross,
                            $currency
                        );
                        $parent->cKonfigpreisLocalized[1][$currencyName] = Preise::getLocalizedPriceString(
                            $net,
                            $currency
                        );
                    }
                }
            }
        }
        $this->sortShippingPosition();

        return $this;
    }

    /**
     * stellt fest, ob der Warenkorb alle Eingaben erhalten hat, um den Bestellvorgang durchzufuehren
     *
     * @return int
     * 10 - alles OK, Bestellung kann gemacht werden.
     * 1 - VersandArt fehlt.
     * 2 - Mindestens eine Variation eines Artikels wurde nicht ausgewaehlt
     * 3 - Warenkorb enthaelt keine Positionen
     */
    public function istBestellungMoeglich(): int
    {
        if (\count($this->PositionenArr) < 1) {
            return 3;
        }
        $mbw = Frontend::getCustomerGroup()->getAttribute(\KNDGRP_ATTRIBUT_MINDESTBESTELLWERT);
        if ($mbw > 0 && $this->gibGesamtsummeWaren(true, false) < $mbw) {
            return 9;
        }
        if ((!isset($_SESSION['bAnti_spam_already_checked']) || $_SESSION['bAnti_spam_already_checked'] !== true)
            && $this->config['kaufabwicklung']['bestellabschluss_spamschutz_nutzen'] === 'Y'
            && $this->config['kaufabwicklung']['bestellabschluss_ip_speichern'] === 'Y'
            && ($ip = Request::getRealIP())
        ) {
            $cnt = Shop::Container()->getDB()->executeQueryPrepared(
                'SELECT COUNT(*) AS anz
                    FROM tbestellung
                    WHERE cIP = :ip
                        AND dErstellt > NOW() - INTERVAL 1 DAY',
                ['ip' => $ip],
                ReturnType::SINGLE_OBJECT
            );
            if ($cnt->anz > 0) {
                $min                = 2 ** $cnt->anz;
                $min                = \min([$min, 1440]);
                $bestellungMoeglich = Shop::Container()->getDB()->executeQueryPrepared(
                    'SELECT dErstellt+INTERVAL ' . $min . ' MINUTE < NOW() AS moeglich
                        FROM tbestellung
                        WHERE cIP = :ip
                            AND dErstellt > NOW()-INTERVAL 1 DAY
                        ORDER BY kBestellung DESC',
                    ['ip' => $ip],
                    ReturnType::SINGLE_OBJECT
                );
                if (!$bestellungMoeglich->moeglich) {
                    return 8;
                }
            }
        }

        return 10;
    }

    /**
     * gibt Gesamtanzahl Artikel des Warenkorbs zurueck
     *
     * @param int[]  $itemTypes
     * @param string $iso
     * @param bool   $excludeShippingCostAttributes
     * @return int|float
     */
    public function gibAnzahlArtikelExt(array $itemTypes, bool $excludeShippingCostAttributes = false, string $iso = '')
    {
        if (!\is_array($itemTypes)) {
            return 0;
        }
        $count = 0;
        foreach ($this->PositionenArr as $item) {
            if (\in_array($item->nPosTyp, $itemTypes)
                && (empty($item->cUnique) || (\mb_strlen($item->cUnique) > 0 && $item->kKonfigitem == 0))
                && $item->isUsedForShippingCostCalculation($iso, $excludeShippingCostAttributes)
            ) {
                $count += $item->nAnzahl;
            }
        }

        return $count;
    }

    /**
     * gibt Anzahl der Positionen des Warenkorbs zurueck
     *
     * @param int[] $itemTypes
     * @return int
     */
    public function gibAnzahlPositionenExt($itemTypes): int
    {
        if (!\is_array($itemTypes)) {
            return 0;
        }
        $count = 0;
        foreach ($this->PositionenArr as $item) {
            if (\in_array($item->nPosTyp, $itemTypes)
                && (empty($item->cUnique) || (\mb_strlen($item->cUnique) > 0 && $item->kKonfigitem == 0))
            ) {
                ++$count;
            }
        }

        return $count;
    }

    /**
     * @return bool
     */
    public function hatTeilbareArtikel(): bool
    {
        foreach ($this->PositionenArr as $item) {
            $item->nPosTyp = (int)$item->nPosTyp;
            if ($item->nPosTyp === \C_WARENKORBPOS_TYP_ARTIKEL
                && isset($item->Artikel->cTeilbar)
                && $item->Artikel->cTeilbar === 'Y'
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * gibt Gesamtanzahl eines bestimmten Artikels im Warenkorb zurueck
     * @param int $productID
     * @param int $excludePos
     * @return int
     */
    public function gibAnzahlEinesArtikels(int $productID, int $excludePos = -1)
    {
        if (!$productID) {
            return 0;
        }
        $qty = 0;
        foreach ($this->PositionenArr as $i => $item) {
            if ((int)$item->kArtikel === $productID && $excludePos !== $i) {
                $qty += $item->nAnzahl;
            }
        }

        return $qty;
    }

    /**
     * @return $this
     */
    public function setzePositionsPreise(): self
    {
        $defaultOptions = Artikel::getDefaultOptions();
        foreach ($this->PositionenArr as $i => $item) {
            if ($item->kArtikel > 0 && $item->nPosTyp === \C_WARENKORBPOS_TYP_ARTIKEL) {
                $oldItem = clone $item;
                $product = new Artikel();
                if (!$product->fuelleArtikel($item->kArtikel, $defaultOptions) || $product->kArtikel === null) {
                    continue;
                }
                // Baue Variationspreise im Warenkorb neu, aber nur wenn es ein gültiger Artikel ist
                if (\is_array($item->WarenkorbPosEigenschaftArr)) {
                    foreach ($this->PositionenArr[$i]->WarenkorbPosEigenschaftArr as $j => $posAttr) {
                        if (!\is_array($product->Variationen)) {
                            continue;
                        }
                        foreach ($product->Variationen as $variation) {
                            if ($posAttr->kEigenschaft != $variation->kEigenschaft) {
                                continue;
                            }
                            foreach ($variation->Werte as $oEigenschaftWert) {
                                if ($posAttr->kEigenschaftWert == $oEigenschaftWert->kEigenschaftWert) {
                                    $item->WarenkorbPosEigenschaftArr[$j]->fAufpreis          =
                                        $oEigenschaftWert->fAufpreisNetto ?? null;
                                    $item->WarenkorbPosEigenschaftArr[$j]->cAufpreisLocalized =
                                        $oEigenschaftWert->cAufpreisLocalized[1] ?? null;
                                    break;
                                }
                            }

                            break;
                        }
                    }
                }
                $anz                     = $this->gibAnzahlEinesArtikels($product->kArtikel);
                $item->Artikel           = $product;
                $item->fPreisEinzelNetto = $product->gibPreis($anz, []);
                $item->fPreis            = $product->gibPreis($anz, $item->WarenkorbPosEigenschaftArr);
                $item->fGesamtgewicht    = $item->gibGesamtgewicht();
                \executeHook(\HOOK_SETZTE_POSITIONSPREISE, [
                    'position'    => $item,
                    'oldPosition' => $oldItem
                ]);
                $item->setzeGesamtpreisLocalized();
                //notify about price changes when the price difference is greater then .01
                if ($oldItem->cGesamtpreisLocalized !== $item->cGesamtpreisLocalized
                    && $oldItem->Artikel->Preise->fVK !== $item->Artikel->Preise->fVK
                ) {
                    $updated                           = new stdClass();
                    $updated->cKonfigpreisLocalized    = $item->cKonfigpreisLocalized;
                    $updated->cGesamtpreisLocalized    = $item->cGesamtpreisLocalized;
                    $updated->cName                    = $item->cName;
                    $updated->cKonfigpreisLocalizedOld = $oldItem->cKonfigpreisLocalized;
                    $updated->cGesamtpreisLocalizedOld = $oldItem->cGesamtpreisLocalized;
                    $updated->istKonfigVater           = $item->istKonfigVater();
                    self::addUpdatedPosition($updated);
                    Shop::Container()->getAlertService()->addAlert(
                        Alert::TYPE_WARNING,
                        \sprintf(
                            Shop::Lang()->get('priceHasChanged', 'checkout'),
                            \is_array($item->cName) ? $item->cName[Shop::getLanguageCode()] : $item->cName
                        ),
                        'priceHasChanged_' . $item->kArtikel,
                        [
                            'saveInSession' => true,
                            'dismissable'   => false,
                            'linkHref'      => Shop::Container()->getLinkService()->getStaticRoute('warenkorb.php'),
                            'linkText'      => Shop::Lang()->get('gotoBasket'),
                        ]
                    );
                }
                unset($item->cHinweis);
                if (isset($_SESSION['Kupon']->kKupon)
                    && $_SESSION['Kupon']->kKupon > 0
                    && (int)$_SESSION['Kupon']->nGanzenWKRabattieren === 0
                ) {
                    $item = Cart::checkCouponCartItems($item, $_SESSION['Kupon']);
                    $item->setzeGesamtpreisLocalized();
                }
            }

            $this->setzeKonfig($item, true, false);
        }

        return $this;
    }

    /**
     * @param object $item
     * @param bool   $prices
     * @param bool   $name
     * @return $this
     */
    public function setzeKonfig(&$item, bool $prices = true, bool $name = true): self
    {
        // Falls Konfigitem gesetzt Preise + Name ueberschreiben
        if ((int)$item->kKonfigitem <= 0 || !\class_exists('Konfigitem')) {
            return $this;
        }
        $configItem = new Konfigitem($item->kKonfigitem);
        if ($configItem->getKonfigitem() > 0) {
            if ($prices) {
                $item->fPreisEinzelNetto = $configItem->getPreis(true);
                $item->fPreis            = $item->fPreisEinzelNetto;
                $item->kSteuerklasse     = $configItem->getSteuerklasse();
                $item->setzeGesamtpreisLocalized();
            }
            if ($name && $configItem->getUseOwnName()) {
                foreach (Frontend::getLanguages() as $language) {
                    $localized                    = new Konfigitemsprache(
                        $configItem->getKonfigitem(),
                        $language->kSprache
                    );
                    $item->cName[$language->cISO] = $localized->getName();
                }
            }
        }

        return $this;
    }

    /**
     * gibt Gesamtanzahl einer bestimmten Variation im Warenkorb zurueck
     * @param int $productID
     * @param int $kEigenschaftsWert
     * @param int $excludePos
     * @return int
     */
    public function gibAnzahlEinerVariation(int $productID, int $kEigenschaftsWert, int $excludePos = -1)
    {
        if (!$productID || !$kEigenschaftsWert) {
            return 0;
        }
        $qty = 0;
        foreach ($this->PositionenArr as $i => $item) {
            if ($item->kArtikel == $productID && $excludePos != $i && \is_array($item->WarenkorbPosEigenschaftArr)) {
                foreach ($item->WarenkorbPosEigenschaftArr as $attr) {
                    if ($attr->kEigenschaftWert == $kEigenschaftsWert) {
                        $qty += $item->nAnzahl;
                    }
                }
            }
        }

        return $qty;
    }

    /**
     * gibt die tatsaechlichen Versandkosten zurueck, falls eine VersandArt gesetzt ist.
     * Es wird ebenso ueberprueft, ob die Summe fuer versandkostnfrei erreicht wurde.
     *
     * @param string $countryCode
     * @return int
     *@todo: param?
     */
    public function gibVersandkostenSteuerklasse($countryCode = ''): int
    {
        $classID = 0;
        if ($this->config['kaufabwicklung']['bestellvorgang_versand_steuersatz'] === 'US') {
            $taxRates = [];
            foreach ($this->PositionenArr as $i => $item) {
                if ($item->nPosTyp === \C_WARENKORBPOS_TYP_ARTIKEL && $item->kSteuerklasse > 0) {
                    if (empty($taxRates[$item->kSteuerklasse])) {
                        $taxRates[$item->kSteuerklasse] = $item->fPreisEinzelNetto * $item->nAnzahl;
                    } else {
                        $taxRates[$item->kSteuerklasse] += $item->fPreisEinzelNetto * $item->nAnzahl;
                    }
                }
            }
            $maxRate = \count($taxRates) > 0 ? \max($taxRates) : 0;
            foreach ($taxRates as $i => $rate) {
                if ($rate === $maxRate) {
                    $classID = $i;
                    break;
                }
            }
        } else {
            $rate = -1;
            foreach ($this->PositionenArr as $i => $item) {
                if ($item->nPosTyp === \C_WARENKORBPOS_TYP_ARTIKEL
                    && $item->kSteuerklasse > 0
                    && Tax::getSalesTax($item->kSteuerklasse) > $rate
                ) {
                    $rate    = Tax::getSalesTax($item->kSteuerklasse);
                    $classID = $item->kSteuerklasse;
                }
            }
        }

        return (int)$classID;
    }

    /**
     * gibt die Versandkosten als String zurueck
     *
     * @return string
     */
    public function gibVersandKostenText(): string
    {
        return isset($_SESSION['Versandart'])
            ? Shop::Lang()->get('noShippingCosts', 'basket')
            : (Shop::Lang()->get('plus', 'basket') . ' ' . Shop::Lang()->get('shipping', 'basket'));
    }

    /**
     * Gibt gesamte Warenkorbsumme zurueck.
     * @param bool $gross
     * @param bool $considerBalance
     * @return float
     */
    public function gibGesamtsummeWaren(bool $gross = false, bool $considerBalance = true)
    {
        $currency         = $this->Waehrung ?? Frontend::getCurrency();
        $conversionFactor = $currency->getConversionFactor();
        $total            = 0;
        foreach ($this->PositionenArr as $item) {
            // Lokalisierte Preise addieren
            if ($gross) {
                $total += $item->fPreis * $conversionFactor * $item->nAnzahl *
                    ((100 + Tax::getSalesTax($item->kSteuerklasse)) / 100);
            } else {
                $total += $item->fPreis * $conversionFactor * $item->nAnzahl;
            }
        }
        if ($gross) {
            $total = \round($total, 2);
        }
        if (!empty($considerBalance)
            && isset(
                $_SESSION['Bestellung']->GuthabenNutzen,
                $_SESSION['Bestellung']->fGuthabenGenutzt,
                $_SESSION['Kunde']->fGuthaben
            )
            && $_SESSION['Bestellung']->GuthabenNutzen == 1
            && $_SESSION['Bestellung']->fGuthabenGenutzt > 0
            && $_SESSION['Kunde']->fGuthaben > 0
        ) {
            // check and correct the SESSION-values for "Guthaben"
            $_SESSION['Bestellung']->GuthabenNutzen   = 1;
            $_SESSION['Bestellung']->fGuthabenGenutzt = \min(
                $_SESSION['Kunde']->fGuthaben,
                Frontend::getCart()->gibGesamtsummeWaren(true, false)
            );
            $total                                   -= $_SESSION['Bestellung']->fGuthabenGenutzt * $conversionFactor;
        }
        $total /= $conversionFactor;
        $this->useSummationRounding();

        return Cart::roundOptionalCurrency($total, $this->Waehrung ?? Frontend::getCurrency());
    }

    /**
     * Gibt gesamte Warenkorbsumme eines positionstyps zurueck.
     * @param int[]  $types
     * @param bool   $gross
     * @param string $iso
     * @param bool   $excludeShippingCostAttributes
     * @return float|int
     */
    public function gibGesamtsummeWarenExt(
        array $types,
        bool $gross = false,
        bool $excludeShippingCostAttributes = false,
        string $iso = ''
    ) {
        if (!\is_array($types)) {
            return 0;
        }
        $total = 0;
        foreach ($this->PositionenArr as $item) {
            if (\in_array($item->nPosTyp, $types, true)
                && $item->isUsedForShippingCostCalculation($iso, $excludeShippingCostAttributes)
            ) {
                if ($gross) {
                    $total += $item->fPreis * $item->nAnzahl * ((100 + Tax::getSalesTax($item->kSteuerklasse)) / 100);
                } else {
                    $total += $item->fPreis * $item->nAnzahl;
                }
            }
        }
        if ($gross) {
            $total = \round($total, 2);
        }
        $this->useSummationRounding();

        return Cart::roundOptionalCurrency($total, $this->Waehrung ?? Frontend::getCurrency());
    }

    /**
     * Gibt gesamte Warenkorbsumme ohne bestimmte Positionstypen zurueck.
     * @param int[] $types
     * @param bool  $gross
     * @return float|int
     */
    public function gibGesamtsummeWarenOhne(array $types, bool $gross = false)
    {
        if (!\is_array($types)) {
            return 0;
        }
        $total    = 0;
        $currency = $this->Waehrung ?? Frontend::getCurrency();
        $factor   = $currency->getConversionFactor();
        foreach ($this->PositionenArr as $item) {
            if (!\in_array($item->nPosTyp, $types)) {
                if ($gross) {
                    $total += $item->fPreis * $factor * $item->nAnzahl *
                        ((100 + Tax::getSalesTax($item->kSteuerklasse)) / 100);
                } else {
                    $total += $item->fPreis * $factor * $item->nAnzahl;
                }
            }
        }
        if ($gross) {
            $total = \round($total, 2);
        }

        return $total / $factor;
    }

    /**
     * @deprecated since 5.0.0 - use WarenkorbHelper::roundOptionalCurrency instead
     * @param float|int $total
     * @return float
     */
    public function optionaleRundung($total)
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return Cart::roundOptionalCurrency($total, $this->Waehrung ?? Frontend::getCurrency());
    }

    /**
     * @return $this
     */
    public function berechnePositionenUst(): self
    {
        foreach ($this->PositionenArr as $item) {
            $item->setzeGesamtpreisLocalized();
        }

        return $this;
    }

    /**
     * Gibt gesamte Warenkorbsumme lokalisiert als array zurueck.
     *
     * @return string[] - Gesamtsumme des Warenkorb
     */
    public function gibGesamtsummeWarenLocalized(): array
    {
        $sum    = [];
        $sum[0] = Preise::getLocalizedPriceString($this->gibGesamtsummeWaren(true));
        $sum[1] = Preise::getLocalizedPriceString($this->gibGesamtsummeWaren());

        return $sum;
    }

    /**
     * Entfernt Positionen mit nAnzahl 0 im Warenkorb
     *
     * @return $this
     */
    public function loescheNullPositionen(): self
    {
        foreach ($this->PositionenArr as $i => $item) {
            if ($item->nAnzahl <= 0) {
                unset($this->PositionenArr[$i]);
            }
        }
        $this->PositionenArr = \array_merge($this->PositionenArr);

        return $this;
    }

    /**
     * schaut, ob eine Position dieses Typs enthalten ist
     *
     * @param int $type
     * @return bool
     */
    public function posTypEnthalten(int $type): bool
    {
        return some($this->PositionenArr, function ($e) use ($type) {
            return (int)$e->nPosTyp === $type;
        });
    }

    /**
     * @return array
     */
    public function gibSteuerpositionen(): array
    {
        $taxRates = [];
        $taxItems = [];
        foreach ($this->PositionenArr as $item) {
            if ($item->kSteuerklasse > 0) {
                $ust = Tax::getSalesTax($item->kSteuerklasse);
                if (!\in_array($ust, $taxRates)) {
                    $taxRates[] = $ust;
                }
            }
        }
        \sort($taxRates);
        foreach ($this->PositionenArr as $item) {
            if ($item->kSteuerklasse <= 0) {
                continue;
            }
            $ust = Tax::getSalesTax($item->kSteuerklasse);
            if ($ust > 0) {
                $idx = \array_search($ust, $taxRates);
                if (!isset($taxItems[$idx]->fBetrag)) {
                    $taxItems[$idx]                  = new stdClass();
                    $taxItems[$idx]->cName           = \lang_steuerposition(
                        $ust,
                        Frontend::getCustomerGroup()->isMerchant()
                    );
                    $taxItems[$idx]->fUst            = $ust;
                    $taxItems[$idx]->fBetrag         = ($item->fPreis * $item->nAnzahl * $ust) / 100.0;
                    $taxItems[$idx]->cPreisLocalized = Preise::getLocalizedPriceString($taxItems[$idx]->fBetrag);
                } else {
                    $taxItems[$idx]->fBetrag        += ($item->fPreis * $item->nAnzahl * $ust) / 100.0;
                    $taxItems[$idx]->cPreisLocalized = Preise::getLocalizedPriceString($taxItems[$idx]->fBetrag);
                }
            }
        }

        return $taxItems;
    }

    /**
     * @return $this
     */
    public function setzeVersandfreiKupon(): self
    {
        foreach ($this->PositionenArr as $i => $item) {
            if ($item->nPosTyp === \C_WARENKORBPOS_TYP_VERSANDPOS) {
                $item->fPreisEinzelNetto = 0.0;
                $item->fPreis            = 0.0;
                $item->setzeGesamtpreisLocalized();
                break;
            }
        }

        return $this;
    }

    /**
     * geht alle Positionen durch, korrigiert Lagerbestaende und entfernt Positionen, die nicht mehr vorraetig sind
     *
     * @return $this
     */
    public function pruefeLagerbestaende(): self
    {
        $redirect      = false;
        $depAmount     = $this->getAllDependentAmount(true);
        $reservedStock = [];

        foreach ($this->PositionenArr as $i => $item) {
            if ($item->kArtikel <= 0
                || $item->Artikel->cLagerBeachten !== 'Y'
                || $item->Artikel->cLagerKleinerNull === 'Y'
            ) {
                continue;
            }
            // Lagerbestand beachten und keine Überverkäufe möglich
            if (isset($item->WarenkorbPosEigenschaftArr)
                && !$item->Artikel->kVaterArtikel
                && !$item->Artikel->nIstVater
                && $item->Artikel->cLagerVariation === 'Y'
                && \count($item->WarenkorbPosEigenschaftArr) > 0
            ) {
                // Position mit Variationen, Lagerbestand in Variationen wird beachtet
                foreach ($item->WarenkorbPosEigenschaftArr as $oWarenkorbPosEigenschaft) {
                    if ($oWarenkorbPosEigenschaft->kEigenschaftWert > 0 && $item->nAnzahl > 0) {
                        //schaue in DB, ob Lagerbestand ausreichend
                        $stock = Shop::Container()->getDB()->query(
                            'SELECT kEigenschaftWert, fLagerbestand >= ' . $item->nAnzahl .
                            ' AS bAusreichend, fLagerbestand
                                FROM teigenschaftwert
                                WHERE kEigenschaftWert = ' . (int)$oWarenkorbPosEigenschaft->kEigenschaftWert,
                            ReturnType::SINGLE_OBJECT
                        );
                        if (isset($stock->kEigenschaftWert) && $stock->kEigenschaftWert > 0 && !$stock->bAusreichend) {
                            if ($stock->fLagerbestand > 0) {
                                $item->nAnzahl = $stock->fLagerbestand;
                            } else {
                                unset($this->PositionenArr[$i]);
                            }
                            $redirect = true;
                        }
                    }
                }
            } else {
                // Position ohne Variationen bzw. Variationen ohne eigenen Lagerbestand
                // schaue in DB, ob Lagerbestand ausreichend
                $depProducts = $item->Artikel->getAllDependentProducts(true);
                $depStock    = Shop::Container()->getDB()->query(
                    'SELECT kArtikel, fLagerbestand
                        FROM tartikel
                        WHERE kArtikel IN (' . \implode(', ', \array_keys($depProducts)) . ')',
                    ReturnType::ARRAY_OF_OBJECTS
                );

                foreach ($depStock as $productStock) {
                    $productID = (int)$productStock->kArtikel;

                    if ($depProducts[$productID]->product->fPackeinheit * $depAmount[$productID]
                        > $productStock->fLagerbestand
                    ) {
                        $newAmount = \floor(($productStock->fLagerbestand
                                - ($reservedStock[$productID] ?? 0))
                            / $depProducts[$productID]->product->fPackeinheit
                            / $depProducts[$productID]->stockFactor);

                        if ($newAmount > $item->nAnzahl) {
                            $newAmount = $item->nAnzahl;
                        }

                        if ($newAmount > 0) {
                            $item->nAnzahl = $newAmount;
                        } else {
                            unset($this->PositionenArr[$i]);
                        }

                        $reservedStock[$productID] = ($reservedStock[$productID] ?? 0)
                            + $newAmount
                            * $depProducts[$productID]->product->fPackeinheit * $depProducts[$productID]->stockFactor;

                        $depAmount = $this->getAllDependentAmount(true);
                        $redirect  = true;
                    }
                }
            }
        }

        if ($redirect) {
            $this->setzePositionsPreise();
            $linkHelper = Shop::Container()->getLinkService();
            \header('Location: ' . $linkHelper->getStaticRoute('warenkorb.php') . '?fillOut=10', true, 303);
            exit;
        }

        return $this;
    }

    /**
     * @param int $kWarenkorb
     * @return $this
     */
    public function loadFromDB(int $kWarenkorb): self
    {
        $obj = Shop::Container()->getDB()->select('twarenkorb', 'kWarenkorb', $kWarenkorb);
        if ($obj !== null) {
            $members = \array_keys(\get_object_vars($obj));
            foreach ($members as $member) {
                $this->$member = $obj->$member;
            }
        }

        return $this;
    }

    /**
     * @return int
     */
    public function insertInDB(): int
    {
        $obj = (object)[
            'kKunde'         => $this->kKunde,
            'kLieferadresse' => $this->kLieferadresse,
            'kZahlungsInfo'  => $this->kZahlungsInfo,
        ];
        if (!isset($obj->kZahlungsInfo) || $obj->kZahlungsInfo === '') {
            $obj->kZahlungsInfo = 0;
        }
        $this->kWarenkorb = Shop::Container()->getDB()->insert('twarenkorb', $obj);

        return $this->kWarenkorb;
    }

    /**
     * @return int
     */
    public function updateInDB(): int
    {
        $obj = (object)[
            'kWarenkorb'     => $this->kWarenkorb,
            'kKunde'         => $this->kKunde,
            'kLieferadresse' => $this->kLieferadresse,
            'kZahlungsInfo'  => $this->kZahlungsInfo,
        ];

        return Shop::Container()->getDB()->update('twarenkorb', 'kWarenkorb', $obj->kWarenkorb, $obj);
    }

    /**
     * @return string
     */
    public function getEstimatedDeliveryTime(): string
    {
        if (!\is_array($this->PositionenArr) || \count($this->PositionenArr) === 0) {
            return '';
        }
        $longestMinDeliveryDays = 0;
        $longestMaxDeliveryDays = 0;

        /** @var WarenkorbPos $item */
        foreach ($this->PositionenArr as $item) {
            if ($item->nPosTyp !== \C_WARENKORBPOS_TYP_ARTIKEL || !$item->Artikel instanceof Artikel) {
                continue;
            }
            $item->Artikel->getDeliveryTime($_SESSION['cLieferlandISO'], $item->nAnzahl);
            WarenkorbPos::setEstimatedDelivery(
                $item,
                $item->Artikel->nMinDeliveryDays,
                $item->Artikel->nMaxDeliveryDays
            );
            if (isset($item->Artikel->nMinDeliveryDays) && $item->Artikel->nMinDeliveryDays > $longestMinDeliveryDays) {
                $longestMinDeliveryDays = $item->Artikel->nMinDeliveryDays;
            }
            if (isset($item->Artikel->nMaxDeliveryDays) && $item->Artikel->nMaxDeliveryDays > $longestMaxDeliveryDays) {
                $longestMaxDeliveryDays = $item->Artikel->nMaxDeliveryDays;
            }
        }

        return ShippingMethod::getDeliverytimeEstimationText($longestMinDeliveryDays, $longestMaxDeliveryDays);
    }

    /**
     * @return object|null
     */
    public function gibLetztenWKArtikel()
    {
        if (!\is_array($this->PositionenArr)) {
            return null;
        }
        $res        = null;
        $lastUpdate = 0;
        foreach ($this->PositionenArr as $item) {
            if ($item->nPosTyp === \C_WARENKORBPOS_TYP_ARTIKEL && $item->kKonfigitem === 0) {
                if (isset($item->nZeitLetzteAenderung) && $item->nZeitLetzteAenderung > $lastUpdate) {
                    $lastUpdate = $item->nZeitLetzteAenderung;
                    $res        = $item->Artikel;
                    Product::addVariationPictures($res, $item->variationPicturesArr);
                } elseif ($res === null) {
                    // Wenn keine nZeitLetzteAenderung gesetzt ist letztes Element des WK-Arrays nehmen
                    $res = $item->Artikel;
                }
            }
        }

        return $res;
    }

    /**
     * @param string $iso
     * @param bool $excludeShippingCostAttributes
     * @return int|float
     */
    public function getWeight(bool $excludeShippingCostAttributes = false, string $iso = '')
    {
        $weight = 0;
        foreach ($this->PositionenArr as $item) {
            if ($item->isUsedForShippingCostCalculation($iso, $excludeShippingCostAttributes)) {
                $weight += $item->fGesamtgewicht;
            }
        }

        return $weight;
    }

    /**
     * @param bool $isRedirect
     * @param bool $unique
     */
    public function redirectTo(bool $isRedirect = false, $unique = false): void
    {
        if (!$isRedirect
            && !$unique
            && !isset($_SESSION['variBoxAnzahl_arr'])
            && $this->config['global']['global_warenkorb_weiterleitung'] === 'Y'
        ) {
            $linkHelper = Shop::Container()->getLinkService();
            \header('Location: ' . $linkHelper->getStaticRoute('warenkorb.php'), true, 303);
            exit;
        }
    }

    /**
     * Unique hash to identify any basket changes
     *
     * @return string
     */
    public function getUniqueHash(): string
    {
        return \sha1(\serialize($this));
    }

    /**
     * make sure the applied coupons are still valid after removing items from the cart
     * or updating amounts
     *
     * @return bool
     */
    public function checkIfCouponIsStillValid(): bool
    {
        $isValid = true;
        if (!isset($_SESSION['Kupon']->kKupon)) {
            return $isValid;
        }
        if ($this->posTypEnthalten(\C_WARENKORBPOS_TYP_KUPON)) {
            // Kupon darf nicht im leeren Warenkorb eingelöst werden
            if (isset($_SESSION['Warenkorb']) && $this->gibAnzahlArtikelExt([\C_WARENKORBPOS_TYP_ARTIKEL]) > 0) {
                $Kupon = Shop::Container()->getDB()->select('tkupon', 'kKupon', (int)$_SESSION['Kupon']->kKupon);
                if (isset($Kupon->kKupon) && $Kupon->kKupon > 0 && $Kupon->cKuponTyp === Kupon::TYPE_STANDARD) {
                    $isValid = (\angabenKorrekt(Kupon::checkCoupon($Kupon)) === 1);
                    $this->updateCouponValue();
                } elseif (!empty($Kupon->kKupon) && $Kupon->cKuponTyp === Kupon::TYPE_SHIPPING) {
                    $isValid = true;
                } else {
                    $isValid = false;
                }
            }
            if ($isValid === false) {
                unset($_SESSION['Kupon']);
                $this->loescheSpezialPos(\C_WARENKORBPOS_TYP_KUPON)
                     ->setzePositionsPreise();
            }
        } elseif (isset($_SESSION['Kupon']->nGanzenWKRabattieren)
            && (int)$_SESSION['Kupon']->nGanzenWKRabattieren === 0
            && $_SESSION['Kupon']->cKuponTyp === Kupon::TYPE_STANDARD
            && $_SESSION['Kupon']->cWertTyp === 'prozent'
        ) {
            if (isset($_SESSION['Warenkorb']) && $this->gibAnzahlArtikelExt([\C_WARENKORBPOS_TYP_ARTIKEL]) > 0) {
                $Kupon   = Shop::Container()->getDB()->select('tkupon', 'kKupon', (int)$_SESSION['Kupon']->kKupon);
                $isValid = false;
                if (isset($Kupon->kKupon) && $Kupon->kKupon > 0 && $Kupon->cKuponTyp === Kupon::TYPE_STANDARD) {
                    $isValid = (\angabenKorrekt(Kupon::checkCoupon($Kupon)) === 1);
                }
            }
            if ($isValid === false) {
                unset($_SESSION['Kupon']);
                $this->loescheSpezialPos(\C_WARENKORBPOS_TYP_KUPON)
                     ->setzePositionsPreise();
            }
        } elseif (isset($_SESSION['Kupon']->nGanzenWKRabattieren)
            && (int)$_SESSION['Kupon']->nGanzenWKRabattieren === 0
            && $_SESSION['Kupon']->cKuponTyp === Kupon::TYPE_STANDARD
        ) {
            //we have a coupon in the current session but none in the cart.
            //this happens with coupons tied to special articles that are no longer valid.
            unset($_SESSION['Kupon']);
        }

        return $isValid;
    }

    /**
     * update coupon value to avoid negative orders or coupon values under predefined value
     */
    public function updateCouponValue(): void
    {
        if (!isset($_SESSION['Kupon']) || $_SESSION['Kupon']->cWertTyp !== 'festpreis') {
            return;
        }
        $coupon        = $_SESSION['Kupon'];
        $maxPreisKupon = $coupon->fWert;
        if ($coupon->fWert > $this->gibGesamtsummeWarenExt([\C_WARENKORBPOS_TYP_ARTIKEL], true)) {
            $maxPreisKupon = $this->gibGesamtsummeWarenExt([\C_WARENKORBPOS_TYP_ARTIKEL], true);
        }
        if ((int)$coupon->nGanzenWKRabattieren === 0
            && $coupon->fWert > \gibGesamtsummeKuponartikelImWarenkorb($coupon, $this->PositionenArr)
        ) {
            $maxPreisKupon = \gibGesamtsummeKuponartikelImWarenkorb($coupon, $this->PositionenArr);
        }
        $specialPosition        = new stdClass();
        $specialPosition->cName = [];
        foreach ($_SESSION['Sprachen'] as $Sprache) {
            $localized                              = Shop::Container()->getDB()->select(
                'tkuponsprache',
                'kKupon',
                (int)$coupon->kKupon,
                'cISOSprache',
                $Sprache->cISO,
                null,
                null,
                false,
                'cName'
            );
            $specialPosition->cName[$Sprache->cISO] = $localized->cName;
        }
        $this->loescheSpezialPos(\C_WARENKORBPOS_TYP_KUPON);
        $this->erstelleSpezialPos(
            $specialPosition->cName,
            1,
            $maxPreisKupon * -1,
            $coupon->kSteuerklasse,
            \C_WARENKORBPOS_TYP_KUPON
        );
    }

    /**
     * use summation rounding to even out discrepancies between total basket sum and sum of basket position totals
     *
     * @param int $precision
     */
    public function useSummationRounding(int $precision = 2): void
    {
        $cumulatedDelta    = 0;
        $cumulatedDeltaNet = 0;
        foreach (Frontend::getCurrencies() as $currency) {
            $currencyName = $currency->getName();
            foreach ($this->PositionenArr as $i => $item) {
                $grossAmount        = Tax::getGross(
                    $item->fPreis * $item->nAnzahl,
                    Tax::getSalesTax($item->kSteuerklasse),
                    12
                );
                $netAmount          = $item->fPreis * $item->nAnzahl;
                $roundedGrossAmount = Tax::getGross(
                    $item->fPreis * $item->nAnzahl + $cumulatedDelta,
                    Tax::getSalesTax($item->kSteuerklasse),
                    $precision
                );
                $roundedNetAmount   = \round($item->fPreis * $item->nAnzahl + $cumulatedDeltaNet, $precision);

                if ($i !== 0 && $item->nPosTyp === \C_WARENKORBPOS_TYP_ARTIKEL) {
                    if ($grossAmount != 0) {
                        $item->cGesamtpreisLocalized[0][$currencyName] = Preise::getLocalizedPriceString(
                            $roundedGrossAmount,
                            $currency
                        );
                    }
                    if ($netAmount != 0) {
                        $item->cGesamtpreisLocalized[1][$currencyName] = Preise::getLocalizedPriceString(
                            $roundedNetAmount,
                            $currency
                        );
                    }
                }
                $cumulatedDelta    += ($grossAmount - $roundedGrossAmount);
                $cumulatedDeltaNet += ($netAmount - $roundedNetAmount);
            }
        }
    }

    /**
     * @param Warenkorb $cart
     * @return string
     */
    public static function getChecksum($cart): string
    {
        $checks = [
            'EstimatedDelivery' => $cart->cEstimatedDelivery ?? '',
            'PositionenCount'   => \count($cart->PositionenArr ?? []),
            'PositionenArr'     => [],
        ];

        if (\is_array($cart->PositionenArr)) {
            foreach ($cart->PositionenArr as $wkPos) {
                $checks['PositionenArr'][] = \md5(\serialize([
                    'kArtikel'          => $wkPos->kArtikel ?? 0,
                    'nAnzahl'           => $wkPos->nAnzahl ?? 0,
                    'kVersandklasse'    => $wkPos->kVersandklasse ?? 0,
                    'nPosTyp'           => $wkPos->nPosTyp ?? 0,
                    'fPreisEinzelNetto' => $wkPos->fPreisEinzelNetto ?? 0.0,
                    'fPreis'            => $wkPos->fPreis ?? 0.0,
                    'cHinweis'          => $wkPos->cHinweis ?? '',
                ]));
            }
            \sort($checks['PositionenArr']);
        }

        return \md5(\serialize($checks));
    }

    /**
     * refresh internal wk-checksum
     * @param Warenkorb|object $cart
     */
    public static function refreshChecksum($cart): void
    {
        $cart->cChecksumme = self::getChecksum($cart);
    }

    /**
     * Check if basket has digital products.
     *
     * @return bool
     */
    public function hasDigitalProducts(): bool
    {
        return Download::hasDownloads($this);
    }

    /**
     * @return null|Versandart - cheapest shipping except shippings that offer cash payment
     */
    public function getFavourableShipping(): ?Versandart
    {
        if ((!empty($_SESSION['Versandart']->kVersandart) && isset($_SESSION['Versandart']->nMinLiefertage))
            || empty($_SESSION['Warenkorb']->PositionenArr)
        ) {
            return null;
        }

        $customerGroupSQL = '';
        $customerGroupID  = $_SESSION['Kunde']->kKundengruppe ?? 0;

        if ($customerGroupID > 0) {
            $countryCode      = $_SESSION['Kunde']->cLand;
            $customerGroupSQL = " OR FIND_IN_SET('" . $customerGroupID . "', REPLACE(va.cKundengruppen, ';', ',')) > 0";
        } else {
            $countryCode = $_SESSION['cLieferlandISO'];
        }
        // if nothing changed, return cached shipping-object
        if ($this->oFavourableShipping !== null
            && $this->oFavourableShipping->cCountryCode === $_SESSION['cLieferlandISO']
        ) {
            return $this->oFavourableShipping;
        }

        $maxPrices       = 0;
        $totalWeight     = 0;
        $shippingClasses = [];

        foreach ($this->PositionenArr as $item) {
            $shippingClasses[] = $item->kVersandklasse;
            $totalWeight      += $item->fGesamtgewicht;
            $maxPrices        += $item->Artikel->Preise->fVKNetto ?? 0;
        }

        // cheapest shipping except shippings that offer cash payment
        $shipping = Shop::Container()->getDB()->query(
            "SELECT va.kVersandart, IF(vas.fPreis IS NOT NULL, vas.fPreis, va.fPreis) AS minPrice, va.nSort
                FROM tversandart va
                LEFT JOIN tversandartstaffel vas
                    ON vas.kVersandart = va.kVersandart
                WHERE cIgnoreShippingProposal != 'Y'
                AND va.cLaender LIKE '%" . $countryCode . "%'
                AND (va.cVersandklassen = '-1'
                    OR va.cVersandklassen IN (" . \implode(',', $shippingClasses) . "))
                AND (va.cKundengruppen = '-1' " . $customerGroupSQL . ')
                AND va.kVersandart NOT IN (
                    SELECT vaza.kVersandart
                        FROM tversandartzahlungsart vaza
                        WHERE kZahlungsart = 6)
                AND (
                    va.kVersandberechnung = 1 OR va.kVersandberechnung = 4
                    OR ( va.kVersandberechnung = 2 AND vas.fBis > 0 AND ' . $totalWeight . ' <= vas.fBis )
                    OR ( va.kVersandberechnung = 3 AND vas.fBis > 0 AND ' . $maxPrices . ' <= vas.fBis )
                    )
                ORDER BY minPrice, nSort ASC LIMIT 1',
            ReturnType::SINGLE_OBJECT
        );

        $this->oFavourableShipping = null;
        if (isset($shipping->kVersandart)) {
            $method               = new Versandart($shipping->kVersandart);
            $method->cCountryCode = $countryCode;

            if ($method->eSteuer === 'brutto') {
                $method->cPriceLocalized[0] = Preise::getLocalizedPriceString($method->fPreis);
                $method->cPriceLocalized[1] = Preise::getLocalizedPriceString(
                    Tax::getNet(
                        $method->fPreis,
                        $_SESSION['Steuersatz'][$this->gibVersandkostenSteuerklasse()]
                    )
                );
            } else {
                $method->cPriceLocalized[0] = Preise::getLocalizedPriceString(
                    Tax::getGross(
                        $method->fPreis,
                        $_SESSION['Steuersatz'][$this->gibVersandkostenSteuerklasse()]
                    )
                );
                $method->cPriceLocalized[1] = Preise::getLocalizedPriceString($method->fPreis);
            }
            $this->oFavourableShipping = $method;
        }

        return $this->oFavourableShipping;
    }
}
