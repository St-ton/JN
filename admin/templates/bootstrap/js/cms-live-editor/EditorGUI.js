/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

function EditorGUI(editor)
{
    this.editor      = editor;
    this.templateUrl = editor.templateUrl;
    this.previewMode = false;
    this.draggedElm  = null;
    this.hoveredElm  = null;
    this.selectedElm = null;
    this.dropTarget  = null;
    this.noop        = function() {};
}

EditorGUI.prototype = {

    constructor: EditorGUI,

    initHostGUI: function()
    {
        this.hostJq                   = $;
        this.iframe                   = this.hostJq('#iframe');
        this.loaderModal              = this.hostJq('#loader-modal');
        this.errorModal               = this.hostJq('#error-modal');
        this.templateModal            = this.hostJq('#template-modal');
        this.errorAlert               = this.hostJq('#error-alert');
        this.portletLabel             = this.hostJq('#portlet-label');
        this.portletToolbar           = this.hostJq('#pinbar');
        this.portletBtns              = this.hostJq('.portlet-button');
        this.exportPageBtn            = this.hostJq('#btn-export')                .click(this.onExportPage.bind(this));
        this.importPageBtn            = this.hostJq('#btn-import')                .click(this.onImportPage.bind(this));
        this.previewBtn               = this.hostJq('#btn-preview')               .click(this.onPreview.bind(this));
        this.editorCloseBtn           = this.hostJq('#cle-btn-close-editor')      .click(this.onEditorClose.bind(this));
        this.editorSaveBtn            = this.hostJq('#cle-btn-save-editor')       .click(this.onEditorSave.bind(this));
        this.selectParentBtn          = this.hostJq('#btn-parent')                .click(this.onSelectParent.bind(this));
        this.storeTemplateBtn         = this.hostJq('#btn-template')              .click(this.onStoreTemplate.bind(this));
        this.trashBtn                 = this.hostJq('#btn-trash')                 .click(this.onTrash.bind(this));
        this.cloneBtn                 = this.hostJq('#btn-clone')                 .click(this.onClone.bind(this));
        this.configBtn                = this.hostJq('#btn-config')                .click(this.onConfig.bind(this));
        this.configForm               = this.hostJq('#config-form')               .submit(this.onConfigSave.bind(this));
        this.configModal              = this.hostJq('#config-modal');
        this.configModalBody          = this.hostJq('#config-modal-body');
        this.templateDeleteModal      = this.hostJq('#template-delete-modal');
        this.templateDeleteModalInput = this.hostJq('#template-ktemplate');
        this.templateForm             = this.hostJq('#template-form')             .submit(this.onTemplateSave.bind(this));
        this.templateDeleteBtn        = this.hostJq('.template-delete')           .click(this.onTemplateDelete.bind(this));
        this.templateDeleteForm       = this.hostJq('#template-delete-form')      .submit(this.onTemplateDeleteConfirm.bind(this));
        this.revisionList             = this.hostJq('#revision-list');
        this.templateBtnBlueprint     = this.hostJq('#template-btn-blueprint');
        this.templateList             = this.hostJq('#templates');
        this.helpBtn                  = this.hostJq('#help')                      .click(this.onHelp.bind(this));
        this.tourModal                = this.hostJq('#tour-modal')                .submit(this.onTakeTour.bind(this));
        this.collapseContent          = this.hostJq('#collapse-content');
        this.collapseGroup            = this.hostJq('.collapse');

        this.portletBtns
            .on('dragstart', this.onPortletBtnDragStart.bind(this))
            .on('dragend', this.onPortletBtnDragEnd.bind(this));

        function toggleChevron(e) {
            $(e.target)
                .prev('.collapse-groups')
                .find('i.fa')
                .toggleClass('fa-plus-circle fa-minus-circle');
        }

        this.collapseGroup.on('hidden.bs.collapse', toggleChevron);
        this.collapseGroup.on('shown.bs.collapse', toggleChevron);
        this.collapseContent.collapse('show');

        this.revisionBtns();
        this.updateTemplateList();
    },

    initIframeGUI: function()
    {
        this.iframeCtx  = this.iframe[0].contentWindow;
        this.iframeJq   = this.iframeCtx.$;
        this.iframeBody = this.iframeJq('body');
        this.loadIframeStylesheet(this.templateUrl + 'css/cms-live-editor-iframe.css');

        this.iframeJq('a, button')          // disable links and buttons that could change the current iframes location
            .off('click')
            .attr('onclick', '')
            .click(function(e) { e.preventDefault(); });

        this.rootAreas = this.iframeJq('.cle-rootarea');
        this.portletLabel.appendTo(this.iframeBody);
        this.portletToolbar.appendTo(this.iframeBody);

        this.enableEditingEvents();
    },

    revisionBtns: function()
    {
        return this.hostJq('.revision-btn')
            .off('click').click(this.onRevision.bind(this));
    },

    templateBtns: function()
    {
        return this.hostJq('.template-button')
            .off('dragstart').on('dragstart', this.onTemplateBtnDragStart.bind(this))
            .off('dragend').on('dragend', this.onPortletBtnDragEnd.bind(this));
    },

    templateDeleteBtns: function()
    {
        return this.hostJq('.template-delete')
            .off('click').click(this.onTemplateDelete.bind(this));
    },

    areas: function()
    {
        return this.iframeJq('.cle-area');
    },

    portlets: function()
    {
        return this.iframeJq('[data-portletid]');
    },

    dropTargets: function()
    {
        return this.iframeJq('.cle-droptarget');
    },

    enableEditingEvents: function()
    {
        this.rootAreas
            .on('mouseover', this.onPortletMouseOver.bind(this))
            .on('click', this.onPortletClick.bind(this))
            .on('dblclick', this.onConfig.bind(this))
            .on('dragstart', this.onPortletDragStart.bind(this))
            .on('dragend', this.onPortletDragEnd.bind(this))
            .on('dragover', this.onPortletDragOver.bind(this))
            .on('drop', this.onPortletDrop.bind(this));

        this.iframeJq(this.iframeCtx.document)
            .on('keydown', this.onKeyDown.bind(this));
    },

    disableEditingEvents: function()
    {
        this.rootAreas
            .off('mouseover')
            .off('click')
            .off('dblclick')
            .off('dragstart')
            .off('dragend')
            .off('dragover')
            .off('drop');

        this.iframeJq(this.iframeCtx.document)
            .off('keydown');
    },

    loadIframeStylesheet: function(url)
    {
        this
            .iframeJq('<link rel="stylesheet" href="' + url + '">')
            .appendTo(this.iframeJq('head'));
    },

    showError: function(msg)
    {
        this.loaderModal.modal('hide');
        this.errorAlert.html(msg);
        this.errorModal.modal('show');
        throw msg;
    },

    showLoader: function()
    {
        this.errorModal.modal('hide');
        this.loaderModal.modal('show');
    },

    hideLoader: function()
    {
        this.loaderModal.modal('hide');
    },

    setUnsaved: function(enable)
    {
        this.editorSaveBtn.find('i').html(enable ? '*' : '');
    },

    updateDropTargets: function()
    {
        this.stripDropTargets();
        this.areas().append('<div class="cle-droptarget">');
        this.portlets().before('<div class="cle-droptarget">');
    },

    stripDropTargets: function()
    {
        this.dropTargets().remove();
    },

    createPortlet: function(content, id, title, props)
    {
        var portlet = this.iframeJq(content);

        if (id !== undefined) {
            portlet.attr('data-portletid', id);
        }

        if (title !== undefined) {
            portlet.attr('data-portlettitle', title);
        }

        if (props !== undefined) {
            portlet.attr('data-properties', JSON.stringify(props));
        }

        return portlet;
    },

    isSelectable: function(elm)
    {
        return elm.is('[data-portletid]');
    },

    isDescendant: function(descendant, tree)
    {
        return tree.has(descendant).length > 0;
    },

    setDragged: function(elm)
    {
        elm = elm || null;

        if(this.draggedElm !== null) {
            this.draggedElm.removeClass('cle-dragged');
        }

        this.draggedElm = elm;

        if(this.draggedElm !== null) {
            this.draggedElm.addClass('cle-dragged');
        }
    },

    setHovered: function(elm)
    {
        elm = elm || null;

        if(this.hoveredElm !== null) {
            this.hoveredElm.removeClass('cle-hovered');
            this.hoveredElm.attr('draggable', 'false');
            this.portletLabel.hide();
        }

        this.hoveredElm = elm;

        if(this.hoveredElm !== null) {
            this.hoveredElm.addClass('cle-hovered');
            this.hoveredElm.attr('draggable', 'true');
            this.portletLabel
                .text(this.hoveredElm.data('portlettitle'))
                .show()
                .css({
                    left: elm.offset().left + 'px',
                    top: elm.offset().top - this.portletLabel.outerHeight() + 'px'
                })
        }
    },

    setSelected: function(elm)
    {
        elm = elm || null;

        if(elm === null || !elm.is(this.selectedElm)) {
            if(this.selectedElm !== null) {
                this.selectedElm.removeClass('cle-selected');
                this.portletToolbar.hide();
            }

            this.selectedElm = elm;

            if(this.selectedElm !== null) {
                this.selectedElm.addClass('cle-selected');
                this.portletToolbar
                    .show()
                    .css({
                        left: elm.offset().left + elm.outerWidth() - this.portletToolbar.outerWidth() + 'px',
                        top: elm.offset().top + elm.outerHeight() + 'px'
                    });
            }
        }
    },

    setDropTarget: function(elm)
    {
        elm = elm || null;

        if(this.dropTarget !== null) {
            this.dropTarget.removeClass('cle-active-droptarget');
        }

        this.dropTarget = elm;

        if(this.dropTarget !== null) {
            this.dropTarget.addClass('cle-active-droptarget');
        }
    },

    cleanUpDrag: function()
    {
        this.setDragged();
        this.setDropTarget();
    },

    openConfigurator: function(portletTitle, portletId, properties)
    {
        this.configSaveCallback = this.noop;

        ioCall('getPortletConfigPanelHtml', [portletId, properties], getConfigPanelSuccess.bind(this));

        function getConfigPanelSuccess(configPanelHtml)
        {
            this.configModalBody.html(configPanelHtml);
            this.configModal.find('.modal-title').html(portletTitle + ' Einstellungen');
            this.configModal.modal('show');
            this.curPortletId = portletId;
        }
    },

    togglePreview: function()
    {
        if (this.previewMode) {
            this.updateDropTargets();
            this.enableEditingEvents();
            this.portlets().removeClass('cle-preview');
            this.previewMode = false;
        }
        else {
            this.stripDropTargets();
            this.disableEditingEvents();
            this.setSelected();
            this.setHovered();
            this.portlets().addClass('cle-preview');
            this.previewMode = true;
        }
    },

    clearPage: function()
    {
        this.rootAreas.empty();
        this.updateDropTargets();
    },

    displayPage: function(page)
    {
        var self = this;

        $.each(
            page.cPreviewHtml_arr,
            function (areaId, html)
            {
                self.iframeJq('#' + areaId).html(html);
            }
        );
    },

    updateRevisionList: function()
    {
        ioCall('getCmsPageRevisions', [this.editor.cPageIdHash], this.onGetRevisions.bind(this));
    },

    updateTemplateList: function()
    {
        ioCall('getCmsTemplates', [], this.onGetTemplates.bind(this));
    },

    onGetRevisions: function(revisions)
    {
        var self = this;

        this.revisionList.empty();

        revisions.forEach(function(rev) {
            $('<a class="list-group-item revision-btn" href="#" data-revision-id="' + rev.id + '">')
                .html(rev.timestamp)
                .appendTo(self.revisionList);
        });

        this.revisionBtns();
    },

    onGetTemplates: function(templates)
    {
        var self = this;

        this.templateList.empty();

        templates.forEach(function (template) {
            var newBtn = self.templateBtnBlueprint
                .clone()
                .attr('id', '')
                .css('display', '');
            newBtn
                .find('a')
                .attr('data-title', template.cName)
                .attr('data-template', template.kTemplate)
                .attr('data-content', template.fullPreviewHtml);
            newBtn
                .find('span')
                .html(template.cName);
            newBtn
                .find('button')
                .attr('data-template', template.kTemplate);
            newBtn
                .appendTo(self.templateList);
        });

        this.templateBtns();
        this.templateDeleteBtns();
    },

    onRevision: function(e)
    {
        var elm = $(e.target);

        this.editor.loadRevision(elm.data('revision-id'));
    },

    onExportPage: function(e)
    {
        download(JSON.stringify(this.editor.io.pageToJson()), 'page-export.json', 'text/plain');
    },

    onImportPage: function(e)
    {
        var self = this;

        $('<input type="file">')
            .change(function(e) {
                var file = e.target.files[0];
                var reader = new FileReader();

                reader.onload = function() {
                    self.clearPage();
                    self.editor.io.pageFromJson(JSON.parse(reader.result));
                };

                reader.readAsText(file);
            })
            .click();
    },

    onPreview: function(e)
    {
        this.togglePreview();
    },

    onEditorClose: function(e)
    {
        this.editor.closeEditor();
    },

    onEditorSave: function(e)
    {
        this.editor.saveEditorPage();
    },

    onSelectParent: function(e)
    {
        if(this.selectedElm !== null) {
            var elm = this.selectedElm.parent();

            while (!this.isSelectable(elm) && !elm.is(this.rootAreas)) {
                elm = elm.parent();
            }

            if (this.isSelectable(elm)) {
                this.setSelected(elm);
            }
        }
    },

    onStoreTemplate: function(e)
    {
        if(this.selectedElm !== null) {
            this.templateModal.modal('show');
        }
    },

    onTemplateSave: function(e)
    {
        if(this.selectedElm !== null) {
            var templateName = this.hostJq('#template-name').val();

            this.editor.storeTemplate(this.selectedElm, templateName);
            this.templateModal.modal('hide');
        }

        e.preventDefault();
    },

    onTemplateDelete: function(e)
    {
        var elm = $(e.target);
        this.templateDeleteModal.modal('show');
        this.templateDeleteModalInput.val(elm.data('template'));
    },

    onTemplateDeleteConfirm: function(e)
    {
        var self = this;
        var elm = this.templateDeleteModalInput.val();

        this.editor.io.deleteTemplate(elm, function() {
            self.updateTemplateList();
        });
        this.templateDeleteModal.modal('hide');

        e.preventDefault();
    },

    onTrash: function(e)
    {
        // TODO Editor: löschen bestätigen
        if(this.selectedElm !== null) {
            this.selectedElm.remove();
            this.setSelected();
            this.updateDropTargets();
            this.editor.io.savePageToWebStorage();
        }
    },

    onClone: function(e)
    {
        if(this.selectedElm !== null) {
            var copiedElm = this.selectedElm.clone();
            copiedElm.insertAfter(this.selectedElm);
            copiedElm.removeClass('cle-selected');
            copiedElm.removeClass('cle-hovered');
            this.setSelected(this.selectedElm);
            this.updateDropTargets();
            this.editor.io.savePageToWebStorage();
        }
    },

    onConfig: function(e)
    {
        this.openConfigurator(
            this.selectedElm.data('portlettitle'),
            this.selectedElm.data('portletid'),
            this.selectedElm.data('properties')
        );
    },

    onConfigSave: function(e)
    {
        this.configSaveCallback();

        var children = this.selectedElm
        // select descendant subareas or non-nested subareas
            .find('.cle-area').not(this.selectedElm.find('[data-portletid] .cle-area'));

        var properties = this.configForm.serializeControls();

        this.propertiesCallback = this.propertiesCallback || this.noop;
        this.propertiesCallback(properties);

        ioCall('getPortletPreviewHtml', [this.curPortletId, properties], onNewHtml.bind(this));

        function onNewHtml(newHtml)
        {
            var newPortlet = this.iframeJq(newHtml);
            var portletTitle = this.selectedElm.data('portlettitle');

            this.selectedElm.replaceWith(newPortlet);
            this.setSelected(newPortlet);
            this.selectedElm.attr('data-portletid', this.curPortletId);
            this.selectedElm.attr('data-portlettitle', portletTitle);
            this.selectedElm.attr('data-properties', JSON.stringify(properties));

            this.selectedElm
                .find('.cle-area')
                .each(function(index, subarea) {
                    if(index < children.length) {
                        this.iframeJq(subarea).html(
                            this.iframeJq(children[index]).html()
                        );
                    }
                }.bind(this));

            this.configModal.modal('hide');
            this.updateDropTargets();
            this.editor.io.savePageToWebStorage();
        }

        e.preventDefault();
    },

    onPortletBtnDragStart: function(e)
    {
        var portletBtn = this.hostJq(e.target).closest('.portlet-button');

        var portlet = this.createPortlet(
            portletBtn.data('content'),
            portletBtn.data('portletid'),
            portletBtn.data('portlettitle'),
            portletBtn.data('defaultprops')
        );

        this.setDragged(portlet);

        // firefox needs this
        e.originalEvent.dataTransfer.effectAllowed = 'move';
        e.originalEvent.dataTransfer.setData('text/html', '');
    },

    onPortletBtnDragEnd: function(e)
    {
        this.cleanUpDrag();
    },

    onTemplateBtnDragStart: function (e)
    {
        var templateBtn = this.hostJq(e.target).closest('.template-button');
        var template = this.createPortlet(templateBtn.data('content'));

        this.setDragged(template);

        // firefox needs this
        e.originalEvent.dataTransfer.effectAllowed = 'move';
        e.originalEvent.dataTransfer.setData('text/html', '');
    },

    onPortletMouseOver: function(e)
    {
        var elm = this.iframeJq(e.target);

        while(!this.isSelectable(elm) && !elm.is(this.rootAreas)) {
            elm = elm.parent();
        }

        this.setHovered(this.isSelectable(elm) ? elm : undefined);
    },

    onPortletClick: function(e)
    {
        var elm = this.iframeJq(e.target);

        while(!this.isSelectable(elm) && !elm.is(this.rootAreas)) {
            elm = elm.parent();
        }

        this.setSelected(this.isSelectable(elm) ? elm : undefined);
    },

    onPortletDragStart: function(e)
    {
        var elm = this.iframeJq(e.target);

        while(!this.isSelectable(elm) && !elm.is(this.rootElm)) {
            elm = elm.parent();
        }

        this.setDragged(elm);

        // firefox needs this
        e.originalEvent.dataTransfer.effectAllowed = 'move';
        e.originalEvent.dataTransfer.setData('text/html', '');
    },

    onPortletDragEnd: function(e)
    {
        this.cleanUpDrag();
    },

    onPortletDragOver: function(e)
    {
        var elm = this.iframeJq(e.target);

        if(elm.hasClass('cle-droptarget') && !this.isDescendant(elm, this.draggedElm)) {
            this.setDropTarget(elm);
        }
        else {
            this.setDropTarget();
        }

        e.preventDefault();
    },

    onPortletDrop: function(e)
    {
        if(this.dropTarget !== null) {
            this.dropTarget.replaceWith(this.draggedElm);
            this.setSelected();
            this.setSelected(this.draggedElm);
            this.updateDropTargets();
            this.editor.io.savePageToWebStorage();
        }
    },

    onKeyDown: function(e)
    {
        if(e.key === 'Delete' && this.selectedElm !== null) {
            this.onTrash(e);
        }
    },

    onHelp: function(e)
    {
        this.tourModal.modal('show');
    },

    onTakeTour: function(e)
    {
        this.tourModal.modal('hide');
        e.preventDefault();
        var tourID = this.hostJq('#tour-form input[name="help-tour"]:checked').val();

        function fixIframePos(element) {
            var off = element.offset();
            var pTop = $('#editor-top-nav').height();
            var pLeft = $('#sidebar-panel').outerWidth();

            element.offset({ top:off.top + pTop, left:off.left +pLeft});
        }

        function fixBackdrop() {
            var off = $('.tour-backdrop.top').offset();
            var pTop = $('#editor-top-nav').height();
            var pLeft = $('#sidebar-panel').outerWidth();
            var leftWidth = $('.tour-backdrop.left').width();

            $('.tour-backdrop.top').offset({ top:off.top + pTop});

            off = $('.tour-backdrop.left').offset();
            $('.tour-backdrop.left').offset({ top:off.top + pTop});
            $('.tour-backdrop.left').width(leftWidth + pLeft);

            off = $('.tour-backdrop.right').offset();
            $('.tour-backdrop.right').offset({ top:off.top + pTop, left:off.left +pLeft});

            off = $('.tour-backdrop.bottom').offset();
            $('.tour-backdrop.bottom').offset({ top:off.top + pTop});
        }

        // Todo Editor: debug ausschalten
        switch (tourID) {
            case 'ht1':
                var confModal = this.configModal;
                var tour = new Tour({
                    name: "tAllgemein",
                    debug: true,
                    orphan: true,
                    template: "<div class='popover tour'><div class='arrow'></div><h3 class='popover-title'></h3><div class='popover-content'></div><div class='popover-navigation'><button class='btn btn-default' data-role='prev'>« Prev</button><span data-role='separator'>|</span><button class='btn btn-default' data-role='next'>Next »</button><button class='btn btn-primary' data-role='end' style='margin-left: 15px;'>End tour</button></div></div>",
                    steps: [
                        {
                            backdrop: true,
                            title: "Willkommen",
                            content: "In dieser kurzen Einführung wollen wir dir einen Überblick über dieses neue Feature geben."
                        },
                        {
                            backdrop: true,
                            element: "#sidebar-panel",
                            title: "Aufteilung",
                            content: "Grundsätzlich ist der Editor in die zwei Bereich aufgeteilt.<br/>Hier siehst du die Sidebar."
                        },
                        {
                            backdrop: true,
                            element: "#iframe-panel",
                            placement: "top",
                            title: "Aufteilung",
                            content: "In diesem Bereich wird der aktuelle Stand deiner Bearbeitung gezeigt."
                        },
                        {
                            backdrop: true,
                            element: "#elements",
                            placement: "right",
                            title: "Portlets",
                            content: "Das ist eines unserer Portlets. Diese kannst du nutzen um deine Seiten mit Inhalt zu füllen."
                        },
                        {
                            backdrop: true,
                            element: $("#iframe").contents().find(".cle-rootarea > .cle-droptarget:first-child"),
                            placement: "top",
                            title: "Portlets",
                            content: "Die grauen Bereiche auf dieser Seite zeigen dir wo du Portlets ablegen kannst.",
                            onShown: function (tour) {
                                fixIframePos($('#step-4'));
                                fixBackdrop();
                            },
                        },
                        {
                            element: "#elements > .portlet-button:first-child",
                            placement: "bottom",
                            title: "Portlets",
                            reflex: 'dragend',
                            content: "Ziehe nun das Portlet 'Überschrift' in den obersten grauen Bereich und du hast den ersten Inhalt auf dieser Seite eingefügt."
                        },
                        {
                            element: $("#iframe").contents().find('#pinbar'),
                            placement: "left",
                            title: "Einstellungen",
                            onShown: function (tour) {
                                fixIframePos($('#step-6'));
                                confModal.off('shown').on('shown.bs.modal', function () {
                                    tour.next();
                                });
                            },
                            content: "An diesem Portlet siehst du eine Leiste mit verschiedenen Icons. Klicke auf das Zahnrad um die Einstellungen zu öffnen."
                        },
                        {
                            element: "#cle-btn-save-config",
                            placement: "bottom",
                            title: "Einstellungen",
                            reflex: true,
                            content: "Alle Portlets bieten verschiedene Einstellungen. Trage hier einen neuen Text für die Überschrift ein und klicke auf Speichern."
                        },
                        {
                            element: "#cle-btn-save-editor",
                            placement: "bottom",
                            title: "Seite Speichern",
                            reflex: true,
                            content: "Mit einem Klick auf das Speichern Symbol werden deine Änderungen übernommen und sind ab dann im Shop sichtbar."
                        },

                    ]
                });

                // Initialize the tour
                tour.init();
                $('.tour-tAllgemein-5-element').on('dragend', function () {
                    tour.next();
                });
                // Initialize the tour
                tour.start(true);
                break;
            case 'ht2':
                var tour2 = new Tour({
                    name: "tAnimation",
                    debug: true,
                    orphan: true,
                    template: "<div class='popover tour'><div class='arrow'></div><h3 class='popover-title'></h3><div class='popover-content'></div><div class='popover-navigation'><button class='btn btn-default' data-role='prev'>« Prev</button><span data-role='separator'>|</span><button class='btn btn-default' data-role='next'>Next »</button><button class='btn btn-primary' data-role='end' style='margin-left: 15px;'>End tour</button></div></div>",
                    steps: [
                        {
                            backdrop: true,
                            title: "Animationen",
                            content: "Lerne hier wie du Portlets mit einfachen Animationen erstellst."
                        }
                    ]
                });
                tour2.init();
                tour2.start(true);
                break;
            case 'ht3':
                var tour3 = new Tour({
                    name: "tTemplate",
                    debug: true,
                    orphan: true,
                    template: "<div class='popover tour'><div class='arrow'></div><h3 class='popover-title'></h3><div class='popover-content'></div><div class='popover-navigation'><button class='btn btn-default' data-role='prev'>« Prev</button><span data-role='separator'>|</span><button class='btn btn-default' data-role='next'>Next »</button><button class='btn btn-primary' data-role='end' style='margin-left: 15px;'>End tour</button></div></div>",
                    steps: [
                        {
                            backdrop: true,
                            title: "Templates",
                            content: "Lerne hier wie du Templates anlegst und wiederverwendest."
                        }
                    ]
                });
                tour3.init();
                tour3.start(true);
                break;
        }

    }

};