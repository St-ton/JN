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
        {row}
            {block name='productlist-result-options-sort'}
                {col cols=12 class="displayoptions form-inline d-flex justify-content-end order-0 order-md-1"}
                    {dropdown class="filter-type-FilterItemSort btn-group  mb-2" variant="light" text="{lang key='sorting' section='productOverview'}"}
                        {foreach $Suchergebnisse->getSortingOptions() as $option}
                            {dropdownitem rel="nofollow" href=$option->getURL() class="filter-item" active=$option->isActive()}
                                {$option->getName()}
                            {/dropdownitem}
                        {/foreach}
                    {/dropdown}
                    {dropdown class="filter-type-FilterItemLimits btn-group  mb-2" variant="light" text="{lang key='productsPerPage' section='productOverview'}"}
                        {foreach $Suchergebnisse->getLimitOptions() as $option}
                            {dropdownitem rel="nofollow" href=$option->getURL() class="filter-item" active=$option->isActive()}
                                {$option->getName()}
                            {/dropdownitem}
                        {/foreach}
                    {/dropdown}
                    {if isset($oErweiterteDarstellung->nDarstellung) && $Einstellungen.artikeluebersicht.artikeluebersicht_erw_darstellung === 'Y' && empty($AktuelleKategorie->categoryFunctionAttributes['darstellung'])}
                        {buttongroup class="mb-2"}
                            {link href=$oErweiterteDarstellung->cURL_arr[$smarty.const.ERWDARSTELLUNG_ANSICHT_LISTE]
                                id="ed_list"
                                class="btn btn-light btn-option ed list{if $oErweiterteDarstellung->nDarstellung === $smarty.const.ERWDARSTELLUNG_ANSICHT_LISTE} active{/if}"
                                role="button"
                                title="{lang key='list' section='productOverview'}"
                            }
                                <span class="fa fa-th-list"></span>
                            {/link}
                            {link href=$oErweiterteDarstellung->cURL_arr[$smarty.const.ERWDARSTELLUNG_ANSICHT_GALERIE]
                                id="ed_gallery"
                                class="btn btn-light btn-option ed gallery{if $oErweiterteDarstellung->nDarstellung === $smarty.const.ERWDARSTELLUNG_ANSICHT_GALERIE} active{/if}"
                                role="button"
                                title="{lang key='gallery' section='productOverview'}"
                            }
                                <span class="fa fa-th-large"></span>
                            {/link}
                        {/buttongroup}
                    {/if}
                {/col}
            {/block}
        {/row}
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
        {/if}
    </div>
{/block}
