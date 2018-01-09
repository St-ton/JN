<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * class AbstractFilter
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
     * @var string
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
     * @param int|array $value
     * @return $this
     */
    public function init($value)
    {
        if ($value !== null) {
            $this->isInitialized = true;
            $this->setValue($value)->setSeo($this->availableLanguages);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->isActive;
    }

    /**
     * @param bool $active
     * @return $this
     */
    public function setIsActive($active)
    {
        $this->isActive = $active;

        return $this;
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function setIsInitialized($value)
    {
        $this->isInitialized = $value;

        return $this;
    }

    /**
     * @return $this
     */
    public function generateActiveFilterData()
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
                $class    = $this->getClassName();
                $instance = (new $class($this->getProductFilter()))->init($value);
            } else {
                $instance = $this;
            }
            $this->activeValues[] = (new FilterOption())->setFrontendName($instance->getName())
                                                        ->setURL($this->getSeo($this->languageID))
                                                        ->setValue($value)
                                                        ->setName($instance->getFrontendName())
                                                        ->setType($this->getType());
        }
        $this->isActive = true;

        return $this;
    }

    /**
     * @param array $collection
     * @return $this
     */
    public function setFilterCollection(array $collection)
    {
        $this->filterCollection = $collection;

        return $this;
    }

    /**
     * @param bool $onlyVisible - only show visible filters
     * @return array
     */
    public function getFilterCollection($onlyVisible = true)
    {
        return $onlyVisible === false
            ? $this->filterCollection
            : array_filter(
                $this->filterCollection,
                function ($f) {
                    /** @var $f IFilter */
                    return $f->getVisibility() !== self::SHOW_NEVER;
                }
            );
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setFrontendName($name)
    {
        $this->frontendName = htmlspecialchars($name);

        return $this;
    }

    /**
     * @return string
     */
    public function getFrontendName()
    {
        return $this->frontendName;
    }

    /**
     * @return int
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * @param int|string $visibility
     * @return $this
     */
    public function setVisibility($visibility)
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
     * @param string|array $url
     * @return $this
     */
    public function setUnsetFilterURL($url)
    {
        $this->unsetFilterURL = $url;

        return $this;
    }

    /**
     * @param string|array|null $idx
     * @return string|array
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
     * @return array
     */
    public function getAvailableLanguages()
    {
        return $this->availableLanguages;
    }

    /**
     * @param int|string $value
     * @return $this
     */
    public function addValue($value)
    {
        $this->value[] = (int)$value;

        return $this;
    }

    /**
     * @return bool
     */
    public function isInitialized()
    {
        return $this->isInitialized;
    }

    /**
     * @param int $idx
     * @return string|null|array
     */
    public function getSeo($idx = null)
    {
        return $idx !== null
            ? (isset($this->cSeo[$idx])
                ? $this->cSeo[$idx]
                : null)
            : $this->cSeo;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = (int)$type;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param mixed $data
     * @return $this
     */
    public function setOptions($data)
    {
        $this->options = $data;

        return $this;
    }

    /**
     * @param null|mixed $data
     * @return FilterOption[]
     */
    public function getOptions($data = null)
    {
        return [];
    }

    /**
     * @param ProductFilter $productFilter
     * @return $this
     */
    public function setProductFilter($productFilter)
    {
        $this->productFilter = $productFilter;

        return $this;
    }

    /**
     * @return ProductFilter
     */
    public function getProductFilter()
    {
        return $this->productFilter;
    }

    /**
     * @param ProductFilter $productFilter
     * @return $this
     */
    public function setBaseData($productFilter)
    {
        $this->productFilter      = $productFilter;
        $this->languageID         = $productFilter->getLanguageID();
        $this->customerGroupID    = $productFilter->getCustomerGroupID();
        $this->availableLanguages = $productFilter->getAvailableLanguages();

        return $this;
    }

    /**
     * @return string
     */
    public function getUrlParam()
    {
        return $this->urlParam;
    }

    /**
     * @param string $param
     * @return $this
     */
    public function setUrlParam($param)
    {
        $this->urlParam = $param;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrlParamSEO()
    {
        return $this->urlParamSEO;
    }

    /**
     * @param string $param
     * @return $this
     */
    public function setUrlParamSEO($param)
    {
        $this->urlParamSEO = $param;

        return $this;
    }

    /**
     * @return bool
     */
    public function isCustom()
    {
        return $this->isCustom;
    }

    /**
     * @return int
     */
    public function getLanguageID()
    {
        return $this->languageID;
    }

    /**
     * @return int
     */
    public function getCustomerGroupID()
    {
        return $this->customerGroupID;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->productFilter->getConfig();
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @param string $className
     * @return $this
     */
    public function setClassName($className)
    {
        $this->className = $className;

        return $this;
    }

    /**
     * @return FilterOption
     */
    public function getExtraFilter()
    {
        return new FilterOption();
    }

    /**
     * @return bool
     */
    public function getIsChecked()
    {
        return $this->isChecked;
    }

    /**
     * @param bool $isChecked
     * @return $this
     */
    public function setIsChecked($isChecked)
    {
        $this->isChecked = $isChecked;

        return $this;
    }

    /**
     * @return bool
     */
    public function getDoUnset()
    {
        return $this->doUnset;
    }

    /**
     * @param bool $doUnset
     * @return $this
     */
    public function setDoUnset($doUnset)
    {
        $this->doUnset = $doUnset;

        return $this;
    }

    /**
     * @return int
     */
    public function getInputType()
    {
        return $this->inputType;
    }

    /**
     * @param int $type
     * @return $this
     */
    public function setInputType($type)
    {
        $this->inputType = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param string $icon
     * @return $this
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @return string
     */
    public function getTableAlias()
    {
        return '';
    }

    /**
     * @param null|int $idx
     * @return FilterOption|FilterOption[]|IFilter
     */
    public function getActiveValues($idx = null)
    {
        $activeValues = $this->activeValues !== null
            ? $this->activeValues
            : $this;
        if (is_array($activeValues) && count($activeValues) === 1) {
            $activeValues = $activeValues[0];
        }

        return $activeValues;
    }

    /**
     * @return $this
     */
    public function hide()
    {
        $this->visibility = self::SHOW_NEVER;

        return $this;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return $this->visibility === self::SHOW_NEVER;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setTableName($name)
    {
        $this->tableName = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getPrimaryKeyRow() {
        return '';
    }

    /**
     * @return array
     */
    public function getSQLCondition()
    {
        return [];
    }

    /**
     * @return FilterJoin
     */
    public function getSQLJoin()
    {
        return new FilterJoin();
    }

    /**
     * @return array|int|string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param array|int|string $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

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
