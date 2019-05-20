{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-filter-search'}
    {if $nSeitenTyp === $smarty.const.PAGE_ARTIKELLISTE}
        <div class="h4">
            {button
            variant="link"
            class="text-decoration-none"
            role="button"
            data=["toggle"=> "collapse", "target"=>"#sidebox{$oBox->getID()}"]
            }{lang key='searchFilter'} +{/button}
        </div>
        {collapse class="box box-search-category" id="sidebox{$oBox->getID()}" visible=$NaviFilter->searchFilterCompat->isActive()}
        {block name='boxes-box-filter-search-content'}
            {include file='snippets/filter/search.tpl'}
        {/block}
        {/collapse}
        <hr class="mt-0 mb-4">
    {else}
        {card class="box box-filter-search mb-7" id="sidebox{$oBox->getID()}" title="{lang key='searchFilter'}"}
            <hr class="mt-0 mb-4">
            {block name='boxes-box-filter-search-content'}
                {include file='snippets/filter/search.tpl'}
            {/block}
        {/card}
    {/if}
{/block}
