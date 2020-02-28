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
 * Class Category
 * @package JTL\Media\Image
 */
class Category extends AbstractImage
{
    public const TYPE = Image::TYPE_CATEGORY;

    /**
     * @var string
     */
    protected $regEx = '/^media\/image\/(?P<type>category)' .
    '\/(?P<id>\d+)\/(?P<size>xs|sm|md|lg|xl|os)\/(?P<name>[a-zA-Z0-9\-_]+)' .
    '(?:(?:~(?P<number>\d+))?)\.(?P<ext>jpg|jpeg|png|gif|webp)$/';

    /**
     * @inheritdoc
     */
    public static function getImageStmt(string $type, int $id): ?stdClass
    {
        return (object)[
            'stmt' => 'SELECT kKategorie, 0 AS number 
                          FROM tkategoriepict 
                          WHERE kKategorie = :kKategorie',
            'bind' => ['kKategorie' => $id]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getImageNames(MediaImageRequest $req): array
    {
        return Shop::Container()->getDB()->queryPrepared(
            'SELECT pic.cPfad AS path, pic.kKategorie, pic.kKategorie AS id, cat.cName, cat.cSeo AS seoPath
                FROM tkategorie cat
                JOIN tkategoriepict pic
                    ON cat.kKategorie = pic.kKategorie
                WHERE pic.kKategorie = :cid',
            ['cid' => $req->getID()],
            ReturnType::COLLECTION
        )->map(static function ($item) {
            return self::getCustomName($item);
        })->toArray();
    }

    /**
     * @inheritdoc
     */
    public static function getCustomName($mixed): string
    {
        if (\is_string($mixed)) {
            return \pathinfo($mixed)['filename'];
        }
        if (isset($mixed->currentImagePath)) {
            return \pathinfo($mixed->currentImagePath)['filename'];
        }
        switch (Image::getSettings()['naming'][Image::TYPE_CATEGORY]) {
            case 2:
                $result = $mixed->path ?? $mixed->cBildpfad ?? null;
                if ($result !== null) {
                    return \pathinfo($result)['filename'];
                }
                break;
            case 1:
                $result = \method_exists($mixed, 'getURL')
                    ? $mixed->getURL()
                    : ($mixed->originalSeo ?? $mixed->seoPath ?? $mixed->cName ?? null);
                break;
            case 0:
            default:
                $result = \method_exists($mixed, 'getID')
                    ? $mixed->getID()
                    : ($mixed->id ?? $mixed->kKategorie ?? null);
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
            'SELECT cPfad AS path
                FROM tkategoriepict
                WHERE kKategorie = :cid LIMIT 1',
            ['cid' => $id],
            ReturnType::SINGLE_OBJECT
        )->path ?? null;
    }

    /**
     * @inheritdoc
     */
    public static function getStoragePath(): string
    {
        return \STORAGE_CATEGORIES;
    }

    /**
     * @inheritdoc
     */
    public static function getAllImages(int $offset = null, int $limit = null): Generator
    {
        $images = Shop::Container()->getDB()->query(
            'SELECT pic.cPfad AS path, pic.kKategorie, pic.kKategorie AS id, cat.cName, cat.cSeo AS seoPath
                FROM tkategorie cat
                JOIN tkategoriepict pic
                    ON cat.kKategorie = pic.kKategorie' . self::getLimitStatement($offset, $limit),
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
            'SELECT COUNT(tkategoriepict.kKategorie) AS cnt
                FROM tkategoriepict
                INNER JOIN tkategorie
                    ON tkategorie.kKategorie = tkategoriepict.kKategorie',
            ReturnType::SINGLE_OBJECT
        )->cnt;
    }

    /**
     * @inheritdoc
     */
    public static function imageIsUsed(DbInterface $db, string $path): bool
    {
        return $db->select('tkategoriepict', 'cPfad', $path) !== null;
    }
}