/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param editor Editor
 * @constructor
 */
function EditorIO(editor)
{
    this.editor = editor;
    this.noop   = function() {};

    setInterval(function() {
        ioCall('lockCmsPage', [this.editor.cPageIdHash]);
    }, 1000 * 60);
}

EditorIO.prototype = {

    constructor: EditorIO,

    loadPage: function()
    {
        ioCall('getCmsPage', [this.editor.cPageIdHash], this.onGetCmsPageResponse.bind(this));
    },

    savePage: function(success, error)
    {
        success = success || this.noop;
        error = error || this.noop;

        ioCall('saveCmsPage', [this.editor.cPageIdHash, this.pageToJson()], success.bind(this), error.bind(this));
    },

    onGetCmsPageResponse: function(cmsPage)
    {
        var serverLastModified = cmsPage && cmsPage.dLastModified || '0000-00-00';
        var localLastModified = this.getPageWebStorageLastModified();

        var locallyModified = localLastModified > serverLastModified;
        var data = locallyModified
            ? JSON.parse(window.localStorage.getItem(this.getPageStorageId()))
            : cmsPage
                ? cmsPage.data
                : {};

        console.log(locallyModified);

        this.pageFromJson(data);
        this.editor.setUnsaved(locallyModified);
    },

    getPageWebStorageLastModified: function()
    {
        return window.localStorage.getItem(this.getPageStorageId() + '.lastmodified') || '0000-00-00';
    },

    getPageStorageId: function()
    {
        return 'cmspage.' + this.editor.cPageIdHash + '.' + this.editor.cAction;
    },

    pageFromJson: function(data)
    {
        for(var areaId in data) {
            this.areaFromJson(data[areaId], this.editor.iframeJq('#' + areaId));
        }

        this.editor.updateDropTargets();
    },

    areaFromJson: function(data, areaElm)
    {
        data.forEach(function(portletData)
        {
            var portletElm = this.editor.iframeJq('<div><i class="fa fa-spinner fa-pulse fa-2x"></i></div>');

            areaElm.append(portletElm);

            ioCall('getPortletPreviewHtml', [portletData.portletId, portletData.properties], function (newHtml)
            {
                var newElm = this.editor.iframeJq(newHtml);

                portletElm.replaceWith(newElm);
                newElm.attr('data-portletid', portletData.portletId);
                newElm.attr('data-portlettitle', portletData.portletTitle);
                newElm.attr('data-properties', JSON.stringify(portletData.properties));

                this.editor.updateDropTargets();

                newElm.find('.cle-area').each(function (index, subarea)
                {
                    this.areaFromJson(portletData.subAreas[index], this.editor.iframeJq(subarea));

                }.bind(this));
            }.bind(this));
        }.bind(this));
    },

    pageToJson: function()
    {
        var result = {};

        this.editor.rootElm.each(function(i, rootArea)
        {
            result[rootArea.id] = this.areaToJson(this.editor.iframeJq(rootArea));

        }.bind(this));

        return result;
    },

    areaToJson: function(rootArea)
    {
        var result = [];

        rootArea.children('[data-portletid]').each(function(i, portletElm)
        {
            result.push(this.portletToJson(this.editor.iframeJq(portletElm)));

        }.bind(this));

        return result;
    },

    portletToJson: function(portletElm)
    {
        var result = {};

        result.portletId = portletElm.data('portletid');
        result.portletTitle = portletElm.data('portlettitle');
        result.properties = portletElm.data('properties');
        result.subAreas = [];
        // todo Editor: wenn portlet eine property "calculated Width" hat die Breite des Portletcontainers speichern
        // wichtig um bei Bildern die srcsets korrekt zu berechnen

        var children = portletElm
        // select direct descendant subareas or non-nested subareas
            .find('> .cle-area') ; //, :not(.jle-subarea) .jle-subarea');

        children.each(function (i, child)
        {
            result.subAreas.push(this.areaToJson(this.editor.iframeJq(child)));

        }.bind(this));

        return result;
    },

    loadPageFromWebStorage: function()
    {
        var pageJson = window.localStorage.getItem(this.getPageStorageId());

        if(pageJson !== null) {
            this.clearPage();
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

        this.editor.setUnsaved(true);
    },

    storePortletAsTemplate: function(portlElem, templateName, success, error)
    {
        success = success || this.noop;
        error = error || this.noop;

        ioCall('storeTemplate', [templateName, this.portletToJson(portlElem)], success.bind(this), error.bind(this));
    },
};