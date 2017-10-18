
function JLEHost(iframeSelector, templateUrl)
{
    this.templateUrl = templateUrl;
    this.iframeCtx = null;
    this.editor = null;
    this.iframe = $(iframeSelector);
    this.iframe.on("load", this.iframeLoaded.bind(this));

    this.curPortletId = 0;

    $('#jle-btn-save').click(this.onSettingsSave.bind(this));
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
    this.editor = new this.iframeCtx.JtlLiveEditor(".jle-editable", this);

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

JLEHost.prototype.showSettings = function(kPortlet)
{
    var self = this;

    ioCall('getPortletSettingsHtml', [kPortlet], function(settingsHtml) {
        $('#settings-modal .modal-body').html(settingsHtml);
        $('#settings-modal').modal('show');
        self.curPortletId = kPortlet;
    });
};

JLEHost.prototype.onSettingsSave = function (e)
{
    var self = this;
    var settingsArray = $('#portlet-settings-form').serializeArray();
    var settings = { };

    settingsArray.forEach(function (setting) {
        settings[setting.name] = setting.value;
    });

    ioCall('getPortletPreviewContent', [this.curPortletId, settings], function(newHtml) {
        self.editor.selectedElm.replaceWith(newHtml);
        $('#settings-modal').modal('hide');
    });
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
