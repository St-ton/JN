<?php declare(strict_types=1);

namespace JTL\RMA;

use JTL\DataObjects\AbstractDataObject;
use JTL\DataObjects\DataTableObjectInterface;
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
     * @var array|null
     */
    private ?array $positions = null;

    /**
     * @var object|null
     */
    private ?object $pickupAddress = null;
    
    /**
     * @var string[]
     */
    private array $columnMapping = [
        'id'                => 'id',
        'wawiID'            => 'wawiID',
        'customerID'        => 'customerID',
        'pickupAddressID'   => 'pickupAddressID',
        'status'            => 'status',
        'createDate'        => 'createDate',
        'lastModified'      => 'lastModified'
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
        $this->id = (int)$id;

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
            $pickupAddressRepository = new PickupAddressRepository();
            $this->pickupAddressID   = $pickupAddressRepository->generateID();
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
        $this->status = (string)$status ?? null;

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
     * @return array
     */
    public function getPositions(): array
    {
        return $this->positions;
    }

    /**
     * @param array $positions
     * @return $this
     */
    public function setPositions(array $positions): self
    {
        $this->positions = $positions;

        return $this;
    }

    /**
     * @return object
     */
    public function getPickupAddress(): object
    {
        return $this->pickupAddress;
    }

    /**
     * @param object $pickupAddress
     * @return $this
     */
    public function setPickupAddress(object $pickupAddress): self
    {
        $this->pickupAddress = $pickupAddress;

        return $this;
    }
}
