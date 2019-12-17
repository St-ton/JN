{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='productlist-result-options'}
    {if $device->isMobile() || $device->isTablet()}
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
                            data=["toggle" => "collapse", "target" => "#collapseFilter"]
                            aria=["expanded" => "{if $Einstellungen.template.productlist.initial_display_filter === 'Y'}true{else}false{/if}",
                                "controls" => "collapseFilter"]
                            role="button"
                        }
                            <span class="fas fa-filter{if $NaviFilter->getFilterCount() > 0} text-primary{/if}"></span> {lang key='filter'}
                        {/button}
                    {/block}
                    {if !$device->isMobile()}
                        {block name='productlist-result-options-filter-include-layout-options'}
                            {include file='productlist/layout_options.tpl'}
                        {/block}
                    {/if}
                {/col}
            {/block}
            {/row}
            {block name='productlist-result-options-filter-collapsible'}
                {collapse id="collapseFilter"
                    class="productlist-filter {if $Einstellungen.template.productlist.initial_display_filter === 'Y'}show{/if}"
                    aria=["expanded" => "{if $Einstellungen.template.productlist.initial_display_filter === 'Y'}true{else}false{/if}"]}
                    {include file='snippets/filter/mobile.tpl'
                        NaviFilter=$NaviFilter
                        show_filters=$show_filters
                        itemCount=$itemCount}
                {/collapse}
            {/block}
        </div>
    {/if}
{/block}
