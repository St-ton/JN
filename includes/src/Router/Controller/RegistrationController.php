<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;

/**
 * Class RegistrationController
 * @package JTL\Router\Controller
 */
class RegistrationController extends AbstractController
{
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

    public function getResponse(JTLSmarty $smarty): ResponseInterface
    {
        $linkHelper = Shop::Container()->getLinkService();
        if (Request::verifyGPCDataInt('editRechnungsadresse') === 0 && Frontend::getCustomer()->getID() > 0) {
            \header('Location: ' . $linkHelper->getStaticRoute('jtl.php'), true, 301);
        }

        require_once PFAD_ROOT . \PFAD_INCLUDES . 'bestellvorgang_inc.php';
        require_once PFAD_ROOT . \PFAD_INCLUDES . 'registrieren_inc.php';

        Shop::setPageType(\PAGE_REGISTRIERUNG);
        $link  = $linkHelper->getSpecialPage(\LINKTYP_REGISTRIEREN);
        $step  = 'formular';
        $titel = Shop::Lang()->get('newAccount', 'login');
        $edit  = Request::getInt('editRechnungsadresse');
        if (isset($_POST['editRechnungsadresse'])) {
            $edit = (int)$_POST['editRechnungsadresse'];
        }
        if (Form::validateToken() && Request::postInt('form') === 1) {
            \kundeSpeichern($_POST);
        }
        if (Request::getInt('editRechnungsadresse') === 1) {
            \gibKunde();
        }
        if ($step === 'formular') {
            \gibFormularDaten(Request::verifyGPCDataInt('checkout'));
        }
        $smarty->assign('editRechnungsadresse', $edit)
            ->assign('Ueberschrift', $titel)
            ->assign('Link', $link)
            ->assign('step', $step)
            ->assign('nAnzeigeOrt', \CHECKBOX_ORT_REGISTRIERUNG)
            ->assign('code_registrieren', false)
            ->assign('unregForm', 0);

        $this->canonicalURL = $linkHelper->getStaticRoute('registrieren.php');

        $this->preRender($smarty);
        if (($this->config['kunden']['kundenregistrierung_pruefen_zeit'] ?? 'N') === 'Y') {
            $_SESSION['dRegZeit'] = \time();
        }

        if (Request::verifyGPCDataInt('accountDeleted') === 1) {
            $this->alertService->addSuccess(
                Shop::Lang()->get('accountDeleted', 'messages'),
                'accountDeleted'
            );
        }

        \executeHook(\HOOK_REGISTRIEREN_PAGE);

        return $smarty->getResponse('register/index.tpl');
    }
}
