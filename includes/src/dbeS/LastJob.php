<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace dbeS;

use DB\ReturnType;
use Shop;
use SingletonTrait;

/**
 * Class LastJob
 * @package dbeS
 */
class LastJob
{
    use SingletonTrait;

    /**
     * Initialize Syncstatus
     */
    protected function init()
    {
    }

    /**
     * @param int $hours
     * @return \stdClass[]
     */
    public function getRepeatedJobs(int $hours): array
    {
        return Shop::Container()->getDB()->queryPrepared(
            "SELECT kJob, nJob, dErstellt
                FROM tlastjob
                WHERE cType = 'RPT'
                    AND (DATE_ADD(dErstellt, INTERVAL :hrs HOUR) < NOW())",
            ['hrs' => $hours],
            ReturnType::ARRAY_OF_OBJECTS
        );
    }

    /**
     * @return \stdClass[]
     */
    public function getStdJobs(): array
    {
        return Shop::Container()->getDB()->selectAll(
            'tlastjob',
            ['cType', 'nFinished'],
            ['STD', 1],
            'kJob, nJob, cJobName, dErstellt',
            'dErstellt'
        );
    }

    /**
     * @param int $jobID
     * @return null|\stdClass
     */
    public function getJob(int $jobID): ?\stdClass
    {
        return Shop::Container()->getDB()->select('tlastjob', 'nJob', $jobID);
    }

    /**
     * @param int    $jobID
     * @param string $name
     * @return \stdClass
     */
    public function run(int $jobID, $name = null): \stdClass
    {
        $job = $this->getJob($jobID);
        if ($job === null) {
            $job = (object)[
                'cType'     => 'STD',
                'nJob'      => $jobID,
                'cJobName'  => $name,
                'nCounter'  => 1,
                'dErstellt' => \date('Y-m-d H:i:s'),
                'nFinished' => 0,
            ];

            $job->kJob = Shop::Container()->getDB()->insert('tlastjob', $job);
        } else {
            $job->nCounter++;
            $job->dErstellt = \date('Y-m-d H:i:s');

            Shop::Container()->getDB()->update('tlastjob', 'kJob', $job->kJob, $job);
        }

        return $job;
    }

    /**
     * @param int $jobID
     * @return int
     */
    public function restartJob(int $jobID): int
    {
        return Shop::Container()->getDB()->update(
            'tlastjob',
            'nJob',
            $jobID,
            (object)[
                'nCounter'  => 0,
                'dErstellt' => \date('Y-m-d H:i:s'),
                'nFinished' => 0,
            ]
        );
    }

    /**
     * @param int|null $jobID
     * @return int
     */
    public function finishStdJobs(int $jobID = null): int
    {
        $keys    = ['cType', 'nFinished'];
        $keyVals = ['STD', 0];

        if (!empty($jobID)) {
            $keys[]    = 'nJob';
            $keyVals[] = $jobID;
        }

        Shop::Container()->getDB()->update('tlastjob', $keys, $keyVals, (object)['nFinished' => 1]);

        $keyVals[1] = 1;
        $jobs       = $this->getStdJobs();
        foreach ($jobs as $job) {
            $fileName   = \PFAD_ROOT . \PFAD_DBES . $job->cJobName . '.inc.php';
            $finishProc = $job->cJobName . '_Finish';

            if (\is_file($fileName)) {
                require_once $fileName;

                if (\function_exists($finishProc)) {
                    $finishProc();
                }
            }
        }

        return Shop::Container()->getDB()->delete('tlastjob', $keys, $keyVals);
    }
}
