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
                    {button variant="light"
                        data=["toggle" => "collapse", "target" => "#filter-collapsible"]
                        aria=["expanded" => {$Einstellungen.template.productlist.initial_display_filter === 'Y'},
                            "controls" => "filter-collapsible"]
                        role="button"
                    }
                        <span class="fas fa-filter{if $NaviFilter->getFilterCount() > 0} text-primary{/if}"></span> {lang key='filter'}
                        <i class="fas fa-chevron-down"></i>
                    {/button}
                    {if isset($oErweiterteDarstellung->nDarstellung)
                        && $Einstellungen.artikeluebersicht.artikeluebersicht_erw_darstellung === 'Y'
                        && empty($AktuelleKategorie->categoryFunctionAttributes['darstellung'])
                        && $navid === 'header'}
                        {buttongroup class="ml-2"}
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
                                                {/button}
                                                {collapse id="filter-collapse-{$subFilter->getFrontendName()|@seofy}" class="mb-2 col-12 col-md-4 max-h-150-scroll"}
                                                    {include file='snippets/filter/genericFilterItem.tpl' itemClass='' displayAt='content' filter=$subFilter sub=true}
                                                {/collapse}
                                            {/if}
                                        {/foreach}
                                    {else}
                                        {if $filter->getFrontendName() === "Preisspanne"}
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

                                            {/button}
                                            {collapse id="filter-collapse-{$filter->getFrontendName()|@seofy}" class="mb-2 py-3 col-12 col-md-4 max-h-150-scroll"}
                                                {inputgroup class="mb-3" size="sm"}
                                                    {input id="price-range-from-content" class="price-range-input mr-4" placeholder=0}
                                                    {input id="price-range-to-content" class="price-range-input ml-4" placeholder=$priceRangeMax}
                                                {/inputgroup}
                                                <div id="price-range-slider-content" class="mx-3 mb-3"></div>
                                            {/collapse}

                                            <script>
                                                $(window).on('load', function(){
                                                    let priceRange       = (new URL(window.location.href)).searchParams.get("pf"),
                                                        priceRangeMin    = 0,
                                                        priceRangeMax    = {$priceRangeMax},
                                                        currentPriceMin  = priceRangeMin,
                                                        currentPriceMax  = priceRangeMax,
                                                        $priceRangeFrom  = $("#price-range-from-content"),
                                                        $priceRangeTo    = $("#price-range-to-content");
                                                    if (priceRange != null) {
                                                        let priceRangeMinMax = priceRange.split('_');
                                                        currentPriceMin      = priceRangeMinMax[0];
                                                        currentPriceMax      = priceRangeMinMax[1];
                                                        $priceRangeFrom.val(currentPriceMin);
                                                        $priceRangeTo.val(currentPriceMax);
                                                    }
                                                    $('#price-range-slider-content').slider({
                                                        range: true,
                                                        min: priceRangeMin,
                                                        max: priceRangeMax,
                                                        values: [currentPriceMin, currentPriceMax],
                                                        slide: function(event, ui) {
                                                            $priceRangeFrom.val(ui.values[0]);
                                                            $priceRangeTo.val(ui.values[1]);
                                                        },
                                                        stop: function(event, ui) {
                                                            redirectToNewPriceRange(ui.values[0] + '_' + ui.values[1]);
                                                        }
                                                    });
                                                    $('.price-range-input').change(function () {
                                                        let prFrom = $priceRangeFrom.val(),
                                                            prTo   = $priceRangeTo.val();
                                                        redirectToNewPriceRange(
                                                            (prFrom > 0 ? prFrom : priceRangeMin) + '_' + (prTo > 0 ? prTo : priceRangeMax)
                                                        );
                                                    });

                                                    function redirectToNewPriceRange(priceRange) {
                                                        window.location.href = updateURLParameter(
                                                            window.location.href,
                                                            'pf',
                                                            priceRange
                                                        );
                                                    }

                                                    function updateURLParameter(url, param, paramVal){
                                                        let newAdditionalURL = '',
                                                            tempArray        = url.split('?'),
                                                            baseURL          = tempArray[0],
                                                            additionalURL    = tempArray[1],
                                                            temp             = '';
                                                        if (additionalURL) {
                                                            tempArray = additionalURL.split('&');
                                                            for (let i=0; i<tempArray.length; i++){
                                                                if(tempArray[i].split('=')[0] != param){
                                                                    newAdditionalURL += temp + tempArray[i];
                                                                    temp = '&';
                                                                }
                                                            }
                                                        }

                                                        return baseURL + '?' + newAdditionalURL + temp + param + '=' + paramVal;
                                                    }
                                                });
                                            </script>
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
                                                {/button}
                                                {collapse id="filter-collapse-{$filter->getFrontendName()|@seofy}" class="mb-2 col-12 col-md-4 max-h-150-scroll"}
                                                    {include file='snippets/filter/genericFilterItem.tpl' displayAt='content' itemClass=$itemClass filter=$filter}
                                                {/collapse}
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
                    {/row}
                    {include file='snippets/filter/active_filter.tpl'}
                {/collapse}
            {/block}
        </div>
    {/if}
{/block}
