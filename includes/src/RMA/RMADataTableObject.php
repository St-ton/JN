<?php declare(strict_types=1);

namespace JTL\RMA;

use JTL\Catalog\Product\Preise;
use JTL\DataObjects\AbstractDataObject;
use JTL\DataObjects\DataTableObjectInterface;
use JTL\Helpers\Date;
use JTL\RMA\PickupAddress\PickupAddressDataTableObject;
use JTL\RMA\PickupAddress\PickupAddressRepository;

/**
 * Class RMADataTableObject
 * @package JTL\RMA
 * @description RMA request created in shop or imported from WAWI
 */
class RMADataTableObject extends AbstractDataObject implements DataTableObjectInterface
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
     * @var int|null
     */
    protected ?int $wawiID = null;
    
    /**
     * @var int
     */
    protected int $customerID = 0;
    
    /**
     * @var int
     */
    protected int $pickupAddressID = 0;
    
    /**
     * @var string|null
     */
    protected ?string $status = null;
    
    /**
     * @var string
     */
    protected string $createDate = '';
    
    /**
     * @var string|null
     */
    protected ?string $lastModified = null;

    /**
     * @var RMAPosDataTableObject[]
     */
    private array $positions = [];

    /**
     * @var PickupAddressDataTableObject|null
     */
    private ?PickupAddressDataTableObject $pickupAddress = null;
    
    /**
     * @var string[]
     */
    private array $columnMapping = [
        'rmaID'           => 'id',
        'wawiID'          => 'wawiID',
        'customerID'      => 'customerID',
        'pickupAddressID' => 'pickupAddressID',
        'rmaStatus'       => 'status',
        'rmaCreateDate'   => 'createDate',
        'rmaLastModified' => 'lastModified'
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
    public function getWawiID(): int
    {
        return $this->wawiID;
    }

    /**
     * @param int|string|null $wawiID
     * @return self
     */
    public function setWawiID(int|string|null $wawiID): self
    {
        $this->wawiID = (int)$wawiID ?? null;

        return $this;
    }

    /**
     * @return int
     */
    public function getCustomerID(): int
    {
        return $this->customerID;
    }

    /**
     * @param int|string $customerID
     * @return $this
     */
    public function setCustomerID(int|string $customerID): self
    {
        $this->customerID = (int)$customerID;

        return $this;
    }

    /**
     * @return int
     */
    public function getPickupAddressID(): int
    {
        return $this->pickupAddressID;
    }

    /**
     * @param int|string $pickupAddressID
     * @return $this
     */
    public function setPickupAddressID(int|string $pickupAddressID): self
    {
        $this->pickupAddressID = (int)$pickupAddressID;
        if ($this->pickupAddressID === 0) {
            $this->pickupAddressID = (new PickupAddressRepository())->generateID();
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStatus(): string|null
    {
        return $this->status;
    }

    /**
     * @param int|string|null $status
     * @return $this
     */
    public function setStatus(int|string|null $status): self
    {
        $this->status = ($status !== null) ? \langRMAStatus((int)$status) : null;

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
     * @return string|null
     */
    public function getLastModified(): string|null
    {
        return $this->lastModified;
    }

    /**
     * @param string|null $lastModified
     * @return $this
     */
    public function setLastModified(string|null $lastModified): self
    {
        $this->lastModified = $lastModified ?? null;

        return $this;
    }

    /**
     * @return AbstractDataObject[]
     */
    public function getPositions(): array
    {
        return $this->positions;
    }

    /**
     * @param RMAPosDataTableObject[] $positions
     * @return $this
     */
    public function setPositions(array $positions): self
    {
        $this->positions = $positions;

        return $this;
    }

    /**
     * @param RMAPosDataTableObject $position
     * @return $this
     */
    public function addPosition(RMAPosDataTableObject $position): self
    {
        $this->positions[/*$position->getID()*/] = $position;

        return $this;
    }

    /**
     * @return PickupAddressDataTableObject|null
     */
    public function getPickupAddress(): ?PickupAddressDataTableObject
    {
        return $this->pickupAddress;
    }

    /**
     * @param PickupAddressDataTableObject $pickupAddress
     * @return $this
     */
    public function setPickupAddress(PickupAddressDataTableObject $pickupAddress): self
    {
        $this->pickupAddress = $pickupAddress;

        return $this;
    }

    /**
     * @param int $shippingNotePosID
     * @return RMAPosDataTableObject
     */
    public function getPos(int $shippingNotePosID): RMAPosDataTableObject
    {
        if ($shippingNotePosID === 0 || $this->positions === null) {
            return new RMAPosDataTableObject();
        }
        foreach ($this->positions as $position) {
            if ($position->getShippingNotePosID() === $shippingNotePosID) {
                return $position;
            }
        }
        return new RMAPosDataTableObject();
    }

    /**
     * @return float
     */
    public function getPriceNet(): float
    {
        $total = 0.00;
        foreach ($this->getPositions() as $pos) {
            $total += $pos->getPriceNet();
        }
        return $total;
    }

    /**
     * @return string
     */
    public function getPriceLocalized(): string
    {
        $total = 0.00;
        foreach ($this->getPositions() as $pos) {
            $total += $pos->getPriceNet();
        }
        return Preise::getLocalizedPriceString($total);
    }
}
