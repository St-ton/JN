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
                        <legend>{block name="checkout-login-title"}{lang key="alreadyCustomer" section="global"}{/block}</legend>
                        {include file="register/form/customer_login.tpl"}
                    </fieldset>
                    {/block}
                </div>
            </div>
            {/block}
        </form>
    </div>
    <div id="customer" class="col-xs-12 {if isset($boxes.left) && !$bExclusive && !empty($boxes.left)}col-md-9{else}col-md-8{/if}">
        <div class="panel panel-wrap">
                {include file='register/inc_vcard_upload.tpl' id='bestellvorgang.php'}
                <form method="post" action="{get_static_route id='bestellvorgang.php'}" class="form" id="form-register">
                    {$jtl_token}
                    {include file='register/form/customer_account.tpl' checkout=1 step="formular"}
                    <hr/>
                    {include file='checkout/inc_shipping_address.tpl'}

                    <input type="hidden" name="checkout" value="1">
                    <input type="hidden" name="form" value="1">
                    <input type="hidden" name="editRechnungsadresse" value="1">

                    <input type="submit" class="btn btn-primary submit submit_once pull-right" value="{lang key="sendCustomerData" section="account data"}">
                </form>
        </div>
    </div>
</div>