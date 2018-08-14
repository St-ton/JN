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
    private static $_instance;

    /**
     * @var MediaImage[]|MediaImageCompatibility[]
     */
    private $types = [];

    /**
     * @return Media
     */
    public static function getInstance(): self
    {
        return self::$_instance ?? new self();
    }

    /**
     *
     */
    public function __construct()
    {
        self::$_instance = $this;
        $this->register(new MediaImage())
             ->register(new MediaImageCompatibility());
    }

    /**
     * @param MediaImage|MediaImageCompatibility $media
     * @return $this
     */
    public function register($media): self
    {
        $this->types[] = $media;

        return $this;
    }

    /**
     * @param string $requestUri
     * @return bool
     */
    public function isValidRequest($requestUri): bool
    {
        foreach ($this->types as $type) {
            if ($type->isValid($requestUri)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $requestUri
     * @return bool|mixed
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
