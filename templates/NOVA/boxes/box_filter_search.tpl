{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-filter-search'}
    {if $nSeitenTyp === $smarty.const.PAGE_ARTIKELLISTE && (!$device->isMobile() || $device->isTablet())}
        <div>
            {button
            variant="link"
            class="text-decoration-none pl-0 text-left"
            block=true
            role="button"
            data=["toggle"=> "collapse", "target"=>"#sidebox{$oBox->getID()}"]
            }
                {lang key='searchFilter'} <i class="fas fa-plus float-right"></i>
            {/button}
        </div>
        {collapse class="box box-search-category" id="sidebox{$oBox->getID()}" visible=$NaviFilter->searchFilterCompat->isActive()}
        {block name='boxes-box-filter-search-content'}
            {include file='snippets/filter/search.tpl'}
        {/block}
        {/collapse}
        <hr class="my-2">
    {/if}
{/block}
