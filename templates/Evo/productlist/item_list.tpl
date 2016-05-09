{* template to display products in product-lists *}

<div class="product-cell thumbnail">
    <div class="product-body row {if $tplscope !== 'list'} text-center{/if}">
        <div class="col-xs-3 col-sm-2 col-lg-3 text-center">
            {block name="image-wrapper"}
                <a class="image-wrapper" href="{$Artikel->cURL}">
                    {if isset($Artikel->Bilder[0]->cAltAttribut)}
                        {assign var="alt" value=$Artikel->Bilder[0]->cAltAttribut|strip_tags|escape:"quotes"|truncate:60}
                    {else}
                        {assign var="alt" value=$Artikel->cName}
                    {/if}
    
                    {include file="snippets/image.tpl" src=$Artikel->Bilder[0]->cPfadNormal alt=$alt tplscope=$tplscope}
    
                    {if isset($Artikel->oSuchspecialBild)}
                        <img class="overlay-img visible-lg" src="{$Artikel->oSuchspecialBild->cPfadKlein}" alt="{if isset($Artikel->oSuchspecialBild->cSuchspecial)}{$Artikel->oSuchspecialBild->cSuchspecial}{else}{$Artikel->cName}{/if}">
                    {/if}
                </a>
            {/block}
            {include file='productdetails/rating.tpl' stars=$Artikel->fDurchschnittsBewertung}
        </div>
        <div class="col-xs-6 col-sm-6 col-lg-5">
            {block name="product-title"}<h4 class="title"><a href="{$Artikel->cURL}">{$Artikel->cName}</a></h4>{/block}

            <div class="product-info hidden-xs">
                {block name="product-info"}
                    <div class="shortdescription">
                        {$Artikel->cKurzBeschreibung}
                    </div>
                    <ul class="attr-group list-unstyled small text-muted top10  hidden-sm">
                        <li class="item row attr-sku">
                            <span class="attr-label col-sm-5">{lang key="productNo" section="global"}: </span> <span class="value col-sm-7">{$Artikel->cArtNr}</span>
                        </li>
                        {if isset($Artikel->dMHD) && isset($Artikel->dMHD_de)}
                            <li class="item row attr-best-before" title="{lang key='productMHDTool' section='global'}">
                                <span class="attr-label col-sm-5">{lang key="productMHD" section="global"}: </span> <span class="value col-sm-7">{$Artikel->dMHD_de}</span>
                            </li>
                        {/if}
                        {if $Einstellungen.artikeluebersicht.artikeluebersicht_hersteller_anzeigen !== 'N' && !empty($Artikel->cHersteller)}
                            <li class="item row attr-manufacturer">
                                <span class="attr-label col-sm-5">{lang key="manufacturerSingle" section="productOverview"}: </span>
                                <span class="value col-sm-7">
                                {if $Artikel->cHerstellerHomepage}
                                    <a href="{$Artikel->cHerstellerHomepage}">
                                {/if}
                                {if $Einstellungen.artikeluebersicht.artikeluebersicht_hersteller_anzeigen !== 'B' && !empty($Artikel->cHersteller)}
                                    {$Artikel->cHersteller}
                                {/if}
                                {if $Einstellungen.artikeluebersicht.artikeluebersicht_hersteller_anzeigen !== 'Y' && !empty($Artikel->cHerstellerBildKlein)}
                                    <img src="{$Artikel->cHerstellerBildKlein}" alt="" />
                                {/if}
                                {if $Artikel->cHerstellerHomepage}
                                    </a>
                                {/if}
                                </span>
                            </li>
                        {/if}
                        {if isset($Artikel->cGewicht) && $Einstellungen.artikeluebersicht.artikeluebersicht_gewicht_anzeigen === 'Y' && $Artikel->fGewicht > 0}
                            <li class="item row attr-weight">
                                <span class="attr-label col-sm-5">{lang key="shippingWeight" section="global"}: </span>
                                <span class="value col-sm-7">{$Artikel->cGewicht} {lang key="weightUnit" section="global"}</span>
                            </li>
                        {/if}
                        {if isset($Artikel->cArtikelgewicht) && $Einstellungen.artikeluebersicht.artikeluebersicht_artikelgewicht_anzeigen === 'Y' && $Artikel->fArtikelgewicht > 0}
                            <li class="item row attr-weight">
                                <span class="attr-label col-sm-5">{lang key="productWeight" section="global"}: </span>
                                <span class="value col-sm-7">{$Artikel->cArtikelgewicht} {lang key="weightUnit" section="global"}</span>
                            </li>
                        {/if}
                        {if $Einstellungen.artikeluebersicht.artikeluebersicht_artikelintervall_anzeigen === 'Y' && $Artikel->fAbnahmeintervall > 0}
                            <li class="item row attr-quantity-scale">
                                <span class="attr-label col-sm-5">{lang key="purchaseIntervall" section="productOverview"}: </span>
                                <span class="value col-sm-7">{$Artikel->fAbnahmeintervall} {$Artikel->cEinheit}</span>
                            </li>
                        {/if}
                        {if count($Artikel->Variationen)>0}
                            <li class="item row attr-variations">
                                <span class="attr-label col-sm-5">{lang key="variationsIn" section="productOverview"}: </span>
                                <span class="value-group col-sm-7">{foreach name=variationen from=$Artikel->Variationen item=Variation}{if !$smarty.foreach.variationen.first}, {/if}
                                <span class="value">{$Variation->cName}</span>{/foreach}</span>
                            </li>
                        {/if}
                    </ul>{* /attr-group *}
                    {if $Artikel->oVariationKombiVorschau_arr|@count > 0 && $Artikel->oVariationKombiVorschau_arr && $Einstellungen.artikeluebersicht.artikeluebersicht_varikombi_anzahl > 0}
                        <div class="varikombis-thumbs">
                            {foreach name=varikombis from=$Artikel->oVariationKombiVorschau_arr item=oVariationKombiVorschau}
                                <a href="{$oVariationKombiVorschau->cURL}" class="thumbnail pull-left"><img src="{$oVariationKombiVorschau->cBildMini}" alt="" /></a>
                            {/foreach}
                        </div>
                    {/if}
                {/block}
            </div>{* /product-info *}

        </div>{* /col-md-9 *}

        <div class="col-xs-3 col-sm-4">
            <form action="navi.php" method="post" class="form form-basket" data-toggle="basket-add">
                {block name="form-basket"}
                    {include file="productdetails/price.tpl" Artikel=$Artikel price_image=$Artikel->Preise->strPreisGrafik_Suche tplscope=$tplscope}
                    <div class="delivery-status">
                    {block name="delivery-status"}
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
                    {/block}
                    </div>
    
                    <div class="hidden-xs basket-details">
                        {block name="basket-details"}
                            {if ($Artikel->inWarenkorbLegbar == 1 || ($Artikel->nErscheinendesProdukt == 1 && $Einstellungen.global.global_erscheinende_kaeuflich === 'Y')) && $Artikel->nIstVater == 0 && $Artikel->Variationen|@count == 0 && !$Artikel->bHasKonfig}
                                <div class="quantity-wrapper form-group top7">
                                    {if $Artikel->cEinheit}
                                        <div class="input-group input-group-sm">
                                            <input type="number" min="0"{if $Artikel->fAbnahmeintervall > 0} step="{$Artikel->fAbnahmeintervall}"{/if} size="2" onfocus="this.setAttribute('autocomplete', 'off');" id="quantity{$Artikel->kArtikel}" class="quantity form-control text-right" name="anzahl" value="{if $Artikel->fAbnahmeintervall > 0}{if $Artikel->fMindestbestellmenge > $Artikel->fAbnahmeintervall}{$Artikel->fMindestbestellmenge}{else}{$Artikel->fAbnahmeintervall}{/if}{else}1{/if}" />
        
                                            {if $Artikel->cEinheit}
                                                <span class="input-group-addon unit">{$Artikel->cEinheit}</span>
                                            {/if}
                                        </div>
                                        <div class="input-group input-group-sm">
                                            <span class="change_quantity input-group-btn">
                                                <button type="submit" class="btn btn-primary btn-block" id="submit{$Artikel->kArtikel}" title="{lang key="addToCart" section="global"}"><span class="fa fa-shopping-cart"></span> {lang key="addToCart" section="global"}</button>
                                            </span>
                                        </div>
                                    {else}
                                        <div class="input-group input-group-sm">
                                            <input type="number" min="0"{if $Artikel->fAbnahmeintervall > 0} step="{$Artikel->fAbnahmeintervall}"{/if} size="2" onfocus="this.setAttribute('autocomplete', 'off');" id="quantity{$Artikel->kArtikel}" class="quantity form-control text-right" name="anzahl" value="{if $Artikel->fAbnahmeintervall > 0}{if $Artikel->fMindestbestellmenge > $Artikel->fAbnahmeintervall}{$Artikel->fMindestbestellmenge}{else}{$Artikel->fAbnahmeintervall}{/if}{else}1{/if}" />
        
                                            <span class="change_quantity input-group-btn">
                                                <button type="submit" class="btn btn-primary" id="submit{$Artikel->kArtikel}" title="{lang key="addToCart" section="global"}"><span class="fa fa-shopping-cart"></span><span class="hidden-md"> {lang key="addToCart" section="global"}</span></button>
                                            </span>
                                        </div>
                                    {/if}
                                </div>
                            {else}
                                <div class="top7 form-group">
                                    <a class="btn btn-default btn-xs btn-block" role="button" href="{$Artikel->cURL}">{lang key="details"}</a>
                                </div>
                            {/if}
                        {/block}
                    </div>
    
                    <input type="hidden" name="a" value="{$Artikel->kArtikel}" />
                    <input type="hidden" name="wke" value="1" />
                    <input type="hidden" name="overview" value="1" />
                    <input type="hidden" name="Sortierung" value="{if isset($Suchergebnisse->Sortierung)}{$Suchergebnisse->Sortierung}{/if}" />
                    {if isset($Suchergebnisse->Seitenzahlen) && $Suchergebnisse->Seitenzahlen->AktuelleSeite > 1}
                        <input type="hidden" name="seite" value="{$Suchergebnisse->Seitenzahlen->AktuelleSeite}" />
                    {/if}
                    {if isset($NaviFilter->Kategorie) && $NaviFilter->Kategorie->kKategorie > 0}
                        <input type="hidden" name="k" value="{$NaviFilter->Kategorie->kKategorie}" />
                    {/if}
                    {if isset($NaviFilter->Hersteller) && $NaviFilter->Hersteller->kHersteller > 0}
                        <input type="hidden" name="h" value="{$NaviFilter->Hersteller->kHersteller}" />
                    {/if}
                    {if isset($NaviFilter->Suchanfrage) && $NaviFilter->Suchanfrage->kSuchanfrage > 0}
                        <input type="hidden" name="l" value="{$NaviFilter->Suchanfrage->kSuchanfrage}" />
                    {/if}
                    {if isset($NaviFilter->MerkmalWert) && $NaviFilter->MerkmalWert->kMerkmalWert > 0}
                        <input type="hidden" name="m" value="{$NaviFilter->MerkmalWert->kMerkmalWert}" />
                    {/if}
                    {if isset($NaviFilter->Tag) && $NaviFilter->Tag->kTag > 0}
                        <input type="hidden" name="t" value="{$NaviFilter->Tag->kTag}">
                    {/if}
                    {if isset($NaviFilter->KategorieFilter) && $NaviFilter->KategorieFilter->kKategorie > 0}
                        <input type="hidden" name="kf" value="{$NaviFilter->KategorieFilter->kKategorie}" />
                    {/if}
                    {if isset($NaviFilter->HerstellerFilter) && $NaviFilter->HerstellerFilter->kHersteller > 0}
                        <input type="hidden" name="hf" value="{$NaviFilter->HerstellerFilter->kHersteller}" />
                    {/if}
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
                {/block}
            </form>

            <form action="navi.php" method="post" class="hidden-sm hidden-xs product-actions">
                {$jtl_token}
                <div class="actions btn-group btn-group-xs btn-group-justified" role="group" aria-label="...">
                {block name="product-actions"}
                    {if !($Artikel->nIstVater && $Artikel->kVaterArtikel == 0)}
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
                                <button type="button" id="n{$Artikel->kArtikel}" class="popup notification btn btn-default btn-left" title="{lang key="requestNotification" section="global"}">
                                    <span class="fa fa-bell"></span>
                                </button>
                            </div>
                        {/if}
                    {/if}
                {/block}
                </div>
                <input type="hidden" name="a" value="{$Artikel->kArtikel}" />
            </form>
        </div>{* /col-md-3 *}
    </div>{* /product-body *}
</div>{* /product-cell *}

{* popup-content *}
{if $Artikel->verfuegbarkeitsBenachrichtigung == 3}
    <div id="popupn{$Artikel->kArtikel}" class="hidden">
        {include file='productdetails/availability_notification_form.tpl' tplscope='artikeldetails'}
    </div>
{/if}