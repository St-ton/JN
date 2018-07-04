<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Mapper;


use Cron\JobInterface;
use Cron\Jobs\Export;
use Cron\Jobs\Newsletter;
use Cron\Jobs\Statusmail;
use Cron\Type;

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
            case Type::EXPORT:
                return Export::class;
            case Type::STATUSMAIL:
                return Statusmail::class;
            case Type::NEWSLETTER:
                return Newsletter::class;
            default:
                throw new \InvalidArgumentException('Invalid job type: ' . $type);
        }
    }
}
