<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use DateTime;
use JTL\Alert\Alert;
use JTL\Customer\Customer;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\RateLimit\ForgotPassword as Limiter;
use JTL\Services\JTL\LinkService;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ForgotPasswordController
 * @package JTL\Router\Controller
 */
class ForgotPasswordController extends AbstractController
{
    /**
     * @var string
     */
    private string $step;

    /**
     * @return bool
     */
    public function init(): bool
    {
        parent::init();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        Shop::setPageType(\PAGE_PASSWORTVERGESSEN);
        $linkService = Shop::Container()->getLinkService();
        $this->step  = 'formular';
        $valid       = $this->tokenIsValid;
        $missing     = ['captcha' => false];
        if ($valid && $this->request->post('email') !== null && $this->request->post('passwort_vergessen') === 1) {
            $missing = $this->initPasswordReset($missing);
        } elseif ($valid
            && $this->request->post('pw_new') !== null
            && $this->request->post('pw_new_confirm') !== null
            && $this->request->post('fpwh') !== null
        ) {
            if (($response = $this->reset($linkService)) !== null) {
                return $response;
            }
        } elseif ($this->request->get('fpwh') !== null) {
            $resetItem = $this->db->select('tpasswordreset', 'cKey', $this->request->get('fpwh'));
            if ($resetItem) {
                $dateExpires = new DateTime($resetItem->dExpires);
                if ($dateExpires >= new DateTime()) {
                    $this->smarty->assign('fpwh', Text::filterXSS($this->request->get('fpwh')));
                } else {
                    $this->alertService->addError(Shop::Lang()->get('invalidHash', 'account data'), 'invalidHash');
                }
            } else {
                $this->alertService->addError(Shop::Lang()->get('invalidHash', 'account data'), 'invalidHash');
            }
            $this->step = 'confirm';
        }
        $this->canonicalURL = $linkService->getStaticRoute('pass.php');
        $link               = $linkService->getSpecialPage(\LINKTYP_PASSWORD_VERGESSEN);
        if (!$this->alertService->alertTypeExists(Alert::TYPE_ERROR)) {
            $this->alertService->addInfo(
                Shop::Lang()->get('forgotPasswordDesc', 'forgot password'),
                'forgotPasswordDesc',
                ['showInAlertListTemplate' => false]
            );
        }

        $this->smarty->assign('step', $this->step)
            ->assign('fehlendeAngaben', $missing)
            ->assign('presetEmail', Text::filterXSS($this->request->request('email')))
            ->assign('Link', $link);

        $this->preRender();

        return $this->smarty->getResponse('account/password.tpl');
    }

    /**
     * @param LinkService $linkService
     * @return null|ResponseInterface
     * @throws \Exception
     */
    protected function reset(LinkService $linkService): ?ResponseInterface
    {
        if ($this->request->post('pw_new') === $this->request->post('pw_new_confirm')) {
            $resetItem = $this->db->select('tpasswordreset', 'cKey', $this->request->post('fpwh'));
            if ($resetItem !== null && new DateTime($resetItem->dExpires) >= new DateTime()) {
                $customer = new Customer((int)$resetItem->kKunde);
                if ($customer->kKunde > 0 && $customer->cSperre !== 'Y') {
                    $customer->updatePassword($this->request->post('pw_new'));
                    $this->db->delete('tpasswordreset', 'kKunde', $customer->kKunde);

                    return new RedirectResponse($linkService->getStaticRoute('jtl.php') . '?updated_pw=true');
                }
                $this->alertService->addError(Shop::Lang()->get('invalidCustomer', 'account data'), 'invalidCustomer');
            } else {
                $this->alertService->addError(Shop::Lang()->get('invalidHash', 'account data'), 'invalidHash');
            }
        } else {
            $this->alertService->addError(
                Shop::Lang()->get('passwordsMustBeEqual', 'account data'),
                'passwordsMustBeEqual'
            );
        }
        $this->step = 'confirm';
        $this->smarty->assign('fpwh', Text::filterXSS($this->request->post('fpwh')));

        return null;
    }

    /**
     * @param array $missing
     * @return array
     * @throws \Exception
     */
    protected function initPasswordReset(array $missing): array
    {
        $hasError     = false;
        $email        = $this->request->post('email', '');
        $customerData = $this->db->getSingleObject(
            'SELECT kKunde, cSperre
                FROM tkunde
                    WHERE cMail = :mail
                    AND nRegistriert = 1',
            ['mail' => $email]
        );
        $customerID   = (int)($customerData->kKunde ?? 0);
        $limiter      = new Limiter($this->db);
        $limiter->init(Request::getRealIP(), $customerID);
        if ($limiter->check() === true) {
            $limiter->persist();
            $limiter->cleanup();
            $validRecaptcha = true;
            if ($this->config['kunden']['forgot_password_captcha'] === 'Y'
                && !Form::validateCaptcha($this->request->getBody())
            ) {
                $validRecaptcha     = false;
                $missing['captcha'] = true;
            }
            if ($validRecaptcha === false) {
                $this->alertService->addError(Shop::Lang()->get('fillOut'), 'accountLocked');
                $hasError = true;
            } elseif ($customerID > 0 && $customerData !== null && $customerData->cSperre !== 'Y') {
                $this->step = 'passwort versenden';
                $customer   = new Customer($customerID);
                $customer->prepareResetPassword();
                $this->smarty->assign('Kunde', $customer);
            } elseif ($customerID > 0 && $customerData !== null && $customerData->cSperre === 'Y') {
                $this->alertService->addError(Shop::Lang()->get('accountLocked'), 'accountLocked');
                $hasError = true;
            }
        } else {
            $missing['limit'] = true;
            $this->alertService->addError(Shop::Lang()->get('formToFast', 'account data'), 'accountLocked');
            $hasError = true;
        }
        if ($hasError === false) {
            $this->alertService->addSuccess(
                \sprintf(
                    Shop::Lang()->get('newPasswordWasGenerated', 'forgot password'),
                    $email
                ),
                'newPasswordWasGenerated',
                [
                    'dismissable' => true,
                    'fadeOut'     => 0
                ]
            );
        }

        return $missing;
    }
}
