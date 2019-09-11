{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if !isset($abschlussseite) || $abschlussseite !== 1}
    {if $oPlugin !== null && $oPlugin instanceof JTL\Plugin\PluginInterface}
        {$method = $oPlugin->getPaymentMethods()->getMethodByID($Bestellung->Zahlungsart->cModulId)}
    {else}
        {$method = null}
    {/if}
    {if ($method === null || $Bestellung->Zahlungsart->cModulId !== $method->getModuleID())
    && $Bestellung->Zahlungsart->cModulId !== 'za_kreditkarte_jtl' && $Bestellung->Zahlungsart->cModulId !== 'za_lastschrift_jtl'}
        {if isset($smarty.session.Zahlungsart->nWaehrendBestellung) && $smarty.session.Zahlungsart->nWaehrendBestellung == 1}
            <div class="alert alert-info">{lang key='orderConfirmationPre' section='checkout'}</div>
        {else}
            <div class="alert alert-info">{lang key='orderConfirmationPost' section='checkout'}</div>
        {/if}
    {/if}

    {if (empty($smarty.session.Zahlungsart->nWaehrendBestellung) || $smarty.session.Zahlungsart->nWaehrendBestellung != 1) && $Bestellung->Zahlungsart->cModulId !== 'za_kreditkarte_jtl' && $Bestellung->Zahlungsart->cModulId !== 'za_lastschrift_jtl'}
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
        {elseif $method !== null && $Bestellung->Zahlungsart->cModulId === $method->getModuleID()}
            {include file=$method->getTemplateFilePath()}
        {/if}
        <br />
    </div>
{/if}
