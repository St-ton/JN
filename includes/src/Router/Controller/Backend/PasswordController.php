<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Helpers\Form;
use JTL\Helpers\Text;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use League\Route\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class PasswordController
 * @package JTL\Router\Controller\Backend
 */
class PasswordController extends AbstractBackendController
{
    public function getResponse(
        ServerRequestInterface $request,
        array $args,
        JTLSmarty $smarty,
        Route $route
    ): ResponseInterface {
        $this->smarty = $smarty;
        $this->checkPermissions('WAWI_SYNC_VIEW');
        $this->getText->loadAdminLocale('pages/pass');

        $step = 'prepare';
        $this->alertService->addWarning(\__('warningPasswordResetAuth'), 'warningPasswordResetAuth');
        if (isset($_POST['mail']) && Form::validateToken()) {
            $this->account->prepareResetPassword(Text::filterXSS($_POST['mail']));
        } elseif (isset($_POST['pw_new'], $_POST['pw_new_confirm'], $_POST['fpm'], $_POST['fpwh']) && Form::validateToken()) {
            if ($_POST['pw_new'] === $_POST['pw_new_confirm']) {
                $verified = $this->account->verifyResetPasswordHash($_POST['fpwh'], $_POST['fpm']);
                if ($verified === true) {
                    $upd        = new stdClass();
                    $upd->cPass = Shop::Container()->getPasswordService()->hash($_POST['pw_new']);
                    $update     = $this->db->update('tadminlogin', 'cMail', $_POST['fpm'], $upd);
                    if ($update > 0) {
                        $this->alertService->addSuccess(
                            \__('successPasswordChange'),
                            'successPasswordChange',
                            ['saveInSession' => true]
                        );
                        \header('Location: ' . Shop::getAdminURL() . '/?pw_updated=true');
                    } else {
                        $this->alertService->addError(\__('errorPasswordChange'), 'errorPasswordChange');
                    }
                } else {
                    $this->alertService->addError(\__('errorHashInvalid'), 'errorHashInvalid');
                }
            } else {
                $this->alertService->addError(\__('errorPasswordMismatch'), 'errorPasswordMismatch');
            }
            $smarty->assign('fpwh', Text::filterXSS($_POST['fpwh']))
                ->assign('fpm', Text::filterXSS($_POST['fpm']));
            $step = 'confirm';
        } elseif (isset($_GET['fpwh'], $_GET['mail'])) {
            $smarty->assign('fpwh', Text::filterXSS($_GET['fpwh']))
                ->assign('fpm', Text::filterXSS($_GET['mail']));
            $step = 'confirm';
        }

        return $smarty->assign('step', $step)
            ->assign('route', $route->getPath())
            ->getResponse('pass.tpl');
    }
}
