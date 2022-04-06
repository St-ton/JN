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
use League\Route\Route;
use LinkHelper;
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
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        Shop::setPageType(\PAGE_PASSWORTVERGESSEN);
        $linkHelper = Shop::Container()->getLinkService();
        $this->step = 'formular';
        $valid      = Form::validateToken();
        $missing    = ['captcha' => false];
        if ($valid && isset($_POST['passwort_vergessen'], $_POST['email']) && (int)$_POST['passwort_vergessen'] === 1) {
            $missing = $this->initPasswordReset($missing);
        } elseif ($valid && isset($_POST['pw_new'], $_POST['pw_new_confirm'], $_POST['fpwh'])) {
            $this->reset($linkHelper);
        } elseif (isset($_GET['fpwh'])) {
            $resetItem = $this->db->select('tpasswordreset', 'cKey', $_GET['fpwh']);
            if ($resetItem) {
                $dateExpires = new DateTime($resetItem->dExpires);
                if ($dateExpires >= new DateTime()) {
                    $this->smarty->assign('fpwh', Text::filterXSS($_GET['fpwh']));
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

        $this->smarty->assign('step', $this->step)
            ->assign('fehlendeAngaben', $missing)
            ->assign('presetEmail', Text::filterXSS(Request::verifyGPDataString('email')))
            ->assign('Link', $link);

        $this->preRender();

        return $this->smarty->getResponse('account/password.tpl');
    }

    /**
     * @param LinkHelper $linkHelper
     * @return void
     * @throws \Exception
     */
    protected function reset(LinkHelper $linkHelper): void
    {
        if ($_POST['pw_new'] === $_POST['pw_new_confirm']) {
            $resetItem = $this->db->select('tpasswordreset', 'cKey', $_POST['fpwh']);
            if ($resetItem !== null && new DateTime($resetItem->dExpires) >= new DateTime()) {
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
            $this->alertService->addError(
                Shop::Lang()->get('passwordsMustBeEqual', 'account data'),
                'passwordsMustBeEqual'
            );
        }
        $this->step = 'confirm';
        $this->smarty->assign('fpwh', Text::filterXSS($_POST['fpwh']));
    }

    /**
     * @param array     $missing
     * @return array
     * @throws \Exception
     */
    protected function initPasswordReset(array $missing): array
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

                $this->smarty->assign('Kunde', $customer);
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
