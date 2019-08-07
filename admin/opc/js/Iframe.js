/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

class Iframe
{
    constructor(io, gui, page, shopUrl, templateUrl)
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
        this.dragNewPortletCls  = null;
        this.dragNewBlueprintId = 0;
    }

    init(pagetree)
    {
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
            'dropTargetBlueprint',
        ]);

        this.pagetree = pagetree;

        return new Promise(res => {
            this.iframe
                .attr('src', this.getIframePageUrl())
                .on('load', res);
        }).then(() => {
            this.ctx  = this.iframe[0].contentWindow;
            this.jq   = this.ctx.$;
            this.head = this.jq('head');
            this.body = this.jq('body');

            this.loadStylesheet(this.shopUrl + '/admin/opc/css/iframe.css');

            this.loadScript(
                this.shopUrl + '/templates/NOVA/js/popper.min.js',
                () => {
                    this.toolbarPopper      = this.makePopper(this.portletToolbar);
                    this.previewLabelPopper = this.makePopper(this.portletPreviewLabel);
                }
            );

            this.jq('a, button')      // disable links and buttons that could change the current iframe page
                .off('click')
                .attr('onclick', '')
                .on('click', e => e.preventDefault());

            this.portletPreviewLabel.appendTo(this.body);
            this.portletToolbar.appendTo(this.body);

            return this.page.initIframe(this.jq)
                .catch(er => this.gui.showError('Error while loading draft preview: ' + er.toString()))
                .then(this.onPageLoad);
        })
    }

    getIframePageUrl()
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
    }

    makePopper(elm)
    {
        return new this.ctx.Popper(
            document.body,
            elm[0],
            {
                placement: 'top-start',
                modifiers: {computeStyle: {gpuAcceleration: false }, offset: {offset:"8,0"}},
                onUpdate: data => {
                    $(data.instance.popper).css('top', data.styles.top + 1 + "px");
                },
            }
        );
    }

    onPageLoad()
    {
        this.enableEditingEvents();
        this.updateDropTargets();
        this.pagetree.render();
        this.gui.hideLoader();
    }

    updateDropTargets()
    {
        this.stripDropTargets();
        this.areas().append(this.dropTargetBlueprint.clone().attr('id', '').show());
        this.portlets().before(this.dropTargetBlueprint.clone().attr('id', '').show());
    }

    stripDropTargets()
    {
        this.dropTargets().remove();
    }

    areas()
    {
        return this.jq('.opc-area');
    }

    portlets()
    {
        return this.jq('[data-portlet]');
    }

    dropTargets()
    {
        return this.jq('.opc-droptarget');
    }

    loadStylesheet(url, isLess)
    {
        this
            .jq('<link rel="stylesheet' + (isLess ? '/less' : '') + '" href="' + url + '">')
            .appendTo(this.head);
    }

    loadScript(url, callback)
    {
        var script = this.ctx.document.createElement('script');

        script.src = url;
        script.addEventListener('load', callback || noop);

        this.head[0].appendChild(script);
    }

    enableEditingEvents()
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
    }

    disableEditingEvents()
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
    }

    onPortletMouseOver(e)
    {
        this.setHovered(this.findSelectableParent(this.jq(e.target)));
    }

    onPortletClick(e)
    {
        this.setSelected(this.findSelectableParent(this.jq(e.target)));
    }

    onPortletDragStart(e)
    {
        initDragStart(e);
        this.setDragged(this.findSelectableParent(this.jq(e.target)));
    }

    findSelectableParent(elm)
    {
        while(!this.isSelectable(elm) && !elm.is(this.page.rootAreas)) {
            elm = elm.parent();
        }

        return this.isSelectable(elm) ? elm : undefined;
    }

    onPortletDragEnd(e)
    {
        this.cleanUpDrag();
    }

    cleanUpDrag()
    {
        this.setDragged();
        this.setDropTarget();
        this.toolbarPopper.update();
        this.previewLabelPopper.update();
    }

    onPortletDragOver(e)
    {
        var elm = this.jq(e.target);

        if(elm.parent().hasClass('opc-droptarget')) {
            elm = elm.parent();
        } else if(elm.parent().parent().hasClass('opc-droptarget')) {
            elm = elm.parent().parent();
        }

        if(elm.hasClass('opc-droptarget') && !this.isDescendant(elm, this.draggedElm)) {
            this.setDropTarget(elm);
        }
        else {
            this.setDropTarget();
        }

        e.preventDefault();
    }

    onPortletDrop(e)
    {
        if(this.dropTarget !== null) {
            var oldArea = this.draggedElm.parent();

            this.dropTarget.replaceWith(this.draggedElm);
            this.updateDropTargets();
            this.setSelected(this.draggedElm);

            if(this.dragNewPortletCls) {
                this.newPortletDropTarget = this.draggedElm;
                this.setSelected();
                this.io.createPortlet(this.dragNewPortletCls)
                    .catch(er => {
                        this.newPortletDropTarget.remove();
                        return this.gui.showError(er.error.message);
                    })
                    .then(this.onNewPortletCreated);
            } else if(this.dragNewBlueprintId > 0) {
                this.newPortletDropTarget = this.draggedElm;
                this.setSelected();
                this.io.getBlueprintPreview(this.dragNewBlueprintId)
                    .then(this.onNewPortletCreated);
            } else {
                this.pagetree.updateArea(oldArea);
                this.pagetree.updateArea(this.draggedElm.parent());
                this.setSelected(this.draggedElm);
                this.gui.setUnsaved(true, true);
            }
        }

        this.page.updateFlipcards();
    }

    onNewPortletCreated(data)
    {
        var newElement = this.createPortletElm(data);

        this.newPortletDropTarget.replaceWith(newElement);

        var newArea = newElement.parent();

        this.pagetree.updateArea(newArea);
        this.setSelected(newElement);
        this.updateDropTargets();
        this.gui.setUnsaved(true, true);
        this.page.updateFlipcards();

        if(this.dragNewPortletGroup === 'content') {
            this.gui.openConfigurator(newElement);
        }
    }

    createPortletElm(previewHtml)
    {
        return this.jq(previewHtml);
    }

    setDragged(elm)
    {
        elm = elm || null;

        if(this.draggedElm !== null) {
            this.draggedElm.removeClass('opc-dragged');
        }

        if(elm !== null) {
            elm.addClass('opc-dragged');
        }

        this.draggedElm = elm;
    }

    setHovered(elm)
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
    }

    setSelected(elm)
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
                this.scrollIntoView(elm);
            }

            this.selectedElm = elm;
        }

        this.pagetree.setSelected(this.selectedElm);
    }

    scrollIntoView(elm)
    {
        var offsTop    = elm.offset().top;
        var viewTop    = this.jq(this.ctx).scrollTop();
        var diffTop    = offsTop - 128 - viewTop;
        var viewBottom = viewTop + $(this.ctx).height();
        var diffBottom = offsTop + 128 + elm.height() - viewBottom;

        if(diffTop < 0) {
            this.ctx.scrollBy(0, diffTop);
        }

        if(diffBottom > 0) {
            this.ctx.scrollBy(0, diffBottom);
        }
    }

    setDropTarget(elm)
    {
        elm = elm || null;

        if(this.dropTarget !== null) {
            this.dropTarget.removeClass('opc-active-droptarget');
        }

        if(elm !== null) {
            elm.addClass('opc-active-droptarget');
        }

        this.dropTarget = elm;
    }

    dragNewPortlet(cls, groupId)
    {
        this.dragNewPortletCls   = cls || null;
        this.dragNewPortletGroup = groupId || null;
        this.setDragged(this.jq('<i class="fa fa-spinner fa-pulse"></i>'));
    }

    dragNewBlueprint(id)
    {
        this.dragNewBlueprintId = id || 0;
        this.setDragged(this.jq('<i class="fa fa-spinner fa-pulse"></i>'));
    }

    onBtnConfig()
    {
        this.gui.openConfigurator(this.selectedElm);
    }

    replaceSelectedPortletHtml(html)
    {
        var newPortlet = this.jq(html);

        this.selectedElm.replaceWith(newPortlet);

        var area = newPortlet.parent();

        this.pagetree.updateArea(area);
        this.setSelected(newPortlet);
        this.updateDropTargets();
        this.gui.setUnsaved(true, true);
    }

    onBtnClone()
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
    }

    onBtnBlueprint()
    {
        if(this.selectedElm !== null) {
            this.gui.blueprintModal.modal('show');
        }
    }

    onBtnParent()
    {
        if(this.selectedElm !== null) {
            var elm = this.findSelectableParent(this.selectedElm.parent());

            if (this.isSelectable(elm)) {
                this.setSelected(elm);
            }
        }
    }

    onBtnTrash()
    {
        if(this.selectedElm !== null) {
            var area = this.selectedElm.parent();
            this.selectedElm.remove();
            this.pagetree.updateArea(area);
            this.setSelected();
            this.updateDropTargets();
            this.gui.setUnsaved(true, true);
            this.page.updateFlipcards();
        }
    }

    onKeyDown(e)
    {
        if(e.key === 'Delete' && this.selectedElm !== null) {
            this.onBtnTrash(e);
        }
    }

    isSelectable(elm)
    {
        return elm && elm.is('[data-portlet]');
    }

    isDescendant(descendant, tree)
    {
        return tree && tree.has(descendant).length > 0;
    }
}
