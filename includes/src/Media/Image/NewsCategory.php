<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Media\Image;

use JTL\DB\ReturnType;
use JTL\Media\Image;
use JTL\Media\MediaImageRequest;
use JTL\Shop;
use stdClass;

/**
 * Class NewsCategory
 * @package JTL\Media\Image
 */
class NewsCategory extends Product
{
    protected $regEx = '/^media\/image\/(?P<type>newscategory)' .
    '\/(?P<id>\d+)\/(?P<size>xs|sm|md|lg|os)\/(?P<name>[a-zA-Z0-9\-_]+)' .
    '(?:(?:~(?P<number>\d+))?)\.(?P<ext>jpg|jpeg|png|gif|webp)$/';

    /**
     * @param string $type
     * @param int    $id
     * @return stdClass|null
     */
    public static function getImageStmt(string $type, int $id): ?stdClass
    {
        return (object)[
            'stmt' => 'SELECT kNewsKategorie, 0 AS number  
                          FROM tnewskategorie 
                          WHERE kNewsKategorie = :cid',
            'bind' => ['cid' => $id]
        ];
    }

    /**
     * @inheritdoc
     */
    protected function getImageNames(MediaImageRequest $req): array
    {
        $names = Shop::Container()->getDB()->queryPrepared(
            'SELECT a.kNewsKategorie, a.cPreviewImage AS path, t.name AS title
                    FROM tnewskategorie AS a
                    LEFT JOIN tnewskategoriesprache t
                        ON a.kNewsKategorie = t.kNewsKategorie
                    WHERE a.kNewsKategorie = :nid',
            ['nid' => $req->id],
            ReturnType::ARRAY_OF_OBJECTS
        );
        if (!empty($names[0]->path)) {
            $req->sourcePath = \str_replace(\PFAD_NEWSKATEGORIEBILDER, '', $names[0]->path);
        }

        return $names;
    }

    /**
     * @inheritdoc
     */
    public static function getCustomName($mixed): string
    {
        $result = $mixed->title;

        return empty($result) ? 'image' : Image::getCleanFilename($result);
    }
}
