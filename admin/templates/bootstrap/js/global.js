/**
 * @returns {jQuery.fn}
 */
jQuery.fn.center = function () {
    this.css('position', 'absolute');
    this.css('top', ( $(window).height() - this.height() ) / 2 + $(window).scrollTop() + 'px');
    this.css('left', ( $(window).width() - this.width() ) / 2 + $(window).scrollLeft() + 'px');
    return this;
};

/**
 * @param type
 * @param assign
 */
jQuery.fn.set_search = function (type, assign) {
    this.click(function () {
        $('.ajax_list_picker.' + type).center().fadeIn(850);
        $('#' + type + '_list_input').focus().val('');
        // empty list views
        set_selected_list(type, $(assign).val());
        $('select[name="' + type + '_list_found"]').empty();
        // set event handler
        if (!$(this).hasClass('init')) {
            $('#' + type + '_list_input').keyup(function () {
                search_list(type, $('#' + type + '_list_input').val());
            });
            $('#' + type + '_list_save').click(function () {
                // save
                var list = '';
                $('select[name="' + type + '_list_selected"] option').each(function (i) {
                    list += $(this).val() + ';';
                });
                $(assign).val(list);
                $('#' + type + '_list_cancel').trigger('click');
                return false;
            });
            $('#' + type + '_list_cancel').click(function () {
                // cancel
                $('.ajax_list_picker.' + type).fadeOut(500);
                return false;
            });
            // mark as initialized
            $(this).addClass('init');
        }
        return false;
    });
};

/**
 * @param type
 * @param list
 */
function set_selected_list(type, list) {
    var myCallback = xajax.callback.create(),
        cb;
    myCallback.onComplete = function (obj) {
        // remove last result set
        $('select[name="' + type + '_list_selected"]').empty();
        // selected list
        $.each(obj.context.selected_arr, function (k, v) {
            $('select[name="' + type + '_list_selected"]').append(
                $('<option></option>').val(v.cBase).html(v.cBase + ' | ' + v.cName).dblclick(function () {
                    $(this).remove();
                })
            );
        });
    };

    cb = get_list_callback(type, 1);
    if (cb) {
        xajax.call(cb, {parameters: [list], callback: myCallback, context: this});
    }
}

/**
 * @param type
 * @param search
 * @returns {boolean}
 */
function search_list(type, search) {
    var myCallback = xajax.callback.create(),
        cb;
    myCallback.onComplete = function (obj) {
        // remove last result set
        $('select[name="' + type + '_list_found"]').empty();
        // search list
        $.each(obj.context.search_arr, function (k, v) {
            $('select[name="' + type + '_list_found"]').append(
                $('<option></option>').val(v.cBase).html(v.cBase + ' | ' + v.cName).dblclick(function () {
                    // selected list
                    $('select[name="' + type + '_list_selected"]').append(
                        $('<option></option>').val(v.cBase).html(v.cBase + ' | ' + v.cName).dblclick(function () {
                            $(this).remove();
                        })
                    );
                })
            );
        });
    };

    cb = get_list_callback(type, 0);
    if (cb) {
        xajax.call(cb, {parameters: [search, type], callback: myCallback, context: this});
    }
    return false;
}

/**
 * @param type
 * @param id
 * @returns {*}
 */
function get_list_callback(type, id) {
    switch (type) {
        case 'article':
            return (id == 0) ? 'getArticleList' :
                'getArticleListFromString';

        case 'manufacturer':
            return (id == 0) ? 'getManufacturerList' :
                'getManufacturerListFromString';

        case 'categories':
            return (id == 0) ? 'getCategoryList' :
                'getCategoryListFromString';

        case 'tag':
            return (id == 0) ? 'getTagList' :
                'getTagListFromString';

        case 'attribute':
            return (id == 0) ? 'getAttributeList' :
                'getAttributeListFromString';
    }
    return false;
}

/**
 * single search browser
 * @param callback
 */
function init_simple_search(callback) {
    var search,
        type,
        res,
        selected,
        browser = $('.single_search_browser');
    browser.find('input').keyup(function () {
        search = $(this).val();
        type = browser.attr('type');
        browser.find('select').empty();
        simple_search_list(type, search, function (result) {
            $(result).each(function (k, v) {
                browser.find('select').append(
                    $('<option></option>').attr('primary', v.kPrimary).attr('url', v.cUrl).val(v.cBase).html(v.cName).dblclick(function () {
                        browser.find('.button.add').trigger('click');
                    })
                );
            });
        });
    });

    browser.find('.button.remove').click(function () {
        browser.fadeOut(850);
    });

    browser.find('.button.add').click(function () {
        // callback
        res = {'kPrimary': 0, 'kKey': 0, 'cName': '', 'cUrl': ''};
        type = browser.attr('type');
        selected = browser.find('select option:selected');
        res.kKey = $(selected).val();
        res.cName = $(selected).html();
        res.kPrimary = $(selected).attr('primary');
        res.cUrl = $(selected).attr('url');

        if (typeof callback === 'function') {
            callback(type, res);
        }
        browser.find('.button.remove').trigger('click');
    });
}

/**
 * @param type
 */
function show_simple_search(type) {
    var browser = $('.single_search_browser');
    browser.attr('type', type);
    browser.center().fadeIn(850);
    browser.find('select').empty();
    browser.find('input').val('').focus();
}

/**
 * @param type
 * @param search
 * @param callback
 * @returns {boolean}
 */
function simple_search_list(type, search, callback) {
    var myCallback = xajax.callback.create(),
        cb;
    myCallback.onComplete = function (obj) {
        callback(obj.context.search_arr);
    };

    cb = get_list_callback(type, 0);
    if (cb) {
        xajax.call(cb, {parameters: [search, type], callback: myCallback, context: this});
    }
    return false;
}

/**
 *
 */
function banners_datepicker() {
    var v = $('#vDatum'),
        b = $('#bDatum');
    if (v && b && v.length > 0 && b.length > 0) {
        v.datepicker();
        b.datepicker();
    }
}

/**
 * @param form
 * @constructor
 */
function AllMessages(form) {
    var x,
        y;
    for (x = 0; x < form.elements.length; x++) {
        y = form.elements[x];
        if (y.name !== 'ALLMSGS') {
            y.checked = form.ALLMSGS.checked;
        }
    }
}

/**
 * @param selector
 */
function checkToggle(selector) {
    var elem = $(selector + ' input[type="checkbox"]');
    elem.prop('checked', !elem.prop('checked'));
}

/**
 * @param form
 * @param cID
 * @constructor
 */
function AllMessagesExcept(form, cID) {
    var x,
        y;
    for (x = 0; x < form.elements.length; x++) {
        y = form.elements[x];
        if (y.name !== 'ALLMSGS') {
            if (cID.length > 0) {
                if (y.id.indexOf(cID)) {
                    y.checked = form.ALLMSGS.checked;
                }
            }
        }
    }
}

/**
 * @param elemID
 * @param picExpandID
 * @param picRetractID
 */
function expand(elemID, picExpandID, picRetractID) {
    var elem;
    if (elemID.length > 0) {
        elem = document.getElementById(elemID);
        if (typeof(elem) !== 'undefined') {
            elem.style.display = 'table-row';
            if (picExpandID.length > 0 && picRetractID.length > 0) {
                document.getElementById(picExpandID).style.display = 'none';
                document.getElementById(picRetractID).style.display = 'table-row';
            }
        }
    }
}

/**
 * @param elemID
 * @param picExpandID
 * @param picRetractID
 */
function retract(elemID, picExpandID, picRetractID) {
    var elem;
    if (elemID.length > 0) {
        elem = document.getElementById(elemID);
        if (typeof(elem) !== 'undefined') {
            elem.style.display = 'none';
            if (picExpandID.length > 0 && picRetractID.length > 0) {
                document.getElementById(picExpandID).style.display = 'table-row';
                document.getElementById(picRetractID).style.display = 'none';
            }
        }
    }
}

/**
 * @param url
 * @param params
 * @param callback
 * @returns {*}
 */
function ajaxCall(url, params, callback) {
    return $.ajax({
        type: "GET",
        dataType: "json",
        url: url,
        data: params,
        success: function (data, textStatus, jqXHR) {
            if (typeof callback === 'function') {
                callback(data);
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            if (typeof callback === 'function') {
                callback(jqXHR.responseJSON, jqXHR);
            }
        }
    });
}

/**
 * Format file size
 */
function formatSize(bytes, si) {
    var thresh = 1024;
    if (Math.abs(bytes) < thresh) {
        return bytes + ' b';
    }
    var units = ['Kb', 'Mb', 'Gb', 'Tb', 'Pb', 'Eb', 'Zb', 'Yb']
    var u = -1;
    do {
        bytes /= thresh;
        ++u;
    } while (Math.abs(bytes) >= thresh && u < units.length - 1);
    return bytes.toFixed(2) + ' ' + units[u];
}

function getRange(a, b, c) {
    var li = [],
        i,
        start, end, step,
        up = true;

    if (arguments.length === 1) {
        start = 0;
        end = a;
        step = 1;
    }

    if (arguments.length === 2) {
        start = a;
        end = b;
        step = 1;
    }

    if (arguments.length === 3) {
        start = a;
        end = b;
        step = c;
        if (c < 0) {
            up = false;
        }
    }

    if (up) {
        for (i = start; i < end; i += step) {
            li.push(i);
        }
    } else {
        for (i = start; i > end; i += step) {
            li.push(i);
        }
    }

    return li;
}

/**
 * @param type
 * @param title
 * @param message
 */
function showNotify(type, title, message) {
    return createNotify({
        title: title,
        message: message
    }, {
        type: type
    });
}

/**
 * @param options
 * @param settings
 * @returns {*|undefined}
 */
function createNotify(options, settings) {
    options = $.extend({}, {
        message: '...',
        title: 'Notification',
        icon: 'fa fa-info-circle'
    }, options);

    settings = $.extend({}, {
        type: 'info',
        delay: 5000,
        allow_dismiss: false,
        placement: {from: 'bottom', align: 'center'},
        animate: {enter: 'animated fadeInDown', exit: 'animated fadeOutUp'},
        template: '<div data-notify="container" class="col-xs-11 col-sm-4 alert alert-{0} alert-custom" role="alert">' +
        '  <button type="button" aria-hidden="true" class="close" data-notify="dismiss"><i class="fa fa-times alert-{0}"></i></button>' +
        '  <div>' +
        '    <div style="float:left;margin-right:10px">' +
        '      <i data-notify="icon"></i>' +
        '    </div>' +
        '    <div style="overflow:hidden">' +
        '      <p data-notify="title" style="font-weight:bold">{1}</p>' +
        '      <div data-notify="message" class="clearfix">{2}</div>' +
        '      <div class="progress" data-notify="progressbar">' +
        '        <div class="progress-bar progress-bar-{0}" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>' +
        '      </div>' +
        '    </div>' +
        '  </div>' +
        '</div>'
    }, settings);

    return $.notify(options, settings);
}

/**
 * document ready
 */
$(document).ready(function () {
    $('#show_article_list').set_search('article', '#assign_article_list');
    $('#show_manufacturer_list').set_search('manufacturer', '#assign_manufacturer_list');
    $('#show_categories_list').set_search('categories', '#assign_categories_list');
    $('.collapse').removeClass('in');

    $('.accordion-toggle').click(function () {
        var self = this;
        $(self).find('i').toggleClass('fa-minus fa-plus');
        $('.accordion-toggle').each(function () {
            if (this !== self) {
                $(this).find('i').toggleClass('fa-minus', false).toggleClass('fa-plus', true);
            }
        });
    });

    banners_datepicker();
    $('.help').each(function () {
        var id = $(this).attr('ref'),
            tooltip = $('<div></div>').text($(this).attr('title')).addClass('tooltip').attr('id', 'help' + id),
            offset;
        $('body').append(tooltip);
        $(this).attr('title', '');
        $(this).bind('mouseenter', function () {
            var help = $('#help' + id);
            offset = $(this).offset();
            help.css({
                left: offset.left - help.outerWidth() + $(this).outerWidth() + 5,
                top: offset.top - ((help.outerHeight() - $(this).outerHeight()) / 2)
            }).fadeIn(200);
        }).bind('mouseleave', function () {
            $('#help' + id).hide();
        });
    });

    $('body').tooltip({selector: '[data-toggle=tooltip]'});
    $('#user_login').focus();
    $('#check-menus').on('change', function () {
        $(this).parent().submit();
    });

    $("#subnav ul li a[href^='#']").on('click', function (e) {
        var hash = this.hash;
        e.preventDefault();
        $('html, body').animate({
            scrollTop: $(this.hash).offset().top
        }, 300, function () {
            window.location.hash = hash;
        });

    });

    $('button.blue, input[type=submit].blue').addClass('btn btn-primary');
    $('button.orange, input[type=submit].orange').addClass('btn btn-default');

    $(window).scroll(function () {
        if ($(this).scrollTop() > 100) {
            $('#scroll-top').fadeIn();
        } else {
            $('#scroll-top').fadeOut();
        }
    });
    //Click event to scroll to top
    $('#scroll-top').click(function () {
        $('html, body').animate({scrollTop: 0}, 800);
        return false;
    });
    $('.btn-tooltip').tooltip({
        container: 'body'
    });
    //open tabs if url contains corresponding hash
    if (location.hash.length > 0 && typeof jQuery.fn.tab === 'function') {
        $('body a[href="' + location.hash + '"]').tab('show');
    }
    //Checkboxen de-/aktivieren die über der Einstellung liegen und in der gleichen Klasse sind
    $(".Boxen").click(function () {
        var checkbox = $(this).parent().parent().find("input:not(.Boxen)");
        var activitem = $(this).prop("checked");
        $(checkbox).each(function (id, item) {
            $(item).prop("checked", activitem);
        });
    });
});