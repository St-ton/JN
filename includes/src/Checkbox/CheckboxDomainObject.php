<?php declare(strict_types=1);

namespace JTL\Checkbox;

use JTL\Checkbox\CheckboxLanguage\CheckboxLanguageDataTableObject;
use JTL\DataObjects\AbstractDataObject;
use JTL\DataObjects\DataTableObjectInterface;

/**
 * Class CheckboxDataTableObject
 * @package JTL\Checkbox
 */
class CheckboxDomainObject extends AbstractDataObject implements DataTableObjectInterface
{
    /**
     * @var string
     */
    private string $primaryKey = 'kCheckBox';

    public function __construct(
        protected int             $checkboxID = 0,
        readonly protected int    $linkID = 0,
        readonly protected int    $checkboxFunctionID = 0,
        readonly protected string $name = '',
        readonly protected string $customerGroupsSelected = '',
        readonly protected string $displayAt = '',
        readonly protected bool   $active = true,
        readonly protected bool   $isMandatory = false,
        readonly protected bool   $hasLogging = true,
        readonly protected int    $sort = 0,
        readonly protected string $created = '',
        readonly protected bool   $internal = false,
        readonly private string   $created_DE = '',
        private array             $languages = [],
        readonly private bool     $nLink = false,
        private array             $checkBoxLanguage_arr = [],
        readonly private array    $customerGroup_arr = [],
        readonly private array    $displayAt_arr = [],
    ) {
    }

    /**
     * @param int $checkboxID
     */
    public function setCheckboxID(int $checkboxID): void
    {
        $this->checkboxID = $checkboxID;
    }

    /**
     * @param array $languages
     */
    public function setLanguages(array $languages): void
    {
        $this->languages = $languages;
    }

    /**
     * @param CheckboxLanguageDataTableObject $checkBoxLanguage
     */
    public function addCheckBoxLanguageArr(CheckboxLanguageDataTableObject $checkBoxLanguage): void
    {
        $this->checkBoxLanguage_arr[] = $checkBoxLanguage;
    }

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
        'nlink'                  => 'hasLink',
        'nFunction'              => 'hasFunction',
        'created_DE'             => 'createdDE',
        'oCheckBoxLanguage_arr'  => 'checkBoxLanguage_arr',
        'customerGroup_arr'      => 'customerGroup_arr',
        'displayAt_arr'          => 'displayAt_arr',
        'internal'               => 'internal',
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
        'nInternal'            => 'internal',
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
     * @return int
     */
    public function getLinkID(): int
    {
        return $this->linkID;
    }

    /**
     * @return int
     */
    public function getCheckboxFunctionID(): int
    {
        return $this->checkboxFunctionID;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getCustomerGroupsSelected(): string
    {
        return $this->customerGroupsSelected;
    }

    /**
     * @return string
     */
    public function getDisplayAt(): string
    {
        return $this->displayAt;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @return bool
     */
    public function isMandatory(): bool
    {
        return $this->isMandatory;
    }

    /**
     * @return bool
     */
    public function isLogging(): bool
    {
        return $this->hasLogging;
    }

    /**
     * @return int
     */
    public function getSort(): int
    {
        return $this->sort;
    }

    /**
     * @return string
     */
    public function getCreated(): string
    {
        return $this->created;
    }

    /**
     * @return bool
     */
    public function getInternal(): bool
    {
        return $this->internal;
    }

    /**
     * @return string
     */
    public function getCreatedDE(): string
    {
        return $this->created_DE;
    }

    /**
     * @return array
     */
    public function getLanguages(): array
    {
        return $this->languages;
    }

    /**
     * @return array
     */
    public function getCheckBoxLanguageArr(): array
    {
        return $this->checkBoxLanguage_arr;
    }

    /**
     * @return array
     */
    public function getCustomerGroupArr(): array
    {
        return $this->customerGroup_arr;
    }

    /**
     * @return array
     */
    public function getDisplayAtArr(): array
    {
        return $this->displayAt_arr;
    }

    /**
     * @return bool
     */
    public function getHasLink(): bool
    {
        return $this->nLink;
    }
}
