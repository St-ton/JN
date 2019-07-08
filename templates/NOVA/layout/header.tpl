{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='layout-header'}
    {block name='layout-header-doctype'}<!DOCTYPE html>{/block}
    <html {block name='layout-header-html-attributes'}lang="{$meta_language}" itemscope {if $nSeitenTyp === $smarty.const.URLART_ARTIKEL}itemtype="http://schema.org/ItemPage"
          {elseif $nSeitenTyp === $smarty.const.URLART_KATEGORIE}itemtype="http://schema.org/CollectionPage"
          {else}itemtype="http://schema.org/WebPage"{/if}{/block}>
    {block name='layout-header-head'}
    <head>
        {block name='layout-header-head-meta'}
            <meta http-equiv="content-type" content="text/html; charset={$smarty.const.JTL_CHARSET}">
            <meta name="description" itemprop="description" content={block name='layout-header-head-meta-description'}"{$meta_description|truncate:1000:"":true}{/block}">
            <meta name="keywords" itemprop="keywords" content="{block name='layout-header-head-meta-keywords'}{$meta_keywords|truncate:255:"":true}{/block}">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="robots" content="{if $robotsContent}{$robotsContent}{elseif $bNoIndex === true  || (isset($Link) && $Link->getNoFollow() === true)}noindex{else}index, follow{/if}">

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
                <meta itemprop="image" content="{$ShopLogoURL}" />
                <meta property="og:image" content="{$ShopLogoURL}" />
            {/if}
        {/block}

        <title itemprop="name">{block name='layout-header-head-title'}{$meta_title}{/block}</title>

        {if !empty($cCanonicalURL)}
            <link rel="canonical" href="{$cCanonicalURL}">
        {/if}

        {block name='layout-header-head-base'}{/block}

        {block name='layout-header-head-icons'}
            <link type="image/x-icon" href="{$shopFaviconURL}" rel="icon">
        {/block}

        {block name='layout-header-head-resources'}
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
            {if \JTL\Shop::isAdmin() && $opc->isEditMode() === false && $opc->isPreviewMode() === false}
                <link type="text/css" href="{$ShopURL}/admin/opc/opc.css" rel="stylesheet">
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
            {block name='layout-header-prev-next'}
                {if $Suchergebnisse->getPages()->getCurrentPage() > 1}
                    <link rel="prev" href="{$filterPagination->getPrev()->getURL()}">
                {/if}
                {if $Suchergebnisse->getPages()->getCurrentPage() < $Suchergebnisse->getPages()->getMaxPage()}
                    <link rel="next" href="{$filterPagination->getNext()->getURL()}">
                {/if}
            {/block}
        {/if}

        {*{if !empty($Einstellungen.template.theme.backgroundcolor) && $Einstellungen.template.theme.backgroundcolor|strlen > 1}
            <style>
                body { background-color: {$Einstellungen.template.theme.backgroundcolor}!important; }
            </style>
        {/if}*}
        {block name='layout-header-head-resources-jquery'}
            <script src="{$ShopURL}/{if empty($parentTemplateDir)}{$currentTemplateDir}{else}{$parentTemplateDir}{/if}js/jquery-3.3.1.min.js"></script>
        {/block}
        {include file='layout/header_inline_js.tpl'}
        {$dbgBarHead}
    </head>
    {/block}

    {has_boxes position='left' assign='hasLeftPanel'}
    {block name='layout-header-body-tag'}
        <body data-page="{$nSeitenTyp}" {if isset($Link) && !empty($Link->getIdentifier())} id="{$Link->getIdentifier()}"{/if}{if $isFluidTemplate} class="unboxed-layout"{/if}>
    {/block}

    {if !$bExclusive}
        {include file=$opcDir|cat:'startmenu.tpl'}

        {if isset($bAdminWartungsmodus) && $bAdminWartungsmodus}
            {alert show=true variant="warning" id="maintenance-mode" dismissible=true}{lang key='adminMaintenanceMode'}{/alert}
        {/if}

        {block name='layout-header-header'}
            {assign var=isSticky value=$Einstellungen.template.theme.static_header === 'Y'}
            <header class="d-print-none{if $isSticky} sticky-top{/if}{if $Einstellungen.template.theme.static_header === 'Y'} fixed-navbar{/if}" id="evo-nav-wrapper">

                {block name='layout-header-container-inner'}

                    <div class="container-fluid px-md-4 clearfix">
                    {block name='layout-header-branding-top-bar'}
                        <div class="top-bar pt-2 text-right d-none {if $nSeitenTyp !== $smarty.const.PAGE_BESTELLVORGANG}d-md-block{/if}">
                            {include file='layout/header_top_bar.tpl'}
                        </div>
                    {/block}

                    {block name='layout-header-category-nav'}

                        {navbar id="evo-main-nav-wrapper" toggleable=true fill=true class="navbar-expand-md accordion row py-2 py-md-0 px-0"}
                            {col id="logo" md="auto" order=2 order-md=1 class="col-auto mr-auto bg-white" style="z-index: 1;"}
                                {block name='layout-header-logo'}
                                    <div class="navbar-brand ml-lg-2" itemprop="publisher" itemscope itemtype="http://schema.org/Organization" itemid="">
                                        <span itemprop="name" class="d-none">{$meta_publisher}</span>
                                        <meta itemprop="url" content="{$ShopURL}">
                                        <meta itemprop="logo" content="{$ShopLogoURL}">

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
                            {col id="shop-nav" order=3 order-md=3 order-lg=4 class="col-auto bg-white {if $nSeitenTyp === $smarty.const.PAGE_BESTELLVORGANG}d-none{/if}" style="z-index: 1;"}
                                {block name='layout-header-branding-shop-nav'}
                                    <div class="d-flex text-right">
                                        {include file='layout/header_nav_icons.tpl'}
                                    </div>
                                {/block}
                            {/col}

                            {col md=12 order=1 order-md=5 order-xl=5 class="no-flex-grow {if $nSeitenTyp === $smarty.const.PAGE_BESTELLVORGANG}d-none{/if}"}
                                {block name='layout-header-navbar-toggler'}
                                    {navbartoggle data=["target"=>"#navbarToggler"] class="d-flex d-md-none"}
                                {/block}
                            {/col}

                            {col cols=12 col-md=auto order=5 order-xl=2 class="col-xl {if $nSeitenTyp === $smarty.const.PAGE_BESTELLVORGANG}d-none{/if}"}
                                {*categories*}
                                {block name='layout-header-include-categories-mega'}
                                    <div id="navbarToggler" class="collapse navbar-collapse" data-parent="#evo-main-nav-wrapper">
                                        {button id="scrollMenuLeft"  variant="light" class="d-none"}
                                            <i class="fas fa-chevron-left"></i>
                                        {/button}
                                        {navbarnav class="megamenu show"}
                                            {include file='snippets/categories_mega.tpl'}
                                        {/navbarnav}
                                        {button id="scrollMenuRight" variant="light" class="d-none"}
                                            <i class="fas fa-chevron-right"></i>
                                        {/button}
                                    </div>
                                {/block}
                            {/col}

                            {col order=6 order-md=2 cols=12 order-lg=3 class="col-md-auto bg-white {if $nSeitenTyp === $smarty.const.PAGE_BESTELLVORGANG}d-none{/if}"}
                                {block name='layout-header-include-header-nav-search'}
                                    {collapse id="nav-search-collapse" tag="div" data=["parent"=>"#evo-main-nav-wrapper"] class="d-md-flex mx-auto float-md-right"}
                                        {include file='layout/header_nav_search.tpl'}
                                    {/collapse}
                                {/block}
                            {/col}

                            {if $nSeitenTyp === $smarty.const.PAGE_BESTELLVORGANG}
                                {col class="d-block text-right text-md-left" order=3}
                                    <i class="fas fa-lock align-center mr-2"></i>{lang key='secureCheckout' section='checkout'}
                                {/col}
                                {col order=4 class="d-none d-md-block"}
                                    <div class="top-bar text-right">
                                        {include file='layout/header_top_bar.tpl'}
                                    </div>
                                {/col}
                            {/if}
                        {/navbar}

                    {/block}

                    </div>
                {/block}
            </header>
        {/block}
    {/if}

    {block name='layout-header-fluid-banner'}
        {assign var=isFluidBanner value=$Einstellungen.template.theme.banner_full_width === 'Y' && isset($oImageMap)}
        {if $isFluidBanner}
            {include file='snippets/banner.tpl'}
        {/if}
        {assign var=isFluidSlider value=$Einstellungen.template.theme.slider_full_width === 'Y' && isset($oSlider) && count($oSlider->getSlides()) > 0}
        {if $isFluidSlider}
            {include file='snippets/slider.tpl'}
        {/if}
    {/block}
    {block name='layout-header-main-wrapper-starttag'}
        <main id="main-wrapper" class="{if $bExclusive} exclusive{/if}{if $hasLeftPanel} aside-active{/if}">
    {/block}
    {block name='layout-header-content-all-starttags'}
        {block name='layout-header-content-wrapper-starttag'}
            <div id="content-wrapper" class="container-fluid mt-0 pt-4 {if $smarty.const.PAGE_ARTIKELLISTE === $nSeitenTyp}px-4 px-lg-7{else}px-0{/if}">
        {/block}

        {block name='layout-header-breadcrumb'}
            {container}
                {block name='layout-header-product-pagination'}
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
                {include file='layout/breadcrumb.tpl'}
            {/container}
        {/block}

        {block name='layout-header-content-row-starttag'}
            <div class="row no-gutters">
        {/block}

        {block name='layout-header-content-starttag'}
            <div id="content" class="col-12{if !$bExclusive && !empty($boxes.left|strip_tags|trim) && ($Einstellungen.template.sidebar_settings.show_sidebar_product_list === 'Y' && $smarty.const.PAGE_ARTIKELLISTE === $nSeitenTyp
            || $Einstellungen.template.sidebar_settings.show_sidebar_product_list === 'N')} col-lg-9{/if} order-lg-1 mb-6">
        {/block}

        {block name='layout-header-alert'}
            {include file='snippets/alert_list.tpl'}
        {/block}

    {/block}{* /content-all-starttags *}
{/block}
