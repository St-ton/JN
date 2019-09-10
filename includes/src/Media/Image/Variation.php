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
 * Class Variation
 * @package JTL\Media\Image
 */
class Variation extends Product
{
    protected $regEx = '/^media\/image\/(?P<type>variation)' .
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
            'stmt' => 'SELECT kEigenschaftWert, 0 AS number 
                        FROM teigenschaftwertpict 
                        WHERE kEigenschaftWert = :vid',
            'bind' => ['vid' => $id]
        ];
    }

    /**
     * @inheritdoc
     */
    protected function getImageNames(MediaImageRequest $req): array
    {
        // @todo
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
            $req->sourcePath = \str_replace(\PFAD_NEWSBILDER, '', $names[0]->path);
        }

        return $names;
    }

    /**
     * @inheritdoc
     */
    public static function getCustomName($mixed): string
    {
        // @todo
        $result = $mixed->title;

        return empty($result) ? 'image' : Image::getCleanFilename($result);
    }
}
