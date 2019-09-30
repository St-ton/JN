<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Media;

use Exception;
use JTL\Media\Image\Category;
use JTL\Media\Image\Characteristic;
use JTL\Media\Image\CharacteristicValue;
use JTL\Media\Image\ConfigGroup;
use JTL\Media\Image\Manufacturer;
use JTL\Media\Image\News;
use JTL\Media\Image\NewsCategory;
use JTL\Media\Image\OPC;
use JTL\Media\Image\Product;
use JTL\Media\Image\Variation;
use function Functional\first;
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
     * @var array
     */
    private static $classMapper = [
        Image::TYPE_CATEGORY             => Category::class,
        Image::TYPE_CHARACTERISTIC       => Characteristic::class,
        Image::TYPE_CHARACTERISTIC_VALUE => CharacteristicValue::class,
        Image::TYPE_CONFIGGROUP          => ConfigGroup::class,
        Image::TYPE_MANUFACTURER         => Manufacturer::class,
        Image::TYPE_NEWS                 => News::class,
        Image::TYPE_NEWSCATEGORY         => NewsCategory::class,
        Image::TYPE_OPC                  => OPC::class,
        Image::TYPE_PRODUCT              => Product::class,
        Image::TYPE_VARIATION            => Variation::class
    ];

    /**
     * @param string $imageType
     * @return string
     */
    public static function getClass(string $imageType): string
    {
        return self::$classMapper[$imageType] ?? Product::class;
    }

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
             ->register(new OPC())
             ->register(new Characteristic())
             ->register(new CharacteristicValue())
             ->register(new ConfigGroup())
             ->register(new Variation())
             ->register(new News())
             ->register(new NewsCategory());
    }

    /**
     * @param IMedia $media
     * @return $this
     */
    public function register(IMedia $media): self
    {
        $this->types[] = $media;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getAllClassNames(): array
    {
        return \array_values(self::$classMapper);
    }

    /**
     * @return IMedia[]
     */
    public function getRegisteredClasses(): array
    {
        return $this->types;
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
        return first($this->types, function (IMedia $type) use ($requestUri) {
            return $type->isValid($requestUri);
        })->handle($requestUri);
    }
}
