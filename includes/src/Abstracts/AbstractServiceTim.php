<?php declare(strict_types=1);

namespace JTL\Abstracts;

use JTL\DataObjects\DomainObjectInterface;

/**
 * Class AbstractService
 * @package JTL\Abstracts
 */
abstract class AbstractServiceTim
{
    /**
     * @param string $className
     * @param array $defaultValues
     * @return DomainObjectInterface
     */
    public function generateDomainObject(string $className, array $defaultValues): DomainObjectInterface
    {
        return new $className(...$defaultValues);
    }
}
