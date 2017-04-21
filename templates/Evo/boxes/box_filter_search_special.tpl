{assign var=ssf value=$NaviFilter->SuchspecialFilter}
{if $bBoxenFilterNach && $ssf->getVisibility() === $ssf::SHOW_ALWAYS}
    {if !empty($Suchergebnisse->Suchspecialauswahl) || $ssf->isInitialized()}
        <section class="panel panel-default box box-filter-special" id="sidebox{$oBox->kBox}">
            <div class="panel-heading">
                <h5 class="panel-title">{$ssf->getFrontendName()}</h5>
            </div>
            <div class="box-body">
                {include file='snippets/filter/special.tpl'}
                {if $ssf->isInitialized()}
                    <ul class="{if isset($class)}{$class}{else}nav nav-list{/if}">
                        <li>
                            <a href="{$ssf->getUnsetFilterURL()}" rel="nofollow">
                                <span class="value">
                                    <i class="fa fa-check-square-o text-muted"></i> {$ssf->getName()}
                                </span>
                            </a>
                        </li>
                    </ul>
                {/if}
            </div>
        </section>
    {/if}
{/if}