{if $bBoxenFilterNach && $BoxenEinstellungen.navigationsfilter.preisspannenfilter_benutzen === 'box' && $Suchergebnisse->Preisspanne|@count > 0}
    <section class="panel panel-default box box-filter-price" id="sidebox{$oBox->kBox}">
        <div class="panel-heading">
            <h5 class="panel-title">{lang key="rangeOfPrices" section="global"}</h5>
        </div>
        <div class="box-body">
            {*{include file='snippets/filter/pricerange.tpl'}*}
            {include file='snippets/filter/genericFilterItem.tpl' filter=$NaviFilter->getPriceRangeFilter()}
        </div>
    </section>
{/if}
