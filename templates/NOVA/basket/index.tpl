{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='basket-index'}
    {block name='basket-index-include-header'}
        {include file='layout/header.tpl'}
    {/block}

    {block name='basket-index-content'}
        {block name='basket-index-heading'}
            {include file='snippets/opc_mount_point.tpl' id='opc_before_heading'}
            <h1>{lang key='basket'}</h1>
        {/block}
        {block name='basket-index-include-extension'}
            {include file='snippets/extension.tpl'}
        {/block}

        {if !empty($WarenkorbVersandkostenfreiHinweis) && $Warenkorb->PositionenArr|@count > 0}
            {block name='basket-index-alert'}
                {alert variant="info"}
                    <span class="basket_notice">{$WarenkorbVersandkostenfreiHinweis}</span>
                {/alert}
            {/block}
        {/if}

        {if ($Warenkorb->PositionenArr|@count > 0)}
            {block name='basket-index-basket'}
                {include file='snippets/opc_mount_point.tpl' id='opc_before_basket'}
                <div class="basket_wrapper">
                    {block name='basket-index-basket-items'}
                        {block name='basket-index-form-cart'}
                            {form id="cart-form" method="post" action="{get_static_route id='warenkorb.php'}" class="evo-validate"}
                                {input type="hidden" name="wka" value="1"}
                                <div class="mb-7">
                                    {block name='basket-index-include-order-items'}
                                        {include file='checkout/inc_order_items.tpl' tplscope='cart'}
                                    {/block}
                                </div>
                                {block name='basket-index-include-uploads'}
                                    {include file='snippets/uploads.tpl' tplscope='basket'}
                                {/block}
                            {/form}
                        {/block}

                        {if $Einstellungen.kaufabwicklung.warenkorb_versandermittlung_anzeigen === 'Y'}
                            {block name='basket-index-form-shipping-calc'}
                                {include file='snippets/opc_mount_point.tpl' id='opc_before_shipping_calculator'}
                                {form id="basket-shipping-estimate-form" method="post" action="{get_static_route id='warenkorb.php'}"}
                                    {block name='basket-index-include-shipping-calculator'}
                                        {include file='snippets/shipping_calculator.tpl' checkout=true}
                                    {/block}
                                {/form}
                            {/block}
                        {/if}
                        {if $Einstellungen.kaufabwicklung.warenkorb_kupon_anzeigen === 'Y' && $KuponMoeglich == 1}
                            {block name='basket-index-coupon'}
                                {row class="apply-coupon"}
                                    {col cols=12 md=4 class="text-left"}
                                        {block name='basket-index-coupon-heading'}
                                        <p>
                                            <strong class="mb-2">{lang key='couponCode' section='account data'}:</strong>
                                        </p>
                                        {/block}
                                    {/col}
                                    {col cols=12 md=8}
                                        {block name='basket-index-form-coupon'}
                                            {form class="form-inline evo-validate" id="basket-coupon-form" method="post" action="{get_static_route id='warenkorb.php'}"}
                                                {block name='basket-coupon'}
                                                    {formgroup class="{if !empty($invalidCouponCode)} has-error{/if}"}
                                                        {inputgroup}
                                                            {input aria=["label"=>"{lang key='couponCode' section='account data'}"] type="text" name="Kuponcode" id="couponCode" maxlength="32" placeholder="{lang key='couponCode' section='account data'}" required=true}
                                                            {button type="submit" value=1}{lang key='useCoupon' section='checkout'}{/button}
                                                        {/inputgroup}
                                                    {/formgroup}
                                                {/block}
                                            {/form}
                                        {/block}
                                    {/col}
                                {/row}
                                <hr class="my-4">
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
                                        <p>
                                            <strong class="mb-2">{lang key='freeGiftFromOrderValueBasket'}</strong>
                                        </p>
                                    {/col}
                                    {col cols=12}
                                        {block name='basket-index-form-freegift'}
                                            {form method="post" name="freegift" action="{get_static_route id='warenkorb.php'}" class="text-center"}
                                                {row}
                                                    {block name='basket-index-freegifts'}
                                                        {foreach $oArtikelGeschenk_arr as $oArtikelGeschenk}
                                                                {col cols=6 md=4}
                                                                    <div class="freegift mb-4">
                                                                        <div class="custom-control custom-radio pl-0">
                                                                            <input class="custom-control-input " type="radio" id="gift{$oArtikelGeschenk->kArtikel}" name="gratisgeschenk" value="{$oArtikelGeschenk->kArtikel}" onclick="submit();">
                                                                            <label for="gift{$oArtikelGeschenk->kArtikel}" class="custom-control-label">
                                                                                {if $selectedFreegift===$oArtikelGeschenk->kArtikel}<div class="text-success text-right"><i class="fa fa-check"></i></div>{/if}
                                                                                {image src=$oArtikelGeschenk->Bilder[0]->cURLKlein class="image" alt=$oArtikelGeschenk->cName}
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
                                <hr class="my-4">
                            {/block}
                        {/if}
                        {block name='basket-index-proceed-button'}
                            {row class="mb-7"}
                                {col cols=12 class="proceed text-right"}
                                    {link href="{get_static_route id='bestellvorgang.php'}?wk=1" class="btn btn-primary btn-lg float-right mb-3"}{lang key='nextStepCheckout' section='checkout'}{/link}
                                {/col}
                            {/row}
                        {/block}
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
                        {link href=$ShopURL class="btn btn-primary"}{lang key='continueShopping' section='checkout'}{/link}
                    {/col}
                {/row}
            {/block}
        {/if}
    {/block}

    {block name='basket-index-include-footer'}
        {include file='layout/footer.tpl'}
    {/block}
{/block}
