<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Survey;

use DB\DbInterface;
use DB\ReturnType;
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

    private $db;

    private $smarty;

    private $errorMsg = '';

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
     * @param int         $customerID
     * @param string|null $ipHash
     * @return bool
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
     * @param \Survey\Survey $oUmfrage
     * @param array          $oNavi_arr
     * @param int            $nAktuelleSeite
     * @return Collection
     */
    public function bearbeiteUmfrageDurchfuehrung($oUmfrage, &$oNavi_arr, &$nAktuelleSeite): Collection
    {
        $oUmfrageFrageTMP_arr = $oUmfrage->getQuestions();
        $oNavi_arr            = baueSeitenNavi($oUmfrageFrageTMP_arr, $oUmfrage->getQuestionCount());
        $nAktuelleSeite       = 1;
        if (\RequestHelper::verifyGPCDataInt('s') === 0) {
            unset($_SESSION['Umfrage']);
            $_SESSION['Umfrage']                    = new \stdClass();
            $_SESSION['Umfrage']->kUmfrage          = $oUmfrage->getID();
            $_SESSION['Umfrage']->oUmfrageFrage_arr = [];
            $_SESSION['Umfrage']->nEnde             = 0;

            // Speicher alle Fragen in Session
            foreach ($oUmfrageFrageTMP_arr as $oUmfrageFrageTMP) {
                $_SESSION['Umfrage']->oUmfrageFrage_arr[$oUmfrageFrageTMP->getID()] = $oUmfrageFrageTMP;
            }

            $from = $oNavi_arr[0]->nVon;
            $max  = $oNavi_arr[0]->nAnzahl;
        } else {
            $nAktuelleSeite = \RequestHelper::verifyGPCDataInt('s');

            if (isset($_POST['next'])) {
                $this->saveAnswers($_POST);

                $kUmfrageFrageError = pruefeEingabe($_POST);
                if ($kUmfrageFrageError > 0) {
                    $this->errorMsg = \Shop::Lang()->get('pollRequired', 'errorMessages');
                } else {
                    ++$nAktuelleSeite;
                }
            } elseif (isset($_POST['back'])) {
                --$nAktuelleSeite;
            }

            $from = $oNavi_arr[$nAktuelleSeite - 1]->nVon;
            $max  = $oNavi_arr[$nAktuelleSeite - 1]->nAnzahl;
        }
        $oUmfrageFrage_arr = $oUmfrage->getQuestions()->slice($from, $max);

        $this->smarty->assign('nSessionFragenWerte_arr', findeFragenInSession($oUmfrageFrage_arr));

        return $oUmfrageFrage_arr;
    }

    /**
     * @param array $post
     */
    public function saveAnswers($post)
    {
        if (is_array($post['kUmfrageFrage']) && count($post['kUmfrageFrage']) > 0) {
            \Shop::dbg($post);
            foreach ($post['kUmfrageFrage'] as $i => $questionID) {
                $questionID = (int)$questionID;
                $question   = $this->survey->getQuestionByID($questionID);
                $type       = $question !== null ? $question->getType() : null;
                if ($question === null
                    || $type === SurveyQuestion::TYPE_TEXT_PAGE_CHANGE
                    || $type === SurveyQuestion::TYPE_TEXT_STATIC
                ) {
                    continue;
                }
                if ($type === SurveyQuestion::TYPE_MATRIX) {
                    $_SESSION['Umfrage']->oUmfrageFrage_arr[$questionID]->oUmfrageFrageAntwort_arr = [];

                    foreach ($question->getAnswerOptions() as $questionAntwort) {
                        $_SESSION['Umfrage']->oUmfrageFrage_arr[$questionID]->oUmfrageFrageAntwort_arr[] =
                            $post[$questionID . '_' . $questionAntwort->kUmfrageFrageAntwort];
                    }
                } elseif ($type === 'matrix_multi') {
                    $_SESSION['Umfrage']->oUmfrageFrage_arr[$questionID]->oUmfrageFrageAntwort_arr = $post[$questionID];
                } else {
                    $_SESSION['Umfrage']->oUmfrageFrage_arr[$questionID]->oUmfrageFrageAntwort_arr = $post[$questionID];
                }
            }
        }
    }



    /**
     * @return array
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
                HAVING COUNT(tumfragefrage.kUmfrageFrage) > 0
                ORDER BY tumfrage.dGueltigVon DESC',
            ['lid' => \Shop::getLanguageID()],
            ReturnType::ARRAY_OF_ASSOC_ARRAYS
        );
        $surveys = [];
        foreach ($surveyIDs as $surveyID) {
            $survey = new Survey($this->db, \Nice::getInstance(), new SurveyQuestionFactory($this->db));
            $survey->load((int)$surveyID['id']);
            $surveys[] = $survey;
        }

        return $surveys;
    }
}
