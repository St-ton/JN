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
                {assign var='grid' value='12'}
            {else}
                {assign var='style' value='gallery'}
                {assign var='grid' value='6'}
                {assign var='gridmd' value='6'}
                {if !$bExclusive || empty($boxes.left)}
                    {assign var='gridmd' value='4'}
                {/if}
            {/if}
            {if !empty($Suchergebnisse->getError())}
                {alert variant="danger"}{$Suchergebnisse->getError()}{/alert}
            {/if}
            {if isset($oBestseller_arr) && $oBestseller_arr|@count > 0}
                {block name='productlist-bestseller'}
                {lang key='bestseller' section='global' assign='slidertitle'}
                {include file='snippets/product_slider.tpl' id='slider-top-products' productlist=$oBestseller_arr title=$slidertitle}
                {/block}
            {/if}

            {block name='productlist-results'}
            {row class="{$style}" id="product-list" itemprop="mainEntity" itemscope=true itemtype="http://schema.org/ItemList"}
                {foreach $Suchergebnisse->getProducts() as $Artikel}
                    {col cols={$grid} md="{if isset($gridmd)}{$gridmd}{/if}" class="product-wrapper mb-5" itemprop="itemListElement" itemscope=true itemtype="http://schema.org/Product"}
                        {if $style === 'list'}
                            {include file='productlist/item_list.tpl' tplscope=$style}
                        {else}
                            {include file='productlist/item_box.tpl' tplscope=$style}
                        {/if}
                    {/col}
                {/foreach}
            {/row}
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
