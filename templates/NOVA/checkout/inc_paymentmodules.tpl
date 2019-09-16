{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='checkout-inc-paymentmodules'}
    {if !isset($abschlussseite) || $abschlussseite !== 1}
        {if $oPlugin !== null && $oPlugin instanceof JTL\Plugin\PluginInterface}
            {$method = $oPlugin->getPaymentMethods()->getMethodByID($Bestellung->Zahlungsart->cModulId)}
        {else}
            {$method = null}
        {/if}
        {assign var=cModulId value=$Bestellung->Zahlungsart->cModulId}
        {if ($method === null || $Bestellung->Zahlungsart->cModulId !== $method->getModuleID())
            && $Bestellung->Zahlungsart->cModulId !== 'za_kreditkarte_jtl'
            && $Bestellung->Zahlungsart->cModulId !== 'za_lastschrift_jtl'
        }
            {block name='checkout-inc-paymentmodules-alert'}
                <p class="mb-4 mb-md-5">
                    {if isset($smarty.session.Zahlungsart->nWaehrendBestellung) && $smarty.session.Zahlungsart->nWaehrendBestellung == 1}
                        {lang key='orderConfirmationPre' section='checkout'}
                    {else}
                        {lang key='orderConfirmationPost' section='checkout'}
                    {/if}
                </p>
            {/block}
        {/if}

        {if (empty($smarty.session.Zahlungsart->nWaehrendBestellung) || $smarty.session.Zahlungsart->nWaehrendBestellung != 1)
            && $Bestellung->Zahlungsart->cModulId !== 'za_kreditkarte_jtl'
            && $Bestellung->Zahlungsart->cModulId !== 'za_lastschrift_jtl'
        }
            {block name='checkout-inc-paymentmodules-during-order'}
                <ul class="list-unstyled">
                    <li><span class="font-weight-bold">{lang key='yourOrderId' section='checkout'}: </span>{$Bestellung->cBestellNr}</li>
                    <li><span class="font-weight-bold">{lang key='yourChosenPaymentOption' section='checkout'}: </span>{$Bestellung->cZahlungsartName}</li>
                </ul>
            {/block}
        {/if}
        {block name='checkout-inc-paymentmodules-method-inner'}
            <div class="payment-method-inner mb-3">
                {if $Bestellung->Zahlungsart->cModulId === 'za_rechnung_jtl'}
                    {*{lang key='invoiceDesc' section='checkout'}*}
                {elseif $Bestellung->Zahlungsart->cModulId === 'za_lastschrift_jtl'}
                    {*{lang key='banktransferDesc' section='checkout'}*}
                {elseif $Bestellung->Zahlungsart->cModulId === 'za_nachnahme_jtl'}
                    {*{lang key='banktransferDesc' section='checkout'}*}
                {elseif $Bestellung->Zahlungsart->cModulId === 'za_barzahlung_jtl'}
                    {lang key='cashOnPickupDesc' section='checkout'}
                {elseif $Bestellung->Zahlungsart->cModulId === 'za_paypal_jtl'}
                    {include file='checkout/modules/paypal/bestellabschluss.tpl'}
                {elseif $Bestellung->Zahlungsart->cModulId === 'za_kreditkarte_jtl'}
                    {include file='account/retrospective_payment.tpl'}
                {elseif $method !== null && $Bestellung->Zahlungsart->cModulId === $method->getModuleID()}
                    {block name='checkout-inc-paymentmodules-include-plugin'}
                        {include file=$method->getTemplateFilePath()}
                    {/block}
                {/if}
                <br />
            </div>
        {/block}
    {/if}
{/block}
