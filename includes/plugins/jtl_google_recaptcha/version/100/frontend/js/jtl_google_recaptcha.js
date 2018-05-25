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
        captcha: {
            loaded: false,
            size: 'checkbox',
            widget: ''
        },

        renderCaptcha: function(parameters) {
            var $g_reCaptcha = $('.g-recaptcha');

            if ($g_reCaptcha.length > 0) {
                var that = this;

                if (typeof parameters !== 'undefined') {
                    this.captcha = $.extend({}, this.captcha, $g_reCaptcha.data(), parameters);
                } else {
                    this.captcha = $.extend({}, this.captcha, $g_reCaptcha.data());
                }

                if (typeof grecaptcha === 'undefined' && !this.captcha.loaded) {
                    this.captcha.loaded = true;
                    var lang            = document.documentElement.lang;
                    $.getScript("https://www.google.com/recaptcha/api.js?render=explicit&onload=g_recaptcha_callback&hl=" + lang);
                } else {
                    $g_reCaptcha.each(function (index, item) {
                        parameters = $.extend({}, $(item).data(), parameters);
                        try {
                            that.captcha.widget = grecaptcha.render(item, parameters);
                        } catch (e) {
                        }
                    });
                }
                if (this.captcha.size !== 'invisible') {
                    $('.g-recaptcha-response').attr('required', true);
                }
            }
        },

        renderCaptchaPopup: function(parameters) {
            //the modal just copies all the html.. so we got duplicate IDs which confuses recaptcha
            var $recaptcha = $('.tmp-modal-content .g-recaptcha');
            if ($recaptcha.length === 1) {
                if (typeof grecaptcha === 'undefined' && !this.captcha.loaded) {
                    this.captcha.loaded = true;
                    var lang            = document.documentElement.lang;
                    $.getScript("https://www.google.com/recaptcha/api.js?render=explicit&onload=g_recaptcha_popup_callback&hl=" + lang);
                } else if (typeof grecaptcha !== 'undefined') {
                    var siteKey       = $recaptcha.data('sitekey'),
                        $newRecaptcha = this.captcha.size === 'invisible' ? $('<div data-size="invisible" data-badge="inline" />') : $('<div />');

                    if (typeof siteKey !== 'undefined') {
                        //create empty recapcha div, give it a unique id and delete the old one
                        $newRecaptcha.attr('id', 'popup-recaptcha').addClass('g-recaptcha form-group');
                        $recaptcha.replaceWith($newRecaptcha);
                        this.captcha.widget = grecaptcha.render('popup-recaptcha', {
                            'sitekey': siteKey,
                            'callback': 'g_recaptcha_filled'
                        });

                        $newRecaptcha.closest('form').on('submit', function (ev) {
                            $.reCaptcha.submitForm(ev);
                        });
                    }
                }
            }
            if (this.captcha.size !== 'invisible') {
                $('.g-recaptcha-response').attr('required', true);
            }
        },

        submitForm: function(ev) {
            if (typeof grecaptcha !== 'undefined' && this.captcha.size === 'invisible' && !$('.g-recaptcha-response').val()) {
                ev.preventDefault();
                this.captcha.submitted = ev.target;
                grecaptcha.execute(this.captcha.widget);
            }
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
    $('.g-recaptcha').closest('form').on('submit', function (ev) {
        $.reCaptcha.submitForm(ev);
    });
})(jQuery);

function g_recaptcha_callback() {
    $.reCaptcha.renderCaptcha();
}

function g_recaptcha_popup_callback() {
    $.reCaptcha.renderCaptchaPopup();
}

function g_recaptcha_filled(token) {
    $('.g-recaptcha').closest('.form-group').find('div.form-error-msg').remove();
    $('.g-recaptcha-response').val(token);
    if (typeof $.reCaptcha.captcha.submitted !== 'undefined') {
        $.reCaptcha.captcha.submitted.submit();
    }
}

