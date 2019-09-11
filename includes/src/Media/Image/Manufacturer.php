<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Media\Image;

use JTL\DB\ReturnType;
use JTL\Media\Image;
use JTL\Media\MediaImageRequest;
use JTL\Shop;
use stdClass;

/**
 * Class Manufacturer
 * @package JTL\Media
 */
class Manufacturer extends Product
{
    /**
     * @var string
     */
    protected $regEx = '/^media\/image\/(?P<type>manufacturer)' .
    '\/(?P<id>\d+)\/(?P<size>xs|sm|md|lg|os)\/(?P<name>[a-zA-Z0-9\-_]+)' .
    '(?:(?:~(?P<number>\d+))?)\.(?P<ext>jpg|jpeg|png|gif|webp)$/';

    /**
     * @inheritdoc
     */
    public static function getImageStmt(string $type, int $id): ?stdClass
    {
        return (object)[
            'stmt' => 'SELECT cBildpfad, 0 AS number 
                                  FROM thersteller 
                                  WHERE kHersteller = :kHersteller',
            'bind' => ['kHersteller' => $id]
        ];
    }

    /**
     * @inheritdoc
     */
    protected function getImageNames(MediaImageRequest $req): array
    {
        $names = Shop::Container()->getDB()->queryPrepared(
            'SELECT kHersteller, cName, cSeo, cBildpfad AS path
                    FROM thersteller
                    WHERE kHersteller = :mid',
            ['mid' => $req->id],
            ReturnType::ARRAY_OF_OBJECTS
        );
        if (!empty($names[0]->path)) {
            $req->sourcePath = $names[0]->path;
        }

        return $names;
    }

    /**
     * @inheritdoc
     */
    public static function getCustomName($mixed): string
    {
        $result = empty($mixed->cSeo) ? $mixed->cName : $mixed->cSeo;

        return empty($result) ? 'image' : Image::getCleanFilename($result);
    }

    /**
     * @inheritdoc
     */
    public static function getPathByID($id, int $number = null): ?string
    {
        return Shop::Container()->getDB()->queryPrepared(
            'SELECT cBildpfad AS path
                FROM thersteller
                WHERE kHersteller = :mid LIMIT 1',
            ['mid' => $id],
            ReturnType::SINGLE_OBJECT
        )->path ?? null;
    }

    /**
     * @inheritdoc
     */
    public static function getStoragePath(): string
    {
        return \STORAGE_MANUFACTURERS;
    }
}
