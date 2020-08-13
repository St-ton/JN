<?php declare(strict_types=1);

namespace JTL\Backend\Wizard\Steps;

use JTL\Backend\Wizard\Question;
use JTL\Backend\Wizard\QuestionInterface;
use JTL\Backend\Wizard\QuestionType;
use JTL\Backend\Wizard\QuestionValidation;
use JTL\Backend\Wizard\SelectOption;
use JTL\DB\DbInterface;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Recommendation\Manager;
use JTL\Recommendation\Recommendation;

/**
 * Class LegalPlugins
 * @package JTL\Backend\Wizard\Steps
 */
final class LegalPlugins extends AbstractStep
{
    /**
     * LegalPlugins constructor.
     * @param DbInterface $db
     * @param AlertServiceInterface $alertService
     */
    public function __construct(DbInterface $db, AlertServiceInterface $alertService)
    {
        parent::__construct($db, $alertService);
        $this->setTitle(__('stepTwo'));
        $this->setDescription(__('stepTwoDesc'));
        $this->setID(2);

        $recommendations = new Manager($this->alertService, Manager::SCOPE_WIZARD_LEGAL_TEXTS);

        $question = new Question($db);
        $question->setID(9);
        $question->setSubheading(__('weRecommend'));
        $question->setSubheadingDescription(__('weRecommendLegalDesc'));
        $question->setSummaryText(__('legalTexts'));
        $question->setType(QuestionType::PLUGIN);
        $question->setIsFullWidth(true);
        $question->setIsRequired(false);
        $question->setValue(false);
        $question->setValidation(function (QuestionInterface $question) {
            $questionValidation = new QuestionValidation($question);
            $questionValidation->checkSSL(true);

            return $questionValidation->getValidationError();
        });

        $recommendations->getRecommendations()->each(static function (Recommendation $recommendation) use ($question) {
            $option = new SelectOption();
            $option->setName($recommendation->getTitle());
            $option->setValue($recommendation->getId());
            $option->setLogoPath($recommendation->getPreviewImage());
            $option->setDescription($recommendation->getTeaser());
            $option->setLink($recommendation->getUrl());
            $question->addOption($option);
        });

        $question->setOnSave(function (QuestionInterface $question) {
            // TODO: install plugins
        });
        $this->addQuestion($question);
    }
}
