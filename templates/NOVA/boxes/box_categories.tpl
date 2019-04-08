{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-categories'}
    {card
        class="box box-categories word-break mb-7"
        id="sidebox_categories{$oBox->getCustomID()}"
        title="{if !empty($oBox->getTitle())}{$oBox->getTitle()}{else}{lang key='categories'}{/if}"
    }
        <hr class="mt-0 mb-4">
        {block name='boxes-box-categories-content'}
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
