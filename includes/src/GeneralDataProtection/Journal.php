<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace GeneralDataProtection;

/**
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
        $this->oNow     = $oNow;
    }

    public const ISSUER_CUSTOMER    = 'CUSTOMER';
    public const ISSUER_APPLICATION = 'APPLICATION';
    public const ISSUER_ADMIN       = 'ADMIN';
    public const ISSUER_PLUGIN      = 'PLUGIN';

    public const ACTION_CUSTOMER_DEACTIVATED = 'CUSTOMER_DEACTIVATED';
    public const ACTION_CUSTOMER_DELETED    = 'CUSTOMER_DELETED';

    /**
     * @param string $issuer
     * @param int $issuerID
     * @param string $action
     * @param string $message
     */
    public function addEntry(string $issuer, int $issuerID, string $action, string $message): void
    {
        \Shop::Container()->getDB()->queryPrepared(
            'INSERT INTO tanondatajournal(cIssuer, iIssuerId, cAction, cMessage, dEventTime)
                VALUES(:pIssuer, :pIssuerId, :cAction, :cMessage, :pEventTime)',
            [
                'cMessage'   => $message,
                'cAction'    => $action,
                'pIssuer'    => $issuer,
                'pIssuerId'  => $issuerID,
                'pEventTime' => $this->oNow->format('Y-m-d H:i:s')
            ],
            \DB\ReturnType::AFFECTED_ROWS
        );
    }
}
