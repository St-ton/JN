<?php declare(strict_types=1);

namespace JTL\Plugin\Data;

/**
 * Class Cache
 * @package JTL\Plugin\Data
 */
class Cache
{
    /**
     * @var string|null
     */
    private ?string $id = null;

    /**
     * @var string|null
     */
    private ?string $group = null;

    /**
     * @return string
     */
    public function getID(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setID(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getGroup(): string
    {
        return $this->group;
    }

    /**
     * @param string $group
     */
    public function setGroup(string $group): void
    {
        $this->group = $group;
    }
}
