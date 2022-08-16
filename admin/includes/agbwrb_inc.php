<?php declare(strict_types=1);

/**
 * @param int   $customerGroupID
 * @param int   $languageID
 * @param array $post
 * @param int   $textID
 * @return bool
 * @deprecated since 5.2.0
 */
function speicherAGBWRB(int $customerGroupID, int $languageID, array $post, int $textID = 0): bool
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return false;
}
