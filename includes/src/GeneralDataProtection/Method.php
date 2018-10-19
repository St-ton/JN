<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace GeneralDataProtection;

class Method
{
    /**
     * object-wide date at the point of instanciating
     *
     * @var object DateTime
     */
    protected $oNow;

    /**
     * interval in "number of days"
     *
     * @var int
     */
    protected $iInterval = 0;

    /**
     * select the maximum of 10,000 rows for one step!
     * (if the scripts are running each day, we need some days
     * to anonymize more than 10,000 data sets)
     *
     * @var
     */
    protected $iWorkLimit = 10000;

    /**
     * main shop logger
     *
     * @var \Monolog\Logger
     */
    protected $oLogger;


    public function __construct(\DateTime $oObjNow, int $iInterval)
    {
        try {
            $this->oLogger = \Shop::Container()->getLogService();
        } catch (\Exception $e) {
            $this->oLogger = null;
        }
        $this->oNow      = $oObjNow;
        $this->iInterval = $iInterval;
    }
}

