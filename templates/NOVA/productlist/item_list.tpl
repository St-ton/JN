{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='productlist-item-list'}
    {if $Einstellungen.template.productlist.variation_select_productlist === 'N'}
        {assign var=hasOnlyListableVariations value=0}
    {else}
        {hasOnlyListableVariations artikel=$Artikel maxVariationCount=$Einstellungen.template.productlist.variation_select_productlist maxWerteCount=$Einstellungen.template.productlist.variation_max_werte_productlist assign='hasOnlyListableVariations'}
    {/if}
    <div id="result-wrapper_buy_form_{$Artikel->kArtikel}" data-wrapper="true" class="productbox productbox-row productbox-show-variations {if $Einstellungen.template.productlist.hover_productlist === 'Y'} productbox-hover{/if}{if isset($listStyle) && $listStyle === 'list'} active{/if}">
        <div class="productbox-inner">
        {row}
            {col cols=12 md=4 lg=3}
                {block name='productlist-item-list-image'}
                    <div class="productbox-image">
                        {if isset($Artikel->Bilder[0]->cAltAttribut)}
                            {assign var=alt value=$Artikel->Bilder[0]->cAltAttribut|strip_tags|truncate:60|escape:'html'}
                        {else}
                            {assign var=alt value=$Artikel->cName}
                        {/if}
                        {if isset($Artikel->oSuchspecialBild)}
                            {block name='productlist-item-list-include-searchspecials'}
                                {include file='snippets/ribbon.tpl'}
                            {/block}
                        {/if}
                        {block name="productlist-item-list-image"}
                            <div class="productbox-images">
                                <div class="clearfix list-gallery carousel carousel-btn-arrows">
                                    {foreach $Artikel->Bilder as $image}
                                        {strip}
                                            <div>
                                                {image data=['lazy'=>$image->cURLMini, 'srcset'=>"{$image->cURLMini} {$Einstellungen.bilder.bilder_artikel_mini_breite}w,
                                                 {$image->cURLKlein} {$Einstellungen.bilder.bilder_artikel_klein_breite}w,
                                                 {$image->cURLNormal} {$Einstellungen.bilder.bilder_artikel_normal_breite}w"]
                                                     sizes="(min-width: 1200px) 175px,95vw"
                                                     alt=$image->cAltAttribut|escape:'html'
                                                     src=$image->cURLMini
                                                     fluid=true
                                                    class='w-100'}
                                            </div>
                                        {/strip}
                                    {/foreach}
                                </div>
                                {block name='productlist-item-list-include-productlist-actions'}
                                    <div class="productbox-quick-actions productbox-onhover">
                                        {include file='productlist/productlist_actions.tpl'}
                                    </div>
                                {/block}
                            </div>
                        {/block}
                        {if $smarty.session.Kundengruppe->mayViewPrices()
                            && isset($Artikel->SieSparenX)
                            && $Artikel->SieSparenX->anzeigen == 1
                            && $Artikel->SieSparenX->nProzent > 0
                            && !$NettoPreise
                            && $Artikel->taxData['tax'] > 0
                        }
                            {block name='productlist-item-list-badge-yousave'}
                                <div class="productbox-sale-percentage">
                                    <div class="ribbon ribbon-7 productbox-ribbon">{$Artikel->SieSparenX->nProzent}%</div>
                                </div>
                            {/block}
                        {/if}
                    </div>
                {/block}
            {/col}
            {col md=''}
                {block name='productlist-item-list-title'}
                    <div class="productbox-title" itemprop="name">
                        {link href=$Artikel->cURLFull}{$Artikel->cName}{/link}
                    </div>
                    <meta itemprop="url" content="{$Artikel->cURLFull}">
                {/block}
                {form id="buy_form_{$Artikel->kArtikel}" action=$ShopURL class="form form-basket evo-validate" data=["toggle" => "basket-add"]}
                    {row}
                        {col cols=12 xl=4 class='productbox-details'}
                            {if $Einstellungen.artikeluebersicht.artikeluebersicht_kurzbeschreibung_anzeigen === 'Y' && $Artikel->cKurzBeschreibung}
                                {block name='productlist-item-list-description'}
                                    <div class="small mb-1" itemprop="description">
                                        {$Artikel->cKurzBeschreibung}
                                    </div>
                                {/block}
                            {/if}
                            {block name='productlist-item-list-details'}
                                {formrow tag='dl' class="text-nowrap"}
                                    {col tag='dt' cols=6}{lang key='productNo'}:{/col}
                                    {col tag='dd' cols=6}{$Artikel->cArtNr}{/col}
                                    {if count($Artikel->Variationen) > 0}
                                        {col tag='dt' cols=6}{lang key='variationsIn' section='productOverview'}:{/col}
                                        {col tag='dd' cols=6}
                                            <ul class="list-unstyled">
                                                {foreach $Artikel->Variationen as $variation}
                                                    <li>{$variation->cName}<li>
                                                    {if $variation@index === 3 && !$variation@last}
                                                        <li>&hellip;</li>
                                                        {break}
                                                    {/if}
                                                {/foreach}
                                            </ul>
                                        {/col}
                                    {/if}
                                    {if !empty($Artikel->cISBN)
                                        && ($Einstellungen.artikeldetails.isbn_display === 'L'
                                            || $Einstellungen.artikeldetails.isbn_display === 'DL')}
                                        {col tag='dt' cols=6}{lang key='isbn'}:{/col}
                                        {col tag='dd' cols=6}{$Artikel->cISBN}{/col}
                                    {/if}

                                    {block name='productlist-item-list-manufacturer'}
                                        {if $Einstellungen.artikeluebersicht.artikeluebersicht_hersteller_anzeigen !== 'N' && !empty($Artikel->cHersteller)}
                                            {col tag='dt' cols=6}{lang key='manufacturer' section='productDetails'}:{/col}
                                            {col tag='dd' cols=6 itemprop='manufacturer' itemscope=true itemtype='http://schema.org/Organization'}
                                                {if !empty($Artikel->cHerstellerHomepage)}
                                                    <a href="{$Artikel->cHerstellerHomepage}" class="text-decoration-none" itemprop="url">
                                                {/if}
                                                {if ($Einstellungen.artikeluebersicht.artikeluebersicht_hersteller_anzeigen === 'BT'
                                                    || $Einstellungen.artikeluebersicht.artikeluebersicht_hersteller_anzeigen === 'B')
                                                    && !empty($Artikel->cHerstellerBildKlein)}
                                                    {image src=$Artikel->cHerstellerBildKlein alt=$Artikel->cHersteller class="img-xs"}
                                                    <meta itemprop="image" content="{$ShopURL}/{$Artikel->cHerstellerBildKlein}">
                                                {/if}
                                                {if ($Einstellungen.artikeluebersicht.artikeluebersicht_hersteller_anzeigen === 'BT'
                                                    || $Einstellungen.artikeluebersicht.artikeluebersicht_hersteller_anzeigen === 'Y')
                                                    && !empty($Artikel->cHersteller)}
                                                        <span itemprop="name">{$Artikel->cHersteller}</span>
                                                {/if}
                                                {if !empty($Artikel->cHerstellerHomepage)}</a>{/if}
                                            {/col}
                                        {/if}
                                    {/block}

                                    {if !empty($Artikel->cUNNummer) && !empty($Artikel->cGefahrnr)
                                        && ($Einstellungen.artikeldetails.adr_hazard_display === 'L'
                                            || $Einstellungen.artikeldetails.adr_hazard_display === 'DL')}
                                        {col tag='dt' cols=6}{lang key='adrHazardSign'}:{/col}
                                        {col tag='dd' cols=6}{$Artikel->cGefahrnr}<br>{$Artikel->cUNNummer}{/col}
                                    {/if}
                                    {if isset($Artikel->dMHD) && isset($Artikel->dMHD_de)}
                                        {col tag='dt' cols=6 title="{lang key='productMHDTool'}"}{lang key='productMHD'}:{/col}
                                        {col tag='dd' cols=6}{$Artikel->dMHD_de}{/col}
                                    {/if}
                                    {if $Einstellungen.artikeluebersicht.artikeluebersicht_gewicht_anzeigen === 'Y' && isset($Artikel->cGewicht) && $Artikel->fGewicht > 0}
                                        {col tag='dt' cols=6}{lang key='shippingWeight'}:{/col}
                                        {col tag='dd' cols=6}{$Artikel->cGewicht} {lang key='weightUnit'}{/col}
                                    {/if}
                                    {if $Einstellungen.artikeluebersicht.artikeluebersicht_artikelgewicht_anzeigen === 'Y' && isset($Artikel->cArtikelgewicht) && $Artikel->fArtikelgewicht > 0}
                                        {col tag='dt' cols=6}{lang key='productWeight'}:{/col}
                                        {col tag='dd' cols=6}{$Artikel->cArtikelgewicht} {lang key='weightUnit'}{/col}
                                    {/if}
                                    {if $Einstellungen.artikeluebersicht.artikeluebersicht_artikelintervall_anzeigen === 'Y' && $Artikel->fAbnahmeintervall > 0}
                                        {col tag='dt' cols=6}{lang key='purchaseIntervall' section='productOverview'}:{/col}
                                        {col tag='dd' cols=6}{$Artikel->fAbnahmeintervall} {$Artikel->cEinheit}{/col}
                                    {/if}
                                    {if $Einstellungen.bewertung.bewertung_anzeigen === 'Y' && $Artikel->fDurchschnittsBewertung > 0}
                                        {block name='productlist-item-list-rating'}
                                            {col tag='dt' cols=6}{lang key='ratingAverage'}:{/col}
                                            {col tag='dd' cols=6}
                                                {link href="{$Artikel->cURLFull}#tab-votes"
                                                    class="d-print-none text-decoration-none"
                                                    aria=["label"=>{lang key='Votes'}]}
                                                    {block name='productlist-item-list-include-rating'}
                                                        {include file='productdetails/rating.tpl' stars=$Artikel->fDurchschnittsBewertung}
                                                    {/block}
                                                {/link}
                                            {/col}
                                        {/block}
                                    {/if}
                                {/formrow}
                            {/block}
                        {/col}
                        {col cols=12 xl=4 class='productbox-variations'}
                            {if $hasOnlyListableVariations > 0 && !$Artikel->bHasKonfig && $Artikel->kEigenschaftKombi === 0}
                                {block name='productlist-item-list-form-variations'}
                                    <div class="productbox-onhover">
                                        {block name='productlist-item-list-form-include-variation'}
                                            {include file='productdetails/variation.tpl' simple=$Artikel->isSimpleVariation showMatrix=false smallView=true ohneFreifeld=($hasOnlyListableVariations == 2)}
                                        {/block}
                                    </div>
                                {/block}
                            {/if}
                        {/col}
                        {col cols=12 xl=4 class='productbox-options' itemprop='offers' itemscope=true itemtype='http://schema.org/Offer'}
                            <link itemprop="businessFunction" href="http://purl.org/goodrelations/v1#Sell" />
                            {block name='productlist-item-list-form'}
                                {block name='productlist-item-list-include-price'}
                                    <div class="mb-3">
                                        {include file='productdetails/price.tpl' Artikel=$Artikel tplscope=$tplscope}
                                    </div>
                                {/block}
                                {block name='productlist-item-list-delivery-status'}
                                    <div class="delivery-status mb-3">
                                        {assign var=anzeige value=$Einstellungen.artikeluebersicht.artikeluebersicht_lagerbestandsanzeige}
                                        {if $Artikel->inWarenkorbLegbar === $smarty.const.INWKNICHTLEGBAR_UNVERKAEUFLICH}
                                            <span class="status"><small>{lang key='productUnsaleable' section='productDetails'}</small></span>
                                        {elseif $Artikel->nErscheinendesProdukt}
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
                                            <div class="signal_image status-{$Artikel->Lageranzeige->nStatus}">{$Artikel->Lageranzeige->AmpelText}</div>
                                        {/if}
                                        {if $Artikel->cEstimatedDelivery}
                                            <div class="estimated_delivery">
                                                {lang key='shippingTime'}: {$Artikel->cEstimatedDelivery}
                                            </div>
                                        {/if}
                                    </div>
                                {/block}
                            {/block}
                            {block name='productlist-item-list-basket-details'}
                                <div class="form-row productbox-onhover productbox-actions mt-5">
                                    {if ($Artikel->inWarenkorbLegbar === 1 || ($Artikel->nErscheinendesProdukt === 1 && $Einstellungen.global.global_erscheinende_kaeuflich === 'Y'))
                                    && (($Artikel->nIstVater === 0 && $Artikel->Variationen|@count === 0) || $hasOnlyListableVariations === 1) && !$Artikel->bHasKonfig}
                                        {if $Artikel->nIstVater && $Artikel->kVaterArtikel == 0}
                                            {col cols=12}
                                                {alert variation="info" class="choose-variations text-left"}
                                                    {lang key='chooseVariations' section='messages'}
                                                {/alert}
                                            {/col}
                                        {else}
                                            {col cols=12}
                                                {input type="{if $Artikel->cTeilbar === 'Y' && $Artikel->fAbnahmeintervall == 0}text{else}number{/if}" min="0"
                                                    step="{if $Artikel->fAbnahmeintervall > 0}{$Artikel->fAbnahmeintervall}{/if}"
                                                    size="2"
                                                    id="quantity{$Artikel->kArtikel}"
                                                    class="quantity"
                                                    name="anzahl"
                                                    autocomplete="off"
                                                    aria=["label"=>{lang key='quantity'}]
                                                    data=["decimals"=>{getDecimalLength quantity=$Artikel->fAbnahmeintervall}]
                                                    value="{if $Artikel->fAbnahmeintervall > 0}{if $Artikel->fMindestbestellmenge > $Artikel->fAbnahmeintervall}{$Artikel->fMindestbestellmenge}{else}{$Artikel->fAbnahmeintervall}{/if}{else}1{/if}"}
                                            {/col}
                                            {col cols=12}
                                                {button type="submit"
                                                    variant="primary"
                                                    block=true id="submit{$Artikel->kArtikel}"
                                                    title="{lang key='addToCart'}"
                                                    class="mt-3"
                                                    aria=["label"=>{lang key='addToCart'}]}
                                                    {lang key='addToCart'}
                                                {/button}
                                            {/col}
                                        {/if}
                                    {else}
                                        {col cols=12}
                                            {link class="btn btn-secondary btn-block" role="button" href=$Artikel->cURLFull}
                                                {lang key='details'}
                                            {/link}
                                        {/col}
                                    {/if}
                                </div>
                            {/block}
                            {block name='productlist-item-form-expandable-inputs-hidden'}
                                {if $Artikel->kArtikelVariKombi > 0}
                                    {input type="hidden" name="aK" value=$Artikel->kArtikelVariKombi}
                                {/if}
                                {if isset($Artikel->kVariKindArtikel)}
                                    {input type="hidden" name="VariKindArtikel" value=$Artikel->kVariKindArtikel}
                                {/if}
                                {input type="hidden" name="a" value=$Artikel->kArtikel}
                                {input type="hidden" name="wke" value="1"}
                                {input type="hidden" name="overview" value="1"}
                                {input type="hidden" name="Sortierung" value="{if !empty($Suchergebnisse->Sortierung)}{$Suchergebnisse->Sortierung}{/if}"}
                                {if $Suchergebnisse->getPages()->getCurrentPage() > 1}
                                    {input type="hidden" name="seite" value=$Suchergebnisse->getPages()->getCurrentPage()}
                                {/if}
                                {if $NaviFilter->hasCategory()}
                                    {input type="hidden" name="k" value=$NaviFilter->getCategory()->getValue()}
                                {/if}
                                {if $NaviFilter->hasManufacturer()}
                                    {input type="hidden" name="h" value=$NaviFilter->getManufacturer()->getValue()}
                                {/if}
                                {if $NaviFilter->hasSearchQuery()}
                                    {input type="hidden" name="l" value=$NaviFilter->getSearchQuery()->getValue()}
                                {/if}
                                {if $NaviFilter->hasCharacteristicValue()}
                                    {input type="hidden" name="m" value=$NaviFilter->getCharacteristicValue()->getValue()}
                                {/if}
                                {if $NaviFilter->hasCategoryFilter()}
                                    {assign var=cfv value=$NaviFilter->getCategoryFilter()->getValue()}
                                    {if is_array($cfv)}
                                        {foreach $cfv as $val}
                                            {input type="hidden" name="hf" value=$val}
                                        {/foreach}
                                    {else}
                                        {input type="hidden" name="kf" value=$cfv}
                                    {/if}
                                {/if}
                                {if $NaviFilter->hasManufacturerFilter()}
                                    {assign var=mfv value=$NaviFilter->getManufacturerFilter()->getValue()}
                                    {if is_array($mfv)}
                                        {foreach $mfv as $val}
                                            {input type="hidden" name="hf" value=$val}
                                        {/foreach}
                                    {else}
                                        {input type="hidden" name="hf" value=$mfv}
                                    {/if}
                                {/if}
                                {foreach $NaviFilter->getCharacteristicFilter() as $filter}
                                    {input type="hidden" name="mf{$filter@iteration}" value=$filter->getValue()}
                                {/foreach}
                            {/block}
                        {/col}
                    {/row}
                {/form}
            {/col}
        {/row}
        </div>
    </div>
{/block}
