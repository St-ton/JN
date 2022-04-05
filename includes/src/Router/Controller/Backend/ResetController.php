<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Reset\Reset;
use JTL\Reset\ResetContentType;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use League\Route\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ResetController
 * @package JTL\Router\Controller\Backend
 */
class ResetController extends AbstractBackendController
{
    public function getResponse(
        ServerRequestInterface $request,
        array $args,
        JTLSmarty $smarty,
        Route $route
    ): ResponseInterface {
        $this->smarty = $smarty;
        $this->checkPermissions('RESET_SHOP_VIEW');
        $this->getText->loadAdminLocale('pages/shopzuruecksetzen');
        if (Request::postInt('zuruecksetzen') === 1 && Form::validateToken()) {
            $options = $_POST['cOption_arr'];
            if (\is_array($options) && count($options) > 0) {
                $reset = new Reset($this->db);
                foreach ($options as $option) {
                    $reset->doReset(ResetContentType::from($option));
                }
                Shop::Container()->getCache()->flushAll();
                $this->db->query('UPDATE tglobals SET dLetzteAenderung = NOW()');
                $this->alertService->addSuccess(\__('successShopReturn'), 'successShopReturn');
            } else {
                $this->alertService->addError(\__('errorChooseOption'), 'errorChooseOption');
            }

            \executeHook(\HOOK_BACKEND_SHOP_RESET_AFTER);
        }

        return $smarty->assign('route', $route->getPath())
            ->getResponse('shopzuruecksetzen.tpl');
    }
}
