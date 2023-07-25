<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Helpers\Form;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class SyncController
 * @package JTL\Router\Controller\Backend
 */
class SyncController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::WAWI_SYNC_VIEW);
        $this->getText->loadAdminLocale('pages/wawisync');

        if (isset($_POST['wawi-pass'], $_POST['wawi-user']) && Form::validateToken()) {
            $this->update($_POST['wawi-user'], $_POST['wawi-pass']);
        }
        $user = $this->db->select('tsynclogin', 'kSynclogin', 1);

        return $smarty->assign('wawiuser', \htmlentities($user->cName ?? ''))
            ->assign('wawipass', ($user->cPass ?? ''))
            ->assign('route', $this->route)
            ->getResponse('wawisync.tpl');
    }

    /**
     * @param string $user
     * @param string $pass
     * @return void
     * @throws \Exception
     */
    private function update(string $user, string $pass): void
    {
        $passwordService = Shop::Container()->getPasswordService();
        if (!$passwordService->hasOnlyValidCharacters($pass)) {
            $this->alertService->addError(\__('errorInvalidPassword'), 'errorInvalidPassword');

            return;
        }
        $passInfo = $passwordService->getInfo($pass);
        $pass     = $passInfo['algo'] > 0
            ? $pass // hashed password was not changed
            : $passwordService->hash($pass); // new clear text password was given

        $this->db->queryPrepared(
            'INSERT INTO `tsynclogin` (kSynclogin, cName, cPass)
                VALUES (1, :cName, :cPass)
                ON DUPLICATE KEY UPDATE
                cName = :cName,
                cPass = :cPass',
            ['cName' => $user, 'cPass' => $pass]
        );

        $this->alertService->addSuccess(\__('successConfigSave'), 'successConfigSave');
    }
}
