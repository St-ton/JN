{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if $Einstellungen.template.productlist.variation_select_productlist === 'N' || $Einstellungen.template.productlist.hover_productlist !== 'Y'}
    {assign var='hasOnlyListableVariations' value=0}
{else}
    {hasOnlyListableVariations artikel=$Artikel maxVariationCount=$Einstellungen.template.productlist.variation_select_productlist maxWerteCount=$Einstellungen.template.productlist.variation_max_werte_productlist assign='hasOnlyListableVariations'}
{/if}
<div id="result-wrapper_buy_form_{$Artikel->kArtikel}" class="product-cell{if $Einstellungen.template.productlist.hover_productlist === 'Y'} hover-enabled{/if}{if isset($listStyle) && $listStyle === 'list'} active{/if}">
    <div class="product-body row {if $tplscope !== 'list'} text-center{/if}">
        <div class="col-xs-3 text-center">
            {block name='image-wrapper'}
                <a class="image-wrapper" href="{$Artikel->cURLFull}">
                    {if isset($Artikel->Bilder[0]->cAltAttribut)}
                        {assign var='alt' value=$Artikel->Bilder[0]->cAltAttribut|strip_tags|truncate:60|escape:'html'}
                    {else}
                        {assign var='alt' value=$Artikel->cName}
                    {/if}

                    {include file='snippets/image.tpl' src=$Artikel->Bilder[0]->cURLNormal alt=$alt tplscope=$tplscope}

                    {block name='searchspecial-overlay'}
                        {if isset($Artikel->oSuchspecialBild)}
                            {include file='snippets/searchspecials.tpl' src=$Artikel->oSuchspecialBild->cURLKlein alt=$alt}
                        {/if}
                    {/block}

                    {if $Einstellungen.template.productlist.quickview_productlist === 'Y' && !$Artikel->bHasKonfig}
                        <span class="quickview badge hidden-xs" data-src="{$Artikel->cURLFull}" data-target="buy_form_{$Artikel->kArtikel}" title="{$Artikel->cName}">{lang key='downloadPreview' section='productDownloads'}</span>
                    {/if}
                </a>
            {/block}
        </div>
        <div class="col-xs-5 product-detail">
            {block name='product-title'}
                <h4 class="title" itemprop="name">
                    <a href="{$Artikel->cURLFull}">{$Artikel->cName}</a>
                </h4>
                <meta itemprop="url" content="{$Artikel->cURLFull}">
            {/block}
            {if $Einstellungen.bewertung.bewertung_anzeigen === 'Y'}
                {include file='productdetails/rating.tpl' stars=$Artikel->fDurchschnittsBewertung}
            {/if}
            {block name='product-manufacturer'}
                {if $Einstellungen.artikeluebersicht.artikeluebersicht_hersteller_anzeigen !== 'N'}
                    <div class="media hidden-xs top0 bottom5" itemprop="manufacturer" itemscope itemtype="http://schema.org/Organization">
                        {if ($Einstellungen.artikeluebersicht.artikeluebersicht_hersteller_anzeigen === 'BT'
                            || $Einstellungen.artikeluebersicht.artikeluebersicht_hersteller_anzeigen === 'B')
                            && !empty($Artikel->cHerstellerBildKlein)}
                            <div class="media-left">
                                {if !empty($Artikel->cHerstellerHomepage)}<a href="{$Artikel->cHerstellerHomepage}">{/if}
                                    <img src="{$Artikel->cHerstellerBildKlein}" alt="" class="img-xs">
                                    <meta itemprop="image" content="{$ShopURL}/{$Artikel->cHerstellerBildKlein}">
                                {if !empty($Artikel->cHerstellerHomepage)}</a>{/if}
                            </div>
                        {/if}
                        {if ($Einstellungen.artikeluebersicht.artikeluebersicht_hersteller_anzeigen === 'BT'
                            || $Einstellungen.artikeluebersicht.artikeluebersicht_hersteller_anzeigen === 'Y')
                            && !empty($Artikel->cHersteller)}
                            <div class="media-body">
                                <span class="small text-uppercase">
                                    {if !empty($Artikel->cHerstellerHomepage)}<a href="{$Artikel->cHerstellerHomepage}" itemprop="url">{/if}
                                        <span itemprop="name">{$Artikel->cHersteller}</span>
                                    {if !empty($Artikel->cHerstellerHomepage)}</a>{/if}
                                </span>
                            </div>
                        {/if}
                    </div>
                {/if}
            {/block}

            <div class="product-info hidden-xs">
                {block name='product-info'}
                    {if $Einstellungen.artikeluebersicht.artikeluebersicht_kurzbeschreibung_anzeigen === 'Y' && $Artikel->cKurzBeschreibung}
                        <div class="shortdescription" itemprop="description">
                            {$Artikel->cKurzBeschreibung}
                        </div>
                    {/if}
                    <ul class="attr-group list-unstyled small text-muted top10 hidden-xs">
                        <li class="item row attr-sku">
                            <span class="attr-label col-sm-5">{lang key='productNo'}: </span> <span class="value col-sm-7" itemprop="sku">{$Artikel->cArtNr}</span>
                        </li>
                        {if !empty($Artikel->cISBN)
                            && ($Einstellungen.artikeldetails.isbn_display === 'L'
                                || $Einstellungen.artikeldetails.isbn_display === 'DL')}
                            <li class="item row">
                                <span class="attr-label col-sm-5">{lang key='isbn'}: </span> <span class="value col-sm-7">{$Artikel->cISBN}</span>
                            </li>
                        {/if}
                        {if !empty($Artikel->cUNNummer) && !empty($Artikel->cGefahrnr)
                            && ($Einstellungen.artikeldetails.adr_hazard_display === 'L'
                                || $Einstellungen.artikeldetails.adr_hazard_display === 'DL')}
                            <li class="item row">
                                <span class="attr-label col-sm-5">
                                    {lang key='adrHazardSign'}:
                                </span>
                                <div class="value col-sm-7">
                                    <table class="adr-table value">
                                        <tr>
                                            <td>{$Artikel->cGefahrnr}</td>
                                        </tr>
                                        <tr>
                                            <td>{$Artikel->cUNNummer}</td>
                                        </tr>
                                    </table>
                                </div>
                            </li>
                        {/if}
                        {if isset($Artikel->dMHD) && isset($Artikel->dMHD_de)}
                            <li class="item row attr-best-before" title="{lang key='productMHDTool'}">
                                <span class="attr-label col-sm-5">{lang key='productMHD'}: </span> <span class="value col-sm-7">{$Artikel->dMHD_de}</span>
                            </li>
                        {/if}
                        {if $Einstellungen.artikeluebersicht.artikeluebersicht_gewicht_anzeigen === 'Y' && isset($Artikel->cGewicht) && $Artikel->fGewicht > 0}
                            <li class="item row attr-weight">
                                <span class="attr-label col-sm-5">{lang key='shippingWeight'}: </span>
                                <span class="value col-sm-7">{$Artikel->cGewicht} {lang key='weightUnit'}</span>
                            </li>
                        {/if}
                        {if $Einstellungen.artikeluebersicht.artikeluebersicht_artikelgewicht_anzeigen === 'Y' && isset($Artikel->cArtikelgewicht) && $Artikel->fArtikelgewicht > 0}
                            <li class="item row attr-weight weight-unit-article hidden-sm">
                                <span class="attr-label col-sm-5">{lang key='productWeight'}: </span>
                                <span class="value col-sm-7">{$Artikel->cArtikelgewicht} {lang key='weightUnit'}</span>
                            </li>
                        {/if}
                        {if $Einstellungen.artikeluebersicht.artikeluebersicht_artikelintervall_anzeigen === 'Y' && $Artikel->fAbnahmeintervall > 0}
                            <li class="item row attr-quantity-scale">
                                <span class="attr-label col-sm-5">{lang key='purchaseIntervall' section='productOverview'}: </span>
                                <span class="value col-sm-7">{$Artikel->fAbnahmeintervall} {$Artikel->cEinheit}</span>
                            </li>
                        {/if}
                        {if count($Artikel->Variationen) > 0}
                            <li class="item row attr-variations">
                                <span class="attr-label col-sm-5">{lang key='variationsIn' section='productOverview'}: </span>
                                <span class="value-group col-sm-7">{foreach $Artikel->Variationen as $variation}{if !$variation@first}, {/if}
                                <span class="value">{$variation->cName}</span>{/foreach}</span>
                            </li>
                        {/if}
                    </ul>
                    {if $Einstellungen.artikeluebersicht.artikeluebersicht_varikombi_anzahl > 0 && $Artikel->oVariationKombiVorschau_arr !== null && $Artikel->oVariationKombiVorschau_arr|@count > 0}
                        <div class="varikombis-thumbs hidden-md hidden-sm">
                            {foreach $Artikel->oVariationKombiVorschau_arr as $oVariationKombiVorschau}
                                <a href="{$oVariationKombiVorschau->cURL}" class="thumbnail pull-left"><img src="{$oVariationKombiVorschau->cBildMini}" alt="" /></a>
                            {/foreach}
                        </div>
                    {/if}
                {/block}
            </div>
        </div>
        <div class="col-xs-4 product-detail">
            <div class="product-detail-cell" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
                <link itemprop="businessFunction" href="http://purl.org/goodrelations/v1#Sell" />
                {block name='form-basket'}
                    {include file='productdetails/price.tpl' Artikel=$Artikel tplscope=$tplscope}
                    <div class="delivery-status">
                        {block name='delivery-status'}
                            {assign var=anzeige value=$Einstellungen.artikeluebersicht.artikeluebersicht_lagerbestandsanzeige}
                            {if $Artikel->nErscheinendesProdukt}
                                <div class="availablefrom">
                                    <small>{lang key='productAvailableFrom'}: {$Artikel->Erscheinungsdatum_de}</small>
                                </div>
                                {if $Einstellungen.global.global_erscheinende_kaeuflich === 'Y' && $Artikel->inWarenkorbLegbar === 1}
                                    <div class="attr attr-preorder"><small class="value">{lang key='preorderPossible'}</small></div>
                                {/if}
                            {elseif $anzeige !== 'nichts'
                                && $Einstellungen.artikeluebersicht.artikeluebersicht_lagerbestandanzeige_anzeigen !== 'N'
                                && $Artikel->cLagerBeachten === 'Y'
                                && ($Artikel->cLagerKleinerNull === 'N'
                                    || $Einstellungen.artikeluebersicht.artikeluebersicht_lagerbestandanzeige_anzeigen === 'U')
                                && $Artikel->fLagerbestand <= 0
                                && $Artikel->fZulauf > 0
                                && isset($Artikel->dZulaufDatum_de)}
                                {assign var=cZulauf value=$Artikel->fZulauf|cat:':::'|cat:$Artikel->dZulaufDatum_de}
                                <div class="signal_image status-1"><small>{lang key='productInflowing' section='productDetails' printf=$cZulauf}</small></div>
                            {elseif $anzeige !== 'nichts'
                                && $Einstellungen.artikeluebersicht.artikeluebersicht_lagerbestandanzeige_anzeigen !== 'N'
                                && $Artikel->cLagerBeachten === 'Y'
                                && $Artikel->fLagerbestand <= 0
                                && $Artikel->fLieferantenlagerbestand > 0
                                && $Artikel->fLieferzeit > 0
                                && ($Artikel->cLagerKleinerNull === 'N'
                                    || $Einstellungen.artikeluebersicht.artikeluebersicht_lagerbestandanzeige_anzeigen === 'U')}
                                <div class="signal_image status-1"><small>{lang key='supplierStockNotice' printf=$Artikel->fLieferzeit}</small></div>
                            {elseif $anzeige === 'verfuegbarkeit' || $anzeige === 'genau'}
                                <div class="signal_image status-{$Artikel->Lageranzeige->nStatus}"><small>{$Artikel->Lageranzeige->cLagerhinweis[$anzeige]}</small></div>
                            {elseif $anzeige === 'ampel'}
                                <div class="signal_image status-{$Artikel->Lageranzeige->nStatus}"><small>{$Artikel->Lageranzeige->AmpelText}</small></div>
                            {/if}
                            {if $Artikel->cEstimatedDelivery}
                                <div class="estimated_delivery hidden-xs">
                                    <small>{lang key='shippingTime'}: {$Artikel->cEstimatedDelivery}</small>
                                </div>
                            {/if}
                        {/block}
                    </div>
                    <form action="" method="post" class="hidden-xs product-actions" data-toggle="product-actions">
                        {$jtl_token}
                        <div class="actions btn-group btn-group-xs btn-group-justified" role="group" aria-label="...">
                            {block name='product-actions'}
                                {if !($Artikel->nIstVater && $Artikel->kVaterArtikel === 0)}
                                    {if $Einstellungen.artikeluebersicht.artikeluebersicht_vergleichsliste_anzeigen === 'Y'}
                                        <div class="btn-group btn-group-xs" role="group">
                                            <button name="Vergleichsliste" type="submit" class="compare btn btn-default" title="{lang key='addToCompare' section='productOverview'}">
                                                <span class="fa fa-tasks"></span>
                                            </button>
                                        </div>
                                    {/if}
                                    {if $Einstellungen.global.global_wunschliste_anzeigen === 'Y' && $Einstellungen.artikeluebersicht.artikeluebersicht_wunschzettel_anzeigen === 'Y'}
                                        <div class="btn-group btn-group-xs" role="group">
                                            <button name="Wunschliste" type="submit" class="wishlist btn btn-default" title="{lang key='addToWishlist' section='productDetails'}">
                                                <span class="fa fa-heart"></span>
                                            </button>
                                        </div>
                                    {/if}
                                    {if $Artikel->verfuegbarkeitsBenachrichtigung === 3 && (($Artikel->cLagerBeachten === 'Y' && $Artikel->cLagerKleinerNull !== 'Y') || $Artikel->cLagerBeachten !== 'Y')}
                                        <div class="btn-group btn-group-xs" role="group">
                                            <button type="button" id="n{$Artikel->kArtikel}" class="popup-dep notification btn btn-default btn-left" title="{lang key='requestNotification'}">
                                                <span class="fa fa-bell"></span>
                                            </button>
                                        </div>
                                    {/if}
                                {/if}
                            {/block}
                        </div>
                        <input type="hidden" name="a" value="{if !empty({$Artikel->kVariKindArtikel})}{$Artikel->kVariKindArtikel}{else}{$Artikel->kArtikel}{/if}" />
                    </form>
                    <div class="expandable">
                        <form id="buy_form_{$Artikel->kArtikel}" action="{$ShopURL}" method="post" class="form form-basket evo-validate" data-toggle="basket-add">
                            {block name='form-expandable'}
                            {if $hasOnlyListableVariations > 0 && !$Artikel->bHasKonfig && $Artikel->kEigenschaftKombi === 0}
                                <div class="hidden-xs basket-variations">
                                    {assign var='singleVariation' value=true}
                                    {include file='productdetails/variation.tpl' simple=$Artikel->isSimpleVariation showMatrix=false smallView=true ohneFreifeld=($hasOnlyListableVariations == 2)}
                                </div>
                            {/if}
                            <div class="hidden-xs basket-details">
                                {block name='basket-details'}
                                    {if ($Artikel->inWarenkorbLegbar === 1 || ($Artikel->nErscheinendesProdukt === 1 && $Einstellungen.global.global_erscheinende_kaeuflich === 'Y'))
                                        && (($Artikel->nIstVater === 0 && $Artikel->Variationen|@count === 0) || $hasOnlyListableVariations === 1) && !$Artikel->bHasKonfig}
                                        <div class="quantity-wrapper form-group top7">
                                            {if $Artikel->nIstVater && $Artikel->kVaterArtikel == 0}
                                                <p class="alert alert-info choose-variations">{lang key='chooseVariations' section='messages'}</p>
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
                                                                    title="{lang key='addToCart'}">
                                                                <span><i class="fa fa-shopping-cart"></i> {lang key='addToCart'}</span>
                                                            </button>
                                                        </span>
                                                    </div>
                                                </div>
                                            {/if}
                                        </div>
                                    {else}
                                        <div class="top7 form-group">
                                            <a class="btn btn-default btn-sm btn-block" role="button" href="{$Artikel->cURLFull}">{lang key='details'}</a>
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
                            {if $Suchergebnisse->getPages()->getCurrentPage() > 1}
                                <input type="hidden" name="seite" value="{$Suchergebnisse->getPages()->getCurrentPage()}" />
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
                                {assign var=cfv value=$NaviFilter->getCategoryFilter()->getValue()}
                                {if is_array($cfv)}
                                    {foreach $cfv as $val}
                                        <input type="hidden" name="hf" value="{$val}" />
                                    {/foreach}
                                {else}
                                    <input type="hidden" name="kf" value="{$cfv}" />
                                {/if}
                            {/if}
                            {if $NaviFilter->hasManufacturerFilter()}
                                {assign var=mfv value=$NaviFilter->getManufacturerFilter()->getValue()}
                                {if is_array($mfv)}
                                    {foreach $mfv as $val}
                                        <input type="hidden" name="hf" value="{$val}" />
                                    {/foreach}
                                {else}
                                    <input type="hidden" name="hf" value="{$mfv}" />
                                {/if}
                            {/if}
                            {if $NaviFilter->hasAttributeFilter()}
                                {foreach $NaviFilter->getAttributeFilter() as $attributeFilter}
                                    <input type="hidden" name="mf{$attributeFilter@iteration}" value="{$attributeFilter->getValue()}" />
                                {/foreach}
                            {/if}
                            {if $NaviFilter->hasTagFilter()}
                                {foreach $NaviFilter->getTagFilter() as $tagFilter}
                                    <input type="hidden" name="tf{$tagFilter@iteration}" value="{$tagFilter->getValue()}" />
                                {/foreach}
                            {/if}
                            {/block}
                        </form>
                    </div>
                {/block}
            </div>
        </div>
    </div>
</div>

{if $Artikel->verfuegbarkeitsBenachrichtigung === 3}
    <div id="popupn{$Artikel->kArtikel}" class="hidden">
        {include file='productdetails/availability_notification_form.tpl' position='popup' tplscope='artikeldetails'}
    </div>
{/if}
