<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Sitemap;

use function Functional\some;
use JTL\DB\DbInterface;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Helpers\Tax;
use JTL\Customer\Kundengruppe;
use JTL\Shop;
use JTL\Sitemap\Factories\FactoryInterface;
use JTL\Sitemap\ItemRenderers\RendererInterface;
use JTL\Sitemap\Items\ItemInterface;
use JTL\Sitemap\SchemaRenderers\SchemaRendererInterface;
use JTL\Sprache;
use Psr\Log\LoggerInterface;
use stdClass;
use function Functional\first;

/**
 * Class Export
 * @package JTL\Sitemap
 */
final class Export
{
    public const SITEMAP_URL_GOOGLE = 'https://www.google.com/webmasters/tools/ping?sitemap=';

    public const SITEMAP_URL_BING = 'https://www.bing.com/ping?sitemap=';

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
     * @var array
     */
    private $blockedURLs;

    /**
     * @var bool
     */
    private $gzip;

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
        $this->baseImageURL   = Shop::getImageBaseURL();
        $this->baseURL        = Shop::getURL() . '/';
        $this->gzip           = \function_exists('gzopen');
        $this->blockedURLs    = [
            'navi.php',
            'suche.php',
            'jtl.php',
            'pass.php',
            'registrieren.php',
            'warenkorb.php',
        ];
        $this->schemaRenderer->setConfig($config);
        $this->renderer->setConfig($config);
    }

    /**
     * @param array              $customerGroupIDs
     * @param array              $languages
     * @param FactoryInterface[] $factories
     */
    public function generate(array $customerGroupIDs, array $languages, array $factories): void
    {
        $this->logger->debug('Sitemap wird erstellt');
        $timeStart  = \microtime(true);
        $fileNumber = 0;
        $itemCount  = 1;
        $urlCounts  = [0 => 0];
        $res        = '';

        foreach ($languages as $language) {
            $language->cISO639 = Text::convertISO2ISO639($language->cISO);
        }
        $this->setSessionData($customerGroupIDs);
        $this->deleteFiles();

        \executeHook(\HOOK_SITEMAP_EXPORT_GET_FACTORIES, [
            'factories' => &$factories,
            'exporter'  => $this
        ]);
        foreach ($factories as $factory) {
            /** @var FactoryInterface $factory */
            foreach ($factory->getCollection($languages, $customerGroupIDs) as $item) {
                /** @var ItemInterface $item */
                if ($item === null) {
                    break;
                }
                if ($itemCount > \SITEMAP_ITEMS_LIMIT) {
                    $itemCount = 1;
                    $this->buildFile($fileNumber, $res);
                    ++$fileNumber;
                    $urlCounts[$fileNumber] = 0;
                    $res                    = '';
                }
                if (!$this->isURLBlocked($item->getLocation())) {
                    $res .= $this->renderer->renderItem($item);
                    ++$itemCount;
                    ++$urlCounts[$fileNumber];
                }
            }
        }
        $res .= $this->renderer->flush();
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
     * @param array $customerGroupIDs
     */
    private function setSessionData(array $customerGroupIDs): void
    {
        $defaultLang             = Sprache::getDefaultLanguage();
        $defaultLangID           = (int)$defaultLang->kSprache;
        $_SESSION['kSprache']    = $defaultLangID;
        $_SESSION['cISOSprache'] = $defaultLang->cISO;
        Tax::setTaxRates();
        if (!isset($_SESSION['Kundengruppe'])) {
            $_SESSION['Kundengruppe'] = new Kundengruppe();
        }
        $_SESSION['Kundengruppe']->setID(first($customerGroupIDs));
    }

    /**
     * @param int $fileNumber
     */
    private function writeIndexFile(int $fileNumber): void
    {
        $indexFile = self::EXPORT_DIR . 'sitemap_index.xml';
        if (\is_writable($indexFile) || !\is_file($indexFile)) {
            $handle       = \fopen($indexFile, 'wb+');
            $extension    = $this->gzip ? '.xml.gz' : '.xml';
            $sitemapFiles = [];
            for ($i = 0; $i <= $fileNumber; ++$i) {
                $sitemapFiles[] = $this->baseURL . \PFAD_EXPORT . 'sitemap_' . $i . $extension;
            }
            \fwrite($handle, $this->schemaRenderer->buildIndex($sitemapFiles));
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
            $status = Request::http_get_status($url . $indexURL);
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
        return some($this->blockedURLs, function ($e) use ($url) {
            return \mb_strpos($url, $e) !== false;
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
        $handle   = $this->gzip
            ? \gzopen($fileName . '.gz', 'w9')
            : \fopen($fileName, 'wb+');
        \fwrite(
            $handle,
            $this->schemaRenderer->buildHeader() . $data . $this->schemaRenderer->buildFooter()
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
            if ($file === 'sitemap_index.xml' || \mb_strpos($file, 'sitemap_') !== false) {
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
        $report                     = new stdClass();
        $report->nTotalURL          = $totalCount;
        $report->fVerarbeitungszeit = \number_format($timeTotal, 2);
        $report->dErstellt          = 'NOW()';

        $reportID = $this->db->insert('tsitemapreport', $report);
        foreach ($urlCounts as $i => $count) {
            if ($count <= 0) {
                continue;
            }
            $ins                 = new stdClass();
            $ins->kSitemapReport = $reportID;
            $ins->cDatei         = 'sitemap_' . $i . '.xml' . ($this->gzip ? '.gz' : '');
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

    /**
     * @return RendererInterface
     */
    public function getRenderer(): RendererInterface
    {
        return $this->renderer;
    }

    /**
     * @param RendererInterface $renderer
     */
    public function setRenderer(RendererInterface $renderer): void
    {
        $this->renderer = $renderer;
    }

    /**
     * @return SchemaRendererInterface
     */
    public function getSchemaRenderer(): SchemaRendererInterface
    {
        return $this->schemaRenderer;
    }

    /**
     * @param SchemaRendererInterface $schemaRenderer
     */
    public function setSchemaRenderer(SchemaRendererInterface $schemaRenderer): void
    {
        $this->schemaRenderer = $schemaRenderer;
    }

    /**
     * @return string
     */
    public function getBaseURL(): string
    {
        return $this->baseURL;
    }

    /**
     * @param string $baseURL
     */
    public function setBaseURL(string $baseURL): void
    {
        $this->baseURL = $baseURL;
    }

    /**
     * @return string
     */
    public function getBaseImageURL(): string
    {
        return $this->baseImageURL;
    }

    /**
     * @param string $baseImageURL
     */
    public function setBaseImageURL(string $baseImageURL): void
    {
        $this->baseImageURL = $baseImageURL;
    }
}
