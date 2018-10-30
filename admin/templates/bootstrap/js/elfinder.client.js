/**
 * elFinder client options and main script for RequireJS
 *
 * Rename "main.default.js" to "main.js" and edit it if you need configure elFInder options or any things. And use that
 * in elfinder.html.
 * e.g. `<script data-main="./main.js" src="./require.js"></script>`
 **/
(function(){
	"use strict";

	var lang = (function() {
        var locq = window.location.search,
            fullLang, locm, lang;

        if (locq && (locm = locq.match(/lang=([a-zA-Z_-]+)/))) {
            // detection by url query (?lang=xx)
            fullLang = locm[1];
        } else {
            // detection by browser language
            fullLang = (navigator.browserLanguage || navigator.language || navigator.userLanguage);
        }

        lang = fullLang.substr(0, 2);

        if (lang === 'pt')
            lang = 'pt_BR';
        else if (lang === 'ug')
            lang = 'ug_CN';
        else if (lang === 'zh')
            lang = (fullLang.substr(0,5).toLowerCase() === 'zh-tw') ? 'zh_TW' : 'zh_CN';

        return lang;
    })();
		
    // Start elFinder (REQUIRED)
    function start(elFinder, editors, config)
    {
        // load jQueryUI CSS
        elFinder.prototype.loadCss('templates/bootstrap/css/jquery-ui.min.css');
        elFinder.prototype.loadCss('templates/bootstrap/css/jquery-ui.theme.min.css');

        $(function() {
            var optEditors = {
                    commandsOptions: {
                        edit: {
                            editors: Array.isArray(editors)? editors : []
                        }
                    }
                },
                opts = {};

            // Interpretation of "elFinderConfig"
            if (config && config.managers) {
                $.each(config.managers, function(id, mOpts) {
                    opts = Object.assign(opts, config.defaultOpts || {});
                    // editors marges to opts.commandOptions.edit
                    try {
                        mOpts.commandsOptions.edit.editors = mOpts.commandsOptions.edit.editors.concat(editors || []);
                    } catch(e) {
                        Object.assign(mOpts, optEditors);
                    }
                    // Make elFinder
                    $('#' + id).elfinder(
                        // 1st Arg - options
                        $.extend(true, { lang: lang }, opts, mOpts || {}),
                        // 2nd Arg - before boot up function
                        function(fm, extraObj) {
                            // `init` event callback function
                            fm.bind('init', function() {
                                // Optional for Japanese decoder "encoding-japanese"
                                if (fm.lang === 'ja') {
                                    require(
                                        [ 'encoding-japanese' ],
                                        function(Encoding) {
                                            if (Encoding && Encoding.convert) {
                                                fm.registRawStringDecoder(function(s) {
                                                    return Encoding.convert(s, {to:'UNICODE',type:'string'});
                                                });
                                            }
                                        }
                                    );
                                }
                            });
                        }
                    );
                });
            } else {
                throw '"elFinderConfig" object is wrong.';
            }
        });
    }

    // JavaScript loader (REQUIRED)
    function load()
    {
        require(
            [
                'elfinder',
                // load text, image editors
                'includes/vendor/studio-42/elfinder/js/extras/editors.default',
                'elFinderConfig',
            ],
            start,
            function(error) {
                throw error;
            }
        );
    }

	// config of RequireJS (REQUIRED)
	require.config({
        baseUrl : '..',
		paths : {
		    'jquery':    'admin/templates/bootstrap/js/jquery-2.2.4.min',
            'jquery-ui': 'admin/templates/bootstrap/js/jquery-ui.min',
            'elfinder':  'includes/vendor/studio-42/elfinder/js/elfinder.full',
		},
		waitSeconds : 10 // optional
	});

	// load JavaScripts (REQUIRED)
	load();
})();
