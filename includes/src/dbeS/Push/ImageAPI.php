<?php declare(strict_types=1);

namespace JTL\dbeS\Push;

use InvalidArgumentException;
use JTL\Helpers\Request;
use JTL\Media\Image;
use JTL\Media\IMedia;
use JTL\Media\Media;
use JTL\Media\MediaImageRequest;
use JTL\Shop;

/**
 * Class ImageAPI
 * @package JTL\dbeS\Push
 */
final class ImageAPI extends AbstractPush
{
    /**
     * @var string
     */
    private string $imageType;

    /**
     * @var int
     */
    private int $imageID = 0;

    /**
     * @inheritdoc
     */
    public function getData(): void
    {
        try {
            $this->getImageType();
        } catch (InvalidArgumentException) {
            return;
        }
        $class = Media::getClass($this->imageType);
        /** @var IMedia $instance */
        $instance = new $class($this->db);
        $imageNo  = Request::getInt('n', 1);
        $path     = $instance->getPathByID($this->imageID, $imageNo);
        if ($path === null) {
            return;
        }
        $req   = MediaImageRequest::create([
            'type'       => $this->imageType,
            'id'         => $this->imageID,
            'size'       => $this->getSizeByID(Request::verifyGPCDataInt('s')),
            'number'     => $imageNo,
            'ext'        => \pathinfo($path, \PATHINFO_EXTENSION),
            'sourcePath' => $path
        ]);
        $names = $instance->getImageNames($req);
        if (\count($names) === 0) {
            return;
        }
        $req->setName($names[0]);
        $thumb = $req->getThumb();
        if (!\file_exists(\PFAD_ROOT . $thumb)) {
            $instance->cacheImage($req);
        }
        if (Request::verifyGPCDataInt('url') === 1) {
            echo Shop::getURL() . '/' . $thumb;
        } else {
            $this->displayImage(\PFAD_ROOT . $thumb);
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    private function getImageType(): void
    {
        if (($id = Request::verifyGPCDataInt('a')) > 0) {
            $this->imageType = Image::TYPE_PRODUCT;
            $this->imageID   = $id;
            return;
        }
        if (($id = Request::verifyGPCDataInt('k')) > 0) {
            $this->imageType = Image::TYPE_CATEGORY;
            $this->imageID   = $id;
            return;
        }
        if (($id = Request::verifyGPCDataInt('h')) > 0) {
            $this->imageType = Image::TYPE_CATEGORY;
            $this->imageID   = $id;
            return;
        }
        if (($id = Request::verifyGPCDataInt('n')) > 0) {
            $this->imageType = Image::TYPE_NEWS;
            $this->imageID   = $id;
            return;
        }
        if (($id = Request::verifyGPCDataInt('nk')) > 0) {
            $this->imageType = Image::TYPE_NEWSCATEGORY;
            $this->imageID   = $id;
            return;
        }
        if (($id = Request::verifyGPCDataInt('m')) > 0) {
            $this->imageType = Image::TYPE_CHARACTERISTIC_VALUE;
            $this->imageID   = $id;
            return;
        }
        if (($id = Request::verifyGPCDataInt('c')) > 0) {
            $this->imageType = Image::TYPE_CHARACTERISTIC;
            $this->imageID   = $id;
            return;
        }
        throw new InvalidArgumentException('Invalid image type');
    }

    /**
     * @param string $imagePath
     */
    private function displayImage(string $imagePath): void
    {
        if (($mime = $this->getMimeType($imagePath)) !== null) {
            \header('Content-type: ' . $mime);
            \readfile($imagePath);
        }
    }

    /**
     * @param string $imagePath
     * @return string|null
     */
    private function getMimeType(string $imagePath): ?string
    {
        return \file_exists($imagePath)
            ? \getimagesize($imagePath)['mime'] ?? null
            : null;
    }

    /**
     * @param int $size
     * @return string
     */
    private function getSizeByID(int $size): string
    {
        return match ($size) {
            1       => Image::SIZE_LG,
            2       => Image::SIZE_MD,
            3       => Image::SIZE_SM,
            4       => Image::SIZE_XS,
            default => Image::SIZE_XL,
        };
    }
}
