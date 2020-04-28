<?php declare(strict_types=1);

namespace JTL\Template\Admin;

use JTL\DB\DbInterface;
use JTL\Helpers\Request;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Shop;

/**
 * Class Controller
 * @package JTL\Template\Admin
 */
class Controller
{
    /**
     * @var AlertServiceInterface
     */
    private $alertService;

    /**
     * @var DbInterface
     */
    private $db;

    public function __construct(DbInterface $db, AlertServiceInterface $alertService)
    {
        $this->db = $db;
        $this->alertService = $alertService;
    }

    public function handleAction()
    {
        $action = Request::verifyGPDataString('action');
        switch ($action) {
            case 'config':
                break;
            case 'activate':
                break;
            case 'switch':
                break;
        }

        Shop::dbg($action, false, 'action@controller:');
    }
}
