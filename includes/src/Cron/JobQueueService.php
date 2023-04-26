<?php

namespace JTL\Cron;

use JTL\Abstracts\AbstractService;
use JTL\Interfaces\RepositoryInterface;

class JobQueueService extends AbstractService
{
    /**
     * @var JobQueueRepository
     */
    protected JobQueueRepository $repository;

    /**
     * @inheritDoc
     */
    public function getRepository(): JobQueueRepository
    {
        return $this->repository;
    }

    /**
     * @inheritDoc
     */
    protected function initDependencies(): void
    {
        $this->repository = new JobQueueRepository();
    }

    /**
     * @param array $ids
     * @return bool
     */
    public function delete(array $ids): bool
    {
        return $this->getRepository()->deleteCron($ids, CronService::getPermanentJobTypes());
    }
}
