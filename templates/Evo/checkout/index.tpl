{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}
{if !isset($bAjaxRequest) || !$bAjaxRequest}
    {include file='layout/header.tpl'}
{/if}
{*<h1 class="text-center">{lang section="breadcrumb" key="checkout"}</h1>*}
<div id="checkout">
    <div id="result-wrapper">
        {include file="checkout/inc_steps.tpl"}
        {include file="snippets/extension.tpl"}
        {if $step === 'accountwahl'}
            {include file='checkout/step0_login_or_register.tpl'}{*bestellvorgang_accountwahl.tpl*}
        {elseif $step === 'unregistriert bestellen'}
            {include file='checkout/step1_proceed_as_guest.tpl'}{*bestellvorgang_unregistriert_formular.tpl*}
        {elseif $step === 'Lieferadresse'}
            {include file='checkout/step2_delivery_address.tpl'}{*bestellvorgang_lieferadresse.tpl*}
        {elseif $step === 'Versand'}
            {include file='checkout/step3_shipping_options.tpl'}{*bestellvorgang_versand.tpl*}
        {elseif $step === 'Zahlung'}
            {include file='checkout/step4_payment_options.tpl'}{*bestellvorgang_zahlung.tpl*}
        {elseif $step === 'ZahlungZusatzschritt'}
            {include file='checkout/step4_payment_additional.tpl'}{*bestellvorgang_zahlung_zusatzschritt*}
        {elseif $step === 'Bestaetigung'}
            {include file='checkout/step5_confirmation.tpl'}{*bestellvorgang_bestaetigung*}
        {/if}
    </div>
</div>
{if !isset($bAjaxRequest) || !$bAjaxRequest}
    {include file='layout/footer.tpl'}
{/if}