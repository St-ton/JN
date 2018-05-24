/**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 */

(function ($) {
    'use strict';

    if (!$.reCaptcha) {
        $.reCaptcha = {};
    }

    var reCaptchaClass = function() {};

    reCaptchaClass.prototype = {
        captcha: {},

        renderCaptcha: function(parameters) {
            var $g_reCaptcha = $('.g-recaptcha');

            if ($g_reCaptcha.length > 0) {
                if (typeof parameters !== 'undefined') {
                    this.captcha = $.extend({}, this.captcha, parameters);
                }

                if (typeof grecaptcha === 'undefined' && !this.captcha.loaded) {
                    this.captcha.loaded = true;
                    var lang            = document.documentElement.lang;
                    $.getScript("https://www.google.com/recaptcha/api.js?render=explicit&onload=g_recaptcha_callback&hl=" + lang);
                } else {
                    $g_reCaptcha.each(function (index, item) {
                        parameters = $.extend({}, $(item).data(), parameters);
                        try {
                            grecaptcha.render(item, parameters);
                        } catch (e) {
                        }
                    });
                }
                $('.g-recaptcha-response').attr('required', true);
            }
        },

        renderCaptchaPopup: function(parameters) {
            //the modal just copies all the html.. so we got duplicate IDs which confuses recaptcha
            var recaptcha = $('.tmp-modal-content .g-recaptcha');
            if (recaptcha.length === 1) {
                var siteKey      = recaptcha.data('sitekey'),
                    newRecaptcha = $('<div />');

                if (typeof siteKey !== 'undefined') {
                    //create empty recapcha div, give it a unique id and delete the old one
                    newRecaptcha.attr('id', 'popup-recaptcha').addClass('g-recaptcha form-group');
                    recaptcha.replaceWith(newRecaptcha);
                    grecaptcha.render('popup-recaptcha', {
                        'sitekey' : siteKey,
                        'callback' : 'g_recaptcha_filled'
                    });
                }
            }
            $('.g-recaptcha-response').attr('required', true);
        }
    };

    $.reCaptcha = new reCaptchaClass();

    $(document)
        .on('evo:captcha.render', function (ev, parameters) {
            $.reCaptcha.renderCaptcha(parameters);
        })
        .on('evo:captcha.render.popup', function (ev, parameters) {
            $.reCaptcha.renderCaptchaPopup(parameters);
        });
})(jQuery);

function g_recaptcha_callback() {
    $.reCaptcha.renderCaptcha();
}

function g_recaptcha_filled() {
    $('.g-recaptcha').closest('.form-group').find('div.form-error-msg').remove();
}

