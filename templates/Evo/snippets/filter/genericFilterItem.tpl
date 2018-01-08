{if !isset($itemClass)}
    {assign var=itemClass value=''}
{/if}

<ul class="{if isset($class)}{$class}{else}nav nav-list{/if}">
    {foreach $filter->getOptions() as $filterOption}
        <li class="filter-item{if $filterOption->isActive()} active{/if}">
            <a rel="nofollow" href="{$filterOption->getURL()}" class="{$itemClass}{if $filterOption->isActive()} active{/if}">
                <span class="badge pull-right">{$filterOption->getCount()}</span>
                <span class="value">
                    {if $filter->getIcon() !== null}
                        <i class="fa {$filter->getIcon()}"></i>
                    {else}
                        <i class="fa {if $filterOption->isActive() || $NaviFilter->getFilterValue($filter->getClassName()) === $filterOption->getValue()}fa-check-square-o{else}fa-square-o{/if} text-muted"></i>
                    {/if}
                    {if $filter->getClassName() === 'FilterItemRating'}
                        {include file='productdetails/rating.tpl' stars=$filterOption->getValue()}
                    {/if}
                    <span class="word-break">{$filterOption->getName()}</span>
                </span>
            </a>
        </li>
    {/foreach}
</ul>