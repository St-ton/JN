{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}
{if !empty($fehlendeAngaben) && !$hinweis}
    <div class="alert alert-danger">{lang key="yourDataDesc" section="account data"}</div>
{/if}
{if $hinweis}
    <div class="alert alert-danger">{$hinweis}</div>
{/if}
<div class="row">
    <div class="col-xs-12">
        {block name="checkout-proceed-as-guest"}
            <div id="order-proceed-as-guest">
                {block name="checkout-proceed-as-guest-body"}
                    <form id="neukunde" method="post" action="{get_static_route id='bestellvorgang.php'}">
                        <div class="panel panel-wrap">
                            {$jtl_token}
                            {include file='checkout/inc_billing_address_form.tpl'}
                        </div>
                        {include file='checkout/inc_shipping_address.tpl'}

                        <input type="hidden" name="unreg_form" value="{if isset($editRechnungsadresse) && $editRechnungsadresse === 1 && !empty($smarty.session.Kunde->kKunde)}0{else}1{/if}" />
                        <input type="hidden" name="editRechnungsadresse" value="{$editRechnungsadresse}" />
                        <input type="submit" class="btn btn-primary btn-lg submit submit_once pull-right" value="{lang key="sendCustomerData" section="account data"}" />
                    </form>
                {/block}
            </div>
        {/block}
    </div>
</div>

