<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Media\Image;

use JTL\Media\MediaImageRequest;
use JTL\OPC\PortletInstance;
use stdClass;

/**
 * Class OPC
 * @package JTL\Media\Image
 */
class OPC extends Product
{
    protected $regEx = '/^media\/image\/(?P<type>opc)' .
    '\/(?P<id>[a-zA-Z0-9]+)\/(?P<size>xs|sm|md|lg|os)\/(?P<name>[a-zA-Z0-9\-_\.]+)' .
    '(?:(?:~(?P<number>\d+))?)\.(?P<ext>jpg|jpeg|png|gif|webp)$/';

    /**
     * @param string $type
     * @param int    $id
     * @return stdClass|null
     */
    public static function getImageStmt(string $type, int $id): ?stdClass
    {
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
}
