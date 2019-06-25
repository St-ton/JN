<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{__('onPageComposer')}</title>

    <link rel="stylesheet" href="{$shopUrl}/templates/NOVA/themes/base/bootstrap/bootstrap.css">
    {*<link rel="stylesheet" href="{$templateUrl}css/bootstrap.min.css">*}
    <link rel="stylesheet" href="{$templateUrl}css/bootstrap-theme.min.css">
    {*<link rel="stylesheet" href="{$templateUrl}css/font-awesome.min.css">*}
    <link rel="stylesheet" href="{$shopUrl}/templates/NOVA/themes/base/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="{$templateUrl}css/bootstrap-colorpicker.min.css">
    <link rel="stylesheet" href="{$templateUrl}css/typeaheadjs.css">
    <link rel="stylesheet" href="{$templateUrl}css/bootstrap-tour.min.css">
    <link rel="stylesheet" href="{$templateUrl}css/bootstrap-datetimepicker.min.css">

    <link rel="stylesheet" href="{$shopUrl}/admin/opc/css/editor.css">

    <script src="{$templateUrl}js/jquery-3.3.1.min.js"></script>
    <script src="{$templateUrl}js/jquery-ui.min.js"></script>
    {*<script src="{$templateUrl}js/bootstrap.min.js"></script>*}
    <script src="{$shopUrl}/templates/NOVA/js/bootstrap.bundle.min.js"></script>

    <script src="{$templateUrl}js/global.js"></script>
    <script src="{$templateUrl}js/searchpicker.js"></script>
    <script src="{$shopUrl}/includes/libs/ckeditor/ckeditor.js"></script>
    <script src="{$templateUrl}js/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js"></script>
    <script src="{$templateUrl}js/moment-with-locales.js"></script>
    <script src="{$templateUrl}js/download.js"></script>
    <script src="{$templateUrl}js/bootstrap-tour.min.js"></script>
    <script src="{$templateUrl}js/typeahead.bundle.js"></script>
    <script src="{$templateUrl}js/bootstrap-datetimepicker.min.js"></script>

    <script src="{$shopUrl}/admin/opc/js/utils.js"></script>
    <script src="{$shopUrl}/admin/opc/js/OPC.js"></script>
    <script src="{$shopUrl}/admin/opc/js/GUI.js"></script>
    <script src="{$shopUrl}/admin/opc/js/Iframe.js"></script>
    <script src="{$shopUrl}/admin/opc/js/Page.js"></script>
    <script src="{$shopUrl}/admin/opc/js/IO.js"></script>
    <script src="{$shopUrl}/admin/opc/js/Tutorial.js"></script>
    <script src="{$shopUrl}/admin/opc/js/PageTree.js"></script>
    <script src="{$shopUrl}/admin/opc/js/PreviewFrame.js"></script>

    <script>
        let opc = new OPC({
            jtlToken:    '{$smarty.session.jtl_token}',
            shopUrl:     '{$shopUrl}',
            templateUrl: '{$templateUrl}',
            pageKey:     {$pageKey},
            error:       {$error|json_encode},
        });

        opc.init();
    </script>
</head>
<body>
    <div id="opc">
    {include file="./sidebar.tpl"}

    <div id="iframePanel">
        <iframe id="iframe"></iframe>
    </div>

    <div id="previewPanel" style="display: none">
        <iframe id="previewFrame" name="previewFrame"></iframe>
        <form action="" target="previewFrame" method="post" id="previewForm">
            <input type="hidden" name="opcPreviewMode" value="yes">
            <input type="hidden" name="pageData" value="" id="previewPageDataInput">
        </form>
    </div>

    <div id="loaderModal" class="modal fade" tabindex="-1" style="padding-top:25%">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{__('Please wait...')}</h4>
                </div>
                <div class="modal-body">
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-info active" style="width:100%">
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
                    <h4 class="modal-title">{__('error')}</h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger" id="errorAlert">
                        {__('somethingHappend')}
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
                    <h4 class="modal-title" id="configModalTitle">{__('Edit Portlet')}</h4>
                </div>
                <form id="configForm">
                    <div class="modal-body" id="configModalBody"></div>
                    <div class="modal-footer">
                        <div class="btn-group" id="stdConfigButtons">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">{__('Cancel')}</button>
                            <button class="btn btn-primary">{__('Save')}</button>
                        </div>
                        <button type="button" class="btn btn-primary" data-dismiss="modal" id="missingConfigButtons">
                            {__('OK')}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="blueprintModal" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{__('Save this Portlet as a blueprint')}</h4>
                </div>
                <form id="blueprintForm">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="blueprintName">{__('Blueprint name')}</label>
                            <input type="text" class="form-control" id="blueprintName" name="blueprintName"
                                   value="Neue Vorlage">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="btn-group">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">{__('Cancel')}</button>
                            <button class="btn btn-primary">{__('Save')}</button>
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
                    <h4 class="modal-title">{__('Delete Blueprint?')}</h4>
                </div>
                <form id="blueprintDeleteForm">
                    <div class="modal-footer">
                        <div class="btn-group">
                            <input type="hidden" id="blueprintDeleteId" name="id" value="">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">{__('cancel')}</button>
                            <button class="btn btn-primary">{__('delete')}</button>
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
                    <h4 class="modal-title">{__('help')}</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xs-12">
                            {__('noteInfoInGuide')}
                            <form id="tourForm">
                                <div class="radio">
                                    <label class="tour-label">
                                        <input type="radio" name="help-tour" id="helpTour1" value="ht1" checked
                                               class="hidden">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">{__('generalIntroduction')}</div>
                                            <div class="panel-body">
                                                {__('getToKnowComposer')}
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                <div class="radio">
                                    <label class="tour-label">
                                        <input type="radio" name="help-tour" id="helpTour2" value="ht2"
                                               class="hidden">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">{__('animation')}</div>
                                            <div class="panel-body">
                                                {__('noteMovementOnPage')}
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                <div class="radio">
                                    <label class="tour-label">
                                        <input type="radio" name="help-tour" id="helpTour3" value="ht3"
                                               class="hidden">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">{__('templates')}</div>
                                            <div class="panel-body">
                                                {__('noteSaveAsTemplate')}
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                <div class="radio">
                                    <label class="tour-label">
                                        <input type="radio" name="help-tour" id="helpTour4" value="ht4"
                                               class="hidden">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">{__('settingsMore')}</div>
                                            <div class="panel-body">{__('noteTricks')}</div>
                                        </div>
                                    </label>
                                </div>
                                <div class="btn-group pull-right">
                                    <button type="button" class="btn btn-danger" data-dismiss="modal">{__('Cancel')}</button>
                                    <button class="btn btn-primary">{__('startTour')}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="publishModal" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{__('draftPublic')}</h4>
                </div>
                <form id="publishForm">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="draftName">{__('draftName')}</label>
                            <input type="text" class="form-control" id="draftName" name="draftName" value="">
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" id="publishFromEnabled">{__('publicFrom')}
                            </label>
                            <input type="text" class="form-control" id="publishFrom" name="publishFrom">
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" id="publishToEnabled">{__('publicTill')}
                            </label>
                            <input type="text" class="form-control" id="publishTo" name="publishTo">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="btn-group">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">{__('cancel')}</button>
                            <button class="btn btn-primary">Übernehmen</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="restoreUnsavedModal" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{__('restoreChanges')}</h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info" id="errorAlert">
                        {__('restoreUnsaved')}
                    </div>
                </div>
                <form id="restoreUnsavedForm">
                    <div class="modal-footer">
                        <div class="btn-group">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">
                                {__('noCurrent')}
                            </button>
                            <button class="btn btn-primary">{__('yesRestore')}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="portletToolbar" class="opc-portlet-toolbar btn-group" style="display:none">
        <button type="button" class="btn btn-default btn-sm opc-label" id="portletLabel">
            {__('portletLabel')}
        </button>
        <button type="button" class="btn btn-default btn-sm" id="btnConfig" title="{__('editSettings')}">
            <i class="fa fa-edit"></i>
        </button>
        <button type="button" class="btn btn-default btn-sm" id="btnClone" title="{__('copySelect')}">
            <i class="fa fa-clone"></i>
        </button>
        <button type="button" class="btn btn-default btn-sm" id="btnBlueprint" title="{__('saveTemplate')}">
            <i class="fa fa-star"></i>
        </button>
        <button type="button" class="btn btn-default btn-sm" id="btnParent" title="{__('goUp')}">
            <i class="fa fa-level-up fas fa-level-up-alt"></i>
        </button>
        <button type="button" class="btn btn-default btn-sm" id="btnTrash" title="{__('deleteSelect')}">
            <i class="fa fa-trash"></i>
        </button>
    </div>

    <div id="portletPreviewLabel" class="opc-label" style="display:none">
        {__('portletPreviewLabel')}
    </div>

    {*blueprint for blueprint entry*}
    <div class="list-group-item" style="display:none" id="blueprintBtnBlueprint">
        <a href="#" class="blueprintButton btn" draggable="true" data-blueprint-id="42">
            <i class="fa fa-puzzle-piece"></i> <span>{__('templateTitle')}</span>
        </a>
        <div class="btn-group pull-right">
            <a href="#" class="blueprintExport btn" data-blueprint-id="999">
                <i class="fa fa-download"></i>
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
    </div>
</body>
</html>