{if $bBoxenFilterNach && $BoxenEinstellungen.navigationsfilter.bewertungsfilter_benutzen === 'box' && $Suchergebnisse->getRatingFilterOptions()|@count > 0}
    <section class="panel panel-default box box-filter-reviews" id="sidebox{$oBox->kBox}">
        <div class="panel-heading">
            <h5 class="panel-title">{lang key='Votes' section='global'}</h5>
        </div>
        <div class="box-body">
            {include file='snippets/filter/genericFilterItem.tpl' filter=$NaviFilter->getRatingFilter()}
        </div>
    </section>
{/if}