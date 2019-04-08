{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-filter-category'}
    {card class="box box-filter-category mb-7" id="sidebox{$oBox->getID()}" title=$oBox->getTitle()}
        <hr class="mt-0 mb-4">
        {block name='boxes-box-filter-category-content'}
            {include file='snippets/filter/genericFilterItem.tpl' filter=$oBox->getItems()}
        {/block}
    {/card}
{/block}
