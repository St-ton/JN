{assign var='bForceFluid' value=$bForceFluid|default:false}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="robots" content="noindex,nofollow" />
    <title>JTL Shop Administration</title>
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
    </script>
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>
{if $account !== false && isset($smarty.session.loginIsValid) && $smarty.session.loginIsValid === true}
    {if permission('SETTINGS_SEARCH_VIEW')}
        <div id="main-search" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <input placeholder="Suchbegriff" name="cSuche" type="search" value="" autocomplete="off" />
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                    </div>
                </div>
            </div>
        </div>
        <script>
        var $grid    = null,
            jtlToken = '{$smarty.session.jtl_token}';

        $(function () {
            var lastQuery = null,
                $search_frame = $('#main-search'),
                $search_input = $search_frame.find('input[type="search"]'),
                $search_result = $search_frame.find('div.modal-body');

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
                    },
                    query = $(event.target).val() || '';
                if (query.length < 3 || event.keyCode === 27) {
                    setResult(null);
                    lastQuery = null;
                } else if(query !== lastQuery) {
                    lastQuery = query;
                    ioCall('adminSearch', [query], function (data) {
                        setResult(data.data.tpl);
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

            $(document).on('keydown', function (event) {
                if (event.keyCode === 71 && event.ctrlKey) {
                    event.preventDefault();
                    $search_frame.modal('toggle');
                }
                if (event.keyCode === 13) {
                    szSearchString = $("[name$=cSuche]").val();
                    if ('' !== szSearchString) {
                        document.location.href = 'einstellungen.php?cSuche=' + szSearchString + '&einstellungen_suchen=1';
                    }
                }
            });
        });
        </script>
    {/if}
    {getCurrentPage assign="currentPage"}
    {$fluid = ['index', 'marktplatz', 'dbmanager', 'status']}
    <div class="backend-wrapper
         {if $bForceFluid || $currentPage|in_array:$fluid}container-fluid{else}container{/if}
         {if $currentPage === 'index' || $currentPage === 'status'} dashboard{/if}
         {if $currentPage === 'marktplatz'} marktplatz{/if}">
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
                                                                <li class="{if $smarty.foreach.linkgruppenlinks.first}subfirst{/if}{if !$oLink->cRecht|permission} noperm{/if}">
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
                        <li class="dropdown" id="notify-drop">{include file="tpl_inc/notify_drop.tpl"}</li>
                        <li class="dropdown" id="favs-drop">{include file="tpl_inc/favs_drop.tpl"}</li>
                        {if permission('DASHBOARD_VIEW')}
                            <li>
                                <a class="link-dashboard" href="index.php" title="Dashboard"><i class="fa fa-home"></i></a>
                            </li>
                        {/if}
                        {if permission('SETTINGS_SEARCH_VIEW')}
                            <li>
                                <a class="link-search" data-toggle="modal" href="#main-search" title="Suche"><i class="fa fa-search"></i></a>
                            </li>
                        {/if}
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle parent" data-toggle="dropdown" title="Hilfe">
                                <i class="fa fa-medkit" aria-hidden="true"></i>
                            </a>
                            <ul class="dropdown-menu" role="main">
                                <li>
                                    <a href="https://guide.jtl-software.de/jtl/JTL-Shop:Installation:Erste_Schritte" target="_blank" rel="noopener">Erste Schritte</a>
                                    <a href="https://guide.jtl-software.de/jtl/JTL-Shop" target="_blank" rel="noopener">JTL Guide</a>
                                    <a href="https://forum.jtl-software.de" target="_blank" rel="noopener">JTL Forum</a>
                                    <a href="https://www.jtl-software.de/Training" target="_blank" rel="noopener">Training</a>
                                    <a href="https://www.jtl-software.de/Servicepartner" target="_blank" rel="noopener">Servicepartner</a>
                                </li>
                            </ul>
                        </li>
                        <li class="dropdown avatar">
                            <a href="#" class="dropdown-toggle parent" data-toggle="dropdown">
                                <img src="{gravatarImage email=$account->cMail}" title="{$account->cMail}" class="img-circle" />
                            </a>
                            <ul class="dropdown-menu" role="main">
                                <li>
                                    {*if permission('ACCOUNT_VIEW')}
                                        <a class="link-profile" href="benutzerverwaltung.php" title="Profil"><i class="fa fa-user"></i> Profil</a>
                                    {/if*}
                                    <a class="link-shop" href="{$URL_SHOP}" title="Zum Shop"><i class="fa fa-shopping-cart"></i> Zum Shop</a>
                                    <a class="link-logout" href="logout.php?token={$smarty.session.jtl_token}" title="{#logout#}"><i class="fa fa-sign-out"></i> {#logout#}</a>
                                </li>
                            </ul>
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
                                        <a class="link-dashboard" href="index.php" title="Dashboard"><i class="fa fa-tachometer"></i> Dashboard</a>
                                    </li>
                                {/if}
                                <li>
                                    <a class="link-logout" href="logout.php?token={$smarty.session.jtl_token}" title="{#logout#}"><i class="fa fa-sign-out"></i> {#logout#}</a>
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
