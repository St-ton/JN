{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='snippets-filter-mobile'}
    {if $device->isMobile() && !$device->isTablet()}
        <span class="h2 mt-3 border-0 px-3" id="productlist-filter">{lang key='filterAndSort'}</span>
    {/if}
    <div class="productlist-filter-wrapper">
        <ul class="productlist-filter-accordion border-md-bottom border-lg-bottom-0">
        {block name='productlist-result-options-sorting'}
            <li>
                {link class="text-decoration-none text-left filter-type-FilterItemSort"
                data=["toggle"=> "collapse", "target"=>"#sorting-collapse"]}
                {lang key='sorting' section='productOverview'}
                    <span class="float-right mx-3 font-italic text-right text-truncate w-40 pr-1">
                    {foreach $Suchergebnisse->getSortingOptions() as $option}
                        {if $option->isActive()} {$option->getName()}{/if}
                    {/foreach}
                </span>
                {/link}
                {collapse id="sorting-collapse" class="my-2"}
                {foreach $Suchergebnisse->getSortingOptions() as $option}
                    {dropdownitem class="filter-item py-1"
                    active=$option->isActive()
                    href=$option->getURL()
                    rel='nofollow'}
                    {$option->getName()}
                    {/dropdownitem}
                {/foreach}
                {/collapse}
            </li>
        {/block}
        {if $show_filters}
            {if count($NaviFilter->getAvailableContentFilters()) > 0}
                {block name='productlist-result-options-filters'}
                    {foreach $NaviFilter->getAvailableContentFilters() as $filter}
                        {if count($filter->getFilterCollection()) > 0}
                            {foreach $filter->getOptions() as $subFilter}
                                {if $subFilter->getVisibility() !== \JTL\Filter\Visibility::SHOW_NEVER
                                    && $subFilter->getVisibility() !== \JTL\Filter\Visibility::SHOW_BOX
                                    && $filter->getOptions()|count > 0}
                                    <li>
                                        {block name='productlist-result-options-filters-button'}
                                            {link class="collapsed"
                                                data=["toggle"=> "collapse", "target"=>"#filter-collapse-{$subFilter->getFrontendName()|@seofy}"]}
                                                {$subFilter->getFrontendName()}
                                            {/link}
                                        {/block}
                                        {block name='productlist-result-options-filters-collapse'}
                                            {collapse id="filter-collapse-{$subFilter->getFrontendName()|@seofy}"
                                                class="my-2"
                                                visible=$subFilter->isActive()}
                                                {if ($subFilter->getData('cTyp') === 'SELECTBOX') && $subFilter->getOptions()|@count > 0}
                                                    {dropdown variant="outline-secondary" text="{lang key='selectFilter' section='global'} " toggle-class="btn-block text-left"}
                                                        {include file='snippets/filter/characteristic.tpl' Merkmal=$subFilter sub=true}
                                                    {/dropdown}
                                                {else}
                                                    {include file='snippets/filter/characteristic.tpl' Merkmal=$subFilter sub=true}
                                                {/if}
                                            {/collapse}
                                        {/block}
                                    </li>
                                {/if}
                            {/foreach}
                        {elseif $filter->getOptions()|count > 0}
                            <li>
                                {if $filter->getClassName() === "JTL\Filter\Items\PriceRange"}
                                    {block name='productlist-result-options-filters-price-range'}
                                        {link class="collapsed"
                                            data=["toggle"=> "collapse", "target"=>"#filter-collapse-{$filter->getFrontendName()|@seofy}"]}
                                            {$filter->getFrontendName()}
                                        {/link}
                                        {collapse id="filter-collapse-{$filter->getFrontendName()|@seofy}"
                                            class="my-2 py-2"
                                            visible=$Einstellungen.template.sidebar_settings.always_show_price_range === 'Y' || $filter->isActive()}
                                            {block name='boxes-box-filter-pricerange-include-price-slider'}
                                                {input data=['id'=>'js-price-range-url'] type="hidden" value="{$NaviFilter->getFilterURL()->getURL()}"}
                                                {include file='snippets/filter/price_slider.tpl' id='price-slider-content'}
                                            {/block}
                                        {/collapse}
                                    {/block}
                                {elseif $filter->getClassName() === "JTL\Filter\Items\Search"}
                                    {block name='productlist-result-options-filters-price-range'}
                                        {link class="collapsed"
                                            data=["toggle"=> "collapse", "target"=>"#filter-collapse-{$filter->getFrontendName()|@seofy}"]}
                                            {$filter->getFrontendName()}
                                        {/link}
                                        {collapse id="filter-collapse-{$filter->getFrontendName()|@seofy}"
                                            class="my-2 py-2"
                                            visible=$filter->isActive()}
                                            {block name='boxes-box-filter-pricerange-include-price-slider'}
                                                {include file='snippets/filter/search.tpl'}
                                            {/block}
                                        {/collapse}
                                    {/block}
                                {elseif $filter->getClassName() === "JTL\Filter\Items\Manufacturer"}
                                    {block name='productlist-result-options-filters-price-range'}
                                        {link class="collapsed"
                                            data=["toggle"=> "collapse", "target"=>"#filter-collapse-{$filter->getFrontendName()|@seofy}"]}
                                            {$filter->getFrontendName()}
                                        {/link}
                                        {collapse id="filter-collapse-{$filter->getFrontendName()|@seofy}"
                                            class="my-2 py-2"
                                            visible=$filter->isActive()}
                                            {block name='boxes-box-filter-pricerange-include-price-slider'}
                                                {include file='snippets/filter/manufacturer.tpl'}
                                            {/block}
                                        {/collapse}
                                    {/block}
                                {elseif $filter->getOptions()|count > 0}
                                    {block name='productlist-result-options-filters-select'}
                                        {link data=["toggle"=> "collapse", "target"=>"#filter-collapse-{$filter->getFrontendName()|@seofy}"]}
                                            {$filter->getFrontendName()}
                                        {/link}
                                        {collapse id="filter-collapse-{$filter->getFrontendName()|@seofy}"
                                            class="my-2"
                                            visible=$filter->isActive()}
                                            {include file='snippets/filter/genericFilterItem.tpl' filter=$filter}
                                        {/collapse}
                                    {/block}
                                {/if}
                            </li>
                        {/if}
                    {/foreach}
                {/block}
            {/if}
        {/if}
        </ul>
        {block name='productlist-result-options-include-active-filter'}
            <div class="productlist-applied-filter mb-5">
                {include file='snippets/filter/active_filter.tpl'}
            </div>
        {/block}
    </div>
    <div class="productlist-filter-footer px-3 mt-auto">
        {formrow class="justify-content-end align-items-center"}
            {col}
                {button block=true
                    variant="outline-primary"
                    class="my-1 no-caret"
                    data=['toggle'=>'collapse', 'dismiss'=>'modal']
                    href="#collapseFilter"
                    aria=['expanded'=>'true','controls'=>'collapseFilter']}
                    {lang key='filterCancel'}
                {/button}
            {/col}
            {col}
                {button type="link"
                    block=true
                    variant="primary"
                    class="min-w-sm my-1 text-nowrap"
                    href="{$NaviFilter->getURL()->getCategories()}"}
                    {lang key='filterShowItem' printf=$itemCount}
                {/button}
            {/col}
        {/formrow}
    </div>
    <div class="js-helpers">
        {input id="js-price-redirect" type="hidden" value=1}
    </div>
    {inline_script}<script>
        {literal}
            $('.js-collapse-filter .filter-item, .js-collapse-filter .js-filter-item').on('click', function(e) {
                e.preventDefault();
                $.evo.initFilters($(this).attr('href'));
            });
        {/literal}
    </script>{/inline_script}
{/block}
