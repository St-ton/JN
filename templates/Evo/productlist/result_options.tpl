{assign var='show_filters' value=false}
{if $Einstellungen.artikeluebersicht.suchfilter_anzeigen_ab == 0 || count($Suchergebnisse->Artikel->elemente) >= $Einstellungen.artikeluebersicht.suchfilter_anzeigen_ab || $NaviFilter->nAnzahlFilter > 0}
    {assign var='show_filters' value=true}
{/if}
<div id="result-options" class="panel-wrap{if !$show_filters} hidden-xs{/if}">
    <style>li span.value { padding-right:40px; }</style>
    <div class="row">
        <div class="col-sm-8 col-sm-push-4 displayoptions form-inline text-right hidden-xs">
            {block name='productlist-result-options-sort'}
            <div class="form-group">
                <select name="Sortierung" onchange="$('#improve_search').submit();" class="form-control form-small">
                    {if !isset($Suchergebnisse->Sortierung) || !$Suchergebnisse->Sortierung}
                        <option value="0">{lang key='sorting' section='productOverview'}</option>{/if}
                    <option value="100" {if isset($smarty.session.Usersortierung) && isset($Sort) && $smarty.session.Usersortierung==$Sort->value}selected="selected"{/if}>{lang key='standard' section='global'}</option>
                    {foreach name=sortierliste from=$Sortierliste item=Sort}
                        <option value="{$Sort->value}" {if $smarty.session.Usersortierung==$Sort->value}selected="selected"{/if}>{$Sort->angezeigterName}</option>
                    {/foreach}
                </select>
            </div>
            <div class="form-group">
                <select name="af" onchange="$('#improve_search').submit();" class="form-control form-small">
                    <option value="0"{if isset($smarty.session.ArtikelProSeite) && $smarty.session.ArtikelProSeite == 0} selected="selected"{/if}>{lang key="productsPerPage" section="productOverview"}</option>
                    <option value="9"{if isset($smarty.session.ArtikelProSeite) && $smarty.session.ArtikelProSeite == 9} selected="selected"{/if}>9 {lang key="productsPerPage" section="productOverview"}</option>
                    <option value="18"{if isset($smarty.session.ArtikelProSeite) && $smarty.session.ArtikelProSeite == 18} selected="selected"{/if}>18 {lang key="productsPerPage" section="productOverview"}</option>
                    <option value="30"{if isset($smarty.session.ArtikelProSeite) && $smarty.session.ArtikelProSeite == 30} selected="selected"{/if}>30 {lang key="productsPerPage" section="productOverview"}</option>
                    <option value="90"{if isset($smarty.session.ArtikelProSeite) && $smarty.session.ArtikelProSeite == 90} selected="selected"{/if}>90 {lang key="productsPerPage" section="productOverview"}</option>
                </select>
            </div>
            {if isset($oErweiterteDarstellung) && isset($Einstellungen.artikeluebersicht.artikeluebersicht_erw_darstellung) &&
            $Einstellungen.artikeluebersicht.artikeluebersicht_erw_darstellung === 'Y' && empty($AktuelleKategorie->categoryFunctionAttributes['darstellung'])}
                <div class="btn-group">
                    <a href="{$oErweiterteDarstellung->cURL_arr[1]}" id="ed_list" class="btn btn-default btn-option ed list {if $oErweiterteDarstellung->nDarstellung == 1}active{/if}" role="button" title="{lang key="list" section="productOverview"}"><span class="fa fa-th-list"></span></a>
                    <a href="{$oErweiterteDarstellung->cURL_arr[2]}" id="ed_gallery" class="btn btn-default btn-option ed gallery {if $oErweiterteDarstellung->nDarstellung == 2}active{/if}" role="button" title="{lang key="gallery" section="productOverview"}"><span class="fa fa-th-large"></span></a>
                </div>
            {/if}
            {/block}
        </div>
        {if $show_filters}
            <div class="col-sm-4 col-sm-pull-8 filter-collapsible-control">
                <a class="btn btn-default" data-toggle="collapse" href="#filter-collapsible" aria-expanded="true" aria-controls="filter-collapsible">
                    <span class="fa fa-filter"></span> {lang key='filterBy' section='global'}
                    <span class="caret"></span>
                </a>
            </div>
        {/if}
    </div>{* /row *}
    {if $show_filters}
        <div id="filter-collapsible" class="collapse in top10" aria-expanded="true">
            <nav class="panel panel-default">
                <div id="navbar-filter" class="panel-body">
                    <div class="form-inline2">
                        {foreach $NaviFilter->getAvailableFilters() as $filter}
                            {if ($filter->getVisibility() === $filter::SHOW_ALWAYS || $filter->getVisibility() === $filter::SHOW_CONTENT) && !$filter->isInitialized()}
                                {if count($filter->getFilterCollection()) > 0}
                                    {block name='productlist-result-options-'|cat:$filter->getClassName()}
                                        {foreach $filter->getOptions() as $subFilter}
                                            <div class="form-group dropdown filter-type-{$filter->getClassName()}">
                                                <a href="#" class="btn btn-default dropdown-toggle form-control" data-toggle="dropdown" role="button" aria-expanded="false">
                                                    {$subFilter->getFrontendName()|escape:'html'} <span class="caret"></span>
                                                </a>
                                                {include file='snippets/filter/genericFilterItem.tpl' class='dropdown-menu' filter=$subFilter}
                                            </div>
                                        {/foreach}
                                    {/block}
                                {else}
                                    {block name='productlist-result-options-'|cat:$filter->getClassName()}
                                        <div class="form-group dropdown filter-type-{$filter->getClassName()}">
                                            <a href="#" class="btn btn-default dropdown-toggle form-control" data-toggle="dropdown" role="button" aria-expanded="false">
                                                {$filter->getFrontendName()|escape:'html'} <span class="caret"></span>
                                            </a>
                                            {include file='snippets/filter/genericFilterItem.tpl' class='dropdown-menu' filter=$filter}
                                        </div>
                                    {/block}
                                {/if}
                            {/if}

                        {/foreach}

                    </div>{* /form-inline *}
                </div>
                {*/.navbar-collapse*}
            </nav>
        </div>
        {if $NaviFilter->getFilterCount() > 0}
            <div class="clearfix top10"></div>
            <div class="active-filters panel panel-default">
                <div class="panel-body">
                    {foreach $NaviFilter->getActiveFilters() as $activeFilter}
                        {*<pre>{$activeFilter|@var_dump}</pre>*}
                        {if $activeFilter->getValue() !== null}
                            {strip}
                                <a href="{$activeFilter->getUnsetFilterURL()}" rel="nofollow" title="Filter {lang key='delete' section='global'}" class="label label-info filter-type-{$filter->getClassName()}">
                                    {$activeFilter->getName()}
                                    &nbsp;<span class="fa fa-trash-o"></span>
                                </a>
                            {/strip}
                        {/if}
                    {/foreach}
                    {if !empty($NaviFilter->URL->cNoFilter)}
                        {strip}
                            <a href="{$NaviFilter->URL->cNoFilter}" title="{lang key="removeFilters" section='global'}" class="label label-warning">
                                {lang key='removeFilters' section='global'}
                            </a>
                        {/strip}
                    {/if}
                </div>
            </div>{* /active-filters *}
        {/if}
    {/if}
</div>
