<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Media\Image;

use Exception;
use FilesystemIterator;
use Generator;
use JTL\DB\DbInterface;
use JTL\Media\Image;
use JTL\Media\IMedia;
use JTL\Media\MediaImageRequest;
use JTL\Shop;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use stdClass;
use function Functional\select;

/**
 * Class AbstractImage
 * @package JTL\Media\Image
 */
abstract class AbstractImage implements IMedia
{
    public const TYPE = '';

    /**
     * @var string
     */
    protected $regEx = '/^bilder\/produkte\/(?P<size>mini|klein|normal|gross)' .
    '\/(?P<path>(?P<name>[a-zA-Z0-9\-_]+)\.(?P<ext>jpg|jpeg|png|gif))$/';

    /**
     * @inheritdoc
     */
    public function handle(string $request)
    {
        try {
            $request  = '/' . \ltrim($request, '/');
            $mediaReq = $this->create($request);
            $imgNames = $this->getImageNames($mediaReq);
            if (\count($imgNames) === 0) {
                throw new Exception('No such image id: ' . (int)$mediaReq->id);
            }

            $imgFilePath = null;
            $matchFound  = false;
            foreach ($imgNames as $imgName) {
                $mediaReq->path   = $mediaReq->name . '.' . $mediaReq->ext;
                $mediaReq->number = (int)$mediaReq->number;
                $imgName->imgPath = static::getThumbByRequest($mediaReq);
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
     * @inheritdoc
     */
    public static function getThumb(string $type, $id, $mixed, $size, int $number = 1, string $source = null): string
    {
        $req   = static::getRequest($type, $id, $mixed, $size, $number, $source);
        $thumb = $req->getThumb($size);
        $raw   = $req->getRaw();
        if (!\file_exists(\PFAD_ROOT . $thumb) && ($raw === null || !\file_exists($raw))) {
            $thumb = \BILD_KEIN_ARTIKELBILD_VORHANDEN;
        }

        return $thumb;
    }

    /**
     * @inheritdoc
     */
    public static function getThumbByRequest(MediaImageRequest $req): string
    {
        $thumb = $req->getThumb($req->getSizeType());
        if (!\file_exists(\PFAD_ROOT . $thumb) && (($raw = $req->getRaw()) === null || !\file_exists($raw))) {
            $thumb = \BILD_KEIN_ARTIKELBILD_VORHANDEN;
        }

        return $thumb;
    }

    /**
     * @inheritdoc
     */
    public static function getRequest(
        string $type,
        $id,
        $mixed,
        string $size,
        int $number = 1,
        string $sourcePath = null
    ): MediaImageRequest {
        return MediaImageRequest::create([
            'size'       => $size,
            'id'         => $id,
            'type'       => $type,
            'number'     => $number,
            'name'       => static::getCustomName($mixed),
            'ext'        => static::getFileExtension($sourcePath),
            'path'       => $sourcePath,
            'sourcePath' => $sourcePath
        ]);
    }

    /**
     * @inheritdoc
     */
    public static function getCustomName($mixed): string
    {
        return 'image';
    }

    /**
     * @inheritdoc
     */
    public function isValid(string $request): bool
    {
        return $this->parse($request) !== null;
    }
    /**
     * @inheritdoc
     */
    public static function getImageStmt(string $type, int $id): ?stdClass
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    protected function getImageNames(MediaImageRequest $req): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getPathByID($id, int $number = null): ?string
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public static function getStoragePath(): string
    {
        return \PFAD_MEDIA_IMAGE_STORAGE;
    }

    /**
     * @inheritdoc
     */
    public static function getStats(bool $filesize = false): StatsItem
    {
        $result = new StatsItem();
        foreach (static::getAllImages() as $image) {
            if ($image === null) {
                continue;
            }
            $raw = $image->getRaw();
            $result->addItem();
            if ($raw !== null && \file_exists($raw)) {
                foreach (Image::getAllSizes() as $size) {
                    $thumb = $image->getThumb($size, true);
                    if (!\file_exists($thumb)) {
                        continue;
                    }
                    $result->addGeneratedItem($size);
                    if ($filesize === true) {
                        $bytes = \filesize($thumb);
                        $result->addGeneratedSizeItem($size, $bytes);
                    }
                }
            } else {
                $result->addCorrupted();
            }
        }

        return $result;
    }

    /**
     * @param int|null $offset
     * @param int|null $limit
     * @return string
     */
    protected static function getLimitStatement(int $offset = null, int $limit = null): string
    {
        $limitStmt = '';
        if ($limit !== null) {
            $limitStmt = ' LIMIT ';
            if ($offset !== null) {
                $limitStmt .= (int)$offset . ', ';
            }
            $limitStmt .= (int)$limit;
        }

        return $limitStmt;
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    public static function getImages(bool $notCached = false, int $offset = null, int $limit = null): array
    {
        $requests = [];
        foreach (static::getAllImages($offset, $limit) as $req) {
            if ($notCached && static::isCached($req)) {
                continue;
            }
            $requests[] = $req;
        }

        return $requests;
    }

    /**
     * @inheritdoc
     */
    public static function getAllImages(int $offset = null, int $limit = null): Generator
    {
        yield null;
    }

    /**
     * @inheritdoc
     */
    public static function getUncachedImageCount(): int
    {
        return \count(select(static::getAllImages(), function (MediaImageRequest $e) {
            return !static::isCached($e) && ($file = $e->getRaw()) !== null && \file_exists($file);
        }));
    }

    /**
     * @inheritDoc
     */
    public static function cacheImage(MediaImageRequest $req, bool $overwrite = false): array
    {
        $result   = [];
        $rawImage = null;
        $rawPath  = $req->getRaw();
        if ($overwrite === true) {
            static::clearCache($req->getID());
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
                    if ($rawImage === null && ($rawPath !== null && !\is_file($rawPath))) {
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
     * @inheritdoc
     */
    public static function clearCache($id = null): void
    {
        $directory = \PFAD_ROOT . MediaImageRequest::getCachePath(static::getType());
        if ($id !== null) {
            $directory .= '/' . (int)$id;
        }
        try {
            $rdi = new RecursiveDirectoryIterator(
                $directory,
                FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS
            );
            foreach (new RecursiveIteratorIterator($rdi, RecursiveIteratorIterator::CHILD_FIRST) as $value) {
                /** @var SplFileInfo $value */
                $value->isFile()
                    ? \unlink($value->getRealPath())
                    : \rmdir($value->getRealPath());
            }

            if ($id !== null) {
                \rmdir($directory);
            }
        } catch (Exception $e) {
        }
    }

    /**
     * @inheritdoc
     */
    public static function imageIsUsed(DbInterface $db, string $path): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function getTotalImageCount(): int
    {
        return 0;
    }

    /**
     * @param MediaImageRequest $req
     * @return bool
     */
    protected static function isCached(MediaImageRequest $req): bool
    {
        return \file_exists($req->getThumb(Image::SIZE_XS, true))
            && \file_exists($req->getThumb(Image::SIZE_SM, true))
            && \file_exists($req->getThumb(Image::SIZE_MD, true))
            && \file_exists($req->getThumb(Image::SIZE_LG, true));
    }

    /**
     * @param string|null $filePath
     * @return string
     */
    protected static function getFileExtension(string $filePath = null): string
    {
        $config = Image::getSettings()['format'];

        return $config === 'auto' && $filePath !== null
            ? \pathinfo($filePath)['extension'] ?? 'jpg'
            : $config;
    }

    /**
     * @param string $request
     * @return array|null
     */
    protected function parse(?string $request): ?array
    {
        if (!\is_string($request) || \mb_strlen($request) === 0) {
            return null;
        }
        if (\mb_strpos($request, '/') === 0) {
            $request = \mb_substr($request, 1);
        }

        return \preg_match($this->regEx, $request, $matches)
            ? \array_intersect_key($matches, \array_flip(\array_filter(\array_keys($matches), '\is_string')))
            : null;
    }

    /**
     * @param string $imageUrl
     * @return MediaImageRequest
     */
    public static function toRequest(string $imageUrl): MediaImageRequest
    {
        return (new static())->create($imageUrl);
    }

    /**
     * @param string $request
     * @return MediaImageRequest
     */
    protected function create(?string $request): MediaImageRequest
    {
        return MediaImageRequest::create($this->parse($request));
    }

    /**
     * @return string
     */
    public static function getType(): string
    {
        return static::TYPE;
    }
}
