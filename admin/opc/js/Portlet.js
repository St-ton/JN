export class Portlet
{
    constructor(portletClass)
    {
        this.portletClass = portletClass;
        this.properties = {};
        this.elm = null;
    }

    async update(io)
    {
        let portletData = {class: this.portletClass, properties: this.properties}
        await io.getPortletPreviewHtml(portletData);
    }
}