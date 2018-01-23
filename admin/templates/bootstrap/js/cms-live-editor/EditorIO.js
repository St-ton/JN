/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

function EditorIO(editor)
{
    this.editor      = editor;
    this.cPageIdHash = editor.cPageIdHash;
    this.gui         = editor.gui;
    this.noop        = function() {};

    setInterval(function() {
        ioCall('lockCmsPage', [this.cPageIdHash]);
    }, 1000 * 60);
}

EditorIO.prototype = {

    constructor: EditorIO,

    loadPage: function()
    {
        ioCall('getCmsPage', [this.cPageIdHash, true], this.onGetCmsPageResponse.bind(this));
    },

    loadRevision: function(id)
    {
        ioCall('getCmsPageRevision', [this.cPageIdHash, id, true], this.onGetCmsPageResponse.bind(this));
    },

    savePage: function(success, error)
    {
        success = success || this.noop;
        error = error || this.noop;

        ioCall('saveCmsPage', [this.cPageIdHash, this.pageToJson()], success.bind(this), error.bind(this));
    },

    onGetCmsPageResponse: function(cmsPage)
    {
        // var serverLastModified = cmsPage && cmsPage.dLastModified || '0000-00-00';
        // var localLastModified = this.getPageWebStorageLastModified();
        //
        // var locallyModified = localLastModified > serverLastModified;
        // var data =
        //     locallyModified ? JSON.parse(window.localStorage.getItem(this.getPageStorageId())) :
        //     cmsPage         ? cmsPage.data :
        //     {};
        //var data = cmsPage ? cmsPage.data : {};
        //this.pageFromJson(data);
        //this.gui.setUnsaved(locallyModified);

        $.each(cmsPage.cPreviewHtml_arr, function (areaId, html) {
            this.gui.iframeJq('#' + areaId).html(html);
        }.bind(this));

        this.gui.updateDropTargets();
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
            lg: null, md: null, sm: null, xs: 1,
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
        // window.localStorage.setItem(
        //     this.getPageStorageId(),
        //     JSON.stringify(this.pageToJson())
        // );
        //
        // window.localStorage.setItem(
        //     this.getPageStorageId() + '.lastmodified',
        //     moment().format("YYYY-MM-DD HH:mm:ss")
        // );
        //
        // this.gui.setUnsaved(true);
    },

    storePortletAsTemplate: function(portlet, templateName, success, error)
    {
        success = success || this.noop;
        error = error || this.noop;

        ioCall('storeCmsTemplate', [templateName, this.portletToJson(portlet)], success.bind(this), error.bind(this));
    },
};