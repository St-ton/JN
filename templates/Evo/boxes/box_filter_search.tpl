{if $bBoxenFilterNach && $NaviFilter->searchFilterCompat->getOptions()|@count > 0 && empty($Suchergebnisse->getSearch()->kSuchanfrage)}
    <section class="panel panel-default box box-filter-price" id="sidebox{$oBox->kBox}">
        <div class="panel-heading">
            <h5 class="panel-title">{lang key='searchFilter'}</h5>
        </div>
        <div class="box-body">
            {include file='snippets/filter/search.tpl'}
            {*{include file='snippets/filter/genericFilterItem.tpl' filter=$NaviFilter->getSearchFilter()}*}
        </div>
    </section>
{/if}