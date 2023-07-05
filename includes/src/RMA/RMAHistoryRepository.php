<?php declare(strict_types=1);

namespace JTL\RMA;

use Illuminate\Support\Collection;
use JTL\Abstracts\AbstractRepository;
use JTL\Catalog\Product\Preise;
use JTL\Shop;
use stdClass;

/**
 * Class RMAHistoryRepository
 * @package JTL\RMA
 */
class RMAHistoryRepository extends AbstractRepository
{
    /**
     * @return string
     */
    public function getTableName(): string
    {
        return 'rma_history';
    }
    
    /**
     * @return string
     */
    public function getKeyName(): string
    {
        return 'id';
    }
}
