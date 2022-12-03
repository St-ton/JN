<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>OPC</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="OnPage Composer">
        <link rel="stylesheet" href="{$shopUrl}/admin/opc/css/editor2.css">
        <script type="module" src="{$shopUrl}/admin/opc/js/editor.js"></script>
    </head>
    <body class="opc">
        {$pageName = 'No Page'}
        {if isset($page)}
            {$pageName = $page->getName()}
        {/if}
        <div id="editor">
            <div id="sidebar">
                <div id="header">
                    <button><i class="fa-solid fa-ellipsis-vertical"></i></button>
                    <div class="title">
                        {__('editPortletPrefix')}&bdquo;{$pageName}&ldquo;{__('editPortletPostfix')}
                    </div>
                    <button><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div id="navbar">
                    <button id="navScrollLeft"><i class="fa-solid fa-angles-left"></i></button>
                    <ul id="navtabs" class="tabs">
                        <li>
                            <button class="active" data-tab="portlets">{__('Portlets')}</button>
                        </li>
                        <li>
                            <button data-tab="blueprints">{__('Blueprints')}</button>
                        </li>
                        <li>
                            <button data-tab="revisions">{__('Revisions')}</button>
                        </li>
                        <li>
                            <button data-tab="pagetree">{__('Page structure')}</button>
                        </li>
                    </ul>
                    <button id="navScrollRight"><i class="fa-solid fa-angles-right"></i></button>
                </div>
                <div class="inner">
                    <div class="tab-pane active" id="portlets">
                        {foreach $opc->getPortletGroups() as $group}
                            {$groupId = $group->getName()|regex_replace:'/[^a-zA-Z0-9]/':'-'|lower}
                            <button class="portlet-group-button"
                                    data-collapse="portlet-group-{$groupId}">
                                <span class="group-name">{$group->getName()}</span>
                                <i class="fas fa-chevron-up"></i>
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="portlet-group collapse" id="portlet-group-{$groupId}">
                                <div class="portlet-group-inner">
                                    {foreach $group->getPortlets() as $portlet}
                                        <button class="portlet-button">
                                            <span class="portlet-button-inner">
                                                {$portlet->getButtonHtml()}
                                            </span>
                                        </button>
                                    {/foreach}
                                </div>
                            </div>
                            {if !$group@last}
                                <hr>
                            {/if}
                        {/foreach}
                    </div>
                    <div class="tab-pane" id="blueprints">
                        Blueprints
                    </div>
                    <div class="tab-pane" id="revisions">
                        Revisions
                    </div>
                    <div class="tab-pane" id="pagetree">
                        Page structure
                    </div>
                </div>
                <div id="publishPanel">
                    <input id="draftNameInput" value="{$pageName}">
                    <label for="draftNameInput">
                        {$pageName}
                        <i class="fas fa-pencil-alt"></i>
                    </label>
                    <div id="draftStatus">
                        {if isset($page)}
                            {$draftStatus = $page->getStatus(0)}
                            {if $draftStatus === 0}
                                {if $page->getPublishTo() === null}
                                    <span class="public">{__('activeSince')}</span>
                                    {$page->getPublishFrom()|date_format:'d.m.Y - H:i'}
                                {else}
                                    <span class="public">{__('activeUntil')}</span>
                                    {$page->getPublishTo()|date_format:'d.m.Y - H:i'}
                                {/if}
                            {elseif $draftStatus === 1}
                                <span class="planned">{__('scheduledFor')}</span>
                                {$page->getPublishFrom()|date_format:'d.m.Y - H:i'}
                            {elseif $draftStatus === 2}
                                <span class="status-draft">{__('notScheduled')}</span>
                            {elseif $draftStatus === 3}
                                <span class="backdate">{__('expiredOn')}</span>
                                {$page->getPublishTo()|date_format:'d.m.Y - H:i'}
                            {/if}
                        {else}
                            No Page
                        {/if}
                    </div>
                    <div class="button-group">
                        <button id="saveButton" class="button">
                            {__('save')}
                            <i class="fas fa-asterisk" id="unsavedIndicator"></i>
                        </button>
                        <button id="publishButton" class="button primary">
                            {__('Publish')}
                        </button>
                    </div>
                </div>
                <div id="previewToolbar">
                    <label class="toggle-switch">
                        {__('preview')}
                        <input type="checkbox">
                        <span class="toggle-slider"></span>
                    </label>
                    <ul id="displayWidths">
                        <li>
                            <button><i class="fas fa-mobile-alt"></i></button>
                        </li>
                        <li>
                            <button><i class="fas fa-tablet-alt"></i></button>
                        </li>
                        <li>
                            <button><i class="fas fa-laptop"></i></button>
                        </li>
                        <li>
                            <button><i class="fas fa-desktop"></i></button>
                        </li>
                        <li>
                            <button class="active"><i class="fas fa-expand"></i></button>
                        </li>
                    </ul>
                </div>
            </div>
            <div id="resizer"></div>
            <div id="pageview">
                <iframe id="iframe"></iframe>
            </div>
        </div>
        <div id="publishModal" class="modal">
            <div class="modal-window">
                <div class="modal-header">
                    <div class="modal-title">{__('draftPublic')}</div>
                    <button data-close><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="modal-body">
                    Interner Name
                </div>
                <div class="modal-footer">
                    <div class="button-group">
                        <button data-close class="button">
                            {__('cancel')}
                        </button>
                        <button class="button primary">
                            {__('apply')}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>