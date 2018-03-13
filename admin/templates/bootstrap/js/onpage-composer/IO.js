/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

function IO(readyCB)
{
    bindProtoOnHandlers(this);

    this.readyCB = readyCB || noop;

    ioCall('opcGetIOFunctionNames', [], this.onGetIOFunctionNames);
}

IO.prototype = {

    constructor: IO,

    onGetIOFunctionNames: function(names)
    {
        for (var i=0; i<names.length; i++) {
            var name       = names[i];
            var publicName = 'opc' + capitalize(name);

            this[name] = this.generateIoFunction(publicName);
        }

        this.readyCB();
    },

    generateIoFunction: function(publicName)
    {
        return function()
        {
            var success = undefined;
            var error = undefined;
            var args = [];

            for(var i=0; i<arguments.length; i++) {
                var arg = arguments[i];

                if(typeof arg === 'function') {
                    if(typeof success === 'function') {
                        error = arg;
                    } else {
                        success = arg;
                    }
                } else {
                    args.push(arg);
                }
            }

            ioCall(publicName, args, success || noop, error || noop);
        };
    },

    createPortlet: function(portletId, success, error)
    {
        this.getPortletInstance({id: portletId, previewHtmlEnabled: true}, success, error);
    },

    getConfigPanelHtml: function(portletId, properties, success, error)
    {
        this.getPortletInstance(
            {id: portletId, properties: properties, configPanelHtmlEnabled: true},
            this.onGetConfigPanelHtml.bind(this, success),
            error);
    },

    onGetConfigPanelHtml: function(success, page)
    {
        success(page.configPanelHtml);
    },

    getPortletPreviewHtml: function(portletData, success, error)
    {
        portletData.previewHtmlEnabled = true;

        this.getPortletInstance(portletData, this.onGetPortletPreviewHtml.bind(this, success), error);
    },

    onGetPortletPreviewHtml: function (success, data)
    {
        success(data.previewHtml);
    },

};