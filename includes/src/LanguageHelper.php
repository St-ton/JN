<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class LanguageHelper
 */
class LanguageHelper
{
    /**
     * @var LanguageHelper
     */
    private static $instance;

    /**
     * @var string
     */
    public $cacheID;

    /**
     *
     */
    public function __construct()
    {
        $this->cacheID  = 'langdata_' . Shop::Container()->getCache()->getBaseID(false, false, true, true, true, false);
        self::$instance = $this;
    }

    /**
     * @return LanguageHelper
     */
    public static function getInstance(): self
    {
        return self::$instance ?? new self();
    }
}
