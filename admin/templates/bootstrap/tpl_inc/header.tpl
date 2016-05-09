<!DOCTYPE html>
<html lang="de">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta charset="windows-1252" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="robots" content="noindex,nofollow" />
    <title>JTL Shop Administration</title>
    <link type="image/x-icon" href="favicon.ico" rel="icon" />
    <link type="image/x-icon" href="favicon.ico" rel="shortcut icon" />
    {$admin_css}
    <link type="text/css" rel="stylesheet" href="{$PFAD_CODEMIRROR}lib/codemirror.css" />
    <link type="text/css" rel="stylesheet" href="{$PFAD_CODEMIRROR}addon/display/fullscreen.css" />
    <link type="text/css" rel="stylesheet" href="{$PFAD_CODEMIRROR}addon/scroll/simplescrollbars.css" />
    {$admin_js}
    <script type="text/javascript" src="{$PFAD_CKEDITOR}ckeditor.js"></script>
    <script type="text/javascript" src="{$PFAD_CODEMIRROR}lib/codemirror.js"></script>
    <script type="text/javascript" src="{$PFAD_CODEMIRROR}mode/css/css.js"></script>
    <script type="text/javascript" src="{$PFAD_CODEMIRROR}mode/javascript/javascript.js"></script>
    <script type="text/javascript" src="{$PFAD_CODEMIRROR}mode/xml/xml.js"></script>
    <script type="text/javascript" src="{$PFAD_CODEMIRROR}mode/php/php.js"></script>
    <script type="text/javascript" src="{$PFAD_CODEMIRROR}mode/htmlmixed/htmlmixed.js"></script>
    <script type="text/javascript" src="{$PFAD_CODEMIRROR}mode/smarty/smarty.js"></script>
    <script type="text/javascript" src="{$PFAD_CODEMIRROR}mode/smartymixed/smartymixed.js"></script>
    <script type="text/javascript" src="{$PFAD_CODEMIRROR}addon/scroll/simplescrollbars.js"></script>
    <script type="text/javascript" src="{$PFAD_CODEMIRROR}addon/display/fullscreen.js"></script>
    <script type="text/javascript" src="{$URL_SHOP}/{$PFAD_ADMIN}{$currentTemplateDir}js/codemirror_init.js"></script>
    <script type="text/javascript">
        var bootstrapButton = $.fn.button.noConflict();
        $.fn.bootstrapBtn = bootstrapButton;
    </script>
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    {if isset($xajax_javascript)}
        {$xajax_javascript}
    {/if}
</head>
<body>
{if $account}
{getCurrentPage assign="currentPage"}
<div class="backend-wrapper
{if $currentPage === 'index' || $currentPage === 'admin' || $currentPage === 'marktplatz' || $currentPage === 'banner'}container-fluid
{else}container{/if}
{if $currentPage === 'index'}dashboard{/if}
{if $currentPage === 'marktplatz'}marktplatz{/if}
">
    <nav class="navbar navbar-default navbar-fixed-top yamm" role="navigation">
        <div class="container-fluid">
            <div class="navbar-header">
                <img src="{$currentTemplateDir}gfx/shop-logo.png" alt="JTL-Shop" class="shop-logo visible-xs pull-left" />
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#nbc-1">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
            </div>
            <div class="collapse navbar-collapse" id="nbc-1">
                <ul class="nav navbar-nav">
                    <li class="hidden-xs"><img src="{$currentTemplateDir}gfx/shop-logo.png" alt="JTL-Shop" class="shop-logo" /></li>
                    {foreach name=linkobergruppen from=$oLinkOberGruppe_arr item=oLinkOberGruppe}
                        {if $oLinkOberGruppe->oLinkGruppe_arr|@count === 0 && $oLinkOberGruppe->oLink_arr|@count === 1}
                            <li class="topmenu {if $smarty.foreach.linkobergruppen.first}topfirst{elseif $smarty.foreach.linkobergruppen.last}toplast{/if}{if isset($oLinkOberGruppe->class)} {$oLinkOberGruppe->class}{/if}">
                                <a href="{$oLinkOberGruppe->oLink_arr[0]->cURL}" class="parent">
                                    {$oLinkOberGruppe->oLink_arr[0]->cLinkname}
                                </a>
                            </li>
                        {else}
                            <li class="dropdown topmenu {if $smarty.foreach.linkobergruppen.first}topfirst{elseif $smarty.foreach.linkobergruppen.last}toplast{/if}{if isset($oLinkOberGruppe->class)} {$oLinkOberGruppe->class}{/if}">
                                <a href="#" class="dropdown-toggle parent" data-toggle="dropdown">{$oLinkOberGruppe->cName}
                                    <span class="caret"> </span>
                                </a>
                                <ul class="dropdown-menu{if $oLinkOberGruppe->oLinkGruppe_arr|@count === 0} single-menu{/if}" role="main">
                                    <li>
                                        <div class="yamm-content">
                                            {foreach name=linkuntergruppen from=$oLinkOberGruppe->oLinkGruppe_arr item=oLinkGruppe}
                                                {if $oLinkGruppe->oLink_arr|@count > 0}
                                                <div class="list-wrapper">
                                                    <ul class="left list-unstyled">
                                                        <li class="dropdown-header" id="dropdown-header-{$oLinkGruppe->cName|replace:' ':'-'|replace:'&':''|lower}">
                                                            {$oLinkGruppe->cName}
                                                        </li>
                                                        {foreach name=linkgruppenlinks from=$oLinkGruppe->oLink_arr item=oLink}
                                                            <li class="{if $smarty.foreach.linkgruppenlinks.first}subfirst {if !$oLink->cRecht|permission}noperm{/if}{/if}">
                                                                <a href="{$oLink->cURL}">{$oLink->cLinkname}</a>
                                                            </li>
                                                        {/foreach}
                                                        {*<li class="divider"></li>*}
                                                    </ul>
                                                </div>
                                                {/if}
                                            {/foreach}
                                            <ul class="left list-unstyled single">
                                            {foreach name=linkuntergruppenlinks from=$oLinkOberGruppe->oLink_arr item=oLink}
                                                <li class="{if $smarty.foreach.linkuntergruppenlinks.first}subfirst{/if} {if !$oLink->cRecht|permission}noperm{/if}">
                                                    <a href="{$oLink->cURL}">{$oLink->cLinkname}</a>
                                                </li>
                                            {/foreach}
                                            </ul>
                                        </div>
                                    </li>
                                </ul>
                            </li>
                        {/if}
                    {/foreach}
                    <li class="navbar-fright">
                        <a class="link-logout" href="logout.php?token={$smarty.session.jtl_token}" title="Ausloggen"><i class="fa fa-sign-out"></i></a>
                    </li>
                    <li class="navbar-fright">
                        <a class="link-shop" href="{$URL_SHOP}" title="Zum Shop"><i class="fa fa-shopping-cart"></i></a>
                    </li>
                    {if permission('DASHBOARD_VIEW')}
                        <li class="navbar-fright">
                            <a class="link-dashboard" href="index.php" title="Dashboard"><i class="fa fa-home"></i></a>
                        </li>
                    {/if}
                    <li class="navbar-fright dropdown topmenu">
                        <a href="#" class="dropdown-toggle parent" data-toggle="dropdown">
                            Hilfe
                            <span class="caret"> </span>
                        </a>
                        <ul class="dropdown-menu{if $oLinkOberGruppe->oLinkGruppe_arr|@count === 0} single-menu{/if}" role="main">
                            <li>
                                <a href="http://guide.jtl-software.de/jtl/JTL-Shop:Installation:Erste_Schritte" target="_blank">Erste Schritte</a>
                                <a href="http://guide.jtl-software.de/jtl/JTL-Shop" target="_blank">JTL Guide</a>
                                <a href="http://forum.jtl-software.de/forum.php" target="_blank">JTL Forum</a>
                                <a href="https://www.jtl-software.de/Training" target="_blank">Training</a>
                                <a href="https://www.jtl-software.de/Servicepartner" target="_blank">Servicepartner</a>

                            </li>
                        </ul>
                    </li>
                    {if permission('SETTINGS_SEARCH_VIEW')}
                        <li class="topmenu-search navbar-fright">
                            <form class="navbar-form navbar-right" method="post" action="einstellungen.php" role="search" id="main-search">
                                {$jtl_token}
                                <div class="input-group">
                                    <input class="form-control" placeholder="Suchbegriff" name="cSuche" type="text" value="" />
                                    <input type="hidden" name="einstellungen_suchen" value="1" />
                                    <input type="hidden" name="kSektion" value="{if isset($kEinstellungenSektion)}{$kEinstellungenSektion}{/if}" />
                                    <span class="input-group-btn">
                                        <button type="submit" class="btn btn-default search_submit"><i class="fa fa-search"></i></button>
                                    </span>
                                </div>
                            </form>
                        </li>
                    {/if}
                </ul>
            </div>
        </div>
    </nav>
    <div id="content_wrapper" class="container-fluid">
    {/if}