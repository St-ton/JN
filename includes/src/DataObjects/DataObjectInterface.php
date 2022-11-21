<?php declare(strict_types=1);

namespace JTL\DataObjects;

/**
 * Interface StarterInterface
 * @package JTL\Cron\Starter
 */
interface DataObjectInterface
{
    /**
     * @param array $data
     * @return DataObjectInterface
     */
    public function hydrate(array $data) : self;

    /**
     * @return array
     */
    public function toArray(): array;

    /**
     * @return array
     */
    public function extract(): array;
}
