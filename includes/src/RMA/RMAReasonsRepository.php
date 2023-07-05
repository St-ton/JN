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
     * @return string
     */
    public function getTableName(): string
    {
        return 'rma_reasons';
    }
    
    /**
     * @return string
     */
    public function getKeyName(): string
    {
        return 'id';
    }
}
