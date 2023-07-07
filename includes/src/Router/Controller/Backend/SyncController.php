<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Shop;
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
    public function getResponse(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $this->checkPermissions(Permissions::WAWI_SYNC_VIEW);
        $this->getText->loadAdminLocale('pages/wawisync');
        if ($this->tokenIsValid
            && $this->request->post('wawi-pass') !== null
            && $this->request->post('wawi-user') !== null
        ) {
            $this->update($this->request->post('wawi-user'), $this->request->post('wawi-pass'));
        }
        $user = $this->db->select('tsynclogin', 'kSynclogin', 1);

        return $this->smarty->assign('wawiuser', \htmlentities($user->cName ?? ''))
            ->assign('wawipass', ($user->cPass ?? ''))
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
