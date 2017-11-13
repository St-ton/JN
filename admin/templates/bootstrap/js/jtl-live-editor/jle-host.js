
function JLEHost(jtlToken, templateUrl, kcfinderPath, cKey, kKey, kSprache)
{
    this.templateUrl = templateUrl;
    this.kcfinderPath = kcfinderPath;
    this.cKey = cKey;
    this.kKey = kKey;
    this.kSprache = kSprache;
    this.iframeCtx = null;
    this.editor = null;
    this.curPortletId = 0;
    this.configSaveCallback = $.noop;
    this.iframe = $('#iframe');

    setJtlToken(jtlToken);

    Split(['#sidebar-panel', '#iframe-panel'], { sizes: [25, 75], gutterSize: 4 });

    this.iframe.on('load', this.iframeLoaded.bind(this));

    $('#config-modal').submit(this.onSettingsSave.bind(this));
    $('#jle-btn-save-editor').click(this.onEditorSave.bind(this));


    // Fix from: https://stackoverflow.com/questions/22637455/how-to-use-ckeditor-in-a-bootstrap-modal
    $.fn.modal.Constructor.prototype.enforceFocus = function ()
    {
        var $modalElement = this.$element;

        $(document).on('focusin.modal', function (e)
        {
            var $parent = $(e.target.parentNode);

            if ($modalElement[0] !== e.target && !$modalElement.has(e.target).length &&
                !$parent.hasClass('cke_dialog_ui_input_select') && !$parent.hasClass('cke_dialog_ui_input_text')
            ) {
                $modalElement.focus();
            }
        });
    };

    $.fn.serializeControls = function() {
        var data = {};

        function buildInputObject(arr, val) {
            if (arr.length < 1)
                return val;
            var objkey = arr[0];
            if (objkey.slice(-1) == "]") {
                objkey = objkey.slice(0,-1);
            }
            var result = {};
            if (arr.length == 1){
                result[objkey] = val;
            } else {
                arr.shift();
                var nestedVal = buildInputObject(arr,val);
                result[objkey] = nestedVal;
            }
            return result;
        }

        $.each(this.serializeArray(), function() {
            var val = this.value;
            var c = this.name.split("[");
            var a = buildInputObject(c, val);
            $.extend(true, data, a);
        });

        return data;
    };
}

JLEHost.prototype.iframeLoaded = function()
{
    this.iframeCtx = this.iframe[0].contentWindow;

    JLEHost.loadStylesheet(
    	this.iframeCtx, this.templateUrl + 'css/jtl-live-editor/jtl-live-editor.css'
	);

    JLEHost.loadScript(
        this.iframeCtx, this.templateUrl + 'js/global.js', function() {
            JLEHost.loadScript(
                this.iframeCtx, this.templateUrl + 'js/jtl-live-editor/jtl-live-editor.js',
                this.liveEditorLoaded.bind(this)
            );
        }.bind(this)
    );
};

JLEHost.prototype.liveEditorLoaded = function()
{
    this.editor = new this.iframeCtx.JtlLiveEditor('.jle-editable', this);

    $('.portlet-button')
        .attr('draggable', 'true')
        .on('dragstart', this.onDragStart.bind(this))
        .on('dragend', this.onDragEnd.bind(this));

    // this.iframeCtx.jtlToken = jtlToken;

    ioCall('getCmsPageJson', [this.cKey, this.kKey, this.kSprache], function(data) {
        this.editor.loadFromJson(data, ioCall);
    }.bind(this));
};

JLEHost.prototype.onDragStart = function(e)
{
    var elm = $(e.target);
    var newElm = $(elm.data('content'));

    newElm.attr('data-portletid', elm.data('portletid'));
    newElm.attr('data-properties', JSON.stringify(elm.data('defaultprops')));

    this.editor.draggedElm = newElm;

    // firefox needs this
    e.originalEvent.dataTransfer.effectAllowed = 'move';
    e.originalEvent.dataTransfer.setData('text/html', this.editor.draggedElm.innerHTML);
};

JLEHost.prototype.onDragEnd = function(e)
{
    this.editor.cleanUpDrag();
};

JLEHost.prototype.openConfigurator = function(portletId, properties)
{
    var self = this;

    ioCall('getPortletConfigPanelHtml', [portletId, properties], function(configPanelHtml) {
        $('#config-modal-body').html(configPanelHtml);
        $('#config-modal').modal('show');
        self.curPortletId = portletId;
    });
};

JLEHost.prototype.onEditorSave = function (e)
{
    ioCall('saveCmsPage', [
        this.cKey, this.kKey, this.kSprache,
        this.editor.toJson()
    ]);

    e.preventDefault();
};

JLEHost.prototype.onSettingsSave = function (e)
{
    this.configSaveCallback();

    var children = this.editor.selectedElm
        // select direct descendant subareas or non-nested subareas
        .find('> .jle-subarea') ; //, :not(.jle-subarea) .jle-subarea');

    var properties = $('#config-form').serializeControls();

    ioCall('getPortletPreviewHtml', [this.curPortletId, properties], onNewHtml.bind(this));

    function onNewHtml(newHtml)
    {
        var newElm = $(newHtml);

        this.editor.selectedElm.replaceWith(newElm);
        this.editor.setSelected(newElm);
        this.editor.selectedElm.attr('data-portletid', this.curPortletId);
        this.editor.selectedElm.attr('data-properties', JSON.stringify(properties));

        this.editor.selectedElm
            .find('.jle-subarea')
            .each(function(index, subarea) {
                if(index < children.length) {
                    $(subarea).html($(children[index]).html());
                }
            });

        $('#config-modal').modal('hide');
    }

    e.preventDefault();
};

JLEHost.prototype.onOpenKCFinder = function (callback)
{
    window.KCFinder = {
        callBack: function(url) {
            callback(url);
            kcFinder.close();
        }
    };

    var kcFinder = window.open(
        this.kcfinderPath + 'browse.php?type=Bilder&lang=de', 'kcfinder_textbox',
        'status=0, toolbar=0, location=0, menubar=0, directories=0, resizable=1, scrollbars=0,' +
        'width=800, height=600'
    );
};

JLEHost.loadScript = function(ctx, url, callback)
{
    var script = ctx.document.createElement('script');

    script.src = url;
    script.addEventListener('load', callback);
    ctx.document.head.appendChild(script);
};

JLEHost.loadStylesheet = function(ctx, url)
{
    var link = ctx.document.createElement('link');

    link.rel = 'stylesheet';
    link.href = url;
    ctx.document.head.appendChild(link);
};