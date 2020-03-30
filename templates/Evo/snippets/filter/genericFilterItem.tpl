{if !isset($itemClass)}
    {assign var=itemClass value=''}
{/if}
{if !isset($class)}
    {$class = ''}
{/if}
{$limit = $Einstellungen.template.productlist.filter_max_options}
{$collapseInit = false}

<ul class="{if !empty($class)}{$class}{else}nav nav-list{/if}">
    {foreach $filter->getOptions() as $filterOption}
        {assign var=filterIsActive value=$filterOption->isActive() || $NaviFilter->getFilterValue($filter->getClassName()) === $filterOption->getValue()}
        {if $limit != -1 && $filterOption@iteration > $limit && !$collapseInit && $class!='dropdown-menu'}
            <div class="collapse {if $filter->isActive()} in{/if}" id="box-collps-filter{$filter->getNiceName()}" aria-expanded="false"><ul class="nav nav-list">
            {$collapseInit = true}
        {/if}
        <li class="filter-item{if $filterIsActive === true} active{/if}">
            <a rel="nofollow"
               href="{if $filterOption->isActive()}{$filter->getUnsetFilterURL($filterOption->getValue())}{else}{$filterOption->getURL()}{/if}"
               class="{$itemClass}{if $filterOption->isActive()} active{/if}">
                <span class="badge pull-right">{$filterOption->getCount()}</span>
                <span class="value">
                    {if $filter->getIcon() !== null}
                        <i class="fa {$filter->getIcon()}"></i>
                    {else}
                        <i class="fa fa-{if $filterIsActive === true}check-{/if}square-o text-muted"></i>
                    {/if}
                    {if $filter->getNiceName() === 'Rating'}
                        {include file='productdetails/rating.tpl' stars=$filterOption->getValue()}
                    {/if}
                    <span class="word-break">
                        {if $filter->getNiceName() === 'Manufacturer'}
                            {if $Einstellungen.navigationsfilter.hersteller_anzeigen_als !== 'T'}
                                <img src="{$filterOption->getData('cBildpfadKlein')}" alt="" class="vmiddle filter-img" />
                            {/if}
                            {if $Einstellungen.navigationsfilter.hersteller_anzeigen_als !== 'B'}
                                &nbsp;{$filterOption->getName()}
                            {/if}
                        {else}
                            {$filterOption->getName()}
                        {/if}
                    </span>
                </span>
            </a>
        </li>
    {/foreach}
    {if $limit != -1 && $filter->getOptions()|count > $limit && $class!='dropdown-menu'}
    </ul></div>
        <button class="btn btn-link pull-right"
                role="button"
                data-toggle="collapse"
                data-target="#box-collps-filter{$filter->getNiceName()}"
        >
            {lang key='showAll'} <span class="caret"></span>
        </button>
    {/if}
</ul>
