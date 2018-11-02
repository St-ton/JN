<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace GeneralDataProtection;

/**
 * Delete old logs containing personal data.
 * (interval former "interval_clear_logs" = 90 days)
 *
 * names of the tables, we manipulate:
 *
 * `temailhistory`
 * `tkontakthistory`
 * `tkundenwerbenkunden`
 * `tzahlungslog`
 * `tproduktanfragehistory`
 * `tverfuegbarkeitsbenachrichtigung`
 * `tjtllog`
 * `tzahlungseingang`
 * `tkundendatenhistory`
 */
class CleanupLogs extends Method implements MethodInterface
{
    /**
     * runs all anonymize-routines
     */
    public function execute()
    {
        $this->clean_temailhistory();
        $this->clean_tkontakthistory();
        $this->clean_tkundenwerbenkunden();
        $this->clean_tzahlungslog();
        $this->clean_tproduktanfragehistory();
        $this->clean_tverfuegbarkeitsbenachrichtigung();
        $this->clean_tjtllog();
        $this->clean_tzahlungseingang();
        $this->clean_tkundendatenhistory();
    }

    /**
     * delete email-history
     * older than given interval
     */
    private function clean_temailhistory()
    {
        \Shop::Container()->getDB()->queryPrepared(
            'DELETE FROM temailhistory
            WHERE dSent <= :pDateLimit
            ORDER BY dSent ASC
            LIMIT :pLimit',
            [
                'pDateLimit' => $this->szDateLimit,
                'pLimit'     => $this->iWorkLimit
            ],
            \DB\ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * delete customer-hostory
     * older than given interval
     */
    private function clean_tkontakthistory()
    {
        \Shop::Container()->getDB()->queryPrepared(
            'DELETE FROM tkontakthistory
            WHERE dErstellt <= :pDateLimit
            ORDER BY dErstellt ASC
            LIMIT :pLimit',
            [
                'pDateLimit' => $this->szDateLimit,
                'pLimit'     => $this->iWorkLimit
            ],
            \DB\ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * delete customer-recruitings
     * older than the given interval
     */
    private function clean_tkundenwerbenkunden()
    {
        \Shop::Container()->getDB()->queryPrepared(
            'DELETE FROM tkundenwerbenkunden
            WHERE dErstellt <= :pDateLimit
            ORDER BY dErstellt ASC
            LIMIT :pLimit',
            [
                'pDateLimit' => $this->szDateLimit,
                'pLimit'     => $this->iWorkLimit
            ],
            \DB\ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * delete log-entries of payments
     * older than the given interval
     */
    private function clean_tzahlungslog()
    {
        \Shop::Container()->getDB()->queryPrepared(
            'DELETE FROM tzahlungslog
            WHERE dDatum <= :pDateLimit
            ORDER BY dDatum ASC
            LIMIT :pLimit',
            [
                'pDateLimit' => $this->szDateLimit,
                'pLimit'     => $this->iWorkLimit
            ],
            \DB\ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * delete product demands of customers,
     * older than the given interval
     */
    private function clean_tproduktanfragehistory()
    {
        \Shop::Container()->getDB()->queryPrepared(
            //'SELECT kProduktanfrageHistory
            'DELETE FROM tproduktanfragehistory
            WHERE dErstellt <= :pDateLimit
            ORDER BY dErstellt ASC
            LIMIT :pLimit',
            [
                'pDateLimit' => $this->szDateLimit,
                'pLimit'     => $this->iWorkLimit
            ],
            \DB\ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * delete availability demands of customers,
     * older than the given interval
     */
    private function clean_tverfuegbarkeitsbenachrichtigung()
    {
        \Shop::Container()->getDB()->queryPrepared(
            'DELETE FROM tverfuegbarkeitsbenachrichtigung
            WHERE dErstellt <= :pDateLimit
            ORDER BY dErstellt ASC
            LIMIT :pLimit',
            [
                'pDateLimit' => $this->szDateLimit,
                'pLimit'     => $this->iWorkLimit
            ],
            \DB\ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * delete jtl-log-entries,
     * older than the given interval
     */
    private function clean_tjtllog()
    {
        \Shop::Container()->getDB()->queryPrepared(
            "DELETE FROM tjtllog
            WHERE
                (cLog LIKE '%@%' OR cLog LIKE '%kKunde%')
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

    /**
     * delete payment-confirmations of customers,
     * not collected by 'wawi' and
     * older than the given interval
     */
    private function clean_tzahlungseingang()
    {
        \Shop::Container()->getDB()->queryPrepared(
            "DELETE FROM tzahlungseingang
            WHERE
                cAbgeholt != 'Y'
                AND dZeit <= :pDateLimit
            ORDER BY dZeit ASC
            LIMIT :pLimit",
            [
                'pDateLimit' => $this->szDateLimit,
                'pLimit'     => $this->iWorkLimit
            ],
            \DB\ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * delete customer-data-historytory
     *
     * CONSIDER: using no time-base or limit here!
     */
    private function clean_tkundendatenhistory()
    {
        \Shop::Container()->getDB()->queryPrepared(
            'DELETE FROM tkundendatenhistory
            WHERE
                dErstellt <= LAST_DAY(DATE_ADD(:pNow - INTERVAL 2 YEAR, INTERVAL 12 - MONTH(:pNow) MONTH))
            ORDER BY dErstellt ASC
            LIMIT :pLimit',
            [
                'pNow'   => $this->oNow->format('Y-m-d H:i:s'),
                'pLimit' => $this->iWorkLimit
            ],
            \DB\ReturnType::AFFECTED_ROWS
        );
    }
}

