{strip}
{has_boxes position='left' assign='hasLeftBox'}
{if !empty($Brotnavi) && !$bExclusive && !$bAjaxRequest && $nSeitenTyp !== $smarty.const.PAGE_STARTSEITE && $nSeitenTyp !== $smarty.const.PAGE_BESTELLVORGANG && $nSeitenTyp !== $smarty.const.PAGE_BESTELLSTATUS}
    <div class="breadcrumb-wrapper hidden-xs">
        <div class="row">
            <div class="col-xs-12">
                <ul id="breadcrumb" class="breadcrumb" itemprop="breadcrumb" itemscope itemtype="http://schema.org/BreadcrumbList">
                    {foreach $Brotnavi as $oItem}
                        {if $oItem@first}
                            {block name='breadcrumb-first-item'}
                                <li class="breadcrumb-item first" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                    <a itemprop="item" href="{$oItem->getURLFull()}" title="{$oItem->getName()|escape:'html'}">
                                        <span class="fa fa-home"></span>
                                        <span itemprop="name" class="hidden">{$oItem->getName()|escape:'html'}</span>
                                    </a>
                                    <meta itemprop="url" content="{$oItem->getURLFull()}" />
                                    <meta itemprop="position" content="{$oItem@iteration}" />
                                </li>
                            {/block}
                        {elseif $oItem@last}
                            {block name='breadcrumb-last-item'}
                                <li class="breadcrumb-item last" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                    <span itemprop="name">
                                        {if $oItem->getName() !== null}
                                            {if $oItem->getHasChild() === true}
                                                <a href="{$oItem->getURLFull()}" title="{$oItem->getName()|escape:'html'}">{$oItem->getName()}</a>
                                            {else}
                                                {$oItem->getName()}
                                            {/if}
                                        {elseif !empty($Suchergebnisse->getSearchTermWrite())}
                                            {$Suchergebnisse->getSearchTermWrite()}
                                        {/if}
                                    </span>
                                    <meta itemprop="position" content="{$oItem@iteration}" />
                                </li>
                            {/block}
                        {else}
                            {block name='breadcrumb-item'}
                                <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                    <a itemprop="item" href="{$oItem->getURLFull()}" title="{$oItem->getName()|escape:'html'}">
                                        <span itemprop="name">{$oItem->getName()}</span>
                                    </a>
                                    <meta itemprop="url" content="{$oItem->getURLFull()}" />
                                    <meta itemprop="position" content="{$oItem@iteration}" />
                                </li>
                            {/block}
                        {/if}
                    {/foreach}
                </ul>
            </div>
        </div>
    </div>
{/if}
{/strip}