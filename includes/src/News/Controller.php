<?php declare(strict_types=1);

namespace JTL\News;

use JTL\DB\DbInterface;
use JTL\Smarty\JTLSmarty;

/**
 * Class Controller
 * @package JTL\News
 * @deprecated since 5.2.0
 */
class Controller
{
    /**
     * Controller constructor.
     * @param DbInterface $db
     * @param array       $config
     * @param JTLSmarty   $smarty
     * @deprecated since 5.2.0
     */
    public function __construct(DbInterface $db, array $config, JTLSmarty $smarty)
    {
    }
}
