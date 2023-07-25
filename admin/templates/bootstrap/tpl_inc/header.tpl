{assign var=bForceFluid value=$bForceFluid|default:false}
{assign var=themeMode value=$themeMode|default:'auto'}
<!DOCTYPE html>
<html lang="de" class="theme-{$themeMode}">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>{__('shopTitle')}</title>
    {assign var=urlPostfix value='?v='|cat:$adminTplVersion}
    <link type="image/x-icon" href="{$faviconAdminURL}" rel="icon">
    {$admin_css}
    {$cm = $shopURL|cat:'/'|cat:$smarty.const.PFAD_CODEMIRROR}
    <link type="text/css" rel="stylesheet" href="{$cm}lib/codemirror.css{$urlPostfix}">
    <link type="text/css" rel="stylesheet" href="{$cm}addon/hint/show-hint.css{$urlPostfix}">
    <link type="text/css" rel="stylesheet" href="{$cm}addon/display/fullscreen.css{$urlPostfix}">
    <link type="text/css" rel="stylesheet" href="{$cm}addon/scroll/simplescrollbars.css{$urlPostfix}">
    {$admin_js}
    <script src="{$shopURL}/{$smarty.const.PFAD_CKEDITOR}/ckeditor.js{$urlPostfix}"></script>
    <script src="{$cm}lib/codemirror.js{$urlPostfix}"></script>
    <script src="{$cm}addon/hint/show-hint.js{$urlPostfix}"></script>
    <script src="{$cm}addon/hint/sql-hint.js{$urlPostfix}"></script>
    <script src="{$cm}addon/scroll/simplescrollbars.js{$urlPostfix}"></script>
    <script src="{$cm}addon/display/fullscreen.js{$urlPostfix}"></script>
    <script src="{$cm}mode/css/css.js{$urlPostfix}"></script>
    <script src="{$cm}mode/javascript/javascript.js{$urlPostfix}"></script>
    <script src="{$cm}mode/xml/xml.js{$urlPostfix}"></script>
    <script src="{$cm}mode/php/php.js{$urlPostfix}"></script>
    <script src="{$cm}mode/htmlmixed/htmlmixed.js{$urlPostfix}"></script>
    <script src="{$cm}mode/sass/sass.js{$urlPostfix}"></script>
    <script src="{$cm}mode/smarty/smarty.js{$urlPostfix}"></script>
    <script src="{$cm}mode/smartymixed/smartymixed.js{$urlPostfix}"></script>
    <script src="{$cm}mode/sql/sql.js{$urlPostfix}"></script>
    <script src="{$templateBaseURL}js/codemirror_init.js{$urlPostfix}"></script>
    <script>
        var bootstrapButton = $.fn.button.noConflict();
        $.fn.bootstrapBtn = bootstrapButton;
        setJtlToken('{$smarty.session.jtl_token}');
        setBackendURL('{$adminURL}/');

        function switchAdminLang(tag)
        {
            event.target.href = `{strip}
                {$adminURL}/users?token={$smarty.session.jtl_token}
                &action=quick_change_language
                &language=` + tag + `
                &referer=` +  encodeURIComponent(window.location.href){/strip};
        }
    </script>

    <script type="text/javascript"
            src="{$templateBaseURL}js/fileinput/locales/{mb_substr($language, 0, 2)}.js"></script>
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
        <div class="backend-main {if !$hasPendingUpdates && $wizardDone}sidebar-offset{/if}">
            {if $smarty.const.SAFE_MODE}
            <div class="alert alert-warning fade show" role="alert">
                <i class="fal fa-exclamation-triangle mr-2"></i>
                {__('Safe mode enabled.')}
                <a href="./?safemode=off" class="btn btn-light"><span class="fas fa-exclamation-circle mr-0 mr-lg-2"></span><span>{__('deactivate')}</span></a>
            </div>
            {/if}
            <div id="topbar" class="backend-navbar row mx-0 align-items-center topbar flex-nowrap">
                {if !$hasPendingUpdates && $wizardDone}
                <div class="col search px-0 px-md-3">
                    {include file='tpl_inc/backend_search.tpl'}
                </div>
                {/if}
                <div class="col-auto ml-auto px-2">
                    <ul class="nav align-items-center">
                        {if !$hasPendingUpdates && $wizardDone}
                            <li class="nav-item dropdown mr-md-3" id="favs-drop">
                                {include file="tpl_inc/favs_drop.tpl"}
                            </li>
                            <li class="nav-item dropdown fa-lg">
                                <a href="#" class="nav-link text-dark-gray px-2" data-toggle="dropdown">
                                    <span class="fal fa-map-marker-question fa-fw"></span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <span class="dropdown-header">{__('helpCenterHeader')}</span>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="https://jtl-url.de/shopschritte" target="_blank" rel="noopener">
                                        {__('firstSteps')}
                                    </a>
                                    <a class="dropdown-item" href="https://jtl-url.de/0762z" target="_blank" rel="noopener">
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
                        <li class="nav-item dropdown fa-lg" id="theme-toggle">
                            <a href="#" class="nav-link px-2" data-toggle="dropdown">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-circle-half theme-toggle-auto{if $themeMode !== 'auto'} d-none{/if}" viewBox="0 0 16 16">
                                    <path d="M8 15A7 7 0 1 0 8 1v14zm0 1A8 8 0 1 1 8 0a8 8 0 0 1 0 16z"/>
                                </svg>

                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-brightness-high theme-toggle-light{if $themeMode !== 'light'} d-none{/if}" viewBox="0 0 16 16">
                                    <path d="M8 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6zm0 1a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z"/>
                                </svg>

                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-moon-stars theme-toggle-dark{if $themeMode !== 'dark'} d-none{/if}" viewBox="0 0 16 16">
                                    <path d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 0 1 .81.316.733.733 0 0 1-.031.893A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278zM4.858 1.311A7.269 7.269 0 0 0 1.025 7.71c0 4.02 3.279 7.276 7.319 7.276a7.316 7.316 0 0 0 5.205-2.162c-.337.042-.68.063-1.029.063-4.61 0-8.343-3.714-8.343-8.29 0-1.167.242-2.278.681-3.286z"/>
                                    <path d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.734 1.734 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.734 1.734 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.734 1.734 0 0 0 1.097-1.097l.387-1.162zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.156 1.156 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.156 1.156 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732L13.863.1z"/>
                                </svg>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right py-0">
                                <a class="dropdown-item py-3{if $themeMode === 'light'} active{/if}"
                                   href="#" rel="noopener" data-theme="light" data-icon="fa-lightbulb-o">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-brightness-high align-bottom" viewBox="0 0 16 16">
                                        <path d="M8 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6zm0 1a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z"/>
                                    </svg> Light
                                </a>
                                <a class="dropdown-item py-3{if $themeMode === 'dark'} active{/if}"
                                   href="#" rel="noopener" data-theme="dark" data-icon="fa-moon-o">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-moon-stars align-bottom" viewBox="0 0 16 16">
                                        <path d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 0 1 .81.316.733.733 0 0 1-.031.893A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278zM4.858 1.311A7.269 7.269 0 0 0 1.025 7.71c0 4.02 3.279 7.276 7.319 7.276a7.316 7.316 0 0 0 5.205-2.162c-.337.042-.68.063-1.029.063-4.61 0-8.343-3.714-8.343-8.29 0-1.167.242-2.278.681-3.286z"/>
                                        <path d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.734 1.734 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.734 1.734 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.734 1.734 0 0 0 1.097-1.097l.387-1.162zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.156 1.156 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.156 1.156 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732L13.863.1z"/>
                                    </svg> Dark
                                </a>
                                <a class="dropdown-item py-3{if $themeMode === 'auto'} active{/if}"
                                   href="#" rel="noopener" data-theme="auto" data-icon="fa-adjust">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-circle-half align-bottom" viewBox="0 0 16 16">
                                        <path d="M8 15A7 7 0 1 0 8 1v14zm0 1A8 8 0 1 1 8 0a8 8 0 0 1 0 16z"/>
                                    </svg> Auto
                                </a>
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
                            <a class="dropdown-item link-shop" href="{$shopURL}?fromAdmin=yes" title="{__('goShop')}" target="_blank">
                                <i class="fa fa-shopping-cart"></i> {__('goShop')}
                            </a>
                            <a class="dropdown-item link-logout" href="{$adminURL}/logout?token={$smarty.session.jtl_token}"
                               title="{__('logout')}">
                                <i class="fa fa-sign-out"></i> {__('logout')}
                            </a>
                        </div>
                    </div>
                </div>
                <div class="opaque-background"></div>
            </div>
            {if !$hasPendingUpdates && $expiredLicenses->count() > 0}
                <div class="modal fade in" id="expiredLicensesNotice" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="staticBackdropLabel">{__('Licensing problem detected')}</h5>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-2"><i class="fa fa-exclamation-triangle" style="font-size: 8em; padding-bottom:10px; color: red;"></i></div>
                                    <div class="col-md-10 ml-auto">
                                        <strong>{__('No valid licence found for the following installed and active extensions:')}</strong>
                                        {form id="plugins-disable-form"}
                                            <input type="hidden" name="action" value="disable-expired-plugins">
                                            <ul>
                                                {$hasPlugin = false}
                                                {$hasTemplate = false}
                                                {foreach $expiredLicenses as $license}
                                                    {if $license->getType() === 'plugin'}
                                                        {$hasPlugin = true}
                                                    {elseif $license->getType() === 'template'}
                                                        {$hasTemplate = true}
                                                    {/if}
                                                    <li>{$license->getName()}</li>
                                                    <input type="hidden" name="pluginID[]" value="{$license->getReferencedItem()->getInternalID()}">
                                                {/foreach}
                                            </ul>
                                        {/form}
                                    </div>
                                </div>
                                <div class="alert alert-secondary" role="alert">
                                    <p><strong>{__('Possible reasons:')}</strong></p>
                                    <ul class="small">
                                        <li>{__('The extension was obtained from a different source than the JTL-Extension Store')}</li>
                                        <li>{__('The licence is not bound to this shop yet (check licence in "My purchases")')}</li>
                                        <li>{__('The licence is bound to a different customer account that is not connected to this shop (check connected account in "My purchases")')}</li>
                                        <li>{__('The manufacturer disabled the licence')}</li>
                                    </ul>
                                </div>
                                <p><strong>{__('Further use of the extension may constitute a licence violation!')}</strong><br>
                                    {__('Please purchase a licence in the JTL-Extension Store or contact the manufacturer of the extension for information on rights of use.')}
                                </p>
                            </div>
                            <div class="modal-footer">
                                <input type="checkbox" id="understood-license-notice">
                                <label for="understood-license-notice">{__('I understood this notice.')}</label>
                                <button type="button" class="btn btn-default" disabled data-dismiss="modal" id="licenseUnderstood">{__('Understood')}</button>
                                {if $hasPlugin === true}
                                    <button type="button" class="btn btn-primary" data-dismiss="modal" id="licenseDisablePlugins">{__('Disable plugins')}</button>
                                {/if}
                                {if $hasTemplate === true}
                                    <button type="button" class="btn btn-primary" data-dismiss="modal" id="licenseGotoTemplates">{__('Disable template')}</button>
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>
                <script>
                    $(document).ready(function() {
                        $('#expiredLicensesNotice').modal('show');
                        $('#understood-license-notice').on('click', function (e) {
                            $('#licenseUnderstood').attr('disabled', false);
                        });
                        $('#licenseUnderstood').on('click', function (e) {
                            var newURL = new URL(window.location.href);
                            newURL.searchParams.append('licensenoticeaccepted', 'true');
                            window.location.href = newURL.toString();
                            return true;
                        });
                        $('#licenseDisablePlugins').on('click', function (e) {
                            $('#plugins-disable-form').submit();
                            return true;
                        });
                        $('#licenseGotoTemplates').on('click', function (e) {
                            window.location.href = '{$adminURL}/template?licensenoticeaccepted=true';
                            return true;
                        });
                    });
                </script>
            {/if}
            <div class="backend-content" id="content_wrapper">

            {include file='snippets/alert_list.tpl'}
{/if}
