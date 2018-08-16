{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='header'}
    {if !isset($bAjaxRequest) || !$bAjaxRequest}
        {include file='layout/header.tpl'}
    {/if}
{/block}

{block name='content'}
    <div id="result-wrapper">
        {if $opcPageService->getCurPage()->isReplace()}
            {include file='snippets/opc_mount_point.tpl' id='opc_replace_all'}
        {else}
            {block name='productlist-header'}
                {include file='productlist/header.tpl'}
            {/block}
            {assign var='style' value='gallery'}
            {if isset($boxes.left) && !$bExclusive && !empty($boxes.left)}
                {assign var='grid' value='col-xs-6 col-lg-4'}
            {else}
                {assign var='grid' value='col-xs-6 col-md-4'}
            {/if}
            {*Prio: -> Funktionsattribut -> Benutzereingabe -> Standarddarstellung*}
            {if (!empty($AktuelleKategorie->categoryFunctionAttributes['darstellung'])
                && $AktuelleKategorie->categoryFunctionAttributes['darstellung'] == 1)
                || (empty($AktuelleKategorie->categoryFunctionAttributes['darstellung'])
                    && ((!empty($oErweiterteDarstellung->nDarstellung) && $oErweiterteDarstellung->nDarstellung == 1)
                        || (empty($oErweiterteDarstellung->nDarstellung)
                            && isset($Einstellungen.artikeluebersicht.artikeluebersicht_erw_darstellung_stdansicht)
                            && $Einstellungen.artikeluebersicht.artikeluebersicht_erw_darstellung_stdansicht == 1))
            )}
                {assign var='style' value='list'}
                {assign var='grid' value='col-xs-12'}
            {/if}
            {if !empty($Suchergebnisse->getError())}
                <p class="alert alert-danger">{$Suchergebnisse->getError()}</p>
            {/if}
            {if isset($oBestseller_arr) && $oBestseller_arr|@count > 0}
                {block name='productlist-bestseller'}
                {lang key='bestseller' section='global' assign='slidertitle'}
                {include file='snippets/product_slider.tpl' id='slider-top-products' productlist=$oBestseller_arr title=$slidertitle}
                {/block}
            {/if}

            {block name='productlist-results'}
            <div class="row {if $style !== 'list'}row-eq-height row-eq-img-height{/if} {$style}" id="product-list" itemprop="mainEntity" itemscope itemtype="http://schema.org/ItemList">
                {foreach $Suchergebnisse->getProducts() as $Artikel}
                    <div class="product-wrapper {$grid}" itemprop="itemListElement" itemscope itemtype="http://schema.org/Product">
                        {if $style === 'list'}
                            {include file='productlist/item_list.tpl' tplscope=$style}
                        {else}
                            {include file='productlist/item_box.tpl' tplscope=$style class='thumbnail'}
                        {/if}
                    </div>
                {/foreach}
            </div>
            {/block}
            {block name='productlist-footer'}
                {include file='productlist/footer.tpl'}
            {/block}
        {/if}
    </div>
{/block}

{block name='footer'}
    {if !isset($bAjaxRequest) || !$bAjaxRequest}
        {include file='layout/footer.tpl'}
    {/if}
{/block}
