<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Cron\Job;

use JTL\Cron\Job;
use JTL\Cron\JobInterface;
use JTL\Cron\QueueEntry;
use JTL\GeneralDataProtection\TableCleaner;

/**
 * Class GeneralDataProtect
 * @package JTL\Cron\Job
 */
final class GeneralDataProtect extends Job
{
    /**
     * @inheritdoc
     */
    public function start(QueueEntry $queueEntry): JobInterface
    {
        parent::start($queueEntry);
        $tableCleaner = new TableCleaner();
        $tableCleaner->execute();
        $this->setFinished(true);

        return $this;
    }
}
