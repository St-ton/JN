<?php

namespace JTL\Country;

use JTL\MagicCompatibilityTrait;

/**
 * Class State
 * @package JTL
 */
class State
{
    use MagicCompatibilityTrait;

    /**
     * @var array
     */
    protected static $mapping = [
        'kStaat'   => 'Id',
        'cLandIso' => 'CountryISO',
        'cName'    => 'Name',
        'cCode'    => 'Iso'
    ];

    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $countryISO;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $iso;

    /**
     * State constructor.
     */
    public function __construct()
    {

    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return State
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getCountryISO(): string
    {
        return $this->countryISO;
    }

    /**
     * @param string $countryISO
     * @return State
     */
    public function setCountryISO(string $countryISO): self
    {
        $this->countryISO = $countryISO;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return State
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getIso(): string
    {
        return $this->iso;
    }

    /**
     * @param string $iso
     * @return State
     */
    public function setIso(string $iso): self
    {
        $this->iso = $iso;

        return $this;
    }
}
