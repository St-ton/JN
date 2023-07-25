<?php declare(strict_types=1);

/**
 * @param int $sliderID
 * @return stdClass|null
 * @deprecated since 5.2.0
 */
function holeExtension(int $sliderID): ?stdClass
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return null;
}
