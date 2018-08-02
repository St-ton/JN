<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Survey;

use DB\DbInterface;
use DB\ReturnType;
use Session\Session;
use Tightenco\Collect\Support\Collection;

/**
 * Class Controller
 * @package Survey
 */
class Controller
{
    /**
     * @var Survey
     */
    private $survey;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var \JTLSmarty
     */
    private $smarty;

    /**
     * @var string
     */
    private $errorMsg = '';

    /**
     * Controller constructor.
     * @param DbInterface $db
     * @param \JTLSmarty  $smarty
     */
    public function __construct(DbInterface $db, \JTLSmarty $smarty)
    {
        $this->db     = $db;
        $this->smarty = $smarty;
    }

    /**
     * @param Survey $survey
     */
    public function setSurvey(Survey $survey)
    {
        $this->survey = $survey;
    }

    /**
     * @return string
     */
    public function getErrorMsg(): string
    {
        return $this->errorMsg;
    }

    /**
     * @param string $errorMsg
     */
    public function setErrorMsg(string $errorMsg)
    {
        $this->errorMsg = $errorMsg;
    }

    /**
     * @param int         $customerID
     * @param string|null $ipHash
     * @return bool
     * @former pruefeUserUmfrage()
     */
    public function checkAlreadyVoted(int $customerID = 0, string $ipHash = null): bool
    {
        $exec = $this->db->select(
            'tumfragedurchfuehrung',
            'kUmfrage',
            $this->survey->getID(),
            'kKunde',
            $customerID,
            $customerID === 0 ? 'cIP' : null,
            $customerID === 0 ? $ipHash : null,
            false,
            'kUmfrageDurchfuehrung'
        );

        return $exec === null;
    }

    /**
     * @param \Survey\Survey $survey
     * @param int            $currentPage
     * @return int
     */
    public function init($survey, int $currentPage = 1): int
    {
        $questions   = $survey->getQuestions();
        $currentPage = \max($currentPage, 1);
        if (\RequestHelper::verifyGPCDataInt('s') === 0) {
            unset($_SESSION['Umfrage']);
            $_SESSION['Umfrage']                    = new \stdClass();
            $_SESSION['Umfrage']->kUmfrage          = $survey->getID();
            $_SESSION['Umfrage']->oUmfrageFrage_arr = [];
            $_SESSION['Umfrage']->nEnde             = 0;

            // Speicher alle Fragen in Session
            foreach ($questions as $question) {
                $answer = new GivenAnswer();
                $answer->setQuestionID($question->getID());
                $answer->setQuestionType($question->getType());
                $_SESSION['Umfrage']->oUmfrageFrage_arr[$question->getID()] = $answer;
            }
        } else {
            $currentPage = \RequestHelper::verifyGPCDataInt('s');

            if (isset($_POST['next'])) {
                $this->saveAnswers($_POST);
                $kUmfrageFrageError = $this->checkInputData($_POST);
                if ($kUmfrageFrageError > 0) {
                    $this->errorMsg = \Shop::Lang()->get('pollRequired', 'errorMessages');
                } else {
                    ++$currentPage;
                }
            } elseif (isset($_POST['back'])) {
                --$currentPage;
            }
        }
        $paginated = $this->getPaginatedQuestions($questions);
        $spliced   = $paginated->first(function ($e) use ($currentPage) {
            return $e->page === $currentPage;
        })->questions;
        $survey->setQuestions($spliced);

        $this->smarty->assign('oUmfrage', $survey)
                     ->assign('nSessionFragenWerte_arr', $this->getAlreadyAnsweredQuestions($questions))
                     ->assign('nAnzahlSeiten', $paginated->count());

        return $currentPage;
    }

    /**
     * @param Collection $questions
     * @return array
     */
    private function getSliceIndices(Collection $questions): array
    {
        $indices = [0];
        $questions->each(function (SurveyQuestion $question, $i) use (&$indices) {
            if ($question->getType() === QuestionType::TEXT_PAGE_CHANGE) {
                $indices[] = $i + 1;
            }
        });

        return \array_reverse($indices);
    }

    /**
     * @param Collection $questions
     * @return Collection
     * @former baueSeitenNavi()
     */
    private function getPaginatedQuestions(Collection $questions): Collection
    {
        $questions = clone $questions;
        $navi      = new Collection();
        $chunks    = [];
        $slices    = $this->getSliceIndices($questions);
        foreach ($slices as $i => $index) {
            $chunks[] = $questions->splice($index);
        }
        $slices = \array_reverse($slices);
        foreach (\array_reverse($chunks) as $i => $chunk) {
            $navItem              = new \stdClass();
            $navItem->page        = $i + 1;
            $navItem->offsetStart = $slices[$i] ?? 0;
            $navItem->count       = $chunk->count();
            $navItem->questions   = $chunk;
            $navi->push($navItem);
        }

        return $navi;
    }

    /**
     * @param array $post
     * @return bool
     */
    public function saveAnswers(array $post): bool
    {
        if (!\is_array($post['kUmfrageFrage']) || \count($post['kUmfrageFrage']) === 0) {
            return false;
        }
        foreach ($post['kUmfrageFrage'] as $questionID) {
            $questionID = (int)$questionID;
            $question   = $this->survey->getQuestionByID($questionID);
            $type       = $question !== null ? $question->getType() : null;
            if ($question === null
                || $type === QuestionType::TEXT_PAGE_CHANGE
                || $type === QuestionType::TEXT_STATIC
            ) {
                continue;
            }
            if ($type === QuestionType::MATRIX_SINGLE) {
                $answer = [];

                foreach ($question->getAnswerOptions() as $answerOption) {
                    $answer[] = $post[$questionID . '_' . $answerOption->getID()];
                }
                $_SESSION['Umfrage']->oUmfrageFrage_arr[$questionID]->setAnswer($answer);
            } else {
                $_SESSION['Umfrage']->oUmfrageFrage_arr[$questionID]->setAnswer($post[$questionID]);
            }
        }

        return true;
    }

    /**
     * @param Collection $questions - collection of ALL questions
     * @return array
     * @former findeFragenInSession()
     */
    public function getAlreadyAnsweredQuestions(Collection $questions): array
    {
        $givenAnswers = [];
        foreach ($questions as $question) {
            $givenAnswer = $_SESSION['Umfrage']->oUmfrageFrage_arr[$question->getID()];
            /** @var GivenAnswer $givenAnswer */
            $givenAnswers[$question->getID()] = $givenAnswer;
        }

        return $givenAnswers;
    }

    /**
     * @return string
     * @former bearbeiteUmfrageAuswertung()
     */
    public function bearbeiteUmfrageAuswertung(): string
    {
        $msg = \Shop::Lang()->get('pollAdd', 'messages');
        $this->save();
        if (Session::Customer()->getID() > 0) {
            // Bekommt der Kunde einen Kupon und ist dieser gültig?
            if ($this->survey->getCouponID() > 0) {
                $oKupon = $this->db->queryPrepared(
                    "SELECT tkuponsprache.cName, tkupon.kKupon, tkupon.cCode
                        FROM tkupon
                        JOIN tkuponsprache 
                            ON tkuponsprache.kKupon = tkupon.kKupon
                        WHERE tkupon.kKupon = :cid
                            AND tkuponsprache.cISOSprache = :liso
                            AND tkupon.cAktiv = 'Y'
                            AND (tkupon.dGueltigAb <= now() 
                                AND (tkupon.dGueltigBis >= now() 
                                    OR tkupon.dGueltigBis = '0000-00-00 00:00:00')
                            )
                            AND (tkupon.kKundengruppe = -1 
                                OR tkupon.kKundengruppe = :cgid)",
                    [
                        'cgid' => Session::Customer()->kKundengruppe,
                        'cid'  => $this->survey->getCouponID(),
                        'liso' => \Shop::getLanguageCode()
                    ],
                    ReturnType::SINGLE_OBJECT
                );
                if ($oKupon->kKupon > 0) {
                    $msg = \sprintf(\Shop::Lang()->get('pollCoupon', 'messages'), $oKupon->cCode);
                } else {
                    \Shop::Container()->getLogService()->error(\sprintf(
                        'Fehlerhafter Kupon in Umfragebelohnung. Kunde: %s  Kupon: %s',
                        Session::Customer()->getID(),
                        $this->survey->getCouponID()
                    ));
                    $this->errorMsg = \Shop::Lang()->get('pollError', 'messages');
                }
            } elseif ($this->survey->getCredits() > 0) {
                $msg = \sprintf(
                    \Shop::Lang()->get('pollCredit', 'messages'),
                    \Preise::getLocalizedPriceString($this->survey->getCredits())
                );
                if (!$this->updateCustomerCredits($this->survey->getCredits(), $_SESSION['Kunde']->kKunde)) {
                    \Shop::Container()->getLogService()->error(\sprintf(
                        'Umfragebelohnung: Guthaben konnte nicht verrechnet werden. Kunde: %s',
                        Session::Customer()->getID()
                    ));
                    $this->errorMsg = \Shop::Lang()->get('pollError', 'messages');
                }
            } elseif ($this->survey->getBonusCredits() > 0) {
                $msg = \sprintf(\Shop::Lang()->get('pollExtrapoint', 'messages'), $this->survey->getBonusCredits());
                // ToDo: Bonuspunkte dem Kunden gutschreiben
            }
        }

        $_SESSION['Umfrage']->nEnde = 1;

        return $msg;
    }

    /**
     * @return array
     * @former holeUmfrageUebersicht()
     */
    public function getOverview(): array
    {
        $surveyIDs = $this->db->queryPrepared(
            'SELECT tumfrage.kUmfrage AS id
                FROM tumfrage
                JOIN tumfragefrage 
                    ON tumfragefrage.kUmfrage = tumfrage.kUmfrage
                WHERE tumfrage.nAktiv = 1
                    AND tumfrage.kSprache = :lid
                    AND (
                        (dGueltigVon <= now() 
                        AND dGueltigBis >= now()) 
                        || (dGueltigVon <= now() 
                        AND dGueltigBis = \'0000-00-00 00:00:00\')
                    )
                GROUP BY tumfrage.kUmfrage
                HAVING COUNT(tumfragefrage.kUmfrageFrage) > 0
                ORDER BY tumfrage.dGueltigVon DESC',
            ['lid' => \Shop::getLanguageID()],
            ReturnType::ARRAY_OF_ASSOC_ARRAYS
        );
        $surveys   = [];
        foreach ($surveyIDs as $surveyID) {
            $survey = new Survey($this->db, \Nice::getInstance(), new SurveyQuestionFactory($this->db));
            $survey->load((int)$surveyID['id']);
            $surveys[] = $survey;
        }

        return $surveys;
    }

    /**
     * @param float $credits
     * @param int   $customerID
     * @return bool
     * @former gibKundeGuthaben()
     */
    private function updateCustomerCredits($credits, int $customerID): bool
    {
        if ($customerID > 0) {
            return $this->db->queryPrepared(
                'UPDATE tkunde
                    SET fGuthaben = fGuthaben + :crdt
                    WHERE kKunde = :cid',
                    ['crdt' => (float)$credits, 'cid' => $customerID],
                    ReturnType::AFFECTED_ROWS
                ) > 0;
        }

        return false;
    }

    /**
     * @return bool
     * @former setzeUmfrageErgebnisse()
     */
    private function save(): bool
    {
        if (empty($_SESSION['Umfrage']->oUmfrageFrage_arr)) {
            return false;
        }
        // Eintrag in tumfragedurchfuehrung
        $participation = new \stdClass();
        if (Session::Customer()->getID() > 0) {
            $participation->kKunde = Session::Customer()->getID();
            $participation->cIP    = '';
        } else {
            $participation->kKunde = 0;
            $participation->cIP    = $_SESSION['oBesucher']->cID;
        }
        $participation->kUmfrage       = $_SESSION['Umfrage']->kUmfrage;
        $participation->dDurchgefuehrt = 'now()';

        $id = $this->db->insert('tumfragedurchfuehrung', $participation);
        foreach ($_SESSION['Umfrage']->oUmfrageFrage_arr as $j => $answer) {
            /** @var GivenAnswer $answer */
            $type = $answer->getQuestionType();
            if ($type === QuestionType::TEXT_STATIC
                || $type === QuestionType::TEXT_PAGE_CHANGE
                || !\is_array($answer->getAnswer())
                || \count($answer->getAnswer()) === 0
            ) {
                continue;
            }
            foreach ($answer->getAnswer() as $i => $given) {
                if ($given === ''
                    || $answer->getAnswer($i) === null
                    || (int)$answer->getAnswer($i) === -1
                ) {
                    continue;
                }
                $data                        = new \stdClass();
                $data->kUmfrageDurchfuehrung = $id;
                $data->kUmfrageFrage         = $answer->getQuestionID();

                if ($type === QuestionType::TEXT_SMALL || $type === QuestionType::TEXT_BIG) {
                    $data->kUmfrageFrageAntwort = 0;
                    $data->kUmfrageMatrixOption = 0;
                    $data->cText                = !empty($given)
                        ? \StringHandler::htmlentities(\StringHandler::filterXSS(\ltrim($given)))
                        : '';
                } elseif ($type === QuestionType::MATRIX_SINGLE || $type === QuestionType::MATRIX_MULTI) {
                    list($kUmfrageFrageAntwort, $kUmfrageMatrixOption) = \explode('_', $given);
                    $data->kUmfrageFrageAntwort = $kUmfrageFrageAntwort;
                    $data->kUmfrageMatrixOption = $kUmfrageMatrixOption;
                    $data->cText                = '';
                } elseif ((int)$given === -1) {
                    $data->kUmfrageFrageAntwort = 0;
                    $data->kUmfrageMatrixOption = 0;
                    $data->cText                = !empty($answer->getAnswer($i + 1))
                        ? \StringHandler::htmlentities(\StringHandler::filterXSS($answer->getAnswer($i + 1)))
                        : '';
                    \array_pop($_SESSION['Umfrage']->oUmfrageFrage_arr[$j]->oUmfrageFrageAntwort_arr);
                } else {
                    $data->kUmfrageFrageAntwort = $given;
                    $data->kUmfrageMatrixOption = 0;
                    $data->cText                = $given ?? '';
                }

                $this->db->insert('tumfragedurchfuehrungantwort', $data);
            }
        }

        return true;
    }

    /**
     * Return 0 falls alles in Ordnung
     * Return $kUmfrageFrage falls inkorrekte oder leere Antwort
     *
     * @param array $cPost_arr
     * @return int
     */
    public function checkInputData(array $cPost_arr): int
    {
        if (!\is_array($cPost_arr['kUmfrageFrage']) || \count($cPost_arr['kUmfrageFrage']) === 0) {
            return 0;
        }
        foreach ($cPost_arr['kUmfrageFrage'] as $i => $questionID) {
            $questionID = (int)$questionID;
            $question   = $this->survey->getQuestionByID($questionID);

            if ($question === null || $question->isRequired() !== true) {
                continue;
            }
            $type          = $question->getType();
            $answerOptions = $question->getAnswerOptions();
            if ($type === QuestionType::MATRIX_SINGLE || $type === QuestionType::MATRIX_MULTI) {
                if ($answerOptions->count() > 0) {
                    foreach ($answerOptions as $answerOption) {
                        if ($type === QuestionType::MATRIX_SINGLE) {
                            if (!isset($cPost_arr[$questionID . '_' . $answerOption->getID()])) {
                                return $questionID;
                            }
                        } elseif ($type === QuestionType::MATRIX_MULTI) {
                            if (\is_array($cPost_arr[$questionID]) && \count($cPost_arr[$questionID]) > 0) {
                                $exists = false;
                                foreach ($cPost_arr[$questionID] as $givenMatrix) {
                                    list($questionIDAntwortTMP, $kUmfrageMatrixOption) = \explode('_', $givenMatrix);
                                    if ((int)$questionIDAntwortTMP === $answerOption->getID()) {
                                        $exists = true;
                                        break;
                                    }
                                }

                                if ($exists === false) {
                                    return $questionID;
                                }
                            } else {
                                return $questionID;
                            }
                        }
                    }
                }
            } elseif ($type === QuestionType::TEXT_SMALL || $type === QuestionType::TEXT_BIG) {
                if (!isset($cPost_arr[$questionID]) || \trim($cPost_arr[$questionID][0]) === '') {
                    return $questionID;
                }
            } elseif (!isset($cPost_arr[$questionID]) && $answerOptions->count() > 0) {
                return $questionID;
            }
        }

        return 0;
    }
}
