<?php declare(strict_types=1);

namespace JTL\Backend\Settings;

use MagicCompatibilityTrait;
use stdClass;

/**
 * Class Item
 * @package Backend\Settings
 */
class Item
{
    use MagicCompatibilityTrait;

    /**
     * @var int
     */
    private $id = 0;

    /**
     * @var bool
     */
    private $configurable = false;

    /**
     * @var string|null
     */
    private $inputType;

    /**
     * @var mixed
     */
    private $setValue;

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var string
     */
    private $valueName = '';

    /**
     * @var string
     */
    private $description = '';

    /**
     * @var int
     */
    private $configSectionID = 0;

    /**
     * @var int
     */
    private $showDefault = 0;

    /**
     * @var int
     */
    private $sort = 0;

    /**
     * @var string|null
     */
    private $moduleID;

    /**
     * @var int
     */
    private $moduleNumber = 0;

    /**
     * @var int
     */
    private $pluginID = 0;

    /**
     * @var array|null
     */
    private $values;

    public $defaultValue;
    public $currentValue;

    /**
     * @var string[]
     */
    protected $mapping = [
        'cConf'                 => 'ConfigurableCompat',
        'cInputTyp'             => 'InputType',
        'gesetzterWert'         => 'SetValue',
        'cWertName'             => 'ValueName',
        'cName'                 => 'Name',
        'kEinstellungenSektion' => 'ConfigSectionID',
        'nStandardAnzeigen'     => 'ShowDefault',
        'nSort'                 => 'Sort',
        'nModul'                => 'ModuleNumber',
        'cModulId'              => 'ModuleID',
        'kEinstellungenConf'    => 'ID',
        'cBeschreibung'         => 'Description',
        'ConfWerte'             => 'Values',
    ];

    /**
     * @param stdClass $dbItem
     */
    public function parseFromDB(stdClass $dbItem): void
    {
        $this->setID((int)($dbItem->kEinstellungenConf ?? 0));
        $this->setConfigSectionID((int)($dbItem->kEinstellungenSektion ?? 0));
        $this->setName($dbItem->cName ?? '');
        $this->setValueName($dbItem->cWertName ?? '');
        $this->setDescription($dbItem->cBeschreibung ?? '');
        $this->setInputType($dbItem->cInputTyp ?? null);
        $this->setModuleID($dbItem->cModulId ?? null);
        $this->setSort((int)($dbItem->nSort ?? 0));
        $this->setShowDefault((int)($dbItem->nStandardAnzeigen ?? 0));
        $this->setModuleNumber((int)($dbItem->nModul ?? 0));
        $this->setConfigurable(($dbItem->cConf ?? 'N') === 'Y');
        $this->setCurrentValue($dbItem->currentValue ?? null);
        $this->setDefaultValue($dbItem->defaultValue ?? null);
        $this->setPluginID((int)($dbItem->kPlugin ?? 0));
    }

    /**
     * @return string
     */
    public function getConfigurableCompat(): string
    {
        return $this->configurable ? 'Y' : 'N';
    }

    /**
     * @param string $value
     */
    public function setConfigurableCompat(string $value): void
    {
        $this->configurable = $value === 'Y';
    }

    /**
     * @return int
     */
    public function getID(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setID(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return bool
     */
    public function isConfigurable(): bool
    {
        return $this->configurable;
    }

    /**
     * @param bool $configurable
     */
    public function setConfigurable(bool $configurable): void
    {
        $this->configurable = $configurable;
    }

    /**
     * @return string|null
     */
    public function getInputType(): ?string
    {
        return $this->inputType;
    }

    /**
     * @param string|null $inputType
     */
    public function setInputType(?string $inputType): void
    {
        $this->inputType = $inputType;
    }

    /**
     * @return mixed
     */
    public function getSetValue()
    {
        return $this->setValue;
    }

    /**
     * @param mixed $setValue
     */
    public function setSetValue($setValue): void
    {
        $this->setValue = $setValue;
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
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getValueName(): string
    {
        return $this->valueName;
    }

    /**
     * @param string $valueName
     */
    public function setValueName(string $valueName): void
    {
        $this->valueName = $valueName;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return int
     */
    public function getConfigSectionID(): int
    {
        return $this->configSectionID;
    }

    /**
     * @param int $configSectionID
     */
    public function setConfigSectionID(int $configSectionID): void
    {
        $this->configSectionID = $configSectionID;
    }

    /**
     * @return int
     */
    public function getShowDefault(): int
    {
        return $this->showDefault;
    }

    /**
     * @param int $showDefault
     */
    public function setShowDefault(int $showDefault): void
    {
        $this->showDefault = $showDefault;
    }

    /**
     * @return int
     */
    public function getSort(): int
    {
        return $this->sort;
    }

    /**
     * @param int $sort
     */
    public function setSort(int $sort): void
    {
        $this->sort = $sort;
    }

    /**
     * @return string|null
     */
    public function getModuleID(): ?string
    {
        return $this->moduleID;
    }

    /**
     * @param string|null $moduleID
     */
    public function setModuleID(?string $moduleID): void
    {
        $this->moduleID = $moduleID;
    }

    /**
     * @return int
     */
    public function getModuleNumber(): int
    {
        return $this->moduleNumber;
    }

    /**
     * @param int $moduleNumber
     */
    public function setModuleNumber(int $moduleNumber): void
    {
        $this->moduleNumber = $moduleNumber;
    }

    /**
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @param mixed $defaultValue
     */
    public function setDefaultValue($defaultValue): void
    {
        $this->defaultValue = $defaultValue;
    }

    /**
     * @return mixed
     */
    public function getCurrentValue()
    {
        return $this->currentValue;
    }

    /**
     * @param mixed $currentValue
     */
    public function setCurrentValue($currentValue): void
    {
        $this->currentValue = $currentValue;
    }

    /**
     * @return array|null
     */
    public function getValues(): ?array
    {
        return $this->values;
    }

    /**
     * @param array|null $values
     */
    public function setValues(?array $values): void
    {
        $this->values = $values;
    }

    /**
     * @return int
     */
    public function getPluginID(): int
    {
        return $this->pluginID;
    }

    /**
     * @param int $pluginID
     */
    public function setPluginID(int $pluginID): void
    {
        $this->pluginID = $pluginID;
    }

    public function x__debugInfo(): ?array
    {
        return [
            'id'        => $this->getID(),
            'name'      => $this->getName(),
            'sort'      => $this->getSort(),
            'sectionID' => $this->getConfigSectionID(),
            'valueName' => $this->getValueName()
        ];
    }
}
