<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Alert\Alert;
use JTL\Backend\AuthToken;
use JTL\Backend\Permissions;
use JTL\Backend\Wizard\ExtensionInstaller;
use JTL\Helpers\Request;
use JTL\License\Manager as LicenseManager;
use JTL\Recommendation\Manager;
use JTL\Router\Route;
use JTL\Session\Backend;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class PremiumPluginController
 * @package JTL\Router\Controller\Backend
 */
class PremiumPluginController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::PLUGIN_ADMIN_VIEW);
        $this->getText->loadAdminLocale('pages/premiumplugin');
        $recommendationID = Request::verifyGPDataString('id');
        $manager          = new LicenseManager($this->db, $this->cache);
        $scope            = Request::verifyGPDataString('scope');
        $recommendations  = new Manager($this->alertService, $scope);
        $hasLicense       = $manager->getLicenseByExsID($recommendationID) !== null;
        $token            = AuthToken::getInstance($this->db);
        $action           = Request::verifyGPDataString('action');
        if ($action === 'install') {
            $this->getText->loadAdminLocale('pages/pluginverwaltung');
            $this->getText->loadAdminLocale('pages/licenses');

            $installer = new ExtensionInstaller($this->db);
            $installer->setRecommendations($recommendations->getRecommendations());
            $errorMsg = $installer->onSaveStep([$recommendationID]);
            if ($errorMsg === '') {
                $successMsg = $scope === Manager::SCOPE_BACKEND_PAYMENT_PROVIDER
                    ? \__('successInstallPaymentPlugin')
                    : \__('successInstallLegalPlugin');
                $this->alertService->addSuccess(
                    $successMsg,
                    'successInstall',
                    ['fadeOut' => Alert::FADE_NEVER, 'saveInSession' => true]
                );
                \header('Refresh:0');
                exit;
            }
            $this->alertService->addWarning($errorMsg, 'errorInstall');
        } elseif ($action === 'auth') {
            $token->requestToken(
                Backend::get('jtl_token'),
                $this->baseURL . '/' . Route::CODE . '/premiumplugin'
            );
        }

        return $smarty->assign('recommendation', $recommendations->getRecommendationById($recommendationID))
            ->assign('hasAuth', $token->isValid())
            ->assign('hasLicense', $hasLicense)
            ->getResponse('premiumplugin.tpl');
    }
}
