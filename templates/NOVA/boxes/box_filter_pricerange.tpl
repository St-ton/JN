{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{card class="box box-filter-price mb-7" id="sidebox{$oBox->getID()}" title="{lang key='rangeOfPrices'}"}
    <hr class="mt-0 mb-4">
    {include file='snippets/filter/genericFilterItem.tpl' filter=$oBox->getItems()}
{/card}
