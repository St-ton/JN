<?php declare(strict_types=1);

namespace JTL\RMA;

use JTL\DataObjects\AbstractDataObject;
use JTL\DataObjects\DataTableObjectInterface;

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
     * @var int
     */
    protected int $wawiID = 0;
    
    /**
     * @var int
     */
    protected int $customerID = 0;
    
    /**
     * @var int
     */
    protected int $pickupAddressID = 0;
    
    /**
     * @var string
     */
    protected string $status = '';
    
    /**
     * @var string
     */
    protected string $createDate = '';
    
    /**
     * @var string
     */
    protected string $lastModified = '';
    
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
    public function getWawiID(): int
    {
        return $this->wawiID;
    }

    /**
     * @param int|string $wawiID
     * @return self
     */
    public function setWawiID(int|string $wawiID): self
    {
        $this->wawiID = (int)$wawiID;

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
     * @param int|string $status
     * @return $this
     */
    public function setStatus(int|string $status): self
    {
        $this->status = (string)$status;

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
     * @return string
     */
    public function getLastModified(): string
    {
        return $this->lastModified;
    }

    /**
     * @param string $lastModified
     * @return $this
     */
    public function setLastModified(string $lastModified): self
    {
        $this->lastModified = $lastModified;

        return $this;
    }
}
