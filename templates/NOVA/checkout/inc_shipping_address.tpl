{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{assign var=fehlendeAngabenShipping value=$fehlendeAngaben.shippingAddress|default:null}
<div class="form-group checkbox control-toggle">
    {input type="hidden" name="shipping_address" value="1"}
    {checkbox id="checkout_register_shipping_address"
        name="shipping_address" value="0" checked=(!isset($Lieferadresse) && empty($kLieferadresse))
        data=["toggle"=>"collapse", "target"=>"#select_shipping_address"]}
        {lang key='shippingAdressEqualBillingAdress' section='account data'}
    {/checkbox}
</div>
{block name='checkout-enter-shipping-address'}
<div id="select_shipping_address" class="collapse collapse-non-validate{if isset($Lieferadresse) || !empty($kLieferadresse)} show{/if}" aria-expanded="{if isset($Lieferadresse) || !empty($kLieferadresse)}true{else}false{/if}">
    {block name='checkout-enter-shipping-address-body'}
    {if !empty($smarty.session.Kunde->kKunde) && isset($Lieferadressen) && $Lieferadressen|count > 0}
        <fieldset>
            <legend>{lang key='deviatingDeliveryAddress' section='account data'}</legend>
            {listgroup class="form-group" tag="ul"}
            {foreach $Lieferadressen as $adresse}
                {if $adresse->kLieferadresse > 0}
                    {listgroupitem tag="li"}
                        <label class="btn-block" for="delivery{$adresse->kLieferadresse}" data-toggle="collapse" data-target="#register_shipping_address.show">
                            {radio name="kLieferadresse" value=$adresse->kLieferadresse id="delivery{$adresse->kLieferadresse}" checked=($kLieferadresse == $adresse->kLieferadresse)}
                                <span class="control-label label-default">{if $adresse->cFirma}{$adresse->cFirma},{/if} {$adresse->cVorname} {$adresse->cNachname}
                                , {$adresse->cStrasse} {$adresse->cHausnummer}, {$adresse->cPLZ} {$adresse->cOrt}
                                    , {$adresse->angezeigtesLand}</span>
                            {/radio}
                        </label>
                    {/listgroupitem}
                {/if}
            {/foreach}
                {listgroupitem tag="li"}
                    <label class="btn-block" for="delivery_new" data-toggle="collapse" data-target="#register_shipping_address:not(.show)">
                        {radio name="kLieferadresse" value="-1" id="delivery_new" checked=($kLieferadresse == -1) required=true aria-required=true}
                            <span class="control-label label-default">{lang key='createNewShippingAdress' section='account data'}</span>
                        {/radio}
                    </label>
                {/listgroupitem}
            {/listgroup}
        </fieldset>
        <fieldset id="register_shipping_address" class="collapse collapse-non-validate{if $kLieferadresse == -1}} show{/if}" aria-expanded="{if $kLieferadresse == -1}}true{else}false{/if}">
            <legend>{lang key='createNewShippingAdress' section='account data'}</legend>
            {include file='checkout/customer_shipping_address.tpl' prefix="register" fehlendeAngaben=$fehlendeAngabenShipping}
            {include file='checkout/customer_shipping_contact.tpl' prefix="register" fehlendeAngaben=$fehlendeAngabenShipping}
        </fieldset>
    {else}
        <fieldset>
            <legend>{lang key='createNewShippingAdress' section='account data'}</legend>
            {include file='checkout/customer_shipping_address.tpl' prefix="register" fehlendeAngaben=$fehlendeAngabenShipping}
            {include file='checkout/customer_shipping_contact.tpl' prefix="register" fehlendeAngaben=$fehlendeAngabenShipping}
        </fieldset>
    {/if}
    {/block}
</div>
{/block}
{if isset($smarty.get.editLieferadresse)}
{literal}
    <script type="text/javascript">
        $(document).ready(function () {
            $('#checkout_register_shipping_address').prop('checked', false);
            $('#select_shipping_address').addClass('in');
            $.evo.extended().smoothScrollToAnchor('#checkout_register_shipping_address');
        });
    </script>
{/literal}
{/if}
