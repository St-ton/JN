/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

function EditorIO(editor)
{
    this.editor      = editor;
    this.cPageIdHash = editor.cPageIdHash;
    this.pageUrl     = editor.pageUrl;
    this.gui         = editor.gui;
    this.noop        = function() {};

    setInterval(function() {
        ioCall('lockCmsPage', [this.cPageIdHash]);
    }, 1000 * 60);
}

EditorIO.prototype = {

    constructor: EditorIO,

    loadPage: function(callback)
    {
        ioCall('getCmsPage', [this.cPageIdHash, true], callback || this.noop);
    },

    loadRevision: function(id, callback)
    {
        if(id === 0) {
            this.loadPage(callback);
        } else {
            ioCall('getCmsPageRevision', [this.cPageIdHash, id, true], callback || this.noop);
        }
    },

    savePage: function(success, error)
    {
        ioCall('saveCmsPage', [this.cPageIdHash, this.pageUrl, this.pageToJson()], success || this.noop, error || this.noop);
    },

    getPageWebStorageLastModified: function()
    {
        return window.localStorage.getItem(this.getPageStorageId() + '.lastmodified') || '0000-00-00';
    },

    getPageStorageId: function()
    {
        return 'cmspage.' + this.cPageIdHash;
    },

    pageFromJson: function(data)
    {
        var self = this;

        this.gui.rootAreas.each(function(i, area) {
            if(data[area.id] !== undefined) {
                self.areaFromJson(data[area.id], self.gui.iframeJq(area));
            }
        });

        this.gui.updateDropTargets();
    },

    areaFromJson: function(data, areaElm)
    {
        var self = this;

        data.forEach(function(portletData)
        {
            var portletPlaceholder = self.gui.iframeJq('<div><i class="fa fa-spinner fa-pulse fa-2x"></i></div>');

            areaElm.append(portletPlaceholder);

            ioCall(
                'getPortletPreviewHtml',
                [portletData.portletId, portletData.properties],
                function (newHtml)
                {
                    var portlet = self.gui.createPortlet(
                        newHtml,
                        portletData.portletId,
                        portletData.portletTitle,
                        portletData.properties
                    );

                    portletPlaceholder.replaceWith(portlet);
                    self.gui.updateDropTargets();

                    portlet.find('.cle-area').each(function (index, subarea)
                    {
                        self.areaFromJson(portletData.subAreas[index], self.gui.iframeJq(subarea));
                    });
                }
            );
        });
    },

    pageToJson: function()
    {
        var result = {};

        this.gui.rootAreas.each(function(i, area)
        {
            result[area.id] = this.areaToJson(this.gui.iframeJq(area));

        }.bind(this));

        return result;
    },

    areaToJson: function(area)
    {
        var result = [];

        area.children('[data-portletid]').each(function(i, portlet)
        {
            result.push(this.portletToJson(this.gui.iframeJq(portlet)));

        }.bind(this));

        return result;
    },

    portletToJson: function(portlet)
    {
        var result = {};

        result.portletId = portlet.data('portletid');
        result.portletTitle = portlet.data('portlettitle');
        result.properties = portlet.data('properties');
        result.subAreas = [];

        // wenn portlet eine property "calculatedWidth" hat => die Breite des Portletcontainers speichern
        // wichtig um bei Bildern die srcsets korrekt zu berechnen

        var elm = portlet;
        var widthHeuristics = {
            lg: null, md: null, sm: null, xs: null,
        };

        while(!elm.is(this.gui.rootAreas)) {
            var clsStr = elm.attr('class');
            var cls = typeof clsStr === 'string' ? elm.attr('class').split(/\s+/) : [];

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

        if(widthHeuristics.sm === null) widthHeuristics.sm = widthHeuristics.xs;
        if(widthHeuristics.md === null) widthHeuristics.md = widthHeuristics.sm;
        if(widthHeuristics.lg === null) widthHeuristics.lg = widthHeuristics.md;

        result.properties.widthHeuristics = widthHeuristics;


        var children = portlet
        // select direct descendant subareas or non-nested subareas
            .find('> .cle-area') ; //, :not(.jle-subarea) .jle-subarea');

        children.each(function (i, child)
        {
            result.subAreas.push(this.areaToJson(this.gui.iframeJq(child)));

        }.bind(this));

        return result;
    },

    loadPageFromWebStorage: function()
    {
        var pageJson = window.localStorage.getItem(this.getPageStorageId());

        if(pageJson !== null) {
            this.gui.clearPage();
            this.pageFromJson(JSON.parse(pageJson));
        }
    },

    savePageToWebStorage: function()
    {
        window.localStorage.setItem(
            this.getPageStorageId(),
            JSON.stringify(this.pageToJson())
        );

        window.localStorage.setItem(
            this.getPageStorageId() + '.lastmodified',
            moment().format("YYYY-MM-DD HH:mm:ss")
        );

        this.gui.setUnsaved(true);
    },

    storePortletAsTemplate: function(portlet, templateName, success, error)
    {
        success = success || this.noop;
        error = error || this.noop;

        ioCall('storeCmsTemplate', [templateName, this.portletToJson(portlet)], success, error);
    },

    deleteTemplate: function(kTemplate, success, error)
    {
        success = success || this.noop;
        error = error || this.noop;

        ioCall('deleteCmsTemplate', [kTemplate], success, error);
    },
};