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
    <link rel="stylesheet" href="{$templateUrl}css/bootstrap-colorpicker.min.css">
    <link rel="stylesheet" href="{$templateUrl}css/cms-live-editor-host.css">
    <link rel="stylesheet" href="{$templateUrl}css/typeaheadjs.css">

    <script src="{$templateUrl}js/jquery-1.12.4.min.js"></script>
    <script src="{$templateUrl}js/jquery-ui-1.11.4.min.js"></script>
    <script src="{$templateUrl}js/bootstrap.min.js"></script>
    <script src="{$templateUrl}js/split.min.js"></script>
    <script src="{$templateUrl}js/global.js"></script>
    <script src="{$templateUrl}js/searchpicker.js"></script>
    <script src="{$templateUrl}js/ckeditor_4.7.3_basic/ckeditor.js"></script>
    <script src="{$templateUrl}js/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js"></script>
    <script src="{$templateUrl}js/moment.js"></script>
    <script src="{$templateUrl}js/cms-live-editor/EditorIO.js"></script>
    <script src="{$templateUrl}js/cms-live-editor/EditorGUI.js"></script>
    <script src="{$templateUrl}js/cms-live-editor/Editor.js"></script>

    <script>
        var editor = new Editor({
            notice: '{$cHinweis|escape:'htmlall'}',
            error: '{$cFehler|escape:'htmlall'}',
            jtlToken: '{$smarty.session.jtl_token}',
            templateUrl: '{$templateUrl}',
            kcfinderUrl: '{$PFAD_KCFINDER}',
            pageUrl: '{$cPageUrl}',
            cAction: '{$cAction}',
            cPageIdHash: '{$cPageIdHash}',
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
                        <a href="#" onclick="$('#iframe').width('100%');"><i class="fa fa-desktop"></i></a>
                    </li>
                    <li>
                        <a href="#" onclick="$('#iframe').width('992px');"><i class="fa fa-laptop"></i></a>
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
                        <a href="#" id="btn-preview" data-toggle="tooltip" data-placement="bottom" title="Preview">
                            <i class="fa fa-eye"></i>
                        </a>
                    </li>
                    <li>
                        <a href="#" id="cle-btn-save-editor" data-toggle="tooltip" data-placement="bottom"
                           title="Seite speichern">
                            <i class="fa fa-save"></i>
                        </a>
                    </li>
                    <li>
                        <a href="{$cPageUrl}" id="cle-btn-close-editor" data-toggle="tooltip" data-placement="bottom"
                           title="Editor schlie&szlig;en">
                            <i class="fa fa-close"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div id="main-frame">
        <div id="sidebar-panel">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#elements" data-toggle="tab">Elemente</a></li>
                <li><a href="#templates" data-toggle="tab">Templates</a></li>
                <li><a href="#revisions" data-toggle="tab">Revisionen</a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="elements">
                    {foreach $oPortlet_arr as $oPortlet}
                        <a href="#" class="portlet-button btn btn-default btn-lg btn-block"
                           data-content="{$oPortlet->getPreviewHtml()|escape:'htmlall'}"
                           data-portletid="{$oPortlet->kPortlet}"
                           data-portlettitle="{$oPortlet->cTitle}"
                           data-defaultprops="{$oPortlet->getDefaultProps()|json_encode|escape:'htmlall'}"
                           title="{$oPortlet->cTitle}" draggable="true">
                            {$oPortlet->getButton()}
                        </a>
                    {/foreach}
                </div>
                <div class="tab-pane" id="templates">
                    {foreach $oTemplate_arr as $oTemplate}
                        <a href="#" class="template-button btn btn-default btn-lg"
                           data-title="{$oTemplate->cName}"
                           data-content="{$oTemplate->getFullPreviewHtml()|escape:'htmlall'}">
                            <i class="fa fa-puzzle-piece"></i> {$oTemplate->cName}
                        </a>
                    {/foreach}
                </div>
                <div class="tab-pane" id="revisions">
                    <div class="list-group">
                        {if isset($oCMSPage)}
                            {foreach $oCMSPage->getRevisions() as $oRevision}
                                <a class="list-group-item revision-btn" href="#" data-revision-id="{$oRevision->id}">
                                    {$oRevision->timestamp}
                                </a>
                            {/foreach}
                        {/if}
                    </div>
                </div>
            </div>
        </div>
        <div id="iframe-panel">
            <iframe id="iframe"></iframe>
        </div>
    </div>
    <div id="config-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
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
                            <button class="btn btn-primary" id="cle-btn-save-config">Speichern</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div id="loader-modal" class="modal fade" tabindex="-1" role="dialog" style="padding-top:25%">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Bitte warten...</h4>
                </div>
                <div class="modal-body">
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-info active" style="width:100%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="error-modal" class="modal fade" tabindex="-1" role="dialog" style="padding-top:25%">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Fehler</h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger" id="error-alert">
                        Something happened
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="template-modal" class="modal fade" tabindex="-1" role="dialog" style="padding-top:25%">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Portlet als Template speichern</h4>
                </div>
                <form id="template-form">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="template-name">Template-Name</label>
                            <input type="text" class="form-control" id="template-name" name="templateName"
                                   value="Neues Template">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="btn-group">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Abbrechen</button>
                            <button class="btn btn-primary" id="btn-save-template">Speichern</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="cle-pinbar btn-group" id="pinbar" style="display:none">
        <button class="btn btn-default btn-sm" id="btn-parent" title="gehe eine Ebene höher"><i class="fa fa-level-up"></i></button>
        <button class="btn btn-default btn-sm" id="btn-template" title="Auswahl als Template speichern"><i class="fa fa-star"></i></button>
        <button class="btn btn-default btn-sm" id="btn-trash"  title="Auswahl löschen"><i class="fa fa-trash"></i></button>
        <button class="btn btn-default btn-sm" id="btn-clone" title="Auswahl kopieren"><i class="fa fa-clone"></i></button>
        <button class="btn btn-default btn-sm" id="btn-config"  title="Einstellungen bearbeiten"><i class="fa fa-cog"></i></button>
    </div>
    <div class="cle-label" id="portlet-label" style="display:none"></div>
</body>
</html>