/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

class IO
{
    init()
    {
        return Promise.all([
            new Promise((res, rej) => ioCall('opcGetIOFunctionNames', [], res, rej))
                .then(names => this.generateIoFunctions(names)),
            new Promise((res, rej) => ioCall('opcGetPageIOFunctionNames', [], res, rej))
                .then(names => this.generateIoFunctions(names)),
        ]);
    }

    generateIoFunctions(names)
    {
        names.forEach(name => {
            this[name] = this.generateIoFunction('opc' + capitalize(name));
        });
    }

    generateIoFunction(publicName)
    {
        return function(...args) {
            return new Promise((res, rej) => {
                ioCall(publicName, args, res, rej);
            })
        };
    }

    createPortlet(portletClass)
    {
        return this.getPortletPreviewHtml({class: portletClass});
    }
}
