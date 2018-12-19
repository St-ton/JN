<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param null|string $cUserAgent
 * @return stdClass
 * @deprecated since 5.0.0
 */
function getBrowser($cUserAgent = null)
{
    trigger_error(__METHOD__ . ' is deprecated. Use Visitor::getBrowserForUserAgent() instead.', E_USER_DEPRECATED);
    return Visitor::getBrowserForUserAgent($cUserAgent);
}
