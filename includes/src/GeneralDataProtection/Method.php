<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace GeneralDataProtection;

class Method
{
    protected $oNow      = null;
    protected $iInterval = 0;

    protected $oLogger = null; // --DEBUG--


    public function __construct(\DateTime $oObjNow, int $iInterval)
    {
        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - --DEBUG--
        include_once('/var/www/html/shop4_07/includes/vendor/apache/log4php/src/main/php/Logger.php');
        \Logger::configure('/var/www/html/shop4_07/_logging_conf.xml');
        $this->oLogger = \Logger::getLogger('default');
        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - --DEBUG--

        $this->oNow      = $oObjNow;
        $this->iInterval = $iInterval;
    }

    /**
     * write the original data into the change-journal
     *
     * @param string szTableName
     * @param array $vUsedFields
     * @param array $vRowArray
     */
    protected function saveToJournal(string $szTableName, array $vUsedFields, array $vRowArray)
    {
        $t_start = microtime(true); // --DEBUG-- time-measurement


        $pdo  = \Shop::Container()->getDB()->getPDO();
        $stmh = $pdo->prepare('INSERT INTO `tanondatajournal` VALUES(0, :pTableSource, :pReason, :pDataRow, :pTime)');

        $szTableSource  = $szTableName;
        $szChangeReason = $this->szReason;
        $szChangeTime   = $this->oNow->format('Y-m-d H:i:s');
        $szRowData      = '';
        foreach ($vRowArray as $oResult) {
            // save the old values
            $vFields   = array_intersect_key(get_object_vars($oResult), $vUsedFields);
            //$this->oLogger->debug(''.print_r($vFields,true )); // --DEBUG--
            $szRowData = json_encode($vFields);
            //$this->oLogger->debug('json fields: '.print_r($szRowData, true )); // --DEBUG--

            $stmh->bindParam(':pTableSource', $szTableSource, \PDO::PARAM_STR);
            $stmh->bindParam(':pReason', $szChangeReason, \PDO::PARAM_STR);
            $stmh->bindParam(':pDataRow', $szRowData, \PDO::PARAM_STR);
            $stmh->bindParam(':pTime', $szChangeTime, \PDO::PARAM_STR);
            $stmh->execute();
        }


        $t_elepsed = microtime(true) - $t_start; // --DEBUG-- time-measurement
        $this->oLogger->debug('ELAPSED TIME: '.$t_elepsed); // --DEBUG-- time-measurement
    }

    /**
     * helper-method to collect all fields,
     * we want to save before change the original
     *
     * @param array $vTableFields
     * @return array
     */
    protected function selectFields(array $vTableFields) : array
    {
        return array_filter(
            $vTableFields,
            function ($val) {
                return $val != null;
            }
        );
    }


}
