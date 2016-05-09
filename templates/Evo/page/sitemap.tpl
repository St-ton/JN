{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}

{if $Einstellungen.sitemap.sitemap_seiten_anzeigen === 'Y'}
    {block name="sitemap-pages"}
    <div class="sitemap panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">{block name="sitemap-pages-title"}{lang key="sitemapSites" section="global"}{/block}</h3>
        </div>
        <div class="panel-body">
            {block name="sitemap-pages-body"}
            <div class="row">
                {foreach name=linkgruppen from=$linkgroups item=oLinkgruppe key=linkgroupName}
                    {if isset($oLinkgruppe->cName) && $oLinkgruppe->cName != 'hidden' && isset($oLinkgruppe->Links) && !empty($oLinkgruppe->Links)}
                        <div class="col-sm-6 col-md-4">
                            <ul class="list-unstyled">
                                {include file='snippets/linkgroup_list.tpl' linkgroupIdentifier=$linkgroupName tplscope='sitemap'}
                            </ul>
                        </div>
                    {/if}
                {/foreach}
            </div>
            {/block}
        </div>
    </div>
    {/block}
{/if}

{if $Einstellungen.sitemap.sitemap_kategorien_anzeigen === 'Y'}
    {if isset($oKategorieliste->elemente) && $oKategorieliste->elemente|@count > 0}
        {block name="sitemap-categories"}
        <div class="sitemap panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{block name="sitemap-categories-title"}{lang key="sitemapKats" section="global"}{/block}</h3>
            </div>
            <div class="panel-body">
                {block name="sitemap-categories-body"}
                <div class="row">
                    {* first: categories with subcategories only *}
                    {foreach name=kategorien from=$oKategorieliste->elemente item=oKategorie}
                        {if $oKategorie->children|@count > 0}
                            <div class="col-sm-6 col-md-4">
                                <ul class="list-unstyled">
                                    <li>
                                        <a href="{$oKategorie->cURL}" title="{$oKategorie->cName}"><strong>{$oKategorie->cName}</strong></a>
                                    </li>
                                    {foreach name=Subkategorien from=$oKategorie->children item=oSubKategorie}
                                        <li>
                                            <a href="{$oSubKategorie->cURL}" title="{$oKategorie->cName}">{$oSubKategorie->cName}</a>
                                        </li>
                                        {if $oSubKategorie->children|@count > 0}
                                            <ul class="list-unstyled">
                                                {foreach name=SubSubkategorien from=$oSubKategorie->children item=oSubSubKategorie}
                                                    <li>
                                                        <a href="{$oSubSubKategorie->cURL}" title="{$oKategorie->cName}">{$oSubSubKategorie->cName}</a>
                                                    </li>
                                                {/foreach}
                                            </ul>
                                        {/if}
                                    {/foreach}
                                </ul>
                            </div>
                        {/if}
                    {/foreach}

                    {* last: all categories without subcategories *}
                    <div class="col-sm-6 col-md-4">
                        <ul class="list-unstyled">
                            {* <li><b>{lang key="otherCategories" section="global"}</b></li> *}
                            {foreach name=kategorien from=$oKategorieliste->elemente item=oKategorie}
                                {if $oKategorie->children|@count == 0}
                                    <li>
                                        &nbsp;&nbsp;<a href="{$oKategorie->cURL}" title="{$oKategorie->cName}">{$oKategorie->cName}</a>
                                    </li>
                                {/if}
                            {/foreach}
                        </ul>
                    </div>
                </div>
                {/block}
            </div>
        </div>
        {/block}
    {/if}
{/if}

{if $Einstellungen.sitemap.sitemap_globalemerkmale_anzeigen === 'Y'}
    {if $oGlobaleMerkmale_arr|@count > 0}
        {block name="sitemap-global-attributes"}
        <div class="sitemap panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{block name="sitemap-global-attributes-title"}{lang key="sitemapGlobalAttributes" section="global"}{/block}</h3></div>
            <div class="panel-body">
                {block name="sitemap-global-attributes-body"}
                {foreach name=globalemerkmale from=$oGlobaleMerkmale_arr item=oGlobaleMerkmale}
                    <strong>{$oGlobaleMerkmale->cName}</strong>
                    <ul class="list-unstyled">
                        {foreach name=globalemerkmalwerte from=$oGlobaleMerkmale->oMerkmalWert_arr item=oGlobaleMerkmaleWerte}
                            <li class="p33">
                                <a href="{$oGlobaleMerkmaleWerte->cURL}">{$oGlobaleMerkmaleWerte->cWert}</a>
                            </li>
                        {/foreach}
                    </ul>
                {/foreach}
                {/block}
            </div>
        </div>
        {/block}
    {/if}
{/if}

{if $Einstellungen.sitemap.sitemap_hersteller_anzeigen === 'Y'}
    {if $oHersteller_arr|@count > 0}
        {block name="sitemap-manufacturer"}
        <div class="sitemap panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{block name="sitemap-manufacturer-title"}{lang key="sitemapNanufacturer" section="global"}{/block}</h3>
            </div>
            <div class="panel-body">
                {block name="sitemap-manufacturer-body"}
                <ul class="list-unstyled">
                    {foreach name=hersteller from=$oHersteller_arr item=oHersteller}
                        <li><a href="{$oHersteller->cURL}">{$oHersteller->cName}</a></li>
                    {/foreach}
                </ul>
                {/block}
            </div>
        </div>
        {/block}
    {/if}
{/if}

{if $Einstellungen.news.news_benutzen === 'Y' && $Einstellungen.sitemap.sitemap_news_anzeigen === 'Y'}
    {if !empty($oNewsMonatsUebersicht_arr) && $oNewsMonatsUebersicht_arr|@count > 0}
        {block name="sitemap-news"}
        <div class="sitemap panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{block name="sitemap-news-title"}{lang key="sitemapNews" section="global"}{/block}</h3>
            </div>
            <div class="panel-body">
                {block name="sitemap-news-body"}
                <div class="row">
                    {foreach name=newsmonatsuebersicht from=$oNewsMonatsUebersicht_arr item=oNewsMonatsUebersicht}
                        {if $oNewsMonatsUebersicht->oNews_arr|@count > 0}
                            {math equation='x-y' x=$smarty.foreach.newsmonatsuebersicht.iteration y=1 assign='i'}
                            <div class="col-sm-6 col-md-4">
                                <strong><a href="{$oNewsMonatsUebersicht->cURL}">{$oNewsMonatsUebersicht->cName}</a></strong>
                                <ul class="list-unstyled">
                                    {foreach name=news from=$oNewsMonatsUebersicht->oNews_arr item=oNews}
                                        <li>&nbsp;&nbsp;<a href="{$oNews->cURL}">{$oNews->cBetreff}</a></li>
                                    {/foreach}
                                </ul>
                            </div>
                        {/if}
                    {/foreach}
                </div>
                {/block}
            </div>
        </div>
        {/block}
    {/if}
{/if}
{if $Einstellungen.news.news_benutzen === 'Y' && $Einstellungen.sitemap.sitemap_newskategorien_anzeigen === 'Y'}
    {if !empty($oNewsKategorie_arr) && $oNewsKategorie_arr|@count > 0}
        {block name="sitemap-news-categories"}
        <div class="sitemap panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{block name="sitemap-news-categories-title"}{lang key="sitemapNewsCats" section="global"}{/block}</h3>
            </div>
            <div class="panel-body">
                {block name="sitemap-news-categories-body"}
                <div class="row">
                    {foreach name=newskategorie from=$oNewsKategorie_arr item=oNewsKategorie}
                        {if $oNewsKategorie->oNews_arr|@count > 0}
                            <div class="col-sm-6 col-md-4">
                                <strong><a href="{$oNewsKategorie->cURL}">{$oNewsKategorie->cName}</a></strong>
                                <ul class="list-unstyled">
                                    {foreach name=news from=$oNewsKategorie->oNews_arr item=oNews}
                                        <li>&nbsp;&nbsp;<a href="{$oNews->cURL}">{$oNews->cBetreff}</a></li>
                                    {/foreach}
                                </ul>
                            </div>
                        {/if}
                    {/foreach}
                </div>
                {/block}
            </div>
        </div>
        {/block}
    {/if}
{/if}