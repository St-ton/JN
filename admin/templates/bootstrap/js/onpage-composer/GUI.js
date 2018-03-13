function GUI(io, page)
{
    bindProtoOnHandlers(this);

    this.io   = io;
    this.page = page;
}

GUI.prototype = {

    constructor: GUI,

    init: function(iframe, tutorial)
    {
        this.iframe   = iframe;
        this.tutorial = tutorial;

        installGuiElements(this, [
            'sidebarPanel',
            'topNav',
            'iframePanel',
            'loaderModal',
            'configModal',
            'configModalTitle',
            'configModalBody',
            'configForm',
            'blueprintModal',
            'blueprintForm',
            'blueprintName',
            'blueprintDeleteModal',
            'blueprintDeleteId',
            'blueprintDeleteForm',
            'btnImport',
            'btnExport',
            'btnHelp',
            'btnPreview',
            'btnSave',
            'btnClose',
            'revisionList',
            'revisionBtnBlueprint',
            'blueprintList',
            'blueprintBtnBlueprint',
            'portletButton',
            'collapseGroup',
        ]);

        this.showLoader();
        this.collapseGroups.first().click();
        this.updateBlueprintList();
        this.updateRevisionList();
    },

    showLoader: function()
    {
        this.loaderModal.modal('show');
    },

    hideLoader: function()
    {
        this.loaderModal.modal('hide');
    },

    updateBlueprintList: function()
    {
        this.io.getBlueprintList(this.onGetBlueprintList);
    },

    onGetBlueprintList: function(blueprints)
    {
        this.blueprintList.empty();

        blueprints.forEach(function(blueprint) {
            var newBtn = this.blueprintBtnBlueprint.clone()
                .attr('id', '').css('display', '')
                .appendTo(this.blueprintList);

            newBtn.find('.blueprintButton').attr('data-blueprint-id', blueprint.kBlueprint);
            newBtn.find('.blueprintDelete').attr('data-blueprint-id', blueprint.kBlueprint);
            newBtn.find('span').html(blueprint.cName);
        }, this);

        this.updateDynamicGui();
    },

    updateRevisionList: function()
    {
        this.page.getRevisions(this.onGetRevisions);
    },

    onGetRevisions: function(revisions)
    {
        this.revisionList.empty();

        revisions.forEach(function(rev) {
            this.revisionBtnBlueprint.clone()
                .attr('id', '').css('display', '')
                .attr('data-revision-id', rev.id)
                .html(rev.timestamp)
                .appendTo(this.revisionList);
        }, this);

        this.updateDynamicGui();
    },

    updateDynamicGui: function()
    {
        installGuiElements(this, [
            'blueprintButton',
            'blueprintDelete',
            'revisionBtn',
        ]);
    },

    onBtnImport: function()
    {
        this.page.loadFromImport(this.iframe.onPageLoad);
    },

    onBtnExport: function()
    {
        this.page.exportAsDownload();
    },

    onBtnHelp: function(e)
    {
        this.tutorial.start();
    },

    onBtnPreview: function(e)
    {
        this.iframe.togglePreview()
    },

    onBtnSave: function(e)
    {
        this.showLoader();
        this.page.save(this.onSavePageDone, this.onSavePageError);
    },

    onSavePageDone: function()
    {
        this.hideLoader();
        this.updateRevisionList();
        this.setUnsaved(false);
    },

    onSavePageError: function()
    {
        window.location.reload();
    },

    setUnsaved: function(enable)
    {
        this.btnSave.find('i').html(enable ? '*' : '');
    },

    onBtnClose: function(e)
    {
        this.page.unlock();
    },

    onCollapseGroup: function(e)
    {
        $(e.target)
            .find('i.fa')
            .toggleClass('fa-plus-circle fa-minus-circle');
    },

    onPortletButtonDragStart: function(e)
    {
        initDragStart(e);

        var portletBtn = $(e.target).closest('.portletButton');

        this.iframe.dragNewPortlet(portletBtn.data('portlet-id'));
    },

    onPortletButtonDragEnd: function(e)
    {
        this.iframe.dragNewPortlet();
        this.iframe.cleanUpDrag();
    },

    onBlueprintButtonDragStart: function(e)
    {
        initDragStart(e);

        var blueprintBtn = $(e.target).closest('.blueprintButton');

        this.iframe.dragNewBlueprint(blueprintBtn.data('blueprint-id'));
    },

    onBlueprintButtonDragEnd: function(e)
    {
        this.iframe.dragNewBlueprint();
        this.iframe.cleanUpDrag();
    },

    onRevisionBtn: function(e)
    {
        var elm   = $(e.target);
        var revId = elm.data('revision-id');

        this.showLoader();
        this.page.loadRev(revId, this.iframe.onPageLoad);
        this.setUnsaved(revId > 0);
    },

    openConfigurator: function(portlet)
    {
        var portletData = portlet.data('portlet');

        this.io.getConfigPanelHtml(portletData.id, portletData.properties, this.onGetConfigPanelHtml);
        this.curPortlet = portlet;
    },

    onGetConfigPanelHtml: function(html)
    {
        var portletData = this.curPortlet.data('portlet');

        this.configModalBody.html(html);
        this.configModalTitle.html(portletData.title + ' bearbeiten');
        this.configModal.modal('show');
    },

    onConfigForm: function(e)
    {
        var portletData = this.page.portletToJSON(this.curPortlet);

        portletData.properties = this.configForm.serializeControls();

        this.io.getPortletPreviewHtml(portletData, this.onPortletPreviewHtml);

        e.preventDefault();
    },

    onPortletPreviewHtml: function(preview)
    {
        this.iframe.replaceSelectedPortletHtml(preview);
        this.configModal.modal('hide');
    },

    onBlueprintForm: function(e)
    {
        if(this.selectedElm !== null) {
            var blueprintName = this.blueprintName.val();
            var blueprintData = this.page.portletToJSON(this.iframe.selectedElm);

            this.io.saveBlueprint(blueprintName, blueprintData, this.onBlueprintSaved);
            this.blueprintModal.modal('hide');
        }

        e.preventDefault();
    },

    onBlueprintSaved: function()
    {
        this.updateBlueprintList();
    },

    onBlueprintDelete: function(e)
    {
        var elm = $(e.target).closest('.blueprintDelete');

        this.blueprintDeleteId.val(elm.data('blueprint-id'));
        this.blueprintDeleteModal.modal('show');
    },

    onBlueprintDeleteForm: function (e)
    {
        var blueprintId = this.blueprintDeleteId.val();

        this.io.deleteBlueprint(blueprintId, this.onBlueprintDeleted);
        this.blueprintDeleteModal.modal('hide');

        e.preventDefault();
    },

    onBlueprintDeleted: function()
    {
        this.updateBlueprintList();
    },

};