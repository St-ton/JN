<?php declare(strict_types=1);

namespace JTL\Media;

use Exception;
use FilesystemIterator;
use JTL\Alert\Alert;
use JTL\DB\DbInterface;
use JTL\Filesystem\Filesystem;
use JTL\Helpers\URL;
use JTL\IO\IOError;
use JTL\L10n\GetText;
use JTL\Media\GarbageCollection\CollectorInterface;
use JTL\Media\GarbageCollection\ImageTypeToCollectorMapper;
use JTL\Media\Image\Category;
use JTL\Media\Image\Characteristic;
use JTL\Media\Image\CharacteristicValue;
use JTL\Media\Image\ConfigGroup;
use JTL\Media\Image\Manufacturer;
use JTL\Media\Image\News;
use JTL\Media\Image\NewsCategory;
use JTL\Media\Image\Product;
use JTL\Media\Image\StatsItem;
use JTL\Media\Image\Variation;
use JTL\Shop;
use stdClass;

/**
 * Class Manager
 * @package JTL\Media
 */
class Manager
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * Manager constructor.
     * @param DbInterface $db
     * @param GetText     $getText
     */
    public function __construct(DbInterface $db, GetText $getText)
    {
        $this->db = $db;
        $getText->loadAdminLocale('pages/bilderverwaltung');
    }

    /**
     * @param bool $filesize
     * @return array
     * @throws Exception
     */
    public function getItems(bool $filesize = false): array
    {
        return [
            Image::TYPE_PRODUCT              => (object)[
                'name'  => __('product'),
                'type'  => Image::TYPE_PRODUCT,
                'stats' => (new Product($this->db))->getStats($filesize)
            ],
            Image::TYPE_CATEGORY             => (object)[
                'name'  => __('category'),
                'type'  => Image::TYPE_CATEGORY,
                'stats' => (new Category($this->db))->getStats($filesize)
            ],
            Image::TYPE_MANUFACTURER         => (object)[
                'name'  => __('manufacturer'),
                'type'  => Image::TYPE_MANUFACTURER,
                'stats' => (new Manufacturer($this->db))->getStats($filesize)
            ],
            Image::TYPE_CHARACTERISTIC       => (object)[
                'name'  => __('characteristic'),
                'type'  => Image::TYPE_CHARACTERISTIC,
                'stats' => (new Characteristic($this->db))->getStats($filesize)
            ],
            Image::TYPE_CHARACTERISTIC_VALUE => (object)[
                'name'  => __('characteristic value'),
                'type'  => Image::TYPE_CHARACTERISTIC_VALUE,
                'stats' => (new CharacteristicValue($this->db))->getStats($filesize)
            ],
            Image::TYPE_VARIATION            => (object)[
                'name'  => __('variation'),
                'type'  => Image::TYPE_VARIATION,
                'stats' => (new Variation($this->db))->getStats($filesize)
            ],
            Image::TYPE_NEWS                 => (object)[
                'name'  => __('news'),
                'type'  => Image::TYPE_NEWS,
                'stats' => (new News($this->db))->getStats($filesize)
            ],
            Image::TYPE_NEWSCATEGORY         => (object)[
                'name'  => __('newscategory'),
                'type'  => Image::TYPE_NEWSCATEGORY,
                'stats' => (new NewsCategory($this->db))->getStats($filesize)
            ],
            Image::TYPE_CONFIGGROUP          => (object)[
                'name'  => __('configgroup'),
                'type'  => Image::TYPE_CONFIGGROUP,
                'stats' => (new ConfigGroup($this->db))->getStats($filesize)
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
        /* attention: this will parallelize async io stats */
        \session_write_close();
        /* but there should not be any session operations after this point */
        $items = $this->getItems(true);

        return ($type === null || \in_array($type, $items, true))
            ? new IOError('Invalid argument request', 500)
            : $items[$type]->stats;
    }

    /**
     * @param string $type
     * @param int    $index
     * @return stdClass
     */
    public function cleanupStorage(string $type, int $index): stdClass
    {
        $startIndex = $index;
        $mapping    = ImageTypeToCollectorMapper::getMapping($type);
        /** @var CollectorInterface $instance */
        $instance = new $mapping($this->db, Shop::Container()->get(Filesystem::class));
        $started  = \time();
        $result   = (object)[
            'total'         => 0,
            'cleanupTime'   => 0,
            'nextIndex'     => 0,
            'deletedImages' => 0,
            'deletes'       => []
        ];
        if ($index === 0) {
            // at the first run, check how many files actually exist in the storage dir
            $_SESSION['image_count']   = \iterator_count(new FilesystemIterator(
                \PFAD_ROOT . $instance->getBaseDir(),
                FilesystemIterator::SKIP_DOTS
            ));
            $_SESSION['deletedImages'] = 0;
            $_SESSION['checkedImages'] = 0;
        }
        $total                      = $_SESSION['image_count'];
        $i                          = $instance->collect($index, \IMAGE_CLEANUP_LIMIT);
        $deletes                    = $instance->getDeletedFiles();
        $deletedInThisRun           = \count($deletes);
        $checkedInThisRun           = $instance->getChecked();
        $_SESSION['deletedImages'] += $deletedInThisRun;
        $_SESSION['checkedImages'] += $checkedInThisRun;
        $index                      = $i > 0 ? $i + 1 - $deletedInThisRun : $total;
        // avoid infinite recursion
        if ($index === $startIndex && $deletedInThisRun === 0) {
            $index = $total;
        }
        $result->total             = $total;
        $result->deletes           = $deletes;
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
    public function clearImageCache(string $type, bool $isAjax = false): array
    {
        if ($type !== null && \preg_match('/[a-z]*/', $type)) {
            $instance = Media::getClass($type);
            /** @var IMedia $instance */
            $res = $instance::clearCache();
            unset($_SESSION['image_count'], $_SESSION['renderedImages']);
            if ($isAjax === true) {
                return $res === true
                    ? ['msg' => __('successCacheReset'), 'ok' => true]
                    : ['msg' => __('errorCacheReset'), 'ok' => false];
            }
            Shop::Smarty()->assign('success', __('successCacheReset'));
        }

        return [];
    }

    /**
     * @param string|null $type
     * @param int|null    $index
     * @return IOError|object
     * @throws Exception
     */
    public function generateImageCache(?string $type, ?int $index)
    {
        if ($type === null || $index === null) {
            return new IOError('Invalid argument request', 500);
        }
        $class = Media::getClass($type);
        /** @var IMedia $instance */
        $instance = new $class($this->db);
        $started  = \time();
        $result   = (object)[
            'total'           => 0,
            'renderTime'      => 0,
            'nextIndex'       => 0,
            'renderedImages'  => 0,
            'lastRenderError' => null,
            'images'          => []
        ];

        if ($index === 0) {
            $_SESSION['image_count']    = $instance->getUncachedImageCount();
            $_SESSION['renderedImages'] = 0;
        }

        $total    = $_SESSION['image_count'];
        $images   = $instance->getImages(true, $index, \IMAGE_PRELOAD_LIMIT);
        $totalAll = $instance->getTotalImageCount();
        while (\count($images) === 0 && $index < $totalAll) {
            $index += 10;
            $images = $instance->getImages(true, $index, \IMAGE_PRELOAD_LIMIT);
        }
        foreach ($images as $image) {
            $seconds = \time() - $started;
            if ($seconds >= 10) {
                break;
            }
            $cachedImage = $instance->cacheImage($image);

            foreach ($cachedImage as $size => $sizeImg) {
                if ($sizeImg->success === false) {
                    $result->lastRenderError = $sizeImg->error;
                    break;
                }
            }

            $result->images[] = $cachedImage;
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
        $class    = Media::getClass($type);
        $instance = new $class(Shop::Container()->getDB());
        /** @var IMedia $instance */
        $corruptedImages = [];
        $totalImages     = $instance->getTotalImageCount();
        $offset          = 0;
        do {
            foreach ($instance->getAllImages($offset, \MAX_IMAGES_PER_STEP) as $image) {
                if (!\file_exists($image->getRaw())) {
                    $corruptedImage            = (object)[
                        'article' => [],
                        'picture' => ''
                    ];
                    $data                      = $this->db->select(
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
                if (\count($corruptedImages) >= $limit) {
                    Shop::Container()->getAlertService()->addAlert(
                        Alert::TYPE_ERROR,
                        __('Too many corrupted images'),
                        'too-many-corrupted-images'
                    );
                    break;
                }
            }
            $offset += \MAX_IMAGES_PER_STEP;
        } while (\count($corruptedImages) < $limit && $offset < $totalImages);

        return [$type => \array_slice($corruptedImages, 0, $limit)];
    }
}
