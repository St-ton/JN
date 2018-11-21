<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
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
        \Shop::Container()->getDB()->queryPrepared(
            "DELETE FROM tkunde
            WHERE
                nRegistriert = 0
                AND cAbgeholt = 'Y'
                AND dErstellt <= :pDateLimit
            ORDER BY dErstellt ASC
            LIMIT :pLimit",
            [
                'pDateLimit' => $this->szDateLimit,
                'pLimit'     => $this->iWorkLimit
            ],
            \DB\ReturnType::AFFECTED_ROWS
        );
    }
}
