{block name='snippets-filter-manufacturer'}
    {$limit = $Einstellungen.template.productlist.filter_max_options}
    {$collapseInit = false}
    <div class="filter-search-wrapper">
    {if (int)$Einstellungen.template.productlist.filter_search_count < count($filter->getOptions())}
        {inputgroup size="sm"}
        {inputgroupaddon prepend=true is-text=true}
            <span class="fa fa-search"></span>
        {/inputgroupaddon}
        {input class="filter-search" placeholder={lang key='filterSearchPlaceholder' section='productOverview' printf=$filter->getFrontendName()}}
            <span class="form-clear d-none"><i class="fas fa-times"></i></span>
        {/inputgroup}
    {/if}
    {if $Einstellungen.navigationsfilter.hersteller_anzeigen_als === 'B'}
        <ul class="nav nav-filter-has-image">
    {/if}
    {foreach $filter->getOptions() as $filterOption}
        {assign var=filterIsActive value=$filterOption->isActive() || $NaviFilter->getFilterValue($filter->getClassName()) === $filterOption->getValue()}
        {if $limit != -1 && $filterOption@iteration > $limit && !$collapseInit}
            {block name='snippets-filter-manufacturer-more-top'}
                <div class="collapse {if $filter->isActive()} show{/if}" id="box-collps-filter{$filter->getNiceName()}" aria-expanded="false" role="button">
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
                <div class="box-link-wrapper">
                    {if $Einstellungen.navigationsfilter.hersteller_anzeigen_als == 'B'}
                        {block name='snippets-filter-manufacturer-item-image'}
                            {image lazy=true webo=true
                                src=$filterOption->getData('cBildpfadKlein')
                                class="vmiddle filter-img"
                            }
                            <span class="d-none filter-item-value">{$filterOption->getName()}</span>
                        {/block}
                    {elseif $Einstellungen.navigationsfilter.hersteller_anzeigen_als === 'BT'}
                        {block name='snippets-filter-manufacturer-item-image-text'}
                            {image lazy=true webp=true
                                src=$filterOption->getData('cBildpfadKlein')
                                class="vmiddle filter-img"
                            }
                            <span class="word-break filter-item-value">{$filterOption->getName()}</span>
                            {badge variant="outline-secondary"}{$filterOption->getCount()}{/badge}
                        {/block}
                    {elseif $Einstellungen.navigationsfilter.hersteller_anzeigen_als === 'T'}
                        {block name='snippets-filter-manufacturer-item-text'}
                            <i class="far fa-{if $filterIsActive === true}check-{/if}square snippets-filter-item-icon-right"></i>
                            <span class="word-break filter-item-value">{$filterOption->getName()}</span>
                            {badge variant="outline-secondary"}{$filterOption->getCount()}{/badge}
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
            <div class="snippets-filter-show-all">
                {button
                    variant="link"
                    role="button"
                    data=["toggle"=> "collapse", "target"=>"#box-collps-filter{$filter->getNiceName()}"]}
                    {lang key='showAll'}
                {/button}
            </div>
        {/block}
    {/if}
    {if $Einstellungen.navigationsfilter.hersteller_anzeigen_als === 'B'}
        </ul>
    {/if}
    </div>
{/block}
