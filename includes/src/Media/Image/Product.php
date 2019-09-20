<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Media\Image;

use Generator;
use JTL\Catalog\Product\Artikel;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Media\Image;
use JTL\Media\MediaImageRequest;
use JTL\Shop;
use PDO;
use stdClass;

/**
 * Class Product
 * @package JTL\Media\Image
 */
class Product extends AbstractImage
{
    public const TYPE = Image::TYPE_PRODUCT;

    /**
     * @var string
     */
    protected $regEx = \MEDIAIMAGE_REGEX;

    /**
     * @inheritdoc
     */
    protected function getImageNames(MediaImageRequest $req): array
    {
        return Shop::Container()->getDB()->queryPrepared(
            'SELECT kArtikel, cName, cSeo, cSeo AS originalSeo, cArtNr, cBarcode
                FROM tartikel
                WHERE kArtikel = :pid',
            ['pid' => $req->getID()],
            ReturnType::COLLECTION
        )->map(function ($item) {
            return self::getCustomName($item);
        })->toArray();
    }

    /**
     * @inheritdoc
     */
    public static function getTotalImageCount(): int
    {
        return (int)Shop::Container()->getDB()->query(
            'SELECT COUNT(tartikelpict.kArtikel) AS cnt
                FROM tartikelpict
                INNER JOIN tartikel
                    ON tartikelpict.kArtikel = tartikel.kArtikel',
            ReturnType::SINGLE_OBJECT
        )->cnt;
    }

    /**
     * @inheritdoc
     */
    public static function getAllImages(int $offset = null, int $limit = null): Generator
    {
        $cols = '';
        switch (Image::getSettings()['naming']['product']) {
            case 1:
                $cols = ', tartikel.cArtNr';
                break;
            case 2:
                $cols = ', tartikel.cSeo, tartikel.cSeo AS originalSeo, tartikel.cName';
                break;
            case 3:
                $cols = ', tartikel.cArtNr, tartikel.cSeo, tartikel.cSeo AS originalSeo, tartikel.cName';
                break;
            case 4:
                $cols = ', tartikel.cBarcode';
                break;
            case 0:
            default:
                break;
        }
        $images = Shop::Container()->getDB()->query(
            'SELECT tartikelpict.cPfad AS path, tartikelpict.nNr AS number, tartikelpict.kArtikel ' . $cols . '
                FROM tartikelpict
                INNER JOIN tartikel
                  ON tartikelpict.kArtikel = tartikel.kArtikel' . self::getLimitStatement($offset, $limit),
            ReturnType::QUERYSINGLE
        );
        while (($image = $images->fetch(PDO::FETCH_OBJ)) !== false) {
            yield MediaImageRequest::create([
                'id'         => $image->kArtikel,
                'type'       => self::TYPE,
                'name'       => self::getCustomName($image),
                'number'     => $image->number,
                'path'       => $image->path,
                'sourcePath' => $image->path,
                'ext'        => static::getFileExtension($image->path)
            ]);
        }
    }

    /**
     * @param Artikel $mixed
     * @return string
     */
    public static function getCustomName($mixed): string
    {
        switch (Image::getSettings()['naming']['product']) {
            case 0:
                $result = $mixed->kArtikel;
                break;
            case 1:
                $result = $mixed->cArtNr;
                break;
            case 2:
                $result = $mixed->originalSeo ?? $mixed->cSeo ?? $mixed->cName;
                break;
            case 3:
                $result = \sprintf('%s_%s', $mixed->cArtNr, empty($mixed->cSeo) ? $mixed->cName : $mixed->cSeo);
                break;
            case 4:
                $result = $mixed->cBarcode;
                break;
            default:
                $result = 'image';
                break;
        }

        return empty($result) ? 'image' : Image::getCleanFilename($result);
    }

    /**
     * @inheritdoc
     */
    public static function getPathByID($id, int $number = null): ?string
    {
        return Shop::Container()->getDB()->queryPrepared(
            'SELECT cPfad AS path
                FROM tartikelpict
                WHERE kArtikel = :pid
                    AND nNr = :no
                ORDER BY nNr
                LIMIT 1',
            ['pid' => $id, 'no' => $number],
            ReturnType::SINGLE_OBJECT
        )->path ?? null;
    }

    /**
     * @inheritdoc
     */
    public static function getStoragePath(): string
    {
        return \PFAD_MEDIA_IMAGE_STORAGE;
    }

    /**
     * @param string $type
     * @param int    $id
     * @return int|null
     */
    public static function getPrimaryNumber(string $type, int $id): ?int
    {
        $prepared = self::getImageStmt($type, $id);
        if ($prepared !== null) {
            $primary = Shop::Container()->getDB()->queryPrepared(
                $prepared->stmt,
                $prepared->bind,
                ReturnType::SINGLE_OBJECT
            );
            if (\is_object($primary)) {
                return \max(1, (int)$primary->number);
            }
        }

        return null;
    }

    /**
     * @param string $type
     * @param int    $id
     * @return stdClass|null
     */
    public static function getImageStmt(string $type, int $id): ?stdClass
    {
        return (object)[
            'stmt' => 'SELECT kArtikel, nNr AS number
                FROM tartikelpict 
                WHERE kArtikel = :kArtikel 
                GROUP BY cPfad 
                ORDER BY nNr ASC',
            'bind' => ['kArtikel' => $id]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function imageIsUsed(DbInterface $db, string $path): bool
    {
        return $db->select('tartikelpict', 'cPfad', $path) !== null;
    }
}
