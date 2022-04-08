<?php declare(strict_types=1);

namespace JTL\Export;

use Exception;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use JTL\Backend\Revision;
use JTL\Backend\Settings\Manager;
use JTL\Backend\Settings\Sections\Export;
use JTL\DB\DbInterface;
use JTL\DB\SqlObject;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Router\BackendRouter;
use JTL\Router\Controller\Backend\AbstractBackendController;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use stdClass;

/**
 * Class Admin
 * @package JTL\Export
 * @deprecated since 5.2.0
 */
class Admin
{
    /**
     * @var DbInterface
     */
    private DbInterface $db;

    /**
     * @var JTLSmarty
     */
    private JTLSmarty $smarty;

    /**
     * @var string
     */
    private string $step = 'overview';

    /**
     * Admin constructor.
     * @param DbInterface           $db
     * @param AlertServiceInterface $alertService
     * @param JTLSmarty             $smarty
     */
    public function __construct(DbInterface $db, AlertServiceInterface $alertService, JTLSmarty $smarty)
    {
        $this->db     = $db;
        $this->smarty = $smarty;
    }

    public function getAction(): void
    {
    }

    /**
     * @return ResponseInterface
     * @throws Exception
     */
    public function display(): ResponseInterface
    {
        return $this->smarty->assign('step', $this->step)
            ->assign('exportformate', Model::loadAll(
                $this->db,
                [],
                []
            )->sortBy('name', \SORT_NATURAL | \SORT_FLAG_CASE))
            ->getResponse('exportformate.tpl');
    }
}
