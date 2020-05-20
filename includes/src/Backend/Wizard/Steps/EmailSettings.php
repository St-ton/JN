<?php declare(strict_types=1);

namespace JTL\Backend\Wizard\Steps;

use JTL\Backend\Wizard\Question;
use JTL\Backend\Wizard\QuestionInterface;
use JTL\Backend\Wizard\QuestionType;
use JTL\Backend\Wizard\SelectOption;
use JTL\DB\DbInterface;
use JTL\Shop;

/**
 * Class GlobalSettings
 * @package JTL\Backend\Wizard\Steps
 */
final class EmailSettings extends AbstractStep
{
    /**
     * ShopConfig constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        parent::__construct($db);
        $this->setTitle(__('stepFour'));
        $this->setDescription(__('stepFourDesc'));
        $this->setID(4);

        $question = new Question($db);
        $question->setID(11);
        $question->setSubheading(__('stepFour'));
        $question->setText(__('masterEmail'));
        $question->setDescription(__('masterEmailDesc'));
        $question->setType(QuestionType::EMAIL);
        $question->setValue(Shop::getSettingValue(\CONF_EMAILS, 'email_master_absender'));
        $question->setOnSave(function (QuestionInterface $question) {
            $question->updateConfig('email_master_absender', $question->getValue());
        });
        $this->addQuestion($question);
    }
}
