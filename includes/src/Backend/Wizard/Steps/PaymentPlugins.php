<?php declare(strict_types=1);

namespace JTL\Backend\Wizard\Steps;

use JTL\Backend\Wizard\Question;
use JTL\Backend\Wizard\QuestionInterface;
use JTL\Backend\Wizard\QuestionType;
use JTL\Backend\Wizard\SelectOption;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use function Functional\map;

/**
 * Class PaymentPlugins
 * @package JTL\Backend\Wizard\Steps
 */
final class PaymentPlugins extends AbstractStep
{
    /**
     * PaymentPlugins constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        parent::__construct($db);
        $this->setTitle(__('stepThree'));

        $paymentMethods = map($db->query(
            "SELECT cModulId FROM tzahlungsart WHERE nNutzbar = 1 AND cModulId LIKE 'za_%'",
            ReturnType::ARRAY_OF_OBJECTS
        ), static function ($e) {
            return __($e->cModulId);
        });

        $this->setDescription(sprintf(__('stepThreeDesc'), implode(', ', $paymentMethods)));
        $this->setID(3);

        $recommendations = json_decode(file_get_contents(\JTLURL_GET_MP_RECOMMENDATIONS));

        $question = new Question($db);
        $question->setID(10);
        $question->setSubheading(__('weRecommend') . ':');
        $question->setSubheadingDescription(__('weRecommendPaymentDesc'));
        $question->setSummaryText(__('paymentMethods'));
        $question->setType(QuestionType::PLUGIN);
        $question->setIsFullWidth(true);
        $question->setIsRequired(false);

        foreach ($recommendations->extensions ?? [] as $recommendation) {
            $option = new SelectOption();
            $option->setName($recommendation->name);
            $option->setValue($recommendation->ext_id);
            $option->setLogoPath($recommendation->logo_url ?? $recommendation->icon_url);
            $option->setDescription($recommendation->description);
            $option->setLink($recommendation->store_url);
            $question->addOption($option);
        }

        $question->setOnSave(function (QuestionInterface $question) {
        });
        $this->addQuestion($question);
    }
}
