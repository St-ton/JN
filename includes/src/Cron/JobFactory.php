<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Cron;


use Mapper\JobTypeToJob;

/**
 * Class JobFactory
 * @package Cron
 */
class JobFactory
{
    /**
     * @param \stdClass $data
     * @return JobInterface
     */
    public function create(\stdClass $data): JobInterface
    {
        $mapper = new JobTypeToJob();
        // @todo: catch Exception
        $class = $mapper->map($data->cJobArt);
        $job   = new $class(\Shop::Container()->getDB()); // @todo: inject?
        /** @var JobInterface $job */
        $job->setType($data->cJobArt);
        $job->setTable($data->cTabelle);
        $job->setForeignKey($data->cKey);
        $job->setForeignKeyID((int)$data->kKey);
        $job->setCronID((int)$data->kCron);
        // @todo: setID vs. setCrontID
        $job->setID((int)$data->kCron);
        $job->setExecuted((int)($data->nLimitN ?? 0));
        if (isset($data->nLimitM)) {
            $job->setLimit((int)$data->nLimitM);
        }

        return $job;
    }
}
