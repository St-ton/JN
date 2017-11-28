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
    this.configSaveCallback = this.hostJq.noop;

    this.newPortlet = {
        content: '',
        portletId: 0,
        defaultProps: {}
    };

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
            .attr('src', this.pageUrl + '?editpage=1&action=' + this.cAction);
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
        this.iframeJq('a, button')
            .off('click')
            .attr('onclick', '')
            .click(function(e) {
                console.log('link click prevented');
                e.preventDefault();
            });

        this.rootElm = this.iframeJq('.cle-area');
        this.labelElm = this.iframeJq('<div>', { 'class': 'cle-label' }).appendTo('body').hide();
        this.pinbarElm = this.createPinbar().appendTo('body').hide();
        this.portletBtnElms = this.hostJq('.portlet-button');
        this.configModalElm = this.hostJq('#config-modal');
        this.configModalBodyElm = this.hostJq('#config-modal-body');
        this.configFormElm = this.hostJq('#config-form');
        this.editorSaveBtnElm = this.hostJq('#cle-btn-save-editor');
        this.loaderBackdrop = this.hostJq('#loader-backdrop');

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

        ioCall('getCmsPageJson', [this.cKey, this.kKey, this.kSprache], this.loadFromJson.bind(this));
    },

    onPortletBtnDragStart: function(e)
    {
        var elm = this.hostJq(e.target);

        var newElm = $(elm.data('content'));

        newElm.attr('data-portletid', elm.data('portletid'));
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

        ioCall(
            'saveCmsPage',
            [
                this.cKey, this.kKey, this.kSprache,
                this.toJson()
            ],
            function() {
                this.loaderBackdrop.hide();
            }.bind(this));

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

            this.selectedElm.replaceWith(newElm);
            this.setSelected(newElm);
            this.selectedElm.attr('data-portletid', this.curPortletId);
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
        }
    },

    onDragEnd: function(e)
    {
        this.cleanUpDrag();
    },

    onFocus: function(e)
    {
        console.log('focus', this.selectedElm);
    },

    onBlur: function(e)
    {
        console.log('blur', this.selectedElm);
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
            var labelText = (
                this.hoveredElm.prop('tagName').toLowerCase() + "." +
                this.hoveredElm.attr('class').split(' ').join('.')
            );
            this.labelElm
                .text(labelText)
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
                this.selectedElm.off('blur').on('blur', this.onBlur.bind(this));
                this.selectedElm.off('focus').on('focus', this.onFocus.bind(this));
                this.pinbarElm
                    .show()
                    .css({
                        left: elm.offset().left + elm.outerWidth() - this.pinbarElm.outerWidth() + 'px',
                        top: elm.offset().top - this.pinbarElm.outerHeight() + 'px'
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

    createPinbar: function()
    {
        var pinbarElm = this.iframeJq('<div class="cle-pinbar btn-group">');

        pinbarElm.append(
            this.iframeJq('<button class="btn btn-default" id="cle-btn-trash"><i class="fa fa-trash"></i></button>')
                .click(this.onTrash.bind(this))
        );
        pinbarElm.append(
            this.iframeJq('<button class="btn btn-default"><i class="fa fa-clone"></i></button>')
                .click(this.onClone.bind(this))
        );
        pinbarElm.append(
            this.iframeJq('<button class="btn btn-default"><i class="fa fa-cog"></i></button>')
                .click(this.onConfig.bind(this))
        );

        return pinbarElm;
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

    toJson: function()
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

    loadFromJson: function(data)
    {
        console.log(data);

        for(var areaId in data) {
            this.loadAreaFromJson(data[areaId], this.iframeJq('#' + areaId));
        }

        this.updateDropTargets();
    },

    loadAreaFromJson: function(data, areaElm)
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
                newElm.attr('data-properties', JSON.stringify(portletData.properties));

                this.updateDropTargets();

                newElm.find('.cle-area').each(function (index, subarea)
                {
                    this.loadAreaFromJson(portletData.subAreas[index], this.iframeJq(subarea));

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
    }

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
    // to serialize input from array-like inputs
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