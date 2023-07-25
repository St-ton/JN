<?php declare(strict_types=1);

namespace JTL\RMA\Repositories;

use JTL\Abstracts\AbstractRepositoryTim;

/**
 * Class RMAReasonRepository
 * @package JTL\RMA
 */
readonly class RMAReasonRepository extends AbstractRepositoryTim
{

    /**
     * @return array
     */
    public function getColumnMapping(): array
    {
        return [
            'rmaID'  => 'id',
            'wawiID' => 'wawiID'
        ];
    }

    /**
     * @param array $data
     * @return array
     */
    public function getDefaultValues(array $data = []): array
    {
        $default = [
            'id'              => 0,
            'wawiID'          => 0
        ];
        return $this->combineData($default, $data);
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return 'rma_reasons';
    }
    
    /**
     * @return string
     */
    public function getKeyName(): string
    {
        return 'id';
    }


    /**
     * @param int $langID
     * @return array
     */
    public function getAllLocalized(int $langID): array
    {
        return $this->getDB()->getObjects(
            'SELECT rma_reasons.id, rma_reasons.wawiID, rma_reasons_lang.title FROM rma_reasons
            JOIN rma_reasons_lang
                ON rma_reasons_lang.reasonID = rma_reasons.id
            WHERE langID = :langID',
            ['langID' => $langID]
        );
    }
}
