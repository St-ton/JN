<br>FilterItemTag<br>
<br>OLD:<br>
<ul class="filter_tag nav nav-list">
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
<br>new:<br>
<ul class="filter_tag nav nav-list">
    {foreach $filter->getOptions() as $oTagFilter}
        <li>
            <a rel="nofollow" href="{$NaviFilter->URL->cAlleTags}" class="active">
                <span class="value">
                    <i class="fa fa-check-square-o text-muted"></i> {$oTagFilter->cName}
                </span>
            </a>
        </li>
    {/foreach}
</ul>