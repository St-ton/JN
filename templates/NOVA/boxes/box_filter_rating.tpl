{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-filter-rating'}
    {if $nSeitenTyp === $smarty.const.PAGE_ARTIKELLISTE && (!$device->isMobile() || $device->isTablet())}
        <div>
            {button
            variant="link"
            class="text-decoration-none pl-0 text-left"
            block=true
            role="button"
            data=["toggle"=> "collapse", "target"=>"#sidebox{$oBox->getID()}"]
            }
                {lang key='Votes'} <i class="fas fa-plus float-right"></i>
            {/button}
        </div>
        {collapse class="box box-filter-rating" id="sidebox{$oBox->getID()}" visible=$oBox->getItems()->isActive()}
        {block name='boxes-box-filter-rating-content'}
            {include file='snippets/filter/genericFilterItem.tpl' filter=$oBox->getItems()}
        {/block}
        {/collapse}
        <hr class="my-2">
    {/if}
{/block}
