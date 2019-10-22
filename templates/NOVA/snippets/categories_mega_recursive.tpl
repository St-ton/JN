{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='snippets-categories-mega-recursive'}
    {block name='snippets-categories-mega-recursive-main-link'}
        <a href="{$mainCategory->getURL()}" class="{if $firstChild === true}submenu-headline submenu-headline-toplevel{/if} nav-link {if $mainCategory->hasChildren()}dropdown-toggle{/if}" aria-expanded="false">
            {$mainCategory->getName()} <span class="badge text-gray-dark product-count">{$mainCategory->getProductCount()}</span>
        </a>
    {/block}
    {if $mainCategory->hasChildren()}
        {block name='snippets-categories-mega-recursive-child-content'}
            <div class="dropdown-menu">
                {block name='snippets-categories-mega-recursive-child-header'}
                    <div class="dropdown-header border-bottom border-primary border-w-5 d-lg-none">
                        <div class="row align-items-center font-size-base">
                            <div class="col">
                                <a href="#" class="font-size-base" data-nav-back="">
                                    <span class="fas fa-chevron-left mr-4"></span>{$mainCategory->getName()}
                                </a>
                            </div>
                            <div class="col-auto">
                                <a href="{$mainCategory->getURL()}">
                                    <span class="far fa-arrow-alt-circle-right ml-auto"></span>
                                </a>
                            </div>
                        </div>
                    </div>
                {/block}
                {block name='snippets-categories-mega-recursive-child-categories'}
                    <ul class="nav">
                        {foreach $mainCategory->getChildren() as $category}
                            {if $category->hasChildren()}
                                {block name='snippets-categories-mega-recursive-child-category-child'}
                                    <li class="nav-item dropdown">
                                        {include file='snippets/categories_mega_recursive.tpl' mainCategory=$category firstChild=false}
                                    </li>
                                {/block}
                            {else}
                                {block name='snippets-categories-mega-recursivechild-category-no-child'}
                                    <li class="nav-item">
                                        <a href="{$category->getURL()}" class="nav-link">
                                            {$category->getName()} <span class="badge text-gray-dark product-count">{$category->getProductCount()}</span>
                                        </a>
                                    </li>
                                {/block}
                            {/if}
                        {/foreach}
                    </ul>
                {/block}
            </div>
        {/block}
    {/if}
{/block}
