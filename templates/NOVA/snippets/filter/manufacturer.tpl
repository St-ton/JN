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
                <span class="value">
                    {link href="{if !empty($filterOption->getURL())}{$filterOption->getURL()}{else}#{/if}"
                        title="{$filterOption->getName()}: {$filterOption->getCount()}"
                        data=["toggle"=>"tooltip", "placement"=>"top", "boundary"=>"window"]
                        class="{if $filterOption->isActive()}active{/if}"
                    }
                        {if $Einstellungen.navigationsfilter.hersteller_anzeigen_als == 'B'}
                                {image src=$filterOption->getData('cBildpfadKlein') class="vmiddle filter-img"}
                        {elseif $Einstellungen.navigationsfilter.hersteller_anzeigen_als === 'BT'}
                             {image src=$filterOption->getData('cBildpfadKlein') class="vmiddle filter-img"}
                            &nbsp;{$filterOption->getName()} ({$filterOption->getCount()})
                        {elseif $Einstellungen.navigationsfilter.hersteller_anzeigen_als === 'T'}
                            <i class="far fa-{if $filterIsActive === true}check-{/if}square text-muted"></i>
                            &nbsp;{$filterOption->getName()}&nbsp;({$filterOption->getCount()})
                        {/if}
                    {/link}
                </span>
            {/block}
        {/foreach}
        {if $limit != -1 && $filter->getOptions()|count > $limit}
                </ul>
            </div>
            {button
                variant="link"
                role="button"
                class="text-right pr-0"
                data=["toggle"=> "collapse", "target"=>"#box-collps-filter{$filter->getNiceName()}"]
            }
            {lang key='showAll'} <i class="fas fa-chevron-down"></i>
            {/button}
        {/if}
    {/nav}
{/block}
