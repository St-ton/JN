{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='productlist-item-box'}
    {if $Einstellungen.template.productlist.variation_select_productlist === 'N' || $Einstellungen.template.productlist.hover_productlist !== 'Y'}
        {assign var=hasOnlyListableVariations value=0}
    {else}
        {hasOnlyListableVariations artikel=$Artikel maxVariationCount=$Einstellungen.template.productlist.variation_select_productlist maxWerteCount=$Einstellungen.template.productlist.variation_max_werte_productlist assign='hasOnlyListableVariations'}
    {/if}
    <div id="result-wrapper_buy_form_{$Artikel->kArtikel}" data-wrapper="true" class="product-cell text-center{if $Einstellungen.template.productlist.hover_productlist === 'Y'} hover-enabled{/if}{if isset($listStyle) && $listStyle === 'gallery'} active{/if}{if isset($class)} {$class}{/if}">
        {block name='productlist-item-box-image'}
            {link class="image-wrapper" href=$Artikel->cURLFull}
                {if isset($Artikel->Bilder[0]->cAltAttribut)}
                    {assign var=alt value=$Artikel->Bilder[0]->cAltAttribut|strip_tags|truncate:60|escape:'html'}
                {else}
                    {assign var=alt value=$Artikel->cName}
                {/if}

                {*{block name='productlist-item-box-include-image'}
                    {include file='snippets/image.tpl' src=$Artikel->Bilder[0]->cURLNormal alt=$alt}
                {/block}*}
                {block name='productlist-item-box-image'}
                    {counter assign=imgcounter print=0}
                    <div class="image-box">
                        {block name='productlist-item-box-include-searchspecials'}
                            {if isset($Artikel->oSuchspecialBild)}
                                {include file='snippets/ribbon.tpl'}
                                {*{include file='snippets/searchspecials.tpl' src=$Artikel->oSuchspecialBild->getURL($smarty.const.IMAGE_SIZE_SM) alt=$alt}*}
                            {/if}
                        {/block}
                        <div class="image-content">
                            {image alt=$alt fluid=true lazy=true
                                src="{$Artikel->Bilder[0]->cURLNormal}"
                                srcset="{$Artikel->Bilder[0]->cURLNormal}"
                                sizes = "auto"
                                data=["id"  => $imgcounter]
                            }
                            {if !empty($Artikel->Bilder[0]->cURLNormal)}
                                <meta itemprop="image" content="{$Artikel->Bilder[0]->cURLNormal}">
                            {/if}
                        </div>
                    </div>
                {/block}

                {if $smarty.session.Kundengruppe->mayViewPrices()
                    && isset($Artikel->SieSparenX)
                    && $Artikel->SieSparenX->anzeigen == 1
                    && $Artikel->SieSparenX->nProzent > 0
                    && !$NettoPreise
                    && $Artikel->taxData['tax'] > 0
                }
                    {block name='productlist-item-badge-yousave'}
                        <div class="yousave badge badge-dark">
                            <span class="percent">{$Artikel->SieSparenX->nProzent}%</span>
                        </div>
                    {/block}
                {/if}
            {/link}

            {block name='productlist-item-box-include-productlist-actions'}
                {include file='productlist/productlist_actions.tpl'}
            {/block}
        {/block}
        {block name='productlist-item-box-caption'}
            <div class="caption mt-2 text-left">
                <div class="h4 title" itemprop="name">{link href=$Artikel->cURLFull class="text-truncate-fade"}{$Artikel->cKurzbezeichnung}{/link}</div>
                {if $Artikel->cName !== $Artikel->cKurzbezeichnung}<meta itemprop="alternateName" content="{$Artikel->cName}">{/if}
                <meta itemprop="url" content="{$Artikel->cURLFull}">
                {if $Einstellungen.bewertung.bewertung_anzeigen === 'Y' && $Artikel->fDurchschnittsBewertung > 0}
                    {block name='productlist-index-include-rating'}
                        {include file='productdetails/rating.tpl' stars=$Artikel->fDurchschnittsBewertung}<br>
                    {/block}
                {/if}
                {block name='productlist-index-include-price'}
                    <div itemprop="offers" itemscope itemtype="http://schema.org/Offer">
                        <link itemprop="businessFunction" href="http://purl.org/goodrelations/v1#Sell" />
                        {include file='productdetails/price.tpl' Artikel=$Artikel tplscope=$tplscope}
                    </div>
                {/block}
            </div>{* /caption *}
        {/block}
    </div>
{/block}
