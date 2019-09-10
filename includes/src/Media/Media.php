<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Media;

use Exception;
use JTL\Media\Image\Category;
use JTL\Media\Image\Manufacturer;
use JTL\Media\Image\MediaImageCompatibility;
use JTL\Media\Image\News;
use JTL\Media\Image\NewsCategory;
use JTL\Media\Image\Product;
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
        $this->register(new Product())
             ->register(new Category())
             ->register(new Manufacturer())
             ->register(new News())
             ->register(new NewsCategory())
             ->register(new MediaImageCompatibility());
    }

    /**
     * @param Product|MediaImageCompatibility $media
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
