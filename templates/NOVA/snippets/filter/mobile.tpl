{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='snippets-filter-mobile'}
    <span class="h2 mt-3 border-0 px-3 d-md-none" id="productlist-filter">Filter &amp; Sortierung</span>
    <div class="productlist-filter-wrapper">
        <ul class="productlist-filter-accordion border-md-bottom border-lg-bottom-0">
        {if $show_filters}
            {if count($NaviFilter->getAvailableContentFilters()) > 0}
                {block name='productlist-result-options-filters'}
                    {foreach $NaviFilter->getAvailableContentFilters() as $filter}
                        {if count($filter->getFilterCollection()) > 0}
                            {foreach $filter->getOptions() as $subFilter}

                                {if $subFilter->getVisibility() !== \JTL\Filter\Visibility::SHOW_NEVER
                                && $subFilter->getVisibility() !== \JTL\Filter\Visibility::SHOW_BOX
                                && $filter->getOptions()|count > 0
                                }
                                    <li>
                                        {block name='productlist-result-options-filters-button'}
                                            {link
                                                class="collapsed"
                                                data=["toggle"=> "collapse", "target"=>"#filter-collapse-{$subFilter->getFrontendName()|@seofy}"]
                                            }
                                                {$subFilter->getFrontendName()}
                                            {/link}
                                        {/block}
                                        {block name='productlist-result-options-filters-collapse'}
                                            {collapse id="filter-collapse-{$subFilter->getFrontendName()|@seofy}"
                                                class="mb-2 col-12 col-md-4 max-h-150-scroll"
                                                visible=$subFilter->isActive()}
                                                {include file='snippets/filter/genericFilterItem.tpl' itemClass='' displayAt='content' filter=$subFilter sub=true}
                                            {/collapse}
                                        {/block}
                                    </li>
                                {/if}
                            {/foreach}
                        {else}
                            {if $filter->getFrontendName() === "Preisspanne"}
                                <li>
                                {block name='productlist-result-options-filters-price-range'}
                                    {assign var=outerClass value='filter-type-'|cat:$filter->getNiceName()}
                                    {assign var=innerClass value='dropdown-menu'}
                                    {assign var=itemClass value=''}
                                    {link
                                        class="collapsed"
                                        data=["toggle"=> "collapse", "target"=>"#filter-collapse-{$filter->getFrontendName()|@seofy}"]}
                                        {$filter->getFrontendName()}
                                    {/link}
                                    {collapse id="filter-collapse-{$filter->getFrontendName()|@seofy}"
                                        class="mb-2 py-3 col-12 col-md-4 max-h-150-scroll"
                                        visible=$filter->isActive()}
                                        {block name='boxes-box-filter-pricerange-include-price-slider'}
                                            {include file='snippets/filter/price_slider.tpl' id='price-slider-content'}
                                        {/block}
                                    {/collapse}
                                {/block}
                                </li>
                            {elseif $filter->getOptions()|count > 0}
                                {if $filter->getInputType() === \JTL\Filter\InputType::SELECT}
                                    <li>
                                    {block name='productlist-result-options-filters-select'}
                                        {assign var=outerClass value='filter-type-'|cat:$filter->getNiceName()}
                                        {assign var=innerClass value='dropdown-menu'}
                                        {assign var=itemClass value=''}
                                        {link data=["toggle"=> "collapse", "target"=>"#filter-collapse-{$filter->getFrontendName()|@seofy}"]}
                                            {$filter->getFrontendName()}
                                        {/link}
                                        {collapse id="filter-collapse-{$filter->getFrontendName()|@seofy}"
                                            class="mb-2 col-12 col-md-4 max-h-150-scroll"
                                            visible=$filter->isActive()}
                                            {include file='snippets/filter/genericFilterItem.tpl' displayAt='content' itemClass=$itemClass filter=$filter}
                                        {/collapse}
                                    {/block}
                                    </li>
                                {elseif $filter->getInputType() === \JTL\Filter\InputType::BUTTON}
                                    <li>
                                    {block name='productlist-result-options-filters-button'}
                                        {assign var=outerClass value='no-dropdown filter-type-'|cat:$filter->getNiceName()}
                                        {assign var=innerClass value='no-dropdown'}
                                        {assign var=itemClass value='btn btn-light'}
                                        {include file='snippets/filter/genericFilterItem.tpl' class=$innerClass itemClass=$itemClass filter=$filter}
                                    {/block}
                                    </li>
                                {else}
                                    <li>
                                    {block name='productlist-result-options-filters-else'}
                                        {assign var=outerClass value='no-dropdown filter-type-'|cat:$filter->getNiceName()}
                                        {assign var=innerClass value='no-dropdown'}
                                        {assign var=itemClass value=''}
                                        {include file='snippets/filter/genericFilterItem.tpl' class=$innerClass itemClass=$itemClass filter=$filter}
                                    {/block}
                                    </li>
                                {/if}
                            {/if}
                        {/if}
                    {/foreach}
                {/block}
            {/if}
        {/if}
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
            </li>
        {/block}
        </ul>
        {block name='productlist-result-options-include-active-filter'}
            <div class="productlist-applied-filter mb-5 d-md-none">
                {include file='snippets/filter/active_filter.tpl'}
            </div>
        {/block}
    </div>
    <div class="productlist-filter-footer px-3 mt-auto">
        <div class="form-row d-lg-none justify-content-end align-items-center">
            <div class="col">
                {button block=true
                    variant="outline-primary"
                    class="my-1 no-caret"
                    data=['toggle'=>'collapse']
                    href="#collapseFilter"
                    aria=['expanded'=>'true','controls'=>'collapseFilter']}
                Abbrechen
                {/button}
            </div>
            <div class="col">
                {button type="link"
                        block=true
                        variant="primary"
                        class="min-w-sm my-1 text-nowrap"
                        href="{$NaviFilter->getURL()->getCategories()}"}
                    {$itemCount} Produkte ansehen
                {/button}
            </div>
        </div>
    </div>

    {inline_script}<script>
        {literal}
            $('#collapseFilter .filter-item, #collapseFilter .js-filter-item').on('click', function(e) {
                e.preventDefault();

                var $wrapper = $('#collapseFilter'),
                    $spinner = $.evo.extended().spinner($wrapper.get(0));

                $wrapper.addClass('loading');
                console.log($spinner);
                console.log('blub1');
                $.ajax($(this).attr('href'), {data: {'isAjax':1, 'quickView':1}})
                .done(function(data) {
                    $wrapper.html(data);
                })
                .always(function() {
                    $spinner.stop();
                    $wrapper.removeClass('loading');
                });
            });
        {/literal}
    </script>{/inline_script}
{/block}
