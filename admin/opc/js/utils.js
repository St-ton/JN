export class Emitter extends EventTarget
{
    constructor()
    {
        super();

        this.anyEventListeners = new Set();
    }

    subject(name)
    {
        return this.subjects[name] = this.subjects[name] || new Subject();
    }

    on(name, cb)
    {
        if (name === '*') {
            this.anyEventListeners.add(cb);
        }
        else {
            this.addEventListener(name, cb);
        }
    }

    off(name, cb)
    {
        if (name === '*') {
            this.anyEventListeners.delete(cb);
        }
        else {
            this.removeEventListener(name, cb);
        }
    }

    once(name, cb)
    {
        this.addEventListener(name, cb, true);
    }

    emit(name, data)
    {
        let event = Object.assign(new CustomEvent(name), {data});
        this.dispatchEvent(event);
        this.anyEventListeners.forEach(cb => cb(event));
    }
}

function noop() {}

function installJqueryFixes()
{
    // Fix from: https://gist.github.com/Reinmar/b9df3f30a05786511a42#gistcomment-2897528
    // to ensure CKEditor text inputs are focused inside bootstrap modals

    $.fn.modal.Constructor.prototype._enforceFocus = function() {
        let $element = $(this._element);
        $(document)
            .off('focusin.bs.modal')
            .on('focusin.bs.modal', function(e) {
                if ($element[0] !== e.target
                    && !$element.has(e.target).length
                    && !$(e.target).closest('.cke_dialog, .cke').length
                ) {
                    $element.trigger('focus');
                }
            });
    };

    // Fix from: https://stackoverflow.com/questions/11127227/jquery-serialize-input-with-arrays/35689636
    // to serialize data from array-like inputs

    $.fn.serializeControls = function()
    {
        var data = {};
        var arr = this.serializeArray();

        arr.forEach(function(item, i) {
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

export function capitalize(str)
{
    return str.charAt(0).toUpperCase() + str.slice(1);
}

export function sleep(ms)
{
    return new Promise(res => setTimeout(res, ms));
}

function bindProtoOnHandlers(obj)
{
    var proto = obj.constructor.prototype;
    var keys  = Object.getOwnPropertyNames(proto);

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
            console.log('warning: ' + elmId + ' could not be found');
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
                elm.off('click').on('click', obj[handlerName]);
            }
        } else if (elm.tagName() === 'form') {
            handlerName = 'on' + capitalize(elmId);

            if (obj[handlerName]) {
                elm.off('submit').submit(obj[handlerName]);
            }
        } else if (elm.tagName() === 'input' && elm.attr('type') === 'checkbox') {
            handlerName = 'on' + capitalize(elmId);

            if (obj[handlerName]) {
                elm.off('click').on('click', obj[handlerName]);
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
