{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
<h1>{lang key='editBillingAdress' section='account data'}</h1>
{include file='snippets/extension.tpl'}
{form method="post" id='rechnungsdaten' action="{get_static_route params=['editRechnungsadresse' => 1]}" class="evo-validate"}
    {card id="panel-address-form"}
        {$jtl_token}
        {include file='checkout/inc_billing_address_form.tpl'}
        {input type="hidden" name="editRechnungsadresse" value="1"}
        {input type="hidden" name="edit" value="1"}
        {input type="submit" value="{lang key='editBillingAdress' section='account data'}" class="btn btn-primary w-auto"}
    {/card}
{/form}
