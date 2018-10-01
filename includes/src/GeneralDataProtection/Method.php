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


    public function __construct(\DateTime $oObjNow, int $iInterval)
    {
        $this->oNow      = $oObjNow;
        $this->iInterval = $iInterval;
    }

    /**
     * write the original data into the change-journal (`tanondatajournal`)
     * (writes bunches of 2000 values for each insert,
     * as long as there are data left)
     *
     * @param string szTableName
     * @param array $vUsedFields
     * @param array $vRowArray
     */
    protected function saveToJournal(string $szTableName, array $vUsedFields, array $vRowArray)
    {
        $szValueLine = '';
        $nRowCount   = 0;
        foreach ($vRowArray as $oRow) {
            $vFields   = array_intersect_key(get_object_vars($oRow), $vUsedFields);
            $szRowData = json_encode($vFields);
            if ($szValueLine !== '') {
                $szValueLine .= ',';
            }
            $szValueLine .= '(\'' . $szTableName . '\',\'' . $this->szReason . '\',\'' . $szRowData . '\',\'' . $this->oNow->format('Y-m-d H:i:s') . '\')';

            if ($nRowCount === 1999) {
                $vResult = \Shop::Container()->getDB()->queryPrepared(
                    'INSERT INTO tanondatajournal(cTableSource,cReason,cOldValue,dEventTime) VALUES' . \Shop::Container()->getDB()->quote($szValueLine),
                    [],
                    \DB\ReturnType::AFFECTED_ROWS
                );
                // reset the row-counter and value-line
                $nRowCount   = -1;
                $szValueLine = '';
            }
            $nRowCount++;
        }
        if ($nRowCount > 0) {
            $vResult = \Shop::Container()->getDB()->queryPrepared(
                'INSERT INTO tanondatajournal(cTableSource,cReason,cOldValue,dEventTime) VALUES' . \Shop::Container()->getDB()->quote($szValueLine),
                [],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
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

                return $val !== null;
            }
        );
    }

}

