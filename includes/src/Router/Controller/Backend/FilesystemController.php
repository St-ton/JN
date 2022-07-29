<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use Exception;
use JTL\Backend\Permissions;
use JTL\Filesystem\AdapterFactory;
use JTL\Filesystem\Filesystem;
use JTL\Helpers\Form;
use JTL\Helpers\Text;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class FilesystemController
 * @package JTL\Router\Controller\Backend
 */
class FilesystemController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::FILESYSTEM_VIEW);
        $this->getText->loadAdminLocale('pages/filesystem');
        $this->getText->loadConfigLocales(true, true);
        if (!empty($_POST) && Form::validateToken()) {
            $postData = Text::filterXSS($_POST);
            $this->saveAdminSectionSettings(\CONF_FS, $_POST);
            Shopsetting::getInstance()->reset();
            if (isset($postData['test'])) {
                $this->test($postData);
            }
        }
        $this->getAdminSectionSettings(\CONF_FS);

        return $smarty->assign('route', $this->route)
            ->getResponse('filesystem.tpl');
    }

    /**
     * @param array $postData
     * @return void
     */
    private function test(array $postData): void
    {
        try {
            $factory = new AdapterFactory(Shop::getSettingSection(\CONF_FS));
            $factory->setFtpConfig([
                'ftp_host'     => $postData['ftp_hostname'],
                'ftp_port'     => (int)($postData['ftp_port'] ?? 21),
                'ftp_username' => $postData['ftp_user'],
                'ftp_password' => $postData['ftp_pass'],
                'ftp_ssl'      => (int)$postData['ftp_ssl'] === 1,
                'ftp_root'     => $postData['ftp_path']
            ]);
            $factory->setSftpConfig([
                'sftp_host'     => $postData['sftp_hostname'],
                'sftp_port'     => (int)($postData['sftp_port'] ?? 22),
                'sftp_username' => $postData['sftp_user'],
                'sftp_password' => $postData['sftp_pass'],
                'sftp_privkey'  => $postData['sftp_privkey'],
                'sftp_root'     => $postData['sftp_path']
            ]);
            $factory->setAdapter($postData['fs_adapter']);
            if ((new Filesystem($factory->getAdapter()))->fileExists('includes/config.JTL-Shop.ini.php')) {
                $this->alertService->addInfo(\__('fsValidConnection'), 'fsValidConnection');
            } else {
                $this->alertService->addError(\__('fsInvalidShopRoot'), 'fsInvalidShopRoot');
            }
        } catch (Exception $e) {
            $this->alertService->addError($e->getMessage(), 'errorFS');
        }
    }
}
