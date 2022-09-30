<?php declare(strict_types=1);

namespace JTL\GeneralDataProtection;

use JTL\DB\ReturnType;

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
    /**
     * max repetitions of this task
     *
     * @var int
     */
    public $taskRepetitions = 0;

    /**
     * runs all anonymize routines
     *
     * @return void
     */
    public function execute(): void
    {
        $this->cleanupNewsletters();
        $this->isFinished = ($this->workSum < $this->workLimit);
    }

    /**
     * delete newsletter registrations with no "opt-in"
     * within the given interval
     *
     * @return void
     */
    private function cleanupNewsletters(): void
    {
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
            $this->workSum += $this->db->queryPrepared(
                'DELETE e, h
                    FROM tnewsletterempfaenger e
                       INNER JOIN tnewsletterempfaengerhistory h
                           ON h.cOptCode = e.cOptCode
                           AND h.cEmail = e.cEmail
                    WHERE e.cOptCode = :optCode',
                ['optCode' => $res->cOptCode],
                ReturnType::AFFECTED_ROWS
            );
        }
    }
}
