<?php

namespace JTL\Mail\Attachments;

use JTL\Abstracts\AbstractRepository;

class AttachmentsRepository extends AbstractRepository
{
    public function getListByMailIDs(array $IDs): array
    {
        if (\count($IDs) > 0) {
            $IDs  = $this->ensureIntValuesInArray($IDs);
            $stmt = 'SELECT * FROM ' . $this->getTableName() .
                ' WHERE mailID IN(' . implode(',', $IDs) . ')';

            return $this->db->getObjects($stmt);
        }

        return [];
    }

    public function getTableName(): string
    {
        return 'emailAttachments';
    }

    public function getKeyName(): string
    {
        return 'id';
    }
}
