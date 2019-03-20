<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Checkout\Bestellung;
use JTL\Shop;
use JTL\Sprache;
use JTL\Helpers\Text;
use JTL\Checkout\ZahlungsLog;
use JTL\DB\ReturnType;

include_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php';

// Debug - 1 = An / 0 = Aus
defined('D_MODE') || define('D_MODE', 1);
defined('D_PFAD') || define('D_PFAD', PFAD_LOGFILES . 'sofortueberweisung.log');

/**
 * Class SofortUeberweisung
 */
class SofortUeberweisung extends PaymentMethod
{
    /**
     * @var int
     */
    public $sofortueberweisung_id = 0;

    /**
     * @var int
     */
    public $sofortueberweisung_project_id = 0;

    /**
     * @var string
     */
    public $reason_1 = '';

    /**
     * @var string
     */
    public $reason_2 = '';

    /**
     * @var string
     */
    public $user_variable_0 = '';

    /**
     * @var string
     */
    public $user_variable_1 = '';

    /**
     * @var string
     */
    public $user_variable_2 = '';

    /**
     * @var string
     */
    public $user_variable_3 = '';

    /**
     * @var string
     */
    public $user_variable_4 = '';

    /**
     * @var string
     */
    public $user_variable_5 = '';

    /**
     * @var bool
     */
    public $bDebug = false;

    /**
     * @var string
     */
    public $strAmount = '';

    /**
     * @var string
     */
    public $strSenderCountryID = '';

    /**
     * @var string
     */
    public $strTransactionID = '';

    /**
     * @var string
     */
    public $hash = '';

    /**
     * @param int $nAgainCheckout
     * @return $this
     */
    public function init($nAgainCheckout = 0)
    {
        parent::init($nAgainCheckout);
        $this->name    = 'SOFORT Überweisung';
        $this->caption = 'SOFORT Überweisung';

        return $this;
    }

    /**
     * Projekt Passwort aus DB lesen
     * (wird nur benutzt, wenn auch in sofortüberweisung.de gesetzt)
     *
     * @return string
     */
    public function getProjectPassword()
    {
        $cPasswort      = '';
        $oEinstellungen = Shop::Container()->getDB()->query(
            "SELECT cWert
                FROM teinstellungen
                WHERE cName = 'zahlungsart_sofortueberweisung_project_password'",
            ReturnType::SINGLE_OBJECT
        );

        if (!empty($oEinstellungen->cWert)) {
            $cPasswort = $oEinstellungen->cWert;
        }

        return $cPasswort;
    }

    /**
     * Benachrichtigungspasswort aus DB lesen
     * (wird nur benutzt, wenn auch in sofortüberweisung.de gesetzt)
     *
     * @return string
     */
    public function getNotificationPassword()
    {
        $cPasswort      = '';
        $oEinstellungen = Shop::Container()->getDB()->query(
            "SELECT cWert
                FROM teinstellungen
                WHERE cName = 'zahlungsart_sofortueberweisung_benachrichtigung_password'",
            ReturnType::SINGLE_OBJECT
        );

        if (!empty($oEinstellungen->cWert)) {
            $cPasswort = $oEinstellungen->cWert;
        }

        return $cPasswort;
    }

    /**
     * @param Bestellung $order
     */
    public function preparePaymentProcess($order)
    {
        $smarty = Shop::Smarty();
        if ($order->fGesamtsummeKundenwaehrung > 0) {
            $this->sofortueberweisung_id         = $this->paymentConfig['zahlungsart_sofortueberweisung_id'];
            $this->sofortueberweisung_project_id = $this->paymentConfig['zahlungsart_sofortueberweisung_project_id'];
            if ($this->paymentConfig['zahlungsart_sofortueberweisung_debugmode'] === 'Y') {
                $this->bDebug = true;
            }

            $paymentHash = $this->generateHash($order);
            $this->baueSicherheitsHash($order, $paymentHash);

            if ($this->bDebug === true) {
                echo '<br/><br/>sender_holder: ' . $this->name . '<br/>';
                echo 'sender_country_id: ' . $this->strSenderCountryID . '<br/>';
                echo 'amount: ' . $this->strAmount . '<br/>';
                echo 'currency_id: ' . $order->Waehrung->cISO . '<br/>';
            }

            if (!($this->sofortueberweisung_id
                && $this->sofortueberweisung_project_id
                && $this->name
                && $this->strSenderCountryID
                && $this->strAmount
                && $order->Waehrung->cISO)
            ) {
                if ($this->bDebug === false) {
                    return 'Es ist ein Datenbankfehler aufgetreten!';
                }
                if (!$this->sofortueberweisung_id) {
                    echo '$this->sofortueberweisung_id is null<br/>';
                }
                if (!$this->sofortueberweisung_project_id) {
                    echo '$this->sofortueberweisung_project_id is null<br/>';
                }
                if (!$this->getProjectPassword()) {
                    echo '$this->getProjectPassword() is null<br/>';
                }
            }

            $strReturn =
                '<form method="post" action="https://www.sofortueberweisung.de/payment/start" target="">' .
                '<input name="user_id" type="hidden" value="' . $this->sofortueberweisung_id . '"/>' .
                '<input name="project_id" type="hidden" value="' . $this->sofortueberweisung_project_id . '"/>' .
                '<input name="sender_holder" type="hidden" value="' . $this->name . '"/>' .
                '<input name="sender_account_number" type="hidden" value=""/>' .
                '<input name="sender_bank_code" type="hidden" value=""/>' .
                '<input name="sender_country_id" type="hidden" value="' . $this->strSenderCountryID . '"/>' .
                '<input name="amount" type="hidden" value="' . $this->strAmount . '"/>' .
                '<input name="currency_id" type="hidden" value="' . $order->Waehrung->cISO . '"/>' .
                '<input name="reason_1" type="hidden" value="' . $this->reason_1 . '"/>' .
                '<input name="reason_2" type="hidden" value="' . $this->reason_2 . '"/>' .
                '<input name="user_variable_0" type="hidden" value="' . $this->user_variable_0 . '"/>' .
                '<input name="user_variable_1" type="hidden" value="' . $this->user_variable_1 . '"/>' .
                '<input name="user_variable_2" type="hidden" value="' . $this->user_variable_2 . '"/>' .
                '<input name="user_variable_3" type="hidden" value="' . $this->user_variable_3 . '"/>' .
                '<input name="user_variable_4" type="hidden" value="' . $this->user_variable_4 . '"/>' .
                '<input name="user_variable_5" type="hidden" value="' . $this->user_variable_5 . '"/>' .
                '<input name="hash" type="hidden" value="' . $this->hash . '"/>' .
                '<input type="hidden" name="encoding" value="' . JTL_CHARSET . '">' .
                '<input name="kBestellung" type="hidden" value="' . $order->kBestellung . '"/>' .
                '<input name="interface_version" type="hidden" value="JTL-Shop-3"/>' .
                '<input type="submit" class="btn btn-primary" name="Sofort-Ueberweisung" value="' .
                    Shop::Lang()->get('payWithSofortueberweisung', 'global') . '"/>' .
                '</form>';
            $smarty->assign('sofortueberweisungform', $strReturn);
        }
    }

    /**
     * @param Bestellung $order
     * @param string     $paymentHash
     */
    public function baueSicherheitsHash($order, $paymentHash)
    {
        $this->gibEinstellungen($order);
        $this->user_variable_0    = $paymentHash;
        $this->user_variable_1    = $this->duringCheckout ? 'sh' : 'ph';
        $this->user_variable_5    = 'JTL-Shop-3';
        $this->strAmount          = round($order->fGesamtsummeKundenwaehrung, 2);
        $this->strSenderCountryID = ($order->kLieferadresse > 0)
            ? $order->Lieferadresse->cLand
            : $order->oRechnungsadresse->cLand;
        $this->name               = $order->oRechnungsadresse->cVorname . ' ' . $order->oRechnungsadresse->cNachname;
        if (strlen($order->oRechnungsadresse->cFirma) > 2) {
            $this->name = $order->oRechnungsadresse->cFirma;
        }
        // ISO pruefen
        preg_match('/[a-zA-Z]{2}/', $this->strSenderCountryID, $cTreffer1_arr);
        if (strlen($cTreffer1_arr[0]) !== strlen($this->strSenderCountryID)) {
            $cISO = Sprache::getIsoCodeByCountryName($this->strSenderCountryID);
            if (strlen($cISO) > 0 && $cISO !== 'noISO') {
                $this->strSenderCountryID = $cISO;
            }
        }
        //Sonderzeichen entfernen
        $this->removeEntities();
        //Sicherheits-Hash erstellen
        $data = [
            $this->sofortueberweisung_id,           // user_id
            $this->sofortueberweisung_project_id,   // project_id
            $this->name,                            // sender_holder
            '',                                     // sender_account_number
            '',                                     // sender_bank_code
            $this->strSenderCountryID,              // sender_country_id
            $this->strAmount,                       // amount
            $order->Waehrung->cISO,                 // currency_id
            $this->reason_1,                        // reason_1
            $this->reason_2,                        // reason_2
            $this->user_variable_0,                 // user_variable_0
            $this->user_variable_1,                 // user_variable_1
            $this->user_variable_2,                 // user_variable_2
            $this->user_variable_3,                 // user_variable_3
            $this->user_variable_4,                 // user_variable_4
            $this->user_variable_5,                 // user_variable_5
            $this->getProjectPassword(),            // Project Password
        ];

        $data_implode = implode('|', $data);
        $this->hash   = sha1($data_implode);
    }

    /**
     * @param Bestellung $order
     * @param string     $paymentHash
     * @param array      $args
     */
    public function handleNotification($order, $paymentHash, $args)
    {
        $this->doLog(print_r($args, true));
        if ($this->verifyNotification($order, $paymentHash, $args)) {
            $transaction = Shop::Container()->getDB()->query(
                'SELECT tzahlungseingang.cZahlungsanbieter, tzahlungseingang.fBetrag, tzahlungsession.nBezahlt
                    FROM tzahlungsession
                    INNER JOIN tzahlungseingang 
                        ON tzahlungseingang.kBestellung = ' . (int)$order->kBestellung . "
                    WHERE tzahlungsession.cZahlungsID = '" . substr($paymentHash, 1) . "'",
                ReturnType::SINGLE_OBJECT
            );

            if (!isset($transaction)
                || (int)$transaction->nBezahlt === 0
                || $transaction->cZahlungsanbieter != $order->cZahlungsartName
                || round($transaction->fBetrag * 100) !== round($args['amount'] * 100)
            ) {
                $this->setOrderStatusToPaid($order);
                $incomingPayment          = new stdClass();
                $incomingPayment->fBetrag = $args['amount'];
                $incomingPayment->cISO    = $args['currency_id'];

                $this->addIncomingPayment($order, $incomingPayment);
                $this->sendConfirmationMail($order);
                $this->updateNotificationID($order->kBestellung, $args['transaction']);
            }
        }

        $url    = $this->getReturnURL($order);
        $header = 'Location: ' . $url;
        header($header);
    }

    /**
     * @param Bestellung $order
     * @param string     $paymentHash
     * @param array      $args
     * @return bool
     */
    public function verifyNotification($order, $paymentHash, $args)
    {
        extract($args);
        $data                  = [
            'transaction'               => $args['transaction'] ?? null,
            'user_id'                   => $args['user_id'] ?? null,
            'project_id'                => $args['project_id'] ?? null,
            'sender_holder'             => $args['sender_holder'] ?? null,
            'sender_account_number'     => $args['sender_account_number'] ?? null,
            'sender_bank_code'          => $args['sender_bank_code'] ?? null,
            'sender_bank_name'          => $args['sender_bank_name'] ?? null,
            'sender_bank_bic'           => $args['sender_bank_bic'] ?? null,
            'sender_iban'               => $args['sender_iban'] ?? null,
            'sender_country_id'         => $args['sender_country_id'] ?? null,
            'recipient_holder'          => $args['recipient_holder'] ?? null,
            'recipient_account_number'  => $args['recipient_account_number'] ?? null,
            'recipient_bank_code'       => $args['recipient_bank_code'] ?? null,
            'recipient_bank_name'       => $args['recipient_bank_name'] ?? null,
            'recipient_bank_bic'        => $args['recipient_bank_bic'] ?? null,
            'recipient_iban'            => $args['recipient_iban'] ?? null,
            'recipient_country_id'      => $args['recipient_country_id'] ?? null,
            'international_transaction' => $args['international_transaction'] ?? null,
            'amount'                    => $args['amount'] ?? null,
            'currency_id'               => $args['currency_id'] ?? null,
            'reason_1'                  => $args['reason_1'] ?? null,
            'reason_2'                  => $args['reason_2'] ?? null,
            'security_criteria'         => $args['security_criteria'] ?? null,
            'user_variable_0'           => $args['user_variable_0'] ?? null,
            'user_variable_1'           => $args['user_variable_1'] ?? null,
            'user_variable_2'           => $args['user_variable_2'] ?? null,
            'user_variable_3'           => $args['user_variable_3'] ?? null,
            'user_variable_4'           => $args['user_variable_4'] ?? null,
            'user_variable_5'           => $args['user_variable_5'] ?? null,
            'created'                   => $args['created'] ?? null,
        ];
        $hash                  = $args['hash'] ?? null;
        $cNotificationPassword = $this->getNotificationPassword();
        if (strlen($cNotificationPassword) > 0) {
            $data['notification_password'] = $cNotificationPassword;
        }
        $data_implode = implode('|', $data);
        $hashTMP      = sha1($data_implode);

        return ($hashTMP === $hash);
    }

    /**
     * @see includes/modules/PaymentMethod#finalizeOrder($order, $hash, $args)
     * @param Bestellung $order
     * @param string     $hash
     * @param array      $args
     * @return bool|true
     */
    public function finalizeOrder($order, $hash, $args)
    {
        return $this->verifyNotification($order, $hash, $args);
    }

    /**
     * @param $order
     */
    public function gibEinstellungen($order)
    {
        $this->sofortueberweisung_id         = $this->paymentConfig['zahlungsart_sofortueberweisung_id'];
        $this->sofortueberweisung_project_id = $this->paymentConfig['zahlungsart_sofortueberweisung_project_id'];

        if ($this->paymentConfig['zahlungsart_sofortueberweisung_reason_1'] == 1) {
            $this->reason_1 = '';
        } elseif ($this->paymentConfig['zahlungsart_sofortueberweisung_reason_1'] == 2) {
            $this->reason_1 = $order->cBestellNr;
        } elseif ($this->paymentConfig['zahlungsart_sofortueberweisung_reason_1'] == 3) {
            $this->reason_1 = $order->cBestellNr . ' ' . $order->oRechnungsadresse->cFirma;
        } elseif ($this->paymentConfig['zahlungsart_sofortueberweisung_reason_1'] == 4) {
            $this->reason_1 = $order->cBestellNr . ' ' . $this->getShopTitle();
        } elseif ($this->paymentConfig['zahlungsart_sofortueberweisung_reason_1'] == 5) {
            $this->reason_1 = $order->cBestellNr . ' ' . $order->oRechnungsadresse->cNachname;
        } elseif ($this->paymentConfig['zahlungsart_sofortueberweisung_reason_1'] == 6) {
            $this->reason_1 = $order->oRechnungsadresse->cVorname . ' ' . $order->oRechnungsadresse->cNachname;
        } elseif ($this->paymentConfig['zahlungsart_sofortueberweisung_reason_1'] == 7) {
            $this->reason_1 = $order->oRechnungsadresse->cFirma;
        } elseif ($this->paymentConfig['zahlungsart_sofortueberweisung_reason_1'] == 8) {
            $this->reason_1 = $this->getShopTitle();
        }
        $this->reason_1 = str_replace('"', '&quot;', $this->reason_1);

        if ($this->paymentConfig['zahlungsart_sofortueberweisung_reason_2'] == 1) {
            $this->reason_2 = '';
        } elseif ($this->paymentConfig['zahlungsart_sofortueberweisung_reason_2'] == 2) {
            $this->reason_2 = $order->cBestellNr;
        } elseif ($this->paymentConfig['zahlungsart_sofortueberweisung_reason_2'] == 3) {
            $this->reason_2 = $order->cBestellNr . ' ' . $order->oRechnungsadresse->cFirma;
        } elseif ($this->paymentConfig['zahlungsart_sofortueberweisung_reason_2'] == 4) {
            $this->reason_2 = $order->cBestellNr . ' ' . $this->getShopTitle();
        } elseif ($this->paymentConfig['zahlungsart_sofortueberweisung_reason_2'] == 5) {
            $this->reason_2 = $order->cBestellNr . ' ' . $order->oRechnungsadresse->cNachname;
        } elseif ($this->paymentConfig['zahlungsart_sofortueberweisung_reason_2'] == 6) {
            $this->reason_2 = $order->oRechnungsadresse->cVorname . ' ' . $order->oRechnungsadresse->cNachname;
        } elseif ($this->paymentConfig['zahlungsart_sofortueberweisung_reason_2'] == 7) {
            $this->reason_2 = $order->oRechnungsadresse->cFirma;
        } elseif ($this->paymentConfig['zahlungsart_sofortueberweisung_reason_2'] == 8) {
            $this->reason_2 = $this->getShopTitle();
        }
        $this->reason_2 = str_replace('"', '&quot;', $this->reason_2);

        if ($this->paymentConfig['zahlungsart_sofortueberweisung_user_variable_2'] == 1) {
            $this->user_variable_2 = '';
        } elseif ($this->paymentConfig['zahlungsart_sofortueberweisung_user_variable_2'] == 2) {
            $this->user_variable_2 = $order->oRechnungsadresse->cFirma;
        } elseif ($this->paymentConfig['zahlungsart_sofortueberweisung_user_variable_2'] == 3) {
            $this->user_variable_2 = $order->oRechnungsadresse->cVorname . ' ' . $order->oRechnungsadresse->cNachname;
        } elseif ($this->paymentConfig['zahlungsart_sofortueberweisung_user_variable_2'] == 4) {
            $this->user_variable_2 = $order->oRechnungsadresse->cStrasse . ' ' . $order->oRechnungsadresse->cHausnummer;
        } elseif ($this->paymentConfig['zahlungsart_sofortueberweisung_user_variable_2'] == 5) {
            $this->user_variable_2 = $order->oRechnungsadresse->cPLZ . ' ' . $order->oRechnungsadresse->cOrt;
        } elseif ($this->paymentConfig['zahlungsart_sofortueberweisung_user_variable_2'] == 6) {
            $this->user_variable_2 = $order->oRechnungsadresse->cLand;
        } elseif ($this->paymentConfig['zahlungsart_sofortueberweisung_user_variable_2'] == 7) {
            $this->user_variable_2 = $order->oRechnungsadresse->cMail;
        } elseif ($this->paymentConfig['zahlungsart_sofortueberweisung_user_variable_2'] == 8) {
            $this->user_variable_2 = $order->oRechnungsadresse->cTel;
        } elseif ($this->paymentConfig['zahlungsart_sofortueberweisung_user_variable_2'] == 9) {
            $this->user_variable_2 = $order->oRechnungsadresse->cFax;
        }
        $this->user_variable_2 = str_replace('"', '&quot;', $this->user_variable_2);

        if ($this->paymentConfig['zahlungsart_sofortueberweisung_user_variable_3'] == 1) {
            $this->user_variable_3 = '';
        } elseif ($this->paymentConfig['zahlungsart_sofortueberweisung_user_variable_3'] == 2) {
            $this->user_variable_3 = $order->oRechnungsadresse->cFirma;
        } elseif ($this->paymentConfig['zahlungsart_sofortueberweisung_user_variable_3'] == 3) {
            $this->user_variable_3 = $order->oRechnungsadresse->cVorname . ' ' . $order->oRechnungsadresse->cNachname;
        } elseif ($this->paymentConfig['zahlungsart_sofortueberweisung_user_variable_3'] == 4) {
            $this->user_variable_3 = $order->oRechnungsadresse->cStrasse . ' ' . $order->oRechnungsadresse->cHausnummer;
        } elseif ($this->paymentConfig['zahlungsart_sofortueberweisung_user_variable_3'] == 5) {
            $this->user_variable_3 = $order->oRechnungsadresse->cPLZ . ' ' . $order->oRechnungsadresse->cOrt;
        } elseif ($this->paymentConfig['zahlungsart_sofortueberweisung_user_variable_3'] == 6) {
            $this->user_variable_3 = $order->oRechnungsadresse->cLand;
        } elseif ($this->paymentConfig['zahlungsart_sofortueberweisung_user_variable_3'] == 7) {
            $this->user_variable_3 = $order->oRechnungsadresse->cMail;
        } elseif ($this->paymentConfig['zahlungsart_sofortueberweisung_user_variable_3'] == 8) {
            $this->user_variable_3 = $order->oRechnungsadresse->cTel;
        } elseif ($this->paymentConfig['zahlungsart_sofortueberweisung_user_variable_3'] == 9) {
            $this->user_variable_3 = $order->oRechnungsadresse->cFax;
        }
        $this->user_variable_3 = str_replace('"', '&quot;', $this->user_variable_3);

        if ($this->paymentConfig['zahlungsart_sofortueberweisung_user_variable_4'] == 1) {
            $this->user_variable_4 = '';
        } elseif ($this->paymentConfig['zahlungsart_sofortueberweisung_user_variable_4'] == 2) {
            $this->user_variable_4 = $order->oRechnungsadresse->cFirma;
        } elseif ($this->paymentConfig['zahlungsart_sofortueberweisung_user_variable_4'] == 3) {
            $this->user_variable_4 = $order->oRechnungsadresse->cVorname . ' ' . $order->oRechnungsadresse->cNachname;
        } elseif ($this->paymentConfig['zahlungsart_sofortueberweisung_user_variable_4'] == 4) {
            $this->user_variable_4 = $order->oRechnungsadresse->cStrasse . ' ' . $order->oRechnungsadresse->cHausnummer;
        } elseif ($this->paymentConfig['zahlungsart_sofortueberweisung_user_variable_4'] == 5) {
            $this->user_variable_4 = $order->oRechnungsadresse->cPLZ . ' ' . $order->oRechnungsadresse->cOrt;
        } elseif ($this->paymentConfig['zahlungsart_sofortueberweisung_user_variable_4'] == 6) {
            $this->user_variable_4 = $order->oRechnungsadresse->cLand;
        } elseif ($this->paymentConfig['zahlungsart_sofortueberweisung_user_variable_4'] == 7) {
            $this->user_variable_4 = $order->oRechnungsadresse->cMail;
        } elseif ($this->paymentConfig['zahlungsart_sofortueberweisung_user_variable_4'] == 8) {
            $this->user_variable_4 = $order->oRechnungsadresse->cTel;
        } elseif ($this->paymentConfig['zahlungsart_sofortueberweisung_user_variable_4'] == 9) {
            $this->user_variable_4 = $order->oRechnungsadresse->cFax;
        }
        $this->user_variable_4 = str_replace('"', '&quot;', $this->user_variable_4);
    }

    /**
     *
     */
    public function removeEntities()
    {
        $this->reason_1        = Text::unhtmlentities($this->reason_1);
        $this->reason_2        = Text::unhtmlentities($this->reason_2);
        $this->user_variable_2 = Text::unhtmlentities($this->user_variable_2);
        $this->user_variable_3 = Text::unhtmlentities($this->user_variable_3);
        $this->user_variable_4 = Text::unhtmlentities($this->user_variable_4);
        $this->name            = Text::unhtmlentities($this->name);
    }

    /**
     * @param array $args_arr
     * @return bool
     */
    public function isValidIntern($args_arr = [])
    {
        if (strlen($this->paymentConfig['zahlungsart_sofortueberweisung_id']) === 0) {
            ZahlungsLog::add($this->moduleID, 'Pflichtparameter "User-ID" ist nicht gesetzt!', null, LOGLEVEL_ERROR);

            return false;
        }
        if (strlen($this->paymentConfig['zahlungsart_sofortueberweisung_project_id']) === 0) {
            ZahlungsLog::add($this->moduleID, 'Pflichtparameter "Projekt-ID" ist nicht gesetzt!', null, LOGLEVEL_ERROR);

            return false;
        }
        if (strlen($this->getNotificationPassword()) === 0) {
            ZahlungsLog::add(
                $this->moduleID,
                'Pflichtparameter "Projekt Passwort" ist nicht gesetzt!',
                null,
                LOGLEVEL_ERROR
            );

            return false;
        }
        if (strlen($this->getProjectPassword()) == 0) {
            ZahlungsLog::add(
                $this->moduleID,
                'Pflichtparameter "Benachrichtigungspasswort" ist nicht gesetzt!',
                null,
                LOGLEVEL_ERROR
            );

            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function canPayAgain()
    {
        return true;
    }
}
