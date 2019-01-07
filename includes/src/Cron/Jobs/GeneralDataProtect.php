<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Cron\Jobs;

use Cron\Job;
use Cron\JobInterface;
use Cron\QueueEntry;
use GeneralDataProtection\TableCleaner;

/**
 * Class GeneralDataProtect
 * @package Cron\Jobs
 */
class GeneralDataProtect extends Job
{
    /**
     * @inheritdoc
     */
    public function start(QueueEntry $queueEntry): JobInterface
    {
        $tableCleaner = new TableCleaner();
        $tableCleaner->execute();

        return $this;
    }
}
