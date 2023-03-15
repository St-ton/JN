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
            <div id="sidebar">
                <div id="header">
                    <button type="button" data-dropdown="menu">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                    </button>
                    <div id="menu" class="dropdown">
                        <button type="button">
                            <i class="fa fas fa-upload fa-fw"></i> {__('Import')}
                        </button>
                        <button type="button">
                            <i class="fa fas fa-download fa-fw"></i> {__('Export')}
                        </button>
                        <button type="button">
                            <i class="fa fas fa-question-circle fa-fw"></i> {__('help')}
                        </button>
                    </div>
                    <div id="title">
                        {__('editPortletPrefix')}
                        &bdquo;<span id="titlePageName">{$pageName}</span>&ldquo;
                        {__('editPortletPostfix')}
                    </div>
                    <button type="button" title="{__('Close OnPage-Composer')}" data-tooltip>
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div id="navbar">
                    <button id="navScrollLeft"><i class="fa-solid fa-angles-left"></i></button>
                    <div id="navtabs" class="tabs">
                        <button type="button" data-tab="portlets" class="active">
                            {__('Portlets')}
                        </button>
                        <button type="button" data-tab="blueprints">
                            {__('Blueprints')}
                        </button>
                        <button type="button" data-tab="revisions">
                            {__('Revisions')}
                        </button>
                        <button type="button" data-tab="pagetree">
                            {__('Page structure')}
                        </button>
                    </div>
                    <button id="navScrollRight"><i class="fa-solid fa-angles-right"></i></button>
                </div>
                <div id="inner">
                    <div class="tab-pane active" id="portlets">
                        {foreach $opc->getPortletGroups() as $group}
                            {$groupId = $group->getName()|regex_replace:'/[^a-zA-Z0-9]/':'-'|lower}
                            <button type="button" class="portlet-group-button" data-collapse="portletGroup{$groupId}">
                                <span class="group-name">{$group->getName()}</span>
                                <i class="fas fa-chevron-up"></i>
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="collapse" id="portletGroup{$groupId}">
                                <div class="portlet-group">
                                    {foreach $group->getPortlets() as $portlet}
                                        <button type="button" class="portlet-button">
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
                        Page tree
                    </div>
                </div>
                <div id="publishPanel">
                    <input id="draftNameInput" value="{$pageName}">
                    <label for="draftNameInput">
                        <span id="draftNameLabel">{$pageName}</span>
                        <i class="fas fa-pencil-alt"></i>
                    </label>
                    <div id="draftStatus">
                        {if isset($page)}
                            {$draftStatus = $page->getStatus(0)}
                            {if $draftStatus === 0}
                                {if $page->getPublishTo() === null}
                                    <span class="status-public">{__('activeSince')}</span>
                                    {$page->getPublishFrom()|date_format:'d.m.Y - H:i'}
                                {else}
                                    <span class="status-public">{__('activeUntil')}</span>
                                    {$page->getPublishTo()|date_format:'d.m.Y - H:i'}
                                {/if}
                            {elseif $draftStatus === 1}
                                <span class="status-planned">{__('scheduledFor')}</span>
                                {$page->getPublishFrom()|date_format:'d.m.Y - H:i'}
                            {elseif $draftStatus === 2}
                                <span class="status-draft">{__('notScheduled')}</span>
                            {elseif $draftStatus === 3}
                                <span class="status-backdate">{__('expiredOn')}</span>
                                {$page->getPublishTo()|date_format:'d.m.Y - H:i'}
                            {/if}
                        {else}
                            No Page
                        {/if}
                    </div>
                    <div class="btn-group">
                        <button type="button" id="saveButton" class="btn btn-secondary">
                            {__('save')} <i class="fas fa-asterisk" id="unsavedIndicator"></i>
                        </button>
                        <button type="button" id="publishButton" class="btn btn-primary">
                            {__('Publish')}
                        </button>
                    </div>
                </div>
                <div id="previewToolbar">
                    <label class="toggle-switch">
                        {__('preview')}
                        <input type="checkbox" id="togglePreviewSwitch">
                    </label>
                    <div id="displayWidths">
                        <button><i class="fas fa-mobile-alt"></i></button>
                        <button><i class="fas fa-tablet-alt"></i></button>
                        <button><i class="fas fa-laptop"></i></button>
                        <button><i class="fas fa-desktop"></i></button>
                        <button class="active"><i class="fas fa-expand"></i></button>
                    </div>
                </div>
            </div>
            <div id="resizer"></div>
            <div id="pageview">
                <iframe id="iframe"></iframe>
            </div>
        </div>
        <div id="errorModal" class="modal">
            <div class="modal-dialog">
                <div class="modal-header">
                    <div class="modal-title" id="errorTitle">{__('error')}</div>
                    <button data-close><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fa-solid fa-triangle-exclamation fa-lg"></i>
                        <span id="errorAlert">{__('somethingHappend')}</span>
                    </div>
                </div>
            </div>
        </div>
        <div id="publishModal" class="modal">
            <div class="modal-dialog">
                <div class="modal-header">
                    <div class="modal-title">{__('draftPublic')}</div>
                    <button data-close><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="draftName">{__('draftName')}</label>
                        <input class="control" id="draftName" name="draftName" value="">
                    </div>
                    <div class="form-group">
                        <input type="radio" id="checkPublishNot" name="scheduleStrategy">
                        <label for="checkPublishNot">{__('publishNot')}</label>
                    </div>
                    <div class="form-group">
                        <input type="radio" id="checkPublishNow" name="scheduleStrategy">
                        <label for="checkPublishNow">{__('publishImmediately')}</label>
                    </div>
                    <div class="row">
                        <div class="form-group">
                            <input type="radio" id="checkPublishSchedule" name="scheduleStrategy">
                            <label for="checkPublishSchedule">{__('selectDate')}</label>
                        </div>
                        <div class="form-group">
                            <input type="checkbox" id="checkPublishInfinite">
                            <label for="checkPublishInfinite">{__('indefinitePeriodOfTime')}</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group">
                            <label for="publishFrom">{__('publicFrom')}</label>
                            <input type="datetime-local" class="control" id="publishFrom" name="publishFrom" value="">
                        </div>
                        <div class="form-group">
                            <label for="publishTo">{__('publicTill')}</label>
                            <input class="control" id="publishTo" name="publishTo" value="">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group">
                        <button data-close class="btn">
                            {__('cancel')}
                        </button>
                        <button class="btn btn-primary">
                            {__('apply')}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>