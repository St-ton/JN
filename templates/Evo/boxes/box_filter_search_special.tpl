{assign var=ssf value=$NaviFilter->getSearchSpecialFilter()}
{if $bBoxenFilterNach
    && !$ssf->getVisibility()->equals(\Filter\Visibility::SHOW_NEVER())
    && !$ssf->getVisibility()->equals(\Filter\Visibility::SHOW_CONTENT())
    && (!empty($Suchergebnisse->getSearchSpecialFilterOptions()) || $ssf->isInitialized())}
    <section class="panel panel-default box box-filter-special" id="sidebox{$oBox->kBox}">
        <div class="panel-heading">
            <div class="panel-title">{$ssf->getFrontendName()}</div>
        </div>
        <div class="box-body">
            {include file='snippets/filter/genericFilterItem.tpl' filter=$ssf}
        </div>
    </section>
{/if}