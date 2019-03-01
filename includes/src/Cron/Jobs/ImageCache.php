<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Cron\Jobs;

use JTL\Cron\Job;
use JTL\Cron\JobInterface;
use JTL\Cron\QueueEntry;
use JTL\Media\Image;
use JTL\Media\MediaImage;

/**
 * Class ImageCache
 * @package JTL\Cron\Jobs
 */
class ImageCache extends Job
{
    /**
     * @var int
     */
    private $nextIndex = 0;

    /**
     * @inheritdoc
     */
    public function hydrate($data)
    {
        parent::hydrate($data);
        if (\JOBQUEUE_LIMIT_IMAGE_CACHE_IMAGES > 0) {
            $this->setLimit(\JOBQUEUE_LIMIT_IMAGE_CACHE_IMAGES);
        }

        return $this;
    }

    /**
     * @param int    $index
     * @param string $type
     * @return bool
     * @throws \Exception
     */
    private function generateImageCache(int $index, string $type = Image::TYPE_PRODUCT): bool
    {
        $rendered = 0;
        $total    = MediaImage::getUncachedProductImageCount();
        $images   = MediaImage::getImages($type, true, $index, $this->getLimit());
        $totalAll = MediaImage::getProductImageCount();
        $this->logger->debug('Uncached images: ' . $total . '/' . $totalAll);
        while (\count($images) === 0 && $index < $totalAll) {
            $index += $this->getLimit();
            $images = MediaImage::getImages($type, true, $index, $this->getLimit());
        }
        foreach ($images as $image) {
            MediaImage::cacheImage($image);
            ++$index;
            ++$rendered;
        }
        $this->logger->debug('Generated cache for ' . $rendered . ' images');
        $this->nextIndex = $total === 0 ? 0 : $index;

        return $total === 0;
    }

    /**
     * @inheritdoc
     */
    public function start(QueueEntry $queueEntry): JobInterface
    {
        parent::start($queueEntry);
        $this->logger->debug('Generating image cache - max. ' . $this->getLimit());
        $res                       = $this->generateImageCache($queueEntry->tasksExecuted);
        $queueEntry->tasksExecuted = $this->nextIndex;
        $this->setFinished($res);

        return $this;
    }
}
