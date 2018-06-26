{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}

{if $Einstellungen.sitemap.sitemap_seiten_anzeigen === 'Y'}
    {block name="sitemap-pages"}
    <div class="sitemap panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">{block name="sitemap-pages-title"}{lang key='sitemapSites'}{/block}</h3>
        </div>
        <div class="panel-body">
            {block name="sitemap-pages-body"}
            <div class="row">
                {foreach $linkgroups as $linkGroup}
                    {if $linkGroup->getTemplate() !== 'hidden' && $linkGroup->getLinks()->count() > 0}
                        <div class="col-sm-6 col-md-4">
                            <ul class="list-unstyled">
                                {include file='snippets/linkgroup_list.tpl' linkgroupIdentifier=$linkGroup->getTemplate() tplscope='sitemap'}
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

{if $Einstellungen.sitemap.sitemap_kategorien_anzeigen === 'Y' && $oKategorieliste->elemente|@count > 0}
    {block name="sitemap-categories"}
    <div class="sitemap panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">{block name="sitemap-categories-title"}{lang key='sitemapKats'}{/block}</h3>
        </div>
        <div class="panel-body">
            {block name="sitemap-categories-body"}
            <div class="row">
                {* first: categories with subcategories only *}
                {foreach name=kategorien from=$oKategorieliste->elemente item=oKategorie}
                    {if $oKategorie->Unterkategorien|@count > 0}
                        <div class="col-sm-6 col-md-4">
                            <ul class="list-unstyled">
                                <li>
                                    <a href="{$oKategorie->cURLFull}" title="{$oKategorie->cName}">
                                        <strong>
                                            {$oKategorie->cKurzbezeichnung}
                                        </strong>
                                    </a>
                                </li>
                                {foreach name=Subkategorien from=$oKategorie->Unterkategorien item=oSubKategorie}
                                    <li>
                                        <a href="{$oSubKategorie->cURLFull}" title="{$oKategorie->cName}">
                                            {$oSubKategorie->cKurzbezeichnung}
                                        </a>
                                    </li>
                                    {if $oSubKategorie->Unterkategorien|@count > 0}
                                        <li>
                                            <ul class="list-unstyled sub-categories">
                                                {foreach name=SubSubkategorien from=$oSubKategorie->Unterkategorien item=oSubSubKategorie}
                                                    <li>
                                                        <a href="{$oSubSubKategorie->cURLFull}"
                                                           title="{$oKategorie->cName}">
                                                            {$oSubSubKategorie->cKurzbezeichnung}
                                                        </a>
                                                    </li>
                                                {/foreach}
                                            </ul>
                                        </li>
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
                        {foreach $oKategorieliste->elemente as $category}
                            {if $category->Unterkategorien|@count == 0}
                                <li>
                                    &nbsp;&nbsp;<a href="{$category->cURLFull}" title="{$category->cName}">
                                        {$category->cKurzbezeichnung}
                                    </a>
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
{if $Einstellungen.sitemap.sitemap_globalemerkmale_anzeigen === 'Y' && $oGlobaleMerkmale_arr|@count > 0}
    {block name="sitemap-global-attributes"}
    <div class="sitemap panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">{block name="sitemap-global-attributes-title"}{lang key='sitemapGlobalAttributes'}{/block}</h3></div>
        <div class="panel-body">
            {block name="sitemap-global-attributes-body"}
            {foreach $oGlobaleMerkmale_arr as $globalAttribute}
                <strong>{$globalAttribute->cName}</strong>
                <ul class="list-unstyled">
                    {foreach $globalAttribute->oMerkmalWert_arr as $attributeValue}
                        <li class="p33">
                            <a href="{$attributeValue->cURL}">{$attributeValue->cWert}</a>
                        </li>
                    {/foreach}
                </ul>
            {/foreach}
            {/block}
        </div>
    </div>
    {/block}
{/if}

{if $Einstellungen.sitemap.sitemap_hersteller_anzeigen === 'Y' && $oHersteller_arr|@count > 0}
    {block name="sitemap-manufacturer"}
    <div class="sitemap panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">{block name="sitemap-manufacturer-title"}{lang key='sitemapNanufacturer'}{/block}</h3>
        </div>
        <div class="panel-body">
            {block name="sitemap-manufacturer-body"}
            <ul class="list-unstyled">
                {foreach $oHersteller_arr as $manufacturer}
                    <li><a href="{$manufacturer->cURL}">{$manufacturer->cName}</a></li>
                {/foreach}
            </ul>
            {/block}
        </div>
    </div>
    {/block}
{/if}

{if $Einstellungen.news.news_benutzen === 'Y' && $Einstellungen.sitemap.sitemap_news_anzeigen === 'Y' && $oNewsMonatsUebersicht_arr|@count > 0}
    {block name="sitemap-news"}
    <div class="sitemap panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">{block name="sitemap-news-title"}{lang key='sitemapNews'}{/block}</h3>
        </div>
        <div class="panel-body">
            {block name="sitemap-news-body"}
            <div class="row">
                {foreach name=newsmonatsuebersicht from=$oNewsMonatsUebersicht_arr item=oNewsMonatsUebersicht}
                    {if $oNewsMonatsUebersicht->oNews_arr|@count > 0}
                        {math equation='x-y' x=$smarty.foreach.newsmonatsuebersicht.iteration y=1 assign='i'}
                        <div class="col-sm-6 col-md-4">
                            <strong><a href="{$oNewsMonatsUebersicht->cURLFull}">{$oNewsMonatsUebersicht->cName}</a></strong>
                            <ul class="list-unstyled">
                                {foreach name=news from=$oNewsMonatsUebersicht->oNews_arr item=oNews}
                                    <li>&nbsp;&nbsp;<a href="{$oNews->cURLFull}">{$oNews->cBetreff}</a></li>
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
{if $Einstellungen.news.news_benutzen === 'Y' && $Einstellungen.sitemap.sitemap_newskategorien_anzeigen === 'Y' && $oNewsKategorie_arr|@count > 0}
    {block name="sitemap-news-categories"}
    <div class="sitemap panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">{block name="sitemap-news-categories-title"}{lang key='sitemapNewsCats'}{/block}</h3>
        </div>
        <div class="panel-body">
            {block name="sitemap-news-categories-body"}
            <div class="row">
                {foreach $oNewsKategorie_arr as $oNewsKategorie}
                    {if $oNewsKategorie->oNews_arr|@count > 0}
                        <div class="col-sm-6 col-md-4">
                            <strong><a href="{$oNewsKategorie->cURLFull}">{$oNewsKategorie->cName}</a></strong>
                            <ul class="list-unstyled">
                                {foreach name=news from=$oNewsKategorie->oNews_arr item=oNews}
                                    <li>&nbsp;&nbsp;<a href="{$oNews->cURLFull}">{$oNews->cBetreff}</a></li>
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
