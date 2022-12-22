<!DOCTYPE html>
<html lang="en">
    <head>
        {if isset($page)}
            {$pageName = $page->getName()}
        {else}
            {$pageName = 'No Page'}
        {/if}
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{__('onPageComposer')}</title>
        <link rel="stylesheet" href="{$shopUrl}/admin/opc/css/editor.css">
    </head>
    <body>
        <div id="editor" class="d-flex">
            <div id="sidebar">
                <div id="header" class="d-flex bg-primary text-white">
                    <button class="btn btn-primary"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                    <div id="title">
                        {__('editPortletPrefix')}&bdquo;{$pageName}&ldquo;{__('editPortletPostfix')}
                    </div>
                    <button class="btn btn-link"><i class="fa-solid fa-xmark"></i></button>
                </div>
            </div>
            <div class="flex-fill">
                <button type="button" class="btn btn-primary">Primary</button>
            </div>
        </div>
    </body>
</html>