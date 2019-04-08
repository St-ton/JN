{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-filter-tag'}
    {assign var=tf value=$NaviFilter->tagFilterCompat}
    {card class="box box-filter-tag mb-7" id="sidebox{$oBox->getID()}" title="{lang key='tagFilter'}"}
        <hr class="mt-0 mb-4">
        {$limit = $Einstellungen.template.productlist.filter_max_options}
        {$collapseInit = false}
        {block name='boxes-box-filter-tag-content'}
            {nav vertical=true}
                {foreach $oBox->getItems() as $oTag}
                    {if $limit != -1 && $oTag@iteration > $limit && !$collapseInit}
                        <div class="collapse" id="box-collps-tagfilter" aria-expanded="false">
                            {$collapseInit = true}
                    {/if}
                    {if $NaviFilter->hasTagFilter() && $NaviFilter->getTagFilter(0)->getValue() === $oTag->kTag}
                        {block name='boxes-box-filter-tag-has-tag'}
                            {navitem}
                                {link rel="nofollow" href=$NaviFilter->tagFilterCompat->getUnsetFilterURL() class="active"}
                                    <i class="far fa-check-square text-muted"></i>
                                    <span class="value">
                                        {$oTag->getName()}
                                        <span class="badge badge-light float-right">{$oTag->getCount()}</span>
                                    </span>
                                {/link}
                            {/navitem}
                        {/block}
                    {else}
                        {block name='boxes-box-filter-tag-not-has-tag'}
                            {navitem}
                                {link rel="nofollow" href=$oTag->getURL() class="active"}
                                    <span class="value">
                                        {$oTag->getName()}
                                        <span class="badge badge-light float-right">{$oTag->getCount()}</span>
                                    </span>
                                {/link}
                            {/navitem}
                        {/block}
                    {/if}
                {/foreach}
                {if $limit != -1 && $oBox->getItems()|count > $limit}
                        </div>
                    {button
                        variant="link"
                        role="button"
                        class="text-right pr-0"
                        data=["toggle"=> "collapse", "target"=>"#box-collps-tagfilter"]
                    }
                        {lang key='showAll'} <i class="fas fa-chevron-down"></i>
                    {/button}
                {/if}
            {/nav}
        {/block}
    {/card}
{/block}
