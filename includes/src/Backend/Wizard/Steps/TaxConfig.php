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
use JTL\Shop;

/**
 * Class TaxConfig
 * @package JTL\Backend\Wizard\Steps
 */
final class TaxConfig extends AbstractStep
{
    /**
     * TaxConfig constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        parent::__construct($db);
        $this->setTitle(__('Steuereinstellungen'));
        $this->setID(2);

        $question = new Question($db);
        $question->setID(4);
        $question->setText(__('Das Angebot dieses Onlineshops richtet sich an'));
        $question->setType(QuestionType::SELECT);
        $question->setIsMultiSelect(true);
        $option = new SelectOption();
        $option->setName(__('Unternehmer/gewerbliche Kunden'));
        $option->setValue('b2b');
        $question->addOption($option);
        $option = new SelectOption();
        $option->setName(__('Endverbraucher/private Kunden'));
        $option->setValue('b2c');
        $question->addOption($option);
        $question->setOnSave(function (QuestionInterface $question) {
            $value = $question->getValue();
            $b2b   = $value === 'b2b' || (\is_array($value) && \in_array('b2b', $value, true));
            $b2c   = $value === 'b2c' || (\is_array($value) && \in_array('b2c', $value, true));
            if ($b2b === true && $b2c === true) {
                $question->updateConfig('kundenregistrierung_abfragen_firma', 'O');
                $question->updateConfig('kundenregistrierung_abfragen_ustid', 'O');
            } elseif ($b2b === true) {
                $question->updateConfig('kundenregistrierung_abfragen_firma', 'Y');
                $question->updateConfig('kundenregistrierung_abfragen_ustid', 'Y');
                $question->updateConfig('bestellvorgang_wrb_anzeigen', 0);
            } elseif ($b2c === true) {
                $question->updateConfig('kundenregistrierung_abfragen_firma', 'N');
                $question->updateConfig('kundenregistrierung_abfragen_ustid', 'N');
            }
        });
        $this->addQuestion($question);

        $question = new Question($db);
        $question->setID(5);
        $question->setText(__('USt-IdNr. der Firma'));
        $question->setIsRequired(false);
        $question->setValue(Shop::getSettingValue(\CONF_KUNDEN, 'shop_ustid'));
        $question->setType(QuestionType::TEXT);
        $question->setOnSave(function (QuestionInterface $question) {
            $question->updateConfig('shop_ustid', $question->getValue());
        });
        $this->addQuestion($question);

        $question = new Question($db);
        $question->setID(6);
        $question->setText(__('Kleinunternehmerregelung nach §19 UStG anwenden?'));
        $question->setType(QuestionType::BOOL);
        $question->setIsRequired(false);
        $question->setOnSave(function (QuestionInterface $question) {
            if ($question->getValue() === true) {
                $question->updateConfig('global_ust_auszeichnung', 'endpreis');
                $question->updateConfig('global_steuerpos_anzeigen', 'N');
                $question->setLocalization(
                    'ger',
                    'global',
                    'footnoteExclusiveVat',
                    'Gemäß §19 UStG wird keine Umsatzsteuer berechnet'
                );
                $question->setLocalization(
                    'eng',
                    'global',
                    'footnoteExclusiveVat',
                    'According to the § 19 UStG we do not charge the german sales tax, ' .
                    'and consequently do not account it (small business)'
                );
            } else {
                $question->updateConfig('global_ust_auszeichnung', 'auto');
                $question->updateConfig('global_steuerpos_anzeigen', 'Y');
                $question->setLocalization(
                    'ger',
                    'global',
                    'footnoteExclusiveVat',
                    'Alle Preise zzgl. gesetzlicher USt.'
                );
                $question->setLocalization(
                    'eng',
                    'global',
                    'footnoteExclusiveVat',
                    'All prices exclusive legal <abbr title="value added tax">VAT</abbr>'
                );
            }
        });
        $this->addQuestion($question);
    }
}
