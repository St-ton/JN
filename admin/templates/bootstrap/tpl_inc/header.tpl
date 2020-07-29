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
    <script src="{$templateBaseURL}js/codemirror_init.js{$urlPostfix}"></script>
    <script>
        var bootstrapButton = $.fn.button.noConflict();
        $.fn.bootstrapBtn = bootstrapButton;
        setJtlToken('{$smarty.session.jtl_token}');

        function switchAdminLang(tag)
        {
            event.target.href = `{strip}
                benutzerverwaltung.php
                ?token={$smarty.session.jtl_token}
                &action=quick_change_language
                &language=` + tag + `
                &referer=` +  encodeURIComponent(window.location.href){/strip};
        }
    </script>

    <script type="text/javascript"
            src="{$templateBaseURL}js/fileinput/locales/{$language|mb_substr:0:2}.js"></script>
    <script type="module" src="{$templateBaseURL}js/app/app.js"></script>
    {include file='snippets/selectpicker.tpl'}
</head>
<body>
{if $account !== false && isset($smarty.session.loginIsValid) && $smarty.session.loginIsValid === true}
    {getCurrentPage assign='currentPage'}
    <div class="spinner"></div>
    <div id="page-wrapper" class="backend-wrapper hidden disable-transitions{if $currentPage === 'index' || $currentPage === 'status'} dashboard{/if}">
        {if !$hasPendingUpdates && $wizardDone}
            {include file='tpl_inc/backend_sidebar.tpl'}
        {/if}
        <div class="backend-main {if $wizardDone}sidebar-offset{/if}">
            {if !$hasPendingUpdates}
            <div id="topbar" class="backend-navbar row mx-0 align-items-center topbar flex-nowrap">
                <div class="col search px-0 px-md-3">
                    {if $wizardDone}
                        {include file='tpl_inc/backend_search.tpl'}
                    {/if}
                </div>
                <div class="col-auto ml-auto px-2">
                    <ul class="nav align-items-center">
                        {if $wizardDone}
                            <li class="nav-item dropdown mr-md-3" id="favs-drop">
                                {include file="tpl_inc/favs_drop.tpl"}
                            </li>
                            <li class="nav-item dropdown fa-lg">
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
                            <li class="nav-item dropdown fa-lg" id="notify-drop">{include file="tpl_inc/notify_drop.tpl"}</li>
                            <li class="nav-item dropdown fa-lg" id="updates-drop">{include file="tpl_inc/updates_drop.tpl"}</li>
                        {/if}
                        <li class="nav-item dropdown">
                            <a href="#" class="nav-link dropdown-toggle parent btn-toggle" data-toggle="dropdown">
                                <i class="fal fa-language d-sm-none"></i> <span class="d-sm-block d-none">{$languageName}</span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">
                                {foreach $languages as $tag => $langName}
                                    {if $language !== $tag}
                                        <a class="dropdown-item" onclick="switchAdminLang('{$tag}')" href="#">
                                            {$langName}
                                        </a>
                                    {/if}
                                {/foreach}
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="col-auto border-left border-dark-gray px-0 px-md-3">
                    <div class="dropdown avatar">
                        <button class="btn btn-link text-decoration-none dropdown-toggle p-0" data-toggle="dropdown">
                            <img src="{getAvatar account=$account}" class="img-circle">
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
                <div class="opaque-background"></div>
            </div>
            {/if}
            <div class="backend-content" id="content_wrapper">

            {include file='snippets/alert_list.tpl'}
{/if}
