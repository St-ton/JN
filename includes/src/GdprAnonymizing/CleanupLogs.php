<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace GdprAnonymizing;

class CleanupLogs implements MethodInterface
{
    public function __construct()
    {
        //
    }

    /**
     * Delete old logs containing personal data.
     * (interval former "interval_clear_logs" = 90 days)
     *
     * @param int
     */
    public function execute(int $nIntervalDays)
    {
        $vQueries   = array();
        $vQueries[] = "DELETE FROM temailhistory WHERE dSent <= NOW() - INTERVAL " . $nIntervalDays . " DAY";
        $vQueries[] = "DELETE FROM tkontakthistory WHERE dErstellt <= NOW() - INTERVAL " . $nIntervalDays . " DAY";
        $vQueries[] = "DELETE FROM tkundenwerbenkunden WHERE dErstellt <= NOW() - INTERVAL " . $nIntervalDays . " DAY";
        $vQueries[] = "DELETE FROM tzahlungslog WHERE dDatum <= NOW() - INTERVAL " . $nIntervalDays . " DAY";
        $vQueries[] = "DELETE FROM tproduktanfragehistory WHERE dErstellt <= NOW() - INTERVAL " . $nIntervalDays . " DAY";
        $vQueries[] = "DELETE FROM tverfuegbarkeitsbenachrichtigung WHERE dBenachrichtigtAm <= NOW() - INTERVAL " . $nIntervalDays . " DAY";

        $vQueries[] = "DELETE FROM tjtllog WHERE (cLog LIKE '%@%' OR cLog LIKE '%kKunde%') AND dErstellt <= NOW() - INTERVAL " . $nIntervalDays . " DAY";
        $vQueries[] = "DELETE FROM tzahlungseingang WHERE cAbgeholt != 'N' AND dZeit <= NOW() - INTERVAL " . $nIntervalDays . " DAY";

        // Customer-history-logs will be deleted at the end of the following year after log-creation according to german law § 76 BDSG (neu)
        $vQueries[] = "DELETE FROM tkundendatenhistory WHERE dErstellt <= LAST_DAY(DATE_ADD(NOW() - INTERVAL 2 YEAR, INTERVAL 12 - MONTH(NOW()) MONTH))";

        foreach ($vQueries as $szQuery) {
            \Shop::Container()->getDB()->queryPrepared($szQuery, [], \DB\ReturnType::SINGLE_OBJECT);
        }
    }
}
