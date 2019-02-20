{block name='doctype'}<!DOCTYPE html>{/block}
<html {block name='html-attributes'}lang="{$meta_language}" itemscope {if $nSeitenTyp === $smarty.const.URLART_ARTIKEL}itemtype="http://schema.org/ItemPage"
      {elseif $nSeitenTyp === $smarty.const.URLART_KATEGORIE}itemtype="http://schema.org/CollectionPage"
      {else}itemtype="http://schema.org/WebPage"{/if}{/block}>
{block name='head'}
<head>
    {block name='head-meta'}
        <meta http-equiv="content-type" content="text/html; charset={$smarty.const.JTL_CHARSET}">
        <meta name="description" itemprop="description" content={block name='head-meta-description'}"{$meta_description|truncate:1000:"":true}{/block}">
        <meta name="keywords" itemprop="keywords" content="{block name='head-meta-keywords'}{$meta_keywords|truncate:255:"":true}{/block}">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="robots" content="{if $bNoIndex === true  || (isset($Link) && $Link->getNoFollow() === true)}noindex{else}index, follow{/if}">

        <meta itemprop="url" content="{$cCanonicalURL}"/>
        <meta property="og:type" content="website" />
        <meta property="og:site_name" content="{$meta_title}" />
        <meta property="og:title" content="{$meta_title}" />
        <meta property="og:description" content="{$meta_description|truncate:1000:"":true}" />
        <meta property="og:url" content="{$cCanonicalURL}"/>

        {if $nSeitenTyp === $smarty.const.PAGE_ARTIKEL && !empty($Artikel->Bilder)}
            <meta itemprop="image" content="{$Artikel->Bilder[0]->cURLGross}" />
            <meta property="og:image" content="{$Artikel->Bilder[0]->cURLGross}">
        {elseif $nSeitenTyp === $smarty.const.PAGE_ARTIKELLISTE
        && $oNavigationsinfo->getImageURL() !== 'gfx/keinBild.gif'
        && $oNavigationsinfo->getImageURL() !== 'gfx/keinBild_kl.gif'
        }
            <meta itemprop="image" content="{$imageBaseURL}{$oNavigationsinfo->getImageURL()}" />
            <meta property="og:image" content="{$imageBaseURL}{$oNavigationsinfo->getImageURL()}" />
        {elseif $nSeitenTyp === $smarty.const.PAGE_NEWSDETAIL && !empty($oNewsArchiv->getPreviewImage())}
            <meta itemprop="image" content="{$imageBaseURL}{$oNewsArchiv->getPreviewImage()}" />
            <meta property="og:image" content="{$imageBaseURL}{$oNewsArchiv->getPreviewImage()}" />
        {else}
            <meta itemprop="image" content="{$imageBaseURL}{$ShopLogoURL}" />
            <meta property="og:image" content="{$imageBaseURL}{$ShopLogoURL}" />
        {/if}
    {/block}

    <title itemprop="name">{block name='head-title'}{$meta_title}{/block}</title>

    {if !empty($cCanonicalURL)}
        <link rel="canonical" href="{$cCanonicalURL}">
    {/if}

    {block name='head-base'}{/block}

    {block name='head-icons'}
        <link type="image/x-icon" href="{$shopFaviconURL}" rel="icon">
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

    {if isset($Suchergebnisse) && $Suchergebnisse->getPages()->getMaxPage() > 1}
        {if $Suchergebnisse->getPages()->getCurrentPage() > 1}
            <link rel="prev" href="{$filterPagination->getPrev()->getURL()}">
        {/if}
        {if $Suchergebnisse->getPages()->getCurrentPage() < $Suchergebnisse->getPages()->getMaxPage()}
            <link rel="next" href="{$filterPagination->getNext()->getURL()}">
        {/if}
    {/if}

    {*{if !empty($Einstellungen.template.theme.backgroundcolor) && $Einstellungen.template.theme.backgroundcolor|strlen > 1}
        <style>
            body { background-color: {$Einstellungen.template.theme.backgroundcolor}!important; }
        </style>
    {/if}*}
    {block name='head-resources-jquery'}
        <script src="{$ShopURL}/{if empty($parentTemplateDir)}{$currentTemplateDir}{else}{$parentTemplateDir}{/if}js/jquery-3.3.1.min.js"></script>
    {/block}
    {include file='layout/header_inline_js.tpl'}
    {$dbgBarHead}
</head>
{/block}

{has_boxes position='left' assign='hasLeftPanel'}
{block name='body-tag'}
    <body data-page="{$nSeitenTyp}" {if isset($Link) && !empty($Link->getIdentifier())} id="{$Link->getIdentifier()}"{/if}>
{/block}

{if !$bExclusive}
    {if isset($bAdminWartungsmodus) && $bAdminWartungsmodus}
        {alert show=true variant="warning" id="maintenance-mode" dismissible=true}{lang key='adminMaintenanceMode'}{/alert}
    {/if}

    {block name='header'}
        {if Shop::isAdmin()}
            {include file='layout/header_composer_menu.tpl'}
        {/if}
        {assign var=isSticky value=$Einstellungen.template.theme.static_header === 'Y'}
        <header class="d-print-none container-fluid {if $isSticky}sticky-top{/if}{if $Einstellungen.template.theme.static_header === 'Y'} fixed-navbar{/if}" id="evo-nav-wrapper">

            {block name='header-container-inner'}
                {if !$isFluidTemplate}
                    <div class="container px-0 px-lg-3 clearfix">
                {/if}
                {block name='header-branding-top-bar'}
                    <div id="top-bar" class="text-right d-none d-md-block">
                        {include file='layout/header_top_bar.tpl'}
                    </div>
                {/block}

                {block name='header-category-nav'}

                    {navbar id="evo-main-nav-wrapper" toggleable=true fill=true class="navbar-expand-md px-0 accordion row"}
                        {col id="logo" md=3 order=2 order-md=1}
                            {block name='logo'}
                                <div class="navbar-brand" itemprop="publisher" itemscope itemtype="http://schema.org/Organization" itemid="">
                                    <span itemprop="name" class="d-none">{$meta_publisher}</span>
                                    <meta itemprop="url" content="{$ShopURL}">
                                    <meta itemprop="logo" content="{$imageBaseURL}{$ShopLogoURL}">

                                    {link href=$ShopURL title=$Einstellungen.global.global_shopname}
                                    {if isset($ShopLogoURL)}
                                        {image src=$ShopLogoURL alt=$Einstellungen.global.global_shopname fluid=true}
                                    {else}
                                        <span class="h1">{$Einstellungen.global.global_shopname}</span>
                                    {/if}
                                    {/link}
                                </div>
                            {/block}
                        {/col}
                        {col id="shop-nav" md=4 order=3 order-md=3 class=""}
                            {block name='header-branding-shop-nav'}
                                <div class="d-flex float-right">
                                    {include file='layout/header_nav_icons.tpl'}
                                </div>
                            {/block}
                        {/col}

                        {col md=12 order=1 order-md=5 class="no-flex-grow"}
                            {navbartoggle data=["target"=>"#navbarToggler"] class="d-flex d-md-none"}{/navbartoggle}
                        {/col}


                        {col cols=12 order=5}
                            {*categories*}
                            <div id="navbarToggler" class="collapse navbar-collapse mt-2" data-parent="#evo-main-nav-wrapper">
                                {navbarnav class="megamenu show"}
                                    {include file='snippets/categories_mega.tpl'}
                                    <span class="TabNav_Indicator"></span>
                                {/navbarnav}
                            </div>
                        {/col}
                        {col cols=12 md=5 order=6 order-md=2}
                            {collapse id="nav-search-collapse" tag="div" data=["parent"=>"#evo-main-nav-wrapper"] class="mt-2 d-md-flex float-md-right"}
                                {include file='layout/header_nav_search.tpl'}
                            {/collapse}
                        {/col}
                    {/navbar}

                {/block}

                {if !$isFluidTemplate}
                    </div>{* /container-block *}
                {/if}
            {/block}
        </header>
    {/block}
{/if}

{*{block name='header-category-nav'}
    {assign var=isSticky value=$Einstellungen.template.theme.static_header === 'Y'}
    {navbar id="evo-main-nav-wrapper" sticky=$isSticky toggleable=true fill=true class="navbar-expand-md accordion"}
        {if $isFluidTemplate}
            {include file='layout/header_category_nav.tpl'}
        {else}
            {container}
                {include file='layout/header_category_nav.tpl'}
            {/container}
        {/if}
    {/navbar}
{/block}*}
{block name='header-fluid-banner'}
    {assign var=isFluidBanner value=$Einstellungen.template.theme.banner_full_width === 'Y' && isset($oImageMap)}
    {if $isFluidBanner}
        {include file='snippets/banner.tpl'}
    {/if}
    {assign var=isFluidSlider value=$Einstellungen.template.theme.slider_full_width === 'Y' && isset($oSlider) && count($oSlider->getSlides()) > 0}
    {if $isFluidSlider}
        {include file='snippets/slider.tpl'}
    {/if}
{/block}
{block name='main-wrapper-starttag'}
    <main id="main-wrapper" class="container{if $isFluidTemplate}-fluid{/if}{if $bExclusive} exclusive{/if}{if $hasLeftPanel} aside-active{/if} mt-0 mt-md-6 pt-4 px-4">
{/block}
{block name='content-all-starttags'}
    {block name='content-wrapper-starttag'}
        <div id="content-wrapper">
    {/block}

    {block name='product-pagination'}
        {if $Einstellungen.artikeldetails.artikeldetails_navi_blaettern === 'Y' && isset($NavigationBlaettern)}
            <div class="d-none d-lg-block product-pagination next">
                {if isset($NavigationBlaettern->naechsterArtikel) && $NavigationBlaettern->naechsterArtikel->kArtikel}
                    {link href=$NavigationBlaettern->naechsterArtikel->cURLFull title=$NavigationBlaettern->naechsterArtikel->cName}<span class="fa fa-chevron-right"></span>{/link}
                {/if}
            </div>
            <div class="d-none d-lg-block product-pagination previous">
                {if isset($NavigationBlaettern->vorherigerArtikel) && $NavigationBlaettern->vorherigerArtikel->kArtikel}
                    {link href=$NavigationBlaettern->vorherigerArtikel->cURLFull title=$NavigationBlaettern->vorherigerArtikel->cName}<span class="fa fa-chevron-left"></span>{/link}
                {/if}
            </div>
        {/if}
    {/block}

    {block name='content-row-starttag'}
        <div class="row">
    {/block}

    {block name='content-starttag'}
        <div id="content" class="col-12{if !$bExclusive && !empty($boxes.left|strip_tags|trim)} col-lg-9{/if} order-lg-1 mb-6">
    {/block}

    {block name='header-breadcrumb'}
        {include file='layout/breadcrumb.tpl'}
    {/block}

    {include file='snippets/alert_list.tpl'}

{/block}{* /content-all-starttags *}
