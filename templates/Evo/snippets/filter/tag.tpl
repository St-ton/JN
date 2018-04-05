<ul class="filter_tag nav nav-list">
    {foreach name=tagfilter from=$NaviFilter->getTagFilter() item=oTagFilter}
        <li>
            {* @todo: use getter *}
            <a rel="nofollow" href="{$NaviFilter->URL->getTags()}" class="active">
                <span class="value">
                    <i class="fa fa-check-square-o text-muted"></i> {$oTagFilter->getName()}
                </span>
            </a>
        </li>
    {/foreach}
</ul>