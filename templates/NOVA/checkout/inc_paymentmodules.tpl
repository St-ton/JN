{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='checkout-inc-paymentmodules'}
    {if !isset($abschlussseite) || $abschlussseite !== 1}
        {assign var=cModulId value=$Bestellung->Zahlungsart->cModulId}
        {if (empty($oPlugin->oPluginZahlungsmethodeAssoc_arr[$cModulId]->cModulId)
                || $Bestellung->Zahlungsart->cModulId != $oPlugin->oPluginZahlungsmethodeAssoc_arr[$cModulId]->cModulId)
            && $Bestellung->Zahlungsart->cModulId !== 'za_kreditkarte_jtl'
            && $Bestellung->Zahlungsart->cModulId !== 'za_lastschrift_jtl'
        }
            {block name='checkout-inc-paymentmodules-alert'}
                {if isset($smarty.session.Zahlungsart->nWaehrendBestellung) && $smarty.session.Zahlungsart->nWaehrendBestellung == 1}
                    {alert variant="info"}{lang key='orderConfirmationPre' section='checkout'}{/alert}
                {else}
                    {alert variant="info"}{lang key='orderConfirmationPost' section='checkout'}{/alert}
                {/if}
            {/block}
        {/if}

        {if (empty($smarty.session.Zahlungsart->nWaehrendBestellung) || $smarty.session.Zahlungsart->nWaehrendBestellung != 1)
            && $Bestellung->Zahlungsart->cModulId !== 'za_kreditkarte_jtl'
            && $Bestellung->Zahlungsart->cModulId !== 'za_lastschrift_jtl'
        }
            {block name='checkout-inc-paymentmodules-during-order'}
                <div class="pament-method-during-order">
                    <p><span class="font-weight-bold">{lang key='yourOrderId' section='checkout'}: </span>>{$Bestellung->cBestellNr}</p>
                    <p><span class="font-weight-bold">{lang key='yourChosenPaymentOption' section='checkout'}: </span>>{$Bestellung->cZahlungsartName}</p>
                </div>
            {/block}
        {/if}
        {block name='checkout-inc-paymentmodules-method-inner'}
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
                {elseif !empty($oPlugin->oPluginZahlungsmethodeAssoc_arr[$cModulId]->cModulId) && $Bestellung->Zahlungsart->cModulId == $oPlugin->oPluginZahlungsmethodeAssoc_arr[$cModulId]->cModulId}
                    {block name='checkout-inc-paymentmodules-include-plugin'}
                        {include file=$oPlugin->oPluginZahlungsmethodeAssoc_arr[$cModulId]->cTemplateFileURL}
                    {/block}
                {/if}
                <br />
            </div>
        {/block}
    {/if}
{/block}
