{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}

<div class="form-group">
    <input type="hidden" name="shipping_address" value="1">
    <label class="control-label" for="checkout_register_shipping_address" data-toggle="collapse" data-target="#register_shipping_address">
        <input id="checkout_register_shipping_address" class="checkbox-inline" type="checkbox" name="shipping_address" value="0"{if !isset($Lieferadresse)} checked="checked"{/if} />
        {lang key="shippingAdressEqualBillingAdress" section="account data"}
    </label>
</div>
<div id="register_shipping_address" class="panel panel-wrap collapse collapse-non-validate{if isset($Lieferadresse)} in{/if}" aria-expanded="false">
    <fieldset>
        <legend>{lang key="createNewShippingAdress" section="account data"}</legend>
        {include file="register/form/customer_shipping_address.tpl" prefix="register"}
    </fieldset>
</div>