<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use Exception;
use JTL\Backend\Permissions;
use JTL\CSV\Export;
use JTL\CSV\Import;
use JTL\Helpers\Form;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Model\DataModelInterface;
use JTL\Pagination\Pagination;
use JTL\Review\Manager;
use JTL\Review\ReviewBonusModel;
use JTL\Review\ReviewModel;
use JTL\Router\Route;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;
use function Functional\map;

/**
 * Class ReviewController
 * @package JTL\Router\Controller\Backend
 */
class ReviewController extends AbstractBackendController
{
    /**
     * @var int[]
     */
    private array $importedProductIDs = [];

    /**
     * @var array
     */
    private array $config;

    /**
     * @var Manager
     */
    private Manager $manager;

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        $this->config  = Shop::getSettings([\CONF_GLOBAL, \CONF_RSS, \CONF_BEWERTUNG]);
        $this->manager = new Manager(
            $this->db,
            $this->alertService,
            $this->cache,
            Shop::Smarty(),
            $this->config
        );
    }

    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::MODULE_VOTESYSTEM_VIEW);
        $this->getText->loadAdminLocale('pages/bewertung');
        $this->setLanguage();
        $tab  = \mb_strlen(Request::verifyGPDataString('tab')) > 0
            ? Request::verifyGPDataString('tab')
            : 'freischalten';
        $step = $this->handleRequest();

        if ($step === 'bewertung_editieren' || Request::getVar('a') === 'editieren') {
            $step = 'bewertung_editieren';
            $smarty->assign('review', $this->getReview(Request::verifyGPCDataInt('kBewertung')));
            if (Request::verifyGPCDataInt('nFZ') === 1) {
                $smarty->assign('nFZ', 1);
            }
        } elseif ($step === 'bewertung_uebersicht') {
            $this->getOverview();
        }

        return $smarty->assign('step', $step)
            ->assign('cTab', $tab)
            ->assign('route', $this->route)
            ->getResponse('bewertung.tpl');
    }

    /**
     * @return string
     */
    public function handleRequest(): string
    {
        $step = 'bewertung_uebersicht';
        if (!Form::validateToken()) {
            return $step;
        }
        $action = Request::verifyGPDataString('action');
        if (Request::verifyGPDataString('importcsv') === 'importRatings') {
            $action = 'csvImport';
        }
        if (Request::verifyGPCDataInt('bewertung_editieren') === 1) {
            $step = 'bewertung_editieren';
            if ($this->edit(Text::filterXSS($_POST))) {
                $step = 'bewertung_uebersicht';
                $this->alertService->addSuccess(\__('successRatingEdit'), 'successRatingEdit');
                if (Request::verifyGPCDataInt('nFZ') === 1) {
                    \header('Location: ' . $this->baseURL . '/' . Route::ACTIVATE);
                    exit();
                }
            } else {
                $this->alertService->addError(\__('errorFillRequired'), 'errorFillRequired');
            }

            return $step;
        }
        if (Request::verifyGPCDataInt('einstellungen') === 1) {
            $this->setConfig($_POST);
        } elseif (Request::verifyGPCDataInt('bewertung_nicht_aktiv') === 1) {
            $this->handleInactive($_POST, $action);
        } elseif (Request::verifyGPCDataInt('bewertung_aktiv') === 1) {
            $this->handleActive($_POST, $action);
        } elseif ($action === 'csvExport') {
            $this->export();
        } elseif ($action === 'csvImport') {
            $this->import(Request::verifyGPCDataInt('importType'));
        }

        return $step;
    }

    /**
     * @return void
     */
    private function export(): void
    {
        $export = new Export();
        $res    = $export->export(
            'activereviews',
            'reviews.csv',
            [$this, 'getAllReviews'],
        );
        if ($res === false) {
            $this->alertService->addInfo(\__('No items exported.'), 'noExportInfo');
        }
    }

    /**
     * @param int $type
     */
    private function import(int $type): void
    {
        $import = new Import($this->db);
        $import->import('importRatings', [$this, 'insertImportItem'], [], null, $type);
        $imported = $import->getImportCount();
        foreach ($import->getErrors() as $i => $error) {
            $this->alertService->addError($error, 'importErr' . $i);
        }
        if ($imported > 0) {
            foreach (\array_unique($this->importedProductIDs) as $id) {
                $this->manager->updateAverage($id, $this->config['bewertung']['bewertung_freischalten']);
            }
            $this->alertService->addSuccess(
                \sprintf(\__('%d item(s) successfully imported.'), $imported),
                'importSuccess'
            );
        }
    }

    /**
     * callback for import
     *
     * @param stdClass $obj
     * @param bool     $importDeleteDone
     * @param int      $importType
     * @return bool
     */
    public function insertImportItem(stdClass $obj, bool &$importDeleteDone, int $importType): bool
    {
        if (!$this->isValidImportRow($obj)) {
            return false;
        }
        if ($importType === Import::TYPE_TRUNCATE_BEFORE && $importDeleteDone === false) {
            $this->db->query('TRUNCATE TABLE tbewertung');
            $importDeleteDone = true;
        }
        $ins                  = new stdClass();
        $ins->kArtikel        = (int)$obj->kArtikel;
        $ins->kKunde          = (int)$obj->kKunde;
        $ins->kSprache        = (int)$obj->kSprache;
        $ins->cName           = $obj->cName;
        $ins->cTitel          = $obj->cTitel;
        $ins->cText           = $obj->cText;
        $ins->nHilfreich      = (int)($obj->nHilfreich ?? 0);
        $ins->nNichtHilfreich = (int)($obj->nNichtHilfreich ?? 0);
        $ins->nSterne         = \min((int)$obj->nSterne, 5);
        $ins->nSterne         = \max($ins->nSterne, 0);
        if ($obj->nAktiv === 'Y' || $obj->nAktiv === 'y') {
            $ins->nAktiv = 1;
        } elseif ($obj->nAktiv === 'N' || $obj->nAktiv === 'n') {
            $ins->nAktiv = 0;
        } else {
            $ins->nAktiv = (int)$obj->nAktiv;
        }
        $ins->dDatum = $obj->dDatum;
        if (isset($obj->cAntwort) && $obj->cAntwort !== '') {
            $ins->cAntwort = $obj->cAntwort;
        }
        if (isset($obj->dAntwortDatum) && $obj->dAntwortDatum !== '') {
            $ins->dAntwortDatum = $obj->dAntwortDatum;
        }
        $exists = -1;
        if ($importType !== Import::TYPE_TRUNCATE_BEFORE && $ins->kKunde > 0) {
            $exists = $this->db->getSingleInt(
                'SELECT kBewertung AS id 
                    FROM tbewertung
                    WHERE kKunde = :cid
                        AND kArtikel = :pid',
                'id',
                ['cid' => $ins->kKunde, 'pid' => $ins->kArtikel],
            );
        }
        if ($exists > 0) {
            if ($importType === Import::TYPE_INSERT_NEW) {
                $ok = true;
            } else {
                $ins->kBewertung = $exists;
                $ok              = $this->db->upsert('tbewertung', $ins) >= 0;
            }
        } else {
            $ok = $this->db->insert('tbewertung', $ins) > 0;
        }
        if ($ok === true) {
            $this->importedProductIDs[] = $ins->kArtikel;
        }

        return $ok;
    }

    /**
     * @param stdClass $rowData
     * @return bool
     */
    private function isValidImportRow(stdClass $rowData): bool
    {
        $required = [
            'kArtikel',
            'kKunde',
            'kSprache',
            'cName',
            'cTitel',
            'cText',
            'nAktiv',
            'nSterne',
            'dDatum'
        ];
        $existing = \array_keys(\get_object_vars($rowData));
        foreach ($required as $key) {
            if (!\in_array($key, $existing, true)) {
                $this->alertService->addError(
                    \sprintf(\__('Required attribute %s not found in row.'), $key),
                    \uniqid('importerr', true)
                );

                return false;
            }
        }

        return true;
    }

    /**
     * @param array $data
     */
    private function setConfig(array $data): void
    {
        if (Request::verifyGPDataString('bewertung_guthaben_nutzen') === 'Y'
            && Request::verifyGPDataString('bewertung_freischalten') !== 'Y'
        ) {
            $this->alertService->addError(\__('errorCreditUnlock'), 'errorCreditUnlock');
            return;
        }
        $this->cache->flushTags([\CACHING_GROUP_ARTICLE]);
        $this->saveAdminSectionSettings(\CONF_BEWERTUNG, $data);
    }

    /**
     * handle request param 'bewertung_nicht_aktiv'
     *
     * @param array  $data
     * @param string $action
     */
    private function handleInactive(array $data, string $action): void
    {
        if ($action === 'activate' && GeneralObject::hasCount('kBewertung', $data)) {
            $this->alertService->addSuccess(
                $this->activate($data['kBewertung']) . \__('successRatingUnlock'),
                'successRatingUnlock'
            );
        } elseif ($action === 'delete' && GeneralObject::hasCount('kBewertung', $data)) {
            $this->alertService->addSuccess(
                $this->delete($_POST['kBewertung']) . \__('successRatingDelete'),
                'successRatingDelete'
            );
        }
    }

    /**
     * @param array  $data
     * @param string $action
     */
    private function handleActive(array $data, string $action): void
    {
        if ($action === 'delete' && GeneralObject::hasCount('kBewertung', $data)) {
            $this->alertService->addSuccess(
                $this->delete($data['kBewertung']) . \__('successRatingDelete'),
                'successRatingDelete'
            );
        }
        if ($action === 'search' && isset($data['cArtNr'])) {
            $filtered = $this->db->getObjects(
                "SELECT tbewertung.*, DATE_FORMAT(tbewertung.dDatum, '%d.%m.%Y') AS Datum, tartikel.cName AS ArtikelName
                    FROM tbewertung
                    LEFT JOIN tartikel 
                        ON tbewertung.kArtikel = tartikel.kArtikel
                    WHERE tbewertung.kSprache = :lang
                        AND (tartikel.cArtNr LIKE :cartnr OR tartikel.cName LIKE :cartnr)
                    ORDER BY tbewertung.kArtikel, tbewertung.dDatum DESC",
                ['lang' => $this->currentLanguageID, 'cartnr' => '%' . $data['cArtNr'] . '%']
            );
            $this->smarty->assign('cArtNr', Text::filterXSS($data['cArtNr']))
                ->assign('filteredReviews', $filtered);
        }
    }

    /**
     *
     */
    public function getOverview(): void
    {
        if (Request::verifyGPDataString('a') === 'delreply' && Form::validateToken()) {
            $this->removeReply(Request::verifyGPCDataInt('kBewertung'));
            $this->alertService->addSuccess(\__('successRatingCommentDelete'), 'successRatingCommentDelete');
        }
        $activePagination   = $this->getActivePagination();
        $inactivePagination = $this->getInactivePagination();
        $this->getAdminSectionSettings(\CONF_BEWERTUNG);
        $this->smarty->assign('oPagiInaktiv', $inactivePagination)
            ->assign('oPagiAktiv', $activePagination)
            ->assign('inactiveReviews', $this->getInactiveReviews($inactivePagination))
            ->assign('activeReviews', $this->getActiveReviews($activePagination));
    }

    /**
     * @param stdClass $ratingData
     * @return stdClass
     */
    public function sanitize(stdClass $ratingData): stdClass
    {
        $ratingData->kBewertung      = (int)$ratingData->kBewertung;
        $ratingData->kArtikel        = (int)$ratingData->kArtikel;
        $ratingData->kKunde          = (int)$ratingData->kKunde;
        $ratingData->kSprache        = (int)$ratingData->kSprache;
        $ratingData->nHilfreich      = (int)$ratingData->nHilfreich;
        $ratingData->nNichtHilfreich = (int)$ratingData->nNichtHilfreich;
        $ratingData->nSterne         = (int)$ratingData->nSterne;
        $ratingData->nAktiv          = (int)$ratingData->nAktiv;
        $ratingData->cText           = Text::filterXSS($ratingData->cText);
        $ratingData->cTitel          = Text::filterXSS($ratingData->cTitel);

        return $ratingData;
    }

    /**
     * @return stdClass[]
     */
    public function getAllReviews(): array
    {
        return $this->db->getCollection(
            'SELECT *
                FROM tbewertung
                ORDER BY tbewertung.kArtikel, tbewertung.dDatum DESC',
        )->each([$this, 'sanitize'])->toArray();
    }

    /**
     * @param Pagination|null $pagination
     * @return stdClass[]
     */
    public function getInactiveReviews(?Pagination $pagination = null): array
    {
        $limit = '';
        if ($pagination !== null) {
            $limit = ' LIMIT ' . $pagination->getLimitSQL();
        }

        return $this->db->getCollection(
            "SELECT tbewertung.*, DATE_FORMAT(tbewertung.dDatum, '%d.%m.%Y') AS Datum, tartikel.cName AS ArtikelName
                FROM tbewertung
                LEFT JOIN tartikel 
                    ON tbewertung.kArtikel = tartikel.kArtikel
                WHERE tbewertung.kSprache = :lid
                    AND tbewertung.nAktiv = 0
                ORDER BY tbewertung.kArtikel, tbewertung.dDatum DESC" . $limit,
            ['lid' => $this->currentLanguageID]
        )->each([$this, 'sanitize'])->toArray();
    }

    /**
     * @param Pagination|null $pagination
     * @return stdClass[]
     */
    public function getActiveReviews(?Pagination $pagination = null): array
    {
        $limit = '';
        if ($pagination !== null) {
            $limit = ' LIMIT ' . $pagination->getLimitSQL();
        }

        return $this->db->getCollection(
            "SELECT tbewertung.*, DATE_FORMAT(tbewertung.dDatum, '%d.%m.%Y') AS Datum, tartikel.cName AS ArtikelName
                FROM tbewertung
                LEFT JOIN tartikel 
                    ON tbewertung.kArtikel = tartikel.kArtikel
                WHERE tbewertung.kSprache = :lid
                    AND tbewertung.nAktiv = 1
                ORDER BY tbewertung.dDatum DESC" . $limit,
            ['lid' => $this->currentLanguageID]
        )->each([$this, 'sanitize'])->toArray();
    }

    /**
     * @return Pagination
     */
    private function getInactivePagination(): Pagination
    {
        $totalCount = $this->db->getSingleInt(
            'SELECT COUNT(*) AS cnt
                FROM tbewertung
                WHERE kSprache = :lid
                    AND nAktiv = 0',
            'cnt',
            ['lid' => $this->currentLanguageID]
        );

        return (new Pagination('inactive'))
            ->setItemCount($totalCount)
            ->assemble();
    }

    /**
     * @return Pagination
     */
    private function getActivePagination(): Pagination
    {
        $activeCount = $this->db->getSingleInt(
            'SELECT COUNT(*) AS cnt
                FROM tbewertung
                WHERE kSprache = :lid
                    AND nAktiv = 1',
            'cnt',
            ['lid' => $this->currentLanguageID]
        );

        return (new Pagination('active'))
            ->setItemCount($activeCount)
            ->assemble();
    }

    /**
     * @param int $id
     * @return ReviewModel|null
     */
    public function getReview(int $id): ?ReviewModel
    {
        try {
            return ReviewModel::load(['id' => $id], $this->db, DataModelInterface::ON_NOTEXISTS_FAIL);
        } catch (Exception) {
            return null;
        }
    }

    /**
     * @param array $data
     * @return bool
     */
    private function edit(array $data): bool
    {
        $id = Request::verifyGPCDataInt('kBewertung');
        try {
            $review = ReviewModel::load(['id' => $id], $this->db, DataModelInterface::ON_NOTEXISTS_FAIL);
        } catch (Exception) {
            return false;
        }
        if ($data['cAntwort'] !== $review->answer) {
            $review->setAnswerDate(!empty($data['cAntwort']) ? \date('Y-m-d') : null);
        }
        $review->setName($data['cName']);
        $review->setTitle($data['cTitel']);
        $review->setContent($data['cText']);
        $review->setStars((int)$data['nSterne']);
        $review->setAnswer(!empty($data['cAntwort']) ? $data['cAntwort'] : null);
        $review->save();
        $this->manager->updateAverage($review->productID, $this->config['bewertung']['bewertung_freischalten']);

        $this->cache->flushTags([\CACHING_GROUP_ARTICLE . '_' . $review->productID]);

        return true;
    }

    /**
     * @param array $ids
     * @return int
     */
    private function delete(array $ids): int
    {
        $cacheTags = [];
        foreach (\array_map('\intval', $ids) as $id) {
            try {
                $model = ReviewModel::load(['id' => $id], $this->db, DataModelInterface::ON_NOTEXISTS_FAIL);
            } catch (Exception) {
                continue;
            }
            $this->deleteReviewReward($model);
            $model->delete();
            $this->manager->updateAverage($model->getProductID(), $this->config['bewertung']['bewertung_freischalten']);
            $cacheTags[] = $model->getProductID();
        }
        $this->cache->flushTags(map($cacheTags, static function ($e) {
            return \CACHING_GROUP_ARTICLE . '_' . $e;
        }));

        return \count($cacheTags);
    }

    /**
     * @param array $ids
     * @return int
     */
    public function activate(array $ids): int
    {
        $cacheTags = [];
        foreach (\array_map('\intval', $ids) as $id) {
            try {
                $model = ReviewModel::load(['id' => $id], $this->db, DataModelInterface::ON_NOTEXISTS_FAIL);
            } catch (Exception) {
                continue;
            }
            $model->setActive(1);
            $model->save(['active']);
            $this->manager->updateAverage($model->getProductID(), $this->config['bewertung']['bewertung_freischalten']);
            $this->manager->addReward($model);
            $cacheTags[] = $model->getProductID();
        }
        $this->cache->flushTags(map($cacheTags, static function ($e) {
            return \CACHING_GROUP_ARTICLE . '_' . $e;
        }));

        return \count($cacheTags);
    }

    /**
     * @param int $id
     */
    private function removeReply(int $id): void
    {
        try {
            $model = ReviewModel::load(['id' => $id], $this->db, DataModelInterface::ON_NOTEXISTS_FAIL);
        } catch (Exception) {
            return;
        }
        $model->setAnswer(null);
        $model->setAnswerDate(null);
        $model->save(['answer', 'answerDate']);
    }

    /**
     * @param ReviewModel $review
     */
    private function deleteReviewReward(ReviewModel $review): void
    {
        foreach ($review->getBonus() as $bonusItem) {
            /** @var ReviewBonusModel $bonusItem */
            $customer = $this->db->select('tkunde', 'kKunde', $bonusItem->getCustomerID());
            if ($customer !== null && $customer->kKunde > 0) {
                $balance = $customer->fGuthaben - $bonusItem->getBonus();
                $upd     = (object)['fGuthaben' => \max($balance, 0)];
                $this->db->update('tkunde', 'kKunde', $bonusItem->getCustomerID(), $upd);
            }
        }
    }
}
