{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{include file='snippets/extension.tpl'}
{form method="post" id='rechnungsdaten' action="{get_static_route params=['editRechnungsadresse' => 1]}" class="evo-validate"}
    <div id="panel-address-form">
        {include file='checkout/inc_billing_address_form.tpl'}
        {row class='mt-5'}
            {col md=2}{/col}
            {col md=5}
                {input type="hidden" name="editRechnungsadresse" value="1"}
                {input type="hidden" name="edit" value="1"}
                {button type="submit" value="1" class="w-auto" variant="primary"}
                    {lang key='editCustomerData' section='account data'}
                {/button}
            {/col}
        {/row}
    </div>
{/form}
