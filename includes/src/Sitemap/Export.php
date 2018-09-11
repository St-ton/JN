<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Sitemap;

use DB\DbInterface;
use Psr\Log\LoggerInterface;
use Sitemap\Factories\Attribute;
use Sitemap\Factories\Base;
use Sitemap\Factories\Category;
use Sitemap\Factories\LiveSearch;
use Sitemap\Factories\Manufacturer;
use Sitemap\Factories\NewsCategory;
use Sitemap\Factories\NewsItem;
use Sitemap\Factories\Page;
use Sitemap\Factories\Product;
use Sitemap\Factories\Tag;
use Sitemap\Items\ItemInterface;
use Sitemap\Renderes\DefaultRenderer;

/**
 * Class Export
 * @package Sitemap
 */
class Export
{
    private const EXPORT_DIR = \PFAD_ROOT . \PFAD_EXPORT;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $config;

    /**
     * Export constructor.
     * @param DbInterface     $db
     * @param LoggerInterface $logger
     * @param array           $config
     */
    public function __construct(DbInterface $db, LoggerInterface $logger, array $config)
    {
        $this->db     = $db;
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     *
     */
    public function generate(): void
    {
        $this->logger->debug('Sitemap wird erstellt');
        $timeStart = \microtime(true);
        // W3C Datetime formats:
        //  YYYY-MM-DD (eg 1997-07-16)
        //  YYYY-MM-DDThh:mmTZD (eg 1997-07-16T19:20+01:00)
        $defaultCustomerGroupID  = \Kundengruppe::getDefaultGroupID();
        $languages               = \Sprache::getAllLanguages();
        $defaultLang             = \Sprache::getDefaultLanguage(true);
        $defaultLangID           = (int)$defaultLang->kSprache;
        $_SESSION['kSprache']    = $defaultLangID;
        $_SESSION['cISOSprache'] = $defaultLang->cISO;
        \TaxHelper::setTaxRates();
        if (!isset($_SESSION['Kundengruppe'])) {
            $_SESSION['Kundengruppe'] = new \Kundengruppe();
        }
        $_SESSION['Kundengruppe']->setID($defaultCustomerGroupID);
        $nStat_arr = [
            'artikel'          => 0,
            'artikelbild'      => 0,
            'artikelsprache'   => 0,
            'link'             => 0,
            'kategorie'        => 0,
            'kategoriesprache' => 0,
            'tag'              => 0,
            'tagsprache'       => 0,
            'hersteller'       => 0,
            'livesuche'        => 0,
            'livesuchesprache' => 0,
            'merkmal'          => 0,
            'merkmalsprache'   => 0,
            'news'             => 0,
            'newskategorie'    => 0,
        ];

        $fileNumber    = 0;
        $nSitemap      = 1;
        $factories     = [];
        $urlCounts     = [0 => 0];
        $nSitemapLimit = 25000;
        $res           = '';
        $cache         = \Shop::Container()->getCache();
        $baseImageURL  = \Shop::getImageBaseURL();
        $baseURL       = \Shop::getURL() . '/';

        $this->deleteFiles();

        $urlGenerator = new URLGenerator($this->db, $cache, $this->config, $baseURL);
//        \Shop::dbg($urlGenerator->getExportURL(1, 'kKategorie', $languages, 1));
        $renderer = new DefaultRenderer($this->config, $baseURL);

        $factories[] = new Base($this->db, $this->config, $baseURL, $baseImageURL);
        $factories[] = new Product($this->db, $this->config, $baseURL, $baseImageURL);
        $factories[] = new Page($this->db, $this->config, $baseURL, $baseImageURL);
        $factories[] = new Category($this->db, $this->config, $baseURL, $baseImageURL);
        $factories[] = new Tag($this->db, $this->config, $baseURL, $baseImageURL);
        $factories[] = new Manufacturer($this->db, $this->config, $baseURL, $baseImageURL);
        $factories[] = new LiveSearch($this->db, $this->config, $baseURL, $baseImageURL);
        $factories[] = new Attribute($this->db, $this->config, $baseURL, $baseImageURL);
        $factories[] = new NewsItem($this->db, $this->config, $baseURL, $baseImageURL);
        $factories[] = new NewsCategory($this->db, $this->config, $baseURL, $baseImageURL);

        \executeHook(\HOOK_SITEMAP_EXPORT_GET_FACTORIES, [
            'factories' => &$factories,
            'exporter'  => $this
        ]);

        foreach ($factories as $factory) {
            $collection = $factory->getCollection($languages, [$defaultCustomerGroupID]);
            foreach ($collection as $item) {
                /** @var ItemInterface $item */
                if ($nSitemap > $nSitemapLimit) {
                    $nSitemap = 1;
                    $this->buildFile($fileNumber, $res);
                    ++$fileNumber;
                    $urlCounts[$fileNumber] = 0;
                    $res                    = '';
                }
                $cUrl = $item->getLocation();
                if (!$this->isURLBlocked($cUrl)) {
                    $res .= $renderer->renderItem($item);
                    ++$nSitemap;
                    ++$urlCounts[$fileNumber];
                }
            }
        }
        $this->buildFile($fileNumber, $res);
        $indexFile = self::EXPORT_DIR . 'sitemap_index.xml';
        if (\is_writable($indexFile) || !\is_file($indexFile)) {
            $file = \fopen($indexFile, 'w+');
            \fwrite($file, $this->buildIndex($fileNumber, \function_exists('gzopen')));
            \fclose($file);
            $timeTotal = \microtime(true) - $timeStart;
            \executeHook(\HOOK_SITEMAP_EXPORT_GENERATED, [
                'nAnzahlURL_arr' => $urlCounts,
                'totalTime'      => $timeTotal
            ]);
            $this->buildReport($urlCounts, $timeTotal);
            $this->ping($baseURL);
        }
    }

    /**
     * @param string $baseURL
     */
    private function ping(string $baseURL): void
    {
        if ($this->config['sitemap']['sitemap_google_ping'] !== 'Y') {
            return;
        }
        $encodedSitemapIndexURL = \urlencode($baseURL . 'sitemap_index.xml');
        $urls                   = [
            'http://www.google.com/webmasters/tools/ping?sitemap=',
            'http://www.bing.com/ping?sitemap='
        ];
        foreach ($urls as $url) {
            $status = \RequestHelper::http_get_status($url . $encodedSitemapIndexURL);
            if ($status !== 200) {
                $this->logger->notice('Sitemap ping to ' . $url . ' failed with status ' . $status);
            }
        }
    }

    /**
     * @param string $url
     * @return bool
     */
    private function isURLBlocked(string $url): bool
    {
        $blocked = [
            'navi.php',
            'suche.php',
            'jtl.php',
            'pass.php',
            'registrieren.php',
            'warenkorb.php',
        ];

        return \Functional\some($blocked, function ($e) use ($url) {
            return \strpos($url, $e) !== false;
        });
    }

    /**
     * @param int  $fileNumber
     * @param bool $gzip
     * @return string
     */
    private function buildIndex(int $fileNumber, bool $gzip): string
    {
        $shopURL = \Shop::getURL();
        $cIndex  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $cIndex  .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        for ($i = 0; $i <= $fileNumber; ++$i) {
            if ($gzip) {
                $cIndex .= '<sitemap><loc>' .
                    \StringHandler::htmlentities($shopURL . '/' . \PFAD_EXPORT . 'sitemap_' . $i . '.xml.gz') .
                    '</loc>' .
                    ($this->config['sitemap']['sitemap_insert_lastmod'] === 'Y'
                        ? ('<lastmod>' . \StringHandler::htmlentities(\date('Y-m-d')) . '</lastmod>') :
                        '') .
                    '</sitemap>' . "\n";
            } else {
                $cIndex .= '<sitemap><loc>' . \StringHandler::htmlentities($shopURL . '/' .
                        \PFAD_EXPORT . 'sitemap_' . $i . '.xml') . '</loc>' .
                    ($this->config['sitemap']['sitemap_insert_lastmod'] === 'Y'
                        ? ('<lastmod>' . \StringHandler::htmlentities(\date('Y-m-d')) . '</lastmod>')
                        : '') .
                    '</sitemap>' . "\n";
            }
        }
        $cIndex .= '</sitemapindex>';

        return $cIndex;
    }

    /**
     * @param int    $fileNumber
     * @param string $data
     * @return bool
     */
    private function buildFile(int $fileNumber, string $data): bool
    {
        $this->logger->debug('Baue "' . self::EXPORT_DIR . 'sitemap_' . $fileNumber . '.xml", Datenlaenge ' . \strlen($data));
        if (empty($data)) {
            return false;
        }
        if (\function_exists('gzopen')) {
            $gz = \gzopen(self::EXPORT_DIR . 'sitemap_' . $fileNumber . '.xml.gz', 'w9');
            \fwrite($gz, \getXMLHeader($this->config['sitemap']['sitemap_googleimage_anzeigen']) . "\n");
            \fwrite($gz, $data);
            \fwrite($gz, '</urlset>');
            \gzclose($gz);
        } else {
            $file = \fopen(self::EXPORT_DIR . 'sitemap_' . $fileNumber . '.xml', 'w+');
            \fwrite($file, \getXMLHeader($this->config['sitemap']['sitemap_googleimage_anzeigen']) . "\n");
            \fwrite($file, $data);
            \fwrite($file, '</urlset>');
            \fclose($file);
        }

        return true;
    }

    /**
     * @return bool
     */
    private function deleteFiles(): bool
    {
        if (!\is_dir(self::EXPORT_DIR) || ($dh = \opendir(self::EXPORT_DIR)) === false) {
            return false;
        }
        while (($file = \readdir($dh)) !== false) {
            if ($file === 'sitemap_index.xml' || \strpos($file, 'sitemap_') !== false) {
                \unlink(self::EXPORT_DIR . $file);
            }
        }
        \closedir($dh);

        return true;
    }

    /**
     * @param array $urlCounts
     * @param float $timeTotal
     * @return bool
     */
    private function buildReport(array $urlCounts, float $timeTotal): bool
    {
        if ($timeTotal <= 0 || \count($urlCounts) === 0) {
            return false;
        }
        $totalCount = 0;
        foreach ($urlCounts as $count) {
            $totalCount += $count;
        }
        $report                     = new \stdClass();
        $report->nTotalURL          = $totalCount;
        $report->fVerarbeitungszeit = \number_format($timeTotal, 2);
        $report->dErstellt          = 'NOW()';

        $reportID = $this->db->insert('tsitemapreport', $report);
        $gzip     = \function_exists('gzopen');
        foreach ($urlCounts as $i => $count) {
            if ($count <= 0) {
                continue;
            }
            $ins                 = new \stdClass();
            $ins->kSitemapReport = $reportID;
            $ins->cDatei         = 'sitemap_' . $i . '.xml' . ($gzip ? '.gz' : '');
            $ins->nAnzahlURL     = $count;
            $ins->fGroesse       = \is_file(self::EXPORT_DIR . $ins->cDatei)
                ? \number_format(\filesize(self::EXPORT_DIR . $ins->cDatei) / 1024, 2)
                : 0;
            $this->db->insert('tsitemapreportfile', $ins);
        }

        return true;
    }
}
