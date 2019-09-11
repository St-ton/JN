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
 * Class Category
 * @package JTL\Media\Image
 */
class Category extends Product
{
    /**
     * @var string
     */
    protected $regEx = '/^media\/image\/(?P<type>category)' .
    '\/(?P<id>\d+)\/(?P<size>xs|sm|md|lg|os)\/(?P<name>[a-zA-Z0-9\-_]+)' .
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
    protected function getImageNames(MediaImageRequest $req): array
    {
        return Shop::Container()->getDB()->queryPrepared(
            'SELECT kKategorie, cName, cSeo
                FROM tkategorie AS a
                WHERE kKategorie = :cid
                UNION SELECT asp.kKategorie, asp.cName, asp.cSeo
                    FROM tkategoriesprache AS asp JOIN tkategorie AS a ON asp.kKategorie = a.kKategorie
                    WHERE asp.kKategorie = :cid',
            ['cid' => $req->id],
            ReturnType::ARRAY_OF_OBJECTS
        );
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
            'SELECT pic.cPfad AS path, pic.kKategorie, pic.kKategorie AS id, cat.cName, cat.cSeo
                FROM tkategorie cat
                JOIN tkategoriepict pic
                    ON cat.kKategorie = pic.kKategorie' . self::getLimitStatement($offset, $limit),
            ReturnType::QUERYSINGLE
        );
        while (($image = $images->fetch(PDO::FETCH_OBJ)) !== false) {
            yield MediaImageRequest::create([
                'id'     => $image->id,
                'type'   => Image::TYPE_CATEGORY,
                'name'   => self::getCustomName($image),
                'number' => 1,
                'path'   => $image->path,
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
}
