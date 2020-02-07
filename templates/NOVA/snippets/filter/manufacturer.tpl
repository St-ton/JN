{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='snippets-filter-manufacturer'}
    {$limit = $Einstellungen.template.productlist.filter_max_options}
    {$collapseInit = false}
    {if $Einstellungen.navigationsfilter.hersteller_anzeigen_als === 'B'}
        <ul class="nav">
    {/if}
    {foreach $filter->getOptions() as $filterOption}
        {assign var=filterIsActive value=$filterOption->isActive() || $NaviFilter->getFilterValue($filter->getClassName()) === $filterOption->getValue()}
        {if $limit != -1 && $filterOption@iteration > $limit && !$collapseInit}
            {block name='snippets-filter-manufacturer-more-top'}
                <div class="collapse {if $filter->isActive()} show{/if}" id="box-collps-filter{$filter->getNiceName()}" aria-expanded="false">
                    <ul class="nav {if $Einstellungen.navigationsfilter.hersteller_anzeigen_als !== 'B'}flex-column{/if}">
                {$collapseInit = true}
            {/block}
        {/if}
        {block name='snippets-filter-manufacturer-item'}
            {if $Einstellungen.navigationsfilter.hersteller_anzeigen_als == 'B'}
                {$tooltip = ["toggle"=>"tooltip", "placement"=>"top", "boundary"=>"window"]}
            {else}
                {$tooltip = []}
            {/if}
            {link href="{if !empty($filterOption->getURL())}{$filterOption->getURL()}{else}#{/if}"
                title="{$filterOption->getName()}: {$filterOption->getCount()}"
                data=$tooltip
                class="filter-item {if $filterOption->isActive()}active{/if}"
            }
                <div class="align-items-center d-flex">
                    {if $Einstellungen.navigationsfilter.hersteller_anzeigen_als == 'B'}
                        {block name='snippets-filter-manufacturer-item-image'}
                            {image lazy=true webo=true
                                src=$filterOption->getData('cBildpfadKlein')
                                class="vmiddle filter-img"
                            }
                        {/block}
                    {elseif $Einstellungen.navigationsfilter.hersteller_anzeigen_als === 'BT'}
                        {block name='snippets-filter-manufacturer-item-image-text'}
                            {image lazy=true webp=true
                                src=$filterOption->getData('cBildpfadKlein')
                                class="vmiddle filter-img"
                            }
                            <span class="word-break">{$filterOption->getName()}</span>
                            <span class="badge badge-outline-secondary ml-auto">{$filterOption->getCount()}</span>
                        {/block}
                    {elseif $Einstellungen.navigationsfilter.hersteller_anzeigen_als === 'T'}
                        {block name='snippets-filter-manufacturer-item-text'}
                            <i class="far fa-{if $filterIsActive === true}check-{/if}square text-muted mr-2"></i>
                            <span class="word-break">{$filterOption->getName()}</span>
                            <span class="badge badge-outline-secondary ml-auto">{$filterOption->getCount()}</span>
                        {/block}
                    {/if}
                </div>
            {/link}
        {/block}
    {/foreach}
    {if $limit != -1 && $filter->getOptions()|count > $limit}
        {block name='snippets-filter-manufacturer-more-bottom'}
                </ul>
            </div>
            <div class="w-100">
                {button
                    variant="link"
                    role="button"
                    class="p-0 ml-auto mt-1"
                    data=["toggle"=> "collapse", "target"=>"#box-collps-filter{$filter->getNiceName()}"]}
                    {lang key='showAll'}
                {/button}
            </div>
        {/block}
    {/if}
    {if $Einstellungen.navigationsfilter.hersteller_anzeigen_als === 'B'}
        </ul>
    {/if}
{/block}
