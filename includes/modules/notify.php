<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/../../includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'sprachfunktionen.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';

define('NO_MODE', 0);
define('NO_PFAD', PFAD_LOGFILES . 'notify.log');

$logger              = Shop::Container()->getLogService();
$moduleId            = null;
$Sprache             = Shop::Container()->getDB()->select('tsprache', 'cShopStandard', 'Y');
$Einstellungen       = Shop::getSettings([
    CONF_GLOBAL,
    CONF_KUNDEN,
    CONF_KAUFABWICKLUNG,
    CONF_ZAHLUNGSARTEN
]);
$cEditZahlungHinweis = '';
//Session Hash
$cPh = RequestHelper::verifyGPDataString('ph');
$cSh = RequestHelper::verifyGPDataString('sh');

executeHook(HOOK_NOTIFY_HASHPARAMETER_DEFINITION);

if (strlen(RequestHelper::verifyGPDataString('ph')) === 0 && strlen(RequestHelper::verifyGPDataString('externalBDRID')) > 0) {
    $cPh = RequestHelper::verifyGPDataString('externalBDRID');
    if ($cPh[0] === '_') {
        $cPh = '';
        $cSh = RequestHelper::verifyGPDataString('externalBDRID');
    }
}
// Work around Sofortüberweisung
if (strlen(RequestHelper::verifyGPDataString('key')) > 0 && strlen(RequestHelper::verifyGPDataString('sid')) > 0) {
    $cPh = RequestHelper::verifyGPDataString('sid');
    if (RequestHelper::verifyGPDataString('key') === 'sh') {
        $cPh = '';
        $cSh = RequestHelper::verifyGPDataString('sid');
    }
}

if (strlen($cSh) > 0) {
    if ($logger->isHandling(JTLLOG_LEVEL_DEBUG)) {
        $logger->debug('Notify SH: ' . print_r($_REQUEST, true));
    }
    // Load from Session Hash / Session Hash starts with "_"
    $sessionHash    = substr(StringHandler::htmlentities(StringHandler::filterXSS($cSh)), 1);
    $paymentSession = Shop::Container()->getDB()->select(
        'tzahlungsession',
        'cZahlungsID',
        $sessionHash,
        null,
        null,
        null,
        null,
        false,
        'cSID, kBestellung'
    );
    if ($paymentSession === false) {
        $logger->error('Session Hash: ' . $cSh . ' ergab keine Bestellung aus tzahlungsession');
        die();
    }
    if ($logger->isHandling(JTLLOG_LEVEL_DEBUG)) {
        Shop::Container()->getLogService()->debug(
            'Session Hash: ' . $cSh . ' ergab tzahlungsession ' .
            print_r($paymentSession, true)
        );
    }
    if (session_id() !== $paymentSession->cSID) {
        session_destroy();
        session_id($paymentSession->cSID);
        $session = \Session\Session::getInstance(true, true);
    } else {
        $session = \Session\Session::getInstance(false, false);
    }
    require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellabschluss_inc.php';

    $logger->debug('Session Hash ' . $cSh . ' ergab cModulId aus Session: ' . $_SESSION['Zahlungsart']->cModulId ?? '---');
    if (!isset($paymentSession->kBestellung) || !$paymentSession->kBestellung) {
        // Generate fake Order and ask PaymentMethod if order should be finalized
        $order = fakeBestellung();
        include_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php';
        $paymentMethod = isset($_SESSION['Zahlungsart']->cModulId)
            ? PaymentMethod::create($_SESSION['Zahlungsart']->cModulId)
            : null;
        if ($paymentMethod !== null) {
            if ($logger->isHandling(JTLLOG_LEVEL_DEBUG)) {
                $logger->debug('Session Hash: ' . $cSh . ' ergab Methode: ' . print_r($paymentMethod, true));
            }

            $kPlugin = Plugin::getIDByModuleID($_SESSION['Zahlungsart']->cModulId);
            if ($kPlugin > 0) {
                $oPlugin            = new Plugin($kPlugin);
                $GLOBALS['oPlugin'] = $oPlugin;
            }

            if ($paymentMethod->finalizeOrder($order, $sessionHash, $_REQUEST)) {
                $logger->debug('Session Hash: ' . $cSh . ' ergab finalizeOrder passed');
                $order = finalisiereBestellung();
                $session->cleanUp();

                if ($order->kBestellung > 0) {
                    $logger->debug('tzahlungsession aktualisiert.');
                    $_upd               = new stdClass();
                    $_upd->nBezahlt     = 1;
                    $_upd->dZeitBezahlt = 'now()';
                    $_upd->kBestellung  = (int)$order->kBestellung;
                    Shop::Container()->getDB()->update('tzahlungsession', 'cZahlungsID', $sessionHash, $_upd);
                    $paymentMethod->handleNotification($order, '_' . $sessionHash, $_REQUEST);
                    if ($paymentMethod->redirectOnPaymentSuccess() === true) {
                        header('Location: ' . $paymentMethod->getReturnURL($order));
                        exit();
                    }
                }
            } else {
                $logger->debug('finalizeOrder failed -> zurueck zur Zahlungsauswahl.');
                $linkHelper = Shop::Container()->getLinkService();
                // UOS Work Around
                if ($_SESSION['Zahlungsart']->cModulId === 'za_sofortueberweisung_jtl' ||
                    $paymentMethod->redirectOnCancel() ||
                    strpos($_SESSION['Zahlungsart']->cModulId, 'za_uos_') !== false ||
                    strpos($_SESSION['Zahlungsart']->cModulId, 'za_ut_') !== false
                ) {
                    // Go to 'Edit PaymentMethod' Page
                    $header = 'Location: ' . $linkHelper->getStaticRoute('bestellvorgang.php') .
                        '?editZahlungsart=1';
                    if (strlen($cEditZahlungHinweis) > 0) {
                        $header = 'Location: ' . $linkHelper->getStaticRoute('bestellvorgang.php') .
                            '?editZahlungsart=1&nHinweis=' . $cEditZahlungHinweis;
                    }
                    header($header);
                    exit();
                }
                if (strlen($cEditZahlungHinweis) > 0) {
                    echo $linkHelper->getStaticRoute('bestellvorgang.php') .
                        '?editZahlungsart=1&nHinweis=' . $cEditZahlungHinweis;
                } else {
                    echo $linkHelper->getStaticRoute('bestellvorgang.php') .
                        '?editZahlungsart=1';
                }
            }
        }
    } else {
        $order = new Bestellung($paymentSession->kBestellung);
        $order->fuelleBestellung(0);
        include_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php';
        $logger->debug('Session Hash ' . $cSh . ' hat kBestellung. Modul ' . $order->Zahlungsart->cModulId . ' wird aufgerufen');

        $paymentMethod = PaymentMethod::create($order->Zahlungsart->cModulId);
        $paymentMethod->handleNotification($order, '_' . $sessionHash, $_REQUEST);
        if ($paymentMethod->redirectOnPaymentSuccess() === true) {
            header('Location: ' . $paymentMethod->getReturnURL($order));
            exit();
        }
    }

    die();
}

/*** Payment Hash ***/

$session = \Session\Session::getInstance();
if (strlen($cPh) > 0) {
    if ($logger->isHandling(JTLLOG_LEVEL_DEBUG)) {
        $logger->debug('Notify request:' . print_r($_REQUEST, true));
    }
    $paymentId   = Shop::Container()->getDB()->queryPrepared(
        "SELECT ZID.kBestellung, ZA.cModulId
            FROM tzahlungsid ZID
            LEFT JOIN tzahlungsart ZA
                ON ZA.kZahlungsart = ZID.kZahlungsart
            WHERE ZID.cId = :hash",
        ['hash' => StringHandler::htmlentities(StringHandler::filterXSS($cPh))],
        \DB\ReturnType::SINGLE_OBJECT
    );

    if ($paymentId === false) {
        $logger->error('Payment Hash ' . $cPh . ' ergab keine Bestellung aus tzahlungsid.');
        die(); // Payment Hash does not exist
    }
    // Load Order
    $moduleId = $paymentId->cModulId;
    $order    = new Bestellung($paymentId->kBestellung);
    $order->fuelleBestellung(0);

    if ($logger->isHandling(JTLLOG_LEVEL_DEBUG)) {
        $logger->debug('Payment Hash ' . $cPh . ' ergab Order ' . print_r($order, true));
    }
}
if ($moduleId !== null) {
    // Let PaymentMethod handle Notification
    include_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php';
    $paymentMethod = PaymentMethod::create($moduleId);
    if ($paymentMethod !== null) {
        if ($logger->isHandling(JTLLOG_LEVEL_DEBUG)) {
            $logger->debug('Payment Hash ' . $cPh . ' ergab Order' . print_r($paymentMethod, true), 1);
        }
        $paymentHash = Shop::Container()->getDB()->escape(StringHandler::htmlentities(StringHandler::filterXSS($cPh)));
        $paymentMethod->handleNotification($order, $paymentHash, $_REQUEST);
        if ($paymentMethod->redirectOnPaymentSuccess() === true) {
            header('Location: ' . $paymentMethod->getReturnURL($order));
            exit();
        }
    }
}
