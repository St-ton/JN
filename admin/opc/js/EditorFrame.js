import {enableTooltip, enableTooltips} from "./gui.js";
import {Emitter, loadIframe} from "./utils.js";

export class EditorFrame extends Emitter
{
    constructor(io, page, {shopUrl})
    {
        super();

        this.io                = io;
        this.page              = page;
        this.shopUrl           = shopUrl;
        this.loadedStylesheets = new Set();
        this.rootAreas         = [];
        this.selectedPortlet   = null;
    }

    async init()
    {
        let url = new URL(this.page.fullUrl);
        url.searchParams.set('opcEditMode', 'yes');
        url.searchParams.set('opcEditedPageKey', this.page.key);
        await loadIframe(window.iframe, url.href);

        this.ctx                 = window.iframe.contentWindow;
        this.ctx.opc             = window.opc;
        this.jq                  = this.ctx.$;
        this.head                = this.jq('head');
        this.body                = this.jq('body');
        this.rootAreas           = this.jq('.opc-rootarea');
        this.portletToolbar      = this.jq(window.template_portletToolbar.innerHTML);
        this.dropTargetBlueprint = this.jq(window.template_dropTarget.innerHTML);

        for(const cssLink of this.jq('[data-opc-portlet-css-link=true]')) {
            this.loadedStylesheets.add(cssLink.href);
        }

        await this.loadStylesheet(this.shopUrl+'/admin/opc/css/iframe.css');
        await this.loadStylesheet(this.shopUrl+'/includes/node_modules/@fortawesome/fontawesome-free/css/all.min.css');
        await this.loadScript(this.shopUrl+'/includes/node_modules/@popperjs/core/dist/umd/popper.min.js')

        this.disableLinks();
        this.renderPreview(await this.page.getPreview());
        this.updateDropTargets();

        this.rootAreas.on('click', e => {
            let $target  = $(e.target);
            let $portlet = this.findPortletParent($target);
            this.selectPortlet($portlet);
        });

        this.portletToolbar.find('#btnConfig').on('click', e => {
            this.emit('editPortlet', {portlet: this.selectedPortlet});
        });
    }

    isPortlet($elm)
    {
        return $elm.is('[data-portlet]');
    }

    findPortletParent($elm)
    {
        if(this.isPortlet($elm)) {
            return $elm;
        }

        if(!$elm.is(this.page.rootAreas)) {
            return this.findPortletParent($elm.parent());
        }
    }

    selectPortlet(portlet = null)
    {
        if(portlet && portlet.is(this.selectedPortlet)) {
            return;
        }

        if(this.selectedPortlet) {
            this.selectedPortlet.removeClass('opc-selected');
        }

        if(this.portletToolbar.popper) {
            this.portletToolbar.popper.destroy();
            this.portletToolbar.popper = null;
        }

        if(portlet) {
            let portletData = portlet.data('portlet');
            this.portletToolbar.popper = this.ctx.Popper.createPopper(portlet[0], this.portletToolbar[0], {});
            this.portletToolbar.find('#portletLabel').text(portletData.title);
            this.body.append(this.portletToolbar);
            this.selectedPortlet = portlet;
        }
    }

    renderPreview(preview)
    {
        const usedAreaIds = [];

        for (const area of this.rootAreas) {
            const areaId = area.dataset.areaId;

            if (typeof areaId === 'string') {
                const areaContent = preview[areaId];

                if (typeof areaContent === 'string') {
                    area.innerHTML = areaContent;
                    usedAreaIds.push(areaId);
                }
            }
        }

        const previewAreaIds   = Object.keys(preview);
        const offscreenAreaIds = previewAreaIds.filter(id => usedAreaIds.includes(id) === false);

        // TODO offscreenAreaIds
    }

    setDraggingPortlet(portlet)
    {
        this.draggingPortlet = portlet;
    }

    updateDropTargets()
    {
        this.stripDropTargets();

        for(const area of this.getAreas()) {
            let $area       = this.jq(area);
            let $droptarget = this.dropTargetBlueprint.clone();

            $droptarget
                .find('.opc-droptarget-info')
                .attr('title', $area.data('title') || $area.data('area-id'));

            $area
                .append($droptarget.clone())
                .children('[data-portlet]').before($droptarget.clone());
        }

        for(const dropTargetInfo of this.getAreas().find('.opc-droptarget-info')) {
            enableTooltip(dropTargetInfo);
        }

        for(const dropTarget of this.getDropTargets()) {
            let $dropTarget = this.jq(dropTarget);

            $dropTarget.on('dragover', e => {
                e.preventDefault();
            });

            $dropTarget.on('drop', e => {
                this.dropPortlet(this.draggingPortlet, $dropTarget);
            });
        }
    }

    stripDropTargets()
    {
        this.getDropTargets().remove();
    }

    async dropPortlet(portlet, target)
    {
        if(typeof portlet === 'string') {
            let portletHtml = await this.io.createPortlet(portlet);
            portlet = this.jq(portletHtml);

            portlet.on('dblclick', () => {
                this.emit('editPortlet', {portlet});
            });
        }

        target.replaceWith(portlet);
        this.updateDropTargets();
    }

    disableLinks()
    {
        // disable links and buttons that could navigate away from the edited page
        this.jq('a:not(.opc-no-disable), button:not(.opc-no-disable)')
            .off('click')
            .removeAttr('onclick')
            .on('click', e => e.preventDefault());
        this.jq('.variations select')
            .off('change');
    }

    async loadStylesheet(url)
    {
        if (!this.loadedStylesheets.has(url)) {
            this.loadedStylesheets.add(url);

            await new Promise(res => {
                this.jq(`<link rel="stylesheet" href="${url}">`)
                    .one('load', res)
                    .appendTo(this.head);
            })
        }
    }

    async loadScript(url)
    {
        await new Promise(res => {
            let script = this.ctx.document.createElement('script');
            script.onload = res;
            script.src = url;
            this.head[0].append(script);
        });
    }

    getAreas()
    {
        return this.jq('.opc-area');
    }

    getPortlets()
    {
        return this.jq('[data-portlet]');
    }

    getDropTargets()
    {
        return this.jq('.opc-droptarget');
    }
}