function OPC(env)
{
    bindProtoOnHandlers(this);
    setJtlToken(env.jtlToken);
    installJqueryFixes();

    this.kcfinderUrl = env.kcfinderUrl;

    this.io       = new IO(this.onIOReady);
    this.page     = new Page(this.io, env.pageId, env.pageUrl, env.fullPageUrl);
    this.gui      = new GUI(this.io, this.page, env.kcfinderUrl);
    this.iframe   = new Iframe(this.io, this.gui, this.page, env.shopUrl, env.templateUrl);
    this.tutorial = new Tutorial(this.gui, this.iframe);
}

OPC.prototype = {

    constructor: OPC,

    onIOReady: function()
    {
        this.gui.init(this.iframe, this.tutorial);
        this.tutorial.init();
        this.page.init(this.onPageLocked);
    },

    onPageLocked: function(state)
    {
        if (state === false) {
            this.gui.showError('Die Seite wird derzeit bearbeitet und kann von Ihnen nicht bearbeitet werden.');
        } else {
            this.iframe.init(this.onPageLoad);
        }
    },

    onPageLoad: function()
    {
        this.gui.hideLoader();
    },

    selectImageProp: function(propName)
    {
        this.gui.selectImageProp(propName);
    },

    setConfigSaveCallback: function(callback)
    {
        this.gui.setConfigSaveCallback(callback);
    },

};