<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use JTL\Checkout\Bestellung;
use JTL\Customer\Customer;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class OrderStatusController
 * @package JTL\Router\Controller
 */
class OrderStatusController extends PageController
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
        Shop::setPageType(\PAGE_BESTELLSTATUS);
        $linkHelper = Shop::Container()->getLinkService();
        $uid        = Request::verifyGPDataString('uid');
        if (!empty($uid)) {
            $status = $this->db->getSingleObject(
                'SELECT kBestellung, failedAttempts
                    FROM tbestellstatus 
                    WHERE dDatum >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
                        AND cUID = :uid
                        AND (failedAttempts <= :maxAttempts OR 1 = :loggedIn)',
                [
                    'uid'         => $uid,
                    'maxAttempts' => (int)$this->config['kunden']['kundenlogin_max_loginversuche'],
                    'loggedIn'    => Frontend::getCustomer()->isLoggedIn() ? 1 : 0,
                ]
            );
            if ($status === null) {
                $this->alertService->addDanger(
                    Shop::Lang()->get('statusOrderNotFound', 'errorMessages'),
                    'statusOrderNotFound',
                    ['saveInSession' => true]
                );

                return new RedirectResponse($linkHelper->getStaticRoute('jtl.php'), 303);
            }
            $order    = new Bestellung((int)$status->kBestellung, true);
            $plzValid = false;

            if (Form::validateToken()) {
                if (isset($_POST['plz']) && $order->oRechnungsadresse->cPLZ === Text::filterXSS($_POST['plz'])) {
                    $plzValid = true;
                } elseif (!empty($_POST['plz'])) {
                    $this->db->update(
                        'tbestellstatus',
                        'cUID',
                        $uid,
                        (object)['failedAttempts' => (int)$status->failedAttempts + 1]
                    );
                    $this->alertService->addDanger(
                        Shop::Lang()->get('incorrectLogin'),
                        'statusOrderincorrectLogin'
                    );
                }
            }

            $this->smarty->assign('Bestellung', $order)
                ->assign('uid', Text::filterXSS($uid))
                ->assign('showLoginPanel', Frontend::getCustomer()->isLoggedIn());

            if ($plzValid || Frontend::getCustomer()->isLoggedIn()) {
                $this->db->update('tbestellstatus', 'cUID', $uid, (object)[
                    'failedAttempts' => 0,
                ]);
                $this->smarty->assign('Kunde', new Customer($order->kKunde))
                    ->assign('Lieferadresse', $order->Lieferadresse)
                    ->assign('billingAddress', $order->oRechnungsadresse)
                    ->assign('incommingPayments', $order->getIncommingPayments());
            }
        } else {
            $this->alertService->addDanger(
                Shop::Lang()->get('uidNotFound', 'errorMessages'),
                'wrongUID',
                ['saveInSession' => true]
            );

            return new RedirectResponse($linkHelper->getStaticRoute('jtl.php'), 303);
        }

        $step = 'bestellung';
        $this->smarty->assign('step', $step)
            ->assign('Link', $linkHelper->getPageLink($linkHelper->getSpecialPageID(\LINKTYP_LOGIN)))
            ->assignDeprecated('BESTELLUNG_STATUS_BEZAHLT', \BESTELLUNG_STATUS_BEZAHLT, '5.0.0')
            ->assignDeprecated('BESTELLUNG_STATUS_VERSANDT', \BESTELLUNG_STATUS_VERSANDT, '5.0.0')
            ->assignDeprecated('BESTELLUNG_STATUS_OFFEN', \BESTELLUNG_STATUS_OFFEN, '5.0.0');

        $this->preRender();

        return $this->smarty->getResponse('account/index.tpl');
    }
}
