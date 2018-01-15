/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param env.notice - Notice message by the server
 * @param env.error - Error message by the server
 * @param env.jtlToken - the current valid CSRF token
 * @param env.templateUrl - the URL to the current admin template
 * @param env.kcfinderUrl - the URL to the KCFinder installation
 * @param env.pageUrl - the URL to the page to edit
 * @param env.cAction - the editor action name
 * @param env.cPageIdHash - current page id hash
 * @constructor
 */
function Editor(env)
{
    this.notice = env.notice;
    this.error  = env.error;

    this.jtlToken    = env.jtlToken;
    this.templateUrl = env.templateUrl;
    this.kcfinderUrl = env.kcfinderUrl;
    this.pageUrl     = env.pageUrl;
    this.cAction     = env.cAction;
    this.cPageIdHash = env.cPageIdHash;

    this.iframeLoaded = false;
    this.curPortletId = 0;

    this.gui = new EditorGUI(this);
    this.io  = new EditorIO(this);

    $(this.onDocumentReady.bind(this));
}

Editor.prototype = {

    constructor: Editor,

    onDocumentReady: function ()
    {
        setJtlToken(this.jtlToken);
        injectJqueryFixes();
        Split(['#sidebar-panel', '#iframe-panel'], { sizes: [25, 75], gutterSize: 4 });

        this.gui.initHostGUI();

        if(this.error !== '') {
            this.gui.showError(this.error);
        }

        if(this.pageUrl === '') {
            this.gui.showError('Parameter f&uuml;r den Live-Editor fehlen!');
        }

        this.gui.showLoader();
        this.gui.iframe
            .on('load', this.onIframeLoad.bind(this))
            .attr('src', this.getIframePageUrl());
    },

    onIframeLoad: function ()
    {
        if(this.iframeLoaded) {
            this.gui.showError('Iframe-URL has changed.');
        }

        this.iframeLoaded = true;
        this.gui.initIframeGUI();
        this.gui.hideLoader();
        this.io.loadPage();
    },

    getIframePageUrl: function ()
    {
        var pageUrlLink = document.createElement('a');

        pageUrlLink.href = this.pageUrl;

        if(pageUrlLink.search !== '') {
            pageUrlLink.search += '&editpage=1&cAction=' + this.cAction;
        } else {
            pageUrlLink.search = '?editpage=1&cAction=' + this.cAction;
        }

        return pageUrlLink.href.toString();
    },

    closeEditor: function ()
    {
        ioCall('unlockCmsPage', [this.cPageIdHash]);
    },

    saveEditorPage: function (success, error)
    {
        this.io.savePage(success, error);
    },

    storeTemplate: function (portlet)
    {
        this.io.storePortletAsTemplate(portlet, 'neuesTemplate');
    },

    onOpenKCFinder: function (callback)
    {
        KCFinder = {
            callBack: function(url) {
                callback(url);
                kcFinder.close();
            }
        };

        var kcFinder = open(
            this.kcfinderUrl + 'browse.php?type=Bilder&lang=de', 'kcfinder_textbox',
            'status=0, toolbar=0, location=0, menubar=0, directories=0, resizable=1, scrollbars=0,' +
            'width=800, height=600'
        );
    },

    setConfigSaveCallback: function (callback)
    {
        this.gui.configSaveCallback = callback;
    },

    openTemplateStoreDialog: function(portletId, properties)
    {
        // todo editor: .tpl in popup laden (zukünftig erweiterbar)
    },

};

function injectJqueryFixes()
{
    // Fix from: https://stackoverflow.com/questions/22637455/how-to-use-ckeditor-in-a-bootstrap-modal
    // to enable CKEditor to show popups when used in a bootstrap modal
    $.fn.modal.Constructor.prototype.enforceFocus = function ()
    {
        var $modalElement = this.$element;

        $(document).on('focusin.modal', function (e)
        {
            var $parent = $(e.target.parentNode);

            if ($modalElement[0] !== e.target &&
                !$modalElement.has(e.target).length &&
                !$parent.hasClass('cke_dialog_ui_input_select') &&
                !$parent.hasClass('cke_dialog_ui_input_text')
            ) {
                $modalElement.focus();
            }
        });
    };

    // Fix from: https://stackoverflow.com/questions/11127227/jquery-serialize-input-with-arrays/35689636
    // to serialize data from array-like inputs
    $.fn.serializeControls = function()
    {
        var data = {};

        function buildInputObject(arr, val)
        {
            if (arr.length < 1) {
                return val;
            }

            var objkey = arr[0];
            var result = {};

            if (objkey.slice(-1) === ']') {
                objkey = objkey.slice(0, -1);
            }

            if (arr.length === 1) {
                result[objkey] = val;
            }
            else {
                arr.shift();
                result[objkey] = buildInputObject(arr, val);
            }

            return result;
        }

        $.each(this.serializeArray(), function() {
            $.extend(
                true, data,
                buildInputObject(
                    this.name.split('['), this.value
                )
            );
        });

        return data;
    };
}

function loadStylesheet(ctx, url)
{
    var link = ctx.document.createElement('link');

    link.rel = 'stylesheet';
    link.href = url;
    ctx.document.head.appendChild(link);
}