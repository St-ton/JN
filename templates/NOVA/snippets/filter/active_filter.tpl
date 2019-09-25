{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='snippets-filter-active-filter'}
{if $NaviFilter->getFilterCount() > 0}
    {block name='snippets-filter-active-filter-content'}
        <div class="clearfix mt-2"></div>
        <div class="active-filters">
            {foreach $NaviFilter->getActiveFilters() as $activeFilter}
                {assign var=activeFilterValue value=$activeFilter->getValue()}
                {assign var=activeValues value=$activeFilter->getActiveValues()}
                {if $activeFilterValue !== null}
                    {if $activeValues|is_array}
                        {foreach $activeValues as $filterOption}
                            {link
                            href=$activeFilter->getUnsetFilterURL($filterOption->getValue())
                            rel="nofollow"
                            title="Filter {lang key='delete'}"
                            class="btn btn-light btn-sm filter-type-{$activeFilter->getNiceName()} mb-2 mr-2"
                            }
                            {$filterOption->getFrontendName()}<span class="fa fa-times ml-2"></span>
                            {/link}
                        {/foreach}
                    {else}
                        {link
                        href=$activeFilter->getUnsetFilterURL($activeFilter->getValue())
                        rel="nofollow"
                        title="Filter {lang key='delete'}"
                        class="btn btn-light btn-sm filter-type-{$activeFilter->getNiceName()} mb-2 mr-2"
                        }
                        {$activeValues->getFrontendName()}<span class="fa fa-times ml-2"></span>
                        {/link}
                    {/if}
                {/if}
            {/foreach}
            {if $NaviFilter->getURL()->getUnsetAll() !== null}
                {link href=$NaviFilter->getURL()->getUnsetAll()
                    title="{lang key='removeFilters'}"
                    class='text-decoration-none d-inline-block'}
                    {lang key='removeFilters'}
                {/link}
            {/if}
        </div>
    {/block}
{/if}
{/block}
