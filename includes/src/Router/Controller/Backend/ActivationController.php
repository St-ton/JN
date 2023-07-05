<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Customer\Customer;
use JTL\DB\SqlObject;
use JTL\Helpers\Form;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Request;
use JTL\Helpers\Seo;
use JTL\Helpers\Text;
use JTL\Pagination\Pagination;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class ActivationController
 * @package JTL\Router\Controller\Backend
 */
class ActivationController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::UNLOCK_CENTRAL_VIEW);
        $this->setLanguage();
        $this->getText->loadAdminLocale('pages/freischalten');

        $ratingsSQL    = new SqlObject();
        $liveSearchSQL = new SqlObject();
        $commentsSQL   = new SqlObject();
        $recipientsSQL = new SqlObject();
        $liveSearchSQL->setOrder(' dZuletztGesucht DESC ');
        $recipientsSQL->setOrder(' tnewsletterempfaenger.dEingetragen DESC');
        $tab = Request::verifyGPDataString('tab');

        if (Request::verifyGPCDataInt('Suche') === 1) {
            $search = Text::filterXSS(Request::verifyGPDataString('cSuche'));

            if (\mb_strlen($search) > 0) {
                switch (Request::verifyGPDataString('cSuchTyp')) {
                    case 'Bewertung':
                        $tab = 'bewertungen';
                        $ratingsSQL->setWhere(' AND (tbewertung.cName LIKE :srch
                                            OR tbewertung.cTitel LIKE :srch
                                            OR tartikel.cName LIKE :srch)');
                        $ratingsSQL->addParam('srch', '%' . $search . '%');
                        break;
                    case 'Livesuche':
                        $tab = 'livesearch';
                        $liveSearchSQL->setWhere(' AND tsuchanfrage.cSuche LIKE :srch');
                        $liveSearchSQL->addParam('srch', '%' . $search . '%');
                        break;
                    case 'Newskommentar':
                        $tab = 'newscomments';
                        $commentsSQL->setWhere(' AND (tnewskommentar.cKommentar LIKE :srch
                                            OR tkunde.cVorname LIKE :srch
                                            OR tkunde.cNachname LIKE :srch
                                            OR t.title LIKE :srch)');
                        $commentsSQL->addParam('srch', '%' . $search . '%');
                        break;
                    case 'Newsletterempfaenger':
                        $tab = 'newsletter';
                        $recipientsSQL->setWhere(' AND (tnewsletterempfaenger.cVorname LIKE :srch
                                                OR tnewsletterempfaenger.cNachname LIKE :srch
                                                OR tnewsletterempfaenger.cEmail LIKE :srch)');
                        $recipientsSQL->addParam('srch', '%' . $search . '%');
                        break;
                    default:
                        break;
                }

                $smarty->assign('cSuche', $search)
                    ->assign('cSuchTyp', Request::verifyGPDataString('cSuchTyp'));
            } else {
                $this->alertService->addError(\__('errorSearchTermMissing'), 'errorSearchTermMissing');
            }
        }

        if (Request::verifyGPCDataInt('nSort') > 0) {
            $smarty->assign('nSort', Request::verifyGPCDataInt('nSort'));

            switch (Request::verifyGPCDataInt('nSort')) {
                case 1:
                    $liveSearchSQL->setOrder(' tsuchanfrage.cSuche ASC ');
                    break;
                case 11:
                    $liveSearchSQL->setOrder(' tsuchanfrage.cSuche DESC ');
                    break;
                case 2:
                    $liveSearchSQL->setOrder(' tsuchanfrage.nAnzahlGesuche DESC ');
                    break;
                case 22:
                    $liveSearchSQL->setOrder(' tsuchanfrage.nAnzahlGesuche ASC ');
                    break;
                case 3:
                    $liveSearchSQL->setOrder(' tsuchanfrage.nAnzahlTreffer DESC ');
                    break;
                case 33:
                    $liveSearchSQL->setOrder(' tsuchanfrage.nAnzahlTreffer ASC ');
                    break;
                case 4:
                    $recipientsSQL->setOrder(' tnewsletterempfaenger.dEingetragen DESC ');
                    break;
                case 44:
                    $recipientsSQL->setOrder(' tnewsletterempfaenger.dEingetragen ASC ');
                    break;
                default:
                    break;
            }
        } else {
            $smarty->assign('nLivesucheSort', -1);
        }

        $this->getAction();

        $pagiRatings    = (new Pagination('bewertungen'))
            ->setItemCount($this->getReviewCount())
            ->assemble();
        $pagiQueries    = (new Pagination('suchanfragen'))
            ->setItemCount($this->getSearchQueryCount())
            ->assemble();
        $pagiComments   = (new Pagination('newskommentare'))
            ->setItemCount($this->getNewsCommentCount())
            ->assemble();
        $pagiRecipients = (new Pagination('newsletter'))
            ->setItemCount($this->getNewsletterRecipientCount())
            ->assemble();

        $reviews      = $this->getReviews(' LIMIT ' . $pagiRatings->getLimitSQL(), $ratingsSQL);
        $queries      = $this->getSearchQueries(' LIMIT ' . $pagiQueries->getLimitSQL(), $liveSearchSQL);
        $newsComments = $this->getNewsComments(' LIMIT ' . $pagiComments->getLimitSQL(), $commentsSQL);
        $recipients   = $this->getNewsletterRecipients(' LIMIT ' . $pagiRecipients->getLimitSQL(), $recipientsSQL);

        return $smarty->assign('ratings', $reviews)
            ->assign('searchQueries', $queries)
            ->assign('comments', $newsComments)
            ->assign('recipients', $recipients)
            ->assign('oPagiBewertungen', $pagiRatings)
            ->assign('oPagiSuchanfragen', $pagiQueries)
            ->assign('oPagiNewskommentare', $pagiComments)
            ->assign('oPagiNewsletterEmpfaenger', $pagiRecipients)
            ->assign('step', 'freischalten_uebersicht')
            ->assign('cTab', $tab)
            ->assign('route', $this->route)
            ->getResponse('freischalten.tpl');
    }

    private function getAction(): void
    {
        if (Request::verifyGPCDataInt('freischalten') !== 1 || !Form::validateToken()) {
            return;
        }
        if (Request::verifyGPCDataInt('bewertungen') === 1) {
            if (isset($_POST['freischaltensubmit'])) {
                if ($this->activateReviews(Request::postVar('kBewertung', []))) {
                    $this->alertService->addSuccess(\__('successRatingUnlock'), 'successRatingUnlock');
                    return;
                }
                $this->alertService->addError(\__('errorAtLeastOneRating'), 'errorAtLeastOneRating');
            } elseif (isset($_POST['freischaltenleoschen'])) {
                if ($this->deleteReviews(Request::postVar('kBewertung', []))) {
                    $this->alertService->addSuccess(\__('successRatingDelete'), 'successRatingDelete');
                    return;
                }
                $this->alertService->addError(\__('errorAtLeastOneRating'), 'errorAtLeastOneRating');
            }
            return;
        }
        if (Request::verifyGPCDataInt('suchanfragen') === 1) { // Suchanfragen
            // Mappen
            if (isset($_POST['submitMapping'])) {
                $mapping = Request::verifyGPDataString('cMapping');
                if (\mb_strlen($mapping) === 0) {
                    $this->alertService->addError(\__('errorMapNameMissing'), 'errorMapNameMissing');
                    return;
                }
                if (!GeneralObject::hasCount('kSuchanfrage', $_POST)) {
                    $this->alertService->addError(
                        \__('errorAtLeastOneLiveSearch'),
                        'errorAtLeastOneLiveSearch'
                    );
                    return;
                }
                $res = $this->mapLiveSearch($_POST['kSuchanfrage'], $mapping);
                if ($res !== 1) {
                    $searchError = match ($res) {
                        2       => \__('errorMapUnknown'),
                        3       => \__('errorSearchNotFoundDB'),
                        4       => \__('errorMapDB'),
                        5       => \__('errorMapToNotExisting'),
                        6       => \__('errorMapSelf'),
                        default => '',
                    };
                    $this->alertService->addError($searchError, 'searchError');
                    return;
                }
                if (!$this->activateSearchQueries(Request::postVar('kSuchanfrage', []))) {
                    $this->alertService->addError(
                        \__('errorLiveSearchMapNotUnlock'),
                        'errorLiveSearchMapNotUnlock'
                    );
                    return;
                }
                $this->alertService->addSuccess(
                    \sprintf(\__('successLiveSearchMap'), $mapping),
                    'successLiveSearchMap'
                );
                return;
            }

            if (isset($_POST['freischaltensubmit'])) {
                if ($this->activateSearchQueries(Request::postVar('kSuchanfrage', []))) {
                    $this->alertService->addSuccess(\__('successSearchUnlock'), 'successSearchUnlock');
                    return;
                }
                $this->alertService->addError(\__('errorAtLeastOneSearch'), 'errorAtLeastOneSearch');
                return;
            }
            if (isset($_POST['freischaltenleoschen'])) {
                if ($this->deleteSearchQueries(Request::postVar('kSuchanfrage', []))) {
                    $this->alertService->addSuccess(\__('successSearchDelete'), 'successSearchDelete');
                    return;
                }
                $this->alertService->addError(\__('errorAtLeastOneSearch'), 'errorAtLeastOneSearch');
            }
            return;
        }
        if (Request::verifyGPCDataInt('newskommentare') === 1 && Form::validateToken()) {
            if (isset($_POST['freischaltensubmit'])) {
                if ($this->activateNewsComments(Request::postVar('kNewsKommentar', []))) {
                    $this->alertService->addSuccess(\__('successNewsCommentUnlock'), 'successNewsCommentUnlock');
                    return;
                }
                $this->alertService->addError(\__('errorAtLeastOneNewsComment'), 'errorAtLeastOneNewsComment');
                return;
            }
            if (isset($_POST['freischaltenleoschen'])) {
                if ($this->deleteNewsComments(Request::postVar('kNewsKommentar', []))) {
                    $this->alertService->addSuccess(\__('successNewsCommentDelete'), 'successNewsCommentDelete');
                    return;
                }
                $this->alertService->addError(\__('errorAtLeastOneNewsComment'), 'errorAtLeastOneNewsComment');
            }
            return;
        }
        if (Request::verifyGPCDataInt('newsletterempfaenger') === 1 && Form::validateToken()) {
            if (isset($_POST['freischaltensubmit'])) {
                if ($this->activateNewsletterRecipients(Request::postVar('kNewsletterEmpfaenger', []))) {
                    $this->alertService->addSuccess(\__('successNewsletterUnlock'), 'successNewsletterUnlock');
                    return;
                }
                $this->alertService->addError(\__('errorAtLeastOneNewsletter'), 'errorAtLeastOneNewsletter');
                return;
            }
            if (isset($_POST['freischaltenleoschen'])) {
                if ($this->deleteNewsletterRecipients(Request::postVar('kNewsletterEmpfaenger', []))) {
                    $this->alertService->addSuccess(\__('successNewsletterDelete'), 'successNewsletterDelete');
                    return;
                }
                $this->alertService->addError(\__('errorAtLeastOneNewsletter'), 'errorAtLeastOneNewsletter');
            }
        }
    }

    /**
     * @param string    $sql
     * @param SqlObject $searchSQL
     * @return stdClass[]
     * @former gibBewertungFreischalten()
     */
    public function getReviews(string $sql, SqlObject $searchSQL): array
    {
        $searchSQL->addParam('lid', $this->currentLanguageID);

        return $this->db->getObjects(
            "SELECT tbewertung.*, DATE_FORMAT(tbewertung.dDatum, '%d.%m.%Y') AS Datum, tartikel.cName AS ArtikelName
                FROM tbewertung
                LEFT JOIN tartikel 
                    ON tbewertung.kArtikel = tartikel.kArtikel
                WHERE tbewertung.kSprache = :lid
                    AND tbewertung.nAktiv = 0
                    " . $searchSQL->getWhere() . '
                ORDER BY tbewertung.kArtikel, tbewertung.dDatum DESC' . $sql,
            $searchSQL->getParams()
        );
    }

    /**
     * @param string    $sql
     * @param SqlObject $searchSQL
     * @return stdClass[]
     * @former gibSuchanfrageFreischalten()
     */
    public function getSearchQueries(string $sql, SqlObject $searchSQL): array
    {
        $searchSQL->addParam('lid', $this->currentLanguageID);

        return $this->db->getObjects(
            "SELECT *, DATE_FORMAT(dZuletztGesucht, '%d.%m.%Y %H:%i') AS dZuletztGesucht_de
                FROM tsuchanfrage
                WHERE nAktiv = 0 
                    AND kSprache = :lid " . $searchSQL->getWhere() . '
                ORDER BY ' . $searchSQL->getOrder() . $sql,
            $searchSQL->getParams()
        );
    }

    /**
     * @param string    $sql
     * @param SqlObject $searchSQL
     * @return stdClass[]
     * @former gibNewskommentarFreischalten()
     */
    public function getNewsComments(string $sql, SqlObject $searchSQL): array
    {
        $searchSQL->addParam('lid', $this->currentLanguageID);

        $newsComments = $this->db->getObjects(
            "SELECT tnewskommentar.*, DATE_FORMAT(tnewskommentar.dErstellt, '%d.%m.%Y  %H:%i') AS dErstellt_de, 
            tkunde.kKunde, tkunde.cVorname, tkunde.cNachname, t.title AS cBetreff
                FROM tnewskommentar
                JOIN tnews 
                    ON tnews.kNews = tnewskommentar.kNews
                JOIN tnewssprache t 
                    ON tnews.kNews = t.kNews
                LEFT JOIN tkunde 
                    ON tkunde.kKunde = tnewskommentar.kKunde
                WHERE tnewskommentar.nAktiv = 0
                    AND t.languageID = :lid"
            . $searchSQL->getWhere() . $sql,
            $searchSQL->getParams()
        );
        $service      = Shop::Container()->getPasswordService();
        foreach ($newsComments as $comment) {
            $customer           = new Customer((int)($comment->kKunde ?? 0), $service, $this->db);
            $comment->cNachname = $customer->cNachname;
        }

        return $newsComments;
    }

    /**
     * @param string    $sql
     * @param SqlObject $searchSQL
     * @return stdClass[]
     * @former gibNewsletterEmpfaengerFreischalten()
     */
    public function getNewsletterRecipients(string $sql, SqlObject $searchSQL): array
    {
        $searchSQL->addParam('lid', $this->currentLanguageID);

        return $this->db->getObjects(
            "SELECT *, DATE_FORMAT(dEingetragen, '%d.%m.%Y  %H:%i') AS dEingetragen_de, 
            DATE_FORMAT(dLetzterNewsletter, '%d.%m.%Y  %H:%i') AS dLetzterNewsletter_de
                FROM tnewsletterempfaenger
                WHERE nAktiv = 0
                    AND kSprache = :lid
                    " . $searchSQL->getWhere() .
                ' ORDER BY ' . $searchSQL->getOrder() . $sql,
            $searchSQL->getParams()
        );
    }

    /**
     * @param array $reviewIDs
     * @return bool
     * @former schalteBewertungFrei()
     */
    private function activateReviews(array $reviewIDs): bool
    {
        if (\count($reviewIDs) === 0) {
            return false;
        }
        $controller = new ReviewController(
            $this->db,
            $this->cache,
            $this->alertService,
            $this->account,
            $this->getText
        );
        $controller->activate($reviewIDs);

        return true;
    }

    /**
     * @param array $searchQueries
     * @return bool
     * @former schalteSuchanfragenFrei()
     */
    private function activateSearchQueries(array $searchQueries): bool
    {
        if (\count($searchQueries) === 0) {
            return false;
        }
        $db = $this->db;
        foreach (\array_map('\intval', $searchQueries) as $qid) {
            $query = $db->getSingleObject(
                'SELECT kSuchanfrage, kSprache, cSuche
                    FROM tsuchanfrage
                    WHERE kSuchanfrage = :qid',
                ['qid' => $qid]
            );
            if ($query === null || $query->kSuchanfrage <= 0) {
                continue;
            }
            $db->delete(
                'tseo',
                ['cKey', 'kKey', 'kSprache'],
                ['kSuchanfrage', $qid, (int)$query->kSprache]
            );
            $seo           = new stdClass();
            $seo->cSeo     = Seo::checkSeo(Seo::getSeo($query->cSuche));
            $seo->cKey     = 'kSuchanfrage';
            $seo->kKey     = $qid;
            $seo->kSprache = (int)$query->kSprache;
            $db->insert('tseo', $seo);
            $db->update(
                'tsuchanfrage',
                'kSuchanfrage',
                $qid,
                (object)['nAktiv' => 1, 'cSeo' => $seo->cSeo]
            );
        }

        return true;
    }

    /**
     * @param array $newsComments
     * @return bool
     * @former schalteNewskommentareFrei()
     */
    private function activateNewsComments(array $newsComments): bool
    {
        if (\count($newsComments) === 0) {
            return false;
        }
        $this->db->query(
            'UPDATE tnewskommentar
                SET nAktiv = 1
                WHERE kNewsKommentar IN (' . \implode(',', \array_map('\intval', $newsComments)) . ')'
        );
        $this->cache->flushTags([\CACHING_GROUP_NEWS]);

        return true;
    }

    /**
     * @param array $recipients
     * @return bool
     * @former schalteNewsletterempfaengerFrei()
     */
    private function activateNewsletterRecipients(array $recipients): bool
    {
        if (\count($recipients) === 0) {
            return false;
        }
        $this->db->query(
            'UPDATE tnewsletterempfaenger
                SET nAktiv = 1
                WHERE kNewsletterEmpfaenger IN (' . \implode(',', \array_map('\intval', $recipients)) .')'
        );

        return true;
    }

    /**
     * @param int[] $ratings
     * @return bool
     * @former loescheBewertung()
     */
    private function deleteReviews(array $ratings): bool
    {
        if (\count($ratings) === 0) {
            return false;
        }
        $this->db->query(
            'DELETE FROM tbewertung
                WHERE kBewertung IN (' . \implode(',', \array_map('\intval', $ratings)) . ')'
        );

        return true;
    }

    /**
     * @param int[] $queries
     * @return bool
     * @former loescheSuchanfragen()
     */
    private function deleteSearchQueries(array $queries): bool
    {
        if (\count($queries) === 0) {
            return false;
        }
        $queries = \array_map('\intval', $queries);

        $this->db->query(
            'DELETE FROM tsuchanfrage
                WHERE kSuchanfrage IN (' . \implode(',', $queries) . ')'
        );
        $this->db->query(
            "DELETE FROM tseo
                WHERE cKey = 'kSuchanfrage'
                    AND kKey IN (" . \implode(',', $queries) . ')'
        );

        return true;
    }

    /**
     * @param int[] $comments
     * @return bool
     * @former loescheNewskommentare()
     */
    private function deleteNewsComments(array $comments): bool
    {
        if (\count($comments) === 0) {
            return false;
        }
        $this->db->query(
            'DELETE FROM tnewskommentar
                WHERE kNewsKommentar IN (' . \implode(',', \array_map('\intval', $comments)) . ')'
        );
        $this->cache->flushTags([\CACHING_GROUP_NEWS]);

        return true;
    }

    /**
     * @param int[] $recipients
     * @return bool
     * @former loescheNewsletterempfaenger()
     */
    private function deleteNewsletterRecipients(array $recipients): bool
    {
        if (\count($recipients) === 0) {
            return false;
        }
        $this->db->query(
            'DELETE FROM tnewsletterempfaenger
            WHERE kNewsletterEmpfaenger IN (' . \implode(',', \array_map('\intval', $recipients)) . ')'
        );

        return true;
    }

    /**
     * @param array  $queryIDs
     * @param string $mapTo
     * @return int
     * @former mappeLiveSuche()
     */
    private function mapLiveSearch(array $queryIDs, string $mapTo): int
    {
        if (\count($queryIDs) === 0 || \mb_strlen($mapTo) === 0) {
            return 2; // Leere Übergabe
        }
        $db = $this->db;
        foreach (\array_map('\intval', $queryIDs) as $queryID) {
            $query = $db->select('tsuchanfrage', 'kSuchanfrage', $queryID);
            if ($query === null || empty($query->kSuchanfrage)) {
                return 3; // Mindestens eine Suchanfrage wurde nicht in der Datenbank gefunden.
            }
            if (\mb_convert_case($query->cSuche, \MB_CASE_LOWER) === \mb_convert_case($mapTo, \MB_CASE_LOWER)) {
                return 6; // Es kann nicht auf sich selbst gemappt werden
            }
            $newQuery = $db->select('tsuchanfrage', 'cSuche', $mapTo);
            if ($newQuery === null || empty($newQuery->kSuchanfrage)) {
                return 5; // Sie haben versucht auf eine nicht existierende Suchanfrage zu mappen
            }
            $mapping                 = new stdClass();
            $mapping->kSprache       = $this->currentLanguageID;
            $mapping->cSuche         = $query->cSuche;
            $mapping->cSucheNeu      = $mapTo;
            $mapping->nAnzahlGesuche = $query->nAnzahlGesuche;

            $kSuchanfrageMapping = $db->insert('tsuchanfragemapping', $mapping);

            if (empty($kSuchanfrageMapping)) {
                return 4; // Mapping konnte nicht gespeichert werden
            }
            $db->queryPrepared(
                'UPDATE tsuchanfrage
                    SET nAnzahlGesuche = nAnzahlGesuche + :cnt
                    WHERE kSprache = :lid
                        AND kSuchanfrage = :sid',
                [
                    'cnt' => $query->nAnzahlGesuche,
                    'lid' => $this->currentLanguageID,
                    'sid' => (int)$newQuery->kSuchanfrage
                ]
            );
            $db->delete('tsuchanfrage', 'kSuchanfrage', (int)$query->kSuchanfrage);
            $db->queryPrepared(
                "UPDATE tseo
                    SET kKey = :sqid
                    WHERE cKey = 'kSuchanfrage'
                        AND kKey = :sqid",
                ['sqid' => (int)$query->kSuchanfrage]
            );
        }

        return 1;
    }

    /**
     * @return int
     * @former gibMaxBewertungen()
     */
    private function getReviewCount(): int
    {
        return (int)$this->db->getSingleObject(
            'SELECT COUNT(*) AS cnt
                FROM tbewertung
                WHERE nAktiv = 0
                    AND kSprache = :lid',
            ['lid' => $this->currentLanguageID]
        )->cnt;
    }

    /**
     * @return int
     * @former gibMaxSuchanfragen()
     */
    private function getSearchQueryCount(): int
    {
        return (int)$this->db->getSingleObject(
            'SELECT COUNT(*) AS cnt
                FROM tsuchanfrage
                WHERE nAktiv = 0
                    AND kSprache = :lid',
            ['lid' => $this->currentLanguageID]
        )->cnt;
    }

    /**
     * @return int
     * @former gibMaxNewskommentare()
     */
    private function getNewsCommentCount(): int
    {
        return (int)$this->db->getSingleObject(
            'SELECT COUNT(tnewskommentar.kNewsKommentar) AS cnt
                FROM tnewskommentar
                JOIN tnews 
                    ON tnews.kNews = tnewskommentar.kNews
                JOIN tnewssprache t 
                    ON tnews.kNews = t.kNews
                WHERE tnewskommentar.nAktiv = 0
                    AND t.languageID = :lid',
            ['lid' => $this->currentLanguageID],
        )->cnt;
    }

    /**
     * @return int
     * @former gibMaxNewsletterEmpfaenger()
     */
    private function getNewsletterRecipientCount(): int
    {
        return (int)$this->db->getSingleObject(
            'SELECT COUNT(*) AS cnt
                FROM tnewsletterempfaenger
                WHERE nAktiv = 0
                    AND kSprache = :lid',
            ['lid' => $this->currentLanguageID],
        )->cnt;
    }
}
