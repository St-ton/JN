class Page
{
    constructor(io, shopUrl, key)
    {
        bindProtoOnHandlers(this);

        this.io           = io;
        this.shopUrl      = shopUrl;
        this.key          = key;
        this.lockTimeout = null;
    }

    lock()
    {
        return this.io.lockDraft(this.key).then(state => {
            if (state === true) {
                this.lockTimeout = setTimeout(() => {
                    this.lock();
                }, 1000 * 60);

                return Promise.resolve();
            } else {
                if(this.lockTimeout !== null) {
                    clearTimeout(this.lockTimeout);
                    this.lockTimeout = null;
                }

                return Promise.reject();
            }
        });
    }

    unlock()
    {
        clearTimeout(this.lockTimeout);
        this.lockTimeout = null;
        return this.io.unlockDraft(this.key);
    }

    updateFlipcards()
    {
        this.rootAreas.find('.opc-Flipcard').each((i, elm) => elm.updateFlipcardHeight());
    }

    getRevisionList()
    {
        return this.io.getRevisionList(this.key);
    }

    initIframe(jq)
    {
        this.jq        = jq;
        this.rootAreas = this.jq('.opc-rootarea');

        return this.loadDraftPreview();
    }

    loadDraft()
    {
        return this.io.getDraft(this.key)
            .then(pageData => {
                this.id          = pageData.id;
                this.name        = pageData.name;
                this.publishFrom = pageData.publishFrom ? this.decodeDate(pageData.publishFrom) : null;
                this.publishTo   = pageData.publishTo ? this.decodeDate(pageData.publishTo) : null;
                this.url         = pageData.url;
                this.lastModified= pageData.lastModified;
                this.fullUrl     = this.shopUrl + this.url;
            });
    }

    loadDraftPreview()
    {
        return this.io.getDraftPreview(this.key).then(this.onLoad)
    }

    loadRev(revId)
    {
        if(revId === -1) {
            return this.loadPageFromWebStorage();
        } else if(revId === 0) {
            return this.io.getDraftPreview(this.key).then(this.onLoad);
        } else {
            return this.io.getRevisionPreview(revId).then(this.onLoad);
        }
    }

    loadFromData(data)
    {
        return this.io.createPagePreview({areas: data.areas})
            .then(this.onLoad);
    }

    loadFromJSON(json)
    {
        try {
            var data = JSON.parse(json);
        } catch (e) {
            return Promise.reject({error:{message:'JSON data could not be loaded'}});
        }

        return this.loadFromData(data);
    }

    loadFromImport()
    {
        return new Promise(res => {
            this.jq('<input type="file" accept=".json">')
                .on('change', res).click();
        }).then(e => {
            return new Promise(res => {
                this.importReader = new FileReader();
                this.importReader.onload = res;
                this.importReader.readAsText(e.target.files[0]);
            });
        }).then(() => this.loadFromJSON(this.importReader.result));
    }

    loadPageFromWebStorage()
    {
        let pageJson = window.localStorage.getItem(this.getStorageId());

        if(pageJson !== null) {
            this.clear();
            return this.loadFromJSON(pageJson);
        } else {
            return Promise.reject({error:{message:'could not find locally stored draft data'}});
        }
    }

    publicate()
    {
        return this.io.publicateDraft({
            key: this.key,
            publishFrom: this.publishFrom ? this.encodeDate(this.publishFrom) : null,
            publishTo: this.publishTo ? this.encodeDate(this.publishTo) : null,
            name: this.name,
        });
    }

    encodeDate(localDate)
    {
        return moment(localDate, localDateFormat).format(internalDateFormat);
    }

    decodeDate(internalDate)
    {
        return moment(internalDate, internalDateFormat).format(localDateFormat);
    }

    getStorageId()
    {
        return 'opcpage.' + this.key;
    }

    onLoad(preview)
    {
        let areas = this.rootAreas;

        this.clear();

        areas.each((i, area) => {
            area = this.jq(area);
            area.html(preview[area.data('area-id')]);
        });
    }

    save()
    {
        return this.io.saveDraft(this.toJSON()).then(() => {
            this.lastModified = moment().format(internalDateFormat);
        });
    }

    savePageToWebStorage()
    {
        window.localStorage.setItem(this.getStorageId(), JSON.stringify(this.toJSON()));
    }

    clearPageWebStorage ()
    {
        window.localStorage.removeItem(this.getStorageId());
    }

    hasUnsavedContent ()
    {
        return window.localStorage.getItem(this.getStorageId()) !== null;
    }

    exportAsDownload()
    {
        download(JSON.stringify(this), this.name + '.json', 'application/json');
    }

    clear()
    {
        this.rootAreas.empty();
    }

    toJSON(withDom)
    {
        withDom = withDom || false;

        let result = {
            id:    this.id,
            url:   this.url,
            key:   this.key,
            areas: {}
        };

        let areas = this.rootAreas;

        for(var i=0; i<areas.length; i++) {
            let area     = this.jq(areas[i]);
            let areaData = this.areaToJSON(area, withDom);

            result.areas[areaData.id] = areaData;
        }

        return result;
    }

    areaToJSON(area, withDom)
    {
        withDom = withDom || false;

        let result   = {id: area.data('area-id'), content: []};
        let portlets = area.children('[data-portlet]');

        portlets.each((i, portlet) => {
            result.content.push(this.portletToJSON(this.jq(portlet), withDom));
        });

        return result;
    }

    portletToJSON(portlet, withDom)
    {
        withDom = withDom || false;

        let data = portlet.data('portlet');

        let result = {
            class: data.class,
            title: data.title,
            properties: data.properties,
            subareas: {},
        };

        let subareas = portlet.find('.opc-area').not(portlet.find('[data-portlet] .opc-area'));

        if (data.class === 'MissingPortlet') {
            result.missingClass = data.missingClass;
        }

        subareas.each((i, subarea) => {
            subarea = this.jq(subarea);
            let subareaData = this.areaToJSON(subarea, withDom);
            result.subareas[subareaData.id] = subareaData;
        });

        result.widthHeuristics = this.computePortletWidthHeuristics(portlet);

        if(withDom) {
            result.elm = portlet;
        }

        return result;
    }

    computePortletWidthHeuristics(portlet)
    {
        var elm             = portlet;
        var widthHeuristics = {xs: null, sm: null, md: null, lg: null};

        while(!elm.is(this.rootAreas)) {
            var clsStr = elm.attr('class');
            var cls    = typeof clsStr === 'string' ? clsStr.split(/\s+/) : [];

            cls.forEach(item => {
                var match = item.match(/col-(xs|sm|md|lg)-([0-9]+)/);

                if(Array.isArray(match)) {
                    var size = match[1];
                    var cols = parseFloat(match[2]);

                    widthHeuristics[size] = widthHeuristics[size] === null ? 1 : widthHeuristics[size];
                    widthHeuristics[size] *= cols / 12;
                }
            });

            elm = elm.parent();
        }

        if(widthHeuristics.xs === null) widthHeuristics.xs = 1;
        if(widthHeuristics.sm === null) widthHeuristics.sm = widthHeuristics.xs;
        if(widthHeuristics.md === null) widthHeuristics.md = widthHeuristics.sm;
        if(widthHeuristics.lg === null) widthHeuristics.lg = widthHeuristics.md;

        return widthHeuristics;
    }
}