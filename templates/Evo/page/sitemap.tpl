{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if $Einstellungen.sitemap.sitemap_seiten_anzeigen === 'Y'}
    {block name='sitemap-pages'}
        {opcMountPoint id='opc_before_pages'}

        <div class="sitemap panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{block name='sitemap-pages-title'}{lang key='sitemapSites'}{/block}</h3>
            </div>
            <div class="panel-body">
                {block name='sitemap-pages-body'}
                    <div class="row">
                        {foreach $linkgroups as $linkgroup}
                            {if isset($linkgroup->getName()) && $linkgroup->getName() !== 'hidden' && !empty($linkgroup->getLinks())}
                                <div class="col-sm-6 col-md-4">
                                    <ul class="list-unstyled">
                                        {include file='snippets/linkgroup_list.tpl' linkgroupIdentifier=$linkgroup->getTemplate() tplscope='sitemap'}
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
{if $Einstellungen.sitemap.sitemap_kategorien_anzeigen === 'Y' && isset($oKategorieliste->elemente) && $oKategorieliste->elemente|@count > 0}
    {block name='sitemap-categories'}
        {opcMountPoint id='opc_before_categories'}

        <div class="sitemap panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{block name='sitemap-categories-title'}{lang key='sitemapKats'}{/block}</h3>
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
                                {* <li><b>{lang key='otherCategories'}</b></li> *}
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
{if $Einstellungen.sitemap.sitemap_hersteller_anzeigen === 'Y' && $oHersteller_arr|@count > 0}
    {block name='sitemap-manufacturer'}
        {opcMountPoint id='opc_before_manufacturers'}

        <div class="sitemap panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{block name='sitemap-manufacturer-title'}{lang key='sitemapNanufacturer'}{/block}</h3>
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
{if $Einstellungen.news.news_benutzen === 'Y' && $Einstellungen.sitemap.sitemap_news_anzeigen === 'Y' && !empty($oNewsMonatsUebersicht_arr) && $oNewsMonatsUebersicht_arr|@count > 0}
    {block name='sitemap-news'}
        {opcMountPoint id='opc_before_news'}

        <div class="sitemap panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{block name='sitemap-news-title'}{lang key='sitemapNews'}{/block}</h3>
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
{if $Einstellungen.news.news_benutzen === 'Y' && $Einstellungen.sitemap.sitemap_newskategorien_anzeigen === 'Y' && !empty($oNewsKategorie_arr) && $oNewsKategorie_arr|@count > 0}
    {block name='sitemap-news-categories'}
        {opcMountPoint id='opc_before_news_categories'}

        <div class="sitemap panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{block name='sitemap-news-categories-title'}{lang key='sitemapNewsCats'}{/block}</h3>
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
