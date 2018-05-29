{assign var=prf value=$NaviFilter->getPriceRangeFilter()}
{if $bBoxenFilterNach
    && !$prf->getVisibility()->equals(\Filter\Visibility::SHOW_NEVER())
    && !$prf->getVisibility()->equals(\Filter\Visibility::SHOW_CONTENT())
    && (!empty($Suchergebnisse->getPriceRangeFilterOptions()) || $prf->isInitialized())}
    <section class="panel panel-default box box-filter-price" id="sidebox{$oBox->kBox}">
        <div class="panel-heading">
            <div class="panel-title">{lang key='rangeOfPrices'}</div>
        </div>
        <div class="box-body">
            {*{include file='snippets/filter/pricerange.tpl'}*}
            {include file='snippets/filter/genericFilterItem.tpl' filter=$NaviFilter->getPriceRangeFilter()}
        </div>
    </section>
{/if}
