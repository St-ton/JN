<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Cron\Jobs;

use Cron\Job;
use Cron\JobInterface;
use Cron\QueueEntry;

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
        $oTableCleaner = new \GeneralDataProtection\TableCleaner();
        $oTableCleaner->execute();

        return $this;
    }
}
