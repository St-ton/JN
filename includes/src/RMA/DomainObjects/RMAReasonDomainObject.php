<?php declare(strict_types=1);

namespace JTL\RMA\DomainObjects;

use JTL\DataObjects\AbstractDomainObject;
use JTL\DataObjects\DomainObjectInterface;

/**
 * Class RMADomainObject
 * @package JTL\RMA
 * @description Data container for RMA request created in shop or imported from WAWI
 */
readonly class RMAReasonDomainObject extends AbstractDomainObject implements DomainObjectInterface
{

    /**
     * @param int $id
     * @param int $wawiID
     */
    public function __construct(
        public int $id,
        public int $wawiID
    ) {
        parent::__construct();
    }
}
