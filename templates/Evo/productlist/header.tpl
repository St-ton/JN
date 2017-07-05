{if !isset($oNavigationsinfo) || isset($Suchergebnisse) && isset($oNavigationsinfo) && empty($oNavigationsinfo->cName)}
    <h1>{$Suchergebnisse->SuchausdruckWrite}</h1>
{/if}

{if !empty($hinweis)}
    <div class="alert alert-success">{$hinweis}</div>
{/if}
{if !empty($fehler)}
    <div class="alert alert-danger">{$fehler}</div>
{/if}

{if isset($Suchergebnisse->SucheErfolglos) && $Suchergebnisse->SucheErfolglos == 1}
    <div class="alert alert-info">{lang key="noResults" section="productOverview"}</div>
    <form id="suche2" action="index.php" method="get" class="form">
        <fieldset>
            <ul class="list-unstyled">
                <li class="form-group">
                    <label for="searchkey">{lang key="searchText" section="global"}</label>
                    <input type="text" class="form-control" name="suchausdruck" value="{if isset($Suchergebnisse->cSuche)}{$Suchergebnisse->cSuche|escape:'htmlall'}{/if}" id="searchkey" />
                </li>
                <li class="form-group">
                    <input type="submit" value="{lang key="searchAgain" section="productOverview"}" class="submit btn btn-primary" />
                </li>
            </ul>
        </fieldset>
    </form>
{/if}

{include file="snippets/extension.tpl"}

{block name="productlist-header"}
{if isset($oNavigationsinfo->cName) && $oNavigationsinfo->cName !== '' || isset($oNavigationsinfo->cBildURL) && !empty($oNavigationsinfo->cBildURL)}
    <div class="title">{if $oNavigationsinfo->cName}<h1>{$oNavigationsinfo->cName}</h1>{/if}</div>
    <div class="desc clearfix">
        {if !empty($oNavigationsinfo->cBildURL) && $oNavigationsinfo->cBildURL !== 'gfx/keinBild.gif' && $oNavigationsinfo->cBildURL !== 'gfx/keinBild_kl.gif'}
          <div class="img pull-left">
            <img src="{$oNavigationsinfo->cBildURL}" alt="{if isset($oNavigationsinfo->oKategorie->cBeschreibung)}{$oNavigationsinfo->oKategorie->cBeschreibung|strip_tags|truncate:40|escape:"html"}{elseif isset($oNavigationsinfo->oHersteller->cBeschreibung)}{$oNavigationsinfo->oHersteller->cBeschreibung|strip_tags|truncate:40|escape:"html"}{/if}" />
          </div>
        {/if}
        {if $Einstellungen.navigationsfilter.kategorie_beschreibung_anzeigen === 'Y' && isset($oNavigationsinfo->oKategorie) && $oNavigationsinfo->oKategorie->cBeschreibung|strlen > 0}
            <div class="item_desc custom_content">{$oNavigationsinfo->oKategorie->cBeschreibung}</div>
        {/if}
        {if $Einstellungen.navigationsfilter.hersteller_beschreibung_anzeigen === 'Y' && isset($oNavigationsinfo->oHersteller) && $oNavigationsinfo->oHersteller->cBeschreibung|strlen > 0}
            <div class="item_desc custom_content">{$oNavigationsinfo->oHersteller->cBeschreibung}</div>
        {/if}
        {if $Einstellungen.navigationsfilter.merkmalwert_beschreibung_anzeigen === 'Y' && isset($oNavigationsinfo->oMerkmalWert) && $oNavigationsinfo->oMerkmalWert->cBeschreibung|strlen > 0}
            <div class="item_desc custom_content">{$oNavigationsinfo->oMerkmalWert->cBeschreibung}</div>
        {/if}
    </div>
{/if}
{/block}

{block name="productlist-subcategories"}
{if $Einstellungen.navigationsfilter.artikeluebersicht_bild_anzeigen !== 'N' && $oUnterKategorien_arr|@count > 0}
    <div class="row row-eq-height content-cats-small clearfix">
        {foreach name=unterkats from=$oUnterKategorien_arr item=Unterkat}
            <div class="col-xs-6 col-md-4 col-lg-3">
                <div class="thumbnail">
                    <a href="{$Unterkat->cURL}">
                        {if $Einstellungen.navigationsfilter.artikeluebersicht_bild_anzeigen !== 'Y'}
                            <img src="{$Unterkat->cBildURL}" alt="{$Unterkat->cName}"/>
                        {/if}
                    </a>
                    {if $Einstellungen.navigationsfilter.artikeluebersicht_bild_anzeigen !== 'B'}
                        <div class="caption text-center">
                            <a href="{$Unterkat->cURL}">
                                {$Unterkat->cName}
                            </a>
                        </div>
                    {/if}
                    {if $Einstellungen.navigationsfilter.unterkategorien_beschreibung_anzeigen === 'Y'}
                        <p class="item_desc small text-muted">{$Unterkat->cBeschreibung|strip_tags|truncate:68}</p>
                    {/if}
                    {if $Einstellungen.navigationsfilter.unterkategorien_lvl2_anzeigen === 'Y'}
                        {if isset($Unterkat->Unterkategorien) && $Unterkat->Unterkategorien|@count > 0}
                            <hr class="hr-sm">
                            <ul class="list-unstyled small subsub">
                                {foreach from=$Unterkat->Unterkategorien item=UnterUnterKat}
                                    <li>
                                        <a href="{$UnterUnterKat->cURL}" title="{$UnterUnterKat->cName}">{$UnterUnterKat->cName}</a>
                                    </li>
                                {/foreach}
                            </ul>
                        {/if}
                    {/if}
                </div>
            </div>
        {/foreach}
    </div>
{/if}
{/block}

{include file="productwizard/index.tpl"}

{if count($Suchergebnisse->Artikel->elemente) > 0}
    <form id="improve_search" action="index.php" method="get" class="form-inline clearfix">
        {if $NaviFilter->hasCategory()}
            <input type="hidden" name="k" value="{$NaviFilter->getCategory()->getValue()}" />
        {/if}
        {if $NaviFilter->hasManufacturer()}
            <input type="hidden" name="h" value="{$NaviFilter->getManufacturer()->getValue()}" />
        {/if}
        {if $NaviFilter->hasSearchQuery()}
            <input type="hidden" name="l" value="{$NaviFilter->getSearchQuery()->getValue()}" />
        {/if}
        {if $NaviFilter->hasAttributeValue()}
            <input type="hidden" name="m" value="{$NaviFilter->getAttributeValue()->getValue()}" />
        {/if}
        {if $NaviFilter->hasSearchSpecial()}
            <input type="hidden" name="q" value="{$NaviFilter->getSearchSpecial()->getValue()}" />
        {/if}
        {if $NaviFilter->hasSearch()}
            <input type="hidden" name="suche" value="{$NaviFilter->getSearch()->getName()|escape:'htmlall'}" />
        {/if}
        {if $NaviFilter->hasTag()}
            <input type="hidden" name="t" value="{$NaviFilter->getTag()->getValue()}" />
        {/if}
        {*Suchergebnisfilter*}
        {if $NaviFilter->hasCategoryFilter()}
            <input type="hidden" name="kf" value="{$NaviFilter->getCategoryFilter()->getValue()}" />
        {/if}
        {if $NaviFilter->hasManufacturerFilter()}
            <input type="hidden" name="hf" value="{$NaviFilter->getManufacturerFilter()->getValue()}" />
        {/if}
        {if $NaviFilter->hasSearchSpecialFilter()}
            <input type="hidden" name="qf" value="{$NaviFilter->getSearchSpecialFilter()->kKey}" />
        {/if}
        {if $NaviFilter->hasRatingFilter()}
            <input type="hidden" name="bf" value="{$NaviFilter->getRatingFilter()->getValue()}" />
        {/if}
        {if $NaviFilter->hasPriceRangeFilter()}
            <input type="hidden" name="pf" value="{$NaviFilter->getPriceRangeFilter()->getValue()}" />
        {/if}
        {if $NaviFilter->hasAttributeFilter()}
            {foreach name=merkmalfilter from=$NaviFilter->getAttributeFilters() item=attributeFilter}
                <input type="hidden" name="mf{$smarty.foreach.merkmalfilter.iteration}" value="{$attributeFilter->getValue()}" />
            {/foreach}
        {/if}
        {if isset($cJTLSearchStatedFilter_arr) && is_array($cJTLSearchStatedFilter_arr)}
            {foreach name=jtlsearchstatedfilter from=$cJTLSearchStatedFilter_arr key=key item=cJTLSearchStatedFilter}
                <input name="fq{$key}" type="hidden" value="{$cJTLSearchStatedFilter}" />
            {/foreach}
        {/if}
        {if $NaviFilter->hasTagFilter()}
            {foreach name=tagfilter from=$NaviFilter->getTagFilters() item=tagFilter}
                <input type="hidden" name="tf{$smarty.foreach.tagfilter.iteration}" value="{$tagFilter->getValue()}" />
            {/foreach}
        {/if}
        {if $NaviFilter->hasSearchFilter()}
            {foreach name=suchfilter from=$NaviFilter->getSearchFilters() item=searchFilter}
                <input type="hidden" name="sf{$smarty.foreach.suchfilter.iteration}" value="{$searchFilter->getValue()}" />
            {/foreach}
        {/if}
        {include file='productlist/result_options.tpl'}
    </form>
{/if}

{if $Suchergebnisse->Artikel->elemente|@count <= 0 && isset($KategorieInhalt)}
    {if isset($KategorieInhalt->TopArtikel->elemente)}
        {lang key="topOffer" section="global" assign='slidertitle'}
        {include file='snippets/product_slider.tpl' id='slider-top-products' productlist=$KategorieInhalt->TopArtikel->elemente title=$slidertitle}
    {/if}

    {if isset($KategorieInhalt->BestsellerArtikel->elemente)}
        {lang key="bestsellers" section="global" assign='slidertitle'}
        {include file='snippets/product_slider.tpl' id='slider-bestseller-products' productlist=$KategorieInhalt->BestsellerArtikel->elemente title=$slidertitle}
    {/if}
{/if}

{if $Suchergebnisse->GesamtanzahlArtikel > 0}
    <div class="row list-pageinfo top10">
        <div class="col-xs-4 page-current">
            <strong>{lang key="page" section="productOverview"} {$Suchergebnisse->Seitenzahlen->AktuelleSeite}</strong> {lang key="of" section="productOverview"} {$Suchergebnisse->Seitenzahlen->MaxSeiten}
        </div>
        <div class="col-xs-8 page-total text-right">
            {lang key="products" section="global"} {$Suchergebnisse->ArtikelVon} - {$Suchergebnisse->ArtikelBis} {lang key="of" section="productOverview"} {$Suchergebnisse->GesamtanzahlArtikel}
        </div>
    </div>
{/if}

<hr>
