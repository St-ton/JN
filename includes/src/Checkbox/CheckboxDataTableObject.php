<?php declare(strict_types=1);

namespace JTL\Checkbox;

use JTL\Checkbox\CheckboxLanguage\CheckboxLanguageDataTableObject;
use JTL\DataObjects\AbstractDataObject;
use JTL\DataObjects\DataTableObjectInterface;

/**
 * Class CheckboxDataTableObject
 * @package JTL\Checkbox
 */
class CheckboxDataTableObject extends AbstractDataObject implements DataTableObjectInterface
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
     * @var array
     */
    private array $languages = [];

    /**
     * @var CheckboxLanguageDataTableObject[]
     */
    private array $checkBoxLanguage_arr = [];

    /**
     * @var array
     */
    private array $customerGroup_arr = [];

    /**
     * @var array
     */
    private array $displayAt_arr = [];

    /**
     * @var string[]
     */
    private array $mapping = [
        'checkboxID'             => 'checkboxID',
        'linkID'                 => 'linkID',
        'checkboxFunctionID'     => 'checkboxFunctionID',
        'name'                   => 'name',
        'customerGroupsSelected' => 'customerGroupsSelected',
        'kKundengruppe'          => 'customerGroupsSelected',
        'displayAt'              => 'displayAt',
        'active'                 => 'active',
        'isMandatory'            => 'isMandatory',
        'hasLogging'             => 'hasLogging',
        'sort'                   => 'sort',
        'created'                => 'created',
        'created_DE'             => 'createdDE',
        'oCheckBoxLanguage_arr'  => 'checkBoxLanguage_arr',
        'customerGroup_arr'      => 'customerGroup_arr',
        'displayAt_arr'          => 'displayAt_arr',
    ];

    /**
     * @var string[]
     */
    private array $columnMapping = [
        'kCheckBox'            => 'checkboxID',
        'kLink'                => 'linkID',
        'kCheckBoxFunktion'    => 'checkboxFunctionID',
        'cName'                => 'name',
        'cKundengruppe'        => 'customerGroupsSelected',
        'cAnzeigeOrt'          => 'displayAt',
        'nAktiv'               => 'active',
        'nPflicht'             => 'isMandatory',
        'nLogging'             => 'hasLogging',
        'nSort'                => 'sort',
        'dErstellt'            => 'created',
        'dErstellt_DE'         => 'createdDE',
        'oCheckBoxSprache_arr' => 'checkBoxLanguage_arr',
        'kKundengruppe_arr'    => 'customerGroup_arr',
        'kAnzeigeOrt_arr'      => 'displayAt_arr',
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
     * @return int
     */
    public function getCheckboxID(): int
    {
        return $this->checkboxID;
    }

    /**
     * @param int|string $checkboxID
     * @return CheckboxDataTableObject
     */
    public function setCheckboxID(int|string $checkboxID): CheckboxDataTableObject
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
     * @param int|string|null $linkID
     * @return CheckboxDataTableObject
     */
    public function setLinkID(int|string|null $linkID): CheckboxDataTableObject
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
     * @param int|string|null $checkboxFunctionID
     * @return CheckboxDataTableObject
     */
    public function setCheckboxFunctionID(int|string|null $checkboxFunctionID): CheckboxDataTableObject
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
     * @return CheckboxDataTableObject
     */
    public function setName(string $name): CheckboxDataTableObject
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
     * @return CheckboxDataTableObject
     */
    public function setCustomerGroupsSelected(array|string $customerGroupsSelected): CheckboxDataTableObject
    {
        if (\is_array($customerGroupsSelected)) {
            $customerGroupsSelected = ';' . \implode(';', $customerGroupsSelected) . ';';
        }
        $this->customerGroupsSelected = $customerGroupsSelected;
        $this->setCustomerGroupArr(\array_filter(\explode(';', $customerGroupsSelected)));

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
     * @return CheckboxDataTableObject
     */
    public function setDisplayAt(array|string $displayAt): CheckboxDataTableObject
    {
        if (\is_array($displayAt)) {
            $displayAt = ';' . \implode(';', $displayAt) . ';';
        }
        $this->displayAt = $displayAt;
        $this->setDisplayAtArr(\array_filter(\explode(';', $displayAt)));

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
     * @return CheckboxDataTableObject
     */
    public function setActive(bool|int|string $active): CheckboxDataTableObject
    {
        $this->active = $this->checkAndReturnBoolValue($active);

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
     * @param bool|int|string $isMandatory
     * @return CheckboxDataTableObject
     */
    public function setIsMandatory(bool|int|string $isMandatory): CheckboxDataTableObject
    {
        $this->isMandatory = $this->checkAndReturnBoolValue($isMandatory);

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
     * @param bool|int|string $hasLogging
     * @return CheckboxDataTableObject
     */
    public function setHasLogging(bool|int|string $hasLogging): CheckboxDataTableObject
    {
        $this->hasLogging = $this->checkAndReturnBoolValue($hasLogging);

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
     * @return CheckboxDataTableObject
     */
    public function setSort(int|string $sort): CheckboxDataTableObject
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
     * @return CheckboxDataTableObject
     */
    public function setCreated(string $created): CheckboxDataTableObject
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
     * @return CheckboxDataTableObject
     */
    public function setCreatedDE(string $created_DE): CheckboxDataTableObject
    {
        $this->created_DE = $created_DE;

        return $this;
    }

    /**
     * @return array
     */
    public function getLanguages(): array
    {
        return $this->languages;
    }

    /**
     * @param string $code
     * @param array  $language
     * @return CheckboxDataTableObject
     */
    public function addLanguage(string $code, array $language): CheckboxDataTableObject
    {
        $this->languages[$code] = $language;

        return $this;
    }

    /**
     * @return array
     */
    public function getCheckBoxLanguageArr(): array
    {
        return $this->checkBoxLanguage_arr;
    }

    /**
     * @param array $checkBoxLanguage_arr
     * @return CheckboxDataTableObject
     */
    public function setCheckBoxLanguageArr(array $checkBoxLanguage_arr): CheckboxDataTableObject
    {
        $this->checkBoxLanguage_arr = $checkBoxLanguage_arr;

        return $this;
    }

    /**
     * @param CheckboxLanguageDataTableObject $checkBoxLanguage
     * @return CheckboxDataTableObject
     */
    public function addCheckBoxLanguageArr(CheckboxLanguageDataTableObject $checkBoxLanguage): CheckboxDataTableObject
    {
        $this->checkBoxLanguage_arr[] = $checkBoxLanguage;

        return $this;
    }

    /**
     * @return array
     */
    public function getCustomerGroupArr(): array
    {
        return $this->customerGroup_arr;
    }

    /**
     * @param array $customerGroup_arr
     * @return CheckboxDataTableObject
     */
    public function setCustomerGroupArr(array $customerGroup_arr): CheckboxDataTableObject
    {
        $this->customerGroup_arr = $customerGroup_arr;

        return $this;
    }

    /**
     * @return array
     */
    public function getDisplayAtArr(): array
    {
        return $this->displayAt_arr;
    }

    /**
     * @param array $displayAt_arr
     * @return CheckboxDataTableObject
     */
    public function setDisplayAtArr(array $displayAt_arr): CheckboxDataTableObject
    {
        $this->displayAt_arr = $displayAt_arr;

        return $this;
    }
}
