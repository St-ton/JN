
function JLEHost(iframeSelector, templateUrl)
{
	this.templateUrl = templateUrl;
    this.iframeCtx = null;
    this.editor = null;
    this.iframe = $(iframeSelector);
    this.iframe.on("load", this.iframeLoaded.bind(this));
}

JLEHost.prototype.iframeLoaded = function()
{
    this.iframeCtx = this.iframe[0].contentWindow;

    JLEHost.loadStylesheet(
    	this.iframeCtx, this.templateUrl + "css/jtl-live-editor/jtl-live-editor.css"
	);

    JLEHost.loadScript(
    	this.iframeCtx, this.templateUrl + "js/jtl-live-editor/jtl-live-editor.js", this.liveEditorLoaded.bind(this)
	);
};

JLEHost.prototype.liveEditorLoaded = function()
{
    this.editor = new this.iframeCtx.JtlLiveEditor("#content");

    $(".portlet-button")
        .attr("draggable", "true")
        .on("dragstart", this.onDragStart.bind(this))
        .on("dragend", this.onDragEnd.bind(this));
};

JLEHost.prototype.onDragStart = function(e)
{
    var elm = $(e.target);

    this.editor.draggedElm = $(elm.data("content"));

    // firefox needs this
    e.originalEvent.dataTransfer.effectAllowed = 'move';
    e.originalEvent.dataTransfer.setData('text/html', this.editor.draggedElm.innerHTML);
};

JLEHost.prototype.onDragEnd = function(e)
{
    this.editor.cleanUpDrag();
};

JLEHost.loadScript = function(ctx, url, callback)
{
	var script = ctx.document.createElement("script");
	
	script.src = url;
	script.addEventListener("load", callback);
	ctx.document.head.appendChild(script);
};

JLEHost.loadStylesheet = function(ctx, url)
{
	var link = ctx.document.createElement("link");
	
	link.rel = "stylesheet";
	link.href = url;
	ctx.document.head.appendChild(link);
};
