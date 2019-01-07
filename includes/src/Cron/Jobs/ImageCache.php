<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Cron\Jobs;

use Cron\Job;
use Cron\JobInterface;
use Cron\QueueEntry;
use MediaImage;

/**
 * Class ImageCache
 * @package Cron\Jobs
 */
class ImageCache extends Job
{
    /**
     * @param int    $index
     * @param string $type
     * @return \stdClass
     * @throws \Exception
     */
    private function generateImageCache(int $index, string $type = \Image::TYPE_PRODUCT): \stdClass
    {
        $started  = \time();
        $rendered = 0;
        $total    = MediaImage::getUncachedProductImageCount();
        $images   = MediaImage::getImages($type, true, $index, $this->getLimit());
        $totalAll = MediaImage::getProductImageCount();
        $this->logger->debug('Uncached images count: ' . $total);
        $this->logger->debug('Total image count: ' . $totalAll);
        while (\count($images) === 0 && $index < $totalAll) {
            $index  += $this->getLimit();
            $images = MediaImage::getImages($type, true, $index, $this->getLimit());
        }
        foreach ($images as $image) {
            MediaImage::cacheImage($image);
            ++$index;
            ++$rendered;
            \sleep(2);
        }

        return (object)[
            'total'          => $total,
            'renderTime'     => \time() - $started,
            'nextIndex'      => $total === 0 ? 0 : $index,
            'renderedImages' => $rendered,
            'finished'       => $total === 0
        ];
    }

    /**
     * @inheritdoc
     */
    public function start(QueueEntry $queueEntry): JobInterface
    {
        if (\JOBQUEUE_LIMIT_IMAGE_CACHE_IMAGES > 0) {
            $this->setLimit(\JOBQUEUE_LIMIT_IMAGE_CACHE_IMAGES);
        }
        parent::start($queueEntry);
        $this->logger->debug('Generating image cache - max. ' . $this->getLimit());
        $res = $this->generateImageCache($queueEntry->nLimitN);
        $this->logger->debug('Generated cache for ' . $res->renderedImages . ' images');

        $queueEntry->nLimitN = $res->nextIndex;
        $this->setFinished($res->finished);

        return $this;
    }
}
