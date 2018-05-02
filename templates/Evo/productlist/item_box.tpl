{* template to display products in boxes and product-lists *}

{if $Einstellungen.template.productlist.variation_select_productlist === 'N' || $Einstellungen.template.productlist.hover_productlist !== 'Y'}
    {assign var="hasOnlyListableVariations" value=0}
{else}
    {hasOnlyListableVariations artikel=$Artikel maxVariationCount=$Einstellungen.template.productlist.variation_select_productlist maxWerteCount=$Einstellungen.template.productlist.variation_max_werte_productlist assign="hasOnlyListableVariations"}
{/if}
<div id="result-wrapper_buy_form_{$Artikel->kArtikel}" class="product-cell text-center{if $Einstellungen.template.productlist.hover_productlist === 'Y'} hover-enabled{/if}{if isset($listStyle) && $listStyle === 'gallery'} active{/if}{if isset($class)} {$class}{/if}">
    {block name="productlist-image"}
        <a class="image-wrapper" href="{$Artikel->cURLFull}">
            {if isset($Artikel->Bilder[0]->cAltAttribut)}
                {assign var="alt" value=$Artikel->Bilder[0]->cAltAttribut|strip_tags|truncate:60|escape:"html"}
            {else}
                {assign var="alt" value=$Artikel->cName}
            {/if}
        {include file="snippets/image.tpl" src=$Artikel->Bilder[0]->cURLNormal alt=$alt}

        {block name="searchspecial-overlay"}
            {if isset($Artikel->oSuchspecialBild)}
                {include file="snippets/searchspecials.tpl" src=$Artikel->oSuchspecialBild->cURLKlein alt=$alt}
            {/if}
        {/block}

        {if $Einstellungen.template.productlist.quickview_productlist === 'Y' && !$Artikel->bHasKonfig}
            <span class="quickview badge hidden-xs" data-src="{$Artikel->cURLFull}" data-target="buy_form_{$Artikel->kArtikel}" title="{$Artikel->cName}">{lang key="downloadPreview" section="productDownloads"}</span>
        {/if}
    </a>
    {/block}
    {block name="productlist-image-caption"}
    <div class="caption">
        <h4 class="title" itemprop="name"><a href="{$Artikel->cURLFull}">{$Artikel->cKurzbezeichnung}</a></h4>
        {if $Artikel->cName !== $Artikel->cKurzbezeichnung}<meta itemprop="alternateName" content="{$Artikel->cName}">{/if}
        <meta itemprop="url" content="{$Artikel->cURLFull}">
        {if $Einstellungen.bewertung.bewertung_anzeigen === 'Y' && $Artikel->fDurchschnittsBewertung > 0}
            {include file='productdetails/rating.tpl' stars=$Artikel->fDurchschnittsBewertung}<br>
        {/if}
        <div itemprop="offers" itemscope itemtype="http://schema.org/Offer">
            <link itemprop="businessFunction" href="http://purl.org/goodrelations/v1#Sell" />
            {include file="productdetails/price.tpl" Artikel=$Artikel tplscope=$tplscope}
        </div>
    </div>{* /caption *}
    {/block}
    <form id="buy_form_{$Artikel->kArtikel}" action="index.php" method="post" class="form form-basket" data-toggle="basket-add">
        {$jtl_token}
        {block name="productlist-delivery-status"}
            <div class="delivery-status">
                {assign var=anzeige value=$Einstellungen.artikeluebersicht.artikeluebersicht_lagerbestandsanzeige}
                {if $Artikel->nErscheinendesProdukt}
                    <div class="availablefrom">
                        <small>{lang key="productAvailable" section="global"}: {$Artikel->Erscheinungsdatum_de}</small>
                    </div>
                    {if $Einstellungen.global.global_erscheinende_kaeuflich === 'Y' && $Artikel->inWarenkorbLegbar === 1}
                        <div class="attr attr-preorder"><small class="value">{lang key="preorderPossible" section="global"}</small></div>
                    {/if}
                {elseif $anzeige !== 'nichts' &&
                    $Einstellungen.artikeluebersicht.artikeluebersicht_lagerbestandanzeige_anzeigen !== 'N' &&
                    $Artikel->cLagerBeachten === 'Y' && ($Artikel->cLagerKleinerNull === 'N' ||
                    $Einstellungen.artikeluebersicht.artikeluebersicht_lagerbestandanzeige_anzeigen === 'U') &&
                    $Artikel->fLagerbestand <= 0 && $Artikel->fZulauf > 0 && isset($Artikel->dZulaufDatum_de)}
                    {assign var=cZulauf value=$Artikel->fZulauf|cat:':::'|cat:$Artikel->dZulaufDatum_de}
                    <div class="signal_image status-1"><small>{lang key="productInflowing" section="productDetails" printf=$cZulauf}</small></div>
                {elseif $anzeige !== 'nichts' &&
                    $Einstellungen.artikeluebersicht.artikeluebersicht_lagerbestandanzeige_anzeigen !== 'N' &&
                    $Artikel->cLagerBeachten === 'Y' && $Artikel->fLagerbestand <= 0 &&
                    $Artikel->fLieferantenlagerbestand > 0 && $Artikel->fLieferzeit > 0 &&
                    ($Artikel->cLagerKleinerNull === 'N' ||
                    $Einstellungen.artikeluebersicht.artikeluebersicht_lagerbestandanzeige_anzeigen === 'U')}
                    <div class="signal_image status-1"><small>{lang key="supplierStockNotice" section="global" printf=$Artikel->fLieferzeit}</small></div>
                {elseif $anzeige === 'verfuegbarkeit' || $anzeige === 'genau'}
                    <div class="signal_image status-{$Artikel->Lageranzeige->nStatus}"><small>{$Artikel->Lageranzeige->cLagerhinweis[$anzeige]}</small></div>
                {elseif $anzeige === 'ampel'}
                    <div class="signal_image status-{$Artikel->Lageranzeige->nStatus}"><small>{$Artikel->Lageranzeige->AmpelText}</small></div>
                {/if}
                {if $Artikel->cEstimatedDelivery}
                    <div class="estimated_delivery hidden-xs">
                        <small>{lang key="shippingTime" section="global"}: {$Artikel->cEstimatedDelivery}</small>
                    </div>
                {/if}
            </div>
        {/block}
        <div class="expandable">
            {block name="form-expandable"}
            {if $hasOnlyListableVariations > 0 && !$Artikel->bHasKonfig && $Artikel->kEigenschaftKombi === 0}
                <div class="basket-variations">
                    {assign var="singleVariation" value=true}
                    {include file="productdetails/variation.tpl" simple=$Artikel->isSimpleVariation showMatrix=false smallView=true ohneFreifeld=($hasOnlyListableVariations == 2)}
                </div>
            {/if}
            <div>
                {block name="productlist-add-basket"}
                {if ($Artikel->inWarenkorbLegbar === 1 || ($Artikel->nErscheinendesProdukt === 1 && $Einstellungen.global.global_erscheinende_kaeuflich === 'Y')) &&
                    (($Artikel->nIstVater === 0 && $Artikel->Variationen|@count === 0) || $hasOnlyListableVariations === 1) && !$Artikel->bHasKonfig
                }
                    <div class="quantity-wrapper form-group top7">
                        {if $Artikel->nIstVater && $Artikel->kVaterArtikel == 0}
                            <p class="alert alert-info choose-variations">{lang key="chooseVariations" section="messages"}</p>
                        {else}
                            <div class="quantity-wrapper form-group top7">
                                <div class="input-group input-group-sm">
                                    <input type="number" min="0"
                                           {if $Artikel->fAbnahmeintervall > 0}step="{$Artikel->fAbnahmeintervall}"{/if} size="2"
                                           id="quantity{$Artikel->kArtikel}" class="quantity form-control text-right" name="anzahl"
                                           autocomplete="off"
                                           value="{if $Artikel->fAbnahmeintervall > 0}{if $Artikel->fMindestbestellmenge > $Artikel->fAbnahmeintervall}{$Artikel->fMindestbestellmenge}{else}{$Artikel->fAbnahmeintervall}{/if}{else}1{/if}">

                                    <span class="input-group-btn">
                                        <button type="submit" class="btn btn-primary" id="submit{$Artikel->kArtikel}"
                                                title="{lang key="addToCart" section="global"}">
                                            <i class="fa fa-shopping-cart"></i><span class="hidden-xs"> {lang key="addToCart" section="global"}</span>
                                        </button>
                                    </span>
                                </div>
                            </div>
                        {/if}
                    </div>
                {else}
                    <div class="top7 form-group">
                        <a class="btn btn-default btn-md btn-block" role="button" href="{$Artikel->cURLFull}">{lang key="details"}</a>
                    </div>
                {/if}
                {/block}
            </div>

            {if $Artikel->kArtikelVariKombi > 0}
                <input type="hidden" name="aK" value="{$Artikel->kArtikelVariKombi}" />
            {/if}
            {if isset($Artikel->kVariKindArtikel)}
                <input type="hidden" name="VariKindArtikel" value="{$Artikel->kVariKindArtikel}" />
            {/if}
            <input type="hidden" name="a" value="{$Artikel->kArtikel}" />
            <input type="hidden" name="wke" value="1" />
            <input type="hidden" name="overview" value="1" />
            <input type="hidden" name="Sortierung" value="{if !empty($Suchergebnisse->Sortierung)}{$Suchergebnisse->Sortierung}{/if}" />
            {if $Suchergebnisse->getPages()->AktuelleSeite > 1}
                <input type="hidden" name="seite" value="{$Suchergebnisse->getPages()->AktuelleSeite}" />
            {/if}
            {if $NaviFilter->hasCategory()}
                <input type="hidden" name="k" value="{$NaviFilter->getCategory()->getValue()}" />
            {/if}
            {if $NaviFilter->hasManufacturer()}
                <input type="hidden" name="h" value="{$NaviFilter->getManufacturer()->getValue()}" />
            {/if}
            {if $NaviFilter->hasSearchQuery()}
                <input type="hidden" name="l" value="{$NaviFilter->getSearchQuery()->getValue()}" />
            {/if}
            {if $NaviFilter->hasAttributeValue()}
                <input type="hidden" name="m" value="{$NaviFilter->getAttributeValue()->getValue()}" />
            {/if}
            {if $NaviFilter->hasTag()}
                <input type="hidden" name="t" value="{$NaviFilter->getTag()->getValue()}">
            {/if}
            {if $NaviFilter->hasCategoryFilter()}
                <input type="hidden" name="kf" value="{$NaviFilter->getCategoryFilter()->getValue()}" />
            {/if}
            {if $NaviFilter->hasManufacturerFilter()}
                <input type="hidden" name="hf" value="{$NaviFilter->getManufacturerFilter()->getValue()}" />
            {/if}
            {if $NaviFilter->hasAttributeFilter()}
                {foreach name=merkmalfilter from=$NaviFilter->getAttributeFilter() item=attributeFilter}
                    <input type="hidden" name="mf{$smarty.foreach.merkmalfilter.iteration}" value="{$attributeFilter->getValue()}" />
                {/foreach}
            {/if}
            {if $NaviFilter->hasTagFilter()}
                {foreach name=tagfilter from=$NaviFilter->getTagFilter() item=tagFilter}
                    <input type="hidden" name="tf{$smarty.foreach.tagfilter.iteration}" value="{$tagFilter->getValue()}" />
                {/foreach}
            {/if}
            {/block}
        </div>
    </form>
</div>{* /product-cell *}
