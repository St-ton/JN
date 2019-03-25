<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Cart;

use function Functional\select;
use function Functional\some;
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

        foreach ($this->PositionenArr as $key => $pos) {
            if (\is_array($excludePos) && \in_array($key, $excludePos)) {
                continue;
            }

            if (!empty($pos->Artikel)
                && (!$onlyStockRelevant
                    || ($pos->Artikel->cLagerBeachten === 'Y' && $pos->Artikel->cLagerKleinerNull !== 'Y'))
            ) {
                $depProducts = $pos->Artikel->getAllDependentProducts($onlyStockRelevant);

                foreach ($depProducts as $productID => $item) {
                    if (isset($depAmount[$productID])) {
                        $depAmount[$productID] += ($pos->nAnzahl * $item->stockFactor);
                    } else {
                        $depAmount[$productID] = $pos->nAnzahl * $item->stockFactor;
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
     * @param int   $position
     * @param float $amount
     * @return float
     */
    public function getMaxAvailableAmount(int $position, float $amount): float
    {
        foreach ($this->PositionenArr[$position]->Artikel->getAllDependentProducts(true) as $dependent) {
            $depProduct = $dependent->product;
            $depAmount  = $this->getDependentAmount($depProduct->kArtikel, true, [$position]);
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
        foreach ($this->PositionenArr as $i => $position) {
            $position->nPosTyp = (int)$position->nPosTyp;
            $delete            = false;
            if (!empty($position->Artikel)) {
                if (isset(
                    $position->Artikel->fLagerbestand,
                    $position->Artikel->cLagerBeachten,
                    $position->Artikel->cLagerKleinerNull,
                    $position->Artikel->cLagerVariation
                )
                    && $position->Artikel->fLagerbestand <= 0
                    && $position->Artikel->cLagerBeachten === 'Y'
                    && $position->Artikel->cLagerKleinerNull !== 'Y'
                    && $position->Artikel->cLagerVariation !== 'Y'
                ) {
                    $delete = true;
                } elseif (empty($position->kKonfigitem)
                    && $position->fPreisEinzelNetto == 0
                    && !$position->Artikel->bHasKonfig
                    && $position->nPosTyp !== \C_WARENKORBPOS_TYP_GRATISGESCHENK
                    && isset($position->fPreisEinzelNetto, $this->config['global']['global_preis0'])
                    && $this->config['global']['global_preis0'] === 'N'
                ) {
                    $delete = true;
                } elseif (!empty($position->Artikel->FunktionsAttribute[\FKT_ATTRIBUT_UNVERKAEUFLICH])) {
                    $delete = true;
                } else {
                    $delete = (Shop::Container()->getDB()->select(
                        'tartikel',
                        'kArtikel',
                        $position->kArtikel
                    ) === null);
                }

                \executeHook(\HOOK_WARENKORB_CLASS_LOESCHEDEAKTIVIERTEPOS, [
                    'oPosition' => $position,
                    'delete'    => &$delete
                ]);
            }
            if ($delete) {
                self::addDeletedPosition($position);
                unset($this->PositionenArr[$i]);
            }
        }
        $this->PositionenArr = \array_merge($this->PositionenArr);

        return $this;
    }

    /**
     * @param object $position
     */
    public static function addUpdatedPosition($position): void
    {
        self::$updatedPositions[] = $position;
    }

    /**
     * @param object $position
     */
    public static function addDeletedPosition($position): void
    {
        self::$deletedPositions[] = $position;
    }

    /**
     * fuegt eine neue Position hinzu
     * @param int         $kArtikel ArtikelKey
     * @param int         $anzahl   Anzahl des Artikel fuer die neue Position
     * @param array       $attributeValues
     * @param int         $nPosTyp
     * @param string|bool $cUnique
     * @param int         $kKonfigitem
     * @param bool        $setzePositionsPreise
     * @param string      $cResponsibility
     * @return $this
     */
    public function fuegeEin(
        int $kArtikel,
        $anzahl,
        array $attributeValues,
        int $nPosTyp = \C_WARENKORBPOS_TYP_ARTIKEL,
        $cUnique = false,
        int $kKonfigitem = 0,
        bool $setzePositionsPreise = true,
        string $cResponsibility = 'core'
    ): self {
        $iso = Shop::getLanguageCode();
        //toDo schaue, ob diese Pos nicht markiert werden muesste, wenn anzahl>lager gekauft wird
        //schaue, ob es nicht schon Positionen mit diesem Artikel gibt
        foreach ($this->PositionenArr as $i => $position) {
            if (!(isset($position->Artikel->kArtikel)
                && $position->Artikel->kArtikel == $kArtikel
                && $position->nPosTyp == $nPosTyp
                && !$position->cUnique)
            ) {
                continue;
            }
            $neuePos = false;
            // hat diese Position schon einen EigenschaftWert ausgewaehlt
            // und ist das dieselbe Eigenschaft wie ausgewaehlt?
            if (!$cUnique) {
                foreach ($position->WarenkorbPosEigenschaftArr as $wke) {
                    foreach ($attributeValues as $oEigenschaftwerte) {
                        // gleiche Eigenschaft suchen
                        if ($oEigenschaftwerte->kEigenschaft != $wke->kEigenschaft) {
                            continue;
                        }
                        // ist es ein Freifeld mit unterschiedlichem Inhalt
                        // oder eine Eigenschaft mit unterschiedlichem Wert?
                        if (($wke->kEigenschaftWert > 0
                                && $wke->kEigenschaftWert != $oEigenschaftwerte->kEigenschaftWert)
                            || (($wke->cTyp === 'FREIFELD' || $wke->cTyp === 'PFLICHT-FREIFELD')
                                && $wke->cEigenschaftWertName[$iso] != $oEigenschaftwerte->cFreifeldWert)
                        ) {
                            $neuePos = true;
                            break;
                        }
                    }
                }
                if (!$neuePos && !$cUnique) {
                    //erhoehe Anzahl dieser Position
                    $position->nZeitLetzteAenderung = \time();
                    $position->nAnzahl             += $anzahl;
                    if ($setzePositionsPreise === true) {
                        $this->setzePositionsPreise();
                    }
                    \executeHook(\HOOK_WARENKORB_CLASS_FUEGEEIN, [
                        'kArtikel'      => $kArtikel,
                        'oPosition_arr' => &$this->PositionenArr,
                        'nAnzahl'       => &$anzahl,
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
        $pos          = new WarenkorbPos();
        $pos->Artikel = new Artikel();
        $pos->Artikel->fuelleArtikel($kArtikel, $options);
        $pos->nAnzahl           = $anzahl;
        $pos->kArtikel          = $pos->Artikel->kArtikel;
        $pos->kVersandklasse    = $pos->Artikel->kVersandklasse;
        $pos->kSteuerklasse     = $pos->Artikel->kSteuerklasse;
        $pos->fPreisEinzelNetto = $pos->Artikel->gibPreis($pos->nAnzahl, []);
        $pos->fPreis            = $pos->Artikel->gibPreis($anzahl, []);
        $pos->cArtNr            = $pos->Artikel->cArtNr;
        $pos->nPosTyp           = $nPosTyp;
        $pos->cEinheit          = $pos->Artikel->cEinheit;
        $pos->cUnique           = $cUnique;
        $pos->cResponsibility   = $cResponsibility;
        $pos->kKonfigitem       = $kKonfigitem;
        $pos->setzeGesamtpreisLocalized();
        $cLieferstatus_StdSprache = $pos->Artikel->cLieferstatus;
        $pos->cName               = [];
        $pos->cLieferstatus       = [];

        $db = Shop::Container()->getDB();

        foreach (Frontend::getLanguages() as $lang) {
            $pos->cName[$lang->cISO]         = $pos->Artikel->cName;
            $pos->cLieferstatus[$lang->cISO] = $cLieferstatus_StdSprache;
            if ($lang->cStandard === 'Y') {
                $localized = $db->select(
                    'tartikel',
                    'kArtikel',
                    (int)$pos->kArtikel,
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
                    (int)$pos->kArtikel,
                    'kSprache',
                    (int)$lang->kSprache,
                    null,
                    null,
                    false,
                    'cName'
                );
            }
            //Wenn fuer die gewaehlte Sprache kein Name vorhanden ist dann StdSprache nehmen
            $pos->cName[$lang->cISO] = (isset($localized->cName) && \mb_strlen(\trim($localized->cName)) > 0)
                ? $localized->cName
                : $pos->Artikel->cName;
            $lieferstatus_spr        = $db->select(
                'tlieferstatus',
                'kLieferstatus',
                (int)($pos->Artikel->kLieferstatus ?? 0),
                'kSprache',
                (int)$lang->kSprache
            );
            if (!empty($lieferstatus_spr->cName)) {
                $pos->cLieferstatus[$lang->cISO] = $lieferstatus_spr->cName;
            }
        }
        // Grundpreise bei Staffelpreisen
        if (isset($pos->Artikel->fVPEWert) && $pos->Artikel->fVPEWert > 0) {
            $nLast = 0;
            for ($j = 1; $j <= 5; $j++) {
                $cStaffel = 'nAnzahl' . $j;
                if (isset($pos->Artikel->Preise->$cStaffel)
                    && $pos->Artikel->Preise->$cStaffel > 0
                    && $pos->Artikel->Preise->$cStaffel <= $pos->nAnzahl
                ) {
                    $nLast = $j;
                }
            }
            if ($nLast > 0) {
                $cStaffel = 'fPreis' . $nLast;
                $pos->Artikel->baueVPE($pos->Artikel->Preise->$cStaffel);
            } else {
                $pos->Artikel->baueVPE();
            }
        }
        $this->setzeKonfig($pos, false);
        if (\is_array($pos->Artikel->Variationen) && \count($pos->Artikel->Variationen) > 0) {
            //foreach ($ewerte as $eWert)
            foreach ($pos->Artikel->Variationen as $eWert) {
                $eWert->kEigenschaft = (int)$eWert->kEigenschaft;
                foreach ($attributeValues as $oEigenschaftwerte) {
                    $oEigenschaftwerte->kEigenschaft = (int)$oEigenschaftwerte->kEigenschaft;
                    //gleiche Eigenschaft suchen
                    if ($oEigenschaftwerte->kEigenschaft !== $eWert->kEigenschaft) {
                        continue;
                    }
                    if ($eWert->cTyp === 'FREIFELD' || $eWert->cTyp === 'PFLICHT-FREIFELD') {
                        $pos->setzeVariationsWert($eWert->kEigenschaft, 0, $oEigenschaftwerte->cFreifeldWert);
                    } elseif ($oEigenschaftwerte->kEigenschaftWert > 0) {
                        $EigenschaftWert = new EigenschaftWert($oEigenschaftwerte->kEigenschaftWert);
                        $Eigenschaft     = new Eigenschaft($EigenschaftWert->kEigenschaft);
                        // Varkombi Kind?
                        if ($pos->Artikel->kVaterArtikel > 0) {
                            if ($Eigenschaft->kArtikel == $pos->Artikel->kVaterArtikel) {
                                $pos->setzeVariationsWert(
                                    $EigenschaftWert->kEigenschaft,
                                    $EigenschaftWert->kEigenschaftWert
                                );
                            }
                        } elseif ($Eigenschaft->kArtikel == $pos->kArtikel) {
                            // Variationswert hat eigene Artikelnummer
                            // und der Artikel hat nur eine Dimension als Variation?
                            if (isset($EigenschaftWert->cArtNr)
                                && \count($pos->Artikel->Variationen) === 1
                                && \mb_strlen($EigenschaftWert->cArtNr) > 0
                            ) {
                                $pos->cArtNr          = $EigenschaftWert->cArtNr;
                                $pos->Artikel->cArtNr = $EigenschaftWert->cArtNr;
                            }

                            $pos->setzeVariationsWert(
                                $EigenschaftWert->kEigenschaft,
                                $EigenschaftWert->kEigenschaftWert
                            );
                            // aktuellen Eigenschaftswert mit Bild ermitteln
                            // und Variationsbild an der Position speichern
                            $kEigenschaftWert = $EigenschaftWert->kEigenschaftWert;
                            $oVariationWert   = \current(
                                \array_filter(
                                    $eWert->Werte,
                                    function ($item) use ($kEigenschaftWert) {
                                        return $item->kEigenschaftWert === $kEigenschaftWert
                                            && !empty($item->cPfadNormal);
                                    }
                                )
                            );

                            if ($oVariationWert !== false) {
                                Cart::setVariationPicture($pos, $oVariationWert);
                            }
                        }
                    }
                }
            }
        }

        $pos->fGesamtgewicht       = $pos->gibGesamtgewicht();
        $pos->nZeitLetzteAenderung = \time();

        switch ($pos->nPosTyp) {
            case \C_WARENKORBPOS_TYP_GRATISGESCHENK:
                $pos->fPreisEinzelNetto = 0;
                $pos->fPreis            = 0;
                $pos->setzeGesamtpreisLocalized();
                break;

            case \C_WARENKORBPOS_TYP_VERSANDPOS:
                if (isset($_SESSION['Versandart']->angezeigterHinweistext[Shop::getLanguageCode()])
                    && \mb_strlen($_SESSION['Versandart']->angezeigterHinweistext[Shop::getLanguageCode()]) > 0
                ) {
                    $pos->cHinweis = $_SESSION['Versandart']->angezeigterHinweistext[Shop::getLanguageCode()];
                }
                break;

            case \C_WARENKORBPOS_TYP_ZAHLUNGSART:
                if (isset($_SESSION['Zahlungsart']->cHinweisText)) {
                    $pos->cHinweis = $_SESSION['Zahlungsart']->cHinweisText;
                }
                break;
        }
        unset($pos->Artikel->oKonfig_arr); //#7482
        $this->PositionenArr[] = $pos;
        if ($setzePositionsPreise === true) {
            $this->setzePositionsPreise();
        }
        $this->updateCouponValue();
        $this->sortShippingPosition();

        \executeHook(\HOOK_WARENKORB_CLASS_FUEGEEIN, [
            'kArtikel'      => $kArtikel,
            'oPosition_arr' => &$this->PositionenArr,
            'nAnzahl'       => &$anzahl,
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
        $oPositionVersand = null;
        $i                = 0;
        foreach ($this->PositionenArr as $position) {
            $position->nPosTyp = (int)$position->nPosTyp;
            if ($position->nPosTyp === \C_WARENKORBPOS_TYP_VERSANDPOS) {
                $oPositionVersand = $position;
                break;
            }
            $i++;
        }

        if ($oPositionVersand !== null) {
            unset($this->PositionenArr[$i]);
            $this->PositionenArr   = \array_merge($this->PositionenArr);
            $this->PositionenArr[] = $oPositionVersand;
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
     * @param int $typ
     * @return $this
     */
    public function loescheSpezialPos(int $typ): self
    {
        if (\count($this->PositionenArr) === 0) {
            return $this;
        }
        foreach ($this->PositionenArr as $i => $position) {
            if (isset($position->nPosTyp) && (int)$position->nPosTyp === $typ) {
                unset($this->PositionenArr[$i]);
            }
        }
        $this->PositionenArr = \array_merge($this->PositionenArr);
        if (!empty($_POST['Kuponcode']) && $typ === \C_WARENKORBPOS_TYP_KUPON) {
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
     * @param string       $anzahl        Positionsanzahl
     * @param string       $preis         Positionspreis
     * @param string       $kSteuerklasse Positionsmwst
     * @param int          $typ           Positionstyp
     * @param bool         $delSamePosType
     * @param bool         $brutto
     * @param string       $hinweis
     * @param string|bool  $cUnique
     * @param int          $kKonfigitem
     * @param int          $kArtikel
     * @return $this
     */
    public function erstelleSpezialPos(
        $name,
        $anzahl,
        $preis,
        $kSteuerklasse,
        int $typ,
        bool $delSamePosType = true,
        bool $brutto = true,
        string $hinweis = '',
        $cUnique = false,
        int $kKonfigitem = 0,
        int $kArtikel = 0
    ): self {
        if ($delSamePosType) {
            $this->loescheSpezialPos($typ);
        }
        $pos                = new WarenkorbPos();
        $pos->nAnzahl       = $anzahl;
        $pos->nAnzahlEinzel = $anzahl;
        $pos->kArtikel      = 0;
        $pos->kSteuerklasse = $kSteuerklasse;
        $pos->fPreis        = $preis;
        $pos->cUnique       = $cUnique;
        $pos->kKonfigitem   = $kKonfigitem;
        $pos->kArtikel      = $kArtikel;
        //fixes #4967
        if (\is_object($_SESSION['Kundengruppe']) && Frontend::getCustomerGroup()->isMerchant()) {
            if ($brutto) {
                $pos->fPreis = $preis / (100 + Tax::getSalesTax($kSteuerklasse)) * 100.0;
            }
            //round net price
            $pos->fPreis = \round($pos->fPreis, 2);
        } elseif ($brutto) {
            //calculate net price based on rounded gross price
            $pos->fPreis = \round($preis, 2) / (100 + Tax::getSalesTax($kSteuerklasse)) * 100.0;
        } else {
            //calculate rounded gross price then calculate net price again.
            $pos->fPreis = \round($preis * (100 + Tax::getSalesTax($kSteuerklasse)) / 100, 2) /
                (100 + Tax::getSalesTax($kSteuerklasse)) * 100.0;
        }

        $pos->fPreisEinzelNetto = $pos->fPreis;
        if ($typ === \C_WARENKORBPOS_TYP_KUPON && isset($name->cName)) {
            $pos->cName = \is_array($name->cName)
                ? $name->cName
                : [Shop::getLanguageCode() => $name->cName];
            if (isset($name->cArticleNameAffix, $name->discountForArticle)) {
                $pos->cArticleNameAffix  = $name->cArticleNameAffix;
                $pos->discountForArticle = $name->discountForArticle;
            }
        } else {
            $pos->cName = \is_array($name)
                ? $name
                : [Shop::getLanguageCode() => $name];
        }
        $pos->nPosTyp  = $typ;
        $pos->cHinweis = $hinweis;
        $nOffset       = \array_push($this->PositionenArr, $pos);
        $pos           = $this->PositionenArr[$nOffset - 1];
        foreach (Frontend::getCurrencies() as $currency) {
            $currencyName = $currency->getName();
            // Standardartikel
            $pos->cGesamtpreisLocalized[0][$currencyName] = Preise::getLocalizedPriceString(
                Tax::getGross(
                    $pos->fPreis * $pos->nAnzahl,
                    Tax::getSalesTax($pos->kSteuerklasse)
                ),
                $currency
            );
            $pos->cGesamtpreisLocalized[1][$currencyName] = Preise::getLocalizedPriceString(
                $pos->fPreis * $pos->nAnzahl,
                $currency
            );
            $pos->cEinzelpreisLocalized[0][$currencyName] = Preise::getLocalizedPriceString(
                Tax::getGross($pos->fPreis, Tax::getSalesTax($pos->kSteuerklasse)),
                $currency
            );
            $pos->cEinzelpreisLocalized[1][$currencyName] = Preise::getLocalizedPriceString(
                $pos->fPreis,
                $currency
            );

            // Konfigurationsartikel: mapto: 9a87wdgad
            if ((int)$pos->kKonfigitem > 0
                && \is_string($pos->cUnique)
                && !empty($pos->cUnique)
            ) {
                $fPreisNetto  = 0;
                $fPreisBrutto = 0;
                $nVaterPos    = null;

                foreach ($this->PositionenArr as $nPos => $position) {
                    if ($pos->cUnique === $position->cUnique) {
                        $fPreisNetto  += $position->fPreis * $position->nAnzahl;
                        $fPreisBrutto += Tax::getGross(
                            $position->fPreis * $position->nAnzahl,
                            Tax::getSalesTax($position->kSteuerklasse)
                        );

                        if ((int)$position->kKonfigitem === 0
                            && \is_string($position->cUnique)
                            && !empty($position->cUnique)
                        ) {
                            $nVaterPos = $nPos;
                        }
                    }
                }

                if ($nVaterPos !== null) {
                    $parent = $this->PositionenArr[$nVaterPos];
                    if (\is_object($parent)) {
                        $pos->nAnzahlEinzel                              = $pos->nAnzahl / $parent->nAnzahl;
                        $parent->cKonfigpreisLocalized[0][$currencyName] = Preise::getLocalizedPriceString(
                            $fPreisBrutto,
                            $currency
                        );
                        $parent->cKonfigpreisLocalized[1][$currencyName] = Preise::getLocalizedPriceString(
                            $fPreisNetto,
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
     * @param int[] $posTypes
     * @return int|float
     */
    public function gibAnzahlArtikelExt($posTypes)
    {
        if (!\is_array($posTypes)) {
            return 0;
        }
        $anz = 0;
        foreach ($this->PositionenArr as $Position) {
            if (\in_array($Position->nPosTyp, $posTypes)
                && (empty($Position->cUnique) || (\mb_strlen($Position->cUnique) > 0 && $Position->kKonfigitem == 0))
            ) {
                $anz += $Position->nAnzahl;
            }
        }

        return $anz;
    }

    /**
     * gibt Anzahl der Positionen des Warenkorbs zurueck
     *
     * @param int[] $posTypes
     * @return int
     */
    public function gibAnzahlPositionenExt($posTypes): int
    {
        if (!\is_array($posTypes)) {
            return 0;
        }
        $anz = 0;
        foreach ($this->PositionenArr as $pos) {
            if (\in_array($pos->nPosTyp, $posTypes)
                && (empty($pos->cUnique) || (\mb_strlen($pos->cUnique) > 0 && $pos->kKonfigitem == 0))
            ) {
                ++$anz;
            }
        }

        return $anz;
    }

    /**
     * @return bool
     */
    public function hatTeilbareArtikel(): bool
    {
        foreach ($this->PositionenArr as $pos) {
            $pos->nPosTyp = (int)$pos->nPosTyp;
            if ($pos->nPosTyp === \C_WARENKORBPOS_TYP_ARTIKEL
                && isset($pos->Artikel->cTeilbar)
                && $pos->Artikel->cTeilbar === 'Y'
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * gibt Gesamtanzahl eines bestimmten Artikels im Warenkorb zurueck
     * @param int $kArtikel
     * @param int $excludePos
     * @return int
     */
    public function gibAnzahlEinesArtikels(int $kArtikel, int $excludePos = -1)
    {
        if (!$kArtikel) {
            return 0;
        }
        $anz = 0;
        foreach ($this->PositionenArr as $i => $pos) {
            if ($pos->kArtikel == $kArtikel && $excludePos !== $i) {
                $anz += $pos->nAnzahl;
            }
        }

        return $anz;
    }

    /**
     * @return $this
     */
    public function setzePositionsPreise(): self
    {
        $defaultOptions = Artikel::getDefaultOptions();
        foreach ($this->PositionenArr as $i => $pos) {
            if ($pos->kArtikel > 0 && $pos->nPosTyp === \C_WARENKORBPOS_TYP_ARTIKEL) {
                $_oldPosition = clone $pos;
                $oArtikel     = new Artikel();
                if (!$oArtikel->fuelleArtikel($pos->kArtikel, $defaultOptions) || $oArtikel->kArtikel === null) {
                    continue;
                }
                // Baue Variationspreise im Warenkorb neu, aber nur wenn es ein gültiger Artikel ist
                if (\is_array($pos->WarenkorbPosEigenschaftArr)) {
                    foreach ($this->PositionenArr[$i]->WarenkorbPosEigenschaftArr as $j => $posAttr) {
                        if (!\is_array($oArtikel->Variationen)) {
                            continue;
                        }
                        foreach ($oArtikel->Variationen as $oVariation) {
                            if ($posAttr->kEigenschaft != $oVariation->kEigenschaft) {
                                continue;
                            }
                            foreach ($oVariation->Werte as $oEigenschaftWert) {
                                if ($posAttr->kEigenschaftWert == $oEigenschaftWert->kEigenschaftWert) {
                                    $pos->WarenkorbPosEigenschaftArr[$j]->fAufpreis          =
                                        $oEigenschaftWert->fAufpreisNetto ?? null;
                                    $pos->WarenkorbPosEigenschaftArr[$j]->cAufpreisLocalized =
                                        $oEigenschaftWert->cAufpreisLocalized[1] ?? null;
                                    break;
                                }
                            }

                            break;
                        }
                    }
                }
                $anz                    = $this->gibAnzahlEinesArtikels($oArtikel->kArtikel);
                $pos->Artikel           = $oArtikel;
                $pos->fPreisEinzelNetto = $oArtikel->gibPreis($anz, []);
                $pos->fPreis            = $oArtikel->gibPreis($anz, $pos->WarenkorbPosEigenschaftArr);
                $pos->fGesamtgewicht    = $pos->gibGesamtgewicht();
                \executeHook(\HOOK_SETZTE_POSITIONSPREISE, [
                    'position'    => $pos,
                    'oldPosition' => $_oldPosition
                ]);
                $pos->setzeGesamtpreisLocalized();
                //notify about price changes when the price difference is greater then .01
                if ($_oldPosition->cGesamtpreisLocalized !== $pos->cGesamtpreisLocalized
                    && $_oldPosition->Artikel->Preise->fVK !== $pos->Artikel->Preise->fVK
                ) {
                    $updatedPosition                           = new stdClass();
                    $updatedPosition->cKonfigpreisLocalized    = $pos->cKonfigpreisLocalized;
                    $updatedPosition->cGesamtpreisLocalized    = $pos->cGesamtpreisLocalized;
                    $updatedPosition->cName                    = $pos->cName;
                    $updatedPosition->cKonfigpreisLocalizedOld = $_oldPosition->cKonfigpreisLocalized;
                    $updatedPosition->cGesamtpreisLocalizedOld = $_oldPosition->cGesamtpreisLocalized;
                    $updatedPosition->istKonfigVater           = $pos->istKonfigVater();
                    self::addUpdatedPosition($updatedPosition);
                }
                unset($pos->cHinweis);
                if (isset($_SESSION['Kupon']->kKupon)
                    && $_SESSION['Kupon']->kKupon > 0
                    && (int)$_SESSION['Kupon']->nGanzenWKRabattieren === 0
                ) {
                    $pos = Cart::checkCouponCartPositions($pos, $_SESSION['Kupon']);
                    $pos->setzeGesamtpreisLocalized();
                }
            }

            $this->setzeKonfig($pos, true, false);
        }

        return $this;
    }

    /**
     * @param object $position
     * @param bool   $bPreise
     * @param bool   $bName
     * @return $this
     */
    public function setzeKonfig(&$position, bool $bPreise = true, bool $bName = true): self
    {
        // Falls Konfigitem gesetzt Preise + Name ueberschreiben
        if ((int)$position->kKonfigitem <= 0 || !\class_exists('Konfigitem')) {
            return $this;
        }
        $oKonfigitem = new Konfigitem($position->kKonfigitem);
        if ($oKonfigitem->getKonfigitem() > 0) {
            if ($bPreise) {
                $position->fPreisEinzelNetto = $oKonfigitem->getPreis(true);
                $position->fPreis            = $position->fPreisEinzelNetto;
                $position->kSteuerklasse     = $oKonfigitem->getSteuerklasse();
                $position->setzeGesamtpreisLocalized();
            }
            if ($bName && $oKonfigitem->getUseOwnName()) {
                foreach (Frontend::getLanguages() as $language) {
                    $localized                        = new Konfigitemsprache(
                        $oKonfigitem->getKonfigitem(),
                        $language->kSprache
                    );
                    $position->cName[$language->cISO] = $localized->getName();
                }
            }
        }

        return $this;
    }

    /**
     * gibt Gesamtanzahl einer bestimmten Variation im Warenkorb zurueck
     * @param int $kArtikel
     * @param int $kEigenschaftsWert
     * @param int $excludePos
     * @return int
     */
    public function gibAnzahlEinerVariation(int $kArtikel, int $kEigenschaftsWert, int $excludePos = -1)
    {
        if (!$kArtikel || !$kEigenschaftsWert) {
            return 0;
        }
        $anz = 0;
        foreach ($this->PositionenArr as $i => $pos) {
            if ($pos->kArtikel == $kArtikel && $excludePos != $i && \is_array($pos->WarenkorbPosEigenschaftArr)) {
                foreach ($pos->WarenkorbPosEigenschaftArr as $attr) {
                    if ($attr->kEigenschaftWert == $kEigenschaftsWert) {
                        $anz += $pos->nAnzahl;
                    }
                }
            }
        }

        return $anz;
    }

    /**
     * gibt die tatsaechlichen Versandkosten zurueck, falls eine VersandArt gesetzt ist.
     * Es wird ebenso ueberprueft, ob die Summe fuer versandkostnfrei erreicht wurde.
     *
     * @todo: param?
     * @param string $Lieferland_ISO
     * @return int
     */
    public function gibVersandkostenSteuerklasse($Lieferland_ISO = ''): int
    {
        $classID = 0;
        if ($this->config['kaufabwicklung']['bestellvorgang_versand_steuersatz'] === 'US') {
            $taxRates = [];
            foreach ($this->PositionenArr as $i => $position) {
                if ($position->nPosTyp === \C_WARENKORBPOS_TYP_ARTIKEL && $position->kSteuerklasse > 0) {
                    if (empty($taxRates[$position->kSteuerklasse])) {
                        $taxRates[$position->kSteuerklasse] = $position->fPreisEinzelNetto * $position->nAnzahl;
                    } else {
                        $taxRates[$position->kSteuerklasse] += $position->fPreisEinzelNetto * $position->nAnzahl;
                    }
                }
            }
            $fMaxValue = \count($taxRates) > 0 ? \max($taxRates) : 0;
            foreach ($taxRates as $i => $rate) {
                if ($rate === $fMaxValue) {
                    $classID = $i;
                    break;
                }
            }
        } else {
            $rate = -1;
            foreach ($this->PositionenArr as $i => $position) {
                if ($position->nPosTyp === \C_WARENKORBPOS_TYP_ARTIKEL
                    && $position->kSteuerklasse > 0
                    && Tax::getSalesTax($position->kSteuerklasse) > $rate
                ) {
                    $rate    = Tax::getSalesTax($position->kSteuerklasse);
                    $classID = $position->kSteuerklasse;
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
        foreach ($this->PositionenArr as $pos) {
            // Lokalisierte Preise addieren
            if ($gross) {
                $total += $pos->fPreis * $conversionFactor * $pos->nAnzahl *
                    ((100 + Tax::getSalesTax($pos->kSteuerklasse)) / 100);
            } else {
                $total += $pos->fPreis * $conversionFactor * $pos->nAnzahl;
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
     * @param array $posTypes
     * @param bool  $gross
     * @return float|int
     */
    public function gibGesamtsummeWarenExt(array $posTypes, bool $gross = false)
    {
        if (!\is_array($posTypes)) {
            return 0;
        }
        $total = 0;
        foreach ($this->PositionenArr as $pos) {
            if (\in_array($pos->nPosTyp, $posTypes, true)) {
                if ($gross) {
                    $total += $pos->fPreis * $pos->nAnzahl *
                        ((100 + Tax::getSalesTax($pos->kSteuerklasse)) / 100);
                } else {
                    $total += $pos->fPreis * $pos->nAnzahl;
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
     * @param array $posTypes
     * @param bool  $gross
     * @return float|int
     */
    public function gibGesamtsummeWarenOhne(array $posTypes, bool $gross = false)
    {
        if (!\is_array($posTypes)) {
            return 0;
        }
        $total    = 0;
        $currency = $this->Waehrung ?? Frontend::getCurrency();
        $factor   = $currency->getConversionFactor();
        foreach ($this->PositionenArr as $pos) {
            if (!\in_array($pos->nPosTyp, $posTypes)) {
                if ($gross) {
                    $total += $pos->fPreis * $factor * $pos->nAnzahl *
                        ((100 + Tax::getSalesTax($pos->kSteuerklasse)) / 100);
                } else {
                    $total += $pos->fPreis * $factor * $pos->nAnzahl;
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
        foreach ($this->PositionenArr as $Position) {
            $Position->setzeGesamtpreisLocalized();
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
        foreach ($this->PositionenArr as $i => $position) {
            if ($position->nAnzahl <= 0) {
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
        $steuersatz = [];
        $steuerpos  = [];
        foreach ($this->PositionenArr as $position) {
            if ($position->kSteuerklasse > 0) {
                $ust = Tax::getSalesTax($position->kSteuerklasse);
                if (!\in_array($ust, $steuersatz)) {
                    $steuersatz[] = $ust;
                }
            }
        }
        \sort($steuersatz);
        foreach ($this->PositionenArr as $position) {
            if ($position->kSteuerklasse <= 0) {
                continue;
            }
            $ust = Tax::getSalesTax($position->kSteuerklasse);
            if ($ust > 0) {
                $idx = \array_search($ust, $steuersatz);
                if (!isset($steuerpos[$idx]->fBetrag)) {
                    $steuerpos[$idx]                  = new stdClass();
                    $steuerpos[$idx]->cName           = \lang_steuerposition(
                        $ust,
                        Frontend::getCustomerGroup()->isMerchant()
                    );
                    $steuerpos[$idx]->fUst            = $ust;
                    $steuerpos[$idx]->fBetrag         = ($position->fPreis * $position->nAnzahl * $ust) / 100.0;
                    $steuerpos[$idx]->cPreisLocalized = Preise::getLocalizedPriceString($steuerpos[$idx]->fBetrag);
                } else {
                    $steuerpos[$idx]->fBetrag        += ($position->fPreis * $position->nAnzahl * $ust) / 100.0;
                    $steuerpos[$idx]->cPreisLocalized = Preise::getLocalizedPriceString($steuerpos[$idx]->fBetrag);
                }
            }
        }

        return $steuerpos;
    }

    /**
     * @return $this
     */
    public function setzeVersandfreiKupon(): self
    {
        foreach ($this->PositionenArr as $i => $position) {
            if ($position->nPosTyp === \C_WARENKORBPOS_TYP_VERSANDPOS) {
                $position->fPreisEinzelNetto = 0.0;
                $position->fPreis            = 0.0;
                $position->setzeGesamtpreisLocalized();
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
        $bRedirect     = false;
        $depAmount     = $this->getAllDependentAmount(true);
        $reservedStock = [];

        foreach ($this->PositionenArr as $i => $position) {
            if ($position->kArtikel <= 0
                || $position->Artikel->cLagerBeachten !== 'Y'
                || $position->Artikel->cLagerKleinerNull === 'Y'
            ) {
                continue;
            }
            // Lagerbestand beachten und keine Überverkäufe möglich
            if (isset($position->WarenkorbPosEigenschaftArr)
                && !$position->Artikel->kVaterArtikel
                && !$position->Artikel->nIstVater
                && $position->Artikel->cLagerVariation === 'Y'
                && \count($position->WarenkorbPosEigenschaftArr) > 0
            ) {
                // Position mit Variationen, Lagerbestand in Variationen wird beachtet
                foreach ($position->WarenkorbPosEigenschaftArr as $oWarenkorbPosEigenschaft) {
                    if ($oWarenkorbPosEigenschaft->kEigenschaftWert > 0 && $position->nAnzahl > 0) {
                        //schaue in DB, ob Lagerbestand ausreichend
                        $stock = Shop::Container()->getDB()->query(
                            'SELECT kEigenschaftWert, fLagerbestand >= ' . $position->nAnzahl .
                            ' AS bAusreichend, fLagerbestand
                                FROM teigenschaftwert
                                WHERE kEigenschaftWert = ' . (int)$oWarenkorbPosEigenschaft->kEigenschaftWert,
                            ReturnType::SINGLE_OBJECT
                        );
                        if (isset($stock->kEigenschaftWert) && $stock->kEigenschaftWert > 0 && !$stock->bAusreichend) {
                            if ($stock->fLagerbestand > 0) {
                                $position->nAnzahl = $stock->fLagerbestand;
                            } else {
                                unset($this->PositionenArr[$i]);
                            }
                            $bRedirect = true;
                        }
                    }
                }
            } else {
                // Position ohne Variationen bzw. Variationen ohne eigenen Lagerbestand
                // schaue in DB, ob Lagerbestand ausreichend
                $depProducts = $position->Artikel->getAllDependentProducts(true);
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

                        if ($newAmount > $position->nAnzahl) {
                            $newAmount = $position->nAnzahl;
                        }

                        if ($newAmount > 0) {
                            $position->nAnzahl = $newAmount;
                        } else {
                            unset($this->PositionenArr[$i]);
                        }

                        $reservedStock[$productID] = ($reservedStock[$productID] ?? 0)
                            + $newAmount
                            * $depProducts[$productID]->product->fPackeinheit * $depProducts[$productID]->stockFactor;

                        $depAmount = $this->getAllDependentAmount(true);
                        $bRedirect = true;
                    }
                }
            }
        }

        if ($bRedirect) {
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

        /** @var WarenkorbPos $pos */
        foreach ($this->PositionenArr as $pos) {
            if ($pos->nPosTyp !== \C_WARENKORBPOS_TYP_ARTIKEL || !$pos->Artikel instanceof Artikel) {
                continue;
            }
            $pos->Artikel->getDeliveryTime($_SESSION['cLieferlandISO'], $pos->nAnzahl);
            WarenkorbPos::setEstimatedDelivery(
                $pos,
                $pos->Artikel->nMinDeliveryDays,
                $pos->Artikel->nMaxDeliveryDays
            );
            if (isset($pos->Artikel->nMinDeliveryDays) && $pos->Artikel->nMinDeliveryDays > $longestMinDeliveryDays) {
                $longestMinDeliveryDays = $pos->Artikel->nMinDeliveryDays;
            }
            if (isset($pos->Artikel->nMaxDeliveryDays) && $pos->Artikel->nMaxDeliveryDays > $longestMaxDeliveryDays) {
                $longestMaxDeliveryDays = $pos->Artikel->nMaxDeliveryDays;
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
        foreach ($this->PositionenArr as $position) {
            if ($position->nPosTyp === \C_WARENKORBPOS_TYP_ARTIKEL && $position->kKonfigitem === 0) {
                if (isset($position->nZeitLetzteAenderung) && $position->nZeitLetzteAenderung > $lastUpdate) {
                    $lastUpdate = $position->nZeitLetzteAenderung;
                    $res        = $position->Artikel;
                    Product::addVariationPictures($res, $position->variationPicturesArr);
                } elseif ($res === null) {
                    // Wenn keine nZeitLetzteAenderung gesetzt ist letztes Element des WK-Arrays nehmen
                    $res = $position->Artikel;
                }
            }
        }

        return $res;
    }

    /**
     * @return int|float
     */
    public function getWeight()
    {
        $weight = 0;
        foreach ($this->PositionenArr as $pos) {
            $weight += $pos->fGesamtgewicht;
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
            foreach ($this->PositionenArr as $i => $position) {
                $grossAmount        = Tax::getGross(
                    $position->fPreis * $position->nAnzahl,
                    Tax::getSalesTax($position->kSteuerklasse),
                    12
                );
                $netAmount          = $position->fPreis * $position->nAnzahl;
                $roundedGrossAmount = Tax::getGross(
                    $position->fPreis * $position->nAnzahl + $cumulatedDelta,
                    Tax::getSalesTax($position->kSteuerklasse),
                    $precision
                );
                $roundedNetAmount   = \round($position->fPreis * $position->nAnzahl + $cumulatedDeltaNet, $precision);

                if ($i !== 0 && $position->nPosTyp === \C_WARENKORBPOS_TYP_ARTIKEL) {
                    if ($grossAmount != 0) {
                        $position->cGesamtpreisLocalized[0][$currencyName] = Preise::getLocalizedPriceString(
                            $roundedGrossAmount,
                            $currency
                        );
                    }
                    if ($netAmount != 0) {
                        $position->cGesamtpreisLocalized[1][$currencyName] = Preise::getLocalizedPriceString(
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
        $kKundengruppe    = $_SESSION['Kunde']->kKundengruppe ?? 0;

        if ($kKundengruppe > 0) {
            $countryCode      = $_SESSION['Kunde']->cLand;
            $customerGroupSQL = " OR FIND_IN_SET('" . $kKundengruppe . "', REPLACE(va.cKundengruppen, ';', ',')) > 0";
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

        foreach ($this->PositionenArr as $Position) {
            $totalWeight      += $Position->fGesamtgewicht;
            $shippingClasses[] = $Position->kVersandklasse;
            $maxPrices        += $Position->Artikel->Preise->fVKNetto ?? 0;
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
                AND (va.cKundengruppen = '-1' " . $customerGroupSQL . ")
                AND va.kVersandart NOT IN (
                    SELECT vaza.kVersandart
                        FROM tversandartzahlungsart vaza
                        WHERE kZahlungsart = 6)
                AND (
                    va.kVersandberechnung = 1 OR va.kVersandberechnung = 4
                    OR ( va.kVersandberechnung = 2 AND vas.fBis > 0 AND " . $totalWeight . " <= vas.fBis )
                    OR ( va.kVersandberechnung = 3 AND vas.fBis > 0 AND " . $maxPrices . " <= vas.fBis )
                    )
                ORDER BY minPrice, nSort ASC LIMIT 1",
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
