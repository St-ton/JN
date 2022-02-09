<?php declare(strict_types=1);

namespace JTL\Export;

use Exception;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use JTL\Alert\Alert;
use JTL\Backend\Revision;
use JTL\Backend\Settings\Manager;
use JTL\Backend\Settings\Sections\Export;
use JTL\DB\DbInterface;
use JTL\DB\SqlObject;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use stdClass;

/**
 * Class Admin
 * @package JTL\Export
 */
class Admin
{
    /**
     * @var DbInterface
     */
    private DbInterface $db;

    /**
     * @var AlertServiceInterface
     */
    private AlertServiceInterface $alertService;

    /**
     * @var JTLSmarty
     */
    private JTLSmarty $smarty;

    /**
     * @var string
     */
    private string $step = 'overview';

    /**
     * @var Export
     */
    private Export $config;

    /**
     * Admin constructor.
     * @param DbInterface           $db
     * @param AlertServiceInterface $alertService
     * @param JTLSmarty             $smarty
     */
    public function __construct(DbInterface $db, AlertServiceInterface $alertService, JTLSmarty $smarty)
    {
        $this->db           = $db;
        $this->alertService = $alertService;
        $this->smarty       = $smarty;
        $manager            = new Manager(
            $db,
            $smarty,
            Shop::Container()->getAdminAccount(),
            Shop::Container()->getGetText(),
            $alertService
        );
        $this->config       = new Export($manager, \CONF_EXPORTFORMATE);
    }

    public function getAction(): void
    {
        if (!Form::validateToken()) {
            return;
        }
        $action   = null;
        $exportID = null;
        if (\mb_strlen(Request::postVar('action', '')) > 0) {
            $action   = $_POST['action'];
            $exportID = Request::postInt('kExportformat');
        } elseif (\mb_strlen(Request::getVar('action', '')) > 0) {
            $action   = $_GET['action'];
            $exportID = Request::getInt('kExportformat');
        }
        if ($exportID === null) {
            return;
        }
        switch ($action) {
            case 'export':
                $this->startExport($exportID);
                break;
            case 'download':
                $this->download($exportID);
                break;
            case 'create':
                $this->step = 'edit';
                $this->createOrUpdate();
                break;
            case 'view':
                $this->step = 'edit';
                $this->view();
                break;
            case 'edit':
                $this->step             = 'edit';
                $_POST['kExportformat'] = $exportID;
                $this->createOrUpdate();
                break;
            case 'delete':
                $this->delete($exportID);
                break;
            case 'exported':
                $this->checkCreated($exportID);
                break;
            default:
                break;
        }
    }

    public function display(): void
    {
        $this->smarty->assign('step', $this->step)
            ->assign('exportformate', Model::loadAll(
                $this->db,
                [],
                []
            )->sortBy('name', \SORT_NATURAL | \SORT_FLAG_CASE))
            ->display('exportformate.tpl');
    }

    private function createOrUpdate(): void
    {
        $model       = Model::newInstance($this->db);
        $checker     = new SyntaxChecker(0, $this->db);
        $checkResult = $checker->check($_POST, $model);
        $doCheck     = 0;
        if (\is_a($checkResult, Model::class)) {
            $checkResult->setFooter($checkResult->getFooter() ?? '');
            $checkResult->setHeader($checkResult->getHeader() ?? '');
            $exportID = $checkResult->getId();
            if ($exportID > 0) {
                $oldModel = Model::load(['id' => $exportID], $this->db);
                /** @var Model $oldModel */
                $exportID = Request::postInt('kExportformat');
                $revision = new Revision($this->db);
                $revision->addRevision('export', $exportID);
                $checkResult->setWasLoaded(true);
                $checkResult->setAsync($oldModel->getAsync());
                $checkResult->setIsSpecial($oldModel->getIsSpecial());
                $checkResult->save();
                $this->alertService->addAlert(
                    Alert::TYPE_SUCCESS,
                    \sprintf(\__('successFormatEdit'), $checkResult->getName()),
                    'successFormatEdit'
                );
            } else {
                $checkResult->setAsync(1);
                $checkResult->save();
                $exportID = $checkResult->getId();
                $this->alertService->addAlert(
                    Alert::TYPE_SUCCESS,
                    \sprintf(\__('successFormatCreate'), $checkResult->getName()),
                    'successFormatCreate'
                );
            }
            $doCheck           = $exportID;
            $_POST['exportID'] = $exportID;
            $this->config->update($_POST, true, []);
            $this->step = 'overview';
            if (Request::postInt('saveAndContinue') === 1) {
                $this->step = 'edit';
                $this->view();
            }
        } else {
            $_POST['cContent']   = \str_replace('<tab>', "\t", $_POST['cContent']);
            $_POST['cKopfzeile'] = \str_replace('<tab>', "\t", Request::postVar('cKopfzeile', ''));
            $_POST['cFusszeile'] = \str_replace('<tab>', "\t", Request::postVar('cFusszeile', ''));
            $this->smarty->assign('cPlausiValue_arr', $checkResult)
                ->assign('cPostVar_arr', Collection::make(Text::filterXSS($_POST))->map(static function ($e) {
                    return \is_string($e) ? Text::htmlentities($e) : $e;
                })->all());
            $this->view();
            $this->step = 'edit';
            $this->alertService->addAlert(Alert::TYPE_ERROR, \__('errorCheckInput'), 'errorCheckInput');
        }
        $this->smarty->assign('checkTemplate', $doCheck ?? 0);
    }

    private function view(): void
    {
        require_once \PFAD_ROOT . \PFAD_ADMIN . \PFAD_INCLUDES . 'admin_tools.php';
        $this->smarty->assign('kundengruppen', $this->db->getObjects(
            'SELECT * 
                FROM tkundengruppe 
                ORDER BY cName'
        ))
            ->assign('waehrungen', $this->db->getObjects(
                'SELECT * 
                    FROM twaehrung 
                    ORDER BY cStandard DESC'
            ))
            ->assign('oKampagne_arr', \holeAlleKampagnen());

        if (Request::postInt('kExportformat') > 0) {
            try {
                $model = Model::load(
                    ['id' => Request::postInt('kExportformat')],
                    $this->db,
                    Model::ON_NOTEXISTS_FAIL
                );
                /** @var Model $model */
                $model->setHeader(\str_replace("\t", '<tab>', $model->getHeader()));
                $model->setContent(Text::htmlentities(\str_replace("\t", '<tab>', $model->getContent())));
                $model->setFooter(\str_replace("\t", '<tab>', $model->getFooter()));
            } catch (Exception $e) {
                $model = null;
            }
        } else {
            $model = Model::newInstance($this->db);
            $model->setUseCache(1);
        }
        $sql = new SqlObject();
        $sql->setWhere('kExportformat = :eid');
        $sql->addParam(':eid', $model->getId());
        $this->config->load($sql);
        $this->smarty->assign('Exportformat', $model)
            ->assign('settings', $this->config->getItems());
    }

    /**
     * @param int $exportID
     */
    private function checkCreated(int $exportID): void
    {
        $exportformat = $this->db->select('texportformat', 'kExportformat', $exportID);
        if ($exportformat === null) {
            $this->alertService->addAlert(
                Alert::TYPE_ERROR,
                \sprintf(\__('errorFormatCreate'), '?'),
                'errorFormatCreate'
            );
        }
        $realBase   = \realpath(\PFAD_ROOT . \PFAD_EXPORT);
        $real       = \realpath(\PFAD_ROOT . \PFAD_EXPORT . $exportformat->cDateiname);
        $ok1        = \is_string($real) && \str_starts_with($real, $realBase);
        $realZipped = \realpath(\PFAD_ROOT . \PFAD_EXPORT . $exportformat->cDateiname . '.zip');
        $ok2        = \is_string($realZipped) && \str_starts_with($realZipped, $realBase);
        if ($ok1 === true || $ok2 === true || (int)($exportformat->nSplitgroesse ?? 0) > 0) {
            if (empty($_GET['hasError'])) {
                $this->alertService->addAlert(
                    Alert::TYPE_SUCCESS,
                    \sprintf(\__('successFormatCreate'), $exportformat->cName),
                    'successFormatCreate'
                );
            } else {
                $this->alertService->addAlert(
                    Alert::TYPE_ERROR,
                    \sprintf(\__('errorFormatCreate'), $exportformat->cName),
                    'errorFormatCreate'
                );
            }
        } else {
            $this->alertService->addAlert(
                Alert::TYPE_ERROR,
                \sprintf(\__('errorFormatCreate'), $exportformat->cName),
                'errorFormatCreate'
            );
        }
    }

    /**
     * @param int $exportID
     * @return bool
     */
    private function delete(int $exportID): bool
    {
        $deleted = $this->db->getAffectedRows(
            "DELETE tcron, texportformat, tjobqueue, texportqueue
               FROM texportformat
               LEFT JOIN tcron 
                  ON tcron.foreignKeyID = texportformat.kExportformat
                  AND tcron.foreignKey = 'kExportformat'
                  AND tcron.tableName = 'texportformat'
               LEFT JOIN tjobqueue 
                  ON tjobqueue.foreignKeyID = texportformat.kExportformat
                  AND tjobqueue.foreignKey = 'kExportformat'
                  AND tjobqueue.tableName = 'texportformat'
                  AND tjobqueue.jobType = 'exportformat'
               LEFT JOIN texportqueue 
                  ON texportqueue.kExportformat = texportformat.kExportformat
               WHERE texportformat.kExportformat = :eid",
            ['eid' => $exportID]
        );

        if ($deleted > 0) {
            $this->alertService->addAlert(Alert::TYPE_SUCCESS, \__('successFormatDelete'), 'successFormatDelete');
        } else {
            $this->alertService->addAlert(Alert::TYPE_ERROR, \__('errorFormatDelete'), 'errorFormatDelete');
        }

        return $deleted > 0;
    }

    /**
     * @param int $exportID
     * @throws InvalidArgumentException
     */
    private function download(int $exportID): void
    {
        try {
            $exportformat = Model::load(['id' => $exportID], $this->db, Model::ON_NOTEXISTS_FAIL);
            /** @var Model $exportformat */
        } catch (Exception $e) {
            throw new InvalidArgumentException('Cannot find export with id ' . $exportID);
        }
        $file = $exportformat->getFilename();
        if (\mb_strlen($file) < 1) {
            return;
        }
        $real = \realpath(\PFAD_ROOT . \PFAD_EXPORT . $file);
        if ($real !== false && \str_starts_with($real, \realpath(\PFAD_ROOT . \PFAD_EXPORT))) {
            \header('Content-type: text/plain');
            \header('Content-Disposition: attachment; filename=' . $file);
            echo \file_get_contents($real);
            exit;
        }
        $this->alertService->addAlert(
            Alert::TYPE_ERROR,
            \sprintf(\__('File %s not found.'), $file),
            'errorCannotDownloadExport'
        );
    }

    /**
     * @param int $exportID
     */
    private function startExport(int $exportID): void
    {
        $async                 = isset($_GET['ajax']);
        $queue                 = new stdClass();
        $queue->kExportformat  = $exportID;
        $queue->nLimit_n       = 0;
        $queue->nLimit_m       = $async ? \EXPORTFORMAT_ASYNC_LIMIT_M : \EXPORTFORMAT_LIMIT_M;
        $queue->nLastArticleID = 0;
        $queue->dErstellt      = 'NOW()';
        $queue->dZuBearbeiten  = 'NOW()';

        $queueID = $this->db->insert('texportqueue', $queue);

        $redir = Shop::getAdminURL() . '/do_export.php?&back=admin&token=' . $_SESSION['jtl_token'] . '&e=' . $queueID;
        if ($async) {
            $redir .= '&ajax';
        }
        \header('Location: ' . $redir);
        exit;
    }
}
