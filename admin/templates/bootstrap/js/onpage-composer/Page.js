function Page(io, shopUrl, key)
{
    debuglog('construct Page');

    bindProtoOnHandlers(this);

    this.io             = io;
    this.shopUrl        = shopUrl;
    this.key            = key;
    this.cachedPageData = [];
}

Page.prototype = {

    constructor: Page,

    init: function(lockedCB)
    {
        debuglog('Page init');

        this.loadDraft(this.lock.bind(this, lockedCB));

        setInterval(this.onTimeToLockAgain, 1000 * 60);
    },

    lock: function(lockedCB)
    {
        debuglog('Page lock');

        this.io.lockDraft(this.key, lockedCB);
    },

    unlock: function(unlockedCB)
    {
        this.io.unlockDraft(this.key, unlockedCB);
    },

    updateFlipcards: function()
    {
        this.rootAreas.find('.flipcard').each(function(i, elm) {
            elm.updateFlipcardHeight();
        });
    },

    onTimeToLockAgain: function()
    {
        this.lock();
    },

    getRevisionList: function(revisionsCB)
    {
        this.io.getRevisionList(this.key, revisionsCB);
    },

    initIframe: function(jq, loadCB, errorCB)
    {
        debuglog('Page initIframe');

        this.jq        = jq;
        this.rootAreas = this.jq('.opc-rootarea');
        this.loadDraftPreview(loadCB, errorCB);
    },

    loadDraft: function(loadCB)
    {
        debuglog('Page loadDraft');

        this.io.getDraft(this.key, this.onLoadDraft.bind(this, loadCB || noop));
    },

    loadDraftPreview: function(loadCB, errorCB)
    {
        debuglog('Page loadDraftPreview');

        this.io.getDraftPreview(this.key, this.onLoad.bind(this, loadCB || noop), errorCB || noop);
    },

    loadRev: function(revId, loadCB, errorCB)
    {
        if(revId === -1) {
            this.loadPageFromWebStorage(loadCB || noop, errorCB || noop);
        } else if(revId === 0) {
            this.io.getDraftPreview(this.key, this.onLoad.bind(this, loadCB || noop), errorCB || noop);
        } else {
            this.io.getRevisionPreview(revId, this.onLoad.bind(this, loadCB || noop), errorCB || noop);
        }
    },

    loadFromData: function(data, loadCB, errorCB)
    {
        this.io.createPagePreview(
            {areas: data.areas},
            this.onLoad.bind(this, loadCB || noop),
            errorCB || noop,
        );
    },

    loadFromJSON: function(json, loadCB, errorCB)
    {
        try {
            var data = JSON.parse(json);
        } catch (e) {
            errorCB({error:{message:'JSON data could not be loaded'}});
        }

        this.loadFromData(data, loadCB, errorCB);
    },

    loadFromImport: function(loadCB, errorCB)
    {
        this.jq('<input type="file" accept=".json">')
            .on('change', this.onImportChosen.bind(this, loadCB, errorCB)).click();
    },

    loadPageFromWebStorage: function(loadCB, errorCB)
    {
        var pageJson = window.localStorage.getItem(this.getStorageId());

        if(pageJson !== null) {
            this.clear();
            this.loadFromJSON(pageJson, loadCB, errorCB);
        } else {
            errorCB({error:{message:'could not find locally stored draft data'}})
        }
    },

    publicate: function(saveCB, errorCB)
    {
        this.io.publicateDraft({
            key: this.key,
            publishFrom: this.publishFrom ? this.encodeDate(this.publishFrom) : null,
            publishTo: this.publishTo ? this.encodeDate(this.publishTo) : null,
            name: this.name,
        }, saveCB, errorCB);
    },

    showLivePreview: function(onDone)
    {
        this.cachedPageData = this.toJSON();

        this.io.createPageLivePreview(
            this.cachedPageData,
            function(preview) {
                this.onLoad(noop, preview);
                onDone();
            }.bind(this)
        );
    },

    hideLivePreview: function(onDone)
    {
        this.loadFromData(this.cachedPageData, onDone);
    },

    encodeDate: function(localDate)
    {
        return moment(localDate, localDateFormat).format(internalDateFormat);
    },

    decodeDate: function(internalDate)
    {
        return moment(internalDate, internalDateFormat).format(localDateFormat);
    },

    getStorageId: function()
    {
        return 'opcpage.' + this.key;
    },

    onImportChosen: function(loadCB, errorCB, e)
    {
        this.importReader = new FileReader();
        this.importReader.onload = this.onReaderLoad.bind(this, loadCB, errorCB);
        this.importReader.readAsText(e.target.files[0]);
    },

    onReaderLoad: function(loadCB, errorCB)
    {
        this.loadFromJSON(this.importReader.result, loadCB, errorCB);
    },

    onLoadDraft: function(loadCB, pageData)
    {
        debuglog('Page on draft loaded');

        this.id          = pageData.id;
        this.name        = pageData.name;
        this.publishFrom = pageData.publishFrom ? this.decodeDate(pageData.publishFrom) : null;
        this.publishTo   = pageData.publishTo ? this.decodeDate(pageData.publishTo) : null;
        this.url         = pageData.url;
        this.replace     = pageData.replace;
        this.fullUrl     = this.shopUrl + this.url;

        loadCB();
    },

    onLoad: function(loadCB, preview)
    {
        var areas = this.rootAreas;

        this.clear();

        for (var i=0; i<areas.length; i++) {
            var area = this.jq(areas[i]);
            var id   = area.data('area-id');
            var html = preview[id];

            area.html(html);
        }

        loadCB();
    },

    save: function(saveCB, errorCB)
    {
        this.io.saveDraft(this.toJSON(), saveCB, errorCB);
    },

    savePageToWebStorage: function()
    {
        window.localStorage.setItem(this.getStorageId(), JSON.stringify(this.toJSON()));
    },

    clearPageWebStorage: function ()
    {
        window.localStorage.removeItem(this.getStorageId());
    },

    hasUnsavedContent: function ()
    {
        return window.localStorage.getItem(this.getStorageId()) !== null;
    },

    exportAsDownload: function()
    {
        download(JSON.stringify(this), this.name + '.json', 'application/json');
    },

    clear: function()
    {
        this.rootAreas.empty();
    },

    toJSON: function(withDom)
    {
        withDom = withDom || false;

        var result = {id: this.id, url: this.url, key: this.key, replace: this.replace, areas: {}};
        var areas  = this.rootAreas;

        for(var i=0; i<areas.length; i++) {
            var area     = this.jq(areas[i]);
            var areaData = this.areaToJSON(area, withDom);

            result.areas[areaData.id] = areaData;
        }

        return result;
    },

    areaToJSON: function(area, withDom)
    {
        withDom = withDom || false;

        var result   = {id: area.data('area-id'), content: []};
        var portlets = area.children('[data-portlet]');

        for(var i=0; i<portlets.length; i++) {
            var portlet = this.jq(portlets[i]);

            result.content.push(this.portletToJSON(portlet, withDom));
        }

        return result;
    },

    portletToJSON: function(portlet, withDom)
    {
        withDom = withDom || false;

        var data     = portlet.data('portlet');
        var result   = {"class": data.class, title: data.title, properties: data.properties, subareas: {}};
        var subareas = portlet.find('.opc-area').not(portlet.find('[data-portlet] .opc-area'));

        if (data.class === 'MissingPortlet') {
            result.missingClass = data.missingClass;
        }

        for(var i=0; i<subareas.length; i++) {
            var subarea     = this.jq(subareas[i]);
            var subareaData = this.areaToJSON(subarea, withDom);

            result.subareas[subareaData.id] = subareaData;
        }

        result.widthHeuristics = this.computePortletWidthHeuristics(portlet);

        if(withDom) {
            result.elm = portlet;
        }

        return result;
    },

    computePortletWidthHeuristics: function(portlet)
    {
        var elm             = portlet;
        var widthHeuristics = {xs: null, sm: null, md: null, lg: null};

        while(!elm.is(this.rootAreas)) {
            var clsStr = elm.attr('class');
            var cls    = typeof clsStr === 'string' ? clsStr.split(/\s+/) : [];

            for(var i=0; i < cls.length; i++) {
                var match = cls[i].match(/col-(xs|sm|md|lg)-([0-9]+)/);

                if(Array.isArray(match)) {
                    var size = match[1];
                    var cols = parseFloat(match[2]);

                    widthHeuristics[size] = widthHeuristics[size] === null ? 1 : widthHeuristics[size];
                    widthHeuristics[size] *= cols / 12;
                }
            }

            elm = elm.parent();
        }

        if(widthHeuristics.xs === null) widthHeuristics.xs = 1;
        if(widthHeuristics.sm === null) widthHeuristics.sm = widthHeuristics.xs;
        if(widthHeuristics.md === null) widthHeuristics.md = widthHeuristics.sm;
        if(widthHeuristics.lg === null) widthHeuristics.lg = widthHeuristics.md;

        return widthHeuristics;
    },

};