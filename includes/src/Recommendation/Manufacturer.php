<?php

namespace JTL\Recommendation;

use stdClass;

/**
 * Class Manufacturer
 * @package JTL\Recommendation
 */
class Manufacturer
{
    /**
     * @var string
     */
    private $iso;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $vatID;

    /**
     * @var string
     */
    private $profileURL;

    /**
     * @var string
     */
    private $gtcURL;

    /**
     * @var stdClass
     */
    private $metas = '';

    /**
     * Manufacturer constructor.
     * @param stdClass $manufacturer
     */
    public function __construct(stdClass $manufacturer)
    {
        $this->setIso($manufacturer->country_code);
        $this->setName($manufacturer->company_name);
        $this->setVatID($manufacturer->vat_id);
        $this->setProfileURL($manufacturer->profile_url);
        $this->setGtcURL($manufacturer->gtc_url);
        $this->setMetas($manufacturer->metas);
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
     */
    public function setIso(string $iso): void
    {
        $this->iso = $iso;
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
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getVatID(): string
    {
        return $this->vatID;
    }

    /**
     * @param string $vatID
     */
    public function setVatID(string $vatID): void
    {
        $this->vatID = $vatID;
    }

    /**
     * @return string
     */
    public function getProfileURL(): string
    {
        return $this->profileURL;
    }

    /**
     * @param string $profileURL
     */
    public function setProfileURL(string $profileURL): void
    {
        $this->profileURL = $profileURL;
    }

    /**
     * @return string
     */
    public function getGtcURL(): string
    {
        return $this->gtcURL;
    }

    /**
     * @param string $gtcURL
     */
    public function setGtcURL(string $gtcURL): void
    {
        $this->gtcURL = $gtcURL;
    }

    /**
     * @return stdClass
     */
    public function getMetas(): stdClass
    {
        return $this->metas;
    }

    /**
     * @param stdClass $metas
     */
    public function setMetas(stdClass $metas): void
    {
        $this->metas = $metas;
    }
}
