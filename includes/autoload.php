<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
if (!file_exists(PFAD_ROOT . PFAD_INCLUDES . 'vendor/autoload.php')) {
    header('Content-type: text/html', true, 503);
    echo 'Use "composer install" to install the required dependencies';
    exit;
}

require PFAD_ROOT . PFAD_INCLUDES . 'vendor/autoload.php';
    if ($class === 'PluginLizenz') {
        require PFAD_ROOT . PFAD_CLASSES . 'interface.JTL-Shop.PluginLizenz.php';
        return true;
    }