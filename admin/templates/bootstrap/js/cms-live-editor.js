/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param env.jtlToken - the current valid CSRF token
 * @param env.templateUrl - the URL to the current admin template
 * @param env.kcfinderUrl - the URL to the KCFinder installation
 * @param env.pageUrl - the URL to the page to edit
 * @param env.cAction - the editor action name
 * @param env.cKey - current page parameter
 * @param env.kKey - current page parameter
 * @param env.kSprache - current page parameter
 */
function CmsLiveEditor(env)
{
    this.jtlToken = env.jtlToken;
    this.templateUrl = env.templateUrl;
    this.kcfinderUrl = env.kcfinderUrl;
    this.pageUrl = env.pageUrl;
    this.cAction = env.cAction;
    this.cKey = env.cKey;
    this.kKey = env.kKey;
    this.kSprache = env.kSprache;

    this.hostCtx = window;
    this.hostJq = this.hostCtx.$;
    this.noop = this.hostJq.noop;
    this.iframeElm = null;
    this.iframeCtx = null;
    this.iframeJq = null;
    this.iframeLoaded = false;

    this.configModalElm = null;
    this.hoveredElm = null;
    this.selectedElm = null;
    this.draggedElm = null;
    this.rootElm = null;
    this.labelElm = null;
    this.pinbarElm = null;
    this.dropTarget = null;

    this.curPortletId = 0;
    this.configSaveCallback = this.noop;

    this.previewMode = false;

    this.hostJq(this.onDocumentReady.bind(this));
}

CmsLiveEditor.prototype = {

    constructor: CmsLiveEditor,

    onDocumentReady: function()
    {
        setJtlToken(this.jtlToken);

        Split(['#sidebar-panel', '#iframe-panel'], { sizes: [25, 75], gutterSize: 4 });

        injectJqueryFixes();

        this.iframeElm = this.hostJq('#iframe');

        this.iframeElm
            .on('load', this.onIframeLoad.bind(this))
            .attr('src', this.pageUrl + '?editpage=1&cAction=' + this.cAction);
    },

    onIframeLoad: function()
    {
        if(this.iframeLoaded) {
            throw 'Iframe-URL has changed.';
        }

        this.iframeLoaded = true;
        this.iframeCtx = this.iframeElm[0].contentWindow;
        this.iframeJq = this.iframeCtx.$;

        loadStylesheet(this.iframeCtx, this.templateUrl + 'css/cms-live-editor-iframe.css');

        this.initEditor();
    },

    initEditor: function()
    {
        // disable links and buttons that could change the current iframes location

        this.iframeJq('a, button')
            .off('click')
            .attr('onclick', '')
            .click(function(e) {
                e.preventDefault();
            });

        this.rootElm = this.iframeJq('.cle-area');
        this.labelElm = this.iframeJq('<div>', { 'class': 'cle-label' }).appendTo('body').hide();
        this.pinbarElm = this.hostJq('#pinbar');
        this.btnTrashElm = this.hostJq('#btn-trash');
        this.btnCloneElm = this.hostJq('#btn-clone');
        this.btnConfigElm = this.hostJq('#btn-config');
        this.portletBtnElms = this.hostJq('.portlet-button');
        this.loaderBackdrop = this.hostJq('#loader-backdrop');
        this.editorSaveBtnElm = this.hostJq('#cle-btn-save-editor');
        this.configModalElm = this.hostJq('#config-modal');
        this.configModalBodyElm = this.hostJq('#config-modal-body');
        this.configFormElm = this.hostJq('#config-form');

        this.enableEvents();

        this.hostJq('#btn-preview')
            .click(this.togglePreview.bind(this));

        this.pinbarElm
            .appendTo(this.iframeCtx.document.body);

        this.btnTrashElm
            .click(this.onTrash.bind(this));

        this.btnCloneElm
            .click(this.onClone.bind(this));

        this.btnConfigElm
            .click(this.onConfig.bind(this));

        this.portletBtnElms
            .attr('draggable', 'true')
            .on('dragstart', this.onPortletBtnDragStart.bind(this))
            .on('dragend', this.onPortletBtnDragEnd.bind(this));

        this.configModalElm
            .submit(this.onSettingsSave.bind(this));

        this.editorSaveBtnElm
            .click(this.onEditorSave.bind(this));

        this.loaderBackdrop
            .hide();

        this.loadPage();
    },

    enableEvents: function()
    {
        this.rootElm
            .on('mouseover', this.onMouseOver.bind(this))
            .on('click', this.onClick.bind(this))
            .on('dblclick', this.onConfig.bind(this))
            .on('dragstart', this.onDragStart.bind(this))
            .on('dragend', this.onDragEnd.bind(this))
            .on('dragover', this.onDragOver.bind(this))
            .on('drop', this.onDrop.bind(this));

        this.iframeJq(this.iframeCtx.document)
            .on('keydown', this.onKeyDown.bind(this));
    },

    disableEvents: function()
    {
        this.rootElm
            .off('mouseover')
            .off('click')
            .off('dblclick')
            .off('dragstart')
            .off('dragend')
            .off('dragover')
            .off('drop');

        this.iframeJq(this.iframeCtx.document)
            .off('keydown');
    },

    onPortletBtnDragStart: function(e)
    {
        var elm = this.hostJq(e.target);

        var newElm = $(elm.data('content'));

        newElm.attr('data-portletid', elm.data('portletid'));
        newElm.attr('data-portlettitle', elm.data('portlettitle'));
        newElm.attr('data-properties', JSON.stringify(elm.data('defaultprops')));

        this.setDragged(newElm);

        // firefox needs this
        e.originalEvent.dataTransfer.effectAllowed = 'move';
        e.originalEvent.dataTransfer.setData('text/html', '');
    },

    onPortletBtnDragEnd: function(e)
    {
        this.cleanUpDrag();
    },

    openConfigurator: function(portletId, properties)
    {
        this.configSaveCallback = this.noop;

        ioCall(
            'getPortletConfigPanelHtml',
            [portletId, properties],
            function(configPanelHtml)
            {
                this.configModalBodyElm.html(configPanelHtml);
                this.configModalElm.modal('show');
                this.curPortletId = portletId;

            }.bind(this)
        );
    },

    onEditorSave: function (e)
    {
        this.loaderBackdrop.show();

        this.savePage(
            function() {
                this.loaderBackdrop.hide();
            },
            function () {
                window.location.reload();
            }
        );

        e.preventDefault();
    },

    onSettingsSave: function (e)
    {
        this.configSaveCallback();

        var children = this.selectedElm
        // select direct descendant subareas or non-nested subareas
            .find('> .cle-area') ; //, :not(.jle-subarea) .jle-subarea');

        var properties = this.configFormElm.serializeControls();

        ioCall('getPortletPreviewHtml', [this.curPortletId, properties], onNewHtml.bind(this));

        function onNewHtml(newHtml)
        {
            var newElm = this.iframeJq(newHtml);
            var portletTitle = this.selectedElm.data('portlettitle');

            this.selectedElm.replaceWith(newElm);
            this.setSelected(newElm);
            this.selectedElm.attr('data-portletid', this.curPortletId);
            this.selectedElm.attr('data-portlettitle', portletTitle);
            this.selectedElm.attr('data-properties', JSON.stringify(properties));

            this.selectedElm
                .find('.cle-area')
                .each(function(index, subarea) {
                    if(index < children.length) {
                        this.iframeJq(subarea).html(
                            this.iframeJq(children[index]).html()
                        );
                    }
                }.bind(this));

            this.configModalElm.modal('hide');
            this.updateDropTargets();
        }

        e.preventDefault();
    },

    onOpenKCFinder: function (callback)
    {
        this.hostCtx.KCFinder = {
            callBack: function(url) {
                callback(url);
                kcFinder.close();
            }
        };

        var kcFinder = this.hostCtx.open(
            this.kcfinderUrl + 'browse.php?type=Bilder&lang=de', 'kcfinder_textbox',
            'status=0, toolbar=0, location=0, menubar=0, directories=0, resizable=1, scrollbars=0,' +
            'width=800, height=600'
        );
    },

    onMouseOver: function(e)
    {
        var elm = this.iframeJq(e.target);

        while(!this.isSelectable(elm) && !elm.is(this.rootElm)) {
            elm = elm.parent();
        }

        if(this.isSelectable(elm)) {
            this.setHovered(elm);
        }
        else {
            this.setHovered();
        }
    },

    onClick: function(e)
    {
        var elm = this.iframeJq(e.target);

        while(!this.isSelectable(elm) && !elm.is(this.rootElm)) {
            elm = elm.parent();
        }

        if(this.isSelectable(elm)) {
            this.setSelected(elm);
        }
        else {
            this.setSelected();
        }
    },

    onKeyDown: function(e)
    {
        if(e.key === 'Delete' && this.selectedElm !== null) {
            this.onTrash(e);
        }
    },

    onDragStart: function(e)
    {
        var elm = this.iframeJq(e.target);

        while(!this.isSelectable(elm) && !elm.is(this.rootElm)) {
            elm = elm.parent();
        }

        this.setDragged(elm);

        // firefox needs this
        e.originalEvent.dataTransfer.effectAllowed = 'move';
        e.originalEvent.dataTransfer.setData('text/html', '');
    },

    onDragOver: function(e)
    {
        var elm = this.iframeJq(e.target);

        if(elm.hasClass('cle-droptarget') && !this.isDescendant(elm, this.draggedElm)) {
            this.setDropTarget(elm);
        }
        else {
            this.setDropTarget();
        }

        e.preventDefault();
    },

    onDrop: function(e)
    {
        if(this.dropTarget !== null) {
            this.dropTarget.replaceWith(this.draggedElm);
            this.updateDropTargets();
            this.setSelected();
            this.setSelected(this.draggedElm);
        }
    },

    onDragEnd: function(e)
    {
        this.cleanUpDrag();
    },

    onTrash: function(e)
    {
        if(this.selectedElm !== null) {
            this.selectedElm.remove();
            this.setSelected();
            this.updateDropTargets();
        }
    },

    onClone: function(e)
    {
        if(this.selectedElm !== null) {
            var copiedElm = this.selectedElm.clone();
            copiedElm.insertAfter(this.selectedElm);
            copiedElm.removeClass('cle-selected');
            copiedElm.removeClass('cle-hovered');
            this.setSelected(this.selectedElm);
        }
    },

    onConfig: function(e)
    {
        this.openConfigurator(
            this.selectedElm.data('portletid'),
            this.selectedElm.data('properties')
        );
    },

    setHovered: function(elm)
    {
        elm = elm || null;

        if(this.hoveredElm !== null) {
            this.hoveredElm.removeClass('cle-hovered');
            this.hoveredElm.attr('draggable', 'false');
            this.labelElm.hide();
        }

        this.hoveredElm = elm;

        if(this.hoveredElm !== null) {
            this.hoveredElm.addClass('cle-hovered');
            this.hoveredElm.attr('draggable', 'true');
            this.labelElm
                .text(this.hoveredElm.data('portlettitle'))
                .show()
                .css({
                    left: elm.offset().left + 'px',
                    top: elm.offset().top - this.labelElm.outerHeight() + 'px'
                })
        }
    },

    setSelected: function(elm)
    {
        elm = elm || null;

        if(elm === null || !elm.is(this.selectedElm)) {
            if(this.selectedElm !== null) {
                this.selectedElm.removeClass('cle-selected');
                this.pinbarElm.hide();
            }

            this.selectedElm = elm;

            if(this.selectedElm !== null) {
                this.selectedElm.addClass('cle-selected');
                this.pinbarElm
                    .show()
                    .css({
                        left: elm.offset().left + elm.outerWidth() - this.pinbarElm.outerWidth() + 'px',
                        top: elm.offset().top + elm.outerHeight() + 'px'
                    });
            }
        }
    },

    setDragged: function(elm)
    {
        elm = elm || null;

        if(this.draggedElm !== null) {
            this.draggedElm.removeClass('cle-dragged');
        }

        this.draggedElm = elm;

        if(this.draggedElm !== null) {
            this.draggedElm.addClass('cle-dragged');
        }
    },

    setDropTarget: function(elm)
    {
        elm = elm || null;

        if(this.dropTarget !== null) {
            this.dropTarget.removeClass('cle-active-droptarget');
        }

        this.dropTarget = elm;

        if(this.dropTarget !== null) {
            this.dropTarget.addClass('cle-active-droptarget');
        }
    },

    cleanUpDrag: function()
    {
        this.setDragged();
        this.setDropTarget();
    },

    isSelectable: function(elm)
    {
        return elm.attr('data-portletid') !== undefined;
        // return elm.is(this.rootElm);
        // return !this.isInline(elm) && !elm.is(this.rootElm) && !elm.parent().is('.row');
        // && !elm.is(this.selectedLabelElm) && !elm.is(this.targetLabelElm);
    },

    isInline: function(elm)
    {
        return elm.css('display') === 'inline' || elm.css('display') === 'inline-block';
    },

    isDescendant: function(descendant, tree)
    {
        return tree.has(descendant).length > 0;
    },

    loadPage: function()
    {
        ioCall(
            'getCmsPageJson',
            [this.cKey, this.kKey, this.kSprache],
            this.pageFromJson.bind(this)
        );
    },

    savePage: function(success, error)
    {
        success = success || this.noop;
        error = error || this.noop;

        ioCall(
            'saveCmsPage',
            [this.cKey, this.kKey, this.kSprache, this.pageToJson()],
            success.bind(this),
            error.bind(this)
        );
    },

    pageToJson: function()
    {
        var result = {};

        this.rootElm.each(function(i, rootArea)
        {
            result[rootArea.id] = this.areaToJson($(rootArea));

        }.bind(this));

        return result;
    },

    areaToJson: function(rootArea)
    {
        var result = [];

        rootArea.children('[data-portletid]').each(function(i, portletElm)
        {
            result.push(this.portletToJson(this.iframeJq(portletElm)));

        }.bind(this));

        return result;
    },

    portletToJson: function(portletElm)
    {
        var result = {};

        result.portletId = portletElm.data('portletid');
        result.portletTitle = portletElm.data('portlettitle');
        result.properties = portletElm.data('properties');
        result.subAreas = [];

        var children = portletElm
        // select direct descendant subareas or non-nested subareas
            .find('> .cle-area') ; //, :not(.jle-subarea) .jle-subarea');

        children.each(function (i, child)
        {
            result.subAreas.push(this.areaToJson($(child)));

        }.bind(this));

        return result;
    },

    pageFromJson: function(data)
    {
        for(var areaId in data) {
            this.areaFromJson(data[areaId], this.iframeJq('#' + areaId));
        }

        this.updateDropTargets();
    },

    areaFromJson: function(data, areaElm)
    {
        data.forEach(function(portletData)
        {
            var portletElm = this.iframeJq('<div><i class="fa fa-spinner fa-pulse fa-2x"></i></div>');

            areaElm.append(portletElm);

            ioCall('getPortletPreviewHtml', [portletData.portletId, portletData.properties], function (newHtml)
            {
                var newElm = this.iframeJq(newHtml);

                portletElm.replaceWith(newElm);
                newElm.attr('data-portletid', portletData.portletId);
                newElm.attr('data-portlettitle', portletData.portletTitle);
                newElm.attr('data-properties', JSON.stringify(portletData.properties));

                this.updateDropTargets();

                newElm.find('.cle-area').each(function (index, subarea)
                {
                    this.areaFromJson(portletData.subAreas[index], this.iframeJq(subarea));

                }.bind(this));
            }.bind(this));
        }.bind(this));
    },

    updateDropTargets: function()
    {
        this.iframeJq('.cle-droptarget').remove();
        this.iframeJq('.cle-area').append('<div class="cle-droptarget">');
        this.iframeJq('[data-portletid]').before('<div class="cle-droptarget">');
    },

    stripDropTargets: function()
    {
        this.iframeJq('.cle-droptarget').remove();
    },

    togglePreview: function()
    {
        if (this.previewMode) {
            this.updateDropTargets();
            this.enableEvents();
            this.iframeJq('[data-portletid]').removeClass('cle-preview');
            this.previewMode = false;
        }
        else {
            this.stripDropTargets();
            this.disableEvents();
            this.setSelected();
            this.setHovered();
            this.iframeJq('[data-portletid]').addClass('cle-preview');
            this.previewMode = true;
        }
    },

    getPageStorageId: function()
    {
        return 'cmspage.' + this.cKey + '.' + this.kKey + '.' + this.kSprache + '.' + this.cAction;
    },

    savePageToWebStorage: function()
    {
        window.localStorage.setItem(
            this.getPageStorageId(),
            JSON.stringify(this.pageToJson())
        );
        window.localStorage.setItem(
            this.getPageStorageId() + '.lastmodified',
            moment().format("YYYY-MM-DD HH:mm:ss")
        )
    },

    loadPageFromWebStorage: function()
    {
        var pageJson = window.localStorage.getItem(this.getPageStorageId());

        if(pageJson !== null) {
            this.clearPage();
            this.pageFromJson(JSON.parse(pageJson));
        }
    },

    clearPage: function()
    {
        this.rootElm.empty();
        this.updateDropTargets();
    },

    setUnsaved: function(enable)
    {
        this.editorSaveBtnElm.find('i').html(enable ? '*' : '');
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