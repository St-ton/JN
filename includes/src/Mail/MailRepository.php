<?php

namespace JTL\Mail;

use JTL\Abstracts\AbstractRepository;
use JTL\DataObjects\DataTableObjectInterface;

class MailRepository extends AbstractRepository
{
    protected string $tableName = 'emails';
    protected string $keyName   = 'id';

    /**
     * @param DataTableObjectInterface $mailDataTableObject
     * @return int
     */
    public function queueMailDataTableObject(DataTableObjectInterface $mailDataTableObject): int
    {
        return $this->insert($mailDataTableObject);
    }

    /**
     * @param int $chunkSize
     * @return array
     */
    public function getNextMailsFromQueue(int $chunkSize = 20): array
    {
        $stmt = 'SELECT * FROM ' . $this->getTableName() .
            ' WHERE isSent = 0 AND isSendingNow = 0 AND sendCount < 3 AND errorCount < 3' .
            ' ORDER BY id LIMIT :chunkSize';

        return $this->getDB()->getArrays($stmt, ['chunkSize' => $chunkSize]);
    }

    /**
     * @param array $mailIds
     * @param int   $isSendingNow
     * @param int   $isSent
     * @return int
     */
    public function setMailStatus(array $mailIds, int $isSendingNow, int $isSent): int
    {
        $ids  = implode(',', $this->ensureIntValuesInArray($mailIds));
        $stmt = 'UPDATE ' .
            $this->getTableName() . ' SET isSent = :isSent, isSendingnow = :isSendingNow, sendCount = sendCount + 1 ' .
            'WHERE id IN (:mailId)';

        return $this->getDB()->queryPrepared($stmt, [
            'isSent'       => $isSent,
            'isSendingNow' => $isSendingNow,
            'mailId'       => $ids
        ]);
    }

    /**
     * @param int    $mailID
     * @param string $errorMsg
     * @return int
     */
    public function setError(int $mailID, string $errorMsg): int
    {
        $stmt = 'UPDATE emails ' .
            'SET isSendingNow = 0, sendCount = sendCount + 1, errorCount = errorCount + 1, lastError = :errorMsg ' .
            'WHERE id = :mailID';

        return $this->getDB()->queryPrepared($stmt, [
            'errorMsg' => $errorMsg,
            'mailID'   => $mailID,
        ]);
    }
}
