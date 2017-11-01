
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
    this.settingsSaveCallback = $.noop;
    this.iframe = $('#iframe');

    setJtlToken(jtlToken);

    Split(['#sidebar-panel', '#iframe-panel'], { sizes: [25, 75], gutterSize: 4 });

    this.iframe.on('load', this.iframeLoaded.bind(this));

    $('#jle-btn-save-settings').click(this.onSettingsSave.bind(this));
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

    ioCall('loadLiveEditorContent', [this.cKey, this.kKey, this.kSprache], function(data) {
        this.editor.loadFromJson(data, ioCall);
    }.bind(this));
};

JLEHost.prototype.onDragStart = function(e)
{
    var elm = $(e.target);
    var newElm = $(elm.data('content'));

    newElm.attr('data-portletid', elm.data('portletid'));
    newElm.attr('data-settings', JSON.stringify(elm.data('initialsettings')));

    this.editor.draggedElm = newElm;

    // firefox needs this
    e.originalEvent.dataTransfer.effectAllowed = 'move';
    e.originalEvent.dataTransfer.setData('text/html', this.editor.draggedElm.innerHTML);
};

JLEHost.prototype.onDragEnd = function(e)
{
    this.editor.cleanUpDrag();
};

JLEHost.prototype.openConfigurator = function(portletId, settings)
{
    var self = this;

    ioCall('getPortletSettingsHtml', [portletId, settings], function(settingsHtml) {
        $('#settings-modal .modal-body').html(settingsHtml);
        $('#settings-modal').modal('show');
        self.curPortletId = portletId;
    });
};

JLEHost.prototype.onEditorSave = function (e)
{
    ioCall('saveLiveEditorContent', [
        this.cKey, this.kKey, this.kSprache,
        this.editor.toJson()
    ]);

    e.preventDefault();
};

JLEHost.prototype.onSettingsSave = function (e)
{
    this.settingsSaveCallback();

    var children = this.editor.selectedElm
        // select direct descendant subareas or non-nested subareas
        .find('> .jle-subarea') ; //, :not(.jle-subarea) .jle-subarea');
    var settingsArray = $('#portlet-settings-form').serializeArray();
    var settings = { };

    settingsArray.forEach(function (setting) {
        settings[setting.name] = setting.value;
    });

    ioCall('getPortletPreviewContent', [this.curPortletId, settings], onNewHtml.bind(this));

    function onNewHtml(newHtml)
    {
        var newElm = $(newHtml);

        this.editor.selectedElm.replaceWith(newElm);
        this.editor.setSelected(newElm);
        this.editor.selectedElm.attr('data-portletid', this.curPortletId);
        this.editor.selectedElm.attr('data-settings', JSON.stringify(settings));

        this.editor.selectedElm
            .find('.jle-subarea')
            .each(function(index, subarea) {
                if(index < children.length) {
                    $(subarea).html($(children[index]).html());
                }
            });

        $('#settings-modal').modal('hide');
    }
};

JLEHost.prototype.onOpenKCFinder = function (t, callback)
{
    console.log(t);

    window.KCFinder = {
        callBack: function(url) {
            console.log(url);
            $(t).html('<img src="' + url + '">');
            kcFinder.close();
            callback(url);
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
