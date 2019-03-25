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
     * @var int
     */
    public $ZIPFrom;

    /**
     * @var int
     */
    public $ZIPTo;

    /**
     * VersandzuschlagBereich constructor.
     * @param int $ZIPFrom
     * @param int $ZIPTo
     */
    public function __construct(int $ZIPFrom, int $ZIPTo)
    {
        $this->setZIPFrom($ZIPFrom)
             ->setZIPTo($ZIPTo);
    }

    /**
     * @return int
     */
    public function getZIPFrom(): int
    {
        return $this->ZIPFrom;
    }

    /**
     * @param int $ZIPFrom
     * @return VersandzuschlagBereich
     */
    public function setZIPFrom(int $ZIPFrom): self
    {
        $this->ZIPFrom = $ZIPFrom;

        return $this;
    }

    /**
     * @return int
     */
    public function getZIPTo(): int
    {
        return $this->ZIPTo;
    }

    /**
     * @param int $ZIPTo
     * @return VersandzuschlagBereich
     */
    public function setZIPTo(int $ZIPTo): self
    {
        $this->ZIPTo = $ZIPTo;

        return $this;
    }

    /**
     * @param int $zip
     * @return bool
     */
    public function isInArea(int $zip): bool
    {
        return ($this->getZIPFrom() >= $zip && $this->getZIPTo() <= $zip);
    }

    /**
     * @return string
     */
    public function getArea(): string
    {
        return $this->getZIPFrom() . ' - ' . $this->getZIPTo();
    }
}
