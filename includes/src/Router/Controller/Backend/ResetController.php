<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Reset\Reset;
use JTL\Reset\ResetContentType;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ResetController
 * @package JTL\Router\Controller\Backend
 */
class ResetController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->checkPermissions(Permissions::RESET_SHOP_VIEW);
        $this->getText->loadAdminLocale('pages/shopzuruecksetzen');
        if ($this->tokenIsValid && $this->request->postInt('zuruecksetzen') === 1) {
            $options = $this->request->post('cOption_arr');
            if (\is_array($options) && \count($options) > 0) {
                $reset = new Reset($this->db);
                foreach ($options as $option) {
                    $reset->doReset(ResetContentType::from($option));
                }
                $this->cache->flushAll();
                $this->db->query('UPDATE tglobals SET dLetzteAenderung = NOW()');
                $this->alertService->addSuccess(\__('successShopReturn'), 'successShopReturn');
            } else {
                $this->alertService->addError(\__('errorChooseOption'), 'errorChooseOption');
            }

            \executeHook(\HOOK_BACKEND_SHOP_RESET_AFTER);
        }

        return $this->smarty->getResponse('shopzuruecksetzen.tpl');
    }
}
