<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{__('onPageComposer')}</title>
        <link rel="stylesheet" href="{$shopUrl}/includes/node_modules/@fortawesome/fontawesome-free/css/all.min.css">
        <link rel="stylesheet" href="{$shopUrl}/admin/opc/css/editor.css">
        <script src="{$shopUrl}/includes/node_modules/jquery/dist/jquery.min.js"></script>
        <script src="{$shopUrl}/includes/node_modules/@popperjs/core/dist/umd/popper.min.js"></script>
        <script type="module" src="{$shopUrl}/admin/opc/js/index.js"></script>
        <script type="application/json" id="editorConfig">
            {
                "jtlToken": "{$smarty.session.jtl_token}",
                "shopUrl":  "{$shopUrl}",
                "pageKey":  {$pageKey},
                "pageUrl":  "{$page->getUrl()}",
                "error":    {json_encode($error)},
                "messages": {json_encode($opc->getEditorMessages())}
            }
        </script>
    </head>
    <body class="opc">
        {$pageName = $page->getName()}

        <div id="editor">
            {include './sidebar.tpl'}
            <div id="resizer"></div>
            <div id="pageview">
                <iframe id="iframe"></iframe>
            </div>
        </div>

        {include './modals/error.tpl'}
        {include './modals/publish.tpl'}

        <template id="template_dropTarget">
            <div class="opc-droptarget">
                <div class="opc-droptarget-hover">
                    <img src="{$shopUrl}/admin/opc/gfx/icon-drop-target.svg" class="opc-droptarget-icon"
                         alt="Drop Target">
                    <span>{__('dropPortletHere')}</span>
                    <i class="opc-droptarget-info fas fa-info-circle" data-toggle="tooltip" data-placement="left"></i>
                </div>
            </div>
        </template>

        <template id="template_portletToolbar">
            <div id="portletToolbar" class="opc-portlet-toolbar">
                Toolbar
            </div>
        </template>
    </body>
</html>