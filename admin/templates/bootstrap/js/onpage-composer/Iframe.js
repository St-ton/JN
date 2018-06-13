/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

function Iframe(io, gui, page, shopUrl, templateUrl)
{
    bindProtoOnHandlers(this);

    this.io          = io;
    this.gui         = gui;
    this.page        = page;
    this.shopUrl     = shopUrl;
    this.templateUrl = templateUrl;

    this.draggedElm         = null;
    this.hoveredElm         = null;
    this.selectedElm        = null;
    this.dropTarget         = null;
    this.previewMode        = false;
    this.dragNewPortletCls  = null;
    this.dragNewBlueprintId = 0;
}

Iframe.prototype = {

    constructor: Iframe,

    init: function(loadCB, pagetree)
    {
        debuglog('Iframe init');

        installGuiElements(this, [
            'iframe',
            'portletToolbar',
            'portletLabel',
            'portletPreviewLabel',
            'btnConfig',
            'btnClone',
            'btnBlueprint',
            'btnParent',
            'btnTrash',
        ]);

        this.pagetree = pagetree;

        this.iframe
            .attr('src', this.getIframePageUrl())
            .on('load', this.onIframeLoad.bind(this, loadCB || noop));
    },

    getIframePageUrl: function()
    {
        var pageUrlLink = document.createElement('a');

        pageUrlLink.href = this.page.fullUrl;

        if(pageUrlLink.search !== '') {
            pageUrlLink.search += '&opcEditMode=yes';
        } else {
            pageUrlLink.search = '?opcEditMode=yes';
        }

        pageUrlLink.search += '&opcEditedPageKey=' + this.page.key;

        return pageUrlLink.href.toString();
    },

    onIframeLoad: function(loadCB)
    {
        debuglog('Iframe onIframeLoad');

        this.ctx = this.iframe[0].contentWindow;
        this.jq  = this.ctx.$;

        this.head = this.jq('head');
        this.body = this.jq('body');

        this.loadStylesheet(this.templateUrl + 'css/onpage-composer/iframe.css');
        this.loadScript('https://unpkg.com/popper.js/dist/umd/popper.min.js', this.onPopperLoad);

        this.jq('a, button')      // disable links and buttons that could change the current iframe page
            .off('click')
            .attr('onclick', '')
            .click(function(e) { e.preventDefault(); });

        this.portletPreviewLabel.appendTo(this.body);
        this.portletToolbar.appendTo(this.body);
        this.page.initIframe(this.jq, this.onPageLoad.bind(this, loadCB));
    },

    onPopperLoad: function()
    {
        debuglog('Iframe onPopperLoad');

        this.toolbarPopper      = this.makePopper(this.portletToolbar);
        this.previewLabelPopper = this.makePopper(this.portletPreviewLabel);
    },

    makePopper: function(elm)
    {
        return new this.ctx.Popper(
            document.body,
            elm[0],
            {placement: 'top-start', modifiers: {computeStyle: {gpuAcceleration: false }}}
        );
    },

    onPageLoad: function(loadCB)
    {
        debuglog('Iframe onPageLoad');

        loadCB = loadCB || noop;

        this.enableEditingEvents();
        this.updateDropTargets();
        this.pagetree.render();
        this.gui.hideLoader();

        loadCB();
    },

    updateDropTargets: function()
    {
        this.stripDropTargets();
        this.areas().append('<div class="opc-droptarget">');
        this.portlets().before('<div class="opc-droptarget">');
    },

    stripDropTargets: function()
    {
        this.dropTargets().remove();
    },

    areas: function()
    {
        return this.jq('.opc-area');
    },

    portlets: function()
    {
        return this.jq('[data-portlet]');
    },

    dropTargets: function()
    {
        return this.jq('.opc-droptarget');
    },

    loadStylesheet: function(url, isLess)
    {
        this
            .jq('<link rel="stylesheet' + (isLess ? '/less' : '') + '" href="' + url + '">')
            .appendTo(this.head);
    },

    loadScript: function(url, callback)
    {
        var script = this.ctx.document.createElement('script');

        script.src = url;
        script.addEventListener('load', callback || noop);

        this.head[0].appendChild(script);
    },

    enableEditingEvents: function()
    {
        this.disableEditingEvents();

        this.page.rootAreas
            .on('mouseover', this.onPortletMouseOver)
            .on('click', this.onPortletClick)
            .on('dblclick', this.onBtnConfig)
            .on('dragstart', this.onPortletDragStart)
            .on('dragend', this.onPortletDragEnd)
            .on('dragover', this.onPortletDragOver)
            .on('drop', this.onPortletDrop);

        this.jq(this.ctx.document)
            .on('keydown', this.onKeyDown);
    },

    disableEditingEvents: function()
    {
        this.page.rootAreas
            .off('mouseover')
            .off('click')
            .off('dblclick')
            .off('dragstart')
            .off('dragend')
            .off('dragover')
            .off('drop');

        this.jq(this.ctx.document)
            .off('keydown');
    },

    onPortletMouseOver: function(e)
    {
        this.setHovered(this.findSelectableParent(this.jq(e.target)));
    },

    onPortletClick: function(e)
    {
        this.setSelected(this.findSelectableParent(this.jq(e.target)));
    },

    onPortletDragStart: function(e)
    {
        initDragStart(e);
        this.setDragged(this.findSelectableParent(this.jq(e.target)));
    },

    findSelectableParent: function(elm)
    {
        while(!this.isSelectable(elm) && !elm.is(this.page.rootAreas)) {
            elm = elm.parent();
        }

        return this.isSelectable(elm) ? elm : undefined;
    },

    onPortletDragEnd: function(e)
    {
        this.cleanUpDrag();
    },

    cleanUpDrag: function()
    {
        this.setDragged();
        this.setDropTarget();
        this.toolbarPopper.update();
        this.previewLabelPopper.update();
    },

    onPortletDragOver: function(e)
    {
        var elm = this.jq(e.target);

        if(elm.hasClass('opc-droptarget') && !this.isDescendant(elm, this.draggedElm)) {
            this.setDropTarget(elm);
        }
        else {
            this.setDropTarget();
        }

        e.preventDefault();
    },

    onPortletDrop: function(e)
    {
        debuglog('Iframe onPortletDrop');

        if(this.dropTarget !== null) {
            var oldArea = this.draggedElm.parent();

            this.dropTarget.replaceWith(this.draggedElm);
            this.updateDropTargets();
            this.setSelected(this.draggedElm);

            if(this.dragNewPortletCls) {
                this.newPortletDropTarget = this.draggedElm;
                this.setSelected();
                this.io.createPortlet(this.dragNewPortletCls, this.onNewPortletCreated);
            } else if(this.dragNewBlueprintId > 0) {
                this.newPortletDropTarget = this.draggedElm;
                this.setSelected();
                this.io.getBlueprintPreview(this.dragNewBlueprintId, this.onNewPortletCreated);
            } else {
                this.pagetree.updateArea(oldArea);
                this.pagetree.updateArea(this.draggedElm.parent());
                this.setSelected(this.draggedElm);
                this.gui.setUnsaved(true, true);
            }
        }
    },

    onNewPortletCreated: function(data)
    {
        var newElement = this.createPortletElm(data);

        this.newPortletDropTarget.replaceWith(newElement);

        var newArea = newElement.parent();

        this.pagetree.updateArea(newArea);
        this.setSelected(newElement);
        this.updateDropTargets();
        this.gui.setUnsaved(true, true);
    },

    createPortletElm: function(previewHtml)
    {
        return this.jq(previewHtml);
    },

    setDragged: function(elm)
    {
        elm = elm || null;

        if(this.draggedElm !== null) {
            this.draggedElm.removeClass('opc-dragged');
        }

        if(elm !== null) {
            elm.addClass('opc-dragged');
        }

        this.draggedElm = elm;
    },

    setHovered: function(elm)
    {
        elm = elm || null;

        if(this.hoveredElm !== null) {
            this.hoveredElm.removeClass('opc-hovered');
            this.hoveredElm.attr('draggable', 'false');
            this.portletPreviewLabel.hide();
        }

        if(elm !== null) {
            elm.addClass('opc-hovered');
            elm.attr('draggable', 'true');
            this.portletPreviewLabel.text(elm.data('portlet').title).show();
            this.previewLabelPopper.reference = elm[0];
            this.previewLabelPopper.update();
        }

        this.hoveredElm = elm;
    },

    setSelected: function(elm)
    {
        elm = elm || null;

        if(elm === null || !elm.is(this.selectedElm)) {
            if(this.selectedElm !== null) {
                this.selectedElm.removeClass('opc-selected');
                this.portletToolbar.hide();
            }

            if(elm !== null) {
                var portletData = elm.data('portlet');
                elm.addClass('opc-selected');
                this.portletLabel.text(portletData ? portletData.title : '');
                this.portletToolbar.show();
                this.toolbarPopper.reference = elm[0];
                this.toolbarPopper.update();
            }

            this.selectedElm = elm;
        }

        this.pagetree.setSelected(this.selectedElm);
    },

    setDropTarget: function(elm)
    {
        elm = elm || null;

        if(this.dropTarget !== null) {
            this.dropTarget.removeClass('opc-active-droptarget');
        }

        if(elm !== null) {
            elm.addClass('opc-active-droptarget');
        }

        this.dropTarget = elm;
    },

    dragNewPortlet: function(cls)
    {
        this.dragNewPortletCls = cls || null;
        this.setDragged(this.jq('<i class="fa fa-spinner fa-pulse"></i>'));
    },

    dragNewBlueprint: function(id)
    {
        this.dragNewBlueprintId = id || 0;
        this.setDragged(this.jq('<i class="fa fa-spinner fa-pulse"></i>'));
    },

    togglePreview: function()
    {
        if (this.previewMode) {
            this.updateDropTargets();
            this.enableEditingEvents();
            this.portlets().removeClass('opc-preview');
            this.previewMode = false;
        }
        else {
            this.stripDropTargets();
            this.disableEditingEvents();
            this.setSelected();
            this.setHovered();
            this.portlets().addClass('opc-preview');
            this.previewMode = true;
        }
    },

    onBtnConfig: function()
    {
        this.gui.openConfigurator(this.selectedElm);
    },

    replaceSelectedPortletHtml: function(html)
    {
        var newPortlet = this.jq(html);

        this.selectedElm.replaceWith(newPortlet);

        var area = newPortlet.parent();

        this.pagetree.updateArea(area);
        this.setSelected(newPortlet);
        this.updateDropTargets();
        this.gui.setUnsaved(true, true);
    },

    onBtnClone: function()
    {
        if(this.selectedElm !== null) {
            var area = this.selectedElm.parent();
            var copiedElm = this.selectedElm.clone();
            copiedElm.insertAfter(this.selectedElm);
            copiedElm.removeClass('opc-selected');
            copiedElm.removeClass('opc-hovered');
            this.pagetree.updateArea(area);
            this.setSelected(this.selectedElm);
            this.updateDropTargets();
            this.gui.setUnsaved(true, true);
        }
    },

    onBtnBlueprint: function()
    {
        if(this.selectedElm !== null) {
            this.gui.blueprintModal.modal('show');
        }
    },

    onBtnParent: function()
    {
        if(this.selectedElm !== null) {
            var elm = this.findSelectableParent(this.selectedElm.parent());

            if (this.isSelectable(elm)) {
                this.setSelected(elm);
            }
        }
    },

    onBtnTrash: function()
    {
        if(this.selectedElm !== null) {
            var area = this.selectedElm.parent();
            this.selectedElm.remove();
            this.pagetree.updateArea(area);
            this.setSelected();
            this.updateDropTargets();
            this.gui.setUnsaved(true, true);
        }
    },

    onKeyDown: function(e)
    {
        if(e.key === 'Delete' && this.selectedElm !== null) {
            this.onBtnTrash(e);
        }
    },

    isSelectable: function(elm)
    {
        return elm && elm.is('[data-portlet]');
    },

    isDescendant: function(descendant, tree)
    {
        return tree && tree.has(descendant).length > 0;
    },
};