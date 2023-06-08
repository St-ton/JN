<?php declare(strict_types=1);

namespace JTL\RMA;

use JTL\Abstracts\AbstractRepository;
use stdClass;

/**
 * Class RMAReasonsRepository
 * @package JTL\RMA
 */
class RMAReasonsRepository extends AbstractRepository
{
    /**
     * @param int $id
     * @return stdClass|null
     */
    public function get(int $id): ?stdClass
    {
        return $this->getDB()->getSingleObject(
            "SELECT *, DATE_FORMAT(dErstellt, '%d.%m.%Y %H:%i:%s') AS dErstellt_DE"
            . ' FROM ' . $this->getTableName()
            . ' WHERE ' . $this->getKeyName() . ' = :cbid',
            ['cbid' => $id]
        );
    }
    
    /**
     * @return string
     */
    public function getTableName(): string
    {
        return 'rmapos';
    }
    
    /**
     * @return string
     */
    public function getKeyName(): string
    {
        return 'id';
    }
}