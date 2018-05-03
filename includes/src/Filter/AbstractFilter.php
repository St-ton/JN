<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Filter;

/**
 * Class AbstractFilter
 * @package Filter
 */
abstract class AbstractFilter implements IFilter
{
    /**
     * filter can increase product amount
     */
    const FILTER_TYPE_OR = 0;

    /**
     * filter will decrease product amount
     */
    const FILTER_TYPE_AND = 1;

    /**
     * never show filter
     */
    const SHOW_NEVER = 0;

    /**
     * show filter in box
     */
    const SHOW_BOX = 1;

    /**
     * show filter in content area
     */
    const SHOW_CONTENT = 2;

    /**
     * always show filter
     */
    const SHOW_ALWAYS = 3;

    /**
     * filter type selectbox
     */
    const INPUT_SELECT = 1;

    /**
     * filter type checkbox
     */
    const INPUT_CHECKBOX = 2;

    /**
     * filter type button
     */
    const INPUT_BUTTON = 3;

    /**
     * @var string|null
     */
    protected $icon;

    /**
     * @var bool
     */
    protected $isCustom = true;

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    public $cSeo = [];

    /**
     * @var int
     */
    protected $type = self::FILTER_TYPE_AND;

    /**
     * @var string
     */
    protected $urlParam = '';

    /**
     * @var string
     */
    protected $urlParamSEO = '';

    /**
     * @var int|string|array
     */
    protected $value;

    /**
     * @var int
     */
    protected $languageID = 0;

    /**
     * @var int
     */
    protected $customerGroupID = 0;

    /**
     * @var array
     */
    protected $availableLanguages = [];

    /**
     * @var bool
     */
    protected $isInitialized = false;

    /**
     * @var string
     */
    protected $className = '';

    /**
     * @var string
     */
    protected $niceName = '';

    /**
     * @var int
     */
    protected $inputType = self::INPUT_SELECT;

    /**
     * @var FilterOption[]
     */
    protected $activeValues;

    /**
     * workaround since built-in filters can be registered multiple times (for example Navigationsfilter->KategorieFilter)
     * this makes sure there value is not used more then once when Navigationsfilter::getURL()
     * generates the current URL.
     *
     * @var bool
     */
    private $isChecked = false;

    /**
     * used to create FilterLoesenURLs
     *
     * @var bool
     */
    private $doUnset = false;

    /**
     * @var string|array
     */
    private $unsetFilterURL = '';

    /**
     * @var int
     */
    private $visibility = self::SHOW_ALWAYS;

    /**
     * @var int
     */
    private $count = 0;

    /**
     * @var int
     */
    private $sort = 0;

    /**
     * @var string
     */
    protected $frontendName = '';

    /**
     * list of filter options for AttributeFilters etc. that consist of multiple different filter options
     *
     * @var array
     */
    private $filterCollection = [];

    /**
     * @var ProductFilter
     */
    protected $productFilter;

    /**
     * @var mixed
     */
    protected $options;

    /**
     * @var string
     */
    protected $tableName = '';

    /**
     * @var bool
     */
    protected $isActive = false;

    /**
     * AbstractFilter constructor
     *
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        $this->setBaseData($productFilter)->setClassName(get_class($this));
    }

    /**
     * @inheritdoc
     */
    public function init($value): IFilter
    {
        if ($value !== null) {
            $this->isInitialized = true;
            $this->setValue($value)->setSeo($this->availableLanguages);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @inheritdoc
     */
    public function setIsActive($active): IFilter
    {
        $this->isActive = $active;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setIsInitialized($value): IFilter
    {
        $this->isInitialized = $value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function generateActiveFilterData(): IFilter
    {
        $this->activeValues = [];
        $values             = $this->getValue();
        $split              = true;
        if (!is_array($values)) {
            $split  = false;
            $values = [$values];
        }
        foreach ($values as $value) {
            if ($split === true) {
                $class = $this->getClassName();
                /** @var IFilter $instance */
                $instance = new $class($this->getProductFilter());
                $instance->init($value);
            } else {
                $instance = $this;
            }
            $this->activeValues[] = (new FilterOption())
                ->setURL($this->getSeo($this->languageID))
                ->setFrontendName($instance->getName())
                ->setValue($value)
                ->setName($instance->getFrontendName())
                ->setType($this->getType());
        }
        $this->isActive = true;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setFilterCollection(array $collection): IFilter
    {
        $this->filterCollection = $collection;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getFilterCollection($onlyVisible = true): array
    {
        return $onlyVisible === false
            ? $this->filterCollection
            : array_filter(
                $this->filterCollection,
                function (IFilter $f) {
                    return $f->getVisibility() !== self::SHOW_NEVER;
                }
            );
    }

    /**
     * @inheritdoc
     */
    public function setFrontendName(string $name): IFilter
    {
        $this->frontendName = htmlspecialchars($name);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getFrontendName()
    {
        return $this->frontendName;
    }

    /**
     * @inheritdoc
     */
    public function getVisibility(): int
    {
        return $this->visibility;
    }

    /**
     * @inheritdoc
     */
    public function setVisibility($visibility): IFilter
    {
        $this->visibility = self::SHOW_NEVER;
        if (is_numeric($visibility)) {
            $this->visibility = $visibility;
        } elseif ($visibility === 'content') {
            $this->visibility = self::SHOW_CONTENT;
        } elseif ($visibility === 'box') {
            $this->visibility = self::SHOW_BOX;
        } elseif ($visibility === 'Y') {
            $this->visibility = self::SHOW_ALWAYS;
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setUnsetFilterURL($url): IFilter
    {
        $this->unsetFilterURL = $url;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getUnsetFilterURL($idx = null)
    {
        if ($idx !== null && is_array($idx) && count($idx) === 1) {
            $idx = $idx[0];
        }

        return $idx === null || is_string($this->unsetFilterURL)
            ? $this->unsetFilterURL
            : $this->unsetFilterURL[$idx];
    }

    /**
     * @inheritdoc
     */
    public function getAvailableLanguages(): array
    {
        return $this->availableLanguages;
    }

    /**
     * @inheritdoc
     */
    public function addValue($value): IFilter
    {
        $this->value[] = (int)$value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isInitialized(): bool
    {
        return $this->isInitialized;
    }

    /**
     * @inheritdoc
     */
    public function getSeo($idx = null)
    {
        return $idx !== null
            ? ($this->cSeo[$idx] ?? null)
            : $this->cSeo;
    }

    /**
     * @inheritdoc
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function setType(int $type): IFilter
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function setName($name): IFilter
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getOptions($data = null): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function setOptions($data): IFilter
    {
        $this->options = $data;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setProductFilter(ProductFilter $productFilter): IFilter
    {
        $this->productFilter = $productFilter;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getProductFilter(): ProductFilter
    {
        return $this->productFilter;
    }

    /**
     * @inheritdoc
     */
    public function setBaseData($productFilter): IFilter
    {
        $this->productFilter      = $productFilter;
        $this->languageID         = $productFilter->getLanguageID();
        $this->customerGroupID    = $productFilter->getCustomerGroupID();
        $this->availableLanguages = $productFilter->getAvailableLanguages();

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getUrlParam()
    {
        return $this->urlParam;
    }

    /**
     * @inheritdoc
     */
    public function setUrlParam($param): IFilter
    {
        $this->urlParam = $param;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getUrlParamSEO()
    {
        return $this->urlParamSEO;
    }

    /**
     * @inheritdoc
     */
    public function setUrlParamSEO($param): IFilter
    {
        $this->urlParamSEO = $param;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isCustom(): bool
    {
        return $this->isCustom;
    }

    /**
     * @inheritdoc
     */
    public function setIsCustom(bool $custom): IFilter
    {
        $this->isCustom = $custom;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLanguageID(): int
    {
        return $this->languageID;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerGroupID(): int
    {
        return $this->customerGroupID;
    }

    /**
     * @inheritdoc
     */
    public function getConfig(): array
    {
        return $this->productFilter->getConfig();
    }

    /**
     * @inheritdoc
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @inheritdoc
     */
    public function setClassName($className): IFilter
    {
        $this->className = $className;
        $this->setNiceName(basename(str_replace('\\', '/', $className)));

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getNiceName()
    {
        return $this->niceName;
    }

    /**
     * @inheritdoc
     */
    public function setNiceName($name): IFilter
    {
        $this->niceName = $name;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsChecked(): bool
    {
        return $this->isChecked;
    }

    /**
     * @inheritdoc
     */
    public function setIsChecked(bool $isChecked): IFilter
    {
        $this->isChecked = $isChecked;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDoUnset(): bool
    {
        return $this->doUnset;
    }

    /**
     * @inheritdoc
     */
    public function setDoUnset(bool $doUnset): IFilter
    {
        $this->doUnset = $doUnset;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getInputType()
    {
        return $this->inputType;
    }

    /**
     * @inheritdoc
     */
    public function setInputType($type): IFilter
    {
        $this->inputType = $type;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @inheritdoc
     */
    public function setIcon($icon): IFilter
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTableAlias(): string
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * @inheritdoc
     */
    public function setTableName($name): IFilter
    {
        $this->tableName = $name;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getActiveValues($idx = null)
    {
        $activeValues = $this->activeValues ?? $this;
        if (is_array($activeValues) && count($activeValues) === 1) {
            $activeValues = $activeValues[0];
        }

        return $activeValues;
    }

    /**
     * @inheritdoc
     */
    public function hide(): IFilter
    {
        $this->visibility = self::SHOW_NEVER;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isHidden(): bool
    {
        return $this->visibility === self::SHOW_NEVER;
    }

    /**
     * @inheritdoc
     */
    public function getPrimaryKeyRow(): string
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function getSQLCondition(): string
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function getSQLJoin()
    {
        return new FilterJoin();
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @inheritdoc
     */
    public function setValue($value): IFilter
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @inheritdoc
     */
    public function setCount($count)
    {
        $this->count = (int)$count;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * @inheritdoc
     */
    public function setSort($sort)
    {
        $this->sort = (int)$sort;

        return $this;
    }

    /**
     * @return int
     */
    public function getValueCompat()
    {
        return $this->value;
    }

    /**
     * this is only called when someone tries to directly set $NaviFilter->Suchanfrage->kSuchanfrage,
     * $NaviFilter-Kategorie->kKategorie etc.
     * it implies that this filter has to be enabled afterwards
     *
     * @param int $value
     * @return $this
     */
    public function setValueCompat($value)
    {
        $this->value = (int)$value;
        if ($this->value > 0) {
            $this->productFilter->enableFilter($this);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        $res                  = get_object_vars($this);
        $res['config']        = '*truncated*';
        $res['productFilter'] = '*truncated*';

        return $res;
    }
}
