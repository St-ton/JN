import {IO} from "./IO.js";
import {Page} from "./Page.js";
import {GUI} from "./GUI.js";
import {Iframe} from "./Iframe.js";
import {Tutorial} from "./Tutorial.js";
import {PageTree} from "./PageTree.js";
import {PreviewFrame} from "./PreviewFrame.js";

export class OPC extends Emitter
{
    constructor(env)
    {
        super();

        bindProtoOnHandlers(this);
        setJtlToken(env.jtlToken);
        installJqueryFixes();

        this.messages     = env.messages;
        this.error        = env.error;
        this.messages     = env.messages;
        this.io           = new IO(env.jtlToken, env.shopUrl, env.adminPath);
        this.page         = new Page(this.io, env.shopUrl, env.pageKey);
        this.gui          = new GUI(this.io, this.page, env.messages);
        this.iframe       = new Iframe(this.io, this.gui, this.page, env.shopUrl, env.adminPath, env.templateUrl);
        this.tutorial     = new Tutorial(this.iframe);
        this.pagetree     = new PageTree(this.page, this.iframe, this.gui);
        this.previewFrame = new PreviewFrame();
    }

    async init()
    {
        await this.io.init();
        this.gui.init(this.iframe, this.previewFrame, this.tutorial, this.error);
        this.tutorial.init();
        this.pagetree.init();
        this.previewFrame.init();

        await this.page.lock(er => {
            if(er === 1) {
                this.gui.showError(this.messages.opcPageLocked);
            } else if(er === 2) {
                this.gui.showError(this.messages.dbUpdateNeeded);
            }
        });

        await this.page.loadDraft();
        await this.iframe.init(this.pagetree);
        this.gui.updateRevisionList();
        this.gui.hideLoader();
        this.pagetree.render();

        if(this.page.hasUnsavedContent()) {
            this.gui.showRestoreUnsaved();
            $(window.unsavedRevision).show();
        } else {
            $(window.unsavedRevision).hide();
        }
    }

    selectImageProp(propName)
    {
        this.gui.selectImageProp(propName);
    }

    selectVideoProp(propName)
    {
        this.gui.selectVideoProp(propName);
    }

    setConfigSaveCallback(callback)
    {
        this.gui.setConfigSaveCallback(callback);
    }

    setImageSelectCallback(callback)
    {
        this.gui.setImageSelectCallback(callback);
    }
}
