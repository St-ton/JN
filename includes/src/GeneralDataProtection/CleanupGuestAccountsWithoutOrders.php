<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace GeneralDataProtection;

/**
 * Deleted guest accounts with no open orders
 *
 * names of the tables, we manipulate:
 *
 * `tkunde`
 */
class CleanupGuestAccountsWithoutOrders extends Method implements MethodInterface
{
    /**
     * runs all anonymize-routines
     */
    public function execute()
    {
        $this->cleanup_tkunde();
    }

    /**
     * delete not registered customers (relicts)
     */
    private function cleanup_tkunde()
    {
        $vResult = \Shop::Container()->getDB()->queryPrepared(
            'SELECT *
            FROM tkunde k
                JOIN tbestellung b ON b.kKunde = k.kKunde
            WHERE
                b.cStatus IN (' . BESTELLUNG_STATUS_VERSANDT . ', ' . BESTELLUNG_STATUS_STORNO . ')
                AND k.nRegistriert = 0
                AND b.cAbgeholt = \'Y\'
            LIMIT :pLimit',
            [
                'pLimit' => $this->iWorkLimit
            ],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (!\is_array($vResult)) {
            return;
        }
        foreach ($vResult as $oResult) {
            \Shop::Container()->getDB()->queryPrepared(
                'DELETE FROM tkunde
                WHERE
                    kKunde = :pKeyKunde',
                [
                    'pKeyKunde' => $oResult->kKunde
                ],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
    }
}

