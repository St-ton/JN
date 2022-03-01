<?php declare(strict_types=1);

namespace JTL\Review;

use Exception;
use JTL\Alert\Alert;
use JTL\Cache\JTLCacheInterface;
use JTL\CSV\Export;
use JTL\CSV\Import;
use JTL\DB\DbInterface;
use JTL\Helpers\Form;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Pagination\Pagination;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use stdClass;
use function Functional\map;

/**
 * Class ReviewAdminController
 * @package JTL\Review
 */
final class ReviewAdminController extends BaseController
{
    /**
     * @var int
     */
    private int $languageID;

    /**
     * ReviewAdminController constructor.
     * @param DbInterface                $db
     * @param JTLCacheInterface          $cache
     * @param AlertServiceInterface|null $alertService
     * @param JTLSmarty|null             $smarty
     */
    public function __construct(
        DbInterface $db,
        JTLCacheInterface $cache,
        ?AlertServiceInterface $alertService = null,
        ?JTLSmarty $smarty = null
    ) {
        $this->db           = $db;
        $this->smarty       = $smarty;
        $this->config       = Shop::getSettings([\CONF_GLOBAL, \CONF_RSS, \CONF_BEWERTUNG]);
        $this->cache        = $cache;
        $this->alertService = $alertService;
        $this->languageID   = (int)$_SESSION['editLanguageID'];
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
                $this->alertService->addAlert(Alert::TYPE_SUCCESS, \__('successRatingEdit'), 'successRatingEdit');
                if (Request::verifyGPCDataInt('nFZ') === 1) {
                    \header('Location: freischalten.php');
                    exit();
                }
            } else {
                $this->alertService->addAlert(Alert::TYPE_ERROR, \__('errorFillRequired'), 'errorFillRequired');
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
            $this->export(Request::verifyGPDataString('exportcsv' === 'exportActiveRatings'));
        } elseif ($action === 'csvImport') {
            $this->import(Request::verifyGPCDataInt('importType'));
        }

        return $step;
    }

    /**
     * @param bool $active
     * @return void
     */
    private function export(bool $active): void
    {
        $export = new Export();
        if ($active === true) {
            $export->handleCsvExportAction(
                'activereviews',
                'active_reviews.csv',
                [$this, 'getActiveReviews'],
            );
        } else {
            $export->handleCsvExportAction(
                'inactivereviews',
                'inactive_reviews.csv',
                [$this, 'getInactiveReviews'],
            );
        }
    }

    private function import(int $type)
    {
        $import = new Import($this->db);
        $import->handleCsvImportAction('importRatings', [$this, 'insertImportItem']);
    }

    public function insertImportItem()
    {
    }

    /**
     * @param array $data
     * @return bool
     */
    private function setConfig(array $data): bool
    {
        if (Request::verifyGPDataString('bewertung_guthaben_nutzen') === 'Y'
            && Request::verifyGPDataString('bewertung_freischalten') !== 'Y'
        ) {
            $this->alertService->addAlert(Alert::TYPE_ERROR, \__('errorCreditUnlock'), 'errorCreditUnlock');
            return false;
        }
        $this->cache->flushTags([\CACHING_GROUP_ARTICLE]);
        \saveAdminSectionSettings(\CONF_BEWERTUNG, $data);

        return true;
    }

    /**
     * handle request param 'bewertung_nicht_aktiv'
     *
     * @param array $data
     * @param string $action
     * @return bool
     */
    private function handleInactive(array $data, string $action): bool
    {
        if ($action === 'activate' && GeneralObject::hasCount('kBewertung', $data)) {
            $this->alertService->addAlert(
                Alert::TYPE_SUCCESS,
                $this->activate($data['kBewertung']) . \__('successRatingUnlock'),
                'successRatingUnlock'
            );

            return true;
        }
        if ($action === 'delete' && GeneralObject::hasCount('kBewertung', $data)) {
            $this->alertService->addAlert(
                Alert::TYPE_SUCCESS,
                $this->delete($_POST['kBewertung']) . \__('successRatingDelete'),
                'successRatingDelete'
            );

            return true;
        }

        return false;
    }

    /**
     * @param array $data
     * @param string $action
     * @return bool
     */
    private function handleActive(array $data, string $action): bool
    {
        if ($action === 'delete' && GeneralObject::hasCount('kBewertung', $data)) {
            $this->alertService->addAlert(
                Alert::TYPE_SUCCESS,
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
                ['lang' => $this->languageID, 'cartnr' => '%' . $data['cArtNr'] . '%']
            );
            $this->smarty->assign('cArtNr', Text::filterXSS($data['cArtNr']))
                ->assign('filteredReviews', $filtered);
        }

        return true;
    }

    /**
     *
     */
    public function getOverview(): void
    {
        if (Request::verifyGPDataString('a') === 'delreply' && Form::validateToken()) {
            $this->removeReply(Request::verifyGPCDataInt('kBewertung'));
            $this->alertService->addAlert(
                Alert::TYPE_SUCCESS,
                \__('successRatingCommentDelete'),
                'successRatingCommentDelete'
            );
        }
        $activePagination   = $this->getActivePagination();
        $inactivePagination = $this->getInactivePagination();
        \getAdminSectionSettings(\CONF_BEWERTUNG);
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
            ['lid' => $this->languageID]
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
            ['lid' => $this->languageID]
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
            ['lid' => $this->languageID]
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
            ['lid' => $this->languageID]
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
            return ReviewModel::load(['id' => $id], $this->db, ReviewModel::ON_NOTEXISTS_FAIL);
        } catch (Exception $e) {
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
            $review = ReviewModel::load(['id' => $id], $this->db, ReviewModel::ON_NOTEXISTS_FAIL);
        } catch (Exception $e) {
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
        $this->updateAverage($review->productID, $this->config['bewertung']['bewertung_freischalten']);

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
                $model = ReviewModel::load(['id' => $id], $this->db, ReviewModel::ON_NOTEXISTS_FAIL);
            } catch (Exception $e) {
                continue;
            }
            $this->updateAverage($model->getProductID(), $this->config['bewertung']['bewertung_freischalten']);
            $this->deleteReviewReward($model);
            $model->delete();
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
                $model = ReviewModel::load(['id' => $id], $this->db, ReviewModel::ON_NOTEXISTS_FAIL);
            } catch (Exception $e) {
                continue;
            }
            $model->setActive(1);
            $model->save(['active']);
            $this->updateAverage($model->getProductID(), $this->config['bewertung']['bewertung_freischalten']);
            $this->addReward($model);
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
            $model = ReviewModel::load(['id' => $id], $this->db, ReviewModel::ON_NOTEXISTS_FAIL);
        } catch (Exception $e) {
            return;
        }
        $model->setAnswer(null);
        $model->setAnswerDate(null);
        $model->save(['answer', 'answerDate']);
    }

    /**
     * @param ReviewModel $review
     * @return int
     */
    private function deleteReviewReward(ReviewModel $review): int
    {
        $affected = 0;
        foreach ($review->getBonus() as $bonusItem) {
            /** @var ReviewBonusModel $bonusItem */
            $customer = $this->db->select('tkunde', 'kKunde', $bonusItem->getCustomerID());
            if ($customer !== null && $customer->kKunde > 0) {
                $balance = $customer->fGuthaben - $bonusItem->getBonus();
                $upd     = (object)['fGuthaben' => $balance > 0 ? $balance : 0];
                $this->db->update('tkunde', 'kKunde', $bonusItem->getCustomerID(), $upd);
                ++$affected;
            }
        }

        return $affected;
    }
}
