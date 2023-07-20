<?php declare(strict_types=1);

namespace JTL\RMA\DomainObjects;

use JTL\DataObjects\AbstractDomainObject;
use JTL\DataObjects\DomainObjectInterface;

/**
 * Class RMAHistoryDomainObject
 * @package JTL\RMA
 */
readonly class RMAHistoryDomainObject extends AbstractDomainObject implements DomainObjectInterface
{

    /**
     * @param int $id
     * @param int $rmaPosID
     * @param string $title
     * @param string $value
     * @param string $lastModified
     */
    public function __construct(
        public int $id,
        public int $rmaPosID,
        public string $title,
        public string $value,
        public string $lastModified
    ) {
        parent::__construct();
    }
}
