{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{strip}
{assign var=max_subsub_items value=4}

{block name='megamenu-categories'}
{if $Einstellungen.template.megamenu.show_categories !== 'N'
    && ($Einstellungen.global.global_sichtbarkeit != 3
        || isset($smarty.session.Kunde->kKunde)
        && $smarty.session.Kunde->kKunde != 0)}
    {assign var='show_subcategories' value=false}
    {if $Einstellungen.template.megamenu.show_subcategories !== 'N'}
        {assign var='show_subcategories' value=true}
    {/if}

    {get_category_array categoryId=0 assign='categories'}
    {if !empty($categories)}
        {if !isset($activeId)}
            {if $NaviFilter->hasCategory()}
                {$activeId = $NaviFilter->getCategory()->getValue()}
            {elseif $nSeitenTyp == 1 && isset($Artikel)}
                {assign var='activeId' value=$Artikel->gibKategorie()}
            {elseif $nSeitenTyp == 1 && isset($smarty.session.LetzteKategorie)}
                {$activeId = $smarty.session.LetzteKategorie}
            {else}
                {$activeId = 0}
            {/if}
        {/if}
        {if !isset($activeParents) && ($nSeitenTyp == 1 || $nSeitenTyp == 2)}
            {get_category_parents categoryId=$activeId assign='activeParents'}
        {/if}
        {foreach $categories as $category}
            {assign var='isDropdown' value=$category->bUnterKategorien && $category->Unterkategorien|count > 0}
            {if $isDropdown}
                {navitemdropdown text="{$category->cKurzbezeichnung}"
                    data=["tab"=>"{$category@iteration}"]
                    class="{if $category@first} pl-0 pr-2{else}px-2{/if}{if $category->kKategorie == $activeId} active{/if}"}
                    {container class="pt-2"}
                        {link href="{$category->cURLFull}" title="{$category->cSeo}" class="title"}
                            {$category->cName}
                        {/link}
                        <hr class="my-2 d-none d-md-block">
                        {row}
                            {assign var=hasInfoColumn value=false}
                        {if $Einstellungen.template.megamenu.show_maincategory_info !== 'N'
                            && ($Einstellungen.template.megamenu.show_category_images !== 'N'
                            && $category->cBildURL !== 'gfx/keinBild.gif'
                            || !empty($category->cBeschreibung))}
                            {assign var=hasInfoColumn value=true}
                        {/if}
                            {col lg="{if $hasInfoColumn}9{else}12{/if}" class="mega-categories{if $hasInfoColumn} hasInfoColumn{/if} pt-3"}
                                {row}
                                    {if $category->bUnterKategorien}
                                        {if !empty($category->Unterkategorien)}
                                            {assign var=sub_categories value=$category->Unterkategorien}
                                        {else}
                                            {get_category_array categoryId=$category->kKategorie assign='sub_categories'}
                                        {/if}
                                        {foreach $sub_categories as $sub}
                                            {col cols=12 md=6 lg=3}
                                                {dropdownitem tag="div" active=$sub->kKategorie == $activeId || (isset($activeParents[1]) && $activeParents[1]->kKategorie == $sub->kKategorie) class="p-0 mb-6"}
                                                    <div class="category-wrapper">
                                                        {if $Einstellungen.template.megamenu.show_category_images !== 'N'}
                                                            <div class="d-none d-md-block">
                                                                {link href="{$sub->cURLFull}" title="{$sub->cSeo}"}
                                                                    {image fluid-grow=true lazy=true src="{$imageBaseURL}gfx/trans.png"
                                                                        alt="{$category->cKurzbezeichnung|escape:'html'}"
                                                                        data=["src"=>"{$sub->cBildURLFull}"]}
                                                                {/link}
                                                            </div>
                                                        {/if}
                                                            <div class="title pt-2">
                                                                {link href="{$sub->cURLFull}" title="{$sub->cSeo}"}
                                                                    {$sub->cKurzbezeichnung}
                                                                {/link}
                                                            </div>
                                                        {if $show_subcategories && $sub->bUnterKategorien}
                                                            {if !empty($sub->Unterkategorien)}
                                                                {assign var=subsub_categories value=$sub->Unterkategorien}
                                                            {else}
                                                                {get_category_array categoryId=$sub->kKategorie assign='subsub_categories'}
                                                            {/if}
                                                            <hr class="my-1 d-none d-md-block">
                                                            <ul class="list-unstyled small subsub py-2">
                                                                {foreach $subsub_categories as $subsub}
                                                                    {if $subsub@iteration <= $max_subsub_items}
                                                                        <li{if $subsub->kKategorie == $activeId || (isset($activeParents[2]) && $activeParents[2]->kKategorie == $subsub->kKategorie)} class="active"{/if}>
                                                                            {link href="{$subsub->cURLFull}" title="{$subsub->cSeo}"}
                                                                                {$subsub->cKurzbezeichnung}
                                                                            {/link}
                                                                        </li>
                                                                    {else}
                                                                        <li class="more">
                                                                            {link href="{$sub->cURLFull}" title="{$sub->cSeo}"}
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
                            {if $hasInfoColumn}
                                {col lg=3 class="d-none d-lg-block mega-info mt-3"}
                                    {if $Einstellungen.template.megamenu.show_category_images !== 'N'
                                        && $category->cBildURL !== 'gfx/keinBild.gif'
                                    }
                                        {link href="{$category->cURLFull}" title="{$category->cSeo}"}
                                            {image class="lazy loading"
                                                fluid=true
                                                data=["src"=>"{$category->cBildURLFull}"]
                                                src="{$imageBaseURL}gfx/trans.png"
                                                alt="{$category->cKurzbezeichnung|escape:'html'}"}
                                        {/link}
                                        <div class="mt-3"></div>
                                    {/if}
                                    <div class="description text-muted small">{$category->cBeschreibung}</div>
                                {/col}
                            {/if}
                        {/row}
                    {/container}
                {/navitemdropdown}
            {else}
                {navitem href="{$category->cURLFull}" title="{$category->cSeo}"
                    data=["tab"=>"{$category@iteration}"] class="{if $category->kKategorie == $activeId}active{/if}"}
                    {$category->cKurzbezeichnung}
                {/navitem}
            {/if}
        {/foreach}
    {/if}
{/if}
{/block}{* /megamenu-categories*}

{block name='megamenu-pages'}
{if $Einstellungen.template.megamenu.show_pages !== 'N'}
    {include file='snippets/linkgroup_list.tpl' linkgroupIdentifier='megamenu' dropdownSupport=true tplscope='megamenu'}
{/if}
{/block}{* megamenu-pages *}

{block name='megamenu-manufacturers'}
{if $Einstellungen.template.megamenu.show_manufacturers !== 'N'
    && ($Einstellungen.global.global_sichtbarkeit != 3
        || isset($smarty.session.Kunde->kKunde)
        && $smarty.session.Kunde->kKunde != 0)}
    {get_manufacturers assign='manufacturers'}
    {if !empty($manufacturers)}
        {assign var='linkKeyHersteller' value=Shop::Container()->getLinkService()->getSpecialPageID(LINKTYP_HERSTELLER)|default:0}
        {assign var='linkSEOHersteller' value=Shop::Container()->getLinkService()->getLinkByID($linkKeyHersteller)|default:null}
        {navitemdropdown text="{if $linkSEOHersteller !== null && !empty($linkSEOHersteller->getName())}{$linkSEOHersteller->getName()}{else}{lang key='manufacturers'}{/if}" data=["tab"=>"manufacturer"]}
            {container}
                <div class="manufacturer">
                    {if isset($linkSEOHersteller)}
                        {link href="{$linkSEOHersteller->getURL()}" title="{$linkSEOHersteller->getName()}"}
                            {$linkSEOHersteller->getName()}
                        {/link}
                    {else}
                        {lang key='manufacturers'}
                    {/if}
                </div>
                <hr class="my-2 d-none d-md-block">
                {row}
                    {foreach $manufacturers as $hst}
                        {col cols=12 md=6 lg=3}
                            {dropdownitem tag="div" active=($NaviFilter->hasManufacturer() && $NaviFilter->getManufacturer()->getValue() == $hst->kHersteller)}
                                <div class="category-wrapper manufacturer mt-3">
                                    {if $Einstellungen.template.megamenu.show_category_images !== 'N'}
                                        <div class="d-none d-md-block">
                                            {link href="{$hst->cURLFull}" title="{$hst->cSeo}"}
                                                {image class="lazy loading" data=["src"=>"{$hst->cBildURLNormal}"]
                                                     src="{$imageBaseURL}gfx/trans.png" alt="{$hst->cName|escape:'html'}"}
                                            {/link}
                                        </div>
                                    {/if}
                                    <div class="title">
                                        {link href="{$hst->cURLFull}" title="{$hst->cSeo}"}
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


{block name='megamenu-global-characteristics'}
{*
{if isset($Einstellungen.template.megamenu.show_global_characteristics) && $Einstellungen.template.megamenu.show_global_characteristics !== 'N'}
    {get_global_characteristics assign='characteristics'}
    {if !empty($characteristics)}

    {/if}
{/if}
*}
{/block}{* megamenu-global-characteristics *}
{/strip}
