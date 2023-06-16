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
    protected int $id = 0;
    
    /**
     * @var int
     */
    protected int $rmaID = 0;
    
    /**
     * @var int
     */
    protected int $shippingNotePosID = 0;
    
    /**
     * @var int
     */
    protected int $orderPosID = 0;
    
    /**
     * @var int|null
     */
    protected ?int $productID = 0;
    
    /**
     * @var string
     */
    protected string $name = '';
    
    /**
     * @var float
     */
    protected float $unitPriceNet = 0.00;
    
    /**
     * @var float
     */
    protected float $quantity = 0.00;
    
    /**
     * @var float
     */
    protected float $vat = 0.00;
    
    /**
     * @var string
     */
    protected string $unit = '';
    
    /**
     * @var float
     */
    protected float $stockBeforePurchase = 0.00;
    
    /**
     * @var int
     */
    protected int $longestMinDelivery = 0;
    
    /**
     * @var int
     */
    protected int $longestMaxDelivery = 0;
    
    /**
     * @var string
     */
    protected string $comment = '';
    
    /**
     * @var string
     */
    protected string $status = '';
    
    /**
     * @var string
     */
    protected string $createDate = '';
    
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

    /**
     * @return int
     */
    public function getRmaID(): int
    {
        return $this->rmaID;
    }

    /**
     * @param int|string $rmaID
     * @return self
     */
    public function setRmaID(int|string $rmaID): self
    {
        $this->rmaID = (int)$rmaID;

        return $this;
    }

    /**
     * @return int
     */
    public function getShippingNotePosID(): int
    {
        return $this->shippingNotePosID;
    }

    /**
     * @param int|string $shippingNotePosID
     * @return $this
     */
    public function setShippingNotePosID(int|string $shippingNotePosID): self
    {
        $this->shippingNotePosID = (int)$shippingNotePosID;

        return $this;
    }

    /**
     * @return int
     */
    public function getOrderPosID(): int
    {
        return $this->orderPosID;
    }

    /**
     * @param int|string $orderPosID
     * @return $this
     */
    public function setOrderPosID(int|string $orderPosID): self
    {
        $this->orderPosID = (int)$orderPosID;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getProductID(): ?int
    {
        return $this->productID;
    }

    /**
     * @param int|string $productID
     * @return $this
     */
    public function setProductID(int|string $productID): self
    {
        $this->productID = (int)$productID;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return float
     */
    public function getUnitPriceNet(): float
    {
        return $this->unitPriceNet;
    }

    /**
     * @param float|string $unitPriceNet
     * @return $this
     */
    public function setUnitPriceNet(float|string $unitPriceNet): self
    {
        $this->unitPriceNet = (float)$unitPriceNet;

        return $this;
    }

    /**
     * @return float
     */
    public function getQuantity(): float
    {
        return $this->quantity;
    }

    /**
     * @param float|string $quantity
     * @return $this
     */
    public function setQuantity(float|string $quantity): self
    {
        $this->quantity = (float)$quantity;

        return $this;
    }

    /**
     * @return float
     */
    public function getVat(): float
    {
        return $this->vat;
    }

    /**
     * @param float|string $vat
     * @return $this
     */
    public function setVat(float|string $vat): self
    {
        $this->vat = (float)$vat;

        return $this;
    }

    /**
     * @return string
     */
    public function getUnit(): string
    {
        return $this->unit;
    }

    /**
     * @param string $unit
     * @return $this
     */
    public function setUnit(string $unit): self
    {
        $this->unit = $unit;

        return $this;
    }

    /**
     * @return float
     */
    public function getStockBeforePurchase(): float
    {
        return $this->stockBeforePurchase;
    }

    /**
     * @param float|string $stockBeforePurchase
     * @return $this
     */
    public function setStockBeforePurchase(float|string $stockBeforePurchase): self
    {
        $this->stockBeforePurchase = (float)$stockBeforePurchase;

        return $this;
    }

    /**
     * @return int
     */
    public function getLongestMinDelivery(): int
    {
        return $this->longestMinDelivery;
    }

    /**
     * @param int|string $longestMinDelivery
     * @return $this
     */
    public function setLongestMinDelivery(int|string $longestMinDelivery): self
    {
        $this->longestMinDelivery = (int)$longestMinDelivery;

        return $this;
    }

    /**
     * @return int
     */
    public function getLongestMaxDelivery(): int
    {
        return $this->longestMaxDelivery;
    }

    /**
     * @param int|string $longestMaxDelivery
     * @return $this
     */
    public function setLongestMaxDelivery(int|string $longestMaxDelivery): self
    {
        $this->longestMaxDelivery = (int)$longestMaxDelivery;

        return $this;
    }

    /**
     * @return string
     */
    public function getComment(): string
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     * @return $this
     */
    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getCreateDate(): string
    {
        return $this->createDate;
    }

    /**
     * @param string $createDate
     * @return $this
     */
    public function setCreateDate(string $createDate): self
    {
        $this->createDate = $createDate;

        return $this;
    }
}
