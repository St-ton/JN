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
 * Class News
 * @package JTL\Media\Image
 */
class News extends Product
{
    protected $regEx = '/^media\/image\/(?P<type>news)' .
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
            'stmt' => 'SELECT kNews, 0 AS number  
                          FROM tnews 
                          WHERE kNews = :nid',
            'bind' => ['nid' => $id]
        ];
    }

    /**
     * @param MediaImageRequest $req
     * @return array
     */
    protected function getImageNames(MediaImageRequest $req): array
    {
        $names = Shop::Container()->getDB()->queryPrepared(
            'SELECT a.kNews, a.cPreviewImage AS path, t.title
                    FROM tnews AS a
                    LEFT JOIN tnewssprache t
                        ON a.kNews = t.kNews
                    WHERE a.kNews = :nid',
            ['nid' => $req->id],
            ReturnType::ARRAY_OF_OBJECTS
        );
        if (!empty($names[0]->path)) {
            $req->path = \str_replace(\PFAD_NEWSBILDER, '', $names[0]->path);
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
