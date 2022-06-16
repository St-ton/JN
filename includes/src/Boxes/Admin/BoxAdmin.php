<?php declare(strict_types=1);

namespace JTL\Boxes\Admin;

use JTL\Boxes\Type;
use JTL\DB\DbInterface;
use JTL\Helpers\Text;
use JTL\Mapper\PageTypeToPageNiceName;
use JTL\Shop;
use stdClass;
use function Functional\filter;
use function Functional\map;

/**
 * Class BoxAdmin
 * @package Boxes\Admin
 * @deprecated since 5.2.0
 */
final class BoxAdmin
{
    /**
     * BoxAdmin constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        \trigger_error(__CLASS__ . ' is deprecated and should not be used anymore.', \E_USER_DEPRECATED);
    }
}
