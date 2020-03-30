<?php declare(strict_types=1);

namespace JTL\Media\Image;

use Generator;
use JTL\DB\ReturnType;
use JTL\Media\Image;
use JTL\Media\MediaImageRequest;
use JTL\Shop;
use PDO;
use stdClass;

/**
 * Class ConfigGroup
 * @package JTL\Media\Image
 */
class ConfigGroup extends AbstractImage
{
    public const TYPE = Image::TYPE_CONFIGGROUP;

    /**
     * @var string
     */
    protected $regEx = '/^media\/image\/(?P<type>configgroup)' .
    '\/(?P<id>\d+)\/(?P<size>xs|sm|md|lg|xl|os)\/(?P<name>[a-zA-Z0-9\-_]+)' .
    '(?:(?:~(?P<number>\d+))?)\.(?P<ext>jpg|jpeg|png|gif|webp)$/';

    /**
     * @inheritdoc
     */
    public static function getImageStmt(string $type, int $id): ?stdClass
    {
        return (object)[
            'stmt' => 'SELECT cBildpfad, 0 AS number 
                        FROM tkonfiggruppe 
                        WHERE kKonfiggruppe = :cid 
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
            'SELECT a.kKonfiggruppe, t.cName, cBildPfad AS path
                FROM tkonfiggruppe a
                JOIN tkonfiggruppesprache t 
                    ON a.kKonfiggruppe = t.kKonfiggruppe
                JOIN tsprache
                    ON tsprache.kSprache = t.kSprache
                WHERE a.kKonfiggruppe = :cid
                AND tsprache.cShopStandard = \'Y\'',
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
        $result = '';
        if (isset($mixed->cName)) {
            $result = $mixed->cName;
        } elseif (\method_exists($mixed, 'getSprache')) {
            $result = $mixed->getSprache()->getName();
        } elseif (isset($mixed->path)) {
            return \pathinfo($mixed->path)['filename'];
        } elseif (isset($mixed->cBildpfad)) {
            return \pathinfo($mixed->cBildpfad)['filename'];
        }

        return empty($result) ? 'image' : Image::getCleanFilename($result);
    }

    /**
     * @inheritdoc
     */
    public static function getStoragePath(): string
    {
        return \STORAGE_CONFIGGROUPS;
    }

    /**
     * @inheritdoc
     */
    public static function getPathByID($id, int $number = null): ?string
    {
        return Shop::Container()->getDB()->queryPrepared(
            'SELECT cBildpfad AS path 
                    FROM tkonfiggruppe 
                    WHERE kKonfiggruppe = :cid LIMIT 1',
            ['cid' => $id],
            ReturnType::SINGLE_OBJECT
        )->path ?? null;
    }

    /**
     * @inheritdoc
     */
    public static function getAllImages(int $offset = null, int $limit = null): Generator
    {
        $images = Shop::Container()->getDB()->query(
            'SELECT a.kKonfiggruppe AS id, t.cName, cBildPfad AS path
                FROM tkonfiggruppe a
                JOIN tkonfiggruppesprache t 
                    ON a.kKonfiggruppe = t.kKonfiggruppe
                JOIN tsprache
                    ON tsprache.kSprache = t.kSprache
                WHERE tsprache.cShopStandard = \'Y\'
                  AND cBildPfad IS NOT NULL
                  AND cBildPfad != \'\'' . self::getLimitStatement($offset, $limit),
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
}
