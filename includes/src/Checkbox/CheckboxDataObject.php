<?php declare(strict_types=1);

namespace JTL\Checkbox;

use JTL\DataObjects\AbstractGenericDataObject;

/**
 *
 */
class CheckboxDataObject extends AbstractGenericDataObject
{
    /**
     * @var string
     */
    private string $primaryKey = 'kCheckBox';

    /**
     * @var int
     */
    protected int $checkboxID = 0;

    /**
     * @var int
     */
    protected int $linkID = 0;

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
    protected string $customerGroupsSelected = '';

    /**
     * @var string
     */

    protected string $displayAt = '';

    /**
     * @var bool
     */

    protected bool $active = true;
    /**
     * @var bool
     */

    protected bool $isMandatory = false;

    /**
     * @var bool
     */
    protected bool $hasLogging = true;

    /**
     * @var int
     */
    protected int $sort = 0;

    /**
     * @var string
     */
    protected string $created = '';

    /**
     * @var string
     */
    private string $created_DE = '';

    /**
     * @var bool
     */
    protected bool $isInternal = false;

    /**
     * @var array|string[]
     */
    private array $mapping = [
        'checkboxID'             => 'checkboxID',
        'linkID'                 => 'linkID',
        'checkboxFunctionID'     => 'checkboxFunctionID',
        'name'                   => 'name',
        'customerGroupsSelected' => 'customerGroupsSelected',
        'displayAt'              => 'displayAt',
        'active'               => 'active',
        'isMandatory'            => 'isMandatory',
        'hasLogging'             => 'hasLogging',
        'sort'                   => 'sort',
        'created'                => 'created',
        'created_DE'             => 'created_DE',
        'isInternal'             => 'isInternal',
    ];

    private array $columnMapping = [
        'kCheckBox'              => 'checkboxID',
        'kLink'                  => 'linkID',
        'kCheckBoxFunktion'      => 'checkboxFunctionID',
        'cName'                  => 'name',
        'cKundengruppe'          => 'customerGroupsSelected',
        'cAnzeigeOrt'            => 'displayAt',
        'nAktiv'                 => 'active',
        'nPflicht'               => 'isMandatory',
        'nLogging'               => 'hasLogging',
        'nSort'                  => 'sort',
        'dErstellt'              => 'created',
        'dErstellt_DE'           => 'created_DE',
        'nInternal'              => 'isInternal',
    ];

    /**
     * @param string $name
     * @param $value
     * @return void
     */
    public function __set(string $name, $value): void
    {
        if ($name === $this->primaryKey) {
            $this->checkboxID = (int)$value;
        }
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        $map = $this->getMapping();

        if ($name === 'kCheckBox') {
            return $this->checkboxID;
        }

        if (isset($map[$name])) {
            $prop = $map[$name];

            return $this->$prop;
        }

        return null;
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->$name);
    }

    /**
     * @param $name
     * @return void
     */
    public function __unset($name)
    {
        unset($this->$name);
    }

    /**
     * @return string
     */
    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    /**
     * @return array|string[]
     */
    public function getMapping(): array
    {
        return array_merge($this->mapping, $this->columnMapping);
    }

    /**
     * @return array
     */
    public function getReverseMapping(): array
    {
        return array_flip($this->mapping);
    }

    /**
     * @return array
     */
    public function getColumnMapping(): array
    {
        return array_flip($this->columnMapping);
    }

    /**
     * @return int
     */
    public function getCheckboxID(): int
    {
        return $this->checkboxID;
    }

    /**
     * @param int $checkboxID
     * @return CheckboxDataObject
     */
    public function setCheckboxID(int $checkboxID): CheckboxDataObject
    {
        $this->checkboxID = $checkboxID;
        return $this;
    }

    /**
     * @return int
     */
    public function getLinkID(): int
    {
        return $this->linkID;
    }

    /**
     * @param int $linkID
     * @return CheckboxDataObject
     */
    public function setLinkID(int $linkID): CheckboxDataObject
    {
        $this->linkID = $linkID;
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
     * @param int $checkboxFunctionID
     * @return CheckboxDataObject
     */
    public function setCheckboxFunctionID(int $checkboxFunctionID): CheckboxDataObject
    {
        $this->checkboxFunctionID = $checkboxFunctionID;
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
     * @return CheckboxDataObject
     */
    public function setName(string $name): CheckboxDataObject
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getCustomerGroupsSelected(): string
    {
        return $this->customerGroupsSelected;
    }

    /**
     * @param  $customerGroupsSelected
     * @return CheckboxDataObject
     */
    public function setCustomerGroupsSelected($customerGroupsSelected): CheckboxDataObject
    {
        if (\is_array($customerGroupsSelected)) {
            $customerGroupsSelected = ';' . implode(';', $customerGroupsSelected) . ';';
        }
        $this->customerGroupsSelected = $customerGroupsSelected;

        return $this;
    }

    /**
     * @return string
     */
    public function getDisplayAt(): string
    {
        return $this->displayAt;
    }

    /**
     * @param  $displayAt
     * @return CheckboxDataObject
     */
    public function setDisplayAt($displayAt): CheckboxDataObject
    {
        if (\is_array($displayAt)) {
            $displayAt = ';' . implode(';', $displayAt) . ';';
        }
        $this->displayAt = $displayAt;

        return $this;
    }

    /**
     * @return bool
     */
    public function getActive(): bool
    {
        return $this->active;
    }

    /**
     * @param  $active
     * @return CheckboxDataObject
     */
    public function setActive($active): CheckboxDataObject
    {
        $this->active = (bool)$active;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsMandatory(): bool
    {
        return $this->isMandatory;
    }

    /**
     * @param  $isMandatory
     * @return CheckboxDataObject
     */
    public function setIsMandatory($isMandatory): CheckboxDataObject
    {
        $this->isMandatory = (bool)$isMandatory;
        return $this;
    }

    /**
     * @return bool
     */
    public function getHasLogging(): bool
    {
        return $this->hasLogging;
    }

    /**
     * @param  $hasLogging
     * @return CheckboxDataObject
     */
    public function setHasLogging($hasLogging): CheckboxDataObject
    {
        $this->hasLogging = (bool)$hasLogging;
        return $this;
    }

    /**
     * @return int
     */
    public function getSort(): int
    {
        return $this->sort;
    }

    /**
     * @param  $sort
     * @return CheckboxDataObject
     */
    public function setSort($sort): CheckboxDataObject
    {
        $this->sort = (int)$sort;
        return $this;
    }

    /**
     * @return string
     */
    public function getCreated(): string
    {
        return $this->created;
    }

    /**
     * @param string $created
     * @return CheckboxDataObject
     */
    public function setCreated(string $created): CheckboxDataObject
    {
        $this->created = $created;
        return $this;
    }

    /**
     * @return string
     */
    public function getCreatedDE(): string
    {
        return $this->created_DE;
    }

    /**
     * @param string $created_DE
     * @return CheckboxDataObject
     */
    public function setCreatedDE(string $created_DE): CheckboxDataObject
    {
        $this->created_DE = $created_DE;
        return $this;
    }

    /**
     * @return bool
     */
    public function isInternal(): bool
    {
        return $this->isInternal;
    }

    /**
     * @param  $isInternal
     * @return CheckboxDataObject
     */
    public function setIsInternal($isInternal): CheckboxDataObject
    {
        $this->isInternal = (bool)$isInternal;
        return $this;
    }
}
