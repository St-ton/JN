{strip}
{assign var=max_subsub_items value=5}

{block name="megamenu-categories"}
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
        {foreach name='categories' from=$categories item='category'}
            {assign var='isDropdown' value=false}
            {if isset($category->bUnterKategorien) && $category->bUnterKategorien}
                {assign var='isDropdown' value=true}
            {/if}
            <li role="presentation" class="nav-item {if $isDropdown}dropdown megamenu-fw{/if}{if $category->kKategorie == $activeId || (isset($activeParents[0]) && $activeParents[0]->kKategorie == $category->kKategorie)} active{/if}">
                <a href="{$category->cURLFull}"{if $isDropdown} class="dropdown-toggle nav-link" data-target="#" data-toggle="dropdown" data-hover="dropdown" data-delay="300" data-hover-delay="100" data-close-others="true"{/if}>
                    {$category->cKurzbezeichnung}
                    {if $isDropdown}<span class="caret"></span>{/if}
                </a>
                {if $isDropdown}
                    <ul class="dropdown-menu">
                        <li>
                            <div class="megamenu-content">
                                <div class="category-title text-center">
                                    <a href="{$category->cURLFull}">
                                        {$category->cName}
                                    </a>
                                </div>
                                <hr class="hr-sm hidden-xs hidden-sm">
                                <div class="row">
                                    {assign var=hasInfoColumn value=false}
                                    {if $Einstellungen.template.megamenu.show_maincategory_info !== 'N'
                                        && ($Einstellungen.template.megamenu.show_category_images !== 'N'
                                            && $category->cBildURL !== 'gfx/keinBild.gif'
                                            || !empty($category->cBeschreibung))}
                                        {assign var=hasInfoColumn value=true}
                                        <div class="col-lg-3 visible-lg">
                                            <div class="mega-info-lg top15">
                                                {if $Einstellungen.template.megamenu.show_category_images !== 'N'
                                                    && $category->cBildURL !== 'gfx/keinBild.gif'}
                                                    <a href="{$category->cURLFull}">
                                                        <img class="img-responsive lazy loading" data-src="{$category->cBildURLFull}"
                                                             src="{$imageBaseURL}gfx/trans.png"
                                                             alt="{$category->cKurzbezeichnung|escape:'html'}">
                                                    </a>
                                                    <div class="clearall top15"></div>
                                                {/if}
                                                <div class="description text-muted small">{$category->cBeschreibung}</div>
                                            </div>
                                        </div>
                                    {/if}
                                    <div class="col-xs-12{if $hasInfoColumn} col-lg-9{/if} mega-categories{if $hasInfoColumn} hasInfoColumn{/if}">
                                        <div class="row">
                                            {if $category->bUnterKategorien}
                                                {if !empty($category->Unterkategorien)}
                                                    {assign var=sub_categories value=$category->Unterkategorien}
                                                {else}
                                                    {get_category_array categoryId=$category->kKategorie assign='sub_categories'}
                                                {/if}
                                                {foreach name=sub_categories from=$sub_categories item='sub'}
                                                    <div class="col-xs-12 col-md-6 col-lg-3">
                                                        <div class="dropdown-item category-wrapper top15{if $sub->kKategorie == $activeId || (isset($activeParents[1]) && $activeParents[1]->kKategorie == $sub->kKategorie)} active{/if}">
                                                            {if $Einstellungen.template.megamenu.show_category_images !== 'N'}
                                                                <div class="img text-center hidden-xs hidden-sm">
                                                                    <a href="{$sub->cURLFull}">
                                                                        <img class="image lazy loading" data-src="{$sub->cBildURLFull}"
                                                                             src="{$imageBaseURL}gfx/trans.png"
                                                                             alt="{$category->cKurzbezeichnung|escape:'html'}">
                                                                    </a>
                                                                </div>
                                                            {/if}
                                                            <div class="caption{if $Einstellungen.template.megamenu.show_category_images !== 'N'} text-center{/if}">
                                                                <div class="title h5">
                                                                    <a href="{$sub->cURLFull}">
                                                                        <span>
                                                                            {$sub->cKurzbezeichnung}
                                                                        </span>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                            {if $show_subcategories && $sub->bUnterKategorien}
                                                                {if !empty($sub->Unterkategorien)}
                                                                    {assign var=subsub_categories value=$sub->Unterkategorien}
                                                                {else}
                                                                    {get_category_array categoryId=$sub->kKategorie assign='subsub_categories'}
                                                                {/if}
                                                                <hr class="hr-sm hidden-xs hidden-sm">
                                                                <ul class="list-unstyled small subsub">
                                                                    {foreach name='subsub_categories' from=$subsub_categories item='subsub'}
                                                                        {if $smarty.foreach.subsub_categories.iteration <= $max_subsub_items}
                                                                            <li{if $subsub->kKategorie == $activeId || (isset($activeParents[2]) && $activeParents[2]->kKategorie == $subsub->kKategorie)} class="active"{/if}>
                                                                                <a href="{$subsub->cURLFull}">
                                                                                    {$subsub->cKurzbezeichnung}
                                                                                </a>
                                                                            </li>
                                                                        {else}
                                                                            <li class="more"><a href="{$sub->cURLFull}"><i class="fa fa-chevron-circle-right"></i> {lang key="more" section="global"} <span class="remaining">({math equation='total - max' total=$subsub_categories|count max=$max_subsub_items})</span></a></li>
                                                                            {break}
                                                                        {/if}
                                                                    {/foreach}
                                                                </ul>
                                                            {/if}
                                                        </div>
                                                    </div>
                                                    {if $smarty.foreach.sub_categories.iteration % 4 == 0}
                                                        <div class="clearfix visible-lg-block"></div>
                                                    {/if}
                                                    {if $smarty.foreach.sub_categories.iteration % 2 == 0}
                                                        <div class="clearfix visible-md-block"></div>
                                                    {/if}
                                                {/foreach}
                                            {/if}
                                        </div>{* /row *}
                                    </div>{* /mega-categories *}
                                </div>{* /row *}
                            </div>{* /megamenu-content *}
                        </li>
                    </ul>
                {/if}
            </li>
        {/foreach}
    {/if}
{/if}
{/block}{* /megamenu-categories*}

{block name="megamenu-pages"}
{if $Einstellungen.template.megamenu.show_pages !== 'N'}
    {include file='snippets/linkgroup_list.tpl' linkgroupIdentifier='megamenu' dropdownSupport=true tplscope='megamenu'}
{/if}
{/block}{* megamenu-pages *}

{block name="megamenu-manufacturers"}
{if $Einstellungen.template.megamenu.show_manufacturers !== 'N'
    && ($Einstellungen.global.global_sichtbarkeit != 3
        || isset($smarty.session.Kunde->kKunde)
        && $smarty.session.Kunde->kKunde != 0)}
    {get_manufacturers assign='manufacturers'}
    {if !empty($manufacturers)}
        <li class="dropdown megamenu-fw{if $NaviFilter->hasManufacturer() || $nSeitenTyp == PAGE_HERSTELLER} active{/if}">
            {assign var="linkKeyHersteller" value=Shop::Container()->getLinkHelper()->getSpecialPageID(LINKTYP_HERSTELLER)}
            {if !empty($linkKeyHersteller)}{assign var="linkSEOHersteller" value=Shop::Container()->getLinkHelper()->getLinkByID($linkKeyHersteller)}{/if}
            {if isset($linkSEOHersteller)}
                <a href="{$linkSEOHersteller->getURL()}" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="300" data-hover-delay="100" data-close-others="true">
                    {$linkSEOHersteller->getName()}
                    <span class="caret"></span>
                </a>
            {else}
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="300" data-hover-delay="100" data-close-others="true">
                    {lang key="manufacturers" section="global"}
                    <span class="caret"></span>
                </a>
            {/if}
            <ul class="dropdown-menu keepopen">
                <li>
                    <div class="megamenu-content">
                        <div class="category-title manufacturer text-center hidden-xs hidden-sm">
                            {if isset($linkSEOHersteller)}
                                <a href="{$linkSEOHersteller->getURL()}">{$linkSEOHersteller->getName()}</a>
                            {else}
                                <span>{lang key="manufacturers" section="global"}</span>
                            {/if}
                        </div>
                        <hr class="hr-sm  hidden-xs hidden-sm">
                        <div class="row">
                            <div class="col-xs-12 mega-categories manufacturer">
                                <div class="row row-eq-height row-eq-img-height">
                                    {foreach name=hersteller from=$manufacturers item=hst}
                                        <div class="col-xs-12 col-md-6 col-lg-3">
                                            <div class="category-wrapper manufacturer top15{if $NaviFilter->hasManufacturer() && $NaviFilter->getManufacturer()->getValue() == $hst->kHersteller} active{/if}">
                                                {if $Einstellungen.template.megamenu.show_category_images !== 'N'}
                                                    <div class="img text-center hidden-xs hidden-sm">
                                                        <a href="{$hst->cURLFull}">
                                                            <img class="lazy loading" data-src="{$hst->cBildURLNormal}" src="{$imageBaseURL}gfx/trans.png" alt="{$hst->cName|escape:'html'}" />
                                                            {*<img src="{$hst->cBildURLNormal}" class=image alt="{$hst->cName|escape:'html'}">*}
                                                        </a>
                                                    </div>
                                                {/if}
                                                <div class="caption{if $Einstellungen.template.megamenu.show_category_images !== 'N'} text-center{/if}">
                                                    <div class="title h5"><a href="{$hst->cURLFull}"><span>{$hst->cName}</span></a></div>
                                                </div>
                                            </div>{* /category-wrapper *}
                                        </div>
                                    {/foreach}
                                </div>{* /row *}
                            </div>{* /mega-categories *}
                        </div>{* /row *}
                    </div>{* /megamenu-content *}
                </li>
            </ul>
        </li>
    {/if}
{/if}
{/block}{* megamenu-manufacturers *}


{block name="megamenu-global-characteristics"}
{*
{if isset($Einstellungen.template.megamenu.show_global_characteristics) && $Einstellungen.template.megamenu.show_global_characteristics !== 'N'}
    {get_global_characteristics assign='characteristics'}
    {if !empty($characteristics)}

    {/if}
{/if}
*}
{/block}{* megamenu-global-characteristics *}
{/strip}
