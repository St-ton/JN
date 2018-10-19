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
        $vResult = \Shop::Container()->getDB()->queryPrepared(
            'SELECT kEmailhistory
            FROM temailhistory e
            WHERE dSent <= (:pNow - INTERVAL :pInterval DAY)
            LIMIT :pLimit',
            [
                'pInterval' => $this->iInterval,
                'pLimit'    => $this->iWorkLimit,
                'pNow'      => $this->oNow->format('Y-m-d H:i:s')
            ],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (!\is_array($vResult)) {
            return;
        }
        foreach ($vResult as $oResult) {
            \Shop::Container()->getDB()->queryPrepared(
                'DELETE FROM temailhistory
                WHERE kEmailhistory = :pKeyEmailhistory',
                ['pKeyEmailhistory' => $oResult->kEmailhistory],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
    }

    /**
     * delete customer-hostory
     * older than given interval
     */
    private function clean_tkontakthistory()
    {
        $vResult = \Shop::Container()->getDB()->queryPrepared(
            'SELECT kKontaktHistory
            FROM tkontakthistory e
            WHERE dErstellt <= (:pNow - INTERVAL :pInterval DAY)
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
            \Shop::Container()->getDB()->queryPrepared(
                'DELETE FROM tkontakthistory
                WHERE kKontaktHistory = :pKeyKontaktHistory',
                ['pKeyKontaktHistory' => $oResult->kKontaktHistory],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
    }

    /**
     * delete customer-recruitings
     * older than the given interval
     */
    private function clean_tkundenwerbenkunden()
    {
        $vResult = \Shop::Container()->getDB()->queryPrepared(
            'SELECT kKundenWerbenKunden
            FROM tkundenwerbenkunden e
            WHERE dErstellt <= (:pNow - INTERVAL :pInterval DAY)
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
            \Shop::Container()->getDB()->queryPrepared(
                'DELETE tkundenwerbenkunden
                WHERE kKundenWerbenKunden = :pKeyKundenWerbenKunden',
                ['pKeyKundenWerbenKunden' => $oResult->kKundenWerbenKunden],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
    }

    /**
     * delete log-entries of payments
     * older than the given interval
     */
    private function clean_tzahlungslog()
    {
        $vResult = \Shop::Container()->getDB()->queryPrepared(
            'SELECT kZahlunglog
            FROM tzahlungslog e
            WHERE dDatum <= (:pNow - INTERVAL :pInterval DAY)
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
            \Shop::Container()->getDB()->queryPrepared(
                'DELETE FROM tzahlungslog
                WHERE kZahlunglog = :pKeyZahlunglog',
                ['pKeyZahlunglog' => $oResult->kZahlunglog],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
    }

    /**
     * delete product demands of customers,
     * older than the given interval
     */
    private function clean_tproduktanfragehistory()
    {
        $vResult = \Shop::Container()->getDB()->queryPrepared(
            'SELECT kProduktanfrageHistory
            FROM tproduktanfragehistory e
            WHERE dErstellt <= (:pNow - INTERVAL :pInterval DAY)
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
            \Shop::Container()->getDB()->queryPrepared(
                'DELETE FROM tproduktanfragehistory
                WHERE kProduktanfrageHistory = :pKeyProduktanfrageHistory',
                ['pKeyProduktanfrageHistory' => $oResult->kProduktanfrageHistory],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
    }

    /**
     * delete availability demands of customers,
     * older than the given interval
     */
    private function clean_tverfuegbarkeitsbenachrichtigung()
    {
        $vResult = \Shop::Container()->getDB()->queryPrepared(
            'SELECT kVerfuegbarkeitsbenachrichtigung
            FROM tverfuegbarkeitsbenachrichtigung e
            WHERE dErstellt <= (:pNow - INTERVAL :pInterval DAY)
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
            \Shop::Container()->getDB()->queryPrepared(
                'DELETE FROM tverfuegbarkeitsbenachrichtigung
                WHERE kVerfuegbarkeitsbenachrichtigung = :pKeyVerfuegbarkeitsbenachrichtigung',
                ['pKeyVerfuegbarkeitsbenachrichtigung' => $oResult->kVerfuegbarkeitsbenachrichtigung],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
    }

    /**
     * delete jtl-log-entries,
     * older than the given interval
     */
    private function clean_tjtllog()
    {
        $vResult = \Shop::Container()->getDB()->queryPrepared(
            "SELECT kLog
            FROM tjtllog
            WHERE
                (cLog LIKE '%@%' OR cLog LIKE '%kKunde%')
                AND dErstellt <= (:pNow - INTERVAL :pInterval DAY)
            LIMIT :pLimit",
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
            \Shop::Container()->getDB()->queryPrepared(
                'DELETE FROM tjtllog
                WHERE kLog = :pKeyLog',
                ['pKeyLog' => $oResult->kLog],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
    }

    /**
     * delete payment-confirmations of customers,
     * older than the given interval
     */
    private function clean_tzahlungseingang()
    {
        $vResult = \Shop::Container()->getDB()->queryPrepared(
            "SELECT kZahlungseingang
            FROM tzahlungseingang
            WHERE
                cAbgeholt != 'Y'
                AND dZeit <= (:pNow - INTERVAL :pInterval DAY)
            LIMIT :pLimit",
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
            \Shop::Container()->getDB()->queryPrepared(
                'DELETE FROM tzahlungseingang
                WHERE kZahlungseingang = :pKeyZahlungseingang',
                ['pKeyZahlungseingang' => $oResult->kZahlungseingang],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
    }

    /**
     * delete customer-data-historytory
     *
     * CONSIDER: using no time-base or limit here!
     */
    private function clean_tkundendatenhistory()
    {
        $vResult = \Shop::Container()->getDB()->queryPrepared(
            'SELECT kKundendatenHistory
            FROM tkundendatenhistory
            WHERE dErstellt <= LAST_DAY(DATE_ADD(:pNow - INTERVAL 2 YEAR, INTERVAL 12 - MONTH(:pNow) MONTH))',
            [
                'pNow' => $this->oNow->format('Y-m-d H:i:s'),
            ],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (!\is_array($vResult)) {
            return;
        }
        foreach ($vResult as $oResult) {
            // Customer-history-logs will be deleted at the end of the following year after log-creation according to german law § 76 BDSG (neu)
            \Shop::Container()->getDB()->queryPrepared(
                'DELETE FROM tkundendatenhistory
                WHERE kKundendatenHistory = :pKeyKundendatenHistory',
                ['pKeyKundendatenHistory' => $oResult->kKundendatenHistory],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
    }
}

