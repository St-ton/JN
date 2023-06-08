<?php declare(strict_types=1);

namespace JTL\RMA;

use JTL\Abstracts\AbstractRepository;
use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\Preise;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Shopsetting;

/**
 * Class RMARepository
 * @package JTL\RMA
 */
class RMARepository extends AbstractRepository
{
    
    /**
     * @return string
     */
    public function getTableName(): string
    {
        return 'rma';
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
            $obj->status     = \langRMAStatus((int)$obj->status);
            $obj->createDate = date('d.m.Y H:i', \strtotime($obj->createDate));
            $dataTableObject = new RMADataTableObject();
            $result[]        = $dataTableObject->hydrateWithObject($obj);
        }
        return $result;
    }
}
