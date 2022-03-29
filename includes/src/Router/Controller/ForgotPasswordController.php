<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use DateTime;
use JTL\Alert\Alert;
use JTL\Customer\Customer;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\RateLimit\ForgotPassword as Limiter;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use LinkHelper;

/**
 * Class ForgotPasswordController
 * @package JTL\Router\Controller
 */
class ForgotPasswordController extends AbstractController
{
    private string $step;

    public function init(): bool
    {
        parent::init();
        Shop::setPageType($this->state->pageType);

        return true;
    }

    public function handleState(JTLSmarty $smarty): void
    {
        echo $this->getResponse($smarty);
    }

    public function getResponse(JTLSmarty $smarty): string
    {
        Shop::setPageType(\PAGE_PASSWORTVERGESSEN);
        $linkHelper = Shop::Container()->getLinkService();
        $this->step = 'formular';
        $valid      = Form::validateToken();
        $missing    = ['captcha' => false];
        if ($valid && isset($_POST['passwort_vergessen'], $_POST['email']) && (int)$_POST['passwort_vergessen'] === 1) {
            $missing = $this->initPasswordReset($smarty, $missing);
        } elseif ($valid && isset($_POST['pw_new'], $_POST['pw_new_confirm'], $_POST['fpwh'])) {
            $this->reset($smarty, $linkHelper);
        } elseif (isset($_GET['fpwh'])) {
            $resetItem = $this->db->select('tpasswordreset', 'cKey', $_GET['fpwh']);
            if ($resetItem) {
                $dateExpires = new DateTime($resetItem->dExpires);
                if ($dateExpires >= new DateTime()) {
                    $smarty->assign('fpwh', Text::filterXSS($_GET['fpwh']));
                } else {
                    $this->alertService->addError(Shop::Lang()->get('invalidHash', 'account data'), 'invalidHash');
                }
            } else {
                $this->alertService->addError(Shop::Lang()->get('invalidHash', 'account data'), 'invalidHash');
            }
            $this->step = 'confirm';
        }
        $this->canonicalURL = $linkHelper->getStaticRoute('pass.php');
        $link               = $linkHelper->getSpecialPage(\LINKTYP_PASSWORD_VERGESSEN);
        if (!$this->alertService->alertTypeExists(Alert::TYPE_ERROR)) {
            $this->alertService->addInfo(
                Shop::Lang()->get('forgotPasswordDesc', 'forgot password'),
                'forgotPasswordDesc',
                ['showInAlertListTemplate' => false]
            );
        }

        $smarty->assign('step', $this->step)
            ->assign('fehlendeAngaben', $missing)
            ->assign('presetEmail', Text::filterXSS(Request::verifyGPDataString('email')))
            ->assign('Link', $link);

        $this->preRender($smarty);

        return $smarty->getResponse('account/password.tpl');
    }

    protected function reset(JTLSmarty $smarty, LinkHelper $linkHelper): void
    {
        if ($_POST['pw_new'] === $_POST['pw_new_confirm']) {
            $resetItem = $this->db->select('tpasswordreset', 'cKey', $_POST['fpwh']);
            if ($resetItem !== null && ($dateExpires = new DateTime($resetItem->dExpires)) >= new DateTime()) {
                $customer = new Customer((int)$resetItem->kKunde);
                if ($customer->kKunde > 0 && $customer->cSperre !== 'Y') {
                    $customer->updatePassword($_POST['pw_new']);
                    $this->db->delete('tpasswordreset', 'kKunde', $customer->kKunde);
                    \header('Location: ' . $linkHelper->getStaticRoute('jtl.php') . '?updated_pw=true');
                    exit();
                }
                $this->alertService->addError(Shop::Lang()->get('invalidCustomer', 'account data'), 'invalidCustomer');
            } else {
                $this->alertService->addError(Shop::Lang()->get('invalidHash', 'account data'), 'invalidHash');
            }
        } else {
            $this->alertService->addError(Shop::Lang()->get('passwordsMustBeEqual', 'account data'), 'passwordsMustBeEqual');
        }
        $this->step = 'confirm';
        $smarty->assign('fpwh', Text::filterXSS($_POST['fpwh']));
    }

    protected function initPasswordReset(JTLSmarty $smarty, array $missing): array
    {
        $customerData = $this->db->getSingleObject(
            'SELECT kKunde, cSperre
                FROM tkunde
                    WHERE cMail = :mail
                    AND nRegistriert = 1',
            ['mail' => $_POST['email']]
        );
        if ($customerData === null) {
            $this->alertService->addError(Shop::Lang()->get('incorrectEmail'), 'incorrectEmail');

            return $missing;
        }
        $customerID = (int)$customerData->kKunde;
        $limiter    = new Limiter($this->db);
        $limiter->init(Request::getRealIP(), $customerID);
        if ($limiter->check() === true) {
            $limiter->persist();
            $limiter->cleanup();
            $validRecaptcha = true;
            if ($this->config['kunden']['forgot_password_captcha'] === 'Y' && !Form::validateCaptcha($_POST)) {
                $validRecaptcha     = false;
                $missing['captcha'] = true;
            }
            if ($validRecaptcha === false) {
                $this->alertService->addError(Shop::Lang()->get('fillOut'), 'accountLocked');
            } elseif ($customerID > 0 && $customerData->cSperre !== 'Y') {
                $this->step = 'passwort versenden';
                $customer   = new Customer($customerID);
                $customer->prepareResetPassword();

                $smarty->assign('Kunde', $customer);
            } elseif ($customerID > 0 && $customerData->cSperre === 'Y') {
                $this->alertService->addError(Shop::Lang()->get('accountLocked'), 'accountLocked');
            }
        } else {
            $missing['limit'] = true;
            $this->alertService->addError(Shop::Lang()->get('formToFast', 'account data'), 'accountLocked');
        }

        return $missing;
    }
}
