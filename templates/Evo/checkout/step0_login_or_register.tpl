{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}

<div id="register-customer" class="row">
    <div id="existing-customer" class="col-xs-12 {if isset($boxes.left) && !$bExclusive && !empty($boxes.left)}col-md-3{else}col-md-4{/if}">
        <form method="post" action="{get_static_route id='bestellvorgang.php'}" class="form" id="order_register_or_login">
            {block name="checkout-login"}
            <div class="panel panel-default">
                <div class="panel-body">
                    {block name="checkout-login-body"}
                    <fieldset>
                        {$jtl_token}
                        <legend>{block name="checkout-login-title"}{lang key="loginForRegisteredCustomers" section="checkout"}{/block}</legend>
                        {include file="register/form/customer_login.tpl"}
                    </fieldset>
                    {/block}
                </div>
            </div>
            {/block}
        </form>
    </div>
    <div id="customer" class="col-xs-12 {if isset($boxes.left) && !$bExclusive && !empty($boxes.left)}col-md-9{else}col-md-8{/if}">
        <form method="post" action="{get_static_route id='registrieren.php'}" class="form" id="form-register">
            <div class="panel panel-default">
                <div class="panel-body">
                    {$jtl_token}
                    {include file='register/form/customer_account.tpl' checkout=1 step="formular"}
                    <hr/>
                    <div class="form-group">
                        <input type="hidden" name="shipping" value="1">
                        <label class="control-label" for="checkout_register_shipping_address" data-toggle="collapse" data-target="#register_shipping_address">
                            <input id="checkout_register_shipping_address" class="checkbox-inline" type="checkbox" name="shipping" value="0" checked="checked" />
                            {lang key="shippingAdressEqualBillingAdress" section="account data"}
                        </label>
                    </div>
                    <div id="register_shipping_address" class="panel panel-default collapse" aria-expanded="false">
                        <div class="panel-body">
                            <fieldset>
                                <legend>{lang key="createNewShippingAdress" section="account data"}</legend>
                                {include file="register/form/customer_shipping_address.tpl" prefix="register"}
                            </fieldset>
                        </div>
                    </div>
                    <input type="hidden" name="checkout" value="1">
                    <input type="hidden" name="form" value="1">
                    <input type="hidden" name="editRechnungsadresse" value="1">

                    <input type="submit" class="btn btn-primary submit submit_once pull-right" value="{lang key="sendCustomerData" section="account data"}">
                </div>
            </div>
        </form>
    </div>
</div>