<?php declare(strict_types=1);

namespace JTL\News;

use Illuminate\Support\Collection;
use JTL\DB\DbInterface;
use JTL\Helpers\CMS;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Helpers\URL;
use JTL\Pagination\Pagination;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\SimpleMail;
use JTL\Smarty\JTLSmarty;
use stdClass;
use function Functional\every;

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
