{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='doctype'}<!DOCTYPE html>{/block}
<html {block name='html-attributes'}lang="{$meta_language}" itemscope {if $nSeitenTyp == URLART_ARTIKEL}itemtype="http://schema.org/ItemPage"
      {elseif $nSeitenTyp == URLART_KATEGORIE}itemtype="http://schema.org/CollectionPage"
      {else}itemtype="http://schema.org/WebPage"{/if}{/block}>
{block name='head'}
<head>
    {block name='head-meta'}
        <meta http-equiv="content-type" content="text/html; charset={$smarty.const.JTL_CHARSET}">
        <meta name="description" itemprop="description" content={block name='head-meta-description'}"{$meta_description|truncate:1000:"":true}{/block}">
        <meta name="keywords" itemprop="keywords" content="{block name='head-meta-keywords'}{$meta_keywords|truncate:255:"":true}{/block}">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="robots" content="{if $bNoIndex === true  || (isset($Link) && $Link->getNoFollow() === true)}noindex{else}index, follow{/if}">

        <meta itemprop="image" content="{$imageBaseURL}{$ShopLogoURL}" />
        <meta itemprop="url" content="{$cCanonicalURL}"/>
        <meta property="og:type" content="website" />
        <meta property="og:site_name" content="{$meta_title}" />
        <meta property="og:title" content="{$meta_title}" />
        <meta property="og:description" content="{$meta_description|truncate:1000:"":true}" />
        <meta property="og:image" content="{$imageBaseURL}{$ShopLogoURL}" />
        <meta property="og:url" content="{$cCanonicalURL}"/>
    {/block}

    <title itemprop="name">{block name='head-title'}{$meta_title}{/block}</title>

    {if !empty($cCanonicalURL)}
        <link rel="canonical" href="{$cCanonicalURL}">
    {/if}

    {block name='head-base'}{/block}

    {block name='head-icons'}
        {if !empty($Einstellungen.template.theme.favicon)}
            {if file_exists("{$currentTemplateDir}{$Einstellungen.template.theme.favicon}")}
                <link type="image/x-icon" href="{$ShopURL}/{$currentTemplateDir}{$Einstellungen.template.theme.favicon}"
                    rel="shortcut icon">
            {else}
                <link type="image/x-icon"
                    href="{$ShopURL}/{$currentTemplateDir}themes/base/images/{$Einstellungen.template.theme.favicon}"
                        rel="shortcut icon">
            {/if}
        {else}
            <link type="image/x-icon" href="{$ShopURL}/favicon-default.ico" rel="shortcut icon">
        {/if}
        {if $nSeitenTyp === 1 && !empty($Artikel->Bilder)}
            <link rel="image_src" href="{$Artikel->Bilder[0]->cURLGross}">
            <meta property="og:image" content="{$Artikel->Bilder[0]->cURLGross}">
        {/if}
    {/block}

    {block name='head-resources'}
        {* css *}
        {if !isset($Einstellungen.template.general.use_minify) || $Einstellungen.template.general.use_minify === 'N'}
            {foreach $cCSS_arr as $cCSS}
                <link type="text/css" href="{$ShopURL}/{$cCSS}?v={$nTemplateVersion}" rel="stylesheet">
            {/foreach}

            {if isset($cPluginCss_arr)}
                {foreach $cPluginCss_arr as $cCSS}
                    <link type="text/css" href="{$ShopURL}/{$cCSS}?v={$nTemplateVersion}" rel="stylesheet">
                {/foreach}
            {/if}
        {else}
            <link type="text/css" href="{$ShopURL}/asset/{$Einstellungen.template.theme.theme_default}.css{if isset($cPluginCss_arr) && $cPluginCss_arr|@count > 0},plugin_css{/if}?v={$nTemplateVersion}" rel="stylesheet">
        {/if}
        {* RSS *}
        {if isset($Einstellungen.rss.rss_nutzen) && $Einstellungen.rss.rss_nutzen === 'Y'}
            <link rel="alternate" type="application/rss+xml" title="Newsfeed {$Einstellungen.global.global_shopname}" href="{$ShopURL}/rss.xml">
        {/if}
        {* Languages *}
        {if !empty($smarty.session.Sprachen) && count($smarty.session.Sprachen) > 1}
            {foreach item=oSprache from=$smarty.session.Sprachen}
                    <link rel="alternate" hreflang="{$oSprache->cISO639}" href="{$oSprache->cURLFull}">
            {/foreach}
        {/if}
    {/block}

    {* Pagination *}
    {if isset($Suchergebnisse) && $Suchergebnisse->getPages()->getMaxPage() > 1}
        {if $Suchergebnisse->getPages()->getCurrentPage() > 1}
            <link rel="prev" href="{$filterPagination->getPrev()->getURL()}">
        {/if}
        {if $Suchergebnisse->getPages()->getCurrentPage() < $Suchergebnisse->getPages()->getMaxPage()}
            <link rel="next" href="{$filterPagination->getNext()->getURL()}">
        {/if}
    {/if}

    {if !empty($Einstellungen.template.theme.backgroundcolor) && $Einstellungen.template.theme.backgroundcolor|strlen > 1}
        <style>
            body { background-color: {$Einstellungen.template.theme.backgroundcolor}!important; }
        </style>
    {/if}
    {block name='head-resources-jquery'}
        <script src="{$ShopURL}/{if empty($parentTemplateDir)}{$currentTemplateDir}{else}{$parentTemplateDir}{/if}js/jquery-1.12.4.min.js"></script>
    {/block}
    {include file='layout/header_inline_js.tpl'}
</head>
{/block}

{assign var='isFluidContent' value=isset($Einstellungen.template.theme.pagelayout) && $Einstellungen.template.theme.pagelayout === 'fluid' && isset($Link) && $Link->getIsFluid()}
{has_boxes position='left' assign='hasLeftPanel'}
{block name='body-tag'}
<body data-page="{$nSeitenTyp}" class="body-offcanvas"{if isset($Link) && !empty($Link->getIdentifier())} id="{$Link->getIdentifier()}"{/if}>
{/block}
{block name='main-wrapper-starttag'}
<div id="main-wrapper" class="main-wrapper{if $bExclusive} exclusive{/if}{if isset($Einstellungen.template.theme.pagelayout) && $Einstellungen.template.theme.pagelayout === 'boxed'} boxed{else} fluid{/if}{if $hasLeftPanel} aside-active{/if}">
{/block}
{if !$bExclusive}
    {if isset($bAdminWartungsmodus) && $bAdminWartungsmodus}
        <div id="maintenance-mode" class="navbar navbar-inverse">
            <div class="container">
                <div class="navbar-text text-center">
                    {lang key='adminMaintenanceMode' section='global'}
                </div>
            </div>
         </div>
    {/if}

    {block name='header'}
        {if Shop::isAdmin()}
            {include file='layout/header_composer_menu.tpl'}
        {/if}
        <header class="hidden-print {if isset($Einstellungen.template.theme.pagelayout) && $Einstellungen.template.theme.pagelayout === 'fluid'}container-block{/if}{if $Einstellungen.template.theme.static_header === 'Y'} fixed-navbar{/if}" id="evo-nav-wrapper">
            <div class="container">
                {block name='header-container-inner'}
                {if isset($Einstellungen.template.theme.pagelayout) && $Einstellungen.template.theme.pagelayout !== 'fluid'}
                    <div class="container-block clearfix">
                {/if}
                <div id="header-branding" >
                    {block name='header-branding'}
                        {block name='header-branding-top-bar'}
                        <div id="top-bar" class="text-right hidden-xs clearfix">
                            {include file='layout/header_top_bar.tpl'}
                        </div>
                        {/block}
                        {block name='header-branding-content'}
                        <div class="row">
                            <div class="col-xs-4" id="logo" itemprop="publisher" itemscope itemtype="http://schema.org/Organization" itemid="">
                                {block name='logo'}
                                <span itemprop="name" class="hidden">{$meta_publisher}</span>
                                <meta itemprop="url" content="{$ShopURL}">
                                <meta itemprop="logo" content="{$imageBaseURL}{$ShopLogoURL}">
                                <a href="{$ShopURL}" title="{$Einstellungen.global.global_shopname}">
                                    {if isset($ShopLogoURL)}
                                        {image src=$ShopLogoURL alt=$Einstellungen.global.global_shopname class="img-responsive"}
                                    {else}
                                        <span class="h1">{$Einstellungen.global.global_shopname}</span>
                                    {/if}
                                </a>
                                {/block}
                            </div>
                            <div class="col-xs-8" id="shop-nav">
                            {block name='header-branding-shop-nav'}
                                {include file='layout/header_shop_nav.tpl'}
                            {/block}
                            </div>
                        </div>
                        {/block}
                    {/block}{* /header-branding *}
                </div>
                {if isset($Einstellungen.template.theme.pagelayout) && $Einstellungen.template.theme.pagelayout !== 'fluid'}
                    </div>{* /container-block *}
                {/if}
                {/block}
            </div>{* /container *}
            {if isset($Einstellungen.template.theme.pagelayout) && $Einstellungen.template.theme.pagelayout !== 'fluid'}
                <div class="container">
            {/if}
            
            {block name='header-category-nav'}
            <div class="category-nav navbar-wrapper">
                {include file='layout/header_category_nav.tpl'}
            </div>{* /category-nav *}
            {/block}
            
            
            {if isset($Einstellungen.template.theme.pagelayout) && $Einstellungen.template.theme.pagelayout !== 'fluid'}
                </div>{* /container-block *}
            {/if}
        </header>
    {/block}
{/if}

{block name='content-all-starttags'}
    {block name='content-wrapper-starttag'}
    <div id="content-wrapper">
    {/block}
    
    {block name='header-fluid-banner'}
        {assign var="isFluidBanner" value=isset($Einstellungen.template.theme.banner_full_width) && $Einstellungen.template.theme.banner_full_width === 'Y' &&  isset($Einstellungen.template.theme.pagelayout) && $Einstellungen.template.theme.pagelayout === 'fluid' && isset($oImageMap)}
        {if $isFluidBanner}
            {include file='snippets/banner.tpl'}
        {/if}
        {assign var='isFluidSlider' value=isset($Einstellungen.template.theme.slider_full_width) && $Einstellungen.template.theme.slider_full_width === 'Y' &&  isset($Einstellungen.template.theme.pagelayout) && $Einstellungen.template.theme.pagelayout === 'fluid' && isset($oSlider->oSlide_arr) && count($oSlider->oSlide_arr) > 0}
        {if $isFluidSlider}
            {include file='snippets/slider.tpl'}
        {/if}
    {/block}

    {block name='content-container-starttag'}
    <div{if !$bExclusive} class="container{if $isFluidContent}-fluid{/if}{/if}">
    {/block}
    
    {block name='content-container-block-starttag'}
    <div class="container-block{if !$isFluidContent} beveled{/if}">
    {/block}

    {block name='product-pagination'}
    {if $Einstellungen.artikeldetails.artikeldetails_navi_blaettern === 'Y' && isset($NavigationBlaettern)}
        <div class="visible-lg product-pagination next">
            {if isset($NavigationBlaettern->naechsterArtikel) && $NavigationBlaettern->naechsterArtikel->kArtikel}<a href="{$NavigationBlaettern->naechsterArtikel->cURLFull}" title="{$NavigationBlaettern->naechsterArtikel->cName}"><span class="fa fa-chevron-right"></span></a>{/if}
        </div>
        <div class="visible-lg product-pagination previous">
            {if isset($NavigationBlaettern->vorherigerArtikel) && $NavigationBlaettern->vorherigerArtikel->kArtikel}<a href="{$NavigationBlaettern->vorherigerArtikel->cURLFull}" title="{$NavigationBlaettern->vorherigerArtikel->cName}"><span class="fa fa-chevron-left"></span></a>{/if}
        </div>
    {/if}
    {/block}
    
    {block name='content-row-starttag'}
    <div class="row">
    {/block}
    
    {block name='content-starttag'}
    <div id="content" class="col-xs-12{if !$bExclusive && !empty($boxes.left|strip_tags|trim)} {if $nSeitenTyp === 2} col-md-8 col-md-push-4 {/if} col-lg-9 col-lg-push-3{/if}">
    {/block}
    
    {block name='header-breadcrumb'}
        {include file='layout/breadcrumb.tpl'}
    {/block}
{/block}{* /content-all-starttags *}
