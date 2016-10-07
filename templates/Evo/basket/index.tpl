{include file='layout/header.tpl'}

<h1>{lang key="basket" section="global"}</h1>

{include file="snippets/extension.tpl"}

{if !empty($WarenkorbVersandkostenfreiHinweis) && $Warenkorb->PositionenArr|@count > 0}
    <div class="alert alert-info">
        <span class="basket_notice">{$WarenkorbVersandkostenfreiHinweis} {$WarenkorbVersandkostenfreiLaenderHinweis|lcfirst}</span>
    </div>
{/if}
{if $Schnellkaufhinweis}
    <div class="alert alert-info">{$Schnellkaufhinweis}</div>
{/if}

{if ($Warenkorb->PositionenArr|@count > 0)}
    {if count($Warenkorbhinweise)>0}
        <div class="alert alert-warning">
            {foreach name=hinweise from=$Warenkorbhinweise item=Warenkorbhinweis}
                {$Warenkorbhinweis}
                <br />
            {/foreach}
        </div>
    {/if}

    {if !empty($BestellmengeHinweis)}
        <div class="alert alert-warning">{$BestellmengeHinweis}</div>
    {/if}

    {if !empty($MsgWarning)}
        <p class="alert alert-danger">{$MsgWarning}</p>
    {/if}

    {if !empty($invalidCouponCode)}
        <p class="alert alert-danger">{lang key="invalidCouponCode" section="checkout"}</p>
    {elseif !empty($cKuponfehler)}
        <p class="alert alert-danger">{lang key="couponErr$cKuponfehler" section="global"}</p>
    {/if}
    {if $nVersandfreiKuponGueltig}
        <div class="alert alert-success">
            {lang key="couponSucc1" section="global"}
            {foreach name=lieferlaender from=$cVersandfreiKuponLieferlaender_arr item=cVersandfreiKuponLieferlaender}
                {$cVersandfreiKuponLieferlaender}{if !$smarty.foreach.lieferlaender.last}, {/if}
            {/foreach}
        </div>
    {/if}
    {block name="basket"}
        <div class="basket_wrapper">
            {if $Schnellkaufhinweis}
                <div class="alert alert-info">{$Schnellkaufhinweis}</div>
            {/if}
            <div class="panel-wrap basket-well">
                {block name="basket-items"}
                    <form id="cart-form" method="post" action="{get_static_route id='warenkorb.php'}">
                        {$jtl_token}
                        <input type="hidden" name="wka" value="1" />
                        {include file='checkout/inc_order_items.tpl' tplscope='cart'}
                        {include file="productdetails/uploads.tpl"}
                    </form>
                    <div class="next-actions row">
                        {assign var="showCoupon" value=false}
                        {if $Einstellungen.kaufabwicklung.warenkorb_kupon_anzeigen === 'Y' && $KuponMoeglich == 1}
                            {assign var="showCoupon" value=true}
                            <div class="apply-coupon col-sm-6 col-lg-4">
                                <form class="form-inline" id="basket-coupon-form" method="post" action="{get_static_route id='warenkorb.php'}">
                                    {$jtl_token}
                                    {block name="basket-coupon"}
                                        <div class="form-group{if !empty($invalidCouponCode) || !empty($cKuponfehler)} has-error{/if}">
                                            <p class="input-group">
                                                <input class="form-control" type="text" name="Kuponcode" id="couponCode" maxlength="32" placeholder="{lang key="couponCode" section="account data"}" />
                                                <span class="input-group-btn">
                                                    <input class="btn btn-default" type="submit" value="{lang key="useCoupon" section="checkout"}" />
                                                </span>
                                            </p>
                                        </div>
                                    {/block}
                                </form>
                            </div>
                        {/if}
                        <div class="proceed col-xs-12{if $showCoupon} col-sm-6 col-lg-8{/if}">
                            <a href="{get_static_route id='bestellvorgang.php'}?wk=1" class="submit btn btn-primary btn-lg pull-right">{lang key="nextStepCheckout" section="checkout"}</a>
                        </div>
                    </div>
                {/block}
            </div>
            <hr>
            {if $Einstellungen.kaufabwicklung.warenkorb_versandermittlung_anzeigen === 'Y'}
                <form id="basket-shipping-estimate-form" method="post" action="{get_static_route id='warenkorb.php'}">
                    {$jtl_token}
                    {block name="basket-shipping-estimate"}
                        {if !isset($Versandarten) || !$Versandarten}
                            {block name="basket-shipping-estimate-form"}
                                <div class="panel panel-default" id="basket-shipping-estimate-form">
                                    <div class="panel-heading">
                                        <div class="panel-title">
                                            {block name="basket-shipping-estimate-form-title"}{lang key="estimateShippingCostsTo" section="checkout"}{/block}
                                        </div>
                                    </div>
                                    <div class="panel-body">
                                        {block name="basket-shipping-estimate-form-body"}
                                            <div class="row">
                                                <div class="col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3">
                                                    <div class="form-inline">
                                                        <label for="country">{lang key="country" section="account data"}</label>
                                                        <select name="land" id="country" class="form-control">
                                                            {foreach name=land from=$laender item=land}
                                                                <option value="{$land->cISO}" {if ($Einstellungen.kunden.kundenregistrierung_standardland==$land->cISO && (!isset($smarty.session.Kunde->cLand) || !$smarty.session.Kunde->cLand)) || (isset($smarty.session.Kunde->cLand) && $smarty.session.Kunde->cLand==$land->cISO)}selected{/if}>{$land->cName}</option>
                                                            {/foreach}
                                                        </select>
                                                        &nbsp;
                                                        <label class="sr-only" for="plz">{lang key="plz" section="forgot password"}</label>
                                                        <span class="input-group">
                                                            <input type="text" name="plz" size="8" maxlength="8" value="{if isset($smarty.session.Kunde->cPLZ)}{$smarty.session.Kunde->cPLZ}{/if}" id="plz" class="form-control" placeholder="{lang key="plz" section="forgot password"}">
                                                            <span class="input-group-btn">
                                                                <button name="versandrechnerBTN" class="btn btn-default" type="submit">{lang key="estimateShipping" section="checkout"}</button>
                                                            </span>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        {/block}
                                    </div>
                                </div>
                            {/block}
                        {else}
                            {block name="basket-shipping-estimated"}
                                <div class="panel panel-default" id="basket-shipping-estimated">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">{block name="basket-shipping-estimated-title"}{lang key="estimateShippingCostsTo" section="checkout"} {$Versandland}, {lang key="plz" section="forgot password"} {$VersandPLZ}{/block}</h4>
                                    </div>
                                    <div class="panel-body">
                                        {block name="basket-shipping-estimated-body"}
                                            {if !empty($Versandarten)}
                                                <table class="table table-striped">
                                                    <tbody>
                                                        {foreach name=versand from=$Versandarten item=versandart}
                                                                <tr class="shipment">
                                                                    <td>
                                                                        {if !empty($versandart->cBild)}
                                                                            <img src="{$versandart->cBild}" alt="{$versandart->angezeigterName|trans}" />
                                                                        {else}
                                                                            {$versandart->angezeigterName|trans}
                                                                        {/if}
                                                                    </td>
                                                                    <td class="text-right">{$versandart->cPreisLocalized}</td>
                                                                </tr>
                                                            {if isset($versandart->specificShippingcosts_arr)}
                                                                {foreach name=specificShippingcosts from=$versandart->specificShippingcosts_arr item=specificShippingcosts}
                                                                    <tr class="shipment shipment-specific">
                                                                        <td>{$specificShippingcosts->cName|trans}</td>
                                                                        <td class="text-right">{$specificShippingcosts->cPreisLocalized}</td>
                                                                    </tr>
                                                                {/foreach}
                                                            {/if}
                                                            {if !empty($versandart->angezeigterHinweistext|trans) && $versandart->angezeigterHinweistext|has_trans}
                                                                <tr class="shipment-note">
                                                                    <td colspan="2">{$versandart->angezeigterHinweistext|trans}</td>
                                                                </tr>
                                                            {/if}
                                                            {if isset($versandart->Zuschlag) && $versandart->Zuschlag->fZuschlag != 0}
                                                                <tr class="shipment-surcharge">
                                                                    <td>{$versandart->Zuschlag->angezeigterName|trans}</td>
                                                                    <td>+{$versandart->Zuschlag->cPreisLocalized})</td>
                                                                </tr>
                                                            {/if}
                                                            {if !empty($versandart->cLieferdauer|trans) && $Einstellungen.global.global_versandermittlung_lieferdauer_anzeigen === 'Y'}
                                                                <tr class="shipment-deliverytime">
                                                                    <td colspan="2">{lang key="shippingTimeLP" section="global"}: {$versandart->cLieferdauer|trans}</td>
                                                                </tr>
                                                            {/if}
                                                        {/foreach}
                                                    </tbody>
                                                </table>
                                                <a href="{get_static_route id='warenkorb.php'}" class="btn btn-default">{lang key="newEstimation" section="checkout"}</a>
                                            {else}
                                                {lang key="noShippingAvailable" section="checkout"}
                                            {/if}
                                        {/block}
                                    </div>
                                </div>
                            {/block}
                            {if !empty($cErrorVersandkosten)}
                                <div class="alert alert-info">{$cErrorVersandkosten}</div>
                            {/if}
                        {/if}
                    {/block}
                </form>
            {/if}

            {if $oArtikelGeschenk_arr|@count > 0}
                {block name="basket-freegift"}
                    <div id="freegift" class="panel panel-info">
                        <div class="panel-heading">{block name="basket-freegift-title"}{lang key="freeGiftFromOrderValueBasket" section="global"}{/block}</div>
                        <div class="panel-body">
                            {block name="basket-freegift-body"}
                                <form method="post" name="freegift" action="{get_static_route id='warenkorb.php'}">
                                    {$jtl_token}
                                    <div class="row row-eq-height">
                                        {foreach name=gratisgeschenke from=$oArtikelGeschenk_arr item=oArtikelGeschenk}
                                            <div class="col-sm-6 col-md-4 text-center">
                                                <label class="thumbnail" for="gift{$oArtikelGeschenk->kArtikel}">
                                                    <img src="{$oArtikelGeschenk->Bilder[0]->cPfadKlein}" class="image" />

                                                    <span class="small text-muted">{lang key="freeGiftFrom1" section="global"} {$oArtikelGeschenk->cBestellwert} {lang key="freeGiftFrom2" section="global"}</span>
                                                    <br />
                                                    <span>{$oArtikelGeschenk->cName}</span>
                                                    <br />
                                                    <input name="gratisgeschenk" type="radio" value="{$oArtikelGeschenk->kArtikel}" id="gift{$oArtikelGeschenk->kArtikel}" />
                                                </label>
                                            </div>
                                        {/foreach}
                                    </div>{* /row *}
                                    <div class="text-center">
                                        <input type="hidden" name="gratis_geschenk" value="1" />
                                        <input name="gratishinzufuegen" type="submit" value="{lang key="addToCart" section="global"}" class="submit btn btn-primary" />
                                    </div>
                                </form>
                            {/block}
                        </div>
                    </div>
                {/block}
            {/if}

            {if !empty($xselling->Kauf) && count($xselling->Kauf->Artikel) > 0}
                {lang key="basketCustomerWhoBoughtXBoughtAlsoY" section="global" assign="panelTitle"}
                {include file='snippets/product_slider.tpl' productlist=$xselling->Kauf->Artikel title=$panelTitle}
            {/if}
        </div>
    {/block}
{else}
    <a href="{$ShopURL}" class="submit btn btn-primary">{lang key="continueShopping" section="checkout"}</a>
{/if}

{include file='layout/footer.tpl'}