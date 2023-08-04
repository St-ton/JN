var editorsSmarty = [],
    editorsSass = [],
    editorsHtml = [],
    editorsSQL = [];

function wc_hex_is_light(color) {
    const hex = color.replace('#', '');
    const c_r = parseInt(hex.substr(0, 2), 16);
    const c_g = parseInt(hex.substr(2, 2), 16);
    const c_b = parseInt(hex.substr(4, 2), 16);
    const brightness = ((c_r * 299) + (c_g * 587) + (c_b * 114)) / 1000;
    return brightness > 155;
}

$(document).ready(function () {
    var idListSmarty = $('.codemirror.smarty'),
        idListHTML = $('.codemirror.html'),
        idListSASS = $('.codemirror.sass'),
        idListSQL = $('.codemirror.sql'),
        codemirrorTheme = wc_hex_is_light($(':root').css('--body-bg')) ? 'default' : 'ayu-dark';

    idListHTML.each(function (idx, elem) {
        if (elem.id && elem.id.length > 0) {
            editorsHtml[idx] = CodeMirror.fromTextArea(document.getElementById(elem.id), {
                lineNumbers:    true,
                mode:           'htmlmixed',
                scrollbarStyle: 'simple',
                lineWrapping:   true,
                extraKeys:      {
                    'Ctrl-Space': function (cm) {
                        cm.setOption('fullScreen', !cm.getOption('fullScreen'));
                    },
                    'Esc':        function (cm) {
                        if (cm.getOption('fullScreen')) cm.setOption('fullScreen', false);
                    }
                },
                theme: codemirrorTheme
            });
        }
    });
    idListSASS.each(function (idx, elem) {
        if (elem.id && elem.id.length > 0) {
            editorsSass[idx] = CodeMirror.fromTextArea(document.getElementById(elem.id), {
                lineNumbers:    true,
                mode:           'sass',
                scrollbarStyle: 'simple',
                lineWrapping:   true,
                extraKeys:      {
                    'Ctrl-Space': function (cm) {
                        cm.setOption('fullScreen', !cm.getOption('fullScreen'));
                    },
                    'Esc':        function (cm) {
                        if (cm.getOption('fullScreen')) cm.setOption('fullScreen', false);
                    }
                },
                theme: codemirrorTheme
            });
        }
    });
    idListSmarty.each(function (idx, elem) {
        if (elem.id && elem.id.length > 0) {
            editorsSmarty[idx] = CodeMirror.fromTextArea(document.getElementById(elem.id), {
                lineNumbers:    true,
                lineWrapping:   true,
                mode:           'smartymixed',
                scrollbarStyle: 'simple',
                extraKeys:      {
                    'Ctrl-Space': function (cm) {
                        cm.setOption('fullScreen', !cm.getOption('fullScreen'));
                    },
                    'Esc':        function (cm) {
                        if (cm.getOption('fullScreen')) cm.setOption('fullScreen', false);
                    }
                },
                theme: codemirrorTheme
            });
        }
    });
    idListSQL.each(function (idx, elem) {
        if (elem.id && elem.id.length > 0) {
            var hint = $('#' + elem.id).data('hint');
            editorsSQL[idx] = CodeMirror.fromTextArea(document.getElementById(elem.id), {
                mode: 'text/x-mysql',
                scrollbarStyle: 'simple',
                lineWrapping:   true,
                smartIndent: true,
                lineNumbers: true,
                matchBrackets : true,
                autofocus: true,
                extraKeys: {"Ctrl-Space": "autocomplete"},
                hintOptions: hint,
                theme: codemirrorTheme
            });
        }
    });
});
