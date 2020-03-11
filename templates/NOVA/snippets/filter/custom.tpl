{block name='snippets-filter-custom'}
    <ul class="{if isset($class)}{$class}{else}nav nav-list{/if}">
        {foreach $filter->getOptions() as $filterOption}
            <li>
                {link rel="nofollow" href=$filterOption->getURL()}
                    <i class="fa {if $NaviFilter->getFilterValue($filter->getClassName()) == $filterOption->getValue()}fa-check-square-o{else}fa-square-o{/if} text-muted"></i>
                    {$filterOption->getName()|escape:'html'}{badge class="float-right"}{$filterOption->getCount()}{/badge}
                {/link}
            </li>
        {/foreach}
    </ul>
{/block}
