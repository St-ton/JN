{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='productlist-result-options'}
    {assign var=contentFilters value=$NaviFilter->getAvailableContentFilters()}
    {assign var=show_filters value=$Einstellungen.artikeluebersicht.suchfilter_anzeigen_ab == 0
    || $NaviFilter->getSearchResults()->getProductCount() >= $Einstellungen.artikeluebersicht.suchfilter_anzeigen_ab
    || $NaviFilter->getFilterCount() > 0}
    <div id="result-options" class="{if !$show_filters} d-none d-sm-block{/if}">
        {if $NaviFilter->getFilterCount() > 0}
            {block name='productlist-result-options-active-filters'}
                <div class="clearfix mt-2"></div>
                <div class="active-filters">
                    {foreach $NaviFilter->getActiveFilters() as $activeFilter}
                        {assign var=activeFilterValue value=$activeFilter->getValue()}
                        {assign var=activeValues value=$activeFilter->getActiveValues()}
                        {if $activeFilterValue !== null}
                            {if $activeValues|is_array}
                                {foreach $activeValues as $filterOption}
                                    {strip}
                                        {link href=$activeFilter->getUnsetFilterURL($filterOption->getValue()) rel="nofollow" title="Filter {lang key='delete'}" class="badge badge-info filter-type-{$activeFilter->getNiceName()} mb-2 mr-2"}
                                            {$filterOption->getFrontendName()}&nbsp;<span class="fa fa-trash"></span>
                                        {/link}
                                    {/strip}
                                {/foreach}
                            {else}
                                {strip}
                                    {link href=$activeFilter->getUnsetFilterURL($activeFilter->getValue()) rel="nofollow" title="Filter {lang key='delete'}" class="badge badge-info filter-type-{$activeFilter->getNiceName()} mb-2 mr-2" }
                                        {$activeValues->getFrontendName()}&nbsp;<span class="fa fa-trash"></span>
                                    {/link}
                                {/strip}
                            {/if}
                        {/if}
                    {/foreach}
                    {if $NaviFilter->getURL()->getUnsetAll() !== null}
                        {strip}
                            {link href=$NaviFilter->getURL()->getUnsetAll() title="{lang key='removeFilters'}"}
                                {lang key='removeFilters'}
                            {/link}
                        {/strip}
                    {/if}
                </div>
            {/block}
            {$alertList->displayAlertByKey('noFilterResults')}
        {/if}
    </div>
{/block}
