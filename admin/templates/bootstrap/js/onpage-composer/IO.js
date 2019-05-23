/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

class IO
{
    constructor(readyCB)
    {
        debuglog('construct IO');

        bindProtoOnHandlers(this);

        this.readyCB      = readyCB || noop;
        this.opcReady     = false;
        this.opcPageReady = false;

        ioCall('opcGetIOFunctionNames', [], this.onGetIOFunctionNames);
    }

    onGetIOFunctionNames(names)
    {
        debuglog('IO onGetIOFunctionNames');

        this.generateIoFunctions(names);
        ioCall('opcGetPageIOFunctionNames', [], this.onGetPageIOFunctionNames);
    }

    onGetPageIOFunctionNames(names)
    {
        debuglog('IO onGetPageIOFunctionNames');

        this.generateIoFunctions(names);
        this.readyCB();
    }

    generateIoFunctions(names)
    {
        for (var i=0; i<names.length; i++) {
            var name       = names[i];
            var publicName = 'opc' + capitalize(name);

            this[name] = this.generateIoFunction(publicName);
        }
    }

    generateIoFunction(publicName)
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
    }

    createPortlet(portletClass, success, error)
    {
        this.getPortletPreviewHtml({"class": portletClass}, success, error);
    }
}
