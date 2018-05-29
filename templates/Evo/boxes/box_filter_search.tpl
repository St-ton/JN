{assign var=sf value=$NaviFilter->searchFilterCompat}
{*{if $bBoxenFilterNach
    && !sf->getVisibility()->equals(\Filter\Visibility::SHOW_NEVER())
    && !sf->getVisibility()->equals(\Filter\Visibility::SHOW_CONTENT())
    && (!empty($Suchergebnisse->getSearchFilterOptions()) || $sf->isInitialized())}*}
{if $bBoxenFilterNach && $sf->getOptions()|@count > 0 && empty($NaviFilter->getSearch()->getValue())}
    <section class="panel panel-default box box-filter-search" id="sidebox{$oBox->kBox}">
    <div class="panel-heading">
            <div class="panel-title">{lang key='searchFilter'}</div>
        </div>
        <div class="box-body">
            {include file='snippets/filter/search.tpl'}
            {*{include file='snippets/filter/genericFilterItem.tpl' filter=$NaviFilter->getSearchFilter()}*}
        </div>
    </section>
{/if}