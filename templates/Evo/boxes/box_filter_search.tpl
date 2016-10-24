{if $bBoxenFilterNach && $Suchergebnisse->SuchFilter|@count > 0 && empty($Suchergebnisse->Suche->kSuchanfrage)}
    <section class="panel panel-default box box-filter-price" id="sidebox{$oBox->kBox}">
        <div class="panel-heading">
            <h5 class="panel-title">{lang key="searchFilter" section="global"}</h5>
        </div>
        <div class="box-body">
            {include file='snippets/filter/search.tpl'}
        </div>
    </section>
{/if}