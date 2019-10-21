{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='snippets-filter-search'}
    {$limit = $Einstellungen.template.productlist.filter_max_options}
    {$collapseInit = false}
    {nav vertical=true class="filter_search"}
        {foreach $NaviFilter->searchFilterCompat->getOptions() as $searchFilter}
            {if $limit != -1 && $searchFilter@iteration > $limit && !$collapseInit}
                {block name='snippets-filter-search-more-top'}
                    <div class="collapse {if $NaviFilter->searchFilterCompat->isActive()} show{/if}" id="box-collps-filter{$NaviFilter->searchFilterCompat->getNiceName()}" aria-expanded="false">
                        <ul class="nav flex-column">
                    {$collapseInit = true}
                {/block}
            {/if}
            {block name='snippets-filter-search-navitem'}
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
            {/block}
        {/foreach}
        {if $limit != -1 && $NaviFilter->searchFilterCompat->getOptions()|count > $limit}
            {block name='snippets-filter-search-more-bottom'}
                    </ul>
                </div>
                {button variant="link"
                    role="button"
                    class="text-right p-0 d-block"
                    data=["toggle"=> "collapse", "target"=>"#box-collps-filter{$NaviFilter->searchFilterCompat->getNiceName()}"]
                    block=true}
                    {lang key='showAll'}
                {/button}
            {/block}
        {/if}
    {/nav}
{/block}
