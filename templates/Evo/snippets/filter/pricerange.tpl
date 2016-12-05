<ul class="{if isset($class)}{$class}{else}nav nav-list{/if}">
    {if isset($NaviFilter->PreisspannenFilter) && $NaviFilter->PreisspannenFilter->cWert}
        {if $NaviFilter->PreisspannenFilter->fVon >= 0 && $NaviFilter->PreisspannenFilter->fBis > 0}
            <li>
                <span class="value">
                    <a href="{$NaviFilter->URL->cAllePreisspannen}" rel="nofollow" class="active">
                        <i class="fa fa-check-square-o text-muted"></i> {$NaviFilter->PreisspannenFilter->cVonLocalized} - {$NaviFilter->PreisspannenFilter->cBisLocalized}
                    </a>
                </span>
            </li>
        {/if}
    {else}
        {foreach name=preisspannen from=$Suchergebnisse->Preisspanne item=oPreisspannenfilter}
            <li>
                <a href="{$oPreisspannenfilter->cURL}" rel="nofollow">
                    <span class="value">
                        <i class="fa fa-square-o text-muted"></i> {$oPreisspannenfilter->cVonLocalized} - {$oPreisspannenfilter->cBisLocalized}
                        <span class="badge pull-right">{$oPreisspannenfilter->nAnzahlArtikel}</span>
                    </span>
                </a>
            </li>
        {/foreach}
    {/if}
</ul>