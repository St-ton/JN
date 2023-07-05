<?php

namespace JTL\Cron;

use JTL\Abstracts\AbstractService;
use JTL\Cron\Type;

/**
 * Class CronService
 * @package JTL\Cron
 */
class CronService extends AbstractService
{
    /**
     * @var CronRepository
     */
    protected CronRepository $repository;

    /**
     * @var JobQueueService
     */
    protected JobQueueService $jobQueueService;

    /**
     * @inheritDoc
     */
    public function getRepository(): CronRepository
    {
        return $this->repository;
    }

    /**
     * @inheritDoc
     */
    protected function initDependencies(): void
    {
        $this->repository      = new CronRepository();
        $this->jobQueueService = new JobQueueService();
    }

    /**
     * @return JobQueueService
     */
    public function getJobQueueService(): JobQueueService
    {
        return $this->jobQueueService;
    }

    /**
     * @return string[]
     */
    public static function getPermanentJobTypes(): array
    {
        return [
            Type::LICENSE_CHECK,
            Type::MAILQUEUE,
        ];
    }

    /**
     * @param array $cronIDs
     * @return bool
     */
    public function delete(array $cronIDs): bool
    {
        $this->getRepository()->deleteCron($cronIDs, self::getPermanentJobTypes());

        return $this->getJobQueueService()->delete($cronIDs);
    }
}
