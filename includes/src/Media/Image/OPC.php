<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Media\Image;

use JTL\Media\Image;
use JTL\Media\MediaImageRequest;
use JTL\OPC\PortletInstance;
use stdClass;

/**
 * Class OPC
 * @package JTL\Media\Image
 */
class OPC extends Product
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
    public static function getImageStmt(string $type, int $id): ?stdClass
    {
        // @todo
        return (object)[
            'stmt' => 'SELECT cBildpfad, 0 AS number 
                          FROM thersteller 
                          WHERE kHersteller = :kHersteller',
            'bind' => ['kHersteller' => $id]
        ];
    }

    /**
     * @inheritdoc
     */
    protected function getImageNames(MediaImageRequest $req): array
    {
        $req->sourcePath = $req->name . '.' . $req->ext;

        return [(object)[]];
    }

    /**
     * @inheritdoc
     */
    public static function getCustomName($mixed): string
    {
        /** @var PortletInstance $mixed */
        return \pathinfo($mixed->currentImagePath)['filename'];
//        return empty($result) ? 'image' : Image::getCleanFilename($result);
    }

    /**
     * @inheritdoc
     */
    public static function getPathByID($id, int $number = null): ?string
    {
        // @todo
        return null;
    }

    /**
     * @inheritdoc
     */
    public static function getStoragePath(): string
    {
        return \PFAD_MEDIAFILES . 'Bilder/';
    }
}
