<?php

include_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'ServerPaymentMethod.class.php';

// Constants
define('HP_TRANSACTION_MODE', 'LIVE'); // INTEGRATOR_TEST | CONNECTOR_TEST | LIVE

/**
 * Class PaymentPartner
 */
class PaymentPartner extends ServerPaymentMethod
{
    /**
     * @param int $nAgainCheckout
     * @return $this
     */
    public function init($nAgainCheckout = 0)
    {
        parent::init($nAgainCheckout);
        $this->name    = 'PaymentPartner';
        $this->caption = 'PaymentPartner';
        $this->path    = '/frontend/payment.prc';

        // Live
        $this->hostname = 'ssl://ctpe.net';
        $this->host     = 'ctpe.net';

        // Test
        //$this->hostname = 'ssl://test.ctpe.net';
        //$this->host = 'test.ctpe.net';

        return $this;
    }

    /**
     * @return null
     */
    public function getLogin()
    {
        global $Einstellungen;

        return (isset($Einstellungen['zahlungsarten']['zahlungsart_paymentpartner_login'])) ? $Einstellungen['zahlungsarten']['zahlungsart_paymentpartner_login'] : null;
    }

    /**
     * @return null
     */
    public function getPassword()
    {
        global $Einstellungen;

        return (isset($Einstellungen['zahlungsarten']['zahlungsart_paymentpartner_password'])) ? $Einstellungen['zahlungsarten']['zahlungsart_paymentpartner_password'] : null;
    }

    /**
     * @return null
     */
    public function getChannel()
    {
        global $Einstellungen;

        return (isset($Einstellungen['zahlungsarten']['zahlungsart_paymentpartner_channel'])) ? $Einstellungen['zahlungsarten']['zahlungsart_paymentpartner_channel'] : null;
    }

    /**
     * @return null
     */
    public function getSender()
    {
        global $Einstellungen;

        return (isset($Einstellungen['zahlungsarten']['zahlungsart_paymentpartner_sender'])) ? $Einstellungen['zahlungsarten']['zahlungsart_paymentpartner_sender'] : null;
    }

    /**
     * @param Bestellung $order
     */
    public function preparePaymentProcess($order)
    {
        $smarty      = Shop::Smarty();
        $amount      = number_format($order->fGesamtsummeKundenwaehrung, 2, '.', '');
        $customer    = $_SESSION['Kunde'];
        $firstItem   = new Artikel($order->Positionen[0]->kArtikel);
        $paymentHash = $this->generateHash($order);

        $fields = array(
            'REQUEST.VERSION'              => '1.0',
            'SECURITY.SENDER'              => $this->getSender(),
            'USER.LOGIN'                   => $this->getLogin(),
            'USER.PWD'                     => $this->getPassword(),
            'TRANSACTION.MODE'             => HP_TRANSACTION_MODE,
            'TRANSACTION.CHANNEL'          => $this->getChannel(),
            'IDENTIFICATION.TRANSACTIONID' => $paymentHash,
            'TRANSACTION.RESPONSE'         => 'SYNC',
            'PRESENTATION.AMOUNT'          => $amount,
            'PRESENTATION.USAGE'           => $this->getShopTitle() . ' ' . $order->cBestellNr,
            'PRESENTATION.CURRENCY'        => $order->Waehrung->cISO,
            'PAYMENT.CODE'                 => 'CC.DB',
            'CONTACT.EMAIL'                => $customer->cMail,
            'ADDRESS.STREET'               => $customer->cStrasse . ' ' . $customer->cHausnummer . ' ' . $customer->cAdressZusatz,
            'ADDRESS.ZIP'                  => $customer->cPLZ,
            'ADDRESS.CITY'                 => $customer->cOrt,
            'ADDRESS.COUNTRY'              => $customer->cLand,
            'NAME.SALUTATION'              => $customer->cAnrede,
            'NAME.TITLE'                   => $customer->cTitel,
            'NAME.GIVEN'                   => $customer->cVorname,
            'NAME.FAMILY'                  => $customer->cNachname,
            'FRONTEND.ENABLED'             => 'true',
            'FRONTEND.POPUP'               => 'true',
            'FRONTEND.MODE'                => 'DEFAULT',
            'FRONTEND.LANGUAGE'            => $customer->cLand,
            'FRONTEND.RESPONSE_URL'        => $this->getNotificationURL($paymentHash));
        $request = $this->postRequest($fields);
        // HTTP Error
        if ($request['status'] === 'error') {
            $smarty->assign('status', 'error');
            $smarty->assign('error', Shop::Lang()->get('errorText', 'paymentMethods'));
            // Error Mail
            $error = Shop::Lang()->get('paymentPartnerHttpError', 'paymentMethods');
            $body  = sprintf(Shop::Lang()->get('errorMailBody', 'paymentMethods'), $this->getShopTitle(), $order->cBestellNr, $this->caption, $error);
            $this->sendErrorMail($body);

            return;
        }
        // Parse Reponse
        $response = $this->parse($request['body']);

        // Error: Not validated
        if (($response['POST.VALIDATION'] !== 'ACK') || (strstr($response['FRONTEND.REDIRECT_URL'], 'http') === false)) {
            $smarty->assign('status', 'error');
            $smarty->assign('error', Shop::Lang()->get('errorText', 'paymentMethods'));
            // Error Mail
            $error = sprintf(Shop::Lang()->get('paymentPartnerError', 'paymentMethods'), $request['body']);
            $body  = sprintf(Shop::Lang()->get('errorMailBody', 'paymentMethods'), $this->getShopTitle(), $order->cBestellNr, $this->caption, $error);
            $this->sendErrorMail($body);

            return;
        }

        $smarty->assign('status', 'success');
        $smarty->assign('url', $response["FRONTEND.REDIRECT_URL"]);
    }

    /**
     * @param Bestellung $order
     * @param string     $paymentHash
     * @param array      $args
     */
    public function handleNotification($order, $paymentHash, $args)
    {
        if (strstr($args['PROCESSING_RESULT'], 'ACK')) {
            if ($this->verifyNotification($order, $paymentHash, $args)) {
                $incomingPayment          = new stdClass();
                $incomingPayment->fBetrag = $order->fGesamtsummeKundenwaehrung;
                $incomingPayment->cISO    = $order->Waehrung->cISO;
                $this->addIncomingPayment($order, $incomingPayment);
                $this->setOrderStatusToPaid($order);
                $this->sendConfirmationMail($order);
                $_upd            = new stdClass();
                $_upd->cNofifyID = Shop::DB()->escape($args['IDENTIFICATION_UNIQUEID']);
                $_upd->dNotify   = 'now()';
                Shop::DB()->update('tzahlungsession', 'cZahlungsID', substr($paymentHash, 1), $_upd);
            }
        }
        // PaymentPartner redirects to:
        echo $this->getReturnURL($order);
    }

    /**
     * @return boolean
     * @param Bestellung $order
     * @param array      $args
     */
    public function verifyNotification($order, $paymentHash, $args)
    {
        extract($args);

        if ($IDENTIFICATION_TRANSACTIONID != $paymentHash) {
            return false;
        }

        if ($CLEARING_AMOUNT != number_format($order->fGesamtsummeKundenwaehrung, 2, '.', '')) {
            return false;
        }

        if ($CLEARING_CURRENCY != $order->Waehrung->cISO) {
            return false;
        }

        return true;
    }

    /**
     * @param Bestellung $order
     * @param string     $hash
     * @param array      $args
     * @return bool
     */
    public function finalizeOrder($order, $hash, $args)
    {
        extract($args);

        return ($PROCESSING_RESULT === 'ACK');
    }

    /**
     * Taken from PaymentPartner
     *
     * @return array
     * @param string $resultURL
     */
    public function parse($resultURL)
    {
        $returnvalue = array();
        $r_arr       = explode('&', $resultURL);

        foreach ($r_arr as $buf) {
            $temp = urldecode($buf);
            $temp = explode('=', $temp, 2);

            $postatt = $temp[0];
            $postvar = $temp[1];

            $returnvalue[$postatt] = $postvar;
        }

        return $returnvalue;
    }

    /**
     * @param array $args_arr
     * @return bool
     */
    public function isValidIntern($args_arr = array())
    {
        if (strlen($this->getSender()) == 0) {
            ZahlungsLog::add($this->moduleID, "Pflichtparameter 'Sender' ist nicht gesetzt!", null, LOGLEVEL_ERROR);

            return false;
        }

        if (strlen($this->getLogin()) == 0) {
            ZahlungsLog::add($this->moduleID, "Pflichtparameter 'Login' ist nicht gesetzt!", null, LOGLEVEL_ERROR);

            return false;
        }

        if (strlen($this->getPassword()) == 0) {
            ZahlungsLog::add($this->moduleID, "Pflichtparameter 'Passwort' ist nicht gesetzt!", null, LOGLEVEL_ERROR);

            return false;
        }

        if (strlen($this->getChannel()) == 0) {
            ZahlungsLog::add($this->moduleID, "Pflichtparameter 'Channel' ist nicht gesetzt!", null, LOGLEVEL_ERROR);

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
