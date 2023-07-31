<?php

namespace JTL\Cron;

use JTL\Abstracts\AbstractRepository;

/**
 * Class CronRepository
 * @package JTL\Cron
 */
class CronRepository extends AbstractRepository
{
    /**
     * @inheritDoc
     */
    public function getTableName(): string
    {
        return 'tcron';
    }

    /**
     * @inheritDoc
     */
    public function getKeyName(): string
    {
        return 'cronID';
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
