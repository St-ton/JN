<?php declare(strict_types=1);

namespace JTL\GeneralDataProtection;

use JTL\Customer\Customer;
use JTL\DB\ReturnType;

/**
 * Class AnonymizeDeletedCustomer
 * @package JTL\GeneralDataProtection
 */
class AnonymizeDeletedCustomer extends Method implements MethodInterface
{
    private array $methodName = [
        'anonymizeRatings',
        'anonymizeReceivedPayments',
        'anonymizeNewsComments'
    ];

    /**
     * max repetitions of this task
     *
     * @var int
     */
    public $taskRepetitions = 0;


    /**
     * runs all anonymize-routines
     *
     * @return void
     */
    public function execute(): void
    {
        $workLimitStart = $this->workLimit;
        foreach ($this->methodName as $method) {
            if ($this->workLimit === 0) {
                $this->isFinished = false;
                return;
            }
            $affected         = $this->$method();
            $this->workLimit -= $affected; // reduce $workLimit locallly for the next method
            $this->workSum   += $affected; // summarize complete work
        }
        $this->isFinished = ($this->workSum < $workLimitStart);
    }

    /**
     * anonymize orphaned ratings.
     * (e.g. of canceled memberships)
     *
     * @return int
     */
    private function anonymizeRatings(): int
    {
        return $this->db->queryPrepared(
            'UPDATE tbewertung b
            SET
                b.cName  = :anonString,
                b.kKunde = 0
            WHERE
                b.cName != :anonString
                AND b.kKunde > 0
                AND dDatum <= :dateLimit
                AND NOT EXISTS (
                    SELECT kKunde
                    FROM tkunde
                    WHERE
                        tkunde.kKunde = b.kKunde
                        AND tkunde.cVorname != :anonString
                        AND tkunde.cNachname != :anonString
                        AND tkunde.cKundenNr != :anonString
                )
            LIMIT :workLimit',
            [
                'dateLimit'  => $this->dateLimit,
                'workLimit'  => $this->workLimit,
                'anonString' => Customer::CUSTOMER_ANONYM
            ],
            ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * anonymize received payments.
     * (replace `cZahler`(e-mail) in `tzahlungseingang`)
     *
     * @return int
     */
    private function anonymizeReceivedPayments(): int
    {
        return $this->db->queryPrepared(
            "UPDATE tzahlungseingang z
            SET
                z.cZahler = '-'
            WHERE
                z.cZahler != '-'
                AND z.cAbgeholt != 'N'
                AND NOT EXISTS (
                    SELECT k.kKunde
                    FROM tkunde k
                        INNER JOIN tbestellung b ON k.kKunde = b.kKunde
                    WHERE
                        b.kBestellung = z.kBestellung
                        AND k.cKundenNr != :anonString
                        AND k.cVorname != :anonString
                        AND k.cNachname != :anonString
                )
                AND z.dZeit <= :dateLimit
            ORDER BY z.dZeit ASC
            LIMIT :workLimit",
            [
                'dateLimit' => $this->dateLimit,
                'workLimit' => $this->workLimit,
                'anonString' => Customer::CUSTOMER_ANONYM
            ],
            ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * anonymize comments of news without registered customers
     * (delete names and e-mails from `tnewskommentar` and remove the customer-relation)
     *
     * CONSIDER: using no time base or limit!
     *
     * @return int
     */
    private function anonymizeNewsComments(): int
    {
        return $this->db->queryPrepared(
            'UPDATE tnewskommentar n
            SET
                n.cName = :anonString,
                n.cEmail = :anonString,
                n.kKunde = 0
            WHERE
                n.cName != :anonString
                AND n.cEmail != :anonString
                AND n.kKunde > 0
                AND NOT EXISTS (
                    SELECT kKunde
                    FROM tkunde
                    WHERE
                        tkunde.kKunde = n.kKunde
                        AND tkunde.cVorname != :anonString
                        AND tkunde.cNachname != :anonString
                        AND tkunde.cKundenNr != :anonString
                )
            LIMIT :workLimit',
            [
                'workLimit'  => $this->workLimit,
                'anonString' => Customer::CUSTOMER_ANONYM
            ],
            ReturnType::AFFECTED_ROWS
        );
    }
}
