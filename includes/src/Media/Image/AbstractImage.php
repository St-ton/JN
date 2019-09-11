<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Media\Image;

use Exception;
use JTL\Media\Image;
use JTL\Media\IMedia;
use JTL\Media\MediaImageRequest;
use JTL\Shop;

/**
 * Class AbstractImage
 * @package JTL\Media\Image
 */
abstract class AbstractImage implements IMedia
{
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
                throw new Exception('No such product id: ' . (int)$mediaReq->id);
            }

            $imgFilePath = null;
            $matchFound  = false;
            foreach ($imgNames as $imgName) {
                $mediaReq->path   = $mediaReq->name . '.' . $mediaReq->ext;
                $mediaReq->number = (int)$mediaReq->number;
                $imgName->imgPath = $this->getThumbByRequest($mediaReq);
                if ('/' . $imgName->imgPath === $request) {
                    $matchFound  = true;
                    $imgFilePath = \PFAD_ROOT . $imgName->imgPath;
                    break;
                }
            }
            if ($matchFound === false) {
                Shop::dbg($request, false, 'REQ:');
                Shop::dbg($imgFilePath, false, '$imgFilePath:');
                Shop::dbg($mediaReq, true);
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
    public static function getThumb(string $type, $id, $mixed, $size, int $number = 1, string $sourcePath = null): string
    {
        $req   = self::getRequest($type, $id, $mixed, $size, $number, $sourcePath);
        $thumb = $req->getThumb($size);
        if (!\file_exists(\PFAD_ROOT . $thumb) && !\file_exists(\PFAD_ROOT . $req->getRaw())) {
            Shop::dbg($thumb, false, 'Thumb@404:');
            Shop::dbg($req, true, 'REQ@404:');
            $thumb = self::getFallback($req);
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
            'path'       => $sourcePath !== null ? \basename($sourcePath) : null,
            'sourcePath' => $sourcePath !== null ? \basename($sourcePath) : null
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
     * @param MediaImageRequest $req
     * @return string
     */
    public function getThumbByRequest(MediaImageRequest $req): string
    {
        $thumb = $req->getThumb($req->getSizeType());
        if (!\file_exists(\PFAD_ROOT . $thumb) && !\file_exists(\PFAD_ROOT . $req->getRaw())) {
            Shop::dbg($thumb, false, 'Thumb@404:');
            Shop::dbg($req, true, 'REQ@404:');
            $thumb = self::getFallback($req);
        }

        return $thumb;
    }

    /**
     * @param MediaImageRequest $req
     * @return string
     */
    protected static function getFallback(MediaImageRequest $req): string
    {
        $thumb = \BILD_KEIN_ARTIKELBILD_VORHANDEN;
        if ($req->getType() === Image::TYPE_PRODUCT) {
            $fallback = $req->getFallbackThumb($req->getSizeType());
            if (\file_exists(\PFAD_ROOT . $fallback)) {
                $thumb = $fallback;
            }
        }

        return $thumb;
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
     * @param string $request
     * @return MediaImageRequest
     */
    protected function create(?string $request): MediaImageRequest
    {
        return MediaImageRequest::create($this->parse($request));
    }
}
