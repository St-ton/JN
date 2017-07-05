{if $bBoxenFilterNach && !empty($Suchergebnisse->Tags)}
    <section class="panel panel-default box box-filter-tag" id="sidebox{$oBox->kBox}">
        <div class="panel-heading">
            <h5 class="panel-title">{lang key="tagFilter" section="global"}</h5>
        </div>
        <div class="box-body">
            <ul class="nav nav-list">
             {foreach $Suchergebnisse->Tags as $oTag}
                 {if $NaviFilter->hasTagFilter() && $NaviFilter->getTagFilters(0)->getValue() === $oTag->kTag}
                     <li>
                         {* @todo: use getter *}
                         <a rel="nofollow" href="{$NaviFilter->tagFilterCompat->getUnsetFilterURL()}" class="active">
                             <i class="fa fa-check-square-o text-muted"></i>
                             <span class="value">
                                 {$oTag->getName()}
                                 <span class="badge pull-right">{$oTag->getCount()}</span>
                             </span>
                         </a>
                     </li>
                 {else}
                     <li>
                         <a rel="nofollow" href="{$oTag->getURL()}" class="active">
                             <span class="value">
                                 {$oTag->getName()}
                                 <span class="badge pull-right">{$oTag->getCount()}</span>
                             </span>
                         </a>
                     </li>
                 {/if}
             {/foreach}
            </ul>
        </div>
    </section>
{/if}