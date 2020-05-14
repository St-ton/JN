<?php declare(strict_types=1);

namespace JTL\Cron\Job;

use GuzzleHttp\Exception\RequestException;
use JTL\Cron\Job;
use JTL\Cron\JobInterface;
use JTL\Cron\QueueEntry;
use JTL\License\Manager;
use JTL\License\Mapper;

/**
 * Class LicenseCheck
 * @package JTL\Cron\Job
 */
final class LicenseCheck extends Job
{
    /**
     * @inheritdoc
     */
    public function start(QueueEntry $queueEntry): JobInterface
    {
        parent::start($queueEntry);
        $manager = new Manager($this->db);
        try {
            $res = $manager->update(true);
            if ($res <= 0) {
                return $this;
            }
        } catch (RequestException $e) {
            return $this;
        }
        $data = $this->db->select('licenses', 'id', $res);
        $this->setFinished((int)($data->returnCode ?? 0) === 200);
        $this->handleExpiredLicenses($manager);

        return $this;
    }

    /**
     * @param Manager $manager
     */
    private function handleExpiredLicenses(Manager $manager): void
    {
        $mapper = new Mapper($this->db, $manager);
        foreach ($mapper->getCollection()->getActiveExpired() as $item) {
            // @todo: do something
        }
    }
}
