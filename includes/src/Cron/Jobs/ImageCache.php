<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Cron\Jobs;

use Cron\Job;
use Cron\JobInterface;
use Cron\QueueEntry;
use DB\DbInterface;
use Psr\Log\LoggerInterface;
use MediaImage;

/**
 * Class ImageCache
 * @package Cron\Jobs
 */
class ImageCache extends Job
{
    /**
     * @inheritdoc
     */
    public function __construct(DbInterface $db, LoggerInterface $logger)
    {
        parent::__construct($db, $logger);
        if (\JOBQUEUE_LIMIT_IMAGE_CACHE_IMAGES > 0) {
            $this->setLimit(\JOBQUEUE_LIMIT_IMAGE_CACHE_IMAGES);
        }
    }

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
        while (\count($images) === 0 && $index < $totalAll) {
            $index  += $this->getLimit();
            $images = MediaImage::getImages($type, true, $index, $this->getLimit());
        }
        foreach ($images as $image) {
            MediaImage::cacheImage($image);
            ++$index;
            ++$rendered;
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
        parent::start($queueEntry);
        $res = $this->generateImageCache($queueEntry->nLimitN);

        $queueEntry->nLimitN = $res->nextIndex;
        $this->setFinished($res->finished);

        return $this;
    }
}
