{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-filter-search-special'}
    {assign var=ssf value=$NaviFilter->getSearchSpecialFilter()}
    {if $bBoxenFilterNach
        && $ssf->getVisibility() !== \JTL\Filter\Visibility::SHOW_NEVER
        && $ssf->getVisibility() !== \JTL\Filter\Visibility::SHOW_CONTENT
        && (!empty($Suchergebnisse->getSearchSpecialFilterOptions()) || $ssf->isInitialized())
        && (!$device->isMobile() || $device->isTablet())}
        {if $nSeitenTyp === $smarty.const.PAGE_ARTIKELLISTE}
            <div>
                {button
                variant="link"
                class="text-decoration-none pl-0 text-left"
                block=true
                role="button"
                data=["toggle"=> "collapse", "target"=>"#sidebox{$oBox->getID()}"]
                }
                    {$ssf->getFrontendName()}
                    <i class="fas fa-plus float-right"></i>{/button}
            </div>
            {collapse class="box box-filter-special" id="sidebox{$oBox->getID()}" visible=$ssf->isActive()}
                {block name='boxes-box-filter-search-special-content'}
                    {include file='snippets/filter/genericFilterItem.tpl' filter=$ssf}
                {/block}
            {/collapse}
            <hr class="my-2">
        {/if}
    {/if}
{/block}
