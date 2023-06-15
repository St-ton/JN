<?php declare(strict_types=1);

namespace JTL\Backend\LocalizationCheck;

use stdClass;

/**
 * Class Item
 * @package JTL\Backend\LocalizationCheck
 */
class Item
{
    /**
     * @var int
     */
    private int $langID;

    /**
     * @var int
     */
    private int $id;

    /**
     * @var string
     */
    private string $name;

    /**
     * @var string|null
     */
    private ?string $additional;

    /**
     * @param stdClass $data
     */
    public function __construct(stdClass $data)
    {
        $this->langID     = (int)$data->langID;
        $this->id         = (int)$data->id;
        $this->name       = $data->name;
        $this->additional = $data->additional ?? null;
    }

    /**
     * @return int
     */
    public function getLanguageID(): int
    {
        return $this->langID;
    }

    /**
     * @param int $langID
     */
    public function setLanguageID(int $langID): void
    {
        $this->langID = $langID;
    }

    /**
     * @return int
     */
    public function getID(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setID(int $id): void
    {
        $this->id = $id;
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
     * @return string|null
     */
    public function getAdditional(): ?string
    {
        return $this->additional;
    }

    /**
     * @param string|null $additional
     */
    public function setAdditional(?string $additional): void
    {
        $this->additional = $additional;
    }
}
