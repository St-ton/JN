<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace GeneralDataProtection;

use DB\ReturnType;

/**
 * Class CleanupCustomerRelicts
 * @package GeneralDataProtection
 *
 * clean up multiple tables at each run
 * (normaly one times a day)
 *
 * names of the tables, we manipulate:
 *
 * `tbesucherarchiv`
 * `tkundenattribut`
 * `tkundenkontodaten`
 * `tkundenwerbenkunden`
 * `tkundenwerbenkundenbonus`
 * `tzahlungsinfo`
 * `tlieferadresse`
 * `trechnungsadresse`
 *
 * data will be removed here!
 */
class CleanupCustomerRelicts extends Method implements MethodInterface
{
    /**
     * runs all anonymize-routines
     */
    public function execute(): void
    {
        $this->cleanupVisitorArchive();
        $this->cleanupCustomerRecruitings();
        $this->cleanupCustomerAttributes();
        $this->cleanupPaymentInformation();
        $this->cleanupCustomerAccountData();
        $this->cleanupDeliveryAddresses();
        $this->cleanupBillingAddresses();
    }

    /**
     * delete visitors in the visitors archive immediately (at each run of the cron),
     * without a valid customer account
     */
    private function cleanupVisitorArchive(): void
    {
        \Shop::Container()->getDB()->queryPrepared(
            'DELETE FROM tbesucherarchiv
            WHERE
                kKunde > 0
                AND NOT EXISTS (SELECT kKunde FROM tkunde WHERE tkunde.kKunde = tbesucherarchiv.kKunde)
                LIMIT :pLimit',
            ['pLimit' => $this->workLimit],
            ReturnType::DEFAULT
        );
    }

    /**
     * delete customer recruitings
     * where no valid customer accounts exist
     */
    private function cleanupCustomerRecruitings()
    {
        \Shop::Container()->getDB()->queryPrepared(
            'DELETE k, b
            FROM
                tkundenwerbenkunden k
                    LEFT JOIN tkundenwerbenkundenbonus b ON k.kKunde = b.kKunde
            WHERE
                k.kKunde > 0
                AND NOT EXISTS (SELECT kKunde FROM tkunde WHERE tkunde.kKunde = k.kKunde)',
            [],
            ReturnType::DEFAULT
        );
    }

    /**
     * delete customer attributes
     * for which there are no valid customer accounts
     */
    private function cleanupCustomerAttributes(): void
    {
        \Shop::Container()->getDB()->queryPrepared(
            'DELETE FROM tkundenattribut
            WHERE
                NOT EXISTS (SELECT kKunde FROM tkunde WHERE tkunde.kKunde = tkundenattribut.kKunde)
            LIMIT :pLimit',
            ['pLimit' => $this->workLimit],
            ReturnType::DEFAULT
        );
    }

    /**
     * delete orphaned payment information about customers
     * which have no valid account
     */
    private function cleanupPaymentInformation(): void
    {
        \Shop::Container()->getDB()->queryPrepared(
            'DELETE FROM tzahlungsinfo
            WHERE
                kKunde > 0
                AND NOT EXISTS (SELECT kKunde FROM tkunde WHERE tkunde.kKunde = tzahlungsinfo.kKunde)
            LIMIT :pLimit',
            ['pLimit' => $this->workLimit],
            ReturnType::DEFAULT
        );
    }

    /**
     * delete orphaned bank account information of customers
     * which have no valid account
     */
    private function cleanupCustomerAccountData(): void
    {
        \Shop::Container()->getDB()->queryPrepared(
            'DELETE FROM tkundenkontodaten
            WHERE
                kKunde > 0
                AND NOT EXISTS (SELECT kKunde FROM tkunde WHERE tkunde.kKunde = tkundenkontodaten.kKunde)
            LIMIT :pLimit',
            ['pLimit' => $this->workLimit],
            ReturnType::DEFAULT
        );
    }

    /**
     * delete delivery addresses
     * which assigned to no valid customer account
     *
     * (ATTENTION: no work limit possible here)
     */
    private function cleanupDeliveryAddresses(): void
    {
        \Shop::Container()->getDB()->query(
            "DELETE k
            FROM tlieferadresse k
                JOIN tbestellung b ON b.kKunde = k.kKunde
            WHERE
                b.cAbgeholt = 'Y'
                AND b.cStatus IN (" . \BESTELLUNG_STATUS_VERSANDT . ', ' . \BESTELLUNG_STATUS_STORNO . ')
                AND NOT EXISTS (SELECT kKunde FROM tkunde WHERE tkunde.kKunde = k.kKunde)',
            ReturnType::DEFAULT
        );
    }

    /**
     * delete billing addresses witout valid customer accounts
     *
     * (ATTENTION: no work limit possible here)
     */
    private function cleanupBillingAddresses(): void
    {
        \Shop::Container()->getDB()->query(
            "DELETE k
            FROM trechnungsadresse k
                JOIN tbestellung b ON b.kKunde = k.kKunde
            WHERE
                b.cAbgeholt = 'Y'
                AND b.cStatus IN (" . \BESTELLUNG_STATUS_VERSANDT . ', ' . \BESTELLUNG_STATUS_STORNO . ')
                AND NOT EXISTS (SELECT kKunde FROM tkunde WHERE tkunde.kKunde = k.kKunde)',
            ReturnType::DEFAULT
        );
    }
}
