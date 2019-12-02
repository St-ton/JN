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
            {elseif $nSeitenTyp === $smarty.const.PAGE_NEWSDETAIL && !empty($newsItem->getPreviewImage())}
                <meta itemprop="image" content="{$imageBaseURL}{$newsItem->getPreviewImage()}" />
                <meta property="og:image" content="{$imageBaseURL}{$newsItem->getPreviewImage()}" />
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
            {include file='layout/header_inline_css.tpl'}
            {* css *}
            {if !isset($Einstellungen.template.general.use_minify) || $Einstellungen.template.general.use_minify === 'N'}
                {foreach $cCSS_arr as $cCSS}
                    <link rel="preload" href="{$ShopURL}/{$cCSS}?v={$nTemplateVersion}" as="style"
                          onload="this.onload=null;this.rel='stylesheet'">
                {/foreach}
                {if isset($cPluginCss_arr)}
                    {foreach $cPluginCss_arr as $cCSS}
                        <link rel="preload" href="{$ShopURL}/{$cCSS}?v={$nTemplateVersion}" as="style"
                              onload="this.onload=null;this.rel='stylesheet'">
                    {/foreach}
                {/if}

                <noscript>
                    {foreach $cCSS_arr as $cCSS}
                        <link rel="stylesheet" href="{$ShopURL}/{$cCSS}?v={$nTemplateVersion}">
                    {/foreach}
                    {if isset($cPluginCss_arr)}
                        {foreach $cPluginCss_arr as $cCSS}
                            <link href="{$ShopURL}/{$cCSS}?v={$nTemplateVersion}" rel="stylesheet">
                        {/foreach}
                    {/if}
                </noscript>
            {else}
                <link rel="preload" href="{$ShopURL}/asset/{$Einstellungen.template.theme.theme_default}.css{if isset($cPluginCss_arr) && $cPluginCss_arr|@count > 0},plugin_css{/if}?v={$nTemplateVersion}" as="style" onload="this.onload=null;this.rel='stylesheet'">
                <noscript>
                    <link href="{$ShopURL}/asset/{$Einstellungen.template.theme.theme_default}.css{if isset($cPluginCss_arr) && $cPluginCss_arr|@count > 0},plugin_css{/if}?v={$nTemplateVersion}" rel="stylesheet">
                </noscript>
            {/if}

            {if \JTL\Shop::isAdmin() && $opc->isEditMode() === false && $opc->isPreviewMode() === false}
                <link rel="preload" href="{$ShopURL}/admin/opc/css/startmenu.css" as="style"
                      onload="this.onload=null;this.rel='stylesheet'">
                <noscript>
                    <link type="text/css" href="{$ShopURL}/admin/opc/css/startmenu.css" rel="stylesheet">
                </noscript>
            {/if}
            {foreach $opcPageService->getCurPage()->getCssList($opc->isEditMode()) as $cssFile => $cssTrue}
                <link rel="preload" href="{$cssFile}" as="style" data-opc-portlet-css-link="true"
                      onload="this.onload=null;this.rel='stylesheet'">
                <noscript>
                    <link rel="stylesheet" href="{$cssFile}">
                </noscript>
            {/foreach}
            <script>

                /*! loadCSS rel=preload polyfill. [c]2017 Filament Group, Inc. MIT License */
                (function( w ){
                    "use strict";
                    // rel=preload support test
                    if( !w.loadCSS ){
                        w.loadCSS = function(){};
                    }
                    // define on the loadCSS obj
                    var rp = loadCSS.relpreload = {};
                    // rel=preload feature support test
                    // runs once and returns a function for compat purposes
                    rp.support = (function(){
                        var ret;
                        try {
                            ret = w.document.createElement( "link" ).relList.supports( "preload" );
                        } catch (e) {
                            ret = false;
                        }
                        return function(){
                            return ret;
                        };
                    })();

                    // if preload isn't supported, get an asynchronous load by using a non-matching media attribute
                    // then change that media back to its intended value on load
                    rp.bindMediaToggle = function( link ){
                        // remember existing media attr for ultimate state, or default to 'all'
                        var finalMedia = link.media || "all";

                        function enableStylesheet(){
                            // unbind listeners
                            if( link.addEventListener ){
                                link.removeEventListener( "load", enableStylesheet );
                            } else if( link.attachEvent ){
                                link.detachEvent( "onload", enableStylesheet );
                            }
                            link.setAttribute( "onload", null );
                            link.media = finalMedia;
                        }

                        // bind load handlers to enable media
                        if( link.addEventListener ){
                            link.addEventListener( "load", enableStylesheet );
                        } else if( link.attachEvent ){
                            link.attachEvent( "onload", enableStylesheet );
                        }

                        // Set rel and non-applicable media type to start an async request
                        // note: timeout allows this to happen async to let rendering continue in IE
                        setTimeout(function(){
                            link.rel = "stylesheet";
                            link.media = "only x";
                        });
                        // also enable media after 3 seconds,
                        // which will catch very old browsers (android 2.x, old firefox) that don't support onload on link
                        setTimeout( enableStylesheet, 3000 );
                    };

                    // loop through link elements in DOM
                    rp.poly = function(){
                        // double check this to prevent external calls from running
                        if( rp.support() ){
                            return;
                        }
                        var links = w.document.getElementsByTagName( "link" );
                        for( var i = 0; i < links.length; i++ ){
                            var link = links[ i ];
                            // qualify links to those with rel=preload and as=style attrs
                            if( link.rel === "preload" && link.getAttribute( "as" ) === "style" && !link.getAttribute( "data-loadcss" ) ){
                                // prevent rerunning on link
                                link.setAttribute( "data-loadcss", true );
                                // bind listeners to toggle media back
                                rp.bindMediaToggle( link );
                            }
                        }
                    };

                    // if unsupported, run the polyfill
                    if( !rp.support() ){
                        // run once at least
                        rp.poly();

                        // rerun poly on an interval until onload
                        var run = w.setInterval( rp.poly, 500 );
                        if( w.addEventListener ){
                            w.addEventListener( "load", function(){
                                rp.poly();
                                w.clearInterval( run );
                            } );
                        } else if( w.attachEvent ){
                            w.attachEvent( "onload", function(){
                                rp.poly();
                                w.clearInterval( run );
                            } );
                        }
                    }

                    // commonjs
                    if( typeof exports !== "undefined" ){
                        exports.loadCSS = loadCSS;
                    }
                    else {
                        w.loadCSS = loadCSS;
                    }
                }( typeof global !== "undefined" ? global : this ) );
            </script>
            {* RSS *}
            {if isset($Einstellungen.rss.rss_nutzen) && $Einstellungen.rss.rss_nutzen === 'Y'}
                <link rel="alternate" type="application/rss+xml" title="Newsfeed {$Einstellungen.global.global_shopname}"
                      href="{$ShopURL}/rss.xml">
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
        {$dbgBarHead}

        {if empty($parentTemplateDir)}
            {$templateDir = $currentTemplateDir}
        {else}
            {$templateDir = $parentTemplateDir}
        {/if}

        <script src="{$ShopURL}/{$templateDir}js/jquery-3.4.1.min.js"></script>

        {if !isset($Einstellungen.template.general.use_minify) || $Einstellungen.template.general.use_minify === 'N'}
            {if isset($cPluginJsHead_arr)}
                {foreach $cPluginJsHead_arr as $cJS}
                    <script defer src="{$ShopURL}/{$cJS}?v={$nTemplateVersion}"></script>
                {/foreach}
            {/if}
        {else}
            {if isset($cPluginJsHead_arr) && $cPluginJsHead_arr|@count > 0}
                <script defer src="{$ShopURL}/asset/plugin_js_head?v={$nTemplateVersion}"></script>
            {/if}
        {/if}

        {if !isset($Einstellungen.template.general.use_minify) || $Einstellungen.template.general.use_minify === 'N'}
            {foreach $cJS_arr as $cJS}
                <script defer src="{$ShopURL}/{$cJS}?v={$nTemplateVersion}"></script>
            {/foreach}
            {if isset($cPluginJsBody_arr)}
                {foreach $cPluginJsBody_arr as $cJS}
                    <script defer src="{$ShopURL}/{$cJS}?v={$nTemplateVersion}"></script>
                {/foreach}
            {/if}
        {else}
            <script defer src="{$ShopURL}/asset/jtl3.js?v={$nTemplateVersion}"></script>
            {if isset($cPluginJsBody_arr) && $cPluginJsBody_arr|@count > 0}
                <script defer src="{$ShopURL}/asset/plugin_js_body?v={$nTemplateVersion}"></script>
            {/if}
        {/if}

        {$customJSPath = $currentTemplateDir|cat:'/js/custom.js'}
        {if file_exists($customJSPath)}
            <script defer src="{$ShopURL}/{$customJSPath}?v={$nTemplateVersion}"></script>
        {/if}

        {getUploaderLang iso=$smarty.session.currentLanguage->cISO639|default:'' assign='uploaderLang'}

        <script defer src="{$ShopURL}/{$templateDir}js/fileinput/fileinput.min.js"></script>
        <script defer src="{$ShopURL}/{$templateDir}js/fileinput/themes/fas/theme.min.js"></script>
        <script defer src="{$ShopURL}/{$templateDir}js/fileinput/locales/{$uploaderLang}.js"></script>
        <script defer type="module" src="{$ShopURL}/{$templateDir}js/app/app.js"></script>
    </head>
    {/block}

    {has_boxes position='left' assign='hasLeftPanel'}
    {block name='layout-header-body-tag'}
        <body class="{if $Einstellungen.template.theme.button_animated === 'Y'}btn-animated{/if}"
              data-page="{$nSeitenTyp}"
              {if isset($Link) && !empty($Link->getIdentifier())} id="{$Link->getIdentifier()}"{/if}>
    {/block}

    {if !$bExclusive}
        {include file=$opcDir|cat:'tpl/startmenu.tpl'}

        {if $bAdminWartungsmodus}
            {block name='layout-header-maintenance-alert'}
                {alert show=true variant="warning" id="maintenance-mode" dismissible=true}{lang key='adminMaintenanceMode'}{/alert}
            {/block}
        {/if}
        {if $smarty.const.SAFE_MODE === true}
            {block name='layout-header-safemode-alert'}
                {alert show=true variant="warning" id="safe-mode" dismissible=true}{lang key='safeModeActive'}{/alert}
            {/block}
        {/if}

        {block name='layout-header-header'}
            {assign var=isSticky value=$Einstellungen.template.theme.static_header === 'Y'}
            <header class="d-print-none{if $isSticky} sticky-top{/if}{if $Einstellungen.template.theme.static_header === 'Y'} fixed-navbar{/if}" id="evo-nav-wrapper">

                {block name='layout-header-container-inner'}
                    <div class="container-fluid container-fluid-xl">
                    {block name='layout-header-branding-top-bar'}
                        {if !$device->isMobile()}
                            {row class="mb-2 d-none {if $nSeitenTyp !== $smarty.const.PAGE_BESTELLVORGANG}d-lg-flex{/if}"}
                                {col class='col-auto ml-auto'}
                                    {include file='layout/header_top_bar.tpl'}
                                {/col}
                            {/row}
                        {/if}
                    {/block}

                    {block name='layout-header-category-nav'}
                        {navbar  toggleable=true fill=true type="expand-lg " class="justify-content-start {if $nSeitenTyp === $smarty.const.PAGE_BESTELLVORGANG}align-items-center{else}align-items-lg-end{/if} px-0 pb-lg-0"}
                            {block name='layout-header-navbar-toggle'}
                                <button class="navbar-toggler mr-3 collapsed {if $nSeitenTyp === $smarty.const.PAGE_BESTELLVORGANG}d-none{/if}" type="button" data-toggle="collapse" data-target="#mainNavigation" aria-controls="mainNavigation" aria-expanded="false" aria-label="Toggle navigation">
                                    <span class="navbar-toggler-icon"></span>
                                </button>
                            {/block}

                            {block name='layout-header-logo'}
                                <div id="logo" itemprop="publisher" itemscope itemtype="http://schema.org/Organization">
                                    <span itemprop="name" class="d-none">{$meta_publisher}</span>
                                    <meta itemprop="url" content="{$ShopURL}">
                                    <meta itemprop="logo" content="{$ShopLogoURL}">
                                    {link class="navbar-brand {if $nSeitenTyp !== $smarty.const.PAGE_BESTELLVORGANG}mb-lg-3{/if} mr-lg-6" href=$ShopURL title=$Einstellungen.global.global_shopname}
                                        {if isset($ShopLogoURL)}
                                            {image src=$ShopLogoURL
                                            alt=$Einstellungen.global.global_shopname
                                            height=53}
                                        {else}
                                            <span class="h1">{$Einstellungen.global.global_shopname}</span>
                                        {/if}
                                    {/link}
                                </div>
                            {/block}

                            {if $nSeitenTyp === $smarty.const.PAGE_BESTELLVORGANG}
                                {block name='layout-header-secure-checkout'}
                                    <div class="ml-auto ml-lg-0">
                                        {block name='layout-header-secure-checkout-title'}
                                            <i class="fas fa-lock align-center mr-2"></i>{lang key='secureCheckout' section='checkout'}
                                        {/block}
                                    </div>
                                    <div class="ml-auto d-none d-lg-block">
                                        {block name='layout-header-secure-include-header-top-bar'}
                                            {include file='layout/header_top_bar.tpl'}
                                        {/block}
                                    </div>
                                {/block}
                            {else}
                                {block name='layout-header-branding-shop-nav'}
                                    {nav id="shop-nav" right=true class="nav-right ml-auto order-lg-last align-items-center flex-shrink-0"}
                                        {include file='layout/header_nav_icons.tpl'}
                                    {/nav}
                                {/block}

                                {*categories*}
                                {block name='layout-header-include-categories-mega'}
                                    <div id="mainNavigation" class="collapse navbar-collapse nav-scrollbar mr-lg-5">
                                        <div class="nav-mobile-header px-3 d-lg-none">
                                            {row class="align-items-center"}
                                                {col}
                                                    <span class="nav-offcanvas-title">{lang key='menuName'}</span>
                                                    {link href="#" class="nav-offcanvas-title d-none" data=["menu-back"=>""]}
                                                        <span class="fas fa-chevron-left mr-2"></span>
                                                        <span>{lang key='back'}</span>
                                                    {/link}
                                                {/col}
                                                {col class="col-auto ml-auto"}
                                                    <button class="navbar-toggler collapsed" type="button" data-toggle="collapse" data-target="#mainNavigation" aria-controls="mainNavigation" aria-expanded="false" aria-label="Toggle navigation">
                                                        <span class="navbar-toggler-icon"></span>
                                                    </button>
                                                {/col}
                                            {/row}
                                            <hr class="my-0" />
                                        </div>
                                        <div class="nav-mobile-body">
                                            {navbarnav class="nav-scrollbar-inner mr-auto"}
                                                {include file='snippets/categories_mega.tpl'}
                                            {/navbarnav}
                                        </div>
                                    </div>
                                {/block}
                            {/if}
                        {/navbar}
                    {/block}
                    </div>
                {/block}
            </header>
        {/block}
    {/if}

    {block name='layout-header-main-wrapper-starttag'}
        <main id="main-wrapper" class="{if $bExclusive} exclusive{/if}{if $hasLeftPanel} aside-active{/if}">
        {opcMountPoint id='opc_before_main'}
    {/block}

    {block name='layout-header-fluid-banner'}
        {assign var=isFluidBanner value=$Einstellungen.template.theme.banner_full_width === 'Y' && isset($oImageMap)}
        {if $isFluidBanner}
            {block name='layout-header-fluid-banner-include-banner'}
                {include file='snippets/banner.tpl' isFluid=true}
            {/block}
        {/if}
        {assign var=isFluidSlider value=$Einstellungen.template.theme.slider_full_width === 'Y' && isset($oSlider) && count($oSlider->getSlides()) > 0}
        {if $isFluidSlider}
            {block name='layout-header-fluid-banner-include-slider'}
                {include file='snippets/slider.tpl' isFluid=true}
            {/block}
        {/if}
    {/block}

    {block name='layout-header-content-all-starttags'}
        {block name='layout-header-content-wrapper-starttag'}
            <div id="content-wrapper"
                 class="{if !$bExclusive && !empty($boxes.left|strip_tags|trim) && $smarty.const.PAGE_ARTIKELLISTE === $nSeitenTyp}
                            container-fluid container-fluid-xl
                        {/if} mt-0 {if $isFluidBanner || $isFluidSlider}pt-3{else}pt-5 pt-lg-7{/if}">
        {/block}

        {block name='layout-header-breadcrumb'}
            {container fluid=($smarty.const.PAGE_ARTIKELLISTE === $nSeitenTyp) class="{if $smarty.const.PAGE_ARTIKELLISTE === $nSeitenTyp}px-0{/if}"}
                {include file='layout/breadcrumb.tpl'}
            {/container}
        {/block}

        {block name='layout-header-content-starttag'}
            <div id="content" class="pb-6">
        {/block}

        {if !$bExclusive && !empty($boxes.left|strip_tags|trim) && $smarty.const.PAGE_ARTIKELLISTE === $nSeitenTyp}
            {block name='layout-header-content-productlist-starttags'}
                <div class="row">
                    <div class="col-lg-8 col-xl-9 ml-auto order-lg-1">
            {/block}
        {/if}

        {block name='layout-header-alert'}
            {include file='snippets/alert_list.tpl'}
        {/block}

    {/block}{* /content-all-starttags *}
{/block}
