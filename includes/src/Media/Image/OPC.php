<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Media\Image;

use DirectoryIterator;
use JTL\Media\Image;
use JTL\Media\MediaImageRequest;
use JTL\OPC\PortletInstance;

/**
 * Class OPC
 * @package JTL\Media\Image
 */
class OPC extends AbstractImage
{
    public const TYPE = Image::TYPE_OPC;

    /**
     * @var string
     */
    protected $regEx = '/^media\/image\/'
    . '(?P<type>opc)'
    . '\/(?P<size>xs|sm|md|lg|xl|os)'
    . '\/(?P<name>[a-zA-Z0-9\-_\.]+)'
    . '(?:(?:~(?P<number>\d+))?)\.(?P<ext>jpg|jpeg|png|gif|webp)$/';

    /**
     * @inheritdoc
     */
    public static function getImageNames(MediaImageRequest $req): array
    {
        $name = $req->getName();
        $file = $name . '.' . $req->getExt();
        if (\file_exists(\PFAD_ROOT . \STORAGE_OPC . $file)) {
            $req->setSourcePath($file);
        } elseif (\file_exists(\PFAD_ROOT . \STORAGE_OPC . $name . '.png')) {
            $req->setSourcePath($name . '.png');
        } elseif (\file_exists(\PFAD_ROOT . \STORAGE_OPC . $name . '.jpg')) {
            $req->setSourcePath($name . '.jpg');
        }

        return [$name];
    }

    /**
     * @inheritdoc
     */
    public static function getCustomName($mixed): string
    {
        /** @var PortletInstance $mixed */
        return \pathinfo($mixed->currentImagePath)['filename'];
    }

    /**
     * @inheritdoc
     */
    public static function getPathByID($id, int $number = null): ?string
    {
        return $id;
    }

    /**
     * @inheritdoc
     */
    public static function getStoragePath(): string
    {
        return \STORAGE_OPC;
    }

    /**
     * @inheritdoc
     */
    public static function getTotalImageCount(): int
    {
        $iterator = new DirectoryIterator(\PFAD_ROOT . self::getStoragePath());
        $cnt      = 0;
        foreach ($iterator as $fileinfo) {
            if ($fileinfo->isDot() || !$fileinfo->isFile()) {
                continue;
            }
            ++$cnt;
        }

        return $cnt;
    }
}
