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
    {*<link rel="stylesheet" href="{$templateUrl}css/custom.css">*}
    <link rel="stylesheet" href="{$templateUrl}css/font-awesome.min.css">

    <script src="{$templateUrl}js/jquery-1.12.4.min.js"></script>
    <script src="{$templateUrl}js/bootstrap.min.js"></script>
    <script src="https://unpkg.com/split.js/split.min.js"></script>
    <script src="{$templateUrl}js/global.js"></script>
    <script src="//cdn.ckeditor.com/4.7.3/basic/ckeditor.js"></script>

    <link rel="stylesheet" href="{$templateUrl}css/jtl-live-editor/jle-host.css">
    <script src="{$templateUrl}js/jtl-live-editor/jle-host.js"></script>

    <script>
        setJtlToken('{$smarty.session.jtl_token}');

        $(function () {
            jleHost = new JLEHost('#iframe-panel iframe', '{$templateUrl}', '{$cKey}', {$kKey}, {$kSprache});
            Split(
                ['#sidebar-panel', '#iframe-panel'],
                {
                    sizes: [25, 75],
                    gutterSize: 4
                }
            );
            // Fix from: https://stackoverflow.com/questions/22637455/how-to-use-ckeditor-in-a-bootstrap-modal
            $.fn.modal.Constructor.prototype.enforceFocus = function () {
                var $modalElement = this.$element;
                $(document).on('focusin.modal', function (e) {
                    var $parent = $(e.target.parentNode);
                    if ($modalElement[0] !== e.target && !$modalElement.has(e.target).length
                        // add whatever conditions you need here:
                        &&
                        !$parent.hasClass('cke_dialog_ui_input_select') && !$parent.hasClass('cke_dialog_ui_input_text')) {
                        $modalElement.focus()
                    }
                })
            };
        });

        function saveLiveEditorContent()
        {
            ioCall('saveLiveEditorContent', [
                '{$cKey}', {$kKey}, {$kSprache},
                jleHost.editor.toJson()
            ]);
        }
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
                    <li>
                        <a href="#" onclick="saveLiveEditorContent();return false;">
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
                {foreach from=$oPortlet_arr item=oPortlet}
                    <a href="#" class="portlet-button btn btn-default" role="button"
                       data-content="{$oPortlet->cPreviewContent|escape:'htmlall'}"
                       data-portletid="{$oPortlet->kPortlet}"
                       data-initialsettings="{$oPortlet->cInitialSettings|json_encode|escape:'htmlall'}">
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
        <iframe src="{URL_SHOP}/{$oSeo->cSeo}?editpage=1&action={$cEditorAction}"></iframe>
    </div>

    <!-- Button trigger modal -->
    <button type="button" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#myModal">
        Launch demo modal
    </button>

    <!-- Modal -->
    <div class="modal fade" id="settings-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Modal title</h4>
                </div>
                <div class="modal-body">
                    ...
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Abbrechen</button>
                    <button type="submit" class="btn btn-primary" id="jle-btn-save">Speichern</button>
                </div>
            </div>
        </div>
    </div>

</body>
</html>