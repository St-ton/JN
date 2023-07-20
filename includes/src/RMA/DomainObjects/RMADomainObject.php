<?php declare(strict_types=1);

namespace JTL\RMA\DomainObjects;

use JTL\DataObjects\AbstractDomainObject;
use JTL\DataObjects\DomainObjectInterface;

/**
 * Class RMADomainObject
 * @package JTL\RMA
 * @description Data container for RMA request created in shop or imported from WAWI
 */
readonly class RMADomainObject extends AbstractDomainObject implements DomainObjectInterface
{

    /**
     * @var RMAPositionDomainObject[]
     */
    private array $positions;

    /**
     * @param int $id
     * @param int|null $wawiID
     * @param int $customerID
     * @param int $pickupAddressID
     * @param string|null $status
     * @param string $createDate
     * @param string|null $lastModified
     * @param array|null $positions
     */
    public function __construct(
        public int $id,
        public ?int $wawiID,
        public int $customerID,
        public int $pickupAddressID,
        public ?string $status,
        public string $createDate,
        public ?string $lastModified,
        ?array $positions
    ) {
        parent::__construct();
        $this->positions = $positions ?? [];
    }

    /**
     * @return RMAPositionDomainObject[]|array
     */
    public function getPositions(): array
    {
        return $this->positions;
    }
}
