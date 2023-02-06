<?php

namespace JTL\Mail;

use JTL\Abstracts\AbstractRepository;
use JTL\DataObjects\DataObjectInterface;
use JTL\DataObjects\DataTableObjectInterface;
use JTL\Interfaces\RepositoryInterface;
use JTL\Mail\SendMailObjects\MailDataTableObject;

class MailRepository extends AbstractRepository
{
    protected string $tableName = 'emails';
    protected string $keyName   = 'id';

    public function queueMailDataTableObject(DataTableObjectInterface $mailDataTableObject)
    {
        return $this->insert($mailDataTableObject);
    }

    public function getNextMailsFromQueue($chunksize = 20): array
    {
        $stmt = 'SELECT * FROM ' . $this->getTableName() .
            ' WHERE isSent = 0 AND isCancelled = 0 AND isBlocked = 0' .
            ' AND isSendingNow = 0 AND sendCount < 3 AND errorCount < 3' .
            ' ORDER BY id LIMIT :chunkSize';

        return $this->getDB()->getArrays($stmt, ['chunkSize' => $chunksize]);
    }

    public function setMailStatus($mailId, $isSendingNow, $isSent): int
    {
        $stmt = 'UPDATE ' .
            $this->getTableName() . ' SET isSent = :isSent, isSendingnow = :isSendingNow, sendCount = sendCount + 1 ' .
            'WHERE id = :mailId';

        return $this->getDB()->queryPrepared($stmt, [
            'isSent'       => $isSent,
            'isSendingNow' => $isSendingNow,
            'mailId'       => $mailId]);
    }

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
