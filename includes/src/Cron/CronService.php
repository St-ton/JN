<?php

namespace JTL\Cron;

use JTL\Abstracts\AbstractService;

/**
 *
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
     * @return JobQueueService
     */
    public function getJobQueueService(): JobQueueService
    {
        return $this->jobQueueService;
    }

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

    public static function getPermanentJobTypes(): array
    {
        return [
            'licensecheck',
            'sendMailQueue',
        ];
    }

    public function delete(array $cronIDs): bool
    {
        $this->getRepository()->deleteCron($cronIDs, self::getPermanentJobTypes());

        return $this->getJobQueueService()->delete($cronIDs);
    }
}
