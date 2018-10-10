<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace GeneralDataProtection;

/**
 * Remove guest accounts fetched by JTL-Wawi and older than x days
 * (interval former "interval_delete_guest_accounts" = 365 days)
 *
 * names of the tables, we manipulate:
 *
 * `tkunde`
 */
class CleanupOldGuestAccounts extends Method implements MethodInterface
{
    /**
     * @var string
     */
    protected $szReasonName;

    /**
     * AnonymizeDeletedCustomer constructor
     *
     * @param $oNow
     * @param $iInterval
     */
    public function __construct($oNow, $iInterval)
    {
        parent::__construct($oNow, $iInterval);
        $this->szReasonName = substr(__CLASS__, strrpos(__CLASS__, '\\')) . ': ';
    }

    /**
     * runs all anonymize-routines
     */
    public function execute()
    {
        $this->clean_tkunde();
    }

    /**
     * delete old guest accounts
     */
    private function clean_tkunde()
    {
        $this->szReason = $this->szReasonName . 'delete customer-data-history';
        $vResult        = \Shop::Container()->getDB()->queryPrepared('SELECT *
            FROM tkunde e
            WHERE
                nRegistriert = 0
                AND cAbgeholt = "Y"
                AND dErstellt <= :pNow - INTERVAL :pInterval DAY
                LIMIT :pLimit',
            [
                'pInterval' => $this->iInterval,
                'pNow'      => $this->oNow->format('Y-m-d H:i:s'),
                'pLimit'    => $this->iWorkLimit
            ],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (!\is_array($vResult)) {

            return;
        }
        foreach ($vResult as $oResult) {
            \Shop::Container()->getDB()->queryPrepared('DELETE FROM tkunde
                WHERE kKunde = pKeyKunde',
                ['pKeyKunde' => $oResult->kKunde],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
    }

}

