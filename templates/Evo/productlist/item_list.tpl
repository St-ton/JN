{* template to display products in product-lists *}

{if $Einstellungen.template.productlist.variation_select_productlist === 'N'}
    {assign var="hasOnlyListableVariations" value=0}
{else}
    {hasOnlyListableVariations artikel=$Artikel maxVariationCount=$Einstellungen.template.productlist.variation_select_productlist assign="hasOnlyListableVariations"}
{/if}
<div class="product-cell thumbnail">
    <div id="result-wrapper_buy_form_{$Artikel->kArtikel}" class="product-body row {if $tplscope !== 'list'} text-center{/if}">
        <div class="col-xs-3 col-sm-2 col-lg-3 text-center">
            {block name="image-wrapper"}
                <a class="image-wrapper" href="{$Artikel->cURL}">
                    {if isset($Artikel->Bilder[0]->cAltAttribut)}
                        {assign var="alt" value=$Artikel->Bilder[0]->cAltAttribut|strip_tags|truncate:60|escape:"html"}
                    {else}
                        {assign var="alt" value=$Artikel->cName}
                    {/if}

                    {include file="snippets/image.tpl" src=$Artikel->Bilder[0]->cPfadNormal alt=$alt tplscope=$tplscope}

                    {if isset($Artikel->oSuchspecialBild)}
                        <img class="overlay-img visible-lg" src="{$Artikel->oSuchspecialBild->cPfadKlein}"
                             alt="{if isset($Artikel->oSuchspecialBild->cSuchspecial)}{$Artikel->oSuchspecialBild->cSuchspecial}{else}{$Artikel->cName}{/if}">
                    {/if}

                    {if $Einstellungen.template.productlist.quickview_productlist === 'Y' && !$Artikel->bHasKonfig}
                        <span class="quickview badge hidden-xs" data-src="{$Artikel->cURL}" data-target="buy_form_{$Artikel->kArtikel}" title="{$Artikel->cName}">{lang key="downloadPreview" section="productDownloads"}</span>
                    {/if}
                </a>
            {/block}
            {if $Einstellungen.bewertung.bewertung_anzeigen === 'Y'}
                {include file='productdetails/rating.tpl' stars=$Artikel->fDurchschnittsBewertung}
            {/if}
        </div>
        <div class="col-xs-6 col-lg-5">
            {block name="product-title"}
                <h4 class="title" itemprop="name">
                    <a href="{$Artikel->cURL}">{$Artikel->cName}</a>
                </h4>
                <meta itemprop="url" content="{$ShopURL}/{$Artikel->cURL}">
            {/block}
            {block name="product-manufacturer"}
                {if $Einstellungen.artikeluebersicht.artikeluebersicht_hersteller_anzeigen !== 'N'}
                    <div class="media hidden-xs top0 bottom5">
                        {if ($Einstellungen.artikeluebersicht.artikeluebersicht_hersteller_anzeigen === 'BT'
                        || $Einstellungen.artikeluebersicht.artikeluebersicht_hersteller_anzeigen === 'B') && !empty($Artikel->cHerstellerBildKlein)}
                            <div class="media-left">
                                {if !empty($Artikel->cHerstellerHomepage)}<a href="{$Artikel->cHerstellerHomepage}">{/if}
                                    <img src="{$Artikel->cHerstellerBildKlein}" alt="" class="img-xs">
                                {if !empty($Artikel->cHerstellerHomepage)}</a>{/if}
                            </div>
                        {/if}
                        {if ($Einstellungen.artikeluebersicht.artikeluebersicht_hersteller_anzeigen === 'BT'
                        || $Einstellungen.artikeluebersicht.artikeluebersicht_hersteller_anzeigen === 'Y') && !empty($Artikel->cHersteller)}
                            <div class="media-body">
                                <span class="small text-uppercase">
                                    {if !empty($Artikel->cHerstellerHomepage)}<a href="{$Artikel->cHerstellerHomepage}">{/if}
                                        {$Artikel->cHersteller}
                                    {if !empty($Artikel->cHerstellerHomepage)}</a>{/if}
                                </span>
                            </div>
                        {/if}
                    </div>
                {/if}
            {/block}

            <div class="product-info hidden-xs">
                {block name="product-info"}
                    {if $Einstellungen.artikeluebersicht.artikeluebersicht_kurzbeschreibung_anzeigen === 'Y' && $Artikel->cKurzBeschreibung}
                        <div class="shortdescription">
                            {$Artikel->cKurzBeschreibung}
                        </div>
                    {/if}
                    <ul class="attr-group list-unstyled small text-muted top10  hidden-sm">
                        <li class="item row attr-sku">
                            <span class="attr-label col-sm-5">{lang key="productNo" section="global"}: </span> <span class="value col-sm-7">{$Artikel->cArtNr}</span>
                        </li>
                        {if isset($Artikel->dMHD) && isset($Artikel->dMHD_de)}
                            <li class="item row attr-best-before" title="{lang key='productMHDTool' section='global'}">
                                <span class="attr-label col-sm-5">{lang key="productMHD" section="global"}: </span> <span class="value col-sm-7">{$Artikel->dMHD_de}</span>
                            </li>
                        {/if}
                        {if isset($Artikel->cGewicht) && $Einstellungen.artikeluebersicht.artikeluebersicht_gewicht_anzeigen === 'Y' && $Artikel->fGewicht > 0}
                            <li class="item row attr-weight">
                                <span class="attr-label col-sm-5">{lang key="shippingWeight" section="global"}: </span>
                                <span class="value col-sm-7">{$Artikel->cGewicht} {lang key="weightUnit" section="global"}</span>
                            </li>
                        {/if}
                        {if isset($Artikel->cArtikelgewicht) && $Einstellungen.artikeluebersicht.artikeluebersicht_artikelgewicht_anzeigen === 'Y' && $Artikel->fArtikelgewicht > 0}
                            <li class="item row attr-weight weight-unit-article">
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
                    {if ($hasOnlyListableVariations == 0 || $Artikel->bHasKonfig) && $Artikel->oVariationKombiVorschau_arr|@count > 0 && $Artikel->oVariationKombiVorschau_arr && $Einstellungen.artikeluebersicht.artikeluebersicht_varikombi_anzahl > 0}
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
            <form id="buy_form_{$Artikel->kArtikel}" action="index.php" method="post" class="form form-basket" data-toggle="basket-add">
                {block name="form-basket"}
                    {assign var=price_image value=""}
                    {if isset($Artikel->Preise->strPreisGrafik_Suche)}
                        {assign var=$price_image value=$Artikel->Preise->strPreisGrafik_Suche}
                    {/if}
                    {include file="productdetails/price.tpl" Artikel=$Artikel price_image=$price_image tplscope=$tplscope}
                    <div class="delivery-status">
                    {block name="delivery-status"}
                        {assign var=anzeige value=$Einstellungen.artikeluebersicht.artikeluebersicht_lagerbestandsanzeige}
                        {if $Artikel->nErscheinendesProdukt}
                            <div class="availablefrom">
                                <small>{lang key="productAvailable" section="global"}: {$Artikel->Erscheinungsdatum_de}</small>
                            </div>
                            {if $Einstellungen.global.global_erscheinende_kaeuflich === 'Y' && $Artikel->inWarenkorbLegbar === 1}
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
                    {/block}
                    </div>
                    {if $hasOnlyListableVariations > 0 && !$Artikel->bHasKonfig}
                        <div class="hidden-xs basket-variations">
                            {assign var="singleVariation" value=true}
                            {include file="productdetails/variation.tpl" simple=$Artikel->isSimpleVariation showMatrix=false smallView=true ohneFreifeld=($hasOnlyListableVariations == 2)}
                        </div>
                    {/if}
                    <div class="hidden-xs basket-details">
                        {block name="basket-details"}
                            {if ($Artikel->inWarenkorbLegbar === 1 || ($Artikel->nErscheinendesProdukt === 1 && $Einstellungen.global.global_erscheinende_kaeuflich === 'Y')) &&
                                (($Artikel->nIstVater === 0 && $Artikel->Variationen|@count === 0) || $hasOnlyListableVariations === 1) && !$Artikel->bHasKonfig
                            }
                                <div class="quantity-wrapper form-group top7">
                                    {if $Artikel->cEinheit}
                                        <div class="input-group input-group-sm">
                                            <input type="number" min="0"{if $Artikel->fAbnahmeintervall > 0} step="{$Artikel->fAbnahmeintervall}"{/if} size="2" onfocus="this.setAttribute('autocomplete', 'off');" id="quantity{$Artikel->kArtikel}" class="quantity form-control text-right" name="anzahl" value="{if $Artikel->fAbnahmeintervall > 0}{if $Artikel->fMindestbestellmenge > $Artikel->fAbnahmeintervall}{$Artikel->fMindestbestellmenge}{else}{$Artikel->fAbnahmeintervall}{/if}{else}1{/if}" />
                                            <span class="input-group-addon unit">{$Artikel->cEinheit}</span>
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

                    {if $Artikel->kArtikelVariKombi > 0}
                        <input type="hidden" name="aK" value="{$Artikel->kArtikelVariKombi}" />
                    {/if}
                    {if isset($Artikel->kVariKindArtikel)}
                        <input type="hidden" name="VariKindArtikel" value="{$Artikel->kVariKindArtikel}" />
                    {/if}
                    <input type="hidden" name="a" value="{$Artikel->kArtikel}" />
                    <input type="hidden" name="wke" value="1" />
                    <input type="hidden" name="overview" value="1" />
                    <input type="hidden" name="Sortierung" value="{if isset($Suchergebnisse->Sortierung)}{$Suchergebnisse->Sortierung}{/if}" />
                    {if isset($Suchergebnisse->Seitenzahlen) && $Suchergebnisse->Seitenzahlen->AktuelleSeite > 1}
                        <input type="hidden" name="seite" value="{$Suchergebnisse->Seitenzahlen->AktuelleSeite}" />
                    {/if}
                    {if $NaviFilter->hasCategory()}
                        <input type="hidden" name="k" value="{$NaviFilter->getCategory()->getValue()}" />
                    {/if}
                    {if $NaviFilter->hasManufacturer()}
                        <input type="hidden" name="h" value="{$NaviFilter->getManufacturer()->getValue()}" />
                    {/if}
                    {if $NaviFilter->hasSearchQuery()}
                        <input type="hidden" name="l" value="{$NaviFilter->getSearch()->getValue()}" />
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
            </form>

            <form action="" method="post" class="hidden-sm hidden-xs product-actions" data-toggle="product-actions">
                {$jtl_token}
                <div class="actions btn-group btn-group-xs btn-group-justified" role="group" aria-label="...">
                {block name="product-actions"}
                    {if !($Artikel->nIstVater && $Artikel->kVaterArtikel === 0)}
                        {if $Einstellungen.artikeluebersicht.artikeluebersicht_vergleichsliste_anzeigen === 'Y'}
                            <div class="btn-group btn-group-xs" role="group">
                                <button name="Vergleichsliste" type="submit" class="compare btn btn-default" title="{lang key="addToCompare" section="productOverview"}">
                                    <span class="fa fa-tasks"></span>
                                </button>
                            </div>
                        {/if}
                        {if $Einstellungen.global.global_wunschliste_anzeigen === 'Y' && $Einstellungen.artikeluebersicht.artikeluebersicht_wunschzettel_anzeigen === 'Y'}
                            <div class="btn-group btn-group-xs" role="group">
                                <button name="Wunschliste" type="submit" class="wishlist btn btn-default" title="{lang key="addToWishlist" section="productDetails"}">
                                    <span class="fa fa-heart"></span>
                                </button>
                            </div>
                        {/if}
                        {if $Artikel->verfuegbarkeitsBenachrichtigung === 3 && (($Artikel->cLagerBeachten === 'Y' && $Artikel->cLagerKleinerNull !== 'Y') || $Artikel->cLagerBeachten !== 'Y')}
                            <div class="btn-group btn-group-xs" role="group">
                                <button type="button" id="n{$Artikel->kArtikel}" class="popup-dep notification btn btn-default btn-left" title="{lang key="requestNotification" section="global"}">
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
{if $Artikel->verfuegbarkeitsBenachrichtigung === 3}
    <div id="popupn{$Artikel->kArtikel}" class="hidden">
        {include file='productdetails/availability_notification_form.tpl' position="popup" tplscope='artikeldetails'}
    </div>
{/if}
