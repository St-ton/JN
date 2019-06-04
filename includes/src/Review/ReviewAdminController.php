<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Review;

use Exception;
use JTL\Alert\Alert;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Pagination\Pagination;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Shop;
use JTLSmarty;
use function Functional\map;

/**
 * Class ReviewAdminController
 * @package JTL\Review
 */
class ReviewAdminController extends BaseController
{
    /**
     * RatingController constructor.
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
        if (Request::verifyGPCDataInt('bewertung_editieren') === 1) {
            $step = 'bewertung_editieren';
            if ($this->edit($_POST)) {
                $step = 'bewertung_uebersicht';
                $this->alertService->addAlert(Alert::TYPE_SUCCESS, __('successRatingEdit'), 'successRatingEdit');
                if (Request::verifyGPCDataInt('nFZ') === 1) {
                    \header('Location: freischalten.php');
                    exit();
                }
            } else {
                $this->alertService->addAlert(Alert::TYPE_ERROR, __('errorFillRequired'), 'errorFillRequired');
            }

            return $step;
        }

        if (isset($_POST['einstellungen']) && (int)$_POST['einstellungen'] === 1) {
            $this->setConfig($_POST);
        } elseif (isset($_POST['bewertung_nicht_aktiv']) && (int)$_POST['bewertung_nicht_aktiv'] === 1) {
            $this->handleInactive($_POST);
        } elseif (isset($_POST['bewertung_aktiv']) && (int)$_POST['bewertung_aktiv'] === 1) {
            $this->handleActive($_POST);
        }

        return $step;
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
            $this->alertService->addAlert(Alert::TYPE_ERROR, __('errorCreditUnlock'), 'errorCreditUnlock');
            return false;
        }
        $this->cache->flushTags([\CACHING_GROUP_ARTICLE]);
        $this->alertService->addAlert(
            Alert::TYPE_SUCCESS,
            \saveAdminSectionSettings(\CONF_BEWERTUNG, $data),
            'saveConf'
        );

        return true;
    }

    /**
     * handle request param 'bewertung_nicht_aktiv'
     *
     * @param array $data
     * @return bool
     */
    private function handleInactive(array $data): bool
    {
        if (isset($data['aktivieren']) && \is_array($data['kBewertung']) && count($data['kBewertung']) > 0) {
            $this->alertService->addAlert(
                Alert::TYPE_SUCCESS,
                $this->activate($data['kBewertung']) . __('successRatingUnlock'),
                'successRatingUnlock'
            );

            return true;
        }
        if (isset($data['loeschen']) && \is_array($data['kBewertung']) && count($data['kBewertung']) > 0) {
            $this->alertService->addAlert(
                Alert::TYPE_SUCCESS,
                $this->delete($_POST['kBewertung']) . __('successRatingDelete'),
                'successRatingDelete'
            );

            return true;
        }

        return false;
    }

    /**
     * @param array $data
     * @return bool
     */
    private function handleActive(array $data): bool
    {
        if (isset($data['loeschen']) && \is_array($data['kBewertung']) && \count($data['kBewertung']) > 0) {
            $this->alertService->addAlert(
                Alert::TYPE_SUCCESS,
                $this->delete($data['kBewertung']) . __('successRatingDelete'),
                'successRatingDelete'
            );
        }
        if (isset($data['cArtNr'])) {
            $filtered = $this->db->queryPrepared(
                "SELECT tbewertung.*, DATE_FORMAT(tbewertung.dDatum, '%d.%m.%Y') AS Datum, tartikel.cName AS ArtikelName
                    FROM tbewertung
                    LEFT JOIN tartikel 
                        ON tbewertung.kArtikel = tartikel.kArtikel
                    WHERE tbewertung.kSprache = :lang
                        AND (tartikel.cArtNr LIKE :cartnr
                            OR tartikel.cName LIKE :cartnr)
                    ORDER BY tbewertung.kArtikel, tbewertung.dDatum DESC",
                ['lang' => (int)$_SESSION['kSprache'], 'cartnr' => '%' . $data['cArtNr'] . '%'],
                ReturnType::ARRAY_OF_OBJECTS
            );
            $this->smarty->assign('cArtNr', Text::filterXSS($data['cArtNr']))
                ->assign('filteredRatings', $filtered ?? []);
        }

        return true;
    }

    /**
     *
     */
    public function getOverview(): void
    {
        if (isset($_GET['a']) && $_GET['a'] === 'delreply' && Form::validateToken()) {
            $this->removeReply(Request::verifyGPCDataInt('kBewertung'));
            $this->alertService->addAlert(
                Alert::TYPE_SUCCESS,
                __('successRatingCommentDelete'),
                'successRatingCommentDelete'
            );
        }
        $activePagination   = $this->getActivePagination();
        $inactivePagination = $this->getInactivePagination();

        $ratings       = $this->db->query(
            "SELECT tbewertung.*, DATE_FORMAT(tbewertung.dDatum, '%d.%m.%Y') AS Datum, tartikel.cName AS ArtikelName
            FROM tbewertung
            LEFT JOIN tartikel 
                ON tbewertung.kArtikel = tartikel.kArtikel
            WHERE tbewertung.kSprache = " . (int)$_SESSION['kSprache'] . '
                AND tbewertung.nAktiv = 0
            ORDER BY tbewertung.kArtikel, tbewertung.dDatum DESC
            LIMIT ' . $inactivePagination->getLimitSQL(),
            ReturnType::ARRAY_OF_OBJECTS
        );
        $last50ratings = $this->db->query(
            "SELECT tbewertung.*, DATE_FORMAT(tbewertung.dDatum, '%d.%m.%Y') AS Datum, tartikel.cName AS ArtikelName
            FROM tbewertung
            LEFT JOIN tartikel 
                ON tbewertung.kArtikel = tartikel.kArtikel
            WHERE tbewertung.kSprache = " . (int)$_SESSION['kSprache'] . '
                AND tbewertung.nAktiv = 1
            ORDER BY tbewertung.dDatum DESC
            LIMIT ' . $activePagination->getLimitSQL(),
            ReturnType::ARRAY_OF_OBJECTS
        );

        $this->smarty->assign('oPagiInaktiv', $inactivePagination)
            ->assign('oPagiAktiv', $activePagination)
            ->assign('oBewertung_arr', $ratings)
            ->assign('oBewertungLetzten50_arr', $last50ratings)
            ->assign('oConfig_arr', \getAdminSectionSettings(\CONF_BEWERTUNG));
    }

    /**
     * @return Pagination
     */
    private function getInactivePagination(): Pagination
    {
        $totalCount = (int)$this->db->query(
            'SELECT COUNT(*) AS nAnzahl
            FROM tbewertung
            WHERE kSprache = ' . (int)$_SESSION['kSprache'] . '
                AND nAktiv = 0',
            ReturnType::SINGLE_OBJECT
        )->nAnzahl;

        return (new Pagination('inactive'))
            ->setItemCount($totalCount)
            ->assemble();
    }

    /**
     * @return Pagination
     */
    private function getActivePagination(): Pagination
    {
        $activeCount = (int)$this->db->query(
            'SELECT COUNT(*) AS nAnzahl
            FROM tbewertung
            WHERE kSprache = ' . (int)$_SESSION['kSprache'] . '
                AND nAktiv = 1',
            ReturnType::SINGLE_OBJECT
        )->nAnzahl;

        return (new Pagination('active'))
            ->setItemCount($activeCount)
            ->assemble();
    }

    /**
     * @param int $id
     * @return ReviewModel|null
     */
    public function getRating(int $id): ?ReviewModel
    {
        try {
            return new ReviewModel(['id' => $id], $this->db);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * @param array $data
     * @return bool
     */
    private function edit($data): bool
    {
        $id = Request::verifyGPCDataInt('kBewertung');
        try {
            $rating = new ReviewModel(['id' => $id], $this->db);
        } catch (Exception $e) {
            return false;
        }
        if ($rating->id === null
            || empty($data['cName'])
            || empty($data['cTitel'])
            || !isset($data['nSterne'])
            || (int)$data['nSterne'] <= 0
        ) {
            return false;
        }
        $rating->name    = $data['cName'];
        $rating->title   = $data['cTitel'];
        $rating->content = $data['cText'];
        $rating->stars   = (int)$data['nSterne'];
        $rating->answer  = !empty($data['cAntwort']) ? $data['cAntwort'] : null;

        if ($data['cAntwort'] !== $rating->answer) {
            $rating->answerDate = !empty($data['cAntwort']) ? \date('Y-m-d') : null;
        }
        $rating->save();
        $this->updateAverage($rating->productID, $this->config['bewertung']['bewertung_freischalten']);

        $this->cache->flushTags([\CACHING_GROUP_ARTICLE . '_' . $rating->productID]);

        return true;
    }

    /**
     * @param array $ids
     * @return int
     */
    private function delete(array $ids): int
    {
        $cacheTags = [];
        $affected  = 0;
        foreach (\array_map('\intval', $ids) as $id) {
            try {
                $model = new ReviewModel(['id' => $id], $this->db);
            } catch (Exception $e) {
                continue;
            }
            $this->updateAverage($model->productID, $this->config['bewertung']['bewertung_freischalten']);
            $this->deleteRatingReward($model);
            $model->delete();
            $cacheTags[] = $model->productID;
            ++$affected;
        }
        $this->cache->flushTags(map($cacheTags, function ($e) {
            return \CACHING_GROUP_ARTICLE . '_' . $e;
        }));

        return $affected;
    }

    /**
     * @param array $ids
     * @return int
     */
    public function activate(array $ids): int
    {
        $cacheTags = [];
        $affected  = 0;
        foreach (\array_map('\intval', $ids) as $i => $id) {
            try {
                $model = new ReviewModel(['id' => $id], $this->db);
            } catch (Exception $e) {
                continue;
            }
            $model->active = 1;
            $model->save(['active']);
            $this->updateAverage($model->productID, $this->config['bewertung']['bewertung_freischalten']);
            $this->addReward($model);
            $cacheTags[] = $model->productID;
            ++$affected;
        }
        $this->cache->flushTags(map($cacheTags, function ($e) {
            return \CACHING_GROUP_ARTICLE . '_' . $e;
        }));

        return $affected;
    }

    /**
     * @param int $id
     */
    private function removeReply(int $id): void
    {
        try {
            $model = new ReviewModel(['id' => $id], $this->db);
        } catch (Exception $e) {
            return;
        }
        $model->answer     = null;
        $model->answerDate = null;
        $model->save(['answer', 'answerDate']);
    }

    /**
     * @param ReviewModel $rating
     * @return int
     */
    private function deleteRatingReward(ReviewModel $rating): int
    {
        $affected = 0;
        foreach ($rating->bonus as $bonusItem) {
            /** @var ReviewBonusModel $bonusItem */
            $customer = $this->db->select('tkunde', 'kKunde', $bonusItem->customerID);
            if ($customer !== null && $customer->kKunde > 0) {
                $balance = $customer->fGuthaben - $bonusItem->bonus;
                $upd     = (object)['fGuthaben' => $balance > 0 ? $balance : 0];
                $this->db->update('tkunde', 'kKunde', $bonusItem->customerID, $upd);
                ++$affected;
            }
        }

        return $affected;
    }
}
