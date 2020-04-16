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
final class GlobalSettings extends AbstractStep
{
    /**
     * ShopConfig constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        parent::__construct($db);
        $this->setTitle(__('stepOne'));
        $this->setDescription(__('stepOneDesc'));
        $this->setID(1);

        $question = new Question($db);
        $question->setID(1);
        $question->setSubheading(__('shopSettings'));
        $question->setText(__('shopName'));
        $question->setDescription(__('shopNameDesc'));
        $question->setValue(Shop::getSettingValue(\CONF_GLOBAL, 'global_shopname'));
        $question->setType(QuestionType::TEXT);
        $question->setOnSave(function (QuestionInterface $question) {
            $question->updateConfig('global_shopname', $question->getValue());
        });
        $this->addQuestion($question);

        $question = new Question($db);
        $question->setID(2);
        $question->setText(__('masterEmail'));
        $question->setDescription(__('masterEmailDesc'));
        $question->setType(QuestionType::EMAIL);
        $question->setValue(Shop::getSettingValue(\CONF_EMAILS, 'email_master_absender'));
        $question->setOnSave(function (QuestionInterface $question) {
            $question->updateConfig('email_master_absender', $question->getValue());
        });
        $this->addQuestion($question);

        $question = new Question($db);
        $question->setID(3);
        $question->setIsRequired(false);
        $question->setValue(true);
        $question->setLabel(__('secureDefaultSettings'));
        $question->setDescription(__('secureDefaultSettingsDesc'));
        $question->setSummaryText(__('secureDefaultSettings'));
        $question->setType(QuestionType::BOOL);
        $question->setIsFullWidth(true);
        $question->setOnSave(function (QuestionInterface $question) {
            if ($question->getValue() === true) {
                $question->updateConfig('kaufabwicklung_ssl_nutzen', 'P');
                $question->updateConfig('email_smtp_verschluesselung', 'tls');
                $question->updateConfig('email_methode', 'smtp');
                $question->updateConfig('global_cookie_secure', 'Y');
                $question->updateConfig('global_cookie_httponly', 'Y');
            } else {
                $question->updateConfig('kaufabwicklung_ssl_nutzen', 'N');
                $question->updateConfig('email_smtp_verschluesselung', '');
//                $question->updateConfig('email_methode', 'mail');
                $question->updateConfig('global_cookie_secure', 'S');
                $question->updateConfig('global_cookie_httponly', 'S');
            }
        });
        $this->addQuestion($question);

        $question = new Question($db);
        $question->setSubheading(__('vatSettings'));
        $question->setID(4);
        $question->setText(__('vatIDCompany'));
        $question->setDescription(__('vatIDCompanyTitle'));
        $question->setIsRequired(false);
        $question->setValue(Shop::getSettingValue(\CONF_KUNDEN, 'shop_ustid'));
        $question->setType(QuestionType::TEXT);
        $question->setOnSave(function (QuestionInterface $question) {
            $question->updateConfig('shop_ustid', $question->getValue());
        });
        $this->addQuestion($question);

        $question = new Question($db);
        $question->setID(5);
        $question->setText(__('smallEntrepreneur'));
        $question->setDescription(__('vatSmallEntrepreneurTitle'));
        $question->setLabel(__('vatSmallEntrepreneur'));
        $question->setSummaryText(__('vatSettings'));
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

        $question = new Question($db);
        $question->setID(6);
        $question->setText(__('customerGroupDesc'));
        $question->setDescription(__('customerGroupDescTitle'));
        $question->setSummaryText(__('customerGroup'));
        $question->setType(QuestionType::MULTI_BOOL);
        $question->setIsFullWidth(true);
        $option = new SelectOption();
        $option->setName(__('customerGroupB2B'));
        $option->setValue('b2b');
        $question->addOption($option);
        $option = new SelectOption();
        $option->setName(__('customerGroupB2C'));
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
    }
}
