<?php declare(strict_types=1);

namespace JTL\RMA;

use JTL\Abstracts\AbstractRepository;
use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\Preise;
use JTL\RMA\PickupAddress\PickupAddressRepository;
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
        $results = [];
        $data    = parent::getList($filters);
        foreach ($data as $obj) {
            $obj->id         = (int)$obj->id;
            $obj->status     = \langRMAStatus((int)$obj->status);
            $obj->createDate = date('d.m.Y H:i', \strtotime($obj->createDate));
            $dataTableObject = new RMADataTableObject();
            $rma             = $dataTableObject->hydrateWithObject($obj);
            $rmaPos          = new RMAPosRepository();
            $rma->setPositions(
                $rmaPos->getList(['rmaID' => $rma->getID()])
            );
            $rmaPickupAddress = new PickupAddressRepository();
            $rma->setPickupAddress(
                $rmaPickupAddress->get($rma->getID()) ?? new \stdClass()
            );

            $results[] = $rma;
        }
        return $results;
    }

    /**
     * @param array $values
     * @return bool
     */
    public function delete(array $values): bool
    {
        $result     = true;
        $customerID = Frontend::getCustomer()->getID();

        foreach ($values as $id) {
            if ($this->getDB()->deleteRow(
                $this->getTableName(),
                [$this->getKeyName(), 'customerID', 'wawiID'],
                [(int)$id, $customerID, null]
            ) === self::DELETE_FAILED) {
                $result = false;
            }
        }
        return $result;
    }
}
