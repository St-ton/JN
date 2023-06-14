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
    private int $id = 0;
    
    /**
     * @var int
     */
    private int $wawiID = 0;
    
    /**
     * @var int
     */
    private int $customerID = 0;
    
    /**
     * @var int
     */
    private int $pickupAddressID = 0;
    
    /**
     * @var string
     */
    private string $status = '';
    
    /**
     * @var string
     */
    private string $createDate = '';
    
    /**
     * @var string
     */
    private string $lastModified = '';
    
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
}
