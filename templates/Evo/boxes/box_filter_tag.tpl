{if $bBoxenFilterNach && !empty($Suchergebnisse->Tags)}
    <section class="panel panel-default box box-filter-tag" id="sidebox{$oBox->kBox}">
        <div class="panel-heading">
            <h5 class="panel-title">{lang key="tagFilter" section="global"}</h5>
        </div>
        <div class="box-body">
            <ul class="nav nav-list">
             {foreach name=tagfilter from=$Suchergebnisse->Tags item=oTagFilter}
                <li>
                   <a rel="nofollow" href="{$oTagFilter->cURL}" class="XXXactive">
                       <i class="fa fa-square-o text-muted"></i> {$oTagFilter->cName}
                       <span class="badge">{$oTagFilter->nAnzahl}</span>
                   </a>
                </li>
             {/foreach}
            </ul>
        </div>
    </section>
{/if}