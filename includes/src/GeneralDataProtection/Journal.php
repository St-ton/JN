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

    public static const ISSUER_CUSTOMER    = 'CUSTOMER';
    public static const ISSUER_APPLICATION = 'APPLICATION';
    public static const ISSUER_ADMIN       = 'ADMIN';

    /**
     * saves the occurence of a data-modify-event to the journal
     *
     * @param string $szAction
     * @param string $szIssuer
     */
    public function save(string $szIssuer, int $iIssuerId, string $szAction): void
    {
        \Shop::Container()->getDB()->queryPrepared(
            'INSERT INTO tanondatajournal(cAction, cIssuer, iIssuerId, dEventTime)
            VALUES(pAction, pIssuer, pIssuerId, pEventTime)',
            [
                'pAction'    => $szAction,
                'pIssuer'    => $szIssuer,
                'pIssuerId'  => $iIssuerId,
                'pEventTime' => $this->oNow->format('Y-m-d H:i:s')
            ],
            \DB\ReturnType::AFFECTED_ROWS
        );
    }

}

