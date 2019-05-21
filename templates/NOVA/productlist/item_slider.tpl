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

                {block name='searchspecial-overlay'}
                    {if isset($Artikel->oSuchspecialBild)}
                        {block name='productlist-item-slider-include-searchspecials'}
                            {include file='snippets/searchspecials.tpl' src=$Artikel->oSuchspecialBild->getURL($smarty.const.IMAGE_SIZE_XS) alt=$alt}
                        {/block}
                    {/if}
                {/block}
                {image data=["lazy" => $Artikel->Bilder[0]->cURLKlein] src="{$imageBaseURL}gfx/trans.png" alt=$alt class="img-fluid"}
                <meta itemprop="image" content="{$Artikel->Bilder[0]->cURLNormal}">
                <meta itemprop="url" content="{$Artikel->cURLFull}">
            {/link}
        {/block}
        {block name='productlist-item-slider-caption'}
            <div class="caption">
                <div class="title word-break">
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
                    {block name='productlist-item-slider-include-price'}
                        <div itemprop="offers" itemscope itemtype="http://schema.org/Offer">
                            {include file='productdetails/price.tpl' Artikel=$Artikel tplscope=$tplscope}
                        </div>
                    {/block}
                {/if}
            </div>
        {/block}
    </div>
{/block}
