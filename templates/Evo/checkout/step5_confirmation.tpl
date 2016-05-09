<div id="order-confirm">
    <p id="check-order-details-alert" class="alert alert-info">
        {lang key="checkOrderDetails" section="checkout"}
    </p>
    {if $hinweis}
       <p class="alert alert-danger">{$hinweis}</p>
    {/if}

    {if !empty($smarty.get.mailBlocked)}
        <p class="alert alert-danger">{lang key="kwkEmailblocked" section="errorMessages"}</p>
    {/if}

    {if !empty($smarty.get.fillOut)}
       <p class="alert alert-danger">{lang key="fillOutQuestion" section="messages"}</p>
    {/if}

    <div class="row">
        <div class="col-xs-12 col-sm-4">
            {block name="checkout-confirmation-billing-address"}
            <div class="panel panel-default" id="panel-edit-billing-address">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        {block name="checkout-confirmation-billing-address-title"}{lang key="billingAdress" section="account data"}
                        <a class="btn btn-default btn-xs pull-right button_edit" href="bestellvorgang.php?editRechnungsadresse=1">
                        <span class="fa fa-pencil" title="{lang key="modifyBillingAdress" section="global"}"></span>
                        </a>
                        {/block}
                    </h3>
                </div>
                <div class="panel-body">
                    {include file='checkout/inc_billing_address.tpl'}
                </div>
            </div>
            {/block}
        </div>

        <div class="col-xs-12 col-sm-4">
            {block name="checkout-confirmation-shipping-address"}
            <div class="panel panel-default" id="panel-edit-shipping-address">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        {block name="checkout-confirmation-shipping-address-title"}
                        {lang key="shippingAdress" section="account data"}
                        <a class="btn btn-default btn-xs pull-right button_edit" href="bestellvorgang.php?editLieferadresse=1" title="{lang key="modifyShippingAdress" section="checkout"}">
                        <span class="fa fa-pencil"></span>
                        </a>
                        {/block}
                    </h3>
                </div>
                <div class="panel-body">
                    {include file='checkout/inc_delivery_address.tpl'}
                </div>
            </div>
            {/block}
        </div>

        <div class="col-xs-12 col-sm-4">
            {block name="checkout-confirmation-shipping-method"}
            <div class="panel panel-default" id="panel-edit-shipping-method">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        {block name="checkout-confirmation-shipping-method-title"}
                        {lang key="shippingOptions" section="global"}
                        <a class="btn btn-default btn-xs pull-right button_edit" href="bestellvorgang.php?editVersandart=1" title="{lang key="modifyShippingOption" section="checkout"}">
                        <span class="fa fa-pencil"></span>
                        </a>
                        {/block}
                    </h3>
                </div>
                <div class="panel-body">
                {$smarty.session.Versandart->angezeigterName|trans}
                </div>
            </div>
            {/block}
            {block name="checkout-confirmation-payment-method"}
            <div class="panel panel-default" id="panel-edit-payment-options">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        {block name="checkout-confirmation-payment-method-title"}
                        {lang key="paymentOptions" section="global"}
                        <a class="btn btn-default btn-xs pull-right button_edit" href="bestellvorgang.php?editZahlungsart=1" title="{lang key="modifyPaymentOption" section="checkout"}">
                        <span class="fa fa-pencil"></span>
                        </a>
                        {/block}
                    </h3>
                </div>
                <div class="panel-body">
                    {block name="checkout-confirmation-payment-method-body"}
                    {$smarty.session.Zahlungsart->angezeigterName|trans}
                    {if isset($smarty.session.Zahlungsart->cHinweisText) && !empty($smarty.session.Zahlungsart->cHinweisText)}{* this should be localized *}
                        <p class="small text-muted">{$smarty.session.Zahlungsart->cHinweisText}</p>
                    {/if}
                    {/block}
                </div>
            </div>
            {/block}
        </div>
    </div>{* /row *}


    <div class="row">
        {if $KuponMoeglich || $GuthabenMoeglich}
            <div class="col-xs-12 col-md-6">
                {block name="checkout-confirmation-coupon"}
                <div class="panel panel-default" id="panel-edit-coupon">
                    <div class="panel-heading">
                        <h3 class="panel-title">{block name="checkout-confirmation-coupon-title"}{lang key="coupon" section="account data"}{/block}</h3>
                    </div>
                    <div class="panel-body">
                        {include file='checkout/coupon_form.tpl'}
                    </div>
                </div>
                {/block}
            </div>
        {/if}
        <div class="col-xs-12 col-md-6">
            {block name="checkout-confirmation-comment"}
            <div class="panel panel-default" id="panel-edit-comment">
                <div class="panel-heading">
                    <h3 class="panel-title">{block name="checkout-confirmation-comment-title"}{lang key="comment" section="product rating"}{/block}</h3>
                </div>
                <div class="panel-body">
                    {block name="checkout-confirmation-comment-body"}
                    {lang assign="orderCommentsTitle" key="orderComments" section="shipping payment"}
                    <textarea class="form-control" title="{$orderCommentsTitle|escape:"html"}" name="kommentar" cols="50" rows="3" id="comment" placeholder="{lang key="comment" section="product rating"}">{if isset($smarty.session.kommentar)}{$smarty.session.kommentar}{/if}</textarea>
                    {/block}
                </div>
            </div>
            {/block}
        </div>
    </div>{* /row *}

    {if isset($safetypay_form)}
        <div class="alert alert-info">{$safetypay_form}</div>
    {/if}
    <form method="post" name="agbform" id="complete_order" action="bestellabschluss.php">
        {$jtl_token}
        {if $Einstellungen.kaufabwicklung.bestellvorgang_wrb_anzeigen==1}
            {lang key="cancellationPolicyNotice" section="checkout" assign="cancellationPolicyNotice"}
            {lang key="wrb" section="checkout" assign="wrb"}
            {if isset($AGB->kLinkWRB) && $AGB->kLinkWRB > 0}
                {assign var='linkWRB' value='<a href="navi.php?s='|cat:$AGB->kLinkWRB|cat:'" class="popup">'|cat:$wrb|cat:'</a>'}

                <div class="alert alert-info">{$cancellationPolicyNotice|replace:"#LINK_WRB#":$linkWRB}</div>
            {elseif !empty($AGB->cWRBContentHtml)}
                {block name="checkout-confirmation-modal-agb-html"}
                {assign var='linkWRB' value='<a href="#" data-toggle="modal" data-target="#wrbHtmlModal" class="modal-popup" id="wrb">'|cat:$wrb|cat:'</a>'}
                <div class="alert alert-info">{$cancellationPolicyNotice|replace:'#LINK_WRB#':$linkWRB}</div>
                <div class="modal fade" id="wrbHtmlModal" tabindex="-1" role="dialog" aria-labelledby="wrbHtmlLabel">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title" id="wrbHtmlLabel">{lang key="wrb" section="checkout"}</h4>
                            </div>
                            <div class="modal-body">
                                {$AGB->cWRBContentHtml}
                            </div>
                        </div>
                    </div>
                </div>
                {/block}
            {elseif !empty($AGB->cWRBContentText)}
                {block name="checkout-confirmation-modal-agb-text"}
                {assign var='linkWRB' value='<a href="#" data-toggle="modal" data-target="#wrbTextModal" class="modal-popup" id="wrb">'|cat:$wrb|cat:'</a>'}
                <div class="alert alert-info">{$cancellationPolicyNotice|replace:'#LINK_WRB#':$linkWRB}</div>
                <div class="modal fade" id="wrbTextModal" tabindex="-1" role="dialog" aria-labelledby="wrbTextLabel">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title" id="wrbTextLabel">{lang key="wrb" section="checkout"}</h4>
                            </div>
                            <div class="modal-body">
                                {$AGB->cWRBContentText}
                            </div>
                        </div>
                    </div>
                </div>
                {/block}
            {/if}
        {/if}
        {if !isset($smarty.session.cPlausi_arr)}
            {assign var=plausiArr value=array()}
        {else}
            {assign var=plausiArr value=$smarty.session.cPlausi_arr}
        {/if}

        {hasCheckBoxForLocation bReturn="bCheckBox" nAnzeigeOrt=$nAnzeigeOrt cPlausi_arr=$plausiArr cPost_arr=$cPost_arr}
        {if $bCheckBox}
            <hr>
            {include file='snippets/checkbox.tpl' nAnzeigeOrt=$nAnzeigeOrt cPlausi_arr=$plausiArr cPost_arr=$cPost_arr}
            <hr>
        {/if}
        <div class="row">
            <div class="col-xs-12 order-submit">
                {block name="checkout-confirmation-confirm-order"}
                <div class="well panel-wrap basket-well basket-final">
                    <div class="panel panel-primary" id="panel-submit-order">
                        <div class="panel-body">
                            <input type="hidden" name="abschluss" value="1" />
                            <input type="hidden" id="comment-hidden" name="kommentar" value="" />
                            {include file="checkout/inc_order_items.tpl" tplscope="confirmation"}
                            <div class="table left shippingTime">
                                <strong>{lang key="shippingTime" section="global"}</strong>: {$smarty.session.Warenkorb->cEstimatedDelivery}
                            </div>
                            <input type="submit" value="{lang key="orderLiableToPay" section="checkout"}" id="complete-order-button" class="btn btn-primary btn-lg pull-right submit submit_once" />
                            <a href="warenkorb.php" class="btn btn-default btn-lg">{lang key="modifyBasket" section="checkout"}</a>
                        </div>
                    </div>
                </div>
                {/block}
            </div>
        </div>{* /row *}
    </form>
</div>