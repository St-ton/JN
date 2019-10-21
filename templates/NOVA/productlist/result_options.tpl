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
                {col cols=12 md=4 class="filter-collapsible-control order-1 order-md-0 d-flex justify-content-between"}
                    {block name='productlist-result-options-filter-link-filter'}
                        {button variant="outline-secondary"
                            data=["toggle" => "collapse", "target" => "#filter-collapsible"]
                            aria=["expanded" => "{if $Einstellungen.template.productlist.initial_display_filter === 'Y'}true{else}false{/if}",
                                "controls" => "filter-collapsible"]
                            role="button"
                        }
                            <span class="fas fa-filter{if $NaviFilter->getFilterCount() > 0} text-primary{/if}"></span> {lang key='filter'}
                        {/button}
                    {/block}
                    {block name='productlist-result-options-filter-include-layout-options'}
                        {include file='productlist/layout_options.tpl'}
                    {/block}
                {/col}
            {/block}
            {/row}
            {block name='productlist-result-options-filter-collapsible'}
                {collapse id="filter-collapsible" class="mt-2 {if $Einstellungen.template.productlist.initial_display_filter === 'Y'}show{/if}" aria=["expanded" => "{if $Einstellungen.template.productlist.initial_display_filter === 'Y'}true{else}false{/if}"]}
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
                                                {block name='productlist-result-options-filters-button'}
                                                    {button
                                                        variant="link"
                                                        class="text-decoration-none text-left"
                                                        role="button"
                                                        block=true
                                                        data=["toggle"=> "collapse", "target"=>"#filter-collapse-{$subFilter->getFrontendName()|@seofy}"]
                                                    }
                                                        {$subFilter->getFrontendName()}
                                                        <span class="float-right mx-3 font-italic text-right text-truncate w-40 pr-1">
                                                            {foreach $subFilter->getOptions() as $filterOption}
                                                                {assign var=filterIsActive value=$filterOption->isActive() || $NaviFilter->getFilterValue($subFilter->getClassName()) === $filterOption->getValue()}
                                                                {if $filterIsActive === true}{$filterOption->getName()}{if !$filterOption@last},{/if} {/if}
                                                            {/foreach}
                                                        </span>
                                                    {/button}
                                                {/block}
                                                {block name='productlist-result-options-filters-collapse'}
                                                    {collapse id="filter-collapse-{$subFilter->getFrontendName()|@seofy}" class="mb-2 col-12 col-md-4 max-h-150-scroll"}
                                                        {include file='snippets/filter/genericFilterItem.tpl' itemClass='' displayAt='content' filter=$subFilter sub=true}
                                                    {/collapse}
                                                {/block}
                                            {/if}
                                        {/foreach}
                                    {else}
                                        {if $filter->getFrontendName() === "Preisspanne"}
                                            {block name='productlist-result-options-filters-price-range'}
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
                                                {/button}
                                                {collapse id="filter-collapse-{$filter->getFrontendName()|@seofy}" class="mb-2 py-3 col-12 col-md-4 max-h-150-scroll" visible=$filter->isActive()}
                                                    {block name='boxes-box-filter-pricerange-include-price-slider'}
                                                        {include file='snippets/filter/price_slider.tpl' id='price-slider-content'}
                                                    {/block}
                                                {/collapse}
                                            {/block}
                                        {else}
                                            {if $filter->getInputType() === \JTL\Filter\InputType::SELECT
                                                && $filter->getOptions()|count > 0
                                            }
                                                {block name='productlist-result-options-filters-select'}
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
                                                        <span class="float-right mx-3 font-italic text-right text-truncate w-40 pr-1">
                                                            {foreach $filter->getOptions() as $filterOption}
                                                                {*TODO: Preisfilter nicht als aktiv markiert*}
                                                                {assign var=filterIsActive value=$filterOption->isActive() || $NaviFilter->getFilterValue($filter->getClassName()) === $filterOption->getValue()}
                                                                {if $filterIsActive === true}{$filterOption->getName()}{if !$filterOption@last},{/if} {/if}
                                                            {/foreach}
                                                        </span>
                                                    {/button}
                                                    {collapse id="filter-collapse-{$filter->getFrontendName()|@seofy}" class="mb-2 col-12 col-md-4 max-h-150-scroll"}
                                                        {include file='snippets/filter/genericFilterItem.tpl' displayAt='content' itemClass=$itemClass filter=$filter}
                                                    {/collapse}
                                                {/block}
                                            {elseif $filter->getInputType() === \JTL\Filter\InputType::BUTTON}
                                                {block name='productlist-result-options-filters-button'}
                                                    {assign var=outerClass value='no-dropdown filter-type-'|cat:$filter->getNiceName()}
                                                    {assign var=innerClass value='no-dropdown'}
                                                    {assign var=itemClass value='btn btn-light'}
                                                    {include file='snippets/filter/genericFilterItem.tpl' class=$innerClass itemClass=$itemClass filter=$filter}
                                                {/block}
                                            {else}
                                                {block name='productlist-result-options-filters-else'}
                                                    {assign var=outerClass value='no-dropdown filter-type-'|cat:$filter->getNiceName()}
                                                    {assign var=innerClass value='no-dropdown'}
                                                    {assign var=itemClass value=''}
                                                    {include file='snippets/filter/genericFilterItem.tpl' class=$innerClass itemClass=$itemClass filter=$filter}
                                                {/block}
                                            {/if}
                                        {/if}
                                    {/if}
                                {/foreach}
                                {/block}
                            {/if}
                        {/if}
                        {block name='productlist-result-options-sorting'}
                            {button
                                variant="link"
                                class="text-decoration-none text-left filter-type-FilterItemSort"
                                role="button"
                                block=true
                                data=["toggle"=> "collapse", "target"=>"#sorting-collapse"]
                            }
                                {lang key='sorting' section='productOverview'}
                                <span class="float-right mx-3 font-italic text-right text-truncate w-40 pr-1">
                                    {foreach $Suchergebnisse->getSortingOptions() as $option}
                                        {if $option->isActive()} {$option->getName()}{/if}
                                    {/foreach}
                                </span>
                            {/button}
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
                        {/block}
                    {/row}
                    {block name='productlist-result-options-include-active-filter'}
                        {include file='snippets/filter/active_filter.tpl'}
                    {/block}
                {/collapse}
            {/block}
        </div>
    {/if}
{/block}
