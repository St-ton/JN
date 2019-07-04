<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{__('onPageComposer')}</title>

    <link rel="stylesheet" href="{$shopUrl}/templates/NOVA/themes/base/bootstrap/bootstrap.css">
    <link rel="stylesheet" href="{$templateUrl}css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="{$shopUrl}/templates/NOVA/themes/base/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="{$templateUrl}css/bootstrap-colorpicker.min.css">
    <link rel="stylesheet" href="{$templateUrl}css/typeaheadjs.css">
    <link rel="stylesheet" href="{$templateUrl}css/bootstrap-tour.min.css">
    <link rel="stylesheet" href="{$templateUrl}css/tempusdominus-bootstrap-4.min.css">
    <link rel="stylesheet" href="{$templateUrl}css/fontawesome-iconpicker.min.css">

    <link rel="stylesheet" href="{$shopUrl}/admin/opc/css/editor.css">

    <script src="{$templateUrl}js/jquery-3.3.1.min.js"></script>
    <script src="{$templateUrl}js/jquery-ui.min.js"></script>
    <script src="{$shopUrl}/templates/NOVA/js/bootstrap.bundle.min.js"></script>

    <script src="{$templateUrl}js/global.js"></script>
    <script src="{$templateUrl}js/searchpicker.js"></script>
    <script src="{$shopUrl}/includes/libs/ckeditor/ckeditor.js"></script>
    <script src="{$templateUrl}js/bootstrap-colorpicker.min.js"></script>
    <script src="{$templateUrl}js/moment-with-locales.js"></script>
    <script src="{$templateUrl}js/download.js"></script>
    <script src="{$templateUrl}js/bootstrap-tour.min.js"></script>
    <script src="{$templateUrl}js/typeahead.bundle.js"></script>
    <script src="{$templateUrl}js/tempusdominus-bootstrap-4.min.js"></script>
    <script src="{$templateUrl}js/fontawesome-iconpicker.min.js"></script>

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
    <div id="iconpicker" data-placement="inline" style="display: none"></div>
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

        {include file="./modals/publish.tpl"}
        {include file="./modals/loader.tpl"}
        {include file="./modals/error.tpl"}
        {include file="./modals/config.tpl"}
        {include file="./modals/blueprint.tpl"}
        {include file="./modals/blueprint_delete.tpl"}
        {include file="./modals/tour.tpl"}
        {include file="./modals/restore_unsaved.tpl"}

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
    </div>
</body>
</html>