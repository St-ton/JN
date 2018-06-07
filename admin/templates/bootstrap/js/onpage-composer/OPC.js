function OPC(env)
{
    debuglog('construct OPC');

    bindProtoOnHandlers(this);
    setJtlToken(env.jtlToken);
    installJqueryFixes();

    this.error    = env.error;
    this.io       = new IO(this.onIOReady);
    //this.page     = new Page(this.io, env.pageId, env.pageUrl, env.fullPageUrl);
    this.page     = new Page(this.io, env.shopUrl, env.pageKey);
    this.gui      = new GUI(this.io, this.page, env.kcfinderUrl);
    this.iframe   = new Iframe(this.io, this.gui, this.page, env.shopUrl, env.templateUrl);
    this.tutorial = new Tutorial(this.gui, this.iframe);
    this.debug    = new Debug();
}

OPC.prototype = {

    constructor: OPC,

    onIOReady: function()
    {
        debuglog('on IO ready');

        this.gui.init(this.iframe, this.tutorial, this.error);
        this.tutorial.init();
        this.page.init(this.onPageLocked);
        this.debug.init();
    },

    onPageLocked: function(state)
    {
        debuglog('OPC onPageLocked');

        if (state === false) {
            this.gui.showError('Die Seite wird derzeit bearbeitet und kann von Ihnen nicht bearbeitet werden.');
        } else {
            this.iframe.init(this.onPageLoadInital);
        }
    },

    onPageLoadInital: function()
    {
        this.onPageLoad();

        if(this.page.hasUnsavedContent()) {
            this.gui.showRestoreUnsaved();
            this.gui.unsavedRevision.show();
        } else {
            this.gui.unsavedRevision.hide();
        }
    },

    onPageLoad: function()
    {
        this.gui.hideLoader();
        this.debug.refresh();
    },

    selectImageProp: function(propName)
    {
        this.gui.selectImageProp(propName);
    },

    selectVideoProp: function(propName)
    {
        this.gui.selectVideoProp(propName);
    },

    setConfigSaveCallback: function(callback)
    {
        this.gui.setConfigSaveCallback(callback);
    },

    setImageSelectCallback: function(callback)
    {
        this.gui.setImageSelectCallback(callback);
    },

};