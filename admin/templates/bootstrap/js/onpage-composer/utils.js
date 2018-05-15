/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

var log = console.log;

function noop() {}

function installJqueryFixes()
{
    // Fix from: https://stackoverflow.com/questions/22637455/how-to-use-ckeditor-in-a-bootstrap-modal
    // to enable CKEditor to show popups when used in a bootstrap modals

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

        $.each(this.serializeArray(), function(i, item) {
            var path   = item.name.split('[');
            var value  = item.value;
            var target = data;

            while(path.length > 0) {
                var key = path.shift();

                if (key.slice(-1) === ']') {
                    key = key.slice(0, -1);
                }

                if (key === '') {
                    key = Object.keys(target).length;
                }

                if(path.length === 0) {
                    target[key] = value;
                } else {
                    target[key] = target[key] || {};
                    target      = target[key];
                }
            }
        });

        return data;
    };

    // Fix from: https://stackoverflow.com/questions/5347357/jquery-get-selected-element-tag-name
    // to conveniently get the tag name of a matched element

    $.fn.tagName = function() {
        return this.prop("tagName").toLowerCase();
    };
}

function capitalize(str)
{
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function bindProtoOnHandlers(obj)
{
    var proto = obj.constructor.prototype;
    var keys  = Object.keys(proto);

    for(var i=0; i<keys.length; i++) {
        var key    = keys[i];
        var member = proto[key];

        if(typeof member === 'function' && key.substr(0, 2) === 'on') {
            obj[key] = member.bind(obj);
        }
    }
}

/**
 * Query DOM elements, bind handlers available in obj to them and set them as properties to obj
 * @param obj
 * @param elmIds
 */
function installGuiElements(obj, elmIds)
{
    elmIds.forEach(function(elmId) {
        var elm         = $('#' + elmId);
        var elmVarName  = elmId;
        var handlerName = '';

        if (elm.length === 0) {
            elm         = $('.' + elmId);
            elmVarName  = elmId + 's';
        }

        if (elm.length === 0) {
            log('warning: ' + elmId + ' could not be found');
            return;
        }

        if (elm.attr('draggable') === 'true') {
            handlerName = 'on' + capitalize(elmId) + 'DragStart';

            if (obj[handlerName]) {
                elm.off('dragstart').on('dragstart', obj[handlerName]);
            }

            handlerName = 'on' + capitalize(elmId) + 'DragEnd';

            if (obj[handlerName]) {
                elm.off('dragend').on('dragend', obj[handlerName]);
            }

        } else if (elm.tagName() === 'a' || elm.tagName() === 'button') {
            handlerName = 'on' + capitalize(elmId);

            if (obj[handlerName]) {
                elm.off('click').click(obj[handlerName]);
            }
        } else if (elm.tagName() === 'form') {
            handlerName = 'on' + capitalize(elmId);

            if (obj[handlerName]) {
                elm.off('submit').submit(obj[handlerName]);
            }
        }

        obj[elmVarName] = elm;
    });
}

function initDragStart(e)
{
    // firefox needs this
    e.originalEvent.dataTransfer.effectAllowed = 'move';
    e.originalEvent.dataTransfer.setData('text/html', '');
}