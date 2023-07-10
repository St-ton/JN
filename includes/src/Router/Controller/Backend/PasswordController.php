<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Helpers\Text;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class PasswordController
 * @package JTL\Router\Controller\Backend
 */
class PasswordController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->getText->loadAdminLocale('pages/pass');

        $step = 'prepare';
        $this->alertService->addWarning(
            \__('warningPasswordResetAuth'),
            'warningPasswordResetAuth',
            ['dismissable' => false]
        );
        if ($this->tokenIsValid && $this->request->post('mail') !== null) {
            $this->account->prepareResetPassword(Text::filterXSS($this->request->post('mail')));
        } elseif ($this->tokenIsValid
            && $this->request->post('pw_new') !== null
            && $this->request->post('pw_new_confirm') !== null
            && $this->request->post('fpm') !== null
            && $this->request->post('fpwh') !== null
        ) {
            if ($this->request->post('pw_new') === $this->request->post('pw_new_confirm')) {
                $verified = $this->account->verifyResetPasswordHash(
                    $this->request->post('fpwh'),
                    $this->request->post('fpm')
                );
                if ($verified === true) {
                    $upd = (object)[
                        'cPass' => Shop::Container()->getPasswordService()->hash($this->request->post('pw_new'))
                    ];
                    if ($this->db->update('tadminlogin', 'cMail', $this->request->post('fpm'), $upd) > 0) {
                        return $this->redirectSuccess();
                    }
                    $this->alertService->addError(\__('errorPasswordChange'), 'errorPasswordChange');
                } else {
                    $this->alertService->addError(\__('errorHashInvalid'), 'errorHashInvalid');
                }
            } else {
                $this->alertService->addError(\__('errorPasswordMismatch'), 'errorPasswordMismatch');
            }
            $this->smarty->assign('fpwh', Text::filterXSS($this->request->post('fpwh')))
                ->assign('fpm', Text::filterXSS($this->request->post('fpm')));
            $step = 'confirm';
        } elseif ($this->request->get('fpwh') !== null && $this->request->get('mail') !== null) {
            $this->smarty->assign('fpwh', Text::filterXSS($this->request->get('fpwh')))
                ->assign('fpm', Text::filterXSS($this->request->get('mail')));
            $step = 'confirm';
        }

        return $this->smarty->assign('step', $step)
            ->getResponse('pass.tpl');
    }

    /**
     * @return ResponseInterface
     */
    private function redirectSuccess(): ResponseInterface
    {
        $this->alertService->addSuccess(
            \__('successPasswordChange'),
            'successPasswordChange',
            ['saveInSession' => true]
        );
        return new RedirectResponse($this->baseURL . '/?pw_updated=true');
    }
}
