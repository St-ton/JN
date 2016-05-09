{include file='layout/header.tpl'}

<h1>{$Warenkorbtext}</h1>

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
            <form id="cart-form" method="post" action="warenkorb.php">
                {$jtl_token}
                <input type="hidden" name="wka" value="1" />
                {if $Schnellkaufhinweis}
                    <div class="alert alert-info">{$Schnellkaufhinweis}</div>
                {/if}
                {block name="basket-note"}
                    <div class="well panel-wrap basket-well">
                        <div class="panel panel-default">
                            <div class="panel-body">
                                {include file='checkout/inc_order_items.tpl' tplscope='cart'}
                                {include file="productdetails/uploads.tpl"}

                                <div class="panel-note">
                                    <a href="bestellvorgang.php?wk=1" class="submit btn btn-primary pull-right">{lang key="nextStepCheckout" section="checkout"}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                {/block}

            </form>

            <form id="basket-coupon-form" method="post" action="warenkorb.php">
                {$jtl_token}
                {if $Einstellungen.kaufabwicklung.warenkorb_kupon_anzeigen === 'Y' && $KuponMoeglich == 1}
                    {block name="basket-coupon"}
                        <div id="coupon" class="panel panel-default">
                            <div class="panel-heading"><h4 class="panel-title">{lang key="useCoupon" section="checkout"}</h4>
                            </div>
                            <div class="panel-body">
                                <div class="input-group col-xs-12 col-md-8 col-lg-6 col-xl-4">
                                    <input class="form-control" type="text" name="Kuponcode" id="couponCode" maxlength="20" placeholder="{lang key="couponCode" section="account data"}" />
                                    <span class="input-group-btn">
                                        <input class="btn btn-default" type="submit" value="{lang key="useCoupon" section="checkout"}" />
                                    </span>
                                </div>
                            </div>
                        </div>
                    {/block}
                {/if}
            </form>

            <form id="basket-shipping-estimate-form" method="post" action="warenkorb.php">
                {$jtl_token}
                {if $Einstellungen.kaufabwicklung.warenkorb_versandermittlung_anzeigen === 'Y'}
                    {block name="basket-shipping-estimate"}

                        {if !isset($Versandarten) || !$Versandarten}
                            {block name="basket-shipping-estimate-form"}
                                <div class="panel panel-default" id="basket-shipping-estimate-form">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">{block name="basket-shipping-estimate-form-title"}{lang key="estimateShippingCostsTo" section="checkout"}{/block}</h4>
                                    </div>
                                    <div class="panel-body form-inline">
                                        {block name="basket-shipping-estimate-form-body"}
                                            <label for="country">{lang key="country" section="account data"}</label>
                                            <select name="land" id="country" class="form-control">
                                                {foreach name=land from=$laender item=land}
                                                    <option value="{$land->cISO}" {if ($Einstellungen.kunden.kundenregistrierung_standardland==$land->cISO && (!isset($smarty.session.Kunde->cLand) || !$smarty.session.Kunde->cLand)) || (isset($smarty.session.Kunde->cLand) && $smarty.session.Kunde->cLand==$land->cISO)}selected{/if}>{$land->cName}</option>
                                                {/foreach}
                                            </select>
                                            &nbsp;
                                            <label for="plz">{lang key="plz" section="forgot password"}</label>
                                            <span class="input-group">
                                                <input type="text" name="plz" maxlength="20" value="{if isset($smarty.session.Kunde->cPLZ)}{$smarty.session.Kunde->cPLZ}{/if}" id="plz" class="form-control" />
                                                <span class="input-group-btn">
                                                    <button name="versandrechnerBTN" class="btn btn-default" type="submit">{lang key="estimateShipping" section="checkout"}</button>
                                                </span>
                                            </span>
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
                                            {if count($ArtikelabhaengigeVersandarten)>0}
                                                <strong>{lang key="productShippingDesc" section="checkout"}:</strong>
                                                <table class="table table-striped">
                                                    {foreach name=artikelversandliste from=$ArtikelabhaengigeVersandarten item=artikelversand}
                                                        <tr>
                                                            <td>{$artikelversand->cName|trans}</td>
                                                            <td class="text-right"><strong>{$artikelversand->cPreisLocalized}</strong>
                                                            </td>
                                                        </tr>
                                                    {/foreach}
                                                </table>
                                            {/if}

                                            {if !empty($Versandarten)}
                                                <table class="table table-striped">
                                                    {foreach name=versand from=$Versandarten item=versandart}
                                                        <tr id="shipment_{$versandart->kVersandart}">
                                                            <td>
                                                                {if $versandart->cBild}
                                                                    <img src="{$versandart->cBild}" alt="{$versandart->angezeigterName|trans}">
                                                                {else}
                                                                    {$versandart->angezeigterName|trans}
                                                                {/if}
                                                                {if $versandart->angezeigterHinweistext|trans}
                                                                    <p>
                                                                        <small>{$versandart->angezeigterHinweistext|trans}</small>
                                                                    </p>
                                                                {/if}
                                                                {if isset($versandart->Zuschlag) && $versandart->Zuschlag->fZuschlag != 0}
                                                                    <p>
                                                                        <small>{$versandart->Zuschlag->angezeigterName|trans}
                                                                            (+{$versandart->Zuschlag->cPreisLocalized})
                                                                        </small>
                                                                    </p>
                                                                {/if}
                                                                {if $versandart->cLieferdauer|trans && $Einstellungen.global.global_versandermittlung_lieferdauer_anzeigen === 'Y'}
                                                                    <p>
                                                                        <small>{lang key="shippingTimeLP" section="global"}: {$versandart->cLieferdauer|trans}</small>
                                                                    </p>
                                                                {/if}
                                                            </td>
                                                            <td class="text-right">
                                                                {if $versandart->fEndpreis == 0}
                                                                    <strong>{lang key="freeshipping" section="global"}</strong>
                                                                {else}
                                                                    <strong>{$versandart->cPreisLocalized}</strong>
                                                                {/if}
                                                            </td>
                                                        </tr>
                                                    {/foreach}
                                                </table>
                                                <a href="warenkorb.php" class="btn btn-default">{lang key="newEstimation" section="checkout"}</a>
                                            {else}
                                                {lang key="noShippingAvailable" section="checkout"}
                                            {/if}
                                        {/block}
                                    </div>
                                </div>
                            {/block}
                        {/if}

                        {if !empty($cErrorVersandkosten)}
                            <div class="alert alert-info">{$cErrorVersandkosten}</div>
                        {/if}

                    {/block}
                {/if}
            </form>

            {if $oArtikelGeschenk_arr|@count > 0}
                {block name="basket-freegift"}
                    <div id="freegift" class="panel panel-info">
                        <div class="panel-heading">{block name="basket-freegift-title"}{lang key="freeGiftFromOrderValueBasket" section="global"}{/block}</div>
                        <div class="panel-body">
                            {block name="basket-freegift-body"}
                                <form method="post" name="freegift" action="warenkorb.php">
                                    {$jtl_token}
                                    <div class="row row-eq-height">
                                        {foreach name=gratisgeschenke from=$oArtikelGeschenk_arr item=oArtikelGeschenk}
                                            <div class="col-sm-6 col-md-4 text-center">
                                                <label class="thumbnail" for="gift{$oArtikelGeschenk->kArtikel}">
                                                    <img src="{$oArtikelGeschenk->Bilder[0]->cPfadKlein}" class="image" />

                                                    <p class="small text-muted">{lang key="freeGiftFrom1" section="global"} {$oArtikelGeschenk->cBestellwert} {lang key="freeGiftFrom2" section="global"}</p>

                                                    <p>{$oArtikelGeschenk->cName}</p>
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