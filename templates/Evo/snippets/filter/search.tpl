<ul class="filter_search nav nav-list">
    {foreach name=suchfilter from=$Suchergebnisse->SuchFilter item=oSuchFilter}
        <li>
            <a rel="nofollow" href="{$oSuchFilter->cURL}" class="active">
                <span class="value">
                    <i class="fa fa-square-o text-muted"></i> {$oSuchFilter->cSuche}
                    <span class="badge pull-right">{$oSuchFilter->nAnzahl}</span>
                </span>
            </a>
        </li>
    {/foreach}
</ul>