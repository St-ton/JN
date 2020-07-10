<?php declare(strict_types=1);

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
     * @var array
     */
    private static $classMapper = [
        Image::TYPE_PRODUCT              => Product::class,
        Image::TYPE_CATEGORY             => Category::class,
        Image::TYPE_CHARACTERISTIC       => Characteristic::class,
        Image::TYPE_CHARACTERISTIC_VALUE => CharacteristicValue::class,
        Image::TYPE_CONFIGGROUP          => ConfigGroup::class,
        Image::TYPE_MANUFACTURER         => Manufacturer::class,
        Image::TYPE_NEWS                 => News::class,
        Image::TYPE_NEWSCATEGORY         => NewsCategory::class,
        Image::TYPE_OPC                  => OPC::class,
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
    }

    /**
     * @param string $type
     * @param string $class
     * @return $this
     */
    public function register(string $type, string $class): self
    {
        self::$classMapper[$type] = $class;

        return $this;
    }

    /**
     * @return IMedia[]
     */
    public function getRegisteredClasses(): array
    {
        return \array_values(self::$classMapper);
    }

    /**
     * @return IMedia[]
     */
    public function getRegisteredTypes(): array
    {
        return \array_keys(self::$classMapper);
    }

    /**
     * @param string $requestUri
     * @return bool
     */
    public function isValidRequest(string $requestUri): bool
    {
        return some(self::$classMapper, static function (string $class) use ($requestUri) {
            /** @var IMedia $class */
            return $class::isValid($requestUri);
        });
    }

    /**
     * @param string $requestUri
     * @return bool|mixed
     * @throws Exception
     */
    public function handleRequest(string $requestUri)
    {
        return first(self::$classMapper, static function (string $class) use ($requestUri) {
            /** @var IMedia $class */
            return $class::isValid($requestUri);
        })->handle($requestUri);
    }
}
