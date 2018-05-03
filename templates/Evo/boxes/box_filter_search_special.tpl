{assign var=ssf value=$NaviFilter->getSearchSpecialFilter()}
{if $bBoxenFilterNach && $ssf->getVisibility()->equals(\Filter\FilterVisibility::SHOW_ALWAYS()) && (!empty($Suchergebnisse->getSearchSpecialFilterOptions()) || $ssf->isInitialized())}
    <section class="panel panel-default box box-filter-special" id="sidebox{$oBox->kBox}">
        <div class="panel-heading">
            <h5 class="panel-title">{$ssf->getFrontendName()}</h5>
        </div>
        <div class="box-body">
            {include file='snippets/filter/genericFilterItem.tpl' filter=$ssf}
        </div>
    </section>
{/if}