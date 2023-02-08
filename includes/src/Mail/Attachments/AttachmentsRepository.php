<?php

namespace JTL\Mail\Attachments;

use JTL\Abstracts\AbstractRepository;

class AttachmentsRepository extends AbstractRepository
{
    protected $tableName = 'emailAttachments';
    protected $keyName   = 'id';

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
}
