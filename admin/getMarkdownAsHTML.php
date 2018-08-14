<?php
/**
 * This file is only intended to deliver HTML,
 * read from a Markdown-file,
 * via the jquery-function .load().
 *
 * Parameters are:
 * ('jtl_token': '', 'path': '')
 *
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->redirectOnFailure();

if (isset($_POST['path']) && FormHelper::validateToken()) {
    $path = realpath($_POST['path']);
    if ($path !== false && strpos($path . '/', PFAD_ROOT . PFAD_PLUGIN) === 0) {
        $info = pathinfo($path);
        if (strtolower($info['extension']) === 'md') {
            $oParseDown       = new Parsedown();
            $szLicenseContent = mb_convert_encoding(
                $oParseDown->text(StringHandler::convertUTF8(file_get_contents($path))),
                'HTML-ENTITIES'
            );
            echo '<div class="markdown">' . $szLicenseContent . '</div>';
        }
    }
}
