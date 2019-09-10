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
     * @param string      $type
     * @param string      $id
     * @param object      $mixed
     * @param string      $size
     * @param int         $number
     * @param string|null $sourcePath
     * @return string
     */
    public static function generateThumb($type, $id, $mixed, $size, int $number = 1, string $sourcePath = null): string
    {
    }

    /**
     * @param string      $type
     * @param string      $id
     * @param object      $mixed
     * @param string      $size
     * @param int         $number
     * @param string|null $sourcePath
     * @return string
     */
    public static function getThumb($type, $id, $mixed, $size, int $number = 1, string $sourcePath = null): string
    {
        $req   = self::getRequest($type, $id, $mixed, $size, $number, $sourcePath);
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
            'size'   => $size,
            'id'     => $id,
            'type'   => $type,
            'number' => $number,
            'name'   => static::getCustomName($mixed),
            'ext'    => static::getFileExtension($sourcePath),
            'path'   => $sourcePath !== null ? \basename($sourcePath) : null
        ]);
    }

    /**
     * @param string|null $sourcePath
     * @return string
     */
    protected static function getFileExtension(string $sourcePath = null): string
    {
        $config = Image::getSettings()['format'];

        return $config === 'auto' && $sourcePath !== null
            ? \pathinfo($sourcePath)['extension'] ?? 'jpg'
            : $config;
    }

    /**
     * @param mixed $mixed
     * @return string
     */
    public static function getCustomName($mixed): string
    {
        return 'image';
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
     * @param string $request
     * @return bool
     */
    public function isValid(string $request): bool
    {
        return $this->parse($request) !== null;
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
}
