{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{strip}
{has_boxes position='left' assign='hasLeftBox'}
{if !empty($Brotnavi) && !$bExclusive && !$bAjaxRequest && $nSeitenTyp !== $smarty.const.PAGE_STARTSEITE && $nSeitenTyp !== $smarty.const.PAGE_BESTELLVORGANG && $nSeitenTyp !== $smarty.const.PAGE_BESTELLSTATUS}
    {row class="breadcrumb-wrapper"}
        {col}
            {breadcrumb id="breadcrumb" itemprop="breadcrumb" itemscope=true itemtype="http://schema.org/BreadcrumbList" class="pl-0 pt-0"}
                {foreach $Brotnavi as $oItem}
                    {if $oItem@first}
                        {block name='breadcrumb-first-item'}
                            {breadcrumbitem class="first"
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
                        {block name='breadcrumb-last-item'}
                            {breadcrumbitem class="last"
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
                        {block name='breadcrumb-item'}
                            {breadcrumbitem router-tag-itemprop="url"
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
            {/breadcrumb}
        {/col}
    {/row}
{/if}
{/strip}
