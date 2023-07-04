<?php declare(strict_types=1);

namespace JTL\RMA;

use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\Preise;
use JTL\DataObjects\AbstractDataObject;
use JTL\DataObjects\DataTableObjectInterface;
use JTL\Exceptions\CircularReferenceException;
use JTL\Exceptions\ServiceNotFoundException;
use JTL\Helpers\Date;

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
    protected ?int $productID = null;

    /**
     * @var int|null
     */
    protected ?int $reasonID = null;
    
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
     * @var float|null
     */
    protected ?float $vat = null;
    
    /**
     * @var string|null
     */
    protected ?string $unit = null;
    
    /**
     * @var float|null
     */
    protected ?float $stockBeforePurchase = null;
    
    /**
     * @var int
     */
    protected int $longestMinDelivery = 0;
    
    /**
     * @var int
     */
    protected int $longestMaxDelivery = 0;
    
    /**
     * @var string|null
     */
    protected ?string $comment = null;
    
    /**
     * @var string|null
     */
    protected ?string $status = null;
    
    /**
     * @var string
     */
    protected string $createDate = '';

    /**
     * @var array|null
     */
    private ?array $history = null;

    /**
     * @var Artikel|null
     */
    private ?Artikel $product = null;

    /**
     * @var object|null
     */
    private ?object $reason = null;
    
    /**
     * @var string[]
     */
    private array $columnMapping = [
        'rmaPosID'            => 'id',
        'rmaID'               => 'rmaID',
        'shippingNotePosID'   => 'shippingNotePosID',
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
     * @return array
     */
    public function getColumnMapping(): array
    {
        return $this->columnMapping;
    }
    
    /**
     * @return mixed
     */
    public function getID(): mixed
    {
        return $this->{$this->getPrimaryKey()};
    }

    /**
     * @param int|string $id
     * @return self
     */
    public function setID(int|string $id): self
    {
        $this->{$this->getPrimaryKey()} = (int)$id;

        return $this;
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
     * @param int|string|null $productID
     * @return $this
     */
    public function setProductID(int|string|null $productID): self
    {
        $this->productID = (int)$productID ?? null;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getReasonID(): ?int
    {
        return $this->reasonID;
    }

    /**
     * @param int|string|null $reasonID
     * @return $this
     */
    public function setReasonID(int|string|null $reasonID): self
    {
        $this->reasonID = (int)$reasonID ?? null;

        return $this;
    }

    /**
     * @param int|string|null $reasonID
     * @param RmaService|null $rmaService
     * @return $this
     */
    public function setReason(int|string|null $reasonID, ?RmaService $rmaService = null): self
    {
        $rmaService   = $rmaService ?? new RmaService();
        $this->reason = $rmaService->getReason($reasonID ?? $this->reasonID ?? 0);

        return $this;
    }

    /**
     * @return object|null
     */
    public function getReason(): ?object
    {
        return $this->reason;
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
     * @return float
     */
    public function getPriceNet(): float
    {
        return $this->unitPriceNet * $this->quantity;
    }

    /**
     * @return string
     */
    public function getPriceLocalized(): string
    {
        return Preise::getLocalizedPriceString($this->unitPriceNet * $this->quantity);
    }

    /**
     * @return string
     */
    public function getUnitPriceLocalized(): string
    {
        return Preise::getLocalizedPriceString($this->unitPriceNet);
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
     * @return float|null
     */
    public function getVat(): ?float
    {
        return $this->vat;
    }

    /**
     * @param float|string|null $vat
     * @return $this
     */
    public function setVat(float|string|null $vat): self
    {
        $this->vat = (float)$vat ?? null;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUnit(): ?string
    {
        return $this->unit;
    }

    /**
     * @param string|null $unit
     * @return $this
     */
    public function setUnit(?string $unit): self
    {
        $this->unit = $unit ?? null;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getStockBeforePurchase(): ?float
    {
        return $this->stockBeforePurchase ?? null;
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
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->comment ?? null;
    }

    /**
     * @param string|null $comment
     * @return $this
     */
    public function setComment(?string $comment): self
    {
        $this->comment = $comment ?? null;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string|null $status
     * @return $this
     */
    public function setStatus(?string $status): self
    {
        $this->status = $status ?? null;

        return $this;
    }

    /**
     * @param bool $localize
     * @return string
     */
    public function getCreateDate(bool $localize = true): string
    {
        return ($localize) ? Date::localize($this->createDate) : $this->createDate;
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

    /**
     * @return array
     */
    public function getHistory(): array
    {
        return $this->history;
    }

    /**
     * @param array $history
     * @return $this
     */
    public function setHistory(array $history): self
    {
        $this->history = $history;

        return $this;
    }

    /**
     * @param int|null $productID
     * @param Artikel|null $product
     * @return $this
     * @throws CircularReferenceException
     * @throws ServiceNotFoundException
     */
    public function setProduct(?int $productID = null, ?Artikel $product = null): self
    {
        if ($product !== null) {
            $this->product = $product;
        } elseif ($productID !== null) {
            $this->product = (new Artikel())->fuelleArtikel($productID);
        }

        return $this;
    }

    /**
     * @return Artikel|null
     */
    public function getProduct(): ?Artikel
    {
        return $this->product;
    }
}
