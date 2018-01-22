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
        this.hostJq             = $;
        this.iframe             = this.hostJq('#iframe');
        this.loaderModal        = this.hostJq('#loader-modal');
        this.errorModal         = this.hostJq('#error-modal');
        this.errorAlert         = this.hostJq('#error-alert');
        this.portletLabel       = this.hostJq('#portlet-label');
        this.portletToolbar     = this.hostJq('#pinbar');
        this.portletBtns        = this.hostJq('.portlet-button');
        this.previewBtn         = this.hostJq('#btn-preview')           .click(this.onPreview.bind(this));
        this.editorCloseBtn     = this.hostJq('#cle-btn-close-editor')  .click(this.onEditorClose.bind(this));
        this.editorSaveBtn      = this.hostJq('#cle-btn-save-editor')   .click(this.onEditorSave.bind(this));
        this.selectParent       = this.hostJq('#btn-parent')            .click(this.onSelectParent.bind(this));
        this.storeTemplateBtn   = this.hostJq('#btn-template')          .click(this.onStoreTemplate.bind(this));
        this.trashBtn           = this.hostJq('#btn-trash')             .click(this.onTrash.bind(this));
        this.cloneBtn           = this.hostJq('#btn-clone')             .click(this.onClone.bind(this));
        this.configBtn          = this.hostJq('#btn-config')            .click(this.onConfig.bind(this));
        this.configForm         = this.hostJq('#config-form')           .submit(this.onConfigSave.bind(this));
        this.configModal        = this.hostJq('#config-modal');
        this.configModalBody    = this.hostJq('#config-modal-body');

        this.portletBtns
            .on('dragstart', this.onPortletBtnDragStart.bind(this))
            .on('dragend', this.onPortletBtnDragEnd.bind(this));

        this.revisionBtns();
    },

    initIframeGUI: function()
    {
        this.iframeCtx  = this.iframe[0].contentWindow
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
        return this.hostJq('.revision-btn').off('click').click(this.onRevision.bind(this));
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
            .on('dblclick', this.onPortletConfig.bind(this))
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

        portlet.attr('data-portletid', id);
        portlet.attr('data-portlettitle', title);
        portlet.attr('data-properties', JSON.stringify(props));

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

    openConfigurator: function(portletId, properties)
    {
        this.configSaveCallback = this.noop;

        ioCall('getPortletConfigPanelHtml', [portletId, properties], getConfigPanelSuccess.bind(this));

        function getConfigPanelSuccess(configPanelHtml)
        {
            this.configModalBody.html(configPanelHtml);
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

    onRevision: function(e)
    {
        var elm = $(e.target);
        this.clearPage();
        this.editor.io.loadRevision(elm.data('revision-id'));
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
        var self = this;

        this.showLoader();

        this.editor.saveEditorPage(
            function() {
                self.hideLoader();
                self.setUnsaved(false);
            },
            function () {
                window.location.reload();
            }
        );
    },

    onSelectParent: function(e)
    {
        if(this.selectedElm !== null) {
            var elem = this.selectedElm.parent();
            while (elem.attr('data-portletid') == undefined && !elem.is(this.rootAreas)) {
                elem = elem.parent();
            }

            if (elem.attr('data-portletid') != undefined) {
                this.setSelected(elem);
            }
        }
    },

    onStoreTemplate: function(e)
    {
        if(this.selectedElm !== null) {
            // todo editor: define name of template, check if exists, confirm save
            /*this.openTemplateStoreDialog(
                this.selectedElm.data('portletid'),
                this.selectedElm.data('properties')
            );*/
            // todo Editor: Namen übergeben
            this.editor.storeTemplate(this.selectedElm);
        }
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
            this.selectedElm.data('portletid'),
            this.selectedElm.data('properties')
        );
    },

    onConfigSave: function(e)
    {
        this.configSaveCallback();

        var children = this.selectedElm
        // select direct descendant subareas or non-nested subareas
            .find('> .cle-area') ; //, :not(.jle-subarea) .jle-subarea');

        var properties = this.configForm.serializeControls();

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

    onPortletConfig: function(e)
    {
        this.openConfigurator(
            this.selectedElm.data('portletid'),
            this.selectedElm.data('properties')
        );
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

};