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
     * @var LanguageHelper|null
     */
    private static $_instance = null;

    /**
     * @var string|null
     */
    public $cacheID = null;

    /**
     *
     */
    public function __construct()
    {
        $this->cacheID   = 'langdata_' . Shop::Cache()->getBaseID(false, false, true, true, true, false);
        self::$_instance = $this;
    }

    /**
     * @return LanguageHelper
     */
    public static function getInstance()
    {
        return (self::$_instance === null) ? new self() : self::$_instance;
    }
}
