<?php declare(strict_types=1);

namespace JTL\RMA\Repositories;

use JTL\Abstracts\AbstractRepositoryTim;

/**
 * Class RMAPositionRepository
 * @package JTL\RMA
 */
class RMAPositionRepository extends AbstractRepositoryTim
{

    /**
     * @return array
     */
    public function getColumnMapping(): array
    {
        return [
            'rmaPosID'            => 'id',
            'rmaID'               => 'rmaID',
            'shippingNotePosID'   => 'shippingNotePosID',
            'orderID'             => 'orderID',
            'orderPosID'          => 'orderPosID',
            'productID'           => 'productID',
            'reasonID'            => 'reasonID',
            'name'                => 'name',
            'unitPriceNet'        => 'unitPriceNet',
            'quantity'            => 'quantity',
            'vat'                 => 'vat',
            'unit'                => 'unit',
            'stockBeforePurchase' => 'stockBeforePurchase',
            'longestMinDelivery'  => 'longestMinDelivery',
            'longestMaxDelivery'  => 'longestMaxDelivery',
            'rmaPosComment'       => 'comment',
            'rmaPosStatus'        => 'status',
            'rmaPosCreateDate'    => 'createDate'
        ];
    }

    /**
     * @param array $data
     * @return array
     */
    public function getDefaultValues(array $data = []): array
    {
        $default = [
            'id'                  => 0,
            'rmaID'               => 0,
            'shippingNotePosID'   => 0,
            'orderID'             => 0,
            'orderPosID'          => 0,
            'productID'           => null,
            'reasonID'            => null,
            'name'                => '',
            'unitPriceNet'        => 0.00,
            'quantity'            => 0.00,
            'vat'                 => 0.00,
            'unit'                => null,
            'stockBeforePurchase' => null,
            'longestMinDelivery'  => 0,
            'longestMaxDelivery'  => 0,
            'comment'             => null,
            'status'              => null,
            'createDate'          => \date('Y-m-d H:i:s'),
            'history'             => null,
            'product'             => null,
            'reason'              => null,
            'property'            => null,
            'productNR'           => null,
            'orderStatus'         => null,
            'seo'                 => null,
            'orderNo'             => null,
            'customerID'          => 0,
            'shippingAddressID'   => 0,
            'shippingNoteID'      => 0
        ];
        return $this->arrayCombine($default, $data);
    }

    /**
     * @param array $rmaIDs
     * @return array
     * @since 5.3.0
     */
    public function getPositionsFor(array $rmaIDs): array
    {
        return $this->getDB()->getObjects(
            'SELECT * FROM ' . $this->getTableName() . ' WHERE ' . $this->getTableName() . 'id IN (:rmaIDs)',
            ['rmaIDs' => $rmaIDs]
        );
    }
    
    /**
     * @return string
     */
    public function getTableName(): string
    {
        return 'rma_pos';
    }
    
    /**
     * @return string
     */
    public function getKeyName(): string
    {
        return 'id';
    }
}
