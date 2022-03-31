<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use InvalidArgumentException;
use JTL\Cart\Cart;
use JTL\Cart\CartHelper;
use JTL\Checkout\Bestellung;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Plugin\Helper;
use JTL\Plugin\Payment\LegacyMethod;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\SimpleMail;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;

/**
 * Class OrderCompleteController
 * @package JTL\Router\Controller
 */
class OrderCompleteController extends CheckoutController
{
    public function init(): bool
    {
        parent::init();
        Shop::setPageType(\PAGE_BESTELLABSCHLUSS);

        return true;
    }

    public function getResponse(JTLSmarty $smarty): ResponseInterface
    {
        require_once \PFAD_ROOT . \PFAD_INCLUDES . 'bestellabschluss_inc.php';
        require_once \PFAD_ROOT . \PFAD_INCLUDES . 'bestellvorgang_inc.php';
        if (Request::getInt('payAgain') === 1 && Request::getInt('kBestellung') > 0) {
            return $this->handlePayAgain($smarty, Request::getInt('kBestellung'));
        }
        $linkHelper = Shop::Container()->getLinkService();
        $cart       = Frontend::getCart();
        $order      = null;
        if (isset($_GET['i'])) {
            $bestellid = $this->db->select('tbestellid', 'cId', $_GET['i']);
            if (isset($bestellid->kBestellung) && $bestellid->kBestellung > 0) {
                $bestellid->kBestellung = (int)$bestellid->kBestellung;
                $order                  = new Bestellung($bestellid->kBestellung);
                $order->fuelleBestellung(false);
                \speicherUploads($order);
                $this->db->delete('tbestellid', 'kBestellung', $bestellid->kBestellung);
            }
            $this->db->query('DELETE FROM tbestellid WHERE dDatum < DATE_SUB(NOW(), INTERVAL 30 DAY)');
            $smarty->assign('abschlussseite', 1);
        } else {
            if (isset($_POST['kommentar'])) {
                $_SESSION['kommentar'] = mb_substr(\strip_tags($this->db->escape($_POST['kommentar'])), 0, 1000);
            } elseif (!isset($_SESSION['kommentar'])) {
                $_SESSION['kommentar'] = '';
            }
            if (SimpleMail::checkBlacklist($_SESSION['Kunde']->cMail)) {
                \header('Location: ' . $linkHelper->getStaticRoute('bestellvorgang.php')
                    . '?mailBlocked=1', true, 303);
                exit;
            }
            if (!\bestellungKomplett()) {
                \header('Location: ' . $linkHelper->getStaticRoute('bestellvorgang.php')
                    . '?fillOut=' . \gibFehlendeEingabe(), true, 303);
                exit;
            }
            if ($cart->removeParentItems() > 0) {
                $this->alertService->addWarning(
                    Shop::Lang()->get('warningCartContainedParentItems', 'checkout'),
                    'warningCartContainedParentItems',
                    ['saveInSession' => true]
                );
                \header('Location: ' . $linkHelper->getStaticRoute('warenkorb.php'), true, 303);
                exit;
            }
            $cart->pruefeLagerbestaende();
            if ($cart->checkIfCouponIsStillValid() === false) {
                $_SESSION['checkCouponResult']['ungueltig'] = 3;
                \header('Location: ' . $linkHelper->getStaticRoute('warenkorb.php'), true, 303);
                exit;
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
                    \header('Location: ' . $linkHelper->getStaticRoute('warenkorb.php'), true, 303);
                    exit;
                }
                $order = \finalisiereBestellung();
                if ($order->Lieferadresse === null && !empty($_SESSION['Lieferadresse']->cVorname)) {
                    $order->Lieferadresse = \gibLieferadresseAusSession();
                }
                $smarty->assign('Bestellung', $order);
            } else {
                $order = \fakeBestellung();
            }
            \setzeSmartyWeiterleitung($order);
        }
        $smarty->assign('WarensummeLocalized', $cart->gibGesamtsummeWarenLocalized())
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
                $smarty->assign('oPlugin', $plugin)
                    ->assign('plugin', $plugin);
            } catch (InvalidArgumentException $e) {
                Shop::Container()->getLogService()->error(
                    'Associated plugin for payment method ' . $order->Zahlungsart->cModulId . ' not found'
                );
            }
        }
        if (empty($_SESSION['Zahlungsart']->nWaehrendBestellung) || isset($_GET['i'])) {
            Frontend::getInstance()->cleanUp();
            $this->preRender($smarty);
            \executeHook(\HOOK_BESTELLABSCHLUSS_PAGE, ['oBestellung' => $order]);
            return $smarty->getResponse('checkout/order_completed.tpl');
        }

        $this->preRender($smarty);
        \executeHook(\HOOK_BESTELLABSCHLUSS_PAGE_ZAHLUNGSVORGANG, ['oBestellung' => $order]);
        return $smarty->getResponse('checkout/step6_init_payment.tpl');
    }

    /**
     * @param JTLSmarty $smarty
     * @param int       $orderID
     * @return ResponseInterface
     */
    protected function handlePayAgain(JTLSmarty $smarty, int $orderID): ResponseInterface
    {
        $linkHelper = Shop::Container()->getLinkService();
        $order      = new Bestellung($orderID, true);
        //abfragen, ob diese Bestellung dem Kunden auch gehoert
        //bei Gastbestellungen ist ggf das Kundenobjekt bereits entfernt bzw nRegistriert = 0
        if ($order->oKunde !== null
            && $order->oKunde->nRegistriert === 1
            && $order->kKunde !== Frontend::getCustomer()->getID()
        ) {
            \header('Location: ' . $linkHelper->getStaticRoute('jtl.php'), true, 303);
            exit;
        }

        $bestellid = $this->db->select('tbestellid', 'kBestellung', $order->kBestellung);
        $moduleID  = $order->Zahlungsart->cModulId;
        $smarty->assign('Bestellung', $order)
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
                        Text::htmlentities(\stripslashes($_POST['bankname']), \ENT_QUOTES);
                    $_SESSION['Zahlungsart']->ZahlungsInfo->cKontoNr  =
                        Text::htmlentities(\stripslashes($_POST['kontonr']), \ENT_QUOTES);
                    $_SESSION['Zahlungsart']->ZahlungsInfo->cBLZ      =
                        Text::htmlentities(\stripslashes($_POST['blz']), \ENT_QUOTES);
                    $_SESSION['Zahlungsart']->ZahlungsInfo->cIBAN     =
                        Text::htmlentities(\stripslashes($_POST['iban']), \ENT_QUOTES);
                    $_SESSION['Zahlungsart']->ZahlungsInfo->cBIC      =
                        Text::htmlentities(\stripslashes($_POST['bic']), \ENT_QUOTES);
                    $_SESSION['Zahlungsart']->ZahlungsInfo->cInhaber  =
                        Text::htmlentities(\stripslashes($_POST['inhaber']), \ENT_QUOTES);
                    $hasAdditionalInformation                         = true;
                }
            }

            if ($hasAdditionalInformation) {
                if (\saveZahlungsInfo($order->kKunde, $order->kBestellung)) {
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
                    \header('Location: ' . $successPaymentURL, true, 303);
                    exit();
                }
            } else {
                $smarty->assign('ZahlungsInfo', $this->gibPostZahlungsInfo());
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
                        $order->Zahlungsart = $this->gibZahlungsart($order->kZahlungsart);
                    }
                }

                $smarty->assign('oPlugin', $plugin)
                    ->assign('plugin', $plugin);
            } catch (InvalidArgumentException $e) {
            }
        } elseif ($moduleID === 'za_lastschrift_jtl') {
            $customerAccountData = $this->gibKundenKontodaten(Frontend::getCustomer()->getID());
            if (isset($customerAccountData->kKunde) && $customerAccountData->kKunde > 0) {
                $smarty->assign('oKundenKontodaten', $customerAccountData);
            }
        }

        $smarty->assign('WarensummeLocalized', Frontend::getCart()->gibGesamtsummeWarenLocalized())
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

        $this->preRender($smarty);

        return $smarty->getResponse('checkout/order_completed.tpl');
    }
}
