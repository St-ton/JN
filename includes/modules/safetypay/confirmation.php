<?php

require_once PFAD_ROOT . PFAD_INCLUDES . 'sprachfunktionen.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';

/**
 * @param object $bestellung
 * @return string
 */
function show_confirmation($bestellung)
{
    $einstellungApiKey       = Shop::Container()->getDB()->query(
        "SELECT cWert FROM teinstellungen WHERE cName = 'zahlungsart_safetypay_apikey'",
        \DB\ReturnType::SINGLE_OBJECT
    );
    $einstellungSignatureKey = Shop::Container()->getDB()->query(
        "SELECT cWert FROM teinstellungen WHERE cName = 'zahlungsart_safetypay_signaturekey'",
        \DB\ReturnType::SINGLE_OBJECT
    );
    $TransactionData         = null;

    define('SAFETYPAY_APIKEY', $einstellungApiKey->cWert);
    define('SAFETYPAY_SIGNTATURE_KEY', $einstellungSignatureKey->cWert);

    require_once __DIR__ . '/class/safetypayProxyAPI.php';
    require_once __DIR__ . '/include/safetypay_functions.php';

    $pLanguageShop          = $_REQUEST['languageShop'] ?? $_POST['languageShop'];
    $pCurrency              = $_REQUEST['Currency'] ?? ($_POST['Currency'] ?? '');
    $pToCurrency            = $_REQUEST['slcToCurrency'] ?? ($_POST['slcToCurrency'] ?? '');
    $pBankID                = $_REQUEST['slcBankID'] ?? $_POST['slcBankID'];
    $txtAmount              = $_REQUEST['txtAmount'] ?? ($_POST['txtAmount'] ?? '');
    $pTrackingCode          = $_REQUEST['TrackingCode'] ?? $_POST['TrackingCode'];
    $pCalculationQuoteRefNo = $_REQUEST['CalcQuoteReferenceNo'] ?? $_POST['CalcQuoteReferenceNo'];
    $pMerchantReferenceNo   = $bestellung->cBestellNr;
    $pURLPaymentSuccesfully = $_REQUEST['URLPaymentSuccesfully'] ?? $_POST['URLPaymentSuccesfully'];
    $pURLPaymentFailed      = $_REQUEST['URLPaymentFailed'] ?? $_POST['URLPaymentFailed'];
    $pSubmit                = $_REQUEST['Submit'] ?? $_POST['Submit'];

    // Instance of SafetyPay Proxy Class
    $proxySTP = new SafetyPayProxy();
    if (!empty($einstellungApiKey)
        && !empty($einstellungApiKey->cWert)
        && !empty($einstellungSignatureKey)
        && !empty($einstellungSignatureKey->cWert)
    ) {
        $proxySTP->LetKeys($einstellungApiKey->cWert, $einstellungSignatureKey->cWert);
    }

    // To Show in the form
    $pApiKey       = $proxySTP->ApiKey;
    $pSignatureKey = $proxySTP->SignatureKey;

    // Create Transaction
    if ($_SESSION['Zahlungsart']->cModulId === 'za_safetypay') {
        $TransactionData = stp_CreateTransaction(
            $proxySTP,
            $pCurrency,
            $txtAmount,
            $pToCurrency,
            $pMerchantReferenceNo,
            $pLanguageShop,
            $pCalculationQuoteRefNo,
            $pTrackingCode,
            $pBankID,
            $pURLPaymentSuccesfully,
            $pURLPaymentFailed
        );
    }

    if (isset($TransactionData['SelectedBank']['AccessType']) && $TransactionData['SelectedBank']['AccessType'] == 2) {
        header('Location: ' . $TransactionData['SelectedBank']['NavigationURL']);
    } else {
        return '<center>
        <div style="float:center; border: #ffffff 1px solid; width: 400px; margin:5px; margin-top:0px;">
        <center>
            <table width="100%" cellpadding="3" cellspacing="3">
                <tr>
                    <td colspan="2" nowrap>
                        <div style="margin:0px; padding:10px; background-color:#D9E5F2; font-family:Arial, Helvetica, sans-serif; font-size:12px;">
                            <div style="color:#000000; background-color:#FFFFFF; font-size:18px; padding:5px; padding-top:10px; text-align:center"><img src="includes/modules/safetypay/gfx/safetypay_logo.png" alt="SafetyPay Inc." border="0" /></div>
                            <div style="padding-left:10px; padding-right:10px; background-color:#FFFFFF;text-align: center">
                    Um die Bezahlung dieser Transaktion abzuschlie&szlig;en,
                    <br>verwenden Sie bitte diese Daten in Ihrem Onlinebanking:
                    </div>
                        <div style="background-color:#FFFFFF; text-align:center; padding-top:10px; font-family:Arial, Helvetica, sans-serif; font-size:13px;"><strong>Transaktions-ID: ' . $TransactionData["TransactionID"] . '</strong></div>
                        <div style="background-color:#FFFFFF; text-align:center; padding-bottom:10px; font-family:Arial, Helvetica, sans-serif; font-size:13px;"><strong>Kaufbetrag:&nbsp;' . $TransactionData["ToCurrency"]["Code"] . ' ' .
            $TransactionData["ToAmount"] . '</strong></div>
                        <div style="background-color:#FFFFFF; text-align:center; padding-bottom:10px; padding-top:10px; font-family:Arial, Helvetica, sans-serif; font-size:16px; color:#0057A7;"><a href="' . $TransactionData["SelectedBank"]["NavigationURL"] .
            '" style="color:#0057A7;">Klicken Sie bitte hier, um die Bezahlung in Ihrer <br/>gew&auml;hlten Bank abzuschlie&szlig;en!</a></div>
                        <div style="padding-left:10px; padding-right:10px; padding-bottom:10px; background-color:#FFFFFF;text-align:center; font-family:Arial, Helvetica, sans-serif; font-size:12px;color:#000000;"><strong>WICHTIG:</strong> Die Transaktion l&auml;uft in 2 Stunden aus!<br /><br />
                        <strong>Herzlichen Dank f&uuml;r das Bezahlen mit SafetyPay!</strong>
                        </div>
                    </div>
                    </td>
                </tr>
            </table>
            </center>
        </div>
        </center>';
    }
}
