function GUI(io, page)
{
    debuglog('construct GUI');

    bindProtoOnHandlers(this);

    this.io            = io;
    this.page          = page;
    this.configSaveCb  = noop;
    this.imageSelectCB = noop;
}

GUI.prototype = {

    constructor: GUI,

    init: function(iframe, tutorial, error)
    {
        debuglog('GUI init');

        this.iframe   = iframe;
        this.tutorial = tutorial;

        installGuiElements(this, [
            'sidebarPanel',
            'topNav',
            'iframePanel',
            'loaderModal',
            'errorModal',
            'errorAlert',
            'configModal',
            'configModalTitle',
            'configModalBody',
            'configForm',
            'stdConfigButtons',
            'missingConfigButtons',
            'blueprintModal',
            'blueprintForm',
            'blueprintName',
            'blueprintDeleteModal',
            'blueprintDeleteId',
            'blueprintDeleteForm',
            'publishModal',
            'publishForm',
            'draftName',
            'publishFrom',
            'publishFromEnabled',
            'publishTo',
            'publishToEnabled',
            'btnImport',
            'btnExport',
            'btnHelp',
            'btnPreview',
            'btnPublish',
            'btnSave',
            'btnClose',
            'btnImportBlueprint',
            'revisionList',
            'revisionBtnBlueprint',
            'unsavedRevision',
            'blueprintList',
            'blueprintBtnBlueprint',
            'portletButton',
            'collapseGroup',
            'restoreUnsavedModal',
            'restoreUnsavedForm',
        ]);

        this.missingConfigButtons.hide();

        if(typeof error === 'string' && error.length > 0) {
            this.showError(error);
        } else {
            this.showLoader();
            this.publishFrom.datetimepicker({locale: 'de', useCurrent: false});
            this.publishTo.datetimepicker({locale: 'de', useCurrent: false});
            this.publishFrom.on("dp.change", function (e) {
                this.publishTo.data("DateTimePicker").minDate(e.date);
            }.bind(this));
            this.publishTo.on("dp.change", function (e) {
                this.publishFrom.data("DateTimePicker").maxDate(e.date);
            }.bind(this));
            this.collapseGroups.first().click();
            this.updateBlueprintList();
            this.updateRevisionList();
        }
    },

    showLoader: function()
    {
        this.loaderModal.modal('show');
    },

    hideLoader: function()
    {
        this.loaderModal.modal('hide');
    },

    showRestoreUnsaved: function()
    {
        this.restoreUnsavedModal.modal('show');
    },

    showError: function(msg)
    {
        this.loaderModal.modal('hide');
        this.errorAlert.html(msg);
        this.errorModal.modal('show');
        throw msg;
    },

    updateBlueprintList: function()
    {
        this.io.getBlueprints(this.onGetBlueprintList);
    },

    onGetBlueprintList: function(blueprints)
    {
        this.blueprintList.empty();

        blueprints.forEach(function(blueprint) {
            var newBtn = this.blueprintBtnBlueprint.clone()
                .attr('id', '').css('display', '')
                .appendTo(this.blueprintList);

            newBtn.find('.blueprintButton').attr('data-blueprint-id', blueprint.id);
            newBtn.find('.blueprintExport').attr('data-blueprint-id', blueprint.id);
            newBtn.find('.blueprintDelete').attr('data-blueprint-id', blueprint.id);
            newBtn.find('span').html(blueprint.name);
        }, this);

        this.updateDynamicGui();
    },

    updateRevisionList: function()
    {
        this.page.getRevisionList(this.onGetRevisions);
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
            'blueprintExport',
            'blueprintDelete',
            'revisionBtn',
        ]);
    },

    onBtnImport: function()
    {
        this.page.loadFromImport(
            this.iframe.onPageLoad,
            er => this.showError('Could not import OPC page JSON: ' + er.error.message)
        );
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
        this.setUnsaved(false, true);
    },

    onSavePageError: function(error)
    {
        this.showError('Page could not be saved: ' + error.error.message);
    },

    setUnsaved: function(enable, record)
    {
        record = record || false;

        this.btnSave.find('i').html(enable ? '*' : '');

        if(enable) {
            if(record) {
                this.page.savePageToWebStorage();
                this.unsavedRevision.show();
            }
        } else {
            if(record) {
                this.page.clearPageWebStorage();
                this.unsavedRevision.hide();
            }
        }
    },

    onBtnClose: function(e)
    {
        this.page.unlock(this.onUnlockedPage);
    },

    onUnlockedPage: function()
    {
        window.location = this.page.fullUrl;
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

        this.iframe.dragNewPortlet(portletBtn.data('portlet-class'));
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
        var elm   = $(e.target).closest('a');
        var revId = elm.data('revision-id');

        this.showLoader();
        this.page.loadRev(
            revId,
            this.iframe.onPageLoad,
            er => this.showError('Error while loading draft preview: ' + er.error.message),
        );
        this.setUnsaved(revId !== 0);
    },

    openConfigurator: function(portlet)
    {
        var portletData = portlet.data('portlet');

        this.setConfigSaveCallback(noop);
        this.setImageSelectCallback(noop);

        this.io.getConfigPanelHtml(
            portletData.class,
            portletData.missingClass,
            portletData.properties,
            this.onGetConfigPanelHtml
        );

        this.curPortlet = portlet;
    },

    onGetConfigPanelHtml: function(html)
    {
        var portletData = this.curPortlet.data('portlet');

        if (portletData.class === 'MissingPortlet') {
            this.stdConfigButtons.hide();
            this.missingConfigButtons.show();
        } else {
            this.stdConfigButtons.show();
            this.missingConfigButtons.hide();
        }

        this.configModalBody.html(html);
        this.configModalTitle.html(portletData.title + ' bearbeiten');
        this.configModal.modal('show');
    },

    onConfigForm: function(e)
    {
        e.preventDefault();

        this.configSaveCb();

        var portletData  = this.page.portletToJSON(this.curPortlet);
        var configObject = this.configForm.serializeControls();

        for(var propname in configObject) {
            var propval   = configObject[propname];
            var propInput = $('#config-' + propname);

            if (propInput.length > 0) {
                var propType = propInput.data('prop-type');

                if (propType === 'json') {
                    propval = JSON.parse(propval);
                } else if (propInput[0].type === 'checkbox') {
                    propval = propval === '1';
                } else if (propInput[0].type === 'number') {
                    propval = parseInt(propval);
                }
            }

            configObject[propname] = propval;
        }

        portletData.properties = configObject;

        this.io.getPortletPreviewHtml(portletData, this.onPortletPreviewHtml, er => {
            this.configModal.modal('hide');
            this.showError('Error wahile saving Portlet configuration: ' + er.error.message);
        });
    },

    onPortletPreviewHtml: function(preview)
    {
        this.iframe.replaceSelectedPortletHtml(preview);
        this.configModal.modal('hide');
        this.page.updateFlipcards();
    },

    onBlueprintForm: function(e)
    {
        e.preventDefault();

        if(this.selectedElm !== null) {
            var blueprintName = this.blueprintName.val();
            var blueprintData = this.page.portletToJSON(this.iframe.selectedElm);

            this.io.saveBlueprint(blueprintName, blueprintData, this.onBlueprintSaved);
            this.blueprintModal.modal('hide');
        }
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

    onBlueprintExport: function(e)
    {
        var elm         = $(e.target).closest('.blueprintExport');
        var blueprintId = elm.data('blueprint-id');

        this.io.getBlueprint(blueprintId, this.onGetExportBlueprint);
    },

    onGetExportBlueprint: function(blueprint)
    {
        download(JSON.stringify(blueprint), blueprint.name + '.json', 'application/json');
    },

    onBtnImportBlueprint: function()
    {
        $('<input type="file" accept=".json">').on('change', this.onBlueprintImportChosen.bind(this)).click();
    },

    onBlueprintImportChosen: function(e)
    {
        this.importReader = new FileReader();
        this.importReader.onload = this.onBlueprintReaderLoad.bind(this);
        this.importReader.readAsText(e.target.files[0]);
    },

    onBlueprintReaderLoad: function()
    {
        var blueprint = JSON.parse(this.importReader.result);

        this.io.saveBlueprint(blueprint.name, blueprint.instance, this.onBlueprintSaved);
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

    onBtnPublish: function(e)
    {
        if(typeof this.page.publishFrom === 'string' && this.page.publishFrom.length > 0) {
            this.publishFrom.val(this.page.publishFrom);
            this.publishFrom.prop('disabled', false);
            this.publishFromEnabled.prop('checked', true);
        } else {
            this.publishFrom.val('Unveröffentlicht');
            this.publishFrom.prop('disabled', true);
            this.publishFromEnabled.prop('checked', false);
        }

        if(typeof this.page.publishTo === 'string' && this.page.publishTo.length > 0) {
            this.publishTo.val(this.page.publishTo);
            this.publishTo.prop('disabled', false);
            this.publishToEnabled.prop('checked', true);
        } else {
            this.publishTo.val('Auf unbestimmte Zeit öffentlich');
            this.publishTo.prop('disabled', true);
            this.publishToEnabled.prop('checked', false);
        }

        this.draftName.val(this.page.name);
        this.publishModal.modal('show');
    },

    onPublishForm: function (e)
    {
        e.preventDefault();

        this.page.name        = this.draftName.val();
        this.page.publishFrom = this.publishFromEnabled.prop('checked') ? this.publishFrom.val() : null;
        this.page.publishTo   = this.publishToEnabled.prop('checked') ? this.publishTo.val() : null;

        this.page.publicate(noop, er => this.showError(er.error.message));

        this.publishModal.modal('hide');
    },

    onPublishFromEnabled: function (e)
    {
        if(this.publishFromEnabled.prop('checked')) {
            this.publishFrom.val(moment().format(localDateFormat));
            this.publishFrom.prop('disabled', false);
        } else {
            this.publishFrom.val('Unveröffentlicht');
            this.publishFrom.prop('disabled', true);
            this.publishTo.data("DateTimePicker").minDate(false);
        }
    },

    onPublishToEnabled: function (e)
    {
        if(this.publishToEnabled.prop('checked')) {
            if(this.publishFromEnabled.prop('checked')) {
                this.publishTo.val(moment(this.publishFrom.val(), localDateFormat).add(1, 'M').format(localDateFormat));
            } else {
                this.publishTo.val(moment().add(1, 'M').format(localDateFormat));
            }
            this.publishTo.prop('disabled', false);
        } else {
            this.publishTo.val('Auf unbestimmte Zeit öffentlich');
            this.publishTo.prop('disabled', true);
            this.publishFrom.data("DateTimePicker").maxDate(false);
        }
    },

    selectImageProp: function(propName)
    {
        this.openElFinder(function(url) {
            this.imageSelectCB(url, propName);
            this.configForm.find('[name="' + propName + '"]').val(url);
            this.configForm.find('#preview-img-' + propName).attr('src', url);
        }.bind(this), 'image');
    },

    selectVideoProp: function(propName)
    {
         this.openElFinder(function(url) {
             this.configForm.find('[name="' + propName + '"]').val(url);
             this.configForm.find('#preview-vid-' + propName).attr('src', url);
             this.configForm.find('#cont-preview-vid-' + propName)[0].load();
         }.bind(this),'video');
    },

    openElFinder: function (callback, type)
    {
        openElFinder(callback, type);
    },

    onRestoreUnsavedForm: function (e)
    {
        e.preventDefault();

        this.unsavedRevision.click();
        this.restoreUnsavedModal.modal('hide');
    },

    setConfigSaveCallback: function(callback)
    {
        this.configSaveCb = callback;
    },

    setImageSelectCallback: function(callback)
    {
        this.imageSelectCB = callback;
    },
};