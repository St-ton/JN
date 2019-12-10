<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Backend\Wizard\Steps;

use JTL\Backend\Wizard\Question;
use JTL\Backend\Wizard\QuestionInterface;
use JTL\Backend\Wizard\QuestionType;
use JTL\Backend\Wizard\SelectOption;
use JTL\DB\DbInterface;

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
        $this->setDescription(__('stepThreeDesc'));
        $this->setID(3);


        $question = new Question($db);
        $question->setID(8);
        $question->setSubheading(__('weRecommend') . ':');
        $question->setSubheadingDescription(__('weRecommendPaymentDesc'));
        $question->setType(QuestionType::PLUGIN);
        $question->setIsFullWidth(true);
        $option = new SelectOption();
        $option->setName(__('PayPal 1'));
        $option->setValue('pluginid PayPal 1');
        $option->setLogoPath('templates/bootstrap/gfx/shop-logo.png');
        $option->setDescription('Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod'
            . ' tempor invidunt');
        $question->addOption($option);
        $option = new SelectOption();
        $option->setName(__('PayPal 2'));
        $option->setValue('pluginid PayPal 2');
        $option->setLogoPath('templates/bootstrap/gfx/shop-logo.png');
        $option->setDescription('Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod'
            . ' tempor invidunt');
        $question->addOption($option);
        $option = new SelectOption();
        $option->setName(__('PayPal 3'));
        $option->setValue('pluginid PayPal 3');
        $option->setLogoPath('templates/bootstrap/gfx/shop-logo.png');
        $option->setDescription('Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod'
            . ' tempor invidunt');
        $question->addOption($option);
        $option = new SelectOption();
        $option->setName(__('PayPal 4'));
        $option->setValue('pluginid PayPal 4');
        $option->setLogoPath('templates/bootstrap/gfx/shop-logo.png');
        $option->setDescription('Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod'
            . ' tempor invidunt');
        $question->addOption($option);
        $option = new SelectOption();
        $option->setName(__('PayPal 5'));
        $option->setValue('pluginid PayPal 5');
        $option->setLogoPath('templates/bootstrap/gfx/JTL-Shop-Logo-rgb.png');
        $option->setDescription('Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod'
            . ' tempor invidunt');
        $question->addOption($option);
        $question->setOnSave(function (QuestionInterface $question) {
        });
        $this->addQuestion($question);
    }
}
