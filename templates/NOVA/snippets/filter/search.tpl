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
                    <div class="align-items-center d-flex">
                        <i class="far fa-{if $searchFilter->isActive()}check-{/if}square text-muted mr-2"></i>
                        <span class="word-break">{$searchFilter->getName()}</span>
                        <span class="badge badge-outline-secondary ml-auto">{$searchFilter->getCount()}</span>
                    </div>
            {/navitem}
        {/foreach}
    {/nav}
{/block}
