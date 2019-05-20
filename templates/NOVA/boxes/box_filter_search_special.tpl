{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-filter-search-special'}
    {assign var=ssf value=$NaviFilter->getSearchSpecialFilter()}
    {if $bBoxenFilterNach
        && $ssf->getVisibility() !== \JTL\Filter\Visibility::SHOW_NEVER
        && $ssf->getVisibility() !== \JTL\Filter\Visibility::SHOW_CONTENT
        && (!empty($Suchergebnisse->getSearchSpecialFilterOptions()) || $ssf->isInitialized())}
        {if $nSeitenTyp === $smarty.const.PAGE_ARTIKELLISTE}
            <div class="h4">
                {button
                variant="link"
                class="text-decoration-none"
                role="button"
                data=["toggle"=> "collapse", "target"=>"#sidebox{$oBox->getID()}"]
                }
                {$ssf->getFrontendName()}
                +{/button}
            </div>
            {collapse class="box box-filter-special" id="sidebox{$oBox->getID()}" visible=$ssf->isActive()}
                {block name='boxes-box-filter-search-special-content'}
                    {include file='snippets/filter/genericFilterItem.tpl' filter=$ssf}
                {/block}
            {/collapse}
            <hr class="mt-0 mb-4">
        {else}
            {card class="box box-filter-special mb-7" id="sidebox{$oBox->getID()}" title=$ssf->getFrontendName()}
                <hr class="mt-0 mb-4">
                {block name='boxes-box-filter-search-special-content'}
                    {include file='snippets/filter/genericFilterItem.tpl' filter=$ssf}
                {/block}
            {/card}
        {/if}
    {/if}
{/block}
