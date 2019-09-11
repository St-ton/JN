<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Helpers\URL;
use JTL\IO\IOError;
use JTL\Media\Media;
use JTL\Media\IMedia;
use JTL\Media\Image\Category;
use JTL\Media\Image\Manufacturer;
use JTL\Media\Image\Product;
use JTL\Shop;

/**
 * @param bool $filesize
 * @return array
 * @throws Exception
 */
function getItems(bool $filesize = false): array
{
    return [
        Image::TYPE_PRODUCT => (object)[
            'name'  => __('product'),
            'type'  => Image::TYPE_PRODUCT,
            'stats' => Product::getStats($filesize)
        ],
        Image::TYPE_CATEGORY => (object)[
            'name'  => __('category'),
            'type'  => Image::TYPE_CATEGORY,
            'stats' => Category::getStats($filesize)
        ],
        Image::TYPE_MANUFACTURER => (object)[
            'name'  => __('manufacturer'),
            'type'  => Image::TYPE_MANUFACTURER,
            'stats' => Manufacturer::getStats($filesize)
        ]
    ];
}

/**
 * @param string $type
 * @return IOError
 * @throws Exception
 */
function loadStats($type)
{
    $items = getItems(true);

    return ($type === null || in_array($type, $items, true))
        ? new IOError('Invalid argument request', 500)
        : $items[$type]->stats;
}

/**
 * @param int $index
 * @return stdClass|IOError
 */
function cleanupStorage(int $index)
{
    $startIndex = $index;

    if ($index === null) {
        return new IOError('Invalid argument request', 500);
    }

    $directory = PFAD_ROOT . PFAD_MEDIA_IMAGE_STORAGE;
    $started   = time();
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
        $_SESSION['image_count']   = iterator_count($storageIterator);
        $_SESSION['deletedImages'] = 0;
        $_SESSION['checkedImages'] = 0;
    }

    $total            = $_SESSION['image_count'];
    $checkedInThisRun = 0;
    $deletedInThisRun = 0;
    $idx              = 0;

    foreach (new LimitIterator(new DirectoryIterator($directory), $index, IMAGE_CLEANUP_LIMIT) as $idx => $fileInfo) {
        if ($fileInfo->isDot()) {
            continue;
        }
        ++$checkedInThisRun;
        $imageIsUsed = Shop::Container()->getDB()->select('tartikelpict', 'cPfad', $fileInfo->getFilename()) !== null;
        // files in the storage folder that have no associated entry in tartikelpict are considered orphaned
        if (!$imageIsUsed) {
            $result->deletes[] = $fileInfo->getFilename();
            unlink($fileInfo->getPathname());
            ++$_SESSION['deletedImages'];
            ++$deletedInThisRun;
        }
    }
    // increment total number of checked files by the amount checked in this run
    $_SESSION['checkedImages'] += $checkedInThisRun;
    $index                      = $idx > 0 ? $idx + 1 - $deletedInThisRun : $total;
    // avoid endless recursion
    if ($index === $startIndex && $deletedInThisRun === 0) {
        $index = $total;
    }
    $result->total             = $total;
    $result->cleanupTime       = time() - $started;
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
function clearImageCache($type, bool $isAjax = false)
{
    Shop::Container()->getGetText()->loadAdminLocale('pages/bilderverwaltung');
    if ($type !== null && preg_match('/[a-z]*/', $type)) {
        $instance = Media::getClass($type);
        /** @var IMedia $instance */
        $instance::clearCache($type);
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
function generateImageCache(?string $type, ?int $index)
{
    if ($type === null || $index === null) {
        return new IOError('Invalid argument request', 500);
    }
    $instance = Media::getClass($type);
    /** @var IMedia $instance */

    $started = time();
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
    $images   = $instance::getImages(true, $index, IMAGE_PRELOAD_LIMIT);
    $totalAll = $instance::getTotalImageCount();
    while (count($images) === 0 && $index < $totalAll) {
        $index += 10;
        $images = $instance::getImages(true, $index, IMAGE_PRELOAD_LIMIT);
    }
    foreach ($images as $image) {
        $seconds = time() - $started;
        if ($seconds >= 10) {
            break;
        }
        $result->images[] = $instance::cacheImage($image);
        ++$index;
        ++$_SESSION['renderedImages'];
    }
    $result->total          = $total;
    $result->renderTime     = time() - $started;
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
function getCorruptedImages($type, int $limit)
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
            if (!file_exists($image->getRaw(true))
                && !file_exists(PFAD_ROOT . $image->getFallbackThumb(Image::SIZE_XS))
            ) {
                $corruptedImage            = (object)[
                    'article' => [],
                    'picture' => ''
                ];
                $data                      = $db->select(
                    'tartikel',
                    'kArtikel',
                    $image->getId()
                );
                $data->cURLFull            = URL::buildURL($data, URLART_ARTIKEL, true);
                $item                      = (object)[
                    'articleNr'      => $data->cArtNr,
                    'articleURLFull' => $data->cURLFull
                ];
                $corruptedImage->article[] = $item;
                $corruptedImage->picture   = $image->getPath();
                if (array_key_exists($image->getPath(), $corruptedImages)) {
                    $corruptedImages[$corruptedImage->picture]->article[] = $item;
                } else {
                    $corruptedImages[$corruptedImage->picture] = $corruptedImage;
                }
            }
        }
    } while (count($corruptedImages) < $limit && $i < $totalImages);

    return [$type => array_slice($corruptedImages, 0, $limit)];
}
