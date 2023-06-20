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
        <script type="application/json" id="editorConfig">{json_encode($editorConfig)}</script>
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
        {include './modals/config.tpl'}
        {include './templates.tpl'}
    </body>
</html>