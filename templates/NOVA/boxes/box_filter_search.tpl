{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-filter-search'}
    {card class="box box-filter-search mb-7" id="sidebox{$oBox->getID()}" title="{lang key='searchFilter'}"}
        <hr class="mt-0 mb-4">
        {block name='boxes-box-filter-search-content'}
            {include file='snippets/filter/search.tpl'}
        {/block}
    {/card}
{/block}
