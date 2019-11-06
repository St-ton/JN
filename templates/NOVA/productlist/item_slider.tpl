{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='productlist-item-slider'}
    <div class="productbox productbox-column productbox-mini {if isset($class)} {$class}{/if}">
        <div class="productbox-inner">
            {block name='productlist-item-slider-link'}
                <div class="productbox-image">
                    {link class="image-wrapper" href=$Artikel->cURLFull}
                        {if isset($Artikel->Bilder[0]->cAltAttribut)}
                            {assign var=alt value=$Artikel->Bilder[0]->cAltAttribut|strip_tags|truncate:60|escape:'html'}
                        {else}
                            {assign var=alt value=$Artikel->cName}
                        {/if}
                        <div class="productbox-image-wrapper">
                            <div class="productbox-image-wrapper-inner">
                            {block name='productlist-item-slider-image'}
                                {image fluid-grow=true webp=true lazy=true
                                    alt=$Artikel->cName
                                    src=$Artikel->Bilder[0]->cURLKlein
                                    srcset="{$Artikel->Bilder[0]->cURLMini} {$Einstellungen.bilder.bilder_artikel_mini_breite}w,
                                        {$Artikel->Bilder[0]->cURLKlein} {$Einstellungen.bilder.bilder_artikel_klein_breite}w,
                                        {$Artikel->Bilder[0]->cURLNormal} {$Einstellungen.bilder.bilder_artikel_normal_breite}w"
                                    sizes="auto"
                                    class="product-image"}
                            {/block}
                            </div>
                        </div>
                        <meta itemprop="image" content="{$Artikel->Bilder[0]->cURLNormal}">
                        <meta itemprop="url" content="{$Artikel->cURLFull}">
                    {/link}
                </div>
            {/block}
            {block name='productlist-item-slider-caption'}
                <div class="text-center">
                    <div class="productbox-title">
                        {block name='productlist-item-slider-caption-short-desc'}
                            {link href=$Artikel->cURLFull}
                                <span class="text-clamp-2">
                                    {if isset($showPartsList) && $showPartsList === true && isset($Artikel->fAnzahl_stueckliste)}
                                        {block name='productlist-item-slider-caption-bundle'}
                                            {$Artikel->fAnzahl_stueckliste}x
                                        {/block}
                                    {/if}
                                    <span itemprop="name">{$Artikel->cKurzbezeichnung}</span>
                                </span>
                            {/link}
                        {/block}
                    </div>
                    {if $tplscope === 'box'}
                        {if $Einstellungen.bewertung.bewertung_anzeigen === 'Y' && $Artikel->fDurchschnittsBewertung > 0}
                            {block name='productlist-item-slider-include-rating'}
                                <small>{include file='productdetails/rating.tpl' stars=$Artikel->fDurchschnittsBewertung}</small>
                            {/block}
                        {/if}
                    {/if}
                    {block name='productlist-item-slider-include-price'}
                        <div itemprop="offers" itemscope itemtype="http://schema.org/Offer">
                            {include file='productdetails/price.tpl' Artikel=$Artikel tplscope=$tplscope}
                        </div>
                    {/block}
                </div>
            {/block}
        </div>
    </div>
{/block}
