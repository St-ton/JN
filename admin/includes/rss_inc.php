<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\URL;

/**
 * @return bool
 */
function generiereRSSXML()
{
    Shop::Container()->getLogService()->debug('RSS wird erstellt');
    if (!is_writable(PFAD_ROOT . FILE_RSS_FEED)) {
        Shop::Container()->getLogService()->error(
            'RSS Verzeichnis ' . PFAD_ROOT . FILE_RSS_FEED . 'nicht beschreibbar!'
        );

        return false;
    }
    $shopURL = Shop::getURL();
    $db      = Shop::Container()->getDB();
    $conf    = Shop::getSettings([CONF_RSS]);
    if ($conf['rss']['rss_nutzen'] !== 'Y') {
        return false;
    }
    $Sprache                 = $db->select('tsprache', 'cShopStandard', 'Y');
    $stdKundengruppe         = $db->select('tkundengruppe', 'cStandard', 'Y');
    $_SESSION['kSprache']    = (int)$Sprache->kSprache;
    $_SESSION['cISOSprache'] = $Sprache->cISO;
    // ISO-8859-1
    $xml = '<?xml version="1.0" encoding="' . JTL_CHARSET . '"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		<title>' . $conf['rss']['rss_titel'] . '</title>
		<link>' . $shopURL . '</link>
		<description>' . $conf['rss']['rss_description'] . '</description>
		<language>' . StringHandler::convertISO2ISO639($Sprache->cISO) . '</language>
		<copyright>' . $conf['rss']['rss_copyright'] . '</copyright>
		<pubDate>' . date('r') . '</pubDate>
		<atom:link href="' . $shopURL . '/rss.xml" rel="self" type="application/rss+xml" />
		<image>
			<url>' . $conf['rss']['rss_logoURL'] . '</url>
			<title>' . $conf['rss']['rss_titel'] . '</title>
			<link>' . $shopURL . '</link>
		</image>';
    //Artikel STD Sprache
    $lagerfilter = Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
    $days        = (int)$conf['rss']['rss_alterTage'];
    if (!$days) {
        $days = 14;
    }
    // Artikel beachten?
    if ($conf['rss']['rss_artikel_beachten'] === 'Y') {
        $products = $db->query(
            "SELECT tartikel.kArtikel, tartikel.cName, tartikel.cKurzBeschreibung, tseo.cSeo, 
                tartikel.dLetzteAktualisierung, tartikel.dErstellt, 
                DATE_FORMAT(tartikel.dErstellt, \"%a, %d %b %Y %H:%i:%s UTC\") AS erstellt
                FROM tartikel
                LEFT JOIN tartikelsichtbarkeit 
                    ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = " . $stdKundengruppe->kKundengruppe . "
                LEFT JOIN tseo 
                    ON tseo.cKey = 'kArtikel'
                    AND tseo.kKey = tartikel.kArtikel
                    AND tseo.kSprache = " . (int)$_SESSION['kSprache'] . "
                WHERE tartikelsichtbarkeit.kArtikel IS NULL
                    AND tartikel.cNeu = 'Y' " .  $lagerfilter . "
                    AND cNeu = 'Y' 
                    AND DATE_SUB(now(), INTERVAL " . $days . ' DAY) < dErstellt
                ORDER BY dLetzteAktualisierung DESC',
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($products as $artikel) {
            $url  = URL::buildURL($artikel, URLART_ARTIKEL, true);
            $xml .= '
        <item>
            <title>' . wandelXMLEntitiesUm($artikel->cName) . '</title>
            <description>' . wandelXMLEntitiesUm($artikel->cKurzBeschreibung) . '</description>
            <link>' . $url . '</link>
            <guid>' . $url . '</guid>
            <pubDate>' . bauerfc2822datum($artikel->dLetzteAktualisierung) . '</pubDate>
        </item>';
        }
    }
    // News beachten?
    if ($conf['rss']['rss_news_beachten'] === 'Y') {
        $news = $db->query(
            "SELECT tnews.*, t.title, t.preview, DATE_FORMAT(dGueltigVon, '%a, %d %b %Y %H:%i:%s UTC') AS dErstellt_RSS
                FROM tnews
                JOIN tnewssprache t 
                    ON tnews.kNews = t.kNews
                WHERE DATE_SUB(now(), INTERVAL " . $days . ' DAY) < dGueltigVon
                    AND nAktiv = 1
                    AND dGueltigVon <= now()
                ORDER BY dGueltigVon DESC',
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($news as $item) {
            $url  = URL::buildURL($item, URLART_NEWS);
            $xml .= '
        <item>p
            <title>' . wandelXMLEntitiesUm($item->title) . '</title>
            <description>' . wandelXMLEntitiesUm($item->preview) . '</description>
            <link>' . $url . '</link>
            <guid>' . $url . '</guid>
            <pubDate>' . bauerfc2822datum($item->dGueltigVon) . '</pubDate>
        </item>';
        }
    }
    // bewertungen beachten?
    if ($conf['rss']['rss_bewertungen_beachten'] === 'Y') {
        $oBewertung_arr = $db->query(
            "SELECT *, dDatum, DATE_FORMAT(dDatum, '%a, %d %b %y %h:%i:%s +0100') AS dErstellt_RSS
                FROM tbewertung
                WHERE DATE_SUB(NOW(), INTERVAL " . $days . ' DAY) < dDatum
                    AND nAktiv = 1',
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($oBewertung_arr as $oBewertung) {
            $url  = URL::buildURL($oBewertung, URLART_ARTIKEL, true);
            $xml .= '
        <item>
            <title>Bewertung ' . wandelXMLEntitiesUm($oBewertung->cTitel) . ' von ' .
                wandelXMLEntitiesUm($oBewertung->cName) . '</title>
            <description>' . wandelXMLEntitiesUm($oBewertung->cText) . '</description>
            <link>' . $url . '</link>
            <guid>' . $url . '</guid>
            <pubDate>' . bauerfc2822datum($oBewertung->dDatum) . '</pubDate>
        </item>';
        }
    }

    $xml .= '
	</channel>
</rss>
		';

    $file = fopen(PFAD_ROOT . FILE_RSS_FEED, 'w+');
    fwrite($file, $xml);
    fclose($file);

    return true;
}

/**
 * @param string $dErstellt
 * @return bool|string
 */
function bauerfc2822datum($dErstellt)
{
    return mb_strlen($dErstellt) > 0
        ? (new DateTime($dErstellt))->format(DATE_RSS)
        : false;
}

/**
 * @param string $cText
 * @return string
 */
function wandelXMLEntitiesUm($cText)
{
    return mb_strlen($cText) > 0
        ? '<![CDATA[ ' . StringHandler::htmlentitydecode($cText) . ' ]]>'
        : '';
}
