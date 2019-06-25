<div id="opcSidebar">
    <header id="opcHeader">
        <div class="opc-dropdown" id="opcMenuBtnDropdown">
            <button type="button" id="opcMenuBtn" data-toggle="dropdown">
                <i class="fa fas fa-ellipsis-v"></i>
            </button>
            <div class="dropdown-menu opc-dropdown-menu">
                <a href="#" onclick="duplicateSelectedOpcDrafts();return false">
                    Duplizieren
                </a>
                <a href="#" onclick="deleteSelectedOpcDrafts();return false">
                    Löschen
                </a>
            </div>
        </div>
        <h1 id="opc-sidebar-title">
            Seite bearbeiten
        </h1>
        <button onclick="closeOpcStartMenu()" class="opc-float-right">
            <i class="fa fas fa-times"></i>
        </button>
        {*<a class="navbar-brand" href="#">{__('onPageComposer')}</a>*}
        {*<div class="collapse navbar-collapse" id="top-navbar-collapse">*}
            {*<ul class="nav navbar-nav navbar-right">*}
                {*<li class="dropdown">*}
                    {*<a href="#" class="dropdown-toggle" data-toggle="dropdown">*}
                        {*<i class="fa fa-ellipsis-v"></i>*}
                    {*</a>*}
                    {*<ul class="dropdown-menu">*}
                        {*<li>*}
                            {*<a href="#" id="btnImport" data-toggle="tooltip" data-placement="right">*}
                                {*<i class="fa fa-upload"></i> {__('Import')}*}
                            {*</a>*}
                        {*</li>*}
                        {*<li role="separator" class="divider"></li>*}
                        {*<li>*}
                            {*<a href="#" id="btnExport" data-toggle="tooltip" data-placement="right">*}
                                {*<i class="fa fa-download"></i> {__('Export')}*}
                            {*</a>*}
                        {*</li>*}
                        {*<li role="separator" class="divider"></li>*}
                        {*<li>*}
                            {*<a href="#" id="btnHelp" data-toggle="tooltip" data-placement="right">*}
                                {*<i class="fa fa-question-circle"></i> {__('help')}*}
                            {*</a>*}
                        {*</li>*}
                    {*</ul>*}
                {*</li>*}
                {*<li>*}
                    {*<a href="#" id="btnPublish" data-toggle="tooltip" data-placement="bottom"*}
                       {*title="{__('Publish')}">*}
                        {*<i class="fa fa-newspaper-o fa-fw"></i>*}
                    {*</a>*}
                {*</li>*}
                {*<li>*}
                    {*<a href="#" id="btnSave" data-toggle="tooltip" data-placement="bottom"*}
                       {*title="{__('Save page')}">*}
                        {*<i class="fa fa-save"></i>*}
                    {*</a>*}
                {*</li>*}
                {*<li>*}
                    {*<a href="#" id="btnClose" data-toggle="tooltip" data-placement="bottom"*}
                       {*title="{__('Close OnPage-Composer')}">*}
                        {*<i class="fa fa-close"></i>*}
                    {*</a>*}
                {*</li>*}
            {*</ul>*}
        {*</div>*}
    </header>

    <ul id="composer-tabs" class="nav nav-tabs">
        <li class="active"><a href="#portlets" data-toggle="tab">{__('Portlets')}</a></li>
        <li><a href="#blueprints" data-toggle="tab">{__('Blueprints')}</a></li>
        <li><a href="#revisions" data-toggle="tab">{__('Revisions')}</a></li>
        <li>
            <a href="#pagetree" data-toggle="tab" title="{__('Page structure')}">
                <i class="fa fa-sitemap"></i>
            </a>
        </li>
    </ul>

    <div id="sidebarInnerPanel" class="container-fluid">
        <div class="tab-content">

            <div class="tab-pane active" id="portlets">
                {foreach $opc->getPortletGroups() as $group}
                    {assign var="groupId" value=$group->getName()|regex_replace:'/[^a-zA-Z0-9]/':'-'|lower}
                    <a href="#collapse-{$groupId}" data-toggle="collapse" class="collapseGroup">
                        <i class="fa fa-plus-circle"></i> {$group->getName()}
                    </a>
                    <div class="collapse" id="collapse-{$groupId}">
                        <div class="row">
                            {foreach $group->getPortlets() as $i => $portlet}
                            {if $i > 0 && $i % 3 === 0}</div><div class="row">{/if}
                            <div class="col-xs-4">
                                <a href="#" class="btn portletButton" draggable="true"
                                   data-portlet-class="{$portlet->getClass()}">
                                    {$portlet->getButtonHtml()}
                                </a>
                            </div>
                            {/foreach}
                        </div>
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

    <div id="displayPreviews">
        <ul id="displayWidths">
            <li>
                <a href="#" id="btnDisplayWidthMobile"><i class="fa fa-mobile"></i></a>
            </li>
            <li>
                <a href="#" id="btnDisplayWidthTablet"><i class="fa fa-tablet"></i></a>
            </li>
            <li>
                <a href="#" id="btnDisplayWidthLaptop"><i class="fa fa-laptop"></i></a>
            </li>
            <li class="active">
                <a href="#" id="btnDisplayWidthDesktop"><i class="fa fa-desktop"></i></a>
            </li>
        </ul>
        <ul>
            <li>
                <a href="#" id="btnPreview" data-toggle="tooltip" data-placement="right"
                   title="Preview">
                    <i class="fa fa-eye"></i>
                </a>
            </li>
        </ul>
    </div>
</div>