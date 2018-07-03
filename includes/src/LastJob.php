<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class LastJob
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
     * @return stdClass[]
     */
    public function getRepeatedJobs(int $hours)
    {
        return Shop::Container()->getDB()->query(
            "SELECT kJob, nJob, dErstellt
                FROM tlastjob
                WHERE cType = 'RPT'
                    AND (dErstellt = '0000-00-00 00:00:00'
                        OR DATE_ADD(dErstellt, INTERVAL " . $hours . " HOUR) < NOW())",
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
    }

    /**
     * @return stdClass[]
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
     * @param int $nJob
     * @return null|stdClass
     */
    public function getJob(int $nJob)
    {
        return Shop::Container()->getDB()->select('tlastjob', 'nJob', $nJob);
    }

    /**
     * @param int $nJob
     * @param string $cJobName
     * @return stdClass
     */
    public function run(int $nJob, $cJobName = null)
    {
        $job = $this->getJob($nJob);
        if ($job === null) {
            $job = (object)[
                'cType'     => 'STD',
                'nJob'      => $nJob,
                'cJobName'  => $cJobName,
                'nCounter'  => 1,
                'dErstellt' => date('Y-m-d H:i:s'),
                'nFinished' => 0,
            ];

            $job->kJob = Shop::Container()->getDB()->insert('tlastjob', $job);
        } else {
            $job->nCounter++;
            $job->dErstellt = date('Y-m-d H:i:s');

            Shop::Container()->getDB()->update('tlastjob', 'kJob', $job->kJob, $job);
        }

        return $job;
    }

    /**
     * @param int $nJob
     * @return bool
     */
    public function restartJob(int $nJob)
    {
        $job = (object)[
            'nCounter'  => 0,
            'dErstellt' => date('Y-m-d H:i:s'),
            'nFinished' => 0,
        ];

        return Shop::Container()->getDB()->update('tlastjob', 'nJob', $nJob, $job);
    }

    /**
     * @param int|null $nJob
     */
    public function finishStdJobs($nJob = null)
    {
        $keys    = ['cType', 'nFinished'];
        $keyVals = ['STD', 0];

        if (!empty($nJob)) {
            $keys[]    = 'nJob';
            $keyVals[] = $nJob;
        }

        Shop::Container()->getDB()->update('tlastjob', $keys, $keyVals, (object)['nFinished' => 1]);

        $keyVals[1] = 1;
        $jobs       = $this->getStdJobs();

        if (is_array($jobs)) {
            foreach ($jobs as $job) {
                $fileName   = PFAD_ROOT . PFAD_DBES . $job->cJobName . '.inc.php';
                $finishProc = $job->cJobName . '_Finish';

                if (is_file($fileName)) {
                    require_once $fileName;

                    if (function_exists($finishProc)) {
                        $finishProc();
                    }
                }
            }
        }

        Shop::Container()->getDB()->delete('tlastjob', $keys, $keyVals);
    }
}
