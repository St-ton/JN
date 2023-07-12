<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use InvalidArgumentException;
use JTL\Cart\Cart;
use JTL\Cart\CartHelper;
use JTL\CheckBox;
use JTL\Checkout\Bestellung;
use JTL\Checkout\OrderHandler;
use JTL\Helpers\Request;
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
        Shop::setPageType(\PAGE_BESTELLABSCHLUSS);
        require_once \PFAD_ROOT . \PFAD_INCLUDES . 'bestellabschluss_inc.php';
        require_once \PFAD_ROOT . \PFAD_INCLUDES . 'bestellvorgang_inc.php';
        if (Request::getInt('payAgain') === 1 && Request::getInt('kBestellung') > 0) {
            return $this->handlePayAgain(Request::getInt('kBestellung'));
        }
        $cart       = Frontend::getCart();
        $handler    = new OrderHandler($this->db, Frontend::getCustomer(), $cart);
        $linkHelper = Shop::Container()->getLinkService();
        $order      = null;
        if (isset($_GET['i'])) {
            $bestellid = $this->db->select('tbestellid', 'cId', $_GET['i']);
            if (isset($bestellid->kBestellung) && $bestellid->kBestellung > 0) {
                $bestellid->kBestellung = (int)$bestellid->kBestellung;
                $order                  = new Bestellung($bestellid->kBestellung);
                $order->fuelleBestellung(false);
                $handler->saveUploads($order);
                $this->db->delete('tbestellid', 'kBestellung', $bestellid->kBestellung);
            }
            $this->db->query('DELETE FROM tbestellid WHERE dDatum < DATE_SUB(NOW(), INTERVAL 30 DAY)');
            $this->smarty->assign('abschlussseite', 1);
        } else {
            if (isset($_POST['kommentar'])) {
                $_SESSION['kommentar'] = \mb_substr(\strip_tags($this->db->escape($_POST['kommentar'])), 0, 1000);
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
        if (empty($_SESSION['Zahlungsart']->nWaehrendBestellung) || isset($_GET['i'])) {
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
        if (Request::verifyGPCDataInt('zusatzschritt') === 1) {
            $hasAdditionalInformation = false;
            if ($moduleID === 'za_lastschrift_jtl') {
                if (($_POST['bankname']
                        && $_POST['blz']
                        && $_POST['kontonr']
                        && $_POST['inhaber'])
                    || ($_POST['bankname']
                        && $_POST['iban']
                        && $_POST['bic']
                        && $_POST['inhaber'])
                ) {
                    $_SESSION['Zahlungsart']->ZahlungsInfo->cBankName =
                        Text::htmlentities(\stripslashes($_POST['bankname'] ?? ''), \ENT_QUOTES);
                    $_SESSION['Zahlungsart']->ZahlungsInfo->cKontoNr  =
                        Text::htmlentities(\stripslashes($_POST['kontonr'] ?? ''), \ENT_QUOTES);
                    $_SESSION['Zahlungsart']->ZahlungsInfo->cBLZ      =
                        Text::htmlentities(\stripslashes($_POST['blz'] ?? ''), \ENT_QUOTES);
                    $_SESSION['Zahlungsart']->ZahlungsInfo->cIBAN     =
                        Text::htmlentities(\stripslashes($_POST['iban'] ?? ''), \ENT_QUOTES);
                    $_SESSION['Zahlungsart']->ZahlungsInfo->cBIC      =
                        Text::htmlentities(\stripslashes($_POST['bic'] ?? ''), \ENT_QUOTES);
                    $_SESSION['Zahlungsart']->ZahlungsInfo->cInhaber  =
                        Text::htmlentities(\stripslashes($_POST['inhaber'] ?? ''), \ENT_QUOTES);
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
                    } elseif (!$paymentMethod->handleAdditional($_POST)) {
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
        $_SESSION['cPlausi_arr'] = (new CheckBox(0, $this->db))->validateCheckBox(
            \CHECKBOX_ORT_BESTELLABSCHLUSS,
            $this->customerGroupID,
            $_POST,
            true
        );
        $_SESSION['cPost_arr']   = $_POST;

        return (isset($_SESSION['Kunde'], $_SESSION['Lieferadresse'], $_SESSION['Versandart'], $_SESSION['Zahlungsart'])
            && $_SESSION['Kunde']
            && $_SESSION['Lieferadresse']
            && (int)$_SESSION['Versandart']->kVersandart > 0
            && (int)$_SESSION['Zahlungsart']->kZahlungsart > 0
            && Request::verifyGPCDataInt('abschluss') === 1
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
