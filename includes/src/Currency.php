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
     * Currency constructor.
     * @param int|null $id
     */
    public function __construct($id = null)
    {
        if ($id > 0) {
            $this->extract(Shop::DB()->select('twaehrung', 'kWaehrung', (int)$id));
        }
    }

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
    public function setID($id)
    {
        $this->id = (int)$id;

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
    public function setCode($code)
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
    public function setName($name)
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
    public function setHtmlEntity($htmlEntity)
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
    public function setConversionFactor($conversionFactor)
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
    public function setIsDefault($isDefault)
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
    public function setForcePlacementBeforeNumber($forcePlacementBeforeNumber)
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
    public function setDecimalSeparator($decimalSeparator)
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
    public function setThousandsSeparator($thousandsSeparator)
    {
        $this->thousandsSeparator = $thousandsSeparator;

        return $this;
    }

    /**
     * @return string
     */
    public function getURL()
    {
        return $this->cURL;
    }

    /**
     * @param string $cURL
     * @return Currency
     */
    public function setURL($cURL)
    {
        $this->cURL = $cURL;

        return $this;
    }

    /**
     * @return string
     */
    public function getURLFull()
    {
        return $this->cURLFull;
    }

    /**
     * @param string $cURLFull
     * @return Currency
     */
    public function setURLFull($cURLFull)
    {
        $this->cURLFull = $cURLFull;

        return $this;
    }

    /**
     * @return Currency
     */
    public function getDefault()
    {
        return $this->extract(Shop::DB()->select('twaehrung', 'cStandard', 'Y'));
    }

    /**
     * @param stdClass $obs
     * @return $this
     */
    private function extract($obs)
    {
        foreach (get_object_vars($obs) as $var => $value) {
            if (($mapped = self::getMapping($var)) !== null) {
                $method = 'set' . $mapped;
                $this->$method($value);
            }
        }

        return $this;
    }
}