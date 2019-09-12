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
 * Class Variation
 * @package JTL\Media\Image
 */
class Variation extends AbstractImage
{
    public const TYPE = Image::TYPE_VARIATION;

    /**
     * @var string
     */
    protected $regEx = '/^media\/image\/(?P<type>variation)' .
    '\/(?P<id>\d+)\/(?P<size>xs|sm|md|lg|xl|os)\/(?P<name>[a-zA-Z0-9\-_]+)' .
    '(?:(?:~(?P<number>\d+))?)\.(?P<ext>jpg|jpeg|png|gif|webp)$/';

    /**
     * @inheritdoc
     */
    public static function getImageStmt(string $type, int $id): ?stdClass
    {
        return (object)[
            'stmt' => 'SELECT kEigenschaftWert, 0 AS number 
                        FROM teigenschaftwertpict 
                        WHERE kEigenschaftWert = :vid',
            'bind' => ['vid' => $id]
        ];
    }

    /**
     * @inheritdoc
     */
    protected function getImageNames(MediaImageRequest $req): array
    {
        $names = Shop::Container()->getDB()->queryPrepared(
            'SELECT p.kEigenschaftWert, p.kEigenschaftWertPict, p.cPfad AS path, t.cName
                FROM teigenschaftwertpict p
                JOIN teigenschaftwert t
                    ON p.kEigenschaftWert = t.kEigenschaftWert
                WHERE p.kEigenschaftWert = :vid',
            ['vid' => $req->id],
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
        $result = $mixed->cName;

        return empty($result) ? 'image' : Image::getCleanFilename($result);
    }

    /**
     * @inheritdoc
     */
    public static function getPathByID($id, int $number = null): ?string
    {
        return Shop::Container()->getDB()->queryPrepared(
            'SELECT cPfad AS path
                FROM teigenschaftwertpict
                WHERE kEigenschaftWert = :vid
                LIMIT 1',
            ['vid' => $id],
            ReturnType::SINGLE_OBJECT
        )->path ?? null;
    }

    /**
     * @inheritdoc
     */
    public static function getStoragePath(): string
    {
        return \STORAGE_VARIATIONS;
    }

    /**
     * @inheritdoc
     */
    public static function getTotalImageCount(): int
    {
        return (int)Shop::Container()->getDB()->query(
            'SELECT COUNT(kEigenschaftWertPict) AS cnt
                FROM teigenschaftwertpict
                WHERE cPfad IS NOT NULL
                    AND cPfad != \'\'',
            ReturnType::SINGLE_OBJECT
        )->cnt;
    }
}
