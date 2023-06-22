export class GUI
{
    constructor(io, page, messages)
    {
        bindProtoOnHandlers(this);

        this.io            = io;
        this.page          = page;
        this.messages      = messages;
        this.imageSelectCB = noop;
        this.iconPickerCB  = noop;
        this.inPreviewMode = false;
        this.loaderShown   = false;
    }

    init(iframe, previewFrame, tutorial, error)
    {
        this.iframe         = iframe;
        this.previewFrame   = previewFrame;
        this.tutorial       = tutorial;
        this.portletButtons = $('.portletButton');

        this.portletButtons
            .on('dragstart', e => this.onPortletButtonDragStart(e))
            .on('dragend', e => this.onPortletButtonDragEnd(e));

        $('.portletGroupBtn').on('click', e => this.onPortletGroupBtn(e));

        // resizer

        window.resizer.addEventListener('mousedown', e => {
            let resizerStartWidth = window.opcSidebar.offsetWidth;
            let resizerStartX = e.clientX;

            window.addEventListener('mousemove', resize);
            window.addEventListener('mouseup', stopResize);
            document.body.classList.add('resizing');

            function resize(e)
            {
                e.stopPropagation();
                setSiderbarSize(resizerStartWidth + e.clientX - resizerStartX);

                if (window.opcTabs.scrollWidth > window.opcTabs.offsetWidth) {
                    window.navScrollRight.classList.remove('d-none');
                    window.navScrollLeft.classList.remove('d-none');
                } else {
                    window.navScrollRight.classList.add('d-none');
                    window.navScrollLeft.classList.add('d-none');
                }
            }

            function setSiderbarSize(width)
            {
                window.opcSidebar.style.width = width + 'px';
                let portletsPerRow = 16;

                while (
                    portletsPerRow > 1 &&
                    window.opcSidebar.offsetWidth < portletsPerRow * 96 + (portletsPerRow - 1) * 22 + 24 * 2
                ) {
                    portletsPerRow --;
                }

                window.portlets.style.setProperty('--portlets-per-row', portletsPerRow);
            }

            function stopResize(e)
            {
                e.stopPropagation();
                window.removeEventListener('mousemove', resize);
                window.removeEventListener('mouseup', stopResize);
                document.body.classList.remove('resizing');
            }
        });

        $('#navScrollRight').on('click', () => {
            window.opcTabs.scrollLeft += 64;
        });

        $('#navScrollLeft').on('click', () => {
            window.opcTabs.scrollLeft -= 64;
        });

        $('#opcTabs .nav-link').on('click', e => {
            e.target.scrollIntoView();
        })

        $(window.missingConfigButtons).hide();

        if(error) {
            if(typeof error === 'string' && error.length > 0) {
                return this.showError(error);
            } else if(typeof error === 'object' && error.desc.length > 0) {
                return this.showError(error.desc, error.heading);
            }
        } else {
            this.showLoader();
            this.initDateTimePicker($(window.publishFrom));
            this.initDateTimePicker($(window.publishTo));

            $(window.publishFrom).on("change.datetimepicker", e => {
                $(window.publishTo).datetimepicker('minDate', e.date);
            });

            $(window.publishTo).on("change.datetimepicker", e => {
                $(window.publishFrom).datetimepicker('maxDate', e.date);
            });

            this.updateBlueprintList();
            this.updateRevisionList();

            $(window.iconpicker).iconpicker().on('iconpickerSelected', e => {
                this.iconPickerCB(e.iconpickerValue);
            });

            $(window.iconpicker).find('.popover-title').prepend('<i class=""></i>');

            $(window.configModal).on('hidden.bs.modal', e => {
                $('#opc').append(window.iconpicker);
            })
        }
    }

    initDateTimePicker(elm)
    {
        elm.datetimepicker({
            locale: 'de',
            format: localDateFormat,
            useCurrent: false,
            icons: {
                time: 'far fa-clock',
                date: 'far fa-calendar',
                up: 'fas fa-chevron-up',
                down: 'fas fa-chevron-down',
                previous: 'fas fa-chevron-left',
                next: 'fas fa-chevron-right',
                today: 'far fa-calendar-check',
                clear: 'fas fa-trash',
                close: 'fas fa-times',
            },
        });
    }

    showLoader()
    {
        $(window.loaderModal)
            .one('shown.bs.modal', () => this.loaderShown = true)
            .modal('show');
    }

    hideLoader()
    {
        $(window.loaderModal).one('hidden.bs.modal', () => this.loaderShown = false);

        if(this.loaderShown) {
            $(window.loaderModal).modal('hide');
        } else {
            $(window.loaderModal).one('shown.bs.modal', () => $(window.loaderModal).modal('hide'));
        }
    }

    showRestoreUnsaved()
    {
        $(window.restoreUnsavedModal).modal('show');
    }

    showError(msg, heading)
    {
        if(heading) {
            $(window.errorTitle).html(heading);
        }

        this.hideLoader();
        $(window.errorAlert).html(msg);
        $(window.errorModal).modal('show');
        return Promise.reject(msg);
    }

    showMessageBox(msg, title)
    {
        $(window.messageboxAlert).html(msg);

        $(window.messageboxModal)
            .modal('show')
            .find('.modal-title').html(title);
    }

    async updateBlueprintList()
    {
        let blueprints = await this.io.getBlueprints();
        window.blueprintList.innerHTML = '';

        blueprints.forEach(blueprint => {
            let newBtn = $(window.blueprintBtnBlueprint)
                .clone()
                .attr('id', null)
                .attr('data-blueprint-id', blueprint.id)
                .show()
                .appendTo(window.blueprintList);

            newBtn.find('.blueprintExport').attr('data-blueprint-id', blueprint.id);
            newBtn.find('.blueprintDelete').attr('data-blueprint-id', blueprint.id);
            newBtn.find('.blueprintTitle').text(blueprint.name);
        });

        this.updateDynamicGui();
    }

    async updateRevisionList()
    {
        let revisions = await this.page.getRevisionList();
        window.revisionList.innerHTML = '';

        revisions.forEach(rev => {
            $(window.revisionBtnBlueprint)
                .clone()
                .attr('id', '').css('display', '')
                .attr('data-revision-id', rev.id)
                .html(
                    '<div>' + rev.content.cName + '</div>' +
                    '<div>' + moment(rev.content.dLastModified, internalDateFormat).format(localDateFormat) + '</div>'
                )
                .appendTo(window.revisionList);
        });

        $('#currentLastModified').text(
            moment(this.page.lastModified, internalDateFormat).format(localDateFormat)
        );

        $('#currentDraftName').text(this.page.name);

        this.updateDynamicGui();
    }

    updatePagetreeBtn()
    {
        if (this.page.offscreenAreas.length) {
            window.btnPagetree.classList.add('has-unmapped');
        } else {
            window.btnPagetree.classList.remove('has-unmapped');
        }
    }

    updateDynamicGui()
    {
        $('.blueprintButton')
            .off('dragstart')
            .on('dragstart', e => this.onBlueprintButtonDragStart(e))
            .off('dragend')
            .on('dragend', e => this.onBlueprintButtonDragEnd(e));

        $('.blueprintExport')
            .off('click')
            .on('click', e => this.onBlueprintExport(e));

        $('.blueprintDelete')
            .off('click')
            .on('click', e => this.onBlueprintDelete(e));

        $('.revisionBtn')
            .off('click')
            .on('click', e => this.onRevisionBtn(e));
    }

    async importDraft()
    {
        try {
            await this.page.loadFromImport()
        } catch(er) {
            return await this.showError('Could not import OPC page JSON: ' + er.error.message);
        }

        this.iframe.onPageLoad();

        let unmappedCount = this.page.offscreenAreas.length;

        if (unmappedCount === 0) {
            this.showMessageBox(
                this.messages.opcImportSuccess,
                this.messages.opcImportSuccessTitle,
            );
        } else {
            if (unmappedCount === 1) {
                this.showMessageBox(
                    this.messages.opcImportSuccess + '<br><br>' + this.messages.opcImportUnmappedS,
                    this.messages.opcImportSuccessTitle,
                );
            } else {
                this.showMessageBox(
                    this.messages.opcImportSuccess + '<br><br>' +
                    this.messages.opcImportUnmappedP.replace('%s', unmappedCount),
                    this.messages.opcImportSuccessTitle,
                );
            }

            $('[href="#pagetree"]').click();
            this.updatePagetreeBtn();
            this.setUnsaved(true, true);
        }
    }

    exportDraft()
    {
        this.page.exportAsDownload();
    }

    startHelp(e)
    {
        this.tutorial.start();
    }

    onBtnPreview()
    {
        if (this.inPreviewMode) {
            $(window.iframePanel).show();
            this.previewFrame.previewPanel.hide();
            this.inPreviewMode = false;
            $(window.disableVeil).hide();
        } else {
            $(window.iframePanel).hide();
            this.previewFrame.showPreview(this.page.fullUrl, JSON.stringify(this.page.toJSON()));
            this.inPreviewMode = true;
            $(window.disableVeil).show();
        }
    }

    async savePage()
    {
        this.showLoader();

        try {
            await this.page.save()
        } catch(error) {
            return await this.showError('Page could not be saved: ' + error.error.message);
        }

        this.hideLoader();
        this.updateRevisionList();
        this.setUnsaved(false, true);
    }

    setUnsaved(enable, record)
    {
        record = record || false;

        if(enable) {
            $(window.unsavedState).show();

            if(record) {
                this.page.savePageToWebStorage();
                $(window.unsavedRevision).show();
            }
        } else {
            $(window.unsavedState).hide();

            if(record) {
                this.page.clearPageWebStorage();
                $(window.unsavedRevision).hide();
            }
        }
    }

    isPageUnsaved()
    {
        return window.unsavedState.style.display !== 'none';
    }

    async closeEditor()
    {
        await this.page.unlock();
        window.location = this.page.fullUrl;
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

        let portletBtn = $(e.target).closest('.portletButton');

        this.iframe.dragNewPortlet(
            portletBtn.data('portlet-class'),
            portletBtn.data('portlet-group'),
        );
    }

    onPortletButtonDragEnd(e)
    {
        this.iframe.dragNewPortlet();
        this.iframe.cleanUpDrag();
    }

    onBlueprintButtonDragStart(e)
    {
        initDragStart(e);

        let blueprintBtn = $(e.target).closest('.blueprintButton');

        this.iframe.dragNewBlueprint(blueprintBtn.data('blueprint-id'));
    }

    onBlueprintButtonDragEnd(e)
    {
        this.iframe.dragNewBlueprint();
        this.iframe.cleanUpDrag();
    }

    async onRevisionBtn(e)
    {
        let elm   = $(e.target).closest('a');
        let revId = elm.data('revision-id');

        this.showLoader();

        try {
            await this.page.loadRev(revId)
        } catch(er) {
            return await this.showError('Error while loading draft preview: ' + er.error.message);
        }

        this.iframe.onPageLoad();
        this.iframe.loadMissingPortletPreviewStyles();
        this.updatePagetreeBtn();
        this.setUnsaved(revId !== 0);
    }

    async openConfigurator(portlet)
    {
        let portletData = portlet.data('portlet');

        this.setImageSelectCallback(noop);

        this.curPortlet = portlet;

        let html = await this.io.getConfigPanelHtml(
            portletData.class,
            portletData.missingClass,
            portletData.properties
        )

        if (portletData.class === 'MissingPortlet') {
            $(window.stdConfigButtons).hide();
            $(window.missingConfigButtons).show();
        } else {
            $(window.stdConfigButtons).show();
            $(window.missingConfigButtons).hide();
        }

        $(window.configModalBody).html(html);
        window.configPortletName.textContent = portletData.title;
        $(window.configModal).modal('show');
    }

    async saveConfig()
    {
        opc.emit('save-config');

        let portletData  = this.page.portletToJSON(this.curPortlet);
        let configObject = $(window.configForm).serializeControls();

        for(let propname in configObject) {
            if(configObject.hasOwnProperty(propname)) {
                let propval   = configObject[propname];
                let propInput = $('#config-' + propname);

                if (propInput.length > 0) {
                    let propType = propInput.data('prop-type');

                    if (propType === 'json') {
                        propval = JSON.parse(propval);
                    } else if (propType === 'datetime') {
                        propval = this.page.encodeDate(propval);
                    } else if (propInput[0].type === 'checkbox') {
                        propval = propval === '1';
                    } else if (propInput[0].type === 'number') {
                        propval = parseInt(propval);
                    } else if (propInput.prop('name') === 'video-yt-id') {
                        let match = propval.match(/^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#&?]*).*/);
                        if (match && match[7] !== undefined) {
                            propval = match[7];
                        }
                    } else if (propInput.prop('name') === 'video-vim-id') {
                        let match = propval.match(/^.*(vimeo\.com\/)((channels\/[A-z]+\/)|(groups\/[A-z]+\/videos\/))?([0-9]+)/);
                        if (match && match[5] !== undefined) {
                            propval = match[5];
                        }
                    }
                }

                configObject[propname] = propval;
            }
        }

        portletData.properties = configObject;
        let preview = null;

        try {
            preview = await this.io.getPortletPreviewHtml(portletData);
        } catch(er) {
            $(window.configModal).modal('hide');
            return await this.showError('Error while saving Portlet configuration: ' + er.error.message);
        }

        this.iframe.replaceSelectedPortletHtml(preview);
        $(window.configModal).modal('hide');
        this.page.updateFlipcards();
        this.iframe.disableLinks();
    }

    async createBlueprint()
    {
        if(this.selectedElm !== null) {
            let blueprintName = window.blueprintName.value;
            let blueprintData = this.page.portletToJSON(this.iframe.selectedElm);

            await this.io.saveBlueprint(blueprintName, blueprintData);
            this.updateBlueprintList();
            $(window.blueprintModal).modal('hide');
        }
    }

    onBlueprintDelete(e)
    {
        let elm   = $(e.target).closest('.blueprintDelete');
        let title = elm.closest('.blueprintButton').find('.blueprintTitle').text();

        $('#blueprintDeleteTitle').text(title);
        window.blueprintDeleteId.value = elm.data('blueprint-id');
        $(window.blueprintDeleteModal).modal('show');
    }

    async onBlueprintExport(e)
    {
        let elm         = $(e.target).closest('.blueprintExport');
        let blueprintId = elm.data('blueprint-id');
        let blueprint   = await this.io.getBlueprint(blueprintId);
        download(JSON.stringify(blueprint), blueprint.name + '.json', 'application/json');
    }

    importBlueprint()
    {
        $('<input type="file" accept=".json">')
            .on(
                'change',
                e => {
                    this.importReader = new FileReader();
                    this.importReader.onload = async () => {
                        let blueprint = JSON.parse(this.importReader.result);
                        await this.io.saveBlueprint(blueprint.name, blueprint.instance);
                        this.updateBlueprintList();
                    };
                    this.importReader.readAsText(e.target.files[0]);
                }
            )
            .click();
    }

    async deleteBlueprint()
    {
        let blueprintId = parseInt(window.blueprintDeleteId.value);

        await this.io.deleteBlueprint(blueprintId)
        this.updateBlueprintList();
        $(window.blueprintDeleteModal).modal('hide');
    }

    publishDraft()
    {
        if(typeof this.page.publishFrom === 'string' && this.page.publishFrom.length > 0) {
            this.setPublishSchedule();
            window.publishFrom.value = this.page.publishFrom;

            if(typeof this.page.publishTo === 'string' && this.page.publishTo.length > 0) {
                this.unsetInfiniteSchedule();
                window.publishTo.value = this.page.publishTo;
            } else {
                this.setInfiniteSchedule();
            }
        } else {
            this.setPublishNow();
        }

        window.draftName.value = this.page.name;
        $(window.publishModal).modal('show');
    }

    onChangePublishStrategy()
    {
        if (window.checkPublishNot.checked) {
            this.setUnpublished();
        } else if (window.checkPublishNow.checked) {
            this.setPublishNow();
        } else {
            this.setPublishSchedule();
        }
    }

    onChangePublishInfinite()
    {
        if(window.checkPublishInfinite.checked) {
            this.setInfiniteSchedule();
        } else {
            this.unsetInfiniteSchedule();
        }
    }

    setUnpublished()
    {
        window.checkPublishNot.checked = true;
        window.publishFrom.disabled = true;
        window.publishFrom.value = opc.messages.notScheduled;
        window.publishTo.disabled = true;
        window.publishTo.value = opc.messages.indefinitePeriodOfTime;
        window.checkPublishInfinite.checked = true;
        window.checkPublishInfinite.disabled = true;
    }

    setPublishNow()
    {
        window.checkPublishNow.checked = true;
        window.publishFrom.disabled = true;
        window.publishFrom.value = opc.messages.now;
        window.publishTo.disabled = true;
        window.publishTo.value = opc.messages.indefinitePeriodOfTime;
        window.checkPublishInfinite.checked = true;
        window.checkPublishInfinite.disabled = true;
    }

    setPublishSchedule()
    {
        window.checkPublishSchedule.checked = true;
        window.publishFrom.disabled = false;
        window.publishFrom.value = moment().format(localDateFormat);
        window.checkPublishInfinite.disabled = false;
    }

    setInfiniteSchedule()
    {
        window.checkPublishInfinite.checked = true;
        window.publishTo.disabled = true;
        window.publishTo.value = opc.messages.indefinitePeriodOfTime;
        $(window.publishFrom).datetimepicker('maxDate', false);
    }

    unsetInfiniteSchedule()
    {
        window.checkPublishInfinite.checked = false;
        window.publishTo.disabled = false;
        window.publishTo.value = moment(window.publishFrom.value, localDateFormat).add(1, 'M').format(localDateFormat);
    }

    async publish()
    {
        this.page.name = window.draftName.value;
        $('#footerDraftName span').text(this.page.name);
        window.titlePageName.innerText = this.page.name;

        if (window.checkPublishNot.checked) {
            this.page.publishFrom = null;
        } else if (window.checkPublishNow.checked) {
            this.page.publishFrom = moment().format(localDateFormat);
        } else {
            let datetime = moment(window.publishFrom.value, localDateFormat);

            if (datetime.isValid() === false) {
                throw this.showError('Invalid From Date');
            }

            this.page.publishFrom = window.publishFrom.value;
        }

        if (window.checkPublishInfinite.checked) {
            this.page.publishTo = null;
        } else {
            let datetime = moment(window.publishTo.value, localDateFormat);

            if (datetime.isValid() === false) {
                throw this.showError('Invalid To Date');
            }

            this.page.publishTo = window.publishTo.value;
        }

        try {
            await this.page.publicate();
        } catch (er) {
            return await this.showError(er.error.message);
        }

        this.io.getDraftStatusHtml(this.page.key);

        if (this.isPageUnsaved()) {
            this.savePage();
        }

        $(window.publishModal).modal('hide');
    }

    selectImageProp(propName)
    {
        this.openElFinder((file, mediafilesBaseUrlPath) => {
            let url = file.url.slice(mediafilesBaseUrlPath.length);
            this.imageSelectCB(url, propName, file.url);
            window.configForm.querySelector('[name="' + propName + '"]').value = url;
            window.configForm.querySelector('#preview-img-' + propName).src = file.url;
        }, 'image');
    }

    selectVideoProp(propName)
    {
         this.openElFinder(file => {
             window.configForm.querySelector('[name="' + propName + '"]').value = file.url;
             window.configForm.querySelector('#preview-vid-' + propName).src = file.url;
             window.configForm.querySelector('#cont-preview-vid-' + propName).load();
         }, 'video');
    }

    openElFinder (callback, type)
    {
        openElFinder(callback, type);
    }

    restoreUnsaved()
    {
        window.unsavedRevision.click();
        $(window.restoreUnsavedModal).modal('hide');
    }

    setImageSelectCallback(callback)
    {
        this.imageSelectCB = callback;
    }

    setIconPickerCallback(callback)
    {
        this.iconPickerCB = callback;
    }

    setDisplayFrameWidth(btn, value)
    {
        window.iframe.style.width = value;
        this.previewFrame.previewFrame.width(value);
        $('#displayWidths .active').removeClass('active');
        $(btn).addClass('active');
    }

    setDisplayWidthXS()
    {
        this.setDisplayFrameWidth(event.currentTarget, '375px');
    }

    setDisplayWidthSM()
    {
        this.setDisplayFrameWidth(event.currentTarget, '577px');
    }

    setDisplayWidthMD()
    {
        this.setDisplayFrameWidth(event.currentTarget, '769px');
    }

    setDisplayWidthLG()
    {
        this.setDisplayFrameWidth(event.currentTarget, '993px');
    }

    setDisplayWidthXL()
    {
        this.setDisplayFrameWidth(event.currentTarget, '100%');
    }

    onBeginEditDraftName()
    {
        $('#footerDraftName').hide();
        $('#footerDraftNameInput').val(this.page.name).show();
    }

    async onFinishEditDraftName()
    {
        let draftNameSpan = $('#footerDraftName');
        let draftNameInput = $('#footerDraftNameInput');
        let draftName = draftNameInput.val();

        if(draftName === '' || this.escapedDraftNameInput === true) {
            this.escapedDraftNameInput = false;
            draftNameSpan.show();
        } else {
            await this.io.changeDraftName(this.page.key, draftName);
            this.page.name = draftName;
            $('#footerDraftName span').text(draftName);
            window.titlePageName.innerText = draftName;
        }

        draftNameInput.hide();
        draftNameSpan.show();
    }

    onDraftNameInputKeydown()
    {
        if (event.key === 'Enter') {
            $('#footerDraftNameInput').blur();
        } else if(event.key === 'Escape') {
            this.escapedDraftNameInput = true;
            $('#footerDraftNameInput').blur();
        }
    }

    noRestoreUnsaved()
    {
        this.setUnsaved(false, true);
    }
}
