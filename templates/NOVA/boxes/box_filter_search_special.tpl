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
        && !($device->isMobile() || $Einstellungen.template.productlist.filter_placement === 'modal')}
        {if $nSeitenTyp === $smarty.const.PAGE_ARTIKELLISTE}
            <div class="box box-filter-special d-none d-lg-block" id="sidebox{$oBox->getID()}">
                {button
                    variant="link"
                    class="text-decoration-none px-0 text-left dropdown-toggle"
                    block=true
                    role="button"
                    data=["toggle"=> "collapse", "target"=>"#cllps-box{$oBox->getID()}"]
                }
                    {$ssf->getFrontendName()}
                {/button}
                {collapse id="cllps-box{$oBox->getID()}" visible=$ssf->isActive()}
                    {block name='boxes-box-filter-search-special-content'}
                        {include file='snippets/filter/genericFilterItem.tpl' filter=$ssf}
                    {/block}
                {/collapse}
                {block name='boxes-box-filter-search-special-hr'}
                    <hr class="my-2">
                {/block}
            </div>
        {/if}
    {/if}
{/block}
