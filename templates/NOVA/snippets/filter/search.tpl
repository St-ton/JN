{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='snippets-filter-search'}
    {nav vertical=true class="filter_search"}
        {foreach $NaviFilter->searchFilterCompat->getOptions() as $searchFilter}
            {navitem nofollow=true
                href=$searchFilter->getURL()
                active=$searchFilter->isActive()
                router-class="px-0"}
                    <span class="value">
                        <i class="far fa-{if $searchFilter->isActive()}check-{/if}square text-muted"></i> {$searchFilter->getName()} ({$searchFilter->getCount()})
                    </span>
            {/navitem}
        {/foreach}
    {/nav}
{/block}
