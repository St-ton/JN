export class EditorFrame
{
    constructor(io, page, {shopUrl})
    {
        this.io = io;
        this.page = page;
        this.shopUrl = shopUrl;
        this.loadedStylesheets  = [];
    }

    async init()
    {
        await new Promise(res => {
            $(window.iframe)
                .on('load', res)
                .attr('src', this.getIframePageUrl());
        });

        this.ctx = window.iframe.contentWindow;
        this.ctx.opc = opc;
        this.jq = this.ctx.$;
        this.head = this.jq('head');
        this.body = this.jq('body');
        this.rootAreas = this.jq('.opc-rootarea');

        this.jq('[data-opc-portlet-css-link=true]').each((e, elm) => {
            this.loadedStylesheets.push(elm.href);
        });

        this.loadStylesheet(this.shopUrl + '/admin/opc/css/iframe.css');
        this.loadStylesheet(this.shopUrl + '/includes/node_modules/@fortawesome/fontawesome-free/css/all.min.css');

        this.disableLinks();

        let preview = await this.page.getPreview();
        this.processPreview(preview);
    }

    processPreview(preview)
    {
        this.clear();

        this.rootAreas.each((i, area) => {
            area = this.jq(area);

            if (area.data('area-foreign')) {
                return;
            }

            let areaId = area.data('area-id');
            area.html(preview[areaId]);
            delete preview[areaId];
        });

        let offscreenAreas = this.jq([]);

        Object.entries(preview).forEach(([areaId, areaContent]) => {
            let area = $('<div class="opc-area opc-rootarea" data-area-id="' + areaId + '">')
                .html(areaContent);
            offscreenAreas = this.offscreenAreas.add(area);
        });

        // TODO something with offscreenAreas
    }

    clear()
    {
        this.rootAreas.not('[data-area-foreign]').empty();
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

    loadStylesheet(url)
    {
        return new Promise(res => {
            if (!this.loadedStylesheets.includes(url)) {
                this.loadedStylesheets.push(url);
                this
                    .jq('<link rel="stylesheet" href="' + url + '">')
                    .on('load', res)
                    .appendTo(this.head);
            } else {
                res();
            }
        })
    }

    getIframePageUrl()
    {
        let pageUrlLink = document.createElement('a');

        pageUrlLink.href = this.page.fullUrl;

        if(pageUrlLink.search !== '') {
            pageUrlLink.search += '&opcEditMode=yes';
        } else {
            pageUrlLink.search = '?opcEditMode=yes';
        }

        pageUrlLink.search += '&opcEditedPageKey=' + this.page.key;

        return pageUrlLink.href.toString();
    }
}