<?php declare(strict_types=1);

namespace JTL\RMA;

use JTL\DataObjects\AbstractDataObject;
use JTL\DataObjects\DataTableObjectInterface;

/**
 * Class RMAPosDataTableObject
 * @package JTL\RMA
 * @description RMA positions created in shop user account or synced from WAWI
 */
class RMAPosDataTableObject extends AbstractDataObject implements DataTableObjectInterface
{
    /**
     * @var string
     */
    private string $primaryKey = 'id';
    
    /**
     * @var int
     */
    private int $id = 0;
    
    /**
     * @var int
     */
    private int $rmaID = 0;
    
    /**
     * @var int
     */
    private int $shippingNotePosID = 0;
    
    /**
     * @var int
     */
    private int $orderPosID = 0;
    
    /**
     * @var int
     */
    private int $productID = 0;
    
    /**
     * @var string
     */
    private string $name = '';
    
    /**
     * @var float
     */
    private float $unitPriceNet = 0.00;
    
    /**
     * @var float
     */
    private float $quantity = 0.00;
    
    /**
     * @var float
     */
    private float $vat = 0.00;
    
    /**
     * @var string
     */
    private string $unit = '';
    
    /**
     * @var float
     */
    private float $stockBeforePurchase = 0.00;
    
    /**
     * @var int
     */
    private int $longestMinDelivery = 0;
    
    /**
     * @var int
     */
    private int $longestMaxDelivery = 0;
    
    /**
     * @var string
     */
    private string $comment = '';
    
    /**
     * @var string
     */
    private string $status = '';
    
    /**
     * @var string
     */
    private string $createDate = '';
    
    /**
     * @var string[]
     */
    private array $columnMapping = [
        'id'                  => 'id',
        'rmaID'               => 'rmaID',
        'shippingNotePosID'   => 'shippingNotePosID',
        'orderPosID'          => 'orderPosID',
        'productID'           => 'productID',
        'name'                => 'name',
        'unitPriceNet'        => 'unitPriceNet',
        'quantity'            => 'quantity',
        'vat'                 => 'vat',
        'unit'                => 'unit',
        'stockBeforePurchase' => 'stockBeforePurchase',
        'longestMinDelivery'  => 'longestMinDelivery',
        'longestMaxDelivery'  => 'longestMaxDelivery',
        'comment'             => 'comment',
        'status'              => 'status',
        'createDate'          => 'createDate'
    ];
    
    /**
     * @return string
     */
    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }
    
    /**
     * @return array
     */
    public function getMapping(): array
    {
        return $this->columnMapping;
    }
    
    /**
     * @return array
     */
    public function getReverseMapping(): array
    {
        return \array_flip($this->columnMapping);
    }
    
    /**
     * @return mixed
     */
    public function getID(): mixed
    {
        return $this->{$this->getPrimaryKey()};
    }
    
    /**
     * @return array
     */
    public function getColumnMapping(): array
    {
        return $this->columnMapping;
    }
}
