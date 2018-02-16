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
    <link rel="stylesheet" href="{$templateUrl}css/typeaheadjs.css">
    <link rel="stylesheet" href="{$templateUrl}css/bootstrap-tour.min.css">
    <link rel="stylesheet/less" href="{$templateUrl}css/live-editor/host.less">

    <script src="{$templateUrl}js/jquery-1.12.4.min.js"></script>
    <script src="{$templateUrl}js/jquery-ui-1.11.4.min.js"></script>
    <script src="{$templateUrl}js/bootstrap.min.js"></script>

    {*<script src="{$templateUrl}js/split.min.js"></script>*}
    <script src="https://unpkg.com/split.js/split.min.js"></script>


    <script src="{$templateUrl}js/global.js"></script>
    <script src="{$templateUrl}js/searchpicker.js"></script>
    <script src="{$templateUrl}js/ckeditor_4.7.3_basic/ckeditor.js"></script>
    <script src="{$templateUrl}js/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js"></script>
    <script src="{$templateUrl}js/moment.js"></script>
    <script src="{$templateUrl}js/download.js"></script>
    <script src="{$templateUrl}js/cms-live-editor/EditorIO.js"></script>
    <script src="{$templateUrl}js/cms-live-editor/EditorGUI.js"></script>
    <script src="{$templateUrl}js/cms-live-editor/Editor.js"></script>
    <script src="{$templateUrl}js/bootstrap-tour.min.js"></script>
    <script src="{$templateUrl}js/typeahead.bundle.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/less.js/3.0.0/less.min.js"></script>
    {*<script src="https://unpkg.com/popper.js/dist/umd/popper.min.js"></script>*}

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
    <div id="main-frame">
        <div id="sidebar-panel">
            <nav id="editor-top-nav" class="navbar navbar-static-top" role="navigation">
                <div class="container-fluid">
                    <div class="navbar-header">
                        <a href="#" class="navbar-brand">Live Editor</a>
                        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#editor-nav-collapse">
                            <i class="fa fa-bars"></i>
                        </button>
                    </div>
                    {*<div class="collapse navbar-collapse" id="editor-nav-collapse">*}
                        <ul class="nav navbar-nav navbar-right">
                            <li class="dropdown">
                                <a href="#" id="dropdownMenu1" data-toggle="dropdown">
                                    <i class="fa fa-ellipsis-v"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-left" aria-labelledby="dropdownMenu1">
                                    <li>
                                        <a href="#" id="btn-import" data-toggle="tooltip" data-placement="bottom"
                                           title="Import">
                                            <i class="fa fa-upload"></i> Import
                                        </a>
                                    </li>
                                    <li role="separator" class="divider"></li>
                                    <li>
                                        <a href="#" id="btn-export" data-toggle="tooltip" data-placement="bottom"
                                           title="Export">
                                            <i class="fa fa-download"></i> Export
                                        </a>
                                    </li>
                                    <li role="separator" class="divider"></li>
                                    <li>
                                        <a href="#" id="help" data-toggle="tooltip" data-placement="bottom"
                                           title="Help">
                                            <i class="fa fa-question-circle"></i> Hilfe
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li>
                                <a href="#" id="btn-preview" data-toggle="tooltip" data-placement="bottom"
                                   title="Preview">
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
                                <a href="{$cPageUrl}" id="cle-btn-close-editor" data-toggle="tooltip"
                                   data-placement="bottom" title="Editor schlie&szlig;en">
                                    <i class="fa fa-close"></i>
                                </a>
                            </li>
                        </ul>
                    {*</div>*}
                </div>
            </nav>
            <ul id="editor-tabs" class="nav nav-tabs">
                <li class="active"><a href="#elements" data-toggle="tab">Elemente</a></li>
                <li><a href="#templates" data-toggle="tab">Templates</a></li>
                <li><a href="#revisions" data-toggle="tab">Revisionen</a></li>
            </ul>
            <div class="container-fluid">
                <div class="tab-content">
                    <div class="tab-pane active" id="elements">
                        {foreach name=groups from=$oPortlet_arr  key=$group item=$group_arr}
                            <div class="collapse-groups" role="button" data-toggle="collapse" href="#collapse-{$group}"
                               aria-controls="collapse-{$group}" aria-expanded="false">
                                <i class="fa fa-plus-circle"></i> {$group}
                            </div>
                            <div class="collapse" id="collapse-{$group}" aria-expanded="false">
                                <div class="row">
                                    {foreach $group_arr as $oPortlet}
                                        <div class="col-xs-4">
                                            <a href="#" class="portlet-button btn"
                                               data-content="{$oPortlet->getPreviewHtml()|escape:'htmlall'}"
                                               data-portletid="{$oPortlet->kPortlet}"
                                               data-portlettitle="{$oPortlet->cTitle}"
                                               data-defaultprops="{$oPortlet->getDefaultProps()|json_encode|escape:'htmlall'}"
                                               title="{$oPortlet->cTitle}" draggable="true">
                                                {$oPortlet->getButton()}
                                            </a>
                                        </div>
                                    {/foreach}
                                </div>
                            </div>
                            <hr/>
                        {/foreach}
                    </div>
                    <div class="tab-pane" id="templates">
                        {* blueprint *}
                        <div class="list-group-item" role="group" style="display:none" id="template-btn-blueprint">
                            <div class="btn-group btn-group-justified">
                                <a href="#" class="template-button btn"
                                   data-title="Template-Title"
                                   data-template="42"
                                   data-content="Template-HTML">
                                    <i class="fa fa-puzzle-piece"></i> <span>Template-Title</span>
                                </a>
                                <a href="#" class="template-delete btn"
                                        data-template="42"> <i class="fa fa-times"></i>
                                </a>
                            </div>
                        </div>
                        {* /blueprint *}
                    </div>
                    <div class="tab-pane" id="revisions">
                        <div class="list-group">
                            {if isset($oCMSPage)}
                                <a class="list-group-item revision-btn" href="#" data-revision-id="0">
                                    Aktuelle Version
                                </a>
                                <div id="revision-list">
                                    {foreach $oCMSPage->getRevisions() as $oRevision}
                                        <a class="list-group-item revision-btn" href="#" data-revision-id="{$oRevision->id}">
                                            {$oRevision->timestamp}
                                        </a>
                                    {/foreach}
                                </div>
                            {/if}
                        </div>
                    </div>
                </div>
            </div>
            {*TODO Editor in EditorGUI.js auslagern*}
            <div  id="display-previews">
                <ul class="">
                    <li>
                        <a href="#" onclick="$('#iframe').width('375px');$('#display-previews a').removeClass('active'); $(this).addClass('active');"><i class="fa fa-mobile"></i></a>
                    </li>
                    <li>
                        <a href="#" onclick="$('#iframe').width('768px');$('#display-previews a').removeClass('active'); $(this).addClass('active');"><i class="fa fa-tablet"></i></a>
                    </li>
                    <li>
                        <a href="#" onclick="$('#iframe').width('992px');$('#display-previews a').removeClass('active'); $(this).addClass('active');"><i class="fa fa-laptop"></i></a>
                    </li>
                    <li>
                        <a href="#" onclick="$('#iframe').width('100%');$('#display-previews a').removeClass('active'); $(this).addClass('active');" class="active"><i class="fa fa-desktop"></i></a>
                    </li>
                </ul>
            </div>
        </div>
        <div id="iframe-panel">
            <iframe id="iframe"></iframe>
        </div>
    </div>
    <div id="config-modal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i class="fa fa-lg fa-times"></i>
                    </button>
                    <h4 class="modal-title">
                        Portlet bearbeiten
                    </h4>
                </div>
                <form id="config-form">
                    <div class="modal-body" id="config-modal-body">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Abbrechen</button>
                        <button class="btn btn-primary" id="cle-btn-save-config">Speichern</button>
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
    <div id="template-modal" class="modal fade" tabindex="-1" role="dialog">
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
    <div id="template-delete-modal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Template löschen?</h4>
                </div>
                <form id="template-delete-form">
                    <div class="modal-footer">
                        <div class="btn-group">
                            <input type="hidden" id="template-ktemplate" name="ktemplate"
                                   value="">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Abbrechen</button>
                            <button class="btn btn-primary" id="btn-delete-template">löschen</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div id="tour-modal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Hilfe</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xs-12">
                            Du findest neben den, nachfolgend aufgelisteten, Touren auch ausführliche Informationen in unserem
                            <a href="https://guide.jtl-software.de" target="_blank"><i class="fa fa-external-link"></i>
                                Guide
                            </a>.

                            <form id="tour-form">
                                <div class="radio">
                                    <label class="tour-label">
                                        <input type="radio" name="help-tour" id="help-tour-1" value="ht1" checked class="hidden">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">Allgemeine Einführung</div>
                                            <div class="panel-body">
                                                lerne den Editor kennen und lege dein erstes Portlet an
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                <div class="radio">
                                    <label class="tour-label">
                                        <input type="radio" name="help-tour" id="help-tour-2" value="ht2" class="hidden">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">Animation</div>
                                            <div class="panel-body">
                                                du möchtest etwas Bewegung auf deinen Seiten, lerne hier wie`s geht
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                <div class="radio">
                                    <label class="tour-label">
                                        <input type="radio" name="help-tour" id="help-tour-3" value="ht3" class="hidden">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">Templates</div>
                                            <div class="panel-body">
                                                Du hast eine tolle Ansicht angelegt die du häufig wiederverwenden möchtest?<br/>
                                                Leg' sie doch als Template an und greife so einfach immer wieder darauf zu.
                                            </div>
                                        </div>
                                    </label>
                                </div>

                                <div class="btn-group pull-right">
                                    <button type="button" class="btn btn-danger" data-dismiss="modal">Abbrechen</button>
                                    <button class="btn btn-primary" id="btn-save-template">Tour starten</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="cle-pinbar btn-group" id="pinbar" style="display:none">
        <button class="btn btn-dafault btn-sm cle-label" id="portlet-label"></button>
        <button class="btn btn-default btn-sm" id="btn-config"  title="Einstellungen bearbeiten"><i class="fa fa-pencil"></i></button>
        <button class="btn btn-default btn-sm" id="btn-clone" title="Auswahl kopieren"><i class="fa fa-clone"></i></button>
        <button class="btn btn-default btn-sm" id="btn-template" title="Auswahl als Template speichern"><i class="fa fa-star"></i></button>
        <button class="btn btn-default btn-sm" id="btn-parent" title="gehe eine Ebene höher"><i class="fa fa-level-up"></i></button>
        <button class="btn btn-default btn-sm" id="btn-trash"  title="Auswahl löschen"><i class="fa fa-trash"></i></button>
    </div>
    <div class="cle-label" id="portlet-preview-label" style="display:none"></div>
</body>
</html>