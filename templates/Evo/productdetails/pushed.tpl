{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{lang key='basketAdded' section='messages' assign='pushed_msg'}
{if $nSeitenTyp !== $smarty.const.PAGE_ARTIKEL}
    {include file='productdetails/pushed_success.tpl' Artikel=$zuletztInWarenkorbGelegterArtikel hinweis=$pushed_msg}
{/if}
