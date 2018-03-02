{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}
{block name='header'}
    {if !isset($bAjaxRequest) || !$bAjaxRequest}
        {include file='layout/header.tpl'}
    {elseif isset($smarty.get.quickView) && $smarty.get.quickView == 1}
        {include file='layout/modal_header.tpl'}
    {/if}
{/block}

{block name='content'}
    {if isset($bAjaxRequest) && $bAjaxRequest && isset($listStyle) && ($listStyle === 'list' || $listStyle === 'gallery')}
        {if $listStyle === 'list'}
            {assign var='tplscope' value='list'}
            {include file='productlist/item_list.tpl'}
        {elseif $listStyle === 'gallery'}
            {assign var='tplscope' value='gallery'}
            {assign var='class' value='thumbnail'}
            {include file='productlist/item_box.tpl'}
        {/if}
    {else}
        <div id="result-wrapper" itemprop="mainEntity" itemscope itemtype="http://schema.org/Product" itemid="{$ShopURL}/{$Artikel->cSeo}">
            <meta itemprop="url" content="{$ShopURL}/{$Artikel->cSeo}">
            {if !empty($opcPage->cFinalHtml_arr['editor_replace_all']) && empty($smarty.get.frontedit)}
                {$opcPage->cFinalHtml_arr['editor_replace_all']}
            {elseif (!empty($smarty.get.frontedit) && !empty($smarty.get.cAction) && $smarty.get.cAction === 'replace')}
                {include file='snippets/opc_mount_point.tpl' id='editor_replace_all'}
            {else}
                {include file='snippets/extension.tpl'}
                {if isset($Artikel->FunktionsAttribute[$FKT_ATTRIBUT_ARTIKELDETAILS_TPL]) && $currentTemplateDirFullPath|cat:'productdetails/'|cat:$Artikel->FunktionsAttribute[$FKT_ATTRIBUT_ARTIKELDETAILS_TPL]|file_exists}
                    {include file='productdetails/'|cat:$Artikel->FunktionsAttribute[$FKT_ATTRIBUT_ARTIKELDETAILS_TPL]}
                {else}
                    {include file='productdetails/details.tpl'}
                {/if}
            {/if}
        </div>
    {/if}
{/block}

{block name='footer'}
    {if !isset($bAjaxRequest) || !$bAjaxRequest}
        {include file='layout/footer.tpl'}
    {elseif isset($smarty.get.quickView) && $smarty.get.quickView == 1}
        {include file='layout/modal_footer.tpl'}
    {/if}
{/block}