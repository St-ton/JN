<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\UrlHelper;

/**
 * @param bool $filesize
 * @return array
 * @throws Exception
 */
function getItems(bool $filesize = false)
{
    $item = (object) [
        'name'  => __('typeProduct'),
        'type'  => Image::TYPE_PRODUCT,
        'stats' => MediaImage::getStats(Image::TYPE_PRODUCT, $filesize)
    ];

    return [Image::TYPE_PRODUCT => $item];
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
    if ($type !== null && preg_match('/[a-z]*/', $type)) {
        MediaImage::clearCache($type);
        unset($_SESSION['image_count'], $_SESSION['renderedImages']);
        if ($isAjax === true) {
            return ['success' => 'Cache wurde erfolgreich zurückgesetzt'];
        }
        Shop::Smarty()->assign('success', 'Cache wurde erfolgreich zurückgesetzt');
    }

    return [];
}

/**
 * @param string $type
 * @param int    $index
 * @return IOError|object
 * @throws Exception
 */
function generateImageCache($type, int $index)
{
    if ($type === null || $index === null) {
        return new IOError('Invalid argument request', 500);
    }

    $started = time();
    $result  = (object)[
        'total'          => 0,
        'renderTime'     => 0,
        'nextIndex'      => 0,
        'renderedImages' => 0,
        'images'         => []
    ];

    if ($index === 0) {
        $_SESSION['image_count']    = count(MediaImage::getImages($type, true));
        $_SESSION['renderedImages'] = 0;
    }

    $total    = $_SESSION['image_count'];
    $images   = MediaImage::getImages($type, true, $index, IMAGE_PRELOAD_LIMIT);
    $totalAll = count(MediaImage::getImages($type));
    while (count($images) === 0 && $index < $totalAll) {
        $index += 10;
        $images = MediaImage::getImages($type, true, $index, IMAGE_PRELOAD_LIMIT);
    }
    foreach ($images as $image) {
        $seconds = time() - $started;
        if ($seconds >= 10) {
            break;
        }
        $result->images[] = MediaImage::cacheImage($image);
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
    static $offset   = 0;
    $corruptedImages = [];
    $totalImages     = count(MediaImage::getImages($type));
    do {
        $images = MediaImage::getImages($type, false, $offset, $limit);
        foreach ($images as $image) {
            $fallback = $image->getFallbackThumb(Image::SIZE_XS);
            if (!file_exists($image->getRaw(true)) && !file_exists(PFAD_ROOT . $fallback)) {
                $corruptedImage            = (object)[
                    'article' => [],
                    'picture' => ''
                ];
                $articleDB                 = Shop::Container()->getDB()->select(
                    'tartikel',
                    'kArtikel',
                    $image->getId()
                );
                $articleDB->cURLFull       = UrlHelper::buildURL($articleDB, URLART_ARTIKEL, true);
                $article                   = (object)[
                    'articleNr'      => $articleDB->cArtNr,
                    'articleURLFull' => $articleDB->cURLFull
                ];
                $corruptedImage->article[] = $article;
                $corruptedImage->picture   = $image->getPath();
                if (array_key_exists($image->getPath(), $corruptedImages)) {
                    $corruptedImages[$corruptedImage->picture]->article[] = $article;
                } else {
                    $corruptedImages[$corruptedImage->picture] = $corruptedImage;
                }
            }
        }
        $offset += count($images);
    } while (count($corruptedImages) < $limit && $offset < $totalImages);

    return [Image::TYPE_PRODUCT => array_slice($corruptedImages, 0, $limit)];
}
