{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-filter-pricerange'}
    {card class="box box-filter-price mb-7" id="sidebox{$oBox->getID()}" title="{lang key='rangeOfPrices'}"}
        <hr class="mt-0 mb-4">
        {block name='boxes-box-filter-pricerange-content'}
            {include file='snippets/filter/genericFilterItem.tpl' filter=$oBox->getItems()}
        {/block}
    {/card}
{/block}
