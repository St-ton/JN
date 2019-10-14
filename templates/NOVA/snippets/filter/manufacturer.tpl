{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='snippets-filter-manufacturer'}
    {$limit = $Einstellungen.template.productlist.filter_max_options}
    {$collapseInit = false}
    {nav vertical=$Einstellungen.navigationsfilter.hersteller_anzeigen_als !== 'B'}
        {foreach $filter->getOptions() as $filterOption}
            {assign var=filterIsActive value=$filterOption->isActive() || $NaviFilter->getFilterValue($filter->getClassName()) === $filterOption->getValue()}
            {if $limit != -1 && $filterOption@iteration > $limit && !$collapseInit}
                <div class="collapse {if $filter->isActive()} show{/if}" id="box-collps-filter{$filter->getNiceName()}" aria-expanded="false">
                    <ul class="nav {if $Einstellungen.navigationsfilter.hersteller_anzeigen_als !== 'B'}flex-column{/if}">
                {$collapseInit = true}
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
                    class="nav-link px-0{if $filterOption->isActive()}active{/if}"
                }
                    <div class="align-items-center d-flex">
                        {if $Einstellungen.navigationsfilter.hersteller_anzeigen_als == 'B'}
                            {image src=$filterOption->getData('cBildpfadKlein') class="vmiddle filter-img"}
                        {elseif $Einstellungen.navigationsfilter.hersteller_anzeigen_als === 'BT'}
                            {image src=$filterOption->getData('cBildpfadKlein') class="vmiddle filter-img"}
                            <span class="word-break">{$filterOption->getName()}</span>
                            <span class="badge badge-outline-secondary ml-auto">{$filterOption->getCount()}</span>
                        {elseif $Einstellungen.navigationsfilter.hersteller_anzeigen_als === 'T'}
                            <i class="far fa-{if $filterIsActive === true}check-{/if}square text-muted mr-2"></i>
                            <span class="word-break">{$filterOption->getName()}</span>
                            <span class="badge badge-outline-secondary ml-auto">{$filterOption->getCount()}</span>
                        {/if}
                    </div>
                {/link}
            {/block}
        {/foreach}
        {if $limit != -1 && $filter->getOptions()|count > $limit}
                </ul>
            </div>
            {button
                variant="link"
                role="button"
                class="text-right p-0 d-block"
                data=["toggle"=> "collapse", "target"=>"#box-collps-filter{$filter->getNiceName()}"]
                block=true}
                {lang key='showAll'}
            {/button}
        {/if}
    {/nav}
{/block}
