<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Cart\Warenkorb;
use JTL\Checkout\Bestellung;
use JTL\Checkout\ZahlungsLog;
use JTL\Customer\Kunde;
use JTL\DB\ReturnType;
use JTL\Helpers\Request;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Plugin\Helper as PluginHelper;
use JTL\Session\Frontend;
use JTL\Shop;

/**
 * Class PaymentMethod
 *
 * Represents a Method of Payment the customer can pay his order with.
 * Paypal, for example.
 */
class PaymentMethod
{
    /**
     * i.e. za_mbqc_visa_jtl
     *
     * @var string
     */
    public $moduleID;

    /**
     * i.e. mbqc_visa for za_mbqc_visa_jtl
     *
     * @var string
     */
    public $moduleAbbr;

    /**
     * Internal Name w/o whitespace, e.g. 'MoneybookersQC'.
     *
     * @var string
     */
    public $name;

    /**
     * E.g. 'Moneybookers Quick Connect'.
     *
     * @var string
     */
    public $caption;

    /**
     * @var bool
     */
    public $duringCheckout;

    /**
     * @var string
     */
    public $cModulId;

    /**
     * @var bool
     */
    public $bPayAgain;

    /**
     * @var array
     */
    public $paymentConfig;

    /**
     * @param string $moduleID
     * @param int    $nAgainCheckout
     */
    public function __construct($moduleID, $nAgainCheckout = 0)
    {
        $this->moduleID = $moduleID;
        // extract: za_mbqc_visa_jtl => myqc_visa
        $pattern = '&za_(.*)_jtl&is';
        preg_match($pattern, $moduleID, $subpattern);
        $this->moduleAbbr = $subpattern[1] ?? null;

        $this->loadSettings();
        $this->init($nAgainCheckout);
    }

    /**
     * Set Members Variables
     *
     * @param int $nAgainCheckout
     * @return $this
     */
    public function init($nAgainCheckout = 0)
    {
        $this->name           = '';
        $result               = Shop::Container()->getDB()->select('tzahlungsart', 'cModulId', $this->moduleID);
        $this->caption        = $result->cName ?? null;
        $this->duringCheckout = isset($result->nWaehrendBestellung)
            ? (int)$result->nWaehrendBestellung
            : 0;

        if ((int)$nAgainCheckout === 1) {
            $this->duringCheckout = 0;
        }
        if ($this->cModulId === 'za_null_jtl' || $this->moduleID === 'za_null_jtl') {
            $this->kZahlungsart = $result->kZahlungsart;
        }
        return $this;
    }

    /**
     * @param Bestellung $order
     * @return string
     */
    public function getOrderHash($order)
    {
        $orderId = isset($order->kBestellung)
            ? Shop::Container()->getDB()->query(
                'SELECT cId FROM tbestellid WHERE kBestellung = ' . (int)$order->kBestellung,
                ReturnType::SINGLE_OBJECT
            )
            : null;

        return $orderId->cId ?? null;
    }

    /**
     * Payment Provider redirects customer to this URL when Payment is complete
     *
     * @param Bestellung $order
     * @return string
     */
    public function getReturnURL($order)
    {
        if (!isset($_SESSION['Zahlungsart']->nWaehrendBestellung)
            && (int)$_SESSION['Zahlungsart']->nWaehrendBestellung > 0
        ) {
            return Shop::getURL() . '/bestellvorgang.php';
        }
        if (Shop::getSettings([CONF_KAUFABWICKLUNG])['kaufabwicklung']['bestellabschluss_abschlussseite'] === 'A') {
            // Abschlussseite
            $oZahlungsID = Shop::Container()->getDB()->query(
                'SELECT cId
                    FROM tbestellid
                    WHERE kBestellung = ' . (int)$order->kBestellung,
                ReturnType::SINGLE_OBJECT
            );
            if (is_object($oZahlungsID)) {
                return Shop::getURL() . '/bestellabschluss.php?i=' . $oZahlungsID->cId;
            }
        }

        return $order->BestellstatusURL;
    }

    /**
     * @param string $hash
     * @return string
     */
    public function getNotificationURL($hash)
    {
        $key = $this->duringCheckout ? 'sh' : 'ph';

        return Shop::getURL() . '/includes/modules/notify.php?' . $key . '=' . $hash;
    }

    /**
     * @param int    $kBestellung
     * @param string $cNotifyID
     * @return $this
     */
    public function updateNotificationID($kBestellung, $cNotifyID)
    {
        $kBestellung = (int)$kBestellung;
        if ($kBestellung > 0) {
            $_upd            = new stdClass();
            $_upd->cNotifyID = Shop::Container()->getDB()->escape($cNotifyID);
            $_upd->dNotify   = 'NOW()';
            Shop::Container()->getDB()->update('tzahlungsession', 'kBestellung', $kBestellung, $_upd);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getShopTitle()
    {
        return Shop::getConfigValue(CONF_GLOBAL, 'global_shopname');
    }

    /**
     * Prepares everything so that the Customer can start the Payment Process.
     * Tells Template Engine.
     *
     * @param Bestellung $order
     */
    public function preparePaymentProcess($order)
    {
        // overwrite!
    }

    /**
     * Sends Error Mail to Master
     *
     * @param string $body
     * @return $this
     */
    public function sendErrorMail($body)
    {
        $conf                = Shop::getSettings([CONF_EMAILS]);
        $mail                = new stdClass();
        $mail->toEmail       = $conf['emails']['email_master_absender'];
        $mail->toName        = $conf['emails']['email_master_absender_name'];
        $mail->fromEmail     = $mail->toEmail;
        $mail->fromName      = $mail->toName;
        $mail->subject       = sprintf(
            Shop::Lang()->get('errorMailSubject', 'paymentMethods'),
            $conf['global']['global_meta_title']
        );
        $mail->bodyText      = $body;
        $mail->methode       = $conf['eMails']['eMail_methode'];
        $mail->sendMail_pfad = $conf['eMails']['eMail_sendMail_pfad'];
        $mail->smtp_hostname = $conf['eMails']['eMail_smtp_hostname'];
        $mail->smtp_port     = $conf['eMails']['eMail_smtp_port'];
        $mail->smtp_auth     = $conf['eMails']['eMail_smtp_auth'];
        $mail->smtp_user     = $conf['eMails']['eMail_smtp_user'];
        $mail->smtp_pass     = $conf['eMails']['eMail_smtp_pass'];
        $mail->SMTPSecure    = $conf['emails']['email_smtp_verschluesselung'];
        include_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
        verschickeMail($mail);

        return $this;
    }

    /**
     * Generates Hash (Payment oder Session Hash) and saves it to DB
     *
     * @param Bestellung $order
     * @param int        $length
     * @return string
     */
    public function generateHash($order, $length = 40)
    {
        $hash = null;
        if ((int)$this->duringCheckout === 1) {
            if (!isset($_SESSION['IP'])) {
                $_SESSION['IP'] = new stdClass();
            }
            $_SESSION['IP']->cIP = Request::getRealIP();
        }

        if ($order->kBestellung !== null) {
            $oBestellID                = Shop::Container()->getDB()->select(
                'tbestellid',
                'kBestellung',
                (int)$order->kBestellung
            );
            $hash                      = $oBestellID->cId;
            $oZahlungsID               = new stdClass();
            $oZahlungsID->kBestellung  = $order->kBestellung;
            $oZahlungsID->kZahlungsart = $order->kZahlungsart;
            $oZahlungsID->cId          = $hash;
            $oZahlungsID->txn_id       = '';
            $oZahlungsID->dDatum       = 'NOW()';
            Shop::Container()->getDB()->insert('tzahlungsid', $oZahlungsID);
        } else {
            Shop::Container()->getDB()->delete('tzahlungsession', ['cSID', 'kBestellung'], [session_id(), 0]);
            $oZahlungSession               = new stdClass();
            $oZahlungSession->cSID         = session_id();
            $oZahlungSession->cNotifyID    = '';
            $oZahlungSession->dZeitBezahlt = '_DBNULL_';
            $oZahlungSession->cZahlungsID  = uniqid('', true);
            $oZahlungSession->dZeit        = 'NOW()';
            Shop::Container()->getDB()->insert('tzahlungsession', $oZahlungSession);
            $hash = '_' . $oZahlungSession->cZahlungsID;
        }

        return $hash;
    }

    /**
     * @param string $paymentHash
     * @return $this
     */
    public function deletePaymentHash($paymentHash)
    {
        Shop::Container()->getDB()->delete('tzahlungsid', 'cId', $paymentHash);

        return $this;
    }

    /**
     * @param Bestellung $order
     * @param Object     $payment (Key, Zahlungsanbieter, Abgeholt, Zeit is set here)
     * @return $this
     */
    public function addIncomingPayment($order, $payment)
    {
        $model = (object)array_merge([
            'kBestellung'       => (int)$order->kBestellung,
            'cZahlungsanbieter' => empty($order->cZahlungsartName) ? $this->name : $order->cZahlungsartName,
            'fBetrag'           => 0,
            'fZahlungsgebuehr'  => 0,
            'cISO'              => Frontend::getCurrency()->getCode(),
            'cEmpfaenger'       => '',
            'cZahler'           => '',
            'dZeit'             => 'NOW()',
            'cHinweis'          => '',
            'cAbgeholt'         => 'N'
        ], (array)$payment);
        Shop::Container()->getDB()->insert('tzahlungseingang', $model);

        return $this;
    }

    /**
     * @param Bestellung $order
     * @return $this
     */
    public function setOrderStatusToPaid($order)
    {
        $_upd                = new stdClass();
        $_upd->cStatus       = BESTELLUNG_STATUS_BEZAHLT;
        $_upd->dBezahltDatum = 'NOW()';
        Shop::Container()->getDB()->update('tbestellung', 'kBestellung', (int)$order->kBestellung, $_upd);

        return $this;
    }

    /**
     * Sends a Mail to the Customer if Payment was recieved
     *
     * @param Bestellung $order
     * @return $this
     */
    public function sendConfirmationMail($order)
    {
        $this->sendMail($order->kBestellung, MAILTEMPLATE_BESTELLUNG_BEZAHLT);

        return $this;
    }

    /**
     * @param Bestellung $order
     * @param string     $hash
     * @param array      $args
     */
    public function handleNotification($order, $hash, $args)
    {
        // overwrite!
    }

    /**
     * @param Bestellung $order
     * @param string     $hash
     * @param array      $args
     *
     * @return true, if $order should be finalized
     */
    public function finalizeOrder($order, $hash, $args)
    {
        // overwrite!
        return false;
    }

    /**
     * @return bool
     */
    public function redirectOnCancel()
    {
        // overwrite!
        return false;
    }

    /**
     * @return bool
     */
    public function redirectOnPaymentSuccess()
    {
        // overwrite!
        return false;
    }

    /**
     * @param string $msg
     * @param int    $level
     * @return $this
     */
    public function doLog($msg, $level = LOGLEVEL_NOTICE)
    {
        ZahlungsLog::add($this->moduleID, $msg, null, $level);

        return $this;
    }

    /**
     * @param int $kKunde
     * @return int
     */
    public function getCustomerOrderCount($kKunde)
    {
        if ((int)$kKunde > 0) {
            $oBestellung = Shop::Container()->getDB()->query(
                "SELECT COUNT(*) AS nAnzahl
                    FROM tbestellung
                    WHERE (cStatus = '2' || cStatus = '3' || cStatus = '4')
                        AND kKunde = " . (int)$kKunde,
                ReturnType::SINGLE_OBJECT
            );

            if (isset($oBestellung->nAnzahl) && count($oBestellung->nAnzahl) > 0) {
                return (int)$oBestellung->nAnzahl;
            }
        }

        return 0;
    }

    /**
     * @return $this
     */
    public function loadSettings()
    {
        $this->paymentConfig = Shop::getSettings([CONF_ZAHLUNGSARTEN])['zahlungsarten'];

        return $this;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getSetting($key)
    {
        $conf = Shop::getSettings([CONF_ZAHLUNGSARTEN, CONF_PLUGINZAHLUNGSARTEN]);

        return $conf['zahlungsarten']['zahlungsart_' . $this->moduleAbbr . '_' . $key]
            ?? ($conf['pluginzahlungsarten'][$this->moduleID . '_' . $key] ?? null);
    }

    /**
     *
     * @param object    $customer
     * @param Warenkorb $cart
     * @return bool - true, if $customer with $cart may use Payment Method
     */
    public function isValid($customer, $cart)
    {
        if ($this->getSetting('min_bestellungen') > 0) {
            if (isset($customer->kKunde) && $customer->kKunde > 0) {
                $res   = Shop::Container()->getDB()->executeQueryPrepared(
                    'SELECT COUNT(*) AS cnt
                        FROM tbestellung
                        WHERE kKunde = :cid
                        AND (cStatus = :stp OR cStatus = :sts)',
                    [
                        'cid' => (int)$customer->kKunde,
                        'stp' => BESTELLUNG_STATUS_BEZAHLT,
                        'sts' => BESTELLUNG_STATUS_VERSANDT
                    ],
                    ReturnType::SINGLE_OBJECT
                );
                $count = (int)$res->cnt;
                if ($count < $this->getSetting('min_bestellungen')) {
                    ZahlungsLog::add(
                        $this->moduleID,
                        'Bestellanzahl ' . $count . ' ist kleiner als die Mindestanzahl von ' .
                            $this->getSetting('min_bestellungen'),
                        null,
                        LOGLEVEL_NOTICE
                    );

                    return false;
                }
            } else {
                ZahlungsLog::add($this->moduleID, 'Es ist kein kKunde vorhanden', null, LOGLEVEL_NOTICE);

                return false;
            }
        }

        if ($this->getSetting('min') > 0 && $cart->gibGesamtsummeWaren(true) <= $this->getSetting('min')) {
            ZahlungsLog::add(
                $this->moduleID,
                'Bestellwert ' . $cart->gibGesamtsummeWaren(true) .
                    ' ist kleiner als der Mindestbestellwert von ' . $this->getSetting('min'),
                null,
                LOGLEVEL_NOTICE
            );

            return false;
        }

        if ($this->getSetting('max') > 0 && $cart->gibGesamtsummeWaren(true) >= $this->getSetting('max')) {
            ZahlungsLog::add(
                $this->moduleID,
                'Bestellwert ' . $cart->gibGesamtsummeWaren(true) .
                    ' ist groesser als der maximale Bestellwert von ' . $this->getSetting('max'),
                null,
                LOGLEVEL_NOTICE
            );

            return false;
        }

        if (!$this->isValidIntern($customer, $cart)) {
            return false;
        }

        return true;
    }

    /**
     * @param array $args_arr
     * @return bool
     */
    public function isValidIntern($args_arr = [])
    {
        // Overwrite
        return true;
    }

    /**
     * determines, if the payment method can be selected in the checkout process
     *
     * @return bool
     */
    public function isSelectable()
    {
        // Overwrite
        return true;
    }

    /**
     * @param array $aPost_arr
     * @return bool
     */
    public function handleAdditional($aPost_arr)
    {
        return true;
    }

    /**
     * @return bool
     */
    public function validateAdditional()
    {
        return true;
    }

    /**
     *
     * @param string $cKey
     * @param string $cValue
     * @return $this
     */
    public function addCache($cKey, $cValue)
    {
        $_SESSION[$this->moduleID][$cKey] = $cValue;

        return $this;
    }

    /**
     * @param string|null $cKey
     * @return $this
     */
    public function unsetCache($cKey = null)
    {
        if ($cKey === null) {
            unset($_SESSION[$this->moduleID]);
        } else {
            unset($_SESSION[$this->moduleID][$cKey]);
        }

        return $this;
    }

    /**
     * @param null|string $cKey
     * @return null
     */
    public function getCache($cKey = null)
    {
        if ($cKey === null) {
            return $_SESSION[$this->moduleID] ?? null;
        }

        return $_SESSION[$this->moduleID][$cKey] ?? null;
    }

    /**
     * @param int $kBestellung
     * @param int $kSprache
     * @return object
     */
    public function createInvoice($kBestellung, $kSprache)
    {
        $oInvoice        = new stdClass();
        $oInvoice->nType = 0;
        $oInvoice->cInfo = '';

        return $oInvoice;
    }

    /**
     * @param int $kBestellung
     * @return $this
     */
    public function reactivateOrder($kBestellung)
    {
        $kBestellung = (int)$kBestellung;
        $this->sendMail($kBestellung, MAILTEMPLATE_BESTELLUNG_RESTORNO);
        $_upd                = new stdClass();
        $_upd->cStatus       = BESTELLUNG_STATUS_IN_BEARBEITUNG;
        $_upd->dBezahltDatum = 'NOW()';
        Shop::Container()->getDB()->update('tbestellung', 'kBestellung', $kBestellung, $_upd);

        return $this;
    }

    /**
     * @param int  $kBestellung
     * @param bool $bDelete
     * @return $this
     */
    public function cancelOrder($kBestellung, $bDelete = false)
    {
        if (!$bDelete) {
            $kBestellung = (int)$kBestellung;
            $this->sendMail($kBestellung, MAILTEMPLATE_BESTELLUNG_STORNO);
            $_upd                = new stdClass();
            $_upd->cStatus       = BESTELLUNG_STATUS_STORNO;
            $_upd->dBezahltDatum = 'NOW()';
            Shop::Container()->getDB()->update('tbestellung', 'kBestellung', $kBestellung, $_upd);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function canPayAgain()
    {
        // overwrite
        return false;
    }

    /**
     * @param int    $orderID
     * @param string $type
     * @param mixed  $additional
     * @return $this
     */
    public function sendMail($orderID, $type, $additional = null)
    {
        $order = new Bestellung($orderID);
        $order->fuelleBestellung(false);
        $customer = new Kunde($order->kKunde);
        $data     = new stdClass();
        $mailer = Shop::Container()->get(Mailer::class);
        $mail   = new Mail();

        switch ($type) {
            case MAILTEMPLATE_BESTELLBESTAETIGUNG:
                $data->tkunde      = $customer;
                $data->tbestellung = $order;
                if (strlen($customer->cMail) > 0) {
                    $mailer->send($mail->createFromTemplateID($type, $data));
                }
                break;

            case MAILTEMPLATE_BESTELLUNG_AKTUALISIERT:
                $data->tkunde      = $customer;
                $data->tbestellung = $order;
                if (strlen($customer->cMail) > 0) {
                    $mailer->send($mail->createFromTemplateID($type, $data));
                }
                break;

            case MAILTEMPLATE_BESTELLUNG_VERSANDT:
                $data->tkunde      = $customer;
                $data->tbestellung = $order;
                if (strlen($customer->cMail) > 0) {
                    $mailer->send($mail->createFromTemplateID($type, $data));
                }
                break;

            case MAILTEMPLATE_BESTELLUNG_TEILVERSANDT:
                $data->tkunde      = $customer;
                $data->tbestellung = $order;
                if (strlen($customer->cMail) > 0) {
                    $mailer->send($mail->createFromTemplateID($type, $data));
                }
                break;

            case MAILTEMPLATE_BESTELLUNG_BEZAHLT:
                $data->tkunde      = $customer;
                $data->tbestellung = $order;
                if (($order->Zahlungsart->nMailSenden & ZAHLUNGSART_MAIL_EINGANG) && strlen($customer->cMail) > 0) {
                    $mailer->send($mail->createFromTemplateID($type, $data));
                }
                break;

            case MAILTEMPLATE_BESTELLUNG_STORNO:
                $data->tkunde      = $customer;
                $data->tbestellung = $order;
                if (($order->Zahlungsart->nMailSenden & ZAHLUNGSART_MAIL_STORNO) && strlen($customer->cMail) > 0) {
                    $mailer->send($mail->createFromTemplateID($type, $data));
                }
                break;

            case MAILTEMPLATE_BESTELLUNG_RESTORNO:
                $data->tkunde      = $customer;
                $data->tbestellung = $order;
                if (($order->Zahlungsart->nMailSenden & ZAHLUNGSART_MAIL_RESTORNO) && strlen($customer->cMail) > 0) {
                    $mailer->send($mail->createFromTemplateID($type, $data));
                }
                break;

            default:
                break;
        }

        return $this;
    }

    /**
     * @param string $moduleId
     * @return PaymentMethod
     */
    public static function create($moduleId)
    {
        global $oPlugin;
        $oTmpPlugin    = $oPlugin;
        $paymentMethod = null;
        $pluginID      = PluginHelper::getIDByModuleID($moduleId);
        if ($pluginID > 0) {
            $loader = PluginHelper::getLoaderByPluginID($pluginID);
            try {
                $oPlugin = $loader->init($pluginID);
            } catch (InvalidArgumentException $e) {
                $oPlugin = null;
            }
            $GLOBALS['oPlugin'] = $oPlugin;

            if ($oPlugin !== null && isset($oPlugin->oPluginZahlungsKlasseAssoc_arr[$moduleId]->cClassPfad)) {
                $classFile = $oPlugin->getPaths()->getVersionedPath() . PFAD_PLUGIN_PAYMENTMETHOD .
                    $oPlugin->oPluginZahlungsKlasseAssoc_arr[$moduleId]->cClassPfad;
                if (file_exists($classFile)) {
                    require_once $classFile;
                    $className               = $oPlugin->oPluginZahlungsKlasseAssoc_arr[$moduleId]->cClassName;
                    $paymentMethod           = new $className($moduleId);
                    $paymentMethod->cModulId = $moduleId;
                }
            }
        } elseif ($moduleId === 'za_null_jtl') {
            require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'fallback/FallBackPayment.php';
            $paymentMethod = new FallBackPayment('za_null_jtl');
        }
        $oPlugin = $oTmpPlugin;

        return $paymentMethod;
    }
}
