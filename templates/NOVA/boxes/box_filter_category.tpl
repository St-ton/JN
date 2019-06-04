{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-filter-category'}
    {if $nSeitenTyp === $smarty.const.PAGE_ARTIKELLISTE}
        <div class="h4">
            {button
            variant="link"
            class="text-decoration-none pl-0 text-left"
            block=true
            role="button"
            data=["toggle"=> "collapse", "target"=>"#sidebox{$oBox->getID()}"]
            }
                {$oBox->getTitle()} <i class="fas fa-plus float-right"></i>
            {/button}
        </div>
        {collapse class="box box-filter-category" id="sidebox{$oBox->getID()}" visible=$oBox->getItems()->isActive()}
        {block name='boxes-box-filter-category-content'}
            {include file='snippets/filter/genericFilterItem.tpl' filter=$oBox->getItems()}
        {/block}
        {/collapse}
        <hr class="mt-0 mb-4">
    {/if}
{/block}
