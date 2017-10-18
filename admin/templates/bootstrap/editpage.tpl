{assign var='templateUrl' value="{$URL_SHOP}/{$PFAD_ADMIN}{$currentTemplateDir}"}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>JTL Live-Editor</title>

    <link rel="stylesheet" href="{$templateUrl}css/bootstrap.min.css">
    <link rel="stylesheet" href="{$templateUrl}css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="{$templateUrl}css/font-awesome.min.css">

    <script src="{$templateUrl}js/jquery-1.12.4.min.js"></script>
    <script src="{$templateUrl}js/bootstrap.min.js"></script>
    <script src="https://unpkg.com/split.js/split.min.js"></script>

    <link rel="stylesheet" href="{$templateUrl}css/jtl-live-editor/jle-host.css">
    <script src="{$templateUrl}js/jtl-live-editor/jle-host.js"></script>

    <script>
        $(function () {
            jleHost = new JLEHost('#iframe-panel iframe', '{$templateUrl}');
            Split(
                ['#sidebar-panel', '#iframe-panel'],
                {
                    sizes: [25, 75],
                    gutterSize: 4
                }
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
                    <li><a href="#" onclick="$('#iframe-panel iframe').width('100%');"><i class="fa fa-television"></i></a></li>
                    <li><a href="#" onclick="$('#iframe-panel iframe').width('768px');"><i class="fa fa-tablet"></i></a></li>
                    <li><a href="#" onclick="$('#iframe-panel iframe').width('375px');"><i class="fa fa-mobile"></i></a></li>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <li><a href="#"><i class="fa fa-save"></i></a></li>
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
                {foreach from=$oPortlet_arr item=oPortlet}
                    <a href="#" class="portlet-button btn btn-default" role="button" data-content="{$oPortlet->cPreviewContent}">
                        {$oPortlet->cTitle}
                    </a>
                {/foreach}
            </div>
            <div class="tab-pane" id="templates">
                Coming soon...
            </div>
        </div>
    </div>
    <div id="iframe-panel">
        <iframe src="{URL_SHOP}/{$oSeo->cSeo}?editpage=1"></iframe>
    </div>
</body>
</html>