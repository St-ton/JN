{assign var='bForceFluid' value=$bForceFluid|default:false}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="robots" content="noindex,nofollow" />
    <title>JTL-Shop Administration</title>
    {assign var=urlPostfix value='?v='|cat:$shopVersion}
    <link type="image/x-icon" href="favicon.ico" rel="icon" />
    <link type="image/x-icon" href="favicon.ico" rel="shortcut icon" />
    {$admin_css}
    <link type="text/css" rel="stylesheet" href="{$PFAD_CODEMIRROR}lib/codemirror.css{$urlPostfix}" />
    <link type="text/css" rel="stylesheet" href="{$PFAD_CODEMIRROR}addon/hint/show-hint.css{$urlPostfix}" />
    <link type="text/css" rel="stylesheet" href="{$PFAD_CODEMIRROR}addon/display/fullscreen.css{$urlPostfix}" />
    <link type="text/css" rel="stylesheet" href="{$PFAD_CODEMIRROR}addon/scroll/simplescrollbars.css{$urlPostfix}" />
    {$admin_js}
    <script type="text/javascript" src="{$PFAD_CKEDITOR}ckeditor.js{$urlPostfix}"></script>
    <script type="text/javascript" src="{$PFAD_CODEMIRROR}lib/codemirror.js{$urlPostfix}"></script>
    <script type="text/javascript" src="{$PFAD_CODEMIRROR}addon/hint/show-hint.js{$urlPostfix}"></script>
    <script type="text/javascript" src="{$PFAD_CODEMIRROR}addon/hint/sql-hint.js{$urlPostfix}"></script>
    <script type="text/javascript" src="{$PFAD_CODEMIRROR}addon/scroll/simplescrollbars.js{$urlPostfix}"></script>
    <script type="text/javascript" src="{$PFAD_CODEMIRROR}addon/display/fullscreen.js{$urlPostfix}"></script>
    <script type="text/javascript" src="{$PFAD_CODEMIRROR}mode/css/css.js{$urlPostfix}"></script>
    <script type="text/javascript" src="{$PFAD_CODEMIRROR}mode/javascript/javascript.js{$urlPostfix}"></script>
    <script type="text/javascript" src="{$PFAD_CODEMIRROR}mode/xml/xml.js{$urlPostfix}"></script>
    <script type="text/javascript" src="{$PFAD_CODEMIRROR}mode/php/php.js{$urlPostfix}"></script>
    <script type="text/javascript" src="{$PFAD_CODEMIRROR}mode/htmlmixed/htmlmixed.js{$urlPostfix}"></script>
    <script type="text/javascript" src="{$PFAD_CODEMIRROR}mode/smarty/smarty.js{$urlPostfix}"></script>
    <script type="text/javascript" src="{$PFAD_CODEMIRROR}mode/smartymixed/smartymixed.js{$urlPostfix}"></script>
    <script type="text/javascript" src="{$PFAD_CODEMIRROR}mode/sql/sql.js{$urlPostfix}"></script>
    <script type="text/javascript" src="{$URL_SHOP}/{$PFAD_ADMIN}{$currentTemplateDir}js/codemirror_init.js{$urlPostfix}"></script>
    <script type="text/javascript">
        var bootstrapButton = $.fn.button.noConflict();
        $.fn.bootstrapBtn = bootstrapButton;
        setJtlToken('{$smarty.session.jtl_token}');
    </script>
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>
{if $account !== false && isset($smarty.session.loginIsValid) && $smarty.session.loginIsValid === true}
    {getCurrentPage assign="currentPage"}
    <div class="backend-wrapper container-fluid
         {if $currentPage === 'index' || $currentPage === 'status'} dashboard{/if}">
        <nav class="backend-sidebar">
            <div class="backend-brandbar">
                <a class="backend-brand" href="index.php" title="Dashboard">
                    <img src="{$currentTemplateDir}gfx/JTL-Shop-Logo-rgb.png" alt="JTL-Shop">
                </a>
                <button type="button" class="backend-sidebar-toggle">
                    <i class="fa fa-angle-double-left fa-2x"></i>
                </button>
            </div>
            <div class="backend-navigation">
                <ul class="backend-menu toplevel">
                    {foreach $oLinkOberGruppe_arr as $oLinkOberGruppe}
                        {assign var='rootEntryName' value=$oLinkOberGruppe->cName|replace:' ':'-'|replace:'&':''|lower}
                        {if $oLinkOberGruppe->oLinkGruppe_arr|@count === 0
                                && $oLinkOberGruppe->oLink_arr|@count === 1}
                            <li class="{if isset($oLinkOberGruppe->class)}{$oLinkOberGruppe->class}{/if} single">
                                <div class="backend-root-label">
                                    <a href="{$oLinkOberGruppe->oLink_arr[0]->cURL}" class="parent">
                                        <i class="fa fa-2x fa-fw backend-root-menu-icon-{$rootEntryName}"></i>
                                        <span>{$oLinkOberGruppe->oLink_arr[0]->cLinkname}</span>
                                    </a>
                                </div>
                            </li>
                        {else}
                            <li {if isset($oLinkOberGruppe->class)}class="{$oLinkOberGruppe->class}"{/if}>
                                <div class="backend-root-label">
                                    <a href="#" class="parent">
                                        <i class="fa fa-2x fa-fw backend-root-menu-icon-{$rootEntryName}"></i>
                                        <span>{$oLinkOberGruppe->cName}</span>
                                    </a>
                                </div>
                                <ul class="backend-menu secondlevel" id="group-{$rootEntryName}">
                                    {foreach $oLinkOberGruppe->oLinkGruppe_arr as $oLinkGruppe}
                                        {if $oLinkGruppe->oLink_arr|@count > 0}
                                            {assign var='entryName'
                                                value=$oLinkGruppe->cName|replace:' ':'-'|replace:'&':''|lower}
                                            <li id="dropdown-header-{$entryName}">
                                                <a href="#collapse-{$entryName}" data-toggle="collapse"
                                                   class="collapsed" data-parent="#group-{$rootEntryName}">
                                                    <span>{$oLinkGruppe->cName}</span>
                                                    <i class="fa"></i>
                                                </a>
                                                <ul class="collapse backend-menu thirdlevel" id="collapse-{$entryName}">
                                                    {foreach $oLinkGruppe->oLink_arr as $oLink}
                                                        <li {if !$oLink->cRecht|permission}class="noperm"{/if}>
                                                            <a href="{$oLink->cURL}">{$oLink->cLinkname}</a>
                                                        </li>
                                                    {/foreach}
                                                </ul>
                                            </li>
                                        {/if}
                                    {/foreach}
                                    {foreach $oLinkOberGruppe->oLink_arr as $oLink}
                                        <li {if !$oLink->cRecht|permission}class="noperm"{/if}>
                                            <a href="{$oLink->cURL}" class="collapsed">{$oLink->cLinkname}</a>
                                        </li>
                                    {/foreach}
                                </ul>
                            </li>
                        {/if}
                    {/foreach}
                </ul>
                <script>
                    $('.thirdlevel').on('show.bs.collapse', function() {
                        $('.thirdlevel').collapse('hide');
                    });
                </script>
            </div>
        </nav>
        <nav class="backend-main">
            <nav class="backend-navbar">
                <ul class="backend-navbar-left">
                    <li>
                        <div class="backend-search dropdown">
                            <i class="fa fa-search"></i>
                            <input id="backend-search-input" placeholder="Suchbegriff" name="cSuche" type="search"
                                   value="" autocomplete="off">
                            <ul id="backend-search-dropdown"></ul>
                            <script>
                                $('#backend-search-input').on('input', function()
                                {
                                    var value = $(this).val();

                                    if (value.length >= 3) {
                                        ioCall('adminSearch', [value], function (data) {
                                            var tpl = data.data.tpl;
                                            if (tpl) {
                                                $('#backend-search-dropdown').html(tpl).addClass('open');
                                            } else {
                                                $('#backend-search-dropdown').removeClass('open');
                                            }
                                        });
                                    } else {
                                        $('#backend-search-dropdown').removeClass('open');
                                    }
                                });
                                $(document).click(function(e) {
                                    if ($(e.target).closest('.backend-search').length === 0) {
                                        $('#backend-search-dropdown').removeClass('open');
                                    }
                                });
                            </script>
                        </div>
                    </li>
                </ul>
                <ul class="backend-navbar-right">
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle parent" data-toggle="dropdown" title="Hilfe">
                            <i class="fa fa-question-circle"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-right" role="main">
                            <li>
                                <a href="https://jtl-url.de/shopschritte" target="_blank" rel="noopener">
                                    Erste Schritte
                                </a>
                                <a href="https://jtl-url.de/shopguide" target="_blank" rel="noopener">
                                    JTL Guide
                                </a>
                                <a href="https://forum.jtl-software.de" target="_blank" rel="noopener">
                                    JTL Forum
                                </a>
                                <a href="https://www.jtl-software.de/Training" target="_blank" rel="noopener">
                                    Training
                                </a>
                                <a href="https://www.jtl-software.de/Servicepartner" target="_blank" rel="noopener">
                                    Servicepartner
                                </a>
                            </li>
                        </ul>
                    </li>
                    {if $currentPage === 'index'}
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle parent btn-toggle" data-toggle="dropdown">
                                <i class="fa fa-gear"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <li class="widget-selector-menu">
                                    {include file='tpl_inc/widget_selector.tpl' oAvailableWidget_arr=$oAvailableWidget_arr}
                                </li>
                            </ul>
                        </li>
                    {/if}
                    <li class="dropdown" id="favs-drop">{include file="tpl_inc/favs_drop.tpl"}</li>
                    <li class="dropdown" id="notify-drop">{include file="tpl_inc/notify_drop.tpl"}</li>
                    <li class="dropdown avatar">
                        <a href="#" class="dropdown-toggle parent" data-toggle="dropdown">
                            <img src="{gravatarImage email=$account->cMail}" title="{$account->cMail}" class="img-circle" />
                        </a>
                        <ul class="dropdown-menu dropdown-menu-right" role="main">
                            <li>
                                <a class="link-shop" href="{$URL_SHOP}" title="Zum Shop">
                                    <i class="fa fa-shopping-cart"></i> Zum Shop
                                </a>
                                <a class="link-logout" href="logout.php?token={$smarty.session.jtl_token}"
                                   title="{#logout#}">
                                    <i class="fa fa-sign-out"></i> {#logout#}
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </nav>
            <div class="backend-content" id="content_wrapper">
{/if}
