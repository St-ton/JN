
function JLEHost()
{
}

var iframeCtx = null;
var iframeJq = null;
var editor = null;

$(main);

function main()
{
	$("iframe").on("load", iframeLoaded);
}

function iframeLoaded()
{
	iframeCtx = $("iframe")[0].contentWindow;
	iframeJq = iframeCtx.$;
	
	loadStylesheet(iframeCtx, "admin/templates/bootstrap/css/jtl-live-editor/jtl-live-editor.css");
	loadScript(iframeCtx, "admin/templates/bootstrap/js/jtl-live-editor/jtl-live-editor.js", liveEditorLoaded);
}

function liveEditorLoaded()
{
	editor = new iframeCtx.JtlLiveEditor("#content");
	
	$("#sidebar-panel button").on("dragstart", onDragStart);
	$("#sidebar-panel button").on("dragend", onDragEnd);
}

function onDragStart(e)
{
	var elm = $(e.target);
	
	editor.draggedElm = $(elm.data("content"));
}

function onDragEnd(e)
{
	editor.cleanUpDrag();
}

function loadScript(ctx, url, callback)
{
	var script = ctx.document.createElement("script");
	
	script.src = url;
	script.addEventListener("load", callback);
	ctx.document.head.appendChild(script);
}

function loadStylesheet(ctx, url)
{
	var link = ctx.document.createElement("link");
	
	link.rel = "stylesheet";
	link.href = url;
	ctx.document.head.appendChild(link);
}
