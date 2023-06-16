<?php declare(strict_types=1);

namespace JTL\RMA;

use JTL\DataObjects\AbstractDataObject;
use JTL\DataObjects\DataTableObjectInterface;

/**
 * Class RMAHistoryDataTableObject
 * @package JTL\RMA
 * @description Store changes to RMA positions
 */
class RMAHistoryDataTableObject extends AbstractDataObject implements DataTableObjectInterface
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
    protected int $rmaPosID = 0;
    
    /**
     * @var string
     */
    protected string $keyName = '';
    
    /**
     * @var string|null
     */
    protected ?string $oldValue;
    
    /**
     * @var string
     */
    protected string $newValue = '';
    
    /**
     * @var string
     */
    protected string $lastModified = '';
    
    
    /**
     * @var string[]
     */
    private array $columnMapping = [
        'id'           => 'id',
        'rmaPosID'     => 'rmaPosID',
        'keyName'      => 'keyName',
        'oldValue'     => 'oldValue',
        'newValue'     => 'newValue',
        'lastModified' => 'lastModified'
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
