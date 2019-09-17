<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Media;

use DirectoryIterator;
use Exception;
use FilesystemIterator;
use Image;
use JTL\Helpers\URL;
use JTL\IO\IOError;
use JTL\Media\Image\Category;
use JTL\Media\Image\Characteristic;
use JTL\Media\Image\CharacteristicValue;
use JTL\Media\Image\Manufacturer;
use JTL\Media\Image\News;
use JTL\Media\Image\NewsCategory;
use JTL\Media\Image\Product;
use JTL\Media\Image\StatsItem;
use JTL\Media\Image\Variation;
use JTL\Shop;
use LimitIterator;
use stdClass;

/**
 * Class Manager
 * @package JTL\Media
 */
class Manager
{
    /**
     * @param bool $filesize
     * @return array
     * @throws Exception
     */
    public function getItems(bool $filesize = false): array
    {
        return [
            Image::TYPE_PRODUCT      => (object)[
                'name'  => __('product'),
                'type'  => Image::TYPE_PRODUCT,
                'stats' => Product::getStats($filesize)
            ],
            Image::TYPE_CATEGORY     => (object)[
                'name'  => __('category'),
                'type'  => Image::TYPE_CATEGORY,
                'stats' => Category::getStats($filesize)
            ],
            Image::TYPE_MANUFACTURER => (object)[
                'name'  => __('manufacturer'),
                'type'  => Image::TYPE_MANUFACTURER,
                'stats' => Manufacturer::getStats($filesize)
            ],
            Image::TYPE_CHARACTERISTIC => (object)[
                'name'  => __('characteristic'),
                'type'  => Image::TYPE_CHARACTERISTIC,
                'stats' => Characteristic::getStats($filesize)
            ],
            Image::TYPE_CHARACTERISTIC_VALUE => (object)[
                'name'  => __('characteristic value'),
                'type'  => Image::TYPE_CHARACTERISTIC_VALUE,
                'stats' => CharacteristicValue::getStats($filesize)
            ],
            Image::TYPE_VARIATION => (object)[
                'name'  => __('variation'),
                'type'  => Image::TYPE_VARIATION,
                'stats' => Variation::getStats($filesize)
            ],
            Image::TYPE_NEWS => (object)[
                'name'  => __('news'),
                'type'  => Image::TYPE_NEWS,
                'stats' => News::getStats($filesize)
            ],
            Image::TYPE_NEWSCATEGORY => (object)[
                'name'  => __('newscategory'),
                'type'  => Image::TYPE_NEWSCATEGORY,
                'stats' => NewsCategory::getStats($filesize)
            ]
        ];
    }

    /**
     * @param string $type
     * @return IOError|StatsItem
     * @throws Exception
     */
    public function loadStats(string $type)
    {
        $items = $this->getItems(true);

        return ($type === null || \in_array($type, $items, true))
            ? new IOError('Invalid argument request', 500)
            : $items[$type]->stats;
    }

    /**
     * @param int $index
     * @return stdClass|IOError
     * @todo use this.
     */
    public function cleanupStorage(string $type, int $index)
    {
        $startIndex = $index;
        $instance   = Media::getClass($type);
        /** @var IMedia $instance */
        $directory = \PFAD_ROOT . $instance::getStoragePath();
        $started   = \time();
        $result    = (object)[
            'total'         => 0,
            'cleanupTime'   => 0,
            'nextIndex'     => 0,
            'deletedImages' => 0,
            'deletes'       => []
        ];
        if ($index === 0) {
            // at the first run, check how many files actually exist in the storage dir
            $storageIterator           = new FilesystemIterator($directory, FilesystemIterator::SKIP_DOTS);
            $_SESSION['image_count']   = \iterator_count($storageIterator);
            $_SESSION['deletedImages'] = 0;
            $_SESSION['checkedImages'] = 0;
        }
        $total            = $_SESSION['image_count'];
        $checkedInThisRun = 0;
        $deletedInThisRun = 0;
        $i                = 0;
        $db               = Shop::Container()->getDB();
        foreach (new LimitIterator(new DirectoryIterator($directory), $index, \IMAGE_CLEANUP_LIMIT) as $i => $info) {
            /** @var DirectoryIterator $info */
            $fileName = $info->getFilename();
            if ($info->isDot() || $info->isDir() || \strpos($fileName, '.git') === 0) {
                continue;
            }
            ++$checkedInThisRun;
            if (!$instance::imageIsUsed($db, $fileName)) {
                $result->deletes[] = $fileName;
//                \unlink($info->getRealPath());
                \error_log('would now delete ' . $info->getRealPath());
                ++$_SESSION['deletedImages'];
                ++$deletedInThisRun;
            }
        }
        // increment total number of checked files by the amount checked in this run
        $_SESSION['checkedImages'] += $checkedInThisRun;
        $index                      = $i > 0 ? $i + 1 - $deletedInThisRun : $total;
        // avoid infinite recursion
        if ($index === $startIndex && $deletedInThisRun === 0) {
            $index = $total;
        }
        $result->total             = $total;
        $result->cleanupTime       = \time() - $started;
        $result->nextIndex         = $index;
        $result->checkedFiles      = $checkedInThisRun;
        $result->checkedFilesTotal = $_SESSION['checkedImages'];
        $result->deletedImages     = $_SESSION['deletedImages'];
        if ($index >= $total) {
            // done.
            unset($_SESSION['image_count'], $_SESSION['deletedImages'], $_SESSION['checkedImages']);
        }

        return $result;
    }

    /**
     * @param string $type
     * @param bool   $isAjax
     * @return array
     */
    public function clearImageCache(string $type, bool $isAjax = false)
    {
        Shop::Container()->getGetText()->loadAdminLocale('pages/bilderverwaltung');
        if ($type !== null && \preg_match('/[a-z]*/', $type)) {
            $instance = Media::getClass($type);
            /** @var IMedia $instance */
            $instance::clearCache();
            unset($_SESSION['image_count'], $_SESSION['renderedImages']);
            if ($isAjax === true) {
                return ['success' => __('successCacheReset')];
            }
            Shop::Smarty()->assign('success', __('successCacheReset'));
        }

        return [];
    }

    /**
     * @param string $type
     * @param int    $index
     * @return IOError|object
     * @throws Exception
     */
    public function generateImageCache(?string $type, ?int $index)
    {
        if ($type === null || $index === null) {
            return new IOError('Invalid argument request', 500);
        }
        $instance = Media::getClass($type);
        /** @var IMedia $instance */

        $started = \time();
        $result  = (object)[
            'total'          => 0,
            'renderTime'     => 0,
            'nextIndex'      => 0,
            'renderedImages' => 0,
            'images'         => []
        ];

        if ($index === 0) {
            $_SESSION['image_count']    = $instance::getUncachedImageCount();
            $_SESSION['renderedImages'] = 0;
        }

        $total    = $_SESSION['image_count'];
        $images   = $instance::getImages(true, $index, \IMAGE_PRELOAD_LIMIT);
        $totalAll = $instance::getTotalImageCount();
        while (\count($images) === 0 && $index < $totalAll) {
            $index += 10;
            $images = $instance::getImages(true, $index, \IMAGE_PRELOAD_LIMIT);
        }
        foreach ($images as $image) {
            $seconds = \time() - $started;
            if ($seconds >= 10) {
                break;
            }
            $result->images[] = $instance::cacheImage($image);
            ++$index;
            ++$_SESSION['renderedImages'];
        }
        $result->total          = $total;
        $result->renderTime     = \time() - $started;
        $result->nextIndex      = $index;
        $result->renderedImages = $_SESSION['renderedImages'];
        if ($_SESSION['renderedImages'] >= $total) {
            unset($_SESSION['image_count'], $_SESSION['renderedImages']);
        }

        return $result;
    }

    /**
     * @param string $type
     * @param int    $limit
     * @return array
     * @throws Exception
     */
    public function getCorruptedImages(string $type, int $limit): array
    {
        $instance = Media::getClass($type);
        /** @var IMedia $instance */
        $corruptedImages = [];
        $totalImages     = $instance::getTotalImageCount();
        $db              = Shop::Container()->getDB();
        do {
            $i = 0;
            foreach ($instance::getAllImages() as $image) {
                ++$i;
                if (!\file_exists($image->getRaw())) {
                    $corruptedImage            = (object)[
                        'article' => [],
                        'picture' => ''
                    ];
                    $data                      = $db->select(
                        'tartikel',
                        'kArtikel',
                        $image->getId()
                    );
                    $data->cURLFull            = URL::buildURL($data, \URLART_ARTIKEL, true);
                    $item                      = (object)[
                        'articleNr'      => $data->cArtNr,
                        'articleURLFull' => $data->cURLFull
                    ];
                    $corruptedImage->article[] = $item;
                    $corruptedImage->picture   = $image->getPath();
                    if (\array_key_exists($image->getPath(), $corruptedImages)) {
                        $corruptedImages[$corruptedImage->picture]->article[] = $item;
                    } else {
                        $corruptedImages[$corruptedImage->picture] = $corruptedImage;
                    }
                }
            }
        } while (\count($corruptedImages) < $limit && $i < $totalImages);

        return [$type => \array_slice($corruptedImages, 0, $limit)];
    }
}
