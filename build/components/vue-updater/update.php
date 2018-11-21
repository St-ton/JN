<?php
require_once __DIR__ . '/vendor/autoload.php';

if (isset($_GET['task'])) {
    $posts          = null;
    $fileGetContent = json_decode(file_get_contents( 'php://input' ), true);

    if (!empty($_POST)) {
        $posts = $_POST;
    } elseif (!empty($fileGetContent)) {
        $posts = $fileGetContent;
    }
    require_once __DIR__ . '/../admin/includes/admininclude.php';

    (new VueUpdater($_GET['task'], $posts))->run();
}