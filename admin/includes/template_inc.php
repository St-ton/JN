<?php

use JTL\Helpers\Template;

/**
 * @param string $dir
 * @param string $type
 * @return bool
 * @deprecated since 5.0.0
 */
function __switchTemplate(string $dir, string $type = 'standard')
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Template::getInstance()->setTemplate($dir, $type);
}
