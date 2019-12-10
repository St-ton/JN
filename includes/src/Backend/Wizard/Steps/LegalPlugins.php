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
 * Class LegalPlugins
 * @package JTL\Backend\Wizard\Steps
 */
final class LegalPlugins extends AbstractStep
{
    /**
     * LegalPlugins constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        parent::__construct($db);
        $this->setTitle(__('stepTwo'));
        $this->setDescription(__('stepTwoDesc'));
        $this->setID(2);

        $question = new Question($db);
        $question->setID(7);
        $question->setSubheading(__('weRecommend') . ':');
        $question->setSubheadingDescription(__('weRecommendLegalDesc'));
        $question->setSummaryText(__('legalTexts'));
        $question->setType(QuestionType::PLUGIN);
        $question->setIsFullWidth(true);
        $option = new SelectOption();
        $option->setName(__('Klarna 1'));
        $option->setValue('pluginid klarna 1');
        $option->setLogoPath('templates/bootstrap/gfx/JTL-Shop-Logo-rgb.png');
        $option->setDescription('Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod'
            . ' tempor invidunt');
        $question->addOption($option);
        $option = new SelectOption();
        $option->setName(__('Klarna 2'));
        $option->setValue('pluginid klarna 2');
        $option->setLogoPath('templates/bootstrap/gfx/JTL-Shop-Logo-rgb.png');
        $option->setDescription('Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod'
            . ' tempor invidunt');
        $question->addOption($option);
        $option = new SelectOption();
        $option->setName(__('Klarna 3'));
        $option->setValue('pluginid klarna 3');
        $option->setLogoPath('templates/bootstrap/gfx/JTL-Shop-Logo-rgb.png');
        $option->setDescription('Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod'
            . ' tempor invidunt');
        $question->addOption($option);
        $option = new SelectOption();
        $option->setName(__('Klarna 4'));
        $option->setValue('pluginid klarna 4');
        $option->setLogoPath('templates/bootstrap/gfx/JTL-Shop-Logo-rgb.png');
        $option->setDescription('Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod'
            . ' tempor invidunt');
        $question->addOption($option);
        $option = new SelectOption();
        $option->setName(__('Klarna 5'));
        $option->setValue('pluginid klarna 5');
        $option->setLogoPath('templates/bootstrap/gfx/JTL-Shop-Logo-rgb.png');
        $option->setDescription('Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod'
            . ' tempor invidunt');
        $question->addOption($option);
        $question->setOnSave(function (QuestionInterface $question) {
        });
        $this->addQuestion($question);
    }
}
