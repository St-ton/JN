<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterExtra
 *
 * @property $kHersteller
 * @property $nAnzahlTagging
 * @property $kTag
 * @property $kKategorie
 * @property $nVon
 * @property $cVonLocalized
 * @property $nBis
 * @property $cBisLocalized
 * @property $nAnzahlArtikel
 * @property $nStern
 * @property $kKey
 * @property $cSuche
 * @property $kSuchanfrage
 */
class FilterExtra extends AbstractFilter
{
    /**
     * @var string
     */
    private $param = '';

    /**
     * @var int
     */
    private $count;

    /**
     * @var string
     */
    private $url;

    /**
     * @var int
     */
    private $sort = 0;

    /**
     * if set to true, Navigationsfilter::getURL() will not return a SEO URL
     *
     * @var bool
     */
    private $disableSeoURLs = false;

    /**
     * @var string
     */
    private $class = '';

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var bool
     */
    protected $isActive = false;

    /**
     * @var array
     */
    private static $mapping = [
        'cName'          => 'Name',
        'nAnzahl'        => 'Count',
        'nAnzahlArtikel' => 'Count',
        'cURL'           => 'URL',
        'Klasse'         => 'Class',
        'nSortNr'        => 'Sort'
    ];

    /**
     * FilterExtra constructor.
     * @param null $productFilter
     */
    public function __construct($productFilter = null)
    {
        $this->isInitialized = true;
    }

    /**
     * @param string $value
     * @return string|null
     */
    private static function getMapping($value)
    {
        return isset(self::$mapping[$value])
            ? self::$mapping[$value]
            : null;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->isActive;
    }

    /**
     * @param $isActive
     * @return $this
     */
    public function setIsActive($isActive)
    {
        $this->isActive = (bool)$isActive;
        $this->nAktiv   = (int)$isActive;

        return $this;
    }

    /**
     * @return int
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * @param int $sort
     * @return $this
     */
    public function setSort($sort)
    {
        $this->sort = (int)$sort;

        return $this;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param string $class
     * @return $this
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @return int
     */
    public function getParam()
    {
        return $this->param;
    }

    /**
     * @param string $param
     * @return $this
     */
    public function setParam($param)
    {
        $this->param = $param;

        return $this;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param int $count
     * @return $this
     */
    public function setCount($count)
    {
        $this->count = (int)$count;

        return $this;
    }

    /**
     * @return string
     */
    public function getURL()
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setURL($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return bool
     */
    public function getDisableSeoURLs()
    {
        return $this->disableSeoURLs;
    }

    /**
     * @param bool $disableSeoURLs
     * @return $this
     */
    public function setDisableSeoURLs($disableSeoURLs)
    {
        $this->disableSeoURLs = $disableSeoURLs;

        return $this;
    }

    /**
     * @param null $data
     * @return array
     */
    public function getOptions($data = null)
    {
        return $this->options;
    }

    /**
     * @param FilterExtra $option
     * @return $this
     */
    public function addOption($option)
    {
        $this->options[] = $option;

        return $this;
    }

    /**
     * @param string $name
     * @param mixed  $value
     * @return $this
     */
    public function __set($name, $value)
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;

            return $this;
        }

        $this->data[$name] = $value;

        return $this;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function __get($name)
    {
        if (($mapped = self::getMapping($name)) !== null) {
            $method = 'get' . $mapped;

            return $this->$method();
        }

        return isset($this->data[$name])
            ? $this->data[$name]
            : null;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return property_exists($this, $name) || self::getMapping($name) !== null || isset($this->data[$name]);
    }

    /**
     * @param array $languages
     * @return $this
     */
    public function setSeo($languages)
    {
        return $this;
    }

    /**
     * @return string
     */
    public function getSQLJoin()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getSQLCondition()
    {
        return '';
    }
}
