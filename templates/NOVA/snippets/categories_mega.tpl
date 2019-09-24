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
                {assign var=isDropdown value=$category->hasChildren()}
                {if isset($activeParents) && is_array($activeParents) && isset($activeParents[$i])}
                    {assign var=activeParent value=$activeParents[$i]}
                {/if}
                {if $isDropdown}
                    <li class="nav-item nav-scrollbar-item dropdown dropdown-full{if $category->getID() === $activeId
                    || ((isset($activeParent)
                        && isset($activeParent->kKategorie))
                        && $activeParent->kKategorie == $category->getID())} active{/if}">
                        {link href=$category->getURL() title=$category->getName() class="float-right subcat-link d-inline-block d-md-none"}
                            <i class="fas fa-arrow-alt-circle-right"></i>
                        {/link}
                        {link href=$category->getURL() title=$category->getName() class="nav-link" data=["toggle"=>"dropdown"] target="_self"}
                            {$category->getName()}
                        {/link}
                        <div class="dropdown-menu">
                        {container class="pt-md-2"}
                            {row}
                                {assign var=hasInfoColumn value=false}
                                {*removed info column in NOVA*}
                                {block name='snippets-categories-mega-sub-categories'}
                                    {col lg="{if $hasInfoColumn}9{else}12{/if}" class="mega-categories{if $hasInfoColumn} hasInfoColumn{/if} pt-md-3"}
                                        {row}
                                            {if $category->hasChildren()}
                                                {if !empty($category->getChildren())}
                                                    {assign var=sub_categories value=$category->getChildren()}
                                                {else}
                                                    {get_category_array categoryId=$category->getID() assign='sub_categories'}
                                                {/if}
                                                {foreach $sub_categories as $sub}
                                                    {col cols=12 md=6 lg=3}
                                                        {dropdownitem tag="div" active=$sub->getID() === $activeId || (isset($activeParents[1]) && $activeParents[1]->kKategorie === $sub->getID()) class="p-3 mb-md-6"}
                                                            <div class="category-wrapper">
                                                                {link href=$sub->getURL() title=$sub->getName()}
                                                                    {if $Einstellungen.template.megamenu.show_category_images !== 'N'
                                                                        && (!$device->isMobile() || $device->isTablet())}
                                                                        {image fluid-grow=false lazy=true src="{$imageBaseURL}gfx/trans.png"
                                                                            alt=$category->getShortName()|escape:'html'
                                                                            data=["src" => $sub->getImage(\JTL\Media\Image::SIZE_SM)]
                                                                            class="img-fluid d-none d-md-block"}
                                                                    {/if}
                                                                    <div class="title pt-2">
                                                                        {$sub->getShortName()}
                                                                    </div>
                                                                {/link}
                                                                {if $show_subcategories && $sub->hasChildren()}
                                                                    {if !empty($sub->getChildren())}
                                                                        {assign var=subsub_categories value=$sub->getChildren()}
                                                                    {else}
                                                                        {get_category_array categoryId=$sub->getID() assign='subsub_categories'}
                                                                    {/if}
                                                                    <hr class="my-1 d-none d-md-block">
                                                                    <ul class="list-unstyled small subsub py-2">
                                                                        {foreach $subsub_categories as $subsub}
                                                                            {if $subsub@iteration <= $max_subsub_items}
                                                                                <li{if $subsub->getID() === $activeId || (isset($activeParents[2]) && $activeParents[2]->kKategorie == $subsub->getID())} class="active"{/if}>
                                                                                    {link href=$subsub->getURL() title=$subsub->getName()}
                                                                                        {$subsub->getShortName()}
                                                                                    {/link}
                                                                                </li>
                                                                            {else}
                                                                                <li class="more">
                                                                                    {link href=$sub->getURL() title=$sub->getName()}
                                                                                        <i class="fa fa-chevron-circle-right"></i> {lang key='more'} <span class="remaining">({math equation='total - max' total=$subsub_categories|count max=$max_subsub_items})</span>
                                                                                    {/link}
                                                                                </li>
                                                                                {break}
                                                                            {/if}
                                                                        {/foreach}
                                                                    </ul>
                                                                {/if}
                                                            </div>
                                                        {/dropdownitem}
                                                    {/col}
                                                {/foreach}
                                            {/if}
                                        {/row}
                                    {/col}{* /mega-categories *}
                                {/block}
                                {if $hasInfoColumn}
                                    {block name='snippets-categories-mega-has-info'}
                                        {*removed info column in NOVA*}
                                    {/block}
                                {/if}
                            {/row}
                        {/container}
                        </div>
                    </li>
                {else}
                    {navitem href=$category->getURL() title=$category->getName()
                        class="nav-scrollbar-item {if $category->getID() === $activeId}active{/if}"}
                        {$category->getShortName()}
                    {/navitem}
                {/if}
            {/foreach}
            {/block}
        {/if}
    {/if}
    {/block}{* /megamenu-categories*}

    {block name='snippets-categories-mega-include-linkgroup-list'}
    {if $Einstellungen.template.megamenu.show_pages !== 'N'}
        {include file='snippets/linkgroup_list.tpl' linkgroupIdentifier='megamenu' dropdownSupport=true tplscope='megamenu'}
    {/if}
    {/block}{* megamenu-pages *}

    {block name='snippets-categories-mega-manufacturers'}
    {if $Einstellungen.template.megamenu.show_manufacturers !== 'N'
        && ($Einstellungen.global.global_sichtbarkeit != 3
            || isset($smarty.session.Kunde->kKunde)
            && $smarty.session.Kunde->kKunde != 0)}
        {get_manufacturers assign='manufacturers'}
        {if !empty($manufacturers)}
            {assign var=manufacturerOverview value=\JTL\Shop::Container()->getLinkService()->getSpecialPage(LINKTYP_HERSTELLER)}
            {if $manufacturerOverview !== null}
            <li class="nav-item dropdown">
                {link href=$category->getURL() title=$category->getName() class="float-right subcat-link d-inline-block d-md-none"}
                    <i class="fas fa-arrow-alt-circle-right"></i>
                {/link}
                {link href=$manufacturerOverview->getURL() title={lang key='manufacturers'} class="nav-link" data=["toggle"=>"dropdown"] target="_self"}
                    {if $manufacturerOverview !== null && !empty($manufacturerOverview->getName())}{$manufacturerOverview->getName()}{else}{lang key='manufacturers'}{/if}
                {/link}
                <div class="dropdown-menu">
                    {container}
                        {row}
                            {foreach $manufacturers as $mft}
                                {col cols=12 md=6 lg=3}
                                    {dropdownitem tag="div" active=($NaviFilter->hasManufacturer() && $NaviFilter->getManufacturer()->getValue() === $mft->getID())}
                                        <div class="category-wrapper manufacturer mt-3">
                                            {link href=$mft->cURLFull title=$mft->cSeo}
                                                {if $Einstellungen.template.megamenu.show_category_images !== 'N'
                                                    && (!$device->isMobile() || $device->isTablet())}
                                                    {image lazy=true data=["src" => $mft->getImage(\JTL\Media\Image::SIZE_SM)]
                                                        src="{$imageBaseURL}gfx/trans.png" alt=$mft->getName()|escape:'html'
                                                        class="d-none d-md-block mb-3"}
                                                {/if}
                                                <div class="title">
                                                    {$mft->getName()}
                                                </div>
                                            {/link}
                                        </div>
                                    {/dropdownitem}
                                {/col}
                            {/foreach}
                        {/row}
                    {/container}
                </div>
            </li>
            {/if}
        {/if}
    {/if}
    {/block}{* megamenu-manufacturers *}

    {block name='snippets-categories-mega-global-characteristics'}
    {*
    {if isset($Einstellungen.template.megamenu.show_global_characteristics) && $Einstellungen.template.megamenu.show_global_characteristics !== 'N'}
        {get_global_characteristics assign='characteristics'}
        {if !empty($characteristics)}

        {/if}
    {/if}
    *}
    {/block}{* megamenu-global-characteristics *}
    {/strip}
{/block}
