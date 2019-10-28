<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Mapper;

use InvalidArgumentException;
use JTL\Cron\Job\Export;
use JTL\Cron\Job\GeneralDataProtect;
use JTL\Cron\Job\ImageCache;
use JTL\Cron\Job\Newsletter;
use JTL\Cron\Job\Statusmail;
use JTL\Cron\Job\Store;
use JTL\Cron\JobInterface;
use JTL\Cron\Type;
use JTL\Events\Dispatcher;
use JTL\Events\Event;

/**
 * Class JobTypeToJob
 * @package JTL\Mapper
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
            case Type::STORE:
                return Store::class;
            default:
                $mapping = null;
                Dispatcher::getInstance()->fire(Event::MAP_CRONJOB_TYPE, ['type' => $type, 'mapping' => &$mapping]);
                if ($mapping === null) {
                    throw new InvalidArgumentException('Invalid job type: ' . $type);
                }

                return $mapping;
        }
    }
}
