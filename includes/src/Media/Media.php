<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Media;

use Exception;
use JTL\Shop;
use function Functional\some;

/**
 * Class Media
 * @package JTL\Media
 */
class Media
{
    /**
     * @var Media
     */
    private static $instance;

    /**
     * @var IMedia[]
     */
    private $types = [];

    /**
     * @return Media
     */
    public static function getInstance(): self
    {
        return self::$instance ?? new self();
    }

    /**
     *
     */
    public function __construct()
    {
        self::$instance = $this;
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
    public function isValidRequest(string $requestUri): bool
    {
        return some($this->types, function (IMedia $e) use ($requestUri) {
            return $e->isValid($requestUri);
        });
    }

    /**
     * @param string $requestUri
     * @return bool|mixed
     * @throws Exception
     */
    public function handleRequest(string $requestUri)
    {
        foreach ($this->types as $type) {
            if ($type->isValid($requestUri)) {
                return $type->handle($requestUri);
            }
        }

        return false;
    }
}
