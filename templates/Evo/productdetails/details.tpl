{if isset($boxes)}{* only available in shop versions > 3.19 *}
    {has_boxes position='left' assign='hasLeftBox'}
{/if}
{if !empty($hinweis)}
    {if isset($bWarenkorbHinzugefuegt) && $bWarenkorbHinzugefuegt}
        {include file='productdetails/pushed_success.tpl' type='alert'}
    {else}
        <div class="alert alert-success">
            {$hinweis}
        </div>
    {/if}
{/if}
{if !empty($fehler)}
    <div class="alert alert-danger">
        {$fehler}
    </div>
{/if}
{if !empty($ProdukttagHinweis)}
    <div class="alert alert-success">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        {$ProdukttagHinweis}
    </div>
{/if}
{if isset($PositiveFeedback) && count($PositiveFeedback) > 0}
    {foreach name=feedback from=$PositiveFeedback item=Feedback}
        <div class="alert alert-success">{$Feedback}</div>
    {/foreach}
{/if}
{if isset($Artikelhinweise) && count($Artikelhinweise) > 0}
    {foreach name=hinweise from=$Artikelhinweise item=Artikelhinweis}
        <div class="alert alert-danger">{$Artikelhinweis}</div>
    {/foreach}
{/if}

<div class="h1 visible-xs text-center">{$Artikel->cName}</div>

<form id="buy_form" method="post" action="{$Artikel->cURLFull}" >
    {$jtl_token}
    <div class="row product-primary" id="product-offer">
        <div class="product-gallery{if $hasLeftBox} col-sm-5{else} col-sm-6{/if}">
            {include file="productdetails/image.tpl"}
        </div>
        <div class="product-info{if $hasLeftBox} col-sm-7{else} col-sm-6{/if}">
            {block name="productdetails-info"}
            <div class="product-info-inner">
                {if $Einstellungen.artikeldetails.artikeldetails_hersteller_anzeigen !== 'N' && isset($Artikel->cHersteller)}
                    {block name="product-info-manufacturer"}
                    <div class="manufacturer-row text-right small" itemprop="brand" itemscope itemtype="http://schema.org/Brand">
                        <a href="{$Artikel->cHerstellerSeo}"{if $Einstellungen.artikeldetails.artikeldetails_hersteller_anzeigen !== 'B'} data-toggle="tooltip" data-placement="left" title="{$Artikel->cHersteller}"{/if} itemprop="url">
                            {if $Einstellungen.artikeldetails.artikeldetails_hersteller_anzeigen !== 'Y' && (!empty($Artikel->cBildpfad_thersteller) || $Einstellungen.artikeldetails.artikeldetails_hersteller_anzeigen === 'B') && isset($Artikel->cHerstellerBildKlein)}
                                <span itemprop="logo" itemscope="" itemtype="http://schema.org/ImageObject">
                                    <img src="{$Artikel->cHerstellerBildKlein}" alt="{$Artikel->cHersteller}" class="img-sm" itemprop="contentUrl">
                                </span>
                            {/if}
                            {if $Einstellungen.artikeldetails.artikeldetails_hersteller_anzeigen !== 'B'}
                                <span itemprop="name">{$Artikel->cHersteller}</span>
                            {/if}
                        </a>
                    </div>
                    {/block}
                {/if}
    
    
                <div class="product-headline hidden-xs">
                    {block name="productdetails-info-product-title"}
                    <h1 class="fn product-title" itemprop="name">{$Artikel->cName}</h1>
                    {/block}
                </div>
    
                {if ($Artikel->Bewertungen->oBewertungGesamt->nAnzahl > 0) || isset($Artikel->cArtNr)}
                    <div class="info-essential row">
                        {block name="productdetails-info-essential"}
                        {if isset($Artikel->cArtNr) || isset($Artikel->dMHD)}
                            <div class="col-xs-8">
                                <p class="text-muted product-sku">{lang key="sortProductno" section="global"}: <span itemprop="sku">{$Artikel->cArtNr}</span></p>
                                {if isset($Artikel->dMHD) && isset($Artikel->dMHD_de)}
                                    <p title="{lang key='productMHDTool' section='global'}" class="best-before text-muted">{lang key="productMHD" section="global"}: <span itemprop="best-before">{$Artikel->dMHD_de}</span></p>
                                {/if}
                            </div>
                        {/if}
                        {if ($Einstellungen.bewertung.bewertung_anzeigen === 'Y' && $Artikel->Bewertungen->oBewertungGesamt->nAnzahl > 0)}
                            <div class="rating-wrapper col-xs-4 text-right" itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
                            <span itemprop="ratingValue"
                                  class="hidden">{$Artikel->Bewertungen->oBewertungGesamt->fDurchschnitt}</span>
                            <span itemprop="reviewCount" class="hidden">{$Artikel->Bewertungen->oBewertungGesamt->nAnzahl}</span>
                            <a href="{$Artikel->cURLFull}#tab-votes" id="jump-to-votes-tab" class="hidden-print">
                                {include file='productdetails/rating.tpl' stars=$Artikel->Bewertungen->oBewertungGesamt->fDurchschnitt total=$Artikel->Bewertungen->oBewertungGesamt->nAnzahl}
                            </a>
                            </div>{* /rating-wrapper*}
                        {/if}
                        {/block}
                    </div>
                    <div class="clearfix top10"></div>
                {/if}
    
                {if $Einstellungen.artikeldetails.artikeldetails_kurzbeschreibung_anzeigen === 'Y' && $Artikel->cKurzBeschreibung}
                    {block name="productdetails-info-description"}
                    <div class="shortdesc" itemprop="description">
                        {$Artikel->cKurzBeschreibung}
                    </div>
                    {/block}
                    <div class="clearfix top10"></div>
                {/if}
    
                {if $Einstellungen.artikeldetails.artikeldetails_kategorie_anzeigen === 'Y'}
                    {block name="productdetails-info-category"}
                    <p class="product-category word-break">
                        <span class="text-muted">{lang key="category" section="global"}: </span>
                        {assign var=i_kat value=$Brotnavi|@count}{assign var=i_kat value=$i_kat-2}
                        <a href="{$Brotnavi[$i_kat]->url}" itemprop="category">{$Brotnavi[$i_kat]->name}</a>
                    </p>
                    {/block}
                {/if}
                
                <div class="product-offer" {if !($Artikel->nIstVater)}itemprop="offers" {if $showMatrix}itemref="product-var-matrix"{/if} itemscope itemtype="http://schema.org/Offer"{/if}>
                    {block name="productdetails-info-hidden"}
                    {if !($Artikel->nIstVater)}
                        <link itemprop="url" href="{$Artikel->cURLFull}" />
                    {/if}
                    <input type="submit" name="inWarenkorb" value="1" class="hidden" />
                    {if $Artikel->kArtikelVariKombi > 0}
                        <input type="hidden" name="aK" value="{$Artikel->kArtikelVariKombi}" />
                    {/if}
                    {if isset($Artikel->kVariKindArtikel)}
                        <input type="hidden" name="VariKindArtikel" value="{$Artikel->kVariKindArtikel}" />
                    {/if}
                    {if isset($smarty.get.ek)}
                        <input type="hidden" name="ek" value="{$smarty.get.ek|intval}" />
                    {/if}
                    <input type="hidden" id="AktuellerkArtikel" class="current_article" name="a" value="{$Artikel->kArtikel}" />
                    <input type="hidden" name="wke" value="1" />
                    <input type="hidden" name="show" value="1" />
                    <input type="hidden" name="kKundengruppe" value="{$smarty.session.Kundengruppe->kKundengruppe}" />
                    <input type="hidden" name="kSprache" value="{$smarty.session.kSprache}" />
                    {/block}
                    <!-- VARIATIONEN -->
                    {include file="productdetails/variation.tpl" simple=$Artikel->isSimpleVariation showMatrix=$showMatrix}
                    <hr>
                    <div class="row">
                        <div class="col-xs-7">
                            {if isset($Artikel->Preise->strPreisGrafik_Detail)}
                                {assign var=priceImage value=$Artikel->Preise->strPreisGrafik_Detail}
                            {else}
                                {assign var=priceImage value=null}
                            {/if}
                            {include file="productdetails/price.tpl" Artikel=$Artikel price_image=$priceImage tplscope="detail"}
                        </div>
                        <div class="col-xs-5 text-right">
                            {include file="productdetails/stock.tpl"}
                        </div>
                    </div>
                    {*WARENKORB anzeigen wenn keine variationen mehr auf lager sind?!*}
                    {include file="productdetails/basket.tpl"}
                    <hr>
                </div>
    
                {if !($Artikel->nIstVater && $Artikel->kVaterArtikel == 0)}
                    {include file="productdetails/actions.tpl"}
                {/if}
            </div>{* /product-info-inner *}
            {/block}{* productdetails-info *}
        </div>{* /col *}
        {if $Artikel->bHasKonfig}
            {block name="productdetails-config"}
            <div id="product-configurator" class="col-sm-12">
                <div class="product-config top10">
                    {*KONFIGURATOR*}
                    {if isset($Artikel->FunktionsAttribute[$FKT_ATTRIBUT_ARTIKELKONFIG_TPL]) && file_exists("tpl_inc/{$Artikel->FunktionsAttribute[$FKT_ATTRIBUT_ARTIKELKONFIG_TPL]}")}
                        {include file='tpl_inc/'|cat:$Artikel->FunktionsAttribute[$FKT_ATTRIBUT_ARTIKELKONFIG_TPL]}
                    {else}
                        {include file="productdetails/config.tpl"}
                    {/if}
                </div>
            </div>
            {/block}
        {/if}
    </div>{* /row *}
    {block name="details-matrix"}
    {include file="productdetails/matrix.tpl"}
    {/block}
</form>

{block name="details-tabs"}
{include file="productdetails/tabs.tpl"}
{/block}

<div class="clearfix"></div>

{*SLIDERS*}
{if isset($Einstellungen.artikeldetails.artikeldetails_stueckliste_anzeigen) && $Einstellungen.artikeldetails.artikeldetails_stueckliste_anzeigen === 'Y' && isset($Artikel->oStueckliste_arr) && $Artikel->oStueckliste_arr|@count > 0
    || isset($Einstellungen.artikeldetails.artikeldetails_produktbundle_nutzen) && $Einstellungen.artikeldetails.artikeldetails_produktbundle_nutzen == 'Y' && isset($Artikel->oProduktBundle_arr) && $Artikel->oProduktBundle_arr|@count > 0
    || isset($Xselling->Standard->XSellGruppen) && count($Xselling->Standard->XSellGruppen) > 0
    || isset($Xselling->Kauf->Artikel) && count($Xselling->Kauf->Artikel) > 0
    || isset($oAehnlicheArtikel_arr) && count($oAehnlicheArtikel_arr) > 0}
    <hr>
    {if isset($Einstellungen.artikeldetails.artikeldetails_stueckliste_anzeigen) && $Einstellungen.artikeldetails.artikeldetails_stueckliste_anzeigen === 'Y' && isset($Artikel->oStueckliste_arr) && $Artikel->oStueckliste_arr|@count > 0}
        <div class="partslist">
            {lang key='listOfItems' section='global' assign='slidertitle'}
            {include file='snippets/product_slider.tpl' id='slider-partslist' productlist=$Artikel->oStueckliste_arr title=$slidertitle showPartsList=true}
        </div>
    {/if}

    {if isset($Einstellungen.artikeldetails.artikeldetails_produktbundle_nutzen) && $Einstellungen.artikeldetails.artikeldetails_produktbundle_nutzen == 'Y' && isset($Artikel->oProduktBundle_arr) && $Artikel->oProduktBundle_arr|@count > 0}
        <div class="bundle">
            {include file="productdetails/bundle.tpl" ProductKey=$Artikel->kArtikel Products=$Artikel->oProduktBundle_arr ProduktBundle=$Artikel->oProduktBundlePrice ProductMain=$Artikel->oProduktBundleMain}
        </div>
    {/if}

    {if isset($Xselling->Standard) || isset($Xselling->Kauf) || isset($oAehnlicheArtikel_arr)}
        <div class="recommendations hidden-print">
            {block name="productdetails-recommendations"}
            {if isset($Xselling->Standard->XSellGruppen) && count($Xselling->Standard->XSellGruppen) > 0}
                {foreach name=Xsell_gruppen from=$Xselling->Standard->XSellGruppen item=Gruppe}
                    {include file='snippets/product_slider.tpl' class='x-supplies' id='slider-xsell-group-'|cat:$smarty.foreach.Xsell_gruppen.iteration productlist=$Gruppe->Artikel title=$Gruppe->Name}
                {/foreach}
            {/if}

            {if isset($Xselling->Kauf->Artikel) && count($Xselling->Kauf->Artikel) > 0}
                {lang key='customerWhoBoughtXBoughtAlsoY' section='productDetails' assign='slidertitle'}
                {include file='snippets/product_slider.tpl' class='x-sell' id='slider-xsell' productlist=$Xselling->Kauf->Artikel title=$slidertitle}
            {/if}

            {if isset($oAehnlicheArtikel_arr) && count($oAehnlicheArtikel_arr) > 0}
                {lang key='RelatedProducts' section='productDetails' assign='slidertitle'}
                {include file='snippets/product_slider.tpl' class='x-related' id='slider-related' productlist=$oAehnlicheArtikel_arr title=$slidertitle}
            {/if}
            {/block}
        </div>
    {/if}
{/if}
<div id="article_popups">
    {include file='productdetails/popups.tpl'}
</div>
