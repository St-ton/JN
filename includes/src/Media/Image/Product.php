<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Media\Image;

use Exception;
use FilesystemIterator;
use Generator;
use InvalidArgumentException;
use JTL\DB\ReturnType;
use JTL\Media\Image;
use JTL\Media\IMedia;
use JTL\Media\MediaImageRequest;
use JTL\Shop;
use PDO;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use stdClass;
use function Functional\select;

/**
 * Class Product
 * @package JTL\Media\Image
 */
class Product extends AbstractImage implements IMedia
{
    protected $regEx = \MEDIAIMAGE_REGEX;

    /**
     * @param string $type
     * @param bool   $filesize
     * @return stdClass
     * @throws Exception
     */
    public static function getStats($type, bool $filesize = false): stdClass
    {
        $result = (object)[
            'total'         => 0,
            'corrupted'     => 0,
            'fallback'      => 0,
            'generated'     => [
                Image::SIZE_ORIGINAL => 0,
                Image::SIZE_XS       => 0,
                Image::SIZE_SM       => 0,
                Image::SIZE_MD       => 0,
                Image::SIZE_LG       => 0,
            ],
            'totalSize'     => 0,
            'generatedSize' => [
                Image::SIZE_ORIGINAL => 0,
                Image::SIZE_XS       => 0,
                Image::SIZE_SM       => 0,
                Image::SIZE_MD       => 0,
                Image::SIZE_LG       => 0,
            ],
        ];
        foreach (self::getProductImages() as $image) {
            $raw = $image->getRaw(true);
            ++$result->total;
            if (\file_exists($raw)) {
                foreach (Image::getAllSizes() as $size) {
                    $thumb = $image->getThumb($size, true);
                    if (!\file_exists($thumb)) {
                        continue;
                    }
                    ++$result->generated[$size];
                    if ($filesize === true) {
                        $result->generatedSize[$size] = \filesize($thumb);
                        $result->totalSize           += $result->generatedSize[$size];
                    }
                }
            } elseif (\file_exists(\PFAD_ROOT . $image->getFallbackThumb(Image::SIZE_XS))) {
                ++$result->fallback;
            } else {
                ++$result->corrupted;
            }
        }

        return $result;
    }

    /**
     * @param string   $type
     * @param null|int $id
     */
    public static function clearCache($type, $id = null): void
    {
        $directory = \PFAD_ROOT . MediaImageRequest::getCachePath($type);
        if ($id !== null) {
            $directory .= '/' . (int)$id;
        }

        try {
            $rdi = new RecursiveDirectoryIterator(
                $directory,
                FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS
            );
            foreach (new RecursiveIteratorIterator($rdi, RecursiveIteratorIterator::CHILD_FIRST) as $value) {
                $value->isFile()
                    ? \unlink($value)
                    : \rmdir($value);
            }

            if ($id !== null) {
                \rmdir($directory);
            }
        } catch (Exception $e) {
        }
    }

    /**
     * @param string $imageUrl
     * @return MediaImageRequest
     */
    public static function toRequest($imageUrl): MediaImageRequest
    {
        $self = new self();

        return $self->create($imageUrl);
    }

    /**
     * @inheritdoc
     */
    protected function getImageNames(MediaImageRequest $req): array
    {
        return Shop::Container()->getDB()->queryPrepared(
            'SELECT kArtikel, cName, cSeo, cArtNr, cBarcode
            FROM tartikel AS a
            WHERE kArtikel = :pid
            UNION SELECT asp.kArtikel, asp.cName, asp.cSeo, a.cArtNr, a.cBarcode
                FROM tartikelsprache AS asp JOIN tartikel AS a ON asp.kArtikel = a.kArtikel
                WHERE asp.kArtikel = :pid',
            ['pid' => $req->id],
            ReturnType::ARRAY_OF_OBJECTS
        );
    }

    /**
     * @param MediaImageRequest $req
     * @param bool              $overwrite
     * @return array
     */
    public static function cacheImage(MediaImageRequest $req, bool $overwrite = false): array
    {
        $result   = [];
        $rawImage = null;
        $rawPath  = $req->getRaw(true);
        if ($overwrite === true) {
            self::clearCache($req->getType(), $req->getID());
        }

        foreach (Image::getAllSizes() as $size) {
            $res = (object)[
                'success'    => true,
                'error'      => null,
                'renderTime' => 0,
                'cached'     => false,
            ];
            try {
                $req->size   = $size;
                $thumbPath   = $req->getThumb(null, true);
                $res->cached = \is_file($thumbPath);
                if ($res->cached === false) {
                    $renderStart = \microtime(true);
                    if ($rawImage === null && !\is_file($rawPath)) {
                        throw new Exception(\sprintf('Image source "%s" does not exist', $rawPath));
                    }
                    Image::render($req);
                    $res->renderTime = (\microtime(true) - $renderStart) * 1000;
                }
            } catch (Exception $e) {
                $res->success = false;
                $res->error   = $e->getMessage();
            }
            $result[$size] = $res;
        }

        if ($rawImage !== null) {
            unset($rawImage);
        }

        return $result;
    }

    /**
     * @return int
     */
    public static function getProductImageCount(): int
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
     * @return int
     */
    public static function getUncachedProductImageCount(): int
    {
        return \count(select(self::getProductImages(), function (MediaImageRequest $e) {
            return !self::isCached($e) && \file_exists($e->getRaw(true));
        }));
    }

    /**
     * @return Generator
     */
    public static function getProductImages(): Generator
    {
        $cols = '';
        switch (Image::getSettings()['naming']['product']) {
            case 1:
                $cols = ', tartikel.cArtNr';
                break;
            case 2:
                $cols = ', tartikel.cSeo, tartikel.cName';
                break;
            case 3:
                $cols = ', tartikel.cArtNr, tartikel.cSeo, tartikel.cName';
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
                  ON tartikelpict.kArtikel = tartikel.kArtikel',
            ReturnType::QUERYSINGLE
        );

        while (($image = $images->fetch(PDO::FETCH_OBJ)) !== false) {
            yield MediaImageRequest::create([
                'id'     => $image->kArtikel,
                'type'   => Image::TYPE_PRODUCT,
                'name'   => self::getCustomName($image),
                'number' => $image->number,
                'path'   => $image->path,
            ]);
        }
    }

    /**
     * @param string   $type
     * @param bool     $notCached
     * @param int|null $offset
     * @param int|null $limit
     * @return MediaImageRequest[]
     * @throws Exception
     */
    public static function getImages($type, bool $notCached = false, int $offset = null, int $limit = null): array
    {
        $requests = [];
        // only select the necessary columns to save memory
        $cols = '';
        $conf = Image::getSettings();
        switch ($conf['naming']['product']) {
            case 1:
                $cols = ', tartikel.cArtNr';
                break;
            case 2:
                $cols = ', tartikel.cSeo, tartikel.cName';
                break;
            case 3:
                $cols = ', tartikel.cArtNr, tartikel.cSeo, tartikel.cName';
                break;
            case 4:
                $cols = ', tartikel.cBarcode';
                break;
            case 0:
            default:
                break;
        }
        $limitStmt = '';
        if ($limit !== null) {
            $limitStmt = ' LIMIT ';
            if ($offset !== null) {
                $limitStmt .= (int)$offset . ', ';
            }
            $limitStmt .= (int)$limit;
        }
        $images = Shop::Container()->getDB()->query(
            'SELECT tartikelpict.cPfad AS path, tartikelpict.nNr AS number, tartikelpict.kArtikel ' . $cols . '
                FROM tartikelpict
                INNER JOIN tartikel
                  ON tartikelpict.kArtikel = tartikel.kArtikel' . $limitStmt,
            ReturnType::QUERYSINGLE
        );

        while (($image = $images->fetch(PDO::FETCH_OBJ)) !== false) {
            $req = MediaImageRequest::create([
                'id'     => $image->kArtikel,
                'type'   => $type,
                'name'   => self::getCustomName($image),
                'number' => $image->number,
                'path'   => $image->path,
            ]);

            if ($notCached && self::isCached($req)) {
                continue;
            }

            $requests[] = $req;
        }

        return $requests;
    }

    /**
     * @param MediaImageRequest $req
     * @return bool
     */
    public static function isCached(MediaImageRequest $req): bool
    {
        return \file_exists($req->getThumb(Image::SIZE_XS, true))
            && \file_exists($req->getThumb(Image::SIZE_SM, true))
            && \file_exists($req->getThumb(Image::SIZE_MD, true))
            && \file_exists($req->getThumb(Image::SIZE_LG, true));
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
     * @param string $type
     * @param int    $id
     * @return int
     */
    public static function imageCount($type, int $id): int
    {
        if (($prepared = static::getImageStmt($type, $id)) === null) {
            return 0;
        }
        $imageCount = Shop::Container()->getDB()->queryPrepared(
            $prepared->stmt,
            $prepared->bind,
            ReturnType::AFFECTED_ROWS
        );

        return \is_numeric($imageCount) ? (int)$imageCount : 0;
    }

    /**
     * @inheritdoc
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
                $result = empty($mixed->cSeo) ? $mixed->cName : $mixed->cSeo;
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
}
