<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

use JTL\Shop;
use JTL\Template;

/**
 * @param string $dir
 * @param string $type
 * @return bool
 */
function __switchTemplate(string $dir, string $type = 'standard')
{
    $dir      = Shop::Container()->getDB()->escape($dir);
    $template = Template::getInstance();
    $check    = $template->setTemplate($dir, $type);
    if ($check) {
        unset($_SESSION['cTemplate'], $_SESSION['template']);
    }

    return $check;
}
