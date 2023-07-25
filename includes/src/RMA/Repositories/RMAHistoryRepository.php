<?php declare(strict_types=1);

namespace JTL\RMA\Repositories;

use JTL\Abstracts\AbstractRepositoryTim;

/**
 * Class RMAHistoryRepository
 * @package JTL\RMA
 */
readonly class RMAHistoryRepository extends AbstractRepositoryTim
{

    /**
     * @return array
     */
    public function getColumnMapping(): array
    {
        return [
            'id'           => 'id',
            'rmaPosID'     => 'rmaPosID',
            'title'        => 'title',
            'value'        => 'value',
            'lastModified' => 'lastModified'
        ];
    }

    /**
     * @param array $data
     * @return array
     */
    public function getDefaultValues(array $data = []): array
    {
        $default = [
            'id'           => 0,
            'rmaPosID'     => 0,
            'title'        => '',
            'value'        => '',
            'lastModified' => \date('Y-m-d H:i:s'),
        ];
        return $this->combineData($default, $data);
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return 'rma_history';
    }

    /**
     * @return string
     */
    public function getKeyName(): string
    {
        return 'id';
    }
}
