{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-filter-rating'}
    {card class="box box-filter-reviews mb-7" id="sidebox{$oBox->getID()}" title="{lang key='Votes'}"}
        <hr class="mt-0 mb-4">
        {block name='boxes-box-filter-rating-content'}
            {include file='snippets/filter/genericFilterItem.tpl' filter=$oBox->getItems()}
        {/block}
    {/card}
{/block}
