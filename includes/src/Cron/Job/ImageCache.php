<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Cron\Job;

use JTL\Cron\Job;
use JTL\Cron\JobInterface;
use JTL\Cron\QueueEntry;
use JTL\Media\Image;
use JTL\Media\Image\Product;
use JTL\Media\IMedia;
use JTL\Media\Media;

/**
 * Class ImageCache
 * @package JTL\Cron\Job
 */
final class ImageCache extends Job
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
     * @param IMedia $instance
     * @return bool
     * @throws \Exception
     */
    private function generateImageCache(int $index, IMedia $instance): bool
    {
        $rendered = 0;
        $total    = $instance::getUncachedImageCount();
        $images   = $instance::getImages(true, $index, $this->getLimit());
        $totalAll = $instance::getTotalImageCount();
        $this->logger->debug('Uncached images: ' . $total . '/' . $totalAll);
        if ($index >= $totalAll) {
            $index  = 0;
            $images = $instance::getImages(true, $index, $this->getLimit());
        }
        while (\count($images) === 0 && $index < $totalAll) {
            $index += $this->getLimit();
            $images = $instance::getImages(true, $index, $this->getLimit());
        }
        foreach ($images as $image) {
            $instance::cacheImage($image);
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
        $media = Media::getInstance();
        $res   = true;
        foreach ($media->getRegisteredClasses() as $type) {
            $res = $this->generateImageCache($queueEntry->tasksExecuted, $type) && $res;
        }
        $queueEntry->tasksExecuted = $this->nextIndex;
        $this->setFinished($res);

        return $this;
    }
}
