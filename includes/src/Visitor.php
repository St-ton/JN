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
     * @since 5.0.0
     */
    public static function generateData()
    {
        $userAgent    = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $kBesucherBot = self::isSpider($userAgent);
        // check, if the visitor is a bot and save that
        if ($kBesucherBot > 0) {
            Shop::Container()->getDB()->queryPrepared(
                'UPDATE tbesucherbot SET dZeit = now() WHERE kBesucherBot = :_kBesucherBot',
                ['_kBesucherBot' => $kBesucherBot],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
        // cleanup `tbesucher`
        self::archive();

        $oVisitor = self::dbLookup($userAgent, RequestHelper::getIP());
        if (null === $oVisitor) {
            if (isset($_SESSION['oBesucher'])) {
                // update the session-object with a new kBesucher-ID(!) (re-write it in the session at the end of the script)
                $oVisitor = self::updateVisitorObject($_SESSION['oBesucher'], 0, $userAgent, $kBesucherBot);
            } else {
                // create a new visitor-object
                $oVisitor = self::createVisitorObject($userAgent, $kBesucherBot);
            }
            // get back the new ID of that visitor (and write it back into the session)
            $oVisitor->kBesucher = self::dbInsert($oVisitor);
            // allways increment the visitor-counter (if no bot)
            Shop::Container()->getDB()->query("UPDATE tbesucherzaehler SET nZaehler = nZaehler + 1",
                \DB\ReturnType::AFFECTED_ROWS
            );
        } else {
            // prevent counting internal redirects by counting only the next request above 3 seconds
            $iTimeDiff = (new DateTime())->getTimestamp() - (new DateTime($oVisitor->dLetzteAktivitaet))->getTimestamp();
            if (2 < $iTimeDiff) {
                $oVisitor = self::updateVisitorObject($oVisitor, $oVisitor->kBesucher, $userAgent, $kBesucherBot);
                // update the db and simultaneously retrieve the ID to update the session below
                $oVisitor->kBesucher = self::dbUpdate($oVisitor, $oVisitor->kBesucher);
            } else {
                // time-diff is to low! so we do nothing but update this "last-action"-time in the session
                $oVisitor->dLetzteAktivitaet = (new \DateTime())->format('Y-m-d H:i:s');
            }
        }
        $_SESSION['oBesucher'] = $oVisitor;
    }

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
            ['interval' => $iInterval],
            \DB\ReturnType::AFFECTED_ROWS
        );
        Shop::Container()->getDB()->queryPrepared(
            "DELETE FROM tbesucher
            WHERE dLetzteAktivitaet <= date_sub(now(), INTERVAL :interval HOUR)",
            ['interval' => $iInterval],
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
    public static function dbLookup($userAgent, $ip)
    {
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
    public static function updateVisitorObject($vis, $visId, $szUserAgent, $kBesucherBot)
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
     * @param string $userAgent
     * @return int
     * @former istSpider()
     * @since 5.0.0
     */
    public static function isSpider($userAgent): int
    {
        $cSpider_arr       = self::getSpiders();
        $oBesucherBot      = null;
        $cBotUserAgent_arr = array_keys($cSpider_arr);
        foreach ($cBotUserAgent_arr as $cBotUserAgent) {
            if (strpos($userAgent, $cBotUserAgent) !== false) {
                $oBesucherBot = Shop::Container()->getDB()->select('tbesucherbot', 'cUserAgent', $cBotUserAgent);

                break;
            }
        }

        return (isset($oBesucherBot->kBesucherBot) && (int)$oBesucherBot->kBesucherBot > 0)
            ? (int)$oBesucherBot->kBesucherBot
            : 0;
    }

    /**
     * @return array
     */
    public static function getSpiders(): array
    {
        $cSpider_arr                                                       = [];
        $cSpider_arr['4anything.com LinkChecker v2.0']                     = '4Anything.com';
        $cSpider_arr['abcdatos']                                           = 'ABCdatos BotLink';
        $cSpider_arr['accoona']                                            = 'Accoona-AI-Agent';
        $cSpider_arr['Aberja Checkomat']                                   = 'Aberja.de Link Checking';
        $cSpider_arr['acme.spider']                                        = 'Acme.Spider';
        $cSpider_arr['acoon']                                              = 'Acoon';
        $cSpider_arr['AgentName/0.1 libwww-perl/5.48']                     = 'LinkoMatic.com Link Checker';
        $cSpider_arr['ahoythehomepagefinder']                              = 'Ahoy&#33;';
        $cSpider_arr['alexa']                                              = 'Alexa';
        $cSpider_arr['ALink']                                              = 'ALink Link Checker';
        $cSpider_arr['alkaline']                                           = 'Alkaline';
        $cSpider_arr['altavista']                                          = 'Scooter';
        $cSpider_arr['AMeta']                                              = 'AMeta Link Checker';
        $cSpider_arr['anthill']                                            = 'Anthill';
        $cSpider_arr['antibot']                                            = 'Antibot';
        $cSpider_arr['aport']                                              = 'Aport';
        $cSpider_arr['appie']                                              = 'Walhello Appie';
        $cSpider_arr['arachnophilia']                                      = 'Arachnophilia';
        $cSpider_arr['arale']                                              = 'Arale';
        $cSpider_arr['araneo']                                             = 'Araneo';
        $cSpider_arr['architext']                                          = 'ArchitextSpider';
        $cSpider_arr['archive_org']                                        = 'Archive_org';
        $cSpider_arr['aretha']                                             = 'Aretha';
        $cSpider_arr['ariadne']                                            = 'ARIADNE';
        $cSpider_arr['arks']                                               = 'Arks';
        $cSpider_arr['aspider']                                            = 'Aspider';
        $cSpider_arr['ASPSearch URL Checker']                              = 'ASPSearch Link Checker';
        $cSpider_arr['atn.txt']                                            = 'ATN Worldwide';
        $cSpider_arr['atomz']                                              = 'Atomz.com Search Robot';
        $cSpider_arr['atSpider/1.0']                                       = 'atEmail Extractor';
        $cSpider_arr['auresys']                                            = 'AURESYS';
        $cSpider_arr['autoemailspider']                                    = 'Auto Email Extractor';
        $cSpider_arr['awbot']                                              = 'AWBot';
        $cSpider_arr['backrub']                                            = 'BackRub';
        $cSpider_arr['bbot']                                               = 'BBot';
        $cSpider_arr['BeebwareDirectory/v0.01']                            = 'Beepware.co.uk Link Checker';
        $cSpider_arr['Big Brother']                                        = 'Big Brother Link Checker';
        $cSpider_arr['bigbrother']                                         = 'Big Brother';
        $cSpider_arr['BigBrother/1.6e']                                    = 'Big Brother Network Monitor';
        $cSpider_arr['bjaaland']                                           = 'Bjaaland';
        $cSpider_arr['blackwidow']                                         = 'BlackWidow';
        $cSpider_arr['blindekuh']                                          = 'Die Blinde Kuh';
        $cSpider_arr['BlogBot']                                            = 'BlogBot Link Checker';
        $cSpider_arr['bloodhound']                                         = 'Bloodhound';
        $cSpider_arr['BMChecker']                                          = 'BMLink Checker';
        $cSpider_arr['bobby']                                              = 'Bobby';
        $cSpider_arr['Bookmark Buddy']                                     = 'Bookmark Buddy Link Checker';
        $cSpider_arr['borg-bot']                                           = 'Borg-Bot';
        $cSpider_arr['boris']                                              = 'Boris';
        $cSpider_arr['brightnet']                                          = 'bright.net caching robot';
        $cSpider_arr['bspider']                                            = 'BSpider';
        $cSpider_arr['bumblebee']                                          = 'Bumblebee';
        $cSpider_arr['cactvschemistryspider']                              = 'CACTVS Chemistry Spider';
        $cSpider_arr['calif']                                              = 'Calif';
        $cSpider_arr['cassandra']                                          = 'Cassandra';
        $cSpider_arr['cg-eye interactive']                                 = 'HtmlHelp.com';
        $cSpider_arr['cgireader']                                          = 'Digimarc Marcspider';
        $cSpider_arr['Check&amp;Get']                                      = 'Check&amp;Get Link Checker';
        $cSpider_arr['checkbot']                                           = 'Checkbot';
        $cSpider_arr['Checkbot/1.xx LWP/5.xx']                             = 'Link Checker';
        $cSpider_arr['CheckLinks/1.x.x']                                   = 'Link Checker';
        $cSpider_arr['CheckUrl']                                           = 'NTL.com Link Checker';
        $cSpider_arr['CheckWeb']                                           = 'CheckWeb Link Checker';
        $cSpider_arr['christcrawler']                                      = 'ChristCrawler.com';
        $cSpider_arr['churl']                                              = 'churl';
        $cSpider_arr['cienciaficcion']                                     = 'cIeNcIaFiCcIoN.nEt';
        $cSpider_arr['CJ Spider/']                                         = 'Commision Junction Link Checker';
        $cSpider_arr['CNET_Snoop']                                         = 'CNET_Snoop Link Checker';
        $cSpider_arr['ColdFusion']                                         = 'Networld.com Link Checker';
        $cSpider_arr['collective']                                         = 'Collective';
        $cSpider_arr['combine']                                            = 'Combine System';
        $cSpider_arr['conceptbot']                                         = 'Conceptbot';
        $cSpider_arr['coolbot']                                            = 'CoolBot';
        $cSpider_arr['core']                                               = 'Web Core / Roots';
        $cSpider_arr['cosmos']                                             = 'XYLEME Robot';
        $cSpider_arr['crawl']                                              = 'Crawl';
        $cSpider_arr['CreativeCommons/0.06-dev']                           = 'CreativeCommons.org';
        $cSpider_arr['Crescent Internet ToolPak HTTP OLE Control v.1.0']   = 'Crescent Email Extractor';
        $cSpider_arr['cruiser']                                            = 'Internet Cruiser Robot';
        $cSpider_arr['cscrawler']                                          = 'CsCrawler';
        $cSpider_arr['CSE HTML Validator']                                 = 'CSE HTML Link Checker';
        $cSpider_arr['CurryGuide SiteScan 1.1']                            = 'CurryGuide Link Checker';
        $cSpider_arr['cusco']                                              = 'Cusco';
        $cSpider_arr['Custo x.x']                                          = 'Netwu.com Link Checker';
        $cSpider_arr['cyberspyder']                                        = 'CyberSpyder Link Test';
        $cSpider_arr['daviesbot']                                          = 'DaviesBot';
        $cSpider_arr['DeadLinkCheck/0.4.0 libwww-perl/5.xx']               = 'Dead Link Checker';
        $cSpider_arr['deepindex']                                          = 'DeepIndex';
        $cSpider_arr['Denmex websearch']                                   = 'Denmax.com Link Checker';
        $cSpider_arr['desertrealm']                                        = 'Desert Realm Spider';
        $cSpider_arr['deweb']                                              = 'DeWeb(c) Katalog/Index';
        $cSpider_arr['dienstspider']                                       = 'DienstSpider';
        $cSpider_arr['digger']                                             = 'Digger';
        $cSpider_arr['digout4u']                                           = 'Digout4u';
        $cSpider_arr['diibot']                                             = 'Digital Integrity Robot';
        $cSpider_arr['direct_hit']                                         = 'Direct Hit Grabber';
        $cSpider_arr['DISCo Watchman']                                     = 'DISCo Watchman Link Checker';
        $cSpider_arr['dnabot']                                             = 'DNAbot';
        $cSpider_arr['DoctorHTML']                                         = 'DoctorHTML Link Checker';
        $cSpider_arr['download_express']                                   = 'DownLoad Express';
        $cSpider_arr['dragonbot']                                          = 'DragonBot';
        $cSpider_arr['DRKSpider']                                          = 'DRKLink Checker';
        $cSpider_arr['dwcp']                                               = 'DWCP';
        $cSpider_arr['e-collector']                                        = 'e-collector';
        $cSpider_arr['ebiness']                                            = 'EbiNess';
        $cSpider_arr['echo']                                               = 'EchO';
        $cSpider_arr['EldoS TimelyWeb/3.x']                                = 'TimelyWeb Link Checker';
        $cSpider_arr['elfinbot']                                           = 'ELFINBOT';
        $cSpider_arr['emacs']                                              = 'Emacs-w3 Search Engine';
        $cSpider_arr['Email Extractor']                                    = 'Email Extractor';
        $cSpider_arr['EmailSiphon']                                        = 'EmailSiphon Extractor';
        $cSpider_arr['EmailWolf']                                          = 'EmailWolf Extractor';
        $cSpider_arr['EmailWolf 1.00']                                     = 'EmailWolf Extractor';
        $cSpider_arr['emcspider']                                          = 'ananzi';
        $cSpider_arr['esther']                                             = 'Esther';
        $cSpider_arr['euroseek']                                           = 'EuroSeek';
        $cSpider_arr['evliyacelebi']                                       = 'Evliya Celebi';
        $cSpider_arr['exactseek']                                          = 'ExactSeek Crawler';
        $cSpider_arr['exalead']                                            = 'Exalead';
        $cSpider_arr['exite']                                              = 'Exite';
        $cSpider_arr['ExtractorPro']                                       = 'Email Extractor';
        $cSpider_arr['ezresult']                                           = 'Ezresult';
        $cSpider_arr['fast']                                               = 'Fast Web Crawler';
        $cSpider_arr['fast-webcrawler']                                    = 'AllTheWeb';
        $cSpider_arr['fastcrawler']                                        = 'FastCrawler';
        $cSpider_arr['FavOrg']                                             = 'FavOrg Link Checker';
        $cSpider_arr['Favorites Sweeper']                                  = 'Favorites Sweeper Link Checker';
        $cSpider_arr['fdse']                                               = 'Fluid Dynamics Search Engine robot';
        $cSpider_arr['felix']                                              = 'Felix IDE';
        $cSpider_arr['ferret']                                             = 'Wild Ferret Web Hopper';
        $cSpider_arr['fetchrover']                                         = 'FetchRover';
        $cSpider_arr['fido']                                               = 'fido';
        $cSpider_arr['finnish']                                            = 'Hämähäkki';
        $cSpider_arr['fireball']                                           = 'Fireball';
        $cSpider_arr['Firstsbot']                                          = 'Firstsbot Link Checker';
        $cSpider_arr['fish']                                               = 'Fish search';
        $cSpider_arr['fouineur']                                           = 'Fouineur';
        $cSpider_arr['francoroute']                                        = 'Robot Francoroute';
        $cSpider_arr['Franklin Locator 1.8']                               = 'Franklin Spam Bot';
        $cSpider_arr['freecrawl']                                          = 'Freecrawl';
        $cSpider_arr['FreshLinks.exe']                                     = 'FreshLinks Link Checker';
        $cSpider_arr['Funnel Web Profiler']                                = 'Funnel Web Profiler Link Checker';
        $cSpider_arr['funnelweb']                                          = 'FunnelWeb';
        $cSpider_arr['gama']                                               = 'gammaSpider, FocusedCrawler';
        $cSpider_arr['gazz']                                               = 'gazz';
        $cSpider_arr['gcreep']                                             = 'GCreep';
        $cSpider_arr['GeonaBot 1.0']                                       = 'Geona Link Checker';
        $cSpider_arr['getbot']                                             = 'GetBot';
        $cSpider_arr['geturl']                                             = 'GetURL';
        $cSpider_arr['gigablast']                                          = 'Gigabot';
        $cSpider_arr['gigabot']                                            = 'GigaBot';
        $cSpider_arr['gnodspider']                                         = 'GNOD Spider';
        $cSpider_arr['golem']                                              = 'Golem';
        $cSpider_arr['feedfetcher']                                        = 'Google Feedfetcher';
        $cSpider_arr['googlebot-image']                                    = 'Google ImageBot';
        $cSpider_arr['adsbot-google']                                      = 'Google AdWords';
        $cSpider_arr['mediapartners-google']                               = 'Google AdSense';
        $cSpider_arr['googlebot']                                          = 'Google';
        $cSpider_arr['google']                                             = 'GoogleBot';
        $cSpider_arr['grapnel']                                            = 'Grapnel';
        $cSpider_arr['griffon']                                            = 'Griffon';
        $cSpider_arr['gromit']                                             = 'Gromit';
        $cSpider_arr['grub']                                               = 'Grub.org';
        $cSpider_arr['gulliver']                                           = 'Northern Light Gulliver';
        $cSpider_arr['gulperbot']                                          = 'Gulper Bot';
        $cSpider_arr['hambot']                                             = 'HamBot';
        $cSpider_arr['harvest']                                            = 'Harvest';
        $cSpider_arr['Haste/0.12']                                         = 'Haste Site Monitoring';
        $cSpider_arr['havindex']                                           = 'havIndex';
        $cSpider_arr['henrythemiragorobot']                                = 'Mirago';
        $cSpider_arr['holmes']                                             = 'Holmes';
        $cSpider_arr['hometown']                                           = 'Hometown Pro';
        $cSpider_arr['htdig']                                              = 'httDig';
        $cSpider_arr['Html Link Validator']                                = 'Html Link Checker';
        $cSpider_arr['htmlgobble']                                         = 'HTMLgobble';
        $cSpider_arr['hyperdecontextualizer']                              = 'Hyper-Decontextualizer';
        $cSpider_arr['ia_archiver']                                        = 'Alexa';
        $cSpider_arr['iajabot']                                            = 'iajaBot';
        $cSpider_arr['iconoclast']                                         = 'Popular Iconoclast';
        $cSpider_arr['IconSurf/2.0']                                       = 'FavIcon Finder';
        $cSpider_arr['IEFav172Free']                                       = 'Favorites Link Checker';
        $cSpider_arr['ilse']                                               = 'Ingrid';
        $cSpider_arr['imagelock']                                          = 'Imagelock';
        $cSpider_arr['incywincy']                                          = 'IncyWincy';
        $cSpider_arr['Industry Program 1.0.x']                             = 'Industry Spam Bot';
        $cSpider_arr['InfoLink/1.x']                                       = 'InfoLink Link Checker';
        $cSpider_arr['informant']                                          = 'Informant';
        $cSpider_arr['infoseek']                                           = 'InfoSeek Robot 1.0';
        $cSpider_arr['infoseeksidewinder']                                 = 'Infoseek Sidewinder';
        $cSpider_arr['infospider']                                         = 'InfoSpiders';
        $cSpider_arr['inktomi']                                            = 'Slurp';
        $cSpider_arr['inspectorwww']                                       = 'Inspector Web';
        $cSpider_arr['intelliagent']                                       = 'IntelliAgent';
        $cSpider_arr['InternetLinkAgent']                                  = 'Internet Link Checker';
        $cSpider_arr['InternetPeriscope']                                  = 'InternetPeriscope Link Checker';
        $cSpider_arr['internetseer']                                       = 'InternetSeer';
        $cSpider_arr['irobot']                                             = 'I, Robot';
        $cSpider_arr['iron33']                                             = 'Iron33';
        $cSpider_arr['israelisearch']                                      = 'Israeli-search';
        $cSpider_arr['IUPUI Research Bot v 1.9a']                          = 'Spam Bot';
        $cSpider_arr['javabee']                                            = 'JavaBee';
        $cSpider_arr['javElink']                                           = 'javElink Link Checker';
        $cSpider_arr['jbot']                                               = 'JBot Java Web Robot';
        $cSpider_arr['JCheckLinks/0.1 RPT-HTTPClient/0.3-1']               = 'JCheckLinks Link Checker';
        $cSpider_arr['jcrawler']                                           = 'JCrawler';
        $cSpider_arr['jdwhatsnew.cgi']                                     = 'jdwhatsnew Link Checker';
        $cSpider_arr['jeeves']                                             = 'Ask Jeeves';
        $cSpider_arr['jennybot']                                           = 'JennyBot';
        $cSpider_arr['jobo']                                               = 'JoBo Java Web Robot';
        $cSpider_arr['jobot']                                              = 'Jobot';
        $cSpider_arr['joebot']                                             = 'JoeBot';
        $cSpider_arr['JRTS Check Favorites Utility']                       = 'Bookmark Checker';
        $cSpider_arr['JRTwine Software Check Favorites Utility']           = 'Bookmark Checker';
        $cSpider_arr['jubii']                                              = 'The Jubii Indexing Robot';
        $cSpider_arr['jumpstation']                                        = 'JumpStation';
        $cSpider_arr['justview']                                           = 'JustView';
        $cSpider_arr['jyxo']                                               = 'Jyxobot';
        $cSpider_arr['kapsi']                                              = 'image.kapsi.net';
        $cSpider_arr['katipo']                                             = 'Katipo';
        $cSpider_arr['kilroy']                                             = 'Kilroy';
        $cSpider_arr['ko_yappo_robot']                                     = 'KO_Yappo_Robot';
        $cSpider_arr['labelgrabber.txt']                                   = 'LabelGrabber';
        $cSpider_arr['Lambda LinkCheck']                                   = 'Lambda Link Checker';
        $cSpider_arr['larbin']                                             = 'larbin';
        $cSpider_arr['LARBIN-EXPERIMENTAL']                                = 'Email Collector';
        $cSpider_arr['legs']                                               = 'legs';
        $cSpider_arr['Lincoln State Web Browser']                          = 'Spam Bot';
        $cSpider_arr['Link Valet Online']                                  = 'Link Valet Online Link Checker';
        $cSpider_arr['LinkAlarm']                                          = 'LinkAlarm Link Checker';
        $cSpider_arr['Linkbot']                                            = 'Linkbot Link Checker';
        $cSpider_arr['linkbot']                                            = 'LinkBot';
        $cSpider_arr['linkchecker']                                        = 'LinkChecker';
        $cSpider_arr['linkidator']                                         = 'Link Validator';
        $cSpider_arr['LinkLint-checkonly']                                 = 'Link Checker';
        $cSpider_arr['Linkman']                                            = 'Linkman Link Checker';
        $cSpider_arr['LinkProver']                                         = 'LinkProver Link Checker';
        $cSpider_arr['Links']                                              = 'Link Checker';
        $cSpider_arr['linkscan']                                           = 'LinkScan';
        $cSpider_arr['LinkScan Server']                                    = 'LinkScan Link Checker';
        $cSpider_arr['LinkSonar/1.35']                                     = 'Link Sonar';
        $cSpider_arr['LinkSweeper']                                        = 'LinkSweeper Link Checker';
        $cSpider_arr['LinkVerify Spider']                                  = 'LinkVerify Link Checker';
        $cSpider_arr['LinkWalker']                                         = 'LinkWalker Link Checker';
        $cSpider_arr['linkwalker']                                         = 'LinkWalker';
        $cSpider_arr['lockon']                                             = 'Lockon';
        $cSpider_arr['logo_gif']                                           = 'logo.gif Crawler';
        $cSpider_arr['lycos']                                              = 'Lycos';
        $cSpider_arr['lycos_']                                             = 'Lycos';
        $cSpider_arr['Mac Finder 1.0.xx']                                  = 'MacFinder Spam Bot';
        $cSpider_arr['macworm']                                            = 'Mac WWWWorm';
        $cSpider_arr['magpie']                                             = 'Magpie';
        $cSpider_arr['marvin']                                             = 'marvin/infoseek';
        $cSpider_arr['mattie']                                             = 'Mattie';
        $cSpider_arr['mediafox']                                           = 'MediaFox';
        $cSpider_arr['mercator']                                           = 'Mercator';
        $cSpider_arr['merzscope']                                          = 'MerzScope';
        $cSpider_arr['meshexplorer']                                       = 'NEC-MeshExplorer';
        $cSpider_arr['metager-linkchecker']                                = 'MetaGer LinkChecker';
        $cSpider_arr['MetaGer-LinkChecker']                                = 'Metager.de Link Checker';
        $cSpider_arr['MFHttpScan']                                         = 'Email Extractor';
        $cSpider_arr['microsoft_url_control']                              = 'Microsoft URL Control';
        $cSpider_arr['mindcrawler']                                        = 'MindCrawler';
        $cSpider_arr['mirago']                                             = 'HenriLeRobotMirago';
        $cSpider_arr['Missauga Locate 1.0.0']                              = 'Missauga Spam Bot';
        $cSpider_arr['Missigua Locator 1.9']                               = 'Missigua Spam Bot';
        $cSpider_arr['Missouri College Browse']                            = 'Missouri Spam Bot';
        $cSpider_arr['mnogosearch']                                        = 'mnoGoSearch search engine software';
        $cSpider_arr['moget']                                              = 'moget';
        $cSpider_arr['momspider']                                          = 'MOMspider';
        $cSpider_arr['monster']                                            = 'Monster';
        $cSpider_arr['Morning Paper']                                      = 'Morning Paper Link Checker';
        $cSpider_arr['motor']                                              = 'Motor';
        $cSpider_arr['MoveAnnouncer']                                      = 'MoveAnnouncer Link Checker';
        $cSpider_arr['msiecrawler']                                        = 'MSIECrawler';
        $cSpider_arr['msnbot']                                             = 'MSNBot';
        $cSpider_arr['msnbot-academic']                                    = 'MSNBot-Academic';
        $cSpider_arr['msnbot-media']                                       = 'MSNBot-Media';
        $cSpider_arr['msnbot-newsblogs']                                   = 'MSNBot-NewsBlogs';
        $cSpider_arr['msnbot-products']                                    = 'MSNBot-Products';
        $cSpider_arr['muncher']                                            = 'Muncher';
        $cSpider_arr['muscatferret']                                       = 'Muscat Ferret';
        $cSpider_arr['mwdsearch']                                          = 'Mwd.Search';
        $cSpider_arr['myweb']                                              = 'Internet Shinchakubin';
        $cSpider_arr['nagios']                                             = 'Nagios';
        $cSpider_arr['naver']                                              = 'dloader';
        $cSpider_arr['ndspider']                                           = 'NDSpider';
        $cSpider_arr['nederland.zoek']                                     = 'Nederland.zoek';
        $cSpider_arr['netcarta']                                           = 'NetCarta WebMap Engine';
        $cSpider_arr['netcraft']                                           = 'Netcraft';
        $cSpider_arr['NetLookout']                                         = 'NetLookout Link Checker';
        $cSpider_arr['netmechanic']                                        = 'NetMechanic';
        $cSpider_arr['NetMechanic']                                        = 'NetMechanic Link Checker';
        $cSpider_arr['NetMechanic Vx.0']                                   = 'NetMechanic Link Checker';
        $cSpider_arr['NetMind-Minder']                                     = 'NetMind Link Checker';
        $cSpider_arr['NetMonitor']                                         = 'NetMonitor Link Checker';
        $cSpider_arr['Netprospector JavaCrawler']                          = 'Netprospector Link Checker';
        $cSpider_arr['netscoop']                                           = 'NetScoop';
        $cSpider_arr['newscan-online']                                     = 'newscan-online';
        $cSpider_arr['nhse']                                               = 'NHSE Web Forager';
        $cSpider_arr['nomad']                                              = 'Nomad';
        $cSpider_arr['northstar']                                          = 'The NorthStar Robot';
        $cSpider_arr['nzexplorer']                                         = 'nzexplorer';
        $cSpider_arr['objectssearch']                                      = 'ObjectsSearch';
        $cSpider_arr['occam']                                              = 'Occam';
        $cSpider_arr['octopus']                                            = 'HKU WWW Octopus';
        $cSpider_arr['omgili']                                             = 'OMGILI';
        $cSpider_arr['online link validator']                              = 'Link Checker';
        $cSpider_arr['online link validator (http://www.dead-links.com/)'] = 'Dead-Links.com Link Validation';
        $cSpider_arr['openfind']                                           = 'Openbot';
        $cSpider_arr['orb_search']                                         = 'Orb Search';
        $cSpider_arr['packrat']                                            = 'Pack Rat';
        $cSpider_arr['pageboy']                                            = 'PageBoy';
        $cSpider_arr['parasite']                                           = 'ParaSite';
        $cSpider_arr['patric']                                             = 'Patric';
        $cSpider_arr['pegasus']                                            = 'pegasus';
        $cSpider_arr['perignator']                                         = 'The Peregrinator';
        $cSpider_arr['perlcrawler']                                        = 'PerlCrawler 1.0';
        $cSpider_arr['perman']                                             = 'Perman surfer';
        $cSpider_arr['petersnews']                                         = 'Petersnews';
        $cSpider_arr['phantom']                                            = 'Phantom';
        $cSpider_arr['phpdig']                                             = 'PhpDig';
        $cSpider_arr['picsearch']                                          = 'Psbot';
        $cSpider_arr['piltdownman']                                        = 'PiltdownMan';
        $cSpider_arr['pimptrain']                                          = 'Pimptrain.com';
        $cSpider_arr['PingALink']                                          = 'Pingalink Site Monitoring';
        $cSpider_arr['PingALink Monitoring Services 1.0']                  = 'Pingalink Site Monitoring';
        $cSpider_arr['pioneer']                                            = 'Pioneer';
        $cSpider_arr['pitkow']                                             = 'html_analyzer';
        $cSpider_arr['pjspider']                                           = 'Portal Juice Spider';
        $cSpider_arr['plumtreewebaccessor']                                = 'PlumtreeWebAccessor';
        $cSpider_arr['pompos']                                             = 'Pompos';
        $cSpider_arr['poppi']                                              = 'Poppi';
        $cSpider_arr['portalb']                                            = 'PortalB Spider';
        $cSpider_arr['Program Shareware 1.0.2']                            = 'Spam Bot';
        $cSpider_arr['ProWebGuide Link Checker']                           = 'Prowebguide Link Checker';
        $cSpider_arr['psbot']                                              = 'psbot';
        $cSpider_arr['python']                                             = 'The Python Robot';
        $cSpider_arr['rambler']                                            = 'StackRambler';
        $cSpider_arr['Rational SiteCheck']                                 = 'Rational Link Checker';
        $cSpider_arr['raven']                                              = 'Raven Search';
        $cSpider_arr['rbse']                                               = 'RBSE Spider';
        $cSpider_arr['redalert']                                           = 'Red Alert';
        $cSpider_arr['resumerobot']                                        = 'Resume Robot';
        $cSpider_arr['rhcs']                                               = 'RoadHouse Crawling System';
        $cSpider_arr['road_runner']                                        = 'Road Runner: The ImageScape Robot';
        $cSpider_arr['robbie']                                             = 'Robbie the Robot';
        $cSpider_arr['robi']                                               = 'ComputingSite Robi/1.0';
        $cSpider_arr['robocrawl']                                          = 'RoboCrawl Spider';
        $cSpider_arr['robofox']                                            = 'RoboFox';
        $cSpider_arr['robot']                                              = 'Robot';
        $cSpider_arr['robozilla']                                          = 'Robozilla';
        $cSpider_arr['Robozilla']                                          = 'Robozilla Link Checker';
        $cSpider_arr['roverbot']                                           = 'Roverbot';
        $cSpider_arr['RPT-HTTPClient']                                     = 'Link Checker';
        $cSpider_arr['rules']                                              = 'RuLeS';
        $cSpider_arr['safetynetrobot']                                     = 'SafetyNet Robot';
        $cSpider_arr['Scan4Mail']                                          = 'Scan4Mail Email Extractor';
        $cSpider_arr['scooter']                                            = 'AltaVista';
        $cSpider_arr['search-info']                                        = 'Sleek';
        $cSpider_arr['search_au']                                          = 'Search.Aus-AU.COM';
        $cSpider_arr['searchprocess']                                      = 'SearchProcess';
        $cSpider_arr['semanticdiscovery/0.x']                              = 'Domain Checker';
        $cSpider_arr['senrigan']                                           = 'Senrigan';
        $cSpider_arr['sgscout']                                            = 'SG-Scout';
        $cSpider_arr['shaggy']                                             = 'ShagSeeker';
        $cSpider_arr['shaihulud']                                          = 'Shai';
        $cSpider_arr['shoutcast']                                          = 'Shoutcast Directory Service';
        $cSpider_arr['sift']                                               = 'Sift';
        $cSpider_arr['simbot']                                             = 'Simmany Robot Ver1.0';
        $cSpider_arr['site-valet']                                         = 'Site Valet';
        $cSpider_arr['sitecheck.internetseer.com']                         = 'Internetseer.com Site Monitoring';
        $cSpider_arr['sitetech']                                           = 'SiteTech-Rover';
        $cSpider_arr['skymob']                                             = 'Skymob.com';
        $cSpider_arr['slcrawler']                                          = 'SLCrawler';
        $cSpider_arr['slurp']                                              = 'Yahoo! Slurp/3.0; http://help.yahoo.com/help/us/ysearch/slurp';
        $cSpider_arr['slysearch']                                          = 'SlySearch';
        $cSpider_arr['smartspider']                                        = 'Smart Spider';
        $cSpider_arr['snooper']                                            = 'Snooper';
        $cSpider_arr['solbot']                                             = 'Solbot';
        $cSpider_arr['speedy']                                             = 'Speedy Spider';
        $cSpider_arr['spider']                                             = 'Spider';
        $cSpider_arr['spider_monkey']                                      = 'spider_monkey';
        $cSpider_arr['spiderbot']                                          = 'SpiderBot';
        $cSpider_arr['spiderline']                                         = 'Spiderline Crawler';
        $cSpider_arr['spiderman']                                          = 'SpiderMan';
        $cSpider_arr['spiderview']                                         = 'SpiderView';
        $cSpider_arr['spry']                                               = 'Spry Wizard Robot';
        $cSpider_arr['ssearcher']                                          = 'Site Searcher';
        $cSpider_arr['suke']                                               = 'Suke';
        $cSpider_arr['suntek']                                             = 'suntek search engine';
        $cSpider_arr['SurfMaster']                                         = 'SurfMaster Link Checker';
        $cSpider_arr['surveybot']                                          = 'SurveyBot';
        $cSpider_arr['sven']                                               = 'Sven';
        $cSpider_arr['SyncIT']                                             = 'SyncIT Link Checker';
        $cSpider_arr['szukacz']                                            = 'Szukacz Robot';
        $cSpider_arr['tach_bw']                                            = 'TACH Black Widow';
        $cSpider_arr['tarantula']                                          = 'Tarantula';
        $cSpider_arr['targetblaster.com/0.9k']                             = 'Targetblaster.com Link Checker';
        $cSpider_arr['tarspider']                                          = 'tarspider';
        $cSpider_arr['techbot']                                            = 'TechBOT';
        $cSpider_arr['templeton']                                          = 'Templeton';
        $cSpider_arr['teoma']                                              = 'Teoma, DirectHit';
        $cSpider_arr['The Informant']                                      = 'The Informant Link Checker';
        $cSpider_arr['The Intraformant']                                   = 'The Intraformant Link Checker';
        $cSpider_arr['titan']                                              = 'TITAN';
        $cSpider_arr['titin']                                              = 'TitIn';
        $cSpider_arr['tkwww']                                              = 'The TkWWW Robot';
        $cSpider_arr['tlspider']                                           = 'TLSpider';
        $cSpider_arr['turnitinbot']                                        = 'Turn It In';
        $cSpider_arr['turtle']                                             = 'Turtle';
        $cSpider_arr['turtlescanner']                                      = 'Turtle';
        $cSpider_arr['ucsd']                                               = 'UCSD Crawl';
        $cSpider_arr['udmsearch']                                          = 'UdmSearch';
        $cSpider_arr['ultraseek']                                          = 'Ultraseek';
        $cSpider_arr['unlost_web_crawler']                                 = 'Unlost Web Crawler';
        $cSpider_arr['urlck']                                              = 'URL Check';
        $cSpider_arr['valkyrie']                                           = 'Valkyrie';
        $cSpider_arr['verticrawl']                                         = 'Verticrawl';
        $cSpider_arr['VeryGoodSearch.com.DaddyLongLegs']                   = 'VeryGoodSearch.com Link Checker';
        $cSpider_arr['victoria']                                           = 'Victoria';
        $cSpider_arr['visionsearch']                                       = 'vision-search';
        $cSpider_arr['voidbot']                                            = 'void-bot';
        $cSpider_arr['voila']                                              = 'Voilabot';
        $cSpider_arr['voyager']                                            = 'Voyager';
        $cSpider_arr['vwbot']                                              = 'VWbot';
        $cSpider_arr['W3CRobot/5.4.0 libwww/5.4.0']                        = 'W3C Link Checker';
        $cSpider_arr['w3index']                                            = 'The NWI Robot';
        $cSpider_arr['w3m2']                                               = 'W3M2';
        $cSpider_arr['wallpaper']                                          = 'WallPaper';
        $cSpider_arr['wanderer']                                           = 'the World Wide Web Wanderer';
        $cSpider_arr['wapspider']                                          = 'w@pby wap4.com';
        $cSpider_arr['Watchfire WebXM']                                    = 'Watchfire WebXM Link Checker';
        $cSpider_arr['WatzNew Agent']                                      = 'WatzNew Link Checker';
        $cSpider_arr['webbandit']                                          = 'WebBandit Web Spider';
        $cSpider_arr['webbase']                                            = 'WebBase';
        $cSpider_arr['webcatcher']                                         = 'WebCatcher';
        $cSpider_arr['webclipping.com']                                    = 'WebClipping.com';
        $cSpider_arr['webcompass']                                         = 'webcompass';
        $cSpider_arr['webcopy']                                            = 'WebCopy';
        $cSpider_arr['webfetcher']                                         = 'webfetcher';
        $cSpider_arr['webfoot']                                            = 'The Webfoot Robot';
        $cSpider_arr['webinator']                                          = 'Webinator';
        $cSpider_arr['weblayers']                                          = 'weblayers';
        $cSpider_arr['Weblink Scanner']                                    = 'Weblink Link Checker';
        $cSpider_arr['weblinker']                                          = 'WebLinker';
        $cSpider_arr['webmirror']                                          = 'WebMirror';
        $cSpider_arr['webmoose']                                           = 'The Web Moose';
        $cSpider_arr['webquest']                                           = 'WebQuest';
        $cSpider_arr['webreader']                                          = 'Digimarc MarcSpider';
        $cSpider_arr['webreaper']                                          = 'WebReaper';
        $cSpider_arr['WebSite-Watcher']                                    = 'WebSite-Watcher Link Checker';
        $cSpider_arr['websnarf']                                           = 'Websnarf';
        $cSpider_arr['webspider']                                          = 'WebSpider';
        $cSpider_arr['WebTrends Link Analyzer']                            = 'WebTrends Link Checker';
        $cSpider_arr['webvac']                                             = 'WebVac';
        $cSpider_arr['webwalk']                                            = 'webwalk';
        $cSpider_arr['webwalker']                                          = 'WebWalker';
        $cSpider_arr['webwatch']                                           = 'WebWatch';
        $cSpider_arr['whalhello']                                          = 'appie';
        $cSpider_arr['whatuseek']                                          = 'whatUseek Winona';
        $cSpider_arr['whowhere']                                           = 'WhoWhere Robot';
        $cSpider_arr['wired-digital']                                      = 'Wired Digital';
        $cSpider_arr['wiseNut']                                            = 'ZyBorg';
        $cSpider_arr['wisenutbot']                                         = 'Looksmart';
        $cSpider_arr['WiseWire-Spider2']                                   = 'Wisewire.com Domain Checker';
        $cSpider_arr['wmir']                                               = 'w3mir';
        $cSpider_arr['wolp']                                               = 'WebStolperer';
        $cSpider_arr['wombat']                                             = 'The Web Wombat';
        $cSpider_arr['wonderer']                                           = 'Web Wombat Redback Spider';
        $cSpider_arr['worm']                                               = 'The World Wide Web Worm';
        $cSpider_arr['wuseek']                                             = 'What U Seek';
        $cSpider_arr['www.elsop.com']                                      = 'elsop.com Link Checker';
        $cSpider_arr['wwwc']                                               = 'WWWC Ver 0.2.5';
        $cSpider_arr['wz101']                                              = 'WebZinger';
        $cSpider_arr['Xenu']                                               = 'Xenu';
        $cSpider_arr['xget']                                               = 'XGET';
        $cSpider_arr['yahoo']                                              = 'Yahoo&#33; Slurp';
        $cSpider_arr['Yahoo-MMCrawler/3.x']                                = 'Yahoo Publisher Network';
        $cSpider_arr['yahoo-verticalcrawler']                              = 'Yahoo';
        $cSpider_arr['YahooYSMcm/1.0.0']                                   = 'Yahoo Publisher Network';
        $cSpider_arr['YahooYSMcm/2.0.0']                                   = 'Yahoo Publisher Network';
        $cSpider_arr['yandex']                                             = 'Yandex bot';
        $cSpider_arr['ypn-rss.overture.com']                               = 'Yahoo Publisher Network';
        $cSpider_arr['zealbot']                                            = 'ZealBot';
        $cSpider_arr['zyborg']                                             = 'Looksmart';
        $cSpider_arr['DotBot']                                             = 'DotBot/1.1 http://www.dotnetdotcom.org/ crawler@dotnetdotcom.org';
        $cSpider_arr['Baiduspider']                                        = 'Baiduspider+(+http://www.baidu.jp/spider/)';
        $cSpider_arr['Twiceler']                                           = 'Twiceler-0.9 http://www.cuil.com/twiceler/robot.html';
        $cSpider_arr['SeznamBot']                                          = 'Seznam Tschechische Suchmaschine http://www.seznam.cz/';
        $cSpider_arr['iisbot']                                             = 'MS Seo Toolkit vom IIS http://www.microsoft.com/web/spotlight/seo.aspx';

        return $cSpider_arr;
    }

    /**
     * @param null|string $userAgent
     * @return stdClass
     */
    public static function getBrowserForUserAgent($userAgent = null): stdClass
    {
        $oBrowser            = new stdClass();
        $oBrowser->nType     = 0;
        $oBrowser->bMobile   = false;
        $oBrowser->cName     = 'Unknown';
        $oBrowser->cBrowser  = 'unknown';
        $oBrowser->cPlatform = 'unknown';
        $oBrowser->cVersion  = '0';

        // agent
        $userAgent       = (isset($_SERVER['HTTP_USER_AGENT']) && $userAgent === null)
            ? $_SERVER['HTTP_USER_AGENT']
            : $userAgent;
        $oBrowser->cAgent = $userAgent;
        // mobile
        $oBrowser->bMobile = preg_match('/android|avantgo|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile' .
                '|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker' .
                '|pocket|psp|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',
                $oBrowser->cAgent,
                $cMatch_arr
            ) ||
            preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)' .
                '|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )' .
                '|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa' .
                '|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob' .
                '|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)' .
                '|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)' .
                '|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)' .
                '|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)' .
                '|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])' .
                '|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)' .
                '|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)' .
                '|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1' .
                '|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio' .
                '|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa' .
                '(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)' .
                '|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)' .
                '|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)' .
                '|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)' .
                '|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/i',
                substr($oBrowser->cAgent, 0, 4),
                $cMatch_arr
            );
        // platform
        if (preg_match('/linux/i', $userAgent)) {
            $oBrowser->cPlatform = 'linux';
        } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
            $oBrowser->cPlatform = 'mac';
        } elseif (preg_match('/windows|win32/i', $userAgent)) {
            $oBrowser->cPlatform = preg_match('/windows mobile|wce/i', $userAgent)
                ? 'mobile'
                : 'windows';
        }
        // browser
        if (preg_match('/MSIE/i', $userAgent) && !preg_match('/Opera/i', $userAgent)) {
            $oBrowser->nType    = BROWSER_MSIE;
            $oBrowser->cName    = 'Internet Explorer';
            $oBrowser->cBrowser = 'msie';
        } elseif (preg_match('/Firefox/i', $userAgent)) {
            $oBrowser->nType    = BROWSER_FIREFOX;
            $oBrowser->cName    = 'Mozilla Firefox';
            $oBrowser->cBrowser = 'firefox';
        } elseif (preg_match('/Chrome/i', $userAgent)) {
            $oBrowser->nType    = BROWSER_CHROME;
            $oBrowser->cName    = 'Google Chrome';
            $oBrowser->cBrowser = 'chrome';
        } elseif (preg_match('/Safari/i', $userAgent)) {
            $oBrowser->nType = BROWSER_SAFARI;
            if (preg_match('/iPhone/i', $userAgent)) {
                $oBrowser->cName    = 'Apple iPhone';
                $oBrowser->cBrowser = 'iphone';
            } elseif (preg_match('/iPad/i', $userAgent)) {
                $oBrowser->cName    = 'Apple iPad';
                $oBrowser->cBrowser = 'ipad';
            } elseif (preg_match('/iPod/i', $userAgent)) {
                $oBrowser->cName    = 'Apple iPod';
                $oBrowser->cBrowser = 'ipod';
            } else {
                $oBrowser->cName    = 'Apple Safari';
                $oBrowser->cBrowser = 'safari';
            }
        } elseif (preg_match('/Opera/i', $userAgent)) {
            $oBrowser->nType = BROWSER_OPERA;
            if (preg_match('/Opera Mini/i', $userAgent)) {
                $oBrowser->cName    = 'Opera Mini';
                $oBrowser->cBrowser = 'opera_mini';
            } else {
                $oBrowser->cName    = 'Opera';
                $oBrowser->cBrowser = 'opera';
            }
        } elseif (preg_match('/Netscape/i', $userAgent)) {
            $oBrowser->nType    = BROWSER_NETSCAPE;
            $oBrowser->cName    = 'Netscape';
            $oBrowser->cBrowser = 'netscape';
        }
        // version
        $cKnown   = ['version', 'other', 'mobile', $oBrowser->cBrowser];
        $cPattern = '/(?<browser>' . implode('|', $cKnown) . ')[\/ ]+(?<version>[0-9.|a-zA-Z.]*)/i';
        preg_match_all($cPattern, $userAgent, $aMatches);
        if (count($aMatches['browser']) !== 1) {
            $oBrowser->cVersion = '0';
            if (isset($aMatches['version'][0])
                && strripos($userAgent, 'Version') < strripos($userAgent, $oBrowser->cBrowser)
            ) {
                $oBrowser->cVersion = $aMatches['version'][0];
            } elseif (isset($aMatches['version'][1])) {
                $oBrowser->cVersion = $aMatches['version'][1];
            }
        } else {
            $oBrowser->cVersion = $aMatches['version'][0];
        }
        if (strlen($oBrowser->cVersion) === 0) {
            $oBrowser->cVersion = '0';
        }

        return $oBrowser;
    }
}
