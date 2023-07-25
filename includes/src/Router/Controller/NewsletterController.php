<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use Exception;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Newsletter\Controller;
use JTL\Newsletter\Helper;
use JTL\Optin\Optin;
use JTL\Optin\OptinBase;
use JTL\Optin\OptinNewsletter;
use JTL\Optin\OptinRefData;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class NewsletterController
 * @package JTL\Router\Controller
 */
class NewsletterController extends PageController
{
    /**
     * @inheritdoc
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
        $this->smarty = $smarty;
        Shop::setPageType($this->state->pageType);
        $valid              = Form::validateToken();
        $controller         = new Controller($this->db, $this->config);
        $option             = 'eintragen';
        $customer           = Frontend::getCustomer();
        $this->canonicalURL = $this->currentLink?->getURL() ?? '';
        if ($valid && Request::verifyGPCDataInt('abonnieren') > 0) {
            $post = Text::filterXSS($_POST);
            if ($customer->getID() > 0) {
                $post['cAnrede']   = $post['cAnrede'] ?? $customer->cAnrede;
                $post['cVorname']  = $post['cVorname'] ?? $customer->cVorname;
                $post['cNachname'] = $post['cNachname'] ?? $customer->cNachname;
                $post['kKunde']    = $customer->getID();
            }
            if (Text::filterEmailAddress($post['cEmail']) !== false) {
                $refData = (new OptinRefData())
                    ->setSalutation($post['cAnrede'] ?? '')
                    ->setFirstName($post['cVorname'] ?? '')
                    ->setLastName($post['cNachname'] ?? '')
                    ->setEmail($post['cEmail'] ?? '')
                    ->setCustomerID((int)($post['kKunde'] ?? 0))
                    ->setCustomerGroupID($customer->getGroupID())
                    ->setLanguageID($this->languageID)
                    ->setRealIP(Request::getRealIP());
                try {
                    (new Optin(OptinNewsletter::class))
                        ->getOptinInstance()
                        ->createOptin($refData)
                        ->sendActivationMail();
                } catch (Exception $e) {
                    Shop::Container()->getLogService()->error($e->getMessage());
                }
            } else {
                $this->alertService->addError(
                    Shop::Lang()->get('newsletterWrongemail', 'errorMessages'),
                    'newsletterWrongemail'
                );
            }
            $this->smarty->assign('cPost_arr', $post);
        } elseif ($valid && Request::verifyGPCDataInt('abmelden') === 1) {
            if (Text::filterEmailAddress($_POST['cEmail']) !== false) {
                try {
                    (new Optin(OptinNewsletter::class))
                        ->setEmail(Text::htmlentities($_POST['cEmail']))
                        ->setAction(OptinBase::DELETE_CODE)
                        ->handleOptin();
                } catch (Exception) {
                    $this->alertService->addError(
                        Shop::Lang()->get('newsletterNoexists', 'errorMessages'),
                        'newsletterNoexists'
                    );
                }
            } else {
                $this->alertService->addError(
                    Shop::Lang()->get('newsletterWrongemail', 'errorMessages'),
                    'newsletterWrongemail'
                );
                $this->smarty->assign('oFehlendeAngaben', (object)['cUnsubscribeEmail' => 1]);
            }
        } elseif (Request::getInt('show') > 0) {
            $option = 'anzeigen';
            if ($history = $controller->getHistory($this->customerGroupID, Request::getInt('show'))) {
                $this->smarty->assign('oNewsletterHistory', $history);
            }
        }
        if ($customer->getID() > 0) {
            $this->smarty->assign('bBereitsAbonnent', Helper::customerIsSubscriber($customer->getID()))
                ->assign('oKunde', $customer);
        }
        $this->smarty->assign('cOption', $option)
            ->assign('Link', $this->currentLink)
            ->assign('nAnzeigeOrt', \CHECKBOX_ORT_NEWSLETTERANMELDUNG)
            ->assign('code_newsletter', false);

        $this->preRender();

        \executeHook(\HOOK_NEWSLETTER_PAGE);

        return $this->smarty->getResponse('newsletter/index.tpl');
    }
}
