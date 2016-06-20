<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Media
 */
class Media
{
    /**
     * @var Media
     */
    private static $_instance = null;

    /**
     * @var array
     */
    private $types = array();

    /**
     * @return Media
     */
    public static function getInstance()
    {
        return (self::$_instance === null) ? new self() : self::$_instance;
    }

    /**
     *
     */
    public function __construct()
    {
        self::$_instance = $this;
        $this->register(new MediaImage());
        $this->register(new MediaImageCompatibility());
    }

    /**
     * @param $media
     * @return $this
     */
    public function register($media)
    {
        $this->types[] = $media;

        return $this;
    }

    /**
     * @param $requestUri
     * @return bool
     */
    public function isValidRequest($requestUri)
    {
        foreach ($this->types as $type) {
            if ($type->isValid($requestUri)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $requestUri
     * @return bool
     */
    public function handleRequest($requestUri)
    {
        foreach ($this->types as $type) {
            if ($type->isValid($requestUri)) {
                return $type->handle($requestUri);
            }
        }

        return false;
    }
}
