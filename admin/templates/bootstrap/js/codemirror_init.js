var editorsSmarty = [],
	editorsHtml = [];
$(document).ready(function () {
	var idListSmarty = $('.codemirror.smarty'),
		idListHTML = $('.codemirror.html');
	idListHTML.each(function (idx, elem) {
		if (elem.id && elem.id.length > 0) {
			editorsSmarty[idx] = CodeMirror.fromTextArea(document.getElementById(elem.id), {
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
});