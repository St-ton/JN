{* template to display products in slider *}

<div class="product-cell text-center{if isset($class)} {$class}{/if} thumbnail">
    <a class="image-wrapper" href="{$Artikel->cURL}">
        {if isset($Artikel->Bilder[0]->cAltAttribut)}
            {assign var="alt" value=$Artikel->Bilder[0]->cAltAttribut|strip_tags|escape:"quotes"|truncate:60}
        {else}
            {assign var="alt" value=$Artikel->cName}
        {/if}

        {*include file="snippets/image.tpl" src=$Artikel->Bilder[0]->cPfadKlein alt=$alt*}
        <img src="{$Artikel->Bilder[0]->cPfadKlein}" alt="{$alt}" />
        {if isset($Artikel->oSuchspecialBild) && !isset($hideOverlays)}
            <img class="overlay-img hidden-xs" src="{$Artikel->oSuchspecialBild->cPfadKlein}" alt="{if isset($Artikel->oSuchspecialBild->cSuchspecial)}{$Artikel->oSuchspecialBild->cSuchspecial}{else}{$Artikel->cName}{/if}">
        {/if}
    </a>
    <div class="caption">
        <h4 class="title">
            {if isset($showPartsList) && $showPartsList === true && isset($Artikel->fAnzahl_stueckliste)}
                <span class="article-bundle-info">
                    <span class="bundle-amount">{$Artikel->fAnzahl_stueckliste}</span> <span class="bundle-times">x</span>
                </span>
            {/if}
            <a href="{$Artikel->cURL}">{$Artikel->cName}</a>
        </h4>
        {if $Artikel->fDurchschnittsBewertung > 0}<small>{include file='productdetails/rating.tpl' stars=$Artikel->fDurchschnittsBewertung}</small>{/if}
        {include file="productdetails/price.tpl" Artikel=$Artikel price_image=$Artikel->Preise->strPreisGrafik_Suche tplscope=$tplscope}
    </div>
</div>{* /product-cell *}