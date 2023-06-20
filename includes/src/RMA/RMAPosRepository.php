<?php declare(strict_types=1);

namespace JTL\RMA;

use Illuminate\Support\Collection;
use JTL\Abstracts\AbstractRepository;
use JTL\Catalog\Product\Preise;
use JTL\Shop;
use stdClass;

/**
 * Class RMAPosRepository
 * @package JTL\RMA
 */
class RMAPosRepository extends AbstractRepository
{
    /**
     * @param array $filters
     * @return array
     * @since 5.3.0
     */
    public function getList(array $filters): array
    {
        $results = parent::getList($filters);
        foreach ($results as &$obj) {
            $obj->unitPriceNet = Preise::getLocalizedPriceString($obj->unitPriceNet);
            $obj->history      = (new RMAHistoryRepository())->getList(['rmaPosID' => $obj->id]);
        }
        return $results;
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
