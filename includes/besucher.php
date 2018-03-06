<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
include PFAD_ROOT . PFAD_INCLUDES . 'spiderlist_inc.php';

$userAgent    = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
$kBesucherBot = istSpider($userAgent);
// check, if the visitor is a bot and save that
if ($kBesucherBot > 0) {
    Shop::DB()->queryPrepared("UPDATE tbesucherbot SET dZeit = now() WHERE kBesucherBot = :_kBesucherBot",
        ['_kBesucherBot' => $kBesucherBot],
        Shop::DB()::RET_AFFECTED_ROWS
    );
}
// cleanup `tbesucher`
archiviereBesucher();

$oVisitor = null;
$oVisitor = dbLookupVisitor($userAgent, gibIP());
if (null === $oVisitor) {
    if (isset($_SESSION['oBesucher'])) {
        // update the session-object with a new kBesucher-ID(!) (re-write it in the session at the end of the script)
        $oVisitor = updateVisitorObject($_SESSION['oBesucher'], 0, $userAgent, $kBesucherBot);
    } else {
        // create a new visitor-object
        $oVisitor = createVisitorObject($userAgent, $kBesucherBot);
    }
    // get back the new ID of that visitor (and write it back into the session)
    $oVisitor->kBesucher = dbInsertVisitor($oVisitor);
    // allways increment the visitor-counter (if no bot)
    Shop::DB()->query("UPDATE tbesucherzaehler SET nZaehler = nZaehler + 1",
        Shop::DB()::RET_AFFECTED_ROWS
    );
} else {
    // prevent counting internal redirects by counting only the next request above 3 seconds
    $iTimeDiff = (new DateTime())->getTimestamp() - (new DateTime($oVisitor->dLetzteAktivitaet))->getTimestamp();
    if (2 < $iTimeDiff) {
        $oVisitor = updateVisitorObject($oVisitor, $oVisitor->kBesucher, $userAgent, $kBesucherBot);
        // update the db and simultaneously retrieve the ID to update the session below
        $oVisitor->kBesucher = dbUpdateVisitor($oVisitor, $oVisitor->kBesucher);
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
 */
function dbLookupVisitor($szUserAgent, $szIp) {
    // check if we know that visitor (first by session-id)
    $oVisitor = Shop::DB()->select('tbesucher', 'cSessID', session_id());
    if (null === $oVisitor) {
        // try to identify the visitor by its ip and user-agent
        $oVisitor = Shop::DB()->select('tbesucher', 'cID', md5($szUserAgent . $szIp));
    }

    return $oVisitor;
}

/**
 * @param object $oVisitor
 * @param int    $oVisitorId
 * @param string $szUserAgent
 * @param int    $kBesucherBot
 * @return object
 */
function updateVisitorObject($oVisitor, $oVisitorId,  $szUserAgent, $kBesucherBot)
{
    $oVisitor->kBesucher         = (int)$oVisitorId;
    $oVisitor->cIP               = gibIP();
    $oVisitor->cSessID           = session_id();
    $oVisitor->cID               = md5($szUserAgent . gibIP());
    $oVisitor->kKunde            = (isset($_SESSION['Kunde']) ? $_SESSION['Kunde']->kKunde : 0);
    $oVisitor->kBestellung       = (isset($_SESSION['Kunde']) ? (refreshCustomerOrderId((int)$oVisitor->kKunde)) : 0);
    $oVisitor->cReferer          = gibReferer();
    $oVisitor->cUserAgent        = StringHandler::filterXSS($_SERVER['HTTP_USER_AGENT']);
    $oVisitor->cBrowser          = gibBrowser();
    $oVisitor->cAusstiegsseite   = $_SERVER['REQUEST_URI'];
    $oVisitor->dLetzteAktivitaet = (new \DateTime())->format('Y-m-d H:i:s');
    $oVisitor->kBesucherBot      = $kBesucherBot;

    return $oVisitor;
}

/**
 * @param string $szUserAgent
 * @param int    $kBesucherBot
 * @return object
 */
function createVisitorObject($szUserAgent, $kBesucherBot)
{
    $oVisitor                    = new stdClass();
    $oVisitor->kBesucher         = 0;
    $oVisitor->cIP               = gibIP();
    $oVisitor->cSessID           = session_id();
    $oVisitor->cID               = md5($szUserAgent . gibIP());
    $oVisitor->kKunde            = (isset($_SESSION['Kunde']) ? $_SESSION['Kunde']->kKunde : 0);
    $oVisitor->kBestellung       = (isset($_SESSION['Kunde']) ? (refreshCustomerOrderId((int)$oVisitor->kKunde)) : 0);
    $oVisitor->cEinstiegsseite   = $_SERVER['REQUEST_URI'];
    $oVisitor->cReferer          = gibReferer();
    $oVisitor->cUserAgent        = StringHandler::filterXSS($_SERVER['HTTP_USER_AGENT']);
    $oVisitor->cBrowser          = gibBrowser();
    $oVisitor->cAusstiegsseite   = $_SERVER['REQUEST_URI'];
    $oVisitor->dLetzteAktivitaet = (new \DateTime())->format('Y-m-d H:i:s');
    $oVisitor->dZeit             = (new \DateTime())->format('Y-m-d H:i:s');
    $oVisitor->kBesucherBot      = $kBesucherBot;
    // store search-string from search-engine too
    if ('' !== $oVisitor->cReferer) {
        werteRefererAus($oVisitor->kBesucher, $oVisitor->cReferer);
    }

    return $oVisitor;
}

/**
 * @param object $oVisitor
 * @return int [LAST_INSERT_ID|0(on fail)]
 */
function dbInsertVisitor($oVisitor)
{
    return Shop::DB()->insert('tbesucher', $oVisitor);
}

/**
 * @param object $oVisitor
 * @param int    $kBesucher
 */
function dbUpdateVisitor($oVisitor, $kBesucher)
{
    return Shop::DB()->update('tbesucher', 'kBesucher', $kBesucher, $oVisitor);
}

/**
 * @param int $nCustomerId
 * @return int [$kBestellung|0]
 */
function refreshCustomerOrderId($nCustomerId)
{
    $oOrder = Shop::DB()->queryPrepared('
        SELECT `kBestellung` FROM `tbestellung` WHERE `kKunde` = :_nCustomerId
        ORDER BY `dErstellt` DESC LIMIT 1',
        [
            '_nCustomerId' => $nCustomerId
        ],
        Shop::DB()::RET_SINGLE_OBJECT
    );

    return (isset($oOrder->kBestellung)) ? $oOrder->kBestellung : 0;
}

/**
 * @return string
 */
function gibBrowser()
{
    $agent = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : '';
    $szMobile = '';
    if (stripos($agent, 'iphone') !== false
        || stripos($agent, 'ipad') !== false
        || stripos($agent, 'ipod') !== false
        || stripos($agent, 'android') !== false
        || stripos($agent, 'opera mobi') !== false
        || stripos($agent, 'blackberry') !== false
        || stripos($agent, 'playbook') !== false
        || stripos($agent, 'kindle') !== false
        || stripos($agent, 'windows phone') !== false
    ) {
        $szMobile = '/Mobile';
    }
    if (strpos($agent, 'msie') !== false) {
        $pos = strpos($agent, 'msie');

        return 'Internet Explorer ' . (int)substr($agent, $pos + 4) . $szMobile;
    }
    if (strpos($agent, 'opera') !== false
        || stripos($agent, 'opr') !== false
    ) {
        return 'Opera' . $szMobile;
    }
    if (stripos($agent, 'vivaldi') !== false) {
        return 'Vivaldi' . $szMobile;
    }
    if (strpos($agent, 'safari') !== false) {
        return 'Safari' . $szMobile;
    }
    if (strpos($agent, 'firefox') !== false) {
        return 'Firefox' . $szMobile;
    }
    if (strpos($agent, 'chrome') !== false) {
        return 'Chrome' . $szMobile;
    }

    return 'Sonstige' . $szMobile;
}

/**
 * @return string
 */
function gibReferer()
{
    if (isset($_SERVER['HTTP_REFERER'])) {
        $teile = explode('/', $_SERVER['HTTP_REFERER']);

        return StringHandler::filterXSS(strtolower($teile[2]));
    }

    return '';
}

/**
 * @return string
 */
function gibBot()
{
    $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    if (strpos($agent, 'googlebot') !== false) {
        return 'Google';
    }
    if (strpos($agent, 'bingbot') !== false) {
        return 'Bing';
    }
    if (strpos($agent, 'inktomi.com') !== false) {
        return 'Inktomi';
    }
    if (strpos($agent, 'yahoo! slurp') !== false) {
        return 'Yahoo!';
    }
    if (strpos($agent, 'msnbot') !== false) {
        return 'MSN';
    }
    if (strpos($agent, 'teoma') !== false) {
        return 'Teoma';
    }
    if (strpos($agent, 'crawler') !== false) {
        return 'Crawler';
    }
    if (strpos($agent, 'scooter') !== false) {
        return 'Scooter';
    }
    if (strpos($agent, 'fireball') !== false) {
        return 'Fireball';
    }
    if (strpos($agent, 'ask jeeves') !== false) {
        return 'Ask';
    }

    return '';
}

/**
 * @param int    $kBesucher
 * @param string $referer
 */
function werteRefererAus($kBesucher, $referer)
{
    $kBesucher           = (int)$kBesucher;
    $roh                 = $_SERVER['HTTP_REFERER'];
    $ausdruck            = new stdClass();
    $ausdruck->kBesucher = $kBesucher;
    $ausdruck->cRohdaten = StringHandler::filterXSS($_SERVER['HTTP_REFERER']);
    $param               = '';
    if (strpos($referer, '.google.') !== false
        || strpos($referer, 'suche.t-online.') !== false
        || strpos($referer, 'search.live.') !== false
        || strpos($referer, '.aol.') !== false
        || strpos($referer, '.aolsvc.') !== false
        || strpos($referer, '.ask.') !== false
        || strpos($referer, 'search.icq.') !== false
        || strpos($referer, 'search.msn.') !== false
        || strpos($referer, '.exalead.') !== false
    ) {
        $param = 'q';
    } elseif (strpos($referer, 'suche.web') !== false) {
        $param = 'su';
    } elseif (strpos($referer, 'suche.aolsvc') !== false) {
        $param = 'query';
    } elseif (strpos($referer, 'search.yahoo') !== false) {
        $param = 'p';
    } elseif (strpos($referer, 'search.ebay') !== false) {
        $param = 'satitle';
    }
    if ($param !== '') {
        preg_match("/(\?$param|&$param)=[^&]+/i", $roh, $treffer);
        $ausdruck->cSuchanfrage = isset($treffer[0]) ? urldecode(substr($treffer[0], 3)) : null;
        if ($ausdruck->cSuchanfrage) {
            Shop::DB()->insert('tbesuchersuchausdruecke', $ausdruck);
        }
    }
}

/**
 * @param string $referer
 * @return int
 */
function istSuchmaschine($referer)
{
    if (!$referer) {
        return 0;
    }
    if (strpos($referer, '.google.') !== false
        || strpos($referer, '.bing.') !== false
        || strpos($referer, 'suche.') !== false
        || strpos($referer, 'search.') !== false
        || strpos($referer, '.yahoo.') !== false
        || strpos($referer, '.fireball.') !== false
        || strpos($referer, '.seekport.') !== false
        || strpos($referer, '.keywordspy.') !== false
        || strpos($referer, '.hotfrog.') !== false
        || strpos($referer, '.altavista.') !== false
        || strpos($referer, '.ask.') !== false
    ) {
        return 1;
    }

    return 0;
}

/**
 * @param string $cUserAgent
 * @return int
 */
function istSpider($cUserAgent)
{
    $cSpider_arr       = getSpiderArr();
    $oBesucherBot      = null;
    $cBotUserAgent_arr = array_keys($cSpider_arr);
    foreach ($cBotUserAgent_arr as $cBotUserAgent) {
        if (strpos($cUserAgent, $cBotUserAgent) !== false) {
            $oBesucherBot = Shop::DB()->select('tbesucherbot', 'cUserAgent', $cBotUserAgent);

            break;
        }
    }

    return (isset($oBesucherBot->kBesucherBot) && (int)$oBesucherBot->kBesucherBot > 0)
        ? (int)$oBesucherBot->kBesucherBot
        : 0;
}
