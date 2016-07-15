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

/**
 * @param string $class
 * @return bool
 */
function ShopAutoload($class)
{
    $classPaths = array(
        PFAD_ROOT . PFAD_CLASSES,
        PFAD_ROOT . PFAD_ADMIN . PFAD_CLASSES,
        PFAD_ROOT . PFAD_INCLUDES_EXT,
        PFAD_ROOT . PFAD_CLASSES_CORE
    );

    foreach ($classPaths as $classPath) {
        $fileName = $classPath . 'class.JTL-Shop.' . $class . '.php';
        if (file_exists($fileName)) {
            require $fileName;

            return true;
        }
        $fileName = $classPath . 'class.JTL-Shopadmin.' . $class . '.php';
        if (file_exists($fileName)) {
            require $fileName;

            return true;
        }
        if (strpos($class, 'Helper') !== false) {
            $fileName = $classPath . 'class.helper.' . ucfirst(str_replace('Helper', '', $class)) . '.php';
            if (file_exists($fileName)) {
                require $fileName;

                return true;
            }
        }
        if ($class[0] === 'I') {
            $fileName = $classPath . 'interface.JTL-Shop.' . $class . '.php';
            if (file_exists($fileName)) {
                require $fileName;

                return true;
            }
        } else {
            $fileName = $classPath . 'class.core.' . $class . '.php';
            if (file_exists($fileName)) {
                require $fileName;

                return true;
            }
        }
    }

    return false;
}

if (function_exists('spl_autoload_functions') && !spl_autoload_functions() || !in_array('ShopAutoload', spl_autoload_functions())) {
    spl_autoload_extensions('.php');
    spl_autoload_register('ShopAutoload');
}
