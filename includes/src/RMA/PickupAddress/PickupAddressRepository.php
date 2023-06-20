<?php declare(strict_types=1);

namespace JTL\RMA\PickupAddress;

use JTL\Abstracts\AbstractRepository;

/**
 * Class PickupAddressRepository
 * @package JTL\RMA
 */
class PickupAddressRepository extends AbstractRepository
{
    /**
     * @return string
     */
    public function getTableName(): string
    {
        return 'pickupaddress';
    }
    
    /**
     * @return string
     */
    public function getKeyName(): string
    {
        return 'id';
    }

    /**
     * @param object $data
     * @return string
     * @since 5.3.0
     */
    private function hashAddress(object $data): string
    {
        $result          = '';
        $dataTableObject = new PickupAddressDataTableObject();
        $hashColumns     = $dataTableObject->getMapping();
        unset($hashColumns['id']);
        unset($hashColumns['hash']);

        foreach ($hashColumns as $column) {
            $result .= $data->{$column} ?? '';
        }
        return md5($result);
    }

    /**
     * @param object|null $data
     * @return int
     * @since 5.3.0
     */
    public function generateID(object $data = null): int
    {
        $id = $this->getDB()->getSingleInt(
            'SELECT id FROM ' . $this->getTableName() . 'WHERE hash = :hash',
            'id',
            ['hash' => $data->hash ?? '']
        ) + 1;
        if ($id <= 1) {
            $id = $this->getDB()->getSingleInt(
                'SELECT MAX(id) FROM ' . $this->getTableName(),
                '',
                []
            ) + 1;
        }
        return ($id <= 1) ? 1 : $id;
    }
}
