<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Checkout;

/**
 * Class VersandzuschlagBereich
 * @package JTL\Checkout
 */
class VersandzuschlagBereich
{
    /**
     * @var string
     */
    public $ZIPFrom;

    /**
     * @var string
     */
    public $ZIPTo;

    /**
     * VersandzuschlagBereich constructor.
     * @param string $ZIPFrom
     * @param string $ZIPTo
     */
    public function __construct(string $ZIPFrom, string $ZIPTo)
    {
        $this->setZIPFrom($ZIPFrom)
             ->setZIPTo($ZIPTo);
    }

    /**
     * @return string
     */
    public function getZIPFrom(): string
    {
        return $this->ZIPFrom;
    }

    /**
     * @param string $ZIPFrom
     * @return VersandzuschlagBereich
     */
    public function setZIPFrom(string $ZIPFrom): self
    {
        $this->ZIPFrom = $ZIPFrom;

        return $this;
    }

    /**
     * @return string
     */
    public function getZIPTo(): string
    {
        return $this->ZIPTo;
    }

    /**
     * @param string $ZIPTo
     * @return VersandzuschlagBereich
     */
    public function setZIPTo(string $ZIPTo): self
    {
        $this->ZIPTo = $ZIPTo;

        return $this;
    }

    /**
     * @param string $zip
     * @return bool
     */
    public function isInArea(string $zip): bool
    {
        return ($this->getZIPFrom() <= $zip && $this->getZIPTo() >= $zip);
    }

    /**
     * @return string
     */
    public function getArea(): string
    {
        return $this->getZIPFrom() . ' - ' . $this->getZIPTo();
    }
}
