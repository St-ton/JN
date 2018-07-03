<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Currency
 */
class Currency
{
    use MagicCompatibilityTrait;

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $htmlEntity;

    /**
     * @var float
     */
    private $conversionFactor;

    /**
     * @var bool
     */
    private $isDefault;

    /**
     * @var bool
     */
    private $forcePlacementBeforeNumber;

    /**
     * @var string
     */
    private $decimalSeparator;

    /**
     * @var string
     */
    private $thousandsSeparator;

    /**
     * @var string
     */
    private $cURL;

    /**
     * @var string
     */
    private $cURLFull;

    /**
     * @var array
     */
    private static $mapping = [
        'kWaehrung'            => 'ID',
        'cISO'                 => 'Code',
        'cName'                => 'Name',
        'cNameHTML'            => 'HtmlEntity',
        'fFaktor'              => 'ConversionFactor',
        'cStandard'            => 'IsDefault',
        'cVorBetrag'           => 'forcePlacementBeforeNumber',
        'cTrennzeichenCent'    => 'DecimalSeparator',
        'cTrennzeichenTausend' => 'ThousandsSeparator',
        'cURL'                 => 'URL',
        'cURLFull'             => 'URLFull'
    ];

    /**
     * Currency constructor.
     * @param int|null $id
     */
    public function __construct(int $id = null)
    {
        if ($id > 0) {
            $this->extract(Shop::Container()->getDB()->select('twaehrung', 'kWaehrung', $id));
        }
    }

    /**
     * @return int
     */
    public function getID()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Currency
     */
    public function setID(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return Currency
     */
    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Currency
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getHtmlEntity()
    {
        return $this->htmlEntity;
    }

    /**
     * @param string $htmlEntity
     * @return Currency
     */
    public function setHtmlEntity(string $htmlEntity): self
    {
        $this->htmlEntity = $htmlEntity;

        return $this;
    }

    /**
     * @return float
     */
    public function getConversionFactor()
    {
        return $this->conversionFactor;
    }

    /**
     * @param float $conversionFactor
     * @return Currency
     */
    public function setConversionFactor($conversionFactor): self
    {
        $this->conversionFactor = (float)$conversionFactor;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDefault()
    {
        return $this->isDefault;
    }

    /**
     * @param bool|string $isDefault
     * @return Currency
     */
    public function setIsDefault($isDefault): self
    {
        if (is_string($isDefault)) {
            $isDefault = $isDefault === 'Y';
        }
        $this->isDefault = $isDefault;

        return $this;
    }

    /**
     * @return bool
     */
    public function getForcePlacementBeforeNumber()
    {
        return $this->forcePlacementBeforeNumber;
    }

    /**
     * @param bool|string $forcePlacementBeforeNumber
     * @return Currency
     */
    public function setForcePlacementBeforeNumber($forcePlacementBeforeNumber): self
    {
        if (is_string($forcePlacementBeforeNumber)) {
            $forcePlacementBeforeNumber = $forcePlacementBeforeNumber === 'Y';
        }
        $this->forcePlacementBeforeNumber = $forcePlacementBeforeNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getDecimalSeparator()
    {
        return $this->decimalSeparator;
    }

    /**
     * @param string $decimalSeparator
     * @return Currency
     */
    public function setDecimalSeparator($decimalSeparator): self
    {
        $this->decimalSeparator = $decimalSeparator;

        return $this;
    }

    /**
     * @return string
     */
    public function getThousandsSeparator()
    {
        return $this->thousandsSeparator;
    }

    /**
     * @param string $thousandsSeparator
     * @return Currency
     */
    public function setThousandsSeparator(string $thousandsSeparator): self
    {
        $this->thousandsSeparator = $thousandsSeparator;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getURL()
    {
        return $this->cURL;
    }

    /**
     * @param string $cURL
     * @return Currency
     */
    public function setURL(string $cURL): self
    {
        $this->cURL = $cURL;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getURLFull()
    {
        return $this->cURLFull;
    }

    /**
     * @param string $cURLFull
     * @return Currency
     */
    public function setURLFull(string $cURLFull): self
    {
        $this->cURLFull = $cURLFull;

        return $this;
    }

    /**
     * @return Currency
     */
    public function getDefault(): self
    {
        return $this->extract(Shop::Container()->getDB()->select('twaehrung', 'cStandard', 'Y'));
    }

    /**
     * @param stdClass $obs
     * @return $this
     */
    private function extract(stdClass $obs): self
    {
        foreach (get_object_vars($obs) as $var => $value) {
            if (($mapped = self::getMapping($var)) !== null) {
                $method = 'set' . $mapped;
                $this->$method($value);
            }
        }

        return $this;
    }

    /**
     * @param float  $priceNet
     * @param float  $priceGross
     * @param string $class
     * @param bool   $forceTax
     * @return string
     */
    public static function getCurrencyConversion($priceNet, $priceGross, $class = '', bool $forceTax = true): string
    {
        $res        = '';
        $currencies = \Session\Session::Currencies();
        if (count($currencies) > 0) {
            $oSteuerklasse = Shop::Container()->getDB()->select('tsteuerklasse', 'cStandard', 'Y');
            $taxClassID    = $oSteuerklasse !== null ? (int)$oSteuerklasse->kSteuerklasse : 1;
            if ((float)$priceNet > 0) {
                $priceNet   = (float)$priceNet;
                $priceGross = TaxHelper::getGross((float)$priceNet, TaxHelper::getSalesTax($taxClassID));
            } elseif ((float)$priceGross > 0) {
                $priceNet   = TaxHelper::getNet((float)$priceGross, TaxHelper::getSalesTax($taxClassID));
                $priceGross = (float)$priceGross;
            }
            $res = '<span class="preisstring ' . $class . '">';
            foreach ($currencies as $i => $currency) {
                $cPreisLocalized       = number_format(
                    $priceNet * $currency->getConversionFactor(),
                    2,
                    $currency->getDecimalSeparator(),
                    $currency->getThousandsSeparator()
                );
                $cPreisBruttoLocalized = number_format(
                    $priceGross * $currency->getConversionFactor(),
                    2,
                    $currency->getDecimalSeparator(),
                    $currency->getThousandsSeparator()
                );
                if ($currency->getForcePlacementBeforeNumber() === true) {
                    $cPreisLocalized       = $currency->getHtmlEntity() . ' ' . $cPreisLocalized;
                    $cPreisBruttoLocalized = $currency->getHtmlEntity() . ' ' . $cPreisBruttoLocalized;
                } else {
                    $cPreisLocalized       = $cPreisLocalized . ' ' . $currency->getHtmlEntity();
                    $cPreisBruttoLocalized = $cPreisBruttoLocalized . ' ' . $currency->getHtmlEntity();
                }
                // Wurde geändert weil der Preis nun als Betrag gesehen wird
                // und die Steuer direkt in der Versandart als eSteuer Flag eingestellt wird
                if ($i > 0) {
                    $res .= $forceTax
                        ? ('<br><strong>' . $cPreisBruttoLocalized . '</strong>' .
                            ' (<em>' . $cPreisLocalized . ' ' .
                            Shop::Lang()->get('net') . '</em>)')
                        : ('<br> ' . $cPreisBruttoLocalized);
                } else {
                    $res .= $forceTax
                        ? ('<strong>' . $cPreisBruttoLocalized . '</strong>' .
                            ' (<em>' . $cPreisLocalized . ' ' .
                            Shop::Lang()->get('net') . '</em>)')
                        : '<strong>' . $cPreisBruttoLocalized . '</strong>';
                }
            }
            $res .= '</span>';
        }

        return $res;
    }

    /**
     * Converts price into given currency
     *
     * @param float  $price
     * @param string $iso - EUR / USD
     * @param int    $id - kWaehrung
     * @param bool   $round
     * @param int    $precision
     * @return float|bool
     */
    public static function convertCurrency($price, string $iso = null, $id = null, bool $round = true, int $precision = 2)
    {
        if (count(Session::Currencies()) === 0) {
            $_SESSION['Waehrungen'] = [];
            $allCurrencies          = Shop::Container()->getDB()->selectAll('twaehrung', [], [], 'kWaehrung');
            foreach ($allCurrencies as $currency) {
                $_SESSION['Waehrungen'][] = new self($currency->kWaehrung);
            }
        }
        foreach (Session::Currencies() as $currency) {
            if (($iso !== null && $currency->getCode() === $iso) || ($id !== null && $currency->getID() === (int)$id)) {
                $newprice = $price * $currency->getConversionFactor();

                return $round ? round($newprice, $precision) : $newprice;
            }
        }

        return false;
    }
}
