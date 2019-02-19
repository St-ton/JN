{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{assign var=contentFilters value=$NaviFilter->getAvailableContentFilters()}
{assign var=show_filters value=$Einstellungen.artikeluebersicht.suchfilter_anzeigen_ab == 0
        || $NaviFilter->getSearchResults()->getProductCount() >= $Einstellungen.artikeluebersicht.suchfilter_anzeigen_ab
        || $NaviFilter->getFilterCount() > 0}
<div id="result-options" class="{if !$show_filters} d-none d-sm-block{/if}">
    {row}
        {if $show_filters && count($contentFilters) > 0}
            {col cols=12 sm=4 class="filter-collapsible-control"}
                {link class="btn btn-link" data=["toggle" => "collapse"] href="#filter-collapsible" aria=["expanded" => {$Einstellungen.template.productlist.initial_display_filter === 'Y'}, "controls" => "filter-collapsible"]}
                    <span class="fas fa-filter"></span> {lang key='filterBy'}
                    <i class="fas fa-chevron-down"></i>
                {/link}
            {/col}
        {/if}
        {col cols=12 sm="{if $show_filters && count($contentFilters) > 0}8{else}12{/if}" class="displayoptions form-inline text-right d-block fs-0"}
            {block name='productlist-result-options-sort'}
            {dropdown class="filter-type-FilterItemSort btn-group ml-3" variant="light" text="{lang key='sorting' section='productOverview'}"}
                {foreach $Suchergebnisse->getSortingOptions() as $option}
                    {dropdownitem rel="nofollow" href="{$option->getURL()}" class="filter-item" active=$option->isActive()}
                        {$option->getName()}
                    {/dropdownitem}
                {/foreach}
            {/dropdown}
            {dropdown class="filter-type-FilterItemLimits btn-group ml-3" variant="light" text="{lang key='productsPerPage' section='productOverview'}"}
                {foreach $Suchergebnisse->getLimitOptions() as $option}
                    {dropdownitem rel="nofollow" href="{$option->getURL()}" class="filter-item" active=$option->isActive()}
                        {$option->getName()}
                    {/dropdownitem}
                {/foreach}
            {/dropdown}
            {if isset($oErweiterteDarstellung->nDarstellung) && $Einstellungen.artikeluebersicht.artikeluebersicht_erw_darstellung === 'Y' && empty($AktuelleKategorie->categoryFunctionAttributes['darstellung'])}
                {buttongroup class="ml-3"}
                    {link href="{$oErweiterteDarstellung->cURL_arr[$smarty.const.ERWDARSTELLUNG_ANSICHT_LISTE]}"
                       id="ed_list"
                       class="btn btn-light btn-option ed list{if $oErweiterteDarstellung->nDarstellung === $smarty.const.ERWDARSTELLUNG_ANSICHT_LISTE} active{/if}"
                       role="button"
                       title="{lang key='list' section='productOverview'}"}
                        <span class="fa fa-th-list"></span>
                    {/link}
                    {link href="{$oErweiterteDarstellung->cURL_arr[$smarty.const.ERWDARSTELLUNG_ANSICHT_GALERIE]}"
                       id="ed_gallery"
                       class="btn btn-light btn-option ed gallery{if $oErweiterteDarstellung->nDarstellung === $smarty.const.ERWDARSTELLUNG_ANSICHT_GALERIE} active{/if}"
                       role="button"
                       title="{lang key='gallery' section='productOverview'}"}
                        <span class="fa fa-th-large"></span>
                    {/link}
                {/buttongroup}
            {/if}
            {/block}
        {/col}
    {/row}
    {if $show_filters}
        {if count($contentFilters) > 0}
            {collapse id="filter-collapsible" class="mt-2 {if $Einstellungen.template.productlist.initial_display_filter === 'Y'}show{/if}" aria=["expanded" => ($Einstellungen.template.productlist.initial_display_filter === 'Y')]}
                <div id="navbar-filter">
                    {foreach $contentFilters as $filter}
                        {if count($filter->getFilterCollection()) > 0}
                            {block name='productlist-result-options-'|cat:$filter->getNiceName()}
                                {foreach $filter->getOptions() as $subFilter}
                                    {if $subFilter->getVisibility() !== \JTL\Filter\Visibility::SHOW_NEVER && $subFilter->getVisibility() !== \JTL\Filter\Visibility::SHOW_BOX}
                                        {dropdown text="{$subFilter->getFrontendName()}" variant="light" class="btn-group mb-2 mr-2"}
                                            {include file='snippets/filter/genericFilterItem.tpl' itemClass='' displayAt='content' filter=$subFilter sub=true}
                                        {/dropdown}
                                    {/if}
                                {/foreach}
                            {/block}
                        {else}
                            {block name='productlist-result-options-'|cat:$filter->getNiceName()}
                                {if $filter->getInputType() === \JTL\Filter\InputType::SELECT}
                                    {assign var=outerClass value='filter-type-'|cat:$filter->getNiceName()}
                                    {assign var=innerClass value='dropdown-menu'}
                                    {assign var=itemClass value=''}
                                    {dropdown class="{$outerClass}" text="{$filter->getFrontendName()}" variant="light" class="btn-group mb-2 mr-2"}
                                        {if $filter->getInputType() === \JTL\Filter\InputType::SELECT}
                                            {include file='snippets/filter/genericFilterItem.tpl' displayAt='content' itemClass=$itemClass filter=$filter}
                                        {/if}
                                    {/dropdown}
                                {elseif $filter->getInputType() === \JTL\Filter\InputType::BUTTON}
                                    {assign var=outerClass value='no-dropdown filter-type-'|cat:$filter->getNiceName()}
                                    {assign var=innerClass value='no-dropdown'}
                                    {assign var=itemClass value='btn btn-light'}
                                    {include file='snippets/filter/genericFilterItem.tpl' class=$innerClass itemClass=$itemClass filter=$filter}
                                {else}
                                    {assign var=outerClass value='no-dropdown filter-type-'|cat:$filter->getNiceName()}
                                    {assign var=innerClass value='no-dropdown'}
                                    {assign var=itemClass value=''}
                                    {include file='snippets/filter/genericFilterItem.tpl' class=$innerClass itemClass=$itemClass filter=$filter}
                                {/if}
                            {/block}
                        {/if}
                    {/foreach}
                </div>
            {/collapse}
        {/if}
        {if $NaviFilter->getFilterCount() > 0}
            <div class="clearfix mt-2"></div>
            <div class="active-filters">
                {foreach $NaviFilter->getActiveFilters() as $activeFilter}
                    {assign var=activeFilterValue value=$activeFilter->getValue()}
                    {assign var=activeValues value=$activeFilter->getActiveValues()}
                    {if $activeFilterValue !== null}
                        {if $activeValues|is_array}
                            {foreach $activeValues as $filterOption}
                                {strip}
                                    {link href="{$activeFilter->getUnsetFilterURL($filterOption->getValue())}" rel="nofollow" title="Filter {lang key='delete'}" class="badge badge-info filter-type-{$activeFilter->getNiceName()} mb-2 mr-2"}
                                        {$filterOption->getFrontendName()}&nbsp;<span class="fa fa-trash"></span>
                                    {/link}
                                {/strip}
                            {/foreach}
                        {else}
                            {strip}
                                {link href="{$activeFilter->getUnsetFilterURL($activeFilter->getValue())}" rel="nofollow" title="Filter {lang key='delete'}" class="badge badge-info filter-type-{$activeFilter->getNiceName()} mb-2 mr-2" }
                                    {$activeValues->getFrontendName()}&nbsp;<span class="fa fa-trash"></span>
                                {/link}
                            {/strip}
                        {/if}
                    {/if}
                {/foreach}
                {if $NaviFilter->getURL()->getUnsetAll() !== null}
                    {strip}
                        {link href="{$NaviFilter->getURL()->getUnsetAll()}" title="{lang key='removeFilters'}"}
                            {lang key='removeFilters'}
                        {/link}
                    {/strip}
                {/if}
            </div>
        {/if}
    {/if}
</div>
