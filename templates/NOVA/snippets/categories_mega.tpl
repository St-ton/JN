{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='snippets-categories-mega'}
    {strip}
    {if !isset($i)}
        {assign var=i value=0}
    {/if}
    {assign var=max_subsub_items value=4}

    {block name='snippets-categories-mega-categories'}
    {if $Einstellungen.template.megamenu.show_categories !== 'N'
        && ($Einstellungen.global.global_sichtbarkeit != 3 || \JTL\Session\Frontend::getCustomer()->getID() > 0)}
        {assign var=show_subcategories value=false}
        {if $Einstellungen.template.megamenu.show_subcategories !== 'N'}
            {assign var=show_subcategories value=true}
        {/if}
        {get_category_array categoryId=0 assign='categories'}
        {if !empty($categories)}
            {if !isset($activeId)}
                {if $NaviFilter->hasCategory()}
                    {$activeId = $NaviFilter->getCategory()->getValue()}
                {elseif $nSeitenTyp === $smarty.const.PAGE_ARTIKEL && isset($Artikel)}
                    {assign var=activeId value=$Artikel->gibKategorie()}
                {elseif $nSeitenTyp === $smarty.const.PAGE_ARTIKEL && isset($smarty.session.LetzteKategorie)}
                    {$activeId = $smarty.session.LetzteKategorie}
                {else}
                    {$activeId = 0}
                {/if}
            {/if}
            {if !isset($activeParents)
            && ($nSeitenTyp === $smarty.const.PAGE_ARTIKEL || $nSeitenTyp === $smarty.const.PAGE_ARTIKELLISTE)}
                {get_category_parents categoryId=$activeId assign='activeParents'}
            {/if}
            {block name='snippets-categories-mega-categories'}
            {foreach $categories as $category}
                {if isset($activeParents) && is_array($activeParents) && isset($activeParents[$i])}
                    {assign var=activeParent value=$activeParents[$i]}
                {/if}
                {if $category->hasChildren()}
                    <li class="nav-item nav-scrollbar-item dropdown dropdown-full{if $category->getID() === $activeId
                    || ((isset($activeParent)
                        && isset($activeParent->kKategorie))
                        && $activeParent->kKategorie == $category->getID())} active{/if}">
                        {link href="{$category->getURL()}" title=$category->getName() class="nav-link dropdown-toggle" target="_self"}
                            {$category->getName()}
                        {/link}
                        <div class="dropdown-menu">
                            <div class="dropdown-header border-bottom border-primary border-w-5 d-lg-none">
                                {row class='align-items-center font-size-base'}
                                    {col}<a href="#" class="font-size-base" data-nav-back><span class="fas fa-chevron-left mr-4"></span> {$category->getName()}</a>{/col}
                                    {col class='col-auto'}<a href="{$category->getURL()}"><span class="far fa-arrow-alt-circle-right ml-auto"></span></a>{/col}
                                {/row}
                            </div>
                            <div class="dropdown-body p-0 py-lg-4">
                                {container}
                                    {row class='lg-row-lg nav'}
                                        {block name='snippets-categories-mega-sub-categories'}
                                            {if $category->hasChildren()}
                                                {if !empty($category->getChildren())}
                                                    {assign var=sub_categories value=$category->getChildren()}
                                                {else}
                                                    {get_category_array categoryId=$category->getID() assign='sub_categories'}
                                                {/if}
                                                {foreach $sub_categories as $sub}
                                                    {col lg=4 xl=3 class="my-lg-4 nav-item {if $sub->hasChildren()}dropdown{/if}"}
                                                        {include file='snippets/categories_mega_recursive.tpl' mainCategory=$sub firstChild=true}
                                                    {/col}
                                                {/foreach}
                                            {/if}
                                        {/block}
                                    {/row}
                                {/container}
                            </div>
                        </div>
                    </li>
                {else}
                    {navitem href=$category->getURL() title=$category->getName()
                        class="nav-scrollbar-item {if $category->getID() === $activeId}active{/if}"}
                        {$category->getShortName()}
                        <span class="badge text-gray-dark product-count">{$category->getProductCount()}</span>
                    {/navitem}
                {/if}
            {/foreach}
            {/block}
        {/if}
    {/if}
    {/block}{* /megamenu-categories*}

    {block name='snippets-categories-mega-manufacturers'}
    {if $Einstellungen.template.megamenu.show_manufacturers !== 'N'
        && ($Einstellungen.global.global_sichtbarkeit != 3
            || isset($smarty.session.Kunde->kKunde)
            && $smarty.session.Kunde->kKunde != 0)}
        {get_manufacturers assign='manufacturers'}
        {if !empty($manufacturers)}
            {assign var=manufacturerOverview value=\JTL\Shop::Container()->getLinkService()->getSpecialPage(LINKTYP_HERSTELLER)}
            {if $manufacturerOverview !== null}
            <li class="nav-item nav-scrollbar-item dropdown dropdown-full">
                {link href='#' title={lang key='manufacturers'} class="nav-link dropdown-toggle" target="_self"}
                    {if $manufacturerOverview !== null && !empty($manufacturerOverview->getName())}{$manufacturerOverview->getName()}{else}{lang key='manufacturers'}{/if}
                {/link}
                <div class="dropdown-menu">
                    <div class="dropdown-header border-bottom border-primary border-w-5 d-lg-none">
                        {row class='align-items-center font-size-base'}
                            {col}<a href="#" class="font-size-base" data-nav-back><span class="fas fa-chevron-left mr-4"></span> {lang key='manufacturers'}</a>{/col}
                            {col class='col-auto'}<a href="{$manufacturerOverview->getURL()}"><span class="far fa-arrow-alt-circle-right ml-auto"></span></a>{/col}
                        {/row}
                    </div>
                    <div class="dropdown-body p-0 py-lg-4">
                        {container}
                            {row class='lg-row-lg nav'}
                                {foreach $manufacturers as $mft}
                                    {col lg=4 xl=3 class='my-lg-4 nav-item'}
                                        {link href=$mft->cURLFull title=$mft->cSeo class='submenu-headline submenu-headline-toplevel nav-link '}
                                            {if $Einstellungen.template.megamenu.show_category_images !== 'N'
                                                && (!$device->isMobile() || $device->isTablet())
                                                && !empty($mft->getImage(\JTL\Media\Image::SIZE_XS))}
                                                {image fluid=true lazy=true
                                                    src=$mft->getImage(\JTL\Media\Image::SIZE_XS)
                                                    srcset="{$mft->getImage(\JTL\Media\Image::SIZE_XS)} {$Einstellungen.bilder.bilder_hersteller_mini_breite}w,
                                                        {$mft->getImage(\JTL\Media\Image::SIZE_SM)} {$Einstellungen.bilder.bilder_hersteller_klein_breite}w,
                                                        {$mft->getImage(\JTL\Media\Image::SIZE_MD)} {$Einstellungen.bilder.bilder_hersteller_normal_breite}w"
                                                    sizes="80px"
                                                    alt=$mft->getName()|escape:'html'
                                                    class="d-none d-md-block mb-3"}
                                            {/if}
                                                {$mft->getName()}
                                        {/link}
                                    {/col}
                                {/foreach}
                            {/row}
                        {/container}
                    </div>
                </div>
            </li>
            {/if}
        {/if}
    {/if}
    {/block} {* /megamenu-manufacturers*}

    {block name='snippets-categories-mega-include-linkgroup-list'}
        {if $Einstellungen.template.megamenu.show_pages !== 'N'}
            {include file='snippets/linkgroup_list.tpl' linkgroupIdentifier='megamenu' dropdownSupport=true tplscope='megamenu'}
        {/if}
    {/block}{* /megamenu-pages*}

    {if $device->isMobile()}
        <li class="d-lg-none"><hr></li>
        {block name='snippets-categories-mega'}
            {foreach $linkgroups->getLinkGroupByTemplate('Kopf')->getLinks() as $Link}
                {navitem active=$Link->getIsActive() href=$Link->getURL() title=$Link->getTitle()}
                    {$Link->getName()}
                {/navitem}
            {/foreach}
        {/block}
        {block name='layout-header-top-bar-user-settings'}
            {block name='layout-header-top-bar-user-settings-currency'}
                {if isset($smarty.session.Waehrungen) && $smarty.session.Waehrungen|@count > 1}
                    <li class="nav-item nav-scrollbar-item dropdown dropdown-full">
                        {link id='currency-dropdown' href='#' title={lang key='currency'} class="nav-link dropdown-toggle" target="_self"}
                            {lang key='currency'}
                        {/link}
                        <div class="dropdown-menu">
                            <div class="dropdown-header border-bottom border-primary border-w-5 d-lg-none">
                                <a href="#" class="font-size-base" data-nav-back><span class="fas fa-chevron-left mr-4"></span> {lang key='currency'}</a>
                            </div>
                            <div class="dropdown-body p-0 py-lg-4">
                                {foreach $smarty.session.Waehrungen as $currency}
                                    {dropdownitem href=$currency->getURLFull() rel="nofollow" active=($smarty.session.Waehrung->getName() === $currency->getName())}
                                        {$currency->getName()}
                                    {/dropdownitem}
                                {/foreach}
                            </div>
                        </div>
                    </li>
                {/if}
            {/block}
            {block name='layout-header-top-bar-user-settings-language'}
                {if isset($smarty.session.Sprachen) && $smarty.session.Sprachen|@count > 1}
                    <li class="nav-item nav-scrollbar-item dropdown dropdown-full">
                        {link id='language-dropdown' href='#' title={lang key='selectLanguage'} class="nav-link dropdown-toggle" target="_self"}
                            {lang key='selectLanguage'}
                        {/link}
                        <div class="dropdown-menu">
                            <div class="dropdown-header border-bottom border-primary border-w-5 d-lg-none">
                                <a href="#" class="font-size-base" data-nav-back><span class="fas fa-chevron-left mr-4"></span> {lang key='selectLanguage'}</a>
                            </div>
                            <div class="dropdown-body p-0 py-lg-4">
                                {foreach $smarty.session.Sprachen as $language}
                                    {dropdownitem href=$language->cURL rel="nofollow" active=($language->kSprache == $smarty.session.kSprache)}
                                        {$language->iso639|upper}
                                    {/dropdownitem}
                                {/foreach}
                            </div>
                        </div>
                    </li>
                {/if}
            {/block}
        {/block}
    {/if}

    {/strip}
{/block}
