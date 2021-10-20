<?php

namespace JTL\Catalog\Wishlist;

use JTL\Helpers\GeneralObject;
use JTL\Shop;

/**
 * Class WishlistItemProperty
 * @package JTL\Catalog\Wishlist
 */
class WishlistItemProperty
{
    /**
     * @var int
     */
    public $kWunschlistePosEigenschaft;

    /**
     * @var int
     */
    public $kWunschlistePos;

    /**
     * @var int
     */
    public $kEigenschaft;

    /**
     * @var int
     */
    public $kEigenschaftWert;

    /**
     * @var string
     */
    public $cFreifeldWert;

    /**
     * @var string
     */
    public $cEigenschaftName;

    /**
     * @var string
     */
    public $cEigenschaftWertName;

    /**
     * WishlistItemProperty constructor.
     * @param int    $propertyID
     * @param null|int    $propertyValueID
     * @param string $freeText
     * @param string $propertyName
     * @param string $propertyValueName
     * @param int    $wishlistItemID
     */
    public function __construct(
        int $propertyID,
        ?int $propertyValueID,
        $freeText,
        $propertyName,
        $propertyValueName,
        int $wishlistItemID
    ) {
        $this->kEigenschaft         = $propertyID;
        $this->kEigenschaftWert     = $propertyValueID;
        $this->kWunschlistePos      = $wishlistItemID;
        $this->cFreifeldWert        = $freeText;
        $this->cEigenschaftName     = $propertyName;
        $this->cEigenschaftWertName = $propertyValueName;
    }

    /**
     * @return $this
     */
    public function schreibeDB(): self
    {
        $this->kWunschlistePosEigenschaft = Shop::Container()->getDB()->insert(
            'twunschlisteposeigenschaft',
            GeneralObject::copyMembers($this)
        );

        return $this;
    }

    /**
     * @return int
     */
    public function getID(): int
    {
        return $this->kWunschlistePosEigenschaft;
    }

    /**
     * @param int $id
     */
    public function setID(int $id): void
    {
        $this->kWunschlistePosEigenschaft = $id;
    }

    /**
     * @return int
     */
    public function getItemID(): int
    {
        return $this->kWunschlistePos;
    }

    /**
     * @param int $itemID
     */
    public function setItemID(int $itemID): void
    {
        $this->kWunschlistePos = $itemID;
    }

    /**
     * @return int
     */
    public function getPropertyID(): int
    {
        return $this->kEigenschaft;
    }

    /**
     * @param int $propertyID
     */
    public function setPropertyID(int $propertyID): void
    {
        $this->kEigenschaft = $propertyID;
    }

    /**
     * @return int
     */
    public function getPropertyValueID(): ?int
    {
        return $this->kEigenschaftWert;
    }

    /**
     * @param int $propertyValueID
     */
    public function setPropertyValueID(?int $propertyValueID): void
    {
        $this->kEigenschaftWert = $propertyValueID;
    }

    /**
     * @return string
     */
    public function getFreeTextValue(): string
    {
        return $this->cFreifeldWert;
    }

    /**
     * @param string $value
     */
    public function setFreeTextValue(string $value): void
    {
        $this->cFreifeldWert = $value;
    }

    /**
     * @return string
     */
    public function getPropertyName(): string
    {
        return $this->cEigenschaftName;
    }

    /**
     * @param string $name
     */
    public function setPropertyName(string $name): void
    {
        $this->cEigenschaftName = $name;
    }

    /**
     * @return string
     */
    public function getPropertyValueName(): string
    {
        return $this->cEigenschaftWertName;
    }

    /**
     * @param string $name
     */
    public function setPropertyValueName(string $name): void
    {
        $this->cEigenschaftWertName = $name;
    }
}
