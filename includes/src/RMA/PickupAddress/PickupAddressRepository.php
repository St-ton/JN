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
}
