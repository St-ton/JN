<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>JTL Live-Editor</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css">

    <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

    <link rel="stylesheet" href="templates/bootstrap/css/jtl-live-editor/jle-host.css">
    <script src="templates/bootstrap/js/jtl-live-editor/jle-host.js"></script>
</head>
<body>
    <div id="sidebar-panel">
        <button draggable="true" data-content="<h1>Heading</h1>">
            Heading
        </button>
        <button draggable="true" data-content="<p>Paragraph</p>">
            Paragraph
        </button>
    </div>
    <div id="iframe-panel">
        <iframe src="..">
    </div>
</body>
</html>