{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}

{if $hinweis}
    <div class="alert alert-danger">{$hinweis}</div>
{/if}
<div class="row">
    <div class="col-xs-12 col-md-10 col-md-offset-1">
        {block name="checkout-proceed-as-guest"}
        <div class="well panel-wrap">
            <div class="panel panel-default" id="order-proceed-as-guest">
                <div class="panel-heading">
                    <h3 class="panel-title">{block name="checkout-proceed-as-guest-title"}{lang key="orderUnregistered" section="checkout"}{/block}</h3>
                </div>
                <div class="panel-body">
                    {block name="checkout-proceed-as-guest-body"}
                    <form id="neukunde" method="post" action="bestellvorgang.php">
                        {$jtl_token}
                        <fieldset>
                            {include file='checkout/inc_billing_address_form.tpl'}
                        </fieldset>
                        <fieldset>
                            <div class="col-xs-12">
                                <input type="hidden" name="unreg_form" value="1" />
                                <input type="hidden" name="editRechnungsadresse" value="{$editRechnungsadresse}" />
                                <input type="submit" class="btn btn-primary btn-lg submit submit_once pull-right" value="{lang key="sendCustomerData" section="account data"}" />
                            </div>
                        </fieldset>
                    </form>
                    {/block}
                </div>
            </div>
        </div>
        {/block}
    </div>
</div>

