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
        && ($Einstellungen.global.global_sichtbarkeit != 3
            || isset($smarty.session.Kunde->kKunde)
            && $smarty.session.Kunde->kKunde != 0)}
        {assign var=show_subcategories value=false}
        {if $Einstellungen.template.megamenu.show_subcategories !== 'N'}
            {assign var=show_subcategories value=true}
        {/if}
        {get_category_array categoryId=0 assign='categories'}
        {if !empty($categories)}
            {if !isset($activeId)}
                {if $NaviFilter->hasCategory()}
                    {$activeId = $NaviFilter->getCategory()->getValue()}
                {elseif $nSeitenTyp == 1 && isset($Artikel)}
                    {assign var=activeId value=$Artikel->gibKategorie()}
                {elseif $nSeitenTyp == 1 && isset($smarty.session.LetzteKategorie)}
                    {$activeId = $smarty.session.LetzteKategorie}
                {else}
                    {$activeId = 0}
                {/if}
            {/if}
            {if !isset($activeParents) && ($nSeitenTyp == 1 || $nSeitenTyp == 2)}
                {get_category_parents categoryId=$activeId assign='activeParents'}
            {/if}
            {block name='snippets-categories-mega-categories'}
            {foreach $categories as $category}
                {assign var=isDropdown value=$category->hasChildren()}
                {if isset($activeParents) && is_array($activeParents) && isset($activeParents[$i])}
                    {assign var=activeParent value=$activeParents[$i]}
                {/if}
                {if $isDropdown}
                    <li class="nav-item dropdown{if $category->getID() === $activeId
                    || ((isset($activeParent)
                        && isset($activeParent->kKategorie))
                        && $activeParent->kKategorie == $category->getID())} active{/if}">
                        {if $category@first}
                            <div class="wee d-none d-md-block"></div>
                        {/if}
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
                                                                    {if $Einstellungen.template.megamenu.show_category_images !== 'N'}
                                                                        <div class="d-none d-md-block">
                                                                            {image fluid-grow=true lazy=true src="{$imageBaseURL}gfx/trans.png"
                                                                                alt=$category->getShortName()|escape:'html'
                                                                                data=["src" => $sub->getImageURL()]}
                                                                        </div>
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
                    {*{/navitemdropdown}*}
                {else}
                    {navitem href=$category->getURL() title=$category->getName()
                        class="{if $category->getID() === $activeId}active{/if}"}
                        {if $category@first}
                            <div class="wee d-none d-md-block"></div>
                        {/if}
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
            {assign var=linkKeyHersteller value=JTL\Shop::Container()->getLinkService()->getSpecialPageID(LINKTYP_HERSTELLER)|default:0}
            {assign var=linkSEOHersteller value=JTL\Shop::Container()->getLinkService()->getLinkByID($linkKeyHersteller)|default:null}
            {navitemdropdown text="{if $linkSEOHersteller !== null && !empty($linkSEOHersteller->getName())}{$linkSEOHersteller->getName()}{else}{lang key='manufacturers'}{/if}"}
                {container}
                    {row}
                        {foreach $manufacturers as $hst}
                            {col cols=12 md=6 lg=3}
                                {dropdownitem tag="div" active=($NaviFilter->hasManufacturer() && $NaviFilter->getManufacturer()->getValue() == $hst->kHersteller)}
                                    <div class="category-wrapper manufacturer mt-3">
                                        {if $Einstellungen.template.megamenu.show_category_images !== 'N'}
                                            <div class="d-none d-md-block mb-3">
                                                {link href=$hst->cURLFull title=$hst->cSeo}
                                                    {image lazy=true data=["src" => $hst->cBildURLNormal]
                                                         src="{$imageBaseURL}gfx/trans.png" alt=$hst->cName|escape:'html'}
                                                {/link}
                                            </div>
                                        {/if}
                                        <div class="title">
                                            {link href=$hst->cURLFull title=$hst->cSeo}
                                                {$hst->cName}
                                            {/link}
                                        </div>
                                    </div>
                                {/dropdownitem}
                            {/col}
                        {/foreach}
                    {/row}
                {/container}
            {/navitemdropdown}
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
