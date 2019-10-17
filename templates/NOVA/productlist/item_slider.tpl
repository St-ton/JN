{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='productlist-item-slider'}
    <div class="product-cell text-center{if isset($class)} {$class}{/if} thumbnail mx-3">
        {block name='productlist-item-slider-link'}
            {link class="image-wrapper" href=$Artikel->cURLFull}
                {if isset($Artikel->Bilder[0]->cAltAttribut)}
                    {assign var=alt value=$Artikel->Bilder[0]->cAltAttribut|strip_tags|truncate:60|escape:'html'}
                {else}
                    {assign var=alt value=$Artikel->cName}
                {/if}
                <div class="image-box">
                    {image fluid=true webp=true lazy=true
                        alt=$Artikel->cName
                        src=$Artikel->Bilder[0]->cURLKlein
                        srcset="{$Artikel->Bilder[0]->cURLMini} {$Einstellungen.bilder.bilder_artikel_mini_breite}w,
                            {$Artikel->Bilder[0]->cURLKlein} {$Einstellungen.bilder.bilder_artikel_klein_breite}w,
                            {$Artikel->Bilder[0]->cURLNormal} {$Einstellungen.bilder.bilder_artikel_normal_breite}w"
                        sizes="200px"
                    }
                </div>
                <meta itemprop="image" content="{$Artikel->Bilder[0]->cURLNormal}">
                <meta itemprop="url" content="{$Artikel->cURLFull}">
            {/link}
        {/block}
        {block name='productlist-item-slider-caption'}
            <div class="caption">
                <div class="title mt-2">
                    {if isset($showPartsList) && $showPartsList === true && isset($Artikel->fAnzahl_stueckliste)}
                        <span class="article-bundle-info">
                            <span class="bundle-amount">{$Artikel->fAnzahl_stueckliste}</span> <span class="bundle-times">x</span>
                        </span>
                    {/if}
                    {link href=$Artikel->cURLFull}
                        <span itemprop="name">{$Artikel->cKurzbezeichnung}</span>
                    {/link}
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
{/block}
