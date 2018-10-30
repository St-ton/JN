<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=2">
		<title>elFinder 2.1.x source version with PHP connector</title>
        <style>
            body, html {
                margin: 0;
                padding: 0;
                height: 100%;
                overflow: hidden;
            }
        </style>
		<script data-main="templates/bootstrap/js/elfinder.client.js"
                src="templates/bootstrap/js/require.js"></script>
		<script>
			define('elFinderConfig', {
				// elFinder options (REQUIRED)
				// Documentation for client options:
				// https://github.com/Studio-42/elFinder/wiki/Client-configuration-options
				defaultOpts : {
                    // connector URL (REQUIRED)
					url : 'elfinder.php',
                    defaultView: 'icons',
                    customData: {
                        token: '{$smarty.session.jtl_token}',
                        jtl_token: '{$smarty.session.jtl_token}',
                        mediafilesType: '{$mediafilesType}',
                    },
					commandsOptions : {
					    upload: {
					        ui: 'uploadbutton',
                        },
					},
                    uiOptions: {
					    toolbar: [
					        ['info', 'quicklook', 'upload'],
                            ['rm', 'duplicate', 'rename'],
                            ['view'],
                            ['help']
                        ],
                    },
                    contextmenu: {
                        navbar: [],
                        cwd: [
                            'reload', 'back', '|', 'upload', 'paste', '|', 'info'
                        ],
                        files: [
                            'getfile', 'quicklook', '|', 'download', '|', 'duplicate', 'rm', 'rename', '|', 'info'
                        ],
                    },
					// bootCalback calls at before elFinder boot up 
					bootCallback : function(fm, extraObj) {
						/* any bind functions etc. */
						fm.bind('init', function() {
							// any your code
						});
						// for example set document.title dynamically.
						var title = document.title;
						fm.bind('open', function() {
							var path = '',
								cwd  = fm.cwd();
							if (cwd) {
								path = fm.path(cwd.hash) || null;
							}
							document.title = path ? path + ':' + title : title;
						}).bind('destroy', function() {
							document.title = title;
						});
					},
				},
				managers : {
					// 'DOM Element ID': { /* elFinder options of this DOM Element */ }
					elfinder: {
                        getFileCallback: function(file, fm) {
                            window.opener.elfinder.getFileCallback(file.url);
                            window.close();
                        },
                        height: '100%',
                        resizable: false,
                    },
				},
			});
		</script>
	</head>
	<body>
		<!-- Element where elFinder will be created (REQUIRED) -->
		<div id="elfinder"></div>
	</body>
</html>
