{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{assign var=tf value=$NaviFilter->tagFilterCompat}
{if $oBox->show()}
    <section class="panel panel-default box box-filter-tag" id="sidebox{$oBox->getID()}">
        <div class="panel-heading">
            <div class="panel-title">{lang key='tagFilter'}</div>
        </div>
        <div class="box-body">
            <ul class="nav nav-list">
             {foreach $oBox->getItems() as $oTag}
                 {if $NaviFilter->hasTagFilter() && $NaviFilter->getTagFilter(0)->getValue() === $oTag->kTag}
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
