<?php

/**
 * class AbstractFilter
 */
abstract class AbstractFilter implements IFilter
{
    const FILTER_TYPE_OR = 0;
    const FILTER_TYPE_AND = 1;

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
     * @var bool
     */
    protected $isInitialized = false;

    /**
     * @param int   $id
     * @param array $languages
     * @return $this
     */
    public function init($id, $languages)
    {
        $this->isInitialized = true;

        return $this->setID($id)->setSeo($languages);
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
            ? ((isset($this->cSeo[$idx]))
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
     * AbstractFilter constructor.
     */
    public function __construct()
    {
    }
}
