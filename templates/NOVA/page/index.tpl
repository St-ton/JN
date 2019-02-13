{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{include file='selectionwizard/index.tpl'}

{if isset($StartseiteBoxen) && $StartseiteBoxen|@count > 0}
    {assign var='moreLink' value=null}
    {assign var='moreTitle' value=null}
    {include file='snippets/opc_mount_point.tpl' id='opc_home_boxes_prepend'}
    {foreach $StartseiteBoxen as $Box}
        {if isset($Box->Artikel->elemente) && count($Box->Artikel->elemente) > 0 && isset($Box->cURL)}
            {if $Box->name === 'TopAngebot'}
                {lang key='topOffer' assign='title'}
                {lang key='showAllTopOffers' assign='moreTitle'}
            {elseif $Box->name === 'Sonderangebote'}
                {lang key='specialOffer' assign='title'}
                {lang key='showAllSpecialOffers' assign='moreTitle'}
            {elseif $Box->name === 'NeuImSortiment'}
                {lang key='newProducts' assign='title'}
                {lang key='showAllNewProducts'  assign='moreTitle'}
            {elseif $Box->name === 'Bestseller'}
                {lang key='bestsellers' assign='title'}
                {lang key='showAllBestsellers' assign='moreTitle'}
            {/if}
            {assign var='moreLink' value=$Box->cURL}
            {include file='snippets/product_slider.tpl' productlist=$Box->Artikel->elemente title=$title hideOverlays=true moreLink=$moreLink moreTitle=$moreTitle}
        {/if}
    {/foreach}
    {include file='snippets/opc_mount_point.tpl' id='opc_home_boxes_apppend'}
{/if}

{block name='index-additional'}
{if isset($oNews_arr) && $oNews_arr|@count > 0}
    <hr>
    {include file='snippets/opc_mount_point.tpl' id='opc_home_news_prepend'}
    <div class="h2">{lang key='news' section='news'}</div>
    {row itemprop="about" itemscope=true itemtype="http://schema.org/Blog" class="news-slider mx-0"}
        {foreach $oNews_arr as $oNewsUebersicht}
            {col}
                {include file='blog/preview.tpl'}
            {/col}
        {/foreach}
    {/row}
    {include file='snippets/opc_mount_point.tpl' id='opc_home_news_append'}
{/if}
{/block}
