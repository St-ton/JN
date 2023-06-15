<?php declare(strict_types=1);

namespace JTL\GeneralDataProtection;

use JTL\DB\ReturnType;

/**
 * Class CleanupLogs
 * @package JTL\GeneralDataProtection
 *
 * Delete old logs containing personal data.
 * (interval former "interval_clear_logs" = 90 days)
 *
 * names of the tables, we manipulate:
 *
 * `temailhistory`
 * `tkontakthistory`
 * `tzahlungslog`
 * `tproduktanfragehistory`
 * `tverfuegbarkeitsbenachrichtigung`
 * `tjtllog`
 * `tzahlungseingang`
 * `tkundendatenhistory`
 * `tfloodprotect`
 */
class CleanupLogs extends Method implements MethodInterface
{
    private array $methodName = [
        'cleanupEmailHistory',
        'cleanupContactHistory',
        'cleanupFloodProtect',
        'cleanupPaymentLogEntries',
        'cleanupProductInquiries',
        'cleanupAvailabilityInquiries',
        'cleanupLogs',
        'cleanupPaymentConfirmations',
        'cleanupCustomerDataHistory',
    ];

    /**
     * max repetitions of this task
     *
     * @var int
     */
    public $taskRepetitions = 0;

    /**
     * last ID in table
     *
     * @var int
     */
    public $lastProductID;

    /**
     * runs all anonymize methods
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
     * delete email history
     * older than given interval
     *
     * @return int
     */
    private function cleanupEmailHistory(): int
    {
        return $this->db->queryPrepared(
            'DELETE FROM temailhistory
                WHERE dSent <= :dateLimit
                ORDER BY dSent ASC
                LIMIT :workLimit',
            [
                'dateLimit' => $this->dateLimit,
                'workLimit' => $this->workLimit
            ],
            ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * delete customer history
     * older than given interval
     *
     * @return int
     */
    private function cleanupContactHistory(): int
    {
        return $this->db->queryPrepared(
            'DELETE FROM tkontakthistory
                WHERE dErstellt <= :dateLimit
                ORDER BY dErstellt ASC
                LIMIT :workLimit',
            [
                'dateLimit' => $this->dateLimit,
                'workLimit' => $this->workLimit
            ],
            ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * delete upload request history
     * older than given interval
     *
     * @return int
     */
    private function cleanupFloodProtect(): int
    {
        return $this->db->queryPrepared(
            'DELETE FROM tfloodprotect
                WHERE dErstellt <= :dateLimit
                ORDER BY dErstellt ASC
                LIMIT :workLimit',
            [
                'dateLimit' => $this->dateLimit,
                'workLimit' => $this->workLimit
            ],
            ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * delete log entries of payments
     * older than the given interval
     *
     * @return int
     */
    private function cleanupPaymentLogEntries(): int
    {
        return $this->db->queryPrepared(
            'DELETE FROM tzahlungslog
                WHERE dDatum <= :dateLimit
                ORDER BY dDatum ASC
                LIMIT :workLimit',
            [
                'dateLimit' => $this->dateLimit,
                'workLimit' => $this->workLimit
            ],
            ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * delete product inquiries of customers
     * older than the given interval
     *
     * @return int
     */
    private function cleanupProductInquiries(): int
    {
        return $this->db->queryPrepared(
            'DELETE FROM tproduktanfragehistory
                WHERE dErstellt <= :dateLimit
                ORDER BY dErstellt ASC
                LIMIT :workLimit',
            [
                'dateLimit' => $this->dateLimit,
                'workLimit' => $this->workLimit
            ],
            ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * delete availability demands of customers
     * older than the given interval
     *
     * @return int
     */
    private function cleanupAvailabilityInquiries(): int
    {
        return $this->db->queryPrepared(
            'DELETE FROM tverfuegbarkeitsbenachrichtigung
                WHERE dBenachrichtigtAm <= :dateLimit
                ORDER BY dBenachrichtigtAm ASC
                LIMIT :workLimit',
            [
                'dateLimit' => $this->dateLimit,
                'workLimit' => $this->workLimit
            ],
            ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * delete jtl log entries
     * older than the given interval
     *
     * @return int
     */
    private function cleanupLogs(): int
    {
        return $this->db->queryPrepared(
            "DELETE FROM tjtllog
                WHERE
                    (cLog LIKE '%@%' OR cLog LIKE '%kKunde%')
                    AND dErstellt <= :dateLimit
                ORDER BY dErstellt ASC
                LIMIT :workLimit",
            [
                'dateLimit' => $this->dateLimit,
                'workLimit' => $this->workLimit
            ],
            ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * delete payment confirmations of customers
     * not collected by 'wawi' and older than the given interval
     *
     * @return int
     */
    private function cleanupPaymentConfirmations(): int
    {
        return $this->db->queryPrepared(
            "DELETE FROM tzahlungseingang
                WHERE
                    cAbgeholt != 'Y'
                    AND dZeit <= :dateLimit
                ORDER BY dZeit ASC
                LIMIT :workLimit",
            [
                'dateLimit' => $this->dateLimit,
                'workLimit' => $this->workLimit
            ],
            ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * delete customer data history
     * CONSIDER: using no time base or limit here!
     *
     * (§76 BDSG Abs(4) : "Die Protokolldaten sind am Ende des auf deren Generierung folgenden Jahres zu löschen.")
     *
     * @return int
     */
    private function cleanupCustomerDataHistory(): int
    {
        return $this->db->queryPrepared(
            'DELETE FROM tkundendatenhistory
                WHERE dErstellt < MAKEDATE(YEAR(:nowTime) - 1, 1)
                ORDER BY dErstellt ASC
                LIMIT :workLimit',
            [
                'nowTime'   => $this->now->format('Y-m-d H:i:s'),
                'workLimit' => $this->workLimit
            ],
            ReturnType::AFFECTED_ROWS
        );
    }
}
