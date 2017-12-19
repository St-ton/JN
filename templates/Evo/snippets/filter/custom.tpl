<ul class="{if isset($class)}{$class}{else}nav nav-list{/if}">
    {foreach $filter->getOptions() as $filterOption}
        <li>
            <a rel="nofollow" href="{$filterOption->getURL()}">
                <span class="value">
                    <i class="fa {if $NaviFilter->getFilterValue($filter->getClassName()) == $filterOption->getValue()}fa-check-square-o{else}fa-square-o{/if} text-muted"></i>
                    {$filterOption->getName()|escape:'html'}<span class="badge pull-right">{$filterOption->getCount()}</span>
                </span>
            </a>
        </li>
    {/foreach}
</ul>

{*FilterExtra variant:*}


{*<ul class="{if isset($class)}{$class}{else}nav nav-list{/if}">*}
    {*{foreach $filter->filterOptions as $filterOption}*}
        {*<li>*}
            {*<a rel="nofollow" href="{$filterOption->getURL()}">*}
                {*<span class="value">*}
                    {*<i class="fa {if $NaviFilter->getFilterValue($filter->cClassname) == $filterOption->getValue()}fa-check-square-o{else}fa-square-o{/if} text-muted"></i>*}
                    {*{$filterOption->getName()|escape:'html'}*}
                    {*<span class="badge pull-right">{$filterOption->getCount()}</span>*}
                {*</span>*}
            {*</a>*}
        {*</li>*}
    {*{/foreach}*}
{*</ul>*}