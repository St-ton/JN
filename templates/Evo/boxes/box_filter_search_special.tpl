{if $bBoxenFilterNach && $Einstellungen.navigationsfilter.allgemein_suchspecialfilter_benutzen === 'Y' && !empty($Suchergebnisse->Suchspecialauswahl)}
    {assign var=totalSearchSpecialCount value=
        $Suchergebnisse->Suchspecialauswahl[1]->nAnzahl+
        $Suchergebnisse->Suchspecialauswahl[2]->nAnzahl+
        $Suchergebnisse->Suchspecialauswahl[3]->nAnzahl+
        $Suchergebnisse->Suchspecialauswahl[4]->nAnzahl+
        $Suchergebnisse->Suchspecialauswahl[5]->nAnzahl+
        $Suchergebnisse->Suchspecialauswahl[6]->nAnzahl}
    {if $totalSearchSpecialCount > 0}
        <section class="panel panel-default box box-filter-special" id="sidebox{$oBox->kBox}">
            <div class="panel-heading">
                <h5 class="panel-title">{lang key="specificProducts" section="global"}</h5>
            </div>
            <div class="panel-body">
                {include file='snippets/filter/special.tpl'}
            </div>
        </section>
    {/if}
{/if}