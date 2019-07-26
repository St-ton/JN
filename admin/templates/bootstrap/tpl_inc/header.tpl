{assign var=bForceFluid value=$bForceFluid|default:false}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>{__('shopTitle')}</title>
    {assign var=urlPostfix value='?v='|cat:$adminTplVersion}
    <link type="image/x-icon" href="{$faviconAdminURL}" rel="icon">
    {$admin_css}
    <link type="text/css" rel="stylesheet" href="{$PFAD_CODEMIRROR}lib/codemirror.css{$urlPostfix}">
    <link type="text/css" rel="stylesheet" href="{$PFAD_CODEMIRROR}addon/hint/show-hint.css{$urlPostfix}">
    <link type="text/css" rel="stylesheet" href="{$PFAD_CODEMIRROR}addon/display/fullscreen.css{$urlPostfix}">
    <link type="text/css" rel="stylesheet" href="{$PFAD_CODEMIRROR}addon/scroll/simplescrollbars.css{$urlPostfix}">
    {$admin_js}
    <script src="{$PFAD_CKEDITOR}ckeditor.js{$urlPostfix}"></script>
    <script src="{$PFAD_CODEMIRROR}lib/codemirror.js{$urlPostfix}"></script>
    <script src="{$PFAD_CODEMIRROR}addon/hint/show-hint.js{$urlPostfix}"></script>
    <script src="{$PFAD_CODEMIRROR}addon/hint/sql-hint.js{$urlPostfix}"></script>
    <script src="{$PFAD_CODEMIRROR}addon/scroll/simplescrollbars.js{$urlPostfix}"></script>
    <script src="{$PFAD_CODEMIRROR}addon/display/fullscreen.js{$urlPostfix}"></script>
    <script src="{$PFAD_CODEMIRROR}mode/css/css.js{$urlPostfix}"></script>
    <script src="{$PFAD_CODEMIRROR}mode/javascript/javascript.js{$urlPostfix}"></script>
    <script src="{$PFAD_CODEMIRROR}mode/xml/xml.js{$urlPostfix}"></script>
    <script src="{$PFAD_CODEMIRROR}mode/php/php.js{$urlPostfix}"></script>
    <script src="{$PFAD_CODEMIRROR}mode/htmlmixed/htmlmixed.js{$urlPostfix}"></script>
    <script src="{$PFAD_CODEMIRROR}mode/smarty/smarty.js{$urlPostfix}"></script>
    <script src="{$PFAD_CODEMIRROR}mode/smartymixed/smartymixed.js{$urlPostfix}"></script>
    <script src="{$PFAD_CODEMIRROR}mode/sql/sql.js{$urlPostfix}"></script>
    <script src="{$URL_SHOP}/{$PFAD_ADMIN}{$currentTemplateDir}js/codemirror_init.js{$urlPostfix}"></script>
    <script>
        var bootstrapButton = $.fn.button.noConflict();
        $.fn.bootstrapBtn = bootstrapButton;
        setJtlToken('{$smarty.session.jtl_token}');
    </script>

    <script type="text/javascript" src="{$URL_SHOP}/{$PFAD_ADMIN}{$currentTemplateDir}js/fileinput/locales/{$language|mb_substr:0:2}.js"></script>
</head>
<body>
{if $account !== false && isset($smarty.session.loginIsValid) && $smarty.session.loginIsValid === true}
    {getCurrentPage assign='currentPage'}
    <div class="spinner"></div>
    <div id="page-wrapper" class="backend-wrapper hidden disable-transitions{if $currentPage === 'index' || $currentPage === 'status'} dashboard{/if}">
        {include file='tpl_inc/backend_sidebar.tpl'}
        <div class="backend-main sidebar-offset">
            <div id="topbar" class="backend-navbar row mx-0 align-items-center topbar flex-nowrap searching">
                <div class="col search">
                    {include file='tpl_inc/backend_search.tpl'}
                </div>
                <div class="col-auto ml-auto">
                    <ul class="nav align-items-center">
                        <li class="nav-item dropdown mr-lg-4" id="favs-drop">
                            {include file="tpl_inc/favs_drop.tpl"}
                        </li>
                        <li class="nav-item dropdown">
                            <a href="#" class="nav-link text-dark-gray px-2" data-toggle="dropdown">
                                <span class="fal fa-map-marker-question fa-fw"></span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <span class="dropdown-header">Hilfecenter</span>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="https://jtl-url.de/shopschritte" target="_blank" rel="noopener">
                                    {__('firstSteps')}
                                </a>
                                <a class="dropdown-item" href="https://jtl-url.de/shopguide" target="_blank" rel="noopener">
                                    {__('jtlGuide')}
                                </a>
                                <a class="dropdown-item" href="https://forum.jtl-software.de" target="_blank" rel="noopener">
                                    {__('jtlForum')}
                                </a>
                                <a class="dropdown-item" href="https://www.jtl-software.de/Training" target="_blank" rel="noopener">
                                    {__('training')}
                                </a>
                                <a class="dropdown-item" href="https://www.jtl-software.de/Servicepartner" target="_blank" rel="noopener">
                                    {__('servicePartners')}
                                </a>
                            </div>
                        </li>
                        <li class="nav-item dropdown" id="notify-drop">{include file="tpl_inc/notify_drop.tpl"}</li>
                        <li class="nav-item dropdown">
                            <a href="#" class="dropdown-toggle parent btn-toggle" data-toggle="dropdown">
                                {$languageName}
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">
                                {foreach $languages as $tag => $langName}
                                    {if $language !== $tag}
                                        <a class="dropdown-item" href="{strip}benutzerverwaltung.php
                                                ?token={$smarty.session.jtl_token}
                                                &action=quick_change_language
                                                &language={$tag}{/strip}">
                                            {$langName}
                                        </a>
                                    {/if}
                                {/foreach}
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="col-auto border-left border-dark-gray">
                    <div class="dropdown avatar">
                        <button class="btn btn-link text-decoration-none dropdown-toggle p-0" data-toggle="dropdown">
                            <img src="{gravatarImage email=$account->cMail}" class="img-circle">
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item link-shop" href="{$URL_SHOP}" title="Zum Shop">
                                <i class="fa fa-shopping-cart"></i> {__('goShop')}
                            </a>
                            <a class="dropdown-item link-logout" href="logout.php?token={$smarty.session.jtl_token}"
                               title="{__('logout')}">
                                <i class="fa fa-sign-out"></i> {__('logout')}
                            </a>
                        </div>
                    </div>
                </div>

            </div>
            <div class="backend-content" id="content_wrapper">

            {include file='snippets/alert_list.tpl'}
{/if}
