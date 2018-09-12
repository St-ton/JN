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
use Sitemap\Factories\FactoryInterface;
use Sitemap\Factories\LiveSearch;
use Sitemap\Factories\Manufacturer;
use Sitemap\Factories\NewsCategory;
use Sitemap\Factories\NewsItem;
use Sitemap\Factories\Page;
use Sitemap\Factories\Product;
use Sitemap\Factories\Tag;
use Sitemap\ItemRenderes\RendererInterface;
use Sitemap\Items\ItemInterface;
use Sitemap\SchemaRenderers\SchemaRendererInterface;

/**
 * Class Export
 * @package Sitemap
 */
class Export
{

    public const SITEMAP_URL_GOOGLE = 'http://www.google.com/webmasters/tools/ping?sitemap=';

    public const SITEMAP_URL_BING = 'http://www.bing.com/ping?sitemap=';

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
     * @var RendererInterface
     */
    private $renderer;

    /**
     * @var SchemaRendererInterface
     */
    private $schemaRenderer;

    /**
     * @var array
     */
    private $config;

    /**
     * @var string
     */
    private $baseURL;

    /**
     * @var string
     */
    private $baseImageURL;

    /**
     * Export constructor.
     * @param DbInterface             $db
     * @param LoggerInterface         $logger
     * @param RendererInterface       $renderer
     * @param SchemaRendererInterface $schemaRenderer
     * @param array                   $config
     */
    public function __construct(
        DbInterface $db,
        LoggerInterface $logger,
        RendererInterface $renderer,
        SchemaRendererInterface $schemaRenderer,
        array $config
    ) {
        $this->db             = $db;
        $this->logger         = $logger;
        $this->renderer       = $renderer;
        $this->schemaRenderer = $schemaRenderer;
        $this->config         = $config;
        $this->baseImageURL   = \Shop::getImageBaseURL();
        $this->baseURL        = \Shop::getURL() . '/';
        $this->schemaRenderer->setConfig($config);
        $this->renderer->setConfig($config);
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

        $fileNumber = 0;
        $nSitemap   = 1;
        $factories  = [];
        $urlCounts  = [0 => 0];
        $res        = '';

        $this->deleteFiles();

        $factories[] = new Base($this->db, $this->config, $this->baseURL, $this->baseImageURL);
        $factories[] = new Product($this->db, $this->config, $this->baseURL, $this->baseImageURL);
        $factories[] = new Page($this->db, $this->config, $this->baseURL, $this->baseImageURL);
        $factories[] = new Category($this->db, $this->config, $this->baseURL, $this->baseImageURL);
        $factories[] = new Tag($this->db, $this->config, $this->baseURL, $this->baseImageURL);
        $factories[] = new Manufacturer($this->db, $this->config, $this->baseURL, $this->baseImageURL);
        $factories[] = new LiveSearch($this->db, $this->config, $this->baseURL, $this->baseImageURL);
        $factories[] = new Attribute($this->db, $this->config, $this->baseURL, $this->baseImageURL);
        $factories[] = new NewsItem($this->db, $this->config, $this->baseURL, $this->baseImageURL);
        $factories[] = new NewsCategory($this->db, $this->config, $this->baseURL, $this->baseImageURL);

        \executeHook(\HOOK_SITEMAP_EXPORT_GET_FACTORIES, [
            'factories' => &$factories,
            'exporter'  => $this
        ]);

        foreach ($factories as $factory) {
            /** @var FactoryInterface $factory */
            foreach ($factory->getCollection($languages, [$defaultCustomerGroupID]) as $item) {
                if ($item === null) {
                    break;
                }
                /** @var ItemInterface $item */
                if ($nSitemap > \SITEMAP_ITEMS_LIMIT) {
                    $nSitemap = 1;
                    $this->buildFile($fileNumber, $res);
                    ++$fileNumber;
                    $urlCounts[$fileNumber] = 0;
                    $res                    = '';
                }
                if (!$this->isURLBlocked($item->getLocation())) {
                    $res .= $this->renderer->renderItem($item);
                    ++$nSitemap;
                    ++$urlCounts[$fileNumber];
                }
            }
        }

        $this->buildFile($fileNumber, $res);
        $this->writeIndexFile($fileNumber);
        $timeTotal = \microtime(true) - $timeStart;
        \executeHook(\HOOK_SITEMAP_EXPORT_GENERATED, [
            'nAnzahlURL_arr' => $urlCounts,
            'totalTime'      => $timeTotal
        ]);
        $this->buildReport($urlCounts, $timeTotal);
        $this->ping();
    }

    /**
     * @param int $fileNumber
     */
    private function writeIndexFile(int $fileNumber): void
    {
        $indexFile = self::EXPORT_DIR . 'sitemap_index.xml';
        if (\is_writable($indexFile) || !\is_file($indexFile)) {
            $handle       = \fopen($indexFile, 'w+');
            $extension    = false && \function_exists('gzopen') ? '.xml.gz' : '.xml';
            $sitemapFiles = [];
            for ($i = 0; $i <= $fileNumber; ++$i) {
                $sitemapFiles[] = $this->baseURL . \PFAD_EXPORT . 'sitemap_' . $i . $extension;
            }
            \fwrite($handle, $this->schemaRenderer->buildIndexSchema($sitemapFiles));
            \fclose($handle);
        }
    }

    /**
     *
     */
    private function ping(): void
    {
        if ($this->config['sitemap']['sitemap_google_ping'] !== 'Y') {
            return;
        }
        $indexURL = \urlencode($this->baseURL . 'sitemap_index.xml');
        foreach ([self::SITEMAP_URL_GOOGLE, self::SITEMAP_URL_BING] as $url) {
            $status = \RequestHelper::http_get_status($url . $indexURL);
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
     * @param int    $fileNumber
     * @param string $data
     * @return bool
     */
    private function buildFile(int $fileNumber, string $data): bool
    {
        if (empty($data)) {
            return false;
        }
        $fileName = self::EXPORT_DIR . 'sitemap_' . $fileNumber . '.xml';
        $handle   = false && \function_exists('gzopen')
            ? \gzopen($fileName . '.gz', 'w9')
            : \fopen($fileName, 'w+');
        \fwrite($handle,
            $this->schemaRenderer->buildXMLHeader() .
            $data .
            $this->schemaRenderer->buildXMLFooter()
        );
        \fclose($handle);

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
        $gzip     = false && \function_exists('gzopen');
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
        $this->logger->debug('Sitemap erfolgreich mit ' . $totalCount . ' URLs erstellt');

        return true;
    }

    /**
     * @return DbInterface
     */
    public function getDB(): DbInterface
    {
        return $this->db;
    }

    /**
     * @param DbInterface $db
     */
    public function setDB(DbInterface $db): void
    {
        $this->db = $db;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config): void
    {
        $this->config = $config;
    }
}
