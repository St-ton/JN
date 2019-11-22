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
                    {block name='snippets-categories-mega-category-child'}
                        <li class="nav-item nav-scrollbar-item dropdown dropdown-full{if $category->getID() === $activeId
                        || ((isset($activeParent)
                            && isset($activeParent->kKategorie))
                            && $activeParent->kKategorie == $category->getID())} active{/if}">
                            {link href=$category->getURL() title=$category->getName() class="nav-link dropdown-toggle" target="_self"}
                                <span class="text-truncate d-block pr-3 pr-lg-0">{$category->getName()}</span>
                            {/link}
                            <div class="dropdown-menu">
                                <div class="dropdown-body p-0 py-lg-4">
                                    {container}
                                        {row class="lg-row-lg nav"}
                                            {col lg=4 xl=3 class="my-lg-4 nav-item dropdown d-lg-none"}
                                                {link href=$category->getURL() class="nav-link font-size-base" rel="nofollow"}
                                                    <span class="text-truncate font-weight-bold d-block pr-3 pr-lg-0">{lang key='menuShow' printf=$category->getName()}</span>
                                                {/link}
                                            {/col}
                                            {block name='snippets-categories-mega-sub-categories'}
                                                {if $category->hasChildren()}
                                                    {if !empty($category->getChildren())}
                                                        {assign var=sub_categories value=$category->getChildren()}
                                                    {else}
                                                        {get_category_array categoryId=$category->getID() assign='sub_categories'}
                                                    {/if}
                                                    {foreach $sub_categories as $sub}
                                                        {col lg=4 xl=3 class="my-lg-4 nav-item {if $sub->hasChildren()}dropdown{/if}"}
                                                            {block name='snippets-categories-mega-category-child-body-include-categories-mega-recursive'}
                                                                {include file='snippets/categories_mega_recursive.tpl' mainCategory=$sub firstChild=true}
                                                            {/block}
                                                        {/col}
                                                    {/foreach}
                                                {/if}
                                            {/block}
                                        {/row}
                                    {/container}
                                </div>
                            </div>
                        </li>
                    {/block}
                {else}
                    {block name='snippets-categories-mega-category-no-child'}
                        {navitem href=$category->getURL() title=$category->getName()
                            class="nav-scrollbar-item {if $category->getID() === $activeId}active{/if}"}
                            <span class="text-truncate d-block pr-3 pr-lg-0">{$category->getShortName()}</span>
                            <span class="badge text-gray-dark product-count">{$category->getProductCount()}</span>
                        {/navitem}
                    {/block}
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
            {block name='snippets-categories-mega-manufacturers-inner'}
                <li class="nav-item nav-scrollbar-item dropdown dropdown-full">
                    {link href="{if $manufacturerOverview !== null}{$manufacturerOverview->getURL()}{else}#{/if}" title={lang key='manufacturers'} class="nav-link dropdown-toggle" target="_self"}
                        <span class="text-truncate">
                            {if $manufacturerOverview !== null && !empty($manufacturerOverview->getName())}
                                {$manufacturerOverview->getName()}
                            {else}
                                {lang key='manufacturers'}
                            {/if}
                        </span>
                    {/link}
                    <div class="dropdown-menu">
                        <div class="dropdown-body p-0 py-lg-4">
                            {container}
                                {row class="lg-row-lg nav"}
                                    {col lg=4 xl=3 class="my-lg-4 nav-item dropdown d-lg-none"}
                                        {block name='snippets-categories-mega-manufacturers-header'}
                                            {link href="{if $manufacturerOverview !== null}{$manufacturerOverview->getURL()}{else}#{/if}" class="nav-link font-size-base" rel="nofollow"}
                                                <span class="text-truncate font-weight-bold d-block pr-3 pr-lg-0">
                                                    {if $manufacturerOverview !== null && !empty($manufacturerOverview->getName())}
                                                        {$manufacturerOverview->getName()}
                                                    {else}
                                                        {lang key='manufacturers'}
                                                    {/if}
                                                </span>
                                            {/link}
                                        {/block}
                                    {/col}
                                    {foreach $manufacturers as $mft}
                                        {col lg=4 xl=3 class='my-lg-4 nav-item'}
                                            {block name='snippets-categories-mega-manufacturers-link'}
                                                {link href=$mft->cURLFull title=$mft->cSeo class='submenu-headline submenu-headline-toplevel nav-link '}
                                                    {if $Einstellungen.template.megamenu.show_manufacturer_images !== 'N'
                                                        && (!$device->isMobile() || $device->isTablet())
                                                        && !empty($mft->getImage(\JTL\Media\Image::SIZE_XS))}
                                                        {image fluid=true lazy=true webp=true
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
                                            {/block}
                                        {/col}
                                    {/foreach}
                                {/row}
                            {/container}
                        </div>
                    </div>
                </li>
            {/block}
        {/if}
    {/if}
    {/block} {* /megamenu-manufacturers*}
    {if $Einstellungen.template.megamenu.show_pages !== 'N'}
        {block name='snippets-categories-mega-include-linkgroup-list'}
            {include file='snippets/linkgroup_list.tpl' linkgroupIdentifier='megamenu' dropdownSupport=true tplscope='megamenu'}
        {/block}
    {/if} {* /megamenu-pages*}

    {if $device->isMobile()}
        {block name='snippets-categories-mega-top-links-hr'}
            <li class="d-lg-none"><hr></li>
        {/block}
        {if $Einstellungen.global.global_wunschliste_anzeigen === 'Y'}
            {navitem href="{get_static_route id='wunschliste.php'}"}
                {lang key='wishlist'}
                {badge id="badge-wl-count" variant="primary" class="text-gray-darker product-count"}
                    {if isset($smarty.session.Wunschliste) && !empty($smarty.session.Wunschliste->CWunschlistePos_arr|count)}
                        {$smarty.session.Wunschliste->CWunschlistePos_arr|count}
                    {else}
                        0
                    {/if}
                {/badge}
            {/navitem}
        {/if}
        {navitem href="{get_static_route id='vergleichsliste.php'}"}
            {lang key='compare'}
            {badge id="comparelist-badge" variant="primary" class="text-gray-darker product-count"}
                {if !empty($smarty.session.Vergleichsliste->oArtikel_arr)}{$smarty.session.Vergleichsliste->oArtikel_arr|count}{else}0{/if}
            {/badge}
        {/navitem}
        {block name='snippets-categories-mega-top-links'}
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
                        {block name='layout-header-top-bar-user-settings-currency-link'}
                            {link id='currency-dropdown' href='#' title={lang key='currency'} class="nav-link dropdown-toggle" target="_self"}
                                {lang key='currency'}
                            {/link}
                        {/block}
                        {block name='layout-header-top-bar-user-settings-currency-body'}
                            <div class="dropdown-menu">
                                <div class="dropdown-body p-0 py-lg-4">
                                    {container}
                                        {row class="lg-row-lg nav"}
                                            {col lg=4 xl=3 class="my-lg-4 nav-item dropdown d-lg-none"}
                                                {block name='layout-header-top-bar-user-settings-currency-header'}
                                                    <span class="font-size-base font-weight-bold ">{lang key='currency'}</span>
                                                {/block}
                                            {/col}
                                            {foreach $smarty.session.Waehrungen as $currency}
                                                {col lg=4 xl=3 class='my-lg-4 nav-item'}
                                                    {block name='layout-header-top-bar-user-settings-currency-header-items'}
                                                        {dropdownitem href=$currency->getURLFull() rel="nofollow" active=($smarty.session.Waehrung->getName() === $currency->getName())}
                                                            {$currency->getName()}
                                                        {/dropdownitem}
                                                    {/block}
                                                {/col}
                                            {/foreach}
                                        {/row}
                                    {/container}
                                </div>
                            </div>
                        {/block}
                    </li>
                {/if}
            {/block}
            {block name='layout-header-top-bar-user-settings-language'}
                {if isset($smarty.session.Sprachen) && $smarty.session.Sprachen|@count > 1}
                    <li class="nav-item nav-scrollbar-item dropdown dropdown-full">
                        {block name='layout-header-top-bar-user-settings-language-link'}
                            {link id='language-dropdown' href='#' title={lang key='selectLanguage'} class="nav-link dropdown-toggle" target="_self"}
                                {lang key='selectLanguage'}
                            {/link}
                        {/block}
                        {block name='layout-header-top-bar-user-settings-language-body'}
                            <div class="dropdown-menu">
                                {container}
                                    {row class="lg-row-lg nav"}
                                        {col lg=4 xl=3 class="my-lg-4 nav-item dropdown d-lg-none"}
                                        {block name='layout-header-top-bar-user-settings-language-header'}
                                            <span class="font-size-base font-weight-bold">{lang key='selectLanguage'}</span>
                                        {/block}
                                        {/col}
                                        {foreach $smarty.session.Sprachen as $language}
                                            {col lg=4 xl=3 class='my-lg-4 nav-item'}
                                                {block name='layout-header-top-bar-user-settings-language-header-items'}
                                                    {dropdownitem href=$language->cURL rel="nofollow" active=($language->kSprache == $smarty.session.kSprache)}
                                                        {$language->iso639|upper}
                                                    {/dropdownitem}
                                                {/block}
                                            {/col}
                                        {/foreach}
                                    {/row}
                                {/container}
                            </div>
                        {/block}
                    </li>
                {/if}
            {/block}
        {/block}
    {/if}

    {/strip}
{/block}
