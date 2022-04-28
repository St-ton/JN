<?php declare(strict_types=1);

namespace JTL\GeneralDataProtection;

/**
 * Class CleanupNewsletterRecipients
 * @package JTL\GeneralDataProtection
 *
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
    protected $isFinished = true;    // TEMPORARY

    /**
     * runs all anonymize routines
     */
    public function execute(): void
    {
        $this->cleanupNewsletters();
    }

    /**
     * delete newsletter registrations with no "opt-in"
     * within the given interval
     */
    private function cleanupNewsletters(): void
    {
        // --DEBUG-- -------------------------------------------------------------
        require_once('/www/shop5_02/includes/vendor/apache/log4php/src/main/php/Logger.php');
        \Logger::configure('/www/shop5_02/_logging_conf.xml');
        $oLogger = \Logger::getLogger('default');
        // --DEBUG-- -------------------------------------------------------------
        $oLogger->debug('workLimit: '.$this->workLimit);

        $data = $this->db->getObjects(
            "SELECT e.cOptCode
                FROM tnewsletterempfaenger e
                    JOIN tnewsletterempfaengerhistory h
                        ON h.cOptCode = e.cOptCode
                        AND h.cEmail = e.cEmail
                WHERE
                    e.nAktiv = 0
                    AND h.cAktion = 'Eingetragen'
                    AND (h.dOptCode = '0000-00-00 00:00:00' OR h.dOptCode IS NULL)
                    AND h.dEingetragen <= :dateLimit
                ORDER BY h.dEingetragen ASC
                LIMIT :workLimit",
            [
                'dateLimit' => $this->dateLimit,
                'workLimit' => $this->workLimit
            ]
        );
        foreach ($data as $res) {
            $this->db->queryPrepared(
                'DELETE e, h
                    FROM tnewsletterempfaenger e
                       INNER JOIN tnewsletterempfaengerhistory h
                           ON h.cOptCode = e.cOptCode
                           AND h.cEmail = e.cEmail
                    WHERE e.cOptCode = :optCode',
                ['optCode' => $res->cOptCode]
            );
        }
    }
}
