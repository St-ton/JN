<?php

/**
 * class AbstractFilter
 */
abstract class AbstractFilter implements IFilter
{
    /**
     * @var string
     */
    public $cName;

    /**
     * @var array
     */
    public $cSeo = [];

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
     * AbstractFilter constructor.
     */
    public function __construct()
    {
    }
}
