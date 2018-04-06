<ul class="filter_search nav nav-list">
    {foreach name=suchfilter from=$NaviFilter->searchFilterCompat->getOptions() item=searchFilter}
        <li>
            <a rel="nofollow" href="{$searchFilter->getURL()}" class="{if $searchFilter->isActive()}active{/if}">
                <span class="badge pull-right">{$searchFilter->getCount()}</span>
                <span class="value">
                    <i class="fa {if $searchFilter->isActive()}fa-check-square-o{else}fa-square-o{/if} text-muted"></i> {$searchFilter->getName()}
                </span>
            </a>
        </li>
    {/foreach}
</ul>