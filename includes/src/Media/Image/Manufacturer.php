<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Media\Image;

use Generator;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Media\Image;
use JTL\Media\MediaImageRequest;
use JTL\Shop;
use PDO;
use stdClass;

/**
 * Class Manufacturer
 * @package JTL\Media
 */
class Manufacturer extends AbstractImage
{
    public const TYPE = Image::TYPE_MANUFACTURER;

    /**
     * @var string
     */
    protected $regEx = '/^media\/image\/(?P<type>manufacturer)' .
    '\/(?P<id>\d+)\/(?P<size>xs|sm|md|lg|xl|os)\/(?P<name>[a-zA-Z0-9\-_]+)' .
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
        return Shop::Container()->getDB()->queryPrepared(
            'SELECT kHersteller, cName, cSeo, cSeo AS originalSeo, cBildpfad AS path
                FROM thersteller
                WHERE kHersteller = :mid',
            ['mid' => $req->getID()],
            ReturnType::COLLECTION
        )->each(function ($item, $key) use ($req) {
            if ($key === 0 && !empty($item->path)) {
                $req->setSourcePath($item->path);
            }
            $item->imageName = self::getCustomName($item);
        })->pluck('imageName')->toArray();
    }

    /**
     * @inheritdoc
     */
    public static function getCustomName($mixed): string
    {
        $result = $mixed->originalSeo ?? $mixed->cSeo ?? $mixed->cName;

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

    /**
     * @inheritdoc
     */
    public static function getAllImages(int $offset = null, int $limit = null): Generator
    {
        $images = Shop::Container()->getDB()->query(
            'SELECT kHersteller AS id, cName, cSeo, cBildpfad AS path
                FROM thersteller
                WHERE cBildpfad IS NOT NULL AND cBildpfad != \'\'' . self::getLimitStatement($offset, $limit),
            ReturnType::QUERYSINGLE
        );
        while (($image = $images->fetch(PDO::FETCH_OBJ)) !== false) {
            yield MediaImageRequest::create([
                'id'         => $image->id,
                'type'       => self::TYPE,
                'name'       => self::getCustomName($image),
                'number'     => 1,
                'path'       => $image->path,
                'sourcePath' => $image->path,
                'ext'        => static::getFileExtension($image->path)
            ]);
        }
    }

    /**
     * @inheritdoc
     */
    public static function getTotalImageCount(): int
    {
        return (int)Shop::Container()->getDB()->query(
            'SELECT COUNT(kHersteller) AS cnt
                FROM thersteller
                WHERE cBildpfad IS NOT NULL AND cBildpfad != \'\'',
            ReturnType::SINGLE_OBJECT
        )->cnt;
    }

    /**
     * @inheritdoc
     */
    public static function imageIsUsed(DbInterface $db, string $path): bool
    {
        return $db->select('thersteller', 'cBildpfad', $path) !== null;
    }
}
