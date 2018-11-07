<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

/**
 * Class TaxHelper
 * @since since 5.0.0
 */
class TaxHelper
{
    /**
     * @param int $taxID
     * @return mixed
     * @since since 5.0.0
     */
    public static function getSalesTax(int $taxID)
    {
        if (!isset($_SESSION['Steuersatz'])
            || !is_array($_SESSION['Steuersatz'])
            || count($_SESSION['Steuersatz']) === 0
        ) {
            self::setTaxRates();
        }
        if (isset($_SESSION['Steuersatz'])
            && is_array($_SESSION['Steuersatz'])
            && !isset($_SESSION['Steuersatz'][$taxID])
        ) {
            $nKey_arr = array_keys($_SESSION['Steuersatz']);
            $taxID    = $nKey_arr[0];
        }

        return $_SESSION['Steuersatz'][$taxID];
    }

    /**
     * @param string $steuerland
     * @since since 5.0.0
     */
    public static function setTaxRates($steuerland = null)
    {
        $_SESSION['Steuersatz'] = [];
        $billingCountryCode     = null;
        $merchantCountryCode    = 'DE';
        $Firma                  = Shop::Container()->getDB()->query(
            'SELECT cLand FROM tfirma',
            \DB\ReturnType::SINGLE_OBJECT
        );
        if (!empty($Firma->cLand)) {
            $merchantCountryCode = Sprache::getIsoCodeByCountryName($Firma->cLand);
        }
        if (defined('STEUERSATZ_STANDARD_LAND')) {
            $merchantCountryCode = STEUERSATZ_STANDARD_LAND;
        }
        $deliveryCountryCode = $merchantCountryCode;
        if ($steuerland) {
            $deliveryCountryCode = $steuerland;
        }
        if (!empty(Session\Session::getCustomer()->cLand)) {
            $deliveryCountryCode = Session\Session::getCustomer()->cLand;
            $billingCountryCode  = Session\Session::getCustomer()->cLand;
        }
        if (!empty($_SESSION['Lieferadresse']->cLand)) {
            $deliveryCountryCode = $_SESSION['Lieferadresse']->cLand;
        }
        if ($billingCountryCode === null) {
            $billingCountryCode = $deliveryCountryCode;
        }
        $_SESSION['Steuerland']     = $deliveryCountryCode;
        $_SESSION['cLieferlandISO'] = $deliveryCountryCode;

        // Pruefen, ob Voraussetzungen fuer innergemeinschaftliche Lieferung (IGL) erfuellt werden #3525
        // Bedingungen fuer Steuerfreiheit bei Lieferung in EU-Ausland:
        // Kunde hat eine zum Rechnungland passende, gueltige USt-ID gesetzt &&
        // Firmen-Land != Kunden-Rechnungsland && Firmen-Land != Kunden-Lieferland
        $UstBefreiungIGL = false;
        if ($merchantCountryCode !== $deliveryCountryCode
            && $merchantCountryCode !== $billingCountryCode
            && !empty(Session\Session::getCustomer()->cUSTID)
            && (strcasecmp($billingCountryCode, substr(Session\Session::getCustomer()->cUSTID, 0, 2)) === 0
                || (strcasecmp($billingCountryCode, 'GR') === 0
                    && strcasecmp(substr(Session\Session::getCustomer()->cUSTID, 0, 2), 'EL') === 0))
        ) {
            $deliveryCountry = Shop::Container()->getDB()->select('tland', 'cISO', $deliveryCountryCode);
            $shopCountry     = Shop::Container()->getDB()->select('tland', 'cISO', $merchantCountryCode);
            if (!empty($deliveryCountry->nEU) && !empty($shopCountry->nEU)) {
                $UstBefreiungIGL = true;
            }
        }
        $steuerzonen = Shop::Container()->getDB()->queryPrepared(
            'SELECT tsteuerzone.kSteuerzone
                FROM tsteuerzone, tsteuerzoneland
                WHERE tsteuerzoneland.cISO = :ciso
                    AND tsteuerzoneland.kSteuerzone = tsteuerzone.kSteuerzone',
            ['ciso' => $deliveryCountryCode],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (count($steuerzonen) === 0) {
            // Keine Steuerzone für $deliveryCountryCode hinterlegt - das ist fatal!
            $redirURL  = Shop::Container()->getLinkService()->getStaticRoute('bestellvorgang.php') .
                '?editRechnungsadresse=1';
            $urlHelper = new UrlHelper(Shop::getURL() . $_SERVER['REQUEST_URI']);
            $country   = Sprache::getCountryCodeByCountryName($deliveryCountryCode);

            Shop::Container()->getLogService()->error('Keine Steuerzone für "' . $country . '" hinterlegt!');

            if (RequestHelper::isAjaxRequest()) {
                $link = new \Link\Link(Shop::Container()->getDB());
                $link->setLinkType(LINKTYP_STARTSEITE);
                $link->setTitle(Shop::Lang()->get('missingParamShippingDetermination', 'errorMessages'));

                Shop::Smarty()
                    ->assign('cFehler', Shop::Lang()->get('missingTaxZoneForDeliveryCountry', 'errorMessages', $country))
                    ->assign('Link', $link)
                    ->display('layout/index.tpl');
                exit;
            }

            if ($redirURL === $urlHelper->normalize()) {
                Shop::Smarty()->assign(
                    'cFehler',
                    Shop::Lang()->get('missingParamShippingDetermination', 'errorMessages')
                    . '<br/>'
                    . Shop::Lang()->get('missingTaxZoneForDeliveryCountry', 'errorMessages', $country)
                );

                return;
            }

            header('Location: ' . $redirURL);
            exit;
        }
        $steuerklassen = Shop::Container()->getDB()->query(
            'SELECT * FROM tsteuerklasse',
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        $zones         = \Functional\map($steuerzonen, function ($e) {
            return (int)$e->kSteuerzone;
        });
        $qry           = count($zones) > 0
            ? 'kSteuerzone IN (' . implode(',', $zones) . ')'
            : '';

        if ($qry !== '') {
            foreach ($steuerklassen as $steuerklasse) {
                $steuersatz = Shop::Container()->getDB()->query(
                    'SELECT fSteuersatz
                        FROM tsteuersatz
                        WHERE kSteuerklasse = ' . (int)$steuerklasse->kSteuerklasse . '
                        AND (' . $qry . ') ORDER BY nPrio DESC',
                    \DB\ReturnType::SINGLE_OBJECT
                );
                if (isset($steuersatz->fSteuersatz)) {
                    $_SESSION['Steuersatz'][$steuerklasse->kSteuerklasse] = $steuersatz->fSteuersatz;
                } else {
                    $_SESSION['Steuersatz'][$steuerklasse->kSteuerklasse] = 0;
                }
                if ($UstBefreiungIGL) {
                    $_SESSION['Steuersatz'][$steuerklasse->kSteuerklasse] = 0;
                }
            }
        }
        if (isset($_SESSION['Warenkorb']) && $_SESSION['Warenkorb'] instanceof Warenkorb) {
            Session\Session::getCart()->setzePositionsPreise();
        }
    }

    /**
     * @param array    $positions
     * @param int|bool $net
     * @param true     $html
     * @param mixed int|object $currency
     * @return array
     * @former gibAlteSteuerpositionen()
     * @since since 5.0.0
     */
    public static function getOldTaxPositions(array $positions, $net = -1, $html = true, $currency = 0): array
    {
        if ($net === -1) {
            $net = Session\Session::getCustomerGroup()->isMerchant();
        }
        $taxRates = [];
        $taxPos   = [];
        $conf     = Shop::getSettings([CONF_GLOBAL]);
        if ($conf['global']['global_steuerpos_anzeigen'] === 'N') {
            return $taxPos;
        }
        foreach ($positions as $position) {
            if ($position->fMwSt > 0 && !in_array($position->fMwSt, $taxRates, true)) {
                $taxRates[] = $position->fMwSt;
            }
        }
        sort($taxRates);
        foreach ($positions as $position) {
            if ($position->fMwSt <= 0) {
                continue;
            }
            $i = array_search($position->fMwSt, $taxRates, true);
            if (!isset($taxPos[$i]->fBetrag) || !$taxPos[$i]->fBetrag) {
                $taxPos[$i]                  = new stdClass();
                $taxPos[$i]->cName           = lang_steuerposition($position->fMwSt, $net);
                $taxPos[$i]->fUst            = $position->fMwSt;
                $taxPos[$i]->fBetrag         = ($position->fPreis * $position->nAnzahl * $position->fMwSt) / 100.0;
                $taxPos[$i]->cPreisLocalized = Preise::getLocalizedPriceString($taxPos[$i]->fBetrag, $currency, $html);
            } else {
                $taxPos[$i]->fBetrag         += ($position->fPreis * $position->nAnzahl * $position->fMwSt) / 100.0;
                $taxPos[$i]->cPreisLocalized = Preise::getLocalizedPriceString($taxPos[$i]->fBetrag, $currency, $html);
            }
        }

        return $taxPos;
    }

    /**
     * @param float $price
     * @param float $taxRate
     * @param int   $precision
     * @return float
     * @since since 5.0.0
     */
    public static function getGross($price, $taxRate, int $precision = 2): float
    {
        return round($price * (100 + $taxRate) / 100, $precision);
    }

    /**
     * @param float $price
     * @param float $taxRate
     * @param int   $precision
     * @return float
     * @since since 5.0.0
     */
    public static function getNet($price, $taxRate, int $precision = 2): float
    {
        return round($price / (100 + (float)$taxRate) * 100, $precision);
    }
}
