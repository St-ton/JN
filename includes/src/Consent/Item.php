<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Consent;

use JTL\Shop;

/**
 * Class Item
 * @package JTL\Consent
 */
class Item implements ItemInterface
{
    /**
     * @var int|string
     */
    private $id;

    /**
     * @var string[]
     */
    private $name;

    /**
     * @var string[]
     */
    private $description;

    /**
     * @var string[]
     */
    private $purpose;

    /**
     * @var string[]
     */
    private $company;

    /**
     * @var string[]
     */
    private $tos;

    /**
     * @var int
     */
    private $currentLanguageID;

    /**
     * Item constructor.
     */
    public function __construct()
    {
        $this->currentLanguageID = Shop::getLanguageID();
    }

    /**
     * @inheritDoc
     */
    public function getID()
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function setID($id): void
    {
        $this->id = $id;
    }

    /**
     * @inheritDoc
     */
    public function getName(int $idx = null): string
    {
        return $this->name[$idx ?? $this->currentLanguageID] ?? '';
    }

    /**
     * @inheritDoc
     */
    public function setName(string $name, int $idx = null): void
    {
        $this->name[$idx ?? $this->currentLanguageID] = $name;
    }

    /**
     * @inheritDoc
     */
    public function getDescription(int $idx = null): string
    {
        return $this->description[$idx ?? $this->currentLanguageID] ?? '';
    }

    /**
     * @inheritDoc
     */
    public function setDescription(string $description, int $idx = null): void
    {
        $this->description[$idx ?? $this->currentLanguageID] = $description;
    }

    /**
     * @inheritDoc
     */
    public function getPurpose(int $idx = null): string
    {
        return $this->purpose[$idx ?? $this->currentLanguageID] ?? '';
    }

    /**
     * @inheritDoc
     */
    public function setPurpose(string $purpose, int $idx = null): void
    {
        $this->purpose[$idx ?? $this->currentLanguageID] = $purpose;
    }

    /**
     * @inheritDoc
     */
    public function getCompany(int $idx = null): string
    {
        return $this->company[$idx ?? $this->currentLanguageID] ?? '';
    }

    /**
     * @inheritDoc
     */
    public function setCompany(string $company, int $idx = null): void
    {
        $this->company[$idx ?? $this->currentLanguageID] = $company;
    }

    /**
     * @inheritDoc
     */
    public function getTos(int $idx = null): string
    {
        return $this->tos[$idx ?? $this->currentLanguageID] ?? '';
    }

    /**
     * @inheritDoc
     */
    public function setTos(string $tos, int $idx = null): void
    {
        $this->tos[$idx ?? $this->currentLanguageID] = $tos;
    }

    /**
     * @inheritDoc
     */
    public function getCurrentLanguageID(): int
    {
        return $this->currentLanguageID;
    }

    /**
     * @inheritDoc
     */
    public function setCurrentLanguageID(int $currentLanguageID): void
    {
        $this->currentLanguageID = $currentLanguageID;
    }
}
