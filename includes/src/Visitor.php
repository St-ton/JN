<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Visitor
 * @since 5.0.0
 */
class Visitor
{
    /**
     * Besucher nach 3 Std in Besucherarchiv verschieben
     * @former archiviereBesucher()
     * @since 5.0.0
     */
    public static function archive()
    {
        $iInterval = 3;
        Shop::Container()->getDB()->queryPrepared(
            "INSERT INTO tbesucherarchiv
            (kBesucher, cIP, kKunde, kBestellung, cReferer, cEinstiegsseite, cBrowser,
              cAusstiegsseite, nBesuchsdauer, kBesucherBot, dZeit)
            SELECT kBesucher, cIP, kKunde, kBestellung, cReferer, cEinstiegsseite, cBrowser, cAusstiegsseite,
            (UNIX_TIMESTAMP(dLetzteAktivitaet) - UNIX_TIMESTAMP(dZeit)) AS nBesuchsdauer, kBesucherBot, dZeit
              FROM tbesucher
              WHERE dLetzteAktivitaet <= date_sub(now(), INTERVAL :interval HOUR)",
            [ 'interval' => $iInterval ],
            \DB\ReturnType::AFFECTED_ROWS
        );
        Shop::Container()->getDB()->queryPrepared(
            "DELETE FROM tbesucher
            WHERE dLetzteAktivitaet <= date_sub(now(), INTERVAL :interval HOUR)",
            [ 'interval' => $iInterval ],
            \DB\ReturnType::AFFECTED_ROWS
        );
    }
    /**
     * @param string $userAgent
     * @param string $ip
     * @return stdClass|null
     * @former dbLookupVisitor()
     * @since 5.0.0
     */
    public static function dbLookup($userAgent, $ip) {
        // check if we know that visitor (first by session-id)
        $oVisitor = Shop::Container()->getDB()->select('tbesucher', 'cSessID', session_id());
        if (null === $oVisitor) {
            // try to identify the visitor by its ip and user-agent
            $oVisitor = Shop::Container()->getDB()->select('tbesucher', 'cID', md5($userAgent . $ip));
        }

        return $oVisitor;
    }

    /**
     * @param object $vis
     * @param int    $visId
     * @param string $szUserAgent
     * @param int    $kBesucherBot
     * @return object
     * @since 5.0.0
     */
    public static function updateVisitorObject($vis, $visId,  $szUserAgent, $kBesucherBot)
    {
        $vis->kBesucher         = (int)$visId;
        $vis->cIP               = RequestHelper::getIP();
        $vis->cSessID           = session_id();
        $vis->cID               = md5($szUserAgent . RequestHelper::getIP());
        $vis->kKunde            = isset($_SESSION['Kunde']) ? $_SESSION['Kunde']->kKunde : 0;
        $vis->kBestellung       = isset($_SESSION['Kunde']) ? self::refreshCustomerOrderId((int)$vis->kKunde) : 0;
        $vis->cReferer          = self::getReferer();
        $vis->cUserAgent        = StringHandler::filterXSS($_SERVER['HTTP_USER_AGENT']);
        $vis->cBrowser          = self::getBrowser();
        $vis->cAusstiegsseite   = $_SERVER['REQUEST_URI'];
        $vis->dLetzteAktivitaet = (new \DateTime())->format('Y-m-d H:i:s');
        $vis->kBesucherBot      = $kBesucherBot;

        return $vis;
    }

    /**
     * @param string $szUserAgent
     * @param int    $kBesucherBot
     * @return object
     * @since 5.0.0
     */
    public static function createVisitorObject($szUserAgent, int $kBesucherBot)
    {
        $vis                    = new stdClass();
        $vis->kBesucher         = 0;
        $vis->cIP               = RequestHelper::getIP();
        $vis->cSessID           = session_id();
        $vis->cID               = md5($szUserAgent . RequestHelper::getIP());
        $vis->kKunde            = isset($_SESSION['Kunde']) ? $_SESSION['Kunde']->kKunde : 0;
        $vis->kBestellung       = isset($_SESSION['Kunde']) ? self::refreshCustomerOrderId((int)$vis->kKunde) : 0;
        $vis->cEinstiegsseite   = $_SERVER['REQUEST_URI'];
        $vis->cReferer          = self::getReferer();
        $vis->cUserAgent        = StringHandler::filterXSS($_SERVER['HTTP_USER_AGENT']);
        $vis->cBrowser          = self::getBrowser();
        $vis->cAusstiegsseite   = $_SERVER['REQUEST_URI'];
        $vis->dLetzteAktivitaet = (new \DateTime())->format('Y-m-d H:i:s');
        $vis->dZeit             = (new \DateTime())->format('Y-m-d H:i:s');
        $vis->kBesucherBot      = $kBesucherBot;
        // store search-string from search-engine too
        if ('' !== $vis->cReferer) {
            self::analyzeReferer($vis->kBesucher, $vis->cReferer);
        }

        return $vis;
    }

    /**
     * @param object $oVisitor
     * @return int
     * @since since 5.0.0
     */
    public static function dbInsert($oVisitor): int
    {
        return Shop::Container()->getDB()->insert('tbesucher', $oVisitor);
    }

    /**
     * @param object $oVisitor
     * @param int    $kBesucher
     * @return int
     * @since since 5.0.0
     */
    public static function dbUpdate($oVisitor, int $kBesucher): int
    {
        return Shop::Container()->getDB()->update('tbesucher', 'kBesucher', $kBesucher, $oVisitor);
    }

    /**
     * @param int $nCustomerId
     * @return int
     * @since 5.0.0
     */
    public static function refreshCustomerOrderId(int $nCustomerId)
    {
        $oOrder = Shop::Container()->getDB()->queryPrepared(
            'SELECT `kBestellung` FROM `tbestellung` WHERE `kKunde` = :_nCustomerId
                ORDER BY `dErstellt` DESC LIMIT 1',
            ['_nCustomerId' => $nCustomerId],
            \DB\ReturnType::SINGLE_OBJECT
        );

        return (int)($oOrder->kBestellung ?? 0);
    }

    /**
     * @return string
     * @former gibBrowser()
     * @since 5.0.0
     */
    public static function getBrowser(): string
    {
        $agent    = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
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
     * @fomer gibReferer()
     * @since 5.0.0
     */
    public static function getReferer(): string
    {
        if (isset($_SERVER['HTTP_REFERER'])) {
            $teile = explode('/', $_SERVER['HTTP_REFERER']);

            return StringHandler::filterXSS(strtolower($teile[2]));
        }

        return '';
    }

    /**
     * @return string
     * @former gibBot()
     * @since 5.0.0
     */
    public static function getBot(): string
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
     * @former werteRefererAus()
     * @since 5.0.0
     */
    public static function analyzeReferer(int $kBesucher, $referer)
    {
        $roh                 = $_SERVER['HTTP_REFERER'] ?? '';
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
                Shop::Container()->getDB()->insert('tbesuchersuchausdruecke', $ausdruck);
            }
        }
    }

    /**
     * @param string $referer
     * @return int
     * @former istSuchmaschine()
     * @since 5.0.0
     */
    public static function isSearchEngine($referer): int
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
     * @former istSpider()
     * @since 5.0.0
     */
    public static function isSpider($cUserAgent): int
    {
        $cSpider_arr       = getSpiderArr();
        $oBesucherBot      = null;
        $cBotUserAgent_arr = array_keys($cSpider_arr);
        foreach ($cBotUserAgent_arr as $cBotUserAgent) {
            if (strpos($cUserAgent, $cBotUserAgent) !== false) {
                $oBesucherBot = Shop::Container()->getDB()->select('tbesucherbot', 'cUserAgent', $cBotUserAgent);

                break;
            }
        }

        return (isset($oBesucherBot->kBesucherBot) && (int)$oBesucherBot->kBesucherBot > 0)
            ? (int)$oBesucherBot->kBesucherBot
            : 0;
    }
}
