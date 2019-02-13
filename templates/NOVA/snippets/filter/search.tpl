{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{nav vertical=true class="filter_search"}
    {foreach $NaviFilter->searchFilterCompat->getOptions() as $searchFilter}
        {navitem nofollow=true
            href="{$searchFilter->getURL()}"
            active=$searchFilter->isActive()
            router-class="px-0"}
                <span class="badge badge-light float-right">{$searchFilter->getCount()}</span>
                <span class="value">
                    <i class="far fa-{if $searchFilter->isActive()}check-{/if}square text-muted"></i> {$searchFilter->getName()}
                </span>
        {/navitem}
    {/foreach}
{/nav}
