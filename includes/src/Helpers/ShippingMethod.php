<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Helpers;

use JTL\Catalog\Product\Artikel;
use JTL\Cart\Warenkorb;
use JTL\DB\ReturnType;
use JTL\Customer\Kundengruppe;
use JTL\Catalog\Product\Preise;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Sprache;
use JTL\Checkout\Versandart;
use stdClass;

/**
 * Class ShippingMethod
 * @package JTL\Helpers
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
     * @param float|int $price
     * @param int       $cgroupID
     * @param int       $shippingClassID
     * @return string
     */
    public function getFreeShippingCountries($price, int $cgroupID, int $shippingClassID = 0): string
    {
        if (!isset($this->countries[$cgroupID][$shippingClassID])) {
            if (!isset($this->countries[$cgroupID])) {
                $this->countries[$cgroupID] = [];
            }
            $this->countries[$cgroupID][$shippingClassID] = Shop::Container()->getDB()->queryPrepared(
                "SELECT *
                    FROM tversandart
                    WHERE fVersandkostenfreiAbX > 0
                        AND (cVersandklassen = '-1'
                        OR cVersandklassen RLIKE :sClasses)
                        AND (cKundengruppen = '-1' OR FIND_IN_SET(:cGroupID, REPLACE(cKundengruppen, ';', ',')) > 0)",
                [
                    'sClasses' => '^([0-9 -]* )?' . $shippingClassID . ' ',
                    'cGroupID' => $cgroupID
                ],
                ReturnType::ARRAY_OF_OBJECTS
            );
        }
        $shippingFreeCountries = [];
        foreach ($this->countries[$cgroupID][$shippingClassID] as $_method) {
            if (isset($_method->fVersandkostenfreiAbX)
                && (float)$_method->fVersandkostenfreiAbX > 0
                && (float)$_method->fVersandkostenfreiAbX < $price
            ) {
                foreach (\explode(' ', $_method->cLaender) as $_country) {
                    if (\mb_strlen($_country) > 0) {
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
     * @param string $country
     * @return bool
     */
    public static function normalerArtikelversand($country): bool
    {
        foreach (Frontend::getCart()->PositionenArr as $pos) {
            if ((int)$pos->nPosTyp === \C_WARENKORBPOS_TYP_ARTIKEL
                && !self::gibArtikelabhaengigeVersandkosten($country, $pos->Artikel, $pos->nAnzahl)
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $cLand
     * @return bool
     */
    public static function hasSpecificShippingcosts($cLand): bool
    {
        return !empty(self::gibArtikelabhaengigeVersandkostenImWK($cLand, Frontend::getCart()->PositionenArr));
    }

    /**
     * @former gibMoeglicheVersandarten()
     * @param string $countryCode
     * @param string $zip
     * @param string $shippingClasses
     * @param int    $cgroupID
     * @return array
     */
    public static function getPossibleShippingMethods($countryCode, $zip, $shippingClasses, int $cgroupID): array
    {
        $db                       = Shop::Container()->getDB();
        $cart                     = Frontend::getCart();
        $kSteuerklasse            = $cart->gibVersandkostenSteuerklasse();
        $minVersand               = 10000;
        $hasSpecificShippingcosts = self::hasSpecificShippingcosts($countryCode);
        $vatNote                  = null;
        $cNurAbhaengigeVersandart = self::normalerArtikelversand($countryCode) === false
            ? 'Y'
            : 'N';
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
                'iso'      => '%' . $countryCode . '%',
                'cGroupID' => $cgroupID,
                'sClasses' => '^([0-9 -]* )?' . $shippingClasses . ' ',
                'depOnly'  => $cNurAbhaengigeVersandart
            ],
            ReturnType::ARRAY_OF_OBJECTS
        );
        $netPricesActive          = Frontend::getCustomerGroup()->isMerchant();

        foreach ($methods as $i => $shippingMethod) {
            $bSteuerPos = $shippingMethod->eSteuer !== 'netto';

            $shippingMethod->kVersandart        = (int)$shippingMethod->kVersandart;
            $shippingMethod->kVersandberechnung = (int)$shippingMethod->kVersandberechnung;
            $shippingMethod->nSort              = (int)$shippingMethod->nSort;
            $shippingMethod->nMinLiefertage     = (int)$shippingMethod->nMinLiefertage;
            $shippingMethod->nMaxLiefertage     = (int)$shippingMethod->nMaxLiefertage;
            $shippingMethod->Zuschlag           = self::getAdditionalFees($shippingMethod, $countryCode, $zip);
            $shippingMethod->fEndpreis          = self::calculateShippingFees($shippingMethod, $countryCode, null);
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
                        $countryCode,
                        $cart->PositionenArr
                    );
                }
            } else {
                // Abfrage ob ein Artikel Artikelabhängige Versandkosten besitzt
                $shippingMethod->cPreisLocalized = Preise::getLocalizedPriceString($shippingCosts) . ($vatNote ?? '');
                if ($hasSpecificShippingcosts === true) {
                    $shippingMethod->specificShippingcosts_arr = self::gibArtikelabhaengigeVersandkostenImWK(
                        $countryCode,
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
                    'cGroupID' => $cgroupID
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
     * @param string $country
     * @param string $zip
     * @param string $errorMsg
     * @return bool
     */
    public static function getShippingCosts($country, $zip, &$errorMsg = ''): bool
    {
        if ($country !== null && $zip !== null && \mb_strlen($country) > 0 && \mb_strlen($zip) > 0) {
            $cgroupID = Frontend::getCustomerGroup()->getID();
            if (isset($_SESSION['Kunde']->kKundengruppe) && $_SESSION['Kunde']->kKundengruppe > 0) {
                $cgroupID = $_SESSION['Kunde']->kKundengruppe;
            }

            $shippingMethods = self::getPossibleShippingMethods(
                Text::filterXSS($country),
                Text::filterXSS($zip),
                self::getShippingClasses(Frontend::getCart()),
                $cgroupID
            );
            if (\count($shippingMethods) > 0) {
                Shop::Smarty()
                    ->assign('ArtikelabhaengigeVersandarten', self::gibArtikelabhaengigeVersandkostenImWK(
                        $country,
                        Frontend::getCart()->PositionenArr
                    ))
                    ->assign('Versandarten', $shippingMethods)
                    ->assign('Versandland', Sprache::getCountryCodeByCountryName($country))
                    ->assign('VersandPLZ', Text::filterXSS($zip));
            } else {
                $errorMsg = Shop::Lang()->get('noDispatchAvailable');
            }
            \executeHook(\HOOK_WARENKORB_PAGE_ERMITTLEVERSANDKOSTEN);

            return true;
        }

        return !(isset($_POST['versandrechnerBTN']) && (\mb_strlen($country) === 0 || \mb_strlen($zip) === 0));
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
        $db       = Shop::Container()->getDB();
        $iso      = $_SESSION['cLieferlandISO'] ?? false;
        $cart     = Frontend::getCart();
        $cgroupID = Frontend::getCustomerGroup()->getID();
        if (!$iso) {
            // Falls kein Land in tfirma da
            $iso = 'DE';
        }
        // Baue ZusatzArtikel
        $additionalProduct                  = new stdClass();
        $additionalProduct->fAnzahl         = 0;
        $additionalProduct->fWarenwertNetto = 0;
        $additionalProduct->fGewicht        = 0;

        $shippingClasses        = self::getShippingClasses($cart);
        $conf                   = Shop::getSettings([\CONF_KAUFABWICKLUNG]);
        $additionalShippingFees = 0;
        $perTaxClass            = [];
        $taxClassID             = 0;
        // Vorkonditionieren -- Gleiche kartikel aufsummieren
        // aber nur, wenn artikelabhaengiger Versand bei dem jeweiligen kArtikel
        $productIDs = [];
        foreach ($products as $product) {
            $productID              = (int)$product['kArtikel'];
            $productIDs[$productID] = isset($productIDs[$productID]) ? 1 : 0;
        }
        $merge          = false;
        $defaultOptions = Artikel::getDefaultOptions();
        foreach ($productIDs as $productID => $nArtikelAssoc) {
            if ($nArtikelAssoc !== 1) {
                continue;
            }
            $tmpProduct = (new Artikel())->fuelleArtikel($productID, $defaultOptions);
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
                    if ($prod['kArtikel'] === $productID) {
                        $fAnzahl += $prod['fAnzahl'];
                        unset($products[$i]);
                    }
                }

                $merged             = [];
                $merged['kArtikel'] = $productID;
                $merged['fAnzahl']  = $fAnzahl;
                $products[]         = $merged;
                $merge              = true;
            }
        }

        if ($merge) {
            $products = \array_merge($products);
        }
        $defaultOptions = Artikel::getDefaultOptions();
        foreach ($products as $i => $product) {
            $tmpProduct = (new Artikel())->fuelleArtikel($product['kArtikel'], $defaultOptions);
            if ($tmpProduct === null || $tmpProduct->kArtikel <= 0) {
                continue;
            }
            $taxClassID = $tmpProduct->kSteuerklasse;
            // Artikelabhaengige Versandkosten?
            if ($tmpProduct->nIstVater === 0) {
                //Summen pro Steuerklasse summieren
                if ($tmpProduct->kSteuerklasse === null) {
                    $perTaxClass[$tmpProduct->kSteuerklasse] = 0;
                }

                $perTaxClass[$tmpProduct->kSteuerklasse] += $tmpProduct->Preise->fVKNetto * $product['fAnzahl'];

                $oVersandPos = self::gibHinzukommendeArtikelAbhaengigeVersandkosten(
                    $tmpProduct,
                    $iso,
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

                if (\mb_strlen($shippingClasses) > 0
                    && \mb_strpos($shippingClasses, $tmpProduct->kVersandklasse) === false
                ) {
                    $shippingClasses = '-' . $tmpProduct->kVersandklasse;
                } elseif (\mb_strlen($shippingClasses) === 0) {
                    $shippingClasses = $tmpProduct->kVersandklasse;
                }
            } elseif ($tmpProduct->nIstVater === 0
                && $tmpProduct->kVaterArtikel === 0
                && \count($tmpProduct->Variationen) > 0
            ) { // Normale Variation
                if ($product['cInputData']{0} === '_') {
                    // 1D
                    $cVariation0                         = \mb_substr($product['cInputData'], 1);
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
                if (\mb_strlen($shippingClasses) > 0
                    && \mb_strpos($shippingClasses, $tmpProduct->kVersandklasse) === false
                ) {
                    $shippingClasses = '-' . $tmpProduct->kVersandklasse;
                } elseif (\mb_strlen($shippingClasses) === 0) {
                    $shippingClasses = $tmpProduct->kVersandklasse;
                }
            } elseif ($tmpProduct->nIstVater > 0) { // Variationskombination (Vater)
                $child = new Artikel();
                if ($product['cInputData']{0} === '_') {
                    // 1D
                    $cVariation0                         = \mb_substr($product['cInputData'], 1);
                    [$kEigenschaft0, $kEigenschaftWert0] = \explode(':', $cVariation0);
                    $kKindArtikel                        = Product::getChildProdctIDByAttribute(
                        $tmpProduct->kArtikel,
                        $kEigenschaft0,
                        $kEigenschaftWert0
                    );
                    $child->fuelleArtikel($kKindArtikel, $defaultOptions);
                    // Summen pro Steuerklasse summieren
                    if (!\array_key_exists($child->kSteuerklasse, $perTaxClass)) {
                        $perTaxClass[$child->kSteuerklasse] = 0;
                    }
                    $perTaxClass[$child->kSteuerklasse] += $child->Preise->fVKNetto * $product['fAnzahl'];

                    $sum = self::gibHinzukommendeArtikelAbhaengigeVersandkosten(
                        $child,
                        $iso,
                        $product['fAnzahl']
                    );
                    if ($sum !== false) {
                        $additionalShippingFees += $sum;
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
                    // Summen pro Steuerklasse summieren
                    if (!\array_key_exists($child->kSteuerklasse, $perTaxClass)) {
                        $perTaxClass[$child->kSteuerklasse] = 0;
                    }

                    $perTaxClass[$child->kSteuerklasse] += $child->Preise->fVKNetto * $product['fAnzahl'];

                    $sum = self::gibHinzukommendeArtikelAbhaengigeVersandkosten(
                        $child,
                        $iso,
                        $product['fAnzahl']
                    );
                    if ($sum !== false) {
                        $additionalShippingFees += $sum;
                        continue;
                    }

                    $additionalProduct->fAnzahl         += $product['fAnzahl'];
                    $additionalProduct->fWarenwertNetto += $product['fAnzahl'] * $child->Preise->fVKNetto;
                    $additionalProduct->fGewicht        += $product['fAnzahl'] * $child->fGewicht;
                }
                if (\mb_strlen($shippingClasses) > 0
                    && \mb_strpos($shippingClasses, $child->kVersandklasse) === false
                ) {
                    $shippingClasses = '-' . $child->kVersandklasse;
                } elseif (\mb_strlen($shippingClasses) === 0) {
                    $shippingClasses = $child->kVersandklasse;
                }
            }
        }

        if (isset($cart->PositionenArr) && \is_array($cart->PositionenArr) && \count($cart->PositionenArr) > 0) {
            // Wenn etwas im Warenkorb ist, dann Vesandart vom Warenkorb rausfinden
            $oVersandartNurWK = self::getFavourableShippingMethod(
                $iso,
                $shippingClasses,
                $cgroupID,
                null
            );
            $depending        = self::gibArtikelabhaengigeVersandkostenImWK(
                $iso,
                $cart->PositionenArr
            );

            $sum = 0;
            foreach ($depending as $oArtikelAbhaenigeVersandkosten) {
                $sum += $oArtikelAbhaenigeVersandkosten->fKosten;
            }

            $oVersandartNurWK->fEndpreis += $sum;
            $shippingMethod               = self::getFavourableShippingMethod(
                $iso,
                $shippingClasses,
                $cgroupID,
                $additionalProduct
            );
            $shippingMethod->fEndpreis   += ($sum + $additionalShippingFees);
        } else {
            $oVersandartNurWK            = new stdClass();
            $shippingMethod              = new stdClass();
            $oVersandartNurWK->fEndpreis = 0;
            $shippingMethod->fEndpreis   = $additionalShippingFees;
        }

        if (\abs($shippingMethod->fEndpreis - $oVersandartNurWK->fEndpreis) > 0.01) {
            // Versand mit neuen Artikeln > als Versand ohne Steuerklasse bestimmen
            foreach ($cart->PositionenArr as $position) {
                if ((int)$position->nPosTyp === \C_WARENKORBPOS_TYP_ARTIKEL) {
                    //Summen pro Steuerklasse summieren
                    if (!\array_key_exists($position->Artikel->kSteuerklasse, $perTaxClass)) {
                        $perTaxClass[$position->Artikel->kSteuerklasse] = 0;
                    }
                    $perTaxClass[$position->Artikel->kSteuerklasse] +=
                        $position->Artikel->Preise->fVKNetto * $position->nAnzahl;
                }
            }

            if ($conf['kaufabwicklung']['bestellvorgang_versand_steuersatz'] === 'US') {
                $maxSum = 0;
                foreach ($perTaxClass as $j => $fWarensummeProSteuerklasse) {
                    if ($fWarensummeProSteuerklasse > $maxSum) {
                        $maxSum     = $fWarensummeProSteuerklasse;
                        $taxClassID = $j;
                    }
                }
            } else {
                $maxTaxRate = 0;
                foreach ($perTaxClass as $j => $fWarensummeProSteuerklasse) {
                    if (Tax::getSalesTax($j) > $maxTaxRate) {
                        $maxTaxRate = Tax::getSalesTax($j);
                        $taxClassID = $j;
                    }
                }
            }

            return \sprintf(
                Shop::Lang()->get('productExtraShippingNotice'),
                Preise::getLocalizedPriceString(
                    Tax::getGross($shippingMethod->fEndpreis, Tax::getSalesTax($taxClassID), 4)
                )
            );
        }

        return Shop::Lang()->get('productNoExtraShippingNotice');
    }

    /**
     * @param string         $deliveryCountry
     * @param string         $shippingClasses
     * @param int            $customerGroupID
     * @param Artikel|object $product
     * @param bool           $checkProductDepedency
     * @return mixed
     * @former gibGuenstigsteVersandart()
     */
    public static function getFavourableShippingMethod(
        $deliveryCountry,
        $shippingClasses,
        $customerGroupID,
        $product,
        $checkProductDepedency = true
    ) {
        $favourableIDX   = 0;
        $minVersand      = 10000;
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
                'iso'      => '%' . $deliveryCountry . '%',
                'cGroupID' => $customerGroupID,
                'sClasses' => '^([0-9 -]* )?' . $shippingClasses . ' '
            ],
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($shippingMethods as $i => $shippingMethod) {
            $shippingMethod->fEndpreis = self::calculateShippingFees($shippingMethod, $deliveryCountry, $product);
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
     * @param Artikel $product
     * @param string  $iso
     * @param float   $productAmount
     * @return bool|stdClass
     */
    public static function gibHinzukommendeArtikelAbhaengigeVersandkosten($product, $iso, $productAmount)
    {
        $product->kArtikel = (int)$product->kArtikel;
        $dep               = self::pruefeArtikelabhaengigeVersandkosten($product);
        if ($dep === 1) {
            return self::gibArtikelabhaengigeVersandkosten($iso, $product, $productAmount, false);
        }
        if ($dep === 2) {
            // Gib alle Artikel im Warenkorb, die Artikel abhaengige Versandkosten beinhalten
            $depending = self::gibArtikelabhaengigeVersandkostenImWK(
                $iso,
                Frontend::getCart()->PositionenArr,
                false
            );

            if (\count($depending) > 0) {
                $amount = $productAmount;
                $total  = 0;
                foreach ($depending as $shipping) {
                    $shipping->kArtikel = (int)$shipping->kArtikel;
                    // Wenn es bereits den hinzukommenden Artikel im Warenkorb gibt
                    // zaehle die Anzahl vom Warenkorb hinzu und gib die Kosten fuer den Artikel im Warenkorb
                    if ($shipping->kArtikel === $product->kArtikel) {
                        $amount += $shipping->nAnzahl;
                        $total   = $shipping->fKosten;
                        break;
                    }
                }

                return self::gibArtikelabhaengigeVersandkosten($iso, $product, $amount, false) - $total;
            }
        }

        return false;
    }

    /**
     * @param Artikel $product
     * @return int
     */
    public static function pruefeArtikelabhaengigeVersandkosten(Artikel $product): int
    {
        $hookReturn = false;
        \executeHook(\HOOK_TOOLS_GLOBAL_PRUEFEARTIKELABHAENGIGEVERSANDKOSTEN, [
            'oArtikel'    => &$product,
            'bHookReturn' => &$hookReturn
        ]);

        if ($hookReturn) {
            return -1;
        }
        if ($product->FunktionsAttribute[\FKT_ATTRIBUT_VERSANDKOSTEN]) {
            // Artikelabhaengige Versandkosten
            return 1;
        }
        if ($product->FunktionsAttribute[\FKT_ATTRIBUT_VERSANDKOSTEN_GESTAFFELT]) {
            // Artikelabhaengige Versandkosten gestaffelt
            return 2;
        }

        return -1;  // Keine artikelabhaengigen Versandkosten
    }

    /**
     * @param string  $country
     * @param Artikel $product
     * @param int     $amount
     * @param bool    $checkDeliveryAddress
     * @return bool|stdClass
     */
    public static function gibArtikelabhaengigeVersandkosten(
        $country,
        Artikel $product,
        $amount,
        bool $checkDeliveryAddress = true
    ) {
        $steuerSatz  = null;
        $bHookReturn = false;
        \executeHook(\HOOK_TOOLS_GLOBAL_GIBARTIKELABHAENGIGEVERSANDKOSTEN, [
            'oArtikel'    => &$product,
            'cLand'       => &$country,
            'nAnzahl'     => &$amount,
            'bHookReturn' => &$bHookReturn
        ]);

        if ($bHookReturn) {
            return false;
        }
        $netPricesActive = Frontend::getCustomerGroup()->isMerchant();
        // Steuersatz nur benötigt, wenn Nettokunde
        if ($netPricesActive === true) {
            $steuerSatz = Shop::Container()->getDB()->select(
                'tsteuersatz',
                'kSteuerklasse',
                Frontend::getCart()->gibVersandkostenSteuerklasse()
            )->fSteuersatz;
        }
        // gestaffelte
        if (!empty($product->FunktionsAttribute[\FKT_ATTRIBUT_VERSANDKOSTEN_GESTAFFELT])) {
            $arrVersand = \array_filter(\explode(
                ';',
                $product->FunktionsAttribute[\FKT_ATTRIBUT_VERSANDKOSTEN_GESTAFFELT]
            ));
            foreach ($arrVersand as $cVersand) {
                // DE 1-45,00:2-60,00:3-80;AT 1-90,00:2-120,00:3-150,00
                [$cLandAttr, $KostenTeil] = \explode(' ', $cVersand);
                if ($cLandAttr && ($country === $cLandAttr || $checkDeliveryAddress === false)) {
                    $arrKosten = \explode(':', $KostenTeil);
                    foreach ($arrKosten as $staffel) {
                        [$bisAnzahl, $fPreis] = \explode('-', $staffel);
                        $fPreis               = (float)\str_replace(',', '.', $fPreis);
                        if ($fPreis >= 0 && $bisAnzahl > 0 && $amount <= $bisAnzahl) {
                            $position = new stdClass();
                            //posname lokalisiert ablegen
                            $position->cName = [];
                            foreach ($_SESSION['Sprachen'] as $Sprache) {
                                $position->cName[$Sprache->cISO] = Shop::Lang()->get('shippingFor', 'checkout') .
                                    ' ' . $product->cName . ' (' . $cLandAttr . ')';
                            }
                            $position->fKosten = $fPreis;
                            if ($netPricesActive === true) {
                                $position->cPreisLocalized = Preise::getLocalizedPriceString(
                                    Tax::getNet((float)$position->fKosten, $steuerSatz)
                                ) . ' ' . Shop::Lang()->get('plus', 'productDetails') . ' ' .
                                Shop::Lang()->get('vat', 'productDetails');
                            } else {
                                $position->cPreisLocalized = Preise::getLocalizedPriceString($position->fKosten);
                            }

                            return $position;
                        }
                    }
                }
            }
        }
        // flache
        if (!empty($product->FunktionsAttribute[\FKT_ATTRIBUT_VERSANDKOSTEN])) {
            $arrVersand = \array_filter(\explode(';', $product->FunktionsAttribute[\FKT_ATTRIBUT_VERSANDKOSTEN]));
            foreach ($arrVersand as $cVersand) {
                [$cLandAttr, $fKosten] = \explode(' ', $cVersand);
                if ($cLandAttr && ($country === $cLandAttr || $checkDeliveryAddress === false)) {
                    $position = new stdClass();
                    //posname lokalisiert ablegen
                    $position->cName = [];
                    foreach ($_SESSION['Sprachen'] as $Sprache) {
                        $position->cName[$Sprache->cISO] = Shop::Lang()->get('shippingFor', 'checkout') . ' ' .
                            $product->cName . ' (' . $cLandAttr . ')';
                    }
                    $position->fKosten = (float)\str_replace(',', '.', $fKosten) * $amount;
                    if ($netPricesActive === true) {
                        $position->cPreisLocalized = Preise::getLocalizedPriceString(Tax::getNet(
                            (float)$position->fKosten,
                            $steuerSatz
                        )) . ' ' . Shop::Lang()->get('plus', 'productDetails') . ' ' .
                        Shop::Lang()->get('vat', 'productDetails');
                    } else {
                        $position->cPreisLocalized = Preise::getLocalizedPriceString($position->fKosten);
                    }

                    return $position;
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
        $shippingPositions = [];
        if (!\is_array($positions)) {
            return $shippingPositions;
        }
        $positions = \array_filter($positions, function ($pos) {
            
            return (int)$pos->nPosTyp === \C_WARENKORBPOS_TYP_ARTIKEL && \is_object($pos->Artikel);
        });
        foreach ($positions as $pos) {
            $shippingPos = self::gibArtikelabhaengigeVersandkosten(
                $country,
                $pos->Artikel,
                $pos->nAnzahl,
                $checkDelivery
            );
            if (!empty($shippingPos->cName)) {
                $shippingPos->kArtikel = (int)$pos->Artikel->kArtikel;
                $shippingPositions[]   = $shippingPos;
            }
        }

        return $shippingPositions;
    }

    /**
     * @param Warenkorb $Warenkorb
     * @return string
     */
    public static function getShippingClasses(Warenkorb $Warenkorb): string
    {
        $classes = [];
        foreach ($Warenkorb->PositionenArr as $pos) {
            $pos->kVersandklasse = (int)$pos->kVersandklasse;
            if ((int)$pos->nPosTyp === \C_WARENKORBPOS_TYP_ARTIKEL
                && $pos->kVersandklasse > 0
                && !\in_array($pos->kVersandklasse, $classes, true)
            ) {
                $classes[] = $pos->kVersandklasse;
            }
        }
        \sort($classes);

        return \implode('-', $classes);
    }

    /**
     * @param Versandart|object $shippingMethod
     * @param string            $iso
     * @param string            $zip
     * @return stdClass|null
     * @former gibVersandZuschlag()
     */
    public static function getAdditionalFees($shippingMethod, $iso, $zip): ?stdClass
    {
        $db   = Shop::Container()->getDB();
        $fees = $db->selectAll(
            'tversandzuschlag',
            ['kVersandart', 'cISO'],
            [(int)$shippingMethod->kVersandart, $iso]
        );
        foreach ($fees as $fee) {
            $zipData = $db->queryPrepared(
                'SELECT * FROM tversandzuschlagplz
                    WHERE ((cPLZAb <= :plz
                        AND cPLZBis >= :plz)
                        OR cPLZ = :plz)
                        AND kVersandzuschlag = :sid',
                ['plz' => $zip, 'sid' => (int)$fee->kVersandzuschlag],
                ReturnType::SINGLE_OBJECT
            );
            if (isset($zipData->kVersandzuschlagPlz) && $zipData->kVersandzuschlagPlz > 0) {
                $fee->angezeigterName = [];
                foreach (Frontend::getLanguages() as $Sprache) {
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
     * @param Versandart|object $shippingMethod
     * @param String            $iso
     * @param Artikel|stdClass  $additionalProduct
     * @param Artikel|null      $product
     * @return int|string
     * @former berechneVersandpreis()
     */
    public static function calculateShippingFees($shippingMethod, $iso, $additionalProduct, $product = null)
    {
        $db = Shop::Container()->getDB();
        if (!isset($additionalProduct->fAnzahl)) {
            if ($additionalProduct === null) {
                $additionalProduct = new stdClass();
            }
            $additionalProduct->fAnzahl         = 0;
            $additionalProduct->fWarenwertNetto = 0;
            $additionalProduct->fGewicht        = 0;
        }
        $calculation = $db->select(
            'tversandberechnung',
            'kVersandberechnung',
            $shippingMethod->kVersandberechnung
        );
        $price       = 0;
        switch ($calculation->cModulId) {
            case 'vm_versandkosten_pauschale_jtl':
                $price = $shippingMethod->fPreis;
                break;

            case 'vm_versandberechnung_gewicht_jtl':
                $totalWeight  = $product
                    ? $product->fGewicht
                    : Frontend::getCart()->getWeight();
                $totalWeight += $additionalProduct->fGewicht;
                $shipping     = $db->queryPrepared(
                    'SELECT *
                        FROM tversandartstaffel
                        WHERE kVersandart = :sid
                            AND fBis >= :wght
                        ORDER BY fBis ASC',
                    ['sid' => (int)$shippingMethod->kVersandart, 'wght' => $totalWeight],
                    ReturnType::SINGLE_OBJECT
                );
                if (isset($shipping->kVersandartStaffel)) {
                    $price = $shipping->fPreis;
                } else {
                    return -1;
                }
                break;

            case 'vm_versandberechnung_warenwert_jtl':
                $total    = $product
                    ? $product->Preise->fVKNetto
                    : Frontend::getCart()->gibGesamtsummeWarenExt([\C_WARENKORBPOS_TYP_ARTIKEL], true);
                $total   += $additionalProduct->fWarenwertNetto;
                $shipping = $db->queryPrepared(
                    'SELECT *
                        FROM tversandartstaffel
                        WHERE kVersandart = :sid
                            AND fBis >= :val
                        ORDER BY fBis ASC',
                    ['sid' => (int)$shippingMethod->kVersandart, 'val' => $total],
                    ReturnType::SINGLE_OBJECT
                );
                if (isset($shipping->kVersandartStaffel)) {
                    $price = $shipping->fPreis;
                } else {
                    return -1;
                }
                break;

            case 'vm_versandberechnung_artikelanzahl_jtl':
                $productCount = 1;
                if (!$product) {
                    $productCount = isset($_SESSION['Warenkorb'])
                        ? Frontend::getCart()->gibAnzahlArtikelExt([\C_WARENKORBPOS_TYP_ARTIKEL])
                        : 0;
                }
                $productCount += $additionalProduct->fAnzahl;
                $shipping      = $db->queryPrepared(
                    'SELECT *
                        FROM tversandartstaffel
                        WHERE kVersandart = :sid
                            AND fBis >= :cnt
                        ORDER BY fBis ASC',
                    ['sid' => (int)$shippingMethod->kVersandart, 'cnt' => $productCount],
                    ReturnType::SINGLE_OBJECT
                );
                if (isset($shipping->kVersandartStaffel)) {
                    $price = $shipping->fPreis;
                } else {
                    return -1;
                }
                break;

            default:
                // bearbeite fremdmodule
                break;
        }
        if ($shippingMethod->cNurAbhaengigeVersandart === 'Y'
            && (!empty($product->FunktionsAttribute['versandkosten'])
                || !empty($product->FunktionsAttribute['versandkosten gestaffelt']))
        ) {
            $fArticleSpecific = self::gibArtikelabhaengigeVersandkosten($iso, $product, 1);
            $price           += $fArticleSpecific->fKosten ?? 0;
        }
        if ($price >= $shippingMethod->fDeckelung && $shippingMethod->fDeckelung > 0) {
            $price = $shippingMethod->fDeckelung;
        }
        if (isset($shippingMethod->Zuschlag->fZuschlag) && $shippingMethod->Zuschlag->fZuschlag != 0) {
            $price += $shippingMethod->Zuschlag->fZuschlag;
        }
        $productPrice = 0;
        $grandTotal   = 0;
        if ($shippingMethod->eSteuer === 'netto') {
            if ($product) {
                $productPrice = $product->Preise->fVKNetto;
            }
            if (isset($_SESSION['Warenkorb'])) {
                $grandTotal = Tax::getNet(
                    Frontend::getCart()->gibGesamtsummeWarenExt([\C_WARENKORBPOS_TYP_ARTIKEL], true),
                    Tax::getSalesTax(Frontend::getCart()->gibVersandkostenSteuerklasse())
                );
            }
        } elseif ($shippingMethod->eSteuer === 'brutto') {
            if ($product) {
                $productPrice = Tax::getGross(
                    $product->Preise->fVKNetto,
                    Tax::getSalesTax($product->kSteuerklasse)
                );
            }
            if (isset($_SESSION['Warenkorb'])) {
                $grandTotal = Frontend::getCart()->gibGesamtsummeWarenExt(
                    [\C_WARENKORBPOS_TYP_ARTIKEL],
                    true
                );
            }
        }

        if ($shippingMethod->fVersandkostenfreiAbX > 0
            && (($product && $productPrice >= $shippingMethod->fVersandkostenfreiAbX)
                || ($grandTotal >= $shippingMethod->fVersandkostenfreiAbX))
        ) {
            $price = 0;
        }
        \executeHook(\HOOK_TOOLSGLOBAL_INC_BERECHNEVERSANDPREIS, [
            'fPreis'         => &$price,
            'ersandart'      => $shippingMethod,
            'cISO'           => $iso,
            'oZusatzArtikel' => $additionalProduct,
            'Artikel'        => $product,
        ]);

        return $price;
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
        if (empty($product->FunktionsAttribute['versandkosten'])
            && empty($product->FunktionsAttribute['versandkosten gestaffelt'])
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
     * @param Versandart|object $method
     * @param float             $cartSum
     * @return string
     * @former baueVersandkostenfreiString()
     */
    public static function getShippingFreeString($method, $cartSum): string
    {
        if (isset($_SESSION['oVersandfreiKupon'])) {
            return '';
        }
        if (!\is_object($method)
            || (float)$method->fVersandkostenfreiAbX <= 0
            || !isset($_SESSION['Warenkorb'], $_SESSION['Steuerland'])
        ) {
            return '';
        }
        $db         = Shop::Container()->getDB();
        $fSummeDiff = (float)$method->fVersandkostenfreiAbX - (float)$cartSum;
        // check if vkfreiabx is calculated net or gross
        if ($method->eSteuer === 'netto') {
            // calculate net with default tax class
            $defaultTaxClass = $db->select('tsteuerklasse', 'cStandard', 'Y');
            if ($defaultTaxClass !== null && isset($defaultTaxClass->kSteuerklasse)) {
                $taxClasss  = (int)$defaultTaxClass->kSteuerklasse;
                $defaultTax = $db->select('tsteuersatz', 'kSteuerklasse', $taxClasss);
                if ($defaultTax !== null) {
                    $defaultTaxValue = $defaultTax->fSteuersatz;
                    $fSummeDiff      = (float)$method->fVersandkostenfreiAbX -
                        Tax::getNet((float)$cartSum, $defaultTaxValue);
                }
            }
        }
        if (isset($method->cNameLocalized)) {
            $name = $method->cNameLocalized;
        } else {
            $localized = $db->select(
                'tversandartsprache',
                'kVersandart',
                $method->kVersandart,
                'cISOSprache',
                Shop::getLanguageCode()
            );
            $name      = !empty($localized->cName)
                ? $localized->cName
                : $method->cName;
        }
        if ($fSummeDiff <= 0) {
            return \sprintf(
                Shop::Lang()->get('noShippingCostsReached', 'basket'),
                $name,
                self::getShippingFreeCountriesString($method),
                (string)$method->cLaender
            );
        }

        return \sprintf(
            Shop::Lang()->get('noShippingCostsAt', 'basket'),
            Preise::getLocalizedPriceString($fSummeDiff),
            $name,
            self::getShippingFreeCountriesString($method)
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
            \mb_strlen($oVersandart->cLaender) . '_' .
            Shop::getLanguageID();
        if (($vkfls = Shop::Container()->getCache()->get($cacheID)) === false) {
            // remove empty strings
            $countryCodes = \array_filter(\explode(' ', $oVersandart->cLaender));
            // only select the needed row
            $select = $_SESSION['cISOSprache'] === 'ger'
                ? 'cDeutsch'
                : 'cEnglisch';
            // generate IN sql statement with stringified country isos
            $sql       = ' cISO IN (' . \implode(', ', \array_map(function ($iso) {
                return "'" . $iso . "'";
            }, $countryCodes)) . ')';
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
        $shippingClasses = self::getShippingClasses(Frontend::getCart());
        $defaultShipping = self::normalerArtikelversand($country);
        $cacheID         = 'vkfrei_' . $customerGroupID . '_' .
            $country . '_' . $shippingClasses . '_' . Shop::getLanguageCode();
        if (($shippingMethod = Shop::Container()->getCache()->get($cacheID)) === false) {
            if (\mb_strlen($country) > 0) {
                $customerSQL = " AND cLaender LIKE '%" . Text::filterXSS($country) . "%'";
            } else {
                $iso         = Shop::Container()->getDB()->query(
                    'SELECT cISO
                        FROM tfirma
                        JOIN tland
                            ON tfirma.cLand = tland.cDeutsch
                        LIMIT 0,1',
                    ReturnType::SINGLE_OBJECT
                );
                $customerSQL = isset($iso->cISO)
                    ? " AND cLaender LIKE '%" . $iso->cISO . "%'"
                    : '';
            }
            $productSpecificCondition = empty($defaultShipping) ? '' : " AND cNurAbhaengigeVersandart = 'N' ";
            $shippingMethod           = Shop::Container()->getDB()->queryPrepared(
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
                        " . $customerSQL . $productSpecificCondition . '
                    ORDER BY tversandart.fVersandkostenfreiAbX, tversandart.nSort ASC
                    LIMIT 1',
                [
                    'cLangID'        => Shop::getLanguageCode(),
                    'cShippingClass' => '^([0-9 -]* )?' . $shippingClasses . ' ',
                    'cGroupID'       => $customerGroupID
                ],
                ReturnType::SINGLE_OBJECT
            );
            Shop::Container()->getCache()->set($cacheID, $shippingMethod, [\CACHING_GROUP_OPTION]);
        }

        return !empty($shippingMethod) && $shippingMethod->fVersandkostenfreiAbX > 0
            ? $shippingMethod
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
        $cartSum      = Frontend::getCart()->gibGesamtsummeWarenExt([\C_WARENKORBPOS_TYP_ARTIKEL], true);
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
        $currencyCode = Frontend::getCurrency()->getID();
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
