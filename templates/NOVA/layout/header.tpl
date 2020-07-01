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
            {if !empty($meta_keywords)}
                <meta name="keywords" itemprop="keywords" content="{block name='layout-header-head-meta-keywords'}{$meta_keywords|truncate:255:'':true}{/block}">
            {/if}
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
            {if empty($parentTemplateDir)}
                {$templateDir = $currentTemplateDir}
            {else}
                {$templateDir = $parentTemplateDir}
            {/if}
            <style id="criticalCSS">
                {file_get_contents("{$currentThemeDir}{$Einstellungen.template.theme.theme_default}_crit.css")}
            </style>
            {* css *}
            {if $Einstellungen.template.general.use_minify === 'N'}
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
                <link rel="preload" href="{$ShopURL}/{$combinedCSS}" as="style" onload="this.onload=null;this.rel='stylesheet'">
                <noscript>
                    <link href="{$ShopURL}/{$combinedCSS}" rel="stylesheet">
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
                (function (w) {
                    "use strict";
                    if (!w.loadCSS) {
                        w.loadCSS = function (){};
                    }
                    var rp = loadCSS.relpreload = {};
                    rp.support                  = (function () {
                        var ret;
                        try {
                            ret = w.document.createElement("link").relList.supports("preload");
                        } catch (e) {
                            ret = false;
                        }
                        return function () {
                            return ret;
                        };
                    })();
                    rp.bindMediaToggle          = function (link) {
                        var finalMedia = link.media || "all";

                        function enableStylesheet() {
                            if (link.addEventListener) {
                                link.removeEventListener("load", enableStylesheet);
                            } else if (link.attachEvent) {
                                link.detachEvent("onload", enableStylesheet);
                            }
                            link.setAttribute("onload", null);
                            link.media = finalMedia;
                        }

                        if (link.addEventListener) {
                            link.addEventListener("load", enableStylesheet);
                        } else if (link.attachEvent) {
                            link.attachEvent("onload", enableStylesheet);
                        }
                        setTimeout(function () {
                            link.rel   = "stylesheet";
                            link.media = "only x";
                        });
                        setTimeout(enableStylesheet, 3000);
                    };

                    rp.poly = function () {
                        if (rp.support()) {
                            return;
                        }
                        var links = w.document.getElementsByTagName("link");
                        for (var i = 0; i < links.length; i++) {
                            var link = links[i];
                            if (link.rel === "preload" && link.getAttribute("as") === "style" && !link.getAttribute("data-loadcss")) {
                                link.setAttribute("data-loadcss", true);
                                rp.bindMediaToggle(link);
                            }
                        }
                    };

                    if (!rp.support()) {
                        rp.poly();

                        var run = w.setInterval(rp.poly, 500);
                        if (w.addEventListener) {
                            w.addEventListener("load", function () {
                                rp.poly();
                                w.clearInterval(run);
                            });
                        } else if (w.attachEvent) {
                            w.attachEvent("onload", function () {
                                rp.poly();
                                w.clearInterval(run);
                            });
                        }
                    }

                    if (typeof exports !== "undefined") {
                        exports.loadCSS = loadCSS;
                    }
                    else {
                        w.loadCSS = loadCSS;
                    }
                }(typeof global !== "undefined" ? global : this));
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

        <script>
            window.lazySizesConfig = window.lazySizesConfig || {};
            window.lazySizesConfig.expand  = 1;
        </script>
        <script src="{$ShopURL}/{$templateDir}js/jquery-3.4.1.min.js"></script>
        <script src="{$ShopURL}/{$templateDir}js/lazysizes.min.js"></script>

        {if $Einstellungen.template.general.use_minify === 'N'}
            {if isset($cPluginJsHead_arr)}
                {foreach $cPluginJsHead_arr as $cJS}
                    <script defer src="{$ShopURL}/{$cJS}?v={$nTemplateVersion}"></script>
                {/foreach}
            {/if}
            {foreach $cJS_arr as $cJS}
                <script defer src="{$ShopURL}/{$cJS}?v={$nTemplateVersion}"></script>
            {/foreach}
            {foreach $cPluginJsBody_arr as $cJS}
                <script defer src="{$ShopURL}/{$cJS}?v={$nTemplateVersion}"></script>
            {/foreach}
        {else}
            {foreach $minifiedJS as $item}
                <script defer src="{$ShopURL}/{$item}"></script>
            {/foreach}
        {/if}

        {if file_exists($currentTemplateDirFullPath|cat:'js/custom.js')}
            <script defer src="{$ShopURL}/{$currentTemplateDir}js/custom.js?v={$nTemplateVersion}"></script>
        {/if}

        {getUploaderLang iso=$smarty.session.currentLanguage->cISO639|default:'' assign='uploaderLang'}

        <link rel="preload" importance="low" href="{$ShopURL}/{$templateDir}themes/base/fontawesome/webfonts/fa-solid-900.woff2" as="font" crossorigin/>
        <link rel="preload" importance="low" href="{$ShopURL}/{$templateDir}themes/base/fontawesome/webfonts/fa-regular-400.woff2" as="font" crossorigin/>
        <link rel="preload" href="{$ShopURL}/{$templateDir}js/app/globals.js" as="script" crossorigin>
        <link rel="preload" href="{$ShopURL}/{$templateDir}js/app/snippets/form-counter.js" as="script" crossorigin>
        <link rel="preload" href="{$ShopURL}/{$templateDir}js/app/plugins/navscrollbar.js" as="script" crossorigin>
        <link rel="preload" href="{$ShopURL}/{$templateDir}js/app/plugins/tabdrop.js" as="script" crossorigin>
        <link rel="preload" href="{$ShopURL}/{$templateDir}js/app/views/header.js" as="script" crossorigin>
        <link rel="preload" href="{$ShopURL}/{$templateDir}js/app/views/productdetails.js" as="script" crossorigin>
        {if !empty($oUploadSchema_arr)}
            <script defer src="{$ShopURL}/{$templateDir}js/fileinput/fileinput.min.js"></script>
            <script defer src="{$ShopURL}/{$templateDir}js/fileinput/themes/fas/theme.min.js"></script>
            <script defer src="{$ShopURL}/{$templateDir}js/fileinput/locales/{$uploaderLang}.js"></script>
        {/if}
        {if $Einstellungen.preisverlauf.preisverlauf_anzeigen === 'Y' && !empty($bPreisverlauf)}
            <script defer src="{$ShopURL}/{$templateDir}js/Chart.bundle.min.js"></script>
        {/if}
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
            {block name='layout-header-branding-top-bar'}
                {if !$isMobile}
                    <div id="header-top-bar" class="d-none {if $nSeitenTyp !== $smarty.const.PAGE_BESTELLVORGANG}d-lg-flex{/if}">
                        <div class="container-fluid container-fluid-xl {if $nSeitenTyp !== $smarty.const.PAGE_BESTELLVORGANG}d-lg-flex flex-row-reverse{/if}">
                            {include file='layout/header_top_bar.tpl'}
                        </div>
                    </div>
                {/if}
            {/block}
            <header class="d-print-none {if !$isMobile || $Einstellungen.template.theme.mobile_search_type !== 'fixed'}sticky-top{/if} fixed-navbar" id="jtl-nav-wrapper">
                {block name='layout-header-container-inner'}
                    <div class="container-fluid container-fluid-xl">
                    {block name='layout-header-category-nav'}
                        {navbar toggleable=true fill=true type="expand-lg" class="justify-content-start {if $nSeitenTyp === $smarty.const.PAGE_BESTELLVORGANG}align-items-center{else}align-items-lg-end{/if} pt-0 px-0 pb-lg-0"}
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
                                    {link class="navbar-brand mr-lg-6" href=$ShopURL title=$Einstellungen.global.global_shopname}
                                        {if isset($ShopLogoURL)}
                                            {image src=$ShopLogoURL
                                                alt=$Einstellungen.global.global_shopname
                                                style="{if $ShopLogoURL|strpos:'.svg' !== false}height: 100px;{/if}"
                                            }
                                        {else}
                                            <span class="h1">{$Einstellungen.global.global_shopname}</span>
                                        {/if}
                                    {/link}
                                </div>
                            {/block}

                            {block name='layout-header-search'}
                                {if $Einstellungen.template.theme.mobile_search_type === 'fixed'}
                                    <div class="d-lg-none{if !$isTablet} container-fluid container-fluid-xl py-2 order-1 bg-white{else} px-4 py-2 flex-grow-1{/if}">
                                        {include file='snippets/search_form.tpl' id='search-header-mobile-top'}
                                    </div>
                                {/if}
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
                                        {block name='layout-header-branding-shop-nav-language'}
                                            {if isset($smarty.session.Sprachen) && $smarty.session.Sprachen|@count > 1}
                                                {navitemdropdown
                                                class="language-dropdown d-flex d-lg-none"
                                                right=true
                                                text="
                                                    {foreach $smarty.session.Sprachen as $language}
                                                        {if $language->kSprache == $smarty.session.kSprache}
                                                            {$language->iso639|upper}
                                                        {/if}
                                                    {/foreach}"
                                                }
                                                    {foreach $smarty.session.Sprachen as $language}
                                                        {dropdownitem href="{$language->cURL}" rel="nofollow" active=($language->kSprache == $smarty.session.kSprache)}
                                                        {$language->iso639|upper}
                                                        {/dropdownitem}
                                                    {/foreach}
                                                {/navitemdropdown}
                                            {/if}
                                        {/block}
                                        {include file='layout/header_nav_icons.tpl'}
                                    {/nav}
                                {/block}

                                {*categories*}
                                {block name='layout-header-include-categories-mega'}
                                    <div id="mainNavigation" class="collapse navbar-collapse nav-scrollbar mr-lg-3">
                                        <div class="nav-mobile-header px-3 d-lg-none">
                                            {row class="align-items-center"}
                                                {col}
                                                    <button class="navbar-toggler collapsed" type="button" data-toggle="collapse" data-target="#mainNavigation" aria-controls="mainNavigation" aria-expanded="false" aria-label="Toggle navigation">
                                                        <span class="navbar-toggler-icon"></span>
                                                    </button>
                                                {/col}
                                                {col class="col-auto ml-auto"}
                                                    <span class="nav-offcanvas-title">{lang key='menuName'}</span>
                                                    {link href="#" class="nav-offcanvas-title d-none" data=["menu-back"=>""]}
                                                        <span class="fas fa-chevron-left mr-2"></span>
                                                        <span>{lang key='back'}</span>
                                                    {/link}
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
            {block name='layout-header-search-fixed'}
                {if $Einstellungen.template.theme.mobile_search_type === 'fixed' && $isMobile}
                    <div class="container-fluid container-fluid-xl fixed-search py-2 fixed-top smoothscroll-top-search d-lg-none d-none">
                        {include file='snippets/search_form.tpl' id='search-header-mobile-fixed'}
                    </div>
                {/if}
            {/block}
        {/block}
    {/if}

    {block name='layout-header-main-wrapper-starttag'}
        <main id="main-wrapper" class="{if $bExclusive} exclusive{/if}{if $hasLeftPanel} aside-active{/if}">
        {opcMountPoint id='opc_before_main' inContainer=false}
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
                 class="{if $smarty.const.PAGE_ARTIKELLISTE === $nSeitenTyp}
                            container-fluid container-fluid-xl
                        {/if} mt-0 {if $isFluidBanner || $isFluidSlider}pt-3{else}pt-sm-3 pt-5 pt-lg-7{/if}">
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
