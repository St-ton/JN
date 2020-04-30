'use strict';

$('body').on('click', '.option li', function (e) {
    var i = $(this).parents('.select').attr('id'),
        v = $(this).children().text(),
        o = $(this).attr('id');
    $('#' + i + ' .selected').attr('id', o).text(v);
});

// prevent multiple form submit on client side
$('.submit_once').closest('form').on('submit', function() {
    $(this).on('submit', function() {
        return false;
    });
    return true;
});

/**
 *  Format file size
 */
function formatSize(size) {
    var fileSize = Math.round(size / 1024),
        suffix = 'KB',
        fileSizeParts;

    if (fileSize > 1000) {
        fileSize = Math.round(fileSize / 1000);
        suffix = 'MB';
    }

    fileSizeParts = fileSize.toString().split('.');
    fileSize = fileSizeParts[0];

    if (fileSizeParts.length > 1) {
        fileSize += '.' + fileSizeParts[1].substr(0, 2);
    }
    fileSize += suffix;

    return fileSize;
}

function getCategoryMenu(categoryId, success) {
    var xx = {};
    var io = $.evo.io();

    io.call('getCategoryMenu', [categoryId], xx, function (error, data) {
        if (error) {
            console.error(data);
        } else if (typeof success === 'function') {
            success(xx.response);
        }
    });

    return true;
}

function categoryMenu(rootcategory) {
    if (typeof rootcategory === 'undefined') {
        rootcategory = $('.sidebar-offcanvas .navbar-categories').html();
    }

    $('.sidebar-offcanvas li a.nav-sub').on('click', function(e) {
        var navbar = $('.sidebar-offcanvas .navbar-categories'),
            ref = $(this).data('ref');

        if (ref === 0) {
            $(navbar).html(rootcategory);
            categoryMenu(rootcategory);
        }
        else {
            getCategoryMenu(ref, function(data) {
                $(navbar).html(data);
                categoryMenu(rootcategory);
            });
        }

        return false;
    });
}

function compatibility() {
    var __enforceFocus = $.fn.modal.Constructor.prototype.enforceFocus;
    $.fn.modal.Constructor.prototype.enforceFocus = function () {
        if ($('.modal-body .g-recaptcha').length === 0) {
            __enforceFocus.apply(this, arguments);
        }
    };
}

function regionsToState() {
    var state = $('#state');
    if (state.length === 0) {
        return;
    }
    var title = state.attr('title');
    var stateIsRequired = state.attr('required') === 'required';

    $('#country').on('change', function() {
        var result = {};
        var io = $.evo.io();
        var val = $(this).find(':selected').val();

        io.call('getRegionsByCountry', [val], result, function (error, data) {
            if (error) {
                console.error(data);
            } else {
                var data = result.response;
                var def = $('#state').val();
                if (data !== null && data.length > 0) {
                    if (stateIsRequired){
                        var state = $('<select />').attr({ id: 'state', name: 'bundesland', class: 'custom-select required form-control', required: 'required'});
                    } else {
                        var state = $('<select />').attr({ id: 'state', name: 'bundesland', class: 'custom-select form-control'});
                    }

                    state.append('<option value="">' + title + '</option>');
                    $(data).each(function(idx, item) {
                        state.append(
                            $('<option></option>').val(item.cCode).html(item.cName)
                                .attr('selected', item.cCode == def || item.cName == def ? 'selected' : false)
                        );
                    });
                    $('#state').replaceWith(state);
                } else {
                    if (stateIsRequired) {
                        var state = $('<input />').attr({ type: 'text', id: 'state', name: 'bundesland', class: 'required form-control', placeholder: title, required: 'required' });
                    } else {
                        var state = $('<input />').attr({ type: 'text', id: 'state', name: 'bundesland', class: 'form-control', placeholder: title });
                    }
                    $('#state').replaceWith(state);
                }
            }
        });
        return false;

    }).trigger('change');
}

function loadContent(url)
{
    $.evo.extended().loadContent(url, function() {
        $.evo.extended().register();

        if (typeof $.evo.article === 'function') {
            $.evo.article().onLoad();
            $.evo.article().register();
            addValidationListener();
        }

        $('html,body').animate({
            scrollTop: $('.list-pageinfo').offset().top - $('#main-nav-wrapper').outerHeight() - 10
        }, 100);
    });
}

function sanitizeOutput(val) {
    return val.replace(/\&/g, '&amp;')
        .replace(/\</g, '&lt;')
        .replace(/\>/g, '&gt;')
        .replace(/\"/g, '&quot;')
        .replace(/\'/g, '&#x27;')
        .replace(/\//g, '&#x2F;');
}

function addValidationListener() {
    var forms      = $('form.jtl-validate'),
        inputs     = $('form.jtl-validate input, form.jtl-validate textarea').not('[type="radio"],[type="checkbox"]'),
        selects    = $('form.jtl-validate select'),
        checkables = $('form.jtl-validate input[type="radio"], form.jtl-validate input[type="checkbox"]'),
        $body      = $('body');

    for (var i = 0; i < forms.length; i++) {
        forms[i].addEventListener('invalid', function (event) {
            event.preventDefault();
            $(event.target).closest('.form-group').find('div.form-error-msg').remove();
            $(event.target).closest('.form-group')
                .addClass('has-error')
                .append('<div class="form-error-msg text-danger w-100">' + sanitizeOutput(event.target.validationMessage) + '</div>');

            if (!$body.data('doScrolling')) {
                var $firstError = $(event.target).closest('.form-group.has-error');
                if ($firstError.length > 0) {
                    $body.data('doScrolling', true);
                    var $nav        = $('#jtl-nav-wrapper.sticky-top'),
                        fixedOffset = $nav.length > 0 ? $nav.outerHeight() : 0,
                        vpHeight    = $(window).height(),
                        scrollTop   = $(window).scrollTop();
                    if ($firstError.offset().top > (scrollTop + vpHeight) || $firstError.offset().top < scrollTop) {
                        $('html, body').animate(
                            {
                                scrollTop: $firstError.offset().top - fixedOffset - parseInt($firstError.css('margin-top'))
                            },
                            {
                                done: function () {
                                    $body.data('doScrolling', false);
                                }
                            }, 300
                        );
                    }
                }
            }
        }, true);
    }

    for (var i = 0; i < inputs.length; i++) {
        inputs[i].addEventListener('blur', function (event) {
            checkInputError(event);
        }, true);
    }
    for (var i = 0; i < checkables.length; i++) {
        checkables[i].addEventListener('click', function (event) {
            checkInputError(event);
        }, true);
    }
    for (var i = 0; i < selects.length; i++) {
        selects[i].addEventListener('change', function (event) {
            checkInputError(event);
        }, true);
    }
}

function checkInputError(event)
{
    var $target = $(event.target);
    if ($target.parents('.cfg-group') != undefined) {
        $target.parents('.cfg-group').find('div.form-error-msg').remove();
    }
    $target.parents('.form-group').find('div.form-error-msg').remove();

    if ($target.data('must-equal-to') !== undefined) {
        var $equalsTo = $($target.data('must-equal-to'));
        if ($equalsTo.length === 1) {
            var theOther = $equalsTo[0];
            if (theOther.value !== '' && theOther.value !== event.target.value && event.target.value !== '') {
                event.target.setCustomValidity($target.data('custom-message') !== undefined ? $target.data('custom-message') : sanitizeOutput(event.target.validationMessage));
            } else {
                event.target.setCustomValidity('');
            }
        }
    }

    if (event.target.validity.valid) {
        $target.closest('.form-group').removeClass('has-error');
    } else {
        $target.closest('.form-group').addClass('has-error').append('<div class="form-error-msg text-danger">' + sanitizeOutput(event.target.validationMessage) + '</div>');
    }
}

function captcha_filled() {
    $('.g-recaptcha').closest('.form-group').find('div.form-error-msg').remove();
}

function isTouchCapable() {
    return 'ontouchstart' in window || (window.DocumentTouch && document instanceof window.DocumentTouch);
}

function addCopyToClipboardListener() {
    var clipboard = new ClipboardJS('.btn.copyToClipboard');

    clipboard.on('success', function(e) {
        $(e.trigger).tooltip({title: 'copied'});
        e.clearSelection();
    });

    clipboard.on('error', function(e) {
        console.error('Action:', e.action);
        console.error('Trigger:', e.trigger);
    });
}

function initWow()
{
    new WOW().init();
}
/*
$(window).load(function(){
    navigation();
});*/

$(document).ready(function () {
    $('.collapse-non-validate')
        .on('hidden.bs.collapse', function(e) {
            $(e.target)
                .addClass('hidden')
                .find('fieldset, .form-control')
                .attr('disabled', true);
            e.stopPropagation();
        })
        .on('show.bs.collapse', function(e) {
            $(e.target)
                .removeClass('hidden')
                .attr('disabled', false);
            e.stopPropagation();
        }).on('shown.bs.collapse', function(e) {
            $(e.target)
                .find('fieldset, .form-control')
                .filter(function (i, e) {
                    return $(e).closest('.collapse-non-validate.collapse').hasClass('show');
                })
                .attr('disabled', false);
            e.stopPropagation();
        });
    $('.collapse-non-validate.collapse.show')
        .removeClass('hidden')
        .find('fieldset, .form-control')
        .attr('disabled', false);
    $('.collapse-non-validate.collapse:not(.show)')
        .addClass('hidden')
        .find('fieldset, .form-control')
        .attr('disabled', true);

    $('#complete-order-button').on('click', function () {
        var commentField = $('#comment'),
            commentFieldHidden = $('#comment-hidden');
        if (commentField && commentFieldHidden) {
            commentFieldHidden.val(commentField.val());
        }
    });

    $(document).on('click', '.footnote-vat a, .versand, .popup', function(e) {
        var url = e.currentTarget.href;
        url += (url.indexOf('?') === -1) ? '?isAjax=true' : '&isAjax=true';
        eModal.ajax({
            size: 'xl',
            url: url,
            title: typeof e.currentTarget.title !== 'undefined' ? e.currentTarget.title : '',
            keyboard: true,
            tabindex: -1,
            buttons: false
        });
        e.stopPropagation();
        return false;
    });

    $(document).on('click', '.pagination-ajax a:not(.active), .js-pagination-ajax:not(.active)', function(e) {
        var url = $(this).attr('href');
        history.pushState(null, null, url);
        loadContent(url);
        return e.preventDefault();
    });

    if ($('.js-pagination-ajax').length > 0) {
        window.addEventListener('popstate', function(e) {
            loadContent(document.location.href);
        }, false);
    }

    $('.dropdown .dropdown-menu.keepopen').on('click touchstart', function(e) {
        e.stopPropagation();
    },{passive: true});

    if (typeof $.fn.jtl_search === 'undefined') {
        var productSearch = new Bloodhound({
            datumTokenizer: Bloodhound.tokenizers.obj.whitespace('keyword'),
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            remote:         {
                url:      'io.php?io={"name":"suggestions", "params":["%QUERY"]}',
                wildcard: '%QUERY'
            }
        });

        let $searchInput = $('input[name="qs"]');
        $searchInput.typeahead(
            {
                highlight: true
            },
            {
                name:      'product-search',
                display:   'keyword',
                source:    productSearch,
                templates: {
                    suggestion: function (e) {
                        return e.suggestion;
                    }
                }
            }
        );
        $searchInput.on('keydown keyup blur', function () {
            if ($(this).val().length === 0) {
                $(this).closest('form').find('.form-clear').addClass('d-none');
            } else {
                $(this).closest('form').find('.form-clear').removeClass('d-none');
            }
        });
        $('.form-clear').on('click', function() {
            $searchInput.typeahead('val', '');
            $(this).addClass('d-none');
        });
    }

    var citySuggestion = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('keyword'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote:         {
            url:      'io.php?io={"name":"getCitiesByZip", "params":["%QUERY", "' + $(this).closest('fieldset').find('.country-input').val() + '", "' + $(this).closest('fieldset').find('.postcode_input').val() + '"]}',
            wildcard: '%QUERY'
        },
        dataType: "json"
    });
    $('.city_input').on('focusin', function () {
        citySuggestion.remote.url = 'io.php?io={"name":"getCitiesByZip", "params":["%QUERY", "' + $(this).closest('fieldset').find('.country-input').val() + '", "' + $(this).closest('fieldset').find('.postcode_input').val() + '"]}';
    });
    $('.postcode_input').on('change', function () {
        citySuggestion.remote.url = 'io.php?io={"name":"getCitiesByZip", "params":["%QUERY", "' + $(this).closest('fieldset').find('.country-input').val() + '", "' + $(this).val() + '"]}';
    });
    $('.country_input').on('change', function () {
        citySuggestion.remote.url = 'io.php?io={"name":"getCitiesByZip", "params":["%QUERY", "' + $(this).val() + '", "' + $(this).closest('fieldset').find('.postcode_input').val() + '"]}';
    });

    $('.city_input').typeahead(
        {
            hint: true,
            minLength: 0
        },
        {
            name:   'cities',
            source: citySuggestion
        }
    );

    $('.btn-offcanvas').on('click', function() {
        $('body').click();
    });

    if ("ontouchstart" in document.documentElement) {
        $('.variations .swatches .variation').on('mouseover', function() {
            $(this).trigger('click');
        });
    }

    /*
     * show subcategory on caret click
     */
    $('section.box-categories .nav-panel li a').on('click', function(e) {
        if ($(e.target).hasClass("nav-toggle")) {
            $(e.delegateTarget)
                .parent('li')
                .find('> ul.nav').toggle();
            return false;
        }
    });

    /*
     * show linkgroup on caret click
     */
    $('section.box-linkgroup .nav-panel li a').on('click', function(e) {
        if ($(e.target).hasClass("nav-toggle")) {
            $(e.delegateTarget)
                .parent('li')
                .find('> ul.nav').toggle();
            return false;
        }
    });

    /*
     * Banner
     */
    var bannerLink = $('.banner > a');
    bannerLink.popover({
        html:      true,
        placement: 'bottom',
        trigger:   'hover',
        container: 'body',
        sanitize: false,
        template:  	'<div class="popover popover-min-width" role="tooltip"><div class="arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>',
        content:   function () {
            return $(this).children('.area-desc').html()
        }
    });

    bannerLink.on('mouseenter', function () {
        $(this).animate({
            borderWidth: 2,
            opacity:     0.5
        }, 300);
    });

    bannerLink.on('mouseleave', function () {
        $(this).animate({
            borderWidth: 0,
            opacity:     1
        }, 300);
    });

    $('.banner').on('mouseenter', function () {
        $(this).children('a').animate({
            borderWidth: 8,
            opacity:     0
        }, 900, function () {
            $(this).css({opacity: 1, borderWidth: 0});
        });
    });

    $('.banner > a[href=""]').on('click', function () {
        return false;
    });

    /*
     * alert actions
     */
    $('.alert .close').on('click', function (){
        $(this).parent().fadeOut(1000);
    });

    $('.alert').each(function(){
        if ($(this).data('fade-out') > 0) {
            $(this).fadeOut($(this).data('fade-out'));
        }
    });


    /*
     * set bootstrap viewport
     */
    (function($, document, window, viewport){
        var $body = $('body');

        $(window).on('resize',
            viewport.changed(function() {
                $body.attr('data-viewport', viewport.current());
            })
        );
        $body.attr('data-viewport', viewport.current());
        $body.attr('data-touchcapable', isTouchCapable() ? 'true' : 'false');
    })(jQuery, document, window, ResponsiveBootstrapToolkit);


    $('.onchangeSubmit').on('change', function(){
        this.form.submit();
    });
    categoryMenu();
    regionsToState();
    compatibility();
    addValidationListener();
    addCopyToClipboardListener();
    initWow();
    setClickableRow();

    document.addEventListener('lazybeforesizes', function(e){
        //use width of parent node instead of the image width itself
        var parent = e.target.parentNode;

        if(parent.nodeName == 'PICTURE'){
            parent = parent.parentNode;
        }
        e.detail.width = parent.offsetWidth || e.detail.width;
    });
});

function setClickableRow ()
{
    $('.clickable-row').on('click', function() {
        window.location = $(this).data('href');
    });
}
