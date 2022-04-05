<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use Exception;
use JTL\Filesystem\AdapterFactory;
use JTL\Filesystem\Filesystem;
use JTL\Helpers\Form;
use JTL\Helpers\Text;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Smarty\JTLSmarty;
use League\Route\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class StatusController
 * @package JTL\Router\Controller\Backend
 */
class FilesystemController extends AbstractBackendController
{
    public function getResponse(
        ServerRequestInterface $request,
        array $args,
        JTLSmarty $smarty,
        Route $route
    ): ResponseInterface {
        $this->smarty = $smarty;
        $this->checkPermissions('FILESYSTEM_VIEW');
        $this->getText->loadAdminLocale('pages/filesystem');
        $this->getText->loadConfigLocales(true, true);
        $shopSettings = Shopsetting::getInstance();

        if (!empty($_POST) && Form::validateToken()) {
            $postData = Text::filterXSS($_POST);
            \saveAdminSectionSettings(\CONF_FS, $_POST);
            $shopSettings->reset();

            if (isset($postData['test'])) {
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
                    $fs         = new Filesystem($factory->getAdapter());
                    $isShopRoot = $fs->fileExists('includes/config.JTL-Shop.ini.php');
                    if ($isShopRoot) {
                        $this->alertService->addInfo(\__('fsValidConnection'), 'fsValidConnection');
                    } else {
                        $this->alertService->addError(\__('fsInvalidShopRoot'), 'fsInvalidShopRoot');
                    }
                } catch (Exception $e) {
                    $this->alertService->addError($e->getMessage(), 'errorFS');
                }
            }
        }
        \getAdminSectionSettings(\CONF_FS);

        return $smarty->assign('route', $route->getPath())
            ->getResponse('filesystem.tpl');
    }
}
