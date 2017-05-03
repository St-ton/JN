{assign var=ssf value=$NaviFilter->SuchspecialFilter}
{if $bBoxenFilterNach && $ssf->getVisibility() === $ssf::SHOW_ALWAYS}
    {if !empty($Suchergebnisse->Suchspecialauswahl) || $ssf->isInitialized()}
        <section class="panel panel-default box box-filter-special" id="sidebox{$oBox->kBox}">
            <div class="panel-heading">
                <h5 class="panel-title">{$ssf->getFrontendName()}</h5>
            </div>
            <div class="box-body">
                {*{include file='snippets/filter/special.tpl'}*}
                {include file='snippets/filter/genericFilterItem.tpl' filter=$ssf}
            </div>
        </section>
    {/if}
{/if}