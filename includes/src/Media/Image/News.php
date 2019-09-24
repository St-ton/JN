<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Media\Image;

use FilesystemIterator;
use Generator;
use JTL\DB\ReturnType;
use JTL\Media\Image;
use JTL\Media\MediaImageRequest;
use JTL\Shop;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use stdClass;

/**
 * Class News
 * @package JTL\Media\Image
 */
class News extends AbstractImage
{
    public const TYPE = Image::TYPE_NEWS;

    /**
     * @var string
     */
    protected $regEx = '/^media\/image\/(?P<type>news)' .
    '\/(?P<id>\d+)\/(?P<size>xs|sm|md|lg|xl|os)\/(?P<name>[a-zA-Z0-9\-_]+)' .
    '(?:(?:~(?P<number>\d+))?)\.(?P<ext>jpg|jpeg|png|gif|webp)$/';

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    protected function getImageNames(MediaImageRequest $req): array
    {
        return Shop::Container()->getDB()->queryPrepared(
            'SELECT a.kNews, a.cPreviewImage AS path, t.title
                FROM tnews AS a
                LEFT JOIN tnewssprache t
                    ON a.kNews = t.kNews
                WHERE a.kNews = :nid',
            ['nid' => $req->getID()],
            ReturnType::COLLECTION
        )->each(function ($item, $key) use ($req) {
            if ($key === 0 && !empty($item->path)) {
                $req->setSourcePath(\str_replace(\PFAD_NEWSBILDER, '', $item->path));
            }
            $item->imageName = self::getCustomName($item);
        })->pluck('imageName')->toArray();
    }

    /**
     * @inheritdoc
     */
    public static function getCustomName($mixed): string
    {
        $result = \method_exists($mixed, 'getTitle') ? $mixed->getTitle() : $mixed->title;

        return empty($result) ? 'image' : Image::getCleanFilename($result);
    }

    /**
     * @inheritdoc
     */
    public static function getPathByID($id, int $number = null): ?string
    {
        $item = Shop::Container()->getDB()->queryPrepared(
            'SELECT cPreviewImage AS path
                FROM tnews
                WHERE kNews = :cid LIMIT 1',
            ['cid' => $id],
            ReturnType::SINGLE_OBJECT
        )->path ?? null;

        return empty($item->path)
            ? null
            : \str_replace(\PFAD_NEWSBILDER, '', $item->path);
    }

    /**
     * @inheritdoc
     */
    public static function getStoragePath(): string
    {
        return \PFAD_NEWSBILDER;
    }

    /**
     * @inheritdoc
     */
    public static function getAllImages(int $offset = null, int $limit = null): Generator
    {
        $base = \PFAD_ROOT . self::getStoragePath();
        $rdi  = new RecursiveDirectoryIterator(
            $base,
            FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS
        );
        foreach (new RecursiveIteratorIterator($rdi, RecursiveIteratorIterator::CHILD_FIRST) as $fileinfo) {
            /** @var SplFileInfo $fileinfo */
            $name = $fileinfo->getFilename();
            if ($fileinfo->isFile() && \strpos($name, '.') !== 0) {
                $path = \str_replace($base, '', $fileinfo->getPathname());
                yield MediaImageRequest::create([
                    'id'         => 1,
                    'type'       => self::TYPE,
                    'name'       => $fileinfo->getFilename(),
                    'number'     => 1,
                    'path'       => $path,
                    'sourcePath' => $path,
                    'ext'        => static::getFileExtension($path)
                ]);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public static function getTotalImageCount(): int
    {
        $rdi = new RecursiveDirectoryIterator(
            \PFAD_ROOT . self::getStoragePath(),
            FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS
        );
        $cnt = 0;
        foreach (new RecursiveIteratorIterator($rdi, RecursiveIteratorIterator::CHILD_FIRST) as $fileinfo) {
            /** @var SplFileInfo $fileinfo */
            if ($fileinfo->isFile() && \strpos($fileinfo->getFilename(), '.') !== 0) {
                ++$cnt;
            }
        }

        return $cnt;
    }
}
