<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Consent\ConsentModel;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ConsentController
 * @package JTL\Router\Controller\Backend
 */
class ConsentController extends GenericModelController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::CONSENT_MANAGER);
        $this->getText->loadAdminLocale('pages/consent');
        $this->smarty->assign('route', $this->route);

        $this->modelClass    = ConsentModel::class;
        $this->adminBaseFile = \ltrim($this->route, '/');
        $smarty->assign('settings', $this->getAdminSectionSettings(\CONF_CONSENTMANAGER));

        return $this->handle('consent.tpl');
    }
}
