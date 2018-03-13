{if !isset($oNavigationsinfo) || isset($Suchergebnisse) && isset($oNavigationsinfo) && empty($oNavigationsinfo->getName())}
    <h1>{$Suchergebnisse->getSearchTermWrite()}</h1>
{/if}

{if !empty($hinweis)}
    <div class="alert alert-success">{$hinweis}</div>
{/if}
{if !empty($fehler)}
    <div class="alert alert-danger">{$fehler}</div>
{/if}

{if $Suchergebnisse->isSearchUnsuccessful() == true}
    <div class="alert alert-info">{lang key='noResults' section='productOverview'}</div>
    <form id="suche2" action="{$ShopURL}" method="get" class="form">
        <fieldset>
            <ul class="list-unstyled">
                <li class="form-group">
                    <label for="searchkey">{lang key='searchText'}</label>
                    <input type="text" class="form-control" name="suchausdruck" value="{if $Suchergebnisse->getSearchTerm()}{$Suchergebnisse->getSearchTerm()|escape:'htmlall'}{/if}" id="searchkey" />
                </li>
                <li class="form-group">
                    <input type="submit" value="{lang key='searchAgain' section='productOverview'}" class="submit btn btn-primary" />
                </li>
            </ul>
        </fieldset>
    </form>
{/if}

{include file='snippets/extension.tpl'}

{block name="productlist-header"}
{if $oNavigationsinfo->hasData()}
    <div class="title">{if $oNavigationsinfo->getName()}<h1>{$oNavigationsinfo->getName()}</h1>{/if}</div>
    <div class="desc clearfix">
        {if $oNavigationsinfo->getImageURL() !== 'gfx/keinBild.gif' && $oNavigationsinfo->getImageURL() !== 'gfx/keinBild_kl.gif'}
          <div class="img pull-left">
            <img class="img-responsive" src="{$ShopURL}/{$oNavigationsinfo->getImageURL()}" alt="{if $oNavigationsinfo->getCategory() !== null}{$oNavigationsinfo->getCategory()->cBeschreibung|strip_tags|truncate:40|escape:'html'}{elseif $oNavigationsinfo->getManufacturer() !== null}{$oNavigationsinfo->getManufacturer()->cBeschreibung|strip_tags|truncate:40|escape:'html'}{/if}" />
          </div>
        {/if}
        {if $Einstellungen.navigationsfilter.kategorie_beschreibung_anzeigen === 'Y'
            && $Einstellungen.navigationsfilter.kategorie_bild_anzeigen !== 'B'
            && $oNavigationsinfo->getCategory() !== null
            && $oNavigationsinfo->getCategory()->cBeschreibung|strlen > 0}
            <div class="item_desc custom_content">{$oNavigationsinfo->getCategory()->cBeschreibung}</div>
        {/if}
        {if $Einstellungen.navigationsfilter.hersteller_beschreibung_anzeigen === 'Y'
            && $Einstellungen.navigationsfilter.hersteller_bild_anzeigen !== 'B'
            && $oNavigationsinfo->getManufacturer() !== null
            && $oNavigationsinfo->getManufacturer()->cBeschreibung|strlen > 0}
            <div class="item_desc custom_content">{$oNavigationsinfo->getManufacturer()->cBeschreibung}</div>
        {/if}
        {if $Einstellungen.navigationsfilter.merkmalwert_beschreibung_anzeigen === 'Y'
            && $Einstellungen.navigationsfilter.merkmalwert_bild_anzeigen !== 'B'
            && $oNavigationsinfo->getAttributeValue() !== null
            && $oNavigationsinfo->getAttributeValue()->cBeschreibung|strlen > 0}
            <div class="item_desc custom_content">{$oNavigationsinfo->getAttributeValue()->cBeschreibung}</div>
        {/if}
    </div>
{/if}
{/block}

{block name="productlist-subcategories"}
{include file='snippets/opc_mount_point.tpl' id='opc_productlist_subcats_prepend'}
{if $Einstellungen.navigationsfilter.artikeluebersicht_bild_anzeigen !== 'N' && $oUnterKategorien_arr|@count > 0}
    <div class="row row-eq-height content-cats-small clearfix">
        {foreach name=unterkats from=$oUnterKategorien_arr item=Unterkat}
            <div class="col-xs-6 col-md-4 col-lg-3">
                <div class="thumbnail">
                    <a href="{$Unterkat->cURL}">
                        {if $Einstellungen.navigationsfilter.artikeluebersicht_bild_anzeigen !== 'Y'}
                            <img src="{$Unterkat->cBildURLFull}" alt="{$Unterkat->cName}"/>
                        {/if}
                    </a>
                    {if $Einstellungen.navigationsfilter.artikeluebersicht_bild_anzeigen !== 'B'}
                        <div class="caption text-center">
                            <a href="{$Unterkat->cURLFull}">
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
                                        <a href="{$UnterUnterKat->cURLFull}" title="{$UnterUnterKat->cName}">{$UnterUnterKat->cName}</a>
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
{include file='snippets/opc_mount_point.tpl' id='opc_productlist_subcats_append'}
{/block}

{include file='productwizard/index.tpl'}

{if count($Suchergebnisse->getProducts()) > 0}
    <form id="improve_search" action="{$ShopURL}" method="get" class="form-inline clearfix">
        {if $NaviFilter->hasCategory()}
            <input type="hidden" name="k" value="{$NaviFilter->getCategory()->getValue()}" />
        {/if}
        {if $NaviFilter->hasManufacturer()}
            <input type="hidden" name="h" value="{$NaviFilter->getManufacturer()->getValue()}" />
        {/if}
        {if $NaviFilter->hasSearchQuery() && $NaviFilter->getSearchQuery()->getValue() > 0}
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
        {if $NaviFilter->hasCategoryFilter()}
            <input type="hidden" name="kf" value="{$NaviFilter->getCategoryFilter()->getValue()}" />
        {/if}
        {if $NaviFilter->hasManufacturerFilter()}
            <input type="hidden" name="hf" value="{$NaviFilter->getManufacturerFilter()->getValue()}" />
        {/if}
        {if $NaviFilter->hasSearchSpecialFilter()}
            <input type="hidden" name="qf" value="{$NaviFilter->getSearchSpecialFilter()->getValueCompat()}" />
        {/if}
        {if $NaviFilter->hasRatingFilter()}
            <input type="hidden" name="bf" value="{$NaviFilter->getRatingFilter()->getValue()}" />
        {/if}
        {if $NaviFilter->hasPriceRangeFilter()}
            <input type="hidden" name="pf" value="{$NaviFilter->getPriceRangeFilter()->getValue()}" />
        {/if}
        {if $NaviFilter->hasAttributeFilter()}
            {foreach name=merkmalfilter from=$NaviFilter->getAttributeFilter() item=attributeFilter}
                <input type="hidden" name="mf{$smarty.foreach.merkmalfilter.iteration}" value="{$attributeFilter->getValue()}" />
            {/foreach}
        {/if}
        {if isset($cJTLSearchStatedFilter_arr) && is_array($cJTLSearchStatedFilter_arr)}
            {foreach name=jtlsearchstatedfilter from=$cJTLSearchStatedFilter_arr key=key item=cJTLSearchStatedFilter}
                <input name="fq{$key}" type="hidden" value="{$cJTLSearchStatedFilter}" />
            {/foreach}
        {/if}
        {if $NaviFilter->hasTagFilter()}
            {foreach name=tagfilter from=$NaviFilter->getTagFilter() item=tagFilter}
                <input type="hidden" name="tf{$smarty.foreach.tagfilter.iteration}" value="{$tagFilter->getValue()}" />
            {/foreach}
        {/if}
        {if $NaviFilter->hasSearchFilter()}
            {foreach name=suchfilter from=$NaviFilter->getSearchFilter() item=searchFilter}
                <input type="hidden" name="sf{$smarty.foreach.suchfilter.iteration}" value="{$searchFilter->getValue()}" />
            {/foreach}
        {/if}
        {include file='productlist/result_options.tpl'}
    </form>
{/if}

{if $Suchergebnisse->getProducts()|@count <= 0 && isset($KategorieInhalt)}
    {if isset($KategorieInhalt->TopArtikel->elemente)}
        {lang key='topOffer' section='global' assign='slidertitle'}
        {include file='snippets/product_slider.tpl' id='slider-top-products' productlist=$KategorieInhalt->TopArtikel->elemente title=$slidertitle}
    {/if}

    {if isset($KategorieInhalt->BestsellerArtikel->elemente)}
        {lang key='bestsellers' section='global' assign='slidertitle'}
        {include file='snippets/product_slider.tpl' id='slider-bestseller-products' productlist=$KategorieInhalt->BestsellerArtikel->elemente title=$slidertitle}
    {/if}
{/if}

{if $Suchergebnisse->getProductCount() > 0}
    <div class="row list-pageinfo top10">
        <div class="col-xs-4 page-current">
            <strong>{lang key='page' section='productOverview'} {$Suchergebnisse->getPages()->AktuelleSeite}</strong> {lang key='of' section='productOverview'} {$Suchergebnisse->getPages()->MaxSeiten}
        </div>
        <div class="col-xs-8 page-total text-right">
            {lang key='products'} {$Suchergebnisse->getOffsetStart()} - {$Suchergebnisse->getOffsetEnd()} {lang key='of' section='productOverview'} {$Suchergebnisse->getProductCount()}
        </div>
    </div>
{/if}

<hr>
