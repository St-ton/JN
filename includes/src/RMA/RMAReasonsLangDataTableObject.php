<?php declare(strict_types=1);

namespace JTL\RMA;

use JTL\DataObjects\AbstractDataObject;
use JTL\DataObjects\DataTableObjectInterface;

/**
 * Class RMAReasonsLangDataTableObject
 * @package JTL\RMA
 * @description Localized RMA reasons synced from WAWI
 */
class RMAReasonsLangDataTableObject extends AbstractDataObject implements DataTableObjectInterface
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
    protected int $reasonID = 0;
    
    /**
     * @var int
     */
    protected int $langID = 0;
    
    /**
     * @var string
     */
    protected string $title = '';
    
    /**
     * @var string[]
     */
    private array $columnMapping = [
        'id'       => 'id',
        'reasonID' => 'reasonID',
        'langID'   => 'langID',
        'title'    => 'title'
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
     * @param int|string $id
     * @return self
     */
    public function setID(int|string $id): self
    {
        $this->id = (int)$id;

        return $this;
    }
    
    /**
     * @return array
     */
    public function getColumnMapping(): array
    {
        return $this->columnMapping;
    }
}