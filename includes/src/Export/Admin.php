<?php declare(strict_types=1);

namespace JTL\Export;

use Exception;
use JTL\DB\DbInterface;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;

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
