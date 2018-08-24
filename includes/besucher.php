<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param string $szUserAgent
 * @param string $szIp
 * @return object
 * @deprecated since 5.0.0
 */
function dbLookupVisitor($szUserAgent, $szIp)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use Visitor::dbLookup() instead.', E_USER_DEPRECATED);
    return Visitor::dbLookup($szUserAgent, $szIp);
}

/**
 * @param object $oVisitor
 * @param int    $visitorId
 * @param string $szUserAgent
 * @param int    $kBesucherBot
 * @return object
 * @deprecated since 5.0.0
 */
function updateVisitorObject($oVisitor, int $visitorId,  $szUserAgent, $kBesucherBot)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Visitor::updateVisitorObject($oVisitor, $visitorId, $szUserAgent, $kBesucherBot);
}

/**
 * @param string $szUserAgent
 * @param int    $kBesucherBot
 * @return object
 * @deprecated since 5.0.0
 */
function createVisitorObject($szUserAgent, int $kBesucherBot)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Visitor::createVisitorObject($szUserAgent, $kBesucherBot);
}

/**
 * @param object $oVisitor
 * @return int [LAST_INSERT_ID|0(on fail)]
 * @deprecated since 5.0.0
 */
function dbInsertVisitor($oVisitor)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Visitor::dbInsert($oVisitor);
}

/**
 * @param object $oVisitor
 * @param int    $kBesucher
 * @return int
 * @deprecated since 5.0.0
 */
function dbUpdateVisitor($oVisitor, int $kBesucher)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Visitor::dbUpdate($oVisitor, $kBesucher);
}

/**
 * @param int $nCustomerId
 * @return int [$kBestellung|0]
 * @deprecated since 5.0.0
 */
function refreshCustomerOrderId(int $nCustomerId)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Visitor::refreshCustomerOrderId($nCustomerId);
}

/**
 * @return string
 * @deprecated since 5.0.0
 */
function gibBrowser()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Visitor::getBrowser();
}

/**
 * @return string
 * @deprecated since 5.0.0
 */
function gibReferer()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Visitor::getReferer();
}

/**
 * @return string
 * @deprecated since 5.0.0
 */
function gibBot()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Visitor::getBot();
}

/**
 * @param int    $kBesucher
 * @param string $referer
 * @deprecated since 5.0.0
 */
function werteRefererAus(int $kBesucher, $referer)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    Visitor::analyzeReferer($kBesucher, $referer);
}

/**
 * @param string $referer
 * @return int
 * @deprecated since 5.0.0
 */
function istSuchmaschine($referer)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Visitor::isSearchEngine($referer);
}

/**
 * @param string $cUserAgent
 * @return int
 * @deprecated since 5.0.0
 */
function istSpider($cUserAgent)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Visitor::isSpider($cUserAgent);
}
