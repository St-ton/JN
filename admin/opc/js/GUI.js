class GUI
{
    constructor(io, page)
    {
        bindProtoOnHandlers(this);

        this.io            = io;
        this.page          = page;
        this.configSaveCb  = noop;
        this.imageSelectCB = noop;
        this.inPreviewMode = false;
    }

    init(iframe, previewFrame, tutorial, error)
    {
        this.iframe       = iframe;
        this.previewFrame = previewFrame;
        this.tutorial     = tutorial;

        installGuiElements(this, [
            'opcSidebar',
            'opcHeader',
            'iframePanel',
            'previewPanel',
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
            'portletGroupBtn',
            'restoreUnsavedModal',
            'restoreUnsavedForm',
            'btnDisplayWidthMobile',
            'btnDisplayWidthTablet',
            'btnDisplayWidthLaptop',
            'btnDisplayWidthDesktop',
        ]);

        this.missingConfigButtons.hide();

        if(typeof error === 'string' && error.length > 0) {
            return this.showError(error);
        } else {
            this.showLoader();
            this.publishFrom.datetimepicker({locale: 'de', useCurrent: false});
            this.publishTo.datetimepicker({locale: 'de', useCurrent: false});

            this.publishFrom.on("dp.change", e => {
                this.publishTo.data("DateTimePicker").minDate(e.date);
            });

            this.publishTo.on("dp.change", e => {
                this.publishFrom.data("DateTimePicker").maxDate(e.date);
            });

            this.updateBlueprintList();
            this.updateRevisionList();
        }
    }

    showLoader()
    {
        this.loaderModal.modal('show');
    }

    hideLoader()
    {
        this.loaderModal.modal('hide');
    }

    showRestoreUnsaved()
    {
        this.restoreUnsavedModal.modal('show');
    }

    showError(msg)
    {
        this.loaderModal.modal('hide');
        this.errorAlert.html(msg);
        this.errorModal.modal('show');
        return Promise.reject(msg);
    }

    updateBlueprintList()
    {
        this.io.getBlueprints().then(blueprints => {
            this.blueprintList.empty();

            blueprints.forEach(blueprint => {
                var newBtn = this.blueprintBtnBlueprint.clone()
                    .attr('id', '').css('display', '')
                    .appendTo(this.blueprintList);

                newBtn.find('.blueprintButton').attr('data-blueprint-id', blueprint.id);
                newBtn.find('.blueprintExport').attr('data-blueprint-id', blueprint.id);
                newBtn.find('.blueprintDelete').attr('data-blueprint-id', blueprint.id);
                newBtn.find('span').html(blueprint.name);
            });

            this.updateDynamicGui();
        });
    }

    updateRevisionList()
    {
        this.page.getRevisionList().then(revisions => {
            this.revisionList.empty();

            revisions.forEach(rev => {
                this.revisionBtnBlueprint.clone()
                    .attr('id', '').css('display', '')
                    .attr('data-revision-id', rev.id)
                    .html(rev.timestamp)
                    .appendTo(this.revisionList);
            });

            this.updateDynamicGui();
        });
    }

    updateDynamicGui()
    {
        installGuiElements(this, [
            'blueprintButton',
            'blueprintExport',
            'blueprintDelete',
            'revisionBtn',
        ]);
    }

    onBtnImport()
    {
        this.page.loadFromImport()
            .catch(er => this.showError('Could not import OPC page JSON: ' + er.error.message))
            .then(this.iframe.onPageLoad);
    }

    onBtnExport()
    {
        this.page.exportAsDownload();
    }

    onBtnHelp(e)
    {
        this.tutorial.start();
    }

    onBtnPreview()
    {
        if (this.inPreviewMode) {
            this.iframePanel.show();
            this.previewFrame.previewPanel.hide();
            this.inPreviewMode = false;
        } else {
            this.iframePanel.hide();
            this.previewFrame.showPreview(this.page.fullUrl, JSON.stringify(this.page.toJSON()));
            this.inPreviewMode = true;
        }
    }

    onBtnSave(e)
    {
        this.showLoader();
        this.page.save()
            .catch(error => this.showError('Page could not be saved: ' + error.error.message))
            .then(() => {
                this.hideLoader();
                this.updateRevisionList();
                this.setUnsaved(false, true);
            });
    }

    setUnsaved(enable, record)
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
    }

    onBtnClose(e)
    {
        this.page.unlock().then(() => {
            window.location = this.page.fullUrl;
        });
    }

    onPortletGroupBtn(e)
    {
        $(e.target)
            .find('i.fa')
            .toggleClass('fa-plus-circle fa-minus-circle');
    }

    onPortletButtonDragStart(e)
    {
        initDragStart(e);

        var portletBtn = $(e.target).closest('.portletButton');

        this.iframe.dragNewPortlet(portletBtn.data('portlet-class'));
    }

    onPortletButtonDragEnd(e)
    {
        this.iframe.dragNewPortlet();
        this.iframe.cleanUpDrag();
    }

    onBlueprintButtonDragStart(e)
    {
        initDragStart(e);

        var blueprintBtn = $(e.target).closest('.blueprintButton');

        this.iframe.dragNewBlueprint(blueprintBtn.data('blueprint-id'));
    }

    onBlueprintButtonDragEnd(e)
    {
        this.iframe.dragNewBlueprint();
        this.iframe.cleanUpDrag();
    }

    onRevisionBtn(e)
    {
        var elm   = $(e.target).closest('a');
        var revId = elm.data('revision-id');

        this.showLoader();

        this.page.loadRev(revId)
            .catch(er => this.showError('Error while loading draft preview: ' + er.error.message))
            .then(this.iframe.onPageLoad);

        this.setUnsaved(revId !== 0);
    }

    openConfigurator(portlet)
    {
        var portletData = portlet.data('portlet');

        this.setConfigSaveCallback(noop);
        this.setImageSelectCallback(noop);

        this.curPortlet = portlet;

        this.io.getConfigPanelHtml(
            portletData.class,
            portletData.missingClass,
            portletData.properties
        ).then(html => {
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
        });

    }

    onConfigForm(e)
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

        this.io.getPortletPreviewHtml(portletData)
            .catch(er => {
                this.configModal.modal('hide');
                return this.showError('Error while saving Portlet configuration: ' + er.error.message);
            })
            .then(preview => {
                this.iframe.replaceSelectedPortletHtml(preview);
                this.configModal.modal('hide');
                this.page.updateFlipcards();
            });
    }

    onBlueprintForm(e)
    {
        e.preventDefault();

        if(this.selectedElm !== null) {
            var blueprintName = this.blueprintName.val();
            var blueprintData = this.page.portletToJSON(this.iframe.selectedElm);

            this.io.saveBlueprint(blueprintName, blueprintData).then(() => {
                this.updateBlueprintList();
            });

            this.blueprintModal.modal('hide');
        }
    }

    onBlueprintDelete(e)
    {
        var elm = $(e.target).closest('.blueprintDelete');

        this.blueprintDeleteId.val(elm.data('blueprint-id'));
        this.blueprintDeleteModal.modal('show');
    }

    onBlueprintExport(e)
    {
        var elm         = $(e.target).closest('.blueprintExport');
        var blueprintId = elm.data('blueprint-id');

        this.io.getBlueprint(blueprintId).then(blueprint => {
            download(JSON.stringify(blueprint), blueprint.name + '.json', 'application/json');
        });
    }

    onBtnImportBlueprint()
    {
        $('<input type="file" accept=".json">')
            .on(
                'change',
                e => {
                    this.importReader = new FileReader();
                    this.importReader.onload = () => {
                        let blueprint = JSON.parse(this.importReader.result);
                        this.io.saveBlueprint(blueprint.name, blueprint.instance)
                            .then(() => this.updateBlueprintList());
                    };
                    this.importReader.readAsText(e.target.files[0]);
                }
            )
            .click();
    }

    onBlueprintDeleteForm (e)
    {
        var blueprintId = this.blueprintDeleteId.val();

        this.io.deleteBlueprint(blueprintId).then(() => this.updateBlueprintList());
        this.blueprintDeleteModal.modal('hide');

        e.preventDefault();
    }

    onBtnPublish(e)
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
    }

    onPublishForm (e)
    {
        e.preventDefault();

        this.page.name        = this.draftName.val();
        this.page.publishFrom = this.publishFromEnabled.prop('checked') ? this.publishFrom.val() : null;
        this.page.publishTo   = this.publishToEnabled.prop('checked') ? this.publishTo.val() : null;

        this.page.publicate().catch(er => this.showError(er.error.message));
        this.publishModal.modal('hide');
    }

    onPublishFromEnabled (e)
    {
        if(this.publishFromEnabled.prop('checked')) {
            this.publishFrom.val(moment().format(localDateFormat));
            this.publishFrom.prop('disabled', false);
        } else {
            this.publishFrom.val('Unveröffentlicht');
            this.publishFrom.prop('disabled', true);
            this.publishTo.data("DateTimePicker").minDate(false);
        }
    }

    onPublishToEnabled (e)
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
            this.publishFrom.data('DateTimePicker').maxDate(false);
        }
    }

    selectImageProp(propName)
    {
        this.openElFinder(url => {
            this.imageSelectCB(url, propName);
            this.configForm.find('[name="' + propName + '"]').val(url);
            this.configForm.find('#preview-img-' + propName).attr('src', url);
        }, 'image');
    }

    selectVideoProp(propName)
    {
         this.openElFinder(url => {
             this.configForm.find('[name="' + propName + '"]').val(url);
             this.configForm.find('#preview-vid-' + propName).attr('src', url);
             this.configForm.find('#cont-preview-vid-' + propName)[0].load();
         }, 'video');
    }

    openElFinder (callback, type)
    {
        openElFinder(callback, type);
    }

    onRestoreUnsavedForm (e)
    {
        e.preventDefault();

        this.unsavedRevision.click();
        this.restoreUnsavedModal.modal('hide');
    }

    setConfigSaveCallback(callback)
    {
        this.configSaveCb = callback;
    }

    setImageSelectCallback(callback)
    {
        this.imageSelectCB = callback;
    }

    onBtnDisplayWidthMobile(e)
    {
        this.iframe.iframe.width('375px');
        this.previewFrame.previewFrame.width('375px');
        $('#displayWidths .active').removeClass('active');
        this.btnDisplayWidthMobile.parent().addClass('active');
    }

    onBtnDisplayWidthTablet(e)
    {
        this.iframe.iframe.width('768px');
        this.previewFrame.previewFrame.width('768px');
        $('#displayWidths .active').removeClass('active');
        this.btnDisplayWidthTablet.parent().addClass('active');
    }

    onBtnDisplayWidthLaptop(e)
    {
        this.iframe.iframe.width('992px');
        this.previewFrame.previewFrame.width('992px');
        $('#displayWidths .active').removeClass('active');
        this.btnDisplayWidthLaptop.parent().addClass('active');
    }

    onBtnDisplayWidthDesktop(e)
    {
        this.iframe.iframe.width('100%');
        this.previewFrame.previewFrame.width('100%');
        $('#displayWidths .active').removeClass('active');
        this.btnDisplayWidthDesktop.parent().addClass('active');
    }
}
