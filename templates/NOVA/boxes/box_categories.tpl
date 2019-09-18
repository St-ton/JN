{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-categories'}
    {card
        class="box box-categories word-break mb-4"
        id="sidebox_categories{$oBox->getCustomID()}"
    }
        {block name='boxes-box-categories-content'}
            {block name='boxes-box-categories-title'}
                <div class="productlist-filter-headline">
                    <span>{if !empty($oBox->getTitle())}{$oBox->getTitle()}{else}{lang key='categories'}{/if}</span>
                </div>
            {/block}
            <div class="nav-panel">
                {nav vertical=true}
                    {block name='boxes-box-categories-include-categories-recursive'}
                        {include file='snippets/categories_recursive.tpl' i=0 categoryId=0 categoryBoxNumber=$oBox->getCustomID() limit=3 categories=$oBox->getItems()}
                    {/block}
                {/nav}
            </div>
        {/block}
    {/card}
{/block}
