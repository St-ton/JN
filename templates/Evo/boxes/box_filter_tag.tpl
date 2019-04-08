{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{assign var=tf value=$NaviFilter->tagFilterCompat}
{$limit = $Einstellungen.template.productlist.filter_max_options}
{$collapseInit = false}
<section class="panel panel-default box box-filter-tag" id="sidebox{$oBox->getID()}">
    <div class="panel-heading">
        <div class="panel-title">{lang key='tagFilter'}</div>
    </div>
    <div class="box-body">
        <ul class="nav nav-list">
            {foreach $oBox->getItems() as $oTag}
                {if $limit != -1 && $oTag@iteration > $limit && !$collapseInit}
                    <div class="collapse" id="box-collps-tagfilter" aria-expanded="false"><ul class="nav nav-list">
                    {$collapseInit = true}
                {/if}
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
        {if $limit != -1 && $oBox->getItems()|count > $limit}
                </ul></div>
            <button class="btn btn-link pull-right"
                    role="button"
                    data-toggle="collapse"
                    data-target="#box-collps-tagfilter"
            >
                {lang key='showAll'} <span class="caret"></span>
            </button>
        {/if}
    </div>
</section>
