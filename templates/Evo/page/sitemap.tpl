{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}
{if $Einstellungen.sitemap.sitemap_seiten_anzeigen === 'Y'}
    {block name='sitemap-pages'}
    <div class="sitemap panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">{block name='sitemap-pages-title'}{lang key='sitemapSites' section='global'}{/block}</h3>
        </div>
        <div class="panel-body">
            {block name='sitemap-pages-body'}
            <div class="row">
                {foreach $linkgroups as $oLinkgruppe}
                    {if isset($oLinkgruppe->cName) && $oLinkgruppe->cName !== 'hidden' && isset($oLinkgruppe->Links) && !empty($oLinkgruppe->Links)}
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
        {block name='sitemap-categories'}
        <div class="sitemap panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{block name='sitemap-categories-title'}{lang key='sitemapKats' section='global'}{/block}</h3>
            </div>
            <div class="panel-body">
                {block name='sitemap-categories-body'}
                <div class="row">
                    {* first: categories with subcategories only *}
                    {foreach $oKategorieliste->elemente as $oKategorie}
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
                                    {foreach $oKategorie->Unterkategorien as $oSubKategorie}
                                        <li>
                                            <a href="{$oSubKategorie->cURLFull}" title="{$oKategorie->cName}">
                                                {$oSubKategorie->cKurzbezeichnung}
                                            </a>
                                        </li>
                                        {if $oSubKategorie->Unterkategorien|@count > 0}
                                            <li>
                                                <ul class="list-unstyled sub-categories">
                                                    {foreach $oSubKategorie->Unterkategorien as $oSubSubKategorie}
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
                            {* <li><b>{lang key='otherCategories' section='global'}</b></li> *}
                            {foreach $oKategorieliste->elemente as $oKategorie}
                                {if $oKategorie->Unterkategorien|@count == 0}
                                    <li>
                                        &nbsp;&nbsp;<a href="{$oKategorie->cURLFull}" title="{$oKategorie->cName}">
                                            {$oKategorie->cKurzbezeichnung}
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
{/if}
{if $Einstellungen.sitemap.sitemap_globalemerkmale_anzeigen === 'Y'}
    {if $oGlobaleMerkmale_arr|@count > 0}
        {block name='sitemap-global-attributes'}
        <div class="sitemap panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{block name='sitemap-global-attributes-title'}{lang key='sitemapGlobalAttributes' section='global'}{/block}</h3></div>
            <div class="panel-body">
                {block name='sitemap-global-attributes-body'}
                {foreach $oGlobaleMerkmale_arr as $oGlobaleMerkmale}
                    <strong>{$oGlobaleMerkmale->cName}</strong>
                    <ul class="list-unstyled">
                        {foreach $oGlobaleMerkmale->oMerkmalWert_arr as $oGlobaleMerkmaleWerte}
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
        {block name='sitemap-manufacturer'}
        <div class="sitemap panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{block name='sitemap-manufacturer-title'}{lang key='sitemapNanufacturer' section='global'}{/block}</h3>
            </div>
            <div class="panel-body">
                {block name='sitemap-manufacturer-body'}
                <ul class="list-unstyled">
                    {foreach $oHersteller_arr as $oHersteller}
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
        {block name='sitemap-news'}
        <div class="sitemap panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{block name='sitemap-news-title'}{lang key='sitemapNews' section='global'}{/block}</h3>
            </div>
            <div class="panel-body">
                {block name='sitemap-news-body'}
                <div class="row">
                    {foreach $oNewsMonatsUebersicht_arr as $oNewsMonatsUebersicht}
                        {if $oNewsMonatsUebersicht->oNews_arr|@count > 0}
                            {math equation='x-y' x=$oNewsMonatsUebersicht@iteration y=1 assign='i'}
                            <div class="col-sm-6 col-md-4">
                                <strong><a href="{$oNewsMonatsUebersicht->cURLFull}">{$oNewsMonatsUebersicht->cName}</a></strong>
                                <ul class="list-unstyled">
                                    {foreach $oNewsMonatsUebersicht->oNews_arr as $oNews}
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
{/if}
{if $Einstellungen.news.news_benutzen === 'Y' && $Einstellungen.sitemap.sitemap_newskategorien_anzeigen === 'Y'}
    {if !empty($oNewsKategorie_arr) && $oNewsKategorie_arr|@count > 0}
        {block name='sitemap-news-categories'}
        <div class="sitemap panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{block name='sitemap-news-categories-title'}{lang key='sitemapNewsCats' section='global'}{/block}</h3>
            </div>
            <div class="panel-body">
                {block name='sitemap-news-categories-body'}
                <div class="row">
                    {foreach $oNewsKategorie_arr as $oNewsKategorie}
                        {if $oNewsKategorie->oNews_arr|@count > 0}
                            <div class="col-sm-6 col-md-4">
                                <strong><a href="{$oNewsKategorie->cURLFull}">{$oNewsKategorie->cName}</a></strong>
                                <ul class="list-unstyled">
                                    {foreach $oNewsKategorie->oNews_arr as $oNews}
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
{/if}