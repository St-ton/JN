{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{assign var=tf value=$NaviFilter->tagFilterCompat}
{card class="box box-filter-tag mb-7" id="sidebox{$oBox->getID()}" title="{lang key='tagFilter'}"}
    <hr class="mt-0 mb-4">
    {nav vertical=true}
        {foreach $oBox->getItems() as $oTag}
            {if $NaviFilter->hasTagFilter() && $NaviFilter->getTagFilter(0)->getValue() === $oTag->kTag}
                {navitem}
                    {link rel="nofollow" href="{$NaviFilter->tagFilterCompat->getUnsetFilterURL()}" class="active"}
                        <i class="far fa-check-square text-muted"></i>
                        <span class="value">
                            {$oTag->getName()}
                            <span class="badge badge-light float-right">{$oTag->getCount()}</span>
                        </span>
                    {/link}
                {/navitem}
            {else}
                {navitem}
                    {link rel="nofollow" href="{$oTag->getURL()}" class="active"}
                        <span class="value">
                            {$oTag->getName()}
                            <span class="badge badge-light float-right">{$oTag->getCount()}</span>
                        </span>
                    {/link}
                {/navitem}
            {/if}
        {/foreach}
    {/nav}
{/card}
