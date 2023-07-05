<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Customer\CustomerGroup;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Language\LanguageHelper;
use JTL\Pagination\Pagination;
use JTL\Shop;
use JTL\Sitemap\Config\DefaultConfig;
use JTL\Sitemap\Export;
use JTL\Sitemap\ItemRenderers\DefaultRenderer;
use JTL\Sitemap\SchemaRenderers\DefaultSchemaRenderer;
use JTL\Smarty\JTLSmarty;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class SitemapExportController
 * @package JTL\Router\Controller\Backend
 */
class SitemapExportController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::EXPORT_SITEMAP_VIEW);
        $this->getText->loadAdminLocale('pages/sitemapexport');

        $this->smarty->assign('route', $this->route);
        if (Request::postVar('action') === 'export') {
            return $this->actionExport();
        }
        $exportDir = \PFAD_ROOT . \PFAD_EXPORT;

        if (!\file_exists($exportDir . 'sitemap_index.xml') && \is_writable($exportDir)) {
            @\touch($exportDir . 'sitemap_index.xml');
        }

        if (!\is_writable($exportDir . 'sitemap_index.xml')) {
            $this->alertService->addError(
                \sprintf(\__('errorSitemapCreatePermission'), '<i>' . $exportDir . 'sitemap_index.xml</i>'),
                'errorSitemapCreatePermission'
            );
        } elseif (isset($_REQUEST['update']) && (int)$_REQUEST['update'] === 1) {
            $this->alertService->addSuccess(
                \sprintf(\__('successSave'), '<i>' . $exportDir . 'sitemap_index.xml</i>'),
                'successSubjectDelete'
            );
        }

        if (Request::postInt('einstellungen') > 0) {
            $this->saveAdminSectionSettings(\CONF_SITEMAP, $_POST);
        } elseif (Request::verifyGPCDataInt('download_edit') === 1) {
            $trackers = \array_map('\intval', Request::postVar('kSitemapTracker', []));
            if (\count($trackers) > 0) {
                $this->db->query(
                    'DELETE
                        FROM tsitemaptracker
                        WHERE kSitemapTracker IN (' . \implode(',', $trackers) . ')'
                );
            }
            $this->alertService->addSuccess(\__('successSitemapDLDelete'), 'successSitemapDLDelete');
        } elseif (Request::verifyGPCDataInt('report_edit') === 1) {
            $reports = \array_map('\intval', Request::postVar('kSitemapReport', []));
            if (\count($reports) > 0) {
                $this->db->query(
                    'DELETE
                        FROM tsitemapreport
                        WHERE kSitemapReport IN (' . \implode(',', $reports) . ')'
                );
            }
            $this->alertService->addSuccess(\__('successSitemapReportDelete'), 'successSitemapReportDelete');
        }

        $yearDownloads = Request::verifyGPCDataInt('nYear_downloads');
        $yearReports   = Request::verifyGPCDataInt('nYear_reports');

        if (Request::postVar('action') === 'year_downloads_delete' && Form::validateToken()) {
            $this->db->queryPrepared(
                'DELETE FROM tsitemaptracker
                    WHERE YEAR(tsitemaptracker.dErstellt) = :yr',
                ['yr' => $yearDownloads]
            );
            $this->alertService->addSuccess(
                \sprintf(\__('successSitemapDLDeleteByYear'), $yearDownloads),
                'successSitemapDLDeleteByYear'
            );
            $yearDownloads = 0;
        }

        if (Request::postVar('action') === 'year_reports_delete' && Form::validateToken()) {
            $this->db->queryPrepared(
                'DELETE FROM tsitemapreport
                     WHERE YEAR(tsitemapreport.dErstellt) = :yr',
                ['yr' => $yearReports]
            );
            $this->alertService->addSuccess(
                \sprintf(\__('successSitemapReportDeleteByYear'), $yearDownloads),
                'successSitemapReportDeleteByYear'
            );
            $yearReports = 0;
        }

        $downloadsPerYear = $this->db->getObjects(
            'SELECT YEAR(dErstellt) AS year, COUNT(*) AS count
                FROM tsitemaptracker
                GROUP BY 1
                ORDER BY 1 DESC'
        );
        if (\count($downloadsPerYear) === 0) {
            $downloadsPerYear[] = (object)[
                'year'  => \date('Y'),
                'count' => 0,
            ];
        }
        if ($yearDownloads === 0) {
            $yearDownloads = (int)$downloadsPerYear[0]->year;
        }
        $downloadPagination = (new Pagination('SitemapDownload'))
            ->setItemCount(\array_reduce($downloadsPerYear, static function ($carry, $item) use ($yearDownloads) {
                return (int)$item->year === $yearDownloads ? (int)$item->count : $carry;
            }, 0))
            ->assemble();
        $sitemapDownloads   = $this->db->getObjects(
            "SELECT tsitemaptracker.*, IF(tsitemaptracker.kBesucherBot = 0, '', 
                IF(CHAR_LENGTH(tbesucherbot.cUserAgent) = 0, tbesucherbot.cName, tbesucherbot.cUserAgent)) AS cBot, 
                DATE_FORMAT(tsitemaptracker.dErstellt, '%d.%m.%Y %H:%i') AS dErstellt_DE
                FROM tsitemaptracker
                LEFT JOIN tbesucherbot 
                    ON tbesucherbot.kBesucherBot = tsitemaptracker.kBesucherBot
                WHERE YEAR(tsitemaptracker.dErstellt) = :yr
                ORDER BY tsitemaptracker.dErstellt DESC
                LIMIT " . $downloadPagination->getLimitSQL(),
            ['yr' => $yearDownloads]
        );

        // Sitemap Reports
        $reportYears = $this->db->getObjects(
            'SELECT YEAR(dErstellt) AS year, COUNT(*) AS count
                FROM tsitemapreport
                GROUP BY 1
                ORDER BY 1 DESC'
        );
        if (\count($reportYears) === 0) {
            $reportYears[] = (object)[
                'year'  => \date('Y'),
                'count' => 0,
            ];
        }
        if ($yearReports === 0) {
            $yearReports = (int)$reportYears[0]->year;
        }
        $pagination     = (new Pagination('SitemapReport'))
            ->setItemCount(\array_reduce($reportYears, static function ($carry, $item) use ($yearReports) {
                return (int)$item->year === $yearReports ? (int)$item->count : $carry;
            }, 0))
            ->assemble();
        $sitemapReports = $this->db->getObjects(
            "SELECT tsitemapreport.*, DATE_FORMAT(tsitemapreport.dErstellt, '%d.%m.%Y %H:%i') AS dErstellt_DE
                FROM tsitemapreport
                WHERE YEAR(tsitemapreport.dErstellt) = :yr
                ORDER BY tsitemapreport.dErstellt DESC
                LIMIT " . $pagination->getLimitSQL(),
            ['yr' => $yearReports]
        );
        foreach ($sitemapReports as $report) {
            if ($report->kSitemapReport <= 0) {
                continue;
            }
            $report->oSitemapReportFile_arr = $this->db->selectAll(
                'tsitemapreportfile',
                'kSitemapReport',
                (int)$report->kSitemapReport
            );
        }
        $this->getAdminSectionSettings(\CONF_SITEMAP);

        return $smarty->assign('nSitemapDownloadYear', $yearDownloads)
            ->assign('oSitemapDownloadYears_arr', $downloadsPerYear)
            ->assign('oSitemapDownloadPagination', $downloadPagination)
            ->assign('oSitemapDownload_arr', $sitemapDownloads)
            ->assign('nSitemapReportYear', $yearReports)
            ->assign('oSitemapReportYears_arr', $reportYears)
            ->assign('oSitemapReportPagination', $pagination)
            ->assign('oSitemapReport_arr', $sitemapReports)
            ->assign('URL', Shop::getURL() . '/' . 'sitemap_index.xml')
            ->getResponse('sitemapexport.tpl');
    }

    /**
     * @return ResponseInterface
     */
    private function actionExport(): ResponseInterface
    {
        $config       = Shop::getSettings([\CONF_GLOBAL, \CONF_SITEMAP]);
        $exportConfig = new DefaultConfig($this->db, $config, Shop::getURL() . '/', Shop::getImageBaseURL());
        $exporter     = new Export(
            $this->db,
            Shop::Container()->getLogService(),
            new DefaultRenderer(),
            new DefaultSchemaRenderer(),
            $config
        );
        $exporter->generate(
            [CustomerGroup::getDefaultGroupID()],
            LanguageHelper::getAllLanguages(0, true),
            $exportConfig->getFactories()
        );

        if (isset($_REQUEST['update']) && (int)$_REQUEST['update'] === 1) {
            return new RedirectResponse($this->baseURL . $this->route . '?update=1');
        }
        $response = (new Response())->withStatus(200)
            ->withAddedHeader('Cache-Control', 'no-cache, must-revalidate')
            ->withAddedHeader('Content-type', 'application/xml')
            ->withAddedHeader('Content-Disposition', 'attachment; filename="sitemap_index.xml"');
        $response->getBody()->write(\file_get_contents(\PFAD_ROOT . 'sitemap.xml'));

        return $response;
    }
}
