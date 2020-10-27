{block name='snippets-filter-genericFilterItem'}
    {block name='snippets-filter-genericFilterItem-nav'}
        {$limit = $Einstellungen.template.productlist.filter_max_options}
        {$collapseInit = false}
            {foreach $filter->getOptions() as $filterOption}
                {if $limit != -1 && $filterOption@iteration > $limit && !$collapseInit}
                    {block name='snippets-filter-genericFilterItem-more-top'}
                        <div class="collapse {if $filter->isActive()} show{/if}" id="box-collps-filter{$filter->getNiceName()}" aria-expanded="false" role="button">
                            {$collapseInit = true}
                    {/block}
                {/if}
                {assign var=filterIsActive value=$filterOption->isActive() || $NaviFilter->getFilterValue($filter->getClassName()) === $filterOption->getValue()}
                {block name='snippets-filter-genericFilterItem-nav-main'}
                    {link class="filter-item {if $filterIsActive === true}active{/if}"
                        href="{if $filterOption->isActive()}{$filter->getUnsetFilterURL($filterOption->getValue())}{else}{$filterOption->getURL()}{/if}"
                        nofollow=true}
                        <div class="align-items-center d-flex">
                            {if $filter->getIcon() !== null}
                                <i class="fa {$filter->getIcon()} mr-2"></i>
                            {else}
                                <i class="far fa-{if $filterIsActive === true}check-{/if}square text-muted-util mr-2"></i>
                            {/if}
                            {if $filter->getNiceName() === 'Rating'}
                                {block name='snippets-filter-genericFilterItem-include-rating-nav'}
                                    <span class="mr-2">{include file='productdetails/rating.tpl' stars=$filterOption->getValue()}</span>
                                {/block}
                            {/if}
                            <span class="word-break">{$filterOption->getName()}</span>
                            {badge variant="outline-secondary" class="ml-auto"}{$filterOption->getCount()}{/badge}
                        </div>
                    {/link}
                {/block}
            {/foreach}
            {if $limit != -1 && $filter->getOptions()|count > $limit}
                {block name='snippets-filter-genericFilterItem-more-bottom'}
                    </div>
                    <div class="w-100">
                        {button variant="link"
                            role="button"
                            class="p-0 ml-auto mt-1"
                            data=["toggle"=> "collapse", "target"=>"#box-collps-filter{$filter->getNiceName()}"]}
                            {lang key='showAll'}
                        {/button}
                    </div>
                {/block}
            {/if}
    {/block}
{/block}
