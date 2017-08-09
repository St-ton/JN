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
class FilterExtra
{
    /**
     * @var mixed
     */
    private $value;

    /**
     * @var int
     */
    private $type;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var string
     */
    private $className;

    /**
     * @var string
     */
    private $param = '';

    /**
     * @var int
     */
    private $count;

    /**
     * @var int
     */
    public $nAnzahl;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    public $cURL = '';

    /**
     * @var int
     */
    private $sort = 0;

    /**
     * @var int
     */
    public $nSortNr = 0;

    /**
     * used to create FilterLoesenURLs
     *
     * @var bool
     */
    private $doUnset = false;

    /**
     * if set to true, Navigationsfilter::getURL() will not return a SEO URL
     *
     * @var bool
     */
    private $disableSeoURLs = false;

    /**
     * @var string
     */
    public $Klasse = '';

    /**
     * @var string
     */
    private $class = '';

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var string
     */
    private $frontendName = '';

    /**
     * @return string
     */
    public function getFrontendName()
    {
        return $this->frontendName;
    }

    /**
     * @param string $frontendName
     * @return FilterExtra
     */
    public function setFrontendName($frontendName)
    {
        $this->frontendName = $frontendName;

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
        $this->sort    = (int)$sort;
        $this->nSortNr = (int)$sort;

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
        $this->Klasse = $class;

        return $this;
    }


    /**
     * @param mixed $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
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
        $this->name  = $name;
        $this->cName = $name;

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
        $this->count   = (int)$count;
        $this->nAnzahl = (int)$count;

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
        $this->url  = $url;
        $this->cURL = $url;

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
     * @return array
     */
    public function getOptions()
    {
        return [$this];
    }

    /**
     * @param string $name
     * @param mixed  $value
     * @return $this
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;

        return $this;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function __get($name)
    {
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
        return isset($this->data[$name]);
    }
}
