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
 * Class CharacteristicValueImage
 * @package JTL\Media
 */
class CharacteristicValue extends Product
{
    /**
     * @var string
     */
    protected $regEx = '/^media\/image\/(?P<type>characteristicvalue)' .
    '\/(?P<id>\d+)\/(?P<size>xs|sm|md|lg|xl|os)\/(?P<name>[a-zA-Z0-9\-_]+)' .
    '(?:(?:~(?P<number>\d+))?)\.(?P<ext>jpg|jpeg|png|gif|webp)$/';

    /**
     * @inheritdoc
     */
    public static function getImageStmt(string $type, int $id): ?stdClass
    {
        return (object)[
            'stmt' => 'SELECT cBildpfad, 0 AS number 
                        FROM tmerkmalwert 
                        WHERE kMerkmalWert = :kMerkmalWert 
                        ORDER BY nSort ASC',
            'bind' => ['kMerkmalWert' => $id]
        ];
    }

    /**
     * @inheritdoc
     */
    protected function getImageNames(MediaImageRequest $req): array
    {
        $names = Shop::Container()->getDB()->queryPrepared(
            'SELECT a.kMerkmalWert, a.cBildpfad AS path, t.cWert
                FROM tmerkmalwert AS a
                LEFT JOIN tmerkmalwertsprache t
                    ON a.kMerkmalWert = t.kMerkmalWert
                WHERE a.kMerkmalWert = :cid',
            ['cid' => $req->id],
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
        $result = empty($mixed->cSeo) ? $mixed->cWert : $mixed->cSeo;

        return empty($result) ? 'image' : Image::getCleanFilename($result);
    }

    /**
     * @inheritdoc
     */
    public static function getPathByID($id, int $number = null): ?string
    {
        // todo
        return null;
    }

    /**
     * @inheritdoc
     */
    public static function getStoragePath(): string
    {
        return \STORAGE_CHARACTERISTIC_VALUES;
    }
}
