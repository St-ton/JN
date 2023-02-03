<?php

namespace JTL\Mail\Attachments;

use JTL\Abstracts\AbstractRepository;

class AttachmentsRepository extends AbstractRepository
{
    protected $tableName = 'emailPdfAttachements';
    protected $keyName   = 'id';

    public function getListByMailIDs(array $IDs): array
    {
        if (\count($IDs) > 0) {
            $IDs  = array_map(function ($id) {
                return (int)$id;
            }, $IDs);
            $stmt = 'SELECT * FROM ' . $this->getTableName() .
                ' WHERE mailID IN(' . implode(',', $IDs) . ')';

            return $this->db->getObjects($stmt);
        }

        return [];
    }
}
