function OPC(env)
{
    bindProtoOnHandlers(this);
    setJtlToken(env.jtlToken);
    installJqueryFixes();

    this.kcfinderUrl = env.kcfinderUrl;

    this.io       = new IO(this.onIOReady);
    this.page     = new Page(this.io, env.pageId, env.pageUrl);
    this.gui      = new GUI(this.io, this.page);
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

    onPageLocked: function()
    {
        this.iframe.init(this.onPageLoad);
    },

    onPageLoad: function()
    {
        this.gui.hideLoader();
    },

};