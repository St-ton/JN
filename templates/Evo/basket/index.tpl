{block name='header'}
    {include file='layout/header.tpl'}
{/block}

{block name='content'}
    {opcMountPoint id='opc_before_heading'}

    <h1>{lang key='basket' section='global'}</h1>

    {include file='snippets/extension.tpl'}

    {if !empty($WarenkorbVersandkostenfreiHinweis) && $Warenkorb->PositionenArr|@count > 0}
        <div class="alert alert-info">
            <span class="basket_notice">{$WarenkorbVersandkostenfreiHinweis}</span>
        </div>
    {/if}

    {if ($Warenkorb->PositionenArr|@count > 0)}
        {block name='basket'}
            {opcMountPoint id='opc_before_basket'}
            <div class="basket_wrapper">
                <div class="basket-well">
                    {block name='basket-items'}
                        <form id="cart-form" method="post" action="{get_static_route id='warenkorb.php'}">
                            {$jtl_token}
                            <input type="hidden" name="wka" value="1" />
                            {include file='checkout/inc_order_items.tpl' tplscope='cart'}
                            {include file='productdetails/uploads.tpl'}
                        </form>
                        <div class="next-actions row">
                            {assign var='showCoupon' value=false}
                            {if $Einstellungen.kaufabwicklung.warenkorb_kupon_anzeigen === 'Y' && $KuponMoeglich == 1}
                                {assign var='showCoupon' value=true}
                                <div class="apply-coupon col-sm-6 col-lg-4">
                                    <form class="form-inline evo-validate" id="basket-coupon-form" method="post" action="{get_static_route id='warenkorb.php'}">
                                        {$jtl_token}
                                        {block name='basket-coupon'}
                                            <div class="form-group{if !empty($invalidCouponCode)} has-error{/if}">
                                                <p class="input-group">
                                                    <input aria-label="{lang key='couponCode' section='account data'}" class="form-control" type="text" name="Kuponcode" id="couponCode" maxlength="32" placeholder="{lang key='couponCode' section='account data'}" required/>
                                                    <span class="input-group-btn">
                                                        <input class="btn btn-default" type="submit" value="{lang key='useCoupon' section='checkout'}" />
                                                    </span>
                                                </p>
                                            </div>
                                        {/block}
                                    </form>
                                </div>
                            {/if}
                            <div class="proceed col-xs-12 text-right{if $showCoupon} col-sm-6 col-lg-8{/if}">
                                <a href="{get_static_route id='bestellvorgang.php'}?wk=1" class="submit btn btn-primary btn-lg pull-right bottom15">{lang key='nextStepCheckout' section='checkout'}</a>
                            </div>
                        </div>
                    {/block}
                </div>
                <hr>
                {if $Einstellungen.kaufabwicklung.warenkorb_versandermittlung_anzeigen === 'Y'}
                    {opcMountPoint id='opc_before_shipping_calculator'}
                    <form id="basket-shipping-estimate-form" method="post" action="{get_static_route id='warenkorb.php'}">
                        {$jtl_token}
                        {include file='snippets/shipping_calculator.tpl' checkout=true}
                    </form>
                {/if}

                {if $oArtikelGeschenk_arr|@count > 0}
                    {block name='basket-freegift'}
                        <div id="freegift" class="panel panel-info">
                            <div class="panel-heading"><div class="panel-title">{block name='basket-freegift-title'}{lang key='freeGiftFromOrderValueBasket'}{/block}</div></div>
                            <div class="panel-body">
                                {block name='basket-freegift-body'}
                                    <form method="post" name="freegift" action="{get_static_route id='warenkorb.php'}">
                                        {$jtl_token}
                                        <div class="row row-eq-height">
                                            {foreach $oArtikelGeschenk_arr as $oArtikelGeschenk}
                                                <div class="col-sm-6 col-md-4 text-center">
                                                    <label class="thumbnail" for="gift{$oArtikelGeschenk->kArtikel}">
                                                        <img src="{$oArtikelGeschenk->Bilder[0]->cURLKlein}" class="image" />
                                                        <div class="caption">
                                                            <p class="small text-muted">{lang key='freeGiftFrom1'} {$oArtikelGeschenk->cBestellwert} {lang key='freeGiftFrom2'}</p>
                                                            <p>{$oArtikelGeschenk->cName}</p>
                                                            <input name="gratisgeschenk" type="radio" value="{$oArtikelGeschenk->kArtikel}" id="gift{$oArtikelGeschenk->kArtikel}" />
                                                        </div>
                                                    </label>
                                                </div>
                                            {/foreach}
                                        </div>{* /row *}
                                        <div class="text-center">
                                            <input type="hidden" name="gratis_geschenk" value="1" />
                                            <input name="gratishinzufuegen" type="submit" value="{lang key='addToCart'}" class="submit btn btn-primary" />
                                        </div>
                                    </form>
                                {/block}
                            </div>
                        </div>
                    {/block}
                {/if}

                {if !empty($xselling->Kauf) && count($xselling->Kauf->Artikel) > 0}
                    {block name='basket-xsell'}
                        {lang key='basketCustomerWhoBoughtXBoughtAlsoY' section='global' assign='panelTitle'}
                        {include file='snippets/product_slider.tpl' productlist=$xselling->Kauf->Artikel title=$panelTitle}
                    {/block}
                {/if}
            </div>
        {/block}
    {else}
        <div class="alert alert-info">{lang key='emptybasket' section='checkout'}</div>
        <a href="{$ShopURL}" class="submit btn btn-primary">{lang key='continueShopping' section='checkout'}</a>
    {/if}
{/block}

{block name='footer'}
    {include file='layout/footer.tpl'}
{/block}
