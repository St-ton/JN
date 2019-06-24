{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='productlist-result-options'}
    {if $device->isMobile() && !$device->isTablet()}
        {assign var=contentFilters value=$NaviFilter->getAvailableContentFilters()}
        {assign var=show_filters value=$Einstellungen.artikeluebersicht.suchfilter_anzeigen_ab == 0
        || $NaviFilter->getSearchResults()->getProductCount() >= $Einstellungen.artikeluebersicht.suchfilter_anzeigen_ab
        || $NaviFilter->getFilterCount() > 0}
        <div id="result-options" class="{if !$show_filters} d-none d-sm-block{/if}">
            {row}
            {block name='productlist-result-options-filter-link'}
                {col cols=12 md=4 class="filter-collapsible-control order-1 order-md-0"}
                    {button variant="light"
                        data=["toggle" => "collapse", "target" => "#filter-collapsible"]
                        aria=["expanded" => {$Einstellungen.template.productlist.initial_display_filter === 'Y'},
                            "controls" => "filter-collapsible"]
                        role="button"
                    }
                        <span class="fas fa-filter{if $NaviFilter->getFilterCount() > 0} text-primary{/if}"></span> {lang key='filter'}
                        <i class="fas fa-chevron-down"></i>
                    {/button}
                {/col}
            {/block}
            {/row}
            {block name='productlist-result-options-filter-collapsible'}
                {collapse id="filter-collapsible" class="mt-2 {if $Einstellungen.template.productlist.initial_display_filter === 'Y'}show{/if}" aria=["expanded" => ($Einstellungen.template.productlist.initial_display_filter === 'Y')]}
                    {row id="navbar-filter" class="d-flex flex-wrap flex-md-row"}
                        {if $show_filters}
                            {if count($contentFilters) > 0}
                                {block name='productlist-result-options-filters'}
                                {foreach $contentFilters as $filter}
                                    {if count($filter->getFilterCollection()) > 0}
                                        {foreach $filter->getOptions() as $subFilter}
                                            {if $subFilter->getVisibility() !== \JTL\Filter\Visibility::SHOW_NEVER
                                                && $subFilter->getVisibility() !== \JTL\Filter\Visibility::SHOW_BOX
                                                && $filter->getOptions()|count > 0
                                            }
                                                {button
                                                    variant="link"
                                                    class="text-decoration-none text-left"
                                                    role="button"
                                                    block=true
                                                    data=["toggle"=> "collapse", "target"=>"#filter-collapse-{$subFilter->getFrontendName()|@seofy}"]
                                                }
                                                    {$subFilter->getFrontendName()}
                                                    <i class="float-right ml-3 fas fa-plus"></i>
                                                    <span class="float-right mx-3 font-italic text-right text-truncate w-40 pr-1">
                                                        {foreach $subFilter->getOptions() as $filterOption}
                                                            {assign var=filterIsActive value=$filterOption->isActive() || $NaviFilter->getFilterValue($subFilter->getClassName()) === $filterOption->getValue()}
                                                            {if $filterIsActive === true}{$filterOption->getName()}{if !$filterOption@last},{/if} {/if}
                                                        {/foreach}
                                                    </span>
                                                    {collapse id="filter-collapse-{$subFilter->getFrontendName()|@seofy}" class="mb-2 col-12 col-md-4 max-h-150-scroll"}
                                                        {include file='snippets/filter/genericFilterItem.tpl' itemClass='' displayAt='content' filter=$subFilter sub=true}
                                                    {/collapse}
                                                {/button}
                                            {/if}
                                        {/foreach}
                                    {else}
                                        {if $filter->getInputType() === \JTL\Filter\InputType::SELECT
                                            && $filter->getOptions()|count > 0
                                        }
                                            {assign var=outerClass value='filter-type-'|cat:$filter->getNiceName()}
                                            {assign var=innerClass value='dropdown-menu'}
                                            {assign var=itemClass value=''}
                                            {button
                                                variant="link"
                                                class="text-decoration-none text-left"
                                                role="button"
                                                block=true
                                                data=["toggle"=> "collapse", "target"=>"#filter-collapse-{$filter->getFrontendName()|@seofy}"]
                                            }
                                                {$filter->getFrontendName()}
                                                <i class="float-right ml-3 fas fa-plus"></i>
                                                <span class="float-right mx-3 font-italic text-right text-truncate w-40 pr-1">
                                                    {foreach $filter->getOptions() as $filterOption}
                                                        {*TODO: Preisfilter nicht als aktiv markiert*}
                                                        {assign var=filterIsActive value=$filterOption->isActive() || $NaviFilter->getFilterValue($filter->getClassName()) === $filterOption->getValue()}
                                                        {if $filterIsActive === true}{$filterOption->getName()}{if !$filterOption@last},{/if} {/if}
                                                    {/foreach}
                                                </span>
                                                {collapse id="filter-collapse-{$filter->getFrontendName()|@seofy}" class="mb-2 col-12 col-md-4 max-h-150-scroll"}
                                                    {include file='snippets/filter/genericFilterItem.tpl' displayAt='content' itemClass=$itemClass filter=$filter}
                                                {/collapse}
                                            {/button}
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
                                    {/if}
                                {/foreach}
                                {/block}
                            {/if}
                        {/if}
                        {button
                            variant="link"
                            class="text-decoration-none text-left filter-type-FilterItemSort"
                            role="button"
                            block=true
                            data=["toggle"=> "collapse", "target"=>"#sorting-collapse"]
                        }
                            {lang key='sorting' section='productOverview'}
                            <i class="float-right ml-3 fas fa-plus"></i>
                            <span class="float-right mx-3 font-italic text-right text-truncate w-40 pr-1">
                                {foreach $Suchergebnisse->getSortingOptions() as $option}
                                    {if $option->isActive()} {$option->getName()}{/if}
                                {/foreach}
                            </span>
                            {collapse id="sorting-collapse" class="mb-2 col-12 col-md-4"}
                                {foreach $Suchergebnisse->getSortingOptions() as $option}
                                    {dropdownitem class="filter-item py-1"
                                        active=$option->isActive()
                                        href=$option->getURL()
                                        rel='nofollow'
                                    }
                                        {$option->getName()}
                                    {/dropdownitem}
                                {/foreach}
                            {/collapse}
                        {/button}
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
                                    <br/>
                                    {link href=$NaviFilter->getURL()->getUnsetAll() title="{lang key='removeFilters'}"}
                                        {lang key='removeFilters'}
                                    {/link}
                                {/if}
                            </div>
                        {/block}
                        {$alertList->displayAlertByKey('noFilterResults')}
                    {/if}
                {/collapse}
            {/block}
        </div>
    {/if}
{/block}
