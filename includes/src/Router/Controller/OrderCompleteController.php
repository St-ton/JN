<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use InvalidArgumentException;
use JTL\Cart\Cart;
use JTL\Cart\CartHelper;
use JTL\Checkbox\CheckboxService;
use JTL\Checkbox\CheckboxValidationDataObject;
use JTL\Checkout\Bestellung;
use JTL\Checkout\OrderHandler;
use JTL\Helpers\Text;
use JTL\Plugin\Helper;
use JTL\Plugin\Payment\LegacyMethod;
use JTL\Plugin\Payment\MethodInterface;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\SimpleMail;
use JTL\Smarty\JTLSmarty;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class OrderCompleteController
 * @package JTL\Router\Controller
 */
class OrderCompleteController extends CheckoutController
{
    /**
     * @var CheckboxService $checkboxService
     */
    protected CheckboxService $checkboxService;

    /**
     * @inheritdoc
     */
    public function init(): bool
    {
        parent::init();

        $this->checkboxService = new CheckboxService();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        Shop::setPageType(\PAGE_BESTELLABSCHLUSS);
        require_once \PFAD_ROOT . \PFAD_INCLUDES . 'bestellabschluss_inc.php';
        require_once \PFAD_ROOT . \PFAD_INCLUDES . 'bestellvorgang_inc.php';
        if ($this->request->getInt('payAgain') === 1 && $this->request->getInt('kBestellung') > 0) {
            return $this->handlePayAgain($this->request->getInt('kBestellung'));
        }
        $cart       = Frontend::getCart();
        $handler    = new OrderHandler($this->db, Frontend::getCustomer(), $cart);
        $linkHelper = Shop::Container()->getLinkService();
        $order      = null;
        if ($this->request->get('i') !== null) {
            $bestellid = $this->db->select('tbestellid', 'cId', $this->request->get('i'));
            if ($bestellid !== null && $bestellid->kBestellung > 0) {
                $bestellid->kBestellung = (int)$bestellid->kBestellung;
                $order                  = new Bestellung($bestellid->kBestellung);
                $order->fuelleBestellung(false);
                $handler->saveUploads($order);
                $this->db->delete('tbestellid', 'kBestellung', $bestellid->kBestellung);
            }
            $this->db->query('DELETE FROM tbestellid WHERE dDatum < DATE_SUB(NOW(), INTERVAL 30 DAY)');
            $this->smarty->assign('abschlussseite', 1);
        } else {
            if ($this->request->post('kommentar') !== null) {
                $_SESSION['kommentar'] = \mb_substr(
                    \strip_tags($this->db->escape($this->request->post('kommentar'))),
                    0,
                    1000
                );
            } elseif (!isset($_SESSION['kommentar'])) {
                $_SESSION['kommentar'] = '';
            }
            if (!$this->isOrderComplete()) {
                return new RedirectResponse(
                    $linkHelper->getStaticRoute('bestellvorgang.php')
                    . '?fillOut=' . $this->getErorCode(),
                    303
                );
            }
            if (isset($_SESSION['Kunde']->cMail) === true && SimpleMail::checkBlacklist($_SESSION['Kunde']->cMail)) {
                return new RedirectResponse($linkHelper->getStaticRoute('bestellvorgang.php') . '?mailBlocked=1', 303);
            }
            if ($cart->removeParentItems() > 0) {
                $this->alertService->addWarning(
                    Shop::Lang()->get('warningCartContainedParentItems', 'checkout'),
                    'warningCartContainedParentItems',
                    ['saveInSession' => true]
                );

                return new RedirectResponse($linkHelper->getStaticRoute('warenkorb.php'), 303);
            }
            $cart->pruefeLagerbestaende();
            if ($cart->checkIfCouponIsStillValid() === false) {
                $_SESSION['checkCouponResult']['ungueltig'] = 3;

                return new RedirectResponse($linkHelper->getStaticRoute('warenkorb.php'), 303);
            }
            if (empty($_SESSION['Zahlungsart']->nWaehrendBestellung)) {
                $cart->loescheDeaktiviertePositionen();
                $wkChecksum = Cart::getChecksum($cart);
                if (!empty($cart->cChecksumme)
                    && $wkChecksum !== $cart->cChecksumme
                ) {
                    if (!$cart->posTypEnthalten(\C_WARENKORBPOS_TYP_ARTIKEL)) {
                        CartHelper::deleteAllSpecialItems();
                    }
                    $_SESSION['Warenkorbhinweise'][] = Shop::Lang()->get('yourbasketismutating', 'checkout');

                    return new RedirectResponse($linkHelper->getStaticRoute('warenkorb.php'), 303);
                }
                $order = $handler->finalizeOrder();
                if ($order->Lieferadresse === null && !empty($_SESSION['Lieferadresse']->cVorname)) {
                    $order->Lieferadresse = $handler->getShippingAddress();
                }
                $this->smarty->assign('Bestellung', $order);
            } else {
                $order = $handler->fakeOrder();
            }
            $handler->saveUploads($order);
            $this->setzeSmartyWeiterleitung($order);
        }
        $this->smarty->assign('WarensummeLocalized', $cart->gibGesamtsummeWarenLocalized())
            ->assign('oPlugin', null)
            ->assign('plugin', null)
            ->assign('Bestellung', $order)
            ->assign('Link', $this->currentLink)
            ->assign('Kunde', $_SESSION['Kunde'] ?? null)
            ->assign('bOrderConf', true)
            ->assignDeprecated('C_WARENKORBPOS_TYP_ARTIKEL', \C_WARENKORBPOS_TYP_ARTIKEL, '5.0.0')
            ->assignDeprecated('C_WARENKORBPOS_TYP_GRATISGESCHENK', \C_WARENKORBPOS_TYP_GRATISGESCHENK, '5.0.0');

        $kPlugin = isset($order->Zahlungsart->cModulId)
            ? Helper::getIDByModuleID($order->Zahlungsart->cModulId)
            : 0;
        if ($kPlugin > 0) {
            $loader = Helper::getLoaderByPluginID($kPlugin, $this->db);
            try {
                $plugin = $loader->init($kPlugin);
                $this->smarty->assign('oPlugin', $plugin)
                    ->assign('plugin', $plugin);
            } catch (InvalidArgumentException) {
                Shop::Container()->getLogService()->error(
                    'Associated plugin for payment method {mid} not found',
                    ['mid' => $order->Zahlungsart->cModulId]
                );
            }
        }
        if (empty($_SESSION['Zahlungsart']->nWaehrendBestellung) || $this->request->get('i') !== null) {
            Frontend::getInstance()->cleanUp();
            $this->preRender();
            \executeHook(\HOOK_BESTELLABSCHLUSS_PAGE, ['oBestellung' => $order]);

            return $this->smarty->getResponse('checkout/order_completed.tpl');
        }

        $this->preRender();
        \executeHook(\HOOK_BESTELLABSCHLUSS_PAGE_ZAHLUNGSVORGANG, ['oBestellung' => $order]);

        return $this->smarty->getResponse('checkout/step6_init_payment.tpl');
    }

    /**
     * @param int $orderID
     * @return ResponseInterface
     */
    protected function handlePayAgain(int $orderID): ResponseInterface
    {
        $linkHelper = Shop::Container()->getLinkService();
        $order      = new Bestellung($orderID, true);
        //abfragen, ob diese Bestellung dem Kunden auch gehoert
        //bei Gastbestellungen ist ggf das Kundenobjekt bereits entfernt bzw nRegistriert = 0
        if ($order->oKunde !== null
            && $order->oKunde->nRegistriert === 1
            && $order->kKunde !== Frontend::getCustomer()->getID()
        ) {
            return new RedirectResponse($linkHelper->getStaticRoute('jtl.php'), 303);
        }

        $bestellid = $this->db->select('tbestellid', 'kBestellung', $order->kBestellung);
        $moduleID  = $order->Zahlungsart->cModulId;
        $this->smarty->assign('Bestellung', $order)
            ->assign('oPlugin', null)
            ->assign('plugin', null);
        if ($this->request->requestInt('zusatzschritt') === 1) {
            $hasAdditionalInformation = false;
            if ($moduleID === 'za_lastschrift_jtl') {
                $blz      = $this->request->post('blz', '');
                $bankName = $this->request->post('bankname', '');
                $no       = $this->request->post('kontonr', '');
                $name     = $this->request->post('inhaber', '');
                $iban     = $this->request->post('iban', '');
                $bic      = $this->request->post('bic', '');
                if (($bankName && $blz && $no && $name)
                    || ($bankName && $iban && $bic && $name)
                ) {
                    $_SESSION['Zahlungsart']->ZahlungsInfo->cBankName =
                        Text::htmlentities(\stripslashes($bankName), \ENT_QUOTES);
                    $_SESSION['Zahlungsart']->ZahlungsInfo->cKontoNr  =
                        Text::htmlentities(\stripslashes($no), \ENT_QUOTES);
                    $_SESSION['Zahlungsart']->ZahlungsInfo->cBLZ      =
                        Text::htmlentities(\stripslashes($blz), \ENT_QUOTES);
                    $_SESSION['Zahlungsart']->ZahlungsInfo->cIBAN     =
                        Text::htmlentities(\stripslashes($iban), \ENT_QUOTES);
                    $_SESSION['Zahlungsart']->ZahlungsInfo->cBIC      =
                        Text::htmlentities(\stripslashes($bic), \ENT_QUOTES);
                    $_SESSION['Zahlungsart']->ZahlungsInfo->cInhaber  =
                        Text::htmlentities(\stripslashes($name), \ENT_QUOTES);
                    $hasAdditionalInformation                         = true;
                }
            }

            if ($hasAdditionalInformation) {
                $handler = new OrderHandler($this->db, Frontend::getCustomer(), Frontend::getCart());
                if ($handler->savePaymentInfo($order->kKunde, $order->kBestellung)) {
                    $this->db->update(
                        'tbestellung',
                        'kBestellung',
                        $order->kBestellung,
                        (object)['cAbgeholt' => 'N']
                    );
                    unset($_SESSION['Zahlungsart']);
                    $successPaymentURL = Shop::getURL();
                    if ($bestellid !== null && $bestellid->cId) {
                        $orderCompleteURL  = $linkHelper->getStaticRoute('bestellabschluss.php');
                        $successPaymentURL = $orderCompleteURL . '?i=' . $bestellid->cId;
                    }

                    return new RedirectResponse($successPaymentURL, 303);
                }
            } else {
                $this->smarty->assign('ZahlungsInfo', $this->getPaymentInfo());
            }
        }
        // Zahlungsart als Plugin
        $pluginID = Helper::getIDByModuleID($moduleID);
        if ($pluginID > 0) {
            $loader = Helper::getLoaderByPluginID($pluginID, $this->db);
            try {
                $plugin        = $loader->init($pluginID);
                $paymentMethod = LegacyMethod::create($moduleID, 1);
                if ($paymentMethod !== null) {
                    if ($paymentMethod->validateAdditional()) {
                        $paymentMethod->preparePaymentProcess($order);
                    } elseif (!$paymentMethod->handleAdditional($this->request->getBody())) {
                        $order->Zahlungsart = $this->getPaymentMethod($order->kZahlungsart);
                    }
                }

                $this->smarty->assign('oPlugin', $plugin)
                    ->assign('plugin', $plugin);
            } catch (InvalidArgumentException) {
            }
        } elseif ($moduleID === 'za_lastschrift_jtl') {
            $customerAccountData = $this->getCustomerAccountData(Frontend::getCustomer()->getID());
            if (isset($customerAccountData->kKunde) && $customerAccountData->kKunde > 0) {
                $this->smarty->assign('oKundenKontodaten', $customerAccountData);
            }
        }

        $this->smarty->assign('WarensummeLocalized', Frontend::getCart()->gibGesamtsummeWarenLocalized())
            ->assign('Bestellung', $order)
            ->assign('Link', $this->currentLink);

        unset(
            $_SESSION['Zahlungsart'],
            $_SESSION['Versandart'],
            $_SESSION['Lieferadresse'],
            $_SESSION['VersandKupon'],
            $_SESSION['NeukundenKupon'],
            $_SESSION['Kupon']
        );

        $this->preRender();

        return $this->smarty->getResponse('checkout/order_completed.tpl');
    }

    /**
     * @return bool
     * @former bestellungKomplett()
     * @since 5.2.0
     */
    public function isOrderComplete(): bool
    {
        $validationData          = (new CheckboxValidationDataObject())->hydrate(
            [
                'customerGroupId' => $this->customerGroupID,
                'location'        => \CHECKBOX_ORT_BESTELLABSCHLUSS,
                'active'          => true,
            ]
        );
        $_SESSION['cPlausi_arr'] = $this->checkboxService->validateCheckBox($validationData, $this->request->getBody());

        $_SESSION['cPost_arr'] = $this->request->getBody();

        return (isset($_SESSION['Kunde'], $_SESSION['Lieferadresse'], $_SESSION['Versandart'], $_SESSION['Zahlungsart'])
            && $_SESSION['Kunde']
            && $_SESSION['Lieferadresse']
            && (int)$_SESSION['Versandart']->kVersandart > 0
            && (int)$_SESSION['Zahlungsart']->kZahlungsart > 0
            && $this->request->requestInt('abschluss') === 1
            && \count($_SESSION['cPlausi_arr']) === 0
        );
    }

    /**
     * @return int
     * @former gibFehlendeEingabe()
     * @since 5.2.0
     */
    public function getErorCode(): int
    {
        if (!isset($_SESSION['Kunde']) || !$_SESSION['Kunde']) {
            return 1;
        }
        if (!isset($_SESSION['Lieferadresse']) || !$_SESSION['Lieferadresse']) {
            return 2;
        }
        if (!isset($_SESSION['Versandart'])
            || !$_SESSION['Versandart']
            || (int)$_SESSION['Versandart']->kVersandart === 0
        ) {
            return 3;
        }
        if (!isset($_SESSION['Zahlungsart'])
            || !$_SESSION['Zahlungsart']
            || (int)$_SESSION['Zahlungsart']->kZahlungsart === 0
        ) {
            return 4;
        }
        if (\count($_SESSION['cPlausi_arr']) > 0) {
            return 6;
        }

        return -1;
    }

    /**
     * @param Bestellung $order
     * @return void
     */
    public function setzeSmartyWeiterleitung(Bestellung $order): void
    {
        $moduleID = $_SESSION['Zahlungsart']->cModulId;

        $logger = Shop::Container()->getLogService();
        if ($logger->isHandling(\JTLLOG_LEVEL_DEBUG)) {
            $logger->withName('cModulId')->debug(
                'setzeSmartyWeiterleitung wurde mit folgender Zahlungsart ausgefuehrt: ' .
                \print_r($_SESSION['Zahlungsart'], true),
                [$moduleID]
            );
        }
        $pluginID = Helper::getIDByModuleID($moduleID);
        if ($pluginID > 0) {
            $loader = Helper::getLoaderByPluginID($pluginID);
            $plugin = $loader->init($pluginID);
            global $oPlugin;
            $oPlugin = $plugin;
            if ($plugin !== null) {
                $pluginPaymentMethod = $plugin->getPaymentMethods()->getMethodByID($moduleID);
                if ($pluginPaymentMethod === null) {
                    return;
                }
                $className = $pluginPaymentMethod->getClassName();
                /** @var MethodInterface $paymentMethod */
                $paymentMethod           = new $className($moduleID);
                $paymentMethod->cModulId = $moduleID;
                $paymentMethod->preparePaymentProcess($order);
                $this->smarty->assign('oPlugin', $plugin)
                    ->assign('plugin', $plugin);
            }
        } elseif ($moduleID === 'za_lastschrift_jtl') {
            $this->smarty->assign('abschlussseite', 1);
        }

        \executeHook(\HOOK_BESTELLABSCHLUSS_INC_SMARTYWEITERLEITUNG);
    }
}
