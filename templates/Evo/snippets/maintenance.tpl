{block name="snippets-maintenance-doctype"}<!DOCTYPE html>{/block}
<html {block name="snippets-maintenance-html-attributes"}lang="{$meta_language}" itemscope {if $nSeitenTyp == URLART_ARTIKEL}itemtype="http://schema.org/ItemPage"
      {elseif $nSeitenTyp == URLART_KATEGORIE}itemtype="http://schema.org/CollectionPage"
      {else}itemtype="http://schema.org/WebPage"{/if}{/block}>
{block name="snippets-maintenance-head"}
    <head>
        {block name="snippets-maintenance-head-meta"}
            <meta http-equiv="content-type" content="text/html; charset={$smarty.const.JTL_CHARSET}">
            <meta name="description" itemprop="description" content={block name="snippets-maintenance-head-meta-description"}"{$meta_description|truncate:1000:"":true}{/block}">
            <meta name="keywords" itemprop="keywords" content="{block name="snippets-maintenance-head-meta-keywords"}{$meta_keywords|truncate:255:"":true}{/block}">
            <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="robots" content="{if $bNoIndex === true  || (isset($Link->cNoFollow) && $Link->cNoFollow === 'Y')}noindex{else}index, follow{/if}">

            <meta itemprop="image" content="{$ShopURL}/{$ShopLogoURL}" />
            <meta itemprop="url" content="{$cCanonicalURL}"/>
            <meta property="og:type" content="website" />
            <meta property="og:site_name" content="{$meta_title}" />
            <meta property="og:title" content="{$meta_title}" />
            <meta property="og:description" content="{$meta_description|truncate:1000:"":true}" />
            <meta property="og:image" content="{$ShopLogoURL}" />
            <meta property="og:url" content="{$cCanonicalURL}"/>
        {/block}

        <title itemprop="name">{block name="snippets-maintenance-head-title"}{$meta_title}{/block}</title>

        {if !empty($cCanonicalURL)}
            <link rel="canonical" href="{$cCanonicalURL}">
        {/if}

        {block name="snippets-maintenance-head-base"}
            <base href="{$ShopURL}/">
        {/block}

        {block name="snippets-maintenance-head-icons"}
            {if !empty($Einstellungen.template.theme.favicon)}
                {if file_exists("{$currentTemplateDir}{$Einstellungen.template.theme.favicon}")}
                    <link type="image/x-icon" href="{$currentTemplateDir}{$Einstellungen.template.theme.favicon}"
                          rel="shortcut icon">
                {else}
                    <link type="image/x-icon"
                          href="{$currentTemplateDir}themes/base/images/{$Einstellungen.template.theme.favicon}"
                          rel="shortcut icon">
                {/if}
            {else}
                <link type="image/x-icon" href="favicon-default.ico" rel="shortcut icon">
            {/if}
            {if $nSeitenTyp == 1 && isset($Artikel) && !empty($Artikel->Bilder)}
                <link rel="image_src" href="{$ShopURL}/{$Artikel->Bilder[0]->cPfadGross}">
                <meta property="og:image" content="{$ShopURL}/{$Artikel->Bilder[0]->cPfadGross}">
            {/if}
        {/block}

        {block name="snippets-maintenance-head-resources"}
            {* css *}
            {if !isset($Einstellungen.template.general.use_minify) || $Einstellungen.template.general.use_minify === 'N'}
                {foreach from=$cCSS_arr item="cCSS"}
                    <link type="text/css" href="{$cCSS}?v={$nTemplateVersion}" rel="stylesheet">
                {/foreach}

                {if isset($cPluginCss_arr)}
                    {foreach from=$cPluginCss_arr item="cCSS"}
                        <link type="text/css" href="{$cCSS}?v={$nTemplateVersion}" rel="stylesheet">
                    {/foreach}
                {/if}
            {else}
                <link type="text/css" href="asset/{$Einstellungen.template.theme.theme_default}.css{if isset($cPluginCss_arr) && $cPluginCss_arr|@count > 0},plugin_css{/if}?v={$nTemplateVersion}" rel="stylesheet">
            {/if}
            {* RSS *}
            {if isset($Einstellungen.rss.rss_nutzen) && $Einstellungen.rss.rss_nutzen === 'Y'}
                <link rel="alternate" type="application/rss+xml" title="Newsfeed {$Einstellungen.global.global_shopname}" href="rss.xml">
            {/if}
            {* Languages *}
            {if !empty($smarty.session.Sprachen) && count($smarty.session.Sprachen) > 1}
                {foreach item=oSprache from=$smarty.session.Sprachen}
                    <link rel="alternate" hreflang="{$oSprache->cISO639}" href="{if $nSeitenTyp === 18 && $oSprache->cStandard === "Y"}{$cCanonicalURL}{else}{$oSprache->cURLFull}{/if}">
                {/foreach}
            {/if}
        {/block}

        {* Pagination *}
        {if isset($Suchergebnisse->Seitenzahlen->maxSeite) && $Suchergebnisse->Seitenzahlen->maxSeite > 1 && isset($oNaviSeite_arr) && $oNaviSeite_arr|@count > 0}
            {if $Suchergebnisse->Seitenzahlen->AktuelleSeite>1}
                <link rel="prev" href="{$oNaviSeite_arr.zurueck->cURL}">
            {/if}
            {if $Suchergebnisse->Seitenzahlen->AktuelleSeite < $Suchergebnisse->Seitenzahlen->maxSeite}
                <link rel="next" href="{$oNaviSeite_arr.vor->cURL}">
            {/if}
        {/if}

        {if !empty($Einstellungen.template.theme.backgroundcolor) && $Einstellungen.template.theme.backgroundcolor|strlen > 1}
            <style>
                body { background-color: {$Einstellungen.template.theme.backgroundcolor}!important; }
            </style>
        {/if}
        {block name="snippets-maintenance-head-resources-jquery"}
            <script src="{if empty($parentTemplateDir)}{$currentTemplateDir}{else}{$parentTemplateDir}{/if}js/jquery-1.12.4.min.js"></script>
            <script src="{if empty($parentTemplateDir)}{$currentTemplateDir}{else}{$parentTemplateDir}{/if}js/bootstrap.min.js"></script>
            {*Prevent auto-execution of scripts when no explicit dataType was provided (See gh-2432)*}
            <script>
                jQuery.ajaxPrefilter( function( s ) {
                    if ( s.crossDomain ) {
                        s.contents.script = false;
                    }
                } );
            </script>
        {/block}
        {include file='layout/header_inline_js.tpl'}
    </head>
{/block}
<body>
{block name="snippets-maintenance-content"}
        <div class="container">
            <div class="row">
                <div class="col-md-6 col-md-offset-3">
                    <div id="maintenance-notice" class="panel panel-info">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-6">
                                    <h3 class="panel-title"><i class="fa fa-wrench"></i> {lang key="maintainance" section="global"}</h3>
                                </div>
                                <div class="col-xs-6">
                                {if isset($smarty.session.Sprachen) && $smarty.session.Sprachen|@count > 1}
                                    <ul class="list-inline user-settings pull-right">
                                        <li class="language-dropdown dropdown">
                                            <a href="#" class="dropdown-toggle btn btn-default btn-xs" data-toggle="dropdown" itemprop="inLanguage" itemscope itemtype="http://schema.org/Language" title="{lang key='selectLang'}">
                                                <i class="fa fa-language"></i>
                                                {foreach from=$smarty.session.Sprachen item=Sprache}
                                                    {if $Sprache->kSprache == $smarty.session.kSprache}
                                                        <span class="lang-{$lang}" itemprop="name"> {if $lang === 'ger'}{$Sprache->cNameDeutsch}{else}{$Sprache->cNameEnglisch}{/if}</span>
                                                    {/if}
                                                {/foreach}
                                                <span class="caret"></span>
                                            </a>
                                            <ul id="language-dropdown" class="dropdown-menu dropdown-menu-right">
                                                {foreach from=$smarty.session.Sprachen item=oSprache}
                                                    {if $oSprache->kSprache != $smarty.session.kSprache}
                                                        <li>
                                                            <a href="{if isset($oSprache->cURLFull)}{$oSprache->cURLFull}{else}{$oSprache->cURL}{/if}?lang={$oSprache->cISO}" class="link_lang {$oSprache->cISO}" rel="nofollow">{if $lang === 'ger'}{$oSprache->cNameDeutsch}{else}{$oSprache->cNameEnglisch}{/if}</a>
                                                        </li>
                                                    {/if}
                                                {/foreach}
                                            </ul>
                                        </li>
                                    </ul>
                                    {* /language-dropdown *}
                                {/if}
                                </div>
                            </div>

                        </div>
                        <div class="panel-body">
                            {lang key="maintenanceModeActive" section="global"}
                        </div>
                    </div>
                </div>
            </div>
        </div>
{/block}
{block name='snippets-maintenance-content-imprint'}
    <div class="container">
        <div class="row">
            <div class="col-md-6 col-md-offset-3 text-center">
            <h2 class="mt-2">
                {$linkHelper->getPageLinkLanguage($oSpezialseiten_arr[$smarty.const.LINKTYP_IMPRESSUM]->kLink)->cTitle}
            </h2>
            <p>
                {$linkHelper->getPageLinkLanguage($oSpezialseiten_arr[$smarty.const.LINKTYP_IMPRESSUM]->kLink)->cContent}
            </p>
            </div>
        </div>
    </div>
{/block}
</body>
