<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use InvalidArgumentException;
use JTL\Cron\QueueEntry;
use JTL\Export\ExporterFactory;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ExportStarterController
 * @package JTL\Router\Controller\Backend
 */
class ExportStarterController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        @\ini_set('max_execution_time', '0');
        if (Request::getInt('e') < 1 || !Form::validateToken()) {
            return $this->returnErrorCode(0);
        }
        $this->getText->loadAdminLocale('pages/exportformate');
        $queue = $this->db->select('texportqueue', 'kExportqueue', Request::getInt('e'));
        if ($queue === null || !$queue->kExportformat || !$queue->nLimit_m) {
            return $this->returnErrorCode(1);
        }
        $queue->jobQueueID    = (int)$queue->kExportqueue;
        $queue->cronID        = 0;
        $queue->taskLimit     = (int)$queue->nLimit_m;
        $queue->tasksExecuted = (int)$queue->nLimit_n;
        $queue->lastProductID = (int)$queue->nLastArticleID;
        $queue->jobType       = 'exportformat';
        $queue->tableName     = null;
        $queue->foreignKey    = 'kExportformat';
        $queue->kExportformat = (int)$queue->kExportformat;
        $queue->foreignKeyID  = $queue->kExportformat;

        $factory = new ExporterFactory($this->db, Shop::Container()->getLogService(), $this->cache);
        $ef      = $factory->getExporter($queue->kExportformat);
        try {
            $ef->startExport(
                $queue->kExportformat,
                new QueueEntry($queue),
                isset($_GET['ajax']),
                Request::getVar('back') === 'admin',
                false,
                Request::getInt('max', null)
            );
        } catch (InvalidArgumentException) {
            return $this->returnErrorCode(2);
        }

        return $this->returnErrorCode(-1);
    }

    /**
     * @param int $errorCode
     * @return ResponseInterface
     */
    private function returnErrorCode(int $errorCode): ResponseInterface
    {
        $response = (new Response())->withStatus(200);
        $response->getBody()->write((string)$errorCode);

        return $response;
    }
}
