/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

(function () {
    'use strict';

    var IOClass = function (options) {
        this.init(options);
    };

    IOClass.DEFAULTS = {
        baseUrl: '',
        ioUrl: ''
    };

    IOClass.prototype = {

        constructor: IOClass,

        init: function (options) {
            this.options = $.extend({}, IOClass.DEFAULTS, options);
        },

        call: function (name, params, context, callback) {
            var data = {'name': name, 'params': params};
            this.request(data, context, callback);
        },

        request: function (req, context, callback) {
            var that = this;

            $.evo.trigger('load.io.request', { req: req });

            return $.ajax({
                type: "POST",
                dataType: "json",
                url: this.getBaseURL() + this.options.ioUrl,
                data: {'io': JSON.stringify(req)},
                success: function (data, textStatus, jqXHR) {
                    that.handleResponse(data, context);
                    if (typeof callback === 'function') {
                        callback(false, context);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    that.handleError(textStatus, errorThrown);
                    if (typeof callback === 'function') {
                        callback(true, textStatus);
                    }
                },
                complete: function(jqXHR, textStatus) {
                    $.evo.trigger('loaded.io.request', {
                        req: req,
                        status: textStatus
                    });
                }
            });
        },

        handleResponse: function (data, context) {
            var jslist = data.js || [];
            var csslist = data.css || [];

            for (var i = 0; i < csslist.length; i++) {
                var item = csslist[i];
                var value = item.data.replace(/'/g, "\\'").replace(/\n/g, "\\n");
                var js = "if ($('#" + item.target + "').length > 0) $('#" + item.target + "')[0]['" + item.attr + "'] = '" + value + "';";
                jslist.push(js);
            }

            if (!context) {
                context = this;
            }

            context.delegate = function () {
                var calllist = jslist.join('');
                eval(calllist);
            };

            context.delegate();
        },

        handleError: function (textStatus, errorThrown) {
            $.evo.error('handleError', textStatus, errorThrown);
        },

        getFormValues: function (parent) {
            return $('#' + parent).serializeObject();
        },

        getBaseURL: function () {
            var url = location.href;  // entire url including querystring - also: window.location.href;
            var baseURL = url.substring(0, url.indexOf('/', 14));

            if (baseURL.indexOf('http://localhost') != -1) {
                // Base Url for localhost
                var url = location.href;  // window.location.href;
                var pathname = location.pathname;  // window.location.pathname;
                var index1 = url.indexOf(pathname);
                var index2 = url.indexOf("/", index1 + 1);
                var baseLocalUrl = url.substr(0, index2);

                return baseLocalUrl + "/";
            }
            else {
                // Root Url for domain name
                return baseURL + "/";
            }

        }
    };

    // PLUGIN DEFINITION
    // =================

    $.evo.io = function() {
        return new IOClass({
            'ioUrl': 'io.php'
        });
    };
})(jQuery);