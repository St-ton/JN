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
    protected string $title = '';
    
    /**
     * @var string|null
     */
    protected ?string $oldValue = null;
    
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
        'title'        => 'title',
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
    public function getRmaPosID(): int
    {
        return $this->rmaPosID;
    }

    /**
     * @param int|string $rmaPosID
     * @return $this
     */
    public function setRmaPosID(int|string $rmaPosID): self
    {
        $this->rmaPosID = (int)$rmaPosID;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getOldValue(): string|null
    {
        return $this->oldValue;
    }

    /**
     * @param string|null $oldValue
     * @return $this
     */
    public function setOldValue(string|null $oldValue): self
    {
        $this->oldValue = $oldValue ?? null;

        return $this;
    }

    /**
     * @return string
     */
    public function getNewValue(): string
    {
        return $this->newValue;
    }

    /**
     * @param string $newValue
     * @return $this
     */
    public function setNewValue(string $newValue): self
    {
        $this->newValue = $newValue;

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
