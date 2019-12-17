{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-filter-search'}
    {if $nSeitenTyp === $smarty.const.PAGE_ARTIKELLISTE
        && !($device->isMobile() || $device->isTablet() || $Einstellungen.template.productlist.filter_placement === 'M')}
        <div class="box box-search-category" id="sidebox{$oBox->getID()}">
            {button
                variant="link"
                class="text-decoration-none px-0 text-left dropdown-toggle"
                block=true
                role="button"
                data=["toggle"=> "collapse", "target"=>"#cllps-box{$oBox->getID()}"]
            }
                {lang key='searchFilter'}
            {/button}
            {collapse id="cllps-box{$oBox->getID()}" visible=$NaviFilter->searchFilterCompat->isActive()}
            {block name='boxes-box-filter-search-content'}
                {include file='snippets/filter/search.tpl'}
            {/block}
            {/collapse}
            {block name='boxes-box-filter-search-hr'}
                <hr class="my-2">
            {/block}
        </div>
    {/if}
{/block}
