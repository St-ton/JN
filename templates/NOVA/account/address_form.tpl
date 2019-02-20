{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
<h1>{lang key='editBillingAdress' section='account data'}</h1>
{include file='snippets/extension.tpl'}
{form method="post" id='rechnungsdaten' action="{get_static_route params=['editRechnungsadresse' => 1]}" class="evo-validate"}
    {card id="panel-address-form"}
        {include file='checkout/inc_billing_address_form.tpl'}
        {input type="hidden" name="editRechnungsadresse" value="1"}
        {input type="hidden" name="edit" value="1"}
        {button type="submit" value="1" class="w-auto" variant="primary"}
            {lang key='editBillingAdress' section='account data'}
        {/button}
    {/card}
{/form}
