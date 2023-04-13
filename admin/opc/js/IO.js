import {capitalize} from "./utils.js";

export class IO
{
    constructor({jtlToken, shopUrl})
    {
        this.jtlToken = jtlToken;
        this.ioUrl    = shopUrl + '/admin/io';
    }

    async init()
    {
        this.generateIoFunctions(['getIOFunctionNames', 'getPageIOFunctionNames']);
        this.generateIoFunctions(await this.getIOFunctionNames());
        this.generateIoFunctions(await this.getPageIOFunctionNames());
    }

    generateIoFunctions(names)
    {
        for(const name of names) {
            this[name] = this.generateIoFunction('opc' + capitalize(name));
        }
    }

    generateIoFunction(publicName)
    {
        return async (...args) => {
            try {
                let result = await this.ioCall(publicName, ...args);
                window.opc.emit('io.' + publicName + ':resolve', result);
                return result;
            } catch (e) {
                window.opc.emit('io.' + publicName + ':reject', e);
                throw e;
            }
        };
    }

    async ioCall(name, ...params)
    {
        let formData = new FormData();
        formData.append('jtl_token', this.jtlToken);
        formData.append('io', JSON.stringify({name, params}));
        let response = await fetch(this.ioUrl, {method: 'POST', body: formData});
        return await response.json();
    }

    createPortlet(portletClass)
    {
        return this.getPortletPreviewHtml({class: portletClass});
    }
}