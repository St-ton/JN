function Page(io, shopUrl, key)
{
    debuglog('construct Page');

    bindProtoOnHandlers(this);

    this.io      = io;
    this.shopUrl = shopUrl;
    this.key     = key;

    // this.id  = id;
    // this.url = url;
    // this.fullUrl = fullUrl;
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

        this.io.lockPage(this.id, lockedCB);
    },

    unlock: function(unlockedCB)
    {
        this.io.unlockPage(this.id, unlockedCB);
    },

    onTimeToLockAgain: function()
    {
        this.lock();
    },

    getRevisions: function(revisionsCB)
    {
        this.io.getPageDraftRevisions(this.key, revisionsCB);
    },

    initIframe: function(jq, loadCB)
    {
        debuglog('Page initIframe');

        this.jq = jq;

        this.rootAreas = this.jq('.opc-rootarea');
        this.fileInput = this.jq('<input type="file" accept=".json">');

        this.loadDraftPreview(loadCB);
    },

    loadDraft: function(loadCB)
    {
        debuglog('Page loadDraft');

        this.io.getPageDraft(this.key, this.onLoadDraft.bind(this, loadCB || noop));
    },

    loadDraftPreview: function(loadCB)
    {
        this.io.getPageDraftPreview(this.key, this.onLoad.bind(this, loadCB || noop));
    },

    load: function(loadCB)
    {
        this.loadRev(0, loadCB);
    },

    loadRev: function(revId, loadCB)
    {
        this.io.loadPagePreview(this.id, revId || 0, this.onLoad.bind(this, loadCB || noop));
    },

    loadFromData: function(data, loadCB)
    {
        this.io.createPagePreview(
            {id: data.id, url: data.url, areas: data.areas},
            this.onLoad.bind(this, loadCB || noop)
        );
    },

    loadFromJSON: function(json, loadCB)
    {
        this.loadFromData(JSON.parse(json), loadCB);
    },

    loadFromImport: function(loadCB)
    {
        this.fileInput.off('change').change(this.onImportChosen.bind(this, loadCB)).click();
    },

    loadPageFromWebStorage: function()
    {
        var pageJson = window.localStorage.getItem(this.getStorageId());

        if(pageJson !== null) {
            this.clear();
            this.loadFromJSON(pageJson);
        }
    },

    publicate: function(saveCB, errorCB)
    {
        this.io.publicatePage({
            key: this.key,
            publishFrom: this.publishFrom ? this.encodeDate(this.publishFrom) : null,
            publishTo: this.publishTo ? this.encodeDate(this.publishTo) : null,
            name: this.name,
        }, saveCB, errorCB);
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
        return 'opcpage.' + this.id;
    },

    onImportChosen: function(loadCB, e)
    {
        this.importReader = new FileReader();
        this.importReader.onload = this.onReaderLoad.bind(this, loadCB);
        this.importReader.readAsText(e.target.files[0]);
    },

    onReaderLoad: function(loadCB)
    {
        this.loadFromJSON(this.importReader.result, loadCB);
    },

    onLoadDraft: function(loadCB, pageData)
    {
        debuglog('Page on draft loaded');

        this.id          = pageData.id;
        this.name        = pageData.name;
        this.publishFrom = pageData.publishFrom ? this.decodeDate(pageData.publishFrom) : null;
        this.publishTo   = pageData.publishTo ? this.decodeDate(pageData.publishTo) : null;
        this.url         = pageData.url;
        this.fullUrl     = this.shopUrl + this.url;

        loadCB();
    },

    onLoad: function(loadCB, preview)
    {
        var areas = this.rootAreas;

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
        this.io.savePage(this.toJSON(), saveCB, errorCB);
    },

    savePageToWebStorage: function()
    {
        window.localStorage.setItem(
            this.getStorageId(),
            JSON.stringify(this.pageToJson())
        );

        window.localStorage.setItem(
            this.getStorageId() + '.lastmodified',
            moment().format("YYYY-MM-DD HH:mm:ss")
        );

        // this.gui.setUnsaved(true);
    },

    exportAsDownload: function()
    {
        download(JSON.stringify(this), 'page-export.json', 'application/json');
    },

    clear: function()
    {
        this.rootAreas.empty();
    },

    toJSON: function(withDom)
    {
        withDom = withDom || false;

        var result = {key: this.key, areas: {}};
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