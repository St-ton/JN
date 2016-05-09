{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}

{include file='layout/header.tpl'}

{if $step === 'login'}
    {include file='account/login.tpl'}
{elseif $step === 'mein Konto'}
    {include file='account/my_account.tpl'}
{elseif $step === 'rechnungsdaten'}
    {include file='account/address_form.tpl'}
{elseif $step === 'passwort aendern'}
    {include file='account/change_password.tpl'}
{elseif $step === 'bestellung'}
    {include file='account/order_details.tpl'}
{elseif $step === 'account loeschen'}
    {include file='account/delete_account.tpl'}
{elseif $step === 'wunschliste anzeigen'}
    {include file='account/wishlist.tpl'}
{elseif $step === 'wunschliste versenden'}
    {include file='account/wishlist_email_form.tpl'}
{elseif $step === 'kunden_werben_kunden'}
    {include file='account/customers_recruiting.tpl'}
{/if}

{include file='layout/footer.tpl'}