<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Mapper;

use Cron\JobInterface;
use Cron\Jobs\Export;
use Cron\Jobs\ImageCache;
use Cron\Jobs\Newsletter;
use Cron\Jobs\Statusmail;
use Cron\Jobs\GeneralDataProtect;
use Cron\Type;
use Events\Dispatcher;
use Events\Event;

/**
 * Class JobTypeToJob
 * @package Mapper
 */
class JobTypeToJob
{
    /**
     * @param string $type
     * @return JobInterface
     */
    public function map(string $type): string
    {
        switch ($type) {
            case Type::IMAGECACHE:
                return ImageCache::class;
            case Type::EXPORT:
                return Export::class;
            case Type::STATUSMAIL:
                return Statusmail::class;
            case Type::NEWSLETTER:
                return Newsletter::class;
            case Type::DATAPROTECTION:
                return GeneralDataProtect::class;
            default:
                $mapping = null;
                Dispatcher::getInstance()->fire(Event::MAP_CRONJOB_TYPE, ['type' => $type, 'mapping' => &$mapping]);
                if ($mapping === null) {
                    throw new \InvalidArgumentException('Invalid job type: ' . $type);
                }

                return $mapping;
        }
    }
}
