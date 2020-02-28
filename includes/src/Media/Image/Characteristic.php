<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Media\Image;

use Generator;
use JTL\DB\ReturnType;
use JTL\Media\Image;
use JTL\Media\MediaImageRequest;
use JTL\Shop;
use PDO;
use stdClass;

/**
 * Class Characteristic
 * @package JTL\Media\Image
 */
class Characteristic extends AbstractImage
{
    public const TYPE = Image::TYPE_CHARACTERISTIC;

    /**
     * @var string
     */
    protected $regEx = '/^media\/image\/(?P<type>characteristic)' .
    '\/(?P<id>\d+)\/(?P<size>xs|sm|md|lg|xl|os)\/(?P<name>[a-zA-Z0-9\-_]+)' .
    '(?:(?:~(?P<number>\d+))?)\.(?P<ext>jpg|jpeg|png|gif|webp)$/';

    /**
     * @inheritdoc
     */
    public static function getImageStmt(string $type, int $id): ?stdClass
    {
        return (object)[
            'stmt' => 'SELECT cBildpfad, 0 AS number 
                           FROM tmerkmal 
                           WHERE kMerkmal = :cid 
                           ORDER BY nSort ASC',
            'bind' => ['cid' => $id]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getImageNames(MediaImageRequest $req): array
    {
        return Shop::Container()->getDB()->queryPrepared(
            'SELECT a.kMerkmal, a.cBildpfad AS path, t.cName
                FROM tmerkmal AS a
                JOIN tmerkmalsprache t
                    ON a.kMerkmal = t.kMerkmal
                JOIN tsprache
                    ON tsprache.kSprache = t.kSprache
                WHERE a.kMerkmal = :cid
                    AND tsprache.cShopStandard = \'Y\'',
            ['cid' => $req->getID()],
            ReturnType::COLLECTION
        )->each(static function ($item, $key) use ($req) {
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
        switch (Image::getSettings()['naming'][Image::TYPE_CHARACTERISTIC]) {
            case 2:
                $result = $mixed->path ?? $mixed->cBildpfad ?? null;
                if ($result !== null) {
                    return \pathinfo($result)['filename'];
                }
                break;
            case 1:
                $result = $mixed->cName ?? null;
                break;
            case 0:
            default:
                $result = $mixed->id ?? $mixed->kMerkmal ?? null;
                break;
        }
        if ($result === null && $mixed->currentImagePath !== null) {
            return \pathinfo($mixed->currentImagePath)['filename'];
        }

        return empty($result) ? 'image' : Image::getCleanFilename((string)$result);
    }

    /**
     * @inheritdoc
     */
    public static function getPathByID($id, int $number = null): ?string
    {
        return Shop::Container()->getDB()->queryPrepared(
            'SELECT cBildpfad AS path
                FROM tmerkmal
                WHERE kMerkmal = :cid LIMIT 1',
            ['cid' => $id],
            ReturnType::SINGLE_OBJECT
        )->path ?? null;
    }

    /**
     * @inheritdoc
     */
    public static function getStoragePath(): string
    {
        return \STORAGE_CHARACTERISTICS;
    }

    /**
     * @inheritdoc
     */
    public static function getAllImages(int $offset = null, int $limit = null): Generator
    {
        $images = Shop::Container()->getDB()->query(
            'SELECT cBildpfad AS path, kMerkmal, kMerkmal AS id, cName
                FROM tmerkmal
                WHERE cBildpfad IS NOT NULL
                    AND cBildpfad != \'\'' . self::getLimitStatement($offset, $limit),
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
            'SELECT COUNT(kMerkmal) AS cnt
                FROM tmerkmal
                WHERE cBildpfad IS NOT NULL
                    AND cBildpfad != \'\'',
            ReturnType::SINGLE_OBJECT
        )->cnt;
    }
}