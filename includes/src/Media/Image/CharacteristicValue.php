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
 * Class CharacteristicValueImage
 * @package JTL\Media
 */
class CharacteristicValue extends AbstractImage
{
    public const TYPE = Image::TYPE_CHARACTERISTIC_VALUE;

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
    public static function getImageNames(MediaImageRequest $req): array
    {
        return Shop::Container()->getDB()->queryPrepared(
            'SELECT a.kMerkmalWert, a.kMerkmalWert AS id, a.cBildpfad AS path, t.cWert AS val, t.cSeo AS seoPath
                FROM tmerkmalwert AS a
                JOIN tmerkmalwertsprache t
                    ON a.kMerkmalWert = t.kMerkmalWert
                JOIN tsprache
                    ON tsprache.kSprache = t.kSprache
                WHERE a.kMerkmalWert = :cid
                    AND tsprache.cShopStandard = \'Y\'',
            ['cid' => $req->getID()],
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
        switch (Image::getSettings()['naming'][Image::TYPE_CHARACTERISTIC_VALUE]) {
            case 2:
                $result = $mixed->path ?? $mixed->cBildpfad ?? $mixed->currentImagePath ?? null;
                if ($result !== null) {
                    return \pathinfo($result)['filename'];
                }
            case 1:
                $result = $mixed->seoPath ?? $mixed->val ?? null;
                if ($result === null && !empty($mixed->currentImagePath)) {
                    return \pathinfo($mixed->currentImagePath)['filename'];
                }
                break;
            case 0:
            default:
                $result = $mixed->id ?? $mixed->kMerkmalWert ?? null;
                break;
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
                FROM tmerkmalwert
                WHERE kMerkmalWert = :cid LIMIT 1',
            ['cid' => $id],
            ReturnType::SINGLE_OBJECT
        )->path ?? null;
    }

    /**
     * @inheritdoc
     */
    public static function getStoragePath(): string
    {
        return \STORAGE_CHARACTERISTIC_VALUES;
    }

    /**
     * @inheritdoc
     */
    public static function getAllImages(int $offset = null, int $limit = null): Generator
    {
        $images = Shop::Container()->getDB()->query(
            'SELECT A.cBildpfad AS path, A.kMerkmalwert, A.kMerkmalwert AS id, B.cWert AS val, B.cSeo AS seoPath
                FROM tmerkmalwert A
                JOIN tmerkmalwertsprache B
                    ON A.kMerkmalWert = B.kMerkmalWert
                JOIN tsprache
                    ON tsprache.kSprache = B.kSprache
                WHERE cBildpfad IS NOT NULL
                    AND cBildpfad != \'\'
                    AND tsprache.cShopStandard = \'Y\'
                GROUP BY path, id' . self::getLimitStatement($offset, $limit),
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
            'SELECT COUNT(kMerkmalWert) AS cnt
                FROM tmerkmalwert
                WHERE cBildpfad IS NOT NULL
                    AND cBildpfad != \'\'',
            ReturnType::SINGLE_OBJECT
        )->cnt;
    }
}
