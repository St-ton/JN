var editorsSmarty = [],
    editorsSass = [],
    editorsHtml = [],
    editorsSQL = [];
$(document).ready(function () {
    var idListSmarty = $('.codemirror.smarty'),
        idListHTML = $('.codemirror.html'),
        idListSASS = $('.codemirror.sass'),
        idListSQL = $('.codemirror.sql');
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
                }
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
                }
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
                }
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
                hintOptions: hint
            });
        }
    });
});
