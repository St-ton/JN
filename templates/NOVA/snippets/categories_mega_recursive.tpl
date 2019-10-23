{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='snippets-categories-mega-recursive'}
    {block name='snippets-categories-mega-recursive-main-link'}
        <a href="{$mainCategory->getURL()}" class="{if $firstChild === true}submenu-headline submenu-headline-toplevel{/if} nav-link {if $mainCategory->hasChildren()}dropdown-toggle{/if}" aria-expanded="false">
            <span class="text-truncate">{$mainCategory->getName()}</span>
            <span class="badge text-gray-dark product-count">{$mainCategory->getProductCount()}</span>
        </a>
    {/block}
    {if $mainCategory->hasChildren()}
        {block name='snippets-categories-mega-recursive-child-content'}
            <div class="dropdown-menu">
                <ul class="nav">
                    {block name='snippets-categories-mega-recursive-child-header'}
                        {navitem class="dropdown d-lg-none"
                            href={$mainCategory->getURL()}}
                            <span class="text-truncate font-weight-bold d-block pr-3 pr-lg-0">
                                {lang key='menuShow' printf=$mainCategory->getName()}
                            </span>
                        {/navitem}
                    {/block}
                    {block name='snippets-categories-mega-recursive-child-categories'}
                        {foreach $mainCategory->getChildren() as $category}
                            {if $category->hasChildren()}
                                {block name='snippets-categories-mega-recursive-child-category-child'}
                                    <li class="nav-item dropdown">
                                        {include file='snippets/categories_mega_recursive.tpl' mainCategory=$category firstChild=false}
                                    </li>
                                {/block}
                            {else}
                                {block name='snippets-categories-mega-recursivechild-category-no-child'}
                                    {navitem href={$category->getURL()}}
                                            <span class="text-truncate">{$category->getName()}</span>
                                            <span class="badge text-gray-dark product-count">{$category->getProductCount()}</span>
                                    {/navitem}
                                {/block}
                            {/if}
                        {/foreach}
                    {/block}
                </ul>
            </div>
        {/block}
    {/if}
{/block}
