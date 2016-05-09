{* template to display products in boxes and product-lists *}

<div class="product-cell text-center{if isset($class)} {$class}{/if}">
    {block name="productlist-image"}
    <a class="image-wrapper" href="{$Artikel->cURL}">
        {if isset($Artikel->Bilder[0]->cAltAttribut)}
            {assign var="alt" value=$Artikel->Bilder[0]->cAltAttribut|strip_tags|escape:"quotes"|truncate:60}
        {else}
            {assign var="alt" value=$Artikel->cName}
        {/if}

        {include file="snippets/image.tpl" src=$Artikel->Bilder[0]->cPfadNormal alt=$alt}

        {if isset($Artikel->oSuchspecialBild)}
            <img class="overlay-img hidden-xs" src="{$Artikel->oSuchspecialBild->cPfadKlein}" alt="{if isset($Artikel->oSuchspecialBild->cSuchspecial)}{$Artikel->oSuchspecialBild->cSuchspecial}{else}{$Artikel->cName}{/if}" />
        {/if}
    </a>
    {/block}
    {block name="productlist-image-caption"}
    <div class="caption">
        <h4 class="title"><a href="{$Artikel->cURL}">{$Artikel->cName}</a></h4>
        {if $Artikel->fDurchschnittsBewertung > 0}{include file='productdetails/rating.tpl' stars=$Artikel->fDurchschnittsBewertung}<br>{/if}
        {include file="productdetails/price.tpl" Artikel=$Artikel price_image=$Artikel->Preise->strPreisGrafik_Suche tplscope=$tplscope}
    </div>{* /caption *}
    {/block}
    <form action="navi.php" method="post" class="form form-basket" data-toggle="basket-add">
        {$jtl_token}
        {block name="productlist-delivery-status"}
        <div class="delivery-status">
            {assign var=anzeige value=$Einstellungen.artikeluebersicht.artikeluebersicht_lagerbestandsanzeige}
            {if $Artikel->nErscheinendesProdukt}
                <div class="availablefrom">
                    <small>{lang key="productAvailable" section="global"}: {$Artikel->Erscheinungsdatum_de}</small>
                </div>
                {if $Einstellungen.global.global_erscheinende_kaeuflich === 'Y' && $Artikel->inWarenkorbLegbar == 1}
                    <div class="attr attr-preorder"><small class="value">{lang key="preorderPossible" section="global"}</small></div>
                {/if}
            {elseif $anzeige !== 'nichts' && $Artikel->cLagerBeachten === 'Y' && ($Artikel->cLagerKleinerNull === 'N' ||
            $Einstellungen.artikeluebersicht.artikeluebersicht_lagerbestandanzeige_anzeigen === 'U') && $Artikel->fLagerbestand <= 0 && $Artikel->fZulauf > 0 && isset($Artikel->dZulaufDatum_de)}
                {assign var=cZulauf value=$Artikel->fZulauf|cat:':::'|cat:$Artikel->dZulaufDatum_de}
                <div class="signal_image status-1"><small>{lang key="productInflowing" section="productDetails" printf=$cZulauf}</small></div>
            {elseif $anzeige !== 'nichts' && $Einstellungen.artikeluebersicht.artikeluebersicht_lagerbestandanzeige_anzeigen !== 'N' && $Artikel->cLagerBeachten === 'Y' &&
            $Artikel->fLagerbestand <= 0 && $Artikel->fLieferantenlagerbestand > 0 && $Artikel->fLieferzeit > 0 &&
            ($Artikel->cLagerKleinerNull === 'N' || $Einstellungen.artikeluebersicht.artikeluebersicht_lagerbestandanzeige_anzeigen === 'U')}
                <div class="signal_image status-1"><small>{lang key="supplierStockNotice" section="global" printf=$Artikel->fLieferzeit}</small></div>
            {elseif $anzeige=='verfuegbarkeit' || $anzeige === 'genau'}
                <div class="signal_image status-{$Artikel->Lageranzeige->nStatus}"><small>{$Artikel->Lageranzeige->cLagerhinweis[$anzeige]}</small></div>
            {elseif $anzeige=='ampel'}
                <div class="signal_image status-{$Artikel->Lageranzeige->nStatus}"><small>{$Artikel->Lageranzeige->AmpelText}</small></div>
            {/if}
            {if $Artikel->cEstimatedDelivery}
                <div class="estimated_delivery hidden-xs">
                    <small>{lang key="shippingTime" section="global"}: {$Artikel->cEstimatedDelivery}</small>
                </div>
            {/if}
        </div>
        {/block}

        <div class="hidden-xs">
            {block name="productlist-add-basket"}
            {if ($Artikel->inWarenkorbLegbar == 1 || ($Artikel->nErscheinendesProdukt == 1 && $Einstellungen.global.global_erscheinende_kaeuflich === 'Y')) && $Artikel->nIstVater == 0 && $Artikel->Variationen|@count == 0 && !$Artikel->bHasKonfig}
                <div class="quantity-wrapper form-group top7">
                    <div class="input-group input-group-sm">
                        <input type="number" min="0"{if $Artikel->fAbnahmeintervall > 0} step="{$Artikel->fAbnahmeintervall}"{/if} size="2" id="quantity{$Artikel->kArtikel}" class="quantity form-control text-right" name="anzahl" autocomplete="off" value="{if $Artikel->fAbnahmeintervall > 0}{if $Artikel->fMindestbestellmenge > $Artikel->fAbnahmeintervall}{$Artikel->fMindestbestellmenge}{else}{$Artikel->fAbnahmeintervall}{/if}{else}1{/if}" />

                        <span class="change_quantity input-group-btn">
                            <button type="submit" class="btn btn-default" id="submit{$Artikel->kArtikel}" title="{lang key="addToCart" section="global"}"><span class="fa fa-shopping-cart"></span><span class="hidden-md"> {lang key="addToCart" section="global"}</span></button>
                        </span>
                    </div>
                </div>
            {else}
                <div class="top7 form-group">
                    <a class="btn btn-default btn-sm btn-block" role="button" href="{$Artikel->cURL}">{lang key="details"}</a>
                </div>
            {/if}
            {/block}
        </div>

        <input type="hidden" name="a" value="{$Artikel->kArtikel}" />
        <input type="hidden" name="wke" value="1" />
        <input type="hidden" name="overview" value="1" />
        <input type="hidden" name="Sortierung" value="{if !empty($Suchergebnisse->Sortierung)}{$Suchergebnisse->Sortierung}{/if}" />
        {if isset($Suchergebnisse->Seitenzahlen) && $Suchergebnisse->Seitenzahlen->AktuelleSeite > 1}
            <input type="hidden" name="seite" value="{$Suchergebnisse->Seitenzahlen->AktuelleSeite}" />{/if}
        {if isset($NaviFilter->Kategorie) && $NaviFilter->Kategorie->kKategorie > 0}
            <input type="hidden" name="k" value="{$NaviFilter->Kategorie->kKategorie}" />{/if}
        {if isset($NaviFilter->Hersteller) && $NaviFilter->Hersteller->kHersteller > 0}
            <input type="hidden" name="h" value="{$NaviFilter->Hersteller->kHersteller}" />{/if}
        {if isset($NaviFilter->Suchanfrage) && $NaviFilter->Suchanfrage->kSuchanfrage > 0}
            <input type="hidden" name="l" value="{$NaviFilter->Suchanfrage->kSuchanfrage}" />{/if}
        {if isset($NaviFilter->MerkmalWert) && $NaviFilter->MerkmalWert->kMerkmalWert > 0}
            <input type="hidden" name="m" value="{$NaviFilter->MerkmalWert->kMerkmalWert}" />{/if}
        {if isset($NaviFilter->Tag) && $NaviFilter->Tag->kTag > 0}<input type="hidden" name="t" value="{$NaviFilter->Tag->kTag}">{/if}
        {if isset($NaviFilter->KategorieFilter) && $NaviFilter->KategorieFilter->kKategorie > 0}
            <input type="hidden" name="kf" value="{$NaviFilter->KategorieFilter->kKategorie}" />{/if}
        {if isset($NaviFilter->HerstellerFilter) && $NaviFilter->HerstellerFilter->kHersteller > 0}
            <input type="hidden" name="hf" value="{$NaviFilter->HerstellerFilter->kHersteller}" />{/if}

        {if isset($NaviFilter->MerkmalFilter)}
            {foreach name=merkmalfilter from=$NaviFilter->MerkmalFilter item=mmfilter}
                <input type="hidden" name="mf{$smarty.foreach.merkmalfilter.iteration}" value="{$mmfilter->kMerkmalWert}" />
            {/foreach}
        {/if}
        {if isset($NaviFilter->TagFilter)}
            {foreach name=tagfilter from=$NaviFilter->TagFilter item=tag}
                <input type="hidden" name="tf{$smarty.foreach.tagfilter.iteration}" value="{$tag->kTag}" />
            {/foreach}
        {/if}
    </form>
    {*
    <form action="navi.php" method="post" class="hidden-sm hidden-xs">
        {block name="productlist-actions"}
        <div class="actions btn-group btn-group-xs btn-group-justified" role="group" aria-label="...">
            {if $Einstellungen.artikeluebersicht.artikeluebersicht_vergleichsliste_anzeigen === 'Y'}
                <div class="btn-group btn-group-xs" role="group">
                    <button name="Vergleichsliste" type="submit" class="compare btn btn-default" title="{lang key="addToCompare" section="productOverview"}">
                        <span class="fa fa-tasks"></span>
                    </button>
                </div>
            {/if}
            {if $Einstellungen.artikeluebersicht.artikeluebersicht_wunschzettel_anzeigen === 'Y'}
                <div class="btn-group btn-group-xs" role="group">
                    <button name="Wunschliste" type="submit" class="wishlist btn btn-default" title="{lang key="addToWishlist" section="productDetails"}">
                        <span class="fa fa-heart"></span>
                    </button>
                </div>
            {/if}
            {if $Artikel->verfuegbarkeitsBenachrichtigung == 3 && (($Artikel->cLagerBeachten === 'Y' && $Artikel->cLagerKleinerNull !== 'Y') || $Artikel->cLagerBeachten !== 'Y')}
                <div class="btn-group btn-group-xs" role="group">
                    <button type="button" id="n{$Artikel->kArtikel}" class="popup notification btn btn-secondary btn-left" title="{lang key="requestNotification" section="global"}"></button>
                </div>
            {/if}
        </div>
        {/block}
        <input type="hidden" name="a" value="{$Artikel->kArtikel}" />
    </form>
    *}

</div>{* /product-cell *}