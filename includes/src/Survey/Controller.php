<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Survey;

use Illuminate\Support\Collection;
use JTL\Catalog\Product\Preise;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Nice;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use stdClass;

/**
 * Class Controller
 * @package JTL\Survey
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
     * @var JTLSmarty
     */
    private $smarty;

    /**
     * @var string
     */
    private $errorMsg = '';

    /**
     * Controller constructor.
     * @param DbInterface $db
     * @param JTLSmarty   $smarty
     */
    public function __construct(DbInterface $db, JTLSmarty $smarty)
    {
        $this->db     = $db;
        $this->smarty = $smarty;
    }

    /**
     * @param Survey $survey
     */
    public function setSurvey(Survey $survey): void
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
    public function setErrorMsg(string $errorMsg): void
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
     * @param Survey $survey
     * @param int    $currentPage
     * @return int
     */
    public function init($survey, int $currentPage = 1): int
    {
        $questions   = $survey->getQuestions();
        $currentPage = \max($currentPage, 1);
        if (Request::verifyGPCDataInt('s') === 0) {
            unset($_SESSION['Umfrage']);
            $_SESSION['Umfrage']                    = new stdClass();
            $_SESSION['Umfrage']->kUmfrage          = $survey->getID();
            $_SESSION['Umfrage']->oUmfrageFrage_arr = [];
            $_SESSION['Umfrage']->nEnde             = 0;
            foreach ($questions as $question) {
                /** @var SurveyQuestion $question */
                $answer = new GivenAnswer();
                $answer->setQuestionID($question->getID());
                $answer->setQuestionType($question->getType());
                $_SESSION['Umfrage']->oUmfrageFrage_arr[$question->getID()] = $answer;
            }
        } else {
            $currentPage = Request::verifyGPCDataInt('s');

            if (isset($_POST['next'])) {
                $this->saveAnswers($_POST);
                $kUmfrageFrageError = $this->checkInputData($_POST);
                if ($kUmfrageFrageError > 0) {
                    $this->errorMsg = Shop::Lang()->get('pollRequired', 'errorMessages');
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
            /** @var Collection $chunk */
            $navItem              = new stdClass();
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
        if (empty($post['kUmfrageFrage'])) {
            return false;
        }
        foreach ($post['kUmfrageFrage'] as $questionID) {
            $questionID = (int)$questionID;
            $question   = $this->survey->getQuestionByID($questionID);
            $type       = $question !== null ? $question->getType() : null;
            $given      = $_SESSION['Umfrage']->oUmfrageFrage_arr[$questionID];
            /** @var GivenAnswer $given */
            if ($question === null
                || $type === QuestionType::TEXT_PAGE_CHANGE
                || $type === QuestionType::TEXT_STATIC
            ) {
                continue;
            }
            if ($type === QuestionType::MATRIX_SINGLE) {
                $answer = [];

                foreach ($question->getAnswerOptions() as $answerOption) {
                    $idx = 'sq' . $questionID . '_' . $answerOption->getID();
                    if (isset($post[$idx])) {
                        $answer[] = $post[$idx];
                    }
                }
            } else {
                $answer = $post['sq' . $questionID];
            }
            $given->setAnswer($answer);
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
        $msg = Shop::Lang()->get('pollAdd', 'messages');
        $this->save();
        if (Frontend::getCustomer()->getID() > 0) {
            // Bekommt der Kunde einen Kupon und ist dieser gültig?
            if ($this->survey->getCouponID() > 0) {
                $coupon = $this->db->queryPrepared(
                    "SELECT tkuponsprache.cName, tkupon.kKupon, tkupon.cCode
                        FROM tkupon
                        JOIN tkuponsprache 
                            ON tkuponsprache.kKupon = tkupon.kKupon
                        WHERE tkupon.kKupon = :cid
                            AND tkuponsprache.cISOSprache = :liso
                            AND tkupon.cAktiv = 'Y'
                            AND (
                                tkupon.dGueltigAb <= NOW() 
                                AND (tkupon.dGueltigBis IS NULL OR tkupon.dGueltigBis >= NOW())
                                )
                            AND (
                                tkupon.kKundengruppe = -1 
                                OR tkupon.kKundengruppe = :cgid)",
                    [
                        'cgid' => Frontend::getCustomer()->kKundengruppe,
                        'cid'  => $this->survey->getCouponID(),
                        'liso' => Shop::getLanguageCode()
                    ],
                    ReturnType::SINGLE_OBJECT
                );
                if ($coupon->kKupon > 0) {
                    $msg = \sprintf(Shop::Lang()->get('pollCoupon', 'messages'), $coupon->cCode);
                } else {
                    Shop::Container()->getLogService()->error(\sprintf(
                        'Fehlerhafter Kupon in Umfragebelohnung. Kunde: %s  Kupon: %s',
                        Frontend::getCustomer()->getID(),
                        $this->survey->getCouponID()
                    ));
                    $this->errorMsg = Shop::Lang()->get('pollError', 'messages');
                }
            } elseif ($this->survey->getCredits() > 0) {
                $msg = \sprintf(
                    Shop::Lang()->get('pollCredit', 'messages'),
                    Preise::getLocalizedPriceString($this->survey->getCredits())
                );
                if (!$this->updateCustomerCredits($this->survey->getCredits(), $_SESSION['Kunde']->kKunde)) {
                    Shop::Container()->getLogService()->error(\sprintf(
                        'Umfragebelohnung: Guthaben konnte nicht verrechnet werden. Kunde: %s',
                        Frontend::getCustomer()->getID()
                    ));
                    $this->errorMsg = Shop::Lang()->get('pollError', 'messages');
                }
            } elseif ($this->survey->getBonusCredits() > 0) {
                $msg = \sprintf(Shop::Lang()->get('pollExtrapoint', 'messages'), $this->survey->getBonusCredits());
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
                        (dGueltigVon <= NOW() AND dGueltigBis >= NOW()) 
                        OR (dGueltigVon <= NOW() AND dGueltigBis IS NULL)
                    )
                GROUP BY tumfrage.kUmfrage
                HAVING COUNT(tumfragefrage.kUmfrageFrage) > 0
                ORDER BY tumfrage.dGueltigVon DESC',
            ['lid' => Shop::getLanguageID()],
            ReturnType::ARRAY_OF_ASSOC_ARRAYS
        );
        $surveys   = [];
        foreach ($surveyIDs as $surveyID) {
            $survey = new Survey($this->db, Nice::getInstance(), new SurveyQuestionFactory($this->db));
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
        if ($customerID <= 0) {
            return false;
        }

        return $this->db->queryPrepared(
            'UPDATE tkunde
                SET fGuthaben = fGuthaben + :crdt
                WHERE kKunde = :cid',
            ['crdt' => (float)$credits, 'cid' => $customerID],
            ReturnType::AFFECTED_ROWS
        ) > 0;
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
        $participation = new stdClass();
        if (Frontend::getCustomer()->getID() > 0) {
            $participation->kKunde = Frontend::getCustomer()->getID();
            $participation->cIP    = '';
        } else {
            $participation->kKunde = 0;
            $participation->cIP    = $_SESSION['oBesucher']->cID;
        }
        $participation->kUmfrage       = $_SESSION['Umfrage']->kUmfrage;
        $participation->dDurchgefuehrt = 'NOW()';

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
                $data                        = new stdClass();
                $data->kUmfrageDurchfuehrung = $id;
                $data->kUmfrageFrage         = $answer->getQuestionID();

                if ($type === QuestionType::TEXT_SMALL || $type === QuestionType::TEXT_BIG) {
                    $data->kUmfrageFrageAntwort = 0;
                    $data->kUmfrageMatrixOption = 0;
                    $data->cText                = !empty($given)
                        ? Text::htmlentities(Text::filterXSS(\ltrim($given)))
                        : '';
                } elseif ($type === QuestionType::MATRIX_SINGLE || $type === QuestionType::MATRIX_MULTI) {
                    [$kUmfrageFrageAntwort, $kUmfrageMatrixOption] = \explode('_', $given);
                    $data->kUmfrageFrageAntwort                    = $kUmfrageFrageAntwort;
                    $data->kUmfrageMatrixOption                    = $kUmfrageMatrixOption;
                    $data->cText                                   = '';
                } elseif ((int)$given === -1) {
                    $data->kUmfrageFrageAntwort = 0;
                    $data->kUmfrageMatrixOption = 0;
                    $data->cText                = !empty($answer->getAnswer($i + 1))
                        ? Text::htmlentities(Text::filterXSS($answer->getAnswer($i + 1)))
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
     * @param array $post
     * @return int
     */
    public function checkInputData(array $post): int
    {
        if (!\is_array($post['kUmfrageFrage']) || \count($post['kUmfrageFrage']) === 0) {
            return 0;
        }
        foreach ($post['kUmfrageFrage'] as $i => $questionID) {
            $questionID = (int)$questionID;
            $question   = $this->survey->getQuestionByID($questionID);
            $idx        = 'sq' . $questionID;

            if ($question === null || $question->isRequired() !== true) {
                continue;
            }
            $type          = $question->getType();
            $answerOptions = $question->getAnswerOptions();
            if ($type === QuestionType::MATRIX_SINGLE || $type === QuestionType::MATRIX_MULTI) {
                if ($answerOptions->count() > 0) {
                    foreach ($answerOptions as $answerOption) {
                        if ($type === QuestionType::MATRIX_SINGLE) {
                            $idx = 'sq' . $questionID . '_' . $answerOption->getID();
                            if (!isset($post[$idx])) {
                                return $questionID;
                            }
                        } elseif ($type === QuestionType::MATRIX_MULTI) {
                            if (GeneralObject::hasCount($idx, $_POST)) {
                                $exists = false;
                                foreach ($post[$idx] as $givenMatrix) {
                                    [$questionIDAntwortTMP] = \explode('_', $givenMatrix);
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
                if (!isset($post[$idx]) || \trim($post[$idx][0]) === '') {
                    return $questionID;
                }
            } elseif (!isset($post[$idx]) && $answerOptions->count() > 0) {
                return $questionID;
            }
        }

        return 0;
    }
}
