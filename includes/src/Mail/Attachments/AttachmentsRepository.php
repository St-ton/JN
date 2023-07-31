<?php declare(strict_types=1);

namespace JTL\Mail\Attachments;

use JTL\Abstracts\AbstractRepository;

/**
 * Class AttachmentsRepository
 * @package JTL\Mail\Attachments
 */
class AttachmentsRepository extends AbstractRepository
{
    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'email_attachments';
    }

    /**
     * @inheritdoc
     */
    public function getKeyName(): string
    {
        return 'id';
    }

    /**
     * @param array $IDs
     * @return array
     */
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
