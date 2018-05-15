{assign var="title" value=$instance->getProperty('sliderTitle')}
{strip}
    {if $productlist|@count > 0}
        {if !isset($tplscope)}
            {assign var='tplscope' value='slider'}
        {/if}
        <section class="panel{if $title|strlen > 0} panel-default{/if} panel-slider{if $tplscope === 'box'} box box-slider{/if}{if isset($class) && $class|strlen > 0} {$class}{/if}"{if isset($id) && $id|strlen > 0} id="{$id}"{/if}>
            <div class="panel-heading">
                {if $title|strlen > 0}
                    <h5 class="panel-title">
                        {$title}
                        {if !empty($moreLink)}
                            <a class="more pull-right" href="{$moreLink}" title="{$moreTitle}" data-toggle="tooltip" data-placement="auto right" aria-label="{$moreTitle}">
                                <i class="fa fa-arrow-circle-right"></i>
                            </a>
                        {/if}
                    </h5>
                {/if}
            </div>
            <div{if $title|strlen > 0} class="panel-body"{/if}>
                <div class="{if $tplscope == 'box'}{block name="product-box-slider-class"}evo-box-slider{/block}{else}{block name="product-slider-class"}evo-slider{/block}{/if}">
                    {foreach name="sliderproducts" from=$productlist item='product'}
                        <div class="product-wrapper{if isset($style)} {$style}{/if}" {if isset($Link) && $Link->nLinkart == $smarty.const.LINKTYP_STARTSEITE}itemprop="about"{else}itemprop="isRelatedTo"{/if} itemscope itemtype="http://schema.org/Product">
                            {include file='productlist/item_slider.tpl' Artikel=$product tplscope=$tplscope class=''}
                        </div>
                    {/foreach}
                </div>
            </div>
        </section>{* /panel *}
    {/if}
{/strip}

{*
{assign var="productlist" value=$portlet->getFilteredProducts($instance)}

{if $productlist|@count > 0}
    {if !isset($tplscope)}
        {assign var='tplscope' value='slider'}
    {/if}
<section class="panel{if $title|strlen > 0} panel-default{/if}
                    panel-slider{if $tplscope === 'box'} box box-slider{/if}
                    {if !empty($properties.attr.class)} {$properties.attr.class}{/if}"
        {if isset($id) && $id|strlen > 0} id="{$id}"{/if}{$styleString}>
    <div class="panel-heading">
        {if $title|strlen > 0}
            <h5 class="panel-title">
                {$title}
                {if !empty($moreLink)}
                    <a class="more pull-right" href="{$moreLink}" title="{$moreTitle}" data-toggle="tooltip"
                       data-placement="auto right" aria-label="{$moreTitle}">
                        <i class="fa fa-arrow-circle-right"></i>
                    </a>
                {/if}
            </h5>
        {/if}
    </div>
    <div{if $title|strlen > 0} class="panel-body"{/if}>
        <div class="{if $tplscope == 'box'}{block name="product-box-slider-class"}evo-box-slider{/block}
                        {else}{block name="product-slider-class"}evo-slider{/block}{/if}">
            {foreach $productlist as $Artikel}
                <div class="product-wrapper{if isset($style)} {$style}{/if}">
                    {* template to display products in slider * }

                    <div class="product-cell text-center{if isset($class)} {$class}{/if} thumbnail">
                        <a class="image-wrapper" href="{$Artikel->cURL}">
                            {if isset($Artikel->Bilder[0]->cAltAttribut)}
                                {assign var="alt" value=$Artikel->Bilder[0]->cAltAttribut|strip_tags|truncate:60|escape:"html"}
                            {else}
                                {assign var="alt" value=$Artikel->cName}
                            {/if}

                            {*include file="snippets/image.tpl" src=$Artikel->Bilder[0]->cPfadKlein alt=$alt* }
                            <img src="{$Artikel->Bilder[0]->cPfadKlein}" alt="{$alt}" />
                            {if isset($Artikel->oSuchspecialBild) && !isset($hideOverlays)}
                                <img class="overlay-img hidden-xs" src="{$Artikel->oSuchspecialBild->cPfadKlein}"
                                     alt="{if isset($Artikel->oSuchspecialBild->cSuchspecial)}{$Artikel->oSuchspecialBild->cSuchspecial}{else}{$Artikel->cName}{/if}">
                            {/if}
                        </a>
                        <div class="caption">
                            <h4 class="title word-break">
                                {if isset($showPartsList) && $showPartsList === true && isset($Artikel->fAnzahl_stueckliste)}
                                    <span class="article-bundle-info">
                                            <span class="bundle-amount">{$Artikel->fAnzahl_stueckliste}</span>
                                            <span class="bundle-times">x</span>
                                        </span>
                                {/if}
                                <a href="{$Artikel->cURL}">{$Artikel->cKurzbezeichnung}</a>
                            </h4>
                            {if $Einstellungen.bewertung.bewertung_anzeigen === 'Y' && $Artikel->fDurchschnittsBewertung > 0}
                                <small>
                                    {include file='productdetails/rating.tpl' stars=$Artikel->fDurchschnittsBewertung}
                                </small>
                            {/if}
                            {if isset($Artikel->Preise->strPreisGrafik_Suche)}
                                {include file="productdetails/price.tpl" Artikel=$Artikel price_image=$Artikel->Preise->strPreisGrafik_Suche tplscope=$tplscope}
                            {else}
                                {include file="productdetails/price.tpl" Artikel=$Artikel price_image=null tplscope=$tplscope}
                            {/if}
                        </div>
                    </div>{* /product-cell * }

                </div>
            {/foreach}
        </div>
    </div>
    </section>{* /panel * }
{/if}
*}