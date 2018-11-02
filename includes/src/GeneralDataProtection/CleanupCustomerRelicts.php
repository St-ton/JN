<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace GeneralDataProtection;

/**
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
    public function execute()
    {
        $this->del_tbesucherarchiv();
        $this->del_tkundenwerbenkunden();
        $this->del_tkundenattribut();
        $this->del_tzahlungsinfo();
        $this->del_tkundenkontodaten();
        $this->del_tlieferadresse();
        $this->del_trechnungsadresse();
    }

    /**
     * delete visitors in the visitors-archive immediately (at each run of the cron),
     * without a valid customer-account
     */
    private function del_tbesucherarchiv()
    {
        \Shop::Container()->getDB()->queryPrepared(
            'DELETE FROM tbesucherarchiv
            WHERE
                kKunde > 0
                AND NOT EXISTS (SELECT kKunde FROM tkunde WHERE tkunde.kKunde = tbesucherarchiv.kKunde)
                LIMIT :pLimit',
            [
                'pLimit' => $this->iWorkLimit
            ],
            \DB\ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * delete customer-recruitings
     * where no valid customer-accounts exists
     */
    private function del_tkundenwerbenkunden()
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
            \DB\ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * delete customer-attributes
     * for which there are no valid customer-accounts
     */
    private function del_tkundenattribut()
    {
        \Shop::Container()->getDB()->queryPrepared(
            'DELETE FROM tkundenattribut
            WHERE
                NOT EXISTS (SELECT kKunde FROM tkunde WHERE tkunde.kKunde = tkundenattribut.kKunde)
            LIMIT :pLimit',
            [
                'pLimit' => $this->iWorkLimit
            ],
            \DB\ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * delete orphaned payment-data of customers,
     * which have no valid account
     */
    private function del_tzahlungsinfo()
    {
        \Shop::Container()->getDB()->queryPrepared(
            'DELETE FROM tzahlungsinfo
            WHERE
                kKunde > 0
                AND NOT EXISTS (SELECT kKunde FROM tkunde WHERE tkunde.kKunde = tzahlungsinfo.kKunde)
            LIMIT :pLimit',
            [
                'pLimit' => $this->iWorkLimit
            ],
            \DB\ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * delete orphaned bank-account-information of customers,
     * which have no valid account
     */
    private function del_tkundenkontodaten()
    {
        \Shop::Container()->getDB()->queryPrepared(
            'DELETE FROM tkundenkontodaten
            WHERE
                kKunde > 0
                AND NOT EXISTS (SELECT kKunde FROM tkunde WHERE tkunde.kKunde = tkundenkontodaten.kKunde)
            LIMIT :pLimit',
            [
                'pLimit' => $this->iWorkLimit
            ],
            \DB\ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * delete delivery-addresses
     * which assigned to no valid customer-account
     *
     * (ATTENTION: no work-limit possible here)
     */
    private function del_tlieferadresse()
    {
        \Shop::Container()->getDB()->queryPrepared(
            "DELETE k
            FROM tlieferadresse k
                JOIN tbestellung b ON b.kKunde = k.kKunde
            WHERE
                b.cAbgeholt = 'Y'
                AND b.cStatus IN (" . BESTELLUNG_STATUS_VERSANDT . ", " . BESTELLUNG_STATUS_STORNO . ")
                AND NOT EXISTS (SELECT kKunde FROM tkunde WHERE tkunde.kKunde = k.kKunde)",
            [],
            \DB\ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * delete billing-addresses
     * which assigned to no valid customer-account
     *
     * (ATTENTION: no work-limit possible here)
     */
    private function del_trechnungsadresse()
    {
        \Shop::Container()->getDB()->queryPrepared(
            "DELETE k
            FROM trechnungsadresse k
                JOIN tbestellung b ON b.kKunde = k.kKunde
            WHERE
                b.cAbgeholt = 'Y'
                AND b.cStatus IN (" . BESTELLUNG_STATUS_VERSANDT . ", " . BESTELLUNG_STATUS_STORNO . ")
                AND NOT EXISTS (SELECT kKunde FROM tkunde WHERE tkunde.kKunde = k.kKunde)",
            [],
            \DB\ReturnType::AFFECTED_ROWS
        );
    }
}

