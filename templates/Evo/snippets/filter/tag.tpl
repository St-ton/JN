<ul class="filter_state nav nav-list">
    {foreach name=tagfilter from=$NaviFilter->TagFilter item=oTagFilter}
        {assign var=kTag value=$oTagFilter->kTag}
        <li>
            <a rel="nofollow" href="{$NaviFilter->URL->cAlleTags}" class="active">
                <span class="value">
                    <i class="fa fa-check-square-o text-muted"></i> {$oTagFilter->cName}
                </span>
            </a>
        </li>
    {/foreach}
</ul>