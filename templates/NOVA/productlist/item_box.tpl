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
    <div id="result-wrapper_buy_form_{$Artikel->kArtikel}" data-wrapper="true" class="productbox productbox-column {if $Einstellungen.template.productlist.hover_productlist === 'Y'} productbox-hover{/if}{if isset($class)} {$class}{/if}">
        <div class="productbox-inner">
            {row}
                {col cols=12}
                    <div class="productbox-image">
                        {if isset($Artikel->Bilder[0]->cAltAttribut)}
                            {assign var=alt value=$Artikel->Bilder[0]->cAltAttribut|strip_tags|truncate:60|escape:'html'}
                        {else}
                            {assign var=alt value=$Artikel->cName}
                        {/if}
                        <div class="image-content">
                            {block name='productlist-item-box-image'}
                                {counter assign=imgcounter print=0}
                                {if isset($Artikel->oSuchspecialBild)}
                                    {block name='productlist-item-box-include-ribbon'}
                                        {include file='snippets/ribbon.tpl'}
                                    {/block}
                                {/if}
                                <div class="productbox-images">
                                    <div class="clearfix list-gallery carousel carousel-btn-arrows">
                                        {block name="productlist-item-list-image"}
                                            {foreach $Artikel->Bilder as $image}
                                                {strip}
                                                    <div>
                                                        {link href=$Artikel->cURLFull}
                                                            {image alt=$alt fluid=true webp=true lazy=true
                                                                src="{$image->cURLKlein}"
                                                                srcset="{$image->cURLMini} {$Einstellungen.bilder.bilder_artikel_mini_breite}w,
                                                                         {$image->cURLKlein} {$Einstellungen.bilder.bilder_artikel_klein_breite}w,
                                                                         {$image->cURLNormal} {$Einstellungen.bilder.bilder_artikel_normal_breite}w"
                                                                sizes="auto"
                                                                data=["id"  => $imgcounter]
                                                                class='w-100'
                                                            }
                                                        {/link}
                                                    </div>
                                                {/strip}
                                            {/foreach}
                                        {/block}
                                    </div>
                                    {if !empty($Artikel->Bilder[0]->cURLNormal)}
                                        <meta itemprop="image" content="{$Artikel->Bilder[0]->cURLNormal}">
                                    {/if}
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
                                    <div class="productbox-sale-percentage">
                                        <div class="ribbon ribbon-7 productbox-ribbon">{$Artikel->SieSparenX->nProzent}%</div>
                                    </div>
                                {/block}
                            {/if}

                            {block name='productlist-item-box-include-productlist-actions'}
                                <div class="productbox-quick-actions productbox-onhover">
                                    {include file='productlist/productlist_actions.tpl'}
                                </div>
                            {/block}
                        </div>
                    </div>
                {/col}
                {col cols=12}
                    {block name='productlist-item-box-caption'}
                        <div class="caption mt-2 text-left">
                            <div class="productbox-title" itemprop="name">{link href=$Artikel->cURLFull class="text-truncate-fade"}{$Artikel->cKurzbezeichnung}{/link}</div>
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
                        </div>
                    {/block}
                {/col}
            {/row}
        </div>
    </div>
{/block}
