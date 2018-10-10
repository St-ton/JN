<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace GeneralDataProtection;

/**
 * --DEVELOPMENT-- 
 *
 * writes a journal of customer-data-changes,
 * e.g. deletion of a customer
 *
 * usage:
 * (new Journal($Somethings->Time))->save(...);
 * (new Journal(new DateTime()))->save(...);
 * (new Journal())->save(...);
 * $oJournal = new Journal([\DateTime]); for($whatever) { $oJournal->save(...); }
 */
class Journal
{
    /**
     * object-wide date at the point of instanciating
     *
     * @var object DateTime
     */
    protected $oNow;

    /**
     * @param \DateTime $oNow
     */
    public function __construct(\DateTime $oNow = null)
    {
        if ($oNow === null) {
            $oNow = new \DateTime();
        }
        $this->oNow = $oNow;
    }

    /**
     * saves the occurence of a data-modify-event to the journal
     * @param string    $szTableName
     * @param int       $iRowId
     * @param \stdClass $oCustomerData
     * @param string    $szReason
     */
    public function save(string $szAction, string $szIssuer): void
    {
        $szValueJSON = \Shop::Container()->getDB()->quote(json_encode($oRow));
        \Shop::Container()->getDB()->queryPrepared(
            'INSERT INTO tanondatajournal(cTableSource, cReason, kId, cOldValue, dEventTime)
            VALUES(:pTableSource, :pReason, :pId, :pOldValue, :pEventTime)',
            [
                'pTableSource' => $szTableName,
                'pReason'      => $szReason,
                'pId'          => $iRowId,
                'pOldValue'    => $szValueJSON,
                'pEventTime'   => $this->oNow->format('Y-m-d H:i:s')
            ],
            \DB\ReturnType::AFFECTED_ROWS
        );
    }

}

