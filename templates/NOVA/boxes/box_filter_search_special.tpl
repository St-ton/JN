{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{assign var=ssf value=$NaviFilter->getSearchSpecialFilter()}
{if $bBoxenFilterNach
    && $ssf->getVisibility() !== \JTL\Filter\Visibility::SHOW_NEVER
    && $ssf->getVisibility() !== \JTL\Filter\Visibility::SHOW_CONTENT
    && (!empty($Suchergebnisse->getSearchSpecialFilterOptions()) || $ssf->isInitialized())}
    {card class="box box-filter-special mb-7" id="sidebox{$oBox->getID()}" title="{$ssf->getFrontendName()}"}
        <hr class="mt-0 mb-4">
        {include file='snippets/filter/genericFilterItem.tpl' filter=$ssf}
    {/card}
{/if}
