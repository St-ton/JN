<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>CMS Live Editor</title>

    <link rel="stylesheet" href="{$templateUrl}css/bootstrap.min.css">
    <link rel="stylesheet" href="{$templateUrl}css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="{$templateUrl}css/font-awesome.min.css">
    <link rel="stylesheet" href="{$templateUrl}css/jtl-live-editor/jle-host.css">
    <link rel="stylesheet" href="{$templateUrl}js/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css">

    <script src="{$templateUrl}js/jquery-1.12.4.min.js"></script>
    <script src="{$templateUrl}js/bootstrap.min.js"></script>
    <script src="{$templateUrl}js/split.min.js"></script>
    <script src="{$templateUrl}js/global.js"></script>
    <script src="{$templateUrl}js/searchpicker.js"></script>
    <script src="//cdn.ckeditor.com/4.7.3/basic/ckeditor.js"></script>
    <script src="{$templateUrl}js/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js"></script>

    <script src="{$templateUrl}js/jtl-live-editor/jle-host.js"></script>

    <script>
        $(function () {
            jleHost = new JLEHost(
                '{$smarty.session.jtl_token}',
                '{$templateUrl}',
                '{$PFAD_KCFINDER}',
                '{$cKey}', {$kKey}, {$kSprache}
            );
        });
    </script>
</head>
<body>
    <nav id="editor-top-nav" class="navbar navbar-inverse">
        <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="collapsed navbar-toggle" data-toggle="collapse"
                        data-target="#le-navbar-collapse" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>
                </button>
                <a href="#" class="navbar-brand">Live Editor</a>
            </div>
            <div class="collapse navbar-collapse" id="le-navbar-collapse">
                <ul class="nav navbar-nav">
                    <li>
                        <a href="#" onclick="$('#iframe').width('100%');"><i class="fa fa-television"></i></a>
                    </li>
                    <li>
                        <a href="#" onclick="$('#iframe').width('768px');"><i class="fa fa-tablet"></i></a>
                    </li>
                    <li>
                        <a href="#" onclick="$('#iframe').width('375px');"><i class="fa fa-mobile"></i></a>
                    </li>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <li>
                        <a href="#" id="jle-btn-save-editor">
                            <i class="fa fa-save"></i>
                        </a>
                    </li>
                    <li><a href="{$URL_SHOP}/{$oSeo->cSeo}"><i class="fa fa-close"></i></a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div id="sidebar-panel">
        <ul class="nav nav-tabs">
            <li class="active"><a href="#elements" data-toggle="tab">Elemente</a></li>
            <li><a href="#templates" data-toggle="tab">Templates</a></li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane active" id="elements">
                {foreach $oPortlet_arr as $oPortlet}
                    <a href="#" class="portlet-button btn btn-default"
                       data-content="{$oPortlet->getPreviewHtml()|escape:'htmlall'}"
                       data-portletid="{$oPortlet->kPortlet}"
                       data-defaultprops="{$oPortlet->getDefaultProps()|json_encode|escape:'htmlall'}">
                        {$oPortlet->cTitle}
                    </a>
                {/foreach}
            </div>
            <div class="tab-pane" id="templates">
                Coming soon...

                <button onclick="openKCFinder()">KC</button>

            </div>
        </div>
    </div>
    <div id="iframe-panel">
        <iframe id="iframe" src="{URL_SHOP}/{$oSeo->cSeo}?editpage=1&action={$cEditorAction}"></iframe>
    </div>
    <div class="modal fade" id="config-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i class="fa fa-times"></i>
                    </button>
                    <h4 class="modal-title">
                        Portlet Einstellungen
                    </h4>
                </div>
                <form id="config-form">
                    <div class="modal-body" id="config-modal-body">
                    </div>
                    <div class="modal-footer">
                        <div class="btn-group">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Abbrechen</button>
                            <button class="btn btn-primary" id="jle-btn-save-config">Speichern</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>