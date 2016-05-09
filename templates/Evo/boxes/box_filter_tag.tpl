{if $bBoxenFilterNach}
    {nocache}
    {if !empty($NaviFilter->TagFilter)}
        <section class="panel panel-default box box-filter-tag" id="sidebox{$oBox->kBox}">
            <div class="panel-heading">
                <h5 class="panel-title">{lang key="tagFilter" section="global"}</h5></div>
            <div class="box-body">
                <ul class="filter_state nav nav-list">
                 {foreach name=tagfilter from=$NaviFilter->TagFilter item=oTagFilter}
                 {assign var=kTag value=$oTagFilter->kTag}
                    <li>
                       <a rel="nofollow" href="{$NaviFilter->URL->cAlleTags}" class="active">{$oTagFilter->cName}</a>
                    </li>
                 {/foreach}
                </ul>
            </div>
        </section>
    {/if}
    {/nocache}
{/if}