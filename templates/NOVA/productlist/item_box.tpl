{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if $Einstellungen.template.productlist.variation_select_productlist === 'N' || $Einstellungen.template.productlist.hover_productlist !== 'Y'}
    {assign var=hasOnlyListableVariations value=0}
{else}
    {hasOnlyListableVariations artikel=$Artikel maxVariationCount=$Einstellungen.template.productlist.variation_select_productlist maxWerteCount=$Einstellungen.template.productlist.variation_max_werte_productlist assign='hasOnlyListableVariations'}
{/if}
<div id="result-wrapper_buy_form_{$Artikel->kArtikel}" class="product-cell text-center{if $Einstellungen.template.productlist.hover_productlist === 'Y'} hover-enabled{/if}{if isset($listStyle) && $listStyle === 'gallery'} active{/if}{if isset($class)} {$class}{/if}">
    {block name='productlist-image'}
        {link class="image-wrapper" href=$Artikel->cURLFull}
            {if isset($Artikel->Bilder[0]->cAltAttribut)}
                {assign var=alt value=$Artikel->Bilder[0]->cAltAttribut|strip_tags|truncate:60|escape:'html'}
            {else}
                {assign var=alt value=$Artikel->cName}
            {/if}

            {block name='searchspecial-overlay'}
                {if isset($Artikel->oSuchspecialBild)}
                    {include file='snippets/searchspecials.tpl' src=$Artikel->oSuchspecialBild->getURL($smarty.const.IMAGE_SIZE_XS) alt=$alt}
                {/if}
            {/block}

            {include file='snippets/image.tpl' src=$Artikel->Bilder[0]->cURLNormal alt=$alt}

            {if $smarty.session.Kundengruppe->mayViewPrices()
                && isset($Artikel->SieSparenX)
                && $Artikel->SieSparenX->anzeigen == 1
                && $Artikel->SieSparenX->nProzent > 0
                && !$NettoPreise
                && $Artikel->taxData['tax'] > 0
            }
                <div class="yousave badge badge-dark">
                    <span class="percent">{$Artikel->SieSparenX->nProzent}%</span>
                </div>
            {/if}
        {/link}
        {include file='productlist/productlist_actions.tpl'}
    {/block}
    {block name='productlist-image-caption'}
    <div class="caption mt-2">
        <div class="h4 title" itemprop="name">{link href=$Artikel->cURLFull}{$Artikel->cKurzbezeichnung}{/link}</div>
        {if $Artikel->cName !== $Artikel->cKurzbezeichnung}<meta itemprop="alternateName" content="{$Artikel->cName}">{/if}
        <meta itemprop="url" content="{$Artikel->cURLFull}">
        {if $Einstellungen.bewertung.bewertung_anzeigen === 'Y' && $Artikel->fDurchschnittsBewertung > 0}
            {include file='productdetails/rating.tpl' stars=$Artikel->fDurchschnittsBewertung}<br>
        {/if}
        <div itemprop="offers" itemscope itemtype="http://schema.org/Offer">
            <link itemprop="businessFunction" href="http://purl.org/goodrelations/v1#Sell" />
            {include file='productdetails/price.tpl' Artikel=$Artikel tplscope=$tplscope}
        </div>
    </div>{* /caption *}
    {/block}
    {form id="buy_form_{$Artikel->kArtikel}" action="{$ShopURL}/" class="form form-basket evo-validate d-none d-md-block" data=["toggle" => "basket-add"]}
        {block name='productlist-delivery-status'}
            <div class="delivery-status my-3">
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
                    && ($Artikel->cLagerKleinerNull === 'N' || $Einstellungen.artikeluebersicht.artikeluebersicht_lagerbestandanzeige_anzeigen === 'U')
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
                    && ($Artikel->cLagerKleinerNull === 'N' || $Einstellungen.artikeluebersicht.artikeluebersicht_lagerbestandanzeige_anzeigen === 'U')}
                    <div class="signal_image status-1"><small>{lang key='supplierStockNotice' printf=$Artikel->fLieferzeit}</small></div>
                {elseif $anzeige === 'verfuegbarkeit' || $anzeige === 'genau'}
                    <div class="signal_image status-{$Artikel->Lageranzeige->nStatus}"><small>{$Artikel->Lageranzeige->cLagerhinweis[$anzeige]}</small></div>
                {elseif $anzeige === 'ampel'}
                    <div class="signal_image status-{$Artikel->Lageranzeige->nStatus}"><small>{$Artikel->Lageranzeige->AmpelText}</small></div>
                {/if}
                {if $Artikel->cEstimatedDelivery}
                    <div class="estimated_delivery d-none d-sm-block">
                        <small>{lang key='shippingTime'}: {$Artikel->cEstimatedDelivery}</small>
                    </div>
                {/if}
            </div>
        {/block}
        <div class="expandable">
            {block name='form-expandable'}
            {if $hasOnlyListableVariations > 0 && !$Artikel->bHasKonfig && $Artikel->kEigenschaftKombi === 0}
                <div class="basket-variations">
                    {assign var=singleVariation value=true}
                    {include file='productdetails/variation.tpl' simple=$Artikel->isSimpleVariation showMatrix=false smallView=true ohneFreifeld=($hasOnlyListableVariations === 2)}
                </div>
            {/if}

            {block name='productlist-add-basket'}
            {if ($Artikel->inWarenkorbLegbar === 1 || ($Artikel->nErscheinendesProdukt === 1 && $Einstellungen.global.global_erscheinende_kaeuflich === 'Y'))
                && (($Artikel->nIstVater === 0 && $Artikel->Variationen|@count === 0) || $hasOnlyListableVariations === 1) && !$Artikel->bHasKonfig}
                {formgroup class="quantity-wrapper"}
                    {if $Artikel->nIstVater && $Artikel->kVaterArtikel == 0}
                        {alert class="choose-variations" variant="info"}
                            {lang key='chooseVariations' section='messages'}
                        {/alert}
                    {else}
                        {inputgroup class="quantity-wrapper mt-1"}
                                {input type="{if $Artikel->cTeilbar === 'Y' && $Artikel->fAbnahmeintervall == 0}text{else}number{/if}" min="0"
                                        step="{if $Artikel->fAbnahmeintervall > 0}{$Artikel->fAbnahmeintervall}{/if}"
                                        id="quantity{$Artikel->kArtikel}"
                                        class="quantity text-right"
                                        name="anzahl"
                                        autocomplete="off"
                                        value="{if $Artikel->fAbnahmeintervall > 0}{if $Artikel->fMindestbestellmenge > $Artikel->fAbnahmeintervall}{$Artikel->fMindestbestellmenge}{else}{$Artikel->fAbnahmeintervall}{/if}{else}1{/if}"}
                                    {inputgroupaddon append=true}
                                        {button type="submit" variant="primary" id="submit{$Artikel->kArtikel}"
                                            title="{lang key='addToCart' section='global'}"
                                            class="ml-3"
                                        }
                                            <i class="fa fa-shopping-cart"></i>
                                        {/button}
                                    {/inputgroupaddon}
                        {/inputgroup}
                    {/if}
                {/formgroup}
            {else}
                {formgroup}
                    {link class="btn btn-secondary btn-md btn-block mt-1" role="button" href=$Artikel->cURLFull}{lang key='details'}{/link}
                {/formgroup}
            {/if}
            {/block}

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
            {if $NaviFilter->hasAttributeValue()}
                {input type="hidden" name="m" value=$NaviFilter->getAttributeValue()->getValue()}
            {/if}
            {if $NaviFilter->hasTag()}
                {input type="hidden" name="t" value=$NaviFilter->getTag()->getValue()}
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
            {if $NaviFilter->hasAttributeFilter()}
                {foreach $NaviFilter->getAttributeFilter() as $attributeFilter}
                    {input type="hidden" name="mf{$attributeFilter@iteration}" value=$attributeFilter->getValue()}
                {/foreach}
            {/if}
            {if $NaviFilter->hasTagFilter()}
                {foreach $NaviFilter->getTagFilter() as $tagFilter}
                    {input type="hidden" name="tf{$tagFilter@iteration}" value=$tagFilter->getValue()}
                {/foreach}
            {/if}
            {/block}
        </div>
    {/form}
</div>
