{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='productlist-item-list'}
    {if $Einstellungen.template.productlist.variation_select_productlist === 'N' || $Einstellungen.template.productlist.hover_productlist !== 'Y'}
        {assign var=hasOnlyListableVariations value=0}
    {else}
        {hasOnlyListableVariations artikel=$Artikel maxVariationCount=$Einstellungen.template.productlist.variation_select_productlist maxWerteCount=$Einstellungen.template.productlist.variation_max_werte_productlist assign='hasOnlyListableVariations'}
    {/if}
    <div id="result-wrapper_buy_form_{$Artikel->kArtikel}" data-wrapper="true" class="product-cell{if $Einstellungen.template.productlist.hover_productlist === 'Y'} hover-enabled{/if}{if isset($listStyle) && $listStyle === 'list'} active{/if}">
        {row class="product-body {if $tplscope !== 'list'} text-center{/if}"}
            {col cols=12 md=3 class="text-center"}
                {block name='productlist-item-list-image-wrapper'}
                    <div class="image-wrapper">
                        {if isset($Artikel->Bilder[0]->cAltAttribut)}
                            {assign var=alt value=$Artikel->Bilder[0]->cAltAttribut|strip_tags|truncate:60|escape:'html'}
                        {else}
                            {assign var=alt value=$Artikel->cName}
                        {/if}
                        {block name='productlist-item-list-include-searchspecials'}
                            {if isset($Artikel->oSuchspecialBild)}
                                {include file='snippets/ribbon.tpl'}
                                {*{include file='snippets/searchspecials.tpl' src=$Artikel->oSuchspecialBild->getURL($smarty.const.IMAGE_SIZE_SM) alt=$alt}*}
                            {/if}
                        {/block}

                        <div class="clearfix list-gallery">
                            {block name="productlist-item-list-image"}
                                {foreach $Artikel->Bilder as $image}
                                    {strip}
                                        {image alt=$image->cAltAttribut|escape:'html'
                                            fluid=true
                                            lazy=true
                                            webp=true
                                            src="{$Artikel->Bilder[0]->cURLNormal}"
                                            srcset="{$image->cURLMini} {$Einstellungen.bilder.bilder_artikel_mini_breite}w,
                                                {$image->cURLKlein} {$Einstellungen.bilder.bilder_artikel_klein_breite}w,
                                                {$image->cURLNormal} {$Einstellungen.bilder.bilder_artikel_normal_breite}w"
                                            sizes = "auto"
                                        }
                                    {/strip}
                                {/foreach}
                            {/block}
                        </div>

                        {if $smarty.session.Kundengruppe->mayViewPrices()
                            && isset($Artikel->SieSparenX)
                            && $Artikel->SieSparenX->anzeigen == 1
                            && $Artikel->SieSparenX->nProzent > 0
                            && !$NettoPreise
                            && $Artikel->taxData['tax'] > 0
                        }
                            {block name='productlist-item-list-badge-yousave'}
                                <div class="yousave badge badge-dark">
                                    <span class="percent">{$Artikel->SieSparenX->nProzent}%</span>
                                </div>
                            {/block}
                        {/if}
                    </div>
                    {block name='productlist-item-list-include-productlist-actions'}
                        {include file='productlist/productlist_actions.tpl'}
                    {/block}
                {/block}
                {if $Einstellungen.bewertung.bewertung_anzeigen === 'Y'}
                    {block name='productlist-item-list-include-rating'}
                        {include file='productdetails/rating.tpl' stars=$Artikel->fDurchschnittsBewertung}
                    {/block}
                {/if}
            {/col}
            {col cols=12 md=5 class="product-detail text-center text-md-left"}
                {block name='productlist-item-list-title'}
                    <div class="h4 title mb-3" itemprop="name">
                        {link href=$Artikel->cURLFull}{$Artikel->cName}{/link}
                    </div>
                    <meta itemprop="url" content="{$Artikel->cURLFull}">
                {/block}
                {block name='productlist-item-list-manufacturer'}
                    {if $Einstellungen.artikeluebersicht.artikeluebersicht_hersteller_anzeigen !== 'N'}
                        <div class="media d-none d-md-block top0 bottom5" itemprop="manufacturer" itemscope itemtype="http://schema.org/Organization">
                            {if ($Einstellungen.artikeluebersicht.artikeluebersicht_hersteller_anzeigen === 'BT'
                                || $Einstellungen.artikeluebersicht.artikeluebersicht_hersteller_anzeigen === 'B')
                                && !empty($Artikel->cHerstellerBildKlein)}
                                <div class="media-left">
                                    {if !empty($Artikel->cHerstellerHomepage)}<a href="{$Artikel->cHerstellerHomepage}">{/if}
                                        {image src=$Artikel->cHerstellerBildKlein alt=$Artikel->cHersteller class="img-xs"}
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

                {block name='productlist-item-list-description'}
                    <div class="product-info d-none d-md-block">
                        {if $Einstellungen.artikeluebersicht.artikeluebersicht_kurzbeschreibung_anzeigen === 'Y' && $Artikel->cKurzBeschreibung}
                            <div class="shortdescription" itemprop="description">
                                {$Artikel->cKurzBeschreibung}
                            </div>
                        {/if}
                        <div class="attr-group list-unstyled small mt-2 d-none d-sm-block">
                            {row class="item attr-sku"}
                                {col sm=5 class="attr-label"}{lang key='productNo'}: {/col}
                                {col sm=7 class="value" itemprop="sku"}{$Artikel->cArtNr}{/col}
                            {/row}
                            {if !empty($Artikel->cISBN)
                                && ($Einstellungen.artikeldetails.isbn_display === 'L'
                                    || $Einstellungen.artikeldetails.isbn_display === 'DL')}
                                {row class="item"}
                                    {col class="attr-label" sm=5}{lang key='isbn'}: {/col}
                                    {col class="value" sm=7}{$Artikel->cISBN}{/col}
                                {/row}
                            {/if}
                            {if !empty($Artikel->cUNNummer) && !empty($Artikel->cGefahrnr)
                                && ($Einstellungen.artikeldetails.adr_hazard_display === 'L'
                                    || $Einstellungen.artikeldetails.adr_hazard_display === 'DL')}
                                {row class="item"}
                                    {col class="attr-label" sm=5}
                                        {lang key='adrHazardSign'}:
                                    {/col}
                                    {col sm=7 class="value"}
                                        <table class="adr-table value">
                                            <tr>
                                                <td>{$Artikel->cGefahrnr}</td>
                                            </tr>
                                            <tr>
                                                <td>{$Artikel->cUNNummer}</td>
                                            </tr>
                                        </table>
                                    {/col}
                                {/row}
                            {/if}
                            {if isset($Artikel->dMHD) && isset($Artikel->dMHD_de)}
                                {row class="item attr-best-before" title="{lang key='productMHDTool'}"}
                                    {col sm=5 class="attr-label"}{lang key='productMHD'}: {/col}
                                    {col sm=7 class="value"}{$Artikel->dMHD_de}{/col}
                                {/row}
                            {/if}
                            {if $Einstellungen.artikeluebersicht.artikeluebersicht_gewicht_anzeigen === 'Y' && isset($Artikel->cGewicht) && $Artikel->fGewicht > 0}
                                {row class="item attr-weight"}
                                    {col class="attr-label" sm=5}{lang key='shippingWeight'}: {/col}
                                    {col class="value" sm=7}{$Artikel->cGewicht} {lang key='weightUnit'}{/col}
                                {/row}
                            {/if}
                            {if $Einstellungen.artikeluebersicht.artikeluebersicht_artikelgewicht_anzeigen === 'Y' && isset($Artikel->cArtikelgewicht) && $Artikel->fArtikelgewicht > 0}
                                {row class="item row attr-weight weight-unit-article d-sm-none d-md-flex"}
                                    {col class="attr-label" sm=5}{lang key='productWeight'}: {/col}
                                    {col class="value" sm=7}{$Artikel->cArtikelgewicht} {lang key='weightUnit'}{/col}
                                {/row}
                            {/if}
                            {if $Einstellungen.artikeluebersicht.artikeluebersicht_artikelintervall_anzeigen === 'Y' && $Artikel->fAbnahmeintervall > 0}
                                {row class="item row attr-quantity-scale"}
                                    {col class="attr-label" sm=5}{lang key='purchaseIntervall' section='productOverview'}: {/col}
                                    {col class="value" sm=7}{$Artikel->fAbnahmeintervall} {$Artikel->cEinheit}{/col}
                                {/row}
                            {/if}
                            {if count($Artikel->Variationen) > 0}
                                {row class="item row attr-variations"}
                                    {col class="attr-label" sm=5}{lang key='variationsIn' section='productOverview'}: {/col}
                                    {col class="value-group" sm=7}
                                        {foreach $Artikel->Variationen as $variation}
                                            {if !$variation@first}, {/if}<span class="value">{$variation->cName}</span>
                                        {/foreach}
                                    {/col}
                                {/row}
                            {/if}
                        </div>
                    </div>
                {/block}
            {/col}
            {col cols=12 md=4 class="product-detail text-center text-md-left"}
                <div class="product-detail-cell" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
                    <link itemprop="businessFunction" href="http://purl.org/goodrelations/v1#Sell" />
                    {block name='productlist-item-list-form'}
                        {block name='productlist-item-list-include-price'}
                            <div class="mb-3">
                                {include file='productdetails/price.tpl' Artikel=$Artikel tplscope=$tplscope}
                            </div>
                        {/block}
                        <div class="delivery-status mb-3">
                            {block name='productlist-item-list-delivery-status'}
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
                                    <div class="estimated_delivery d-none d-sm-block">
                                        {lang key='shippingTime'}: {$Artikel->cEstimatedDelivery}
                                    </div>
                                {/if}
                            {/block}
                        </div>
                        <div class="expandable">
                            {block name='productlist-item-list-form-expandable'}
                                {form id="buy_form_{$Artikel->kArtikel}" action=$ShopURL class="form form-basket evo-validate" data=["toggle" => "basket-add"]}
                                    {if $hasOnlyListableVariations > 0 && !$Artikel->bHasKonfig && $Artikel->kEigenschaftKombi === 0}
                                        <div class="d-none d-sm-block basket-variations">
                                            {assign var=singleVariation value=true}
                                            {block name='productlist-item-list-form-expandable-include-variation'}
                                                {include file='productdetails/variation.tpl' simple=$Artikel->isSimpleVariation showMatrix=false smallView=true ohneFreifeld=($hasOnlyListableVariations == 2)}
                                            {/block}
                                        </div>
                                    {/if}
                                    <div class="d-none d-sm-block basket-details">
                                        {block name='productlist-item-list-basket-details'}
                                            {if ($Artikel->inWarenkorbLegbar === 1 || ($Artikel->nErscheinendesProdukt === 1 && $Einstellungen.global.global_erscheinende_kaeuflich === 'Y'))
                                                && (($Artikel->nIstVater === 0 && $Artikel->Variationen|@count === 0) || $hasOnlyListableVariations === 1) && !$Artikel->bHasKonfig}
                                                {formgroup class="quantity-wrapper top7"}
                                                    {if $Artikel->nIstVater && $Artikel->kVaterArtikel == 0}
                                                        {alert variation="info" class="choose-variations"}{lang key='chooseVariations' section='messages'}{/alert}
                                                    {else}
                                                        {inputgroup class="quantity-wrapper mt-2"}
                                                            {input type="{if $Artikel->cTeilbar === 'Y' && $Artikel->fAbnahmeintervall == 0}text{else}number{/if}" min="0"
                                                                step="{if $Artikel->fAbnahmeintervall > 0}{$Artikel->fAbnahmeintervall}{/if}"
                                                                size="2"
                                                                id="quantity{$Artikel->kArtikel}"
                                                                class="quantity"
                                                                name="anzahl"
                                                                autocomplete="off"
                                                                aria=["label"=>{lang key='quantity'}]
                                                                data=["decimals"=>{getDecimalLength quantity=$Artikel->fAbnahmeintervall}]
                                                                value="{if $Artikel->fAbnahmeintervall > 0}{if $Artikel->fMindestbestellmenge > $Artikel->fAbnahmeintervall}{$Artikel->fMindestbestellmenge}{else}{$Artikel->fAbnahmeintervall}{/if}{else}1{/if}"
                                                            }
                                                            {inputgroupaddon append=true}
                                                                {button type="submit" variant="primary"
                                                                    block=true id="submit{$Artikel->kArtikel}"
                                                                    title="{lang key='addToCart'}"
                                                                    class="ml-3"
                                                                    aria=["label"=>{lang key='addToCart'}]
                                                                }
                                                                    <i class="fa fa-shopping-cart"></i>
                                                                {/button}
                                                            {/inputgroupaddon}
                                                        {/inputgroup}
                                                    {/if}
                                                {/formgroup}
                                            {else}
                                                {formgroup class="mt-1"}
                                                    {link class="btn btn-secondary btn-block" role="button" href=$Artikel->cURLFull}{lang key='details'}{/link}
                                                {/formgroup}
                                            {/if}
                                        {/block}
                                    </div>
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
                                {/form}
                            {/block}
                        </div>
                    {/block}
                </div>
            {/col}
        {/row}
    </div>
{/block}
