<?php declare(strict_types=1);

namespace JTL\RMA;

use Illuminate\Support\Collection;
use JTL\Abstracts\AbstractRepository;
use JTL\Catalog\Product\Preise;
use JTL\Shop;
use stdClass;

/**
 * Class RMAReasonsLangRepository
 * @package JTL\RMA
 */
class RMAReasonsLangRepository extends AbstractRepository
{
    /**
     * @return string
     */
    public function getTableName(): string
    {
        return 'rma_reasons_lang';
    }
    
    /**
     * @return string
     */
    public function getKeyName(): string
    {
        return 'id';
    }

    /**
     * @param array $filters
     * @return array
     * @since 5.3.0
     */
    public function getList(array $filters): array
    {
        $result = [];
        $data   = parent::getList($filters);
        foreach ($data as $obj) {
            $reason           = new \stdClass();
            $reason->reasonID = (int)$obj->reasonID;
            $reason->title    = $obj->title;
            $result[]         = $reason;
        }
        return $result;
    }
}
