<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
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
     * descriptive string for journal-purposes
     *
     * @var string
     */
    protected $szReason;

    /**
     * interval in "number of days"
     *
     * @var int
     */
    protected $iInterval = 0;

    /**
     * maximum number of rows, which we are process
     *
     * @var
     */
    protected $iWorkLimit = 10000;

    /**
     * main shop logger
     *
     * @var \Logger
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

