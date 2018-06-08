<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class VersandartHelper
 */
class VersandartHelper
{
    /**
     * @var VersandartHelper
     */
    private static $_instance;

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
        $this->cacheID         = 'smeth_' . Shop::Cache()->getBaseID();
        $this->shippingMethods = $this->getShippingMethods();
        self::$_instance       = $this;
    }

    /**
     * @return VersandartHelper
     */
    public static function getInstance(): self
    {
        return self::$_instance ?? new self();
    }

    /**
     * @return array
     */
    public function getShippingMethods(): array
    {
        return $this->shippingMethods ?? Shop::Container()->getDB()->query(
                'SELECT * FROM tversandart',
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
    }

    /**
     * @param float|int $freeFromX
     * @return array
     */
    public function filter($freeFromX): array
    {
        $freeFromX = (float)$freeFromX;

        return array_filter(
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
    public function getFreeShippingCountries($wert, $kKundengruppe, $versandklasse = 0)
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
                    'cGroupID' => (int)$kKundengruppe
                ],
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
        }
        $shippingFreeCountries = [];
        foreach ($this->countries[$kKundengruppe][$versandklasse] as $_method) {
            if (isset($_method->fVersandkostenfreiAbX)
                && (float)$_method->fVersandkostenfreiAbX > 0
                && (float)$_method->fVersandkostenfreiAbX < $wert
            ) {
                foreach (explode(' ', $_method->cLaender) as $_country) {
                    if (strlen($_country) > 0) {
                        $shippingFreeCountries[] = $_country;
                    }
                }
            }
        }
        $shippingFreeCountries = array_unique($shippingFreeCountries);
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
    public static function normalerArtikelversand($cLand)
    {
        $bNoetig = false;
        $cart    = Session::Cart();
        foreach ($cart->PositionenArr as $pos) {
            if ((int)$pos->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL
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
        return !empty(self::gibArtikelabhaengigeVersandkostenImWK($cLand, Session::Cart()->PositionenArr));
    }

    /**
     * @former gibMoeglicheVersandarten()
     * @param string $lieferland
     * @param string $plz
     * @param string $versandklassen
     * @param int    $kKundengruppe
     * @return array
     */
    public static function getPossibleShippingMethods($lieferland, $plz, $versandklassen, $kKundengruppe): array
    {
        $cart                     = Session::Cart();
        $kSteuerklasse            = $cart->gibVersandkostenSteuerklasse();
        $minVersand               = 10000;
        $cISO                     = $lieferland;
        $hasSpecificShippingcosts = self::hasSpecificShippingcosts($lieferland);
        $vatNote                  = null;
        $cNurAbhaengigeVersandart = self::normalerArtikelversand($lieferland) === false
            ? 'Y'
            : 'N';
        $methods                  = Shop::Container()->getDB()->queryPrepared(
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
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        $netPricesActive          = Session::CustomerGroup()->isMerchant();

        foreach ($methods as $i => $shippingMethod) {
            $bSteuerPos = $shippingMethod->eSteuer !== 'netto';

            $shippingMethod->kVersandart        = (int)$shippingMethod->kVersandart;
            $shippingMethod->kVersandberechnung = (int)$shippingMethod->kVersandberechnung;
            $shippingMethod->nSort              = (int)$shippingMethod->nSort;
            $shippingMethod->nMinLiefertage     = (int)$shippingMethod->nMinLiefertage;
            $shippingMethod->nMaxLiefertage     = (int)$shippingMethod->nMaxLiefertage;
            $shippingMethod->Zuschlag           = self::getAdditionalFees($shippingMethod, $cISO, $plz);
            $shippingMethod->fEndpreis          = self::calculateShippingFees($shippingMethod, $cISO, null);
            if ($shippingMethod->fEndpreis === -1) {
                unset($methods[$i]);
                continue;
            }
            if ($netPricesActive === true) {
                $shippingCosts = $bSteuerPos
                    ? $shippingMethod->fEndpreis / (100 + gibUst($kSteuerklasse)) * 100.0
                    : round($shippingMethod->fEndpreis, 2);
                $vatNote       = ' ' . Shop::Lang()->get('plus', 'productDetails') . ' ' .
                    Shop::Lang()->get('vat', 'productDetails');
            } else {
                $shippingCosts = $bSteuerPos
                    ? $shippingMethod->fEndpreis
                    : round($shippingMethod->fEndpreis * (100 + gibUst($kSteuerklasse)) / 100, 2);
            }
            // posname lokalisiert ablegen
            $shippingMethod->angezeigterName           = [];
            $shippingMethod->angezeigterHinweistext    = [];
            $shippingMethod->cLieferdauer              = [];
            $shippingMethod->specificShippingcosts_arr = null;
            foreach ($_SESSION['Sprachen'] as $Sprache) {
                $name_spr = Shop::Container()->getDB()->select(
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
            // Versandart Versandkostenfrei
            if ($shippingMethod->fEndpreis == 0) {
                // Abfrage ob ein Artikel Artikelabhängige Versandkosten besitzt
                $shippingMethod->cPreisLocalized = Shop::Lang()->get('freeshipping');
                if ($hasSpecificShippingcosts === true) {
                    $shippingMethod->cPreisLocalized           = gibPreisStringLocalized($shippingCosts);
                    $shippingMethod->specificShippingcosts_arr = self::gibArtikelabhaengigeVersandkostenImWK(
                        $lieferland,
                        $cart->PositionenArr
                    );
                }
            } else {
                // Abfrage ob ein Artikel Artikelabhängige Versandkosten besitzt
                $shippingMethod->cPreisLocalized = gibPreisStringLocalized($shippingCosts) . ($vatNote ?? '');
                if ($hasSpecificShippingcosts === true) {
                    $shippingMethod->specificShippingcosts_arr = self::gibArtikelabhaengigeVersandkostenImWK(
                        $lieferland,
                        $cart->PositionenArr
                    );
                }
            }
            // Abfrage ob die Zahlungsart/en zur Versandart gesetzt ist/sind
            $zahlungsarten   = Shop::Container()->getDB()->queryPrepared(
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
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
            $bVersandGueltig = false;
            foreach ($zahlungsarten as $zahlungsart) {
                if (ZahlungsartHelper::shippingMethodWithValidPaymentMethod($zahlungsart)) {
                    $bVersandGueltig = true;
                    break;
                }
            }
            if (!$bVersandGueltig) {
                unset($shippingMethod);
            }
        }
        // auf anzeige filtern
        $possibleMethods = array_filter(
            array_merge($methods),
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
                $method->cPreisLocalized = gibPreisStringLocalized($method->fEndpreis);
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
        if ($cLand !== null && $cPLZ !== null && strlen($cLand) > 0 && strlen($cPLZ) > 0) {
            $kKundengruppe = Session::CustomerGroup()->getID();
            if (isset($_SESSION['Kunde']->kKundengruppe) && $_SESSION['Kunde']->kKundengruppe > 0) {
                $kKundengruppe = $_SESSION['Kunde']->kKundengruppe;
            }

            $oVersandart_arr = self::getPossibleShippingMethods(
                StringHandler::filterXSS($cLand),
                StringHandler::filterXSS($cPLZ),
                self::getShippingClasses(Session::Cart()),
                $kKundengruppe
            );
            if (count($oVersandart_arr) > 0) {
                Shop::Smarty()
                    ->assign('ArtikelabhaengigeVersandarten', self::gibArtikelabhaengigeVersandkostenImWK(
                        $cLand,
                        Session::Cart()->PositionenArr
                    ))
                    ->assign('Versandarten', $oVersandart_arr)
                    ->assign('Versandland', ISO2land(StringHandler::filterXSS($cLand)))
                    ->assign('VersandPLZ', StringHandler::filterXSS($cPLZ));
            } else {
                $cError = Shop::Lang()->get('noDispatchAvailable');
            }
            executeHook(HOOK_WARENKORB_PAGE_ERMITTLEVERSANDKOSTEN);

            return true;
        }

        return !(isset($_POST['versandrechnerBTN']) && (strlen($cLand) === 0 || strlen($cPLZ) === 0));
    }

    /**
     * @former ermittleVersandkostenExt()
     * @param array $oArtikel_arr
     * @return string
     */
    public static function getShippingCostsExt($oArtikel_arr)
    {
        if (!isset($_SESSION['shipping_count'])) {
            $_SESSION['shipping_count'] = 0;
        }
        if (!is_array($oArtikel_arr) || count($oArtikel_arr) === 0) {
            return null;
        }
        $cLandISO = $_SESSION['cLieferlandISO'] ?? false;
        $cart     = Session::Cart();
        if (!$cLandISO) {
            //Falls kein Land in tfirma da
            $cLandISO = 'DE';
        }

        $kKundengruppe = Session::CustomerGroup()->getID();
        // Baue ZusatzArtikel
        $oZusatzArtikel                  = new stdClass();
        $oZusatzArtikel->fAnzahl         = 0;
        $oZusatzArtikel->fWarenwertNetto = 0;
        $oZusatzArtikel->fGewicht        = 0;

        $cVersandklassen                                   = self::getShippingClasses($cart);
        $conf                                              = Shop::getSettings([CONF_KAUFABWICKLUNG]);
        $fSummeHinzukommendeArtikelabhaengigeVersandkosten = 0;
        $fWarensummeProSteuerklasse_arr                    = [];
        $kSteuerklasse                                     = 0;
        // Vorkonditionieren -- Gleiche kartikel aufsummieren
        // aber nur, wenn artikelabhaengiger Versand bei dem jeweiligen kArtikel
        $nArtikelAssoc_arr = [];
        foreach ($oArtikel_arr as $oArtikel) {
            $kArtikel                     = (int)$oArtikel['kArtikel'];
            $nArtikelAssoc_arr[$kArtikel] = isset($nArtikelAssoc_arr[$kArtikel]) ? 1 : 0;
        }

        $bMerge         = false;
        $defaultOptions = Artikel::getDefaultOptions();
        foreach ($nArtikelAssoc_arr as $kArtikel => $nArtikelAssoc) {
            if ($nArtikelAssoc !== 1) {
                continue;
            }
            $oArtikelTMP = (new Artikel())->fuelleArtikel($kArtikel, $defaultOptions);
            // Normaler Variationsartikel
            if ($oArtikelTMP !== null
                && $oArtikelTMP->nIstVater === 0
                && $oArtikelTMP->kVaterArtikel === 0
                && count($oArtikelTMP->Variationen) > 0
                && self::pruefeArtikelabhaengigeVersandkosten($oArtikelTMP) === 2
            ) {
                // Nur wenn artikelabhaengiger Versand gestaffelt als Funktionsattribut gesetzt ist
                $fAnzahl      = 0;
                $nArrayAnzahl = count($oArtikel_arr);
                for ($i = 0; $i < $nArrayAnzahl; $i++) {
                    if ($oArtikel_arr[$i]['kArtikel'] === $kArtikel) {
                        $fAnzahl += $oArtikel_arr[$i]['fAnzahl'];
                        unset($oArtikel_arr[$i]);
                    }
                }

                $oArtikelMerged             = [];
                $oArtikelMerged['kArtikel'] = $kArtikel;
                $oArtikelMerged['fAnzahl']  = $fAnzahl;
                $oArtikel_arr[]             = $oArtikelMerged;
                $bMerge                     = true;
            }
        }

        if ($bMerge) {
            $oArtikel_arr = array_merge($oArtikel_arr);
        }

        $defaultOptions = Artikel::getDefaultOptions();
        foreach ($oArtikel_arr as $i => $oArtikel) {
            $oArtikelTMP = (new Artikel())->fuelleArtikel($oArtikel['kArtikel'], $defaultOptions);
            if ($oArtikelTMP === null || $oArtikelTMP->kArtikel <= 0) {
                continue;
            }
            $kSteuerklasse = $oArtikelTMP->kSteuerklasse;
            // Artikelabhaengige Versandkosten?
            if ($oArtikelTMP->nIstVater === 0) {
                //Summen pro Steuerklasse summieren
                if ($oArtikelTMP->kSteuerklasse === null) {
                    $fWarensummeProSteuerklasse_arr[$oArtikelTMP->kSteuerklasse] = 0;
                }

                $fWarensummeProSteuerklasse_arr[$oArtikelTMP->kSteuerklasse] +=
                    $oArtikelTMP->Preise->fVKNetto * $oArtikel['fAnzahl'];

                $oVersandPos = self::gibHinzukommendeArtikelAbhaengigeVersandkosten(
                    $oArtikelTMP,
                    $cLandISO,
                    $oArtikel['fAnzahl']
                );
                if ($oVersandPos !== false) {
                    $fSummeHinzukommendeArtikelabhaengigeVersandkosten += $oVersandPos->fKosten;
                    continue;
                }
            }
            // Normaler Artikel oder Kind Artikel
            if ($oArtikelTMP->kVaterArtikel > 0 || count($oArtikelTMP->Variationen) === 0) {
                $oZusatzArtikel->fAnzahl         += $oArtikel['fAnzahl'];
                $oZusatzArtikel->fWarenwertNetto += $oArtikel['fAnzahl'] * $oArtikelTMP->Preise->fVKNetto;
                $oZusatzArtikel->fGewicht        += $oArtikel['fAnzahl'] * $oArtikelTMP->fGewicht;

                if (strlen($cVersandklassen) > 0 && strpos($cVersandklassen, $oArtikelTMP->kVersandklasse) === false) {
                    $cVersandklassen = '-' . $oArtikelTMP->kVersandklasse;
                } elseif (strlen($cVersandklassen) === 0) {
                    $cVersandklassen = $oArtikelTMP->kVersandklasse;
                }
            } elseif ($oArtikelTMP->nIstVater === 0
                && $oArtikelTMP->kVaterArtikel === 0
                && count($oArtikelTMP->Variationen) > 0
            ) { // Normale Variation
                if ($oArtikel['cInputData']{0} === '_') {
                    // 1D
                    $cVariation0 = substr($oArtikel['cInputData'], 1);
                    list($kEigenschaft0, $kEigenschaftWert0) = explode(':', $cVariation0);

                    $oVariation = findeVariation($oArtikelTMP->Variationen, $kEigenschaft0, $kEigenschaftWert0);

                    $oZusatzArtikel->fAnzahl         += $oArtikel['fAnzahl'];
                    $oZusatzArtikel->fWarenwertNetto += $oArtikel['fAnzahl'] *
                        ($oArtikelTMP->Preise->fVKNetto + $oVariation->fAufpreisNetto);
                    $oZusatzArtikel->fGewicht        += $oArtikel['fAnzahl'] *
                        ($oArtikelTMP->fGewicht + $oVariation->fGewichtDiff);
                } else {
                    // 2D
                    list($cVariation0, $cVariation1) = explode('_', $oArtikel['cInputData']);
                    list($kEigenschaft0, $kEigenschaftWert0) = explode(':', $cVariation0);
                    list($kEigenschaft1, $kEigenschaftWert1) = explode(':', $cVariation1);

                    $oVariation0 = findeVariation($oArtikelTMP->Variationen, $kEigenschaft0, $kEigenschaftWert0);
                    $oVariation1 = findeVariation($oArtikelTMP->Variationen, $kEigenschaft1, $kEigenschaftWert1);

                    $oZusatzArtikel->fAnzahl         += $oArtikel['fAnzahl'];
                    $oZusatzArtikel->fWarenwertNetto += $oArtikel['fAnzahl'] *
                        ($oArtikelTMP->Preise->fVKNetto + $oVariation0->fAufpreisNetto + $oVariation1->fAufpreisNetto);
                    $oZusatzArtikel->fGewicht        += $oArtikel['fAnzahl'] *
                        ($oArtikelTMP->fGewicht + $oVariation0->fGewichtDiff + $oVariation1->fGewichtDiff);
                }
                if (strlen($cVersandklassen) > 0 && strpos($cVersandklassen, $oArtikelTMP->kVersandklasse) === false) {
                    $cVersandklassen = '-' . $oArtikelTMP->kVersandklasse;
                } elseif (strlen($cVersandklassen) === 0) {
                    $cVersandklassen = $oArtikelTMP->kVersandklasse;
                }
            } elseif ($oArtikelTMP->nIstVater > 0) { // Variationskombination (Vater)
                $oArtikelKind = new Artikel();
                if ($oArtikel['cInputData']{0} === '_') {
                    // 1D
                    $cVariation0 = substr($oArtikel['cInputData'], 1);
                    list($kEigenschaft0, $kEigenschaftWert0) = explode(':', $cVariation0);
                    $kKindArtikel = findeKindArtikelZuEigenschaft(
                        $oArtikelTMP->kArtikel,
                        $kEigenschaft0,
                        $kEigenschaftWert0
                    );
                    $oArtikelKind->fuelleArtikel($kKindArtikel, $defaultOptions);
                    //Summen pro Steuerklasse summieren
                    if (!array_key_exists($oArtikelKind->kSteuerklasse, $fWarensummeProSteuerklasse_arr)) {
                        $fWarensummeProSteuerklasse_arr[$oArtikelKind->kSteuerklasse] = 0;
                    }

                    $fWarensummeProSteuerklasse_arr[$oArtikelKind->kSteuerklasse] +=
                        $oArtikelKind->Preise->fVKNetto * $oArtikel['fAnzahl'];

                    $fSumme = self::gibHinzukommendeArtikelAbhaengigeVersandkosten(
                        $oArtikelKind,
                        $cLandISO,
                        $oArtikel['fAnzahl']
                    );
                    if ($fSumme !== false) {
                        $fSummeHinzukommendeArtikelabhaengigeVersandkosten += $fSumme;
                        continue;
                    }

                    $oZusatzArtikel->fAnzahl         += $oArtikel['fAnzahl'];
                    $oZusatzArtikel->fWarenwertNetto += $oArtikel['fAnzahl'] * $oArtikelKind->Preise->fVKNetto;
                    $oZusatzArtikel->fGewicht        += $oArtikel['fAnzahl'] * $oArtikelKind->fGewicht;
                } else {
                    // 2D
                    list($cVariation0, $cVariation1) = explode('_', $oArtikel['cInputData']);
                    list($kEigenschaft0, $kEigenschaftWert0) = explode(':', $cVariation0);
                    list($kEigenschaft1, $kEigenschaftWert1) = explode(':', $cVariation1);

                    $kKindArtikel = findeKindArtikelZuEigenschaft(
                        $oArtikelTMP->kArtikel,
                        $kEigenschaft0,
                        $kEigenschaftWert0,
                        $kEigenschaft1,
                        $kEigenschaftWert1
                    );
                    $oArtikelKind->fuelleArtikel($kKindArtikel, $defaultOptions);
                    //Summen pro Steuerklasse summieren
                    if (!array_key_exists($oArtikelKind->kSteuerklasse, $fWarensummeProSteuerklasse_arr)) {
                        $fWarensummeProSteuerklasse_arr[$oArtikelKind->kSteuerklasse] = 0;
                    }

                    $fWarensummeProSteuerklasse_arr[$oArtikelKind->kSteuerklasse] += $oArtikelKind->Preise->fVKNetto * $oArtikel['fAnzahl'];

                    $fSumme = self::gibHinzukommendeArtikelAbhaengigeVersandkosten($oArtikelKind, $cLandISO,
                        $oArtikel['fAnzahl']);
                    if ($fSumme !== false) {
                        $fSummeHinzukommendeArtikelabhaengigeVersandkosten += $fSumme;
                        continue;
                    }

                    $oZusatzArtikel->fAnzahl         += $oArtikel['fAnzahl'];
                    $oZusatzArtikel->fWarenwertNetto += $oArtikel['fAnzahl'] * $oArtikelKind->Preise->fVKNetto;
                    $oZusatzArtikel->fGewicht        += $oArtikel['fAnzahl'] * $oArtikelKind->fGewicht;
                }
                if (strlen($cVersandklassen) > 0 && strpos($cVersandklassen, $oArtikelKind->kVersandklasse) === false) {
                    $cVersandklassen = '-' . $oArtikelKind->kVersandklasse;
                } elseif (strlen($cVersandklassen) === 0) {
                    $cVersandklassen = $oArtikelKind->kVersandklasse;
                }
            }
        }

        if (isset($cart->PositionenArr)
            && is_array($cart->PositionenArr)
            && count($cart->PositionenArr) > 0
        ) {
            // Wenn etwas im Warenkorb ist, dann Vesandart vom Warenkorb rausfinden
            $oVersandartNurWK                   = self::getFavourableShippingMethod(
                $cLandISO,
                $cVersandklassen,
                $kKundengruppe,
                null
            );
            $oArtikelAbhaenigeVersandkosten_arr = self::gibArtikelabhaengigeVersandkostenImWK(
                $cLandISO,
                $cart->PositionenArr
            );

            $fSumme = 0;
            if (count($oArtikelAbhaenigeVersandkosten_arr) > 0) {
                foreach ($oArtikelAbhaenigeVersandkosten_arr as $oArtikelAbhaenigeVersandkosten) {
                    $fSumme += $oArtikelAbhaenigeVersandkosten->fKosten;
                }
            }

            $oVersandartNurWK->fEndpreis += $fSumme;
            $oVersandart                 = self::getFavourableShippingMethod($cLandISO, $cVersandklassen,
                $kKundengruppe, $oZusatzArtikel);
            $oVersandart->fEndpreis      += ($fSumme + $fSummeHinzukommendeArtikelabhaengigeVersandkosten);
        } else {
            $oVersandartNurWK            = new stdClass();
            $oVersandart                 = new stdClass();
            $oVersandartNurWK->fEndpreis = 0;
            $oVersandart->fEndpreis      = $fSummeHinzukommendeArtikelabhaengigeVersandkosten;
        }

        if (abs($oVersandart->fEndpreis - $oVersandartNurWK->fEndpreis) > 0.01) {
            //Versand mit neuen Artikeln > als Versand ohne Steuerklasse bestimmen
            foreach ($cart->PositionenArr as $oPosition) {
                if ((int)$oPosition->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL) {
                    //Summen pro Steuerklasse summieren
                    if (!array_key_exists($oPosition->Artikel->kSteuerklasse, $fWarensummeProSteuerklasse_arr)) {
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
                    if (gibUst($j) > $nMaxSteuersatz) {
                        $nMaxSteuersatz = gibUst($j);
                        $kSteuerklasse  = $j;
                    }
                }
            }

            return sprintf(
                Shop::Lang()->get('productExtraShippingNotice'),
                gibPreisStringLocalized(berechneBrutto($oVersandart->fEndpreis, gibUst($kSteuerklasse), 4) . ' an.')
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
            \DB\ReturnType::ARRAY_OF_OBJECTS
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
        $oArtikel->kArtikel = (int)$oArtikel->kArtikel;
        // Prueft, ob es Artikel abhaengige Versandkosten bei dem hinzukommenden Artikel gibt
        $nArtikelAbhaengigeVersandkosten = self::pruefeArtikelabhaengigeVersandkosten($oArtikel);

        if ($nArtikelAbhaengigeVersandkosten === 1) {
            // Artikelabhaengige Versandkosten
            return self::gibArtikelabhaengigeVersandkosten($cLandISO, $oArtikel, $fArtikelAnzahl, false);
        }
        if ($nArtikelAbhaengigeVersandkosten === 2) {
            // Artikelabhaengige Versandkosten Gestaffelt

            // Gib alle Artikel im Warenkorb, die Artikel abhaengige Versandkosten beinhalten
            $oWarenkorbArtikelAbhaengigerVersand_arr = self::gibArtikelabhaengigeVersandkostenImWK(
                $cLandISO,
                Session::Cart()->PositionenArr,
                false
            );

            if (count($oWarenkorbArtikelAbhaengigerVersand_arr) > 0) {
                $nAnzahl = $fArtikelAnzahl;
                $fKosten = 0;
                foreach ($oWarenkorbArtikelAbhaengigerVersand_arr as $oWarenkorbArtikelAbhaengigerVersand) {
                    $oWarenkorbArtikelAbhaengigerVersand->kArtikel = (int)$oWarenkorbArtikelAbhaengigerVersand->kArtikel;
                    // Wenn es bereits den hinzukommenden Artikel im Warenkorb gibt
                    // zaehle die Anzahl vom Warenkorb hinzu und gib die Kosten fuer den Artikel im Warenkorb
                    if ($oWarenkorbArtikelAbhaengigerVersand->kArtikel === $oArtikel->kArtikel) {
                        // Zaehle die Anzahl des gleichen Artikels im Warenkorb auf die Anzahl die hinzukommen soll hinzu
                        $nAnzahl += $oWarenkorbArtikelAbhaengigerVersand->nAnzahl;
                        // Die Kosten vom Artikel im Warenkorb merken
                        $fKosten = $oWarenkorbArtikelAbhaengigerVersand->fKosten;
                        break;
                    }
                }

                // Gib die Differenzsumme fuer den hinzukommen Artikel zurueck
                return self::gibArtikelabhaengigeVersandkosten($cLandISO, $oArtikel, $nAnzahl, false) - $fKosten;
            }
        }

        return false;
    }

    /**
     * @param Artikel $oArtikel
     * @return int
     */
    public static function pruefeArtikelabhaengigeVersandkosten($oArtikel)
    {
        $bHookReturn = false;
        executeHook(HOOK_TOOLS_GLOBAL_PRUEFEARTIKELABHAENGIGEVERSANDKOSTEN, [
            'oArtikel'    => &$oArtikel,
            'bHookReturn' => &$bHookReturn
        ]);

        if ($bHookReturn) {
            return -1;
        }
        if ($oArtikel->FunktionsAttribute[FKT_ATTRIBUT_VERSANDKOSTEN]) {
            // Artikelabhaengige Versandkosten
            return 1;
        }
        if ($oArtikel->FunktionsAttribute[FKT_ATTRIBUT_VERSANDKOSTEN_GESTAFFELT]) {
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
    public static function gibArtikelabhaengigeVersandkosten($cLand, $Artikel, $nAnzahl, $bCheckLieferadresse = true)
    {
        $steuerSatz  = null;
        $bHookReturn = false;
        executeHook(HOOK_TOOLS_GLOBAL_GIBARTIKELABHAENGIGEVERSANDKOSTEN, [
            'oArtikel'    => &$Artikel,
            'cLand'       => &$cLand,
            'nAnzahl'     => &$nAnzahl,
            'bHookReturn' => &$bHookReturn
        ]);

        if ($bHookReturn) {
            return false;
        }
        $netPricesActive = Session::CustomerGroup()->isMerchant();
        // Steuersatz nur benötigt, wenn Nettokunde
        if ($netPricesActive === true) {
            $steuerSatz = Shop::Container()->getDB()->select(
                'tsteuersatz',
                'kSteuerklasse',
                Session::Cart()->gibVersandkostenSteuerklasse()
            )->fSteuersatz;
        }
        // gestaffelte
        if (!empty($Artikel->FunktionsAttribute[FKT_ATTRIBUT_VERSANDKOSTEN_GESTAFFELT])) {
            $arrVersand = array_filter(explode(';',
                $Artikel->FunktionsAttribute[FKT_ATTRIBUT_VERSANDKOSTEN_GESTAFFELT]));
            foreach ($arrVersand as $cVersand) {
                //DE 1-45,00:2-60,00:3-80;AT 1-90,00:2-120,00:3-150,00
                list($cLandAttr, $KostenTeil) = explode(' ', $cVersand);
                if ($cLandAttr && ($cLand === $cLandAttr || $bCheckLieferadresse === false)) {
                    $arrKosten = explode(':', $KostenTeil);
                    foreach ($arrKosten as $staffel) {
                        list($bisAnzahl, $fPreis) = explode('-', $staffel);
                        $fPreis = (float)str_replace(',', '.', $fPreis);
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
                                $oVersandPos->cPreisLocalized = gibPreisStringLocalized(
                                        berechneNetto((float)$oVersandPos->fKosten, $steuerSatz)
                                    ) . ' ' . Shop::Lang()->get('plus', 'productDetails') . ' ' .
                                    Shop::Lang()->get('vat', 'productDetails');
                            } else {
                                $oVersandPos->cPreisLocalized = gibPreisStringLocalized($oVersandPos->fKosten);
                            }

                            return $oVersandPos;
                        }
                    }
                }
            }
        }
        // flache
        if (!empty($Artikel->FunktionsAttribute[FKT_ATTRIBUT_VERSANDKOSTEN])) {
            $arrVersand = array_filter(explode(';', $Artikel->FunktionsAttribute[FKT_ATTRIBUT_VERSANDKOSTEN]));
            foreach ($arrVersand as $cVersand) {
                list($cLandAttr, $fKosten) = explode(' ', $cVersand);
                if ($cLandAttr && ($cLand === $cLandAttr || $bCheckLieferadresse === false)) {
                    $oVersandPos = new stdClass();
                    //posname lokalisiert ablegen
                    $oVersandPos->cName = [];
                    foreach ($_SESSION['Sprachen'] as $Sprache) {
                        $oVersandPos->cName[$Sprache->cISO] = Shop::Lang()->get('shippingFor', 'checkout') . ' ' .
                            $Artikel->cName . ' (' . $cLandAttr . ')';
                    }
                    $oVersandPos->fKosten = (float)str_replace(',', '.', $fKosten) * $nAnzahl;
                    if ($netPricesActive === true) {
                        $oVersandPos->cPreisLocalized = gibPreisStringLocalized(berechneNetto(
                                (float)$oVersandPos->fKosten,
                                $steuerSatz
                            )) . ' ' . Shop::Lang()->get('plus', 'productDetails') . ' ' .
                            Shop::Lang()->get('vat', 'productDetails');
                    } else {
                        $oVersandPos->cPreisLocalized = gibPreisStringLocalized($oVersandPos->fKosten);
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
        if (!is_array($positions)) {
            return $arrVersandpositionen;
        }
        $positions = array_filter($positions, function ($pos) {
            return (int)$pos->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL;
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
            if ((int)$pos->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL
                && !in_array($pos->kVersandklasse, $VKarr, true)
                && $pos->kVersandklasse > 0
            ) {
                $VKarr[] = $pos->kVersandklasse;
            }
        }
        sort($VKarr);

        return implode('-', $VKarr);
    }

    /**
     * @param Versandart|object $versandart
     * @param string            $cISO
     * @param string            $plz
     * @return stdClass|null
     * @former gibVersandZuschlag()
     */
    public static function getAdditionalFees($versandart, $cISO, $plz)
    {
        $versandzuschlaege = Shop::Container()->getDB()->selectAll(
            'tversandzuschlag',
            ['kVersandart', 'cISO'],
            [(int)$versandart->kVersandart, $cISO]
        );

        foreach ($versandzuschlaege as $versandzuschlag) {
            //ist plz enthalten?
            $plz_x = Shop::Container()->getDB()->queryPrepared(
                "SELECT * FROM tversandzuschlagplz
                WHERE ((cPLZAb <= :plz
                    AND cPLZBis >= :plz)
                    OR cPLZ = :plz)
                    AND kVersandzuschlag = :sid",
                ['plz' => $plz, 'sid' => (int)$versandzuschlag->kVersandzuschlag],
                \DB\ReturnType::SINGLE_OBJECT
            );
            if (isset($plz_x->kVersandzuschlagPlz) && $plz_x->kVersandzuschlagPlz > 0) {
                //posname lokalisiert ablegen
                $versandzuschlag->angezeigterName = [];
                foreach (Session::Languages() as $Sprache) {
                    $name_spr = Shop::Container()->getDB()->select(
                        'tversandzuschlagsprache',
                        'kVersandzuschlag', (int)$versandzuschlag->kVersandzuschlag,
                        'cISOSprache', $Sprache->cISO
                    );

                    $versandzuschlag->angezeigterName[$Sprache->cISO] = $name_spr->cName;
                }
                $versandzuschlag->cPreisLocalized = gibPreisStringLocalized($versandzuschlag->fZuschlag);

                return $versandzuschlag;
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
     * @return int|string
     * @former berechneVersandpreis()
     */
    public static function calculateShippingFees($versandart, $cISO, $oZusatzArtikel, $Artikel = 0)
    {
        if (!isset($oZusatzArtikel->fAnzahl)) {
            if (!isset($oZusatzArtikel)) {
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
                    : Session::Cart()->getWeight();
                $warenkorbgewicht += $oZusatzArtikel->fGewicht;
                $versand           = Shop::Container()->getDB()->queryPrepared(
                    'SELECT *
                        FROM tversandartstaffel
                        WHERE kVersandart = :sid
                            AND fBis >= :wght
                        ORDER BY fBis ASC',
                    ['sid' => (int)$versandart->kVersandart, 'wght' => $warenkorbgewicht],
                    \DB\ReturnType::SINGLE_OBJECT
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
                    : Session::Cart()->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], true);
                $warenkorbwert += $oZusatzArtikel->fWarenwertNetto;
                $versand        = Shop::Container()->getDB()->queryPrepared(
                    'SELECT *
                        FROM tversandartstaffel
                        WHERE kVersandart = :sid
                            AND fBis >= :val
                        ORDER BY fBis ASC',
                    ['sid' => (int)$versandart->kVersandart, 'val' => $warenkorbwert],
                    \DB\ReturnType::SINGLE_OBJECT
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
                        ? Session::Cart()->gibAnzahlArtikelExt([C_WARENKORBPOS_TYP_ARTIKEL])
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
                    \DB\ReturnType::SINGLE_OBJECT
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
            && (!empty($Artikel->FunktionsAttribute['versandkosten'])
                || !empty($Artikel->FunktionsAttribute['versandkosten gestaffelt']))
        ) {
            $fArticleSpecific = VersandartHelper::gibArtikelabhaengigeVersandkosten($cISO, $Artikel, 1);
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
                $fGesamtsummeWaren = berechneNetto(
                    Session::Cart()->gibGesamtsummeWarenExt(
                        [C_WARENKORBPOS_TYP_ARTIKEL],
                        1
                    ),
                    gibUst(Session::Cart()->gibVersandkostenSteuerklasse())
                );
            }
        } elseif ($versandart->eSteuer === 'brutto') {
            if ($Artikel) {
                $fArtikelPreis = berechneBrutto($Artikel->Preise->fVKNetto, gibUst($Artikel->kSteuerklasse));
            }
            if (isset($_SESSION['Warenkorb'])) {
                $fGesamtsummeWaren = Session::Cart()->gibGesamtsummeWarenExt(
                    [C_WARENKORBPOS_TYP_ARTIKEL],
                    1
                );
            }
        }

        if ($versandart->fVersandkostenfreiAbX > 0
            && (($Artikel && $fArtikelPreis >= $versandart->fVersandkostenfreiAbX)
                || ($fGesamtsummeWaren >= $versandart->fVersandkostenfreiAbX))
        ) {
            $preis = 0;
        }
        executeHook(HOOK_TOOLSGLOBAL_INC_BERECHNEVERSANDPREIS, [
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
     * @param string  $cISO
     * @param Artikel $Artikel
     * @param int     $barzahlungZulassen
     * @param int     $kKundengruppe
     * @return int|float
     * @former gibGuenstigsteVersandkosten()
     */
    public static function getLowestShippingFees($cISO, $Artikel, $barzahlungZulassen, $kKundengruppe)
    {
        $versandpreis = 99999;
        $query        = "SELECT *
            FROM tversandart
            WHERE cIgnoreShippingProposal != 'Y'
                AND cLaender LIKE '%" . $cISO . "%'
                AND (cVersandklassen = '-1'
                    OR cVersandklassen RLIKE '^([0-9 -]* )?" . $Artikel->kVersandklasse . " ')
                AND (cKundengruppen = '-1'
                    OR FIND_IN_SET('{$kKundengruppe}', REPLACE(cKundengruppen, ';', ',')) > 0)";
        // artikelabhaengige Versandarten nur laden und prüfen wenn der Artikel das entsprechende Funktionasattribut hat
        if (empty($Artikel->FunktionsAttribute['versandkosten'])
            && empty($Artikel->FunktionsAttribute['versandkosten gestaffelt'])
        ) {
            $query .= " AND cNurAbhaengigeVersandart = 'N'";
        }
        $methods = Shop::Container()->getDB()->query($query, \DB\ReturnType::ARRAY_OF_OBJECTS);
        foreach ($methods as $method) {
            if (!$barzahlungZulassen) {
                $za_bar = Shop::Container()->getDB()->select(
                    'tversandartzahlungsart',
                    'kZahlungsart', 6,
                    'kVersandart', (int)$method->kVersandart
                );
                if ($za_bar !== null && isset($za_bar->kVersandartZahlungsart) && $za_bar->kVersandartZahlungsart > 0) {
                    continue;
                }
            }
            $vp = self::calculateShippingFees($method, $cISO, null, $Artikel);
            if ($vp !== -1 && $vp < $versandpreis) {
                $versandpreis = $vp;
            }
            if ($vp === 0) {
                break;
            }
        }

        return $versandpreis === 99999 ? -1 : $versandpreis;
    }
}
