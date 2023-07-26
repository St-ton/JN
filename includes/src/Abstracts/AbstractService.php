<?php declare(strict_types=1);

namespace JTL\Abstracts;

use JTL\DataObjects\DomainObjectInterface;
use JTL\DB\DbInterface;

/**
 * Class AbstractService
 * @package JTL\Abstracts
 */
abstract class AbstractService
{

    protected DbInterface $db;

    /**
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db = $db;
    }

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
