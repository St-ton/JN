{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if !isset($checkout)}
    {include file='register/inc_vcard_upload.tpl' id='registrieren.php'}
{/if}

<form method="post" action="{get_static_route id='registrieren.php'}" class="evo-validate">
    {$jtl_token}
    {include file='register/form/customer_account.tpl'}
    <hr>
    {if isset($checkout) && $checkout === 1}
        {include file='checkout/inc_shipping_address.tpl'}
    {/if}
    <input type="hidden" name="checkout" value="{if isset($checkout)}{$checkout}{/if}">
    <input type="hidden" name="form" value="1">
    <input type="hidden" name="editRechnungsadresse" value="{$editRechnungsadresse}">
    <input type="submit" class="btn btn-primary btn-lg pull-right submit submit_once" value="{lang key='sendCustomerData' section='account data'}">
</form>
