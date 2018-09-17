<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace GeneralDataProtection;

class Method
{
    /**
     * @var DateTime-object
     * object-wide date at the point of instanciating
     */
    protected $oNow      = null;

    /**
     * @var int
     * interval in "number of days"
     */
    protected $iInterval = 0;

    public function __construct(\DateTime $oObjNow, int $iInterval)
    {
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
        $szValueLine = '';
        $nRowCount   = 0;
        foreach ($vRowArray as $oRow) {
            // save the old values
            $vFields   = array_intersect_key(get_object_vars($oRow), $vUsedFields);
            $szRowData = json_encode($vFields);
            if ('' !== $szValueLine) {
                $szValueLine .= ',';
            }
            $szValueLine .= '(\'' . $szTableName . '\',\'' . $this->szReason . '\',\'' . $szRowData . '\',\'' . $this->oNow->format('Y-m-d H:i:s') . '\')';

            if (1999 === $nRowCount) {
                $vResult = \Shop::Container()->getDB()->queryPrepared(
                    'INSERT INTO `tanondatajournal`(`cTableSource`,`cReason`,`cOldValue`,`dEventTime`) VALUES' . $szValueLine,
                    [],
                    \DB\ReturnType::AFFECTED_ROWS
                );
                // reset the row-counter and value-line
                $nRowCount   = -1;
                $szValueLine = '';
            }
            $nRowCount++;
        }
        if (0 <= $nRowCount) {
            $vResult = \Shop::Container()->getDB()->queryPrepared(
                'INSERT INTO `tanondatajournal`(`cTableSource`,`cReason`,`cOldValue`,`dEventTime`) VALUES' . $szValueLine,
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
                return $val != null;
            }
        );
    }

}
