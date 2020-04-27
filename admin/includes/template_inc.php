<?php

use JTL\Shop;
use JTL\Template;

/**
 * @param string $dir
 * @param string $type
 * @return bool
 */
function __switchTemplate(string $dir, string $type = 'standard')
{
    return Template::getInstance()->setTemplate($dir, $type);
}
