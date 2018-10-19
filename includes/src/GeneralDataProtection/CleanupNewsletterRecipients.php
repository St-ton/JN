<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace GeneralDataProtection;

/**
 * Delete newsletter-registrations with no opt-in within given interval
 * (interval former "interval_clear_logs" = 90 days)
 *
 * names of the tables, we manipulate:
 *
 * `tnewsletterempfaenger`
 * `tnewsletterempfaengerhistory`
 */
class CleanupNewsletterRecipients extends Method implements MethodInterface
{
    /**
     * runs all anonymize-routines
     */
    public function execute()
    {
        $this->clean_tnewsletter();
    }

    /**
     * delete newsletter-registrations withi no "opt-in"
     * within the given interval
     */
    private function clean_tnewsletter()
    {
        $vResult = \Shop::Container()->getDB()->queryPrepared(
            "SELECT cOptCode
            FROM tnewsletterempfaenger e
                JOIN tnewsletterempfaengerhistory h ON h.cOptCode = e.cOptCode AND h.cEmail = e.cEmail
            WHERE
                e.nAktiv = 0
                AND h.cAktion = 'Eingetragen'
                AND (h.dOptCode = '0000-00-00 00:00:00' OR h.dOptCode IS NULL)
                AND h.dEingetragen <= (:pNow - INTERVAL :pInterval DAY)
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
                'DELETE e, h
                FROM tnewsletterempfaenger e
                   INNER JOIN tnewsletterempfaengerhistory h ON h.cOptCode = e.cOptCode AND h.cEmail = e.cEmail
                WHERE
                   e.cOptCode = :pOpCode',
                ['pOpCode' => $oResult->cOptCode],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
    }
}

