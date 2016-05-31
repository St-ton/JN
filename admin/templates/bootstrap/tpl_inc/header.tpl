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
    {if permission('SETTINGS_SEARCH_VIEW')}
        <div id="main-search" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <form method="post" action="einstellungen.php" role="search">
                            {$jtl_token}
                            <input type="hidden" name="einstellungen_suchen" value="1" />
                            <input placeholder="Suchbegriff" name="cSuche" type="search" value="" autocomplete="off" />
                        </form>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                    </div>
                </div>
            </div>
        </div>
        <script src="//npmcdn.com/masonry-layout@4.0/dist/masonry.pkgd.min.js"></script>
        <script>
        var $grid = null;
        
        $(function () {
            var lastQuery = null;
            var $search_frame = $('#main-search');
            var $search_input = $search_frame.find('input[type="search"]');
            var $search_result = $search_frame.find('div.modal-body');

            function searchEvent(event) {
                var setResult = function(content) {
                    content = content || '';
                
                    if ($grid) {
                        $grid.masonry('destroy');
                    }
                
                    $search_result.html(content);
                
                    $grid = $search_result.masonry({
                        itemSelector: '.grid-item',
                        columnWidth: '.grid-item',
                        percentPosition: true
                    });
                };
                
                var query = $(event.target).val() || '';
                if (query.length < 3) {
                    setResult(null);
                }
                else if(query != lastQuery) {
                    lastQuery = query;
                    ajaxCallV2('suche.php', { query: query }, function(result, error) {
                        if (error) {
                            setResult(null);
                        }
                        else {
                            setResult(result.data.tpl);
                        }
                    });
                }
            }

            $search_frame.on('shown.bs.modal', function (e) {
                $search_input.on('keyup', searchEvent).focus();
            });
            
            $search_frame.on('hidden.bs.modal', function (e) {
                $('body').focus();
                $search_input.off('keyup', searchEvent);
            });
            
            $(document).on("keydown", function (event) {
                if (event.keyCode == 70 && event.ctrlKey) {
                    event.preventDefault();
                    $search_frame.modal('toggle');
                }
            });
            
            /*
            $search_input.on('keydown', function(event) {
                if (event.keyCode == 38 || event.keyCode == 40) {
                    event.preventDefault();
                }
                switch (event.keyCode) {
                    case 38: {
                        console.log('up');
                        break;
                    }
                    case 40: {
                        if ($search_result.find('li.active').length == 0) {
                            $search_result.find('li').first().addClass('active');
                        }
                        else {
                            $active = $search_result.find('li.active');
                            $next = $active.next('li');
                            if ($next.length == 0) {
                                $next = $active.closest('.grid-item').next().find('li').first();
                            }
                            
                            if ($next.length) {
                                $active.removeClass('active');
                                $next.addClass('active');
                            }
                        }
                        break;
                    }
                }
            });
            */
        });
        </script>
    {/if}
    {getCurrentPage assign="currentPage"}
    {$fluid = ['index', 'marktplatz', 'banner']}
    <div class="backend-wrapper {if $currentPage|in_array:$fluid}container-fluid{else}container{/if}{if $currentPage === 'index'} dashboard{/if}{if $currentPage === 'marktplatz'} marktplatz{/if}">
        <nav class="navbar navbar-inverse navbar-fixed-top yamm" role="navigation">
            <div class="container-fluid">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#nbc-1" aria-expanded="false" aria-controls="navbar">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="index.php"><img src="{$currentTemplateDir}gfx/shop-logo.png" alt="JTL-Shop" /></a>
                </div>
                <div class="navbar-collapse collapse" id="nbc-1">   
                    <ul class="nav navbar-nav">
                        {foreach name=linkobergruppen from=$oLinkOberGruppe_arr item=oLinkOberGruppe}
                            {if $oLinkOberGruppe->oLinkGruppe_arr|@count === 0 && $oLinkOberGruppe->oLink_arr|@count === 1}
                                <li {if isset($oLinkOberGruppe->class)}class="{$oLinkOberGruppe->class}"{/if}>
                                    <a href="{$oLinkOberGruppe->oLink_arr[0]->cURL}" class="parent">
                                        {$oLinkOberGruppe->oLink_arr[0]->cLinkname}
                                    </a>
                                </li>
                            {else}
                                <li class="dropdown {if isset($oLinkOberGruppe->class)}{$oLinkOberGruppe->class}{/if}">
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
                    </ul>
                    <ul class="nav navbar-nav navbar-right">
                        {*
                        {if $hasUpdates && permission('SHOP_UPDATE_VIEW')}
                            <li><a href="dbupdater.php"><i class="fa fa-refresh" aria-hidden="true"></i> Updates</a></li>
                        {/if}
                        *}
                        {if $notifications->count() > 0}
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle parent" data-toggle="dropdown">
                                    <span class="badge badge-notify badge-type-{$notifications->count()}">{$notifications->count()}</span>
                                    <!--span class="glyphicon glyphicon-bell"></span>-->
                                    Mitteilungen
                                    <span class="caret"></span>
                                </a>
                                <ul class="dropdown-menu" role="main">
                                    {foreach $notifications as $notify}
                                        <li class="nag">
                                            <div class="nag-split btn-{$notify->getTypeName()}"><i class="fa fa-angle-right" aria-hidden="true"></i></div>
                                            <div class="nag-content">
                                                <a href="{$notify->getUrl()}">
                                                    <div class="nag-title">{$notify->getTitle()}</div>
                                                    <div class="nag-text">{$notify->getDescription()}</div>
                                                </a>
                                            </div>
                                        </li>
                                    {/foreach}
                                </ul>
                            </li>
                        {/if}
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle parent" data-toggle="dropdown">
                                <i class="fa fa-medkit" aria-hidden="true"></i>
                            </a>
                            <ul class="dropdown-menu" role="main">
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
                            <li>
                                <a class="link-search" data-toggle="modal" href="#main-search" title="Suche"><i class="fa fa-search"></i></a>
                            </li>
                        {/if}
                        
                        <!--
                        <li>
                            <a class="link-shop" href="{$URL_SHOP}" title="Zum Shop"><i class="fa fa-shopping-cart"></i> Zum Shop</a>
                        </li>
                        -->
                        {if permission('DASHBOARD_VIEW')}
                            <li>
                                <a class="link-dashboard" href="index.php" title="Dashboard"><i class="fa fa-home"></i> Dashboard</a>
                            </li>
                        {/if}
                        <li>
                            <a class="link-logout" href="logout.php?token={$smarty.session.jtl_token}" title="Abmelden"><i class="fa fa-sign-out"></i> Abmelden</a>
                        </li>
                        
                        {*
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle parent" data-toggle="dropdown">
                                <i class="fa fa-bars" aria-hidden="true"></i>
                            </a>
                            <ul class="dropdown-menu" role="main">
                                <li>
                                    <a class="link-shop" href="{$URL_SHOP}" title="Zum Shop"><i class="fa fa-shopping-cart"></i> Zum Shop</a>
                                </li>
                                {if permission('DASHBOARD_VIEW')}
                                    <li>
                                        <a class="link-dashboard" href="index.php" title="Dashboard"><i class="fa fa-home"></i> Dashboard</a>
                                    </li>
                                {/if}
                                <li>
                                    <a class="link-logout" href="logout.php?token={$smarty.session.jtl_token}" title="Abmelden"><i class="fa fa-sign-out"></i> Abmelden</a>
                                </li>
                            </ul>
                        </li>
                        *}
                    </ul>
                </div>
            </div>
        </nav>
        <div id="content_wrapper" class="container-fluid">
{/if}