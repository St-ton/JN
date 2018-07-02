<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @return array
 * @deprecated since 5.0.0
 */
function getSpiderArr()
{
    trigger_error(__METHOD__ . ' is deprecated. Use Visitor::getSpiders() instead.', E_USER_DEPRECATED);
    return Visitor::getSpiders();
}
