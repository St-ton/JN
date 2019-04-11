{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='checkout-step5-confirmation'}
    <div id="order-confirm">
        {block name='checkout-step5-confirmation-alert'}
            {if !empty($smarty.get.mailBlocked)}
                {alert variant="danger"}{lang key='kwkEmailblocked' section='errorMessages'}{/alert}
            {/if}
            {if !empty($smarty.get.fillOut)}
                {alert variant="danger"}{lang key='mandatoryFieldNotification' section='errorMessages'}{/alert}
            {/if}
        {/block}

        {row class="row-eq-height"}
            {col cols=6 id="billing-address"}
                {block name='checkout-step5-confirmation-billing-address'}
                    {card no-body=true class="mb-3 border-0"}
                        {cardheader}
                            {lang key='billingAdress' section='account data'}
                        {/cardheader}
                        {cardbody}
                            {block name='checkout-step5-confirmation-include-inc-billing-address'}
                                <p>
                                    {include file='checkout/inc_billing_address.tpl'}
                                </p>
                            {/block}
                            {link class="small edit" href="{get_static_route id='bestellvorgang.php'}?editRechnungsadresse=1"}
                                <span class="fa fa-pencil-alt"></span> {lang key='modifyBillingAdress'}
                            {/link}
                        {/cardbody}
                    {/card}
                {/block}
            {/col}
            {col cols=6 id="shipping-address"}
                {block name='checkout-step5-confirmation-shipping-address'}
                    {card no-body=true class="mb-3 border-0"}
                        {cardheader}
                            {lang key='shippingAdress' section='account data'}
                        {/cardheader}
                        {cardbody}
                            {block name='checkout-step5-confirmation-include-inc-delivery-address'}
                                <p>
                                    {include file='checkout/inc_delivery_address.tpl'}
                                </p>
                            {/block}
                            {link class="small edit" href="{get_static_route id='bestellvorgang.php'}?editLieferadresse=1"}
                                <span class="fa fa-pencil-alt"></span> {lang key='modifyShippingAdress' section='checkout'}
                            {/link}
                        {/cardbody}
                    {/card}
                {/block}
            {/col}
            {col cols=6 id="shipping-method" class="mb-3 border-0"}
                {block name='checkout-step5-confirmation-shipping-method'}
                    {card no-body=true class="mb-3 border-0"}
                        {cardheader}
                                {* ToDo: New Localization! *}
                            {lang key='shippingOptions'}
                        {/cardheader}
                        {cardbody}
                            <p>
                                <strong class="title">{$smarty.session.Versandart->angezeigterName|trans}</strong>
                            </p>

                            {$cEstimatedDelivery = $smarty.session.Warenkorb->getEstimatedDeliveryTime()}
                            {if $cEstimatedDelivery|@count_characters > 0}
                                <p class="small text-muted">
                                    <strong>{lang key='shippingTime'}</strong>: {$cEstimatedDelivery}
                                </p>
                            {/if}
                            {link class="small edit" href="{get_static_route id='bestellvorgang.php'}?editVersandart=1"}
                                <span class="fa fa-pencil-alt"></span> {lang key='modifyShippingOption' section='checkout'}
                            {/link}
                        {/cardbody}
                    {/card}
                {/block}
            {/col}
            {col cols=6 id="payment-method"}
                {block name='checkout-step5-confirmation-payment-method'}
                    {card no-body=true class="mb-3 border-0"}
                        {cardheader}
                            {* ToDo: New Localization! *}
                            {lang key='paymentOptions'}
                        {/cardheader}
                        {cardbody}
                            <p>
                                <strong class="title">{$smarty.session.Zahlungsart->angezeigterName|trans}</strong>
                            </p>
                            {if isset($smarty.session.Zahlungsart->cHinweisText) && !empty($smarty.session.Zahlungsart->cHinweisText)}{* this should be localized *}
                                <p class="small text-muted">{$smarty.session.Zahlungsart->cHinweisText}</p>
                            {/if}
                            {link class="small edit" href="{get_static_route id='bestellvorgang.php'}?editZahlungsart=1"}
                                <span class="fa fa-pencil-alt"></span> {lang key='modifyPaymentOption' section='checkout'}
                            {/link}
                        {/cardbody}
                    {/card}
                {/block}
            {/col}

            {if $GuthabenMoeglich}
                {block name='checkout-step5-confirmation-credit'}
                    {col cols=12}
                        {card id="panel-edit-credit" no-body=true class="mb-3 border-0"}
                            {cardheader}
                                {lang key='credit' section='account data'}
                            {/cardheader}
                            {cardbody}
                                {block name='checkout-step5-confirmation-include-credit-form'}
                                    {include file='checkout/credit_form.tpl'}
                                {/block}
                            {/cardbody}
                        {/card}
                    {/col}
                {/block}
            {/if}

            {col cols=12 md=6}
                {block name='checkout-step5-confirmation-comment'}
                    {card no-body=true id="panel-edit-comment" class="mb-3 border-0"}
                        {cardheader}
                            {lang key='comment' section='product rating'}
                        {/cardheader}
                        {cardbody}
                            {block name='checkout-step5-confirmation-comment-body'}
                                {lang assign='orderCommentsTitle' key='orderComments' section='shipping payment'}
                                {textarea title=$orderCommentsTitle|escape:'html' name="kommentar" cols="50" rows="3" id="comment" placeholder="{lang key='comment' section='product rating'}"}
                                    {if isset($smarty.session.kommentar)}{$smarty.session.kommentar}{/if}
                                {/textarea}
                            {/block}
                        {/cardbody}
                    {/card}
                {/block}
            {/col}
            {if $KuponMoeglich}
                {col cols=12 md=6}
                    {block name='checkout-step5-confirmation-coupon'}
                        {card no-body=true id="panel-edit-coupon" class="mb-3 border-0"}
                            {cardheader}
                                {lang key='coupon' section='account data'}
                            {/cardheader}
                            {cardbody}
                                {block name='checkout-step5-confirmation-include-coupon-form'}
                                    {include file='checkout/coupon_form.tpl'}
                                {/block}
                            {/cardbody}
                        {/card}
                    {/block}
                {/col}
            {/if}
        {/row}
        {block name='checkout-step5-confirmation-form'}
            {form method="post" name="agbform" id="complete_order" action="{get_static_route id='bestellabschluss.php'}" class="evo-validate"}
                {block name='checkout-step5-confirmation-form-content'}
                    {lang key='agb' assign='agb'}
                    {if isset($AGB->kLinkAGB) && $AGB->kLinkAGB > 0}
                        {lang key='termsAndConditionsNotice' section='checkout' printf=$AGB->cURLAGB|cat:':::class="popup"' assign='agbNotice'}
                    {elseif !empty($AGB->cAGBContentHtml)}
                        {block name='checkout-step5-confirmation-modal-agb-html'}
                            {lang key='termsAndConditionsNotice' section='checkout' printf=$AGB->cURLAGB|cat:':::data-toggle="modal" data-target="#agb-html-modal" class="modal-popup" id="agb"' assign='agbNotice'}
                            {modal id="agb-html-modal" title=$agb}
                                {$AGB->cAGBContentHtml}
                            {/modal}
                        {/block}
                    {elseif !empty($AGB->cAGBContentText)}
                        {block name='checkout-step5-confirmation-modal-agb-text'}
                            {lang key='termsAndConditionsNotice' section='checkout' printf=$AGB->cURLAGB|cat:':::data-toggle="modal" data-target="#agb-text-modal" class="modal-popup" id="agb"' assign='agbNotice'}
                            {modal id="agb-text-modal" title=$agb}
                                {$AGB->cAGBContentText}
                            {/modal}
                        {/block}
                    {/if}

                    {if $Einstellungen.kaufabwicklung.bestellvorgang_wrb_anzeigen == 1}
                        {lang key='wrb' section='checkout' assign='wrb'}
                        {if isset($AGB->kLinkWRB) && $AGB->kLinkWRB > 0}
                            {lang key='cancellationPolicyNotice' section='checkout' printf=$AGB->cURLWRB|cat:':::class="popup"' assign='wrbNotice'}
                        {elseif !empty($AGB->cWRBContentHtml)}
                            {block name='checkout-step5-confirmation-modal-wrb-html'}
                                {lang key='cancellationPolicyNotice' section='checkout' printf=$AGB->cURLWRB|cat:':::data-toggle="modal" data-target="#wrb-html-modal" class="modal-popup" id="wrb"' assign='wrbNotice'}
                                {modal id="wrb-html-modal" title=$wrb}
                                    {$AGB->cWRBContentHtml}
                                {/modal}
                            {/block}
                        {elseif !empty($AGB->cWRBContentText)}
                            {block name='checkout-step5-confirmation-modal-wrb-text'}
                                {lang key='cancellationPolicyNotice' section='checkout' printf=$AGB->cURLWRB|cat:':::data-toggle="modal" data-target="#wrb-text-modal" class="modal-popup" id="wrb"' assign='wrbNotice'}
                                {modal id="wrb-text-modal" title=$wrb}
                                    {$AGB->cWRBContentText}
                                {/modal}
                            {/block}
                        {/if}
                    {/if}

                    {if isset($wrbNotice) || isset($agbNotice)}
                        {block name='checkout-step5-confirmation-alert-agb'}
                            {alert variant="info" class="mb-5"}
                                {if isset($agbNotice)}<p>{$agbNotice}</p>{/if}
                                {if isset($wrbNotice)}<p>{$wrbNotice}</p>{/if}
                            {/alert}
                        {/block}
                    {/if}

                    {if !isset($smarty.session.cPlausi_arr)}
                        {assign var=plausiArr value=array()}
                    {else}
                        {assign var=plausiArr value=$smarty.session.cPlausi_arr}
                    {/if}

                    {hasCheckBoxForLocation bReturn="bCheckBox" nAnzeigeOrt=$nAnzeigeOrt cPlausi_arr=$plausiArr cPost_arr=$cPost_arr}
                    {if $bCheckBox}
                        {block name='checkout-step5-confirmation-include-checkbox'}
                            <hr>
                            {include file='snippets/checkbox.tpl' nAnzeigeOrt=$nAnzeigeOrt cPlausi_arr=$plausiArr cPost_arr=$cPost_arr}
                            <hr>
                        {/block}
                    {/if}
                    {row}
                        {col cols=12 class="order-submit"}
                            {block name='checkout-step5-confirmation-confirm-order'}
                            <div class="basket-final">
                                <div id="panel-submit-order">
                                    {input type="hidden" name="abschluss" value="1"}
                                    {input type="hidden" id="comment-hidden" name="kommentar" value=""}
                                    {block name='checkout-step5-confirmation-include-inc-order-items'}
                                        <div class="mb-7">
                                            {include file='checkout/inc_order_items.tpl' tplscope='confirmation'}
                                        </div>
                                    {/block}
                                    {button type="submit" variant="primary" id="complete-order-button" class="submit_once float-right ml-3"}
                                        {lang key='orderLiableToPay' section='checkout'}
                                    {/button}
                                    {link href="{get_static_route id='warenkorb.php'}" class="btn btn-light float-right"}
                                        {lang key='modifyBasket' section='checkout'}
                                    {/link}
                                </div>
                            </div>
                            {/block}
                        {/col}
                    {/row}
                {/block}
            {/form}
        {/block}
    </div>
{/block}
