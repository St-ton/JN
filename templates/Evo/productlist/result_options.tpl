{assign var=contentFilters value=$NaviFilter->getAvailableContentFilters()}
{assign var=show_filters value=$Einstellungen.artikeluebersicht.suchfilter_anzeigen_ab == 0
        || $NaviFilter->getSearchResults(false)->getProductCount() >= $Einstellungen.artikeluebersicht.suchfilter_anzeigen_ab
        || $NaviFilter->getFilterCount() > 0}
<div id="result-options" class="panel-wrap{if !$show_filters} hidden-xs{/if}">
    <div class="row">
        <div class="col-sm-8 col-sm-push-4 displayoptions form-inline text-right hidden-xs fs-0">
            {block name='productlist-result-options-sort'}
            <div class="form-group dropdown filter-type-FilterItemSort">
                <a href="#" class="btn btn-default dropdown-toggle form-control" data-toggle="dropdown" role="button" aria-expanded="true">
                    {lang key='sorting' section='productOverview'} <span class="caret"></span>
                </a>
                <ul class="dropdown-menu">
                    {foreach $Suchergebnisse->getSortingOptions() as $option}
                    <li class="filter-item{if $option->isActive()} active{/if}">
                        <a rel="nofollow" href="{$option->getURL()}">{$option->getName()}</a>
                    </li>
                    {/foreach}
                </ul>
            </div>
            <div class="form-group dropdown filter-type-FilterItemLimits">
                <a href="#" class="btn btn-default dropdown-toggle form-control" data-toggle="dropdown" role="button" aria-expanded="true">
                    {lang key='productsPerPage' section='productOverview'} <span class="caret"></span>
                </a>
                <ul class="dropdown-menu">
                    {foreach $Suchergebnisse->getLimitOptions() as $option}
                        <li class="filter-item{if $option->isActive()} active{/if}">
                            <a rel="nofollow" href="{$option->getURL()}">{$option->getName()}</a>
                        </li>
                    {/foreach}
                </ul>
            </div>
            {if isset($oErweiterteDarstellung->nDarstellung) && $Einstellungen.artikeluebersicht.artikeluebersicht_erw_darstellung === 'Y' && empty($AktuelleKategorie->categoryFunctionAttributes['darstellung'])}
                <div class="btn-group">
                    <a href="{$oErweiterteDarstellung->cURL_arr[$smarty.const.ERWDARSTELLUNG_ANSICHT_LISTE]}"
                       id="ed_list"
                       class="btn btn-default btn-option ed list{if $oErweiterteDarstellung->nDarstellung === $smarty.const.ERWDARSTELLUNG_ANSICHT_LISTE} active{/if}"
                       role="button" title="{lang key='list'
                       section='productOverview'}">
                        <span class="fa fa-th-list"></span>
                    </a>
                    <a href="{$oErweiterteDarstellung->cURL_arr[$smarty.const.ERWDARSTELLUNG_ANSICHT_GALERIE]}"
                       id="ed_gallery"
                       class="btn btn-default btn-option ed gallery{if $oErweiterteDarstellung->nDarstellung === $smarty.const.ERWDARSTELLUNG_ANSICHT_GALERIE} active{/if}"
                       role="button"
                       title="{lang key='gallery' section='productOverview'}">
                        <span class="fa fa-th-large"></span>
                    </a>
                </div>
            {/if}
            {/block}
        </div>
        {if $show_filters && count($contentFilters) > 0}
            <div class="col-sm-4 col-sm-pull-8 filter-collapsible-control">
                <a class="btn btn-default" data-toggle="collapse" href="#filter-collapsible" aria-expanded="true" aria-controls="filter-collapsible">
                    <span class="fa fa-filter"></span> {lang key='filterBy'}
                    <span class="caret"></span>
                </a>
            </div>
        {/if}
    </div>{* /row *}
    {if $show_filters}
        {if count($contentFilters) > 0}
            <div id="filter-collapsible" class="collapse in top10" aria-expanded="true">
                <nav class="panel panel-default">
                    <div id="navbar-filter" class="panel-body">
                        <div class="fs-0">
                            {foreach $contentFilters as $filter}
                                {if count($filter->getFilterCollection()) > 0}
                                    {block name='productlist-result-options-'|cat:$filter->getNiceName()}
                                        {foreach $filter->getOptions() as $subFilter}
                                            {if !$subFilter->getVisibility()->equals(\Filter\FilterVisibility::SHOW_NEVER())}
                                                <div class="form-group dropdown filter-type-{$filter->getNiceName()}">
                                                    <a href="#" class="btn btn-default dropdown-toggle form-control" data-toggle="dropdown" role="button" aria-expanded="false">
                                                        {$subFilter->getFrontendName()} <span class="caret"></span>
                                                    </a>
                                                    {include file='snippets/filter/genericFilterItem.tpl' class='dropdown-menu' filter=$subFilter sub=true}
                                                </div>
                                            {/if}
                                        {/foreach}
                                    {/block}
                                {else}
                                    {block name='productlist-result-options-'|cat:$filter->getNiceName()}
                                        {if $filter->getInputType() === $filter::INPUT_SELECT}
                                            {assign var=outerClass value='form-group dropdown filter-type-'|cat:$filter->getNiceName()}
                                            {assign var=innerClass value='dropdown-menu'}
                                            {assign var=itemClass value=''}
                                        {elseif $filter->getInputType() === $filter::INPUT_BUTTON}
                                            {assign var=outerClass value='form-group no-dropdown filter-type-'|cat:$filter->getNiceName()}
                                            {assign var=innerClass value='no-dropdown'}
                                            {assign var=itemClass value='btn btn-default'}
                                        {else}
                                            {assign var=outerClass value='form-group no-dropdown filter-type-'|cat:$filter->getNiceName()}
                                            {assign var=innerClass value='no-dropdown'}
                                            {assign var=itemClass value=''}
                                        {/if}
                                        <div class="{$outerClass}">
                                            {if $filter->getInputType() === $filter::INPUT_SELECT}
                                                <a href="#" class="btn btn-default dropdown-toggle form-control" data-toggle="dropdown" role="button" aria-expanded="false">
                                                    {$filter->getFrontendName()} <span class="caret"></span>
                                                </a>
                                            {/if}
                                            {include file='snippets/filter/genericFilterItem.tpl' class=$innerClass itemClass=$itemClass filter=$filter}
                                        </div>
                                    {/block}
                                {/if}
                            {/foreach}
                        </div>{* /form-inline2 *}
                    </div>
                    {*/.navbar-collapse*}
                </nav>
            </div>
        {/if}
        {if $NaviFilter->getFilterCount() > 0}
            <div class="clearfix top10"></div>
            <div class="active-filters panel panel-default">
                <div class="panel-body">
                    {foreach $NaviFilter->getActiveFilters() as $activeFilter}
                        {assign var=activeFilterValue value=$activeFilter->getValue()}
                        {assign var=activeValues value=$activeFilter->getActiveValues()}
                        {if $activeFilterValue !== null}
                            {if $activeValues|is_array}
                                {foreach $activeValues as $filterOption}
                                    {strip}
                                        <a href="{$activeFilter->getUnsetFilterURL($filterOption->getValue())}" rel="nofollow" title="Filter {lang key='delete'}" class="label label-info filter-type-{$activeFilter->getNiceName()}">
                                            {$filterOption->getFrontendName()}&nbsp;<span class="fa fa-trash-o"></span>
                                        </a>
                                    {/strip}
                                {/foreach}
                            {else}
                                {strip}
                                    <a href="{$activeFilter->getUnsetFilterURL($activeFilter->getValue())}" rel="nofollow" title="Filter {lang key='delete'}" class="label label-info filter-type-{$activeFilter->getNiceName()}">
                                        {$activeValues->getFrontendName()}&nbsp;<span class="fa fa-trash-o"></span>
                                    </a>
                                {/strip}
                            {/if}
                        {/if}
                    {/foreach}
                    {if $NaviFilter->getURL()->getUnsetAll() !== null}
                        {strip}
                            <a href="{$NaviFilter->getURL()->getUnsetAll()}" title="{lang key="removeFilters" section='global'}" class="label label-warning">
                                {lang key='removeFilters'}
                            </a>
                        {/strip}
                    {/if}
                </div>
            </div>{* /active-filters *}
        {/if}
    {/if}
</div>
