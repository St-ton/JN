<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Staat
 */
class Staat
{
    /**
     * @var int
     */
    public $kStaat;

    /**
     * @var string
     */
    public $cLandIso;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var string
     */
    public $cCode;

    /**
     * @param array $options
     */
    public function __construct(array $options = null)
    {
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options): self
    {
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (in_array($method, $methods, true) && method_exists($this, $method)) {
                $this->$method($value);
            }
        }

        return $this;
    }

    /**
     * @return int|null
     */
    public function getStaat(): ?int
    {
        return $this->kStaat;
    }

    /**
     * @return string|null
     */
    public function getLandIso(): ?string
    {
        return $this->cLandIso;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->cName;
    }

    /**
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->cCode;
    }

    /**
     * @param int $kStaat
     * @return $this
     */
    public function setStaat(int $kStaat): self
    {
        $this->kStaat = $kStaat;

        return $this;
    }

    /**
     * @param string $cLandIso
     * @return $this
     */
    public function setLandIso(string $cLandIso): self
    {
        $this->cLandIso = $cLandIso;

        return $this;
    }

    /**
     * @param string $cName
     * @return $this
     */
    public function setName(string $cName): self
    {
        $this->cName = $cName;

        return $this;
    }

    /**
     * @param string $cCode
     * @return $this
     */
    public function setCode(string $cCode): self
    {
        $this->cCode = $cCode;

        return $this;
    }

    /**
     * @param string $cLandIso
     * @return array|null
     */
    public static function getRegions(string $cLandIso): ?array
    {
        if (mb_strlen($cLandIso) === 2) {
            $countries = Shop::Container()->getDB()->selectAll('tstaat', 'cLandIso', $cLandIso, '*', 'cName');
            if (is_array($countries) && count($countries) > 0) {
                $states = [];
                foreach ($countries as $country) {
                    $options = [
                        'Staat'   => $country->kStaat,
                        'LandIso' => $country->cLandIso,
                        'Name'    => $country->cName,
                        'Code'    => $country->cCode,
                    ];

                    $states[] = new self($options);
                }

                return $states;
            }
        }

        return null;
    }

    /**
     * @param string $cCode
     * @param string $cLandISO
     * @return null|Staat
     */
    public static function getRegionByIso($cCode, $cLandISO = ''): ?Staat
    {
        if (mb_strlen($cCode) > 0) {
            $key2 = null;
            $val2 = null;
            if (mb_strlen($cLandISO) > 0) {
                $key2 = 'cLandIso';
                $val2 = $cLandISO;
            }
            $oObj = Shop::Container()->getDB()->select('tstaat', 'cCode', $cCode, $key2, $val2);
            if (isset($oObj->kStaat) && $oObj->kStaat > 0) {
                $options = [
                    'Staat'   => $oObj->kStaat,
                    'LandIso' => $oObj->cLandIso,
                    'Name'    => $oObj->cName,
                    'Code'    => $oObj->cCode,
                ];

                return new self($options);
            }
        }

        return null;
    }

    /**
     * @param string $cName
     * @return null|Staat
     */
    public static function getRegionByName(string $cName): ?Staat
    {
        if (mb_strlen($cName) > 0) {
            $oObj = Shop::Container()->getDB()->select('tstaat', 'cName', $cName);
            if (isset($oObj->kStaat) && $oObj->kStaat > 0) {
                $options = [
                    'Staat'   => $oObj->kStaat,
                    'LandIso' => $oObj->cLandIso,
                    'Name'    => $oObj->cName,
                    'Code'    => $oObj->cCode,
                ];

                return new self($options);
            }
        }

        return null;
    }
}
