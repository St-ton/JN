{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-filter-search'}
    {if $nSeitenTyp === $smarty.const.PAGE_ARTIKELLISTE && (!$device->isMobile() || $device->isTablet())}
        <div class="box box-search-category" id="sidebox{$oBox->getID()}">
            {button
                variant="link"
                class="text-decoration-none pl-0 text-left"
                block=true
                role="button"
                data=["toggle"=> "collapse", "target"=>"#cllps-box{$oBox->getID()}"]
            }
                {lang key='searchFilter'} <i class="fas fa-plus float-right"></i>
            {/button}
            {collapse id="cllps-box{$oBox->getID()}" visible=$NaviFilter->searchFilterCompat->isActive()}
            {block name='boxes-box-filter-search-content'}
                {include file='snippets/filter/search.tpl'}
            {/block}
            {/collapse}
            <hr class="my-2">
        </div>
    {/if}
{/block}
