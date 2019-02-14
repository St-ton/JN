{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if !isset($itemClass)}
    {assign var=itemClass value=''}
{/if}

{if !empty($displayAt) && $displayAt === 'content'}
    {foreach $filter->getOptions() as $filterOption}
        {assign var=filterIsActive value=$filterOption->isActive() || $NaviFilter->getFilterValue($filter->getClassName()) === $filterOption->getValue()}
        {dropdownitem class="filter-item"
            active=$filterIsActive
            href="{if $filterOption->isActive()}{$filter->getUnsetFilterURL($filterOption->getValue())}{else}{$filterOption->getURL()}{/if}"
            rel='nofollow'}
            <span class="badge badge-light float-right">{$filterOption->getCount()}</span>
            <span class="value mr-5">
                {if $filter->getIcon() !== null}
                    <i class="fa {$filter->getIcon()}"></i>
                {else}
                    <i class="far fa-{if $filterIsActive === true}check-{/if}square text-muted"></i>
                {/if}
                {if $filter->getNiceName() === 'Rating'}
                    {include file='productdetails/rating.tpl' stars=$filterOption->getValue()}
                {/if}
                <span class="word-break">{$filterOption->getName()}</span>
            </span>
        {/dropdownitem}
    {/foreach}
{else}
    {nav vertical=true}
        {foreach $filter->getOptions() as $filterOption}
            {assign var=filterIsActive value=$filterOption->isActive() || $NaviFilter->getFilterValue($filter->getClassName()) === $filterOption->getValue()}
            {navitem class="filter-item"
                active=$filterIsActive
                href="{if $filterOption->isActive()}{$filter->getUnsetFilterURL($filterOption->getValue())}{else}{$filterOption->getURL()}{/if}"
                nofollow=true
                router-class="px-0"}
                <span class="value">
                    {if $filter->getIcon() !== null}
                        <i class="fa {$filter->getIcon()}"></i>
                    {else}
                        <i class="far fa-{if $filterIsActive === true}check-{/if}square text-muted"></i>
                    {/if}
                    {if $filter->getNiceName() === 'Rating'}
                        {include file='productdetails/rating.tpl' stars=$filterOption->getValue()}
                    {/if}
                    <span class="word-break">{$filterOption->getName()}</span>
                </span>
                <span class="badge badge-light float-right">{$filterOption->getCount()}</span>
            {/navitem}
        {/foreach}
    {/nav}
{/if}
