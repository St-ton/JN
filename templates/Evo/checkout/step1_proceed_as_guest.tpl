{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}

{if $hinweis}
    <div class="alert alert-danger">{$hinweis}</div>
{/if}
<div class="row">
    <div class="col-xs-12">
        {block name="checkout-proceed-as-guest"}
        <div id="order-proceed-as-guest">
            {block name="checkout-proceed-as-guest-body"}
            <form id="neukunde" method="post" action="{get_static_route id='bestellvorgang.php'}">
                <div class="panel panel-strap">
                    {$jtl_token}
                    {include file='checkout/inc_billing_address_form.tpl'}
                </div>
                <hr/>
                <div class="form-group">
                    <input type="hidden" name="shipping_address" value="1">
                    <label class="control-label" for="checkout_register_shipping_address" data-toggle="collapse" data-target="#register_shipping_address">
                        <input id="checkout_register_shipping_address" class="checkbox-inline" type="checkbox" name="shipping_address" value="0" checked="checked" />
                        {lang key="shippingAdressEqualBillingAdress" section="account data"}
                    </label>
                </div>
                <div id="register_shipping_address" class="panel panel-strap collapse collapse-non-validate" aria-expanded="false">
                    <fieldset>
                        <legend>{lang key="createNewShippingAdress" section="account data"}</legend>
                        {include file="register/form/customer_shipping_address.tpl" prefix="register"}
                    </fieldset>
                </div>

                <input type="hidden" name="unreg_form" value="1" />
                <input type="hidden" name="editRechnungsadresse" value="{$editRechnungsadresse}" />
                <input type="submit" class="btn btn-primary btn-lg submit submit_once pull-right" value="{lang key="sendCustomerData" section="account data"}" />
            </form>
            {/block}
        </div>
        {/block}
    </div>
</div>

