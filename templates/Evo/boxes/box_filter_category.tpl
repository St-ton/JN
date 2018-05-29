{assign var=catf value=$NaviFilter->getCategoryFilter()}
{if $bBoxenFilterNach
    && !$catf->getVisibility()->equals(\Filter\Visibility::SHOW_NEVER())
    && !$catf->getVisibility()->equals(\Filter\Visibility::SHOW_CONTENT())
    && (!empty($Suchergebnisse->getCategoryFilterOptions()) || $catf->isInitialized())}
    <section class="panel panel-default box box-filter-category" id="sidebox{$oBox->kBox}">
        <div class="panel-heading">
            <div class="panel-title">{$catf->getFrontendName()}</div>
        </div>
        <div class="box-body">
            {include file='snippets/filter/genericFilterItem.tpl' filter=$catf}
        </div>
    </section>
{/if}