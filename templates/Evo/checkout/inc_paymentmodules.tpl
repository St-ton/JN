{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if isset($abschlussseite) && $abschlussseite == 1}
    {include file='checkout/inc_trustedshops_excellence.tpl'}
{else}
    {assign var=cModulId value=$Bestellung->Zahlungsart->cModulId}
    {if (empty($oPlugin->oPluginZahlungsmethodeAssoc_arr[$cModulId]->cModulId) || $Bestellung->Zahlungsart->cModulId != $oPlugin->oPluginZahlungsmethodeAssoc_arr[$cModulId]->cModulId) && $Bestellung->Zahlungsart->cModulId|substr:0:10 !== 'za_billpay'
    && $Bestellung->Zahlungsart->cModulId !== 'za_kreditkarte_jtl' && $Bestellung->Zahlungsart->cModulId !== 'za_lastschrift_jtl'}
        {if isset($smarty.session.Zahlungsart->nWaehrendBestellung) && $smarty.session.Zahlungsart->nWaehrendBestellung == 1}
            <div class="alert alert-info">{lang key='orderConfirmationPre' section='checkout'}</div>
        {else}
            <div class="alert alert-info">{lang key='orderConfirmationPost' section='checkout'}</div>
        {/if}
    {/if}

    {if (empty($smarty.session.Zahlungsart->nWaehrendBestellung) || $smarty.session.Zahlungsart->nWaehrendBestellung != 1) && $Bestellung->Zahlungsart->cModulId|substr:0:10 !== 'za_billpay' && $Bestellung->Zahlungsart->cModulId !== 'za_kreditkarte_jtl' && $Bestellung->Zahlungsart->cModulId !== 'za_lastschrift_jtl'}
        <div class="pament-method-during-order">
            <p>{lang key='yourOrderId' section='checkout'}: <strong>{$Bestellung->cBestellNr}</strong></p>
            <p>{lang key='yourChosenPaymentOption' section='checkout'}: <strong>{$Bestellung->cZahlungsartName}</strong></p>
        </div>
    {/if}
    <div class="payment-method-inner">
        {if $Bestellung->Zahlungsart->cModulId === 'za_rechnung_jtl'}
            {lang key='invoiceDesc' section='checkout'}
        {elseif $Bestellung->Zahlungsart->cModulId === 'za_lastschrift_jtl'}
            {lang key='banktransferDesc' section='checkout'}
        {elseif $Bestellung->Zahlungsart->cModulId === 'za_nachnahme_jtl'}
            {lang key='banktransferDesc' section='checkout'}
        {elseif $Bestellung->Zahlungsart->cModulId === 'za_barzahlung_jtl'}
            {lang key='cashOnPickupDesc' section='checkout'}
        {elseif $Bestellung->Zahlungsart->cModulId === 'za_paypal_jtl'}
            {include file='checkout/modules/paypal/bestellabschluss.tpl'}
        {elseif $Bestellung->Zahlungsart->cModulId === 'za_kreditkarte_jtl'}
            {include file='account/retrospective_payment.tpl'}
        {elseif $Bestellung->Zahlungsart->cModulId === 'za_billpay_jtl'}
            {include file='checkout/modules/billpay/bestellabschluss.tpl'}
        {elseif $Bestellung->Zahlungsart->cModulId === 'za_billpay_invoice_jtl'}
            {include file='checkout/modules/billpay/bestellabschluss.tpl'}
        {elseif $Bestellung->Zahlungsart->cModulId === 'za_billpay_direct_debit_jtl'}
            {include file='checkout/modules/billpay/bestellabschluss.tpl'}
        {elseif $Bestellung->Zahlungsart->cModulId === 'za_billpay_rate_payment_jtl'}
            {include file='checkout/modules/billpay/bestellabschluss.tpl'}
        {elseif $Bestellung->Zahlungsart->cModulId === 'za_billpay_paylater_jtl'}
            {include file='checkout/modules/billpay/bestellabschluss.tpl'}
        {elseif $Bestellung->Zahlungsart->cModulId === 'za_sofortueberweisung_jtl'}
            {lang key='sofortueberweisungDesc' section='checkout'}
            <br />
            {$sofortueberweisungform}
            <br />
        {elseif !empty($oPlugin->oPluginZahlungsmethodeAssoc_arr[$cModulId]->cModulId) && $Bestellung->Zahlungsart->cModulId == $oPlugin->oPluginZahlungsmethodeAssoc_arr[$cModulId]->cModulId}
            {include file=$oPlugin->oPluginZahlungsmethodeAssoc_arr[$cModulId]->cTemplateFileURL}
        {/if}
        <br />
        {include file='checkout/inc_trustedshops_excellence.tpl'}
    </div>
{/if}
{include file='checkout/inc_conversion_tracking.tpl'}
