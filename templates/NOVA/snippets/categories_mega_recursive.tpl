{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='snippets-categories-mega-recursive'}
    {block name='snippets-categories-mega-recursive-main-link'}
        {link href=$mainCategory->getURL() class="d-lg-block {if $firstChild}submenu-headline submenu-headline-toplevel{/if} nav-link {if $mainCategory->hasChildren()}dropdown-toggle{/if}" aria=["expanded"=>"false"]}
            {if $firstChild
                && $Einstellungen.template.megamenu.show_category_images !== 'N'
                && (!$device->isMobile() || $device->isTablet())
                && !empty($mainCategory->getImage(\JTL\Media\Image::SIZE_XS))}
                    {image fluid=true lazy=true webp=true
                    src=$mainCategory->getImage(\JTL\Media\Image::SIZE_XS)
                    srcset="{$mainCategory->getImage(\JTL\Media\Image::SIZE_XS)} {$Einstellungen.bilder.bilder_kategorien_mini_breite}w,
                            {$mainCategory->getImage(\JTL\Media\Image::SIZE_SM)} {$Einstellungen.bilder.bilder_kategorien_klein_breite}w,
                            {$mainCategory->getImage(\JTL\Media\Image::SIZE_MD)} {$Einstellungen.bilder.bilder_kategorien_breite}w"
                sizes="80px"
                alt=$mainCategory->getName()|escape:'html'
                class="d-none d-md-block mb-3"}
            {/if}
            <span class="text-truncate d-block pr-3 pr-lg-0">{$mainCategory->getName()}</span>
            <span class="badge text-gray-dark product-count">{$mainCategory->getProductCount()}</span>
        {/link}
    {/block}
    {if $mainCategory->hasChildren() && $Einstellungen.template.megamenu.show_subcategories !== 'N'}
        {block name='snippets-categories-mega-recursive-child-content'}
            <div class="dropdown-menu">
                {nav}
                    {block name='snippets-categories-mega-recursive-child-header'}
                        {navitem class="dropdown d-lg-none" href=$mainCategory->getURL() nofollow=true}
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
                                    {navitem href=$category->getURL()}
                                            <span class="text-truncate d-block pr-3 pr-lg-0">{$category->getName()}</span>
                                            <span class="badge text-gray-dark product-count">{$category->getProductCount()}</span>
                                    {/navitem}
                                {/block}
                            {/if}
                        {/foreach}
                    {/block}
                {/nav}
            </div>
        {/block}
    {/if}
{/block}
