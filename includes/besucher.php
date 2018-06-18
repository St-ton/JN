<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
include PFAD_ROOT . PFAD_INCLUDES . 'spiderlist_inc.php';

$userAgent    = $_SERVER['HTTP_USER_AGENT'] ?? '';
$kBesucherBot = Visitor::isSpider($userAgent);
// check, if the visitor is a bot and save that
if ($kBesucherBot > 0) {
    Shop::Container()->getDB()->queryPrepared(
        'UPDATE tbesucherbot SET dZeit = now() WHERE kBesucherBot = :_kBesucherBot',
        ['_kBesucherBot' => $kBesucherBot],
        \DB\ReturnType::AFFECTED_ROWS
    );
}
// cleanup `tbesucher`
Visitor::archive();

$oVisitor = Visitor::dbLookup($userAgent, RequestHelper::getIP());
if (null === $oVisitor) {
    if (isset($_SESSION['oBesucher'])) {
        // update the session-object with a new kBesucher-ID(!) (re-write it in the session at the end of the script)
        $oVisitor = Visitor::updateVisitorObject($_SESSION['oBesucher'], 0, $userAgent, $kBesucherBot);
    } else {
        // create a new visitor-object
        $oVisitor = Visitor::createVisitorObject($userAgent, $kBesucherBot);
    }
    // get back the new ID of that visitor (and write it back into the session)
    $oVisitor->kBesucher = Visitor::dbInsert($oVisitor);
    // allways increment the visitor-counter (if no bot)
    Shop::Container()->getDB()->query("UPDATE tbesucherzaehler SET nZaehler = nZaehler + 1",
        \DB\ReturnType::AFFECTED_ROWS
    );
} else {
    // prevent counting internal redirects by counting only the next request above 3 seconds
    $iTimeDiff = (new DateTime())->getTimestamp() - (new DateTime($oVisitor->dLetzteAktivitaet))->getTimestamp();
    if (2 < $iTimeDiff) {
        $oVisitor = Visitor::updateVisitorObject($oVisitor, $oVisitor->kBesucher, $userAgent, $kBesucherBot);
        // update the db and simultaneously retrieve the ID to update the session below
        $oVisitor->kBesucher = Visitor::dbUpdate($oVisitor, $oVisitor->kBesucher);
    } else {
        // time-diff is to low! so we do nothing but update this "last-action"-time in the session
        $oVisitor->dLetzteAktivitaet = (new \DateTime())->format('Y-m-d H:i:s');
    }
}
// "update" the session
$_SESSION['oBesucher'] = $oVisitor;


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
 * @param int    $oVisitorId
 * @param string $szUserAgent
 * @param int    $kBesucherBot
 * @return object
 * @deprecated since 5.0.0
 */
function updateVisitorObject($oVisitor, $oVisitorId,  $szUserAgent, $kBesucherBot)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Visitor::updateVisitorObject($oVisitor, $oVisitorId, $szUserAgent, $kBesucherBot);
}

/**
 * @param string $szUserAgent
 * @param int    $kBesucherBot
 * @return object
 * @deprecated since 5.0.0
 */
function createVisitorObject($szUserAgent, $kBesucherBot)
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
function dbUpdateVisitor($oVisitor, $kBesucher)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Visitor::dbUpdate($oVisitor, $kBesucher);
}

/**
 * @param int $nCustomerId
 * @return int [$kBestellung|0]
 * @deprecated since 5.0.0
 */
function refreshCustomerOrderId($nCustomerId)
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
function werteRefererAus($kBesucher, $referer)
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
