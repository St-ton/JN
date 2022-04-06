<?php declare(strict_types=1);

use JTL\Catalog\Separator;
use JTL\Helpers\Text;
use JTL\Shop;

/**
 * @param array $post
 * @return bool
 * @deprecated since 5.2.0
 */
function speicherTrennzeichen(array $post): bool
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return false;
}
