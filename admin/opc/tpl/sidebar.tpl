<div id="opcSidebar">
    <header id="opcHeader">
        <div class="opc-dropdown" id="opcMenuBtnDropdown">
            <button type="button" id="opcMenuBtn" data-toggle="dropdown" class="opc-header-btn">
                <i class="fa fas fa-ellipsis-v"></i>
            </button>
            <div class="dropdown-menu opc-dropdown-menu">
                <button type="button" class="opc-dropdown-item" id="btnImport">
                    <i class="fa fas fa-upload fa-fw"></i> {__('Import')}
                </button>
                <button type="button" class="opc-dropdown-item" id="btnExport">
                    <i class="fa fas fa-download fa-fw"></i> {__('Export')}
                </button>
                <button type="button" class="opc-dropdown-item" id="btnHelp">
                    <i class="fa fas fa-question-circle fa-fw"></i> {__('help')}
                </button>
            </div>
        </div>
        <h1 id="opc-sidebar-title">
            Seite bearbeiten
        </h1>
        <button type="button" id="btnClose" class="opc-float-right opc-header-btn" data-toggle="tooltip"
                data-placement="bottom" title="{__('Close OnPage-Composer')}">
            <i class="fa fas fa-times"></i>
        </button>
        {*
        <div class="collapse navbar-collapse" id="top-navbar-collapse">
            <ul class="nav navbar-nav navbar-right">
                <li>
                    <a href="#" id="btnPublish" data-toggle="tooltip" data-placement="bottom"
                       title="{__('Publish')}">
                        <i class="fa fa-newspaper-o fa-fw"></i>
                    </a>
                </li>
                <li>
                    <a href="#" id="btnSave" data-toggle="tooltip" data-placement="bottom"
                       title="{__('Save page')}">
                        <i class="fa fa-save"></i>
                    </a>
                </li>
            </ul>
        </div>
        *}
    </header>

    <ul class="nav nav-tabs" id="opcTabs">
        <li class="nav-item">
            <a class="nav-link active" href="#portlets" data-toggle="tab">{__('Portlets')}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#blueprints" data-toggle="tab">{__('Blueprints')}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#revisions" data-toggle="tab">{__('Revisions')}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#pagetree" data-toggle="tab">{__('Page structure')}</a>
        </li>
    </ul>

    <div id="sidebarInnerPanel">
        <div class="tab-content">
            <div class="tab-pane show active" id="portlets">
                {foreach $opc->getPortletGroups() as $group}
                    {assign var="groupId" value=$group->getName()|regex_replace:'/[^a-zA-Z0-9]/':'-'|lower}
                    <button class="portletGroupBtn" type="button"
                            data-toggle="collapse" data-target="#collapse-{$groupId}">
                        {$group->getName()} <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="collapse show" id="collapse-{$groupId}">
                        {foreach $group->getPortlets() as $i => $portlet}
                            <button type="button" class="portletButton" draggable="true"
                                    data-portlet-class="{$portlet->getClass()}">
                                <span class="portletBtnInner">
                                    {$portlet->getButtonHtml()}
                                </span>
                            </button>
                        {/foreach}
                    </div>
                {/foreach}
            </div>
            <div class="tab-pane" id="blueprints">
                <div class="list-group">
                    <div id="blueprintList"></div>
                    <div class="list-group-item">
                        <a href="#" class="blueprintButton btn" id="btnImportBlueprint">
                            <i class="fa fa-upload"></i> <span>{__('Import blueprint')}</span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="revisions">
                <div class="list-group">
                    <a class="list-group-item revisionBtn" href="#" data-revision-id="-1" id="unsavedRevision">
                        <i>{__('Unsaved revision')}</i>
                    </a>
                    <a class="list-group-item revisionBtn" href="#" data-revision-id="0">
                        {__('Current revision')}
                    </a>
                    <div id="revisionList"></div>
                </div>
            </div>
            <div class="tab-pane" id="pagetree">
                <div id="pageTreeView"></div>
            </div>
        </div>
    </div>

    <div id="sidebarFooter">
        <div id="savePublishPanel">
            <button type="button" id="footerDraftName">
                <span>{$page->getName()}</span><i class="fas fa-pencil-alt"></i>
            </button>
        </div>
        <div id="previewToolbar">
            <label class="toggle-switch">
                Preview
                <input type="checkbox" onchange="opc.gui.onBtnPreview()">
                <span class="toggle-slider"></span>
            </label>
            <ul id="displayWidths">
                <li>
                    <button id="btnDisplayWidthMobile"><i class="fas fa-mobile-alt"></i></button>
                </li>
                <li>
                    <button id="btnDisplayWidthTablet"><i class="fas fa-tablet-alt"></i></button>
                </li>
                <li>
                    <button id="btnDisplayWidthLaptop"><i class="fas fa-laptop"></i></button>
                </li>
                <li class="active">
                    <button id="btnDisplayWidthDesktop"><i class="fas fa-desktop"></i></button>
                </li>
            </ul>
        </div>
    </div>

    {if false}
    <div id="sidebarInnerPanel" class="container-fluid">
        <div class="tab-content">

            <div class="tab-pane" id="blueprints">
                <div class="list-group">
                    <div id="blueprintList"></div>
                    <div class="list-group-item">
                        <a href="#" class="blueprintButton btn" id="btnImportBlueprint">
                            <i class="fa fa-upload"></i> <span>{__('Import blueprint')}</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="tab-pane" id="revisions">
                <div class="list-group">
                    <a class="list-group-item revisionBtn" href="#" data-revision-id="-1" id="unsavedRevision">
                        <i>{__('Unsaved revision')}</i>
                    </a>
                    <a class="list-group-item revisionBtn" href="#" data-revision-id="0">
                        {__('Current revision')}
                    </a>
                    <div id="revisionList"></div>
                </div>
            </div>

            <div class="tab-pane" id="pagetree">
                <div id="pageTreeView"></div>
            </div>

        </div>
    </div>
    {/if}
</div>