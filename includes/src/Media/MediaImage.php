<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Media;

use Exception;
use FilesystemIterator;
use Generator;
use InvalidArgumentException;
use JTL\DB\ReturnType;
use JTL\Shop;
use PDO;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use stdClass;
use function Functional\select;

/**
 * Class MediaImage
 * @package JTL\Media
 */
class MediaImage implements IMedia
{
    /**
     * MediaImage constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param string|null $sourcePath
     * @return string
     */
    private static function getFileExtension(string $sourcePath = null): string
    {
        $config = Image::getSettings()['format'];

        return $config === 'auto'
            ? \pathinfo($sourcePath)['extension'] ?? 'jpg'
            : $config;
    }

    /**
     * @param string $type
     * @param string $id
     * @param object $mixed
     * @param string $size
     * @param int    $number
     * @return MediaImageRequest
     */
    public static function getRequest($type, $id, $mixed, $size, int $number = 1): MediaImageRequest
    {
        return MediaImageRequest::create([
            'id'     => $id,
            'type'   => $type,
            'number' => $number,
            'name'   => Image::getCustomName($type, $mixed),
            'size'   => $size,
        ]);
    }

    /**
     * @param string $type
     * @param string $id
     * @param object $mixed
     * @param string $size
     * @param int    $number
     * @param string|null $sourcePath
     * @return string
     */
    public static function getThumb($type, $id, $mixed, $size, int $number = 1, string $sourcePath = null): string
    {
        $req   = MediaImageRequest::create([
            'id'     => $id,
            'type'   => $type,
            'number' => $number,
            'name'   => Image::getCustomName($type, $mixed),
            'ext'    => self::getFileExtension($sourcePath),
        ]);
        $thumb = $req->getThumb($size);
        if (!\file_exists(\PFAD_ROOT . $thumb) && !\file_exists(\PFAD_ROOT . $req->getRaw())) {
            $fallback = $req->getFallbackThumb($size);
            $thumb    = \file_exists(\PFAD_ROOT . $fallback)
                ? $fallback
                : \BILD_KEIN_ARTIKELBILD_VORHANDEN;
        }

        return $thumb;
    }

    /**
     * @param string $type
     * @param string $id
     * @param string $size
     * @param int    $number
     * @return string
     */
    public static function getThumbUrl($type, $id, $size, int $number = 1): string
    {
        return MediaImageRequest::create([
            'type'   => $type,
            'id'     => $id,
            'number' => $number,
        ])->getThumbUrl($size);
    }

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
                Image::SIZE_XS => 0,
                Image::SIZE_SM => 0,
                Image::SIZE_MD => 0,
                Image::SIZE_LG => 0,
            ],
            'totalSize'     => 0,
            'generatedSize' => [
                Image::SIZE_XS => 0,
                Image::SIZE_SM => 0,
                Image::SIZE_MD => 0,
                Image::SIZE_LG => 0,
            ],
        ];
        foreach (self::getProductImages() as $image) {
            $raw = $image->getRaw(true);
            ++$result->total;
            if (!\file_exists($raw)) {
                if (\file_exists(\PFAD_ROOT . $image->getFallbackThumb(Image::SIZE_XS))) {
                    ++$result->fallback;
                } else {
                    ++$result->corrupted;
                }
            } else {
                foreach ([Image::SIZE_XS, Image::SIZE_SM, Image::SIZE_MD, Image::SIZE_LG] as $size) {
                    $thumb = $image->getThumb($size, true);
                    if (\file_exists($thumb)) {
                        ++$result->generated[$size];
                        if ($filesize === true) {
                            $result->generatedSize[$size] = \filesize($thumb);
                            $result->totalSize           += $result->generatedSize[$size];
                        }
                    }
                }
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
     * @param string $request
     * @return bool
     */
    public function isValid(string $request): bool
    {
        return $this->parse($request) !== null;
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
     * @param string $request
     * @return mixed|void
     * @throws Exception
     */
    public function handle(string $request)
    {
        try {
            $request  = '/' . \ltrim($request, '/');
            $mediaReq = $this->create($request);
            $imgNames = $this->getImageNames($mediaReq);
            if (\count($imgNames) === 0) {
                throw new Exception('No such product id: ' . (int)$mediaReq->id);
            }

            $imgFilePath = null;
            $matchFound  = false;
            foreach ($imgNames as $imgName) {
                $imgName->imgPath = self::getThumb(
                    $mediaReq->type,
                    $mediaReq->id,
                    $imgName,
                    $mediaReq->size,
                    (int)$mediaReq->number,
                    $mediaReq->name . '.' . $mediaReq->ext
                );
                if ('/' . $imgName->imgPath === $request) {
                    $matchFound  = true;
                    $imgFilePath = \PFAD_ROOT . $imgName->imgPath;
                    break;
                }
            }
            if ($matchFound === false) {
                \header('Location: ' . Shop::getURL() . '/' . $imgNames[0]->imgPath, true, 301);
                exit;
            }
            if (!\is_file($imgFilePath)) {
                Image::render($mediaReq, true);
            }
        } catch (Exception $e) {
            $display = \strtolower(\ini_get('display_errors'));
            if (\in_array($display, ['on', '1', 'true'], true)) {
                echo $e->getMessage();
            }
            \http_response_code(404);
        }
        exit;
    }

    /**
     * @param MediaImageRequest $req
     * @return array
     */
    private function getImageNames(MediaImageRequest $req): array
    {
        switch ($req->type) {
            case Image::TYPE_PRODUCT:
                $names = Shop::Container()->getDB()->queryPrepared(
                    'SELECT kArtikel, cName, cSeo, cArtNr, cBarcode
                    FROM tartikel AS a
                    WHERE kArtikel = :pid
                    UNION SELECT asp.kArtikel, asp.cName, asp.cSeo, a.cArtNr, a.cBarcode
                        FROM tartikelsprache AS asp JOIN tartikel AS a ON asp.kArtikel = a.kArtikel
                        WHERE asp.kArtikel = :pid',
                    ['pid' => $req->id],
                    ReturnType::ARRAY_OF_OBJECTS
                );
                break;
            case Image::TYPE_MANUFACTURER:
                $names = Shop::Container()->getDB()->queryPrepared(
                    'SELECT kHersteller, cName, cSeo, cBildpfad
                    FROM thersteller
                    WHERE kHersteller = :mid',
                    ['mid' => $req->id],
                    ReturnType::ARRAY_OF_OBJECTS
                );
                if (!empty($names[0]->cBildpfad)) {
                    $req->path = $names[0]->cBildpfad;
                }
                break;
            default:
                $names = [];
                break;
        }

        return $names;
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
            self::clearCache($req->getType(), $req->getId());
        }

        foreach ([Image::SIZE_XS, Image::SIZE_SM, Image::SIZE_MD, Image::SIZE_LG] as $size) {
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
                'name'   => Image::getCustomName(Image::TYPE_PRODUCT, $image),
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
        switch ($type) {
            case Image::TYPE_PRODUCT:
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
                break;

            default:
                throw new InvalidArgumentException('Image type not implemented');
        }

        while (($image = $images->fetch(PDO::FETCH_OBJ)) !== false) {
            $req = MediaImageRequest::create([
                'id'     => $image->kArtikel,
                'type'   => $type,
                'name'   => Image::getCustomName($type, $image),
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
     * @param string $request
     * @return array|null
     */
    private function parse(?string $request): ?array
    {
        if (!\is_string($request) || \mb_strlen($request) === 0) {
            return null;
        }
        if (\mb_strpos($request, '/') === 0) {
            $request = \mb_substr($request, 1);
        }

        return \preg_match(\MEDIAIMAGE_REGEX, $request, $matches)
            ? \array_intersect_key($matches, \array_flip(\array_filter(\array_keys($matches), '\is_string')))
            : null;
    }

    /**
     * @param string $request
     * @return MediaImageRequest
     */
    private function create(?string $request): MediaImageRequest
    {
        return MediaImageRequest::create($this->parse($request));
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
        switch ($type) {
            case Image::TYPE_PRODUCT:
                $res = [
                    'stmt' => 'SELECT kArtikel, nNr AS number
                        FROM tartikelpict 
                        WHERE kArtikel = :kArtikel 
                        GROUP BY cPfad 
                        ORDER BY nNr ASC',
                    'bind' => ['kArtikel' => $id]
                ];
                break;
            case Image::TYPE_CATEGORY:
                $res = [
                    'stmt' => 'SELECT kKategorie, 0 AS number 
                                  FROM tkategoriepict 
                                  WHERE kKategorie = :kKategorie',
                    'bind' => ['kKategorie' => $id]
                ];
                break;
            case Image::TYPE_CONFIGGROUP:
                $res = [
                    'stmt' => 'SELECT cBildpfad, 0 AS number 
                        FROM tkonfiggruppe 
                        WHERE kKonfiggruppe = :kKonfiggruppe 
                        ORDER BY nSort ASC',
                    'bind' => ['kKonfiggruppe' => $id]
                ];
                break;
            case Image::TYPE_VARIATION:
                $res = [
                    'stmt' => 'SELECT kEigenschaftWert, 0 AS number 
                        FROM teigenschaftwertpict 
                        WHERE kEigenschaftWert = :kEigenschaftWert',
                    'bind' => ['kEigenschaftWert' => $id]
                ];
                break;
            case Image::TYPE_MANUFACTURER:
                $res = [
                    'stmt' => 'SELECT cBildpfad, 0 AS number 
                                  FROM thersteller 
                                  WHERE kHersteller = :kHersteller',
                    'bind' => ['kHersteller' => $id]
                ];
                break;
            case Image::TYPE_ATTRIBUTE:
                $res = [
                    'stmt' => 'SELECT cBildpfad, 0 AS number 
                        FROM tmerkmal 
                        WHERE kMerkmal = :kMerkmal 
                        ORDER BY nSort ASC',
                    'bind' => ['kMerkmal' => $id]
                ];
                break;
            case Image::TYPE_ATTRIBUTE_VALUE:
                $res = [
                    'stmt' => 'SELECT cBildpfad, 0 AS number 
                        FROM tmerkmalwert 
                        WHERE kMerkmalWert = :kMerkmalWert 
                        ORDER BY nSort ASC',
                    'bind' => ['kMerkmalWert' => $id]
                ];
                break;
            default:
                return null;
        }

        return (object)$res;
    }

    /**
     * @param string $type
     * @param int    $id
     * @return int
     */
    public static function imageCount($type, int $id): int
    {
        $prepared = static::getImageStmt($type, $id);

        if ($prepared !== null) {
            $imageCount = Shop::Container()->getDB()->queryPrepared(
                $prepared->stmt,
                $prepared->bind,
                ReturnType::AFFECTED_ROWS
            );

            return \is_numeric($imageCount) ? (int)$imageCount : 0;
        }

        return 0;
    }
}
