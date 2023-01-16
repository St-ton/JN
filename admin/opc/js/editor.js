import './gui.js';
import {IO} from "./IO.js";
import {Page} from "./Page.js";
import {EditorFrame} from "./EditorFrame.js";
import {Emitter} from "./utils.js";
import {showError} from "./gui.js";

class Editor extends Emitter
{
    constructor(config)
    {
        super();
        this.config = config;
        this.shopUrl = config.shopUrl;
        this.messages = config.messages;
        this.io = new IO(config);
        this.page = new Page(this.io, config);
        this.iframe = new EditorFrame(this.io, this.page, config)
        this.init();
    }

    async init()
    {
        await this.io.init();

        try {
            await this.page.lock();
        } catch (e) {
            if (e === 1) {
                showError(this.messages.opcPageLocked);
            } else if (e === 2) {
                showError(this.messages.dbUpdateNeeded.replace(/%s/, this.shopUrl));
            }

            return;
        }

        await this.page.loadMetaData();
        await this.iframe.init();
    }
}

window.opc = new Editor(JSON.parse(window.editorConfig.innerText));