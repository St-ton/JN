<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Helpers\Form;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use League\Route\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class SyncController
 * @package JTL\Router\Controller\Backend
 */
class SyncController extends AbstractBackendController
{
    public function getResponse(
        ServerRequestInterface $request,
        array $args,
        JTLSmarty $smarty,
        Route $route
    ): ResponseInterface {
        $this->smarty = $smarty;
        $this->checkPermissions('WAWI_SYNC_VIEW');
        $this->getText->loadAdminLocale('pages/wawisync');

        if (isset($_POST['wawi-pass'], $_POST['wawi-user']) && Form::validateToken()) {
            $passwordService = Shop::Container()->getPasswordService();
            if ($passwordService->hasOnlyValidCharacters($_POST['wawi-pass'])) {
                $passInfo   = $passwordService->getInfo($_POST['wawi-pass']);
                $upd        = new stdClass();
                $upd->cName = $_POST['wawi-user'];
                $upd->cPass = $passInfo['algo'] > 0
                    ? $_POST['wawi-pass'] // hashed password was not changed
                    : $passwordService->hash($_POST['wawi-pass']); // new clear text password was given

                $this->db->queryPrepared(
                    'INSERT INTO `tsynclogin` (kSynclogin, cName, cPass)
                        VALUES (1, :cName, :cPass)
                        ON DUPLICATE KEY UPDATE
                        cName = :cName,
                        cPass = :cPass',
                    ['cName' => $upd->cName, 'cPass' => $upd->cPass]
                );

                $this->alertService->addSuccess(\__('successConfigSave'), 'successConfigSave');
            } else {
                $this->alertService->addError(\__('errorInvalidPassword'), 'errorInvalidPassword');
            }
        }

        $user = $this->db->select('tsynclogin', 'kSynclogin', 1);

        return $smarty->assign('wawiuser', \htmlentities($user->cName))
            ->assign('wawipass', $user->cPass)
            ->assign('route', $route->getPath())
            ->getResponse('wawisync.tpl');
    }
}
