<ul class="filter_state nav nav-list">
    {foreach name=suchfilter from=$NaviFilter->SuchFilter item=oSuchFilter}
        {assign var=kSuchanfrage value=$oSuchFilter->kSuchanfrage}
        <li>
            <a rel="nofollow" href="{$NaviFilter->URL->cAlleSuchFilter[$kSuchanfrage]}" class="active">
                <i class="fa fa-check-square-o text-muted"></i> {$oSuchFilter->cSuche}
            </a>
        </li>
    {/foreach}
</ul>