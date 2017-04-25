<?php

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
     * @var bool
     */
    public $isCustom = true;

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
    private $type = self::FILTER_TYPE_AND;

    /**
     * @var string
     */
    public $urlParam = '';

    /**
     * @var string
     */
    public $urlParamSEO = '';

    /**
     * @var int|string|array
     */
    public $value;

    /**
     * @var int
     */
    private $languageID = 0;

    /**
     * @var int
     */
    private $customerGroupID = 0;

    /**
     * @var array
     */
    private $config = [];

    /**
     * @var array
     */
    private $availableLanguages = [];

    /**
     * @var bool
     */
    protected $isInitialized = false;

    /**
     * @var string
     */
    private $className = '';

    /**
     * workaround since built-in filters can be registered multiple times (for example Navigationsfilter->KategorieFilter)
     * this makes sure there value is not used more then once when Navigationsfilter::getURL()
     * generates the current URL.
     *
     * @var bool
     */
    public $isChecked = false;

    /**
     * used to create FilterLoesenURLs
     *
     * @var bool
     */
    private $doUnset = false;

    /**
     * @var string
     */
    private $unsetFilterURL = '';

    /**
     * @var int
     */
    private $visibility = self::SHOW_ALWAYS;

    /**
     * @var string
     */
    private $frontendName = '';

    /**
     * list of filter options for AttributeFilters etc. that consist of multiple different filter options
     *
     * @var array
     */
    private $filterCollection = [];

    /**
     * @var mixed
     */
    protected $options;

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
     * @param array $collection
     * @return $this
     */
    public function setFilterCollection(array $collection)
    {
        $this->filterCollection = $collection;

        return $this;
    }

    /**
     * @return array
     */
    public function getFilterCollection()
    {
        return $this->filterCollection;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setFrontendName($name) {
        $this->frontendName = $name;

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
     * @param string $url
     * @return $this
     */
    public function setUnsetFilterURL($url)
    {
        $this->unsetFilterURL = $url;

        return $this;
    }

    /**
     * @return string
     */
    public function getUnsetFilterURL()
    {
        return $this->unsetFilterURL;
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
        return ($idx !== null)
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
     * @param mixed $mixed
     * @return $this
     */
    public function setOptions($mixed)
    {
        $this->options = $mixed;

        return $this;
    }

    /**
     * @param null|mixed $mixed
     * @return array
     */
    public function getOptions($mixed = null)
    {
        return [];
    }

    /**
     * AbstractFilter constructor
     *
     * @param int|null   $languageID
     * @param int|null   $customerGroupID
     * @param array|null $config
     * @param array|null $languages
     */
    public function __construct($languageID = null, $customerGroupID = null, $config = null, $languages = null)
    {
        if ($languageID !== null && $customerGroupID !== null && $config !== null && $languages !== null) {
            $this->setData($languageID, $customerGroupID, $config, $languages);
        }
        $this->setClassName(get_class($this));
    }

    /**
     * @param int   $languageID
     * @param int   $customerGroupID
     * @param array $config
     * @param array $languages
     * @return $this
     */
    public function setData($languageID, $customerGroupID, $config, $languages = [])
    {
        $this->languageID         = $languageID;
        $this->customerGroupID    = $customerGroupID;
        $this->config             = $config;
        $this->availableLanguages = $languages;

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
        return $this->config;
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
        $res           = get_object_vars($this);
        $res['config'] = '*truncated*';

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
}
