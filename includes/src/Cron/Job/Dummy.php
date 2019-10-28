<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Cron\Job;

use JTL\Cron\Job;
use JTL\Cron\JobInterface;
use JTL\Cron\QueueEntry;

/**
 * Class Dummy
 * @package JTL\Cron\Job
 */
final class Dummy extends Job
{
    /**
     * @inheritdoc
     */
    public function hydrate($data)
    {
        parent::hydrate($data);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function start(QueueEntry $queueEntry): JobInterface
    {
        parent::start($queueEntry);

        return $this;
    }
}
