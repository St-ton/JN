<?php

namespace JTL\Cron;

use JTL\Abstracts\AbstractRepository;

/**
 * Class JobQueueRepository
 * @package JTL\Cron
 */
class JobQueueRepository extends AbstractRepository
{
    /**
     * @inheritDoc
     */
    public function getTableName(): string
    {
        return 'tjobqueue';
    }

    /**
     * @inheritDoc
     */
    public function getKeyName(): string
    {
        return 'jobQueueID';
    }

    /**
     * @param array $ids
     * @param array $exclude
     * @return bool
     */
    public function deleteCron(array $ids, array $exclude): bool
    {
        return $this->getDB()->queryPrepared(
            'DELETE FROM ' . $this->getTableName() . ' WHERE cronID IN (:IDs) AND jobType NOT IN (:jobTypes)',
            [
                'IDs'      => implode(',', $ids),
                'jobTypes' => implode(',', $exclude)
            ]
        );
    }
}
