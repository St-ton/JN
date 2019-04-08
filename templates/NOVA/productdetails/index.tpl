{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='productdetails-index'}
    {block name='productdetails-index-include-header'}
        {if !isset($bAjaxRequest) || !$bAjaxRequest}
            {include file='layout/header.tpl'}
        {elseif isset($smarty.get.quickView) && $smarty.get.quickView == 1}
            {include file='layout/modal_header.tpl'}
        {/if}
    {/block}
    {block name='productdetails-index-content'}
        {if isset($bAjaxRequest) && $bAjaxRequest && isset($listStyle) && ($listStyle === 'list' || $listStyle === 'gallery')}
            {if $listStyle === 'list'}
                {assign var=tplscope value='list'}
                {block name='productdetails-index-include-item-list'}
                    {include file='productlist/item_list.tpl'}
                {/block}
            {elseif $listStyle === 'gallery'}
                {assign var=tplscope value='gallery'}
                {assign var=class value='thumbnail'}
                {block name='productdetails-index-include'}
                    {include file='productlist/item_box.tpl'}
                {/block}
            {/if}
        {else}
            <div id="result-wrapper" itemprop="mainEntity" itemscope itemtype="http://schema.org/Product" itemid="{$ShopURL}/{$Artikel->cSeo}">
                <meta itemprop="url" content="{$ShopURL}/{$Artikel->cSeo}">
                {if $opcPageService->getCurPage()->isReplace()}
                    {include file='snippets/opc_mount_point.tpl' id='opc_replace_all'}
                {else}
                    {block name='productdetails-index-include-extension'}
                        {include file='snippets/extension.tpl'}
                    {/block}
                    {block name='productdetails-index-include-details'}
                        {include file='productdetails/details.tpl'}
                    {/block}
                {/if}
            </div>
        {/if}
    {/block}

    {block name='productdetails-include-footer'}
        {if !isset($bAjaxRequest) || !$bAjaxRequest}
            {include file='layout/footer.tpl'}
        {elseif isset($smarty.get.quickView) && $smarty.get.quickView == 1}
            {include file='layout/modal_footer.tpl'}
        {/if}
    {/block}
{/block}
