{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='layout-breadcrumb'}
    {strip}
    {has_boxes position='left' assign='hasLeftBox'}
    {if !empty($Brotnavi) && !$bExclusive && !$bAjaxRequest && $nSeitenTyp !== $smarty.const.PAGE_STARTSEITE && $nSeitenTyp !== $smarty.const.PAGE_BESTELLVORGANG && $nSeitenTyp !== $smarty.const.PAGE_BESTELLSTATUS}
        {row class="breadcrumb-wrapper no-gutters align-items-center"}
            {col cols="auto"}
                {breadcrumb id="breadcrumb" itemprop="breadcrumb" itemscope=true itemtype="http://schema.org/BreadcrumbList" class="p-0 py-2 mb-0"}
                    {block name='layout-breadcrumb-xs-back'}
                        {$parent = $Brotnavi[$Brotnavi|count -2|max:0]}
                        {breadcrumbitem class="breadcrumb-back"
                            href=$parent->getURLFull()
                            title=$parent->getName()|escape:'html'
                        }
                            <span class="fas fa-long-arrow-alt-left mr-2"></span><span itemprop="name">{$parent->getName()}</span>
                        {/breadcrumbitem}
                    {/block}
                    {block name='layout-breadcrumb-items'}
                        {foreach $Brotnavi as $oItem}
                            {if $oItem@first}
                                {block name='layout-breadcrumb-first-item'}
                                    {breadcrumbitem class="first d-none d-sm-flex"
                                        router-tag-itemprop="url"
                                        href=$oItem->getURLFull()
                                        title=$oItem->getName()|escape:'html'
                                        itemprop="itemListElement"
                                        itemscope=true
                                        itemtype="http://schema.org/ListItem"
                                    }
                                        <span itemprop="name">{$oItem->getName()|escape:'html'}</span>
                                        <meta itemprop="item" content="{$oItem->getURLFull()}" />
                                        <meta itemprop="position" content="{$oItem@iteration}" />
                                    {/breadcrumbitem}
                                {/block}
                            {elseif $oItem@last}
                                {block name='layout-breadcrumb-last-item'}
                                    {breadcrumbitem class="last d-none d-sm-flex"
                                        router-tag-itemprop="url"
                                        href="{if $oItem->getHasChild() === true}{$oItem->getURLFull()}{/if}"
                                        title=$oItem->getName()|escape:'html'
                                        itemprop="itemListElement"
                                        itemscope=true
                                        itemtype="http://schema.org/ListItem"
                                    }
                                        <span itemprop="name">
                                            {if $oItem->getName() !== null}
                                                {$oItem->getName()}
                                            {elseif !empty($Suchergebnisse->getSearchTermWrite())}
                                                {$Suchergebnisse->getSearchTermWrite()}
                                            {/if}
                                        </span>
                                        <meta itemprop="item" content="{$oItem->getURLFull()}" />
                                        <meta itemprop="position" content="{$oItem@iteration}" />
                                    {/breadcrumbitem}
                                {/block}
                            {else}
                                {block name='layout-breadcrumb-item'}
                                    {breadcrumbitem router-tag-itemprop="url"
                                        class="d-none d-sm-flex"
                                        href=$oItem->getURLFull()
                                        title=$oItem->getName()|escape:'html'
                                        itemprop="itemListElement"
                                        itemscope=true
                                        itemtype="http://schema.org/ListItem"
                                    }
                                        <span itemprop="name">{$oItem->getName()}</span>
                                        <meta itemprop="item" content="{$oItem->getURLFull()}" />
                                        <meta itemprop="position" content="{$oItem@iteration}" />
                                    {/breadcrumbitem}
                                {/block}
                            {/if}
                        {/foreach}
                    {/block}
                {/breadcrumb}
            {/col}
            {col}
            {if !empty($NavigationBlaettern)}
                {block name='layout-header-product-pagination'}
                    {if isset($NavigationBlaettern->naechsterArtikel) && $NavigationBlaettern->naechsterArtikel->kArtikel}
                        {link class="btn btn-default float-right"
                            href=$NavigationBlaettern->naechsterArtikel->cURLFull
                            title=$NavigationBlaettern->naechsterArtikel->cName
                            aria=["label"=>"{lang section='productDetails' key='nextProduct'}: {$NavigationBlaettern->naechsterArtikel->cName}"]
                        }
                            <span class="fa fa-chevron-right"></span>
                        {/link}
                    {/if}
                    {if isset($NavigationBlaettern->vorherigerArtikel) && $NavigationBlaettern->vorherigerArtikel->kArtikel}
                        {link class="btn btn-default float-right"
                            href=$NavigationBlaettern->vorherigerArtikel->cURLFull
                            title=$NavigationBlaettern->vorherigerArtikel->cName
                            aria=["label"=>"{lang section='productDetails' key='previousProduct'}: {$NavigationBlaettern->vorherigerArtikel->cName}"]
                        }
                            <span class="fa fa-chevron-left"></span>
                        {/link}
                    {/if}
                {/block}
            {/if}
            {/col}
        {/row}
    {/if}
    {/strip}
{/block}
