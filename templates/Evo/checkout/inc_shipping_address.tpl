{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}

<div class="form-group checkbox">
    <input type="hidden" name="shipping_address" value="1">
    <label for="checkout_register_shipping_address" class="btn-block" data-toggle="collapse" data-target="#select_shipping_address">
        <input id="checkout_register_shipping_address" class="radio-checkbox" type="checkbox" name="shipping_address" value="0"{if !isset($Lieferadresse) && empty($kLieferadresse)} checked="checked"{/if} />
        <span class="control-label label-default">
            {lang key="shippingAdressEqualBillingAdress" section="account data"}
        </span>
    </label>
</div>
{block name="checkout-enter-shipping-address"}
<div id="select_shipping_address" class="collapse collapse-non-validate{if isset($Lieferadresse) || !empty($kLieferadresse)} in{/if}" aria-expanded="{if isset($Lieferadresse) || !empty($kLieferadresse)}true{else}false{/if}">
    {block name="checkout-enter-shipping-address-body"}
    {if !empty($smarty.session.Kunde->kKunde) && isset($Lieferadressen) && $Lieferadressen|count > 0}
        <fieldset>
            <legend>{lang key="deviatingDeliveryAddress" section="account data"}</legend>
            <ul class="list-group form-group">
            {foreach name=lieferad from=$Lieferadressen item=adresse}
                {if $adresse->kLieferadresse>0}
                    <li class="list-group-item">
                        <div class="radio">
                            <label class="btn-block" for="delivery{$adresse->kLieferadresse}" data-toggle="collapse" data-target="#register_shipping_address.in">
                                <input class="radio-checkbox" type="radio" name="kLieferadresse" value="{$adresse->kLieferadresse}" id="delivery{$adresse->kLieferadresse}" {if $kLieferadresse == $adresse->kLieferadresse}checked{/if}>
                                <span class="control-label label-default">{if $adresse->cFirma}{$adresse->cFirma},{/if} {$adresse->cVorname} {$adresse->cNachname}
                                , {$adresse->cStrasse} {$adresse->cHausnummer}, {$adresse->cPLZ} {$adresse->cOrt}
                                    , {$adresse->angezeigtesLand}</span></label>
                        </div>
                    </li>
                {/if}
            {/foreach}
                <li class="list-group-item">
                    <div class="radio">
                        <label class="btn-block" for="delivery_new" data-toggle="collapse" data-target="#register_shipping_address:not(.in)">
                            <input class="radio-checkbox" type="radio" name="kLieferadresse" value="-1" id="delivery_new" {if $kLieferadresse == -1}checked{/if} required="required" aria-required="true">
                            <span class="control-label label-default">{lang key="createNewShippingAdress" section="account data"}</span>
                        </label>
                    </div>
                </li>
            </ul>
        </fieldset>
        <fieldset id="register_shipping_address" class="collapse collapse-non-validate{if $kLieferadresse == -1}} in{/if}" aria-expanded="{if $kLieferadresse == -1}}true{else}false{/if}">
            <legend>{lang key="createNewShippingAdress" section="account data"}</legend>
            {include file="register/form/customer_shipping_address.tpl" prefix="register" fehlendeAngaben=$fehlendeAngaben.shipping_address}
            {include file="register/form/customer_shipping_contact.tpl" prefix="register" fehlendeAngaben=$fehlendeAngaben.shipping_address}
        </fieldset>
    {else}
        <fieldset>
            <legend>{lang key="createNewShippingAdress" section="account data"}</legend>
            {include file="register/form/customer_shipping_address.tpl" prefix="register" fehlendeAngaben=$fehlendeAngaben.shipping_address}
            {include file="register/form/customer_shipping_contact.tpl" prefix="register" fehlendeAngaben=$fehlendeAngaben.shipping_address}
        </fieldset>
    {/if}
    {/block}
</div>
{/block}