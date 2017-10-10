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
    public $cName;

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
     * @var FilterExtra|FilterExtra[]
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
     * @var Navigationsfilter
     */
    protected $naviFilter;

    /**
     * @var mixed
     */
    protected $options;

    /**
     * AbstractFilter constructor
     *
     * @param Navigationsfilter $naviFilter
     */
    public function __construct($naviFilter)
    {
        $this->setData($naviFilter)->setClassName(get_class($this));
    }

    /**
     * @param int|array $value
     * @return $this
     */
    public function init($value)
    {
        $this->isInitialized = true;

        return $this->setValue($value)->setSeo($this->availableLanguages);
    }

    /**
     * @return $this
     */
    public function generateActiveFilterData()
    {
        $this->activeValues = [];
        $values             = $this->getValue();
        if (!is_array($values)) {
            $values = [$values];
        }
        foreach ($values as $value) {
            $this->activeValues[] = (new FilterExtra())->setFrontendName($this->getName())
                                                       ->setURL($this->getSeo($this->languageID))
                                                       ->setValue($value)
                                                       ->setName($this->getFrontendName())
                                                       ->setType($this->getType());
        }

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
     * @param string|null $idx
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
        return $this->cName;
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
     * @return array
     */
    public function getOptions($data = null)
    {
        return [];
    }

    /**
     * @param Navigationsfilter $naviFilter
     * @return $this
     */
    public function setNaviFilter($naviFilter)
    {
        $this->naviFilter = $naviFilter;

        return $this;
    }

    /**
     * @return Navigationsfilter
     */
    public function getNaviFilter()
    {
        return $this->naviFilter;
    }

    /**
     * @param Navigationsfilter $naviFilter
     * @return $this
     */
    public function setData($naviFilter)
    {
        $this->naviFilter         = $naviFilter;
        $this->languageID         = $naviFilter->getLanguageID();
        $this->customerGroupID    = $naviFilter->getCustomerGroupID();
        $this->availableLanguages = $naviFilter->getAvailableLanguages();

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
     * @return string
     */
    public function getUrlParamSEO()
    {
        return $this->urlParamSEO;
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
        return $this->naviFilter->getConfig();
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
     * @return FilterExtra
     */
    public function getExtraFilter()
    {
        return new FilterExtra();
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        $res               = get_object_vars($this);
        $res['config']     = '*truncated*';
        $res['naviFilter'] = '*truncated*';

        return $res;
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
     * @return FilterExtra|FilterExtra[]|IFilter
     */
    public function getActiveValues($idx = null)
    {
//        return $this->activeValues !== null
//            ? $this->activeValues
//            : $this;
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
        return '';
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
     * @param array|int|string $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return array|int|string
     */
    public function getValue()
    {
        return $this->value;
    }
}
