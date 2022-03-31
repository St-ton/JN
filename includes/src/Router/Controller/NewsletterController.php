<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use Exception;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Newsletter\Controller;
use JTL\Newsletter\Helper;
use JTL\Optin\Optin;
use JTL\Optin\OptinNewsletter;
use JTL\Optin\OptinRefData;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;

/**
 * Class NewsletterController
 * @package JTL\Router\Controller
 */
class NewsletterController extends AbstractController
{
    public function init(): bool
    {
        parent::init();
        Shop::setPageType($this->state->pageType);

        return true;
    }

    public function getResponse(JTLSmarty $smarty): ResponseInterface
    {
        Shop::setPageType(\PAGE_NEWSLETTER);
        $linkHelper = Shop::Container()->getLinkService();
        $kLink      = $linkHelper->getSpecialPageID(\LINKTYP_NEWSLETTER, false);
        $valid      = Form::validateToken();
        $controller = new Controller($this->db, $this->config);
        if ($kLink === false) {
            // @todo
            $bFileNotFound       = true;
            Shop::$kLink         = $linkHelper->getSpecialPageID(\LINKTYP_404) ?: 0;
            Shop::$bFileNotFound = true;
            Shop::$is404         = true;

            return;
        }
        $link               = $linkHelper->getPageLink($kLink);
        $this->canonicalURL = '';
        $option             = 'eintragen';
        $customer           = Frontend::getCustomer();
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
            $smarty->assign('cPost_arr', $post);
        } elseif ($valid && Request::verifyGPCDataInt('abmelden') === 1) {
            if (Text::filterEmailAddress($_POST['cEmail']) !== false) {
                try {
                    (new Optin(OptinNewsletter::class))
                        ->setEmail(Text::htmlentities($_POST['cEmail']))
                        ->setAction(Optin::DELETE_CODE)
                        ->handleOptin();
                } catch (Exception $e) {
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
                $smarty->assign('oFehlendeAngaben', (object)['cUnsubscribeEmail' => 1]);
            }
        } elseif (Request::getInt('show') > 0) {
            $option = 'anzeigen';
            if ($history = $controller->getHistory($this->customerGroupID, Request::getInt('show'))) {
                $smarty->assign('oNewsletterHistory', $history);
            }
        }
        if ($customer->getID() > 0) {
            $smarty->assign('bBereitsAbonnent', Helper::customerIsSubscriber($customer->getID()))
                ->assign('oKunde', $customer);
        }
        $this->canonicalURL = $linkHelper->getStaticRoute('newsletter.php');

        $smarty->assign('cOption', $option)
            ->assign('Link', $link)
            ->assign('nAnzeigeOrt', \CHECKBOX_ORT_NEWSLETTERANMELDUNG)
            ->assign('code_newsletter', false);

        $this->preRender($smarty);

        \executeHook(\HOOK_NEWSLETTER_PAGE);

        return $smarty->getResponse('newsletter/index.tpl');
    }
}
