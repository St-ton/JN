{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-filter-pricerange'}
    {if $nSeitenTyp === $smarty.const.PAGE_ARTIKELLISTE}
        <div class="h4">
            {button
            variant="link"
            class="text-decoration-none"
            role="button"
            data=["toggle"=> "collapse", "target"=>"#sidebox{$oBox->getID()}"]
            }
            {lang key='rangeOfPrices'}
            +{/button}
        </div>
        {collapse class="box box-filter-price" id="sidebox{$oBox->getID()}"}
        {block name='boxes-box-filter-pricerange-content'}
            {include file='snippets/filter/genericFilterItem.tpl' filter=$oBox->getItems()}
        {/block}
        {/collapse}
        <hr class="mt-0 mb-4">
    {else}
        {card class="box box-filter-price mb-7" id="sidebox{$oBox->getID()}" title="{lang key='rangeOfPrices'}"}
            <hr class="mt-0 mb-4">
            {block name='boxes-box-filter-pricerange-content'}
                {include file='snippets/filter/genericFilterItem.tpl' filter=$oBox->getItems()}
            {/block}
        {/card}
    {/if}
{/block}
