<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>OnPage Composer</title>

    <link rel="stylesheet" href="{$templateUrl}css/bootstrap.min.css">
    <link rel="stylesheet" href="{$templateUrl}css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="{$templateUrl}css/font-awesome.min.css">
    <link rel="stylesheet" href="{$templateUrl}css/bootstrap-colorpicker.min.css">
    <link rel="stylesheet" href="{$templateUrl}css/typeaheadjs.css">
    <link rel="stylesheet" href="{$templateUrl}css/bootstrap-tour.min.css">

    <link rel="stylesheet/less" href="{$templateUrl}css/onpage-composer/host.less">

    <script src="{$templateUrl}js/jquery-1.12.4.min.js"></script>
    <script src="{$templateUrl}js/jquery-ui-1.11.4.min.js"></script>
    <script src="{$templateUrl}js/bootstrap.min.js"></script>
    <script src="{$templateUrl}js/split.min.js"></script>

    <script src="{$templateUrl}js/global.js"></script>
    <script src="{$templateUrl}js/searchpicker.js"></script>
    <script src="{$templateUrl}js/ckeditor_4.7.3_basic/ckeditor.js"></script>
    <script src="{$templateUrl}js/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js"></script>
    <script src="{$templateUrl}js/moment.js"></script>
    <script src="{$templateUrl}js/download.js"></script>
    <script src="{$templateUrl}js/bootstrap-tour.min.js"></script>
    <script src="{$templateUrl}js/typeahead.bundle.js"></script>
    <script src="{$templateUrl}js/less.min.js"></script>

    <script src="{$templateUrl}js/onpage-composer/utils.js"></script>
    <script src="{$templateUrl}js/onpage-composer/OPC.js"></script>
    <script src="{$templateUrl}js/onpage-composer/GUI.js"></script>
    <script src="{$templateUrl}js/onpage-composer/Iframe.js"></script>
    <script src="{$templateUrl}js/onpage-composer/Page.js"></script>
    <script src="{$templateUrl}js/onpage-composer/IO.js"></script>
    <script src="{$templateUrl}js/onpage-composer/Tutorial.js"></script>

    <script>
        var opc = new OPC({
            jtlToken:    '{$smarty.session.jtl_token}',
            templateUrl: '{$templateUrl}',
            kcfinderUrl: '{$PFAD_KCFINDER}',
            pageUrl:     '{$pageUrl}',
            pageId:      '{$pageId}',
            action:      '{$action}',
        });
    </script>
</head>
<body>
    <div id="sidebarPanel">

        <nav id="topNav" class="navbar navbar-default">
            <div class="container-fluid">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                            data-target="#top-navbar-collapse">
                        <span class="sr-only">Toggle navigation</span>
                        <i class="fa fa-bars"></i>
                    </button>
                    <a class="navbar-brand" href="#">OnPage Composer</a>
                </div>
                <div class="collapse navbar-collapse" id="top-navbar-collapse">
                    <ul class="nav navbar-nav navbar-right">
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <i class="fa fa-ellipsis-v"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a href="#" id="btnImport" data-toggle="tooltip" data-placement="bottom"
                                       title="Import">
                                        <i class="fa fa-upload"></i> Import
                                    </a>
                                </li>
                                <li role="separator" class="divider"></li>
                                <li>
                                    <a href="#" id="btnExport" data-toggle="tooltip" data-placement="bottom"
                                       title="Export">
                                        <i class="fa fa-download"></i> Export
                                    </a>
                                </li>
                                <li role="separator" class="divider"></li>
                                <li>
                                    <a href="#" id="btnHelp" data-toggle="tooltip" data-placement="bottom"
                                       title="Help">
                                        <i class="fa fa-question-circle"></i> Hilfe
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="#" id="btnPreview" data-toggle="tooltip" data-placement="bottom"
                               title="Preview">
                                <i class="fa fa-eye"></i>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="btnSave" data-toggle="tooltip" data-placement="bottom"
                               title="Seite speichern">
                                <i class="fa fa-save"></i>
                            </a>
                        </li>
                        <li>
                            <a href="{$pageUrl}" id="btnClose" data-toggle="tooltip"
                               data-placement="bottom" title="Editor schließen">
                                <i class="fa fa-close"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <ul id="composer-tabs" class="nav nav-tabs">
            <li class="active"><a href="#portlets" data-toggle="tab">Portlets</a></li>
            <li><a href="#blueprints" data-toggle="tab">Vorlagen</a></li>
            <li><a href="#revisions" data-toggle="tab">Versionen</a></li>
        </ul>

        <div class="container-fluid">
            <div class="tab-content">

                <div class="tab-pane active" id="portlets">
                    {foreach $opc->getPortletGroups() as $groupName => $group}
                        <a href="#collapse-{$groupName}" data-toggle="collapse" class="collapseGroup">
                            <i class="fa fa-plus-circle"></i> {$groupName}
                        </a>
                        <div class="collapse" id="collapse-{$groupName}">
                            <div class="row">
                                {foreach $group->getPortlets() as $portlet}
                                    <div class="col-xs-4">
                                        <a href="#" class="btn portletButton" draggable="true"
                                           data-portlet-id="{$portlet->getId()}">
                                            {$portlet->getButtonHtml()}
                                        </a>
                                    </div>
                                {/foreach}
                            </div>
                        </div>
                    {/foreach}
                </div>

                <div class="tab-pane" id="blueprints">
                    <div class="list-group" id="blueprintList"></div>
                </div>

                <div class="tab-pane" id="revisions">
                    <div class="list-group">
                        <a class="list-group-item revisionBtn" href="#" data-revision-id="0">
                            Aktuelle Version
                        </a>
                        <div id="revisionList"></div>
                    </div>
                </div>

            </div>
        </div>

        <div id="displayPreviews">
            <ul class="">
                <li>
                    <a href="#" onclick="$('#iframe').width('375px');$('#displayPreviews a').removeClass('active'); $(this).addClass('active');"><i class="fa fa-mobile"></i></a>
                </li>
                <li>
                    <a href="#" onclick="$('#iframe').width('768px');$('#displayPreviews a').removeClass('active'); $(this).addClass('active');"><i class="fa fa-tablet"></i></a>
                </li>
                <li>
                    <a href="#" onclick="$('#iframe').width('992px');$('#displayPreviews a').removeClass('active'); $(this).addClass('active');"><i class="fa fa-laptop"></i></a>
                </li>
                <li>
                    <a href="#" onclick="$('#iframe').width('100%');$('#displayPreviews a').removeClass('active'); $(this).addClass('active');" class="active"><i class="fa fa-desktop"></i></a>
                </li>
            </ul>
        </div>

    </div>

    <div id="iframePanel">
        <iframe id="iframe"></iframe>
    </div>

    <div id="loaderModal" class="modal fade" tabindex="-1" style="padding-top:25%">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Bitte warten...</h4>
                </div>
                <div class="modal-body">
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-info active"
                             style="width:100%">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="errorModal" class="modal fade" tabindex="-1" style="padding-top:25%">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Fehler</h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger" id="errorAlert">
                        Something happened
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="configModal" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        <i class="fa fa-lg fa-times"></i>
                    </button>
                    <h4 class="modal-title" id="configModalTitle">Portlet bearbeiten</h4>
                </div>
                <form id="configForm">
                    <div class="modal-body" id="configModalBody"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Abbrechen</button>
                        <button class="btn btn-primary">Speichern</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="blueprintModal" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Portlet als Vorlage speichern</h4>
                </div>
                <form id="blueprintForm">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="blueprintName">Vorlagen-Name</label>
                            <input type="text" class="form-control" id="blueprintName" name="blueprintName"
                                   value="Neue Vorlage">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="btn-group">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Abbrechen</button>
                            <button class="btn btn-primary">Speichern</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="blueprintDeleteModal" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Vorlage löschen?</h4>
                </div>
                <form id="blueprintDeleteForm">
                    <div class="modal-footer">
                        <div class="btn-group">
                            <input type="hidden" id="blueprintDeleteId" name="id" value="">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Abbrechen</button>
                            <button class="btn btn-primary">Löschen</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="tourModal" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Hilfe</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xs-12">
                            Du findest neben den, nachfolgend aufgelisteten, Touren auch ausführliche Informationen in
                            unserem
                            <a href="https://guide.jtl-software.de" target="_blank"><i class="fa fa-external-link"></i>
                                Guide
                            </a>.

                            <form id="tourForm">
                                <div class="radio">
                                    <label class="tour-label">
                                        <input type="radio" name="help-tour" id="helpTour1" value="ht1" checked
                                               class="hidden">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">Allgemeine Einführung</div>
                                            <div class="panel-body">
                                                lerne den Composer kennen und lege dein erstes Portlet an
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                <div class="radio">
                                    <label class="tour-label">
                                        <input type="radio" name="help-tour" id="helpTour2" value="ht2"
                                               class="hidden">
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
                                        <input type="radio" name="help-tour" id="helpTour3" value="ht3"
                                               class="hidden">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">Vorlagen</div>
                                            <div class="panel-body">
                                                Du hast eine tolle Ansicht angelegt die du häufig wiederverwenden
                                                möchtest?<br>
                                                Leg' sie doch als Vorlage an und greife so einfach immer wieder darauf
                                                zu.
                                            </div>
                                        </div>
                                    </label>
                                </div>

                                <div class="btn-group pull-right">
                                    <button type="button" class="btn btn-danger" data-dismiss="modal">Abbrechen</button>
                                    <button class="btn btn-primary">Tour starten</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="portletToolbar" class="btn-group opc-portlet-toolbar" style="display:none">
        <button class="btn btn-default btn-sm opc-label" id="portletLabel">
            Portlet-Label
        </button>
        <button class="btn btn-default btn-sm" id="btnConfig" title="Einstellungen bearbeiten">
            <i class="fa fa-pencil"></i>
        </button>
        <button class="btn btn-default btn-sm" id="btnClone" title="Auswahl kopieren">
            <i class="fa fa-clone"></i>
        </button>
        <button class="btn btn-default btn-sm" id="btnBlueprint" title="Auswahl als Vorlage speichern">
            <i class="fa fa-star"></i>
        </button>
        <button class="btn btn-default btn-sm" id="btnParent" title="Gehe eine Ebene höher">
            <i class="fa fa-level-up"></i>
        </button>
        <button class="btn btn-default btn-sm" id="btnTrash" title="Auswahl löschen">
            <i class="fa fa-trash"></i>
        </button>
    </div>

    <div id="portletPreviewLabel" class="opc-label" style="display:none">
        Portlet-Preview-Label
    </div>

    {*blueprint for blueprint entry*}
    <div class="list-group-item" style="display:none" id="blueprintBtnBlueprint">
        <div class="btn-group btn-group-justified">
            <a href="#" class="blueprintButton btn" draggable="true" data-blueprint-id="42">
                <i class="fa fa-puzzle-piece"></i> <span>Vorlagen-Titel</span>
            </a>
            <a href="#" class="blueprintDelete btn" data-blueprint-id="999">
                <i class="fa fa-times"></i>
            </a>
        </div>
    </div>
    {*/blueprint*}

    {*blueprint for revision entry*}
    <a class="list-group-item revisionBtn" href="#" data-revision-id="999"
       style="display:none" id="revisionBtnBlueprint"></a>
    {*/blueprint*}

</body>
</html>