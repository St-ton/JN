<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Survey;

use DB\DbInterface;

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
    
    public function __construct(DbInterface $db, Survey $survey, \JTLSmarty $smarty)
    {
        $this->survey = $survey;
        $this->db     = $db;
        $this->smarty = $smarty;
    }

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
     * @param array $post
     */
    public function saveAnswers($post)
    {
        if (is_array($post['kUmfrageFrage']) && count($post['kUmfrageFrage']) > 0) {
            foreach ($post['kUmfrageFrage'] as $i => $questionID) {
                $questionID = (int)$questionID;
                $question = $this->survey->getQuestionByID($questionID);
                $type = $question !== null ? $question->getType() : null;
                if ($question === null
                    || $type === SurveyQuestion::TYPE_TEXT_PAGE_CHANGE
                    || $type === SurveyQuestion::TYPE_TEXT_STATIC
                ) {
                    continue;
                }
                if ($type === 'matrix_single') {
                    $_SESSION['Umfrage']->oUmfrageFrage_arr[$questionID]->oUmfrageFrageAntwort_arr = [];

                    $questionAntwort_arr = Shop::Container()->getDB()->selectAll(
                        'tumfragefrageantwort',
                        'kUmfrageFrage',
                        $questionID,
                        'kUmfrageFrageAntwort'
                    );
                    if (is_array($questionAntwort_arr) && count($questionAntwort_arr) > 0) {
                        foreach ($questionAntwort_arr as $questionAntwort) {
                            $_SESSION['Umfrage']->oUmfrageFrage_arr[$questionID]->oUmfrageFrageAntwort_arr[] =
                                $post[$questionID . '_' . $questionAntwort->kUmfrageFrageAntwort];
                        }
                    }
                } elseif ($type === 'matrix_multi') {
                    $_SESSION['Umfrage']->oUmfrageFrage_arr[$questionID]->oUmfrageFrageAntwort_arr = $post[$questionID];
                } else {
                    $_SESSION['Umfrage']->oUmfrageFrage_arr[$questionID]->oUmfrageFrageAntwort_arr = $post[$questionID];
                }
            }
        }
    }
}
