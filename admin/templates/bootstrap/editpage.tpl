{assign var='templateUrl' value="{$URL_SHOP}/{$PFAD_ADMIN}{$currentTemplateDir}"}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>JTL Live-Editor</title>

    <link rel="stylesheet" href="{$templateUrl}css/bootstrap.min.css">
    <link rel="stylesheet" href="{$templateUrl}css/bootstrap-theme.min.css">

    <script src="{$templateUrl}js/jquery-1.12.4.min.js"></script>
    <script src="{$templateUrl}js/bootstrap.min.js"></script>

    <link rel="stylesheet" href="{$templateUrl}css/jtl-live-editor/jle-host.css">
    <script src="{$templateUrl}js/jtl-live-editor/jle-host.js"></script>

    <script src="https://unpkg.com/split.js/split.min.js"></script>

    <script>
        $(function () {
            jleHost = new JLEHost('#right-panel iframe', '{$templateUrl}');
            Split(
                ['#sidebar-panel', '#right-panel'],
                {
                    sizes: [25, 75],
                    gutterSize: 4
                }
            );
        });
    </script>
</head>
<body>
    <div id="sidebar-panel">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">Standard Blocks</h3>
            </div>
            <div class="panel-body">
                <div class="list-group">
                    <a href="#" class="portlet-button list-group-item" data-content="<h1>Heading</h1>">
                        <h1>Heading 1</h1>
                    </a>
                    <a href="#" class="portlet-button list-group-item" data-content="<h2>Heading</h2>">
                        <h2>Heading 2</h2>
                    </a>
                    <a href="#" class="portlet-button list-group-item" data-content="<h3>Heading</h3>">
                        <h3>Heading 3</h3>
                    </a>
                    <a href="#" class="portlet-button list-group-item" data-content="<p>Paragraph</p>">
                        <p>Paragraph</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div id="right-panel">
        <iframe src="{URL_SHOP}"></iframe>
    </div>
</body>
</html>