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
                'UPDATE tbesucherbot SET dZeit = NOW() WHERE kBesucherBot = :_kBesucherBot',
                ['_kBesucherBot' => $kBesucherBot],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
        // cleanup `tbesucher`
        self::archive();

        $oVisitor = self::dbLookup($userAgent, RequestHelper::getIP());
        if ($oVisitor === null) {
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
            Shop::Container()->getDB()->query(
                'UPDATE tbesucherzaehler SET nZaehler = nZaehler + 1',
                \DB\ReturnType::AFFECTED_ROWS
            );
        } else {
            $oVisitor->kBesucher    = (int)$oVisitor->kBesucher;
            $oVisitor->kKunde       = (int)$oVisitor->kKunde;
            $oVisitor->kBestellung  = (int)$oVisitor->kBestellung;
            $oVisitor->kBesucherBot = (int)$oVisitor->kBesucherBot;
            // prevent counting internal redirects by counting only the next request above 3 seconds
            $iTimeDiff = (new DateTime())->getTimestamp() - (new DateTime($oVisitor->dLetzteAktivitaet))->getTimestamp();
            if ($iTimeDiff > 2) {
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
            'INSERT INTO tbesucherarchiv
            (kBesucher, cIP, kKunde, kBestellung, cReferer, cEinstiegsseite, cBrowser,
              cAusstiegsseite, nBesuchsdauer, kBesucherBot, dZeit)
            SELECT kBesucher, cIP, kKunde, kBestellung, cReferer, cEinstiegsseite, cBrowser, cAusstiegsseite,
            (UNIX_TIMESTAMP(dLetzteAktivitaet) - UNIX_TIMESTAMP(dZeit)) AS nBesuchsdauer, kBesucherBot, dZeit
              FROM tbesucher
              WHERE dLetzteAktivitaet <= DATE_SUB(NOW(), INTERVAL :interval HOUR)',
            ['interval' => $iInterval],
            \DB\ReturnType::AFFECTED_ROWS
        );
        Shop::Container()->getDB()->queryPrepared(
            'DELETE FROM tbesucher
                WHERE dLetzteAktivitaet <= DATE_SUB(NOW(), INTERVAL :interval HOUR)',
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
    public static function updateVisitorObject($vis, int $visId, $szUserAgent, int $kBesucherBot)
    {
        $vis->kBesucher         = (int)$visId;
        $vis->cIP               = (new \GeneralDataProtection\IpAnonymizer(RequestHelper::getIP()))->anonymize(); // anonymize immediately
        $vis->cSessID           = session_id();
        $vis->cID               = md5($szUserAgent . RequestHelper::getIP());
        $vis->kKunde            = \Session\Session::Customer()->getID();
        $vis->kBestellung       = $vis->kKunde > 0 ? self::refreshCustomerOrderId((int)$vis->kKunde) : 0;
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
        $vis->cIP               = (new \GeneralDataProtection\IpAnonymizer(RequestHelper::getIP()))->anonymize(); // anonymize immediately
        $vis->cSessID           = session_id();
        $vis->cID               = md5($szUserAgent . RequestHelper::getIP());
        $vis->kKunde            = \Session\Session::Customer()->getID();
        $vis->kBestellung       = $vis->kKunde > 0 ? self::refreshCustomerOrderId((int)$vis->kKunde) : 0;
        $vis->cEinstiegsseite   = $_SERVER['REQUEST_URI'];
        $vis->cReferer          = self::getReferer();
        $vis->cUserAgent        = StringHandler::filterXSS($_SERVER['HTTP_USER_AGENT']);
        $vis->cBrowser          = self::getBrowser();
        $vis->cAusstiegsseite   = $_SERVER['REQUEST_URI'];
        $vis->dLetzteAktivitaet = (new \DateTime())->format('Y-m-d H:i:s');
        $vis->dZeit             = (new \DateTime())->format('Y-m-d H:i:s');
        $vis->kBesucherBot      = $kBesucherBot;
        // store search-string from search-engine too
        if ($vis->cReferer !== '') {
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
    public static function refreshCustomerOrderId(int $nCustomerId): int
    {
        $oOrder = Shop::Container()->getDB()->queryPrepared(
            'SELECT `kBestellung`
                FROM `tbestellung`
                WHERE `kKunde` = :_nCustomerId
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
        if (!isset($_SERVER['HTTP_REFERER'])) {
            return '';
        }
        $parts = explode('/', $_SERVER['HTTP_REFERER']);

        return StringHandler::filterXSS(strtolower($parts[2]));
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
        $oBesucherBot = null;
        foreach (array_keys(self::getSpiders()) as $cBotUserAgent) {
            if (strpos($userAgent, $cBotUserAgent) !== false) {
                $oBesucherBot = Shop::Container()->getDB()->select('tbesucherbot', 'cUserAgent', $cBotUserAgent);
                break;
            }
        }

        return $oBesucherBot === null ? 0 : (int)$oBesucherBot->kBesucherBot;
    }

    /**
     * @return array
     */
    public static function getSpiders(): array
    {
        $spiders                                                       = [];
        $spiders['4anything.com LinkChecker v2.0']                     = '4Anything.com';
        $spiders['abcdatos']                                           = 'ABCdatos BotLink';
        $spiders['accoona']                                            = 'Accoona-AI-Agent';
        $spiders['Aberja Checkomat']                                   = 'Aberja.de Link Checking';
        $spiders['acme.spider']                                        = 'Acme.Spider';
        $spiders['acoon']                                              = 'Acoon';
        $spiders['AgentName/0.1 libwww-perl/5.48']                     = 'LinkoMatic.com Link Checker';
        $spiders['ahoythehomepagefinder']                              = 'Ahoy&#33;';
        $spiders['alexa']                                              = 'Alexa';
        $spiders['ALink']                                              = 'ALink Link Checker';
        $spiders['alkaline']                                           = 'Alkaline';
        $spiders['altavista']                                          = 'Scooter';
        $spiders['AMeta']                                              = 'AMeta Link Checker';
        $spiders['anthill']                                            = 'Anthill';
        $spiders['antibot']                                            = 'Antibot';
        $spiders['aport']                                              = 'Aport';
        $spiders['appie']                                              = 'Walhello Appie';
        $spiders['arachnophilia']                                      = 'Arachnophilia';
        $spiders['arale']                                              = 'Arale';
        $spiders['araneo']                                             = 'Araneo';
        $spiders['architext']                                          = 'ArchitextSpider';
        $spiders['archive_org']                                        = 'Archive_org';
        $spiders['aretha']                                             = 'Aretha';
        $spiders['ariadne']                                            = 'ARIADNE';
        $spiders['arks']                                               = 'Arks';
        $spiders['aspider']                                            = 'Aspider';
        $spiders['ASPSearch URL Checker']                              = 'ASPSearch Link Checker';
        $spiders['atn.txt']                                            = 'ATN Worldwide';
        $spiders['atomz']                                              = 'Atomz.com Search Robot';
        $spiders['atSpider/1.0']                                       = 'atEmail Extractor';
        $spiders['auresys']                                            = 'AURESYS';
        $spiders['autoemailspider']                                    = 'Auto Email Extractor';
        $spiders['awbot']                                              = 'AWBot';
        $spiders['backrub']                                            = 'BackRub';
        $spiders['bbot']                                               = 'BBot';
        $spiders['BeebwareDirectory/v0.01']                            = 'Beepware.co.uk Link Checker';
        $spiders['Big Brother']                                        = 'Big Brother Link Checker';
        $spiders['bigbrother']                                         = 'Big Brother';
        $spiders['BigBrother/1.6e']                                    = 'Big Brother Network Monitor';
        $spiders['bjaaland']                                           = 'Bjaaland';
        $spiders['blackwidow']                                         = 'BlackWidow';
        $spiders['blindekuh']                                          = 'Die Blinde Kuh';
        $spiders['BlogBot']                                            = 'BlogBot Link Checker';
        $spiders['bloodhound']                                         = 'Bloodhound';
        $spiders['BMChecker']                                          = 'BMLink Checker';
        $spiders['bobby']                                              = 'Bobby';
        $spiders['Bookmark Buddy']                                     = 'Bookmark Buddy Link Checker';
        $spiders['borg-bot']                                           = 'Borg-Bot';
        $spiders['boris']                                              = 'Boris';
        $spiders['brightnet']                                          = 'bright.net caching robot';
        $spiders['bspider']                                            = 'BSpider';
        $spiders['bumblebee']                                          = 'Bumblebee';
        $spiders['cactvschemistryspider']                              = 'CACTVS Chemistry Spider';
        $spiders['calif']                                              = 'Calif';
        $spiders['cassandra']                                          = 'Cassandra';
        $spiders['cg-eye interactive']                                 = 'HtmlHelp.com';
        $spiders['cgireader']                                          = 'Digimarc Marcspider';
        $spiders['Check&amp;Get']                                      = 'Check&amp;Get Link Checker';
        $spiders['checkbot']                                           = 'Checkbot';
        $spiders['Checkbot/1.xx LWP/5.xx']                             = 'Link Checker';
        $spiders['CheckLinks/1.x.x']                                   = 'Link Checker';
        $spiders['CheckUrl']                                           = 'NTL.com Link Checker';
        $spiders['CheckWeb']                                           = 'CheckWeb Link Checker';
        $spiders['christcrawler']                                      = 'ChristCrawler.com';
        $spiders['churl']                                              = 'churl';
        $spiders['cienciaficcion']                                     = 'cIeNcIaFiCcIoN.nEt';
        $spiders['CJ Spider/']                                         = 'Commision Junction Link Checker';
        $spiders['CNET_Snoop']                                         = 'CNET_Snoop Link Checker';
        $spiders['ColdFusion']                                         = 'Networld.com Link Checker';
        $spiders['collective']                                         = 'Collective';
        $spiders['combine']                                            = 'Combine System';
        $spiders['conceptbot']                                         = 'Conceptbot';
        $spiders['coolbot']                                            = 'CoolBot';
        $spiders['core']                                               = 'Web Core / Roots';
        $spiders['cosmos']                                             = 'XYLEME Robot';
        $spiders['crawl']                                              = 'Crawl';
        $spiders['CreativeCommons/0.06-dev']                           = 'CreativeCommons.org';
        $spiders['Crescent Internet ToolPak HTTP OLE Control v.1.0']   = 'Crescent Email Extractor';
        $spiders['cruiser']                                            = 'Internet Cruiser Robot';
        $spiders['cscrawler']                                          = 'CsCrawler';
        $spiders['CSE HTML Validator']                                 = 'CSE HTML Link Checker';
        $spiders['CurryGuide SiteScan 1.1']                            = 'CurryGuide Link Checker';
        $spiders['cusco']                                              = 'Cusco';
        $spiders['Custo x.x']                                          = 'Netwu.com Link Checker';
        $spiders['cyberspyder']                                        = 'CyberSpyder Link Test';
        $spiders['daviesbot']                                          = 'DaviesBot';
        $spiders['DeadLinkCheck/0.4.0 libwww-perl/5.xx']               = 'Dead Link Checker';
        $spiders['deepindex']                                          = 'DeepIndex';
        $spiders['Denmex websearch']                                   = 'Denmax.com Link Checker';
        $spiders['desertrealm']                                        = 'Desert Realm Spider';
        $spiders['deweb']                                              = 'DeWeb(c) Katalog/Index';
        $spiders['dienstspider']                                       = 'DienstSpider';
        $spiders['digger']                                             = 'Digger';
        $spiders['digout4u']                                           = 'Digout4u';
        $spiders['diibot']                                             = 'Digital Integrity Robot';
        $spiders['direct_hit']                                         = 'Direct Hit Grabber';
        $spiders['DISCo Watchman']                                     = 'DISCo Watchman Link Checker';
        $spiders['dnabot']                                             = 'DNAbot';
        $spiders['DoctorHTML']                                         = 'DoctorHTML Link Checker';
        $spiders['download_express']                                   = 'DownLoad Express';
        $spiders['dragonbot']                                          = 'DragonBot';
        $spiders['DRKSpider']                                          = 'DRKLink Checker';
        $spiders['dwcp']                                               = 'DWCP';
        $spiders['e-collector']                                        = 'e-collector';
        $spiders['ebiness']                                            = 'EbiNess';
        $spiders['echo']                                               = 'EchO';
        $spiders['EldoS TimelyWeb/3.x']                                = 'TimelyWeb Link Checker';
        $spiders['elfinbot']                                           = 'ELFINBOT';
        $spiders['emacs']                                              = 'Emacs-w3 Search Engine';
        $spiders['Email Extractor']                                    = 'Email Extractor';
        $spiders['EmailSiphon']                                        = 'EmailSiphon Extractor';
        $spiders['EmailWolf']                                          = 'EmailWolf Extractor';
        $spiders['EmailWolf 1.00']                                     = 'EmailWolf Extractor';
        $spiders['emcspider']                                          = 'ananzi';
        $spiders['esther']                                             = 'Esther';
        $spiders['euroseek']                                           = 'EuroSeek';
        $spiders['evliyacelebi']                                       = 'Evliya Celebi';
        $spiders['exactseek']                                          = 'ExactSeek Crawler';
        $spiders['exalead']                                            = 'Exalead';
        $spiders['exite']                                              = 'Exite';
        $spiders['ExtractorPro']                                       = 'Email Extractor';
        $spiders['ezresult']                                           = 'Ezresult';
        $spiders['fast']                                               = 'Fast Web Crawler';
        $spiders['fast-webcrawler']                                    = 'AllTheWeb';
        $spiders['fastcrawler']                                        = 'FastCrawler';
        $spiders['FavOrg']                                             = 'FavOrg Link Checker';
        $spiders['Favorites Sweeper']                                  = 'Favorites Sweeper Link Checker';
        $spiders['fdse']                                               = 'Fluid Dynamics Search Engine robot';
        $spiders['felix']                                              = 'Felix IDE';
        $spiders['ferret']                                             = 'Wild Ferret Web Hopper';
        $spiders['fetchrover']                                         = 'FetchRover';
        $spiders['fido']                                               = 'fido';
        $spiders['finnish']                                            = 'Hämähäkki';
        $spiders['fireball']                                           = 'Fireball';
        $spiders['Firstsbot']                                          = 'Firstsbot Link Checker';
        $spiders['fish']                                               = 'Fish search';
        $spiders['fouineur']                                           = 'Fouineur';
        $spiders['francoroute']                                        = 'Robot Francoroute';
        $spiders['Franklin Locator 1.8']                               = 'Franklin Spam Bot';
        $spiders['freecrawl']                                          = 'Freecrawl';
        $spiders['FreshLinks.exe']                                     = 'FreshLinks Link Checker';
        $spiders['Funnel Web Profiler']                                = 'Funnel Web Profiler Link Checker';
        $spiders['funnelweb']                                          = 'FunnelWeb';
        $spiders['gama']                                               = 'gammaSpider, FocusedCrawler';
        $spiders['gazz']                                               = 'gazz';
        $spiders['gcreep']                                             = 'GCreep';
        $spiders['GeonaBot 1.0']                                       = 'Geona Link Checker';
        $spiders['getbot']                                             = 'GetBot';
        $spiders['geturl']                                             = 'GetURL';
        $spiders['gigablast']                                          = 'Gigabot';
        $spiders['gigabot']                                            = 'GigaBot';
        $spiders['gnodspider']                                         = 'GNOD Spider';
        $spiders['golem']                                              = 'Golem';
        $spiders['feedfetcher']                                        = 'Google Feedfetcher';
        $spiders['googlebot-image']                                    = 'Google ImageBot';
        $spiders['adsbot-google']                                      = 'Google AdWords';
        $spiders['mediapartners-google']                               = 'Google AdSense';
        $spiders['googlebot']                                          = 'Google';
        $spiders['google']                                             = 'GoogleBot';
        $spiders['grapnel']                                            = 'Grapnel';
        $spiders['griffon']                                            = 'Griffon';
        $spiders['gromit']                                             = 'Gromit';
        $spiders['grub']                                               = 'Grub.org';
        $spiders['gulliver']                                           = 'Northern Light Gulliver';
        $spiders['gulperbot']                                          = 'Gulper Bot';
        $spiders['hambot']                                             = 'HamBot';
        $spiders['harvest']                                            = 'Harvest';
        $spiders['Haste/0.12']                                         = 'Haste Site Monitoring';
        $spiders['havindex']                                           = 'havIndex';
        $spiders['henrythemiragorobot']                                = 'Mirago';
        $spiders['holmes']                                             = 'Holmes';
        $spiders['hometown']                                           = 'Hometown Pro';
        $spiders['htdig']                                              = 'httDig';
        $spiders['Html Link Validator']                                = 'Html Link Checker';
        $spiders['htmlgobble']                                         = 'HTMLgobble';
        $spiders['hyperdecontextualizer']                              = 'Hyper-Decontextualizer';
        $spiders['ia_archiver']                                        = 'Alexa';
        $spiders['iajabot']                                            = 'iajaBot';
        $spiders['iconoclast']                                         = 'Popular Iconoclast';
        $spiders['IconSurf/2.0']                                       = 'FavIcon Finder';
        $spiders['IEFav172Free']                                       = 'Favorites Link Checker';
        $spiders['ilse']                                               = 'Ingrid';
        $spiders['imagelock']                                          = 'Imagelock';
        $spiders['incywincy']                                          = 'IncyWincy';
        $spiders['Industry Program 1.0.x']                             = 'Industry Spam Bot';
        $spiders['InfoLink/1.x']                                       = 'InfoLink Link Checker';
        $spiders['informant']                                          = 'Informant';
        $spiders['infoseek']                                           = 'InfoSeek Robot 1.0';
        $spiders['infoseeksidewinder']                                 = 'Infoseek Sidewinder';
        $spiders['infospider']                                         = 'InfoSpiders';
        $spiders['inktomi']                                            = 'Slurp';
        $spiders['inspectorwww']                                       = 'Inspector Web';
        $spiders['intelliagent']                                       = 'IntelliAgent';
        $spiders['InternetLinkAgent']                                  = 'Internet Link Checker';
        $spiders['InternetPeriscope']                                  = 'InternetPeriscope Link Checker';
        $spiders['internetseer']                                       = 'InternetSeer';
        $spiders['irobot']                                             = 'I, Robot';
        $spiders['iron33']                                             = 'Iron33';
        $spiders['israelisearch']                                      = 'Israeli-search';
        $spiders['IUPUI Research Bot v 1.9a']                          = 'Spam Bot';
        $spiders['javabee']                                            = 'JavaBee';
        $spiders['javElink']                                           = 'javElink Link Checker';
        $spiders['jbot']                                               = 'JBot Java Web Robot';
        $spiders['JCheckLinks/0.1 RPT-HTTPClient/0.3-1']               = 'JCheckLinks Link Checker';
        $spiders['jcrawler']                                           = 'JCrawler';
        $spiders['jdwhatsnew.cgi']                                     = 'jdwhatsnew Link Checker';
        $spiders['jeeves']                                             = 'Ask Jeeves';
        $spiders['jennybot']                                           = 'JennyBot';
        $spiders['jobo']                                               = 'JoBo Java Web Robot';
        $spiders['jobot']                                              = 'Jobot';
        $spiders['joebot']                                             = 'JoeBot';
        $spiders['JRTS Check Favorites Utility']                       = 'Bookmark Checker';
        $spiders['JRTwine Software Check Favorites Utility']           = 'Bookmark Checker';
        $spiders['jubii']                                              = 'The Jubii Indexing Robot';
        $spiders['jumpstation']                                        = 'JumpStation';
        $spiders['justview']                                           = 'JustView';
        $spiders['jyxo']                                               = 'Jyxobot';
        $spiders['kapsi']                                              = 'image.kapsi.net';
        $spiders['katipo']                                             = 'Katipo';
        $spiders['kilroy']                                             = 'Kilroy';
        $spiders['ko_yappo_robot']                                     = 'KO_Yappo_Robot';
        $spiders['labelgrabber.txt']                                   = 'LabelGrabber';
        $spiders['Lambda LinkCheck']                                   = 'Lambda Link Checker';
        $spiders['larbin']                                             = 'larbin';
        $spiders['LARBIN-EXPERIMENTAL']                                = 'Email Collector';
        $spiders['legs']                                               = 'legs';
        $spiders['Lincoln State Web Browser']                          = 'Spam Bot';
        $spiders['Link Valet Online']                                  = 'Link Valet Online Link Checker';
        $spiders['LinkAlarm']                                          = 'LinkAlarm Link Checker';
        $spiders['Linkbot']                                            = 'Linkbot Link Checker';
        $spiders['linkbot']                                            = 'LinkBot';
        $spiders['linkchecker']                                        = 'LinkChecker';
        $spiders['linkidator']                                         = 'Link Validator';
        $spiders['LinkLint-checkonly']                                 = 'Link Checker';
        $spiders['Linkman']                                            = 'Linkman Link Checker';
        $spiders['LinkProver']                                         = 'LinkProver Link Checker';
        $spiders['Links']                                              = 'Link Checker';
        $spiders['linkscan']                                           = 'LinkScan';
        $spiders['LinkScan Server']                                    = 'LinkScan Link Checker';
        $spiders['LinkSonar/1.35']                                     = 'Link Sonar';
        $spiders['LinkSweeper']                                        = 'LinkSweeper Link Checker';
        $spiders['LinkVerify Spider']                                  = 'LinkVerify Link Checker';
        $spiders['LinkWalker']                                         = 'LinkWalker Link Checker';
        $spiders['linkwalker']                                         = 'LinkWalker';
        $spiders['lockon']                                             = 'Lockon';
        $spiders['logo_gif']                                           = 'logo.gif Crawler';
        $spiders['lycos']                                              = 'Lycos';
        $spiders['lycos_']                                             = 'Lycos';
        $spiders['Mac Finder 1.0.xx']                                  = 'MacFinder Spam Bot';
        $spiders['macworm']                                            = 'Mac WWWWorm';
        $spiders['magpie']                                             = 'Magpie';
        $spiders['marvin']                                             = 'marvin/infoseek';
        $spiders['mattie']                                             = 'Mattie';
        $spiders['mediafox']                                           = 'MediaFox';
        $spiders['mercator']                                           = 'Mercator';
        $spiders['merzscope']                                          = 'MerzScope';
        $spiders['meshexplorer']                                       = 'NEC-MeshExplorer';
        $spiders['metager-linkchecker']                                = 'MetaGer LinkChecker';
        $spiders['MetaGer-LinkChecker']                                = 'Metager.de Link Checker';
        $spiders['MFHttpScan']                                         = 'Email Extractor';
        $spiders['microsoft_url_control']                              = 'Microsoft URL Control';
        $spiders['mindcrawler']                                        = 'MindCrawler';
        $spiders['mirago']                                             = 'HenriLeRobotMirago';
        $spiders['Missauga Locate 1.0.0']                              = 'Missauga Spam Bot';
        $spiders['Missigua Locator 1.9']                               = 'Missigua Spam Bot';
        $spiders['Missouri College Browse']                            = 'Missouri Spam Bot';
        $spiders['mnogosearch']                                        = 'mnoGoSearch search engine software';
        $spiders['moget']                                              = 'moget';
        $spiders['momspider']                                          = 'MOMspider';
        $spiders['monster']                                            = 'Monster';
        $spiders['Morning Paper']                                      = 'Morning Paper Link Checker';
        $spiders['motor']                                              = 'Motor';
        $spiders['MoveAnnouncer']                                      = 'MoveAnnouncer Link Checker';
        $spiders['msiecrawler']                                        = 'MSIECrawler';
        $spiders['msnbot']                                             = 'MSNBot';
        $spiders['msnbot-academic']                                    = 'MSNBot-Academic';
        $spiders['msnbot-media']                                       = 'MSNBot-Media';
        $spiders['msnbot-newsblogs']                                   = 'MSNBot-NewsBlogs';
        $spiders['msnbot-products']                                    = 'MSNBot-Products';
        $spiders['muncher']                                            = 'Muncher';
        $spiders['muscatferret']                                       = 'Muscat Ferret';
        $spiders['mwdsearch']                                          = 'Mwd.Search';
        $spiders['myweb']                                              = 'Internet Shinchakubin';
        $spiders['nagios']                                             = 'Nagios';
        $spiders['naver']                                              = 'dloader';
        $spiders['ndspider']                                           = 'NDSpider';
        $spiders['nederland.zoek']                                     = 'Nederland.zoek';
        $spiders['netcarta']                                           = 'NetCarta WebMap Engine';
        $spiders['netcraft']                                           = 'Netcraft';
        $spiders['NetLookout']                                         = 'NetLookout Link Checker';
        $spiders['netmechanic']                                        = 'NetMechanic';
        $spiders['NetMechanic']                                        = 'NetMechanic Link Checker';
        $spiders['NetMechanic Vx.0']                                   = 'NetMechanic Link Checker';
        $spiders['NetMind-Minder']                                     = 'NetMind Link Checker';
        $spiders['NetMonitor']                                         = 'NetMonitor Link Checker';
        $spiders['Netprospector JavaCrawler']                          = 'Netprospector Link Checker';
        $spiders['netscoop']                                           = 'NetScoop';
        $spiders['newscan-online']                                     = 'newscan-online';
        $spiders['nhse']                                               = 'NHSE Web Forager';
        $spiders['nomad']                                              = 'Nomad';
        $spiders['northstar']                                          = 'The NorthStar Robot';
        $spiders['nzexplorer']                                         = 'nzexplorer';
        $spiders['objectssearch']                                      = 'ObjectsSearch';
        $spiders['occam']                                              = 'Occam';
        $spiders['octopus']                                            = 'HKU WWW Octopus';
        $spiders['omgili']                                             = 'OMGILI';
        $spiders['online link validator']                              = 'Link Checker';
        $spiders['online link validator (http://www.dead-links.com/)'] = 'Dead-Links.com Link Validation';
        $spiders['openfind']                                           = 'Openbot';
        $spiders['orb_search']                                         = 'Orb Search';
        $spiders['packrat']                                            = 'Pack Rat';
        $spiders['pageboy']                                            = 'PageBoy';
        $spiders['parasite']                                           = 'ParaSite';
        $spiders['patric']                                             = 'Patric';
        $spiders['pegasus']                                            = 'pegasus';
        $spiders['perignator']                                         = 'The Peregrinator';
        $spiders['perlcrawler']                                        = 'PerlCrawler 1.0';
        $spiders['perman']                                             = 'Perman surfer';
        $spiders['petersnews']                                         = 'Petersnews';
        $spiders['phantom']                                            = 'Phantom';
        $spiders['phpdig']                                             = 'PhpDig';
        $spiders['picsearch']                                          = 'Psbot';
        $spiders['piltdownman']                                        = 'PiltdownMan';
        $spiders['pimptrain']                                          = 'Pimptrain.com';
        $spiders['PingALink']                                          = 'Pingalink Site Monitoring';
        $spiders['PingALink Monitoring Services 1.0']                  = 'Pingalink Site Monitoring';
        $spiders['pioneer']                                            = 'Pioneer';
        $spiders['pitkow']                                             = 'html_analyzer';
        $spiders['pjspider']                                           = 'Portal Juice Spider';
        $spiders['plumtreewebaccessor']                                = 'PlumtreeWebAccessor';
        $spiders['pompos']                                             = 'Pompos';
        $spiders['poppi']                                              = 'Poppi';
        $spiders['portalb']                                            = 'PortalB Spider';
        $spiders['Program Shareware 1.0.2']                            = 'Spam Bot';
        $spiders['ProWebGuide Link Checker']                           = 'Prowebguide Link Checker';
        $spiders['psbot']                                              = 'psbot';
        $spiders['python']                                             = 'The Python Robot';
        $spiders['rambler']                                            = 'StackRambler';
        $spiders['Rational SiteCheck']                                 = 'Rational Link Checker';
        $spiders['raven']                                              = 'Raven Search';
        $spiders['rbse']                                               = 'RBSE Spider';
        $spiders['redalert']                                           = 'Red Alert';
        $spiders['resumerobot']                                        = 'Resume Robot';
        $spiders['rhcs']                                               = 'RoadHouse Crawling System';
        $spiders['road_runner']                                        = 'Road Runner: The ImageScape Robot';
        $spiders['robbie']                                             = 'Robbie the Robot';
        $spiders['robi']                                               = 'ComputingSite Robi/1.0';
        $spiders['robocrawl']                                          = 'RoboCrawl Spider';
        $spiders['robofox']                                            = 'RoboFox';
        $spiders['robot']                                              = 'Robot';
        $spiders['robozilla']                                          = 'Robozilla';
        $spiders['Robozilla']                                          = 'Robozilla Link Checker';
        $spiders['roverbot']                                           = 'Roverbot';
        $spiders['RPT-HTTPClient']                                     = 'Link Checker';
        $spiders['rules']                                              = 'RuLeS';
        $spiders['safetynetrobot']                                     = 'SafetyNet Robot';
        $spiders['Scan4Mail']                                          = 'Scan4Mail Email Extractor';
        $spiders['scooter']                                            = 'AltaVista';
        $spiders['search-info']                                        = 'Sleek';
        $spiders['search_au']                                          = 'Search.Aus-AU.COM';
        $spiders['searchprocess']                                      = 'SearchProcess';
        $spiders['semanticdiscovery/0.x']                              = 'Domain Checker';
        $spiders['senrigan']                                           = 'Senrigan';
        $spiders['sgscout']                                            = 'SG-Scout';
        $spiders['shaggy']                                             = 'ShagSeeker';
        $spiders['shaihulud']                                          = 'Shai';
        $spiders['shoutcast']                                          = 'Shoutcast Directory Service';
        $spiders['sift']                                               = 'Sift';
        $spiders['simbot']                                             = 'Simmany Robot Ver1.0';
        $spiders['site-valet']                                         = 'Site Valet';
        $spiders['sitecheck.internetseer.com']                         = 'Internetseer.com Site Monitoring';
        $spiders['sitetech']                                           = 'SiteTech-Rover';
        $spiders['skymob']                                             = 'Skymob.com';
        $spiders['slcrawler']                                          = 'SLCrawler';
        $spiders['slurp']                                              = 'Yahoo! Slurp/3.0; http://help.yahoo.com/help/us/ysearch/slurp';
        $spiders['slysearch']                                          = 'SlySearch';
        $spiders['smartspider']                                        = 'Smart Spider';
        $spiders['snooper']                                            = 'Snooper';
        $spiders['solbot']                                             = 'Solbot';
        $spiders['speedy']                                             = 'Speedy Spider';
        $spiders['spider']                                             = 'Spider';
        $spiders['spider_monkey']                                      = 'spider_monkey';
        $spiders['spiderbot']                                          = 'SpiderBot';
        $spiders['spiderline']                                         = 'Spiderline Crawler';
        $spiders['spiderman']                                          = 'SpiderMan';
        $spiders['spiderview']                                         = 'SpiderView';
        $spiders['spry']                                               = 'Spry Wizard Robot';
        $spiders['ssearcher']                                          = 'Site Searcher';
        $spiders['suke']                                               = 'Suke';
        $spiders['suntek']                                             = 'suntek search engine';
        $spiders['SurfMaster']                                         = 'SurfMaster Link Checker';
        $spiders['surveybot']                                          = 'SurveyBot';
        $spiders['sven']                                               = 'Sven';
        $spiders['SyncIT']                                             = 'SyncIT Link Checker';
        $spiders['szukacz']                                            = 'Szukacz Robot';
        $spiders['tach_bw']                                            = 'TACH Black Widow';
        $spiders['tarantula']                                          = 'Tarantula';
        $spiders['targetblaster.com/0.9k']                             = 'Targetblaster.com Link Checker';
        $spiders['tarspider']                                          = 'tarspider';
        $spiders['techbot']                                            = 'TechBOT';
        $spiders['templeton']                                          = 'Templeton';
        $spiders['teoma']                                              = 'Teoma, DirectHit';
        $spiders['The Informant']                                      = 'The Informant Link Checker';
        $spiders['The Intraformant']                                   = 'The Intraformant Link Checker';
        $spiders['titan']                                              = 'TITAN';
        $spiders['titin']                                              = 'TitIn';
        $spiders['tkwww']                                              = 'The TkWWW Robot';
        $spiders['tlspider']                                           = 'TLSpider';
        $spiders['turnitinbot']                                        = 'Turn It In';
        $spiders['turtle']                                             = 'Turtle';
        $spiders['turtlescanner']                                      = 'Turtle';
        $spiders['ucsd']                                               = 'UCSD Crawl';
        $spiders['udmsearch']                                          = 'UdmSearch';
        $spiders['ultraseek']                                          = 'Ultraseek';
        $spiders['unlost_web_crawler']                                 = 'Unlost Web Crawler';
        $spiders['urlck']                                              = 'URL Check';
        $spiders['valkyrie']                                           = 'Valkyrie';
        $spiders['verticrawl']                                         = 'Verticrawl';
        $spiders['VeryGoodSearch.com.DaddyLongLegs']                   = 'VeryGoodSearch.com Link Checker';
        $spiders['victoria']                                           = 'Victoria';
        $spiders['visionsearch']                                       = 'vision-search';
        $spiders['voidbot']                                            = 'void-bot';
        $spiders['voila']                                              = 'Voilabot';
        $spiders['voyager']                                            = 'Voyager';
        $spiders['vwbot']                                              = 'VWbot';
        $spiders['W3CRobot/5.4.0 libwww/5.4.0']                        = 'W3C Link Checker';
        $spiders['w3index']                                            = 'The NWI Robot';
        $spiders['w3m2']                                               = 'W3M2';
        $spiders['wallpaper']                                          = 'WallPaper';
        $spiders['wanderer']                                           = 'the World Wide Web Wanderer';
        $spiders['wapspider']                                          = 'w@pby wap4.com';
        $spiders['Watchfire WebXM']                                    = 'Watchfire WebXM Link Checker';
        $spiders['WatzNew Agent']                                      = 'WatzNew Link Checker';
        $spiders['webbandit']                                          = 'WebBandit Web Spider';
        $spiders['webbase']                                            = 'WebBase';
        $spiders['webcatcher']                                         = 'WebCatcher';
        $spiders['webclipping.com']                                    = 'WebClipping.com';
        $spiders['webcompass']                                         = 'webcompass';
        $spiders['webcopy']                                            = 'WebCopy';
        $spiders['webfetcher']                                         = 'webfetcher';
        $spiders['webfoot']                                            = 'The Webfoot Robot';
        $spiders['webinator']                                          = 'Webinator';
        $spiders['weblayers']                                          = 'weblayers';
        $spiders['Weblink Scanner']                                    = 'Weblink Link Checker';
        $spiders['weblinker']                                          = 'WebLinker';
        $spiders['webmirror']                                          = 'WebMirror';
        $spiders['webmoose']                                           = 'The Web Moose';
        $spiders['webquest']                                           = 'WebQuest';
        $spiders['webreader']                                          = 'Digimarc MarcSpider';
        $spiders['webreaper']                                          = 'WebReaper';
        $spiders['WebSite-Watcher']                                    = 'WebSite-Watcher Link Checker';
        $spiders['websnarf']                                           = 'Websnarf';
        $spiders['webspider']                                          = 'WebSpider';
        $spiders['WebTrends Link Analyzer']                            = 'WebTrends Link Checker';
        $spiders['webvac']                                             = 'WebVac';
        $spiders['webwalk']                                            = 'webwalk';
        $spiders['webwalker']                                          = 'WebWalker';
        $spiders['webwatch']                                           = 'WebWatch';
        $spiders['whalhello']                                          = 'appie';
        $spiders['whatuseek']                                          = 'whatUseek Winona';
        $spiders['whowhere']                                           = 'WhoWhere Robot';
        $spiders['wired-digital']                                      = 'Wired Digital';
        $spiders['wiseNut']                                            = 'ZyBorg';
        $spiders['wisenutbot']                                         = 'Looksmart';
        $spiders['WiseWire-Spider2']                                   = 'Wisewire.com Domain Checker';
        $spiders['wmir']                                               = 'w3mir';
        $spiders['wolp']                                               = 'WebStolperer';
        $spiders['wombat']                                             = 'The Web Wombat';
        $spiders['wonderer']                                           = 'Web Wombat Redback Spider';
        $spiders['worm']                                               = 'The World Wide Web Worm';
        $spiders['wuseek']                                             = 'What U Seek';
        $spiders['www.elsop.com']                                      = 'elsop.com Link Checker';
        $spiders['wwwc']                                               = 'WWWC Ver 0.2.5';
        $spiders['wz101']                                              = 'WebZinger';
        $spiders['Xenu']                                               = 'Xenu';
        $spiders['xget']                                               = 'XGET';
        $spiders['yahoo']                                              = 'Yahoo&#33; Slurp';
        $spiders['Yahoo-MMCrawler/3.x']                                = 'Yahoo Publisher Network';
        $spiders['yahoo-verticalcrawler']                              = 'Yahoo';
        $spiders['YahooYSMcm/1.0.0']                                   = 'Yahoo Publisher Network';
        $spiders['YahooYSMcm/2.0.0']                                   = 'Yahoo Publisher Network';
        $spiders['yandex']                                             = 'Yandex bot';
        $spiders['ypn-rss.overture.com']                               = 'Yahoo Publisher Network';
        $spiders['zealbot']                                            = 'ZealBot';
        $spiders['zyborg']                                             = 'Looksmart';
        $spiders['DotBot']                                             = 'DotBot/1.1 http://www.dotnetdotcom.org/ crawler@dotnetdotcom.org';
        $spiders['Baiduspider']                                        = 'Baiduspider+(+http://www.baidu.jp/spider/)';
        $spiders['Twiceler']                                           = 'Twiceler-0.9 http://www.cuil.com/twiceler/robot.html';
        $spiders['SeznamBot']                                          = 'Seznam Tschechische Suchmaschine http://www.seznam.cz/';
        $spiders['iisbot']                                             = 'MS Seo Toolkit vom IIS http://www.microsoft.com/web/spotlight/seo.aspx';

        return $spiders;
    }

    /**
     * @param null|string $userAgent
     * @return stdClass
     */
    public static function getBrowserForUserAgent($userAgent = null): stdClass
    {
        $userAgent           = (isset($_SERVER['HTTP_USER_AGENT']) && $userAgent === null)
            ? $_SERVER['HTTP_USER_AGENT']
            : $userAgent;
        $oBrowser            = new stdClass();
        $oBrowser->nType     = 0;
        $oBrowser->bMobile   = false;
        $oBrowser->cName     = 'Unknown';
        $oBrowser->cBrowser  = 'unknown';
        $oBrowser->cPlatform = 'unknown';
        $oBrowser->cVersion  = '0';

        $oBrowser->cAgent  = $userAgent;
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
