{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='header'}
    {include file='layout/header.tpl'}
{/block}

{block name='content'}
    <h1>{lang key='basket'}</h1>
    
    {include file='snippets/extension.tpl'}
    
    {if !empty($WarenkorbVersandkostenfreiHinweis) && $Warenkorb->PositionenArr|@count > 0}
        {alert variant="info"}
            <span class="basket_notice">{$WarenkorbVersandkostenfreiHinweis}</span>
        {/alert}
    {/if}
    
    {if ($Warenkorb->PositionenArr|@count > 0)}
        {block name='basket'}
            <div class="basket_wrapper">
                {block name='basket-items'}
                    {form id="cart-form" method="post" action="{get_static_route id='warenkorb.php'}" class="evo-validate"}
                        {$jtl_token}
                        {input type="hidden" name="wka" value="1"}
                        <div class="mb-7">
                            {include file='checkout/inc_order_items.tpl' tplscope='cart'}
                        </div>
                        {include file='snippets/uploads.tpl' tplscope='basket'}
                    {/form}

                    {if $Einstellungen.kaufabwicklung.warenkorb_versandermittlung_anzeigen === 'Y'}
                        {form id="basket-shipping-estimate-form" method="post" action="{get_static_route id='warenkorb.php'}"}
                        {$jtl_token}
                            {include file='snippets/shipping_calculator.tpl' checkout=true}
                        {/form}
                    {/if}
                    {if $Einstellungen.kaufabwicklung.warenkorb_kupon_anzeigen === 'Y' && $KuponMoeglich == 1}
                        {row class="apply-coupon"}
                            {col cols=12 md=4 class="text-left"}
                                <p>
                                    <strong class="mb-2">{lang key='couponCode' section='account data'}:</strong>
                                </p>
                            {/col}
                            {col cols=12 md=8}
                                {form class="form-inline evo-validate" id="basket-coupon-form" method="post" action="{get_static_route id='warenkorb.php'}"}
                                    {$jtl_token}
                                    {block name='basket-coupon'}
                                        {formgroup class="{if !empty($invalidCouponCode)} has-error{/if}"}
                                            {inputgroup}
                                                {input aria=["label"=>"{lang key='couponCode' section='account data'}"] class="form-control" type="text" name="Kuponcode" id="couponCode" maxlength="32" placeholder="{lang key='couponCode' section='account data'}" required=true}
                                                {input class="btn btn-secondary" type="submit" value="{lang key='useCoupon' section='checkout'}"}
                                            {/inputgroup}
                                        {/formgroup}
                                    {/block}
                                {/form}
                            {/col}
                        {/row}
                        <hr class="my-4">
                    {/if}
                    {if $oArtikelGeschenk_arr|@count > 0}
                        {block name='basket-freegift'}
                            {$selectedFreegift=0}
                            {foreach $smarty.session.Warenkorb->PositionenArr as $oPosition}
                                {if $oPosition->nPosTyp == $C_WARENKORBPOS_TYP_GRATISGESCHENK}
                                    {$selectedFreegift=$oPosition->Artikel->kArtikel}
                                {/if}
                            {/foreach}
                            {row id="freegift"}
                                {block name='basket-freegift-title'}
                                    {col cols=12}
                                        <p>
                                            <strong class="mb-2">{lang key='freeGiftFromOrderValueBasket'}</strong>
                                        </p>
                                    {/col}
                                {/block}
                                {block name='basket-freegift-body'}
                                    {col cols=12}
                                        {form method="post" name="freegift" action="{get_static_route id='warenkorb.php'}" class="text-center"}
                                            {$jtl_token}
                                            {row}
                                                {foreach $oArtikelGeschenk_arr as $oArtikelGeschenk}
                                                    {col cols=6 md=4}
                                                        <div class="freegift mb-4">
                                                            <div class="custom-control custom-radio pl-0">
                                                                <input class="custom-control-input " type="radio" id="gift{$oArtikelGeschenk->kArtikel}" name="gratisgeschenk" value="{$oArtikelGeschenk->kArtikel}" onclick="submit();">
                                                                <label for="gift{$oArtikelGeschenk->kArtikel}" class="custom-control-label">
                                                                    {if $selectedFreegift===$oArtikelGeschenk->kArtikel}<div class="text-success text-right"><i class="fa fa-check"></i></div>{/if}
                                                                    {image src="{$oArtikelGeschenk->Bilder[0]->cURLKlein}" class="image"}
                                                                    <div class="caption">
                                                                        <p class="small text-muted">{lang key='freeGiftFrom1'} {$oArtikelGeschenk->cBestellwert} {lang key='freeGiftFrom2'}</p>
                                                                        <p>{$oArtikelGeschenk->cName}</p>
                                                                    </div>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    {/col}
                                                {/foreach}
                                            {/row}
                                            {input type="hidden" name="gratis_geschenk" value="1"}
                                            {input name="gratishinzufuegen" type="hidden" value="{lang key='addToCart'}"}
                                        {/form}
                                    {/col}
                                {/block}
                            {/row}
                            <hr class="my-4">
                        {/block}
                    {/if}
                    {row class="mb-7"}
                        {col cols=12 class="proceed text-right"}
                            {link href="{get_static_route id='bestellvorgang.php'}?wk=1" class="submit btn btn-primary btn-lg float-right mb-3"}{lang key='nextStepCheckout' section='checkout'}{/link}
                        {/col}
                    {/row}
                {/block}

                {if !empty($xselling->Kauf) && count($xselling->Kauf->Artikel) > 0}
                    {block name='basket-xsell'}
                        {lang key='basketCustomerWhoBoughtXBoughtAlsoY' assign='panelTitle'}
                        {include file='snippets/product_slider.tpl' productlist=$xselling->Kauf->Artikel title=$panelTitle}
                    {/block}
                {/if}
            </div>
        {/block}
    {else}
        {row}
            {col class="text-center"}
                {alert variant="info" class="text-center mt-4 pt-2 pb-5"}
                    {badge variant="light" class="bubble"}
                        <i class="fas fa-shopping-cart"></i>
                    {/badge}<br/>
                    {lang key='emptybasket' section='checkout'}
                {/alert}
                {link href="{$ShopURL}" class="btn btn-primary"}{lang key='continueShopping' section='checkout'}{/link}
            {/col}
        {/row}

    {/if}
{/block}

{block name='footer'}
    {include file='layout/footer.tpl'}
{/block}
