import {IO} from "./IO.js";
import {Page} from "./Page.js";
import {EditorFrame} from "./EditorFrame.js";
import {Emitter} from "./utils.js";
import {Sidebar} from "./Sidebar.js";

export class Editor extends Emitter
{
    constructor(config)
    {
        super();

        this.config  = config;
        this.io      = new IO(this.config);
        this.page    = new Page(this.io, this.config);
        this.sidebar = new Sidebar();
        this.iframe  = new EditorFrame(this.io, this.page, this.config);
    }

    async init()
    {
        await this.io.init();
        await this.page.init();
        await this.sidebar.init();
        await this.iframe.init();

        this.io.on('*', e => this.emit('io.' + e.type, e.data));
        this.sidebar.on('startPortletDrag', console.log);
    }

    async close()
    {
        await this.page.unlock();
        window.location = this.page.fullUrl;
    }
}