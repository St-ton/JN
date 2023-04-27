import {IO} from "./IO.js";
import {Page} from "./Page.js";
import {EditorFrame} from "./EditorFrame.js";
import {Emitter} from "./utils.js";

export class Editor extends Emitter
{
    constructor(config)
    {
        super();

        this.config   = config;
        this.shopUrl  = config.shopUrl;
        this.messages = config.messages;
        this.io       = new IO(config);
        this.page     = new Page(this.io, config);
        this.iframe   = new EditorFrame(this.io, this.page, config);

        this.io.on('*', e => this.emit('io.' + e.type, e.data));
    }

    async init()
    {
        await this.io.init();
        await this.page.init();
        await this.iframe.init();
    }

    async close()
    {
        await this.page.unlock();
        window.location = this.page.fullUrl;
    }
}