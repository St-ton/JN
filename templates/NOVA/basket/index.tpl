{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='basket-index'}
    {block name='basket-index-include-header'}
        {include file='layout/header.tpl'}
    {/block}

    {block name='basket-index-content'}
        {container}
            {row}
                {block name='basket-index-main'}
                {col cols=12 lg="{if ($Warenkorb->PositionenArr|@count > 0)}7{else}12{/if}"}
                    {opcMountPoint id='opc_before_heading'}
                    {block name='basket-index-heading'}
                        <h1 class="h2 b-5">{lang key='basket'} ({count($smarty.session.Warenkorb->PositionenArr)} {lang key='products'})</h1>
                    {/block}
                    {block name='basket-index-include-extension'}
                        {include file='snippets/extension.tpl'}
                    {/block}

                    {if ($Warenkorb->PositionenArr|@count > 0)}
                        {block name='basket-index-basket'}
                            {opcMountPoint id='opc_before_basket'}
                            <div class="basket_wrapper">
                                {block name='basket-index-basket-items'}
                                    {block name='basket-index-form-cart'}
                                        {form id="cart-form" method="post" action="{get_static_route id='warenkorb.php'}" class="evo-validate"}
                                            {input type="hidden" name="wka" value="1"}
                                            <div class="mb-7">
                                                {block name='basket-index-include-order-items'}
                                                    {include file='basket/cart_items.tpl'}
                                                {/block}
                                            </div>
                                            {block name='basket-index-include-uploads'}
                                                {include file='snippets/uploads.tpl' tplscope='basket'}
                                            {/block}
                                        {/form}
                                    {/block}

                                    {if $Einstellungen.kaufabwicklung.warenkorb_versandermittlung_anzeigen === 'Y'}
                                        {block name='basket-index-form-shipping-calc'}
                                            {opcMountPoint id='opc_before_shipping_calculator'}
                                            {form id="basket-shipping-estimate-form" method="post" action="{get_static_route id='warenkorb.php'}"}
                                                {block name='basket-index-include-shipping-calculator'}
                                                    {include file='snippets/shipping_calculator.tpl' checkout=true}
                                                {/block}
                                            {/form}
                                        {/block}
                                    {/if}
                                    {if $oArtikelGeschenk_arr|@count > 0}
                                        {block name='basket-index-freegifts-content'}
                                            {$selectedFreegift=0}
                                            {foreach $smarty.session.Warenkorb->PositionenArr as $oPosition}
                                                {if $oPosition->nPosTyp == $C_WARENKORBPOS_TYP_GRATISGESCHENK}
                                                    {$selectedFreegift=$oPosition->Artikel->kArtikel}
                                                {/if}
                                            {/foreach}
                                            {row id="freegift"}
                                                {col cols=12}
                                                    {block name='basket-index-freegifts-heading'}
                                                        <h3 class="mb-4">{lang key='freeGiftFromOrderValueBasket'}</h3>
                                                    {/block}
                                                {/col}
                                                {col cols=12}
                                                    {block name='basket-index-form-freegift'}
                                                        {form method="post" name="freegift" action="{get_static_route id='warenkorb.php'}" class="text-center"}
                                                            {row}
                                                                {block name='basket-index-freegifts'}
                                                                    {foreach $oArtikelGeschenk_arr as $oArtikelGeschenk}
                                                                        {col cols=12}
                                                                            <div class="freegift mb-4">
                                                                                <div class="custom-control custom-radio pl-0">
                                                                                    <input class="custom-control-input " type="radio" id="gift{$oArtikelGeschenk->kArtikel}" name="gratisgeschenk" value="{$oArtikelGeschenk->kArtikel}" onclick="submit();">
                                                                                    <label for="gift{$oArtikelGeschenk->kArtikel}" class="p-3 custom-control-label {if $selectedFreegift===$oArtikelGeschenk->kArtikel}badge-check{/if}">
                                                                                        {if $selectedFreegift===$oArtikelGeschenk->kArtikel}{badge class="badge-circle"}<i class="fas fa-check mx-auto"></i>{/badge}{/if}
                                                                                        {image src=$oArtikelGeschenk->Bilder[0]->cURLKlein class="image" fluid=true alt=$oArtikelGeschenk->cName}
                                                                                        <div class="caption">
                                                                                            <p class="small text-muted">{lang key='freeGiftFrom1'} {$oArtikelGeschenk->cBestellwert} {lang key='freeGiftFrom2'}</p>
                                                                                            <p>{$oArtikelGeschenk->cName}</p>
                                                                                        </div>
                                                                                    </label>
                                                                                </div>
                                                                            </div>
                                                                        {/col}
                                                                    {/foreach}
                                                                {/block}
                                                            {/row}
                                                            {block name='basket-index-freegifts-form-submit'}
                                                                {input type="hidden" name="gratis_geschenk" value="1"}
                                                                {input name="gratishinzufuegen" type="hidden" value="{lang key='addToCart'}"}
                                                            {/block}
                                                        {/form}
                                                    {/block}
                                                {/col}
                                            {/row}
                                        {/block}
                                    {/if}
                                {/block}

                                {if !empty($xselling->Kauf) && count($xselling->Kauf->Artikel) > 0}
                                    {block name='basket-index-basket-xsell'}
                                        {lang key='basketCustomerWhoBoughtXBoughtAlsoY' assign='panelTitle'}
                                        {block name='basket-index-include-product-slider'}
                                            {include file='snippets/product_slider.tpl' productlist=$xselling->Kauf->Artikel title=$panelTitle}
                                        {/block}
                                    {/block}
                                {/if}
                            </div>
                        {/block}
                    {else}
                        {block name='basket-index-cart-empty'}
                            {row}
                                {col class="text-center"}
                                    {block name='basket-index-alert-empty'}
                                        {alert variant="info" class="text-center mt-4 pt-2 pb-5"}
                                            {badge variant="light" class="bubble"}
                                                <i class="fas fa-shopping-cart"></i>
                                            {/badge}<br/>
                                            {lang key='emptybasket' section='checkout'}
                                        {/alert}
                                    {/block}
                                    {block name='basket-index-empty-continue-shopping'}
                                        {link href=$ShopURL class="btn btn-primary"}{lang key='continueShopping' section='checkout'}{/link}
                                    {/block}
                                {/col}
                            {/row}
                        {/block}
                    {/if}
                {/col}
                {/block}
                {if ($Warenkorb->PositionenArr|@count > 0)}
                    {block name='basket-index-side'}
                    {col class='ml-auto' cols=12 lg=4}
                        <div class="sticky-top cart-summary">
                            {block name='basket-index-side-heading'}
                                <div class="h2 mb-4">{lang key="orderOverview" section="account data"}</div>
                            {/block}
                            {if $Einstellungen.kaufabwicklung.warenkorb_kupon_anzeigen === 'Y' && $KuponMoeglich == 1}
                                {block name='basket-index-coupon'}
                                    {card class='card-gray' no-body=true}
                                        {cardheader}
                                        {block name='basket-index-coupon-heading'}
                                            <span class="font-weight-bold font-size-lg" data-toggle="collapse" data-target="#coupon-form">{lang key='useCoupon' section='checkout'}</span>
                                        {/block}
                                        {/cardheader}
                                        {collapse id="coupon-form"}
                                            {cardbody}
                                            {block name='basket-index-coupon-form'}
                                                {form class="form-inline evo-validate" id="basket-coupon-form" method="post" action="{get_static_route id='warenkorb.php'}"}
                                                    {formgroup class="mw-100{if !empty($invalidCouponCode)} has-error{/if}"}
                                                    {inputgroup}
                                                        {input aria=["label"=>"{lang key='couponCode' section='account data'}"] type="text" name="Kuponcode" id="couponCode" maxlength="32" placeholder="{lang key='couponCode' section='account data'}" required=true}
                                                        {button type="submit" value=1}{lang key='useCoupon' section='checkout'}{/button}
                                                    {/inputgroup}
                                                    {/formgroup}
                                                {/form}
                                            {/block}
                                            {/cardbody}
                                        {/collapse}
                                    {/card}
                                {/block}
                            {/if}
                            {card class="card-gray mt-4"}
                                {block name='basket-index-price-tax'}
                                    {if $NettoPreise}
                                        {block name='basket-index-price-net'}
                                            {row class="total-net"}
                                                {col class="text-left" cols=7}
                                                    <span class="price_label"><strong>{lang key='totalSum'} ({lang key='net'}):</strong></span>
                                                {/col}
                                                {col class="text-right price-col" cols=5}
                                                    <strong class="price total-sum">{$WarensummeLocalized[$NettoPreise]}</strong>
                                                {/col}
                                            {/row}
                                        {/block}
                                    {/if}

                                    {if $Einstellungen.global.global_steuerpos_anzeigen !== 'N' && $Steuerpositionen|@count > 0}
                                        {block name='basket-index-tax'}
                                            {foreach $Steuerpositionen as $Steuerposition}
                                                {row class="tax"}
                                                    {col class="text-left" cols=7}
                                                        <span class="tax_label">{$Steuerposition->cName}:</span>
                                                    {/col}
                                                    {col class="text-right price-col" cols=5}
                                                        <span class="tax_label">{$Steuerposition->cPreisLocalized}</span>
                                                    {/col}
                                                {/row}
                                            {/foreach}
                                        {/block}
                                    {/if}

                                    {if isset($smarty.session.Bestellung->GuthabenNutzen) && $smarty.session.Bestellung->GuthabenNutzen == 1}
                                        {block name='basket-index-credit'}
                                            {row class="customer-credit"}
                                                {col class="text-left" cols=7}
                                                    {lang key='useCredit' section='account data'}
                                                {/col}
                                                {col class="text-right" cols=5}
                                                    {$smarty.session.Bestellung->GutscheinLocalized}
                                                {/col}
                                            {/row}
                                        {/block}
                                    {/if}
                                    {block name='basket-index-price-sticky'}
                                        {row class="total border-top mt-3 pt-3 font-size-lg"}
                                            {col class="text-left" cols=7}
                                                <span class="price_label">{lang key='totalSum'}:</span>
                                            {/col}
                                            {col class="text-right price-col" cols=5}
                                                <strong class="total-sum">{$WarensummeLocalized[0]}</strong>
                                            {/col}
                                        {/row}
                                    {/block}
                                {/block}
                                {block name='basket-index-shipping'}
                                    {if isset($FavourableShipping)}
                                        {if $NettoPreise}
                                            {$shippingCosts = "`$FavourableShipping->cPriceLocalized[$NettoPreise]` {lang key='plus' section='basket'} {lang key='vat' section='productDetails'}"}
                                        {else}
                                            {$shippingCosts = $FavourableShipping->cPriceLocalized[$NettoPreise]}
                                        {/if}
                                        {row class="shipping-costs"}
                                            {col cols=12}
                                                <small>{lang|sprintf:$oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND]->getURL():$shippingCosts:$FavourableShipping->cCountryCode key='shippingInformationSpecific' section='basket'}</small>
                                            {/col}
                                        {/row}
                                    {elseif empty($FavourableShipping) && empty($smarty.session.Versandart)}
                                        {row class="shipping-costs"}
                                            {col cols=12}
                                                <small>{lang|sprintf:$oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND]->getURL() key='shippingInformation' section='basket'}</small>
                                            {/col}
                                        {/row}
                                    {/if}
                                {/block}
                                {block name='basket-index-proceed-button'}
                                    {link href="{get_static_route id='bestellvorgang.php'}?wk=1" class="btn btn-primary w-100 mt-3"}{lang key='nextStepCheckout' section='checkout'}{/link}
                                {/block}
                            {/card}
                            {if !empty($WarenkorbVersandkostenfreiHinweis) && $Warenkorb->PositionenArr|@count > 0}
                                {block name='basket-index-alert'}
                                    {row class="mt-5"}
                                        {col cols=1}<i class="fas fa-truck"></i>{/col}
                                        {col cols=10 class="basket_notice"}{$WarenkorbVersandkostenfreiHinweis}{/col}
                                    {/row}
                                {/block}
                            {/if}
                        </div>
                    {/col}
                    {/block}
                {/if}
            {/row}
        {/container}
    {/block}

    {block name='basket-index-include-footer'}
        {include file='layout/footer.tpl'}
    {/block}
{/block}
