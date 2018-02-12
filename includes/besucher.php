<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
include PFAD_ROOT . PFAD_INCLUDES . 'spiderlist_inc.php';


$userAgent    = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
$kBesucherBot = istSpider($userAgent);
$bJustCreated = false;
//besucherzähler
if (!isset($_SESSION['oBesucher'])) {
    if ($kBesucherBot > 0) {
        Shop::DB()->query("UPDATE tbesucherbot SET dZeit = now() WHERE kBesucherBot = " . $kBesucherBot, 4);
    }
    archiviereBesucher();
    //schaue, ob für diese SessionID schon ein Besucher existiert
    $besucher = Shop::DB()->select('tbesucher', 'cSessID', session_id());
    if (!isset($besucher->kBesucher)) {
        //schaue, ob für diese IP + Browser schon ein Besucher existiert
        $besucher = Shop::DB()->select('tbesucher', 'cID', md5($userAgent . gibIP()));
    }
    if (!isset($besucher->kBesucher)) {
        //erstelle neuen Besucher
        //alltime BEsucherzähler hochsetzen
        Shop::DB()->query("UPDATE tbesucherzaehler SET nZaehler = nZaehler+1", 4);
        //neuen Besucher erstellen
        $besucher = createVisitorEntry($userAgent, $kBesucherBot);
    }
    //Besucher in der Session festhalten, falls einer erstellt oder rausgeholt
    if (isset($besucher->kBesucher) && $besucher->kBesucher > 0) {
        $besucher->kBesucher   = (int)$besucher->kBesucher;
        $besucher->kKunde      = (int)$besucher->kKunde;
        $_SESSION['oBesucher'] = $besucher;
    }
    $bJustCreated = true;
}
//Besucheraktivität aktualisieren
if ($bJustCreated !== true && isset($_SESSION['oBesucher']->kBesucher) && $_SESSION['oBesucher']->kBesucher > 0) {
    $oVisitorReNew = restoreVisitorEntry($userAgent, $kBesucherBot);
    // update the session (if visitor is a known customer)
    $_SESSION['oBesucher']->kKunde = isset($_SESSION['Kunde']->kKunde)
        ? $_SESSION['Kunde']->kKunde
        : 0;
    // update the databse
    Shop::DB()->executeQuery(
        "INSERT INTO
            tbesucher(kBesucher, cIP, cSessID, cID, kKunde, kBestellung, cReferer, cUserAgent, cEinstiegsseite,
                cBrowser, cAusstiegsseite, kBesucherBot, dLetzteAktivitaet, dZeit)
            VALUES(
                " . (int)$oVisitorReNew->kBesucher . ",
                '" . $oVisitorReNew->cIP . "',
                '" . $oVisitorReNew->cSessID . "',
                '" . $oVisitorReNew->cID . "',
                " . (int)$oVisitorReNew->kKunde . ",
                " . (int)$oVisitorReNew->kBestellung . ",
                '" . $oVisitorReNew->cReferer . "',
                '" . $oVisitorReNew->cUserAgent . "',
                '" . $oVisitorReNew->cEinstiegsseite . "',
                '" . $oVisitorReNew->cBrowser . "',
                '" . $oVisitorReNew->cAusstiegsseite . "',
                " . (int)$oVisitorReNew->kBesucherBot . ",
                " . $oVisitorReNew->dLetzteAktivitaet . ",
                " . $oVisitorReNew->dZeit . "
            )
            ON DUPLICATE KEY UPDATE
                cIP = VALUES(cIP), cSessID = VALUES(cSessID), cID = VALUES(cID), kKunde = VALUES(kKunde),
                kBestellung = VALUES(kBestellung), cReferer = VALUES(cReferer), cUserAgent = VALUES(cUserAgent),
                cEinstiegsseite = VALUES(cEinstiegsseite), cBrowser = VALUES(cBrowser),
                cAusstiegsseite = VALUES(cAusstiegsseite), kBesucherBot = VALUES(kBesucherBot),
                dLetzteAktivitaet = VALUES(dLetzteAktivitaet), dZeit = VALUES(dZeit)
        ;", 3);
}

/**
 * @param string $szUserAgent
 * @param int $kBesucherBot
 * @return object
 */
function createVisitorEntry($szUserAgent, $kBesucherBot)
{
    $oVisitor                    = new stdClass();
    $oVisitor->cIP               = gibIP();
    $oVisitor->cSessID           = session_id();
    $oVisitor->cID               = md5($szUserAgent . gibIP());
    $oVisitor->kKunde            = 0;
    $oVisitor->kBestellung       = 0;
    $oVisitor->cEinstiegsseite   = $_SERVER['REQUEST_URI'];
    $oVisitor->cReferer          = gibReferer();
    $oVisitor->cUserAgent        = StringHandler::filterXSS($_SERVER['HTTP_USER_AGENT']);
    $oVisitor->cBrowser          = gibBrowser();
    $oVisitor->cAusstiegsseite   = $_SERVER['REQUEST_URI'];
    $oVisitor->dLetzteAktivitaet = 'now()';
    $oVisitor->dZeit             = 'now()';
    $oVisitor->kBesucherBot      = $kBesucherBot;
    $oVisitor->kBesucher         = Shop::DB()->insert('tbesucher', $oVisitor);
    //falls SuMa -> Suchstrings festhalten
    if ($oVisitor->cReferer) {
        werteRefererAus($oVisitor->kBesucher, $oVisitor->cReferer);
    }

    return $oVisitor;
}

/**
 * @param string $szUserAgent
 * @param int $kBesucherBot
 * @return object
 */
function restoreVisitorEntry($szUserAgent, $kBesucherBot)
{
    $oVisitor                    = new stdClass();
    $oVisitor->cIP               = gibIP();
    $oVisitor->cSessID           = session_id();
    $oVisitor->cID               = md5($szUserAgent . gibIP());
    $oVisitor->kKunde            = $_SESSION['oBesucher']->kKunde;
    $oVisitor->kBestellung       = refreshCustomerOrderId((int)$_SESSION['oBesucher']->kKunde);
    $oVisitor->cEinstiegsseite   = $_SESSION['oBesucher']->cEinstiegsseite;
    $oVisitor->cReferer          = $_SESSION['oBesucher']->cReferer;
    $oVisitor->cUserAgent        = StringHandler::filterXSS($_SERVER['HTTP_USER_AGENT']);
    $oVisitor->cBrowser          = gibBrowser();
    $oVisitor->cAusstiegsseite   = $_SERVER['REQUEST_URI'];
    $oVisitor->dLetzteAktivitaet = 'now()';
    $oVisitor->dZeit             = 'now()';
    $oVisitor->kBesucherBot      = $kBesucherBot;
    $oVisitor->kBesucher         = $_SESSION['oBesucher']->kBesucher;

    return $oVisitor;
}

/**
 * @param int $nCustomerId
 * @return int
 */
function refreshCustomerOrderId($nCustomerId)
{
    $oOrder = Shop::DB()->query('
        SELECT `kBestellung` FROM `tbestellung` WHERE `kKunde` = ' . $nCustomerId . '
        ORDER BY `dErstellt` DESC LIMIT 1'
    , 1);

    return $oOrder->kBestellung;
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
