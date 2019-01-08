<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Helpers;

use Artikel;
use DB\ReturnType;
use Kundengruppe;
use Preise;
use Session\Session;
use Shop;
use Sprache;
use stdClass;
use StringHandler;
use Versandart;
use Warenkorb;

/**
 * Class ShippingMethod
 * @package Helpers
 */
class ShippingMethod
{
    /**
     * @var ShippingMethod
     */
    private static $instance;

    /**
     * @var string
     */
    public $cacheID;

    /**
     * @var array
     */
    public $shippingMethods;

    /**
     * @var array
     */
    public $countries = [];

    /**
     *
     */
    public function __construct()
    {
        $this->cacheID         = 'smeth_' . Shop::Container()->getCache()->getBaseID();
        $this->shippingMethods = $this->getShippingMethods();
        self::$instance        = $this;
    }

    /**
     * @return ShippingMethod
     */
    public static function getInstance(): self
    {
        return self::$instance ?? new self();
    }

    /**
     * @return array
     */
    public function getShippingMethods(): array
    {
        return $this->shippingMethods ?? Shop::Container()->getDB()->query(
            'SELECT * FROM tversandart',
            ReturnType::ARRAY_OF_OBJECTS
        );
    }

    /**
     * @param float|int $freeFromX
     * @return array
     */
    public function filter($freeFromX): array
    {
        $freeFromX = (float)$freeFromX;

        return \array_filter(
            $this->shippingMethods,
            function ($s) use ($freeFromX) {
                return $s->fVersandkostenfreiAbX !== '0.00'
                    && (float)$s->fVersandkostenfreiAbX > 0
                    && (float)$s->fVersandkostenfreiAbX <= $freeFromX;
            }
        );
    }

    /**
     * @param float|int $wert
     * @param int       $kKundengruppe
     * @param int       $versandklasse
     * @return string
     */
    public function getFreeShippingCountries($wert, int $kKundengruppe, int $versandklasse = 0): string
    {
        if (!isset($this->countries[$kKundengruppe][$versandklasse])) {
            if (!isset($this->countries[$kKundengruppe])) {
                $this->countries[$kKundengruppe] = [];
            }
            $this->countries[$kKundengruppe][$versandklasse] = Shop::Container()->getDB()->queryPrepared(
                "SELECT *
                    FROM tversandart
                    WHERE fVersandkostenfreiAbX > 0
                        AND (cVersandklassen = '-1'
                        OR cVersandklassen RLIKE :sClasses)
                        AND (cKundengruppen = '-1' OR FIND_IN_SET(:cGroupID, REPLACE(cKundengruppen, ';', ',')) > 0)",
                [
                    'sClasses' => '^([0-9 -]* )?' . $versandklasse . ' ',
                    'cGroupID' => $kKundengruppe
                ],
                ReturnType::ARRAY_OF_OBJECTS
            );
        }
        $shippingFreeCountries = [];
        foreach ($this->countries[$kKundengruppe][$versandklasse] as $_method) {
            if (isset($_method->fVersandkostenfreiAbX)
                && (float)$_method->fVersandkostenfreiAbX > 0
                && (float)$_method->fVersandkostenfreiAbX < $wert
            ) {
                foreach (\explode(' ', $_method->cLaender) as $_country) {
                    if (\strlen($_country) > 0) {
                        $shippingFreeCountries[] = $_country;
                    }
                }
            }
        }
        $shippingFreeCountries = \array_unique($shippingFreeCountries);
        $res                   = '';
        foreach ($shippingFreeCountries as $i => $_country) {
            $res .= (($i > 0) ? ', ' : '') . $_country;
        }

        return $res;
    }

    /**
     * @param string $cLand
     * @return bool
     */
    public static function normalerArtikelversand($cLand): bool
    {
        $bNoetig = false;
        $cart    = Session::getCart();
        foreach ($cart->PositionenArr as $pos) {
            if ((int)$pos->nPosTyp === \C_WARENKORBPOS_TYP_ARTIKEL
                && !self::gibArtikelabhaengigeVersandkosten($cLand, $pos->Artikel, $pos->nAnzahl)
            ) {
                $bNoetig = true;
                break;
            }
        }

        return $bNoetig;
    }

    /**
     * @param string $cLand
     * @return bool
     */
    public static function hasSpecificShippingcosts($cLand): bool
    {
        return !empty(self::gibArtikelabhaengigeVersandkostenImWK($cLand, Session::getCart()->PositionenArr));
    }

    /**
     * @former gibMoeglicheVersandarten()
     * @param string $lieferland
     * @param string $plz
     * @param string $versandklassen
     * @param int    $kKundengruppe
     * @return array
     */
    public static function getPossibleShippingMethods($lieferland, $plz, $versandklassen, int $kKundengruppe): array
    {
        $db                       = Shop::Container()->getDB();
        $cart                     = Session::getCart();
        $kSteuerklasse            = $cart->gibVersandkostenSteuerklasse();
        $minVersand               = 10000;
        $cISO                     = $lieferland;
        $hasSpecificShippingcosts = self::hasSpecificShippingcosts($lieferland);
        $vatNote                  = null;
        $cNurAbhaengigeVersandart = self::normalerArtikelversand($lieferland) === false
            ? 'Y'
            : 'N';
        $excludeShippingCostAttributes = $cNurAbhaengigeVersandart === 'N';

        $methods                  = $db->queryPrepared(
            "SELECT * FROM tversandart
                WHERE cNurAbhaengigeVersandart = :depOnly
                    AND cLaender LIKE :iso
                    AND (cVersandklassen = '-1'
                    OR cVersandklassen RLIKE :sClasses)
                    AND (cKundengruppen = '-1'
                    OR FIND_IN_SET(:cGroupID, REPLACE(cKundengruppen, ';', ',')) > 0)
                ORDER BY nSort",
            [
                'iso'      => '%' . $cISO . '%',
                'cGroupID' => $kKundengruppe,
                'sClasses' => '^([0-9 -]* )?' . $versandklassen . ' ',
                'depOnly'  => $cNurAbhaengigeVersandart
            ],
            ReturnType::ARRAY_OF_OBJECTS
        );
        $netPricesActive          = Session::getCustomerGroup()->isMerchant();

        foreach ($methods as $i => $shippingMethod) {
            $bSteuerPos = $shippingMethod->eSteuer !== 'netto';

            $shippingMethod->kVersandart        = (int)$shippingMethod->kVersandart;
            $shippingMethod->kVersandberechnung = (int)$shippingMethod->kVersandberechnung;
            $shippingMethod->nSort              = (int)$shippingMethod->nSort;
            $shippingMethod->nMinLiefertage     = (int)$shippingMethod->nMinLiefertage;
            $shippingMethod->nMaxLiefertage     = (int)$shippingMethod->nMaxLiefertage;
            $shippingMethod->Zuschlag           = self::getAdditionalFees($shippingMethod, $cISO, $plz);
            $shippingMethod->fEndpreis          = self::calculateShippingFees($shippingMethod, $cISO, null, 0, $excludeShippingCostAttributes);
            if ($shippingMethod->fEndpreis === -1) {
                unset($methods[$i]);
                continue;
            }
            if ($netPricesActive === true) {
                $shippingCosts = $bSteuerPos
                    ? $shippingMethod->fEndpreis / (100 + Tax::getSalesTax($kSteuerklasse)) * 100.0
                    : \round($shippingMethod->fEndpreis, 2);
                $vatNote       = ' ' . Shop::Lang()->get('plus', 'productDetails') . ' ' .
                    Shop::Lang()->get('vat', 'productDetails');
            } else {
                $shippingCosts = $bSteuerPos
                    ? $shippingMethod->fEndpreis
                    : \round($shippingMethod->fEndpreis * (100 + Tax::getSalesTax($kSteuerklasse)) / 100, 2);
            }
            // posname lokalisiert ablegen
            $shippingMethod->angezeigterName           = [];
            $shippingMethod->angezeigterHinweistext    = [];
            $shippingMethod->cLieferdauer              = [];
            $shippingMethod->specificShippingcosts_arr = null;
            foreach ($_SESSION['Sprachen'] as $Sprache) {
                $name_spr = $db->select(
                    'tversandartsprache',
                    'kVersandart',
                    (int)$shippingMethod->kVersandart,
                    'cISOSprache',
                    $Sprache->cISO
                );
                if (isset($name_spr->cName)) {
                    $shippingMethod->angezeigterName[$Sprache->cISO]        = $name_spr->cName;
                    $shippingMethod->angezeigterHinweistext[$Sprache->cISO] = $name_spr->cHinweistextShop;
                    $shippingMethod->cLieferdauer[$Sprache->cISO]           = $name_spr->cLieferdauer;
                }
            }
            if ($shippingMethod->fEndpreis < $minVersand) {
                $minVersand = $shippingMethod->fEndpreis;
            }
            // lokalisieren
            if ($shippingMethod->fEndpreis == 0) {
                // Abfrage ob ein Artikel Artikelabhängige Versandkosten besitzt
                $shippingMethod->cPreisLocalized = Shop::Lang()->get('freeshipping');
                if ($hasSpecificShippingcosts === true) {
                    $shippingMethod->cPreisLocalized           = Preise::getLocalizedPriceString($shippingCosts);
                    $shippingMethod->specificShippingcosts_arr = self::gibArtikelabhaengigeVersandkostenImWK(
                        $lieferland,
                        $cart->PositionenArr
                    );
                }
            } else {
                // Abfrage ob ein Artikel Artikelabhängige Versandkosten besitzt
                $shippingMethod->cPreisLocalized = Preise::getLocalizedPriceString($shippingCosts) . ($vatNote ?? '');
                if ($hasSpecificShippingcosts === true) {
                    $shippingMethod->specificShippingcosts_arr = self::gibArtikelabhaengigeVersandkostenImWK(
                        $lieferland,
                        $cart->PositionenArr
                    );
                }
            }
            // Abfrage ob die Zahlungsart/en zur Versandart gesetzt ist/sind
            $zahlungsarten   = $db->queryPrepared(
                "SELECT tversandartzahlungsart.*, tzahlungsart.*
                     FROM tversandartzahlungsart, tzahlungsart
                     WHERE tversandartzahlungsart.kVersandart = :methodID
                         AND tversandartzahlungsart.kZahlungsart = tzahlungsart.kZahlungsart
                         AND (tzahlungsart.cKundengruppen IS NULL OR tzahlungsart.cKundengruppen = ''
                         OR FIND_IN_SET(:cGroupID, REPLACE(tzahlungsart.cKundengruppen, ';', ',')) > 0)
                         AND tzahlungsart.nActive = 1
                         AND tzahlungsart.nNutzbar = 1
                     ORDER BY tzahlungsart.nSort",
                [
                    'methodID' => (int)$shippingMethod->kVersandart,
                    'cGroupID' => $kKundengruppe
                ],
                ReturnType::ARRAY_OF_OBJECTS
            );
            $bVersandGueltig = false;
            foreach ($zahlungsarten as $zahlungsart) {
                if (PaymentMethod::shippingMethodWithValidPaymentMethod($zahlungsart)) {
                    $bVersandGueltig = true;
                    break;
                }
            }
            if (!$bVersandGueltig) {
                unset($shippingMethod);
            }
        }
        // auf anzeige filtern
        $possibleMethods = \array_filter(
            \array_merge($methods),
            function ($p) use ($minVersand) {
                return $p->cAnzeigen === 'immer'
                    || ($p->cAnzeigen === 'guenstigste' && $p->fEndpreis <= $minVersand);
            }
        );
        // evtl. Versandkupon anwenden
        if (!empty($_SESSION['VersandKupon'])) {
            foreach ($possibleMethods as $method) {
                $method->fEndpreis = 0;
                // lokalisieren
                $method->cPreisLocalized = Preise::getLocalizedPriceString($method->fEndpreis);
            }
        }

        return $possibleMethods;
    }

    /**
     * @former ermittleVersandkosten()
     * @param string $cLand
     * @param string $cPLZ
     * @param string $cError
     * @return bool
     */
    public static function getShippingCosts($cLand, $cPLZ, &$cError = ''): bool
    {
        if ($cLand !== null && $cPLZ !== null && \strlen($cLand) > 0 && \strlen($cPLZ) > 0) {
            $kKundengruppe = Session::getCustomerGroup()->getID();
            if (isset($_SESSION['Kunde']->kKundengruppe) && $_SESSION['Kunde']->kKundengruppe > 0) {
                $kKundengruppe = $_SESSION['Kunde']->kKundengruppe;
            }

            $oVersandart_arr = self::getPossibleShippingMethods(
                StringHandler::filterXSS($cLand),
                StringHandler::filterXSS($cPLZ),
                self::getShippingClasses(Session::getCart()),
                $kKundengruppe
            );
            if (\count($oVersandart_arr) > 0) {
                Shop::Smarty()
                    ->assign('ArtikelabhaengigeVersandarten', self::gibArtikelabhaengigeVersandkostenImWK(
                        $cLand,
                        Session::getCart()->PositionenArr
                    ))
                    ->assign('Versandarten', $oVersandart_arr)
                    ->assign('Versandland', Sprache::getCountryCodeByCountryName($cLand))
                    ->assign('VersandPLZ', StringHandler::filterXSS($cPLZ));
            } else {
                $cError = Shop::Lang()->get('noDispatchAvailable');
            }
            \executeHook(\HOOK_WARENKORB_PAGE_ERMITTLEVERSANDKOSTEN);

            return true;
        }

        return !(isset($_POST['versandrechnerBTN']) && (\strlen($cLand) === 0 || \strlen($cPLZ) === 0));
    }

    /**
     * @former ermittleVersandkostenExt()
     * @param array $products
     * @return string
     */
    public static function getShippingCostsExt(array $products): string
    {
        if (!isset($_SESSION['shipping_count'])) {
            $_SESSION['shipping_count'] = 0;
        }
        if (!\is_array($products) || \count($products) === 0) {
            return null;
        }
        $cLandISO = $_SESSION['cLieferlandISO'] ?? false;
        $cart     = Session::getCart();
        if (!$cLandISO) {
            //Falls kein Land in tfirma da
            $cLandISO = 'DE';
        }

        $kKundengruppe = Session::getCustomerGroup()->getID();
        // Baue ZusatzArtikel
        $additionalProduct                  = new stdClass();
        $additionalProduct->fAnzahl         = 0;
        $additionalProduct->fWarenwertNetto = 0;
        $additionalProduct->fGewicht        = 0;

        $shippingClasses                = self::getShippingClasses($cart);
        $conf                           = Shop::getSettings([\CONF_KAUFABWICKLUNG]);
        $additionalShippingFees         = 0;
        $fWarensummeProSteuerklasse_arr = [];
        $kSteuerklasse                  = 0;
        // Vorkonditionieren -- Gleiche kartikel aufsummieren
        // aber nur, wenn artikelabhaengiger Versand bei dem jeweiligen kArtikel
        $nArtikelAssoc_arr = [];
        foreach ($products as $product) {
            $kArtikel                     = (int)$product['kArtikel'];
            $nArtikelAssoc_arr[$kArtikel] = isset($nArtikelAssoc_arr[$kArtikel]) ? 1 : 0;
        }
        $bMerge         = false;
        $defaultOptions = Artikel::getDefaultOptions();
        foreach ($nArtikelAssoc_arr as $kArtikel => $nArtikelAssoc) {
            if ($nArtikelAssoc !== 1) {
                continue;
            }
            $tmpProduct = (new Artikel())->fuelleArtikel($kArtikel, $defaultOptions);
            // Normaler Variationsartikel
            if ($tmpProduct !== null
                && $tmpProduct->nIstVater === 0
                && $tmpProduct->kVaterArtikel === 0
                && \count($tmpProduct->Variationen) > 0
                && self::pruefeArtikelabhaengigeVersandkosten($tmpProduct) === 2
            ) {
                // Nur wenn artikelabhaengiger Versand gestaffelt als Funktionsattribut gesetzt ist
                $fAnzahl = 0;
                foreach ($products as $i => $prod) {
                    if ($prod['kArtikel'] === $kArtikel) {
                        $fAnzahl += $prod['fAnzahl'];
                        unset($products[$i]);
                    }
                }

                $oArtikelMerged             = [];
                $oArtikelMerged['kArtikel'] = $kArtikel;
                $oArtikelMerged['fAnzahl']  = $fAnzahl;
                $products[]                 = $oArtikelMerged;
                $bMerge                     = true;
            }
        }

        if ($bMerge) {
            $products = \array_merge($products);
        }
        $defaultOptions = Artikel::getDefaultOptions();
        foreach ($products as $i => $product) {
            $tmpProduct = (new Artikel())->fuelleArtikel($product['kArtikel'], $defaultOptions);
            if ($tmpProduct === null || $tmpProduct->kArtikel <= 0) {
                continue;
            }
            $kSteuerklasse = $tmpProduct->kSteuerklasse;
            // Artikelabhaengige Versandkosten?
            if ($tmpProduct->nIstVater === 0) {
                //Summen pro Steuerklasse summieren
                if ($tmpProduct->kSteuerklasse === null) {
                    $fWarensummeProSteuerklasse_arr[$tmpProduct->kSteuerklasse] = 0;
                }

                $fWarensummeProSteuerklasse_arr[$tmpProduct->kSteuerklasse] +=
                    $tmpProduct->Preise->fVKNetto * $product['fAnzahl'];

                $oVersandPos = self::gibHinzukommendeArtikelAbhaengigeVersandkosten(
                    $tmpProduct,
                    $cLandISO,
                    $product['fAnzahl']
                );
                if ($oVersandPos !== false) {
                    $additionalShippingFees += $oVersandPos->fKosten;
                    continue;
                }
            }
            // Normaler Artikel oder Kind Artikel
            if ($tmpProduct->kVaterArtikel > 0 || \count($tmpProduct->Variationen) === 0) {
                $additionalProduct->fAnzahl         += $product['fAnzahl'];
                $additionalProduct->fWarenwertNetto += $product['fAnzahl'] * $tmpProduct->Preise->fVKNetto;
                $additionalProduct->fGewicht        += $product['fAnzahl'] * $tmpProduct->fGewicht;

                if (\strlen($shippingClasses) > 0
                    && \strpos($shippingClasses, $tmpProduct->kVersandklasse) === false
                ) {
                    $shippingClasses = '-' . $tmpProduct->kVersandklasse;
                } elseif (\strlen($shippingClasses) === 0) {
                    $shippingClasses = $tmpProduct->kVersandklasse;
                }
            } elseif ($tmpProduct->nIstVater === 0
                && $tmpProduct->kVaterArtikel === 0
                && \count($tmpProduct->Variationen) > 0
            ) { // Normale Variation
                if ($product['cInputData']{0} === '_') {
                    // 1D
                    $cVariation0                         = \substr($product['cInputData'], 1);
                    [$kEigenschaft0, $kEigenschaftWert0] = \explode(':', $cVariation0);

                    $oVariation = Product::findVariation(
                        $tmpProduct->Variationen,
                        $kEigenschaft0,
                        $kEigenschaftWert0
                    );

                    $additionalProduct->fAnzahl         += $product['fAnzahl'];
                    $additionalProduct->fWarenwertNetto += $product['fAnzahl'] *
                        ($tmpProduct->Preise->fVKNetto + $oVariation->fAufpreisNetto);
                    $additionalProduct->fGewicht        += $product['fAnzahl'] *
                        ($tmpProduct->fGewicht + $oVariation->fGewichtDiff);
                } else {
                    // 2D
                    [$cVariation0, $cVariation1]         = \explode('_', $product['cInputData']);
                    [$kEigenschaft0, $kEigenschaftWert0] = \explode(':', $cVariation0);
                    [$kEigenschaft1, $kEigenschaftWert1] = \explode(':', $cVariation1);

                    $oVariation0 = Product::findVariation(
                        $tmpProduct->Variationen,
                        $kEigenschaft0,
                        $kEigenschaftWert0
                    );
                    $oVariation1 = Product::findVariation(
                        $tmpProduct->Variationen,
                        $kEigenschaft1,
                        $kEigenschaftWert1
                    );

                    $additionalProduct->fAnzahl         += $product['fAnzahl'];
                    $additionalProduct->fWarenwertNetto += $product['fAnzahl'] *
                        ($tmpProduct->Preise->fVKNetto + $oVariation0->fAufpreisNetto + $oVariation1->fAufpreisNetto);
                    $additionalProduct->fGewicht        += $product['fAnzahl'] *
                        ($tmpProduct->fGewicht + $oVariation0->fGewichtDiff + $oVariation1->fGewichtDiff);
                }
                if (\strlen($shippingClasses) > 0
                    && \strpos($shippingClasses, $tmpProduct->kVersandklasse) === false
                ) {
                    $shippingClasses = '-' . $tmpProduct->kVersandklasse;
                } elseif (\strlen($shippingClasses) === 0) {
                    $shippingClasses = $tmpProduct->kVersandklasse;
                }
            } elseif ($tmpProduct->nIstVater > 0) { // Variationskombination (Vater)
                $child = new Artikel();
                if ($product['cInputData']{0} === '_') {
                    // 1D
                    $cVariation0                         = \substr($product['cInputData'], 1);
                    [$kEigenschaft0, $kEigenschaftWert0] = \explode(':', $cVariation0);
                    $kKindArtikel                        = Product::getChildProdctIDByAttribute(
                        $tmpProduct->kArtikel,
                        $kEigenschaft0,
                        $kEigenschaftWert0
                    );
                    $child->fuelleArtikel($kKindArtikel, $defaultOptions);
                    //Summen pro Steuerklasse summieren
                    if (!\array_key_exists($child->kSteuerklasse, $fWarensummeProSteuerklasse_arr)) {
                        $fWarensummeProSteuerklasse_arr[$child->kSteuerklasse] = 0;
                    }

                    $fWarensummeProSteuerklasse_arr[$child->kSteuerklasse] +=
                        $child->Preise->fVKNetto * $product['fAnzahl'];

                    $fSumme = self::gibHinzukommendeArtikelAbhaengigeVersandkosten(
                        $child,
                        $cLandISO,
                        $product['fAnzahl']
                    );
                    if ($fSumme !== false) {
                        $additionalShippingFees += $fSumme;
                        continue;
                    }

                    $additionalProduct->fAnzahl         += $product['fAnzahl'];
                    $additionalProduct->fWarenwertNetto += $product['fAnzahl'] * $child->Preise->fVKNetto;
                    $additionalProduct->fGewicht        += $product['fAnzahl'] * $child->fGewicht;
                } else {
                    // 2D
                    [$cVariation0, $cVariation1]         = \explode('_', $product['cInputData']);
                    [$kEigenschaft0, $kEigenschaftWert0] = \explode(':', $cVariation0);
                    [$kEigenschaft1, $kEigenschaftWert1] = \explode(':', $cVariation1);

                    $kKindArtikel = Product::getChildProdctIDByAttribute(
                        $tmpProduct->kArtikel,
                        $kEigenschaft0,
                        $kEigenschaftWert0,
                        $kEigenschaft1,
                        $kEigenschaftWert1
                    );
                    $child->fuelleArtikel($kKindArtikel, $defaultOptions);
                    //Summen pro Steuerklasse summieren
                    if (!\array_key_exists($child->kSteuerklasse, $fWarensummeProSteuerklasse_arr)) {
                        $fWarensummeProSteuerklasse_arr[$child->kSteuerklasse] = 0;
                    }

                    $fWarensummeProSteuerklasse_arr[$child->kSteuerklasse] +=
                        $child->Preise->fVKNetto * $product['fAnzahl'];

                    $fSumme = self::gibHinzukommendeArtikelAbhaengigeVersandkosten(
                        $child,
                        $cLandISO,
                        $product['fAnzahl']
                    );
                    if ($fSumme !== false) {
                        $additionalShippingFees += $fSumme;
                        continue;
                    }

                    $additionalProduct->fAnzahl         += $product['fAnzahl'];
                    $additionalProduct->fWarenwertNetto += $product['fAnzahl'] * $child->Preise->fVKNetto;
                    $additionalProduct->fGewicht        += $product['fAnzahl'] * $child->fGewicht;
                }
                if (\strlen($shippingClasses) > 0 && \strpos($shippingClasses, $child->kVersandklasse) === false
                ) {
                    $shippingClasses = '-' . $child->kVersandklasse;
                } elseif (\strlen($shippingClasses) === 0) {
                    $shippingClasses = $child->kVersandklasse;
                }
            }
        }

        if (isset($cart->PositionenArr)
            && \is_array($cart->PositionenArr)
            && \count($cart->PositionenArr) > 0
        ) {
            // Wenn etwas im Warenkorb ist, dann Vesandart vom Warenkorb rausfinden
            $oVersandartNurWK                   = self::getFavourableShippingMethod(
                $cLandISO,
                $shippingClasses,
                $kKundengruppe,
                null
            );
            $oArtikelAbhaenigeVersandkosten_arr = self::gibArtikelabhaengigeVersandkostenImWK(
                $cLandISO,
                $cart->PositionenArr
            );

            $fSumme = 0;
            if (\count($oArtikelAbhaenigeVersandkosten_arr) > 0) {
                foreach ($oArtikelAbhaenigeVersandkosten_arr as $oArtikelAbhaenigeVersandkosten) {
                    $fSumme += $oArtikelAbhaenigeVersandkosten->fKosten;
                }
            }

            $oVersandartNurWK->fEndpreis += $fSumme;
            $oVersandart                  = self::getFavourableShippingMethod(
                $cLandISO,
                $shippingClasses,
                $kKundengruppe,
                $additionalProduct
            );
            $oVersandart->fEndpreis      += ($fSumme + $additionalShippingFees);
        } else {
            $oVersandartNurWK            = new stdClass();
            $oVersandart                 = new stdClass();
            $oVersandartNurWK->fEndpreis = 0;
            $oVersandart->fEndpreis      = $additionalShippingFees;
        }

        if (\abs($oVersandart->fEndpreis - $oVersandartNurWK->fEndpreis) > 0.01) {
            //Versand mit neuen Artikeln > als Versand ohne Steuerklasse bestimmen
            foreach ($cart->PositionenArr as $oPosition) {
                if ((int)$oPosition->nPosTyp === \C_WARENKORBPOS_TYP_ARTIKEL) {
                    //Summen pro Steuerklasse summieren
                    if (!\array_key_exists($oPosition->Artikel->kSteuerklasse, $fWarensummeProSteuerklasse_arr)) {
                        $fWarensummeProSteuerklasse_arr[$oPosition->Artikel->kSteuerklasse] = 0;
                    }
                    $fWarensummeProSteuerklasse_arr[$oPosition->Artikel->kSteuerklasse] +=
                        $oPosition->Artikel->Preise->fVKNetto * $oPosition->nAnzahl;
                }
            }

            if ($conf['kaufabwicklung']['bestellvorgang_versand_steuersatz'] === 'US') {
                $nMaxSumme = 0;
                foreach ($fWarensummeProSteuerklasse_arr as $j => $fWarensummeProSteuerklasse) {
                    if ($fWarensummeProSteuerklasse > $nMaxSumme) {
                        $nMaxSumme     = $fWarensummeProSteuerklasse;
                        $kSteuerklasse = $j;
                    }
                }
            } else {
                $nMaxSteuersatz = 0;
                foreach ($fWarensummeProSteuerklasse_arr as $j => $fWarensummeProSteuerklasse) {
                    if (Tax::getSalesTax($j) > $nMaxSteuersatz) {
                        $nMaxSteuersatz = Tax::getSalesTax($j);
                        $kSteuerklasse  = $j;
                    }
                }
            }

            return \sprintf(
                Shop::Lang()->get('productExtraShippingNotice'),
                Preise::getLocalizedPriceString(
                    Tax::getGross($oVersandart->fEndpreis, Tax::getSalesTax($kSteuerklasse), 4)
                )
            );
        }

        //Versand mit neuen Artikeln gleich oder guenstiger als ohne
        return Shop::Lang()->get('productNoExtraShippingNotice');
    }

    /**
     * @param string         $deliveryCountry
     * @param string         $shippingClasses
     * @param int            $customerGroupID
     * @param Artikel|object $article
     * @param bool           $checkProductDepedency
     * @return mixed
     * @former gibGuenstigsteVersandart()
     */
    public static function getFavourableShippingMethod(
        $deliveryCountry,
        $shippingClasses,
        $customerGroupID,
        $article,
        $checkProductDepedency = true
    ) {
        $favourableIDX   = 0;
        $minVersand      = 10000;
        $cISO            = $deliveryCountry;
        $depOnly         = ($checkProductDepedency && self::normalerArtikelversand($deliveryCountry) === false)
            ? 'Y'
            : 'N';
        $shippingMethods = Shop::Container()->getDB()->queryPrepared(
            "SELECT *
            FROM tversandart
            WHERE cNurAbhaengigeVersandart = :depOnly
                AND cLaender LIKE :iso
                AND (cVersandklassen = '-1' 
                    OR cVersandklassen RLIKE :sClasses)
                AND (cKundengruppen = '-1' 
                    OR FIND_IN_SET(:cGroupID, REPLACE(cKundengruppen, ';', ',')) > 0) 
            ORDER BY nSort",
            [
                'depOnly'  => $depOnly,
                'iso'      => '%' . $cISO . '%',
                'cGroupID' => $customerGroupID,
                'sClasses' => '^([0-9 -]* )?' . $shippingClasses . ' '
            ],
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($shippingMethods as $i => $shippingMethod) {
            $shippingMethod->fEndpreis = self::calculateShippingFees($shippingMethod, $cISO, $article);
            if ($shippingMethod->fEndpreis === -1) {
                unset($shippingMethods[$i]);
                continue;
            }
            if ($shippingMethod->fEndpreis < $minVersand) {
                $minVersand    = $shippingMethod->fEndpreis;
                $favourableIDX = $i;
            }
        }

        return $shippingMethods[$favourableIDX];
    }

    /**
     * Prueft, ob es artikelabhaengige Versandkosten gibt und falls ja,
     * wird die hinzukommende Versandsumme fuer den Artikel
     * der hinzugefuegt werden soll errechnet und zurueckgegeben.
     *
     * @param Artikel $oArtikel
     * @param string  $cLandISO
     * @param float   $fArtikelAnzahl
     * @return bool|stdClass
     */
    public static function gibHinzukommendeArtikelAbhaengigeVersandkosten($oArtikel, $cLandISO, $fArtikelAnzahl)
    {
        $oArtikel->kArtikel              = (int)$oArtikel->kArtikel;
        $nArtikelAbhaengigeVersandkosten = self::pruefeArtikelabhaengigeVersandkosten($oArtikel);
        if ($nArtikelAbhaengigeVersandkosten === 1) {
            return self::gibArtikelabhaengigeVersandkosten($cLandISO, $oArtikel, $fArtikelAnzahl, false);
        }
        if ($nArtikelAbhaengigeVersandkosten === 2) {
            // Gib alle Artikel im Warenkorb, die Artikel abhaengige Versandkosten beinhalten
            $depending = self::gibArtikelabhaengigeVersandkostenImWK(
                $cLandISO,
                Session::getCart()->PositionenArr,
                false
            );

            if (\count($depending) > 0) {
                $nAnzahl = $fArtikelAnzahl;
                $fKosten = 0;
                foreach ($depending as $shipping) {
                    $shipping->kArtikel = (int)$shipping->kArtikel;
                    // Wenn es bereits den hinzukommenden Artikel im Warenkorb gibt
                    // zaehle die Anzahl vom Warenkorb hinzu und gib die Kosten fuer den Artikel im Warenkorb
                    if ($shipping->kArtikel === $oArtikel->kArtikel) {
                        $nAnzahl += $shipping->nAnzahl;
                        $fKosten  = $shipping->fKosten;
                        break;
                    }
                }

                return self::gibArtikelabhaengigeVersandkosten($cLandISO, $oArtikel, $nAnzahl, false) - $fKosten;
            }
        }

        return false;
    }

    /**
     * @param Artikel $oArtikel
     * @return int
     */
    public static function pruefeArtikelabhaengigeVersandkosten(Artikel $oArtikel): int
    {
        $bHookReturn = false;
        \executeHook(\HOOK_TOOLS_GLOBAL_PRUEFEARTIKELABHAENGIGEVERSANDKOSTEN, [
            'oArtikel'    => &$oArtikel,
            'bHookReturn' => &$bHookReturn
        ]);

        if ($bHookReturn) {
            return -1;
        }
        if ($oArtikel->FunktionsAttribute[\FKT_ATTRIBUT_VERSANDKOSTEN]) {
            // Artikelabhaengige Versandkosten
            return 1;
        }
        if ($oArtikel->FunktionsAttribute[\FKT_ATTRIBUT_VERSANDKOSTEN_GESTAFFELT]) {
            // Artikelabhaengige Versandkosten gestaffelt
            return 2;
        }

        return -1;  // Keine artikelabhaengigen Versandkosten
    }

    /**
     * @param string  $cLand
     * @param Artikel $Artikel
     * @param int     $nAnzahl
     * @param bool    $bCheckLieferadresse
     * @return bool|stdClass
     */
    public static function gibArtikelabhaengigeVersandkosten(
        $cLand,
        Artikel $Artikel,
        $nAnzahl,
        bool $bCheckLieferadresse = true
    ) {
        $steuerSatz  = null;
        $bHookReturn = false;
        \executeHook(\HOOK_TOOLS_GLOBAL_GIBARTIKELABHAENGIGEVERSANDKOSTEN, [
            'oArtikel'    => &$Artikel,
            'cLand'       => &$cLand,
            'nAnzahl'     => &$nAnzahl,
            'bHookReturn' => &$bHookReturn
        ]);

        if ($bHookReturn) {
            return false;
        }
        $netPricesActive = Session::getCustomerGroup()->isMerchant();
        // Steuersatz nur benötigt, wenn Nettokunde
        if ($netPricesActive === true) {
            $steuerSatz = Shop::Container()->getDB()->select(
                'tsteuersatz',
                'kSteuerklasse',
                Session::getCart()->gibVersandkostenSteuerklasse()
            )->fSteuersatz;
        }
        // gestaffelte
        if (!empty($Artikel->FunktionsAttribute[\FKT_ATTRIBUT_VERSANDKOSTEN_GESTAFFELT])) {
            $arrVersand = \array_filter(\explode(
                ';',
                $Artikel->FunktionsAttribute[\FKT_ATTRIBUT_VERSANDKOSTEN_GESTAFFELT]
            ));
            foreach ($arrVersand as $cVersand) {
                // DE 1-45,00:2-60,00:3-80;AT 1-90,00:2-120,00:3-150,00
                [$cLandAttr, $KostenTeil] = \explode(' ', $cVersand);
                if ($cLandAttr && ($cLand === $cLandAttr || $bCheckLieferadresse === false)) {
                    $arrKosten = \explode(':', $KostenTeil);
                    foreach ($arrKosten as $staffel) {
                        [$bisAnzahl, $fPreis] = \explode('-', $staffel);
                        $fPreis               = (float)\str_replace(',', '.', $fPreis);
                        if ($fPreis >= 0 && $bisAnzahl > 0 && $nAnzahl <= $bisAnzahl) {
                            $oVersandPos = new stdClass();
                            //posname lokalisiert ablegen
                            $oVersandPos->cName = [];
                            foreach ($_SESSION['Sprachen'] as $Sprache) {
                                $oVersandPos->cName[$Sprache->cISO] = Shop::Lang()->get('shippingFor', 'checkout') .
                                    ' ' . $Artikel->cName . ' (' . $cLandAttr . ')';
                            }
                            $oVersandPos->fKosten = $fPreis;
                            if ($netPricesActive === true) {
                                $oVersandPos->cPreisLocalized = Preise::getLocalizedPriceString(
                                    Tax::getNet((float)$oVersandPos->fKosten, $steuerSatz)
                                ) . ' ' . Shop::Lang()->get('plus', 'productDetails') . ' ' .
                                Shop::Lang()->get('vat', 'productDetails');
                            } else {
                                $oVersandPos->cPreisLocalized = Preise::getLocalizedPriceString($oVersandPos->fKosten);
                            }

                            return $oVersandPos;
                        }
                    }
                }
            }
        }
        // flache
        if (!empty($Artikel->FunktionsAttribute[\FKT_ATTRIBUT_VERSANDKOSTEN])) {
            $arrVersand = \array_filter(\explode(';', $Artikel->FunktionsAttribute[\FKT_ATTRIBUT_VERSANDKOSTEN]));
            foreach ($arrVersand as $cVersand) {
                [$cLandAttr, $fKosten] = \explode(' ', $cVersand);
                if ($cLandAttr && ($cLand === $cLandAttr || $bCheckLieferadresse === false)) {
                    $oVersandPos = new stdClass();
                    //posname lokalisiert ablegen
                    $oVersandPos->cName = [];
                    foreach ($_SESSION['Sprachen'] as $Sprache) {
                        $oVersandPos->cName[$Sprache->cISO] = Shop::Lang()->get('shippingFor', 'checkout') . ' ' .
                            $Artikel->cName . ' (' . $cLandAttr . ')';
                    }
                    $oVersandPos->fKosten = (float)\str_replace(',', '.', $fKosten) * $nAnzahl;
                    if ($netPricesActive === true) {
                        $oVersandPos->cPreisLocalized = Preise::getLocalizedPriceString(Tax::getNet(
                            (float)$oVersandPos->fKosten,
                            $steuerSatz
                        )) . ' ' . Shop::Lang()->get('plus', 'productDetails') . ' ' .
                        Shop::Lang()->get('vat', 'productDetails');
                    } else {
                        $oVersandPos->cPreisLocalized = Preise::getLocalizedPriceString($oVersandPos->fKosten);
                    }

                    return $oVersandPos;
                }
            }
        }

        return false;
    }

    /**
     * @param string $country
     * @param array  $positions
     * @param bool   $checkDelivery
     * @return array
     */
    public static function gibArtikelabhaengigeVersandkostenImWK($country, $positions, $checkDelivery = true): array
    {
        $arrVersandpositionen = [];
        if (!\is_array($positions)) {
            return $arrVersandpositionen;
        }
        $positions = \array_filter($positions, function ($pos) {
            return (int)$pos->nPosTyp === \C_WARENKORBPOS_TYP_ARTIKEL;
        });
        foreach ($positions as $pos) {
            $shippingPos = self::gibArtikelabhaengigeVersandkosten(
                $country,
                $pos->Artikel,
                $pos->nAnzahl,
                $checkDelivery
            );
            if (!empty($shippingPos->cName)) {
                $shippingPos->kArtikel  = (int)$pos->Artikel->kArtikel;
                $arrVersandpositionen[] = $shippingPos;
            }
        }

        return $arrVersandpositionen;
    }

    /**
     * @param Warenkorb $Warenkorb
     * @return string
     */
    public static function getShippingClasses(Warenkorb $Warenkorb): string
    {
        $VKarr = [];
        foreach ($Warenkorb->PositionenArr as $pos) {
            $pos->kVersandklasse = (int)$pos->kVersandklasse;
            if ((int)$pos->nPosTyp === \C_WARENKORBPOS_TYP_ARTIKEL
                && $pos->kVersandklasse > 0
                && !\in_array($pos->kVersandklasse, $VKarr, true)
            ) {
                $VKarr[] = $pos->kVersandklasse;
            }
        }
        \sort($VKarr);

        return \implode('-', $VKarr);
    }

    /**
     * @param Versandart|object $versandart
     * @param string            $cISO
     * @param string            $plz
     * @return stdClass|null
     * @former gibVersandZuschlag()
     */
    public static function getAdditionalFees($versandart, $cISO, $plz): ?stdClass
    {
        $db   = Shop::Container()->getDB();
        $fees = $db->selectAll(
            'tversandzuschlag',
            ['kVersandart', 'cISO'],
            [(int)$versandart->kVersandart, $cISO]
        );
        foreach ($fees as $fee) {
            $plz_x = $db->queryPrepared(
                'SELECT * FROM tversandzuschlagplz
                    WHERE ((cPLZAb <= :plz
                        AND cPLZBis >= :plz)
                        OR cPLZ = :plz)
                        AND kVersandzuschlag = :sid',
                ['plz' => $plz, 'sid' => (int)$fee->kVersandzuschlag],
                ReturnType::SINGLE_OBJECT
            );
            if (isset($plz_x->kVersandzuschlagPlz) && $plz_x->kVersandzuschlagPlz > 0) {
                $fee->angezeigterName = [];
                foreach (Session::getLanguages() as $Sprache) {
                    $localized = $db->select(
                        'tversandzuschlagsprache',
                        'kVersandzuschlag',
                        (int)$fee->kVersandzuschlag,
                        'cISOSprache',
                        $Sprache->cISO
                    );

                    $fee->angezeigterName[$Sprache->cISO] = $localized->cName;
                }
                $fee->cPreisLocalized = Preise::getLocalizedPriceString($fee->fZuschlag);

                return $fee;
            }
        }

        return null;
    }

    /**
     * @todo Hier gilt noch zu beachten, dass fWarenwertNetto vom Zusatzartikel
     *       darf kein Netto sein, sondern der Preis muss in Brutto angegeben werden.
     * @param Versandart|object $versandart
     * @param String            $cISO
     * @param Artikel|stdClass  $oZusatzArtikel
     * @param Artikel|int       $Artikel
     * @param bool              $excludeShippingCostAttributes - exclude articles with these attributes from weight, amount and count calculation
     * @return int|string
     * @former berechneVersandpreis()
     */
    public static function calculateShippingFees($versandart, $cISO, $oZusatzArtikel, $Artikel = 0, $excludeShippingCostAttributes = false)
    {
        if (!isset($oZusatzArtikel->fAnzahl)) {
            if ($oZusatzArtikel === null) {
                $oZusatzArtikel = new stdClass();
            }
            $oZusatzArtikel->fAnzahl         = 0;
            $oZusatzArtikel->fWarenwertNetto = 0;
            $oZusatzArtikel->fGewicht        = 0;
        }
        $versandberechnung = Shop::Container()->getDB()->select(
            'tversandberechnung',
            'kVersandberechnung',
            $versandart->kVersandberechnung
        );
        $preis             = 0;
        switch ($versandberechnung->cModulId) {
            case 'vm_versandkosten_pauschale_jtl':
                $preis = $versandart->fPreis;
                break;

            case 'vm_versandberechnung_gewicht_jtl':
                $warenkorbgewicht  = $Artikel
                    ? $Artikel->fGewicht
                    : Session::getCart()->getWeight($cISO, $excludeShippingCostAttributes);
                $warenkorbgewicht += $oZusatzArtikel->fGewicht;
                $versand           = Shop::Container()->getDB()->queryPrepared(
                    'SELECT *
                        FROM tversandartstaffel
                        WHERE kVersandart = :sid
                            AND fBis >= :wght
                        ORDER BY fBis ASC',
                    ['sid' => (int)$versandart->kVersandart, 'wght' => $warenkorbgewicht],
                    ReturnType::SINGLE_OBJECT
                );
                if (isset($versand->kVersandartStaffel)) {
                    $preis = $versand->fPreis;
                } else {
                    return -1;
                }
                break;

            case 'vm_versandberechnung_warenwert_jtl':
                $warenkorbwert  = $Artikel
                    ? $Artikel->Preise->fVKNetto
                    : Session::getCart()->gibGesamtsummeWarenExt([\C_WARENKORBPOS_TYP_ARTIKEL], true, $cISO, $excludeShippingCostAttributes);
                $warenkorbwert += $oZusatzArtikel->fWarenwertNetto;
                $versand        = Shop::Container()->getDB()->queryPrepared(
                    'SELECT *
                        FROM tversandartstaffel
                        WHERE kVersandart = :sid
                            AND fBis >= :val
                        ORDER BY fBis ASC',
                    ['sid' => (int)$versandart->kVersandart, 'val' => $warenkorbwert],
                    ReturnType::SINGLE_OBJECT
                );
                if (isset($versand->kVersandartStaffel)) {
                    $preis = $versand->fPreis;
                } else {
                    return -1;
                }
                break;

            case 'vm_versandberechnung_artikelanzahl_jtl':
                $artikelanzahl = 1;
                if (!$Artikel) {
                    $artikelanzahl = isset($_SESSION['Warenkorb'])
                        ? Session::getCart()->gibAnzahlArtikelExt([\C_WARENKORBPOS_TYP_ARTIKEL], $cISO, $excludeShippingCostAttributes)
                        : 0;
                }
                $artikelanzahl += $oZusatzArtikel->fAnzahl;
                $versand        = Shop::Container()->getDB()->queryPrepared(
                    'SELECT *
                        FROM tversandartstaffel
                        WHERE kVersandart = :sid
                            AND fBis >= :cnt
                        ORDER BY fBis ASC',
                    ['sid' => (int)$versandart->kVersandart, 'cnt' => $artikelanzahl],
                    ReturnType::SINGLE_OBJECT
                );
                if (isset($versand->kVersandartStaffel)) {
                    $preis = $versand->fPreis;
                } else {
                    return -1;
                }
                break;

            default:
                //bearbeite fremdmodule
                break;
        }
        //artikelabhaengiger Versand?
        if ($versandart->cNurAbhaengigeVersandart === 'Y'
            && (!empty($Artikel->FunktionsAttribute[FKT_ATTRIBUT_VERSANDKOSTEN])
                || !empty($Artikel->FunktionsAttribute[FKT_ATTRIBUT_VERSANDKOSTEN_GESTAFFELT]))
        ) {
            $fArticleSpecific = self::gibArtikelabhaengigeVersandkosten($cISO, $Artikel, 1);
            $preis           += $fArticleSpecific->fKosten ?? 0;
        }
        //Deckelung?
        if ($preis >= $versandart->fDeckelung && $versandart->fDeckelung > 0) {
            $preis = $versandart->fDeckelung;
        }
        //Zuschlag
        if (isset($versandart->Zuschlag->fZuschlag) && $versandart->Zuschlag->fZuschlag != 0) {
            $preis += $versandart->Zuschlag->fZuschlag;
        }
        //versandkostenfrei?
        $fArtikelPreis     = 0;
        $fGesamtsummeWaren = 0;
        if ($versandart->eSteuer === 'netto') {
            if ($Artikel) {
                $fArtikelPreis = $Artikel->Preise->fVKNetto;
            }
            if (isset($_SESSION['Warenkorb'])) {
                $fGesamtsummeWaren = Tax::getNet(
                    Session::getCart()->gibGesamtsummeWarenExt([\C_WARENKORBPOS_TYP_ARTIKEL], true),
                    Tax::getSalesTax(Session::getCart()->gibVersandkostenSteuerklasse())
                );
            }
        } elseif ($versandart->eSteuer === 'brutto') {
            if ($Artikel) {
                $fArtikelPreis = Tax::getGross(
                    $Artikel->Preise->fVKNetto,
                    Tax::getSalesTax($Artikel->kSteuerklasse)
                );
            }
            if (isset($_SESSION['Warenkorb'])) {
                $fGesamtsummeWaren = Session::getCart()->gibGesamtsummeWarenExt(
                    [\C_WARENKORBPOS_TYP_ARTIKEL],
                    true
                );
            }
        }

        if ($versandart->fVersandkostenfreiAbX > 0
            && (($Artikel && $fArtikelPreis >= $versandart->fVersandkostenfreiAbX)
                || ($fGesamtsummeWaren >= $versandart->fVersandkostenfreiAbX))
        ) {
            $preis = 0;
        }
        \executeHook(\HOOK_TOOLSGLOBAL_INC_BERECHNEVERSANDPREIS, [
            'fPreis'         => &$preis,
            'versandart'     => $versandart,
            'cISO'           => $cISO,
            'oZusatzArtikel' => $oZusatzArtikel,
            'Artikel'        => $Artikel,
        ]);

        return $preis;
    }

    /**
     * calculate shipping costs for exports
     *
     * @param string  $iso
     * @param Artikel $product
     * @param int     $allowCash
     * @param int     $customerGroupID
     * @return int|float
     * @former gibGuenstigsteVersandkosten()
     */
    public static function getLowestShippingFees($iso, $product, $allowCash, $customerGroupID)
    {
        $dep = '';
        $fee = 99999;
        $db  = Shop::Container()->getDB();
        if (empty($product->FunktionsAttribute[\FKT_ATTRIBUT_VERSANDKOSTEN])
            && empty($product->FunktionsAttribute[\FKT_ATTRIBUT_VERSANDKOSTEN_GESTAFFELT])
        ) {
            $dep = " AND cNurAbhaengigeVersandart = 'N'";
        }
        $methods = $db->queryPrepared(
            "SELECT *
                FROM tversandart
                WHERE cIgnoreShippingProposal != 'Y'
                    AND cLaender LIKE :iso
                    AND (cVersandklassen = '-1'
                        OR cVersandklassen RLIKE :scls)
                    AND (cKundengruppen = '-1'
                        OR FIND_IN_SET(:cgid, REPLACE(cKundengruppen, ';', ',')) > 0)" . $dep,
            [
                'iso'  => '%' . $iso . '%',
                'scls' => '^([0-9 -]* )?' . $product->kVersandklasse,
                'cgid' => $customerGroupID
            ],
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($methods as $method) {
            if (!$allowCash) {
                $cash = $db->select(
                    'tversandartzahlungsart',
                    'kZahlungsart',
                    6,
                    'kVersandart',
                    (int)$method->kVersandart
                );
                if ($cash !== null && isset($cash->kVersandartZahlungsart) && $cash->kVersandartZahlungsart > 0) {
                    continue;
                }
            }
            $vp = self::calculateShippingFees($method, $iso, null, $product);
            if ($vp !== -1 && $vp < $fee) {
                $fee = $vp;
            }
            if ($vp === 0) {
                break;
            }
        }

        return $fee === 99999 ? -1 : $fee;
    }

    /**
     * @param int $minDeliveryDays
     * @param int $maxDeliveryDays
     * @return string
     */
    public static function getDeliverytimeEstimationText(int $minDeliveryDays, int $maxDeliveryDays): string
    {
        $deliveryText = $minDeliveryDays === $maxDeliveryDays
            ? \str_replace(
                '#DELIVERYDAYS#',
                $minDeliveryDays,
                Shop::Lang()->get('deliverytimeEstimationSimple')
            )
            : \str_replace(
                ['#MINDELIVERYDAYS#', '#MAXDELIVERYDAYS#'],
                [$minDeliveryDays, $maxDeliveryDays],
                Shop::Lang()->get('deliverytimeEstimation')
            );

        \executeHook(\HOOK_GET_DELIVERY_TIME_ESTIMATION_TEXT, [
            'min'  => $minDeliveryDays,
            'max'  => $maxDeliveryDays,
            'text' => &$deliveryText
        ]);

        return $deliveryText;
    }

    /**
     * @param Versandart|object $oVersandart
     * @param float             $fWarenkorbSumme
     * @return string
     * @former baueVersandkostenfreiString()
     */
    public static function getShippingFreeString($oVersandart, $fWarenkorbSumme): string
    {
        if (isset($_SESSION['oVersandfreiKupon'])) {
            return '';
        }
        if (!\is_object($oVersandart)
            || (float)$oVersandart->fVersandkostenfreiAbX <= 0
            || !isset($_SESSION['Warenkorb'], $_SESSION['Steuerland'])
        ) {
            return '';
        }
        $fSummeDiff = (float)$oVersandart->fVersandkostenfreiAbX - (float)$fWarenkorbSumme;
        // check if vkfreiabx is calculated net or gross
        if ($oVersandart->eSteuer === 'netto') {
            // calculate net with default tax class
            $defaultTaxClass = Shop::Container()->getDB()->select('tsteuerklasse', 'cStandard', 'Y');
            if ($defaultTaxClass !== null && isset($defaultTaxClass->kSteuerklasse)) {
                $taxClasss  = (int)$defaultTaxClass->kSteuerklasse;
                $defaultTax = Shop::Container()->getDB()->select('tsteuersatz', 'kSteuerklasse', $taxClasss);
                if ($defaultTax !== null) {
                    $defaultTaxValue = $defaultTax->fSteuersatz;
                    $fSummeDiff      = (float)$oVersandart->fVersandkostenfreiAbX -
                        Tax::getNet((float)$fWarenkorbSumme, $defaultTaxValue);
                }
            }
        }
        if (isset($oVersandart->cNameLocalized)) {
            $name = $oVersandart->cNameLocalized;
        } else {
            $VersandartSprache = Shop::Container()->getDB()->select(
                'tversandartsprache',
                'kVersandart',
                $oVersandart->kVersandart,
                'cISOSprache',
                Shop::getLanguageCode()
            );
            $name             = !empty($VersandartSprache->cName)
                ? $VersandartSprache->cName
                : $oVersandart->cName;
        }
        if ($fSummeDiff <= 0) {
            return \sprintf(
                Shop::Lang()->get('noShippingCostsReached', 'basket'),
                $name,
                self::getShippingFreeCountriesString($oVersandart),
                (string)$oVersandart->cLaender
            );
        }

        return \sprintf(
            Shop::Lang()->get('noShippingCostsAt', 'basket'),
            Preise::getLocalizedPriceString($fSummeDiff),
            $name,
            self::getShippingFreeCountriesString($oVersandart)
        );
    }

    /**
     * @param Versandart $oVersandart
     * @return string
     * @former baueVersandkostenfreiLaenderString()
     */
    public static function getShippingFreeCountriesString($oVersandart): string
    {
        if (!\is_object($oVersandart) || (float)$oVersandart->fVersandkostenfreiAbX <= 0) {
            return '';
        }
        $cacheID = 'bvkfls_' .
            $oVersandart->fVersandkostenfreiAbX .
            \strlen($oVersandart->cLaender) . '_' .
            Shop::getLanguageID();
        if (($vkfls = Shop::Container()->getCache()->get($cacheID)) === false) {
            // remove empty strings
            $cLaender_arr = \array_filter(\explode(' ', $oVersandart->cLaender));
            // only select the needed row
            $select = $_SESSION['cISOSprache'] === 'ger'
                ? 'cDeutsch'
                : 'cEnglisch';
            // generate IN sql statement with stringified country isos
            $sql       = ' cISO IN (' . \implode(', ', \array_map(function ($iso) {
                return "'" . $iso . "'";
            }, $cLaender_arr)) . ')';
            $countries = Shop::Container()->getDB()->query(
                'SELECT ' . $select . ' AS name
                FROM tland
                WHERE ' . $sql,
                ReturnType::ARRAY_OF_OBJECTS
            );
            // re-concatinate isos with "," for the final output
            $resultString = \implode(', ', \array_map(function ($e) {
                return $e->name;
            }, $countries));

            $vkfls = \sprintf(Shop::Lang()->get('noShippingCostsAtExtended', 'basket'), $resultString);
            Shop::Container()->getCache()->set($cacheID, $vkfls, [\CACHING_GROUP_OPTION]);
        }

        return $vkfls;
    }

    /**
     * @param int    $customerGroupID
     * @param string $country
     * @return int|mixed
     * @former gibVersandkostenfreiAb()
     */
    public static function getFreeShippingMinimum(int $customerGroupID, $country = '')
    {
        $shippingClasses = self::getShippingClasses(Session::getCart());
        $defaultShipping = self::normalerArtikelversand($country);
        $cacheID         = 'vkfrei_' . $customerGroupID . '_' .
            $country . '_' . $shippingClasses . '_' . Shop::getLanguageCode();
        if (($oVersandart = Shop::Container()->getCache()->get($cacheID)) === false) {
            if (\strlen($country) > 0) {
                $customerSQL = " AND cLaender LIKE '%" . StringHandler::filterXSS($country) . "%'";
            } else {
                $landIso     = Shop::Container()->getDB()->query(
                    'SELECT cISO
                        FROM tfirma
                        JOIN tland
                            ON tfirma.cLand = tland.cDeutsch
                        LIMIT 0,1',
                    ReturnType::SINGLE_OBJECT
                );
                $customerSQL = isset($landIso->cISO)
                    ? " AND cLaender LIKE '%" . $landIso->cISO . "%'"
                    : '';
            }
            $cProductSpecificSQLWhere = empty($defaultShipping) ? '' : " AND cNurAbhaengigeVersandart = 'N' ";
            $oVersandart              = Shop::Container()->getDB()->queryPrepared(
                "SELECT tversandart.*, tversandartsprache.cName AS cNameLocalized
                    FROM tversandart
                    LEFT JOIN tversandartsprache
                        ON tversandart.kVersandart = tversandartsprache.kVersandart
                        AND tversandartsprache.cISOSprache = :cLangID
                    WHERE fVersandkostenfreiAbX > 0
                        AND (cVersandklassen = '-1'
                            OR cVersandklassen RLIKE :cShippingClass)
                        AND (cKundengruppen = '-1'
                            OR FIND_IN_SET(:cGroupID, REPLACE(cKundengruppen, ';', ',')) > 0)
                        " . $customerSQL . $cProductSpecificSQLWhere . '
                    ORDER BY tversandart.fVersandkostenfreiAbX, tversandart.nSort ASC
                    LIMIT 1',
                [
                    'cLangID'        => Shop::getLanguageCode(),
                    'cShippingClass' => '^([0-9 -]* )?' . $shippingClasses . ' ',
                    'cGroupID'       => $customerGroupID
                ],
                ReturnType::SINGLE_OBJECT
            );
            Shop::Container()->getCache()->set($cacheID, $oVersandart, [\CACHING_GROUP_OPTION]);
        }

        return !empty($oVersandart) && $oVersandart->fVersandkostenfreiAbX > 0
            ? $oVersandart
            : 0;
    }

    /**
     * @param int   $customerGroupID
     * @param bool  $ignoreConf
     * @param bool  $force
     * @param array $filterISO
     * @return array
     * @former gibBelieferbareLaender()
     * @since 5.0.0
     */
    public static function getPossibleShippingCountries(
        int $customerGroupID = 0,
        bool $ignoreConf = false,
        bool $force = false,
        array $filterISO = []
    ): array {
        if (empty($customerGroupID)) {
            $customerGroupID = Kundengruppe::getDefaultGroupID();
        }
        $conf    = Shop::getSettings([\CONF_KUNDEN]);
        $colName = Sprache::getInstance()->gibISO() === 'ger' ? 'cDeutsch' : 'cEnglisch';

        if (!$force && ($conf['kunden']['kundenregistrierung_nur_lieferlaender'] === 'Y' || $ignoreConf)) {
            $countries = Shop::Container()->getDB()->query(
                'SELECT DISTINCT tland.cISO, ' . $colName . " AS cName
                    FROM tland
                    INNER JOIN tversandart ON FIND_IN_SET(tland.cISO, REPLACE(tversandart.cLaender, ' ', ','))
                    WHERE (tversandart.cKundengruppen = '-1'
                        OR FIND_IN_SET('" . $customerGroupID . "', REPLACE(cKundengruppen, ';', ',')) > 0)
                        " . (\count($filterISO) > 0
                    ? "AND tland.cISO IN ('" . \implode("','", $filterISO) . "')"
                    : '') .
                    ' ORDER BY CONVERT(' . $colName . ' USING utf8) COLLATE utf8_german2_ci',
                ReturnType::ARRAY_OF_OBJECTS
            );
        } else {
            $countries = Shop::Container()->getDB()->query(
                'SELECT cISO, ' . $colName . ' AS cName
                    FROM tland ' . (\count($filterISO) > 0
                        ? "WHERE tland.cISO IN ('" . \implode("','", $filterISO) . "')"
                        : '') .
                ' ORDER BY CONVERT(' . $colName . ' USING utf8) COLLATE utf8_german2_ci',
                ReturnType::ARRAY_OF_OBJECTS
            );
        }
        \executeHook(\HOOK_TOOLSGLOBAL_INC_GIBBELIEFERBARELAENDER, [
            'oLaender_arr' => &$countries
        ]);

        return $countries;
    }

    /**
     * @param int $customerGroupID
     * @return array
     * @former gibMoeglicheVerpackungen()
     * @since 5.0.0
     */
    public static function getPossiblePackagings(int $customerGroupID): array
    {
        $cartSum      = Session::getCart()->gibGesamtsummeWarenExt([\C_WARENKORBPOS_TYP_ARTIKEL], true);
        $packagings   = Shop::Container()->getDB()->queryPrepared(
            "SELECT * FROM tverpackung
                JOIN tverpackungsprache
                    ON tverpackung.kVerpackung = tverpackungsprache.kVerpackung
                WHERE tverpackungsprache.cISOSprache = :lcode
                AND (tverpackung.cKundengruppe = '-1'
                    OR FIND_IN_SET(:cid, REPLACE(tverpackung.cKundengruppe, ';', ',')) > 0)
                AND :csum >= tverpackung.fMindestbestellwert
                AND tverpackung.nAktiv = 1
                ORDER BY tverpackung.kVerpackung",
            [
                'lcode' => Shop::getLanguageCode(),
                'cid'   => $customerGroupID,
                'csum'  => $cartSum
            ],
            ReturnType::ARRAY_OF_OBJECTS
        );
        $currencyCode = Session::getCurrency()->getID();
        foreach ($packagings as $packaging) {
            $packaging->nKostenfrei      = ($cartSum >= $packaging->fKostenfrei
                && $packaging->fBrutto > 0
                && $packaging->fKostenfrei != 0)
                ? 1
                : 0;
            $packaging->fBruttoLocalized = Preise::getLocalizedPriceString($packaging->fBrutto, $currencyCode);
        }

        return $packagings;
    }
}
