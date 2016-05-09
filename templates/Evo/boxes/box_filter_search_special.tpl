{if $bBoxenFilterNach && $Einstellungen.navigationsfilter.allgemein_suchspecialfilter_benutzen === 'Y'}
    {nocache}
    {if !empty($Suchergebnisse->Suchspecialauswahl) && $NaviFilter->SuchspecialFilter->kKey > 0}
        <section class="panel panel-default box box-filter-special" id="sidebox{$oBox->kBox}">
            <div class="panel-heading">
                <h5 class="panel-title">{lang key="specificProducts" section="global"}</h5>
            </div>
            <div class="panel-body">
                {include file='snippets/filter/special.tpl'}
            </div>
        </section>
    {/if}
    {/nocache}
{/if}