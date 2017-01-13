{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}

{if !isset($bAjaxRequest) || !$bAjaxRequest}
    {include file='layout/header.tpl'}
{/if}
<div id="result-wrapper" itemprop="mainEntity" itemscope  itemtype="http://schema.org/Product" itemid="{$ShopURL}/{$Artikel->cSeo}">
    <meta itemprop="url" content="{$ShopURL}/{$Artikel->cSeo}">
{include file="snippets/extension.tpl"}
{if isset($Artikel->FunktionsAttribute[$FKT_ATTRIBUT_ARTIKELDETAILS_TPL]) && $currentTemplateDirFullPath|cat:'productdetails/'|cat:$Artikel->FunktionsAttribute[$FKT_ATTRIBUT_ARTIKELDETAILS_TPL]|file_exists}
    {include file='productdetails/'|cat:$Artikel->FunktionsAttribute[$FKT_ATTRIBUT_ARTIKELDETAILS_TPL]}
{else}
    {include file='productdetails/details.tpl'}
{/if}
</div>
{if !isset($bAjaxRequest) || !$bAjaxRequest}
    {include file='layout/footer.tpl'}
{/if}