<?php declare(strict_types=1);

namespace JTL\Checkbox\CheckboxFunction;

use JTL\DataObjects\AbstractDataObject;
use JTL\DataObjects\DataTableObjectInterface;

/**
 * Class CheckboxFunctionDataTableObject
 * @package JTL\Checkbox\CheckboxFunction
 */
class CheckboxFunctionDataTableObject extends AbstractDataObject implements DataTableObjectInterface
{
    /**
     * @var string
     */
    private string $primaryKey = 'kCheckBoxFunktion';

    /**
     * @var ?int
     */
    protected ?int $pluginID = null;

    /**
     * @var int
     */
    protected int $checkboxFunctionID = 0;

    /**
     * @var string
     */
    protected string $name = '';

    /**
     * @var string
     */
    protected string $identifier = '';

    /**
     * @var string[]
     */
    private array $mapping = [
        'checkboxFunctionID' => 'checkboxFunctionID',
        'pluginID'           => 'pluginID',
        'name'               => 'name',
        'identifier'         => 'identifier',
    ];

    /**
     * @var string[]
     */
    private array $columnMapping = [
        'kCheckBoxFunktion' => 'checkboxFunctionID',
        'kPlugin'           => 'pluginID',
        'cName'             => 'name',
        'cID'               => 'identifier',
    ];

    /**
     * @return string
     */
    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    /**
     * @return string[]
     */
    public function getMapping(): array
    {
        return \array_merge($this->mapping, $this->columnMapping);
    }

    /**
     * @return array
     */
    public function getReverseMapping(): array
    {
        return \array_flip($this->mapping);
    }

    /**
     * @return array
     */
    public function getColumnMapping(): array
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
     * @return int|null
     */
    public function getPluginID(): ?int
    {
        return $this->pluginID;
    }

    /**
     * @param int|string|null $pluginID
     * @return CheckboxFunctionDataTableObject
     */
    public function setPluginID(null|int|string $pluginID): CheckboxFunctionDataTableObject
    {
        $this->pluginID = (int)$pluginID;

        return $this;
    }

    /**
     * @return int
     */
    public function getCheckboxFunctionID(): int
    {
        return $this->checkboxFunctionID;
    }

    /**
     * @param int|string|null $checkboxFunctionID
     * @return CheckboxFunctionDataTableObject
     */
    public function setCheckboxFunctionID(null|int|string $checkboxFunctionID): CheckboxFunctionDataTableObject
    {
        $this->checkboxFunctionID = (int)$checkboxFunctionID;

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
     * @return CheckboxFunctionDataTableObject
     */
    public function setName(string $name): CheckboxFunctionDataTableObject
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     * @return CheckboxFunctionDataTableObject
     */
    public function setIdentifier(string $identifier): CheckboxFunctionDataTableObject
    {
        $this->identifier = $identifier;

        return $this;
    }
}
