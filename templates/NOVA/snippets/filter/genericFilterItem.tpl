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
                {dropdownitem class="filter-item py-1 px-0"
                    active=$filterIsActive
                    href="{$filterOption->getURL()}"
                    rel='nofollow'}
                    <div class="align-items-center d-flex">
                        {if $filter->getIcon() !== null}
                            <i class="fa {$filter->getIcon()} mr-2"></i>
                        {else}
                            <i class="far fa-{if $filterIsActive === true}check-{/if}square text-muted mr-2"></i>
                        {/if}
                        {if $filter->getNiceName() === 'Rating'}
                            {block name='snippets-filter-genericFilterItem-include-rating-content'}
                                <span class="mr-2">{include file='productdetails/rating.tpl' stars=$filterOption->getValue()}</span>
                            {/block}
                        {/if}
                        <span class="word-break">{$filterOption->getName()}</span>
                        <span class="badge badge-outline-secondary ml-auto">{$filterOption->getCount()}</span>
                    </div>
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
                        {block name='snippets-filter-genericFilterItem-more-top'}
                            <div class="collapse {if $filter->isActive()} show{/if}" id="box-collps-filter{$filter->getNiceName()}" aria-expanded="false">
                                {$collapseInit = true}
                        {/block}
                    {/if}
                    {assign var=filterIsActive value=$filterOption->isActive() || $NaviFilter->getFilterValue($filter->getClassName()) === $filterOption->getValue()}
                    {block name='snippets-filter-genericFilterItem-nav-main'}
                        {navitem class="filter-item"
                            active=$filterIsActive
                            href="{$filterOption->getURL()}"
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
                                        <span class="mr-2">{include file='productdetails/rating.tpl' stars=$filterOption->getValue()}</span>
                                    {/block}
                                {/if}
                                <span class="word-break">{$filterOption->getName()}</span>
                                <span class="badge badge-outline-secondary ml-auto">{$filterOption->getCount()}</span>
                            </div>
                        {/navitem}
                    {/block}
                {/foreach}
                {if $limit != -1 && $filter->getOptions()|count > $limit}
                    {block name='snippets-filter-genericFilterItem-more-bottom'}
                        </div>
                        {button variant="link"
                            role="button"
                            class="text-right p-0 d-block"
                            data=["toggle"=> "collapse", "target"=>"#box-collps-filter{$filter->getNiceName()}"]
                            block=true}
                            {lang key='showAll'}
                        {/button}
                    {/block}
                {/if}
            {/nav}
        {/block}
    {/if}
{/block}
