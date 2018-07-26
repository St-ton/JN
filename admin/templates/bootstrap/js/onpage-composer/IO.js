/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

function IO(readyCB)
{
    debuglog('construct IO');

    bindProtoOnHandlers(this);

    this.readyCB      = readyCB || noop;
    this.opcReady     = false;
    this.opcPageReady = false;

    ioCall('opcGetIOFunctionNames', [], this.onGetIOFunctionNames);
}

IO.prototype = {

    constructor: IO,

    onGetIOFunctionNames: function(names)
    {
        debuglog('IO onGetIOFunctionNames');

        this.generateIoFunctions(names);
        ioCall('opcGetPageIOFunctionNames', [], this.onGetPageIOFunctionNames);
    },

    onGetPageIOFunctionNames: function(names)
    {
        debuglog('IO onGetPageIOFunctionNames');

        this.generateIoFunctions(names);
        this.readyCB();
    },

    generateIoFunctions: function(names)
    {
        for (var i=0; i<names.length; i++) {
            var name       = names[i];
            var publicName = 'opc' + capitalize(name);

            this[name] = this.generateIoFunction(publicName);
        }
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

            debuglog('IO call', publicName);

            ioCall(publicName, args, success || noop, error || noop);
        };
    },

    createPortlet: function(portletClass, success, error)
    {
        this.getPortletPreviewHtml({"class": portletClass}, success, error);
    },

};