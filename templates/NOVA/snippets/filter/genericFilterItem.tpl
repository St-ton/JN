{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='snippets-filter-genericFilterItem'}
    {if !isset($itemClass)}
        {assign var=itemClass value=''}
    {/if}
    
    {if !empty($displayAt) && $displayAt === 'content'}
        {block name='snippets-filter-genericFilterItem-content'}
            {foreach $filter->getOptions() as $filterOption}
                {assign var=filterIsActive value=$filterOption->isActive() || $NaviFilter->getFilterValue($filter->getClassName()) === $filterOption->getValue()}
                {dropdownitem class="filter-item py-1"
                    active=$filterIsActive
                    href="{if $filterOption->isActive()}{$filter->getUnsetFilterURL($filterOption->getValue())}{else}{$filterOption->getURL()}{/if}"
                    rel='nofollow'}
                    <span class="value">
                        {if $filter->getIcon() !== null}
                            <i class="fa {$filter->getIcon()}"></i>
                        {else}
                            <i class="far fa-{if $filterIsActive === true}check-{/if}square text-muted"></i>
                        {/if}
                        {if $filter->getNiceName() === 'Rating'}
                            {block name='snippets-filter-genericFilterItem-include-rating-content'}
                                {include file='productdetails/rating.tpl' stars=$filterOption->getValue()}
                            {/block}
                        {/if}
                        <span class="word-break">{$filterOption->getName()}</span><span class="ml-2">({$filterOption->getCount()})</span>
                    </span>
                {/dropdownitem}
            {/foreach}
        {/block}
    {else}
        {block name='snippets-filter-genericFilterItem-nav'}
            {$limit = $Einstellungen.template.productlist.filter_max_options}
            {$collapseInit = false}
            {nav vertical=true}
                {foreach $filter->getOptions() as $filterOption}
                    {if $limit != -1 && $filterOption@iteration > $limit && !$collapseInit}
                        <div class="collapse {if $filter->isActive()} show{/if}" id="box-collps-filter{$filter->getNiceName()}" aria-expanded="false">
                            {$collapseInit = true}
                    {/if}
                    {assign var=filterIsActive value=$filterOption->isActive() || $NaviFilter->getFilterValue($filter->getClassName()) === $filterOption->getValue()}
                    {navitem class="filter-item"
                        active=$filterIsActive
                        href="{if $filterOption->isActive()}{$filter->getUnsetFilterURL($filterOption->getValue())}{else}{$filterOption->getURL()}{/if}"
                        nofollow=true
                        router-class="px-0"}
                        <div class="align-items-center d-flex">
                            {if $filter->getIcon() !== null}
                                <i class="fa {$filter->getIcon()} mr-2"></i>
                            {else}
                                <i class="far fa-{if $filterIsActive === true}check-{/if}square text-muted mr-2"></i>
                            {/if}
                            {if $filter->getNiceName() === 'Rating'}
                                {block name='snippets-filter-genericFilterItem-include-rating-nav'}
                                    {include file='productdetails/rating.tpl' stars=$filterOption->getValue()}
                                {/block}
                            {/if}
                            <span class="word-break">{$filterOption->getName()}</span>
                            <span class="badge badge-outline-secondary ml-auto">{$filterOption->getCount()}</span>
                        </div>
                    {/navitem}
                {/foreach}
                {if $limit != -1 && $filter->getOptions()|count > $limit}
                    </div>
                    {button variant="link"
                        role="button"
                        class="text-right p-0"
                        data=["toggle"=> "collapse", "target"=>"#box-collps-filter{$filter->getNiceName()}"]
                        block=true}
                        {lang key='showAll'} <i class="fas fa-chevron-down"></i>
                    {/button}
                {/if}
            {/nav}
        {/block}
    {/if}
{/block}
