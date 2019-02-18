{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if isset($editRechnungsadresse) && $editRechnungsadresse === 1 && !empty($smarty.session.Kunde->kKunde)}
    {assign var=unreg_form value=0}
    {assign var=unreg_step value=$step}
{else}
    {assign var=unreg_form value=1}
    {assign var=unreg_step value='formular'}
{/if}
{if !empty($fehlendeAngaben) && !$alertNote}
    {alert variant="danger"}{lang key='mandatoryFieldNotification' section='errorMessages'}{/alert}
{/if}
{row}
    {col cols=12}
        {block name='checkout-proceed-as-guest'}
            <div id="order-proceed-as-guest">
                {block name='checkout-proceed-as-guest-body'}
                    {form id="neukunde" method="post" action="{get_static_route id='bestellvorgang.php'}" class="evo-validate"}
                        {include file='checkout/inc_billing_address_form.tpl' step=$unreg_step}
                        {include file='checkout/inc_shipping_address.tpl'}
                        <div class="text-right">
                            {input type="hidden" name="unreg_form" value=$unreg_form}
                            {input type="hidden" name="editRechnungsadresse" value=$editRechnungsadresse}
                            {button variant="primary" type="submit" class="submit_once"}
                                {lang key='sendCustomerData' section='account data'}
                            {/button}
                        </div>
                    {/form}
                {/block}
            </div>
        {/block}
    {/col}
{/row}
