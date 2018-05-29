{assign var=bf value=$NaviFilter->getRatingFilter()}
{if $bBoxenFilterNach
    && !$bf->getVisibility()->equals(\Filter\Visibility::SHOW_NEVER())
    && !$bf->getVisibility()->equals(\Filter\Visibility::SHOW_CONTENT())
    && (!empty($Suchergebnisse->getRatingFilterOptions()) || $bf->isInitialized())}
    <section class="panel panel-default box box-filter-reviews" id="sidebox{$oBox->kBox}">
        <div class="panel-heading">
            <div class="panel-title">{lang key='Votes'}</div>
        </div>
        <div class="box-body">
            {include file='snippets/filter/genericFilterItem.tpl' filter=$NaviFilter->getRatingFilter()}
        </div>
    </section>
{/if}