{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{card class="box box-filter-reviews mb-7" id="sidebox{$oBox->getID()}" title="{lang key='Votes'}"}
    <hr class="mt-0 mb-4">
    {include file='snippets/filter/genericFilterItem.tpl' filter=$oBox->getItems()}
{/card}
