<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\FormHelper;
use Helpers\RequestHelper;
use Pagination\Pagination;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('ORDER_BILLPAY_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'billpay_inc.php';
include_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php';

loadConfigLocalizations();

$cFehler = null;
$cStep   = 'uebersicht';

/** @global Smarty\JTLSmarty $smarty */
$smarty->assign('cTab', $cStep);
if (strlen(RequestHelper::verifyGPDataString('tab')) > 0) {
    $smarty->assign('cTab', RequestHelper::verifyGPDataString('tab'));
}
/** @var Billpay $oBillpay */
$oBillpay = PaymentMethod::create('za_billpay_jtl');

if (strlen($oBillpay->getSetting('pid')) > 0
    && strlen($oBillpay->getSetting('mid')) > 0
    && strlen($oBillpay->getSetting('bpsecure')) > 0
) {
    $oItem_arr = [];
    $oConfig   = $oBillpay->getApi('module_config');
    foreach (['AUT' => ['EUR'], 'DEU' => ['EUR'], 'NLD' => ['EUR'], 'CHE' => ['EUR', 'CHF']] as $cLand => $currencies) {
        foreach ($currencies as $cWaehrung) {
            $oItem            = new stdClass;
            $oItem->cLand     = $cLand;
            $oItem->cWaehrung = $cWaehrung;
            $oConfig->set_locale($oItem->cLand, $oItem->cWaehrung, 'de');
            try {
                $oConfig->send();
                if ($oConfig->has_error()) {
                    $oItem->cFehler = $oConfig->get_merchant_error_message();
                } else {
                    $oRechnung          = new stdClass();
                    $oRechnung->bAktiv  = $oConfig->is_invoice_allowed();
                    $oRechnung->cValMax = fmtUnit($oConfig->get_static_limit_invoice());
                    $oRechnung->cValMin = fmtUnit($oConfig->get_invoice_min_value());

                    $oRechnungB2B          = new stdClass();
                    $oRechnungB2B->bAktiv  = $oConfig->is_invoicebusiness_allowed();
                    $oRechnungB2B->cValMax = fmtUnit($oConfig->get_static_limit_invoicebusiness());
                    $oRechnungB2B->cValMin = fmtUnit($oConfig->get_invoicebusiness_min_value());

                    $oLastschrift          = new stdClass();
                    $oLastschrift->bAktiv  = $oConfig->is_direct_debit_allowed();
                    $oLastschrift->cValMax = fmtUnit($oConfig->get_static_limit_direct_debit());
                    $oLastschrift->cValMin = fmtUnit($oConfig->get_direct_debit_min_value());

                    $oRatenzahlung          = new stdClass();
                    $oRatenzahlung->bAktiv  = $oConfig->is_hire_purchase_allowed();
                    $oRatenzahlung->cValMax = fmtUnit($oConfig->get_static_limit_hire_purchase());
                    $oRatenzahlung->cValMin = fmtUnit($oConfig->get_hire_purchase_min_value());

                    $oPaylater          = new stdClass();
                    $oPaylater->bAktiv  = $oConfig->is_paylater_allowed();
                    $oPaylater->cValMax = fmtUnit($oConfig->get_static_limit_hire_purchase());
                    $oPaylater->cValMin = fmtUnit($oConfig->get_paylater_min_value());
                    $oPaylater->bAktiv  = $oPaylater->bAktiv && $oPaylater->cValMax > 0;

                    $oPaylaterB2B          = new stdClass();
                    $oPaylaterB2B->bAktiv  = $oConfig->is_paylaterbusiness_allowed();
                    $oPaylaterB2B->cValMax = fmtUnit($oConfig->get_static_limit_hire_purchase());
                    $oPaylaterB2B->cValMin = fmtUnit($oConfig->get_paylaterbusiness_min_value());
                    $oPaylaterB2B->bAktiv  = $oPaylaterB2B->bAktiv && $oPaylaterB2B->cValMax > 0;

                    $oItem->oRechnung     = $oRechnung;
                    $oItem->oRechnungB2B  = $oRechnungB2B;
                    $oItem->oLastschrift  = $oLastschrift;
                    $oItem->oRatenzahlung = $oRatenzahlung;
                    $oItem->oPaylater     = $oPaylater;
                    $oItem->oPaylaterB2B  = $oPaylaterB2B;
                }
            } catch (Exception $e) {
                $oItem->cFehler = $e->getMessage();
            }
            $oItem_arr[] = $oItem;
        }
    }

    $oLog_arr  = ZahlungsLog::getLog([
        'za_billpay_invoice_jtl',
        'za_billpay_direct_debit_jtl',
        'za_billpay_rate_payment_jtl',
        'za_billpay_paylater_jtl'
    ]);
    $oPagiLog  = (new Pagination('log'))
        ->setItemArray($oLog_arr)
        ->assemble();
    $nLogCount = count($oLog_arr);

    $smarty->assign('oLog_arr', $oPagiLog->getPageItems())
           ->assign('oItem_arr', $oItem_arr)
           ->assign('oPagiLog', $oPagiLog);
} else {
    $cFehler = 'Billpay wurde bisher nicht konfiguriert. ' .
        '<a href="https://jtl-url.de/0kqhs" rel="noopener" target="_blank">' .
        '<i class="fa fa-external-link"></i> Zur Dokumentation</a>';
}

$smarty->assign('cFehlerBillpay', $cFehler);

$Conf = Shop::Container()->getDB()->selectAll(
    'teinstellungenconf',
    ['cModulId', 'cConf'],
    ['za_billpay_jtl', 'Y'],
    '*',
    'nSort'
);

localizeConfigs($Conf);

if (isset($_POST['einstellungen_bearbeiten']) && FormHelper::validateToken()) {
    foreach ($Conf as $i => $oConfig) {
        unset($aktWert);
        $aktWert = new stdClass();
        if (isset($_POST[$Conf[$i]->cWertName])) {
            $aktWert->cWert                 = $_POST[$Conf[$i]->cWertName];
            $aktWert->cName                 = $Conf[$i]->cWertName;
            $aktWert->kEinstellungenSektion = $Conf[$i]->kEinstellungenSektion;
            switch ($Conf[$i]->cInputTyp) {
                case 'kommazahl':
                    $aktWert->cWert = (float)str_replace(',', '.', $aktWert->cWert);
                    break;
                case 'zahl':
                case 'number':
                    $aktWert->cWert = (int)$aktWert->cWert;
                    break;
                case 'text':
                    $aktWert->cWert = substr($aktWert->cWert, 0, 255);
                    break;
                case 'pass':
                    $aktWert->cWert = substr($aktWert->cWert, 0, 255);
                    break;
            }
            Shop::Container()->getDB()->delete(
                'teinstellungen',
                ['kEinstellungenSektion', 'cName'],
                [(int)$Conf[$i]->kEinstellungenSektion, $Conf[$i]->cWertName]
            );
            Shop::Container()->getDB()->insert('teinstellungen', $aktWert);
        }
    }
    Shop::Container()->getDB()->query('UPDATE tglobals SET dLetzteAenderung = NOW()', \DB\ReturnType::DEFAULT);
    Shop::Container()->getCache()->flushTags([CACHING_GROUP_OPTION]);

    $smarty->assign('saved', true);
}

$configCount = count($Conf);
for ($i = 0; $i < $configCount; $i++) {
    if ($Conf[$i]->cInputTyp === 'selectbox') {
        $Conf[$i]->ConfWerte = Shop::Container()->getDB()->selectAll(
            'teinstellungenconfwerte',
            'kEinstellungenConf',
            (int)$Conf[$i]->kEinstellungenConf,
            '*',
            'nSort'
        );

        localizeConfigValues($Conf[$i], $Conf[$i]->ConfWerte);
    }
    $setValue                = Shop::Container()->getDB()->select(
        'teinstellungen',
        'kEinstellungenSektion',
        (int)$Conf[$i]->kEinstellungenSektion,
        'cName',
        $Conf[$i]->cWertName
    );
    $Conf[$i]->gesetzterWert = isset($setValue->cWert) ? StringHandler::htmlentities($setValue->cWert) : null;
}

$smarty->assign('Conf', $Conf)
       ->assign('kEinstellungenSektion', 100)
       ->display('billpay.tpl');
