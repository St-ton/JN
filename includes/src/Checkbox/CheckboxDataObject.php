<?php declare(strict_types=1);

namespace JTL\Checkbox;

use JTL\DataObjects\AbstractDataObject;

/**
 * Class CheckboxDataObject
 * @package JTL\Checkbox
 */
class CheckboxDataObject extends AbstractDataObject
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
        'active'                 => 'active',
        'isMandatory'            => 'isMandatory',
        'hasLogging'             => 'hasLogging',
        'sort'                   => 'sort',
        'created'                => 'created',
        'created_DE'             => 'created_DE',
        'isInternal'             => 'isInternal',
    ];

    /**
     * @var array|string[]
     */
    private array $columnMapping = [
        'kCheckBox'         => 'checkboxID',
        'kLink'             => 'linkID',
        'kCheckBoxFunktion' => 'checkboxFunctionID',
        'cName'             => 'name',
        'cKundengruppe'     => 'customerGroupsSelected',
        'cAnzeigeOrt'       => 'displayAt',
        'nAktiv'            => 'active',
        'nPflicht'          => 'isMandatory',
        'nLogging'          => 'hasLogging',
        'nSort'             => 'sort',
        'dErstellt'         => 'created',
        'dErstellt_DE'      => 'created_DE',
        'nInternal'         => 'isInternal',
    ];

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
     * @return int
     */
    public function getCheckboxID(): int
    {
        return $this->checkboxID;
    }

    /**
     * @param int|string $checkboxID
     * @return CheckboxDataObject
     */
    public function setCheckboxID(int|string $checkboxID): CheckboxDataObject
    {
        $this->checkboxID = (int)$checkboxID;

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
     * @param int|string $linkID
     * @return CheckboxDataObject
     */
    public function setLinkID(int|string  $linkID): CheckboxDataObject
    {
        $this->linkID = (int)$linkID;

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
     * @param int|string  $checkboxFunctionID
     * @return CheckboxDataObject
     */
    public function setCheckboxFunctionID(int|string  $checkboxFunctionID): CheckboxDataObject
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
     * @param array|string $customerGroupsSelected
     * @return CheckboxDataObject
     */
    public function setCustomerGroupsSelected(array|string $customerGroupsSelected): CheckboxDataObject
    {
        if (\is_array($customerGroupsSelected)) {
            $customerGroupsSelected = ';' . \implode(';', $customerGroupsSelected) . ';';
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
     * @param array|string $displayAt
     * @return CheckboxDataObject
     */
    public function setDisplayAt(array|string $displayAt): CheckboxDataObject
    {
        if (\is_array($displayAt)) {
            $displayAt = ';' . \implode(';', $displayAt) . ';';
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
     * @param bool|int|string $active
     * @return CheckboxDataObject
     */
    public function setActive(bool|int|string $active): CheckboxDataObject
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
     * @param  bool|int|string $isMandatory
     * @return CheckboxDataObject
     */
    public function setIsMandatory(bool|int|string $isMandatory): CheckboxDataObject
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
     * @param  bool|int|string $hasLogging
     * @return CheckboxDataObject
     */
    public function setHasLogging(bool|int|string $hasLogging): CheckboxDataObject
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
     * @param int|string $sort
     * @return CheckboxDataObject
     */
    public function setSort(int|string $sort): CheckboxDataObject
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
    public function getIsInternal(): bool
    {
        return $this->isInternal;
    }

    /**
     * @param  bool|int|string $isInternal
     * @return CheckboxDataObject
     */
    public function setIsInternal(bool|int|string $isInternal): CheckboxDataObject
    {
        $this->isInternal = (bool)$isInternal;

        return $this;
    }
}
