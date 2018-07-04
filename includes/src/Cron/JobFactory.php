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
        $job = new $class(\Shop::Container()->getDB()); // @todo: inject?
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

        //@todo: move to job implementations
//        switch ($job->getType()) {
//            case Type::NEWSLETTER :
//                if (JOBQUEUE_LIMIT_M_NEWSLETTER > 0) {
//                    $job->setLimit(JOBQUEUE_LIMIT_M_NEWSLETTER);
//                }
//                break;
//
//            case Type::EXPORT :
//                if (JOBQUEUE_LIMIT_M_EXPORTE > 0) {
//                    $job->setLimit(JOBQUEUE_LIMIT_M_EXPORTE);
//                }
//                break;
//
//            case Type::STATUSMAIL :
//                if (JOBQUEUE_LIMIT_M_STATUSEMAIL > 0) {
//                    $job->setLimit(JOBQUEUE_LIMIT_M_STATUSEMAIL);
//                }
//                break;
//
//            case Type::TS_RATING :
//                $job->setLimit(5);
//                break;
//
//            case Type::CLEAR_CACHE :
//                $job->setLimit(10);
//                break;
//
//            default:
//                break;
//
//        }

        return $job;
    }
}
