<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Survey;


use DB\DbInterface;

/**
 * Class SurveyQuestionFactory
 * @package JTL
 */
class SurveyQuestionFactory
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * SurveyQuestionFactory constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @return SurveyQuestion
     */
    public function create(): SurveyQuestion
    {
        return new SurveyQuestion($this->db);
    }
}
