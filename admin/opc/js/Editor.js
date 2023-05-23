import {IO} from "./IO.js";
import {Page} from "./Page.js";
import {EditorFrame} from "./EditorFrame.js";
import {Emitter} from "./utils.js";
import {Sidebar} from "./Sidebar.js";
import {enableCollapses, enableColorpickers, enableTabs, enableTooltips, showModal} from "./gui.js";

export class Editor extends Emitter
{
    constructor(config)
    {
        super();

        this.config  = config;
        this.io      = new IO(this.config);
        this.page    = new Page(this.io, this.config);
        this.sidebar = new Sidebar(this.io);
        this.iframe  = new EditorFrame(this.io, this.page, this.config);
    }

    async init()
    {
        await this.io.init();
        await this.page.init();
        await this.sidebar.init();
        await this.iframe.init();

        this.io.on('*', e => this.emit('io.' + e.type, e.data));

        this.sidebar.on('portletDragStarted', e => {
            this.iframe.setDraggingPortlet(e.data.portlet);
        });

        this.iframe.on('editPortlet', e => {
            this.configurePortlet(e.data.portlet);
        });
    }

    async close()
    {
        await this.page.unlock();
        window.location = this.page.fullUrl;
    }

    async configurePortlet(portlet)
    {
        let portletData = portlet.data('portlet');

        let configPanelHtml = await this.io.getConfigPanelHtml(
            portletData.class,
            portletData.missingClass,
            portletData.properties,
        );

        if (portletData.class === 'MissingPortlet') {
            $('#stdConfigButtons').hide();
            $('#missingConfigButtons').show();
        } else {
            $('#stdConfigButtons').show();
            $('#missingConfigButtons').hide();
        }

        $('#configModalBody').html(configPanelHtml);
        $('#configPortletName').text(portletData.title);

        enableTabs(window.configModal);
        enableCollapses(window.configModal);
        enableTooltips(window.configModal);
        enableColorpickers(window.configModal);
        showModal(window.configModal);
    }
}