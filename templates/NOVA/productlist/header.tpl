{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='productlist-header'}
    {if !isset($oNavigationsinfo) || isset($Suchergebnisse) && isset($oNavigationsinfo) && empty($oNavigationsinfo->getName())}
        {block name='productlist-header-heading'}
            {opcMountPoint id='opc_before_heading'}
            <div class="h1">{$Suchergebnisse->getSearchTermWrite()}</div>
        {/block}
    {/if}

    {if $Suchergebnisse->getSearchUnsuccessful() == true}
        {block name='productlist-header-alert'}
            {opcMountPoint id='opc_before_no_results'}
            {alert variant="info"}{lang key='noResults' section='productOverview'}{/alert}
        {/block}
        {block name='productlist-header-form-search'}
            {form id="suche2" action=$ShopURL method="get"}
                <fieldset>
                    {formgroup label-for="searchkey" label="{lang key='searchText'}"}
                            {input type="text" name="suchausdruck" value="{if $Suchergebnisse->getSearchTerm()}{$Suchergebnisse->getSearchTerm()|escape:'htmlall'}{/if}" id="searchkey"}
                    {/formgroup}
                    {button variant="primary" type="submit" value="1"}{lang key='searchAgain' section='productOverview'}{/button}
                </fieldset>
            {/form}
        {/block}
    {/if}

    {block name='productlist-header-include-extension'}
        {include file='snippets/extension.tpl'}
    {/block}

    {block name='productlist-header-description'}
        {if $oNavigationsinfo->hasData()}
            <div class="desc clearfix mb-5">
                {if $oNavigationsinfo->getImageURL() !== $smarty.const.BILD_KEIN_KATEGORIEBILD_VORHANDEN && $oNavigationsinfo->getImageURL() !== 'gfx/keinBild_kl.gif'}
                    {image fluid-grow=true fluid=true
                        src="{$oNavigationsinfo->getImageURL()}"
                        alt="{if $oNavigationsinfo->getCategory() !== null}{$oNavigationsinfo->getCategory()->cBeschreibung|strip_tags|truncate:40|escape:'html'}{elseif $oNavigationsinfo->getManufacturer() !== null}{$oNavigationsinfo->getManufacturer()->cBeschreibung|strip_tags|truncate:40|escape:'html'}{/if}"
                        class="mb-5"
                    }
                {/if}
                <div class="title mb-4">
                    {if $oNavigationsinfo->getName()}
                        {opcMountPoint id='opc_before_heading'}
                        <h1>{$oNavigationsinfo->getName()}</h1>
                    {/if}
                </div>
                {if $Einstellungen.navigationsfilter.kategorie_beschreibung_anzeigen === 'Y'
                    && $oNavigationsinfo->getCategory() !== null
                    && $oNavigationsinfo->getCategory()->cBeschreibung|strlen > 0}
                    <div class="item_desc custom_content">{$oNavigationsinfo->getCategory()->cBeschreibung}</div>
                {/if}
                {if $Einstellungen.navigationsfilter.hersteller_beschreibung_anzeigen === 'Y'
                    && $oNavigationsinfo->getManufacturer() !== null
                    && $oNavigationsinfo->getManufacturer()->cBeschreibung|strlen > 0}
                    <div class="item_desc custom_content">{$oNavigationsinfo->getManufacturer()->cBeschreibung}</div>
                {/if}
                {if $Einstellungen.navigationsfilter.merkmalwert_beschreibung_anzeigen === 'Y'
                    && $oNavigationsinfo->getCharacteristicValue() !== null
                    && $oNavigationsinfo->getCharacteristicValue()->cBeschreibung|strlen > 0}
                    <div class="item_desc custom_content">{$oNavigationsinfo->getCharacteristicValue()->cBeschreibung}</div>
                {/if}
            </div>
        {/if}
    {/block}

    {block name='productlist-header-subcategories'}
        {if $Einstellungen.navigationsfilter.artikeluebersicht_bild_anzeigen !== 'N' && $oUnterKategorien_arr|@count > 0}
            {opcMountPoint id='opc_before_subcategories'}
            {row class="row-eq-height content-cats-small clearfix"}
                {foreach $oUnterKategorien_arr as $subCategory}
                    {col cols=6 md=4 lg=3}
                        {if $Einstellungen.navigationsfilter.artikeluebersicht_bild_anzeigen !== 'Y'}
                            {link href=$subCategory->getURL()}
                                {image fluid-grow=true lazy=true src=$subCategory->getImage() alt=$subCategory->getName() class="mb-2"}
                            {/link}
                        {/if}
                        {if $Einstellungen.navigationsfilter.artikeluebersicht_bild_anzeigen !== 'B'}
                            <div class="caption text-center mb-2">
                                {link href=$subCategory->getURL()}
                                    {$subCategory->getName()}
                                {/link}
                            </div>
                        {/if}
                        {if $Einstellungen.navigationsfilter.unterkategorien_beschreibung_anzeigen === 'Y' && !empty($subCategory->getDescription())}
                            <p class="item_desc small text-muted">{$subCategory->getDescription()|strip_tags|truncate:68}</p>
                        {/if}
                        {if $Einstellungen.navigationsfilter.unterkategorien_lvl2_anzeigen === 'Y'}
                            {if $subCategory->hasChildren()}
                                <hr class="my-3">
                                <ul class="list-unstyled small subsub">
                                    {foreach $subCategory->getChildren() as $subChild}
                                        <li>
                                            {link href=$subChild->getURL() title=$subChild->getName()}{$subChild->getName()}{/link}
                                        </li>
                                    {/foreach}
                                </ul>
                            {/if}
                        {/if}
                    {/col}
                {/foreach}
            {/row}
        {/if}
    {/block}

    {block name='productlist-header-include-selection-wizard'}
        {include file='selectionwizard/index.tpl'}
    {/block}

    {if $Suchergebnisse->getProducts()|@count <= 0 && isset($KategorieInhalt)}
        {if isset($KategorieInhalt->TopArtikel->elemente) && $KategorieInhalt->TopArtikel->elemente|@count > 0}
            {block name='productlist-header-include-product-slider-top'}
                {opcMountPoint id='opc_before_category_top'}
                {lang key='topOffer' assign='slidertitle'}
                {include file='snippets/product_slider.tpl' id='slider-top-products' productlist=$KategorieInhalt->TopArtikel->elemente title=$slidertitle}
            {/block}
        {/if}

        {if isset($KategorieInhalt->BestsellerArtikel->elemente) && $KategorieInhalt->BestsellerArtikel->elemente|@count > 0}
            {block name='productlist-header-include-product-slider-bestseller'}
                {opcMountPoint id='opc_before_category_bestseller'}
                {lang key='bestsellers'  assign='slidertitle'}
                {include file='snippets/product_slider.tpl' id='slider-bestseller-products' productlist=$KategorieInhalt->BestsellerArtikel->elemente title=$slidertitle}
            {/block}
        {/if}
    {/if}

    {block name='productlist-header-include-productlist-page-nav'}
        {include file='snippets/productlist_page_nav.tpl' navid='header'}
    {/block}

    {if !$device->isMobile() || $Suchergebnisse->getProducts()|@count <= 0}
        {block name='productlist-header-include-active-filter'}
            {$alertList->displayAlertByKey('noFilterResults')}
            <div class="my-3">
                {include file='snippets/filter/active_filter.tpl'}
            </div>
        {/block}
    {/if}
{/block}
