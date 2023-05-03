import {enableTooltip} from "./gui.js";

export class EditorFrame
{
    constructor(io, page, {shopUrl})
    {
        this.io                = io;
        this.page              = page;
        this.shopUrl           = shopUrl;
        this.loadedStylesheets = new Set();
        this.rootAreas         = [];
    }

    async init()
    {
        let url = new URL(this.page.fullUrl);
        url.searchParams.set('opcEditMode', 'yes');
        url.searchParams.set('opcEditedPageKey', this.page.key);
        let iframeUrl = url.href;

        await new Promise(res => {
            $(window.iframe).one('load', res).attr('src', iframeUrl);
        });

        this.ctx       = window.iframe.contentWindow;
        this.ctx.opc   = window.opc;
        this.jq        = this.ctx.$;
        this.head      = this.jq('head');
        this.body      = this.jq('body');
        this.rootAreas = this.jq('.opc-rootarea');

        for(const cssLink of this.jq('[data-opc-portlet-css-link=true]')) {
            this.loadedStylesheets.add(cssLink.href);
        }

        this.loadStylesheet(this.shopUrl + '/admin/opc/css/iframe.css');
        this.loadStylesheet(this.shopUrl + '/includes/node_modules/@fortawesome/fontawesome-free/css/all.min.css');
        this.disableLinks();
        this.renderPreview(await this.page.getPreview());

        this.rootAreas.on('click', e => {
            this.selectPortlet(this.findPortletParent(this.jq(e.target)));
        });
    }

    isPortlet(elm)
    {
        return elm && elm.is('[data-portlet]');
    }

    findPortletParent(elm)
    {
        if(this.isPortlet(elm)) {
            return elm;
        }

        if(!elm.is(this.page.rootAreas)) {
            return this.findPortletParent(elm.parent());
        }
    }

    selectPortlet(portlet)
    {
        console.log(portlet);
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

    updateDropTargets()
    {
        this.stripDropTargets();

        for(const area of this.getAreas()) {
            let droptarget = $(window.dropTargetBlueprint).clone().attr('id', '').show();

            droptarget
                .find('.opc-droptarget-info')
                .attr('title', area.data('title') || area.data('area-id'));

            this.jq(area)
                .append(droptarget.clone())
                .children('[data-portlet]').before(droptarget.clone());
        }

        enableTooltip(this.areas().find('.opc-droptarget-info'));
    }

    stripDropTargets()
    {
        this.getDropTargets().remove();
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