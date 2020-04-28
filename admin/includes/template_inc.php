<?php

use JTL\Shop;
use JTL\Template;

/**
 * @param string $dir
 * @param string $type
 * @return bool
 * @deprecated since 5.0.0
 */
function __switchTemplate(string $dir, string $type = 'standard')
{
    return Template::getInstance()->setTemplate($dir, $type);
}
