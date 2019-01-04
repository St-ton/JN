<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Cron;

/**
 * Class JobHydrator
 * @package Cron
 */
final class JobHydrator
{
    private static $mapping = [
        'kCron'         => 'CronID',
        'cJobArt'       => 'Type',
        'nLimitM'       => 'Limit',
        'nLimitN'       => 'Executed',
        'kKey'          => 'ForeignKeyID',
        'cKey'          => 'ForeignKey',
        'cTabelle'      => 'Table',
        'kJobQueue'     => 'QueueID',
        'dLetzterStart' => 'LastStarted',
        'dStartZeit'    => 'StartTime',
        'nAlleXStd'     => 'Frequency',
        'nInArbeit'     => 'Running'
    ];

    /**
     * @param string $key
     * @return string|null
     */
    private function getMapping(string $key): ?string
    {
        return self::$mapping[$key] ?? null;
    }

    /**
     * @param object $class
     * @param object $data
     * @return object
     */
    public function hydrate($class, $data)
    {
        foreach (\get_object_vars($data) as $key => $value) {
            if (($mapping = $this->getMapping($key)) !== null) {
                $method = 'set' . $mapping;
                $class->$method($value);
            }
        }

        return $class;
    }
}
