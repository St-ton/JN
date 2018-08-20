{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
<div class="product-cell text-center{if isset($class)} {$class}{/if} thumbnail">
    <a class="image-wrapper" href="{$Artikel->cURLFull}">
        {if isset($Artikel->Bilder[0]->cAltAttribut)}
            {assign var='alt' value=$Artikel->Bilder[0]->cAltAttribut|strip_tags|truncate:60|escape:'html'}
        {else}
            {assign var='alt' value=$Artikel->cName}
        {/if}

        <img data-lazy="{$Artikel->Bilder[0]->cURLKlein}" src="{$imageBaseURL}gfx/trans.png" alt="{$alt}" />
        {block name='searchspecial-overlay'}
            {if isset($Artikel->oSuchspecialBild)}
                {include file='snippets/searchspecials.tpl' src=$Artikel->oSuchspecialBild->cURLKlein alt=$alt}
            {/if}
        {/block}
    </a>
    <div class="caption">
        <h4 class="title word-break">
            {if isset($showPartsList) && $showPartsList === true && isset($Artikel->fAnzahl_stueckliste)}
                <span class="article-bundle-info">
                    <span class="bundle-amount">{$Artikel->fAnzahl_stueckliste}</span> <span class="bundle-times">x</span>
                </span>
            {/if}
            <a href="{$Artikel->cURLFull}" itemprop="url"><span itemprop="name">{$Artikel->cKurzbezeichnung}</span></a>
        </h4>
        {if $Einstellungen.bewertung.bewertung_anzeigen === 'Y' && $Artikel->fDurchschnittsBewertung > 0}<small>{include file='productdetails/rating.tpl' stars=$Artikel->fDurchschnittsBewertung}</small>{/if}
        <div itemprop="offers" itemscope itemtype="http://schema.org/Offer">
            {include file='productdetails/price.tpl' Artikel=$Artikel tplscope=$tplscope}
        </div>
    </div>
</div>
