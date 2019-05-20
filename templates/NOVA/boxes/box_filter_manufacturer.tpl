{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-filter-manufacturer'}
    {if $nSeitenTyp === $smarty.const.PAGE_ARTIKELLISTE}
        <div class="h4">
            {button
            variant="link"
            class="text-decoration-none"
            role="button"
            data=["toggle"=> "collapse", "target"=>"#sidebox{$oBox->getID()}"]
            }{lang key='manufacturers'}+{/button}
        </div>
        {collapse class="box box-filter-manufacturer" id="sidebox{$oBox->getID()}" visible=$oBox->getItems()->isActive()}
        {block name='boxes-box-filter-search-special-content'}
            {include file='snippets/filter/genericFilterItem.tpl' filter=$oBox->getItems()}
        {/block}
        {/collapse}
        <hr class="mt-0 mb-4">
    {else}
        {card class="box box-filter-manufacturer mb-7" id="sidebox{$oBox->getID()}" title="{lang key='manufacturers'}"}
            <hr class="mt-0 mb-4">
            {block name='boxes-box-filter-manufacturer-content'}
                {include file='snippets/filter/genericFilterItem.tpl' filter=$oBox->getItems()}
            {/block}
        {/card}
    {/if}
{/block}
