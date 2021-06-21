<?php declare(strict_types=1);

namespace JTL\Media\GarbageCollection;

use JTL\Media\GarbageCollection\Exception\MappingNotFoundException;
use JTL\Media\Image;

/**
 * Class ImageTypeToCollectorMapper
 * @package JTL\Media\GarbageCollection
 */
class ImageTypeToCollectorMapper
{
    /**
     * @var array
     */
    private static $classMapper = [
        Image::TYPE_CATEGORY             => CategoryImages::class,
        Image::TYPE_CHARACTERISTIC       => CharacteristicsImages::class,
        Image::TYPE_CHARACTERISTIC_VALUE => CharacteristicValuesImages::class,
        Image::TYPE_CONFIGGROUP          => ConfigGroupImages::class,
        Image::TYPE_MANUFACTURER         => ManufacturerImages::class,
//        Image::TYPE_NEWS                 => News::class,
//        Image::TYPE_NEWSCATEGORY         => NewsCategory::class,
//        Image::TYPE_OPC                  => OPC::class,
        Image::TYPE_PRODUCT              => ProductImages::class,
        Image::TYPE_VARIATION            => VariationImages::class
    ];

    /**
     * @param string $imageType
     * @return string
     */
    public static function getMapping(string $imageType): string
    {
        if (!isset(self::$classMapper[$imageType])) {
            throw new MappingNotFoundException('No mapping for ' . $imageType);
        }

        return self::$classMapper[$imageType];
    }
}
