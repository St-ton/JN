{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='productlist-result-options'}
    {assign var=show_filters value=$Einstellungen.artikeluebersicht.suchfilter_anzeigen_ab == 0
    || $NaviFilter->getSearchResults()->getProductCount() >= $Einstellungen.artikeluebersicht.suchfilter_anzeigen_ab
    || $NaviFilter->getFilterCount() > 0}
    {if $device->isMobile()}
        {$filterPlacement="collapse"}
    {elseif $device->isTablet() || $Einstellungen.template.productlist.filter_placement === 'M'}
        {$filterPlacement="modal"}
    {/if}

    <div id="result-options" class="{if empty($filterPlacement)}d-lg-none{/if}">
        {row}
        {block name='productlist-result-options-filter-link'}
            {col cols=12 md=4 class="filter-collapsible-control order-1 order-md-0 d-flex justify-content-between"}
                {block name='productlist-result-options-filter-link-filter'}
                    {button id="js-filters" variant="outline-secondary"
                        class="text-nowrap"
                        data=["toggle" => "{if !empty($filterPlacement)}{$filterPlacement}{else}modal{/if}", "target" => "#collapseFilter"]
                        aria=["expanded" => "{if $Einstellungen.template.productlist.initial_display_filter === 'Y'}true{else}false{/if}",
                            "controls" => "collapseFilter"]
                        role="button"}
                        <span class="fas fa-filter{if $NaviFilter->getFilterCount() > 0} text-primary{/if}"></span> {lang key='filter'}
                    {/button}
                {/block}
                {if !empty($filterPlacement) && !$filterPlacement === "collapse"}
                    {block name='productlist-result-options-filter-include-layout-options'}
                        {include file='productlist/layout_options.tpl'}
                    {/block}
                {/if}
            {/col}
        {/block}
        {/row}

        {block name='productlist-result-options-filter-collapsible'}
            {if !empty($filterPlacement) && $filterPlacement === 'collapse' && $show_filters}
                {collapse id="collapseFilter"
                    class="productlist-filter js-collapse-filter {if $Einstellungen.template.productlist.initial_display_filter === 'Y'}show{/if}"
                    aria=["expanded" => "{if $Einstellungen.template.productlist.initial_display_filter === 'Y'}true{else}false{/if}"]}
                {/collapse}
            {else}
                <div class="modal" id="collapseFilter">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">{lang key='filterAndSort'}</h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body js-collapse-filter">
                            </div>
                        </div>
                    </div>
                </div>

            {/if}
            {inline_script}<script>
                let initiallized = false;
                $('#js-filters').on('click', function() {
                    if (!initiallized) {
                        $.evo.initFilters(window.location.href);
                        initiallized = true;
                    }
                });
            </script>{/inline_script}
        {/block}
    </div>
{/block}
