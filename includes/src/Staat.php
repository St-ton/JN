<?php

namespace JTL;

/**
 * Class Staat
 * @package JTL
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
     * Staat constructor.
     * @param array|null $options
     */
    public function __construct(array $options = null)
    {
        if (\is_array($options)) {
            $this->setOptions($options);
        }
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options): self
    {
        $methods = \get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'set' . \ucfirst($key);
            if (\in_array($method, $methods, true) && \method_exists($this, $method)) {
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
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->cName = $name;

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
     * @param string $iso
     * @return array|null
     */
    public static function getRegions(string $iso): ?array
    {
        if (\mb_strlen($iso) !== 2) {
            return null;
        }
        $countries = Shop::Container()->getDB()->selectAll('tstaat', 'cLandIso', $iso, '*', 'cName');
        if (\is_array($countries) && \count($countries) > 0) {
            $states = [];
            foreach ($countries as $country) {
                $options = [
                    'Staat'   => (int)$country->kStaat,
                    'LandIso' => $country->cLandIso,
                    'Name'    => $country->cName,
                    'Code'    => $country->cCode,
                ];

                $states[] = new self($options);
            }

            return $states;
        }

        return null;
    }

    /**
     * @param string $code
     * @param string $countryISO
     * @return null|Staat
     */
    public static function getRegionByIso(string $code, $countryISO = ''): ?Staat
    {
        if (\mb_strlen($code) === 0) {
            return null;
        }
        $key2 = null;
        $val2 = null;
        if (\mb_strlen($countryISO) > 0) {
            $key2 = 'cLandIso';
            $val2 = $countryISO;
        }
        $data = Shop::Container()->getDB()->select('tstaat', 'cCode', $code, $key2, $val2);
        if (isset($data->kStaat) && $data->kStaat > 0) {
            $options = [
                'Staat'   => (int)$data->kStaat,
                'LandIso' => $data->cLandIso,
                'Name'    => $data->cName,
                'Code'    => $data->cCode,
            ];

            return new self($options);
        }

        return null;
    }

    /**
     * @param string $name
     * @return null|Staat
     */
    public static function getRegionByName(string $name): ?Staat
    {
        $data = Shop::Container()->getDB()->select('tstaat', 'cName', $name);
        if (!isset($data->kStaat)) {
            return null;
        }
        $options = [
            'Staat'   => (int)$data->kStaat,
            'LandIso' => $data->cLandIso,
            'Name'    => $data->cName,
            'Code'    => $data->cCode,
        ];

        return new self($options);
    }
}
